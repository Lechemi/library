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

/*
 * Updates one or more fields for the author with the specified id.
 */
/**
 * @throws Exception
 */
function update_author($id, $firstName, $lastName, $bio, $birthDate, $deathDate, $alive): void
{
    // Check for at least one non-falsy field
    $fields = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'bio' => $bio,
        'birth_date' => $birthDate,
        'death_date' => $deathDate,
        'alive' => $alive
    ];

    // Filter out falsy fields
    $validFields = array_filter($fields, fn($value) => $value !== null && $value !== '');
    if (empty($validFields)) {
        throw new InvalidArgumentException('At least one field must be provided for update.');
    }

    $setParts = [];
    foreach ($validFields as $field => $value) {
        $setParts[] = "$field = '$value'";
    }

    $setClause = implode(', ', $setParts);
    $sql = "UPDATE library.author SET $setClause WHERE id = $id";

    $db = open_connection();

    pg_prepare($db, 'update-author', $sql);
    $result = pg_execute($db, 'update-author', array());

    if (!$result) {
        throw new Exception('Cannot update author\'s info. ' . pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid author id: ' . $id);
    }

    close_connection($db);
}