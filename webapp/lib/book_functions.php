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
