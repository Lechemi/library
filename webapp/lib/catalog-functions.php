<?php

use PgSql\Connection;

include_once('../lib/connection.php');

/**
 * Retrieves the book with the specified id, or the books
 * whose title/authors match the search input.
 * If the search input is null or empty, retrieves all books in the catalog.
 * @throws Exception
 */
function get_books($searchInput): array
{
    $searchInput = trim($searchInput);
    $whereConditions = '';
    if (!empty($searchInput)) {
        $whereConditions =
            "WHERE isbn = '$searchInput' 
                OR title ILIKE '$searchInput'
                OR first_name ILIKE '$searchInput'
                OR last_name ILIKE '$searchInput'
                OR concat(first_name, ' ', last_name) ILIKE '$searchInput'";
    }

    $sql = "SELECT b.isbn, b.title, CONCAT(a.first_name, ' ', a.last_name) as author, a.id AS author_id, p.name AS publisher, blurb
    FROM library.book b 
        INNER JOIN library.credits c ON b.isbn = c.book 
        INNER JOIN library.author a ON a.id = c.author
        INNER JOIN library.publisher p ON b.publisher = p.name
    $whereConditions
    ";

    $db = open_connection();
    pg_prepare($db, 'get-books', $sql);
    $result = pg_execute($db, 'get-books', array());
    close_connection($db);

    return group_authors(pg_fetch_all($result));
}

/**
 * Utility function that takes in the list of all books, in which multi-authored
 * books appear more than once, and returns the set of unique books, with each book
 * having its list of authors.
 */
function group_authors($queryResults): array
{
    $books = [];

    foreach ($queryResults as $row) {
        $isbn = $row['isbn'];
        $title = $row['title'];
        $author = $row['author'];
        $author_id = $row['author_id'];
        $publisher = $row['publisher'];
        $blurb = $row['blurb'];

        // If this book is not already in the books array, add it
        if (!isset($books[$isbn])) {
            $books[$isbn] = [
                'title' => $title,
                'publisher' => $publisher,
                'blurb' => $blurb,
                'authors' => []
            ];
        }

        // Append the author to the book's authors array
        $books[$isbn]['authors'][] = ['name' => $author, 'id' => $author_id];
    }

    return $books;
}

/**
 * If a branch is specified, returns the id's of all available copies for
 * the specified book that are kept in the specified branch.
 * Otherwise, returns the id's of all available copies for the specified book.
 * @throws Exception
 */
function get_available_copies($isbn, $branch): array
{
    if (!$isbn)
        throw new Exception("ISBN must be provided");

    $sql = "
        SELECT id
        FROM library.book_copy
            WHERE book = '$isbn'
            AND id NOT IN (SELECT copy FROM library.loan WHERE returned IS NULL)
            AND removed = FALSE
    ";

    if ($branch) {
        $sql .= " AND branch = $branch";
    }

    $db = open_connection();
    pg_prepare($db, 'available_copies', $sql);
    $result = pg_execute($db, 'available_copies', array());
    close_connection($db);

    return pg_fetch_all($result);
}

/**
 * Retrieves the list of all publishers.
 */
function get_publishers(): array
{
    $sql = "
        SELECT name
        FROM library.publisher
        order by name
    ";

    $db = open_connection();
    pg_prepare($db, 'publishers', $sql);
    $result = pg_execute($db, 'publishers', array());
    close_connection($db);

    return pg_fetch_all($result);
}

/**
 * Attempts to make a loan for the specified patron and book.
 * If preferred branches are specified, the loan is attempted in all of them.
 * Returns the outcome of the attempt, along with:
 * - the loaned copy and respective branch in case of success;
 * - an error message in case of failure.
 * @throws Exception
 */
function make_loan($isbn, $patron, $preferredBranches): array
{
    if (!$isbn || !$patron)
        throw new Exception("ISBN and patron id must be provided");

    if ($preferredBranches) {
        $preferredBranches = '{' . implode(',', $preferredBranches) . '}';
    }

    $params = array($isbn, $patron, $preferredBranches);

    $db = open_connection();
    setSearchPathToLibrary($db);

    $sql = "
        SELECT * FROM library.make_loan($1, $2, $3);
    ";

    pg_prepare($db, 'make-loan', $sql);
    @ $result = pg_execute($db, 'make-loan', $params);

    if ($result) {
        $loan = pg_fetch_all($result)[0];
        $result = ['ok' => true, 'copy' => $loan['_loaned_copy'], 'branch' => $loan['_loan_branch']];
    } else {
        $result = ['ok' => false, 'error' => prettifyErrorMessages(pg_last_error($db))];
    }

    close_connection($db);
    return $result;
}

/**
 * Retrieves all active and ended loans for the specified patron.
 * @throws Exception
 */
