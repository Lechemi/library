<?php

use PgSql\Result;

include_once('../lib/connection.php');


/*
 * TODO missing specs for get_book
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
    $sql = "SELECT b.isbn, b.title, a.first_name, a.last_name, a.id AS author, p.name AS publisher, blurb
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

print_r(pg_fetch_all(get_books('9788804484447')));

?>
