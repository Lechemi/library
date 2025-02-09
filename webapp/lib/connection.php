<?php
use PgSql\Connection;

include_once(__DIR__ . '/../conf/conf.php');

/**
 * Opens a connection with the database.
 */
function open_connection(): false|Connection
{
    $conn = "host=" . myhost . " dbname=" . mydb . " user=" . myuser . " password=" . mypassword;
    return pg_connect($conn);
}

/**
 * Closes the specified connection.
 */
function close_connection($db): true
{
    return pg_close($db);
}

/**
 * Sets the search path to library.
 */
function setSearchPathToLibrary(false|Connection $db): void
{
    $sql = "SET search_path TO library;";
    pg_prepare($db, 'set-sp', $sql);
    pg_execute($db, 'set-sp', array());
}

/*
 * Utility function.
 */
function prettifyErrorMessages($exceptionMessage): string
{
    $pattern = '/ERROR:\s(.*?)\sCONTEXT:/';
    if (preg_match($pattern, $exceptionMessage, $matches)) {
        return $matches[1];
    }
    return '';
}