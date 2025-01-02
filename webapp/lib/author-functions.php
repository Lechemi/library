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

/*
 * Adds a new author to the catalog.
 */
/**
 * @throws Exception
 */
function add_author($firstName, $lastName, $bio, $birthDate, $deathDate, $alive): void
{
    $db = open_connection();

    if ($deathDate) {
        $sql = "
        INSERT INTO library.author (first_name, last_name, bio, birth_date, death_date, alive)
        VALUES ('$firstName', '$lastName', '$bio', '$birthDate', '$deathDate', '$alive')
        ";
    } else {
        $sql = "
        INSERT INTO library.author (first_name, last_name, bio, birth_date, alive)
        VALUES ('$firstName', '$lastName', '$bio', '$birthDate', '$alive')
        ";
    }

    pg_prepare($db, 'add-author', $sql);
    $result = pg_execute($db, 'add-author', array());

    if (!$result) {
        throw new Exception('Cannot insert author. ' . pg_last_error($db));
    }

    close_connection($db);
}