<?php

use PgSql\Connection;

/*
 * Opens a connection with the database.
 */
function open_connection(): false|Connection
{
    include_once('../conf/conf.php');
    $conn = "host=" . myhost . " dbname=" . mydb . " user=" . myuser . " password=" . mypassword;
    return pg_connect($conn);
}

/*
 * Closes the specified connection.
 */
function close_connection($db): true
{
    return pg_close($db);
}