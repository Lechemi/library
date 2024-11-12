<?php

use PgSql\Result;

include_once('../lib/connection.php');

/*
 * Returns the entire book catalog.
 */
function get_catalog(): false|Result
{
    $db = open_connection();
    $sql = "SELECT b.isbn, b.title, a.first_name, a.last_name, a.id as author FROM library.book b 
    INNER JOIN library.credits c ON b.isbn = c.book 
    INNER JOIN library.author a on a.id = c.author";
    pg_prepare($db, 'catalog', $sql);
    $result = pg_execute($db, 'catalog', array());
    close_connection($db);
    return $result;
}

/*
 * Returns the book(s) with title $book or with ISBN $book.
 */
function get_book($searchInput): false|Result
{
    $searchInput = trim($searchInput);
    $params = array($searchInput);

    $db = open_connection();
    $sql = "SELECT b.isbn, b.title, a.first_name, a.last_name, a.id AS author, p.name AS publisher, blurb
    FROM library.book b 
        INNER JOIN library.credits c ON b.isbn = c.book 
        INNER JOIN library.author a ON a.id = c.author
        INNER JOIN library.publisher p ON b.publisher = p.name
    WHERE isbn = $1 
        OR title ILIKE $1 
        OR first_name ILIKE $1 
        OR last_name ILIKE $1
        OR concat(first_name, ' ', last_name) ILIKE $1
    ";

    pg_prepare($db, 'book', $sql);
    $result = pg_execute($db, 'book', $params);
    close_connection($db);
    return $result;
}
