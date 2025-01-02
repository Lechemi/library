<?php

use PgSql\Result;

include_once('../lib/connection.php');

/*
 * Returns the author with id of $id.
 */
function get_author($id): false|Result
{
    $id = trim($id);
    $params = array($id);

    $db = open_connection();
    $sql = "
    SELECT * FROM library.author WHERE id = $1;
    ";

    pg_prepare($db, 'author', $sql);
    $result = pg_execute($db, 'author', $params);
    close_connection($db);
    return $result;
}

function add_author($firstName, $lastName, $bio, )
{

}