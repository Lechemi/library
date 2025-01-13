<?php

include_once('../lib/connection.php');

/*
 * Adds a new author to the catalog.
 */
/**
 * @throws Exception
 */
function add_author($firstName, $lastName, $alive, $bio, $birthDate = null, $deathDate = null): void
{

    if (!$firstName || !$lastName || !$bio || !isset($alive))
        throw new Exception('Missing required fields.');

    $alive = $alive ? 1 : 0;

    $columns = ['first_name', 'last_name', 'alive', 'bio'];
    $values = ['$1', '$2', '$3', '$4'];
    $params = [$firstName, $lastName, $alive, $bio];
    $paramIndex = 5;

    if (!empty($birthDate)) {
        $columns[] = 'birth_date';
        $values[] = '$' . $paramIndex++;
        $params[] = $birthDate;
    }
    if (!empty($deathDate)) {
        $columns[] = 'death_date';
        $values[] = '$' . $paramIndex++;
        $params[] = $deathDate;
    }

    $sql = "
        INSERT INTO library.author (" . implode(', ', $columns) . ")
        VALUES (" . implode(', ', $values) . ")
    ";

    $db = open_connection();
    pg_prepare($db, 'add-author', $sql);
    $result = pg_execute($db, 'add-author', $params);

    if (!$result) throw new Exception(prettifyExceptionMessages(pg_last_error($db)));

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

    if (!$id)
        throw new InvalidArgumentException('Missing author ID.');

    $fields = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'bio' => $bio,
        'birth_date' => $birthDate,
        'death_date' => $deathDate,
        'alive' => $alive ? 1 : 0,
    ];

    $validFields = array_filter($fields, fn($value) => $value !== null && $value !== '');
    if (empty($validFields))
        throw new Exception('At least one field must be provided for update.');

    $setParts = [];
    $params = [];
    $paramIndex = 1;

    foreach ($validFields as $field => $value) {
        $setParts[] = "$field = $" . $paramIndex;
        $params[] = $value;
        $paramIndex++;
    }

    $setClause = implode(', ', $setParts);
    $params[] = $id;

    $sql = "UPDATE library.author SET $setClause WHERE id = $" . $paramIndex;

    $db = open_connection();
    pg_prepare($db, 'update-author', $sql);
    $result = pg_execute($db, 'update-author', $params);

    if (!$result)
        throw new Exception(prettifyExceptionMessages(pg_last_error($db)));

    if (pg_affected_rows($result) != 1)
        throw new Exception('Invalid author ID: ' . $id);

    close_connection($db);
}

/*
 * Returns the author with the specified id, or the
 * authors whose names match the search input.
 */
/**
 * @throws Exception
 */
function get_authors($searchInput): array
{
    $searchInput = trim($searchInput);
    $whereConditions = '';
    if (!empty($searchInput)) {
        if (is_numeric($searchInput)) {
            $whereConditions = "WHERE id = '$searchInput'";
        } else {
            $whereConditions =
                "WHERE first_name ILIKE '$searchInput'
                OR last_name ILIKE '$searchInput'
                OR concat(first_name, ' ', last_name) ILIKE '$searchInput'";
        }
    }

    $db = open_connection();
    $sql = "
        SELECT *
        FROM library.author
        $whereConditions
    ";

    pg_prepare($db, 'get-authors', $sql);
    $result = pg_execute($db, 'get-authors', array());

    if (!$result) {
        throw new Exception('Cannot get authors: ' . pg_last_error($db));
    }

    close_connection($db);

    return pg_fetch_all($result);
}