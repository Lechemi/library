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
 * Returns the book with title $book or with ISBN $book.
 */
function get_book($book): false|Result
{
    $book = trim($book);
    $params = array($book);

    $db = open_connection();
    $sql = "SELECT b.isbn, b.title, a.first_name, a.last_name, a.id as author, p.name as publisher, blurb
    FROM library.book b 
    INNER JOIN library.credits c ON b.isbn = c.book 
    INNER JOIN library.author a on a.id = c.author
    inner join library.publisher p on b.publisher = p.name
    where isbn = $1 OR title ILIKE $1";

    pg_prepare($db, 'book', $sql);
    $result = pg_execute($db, 'book', $params);
    close_connection($db);
    return $result;
}
