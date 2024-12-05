<?php

use PgSql\Result;

include_once('../lib/connection.php');


/*
 * TODO missing specs
 */
function get_books($searchInput): false|Result
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

    $db = open_connection();
    $sql = "SELECT b.isbn, b.title, CONCAT(a.first_name, ' ', a.last_name) as author, a.id AS author_id, p.name AS publisher, blurb
    FROM library.book b 
        INNER JOIN library.credits c ON b.isbn = c.book 
        INNER JOIN library.author a ON a.id = c.author
        INNER JOIN library.publisher p ON b.publisher = p.name
    $whereConditions
    ";

    pg_prepare($db, 'book', $sql);
    $result = pg_execute($db, 'book', array());
    close_connection($db);
    return $result;
}

/*
 * TODO missing specs
 */
function group_authors($queryResults): array
{
    $books = [];

    foreach (pg_fetch_all($queryResults) as $row) {
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
 * TODO missing specs
 */
function get_available_copies($isbn): Result|false
{
    $db = open_connection();
    $sql = "
        SELECT id
        FROM library.book_copy
            WHERE book = '$isbn'
            AND id NOT IN (SELECT copy FROM library.loan WHERE returned IS NULL)
            AND removed = FALSE
    ";

    pg_prepare($db, 'available_copies', $sql);
    $result = pg_execute($db, 'available_copies', array());
    close_connection($db);
    return $result;
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
function get_active_loans($patron): Result|false
{
    $db = open_connection();
    $sql = "
        SELECT *
        FROM library.loan
            INNER JOIN library.book_copy ON loan.copy = book_copy.id
            INNER JOIN library.book ON book_copy.book = book.isbn
            INNER JOIN library.branch ON book_copy.branch = branch.id
        WHERE returned IS NULL 
            AND patron = '$patron'
        ORDER BY due
    ";

    pg_prepare($db, 'active-loans', $sql);
    $result = pg_execute($db, 'active-loans', array());
    close_connection($db);
    return $result;
}