function get_loans($patron): array
{
    if (!$patron)
        throw new Exception("Patron id must be provided");

    $db = open_connection();
    $sql = "
        SELECT loan.start, loan.copy, loan.due, loan.returned, loan.id, book.isbn, book.title, branch.address, branch.city
        FROM library.loan
            INNER JOIN library.book_copy ON loan.copy = book_copy.id
            INNER JOIN library.book ON book_copy.book = book.isbn
            INNER JOIN library.branch ON book_copy.branch = branch.id
        WHERE patron = '$patron'
        ORDER BY returned desc
    ";

    pg_prepare($db, 'loans', $sql);
    $result = pg_execute($db, 'loans', array());
    close_connection($db);
    return pg_fetch_all($result);
}

/**
 * Ends the specified loan by returning the respective copy.
 * @throws Exception
 */
function return_copy($loanId): void
{
    if (!$loanId)
        throw new Exception("Loan id must be provided");

    $db = open_connection();
    setSearchPathToLibrary($db);

    $sql = "
        UPDATE library.loan l
        SET returned = NOW()
        WHERE l.id = '$loanId'
    ";

    pg_prepare($db, 'return-copy', $sql);
    @ $result = pg_execute($db, 'return-copy', array());

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    if (pg_affected_rows($result) != 1)
        throw new Exception('Invalid loan id: ' . $loanId);

    close_connection($db);
}

/**
 * Postpones the due for loan with id $loanId by $days days.
 * @throws Exception
 */
function postpone_due($loanId, $days): void
{
    if (!$loanId || !$days)
        throw new Exception("Loan id and number of days must be provided");

    $sql = "
        UPDATE library.loan l
        SET due = due + INTERVAL '$days days' 
        WHERE l.id = '$loanId'
    ";

    $db = open_connection();
    pg_prepare($db, 'postpone-due', $sql);
    @ $result = pg_execute($db, 'postpone-due', array());

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    if (pg_affected_rows($result) != 1)
        throw new Exception('Invalid loan id: ' . $loanId);

    close_connection($db);
}

/**
 * Utility function to add credits to a book.
 * @throws Exception
 */
function add_credits($authors, $isbn, false|Connection $db): void
{
    foreach ($authors as $author) {
        $sql = "INSERT INTO library.credits (author, book) VALUES ($author, '$isbn');";
        pg_prepare($db, 'add-credits' . $author, $sql);
        @ $result = pg_execute($db, 'add-credits' . $author, array());
        if (!$result) {
            throw new Exception('List of author id\'s was not valid.');
        }
    }
}

/**
 * Adds a book to the catalog with the specified fields, assigning the credits
 * to the author(s) in $authors.
 * @throws Exception
 */
function add_book($isbn, $title, $blurb, $publisher, $authors): void
{
    if (!$isbn || !$title || !$blurb || !$publisher)
        throw new Exception("All fields must be provided");

    if (!$authors)
        throw new Exception("List of author id's was not valid or empty.");

    if (!ctype_digit($isbn) || strlen($isbn) !== 13)
        throw new Exception("Not a valid ISBN.");

    $db = open_connection();

    try {
        pg_query($db, "BEGIN");

        $sql = "
        INSERT INTO library.book (isbn, title, blurb, publisher)
        VALUES ($1, $2, $3, $4)
        ";

        pg_prepare($db, 'add-book', $sql);
        @ $result = pg_execute($db, 'add-book', array($isbn, $title, $blurb, $publisher));

        if (!$result)
            throw new Exception("A book with ISBN $isbn is already in the catalog.");

        add_credits($authors, $isbn, $db);
        pg_query($db, "COMMIT");
    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        throw new Exception($e->getMessage());
    }

    close_connection($db);
}

/**
 * Updates title, blurb or publisher of the specified book.
 * @throws Exception
 */
function update_book($isbn, $title, $blurb, $publisher): void
{
    if (!$isbn)
        throw new InvalidArgumentException('ISBN is required');

    $fields = [
        'title' => $title,
        'blurb' => $blurb,
        'publisher' => $publisher,
    ];

    $validFields = array_filter($fields, fn($value) => $value !== null && $value !== '');
    if (empty($validFields))
        throw new InvalidArgumentException('At least one field must be provided for update.');

    $setParts = [];
    $params = [];
    $paramIndex = 1;

    foreach ($validFields as $field => $value) {
        $setParts[] = "$field = $" . $paramIndex;
        $params[] = $value;
        $paramIndex++;
    }

    $setClause = implode(', ', $setParts);
    $params[] = $isbn;

    $sql = "UPDATE library.book SET $setClause WHERE isbn = $" . $paramIndex;

    $db = open_connection();
    pg_prepare($db, 'update-book', $sql);
    $result = pg_execute($db, 'update-book', $params);

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    if (pg_affected_rows($result) != 1)
        throw new Exception('Invalid book isbn: ' . $isbn);

    close_connection($db);
}

