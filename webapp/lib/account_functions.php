<?php

use PgSql\Result;

include_once('../lib/connection.php');

/*
 * Retrieves the patron associated with userId.
 */
function get_patron($userId): Result|false
{
    $db = open_connection();
    $sql = "
        SELECT *
        FROM library.patron
        WHERE patron.user = '$userId'
    ";

    pg_prepare($db, 'patron', $sql);
    $result = pg_execute($db, 'patron', array());
    close_connection($db);
    return $result;
}