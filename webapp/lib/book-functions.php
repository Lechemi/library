<?php

use PgSql\Connection;
use PgSql\Result;

include_once('../lib/connection.php');


/*
 * Retrieves the book with the specified id, or the books
 * whose title/authors match the search input.
 * If the search input is null or empty, retrieves all books in the catalog.
 */
/**
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

/*
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

/*
 * If a branch is specified, returns the id's of all available copies for
 * the specified book that are kept in the specified branch.
 * Otherwise, returns the id's of all available copies for the specified book.
 */
function get_available_copies($isbn, $branch): array
{
    $db = open_connection();
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

    pg_prepare($db, 'available_copies', $sql);
    $result = pg_execute($db, 'available_copies', array());
    close_connection($db);

    return pg_fetch_all($result);
}

function get_publishers(): array
{
    $db = open_connection();
    $sql = "
        SELECT name
        FROM library.publisher
        order by name
    ";

    pg_prepare($db, 'publishers', $sql);
    $result = pg_execute($db, 'publishers', array());
    close_connection($db);

    return pg_fetch_all($result);
}

/*
 * TODO specs
 */
function make_loan($isbn, $patron, $preferredBranches): array
{
    if ($preferredBranches) {
        $preferredBranches = '{' . implode(',', $preferredBranches) . '}';
    }

    $params = array($isbn, $patron, $preferredBranches);

    $db = open_connection();

    $sql = "SET search_path TO library;";
    pg_prepare($db, 'set-sp', $sql);
    pg_execute($db, 'set-sp', array());

    $sql = "
        SELECT * FROM library.make_loan($1, $2, $3);
    ";

    pg_prepare($db, 'make-loan', $sql);
    @ $result = pg_execute($db, 'make-loan', $params);

    if ($result) {
        $loan = pg_fetch_all($result)[0];
        $result = ['ok' => true, 'copy' => $loan['_loaned_copy'], 'branch' => $loan['_loan_branch']];
    } else {
        $result = ['ok' => false, 'error' => pg_last_error($db)];
    }

    close_connection($db);
    return $result;
}

/*
 * TODO specs
 */
function get_loans($patron): Result|false
{
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
    return $result;
}

/*
 * TODO specs
 */
/**
 * @throws Exception
 */
function return_copy($loanId): void
{
    $db = open_connection();

    $sql = "SET search_path TO library;";
    pg_prepare($db, 'set-sp', $sql);
    pg_execute($db, 'set-sp', array());

    $sql = "
        UPDATE library.loan l
        SET returned = NOW()
        WHERE l.id = '$loanId'
    ";

    pg_prepare($db, 'return-copy', $sql);
    @ $result = pg_execute($db, 'return-copy', array());

    if (!$result) {
        throw new Exception(pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid loan id: ' . $loanId);
    }

    close_connection($db);
}

/*
 * Postpones the due for loan with id $loanId by $days days.
 */
/**
 * @throws Exception
 */
function postpone_due($loanId, $days): void
{
    $db = open_connection();

    $sql = "
        UPDATE library.loan l
        SET due = due + INTERVAL '$days days' 
        WHERE l.id = '$loanId'
    ";

    pg_prepare($db, 'postpone-due', $sql);
    @ $result = pg_execute($db, 'postpone-due', array());

    if (!$result) {
        throw new Exception(pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid loan id: ' . $loanId);
    }

    close_connection($db);
}

/*
 * Utility function to add credits to a book.
 */
/**
 * @throws Exception
 */
function add_credits($authors, $isbn, false|Connection $db): void
{
    foreach ($authors as $author) {
        $sql = "INSERT INTO library.credits (author, book) VALUES ($author, '$isbn');";
        pg_prepare($db, 'add-credits' . $author, $sql);
        $result = pg_execute($db, 'add-credits' . $author, array());
        if (!$result) {
            throw new Exception(pg_last_error($db));
        }
    }
}

/*
 * Adds a book to the catalog with the specified fields, assigning the credits to the
 * author(s) in $authors.
 */
/**
 * @throws Exception
 */
function add_book($isbn, $title, $blurb, $publisher, $authors): void
{

    $db = open_connection();

    try {

        pg_query($db, "BEGIN");

        $sql = "
        INSERT INTO library.book (isbn, title, blurb, publisher)
        VALUES ($1, $2, $3, $4)
        ";

        pg_prepare($db, 'add-book', $sql);
        $result = pg_execute($db, 'add-book', array($isbn, $title, $blurb, $publisher));

        if (!$result) {
            throw new Exception(pg_last_error($db));
        }

        add_credits($authors, $isbn, $db);

        pg_query($db, "COMMIT");

    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        throw new Exception('Error inserting book. ' . $e->getMessage());
    }

    close_connection($db);
}

/**
 * @throws Exception
 */
function update_book($isbn, $title, $blurb, $publisher): void
{
    if (!$isbn) {
        throw new InvalidArgumentException('Missing isbn.');
    }

    // Check for at least one non-falsy field
    $fields = [
        'title' => $title,
        'blurb' => $blurb,
        'publisher' => $publisher,
    ];

    // Filter out falsy fields
    $validFields = array_filter($fields, fn($value) => $value !== null && $value !== '');
    if (empty($validFields)) {
        throw new InvalidArgumentException('At least one field must be provided for update.');
    }

    // Build the SET clause dynamically
    $setParts = [];
    $params = [];
    $paramIndex = 1; // PostgreSQL parameter indexing starts at 1

    foreach ($validFields as $field => $value) {
        $setParts[] = "$field = $" . $paramIndex;
        $params[] = $value;
        $paramIndex++;
    }

    $setClause = implode(', ', $setParts);
    $params[] = $isbn; // Add the ISBN as the last parameter

    $sql = "UPDATE library.book SET $setClause WHERE isbn = $" . $paramIndex;

    $db = open_connection();

    // Prepare and execute the parameterized query
    pg_prepare($db, 'update-book', $sql);
    $result = pg_execute($db, 'update-book', $params);

    if (!$result) {
        throw new Exception('Cannot update book\'s info. ' . pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid book isbn: ' . $isbn);
    }

    close_connection($db);
}

/*
 * Replaces the current writer(s) for the specified book with the one(s) in $authors.
 */
/**
 * @throws Exception
 */
function update_authors($isbn, $authors): void
{
    $db = open_connection();

    if (empty($authors)) {
        throw new InvalidArgumentException('Authors list is empty');
    }

    try {

        pg_query($db, "BEGIN");

        $sql = "
            DELETE FROM library.credits
            WHERE book = '$isbn'
        ";

        pg_prepare($db, 'reset-credits', $sql);
        $result = pg_execute($db, 'reset-credits', array());

        if (!$result) {
            throw new Exception(pg_last_error($db));
        }

        add_credits($authors, $isbn, $db);

        pg_query($db, "COMMIT");

    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        throw new Exception('Error updating credits. ' . $e->getMessage());
    }

    close_connection($db);
}