/**
 * Replaces the current writer(s) for the specified book with the one(s) in $authors.
 * @throws Exception
 */
function update_authors($isbn, $authors): void
{
    if (!$isbn || !$authors)
        throw new Exception("All fields must be provided");

    $db = open_connection();

    try {
        pg_query($db, "BEGIN");

        $sql = "
            DELETE FROM library.credits
            WHERE book = '$isbn'
        ";

        pg_prepare($db, 'reset-credits', $sql);
        $result = pg_execute($db, 'reset-credits', array());

        if (!$result)
            throw new Exception(prettifyErrorMessages(pg_last_error($db)));

        add_credits($authors, $isbn, $db);
        pg_query($db, "COMMIT");
    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        throw new Exception('Error updating credits. ' . $e->getMessage());
    }

    close_connection($db);
}

/**
 * Adds a new author to the catalog.
 * @throws Exception
 */
function add_author($firstName, $lastName, $alive, $bio, $birthDate = null, $deathDate = null): void
{
    if (!$firstName || !$lastName || !$bio || !isset($alive))
        throw new Exception('Missing required fields.');

    $alive = $alive ? 1 : 0;

    $columns = ['first_name', 'last_name', 'alive', 'bio'];
    $values = ['$1', '$2', '$3', '$4'];
    $params = [$firstName, $lastName, $alive, $bio];
    $paramIndex = 5;

    if (!empty($birthDate)) {
        $columns[] = 'birth_date';
        $values[] = '$' . $paramIndex++;
        $params[] = $birthDate;
    }
    if (!empty($deathDate)) {
        $columns[] = 'death_date';
        $values[] = '$' . $paramIndex++;
        $params[] = $deathDate;

        if ($alive)
            throw new Exception("If a death date is specified, the author cannot be alive.");

        if ($birthDate && isDateAfter($birthDate, $deathDate))
            throw new Exception("Death date must follow the birth date.");
    }

    $sql = "
        INSERT INTO library.author (" . implode(', ', $columns) . ")
        VALUES (" . implode(', ', $values) . ")
    ";

    $db = open_connection();
    pg_prepare($db, 'add-author', $sql);
    @ $result = pg_execute($db, 'add-author', $params);

    if (!$result)
        throw new Exception("An author named $firstName $lastName already exists.");

    close_connection($db);
}

function isDateAfter($date1, $date2): bool
{
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    return $d1 > $d2;
}

/**
 * Updates one or more fields for the author with the specified id.
 * @throws Exception
 */
function update_author($id, $firstName, $lastName, $bio, $birthDate, $deathDate, $alive): void
{
    if (!$id)
        throw new InvalidArgumentException('Missing author ID.');

    $fields = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'bio' => $bio,
        'birth_date' => $birthDate,
        'death_date' => $deathDate,
        'alive' => $alive ? 1 : 0,
    ];

    $validFields = array_filter($fields, fn($value) => $value !== null && $value !== '');
    if (empty($validFields))
        throw new Exception('At least one field must be provided for update.');

    $setParts = [];
    $params = [];
    $paramIndex = 1;

    foreach ($validFields as $field => $value) {
        $setParts[] = "$field = $" . $paramIndex;
        $params[] = $value;
        $paramIndex++;
    }

    $setClause = implode(', ', $setParts);
    $params[] = $id;

    $sql = "UPDATE library.author SET $setClause WHERE id = $" . $paramIndex;

    $db = open_connection();
    pg_prepare($db, 'update-author', $sql);
    $result = pg_execute($db, 'update-author', $params);

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    if (pg_affected_rows($result) != 1)
        throw new Exception('Invalid author ID: ' . $id);

    close_connection($db);
}

/**
 * Returns the author with the specified id, or the
 * authors whose names match the search input.
 * @throws Exception
 */
function get_authors($searchInput): array
{
    if (!$searchInput)
        throw new Exception('Missing search input.');

    $searchInput = trim($searchInput);
    $whereConditions = '';
    if (!empty($searchInput)) {
        if (is_numeric($searchInput)) {
            $whereConditions = "WHERE id = '$searchInput'";
        } else {
            $whereConditions =
                "WHERE first_name ILIKE '$searchInput'
                OR last_name ILIKE '$searchInput'
                OR concat(first_name, ' ', last_name) ILIKE '$searchInput'";
        }
    }

    $sql = "
        SELECT *
        FROM library.author
        $whereConditions
    ";

    $db = open_connection();
    pg_prepare($db, 'get-authors', $sql);
    $result = pg_execute($db, 'get-authors', array());

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    close_connection($db);

    return pg_fetch_all($result);
}