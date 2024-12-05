<?php

use PgSql\Result;

include_once('../lib/connection.php');

/*
 * Retrieves all branches.
 */
/**
 * @throws Exception
 */
function get_branches(): array
{

    $db = open_connection();
    $sql = "
        SELECT *
        FROM library.branch ORDER BY city
    ";

    pg_prepare($db, 'branches', $sql);
    $result = pg_execute($db, 'branches', array());
    close_connection($db);

    if ($result) return pg_fetch_all($result);

    throw new Exception(pg_last_error($db));
}

/**
 * @throws Exception
 */
function get_branch_stats($id): array
{

    $db = open_connection();

    $sql = "
        SELECT *
        FROM library.managed_copies mc
            INNER JOIN library.managed_books mb ON mc.branch = mb.branch
            INNER JOIN library.active_loans al ON mc.branch = al.branch
            INNER JOIN library.branch b ON mc.branch = b.id
        WHERE mc.branch = '$id'
    ";
    pg_prepare($db, 'branch-stats', $sql);
    $result = pg_execute($db, 'branch-stats', array());

    if (!$result) throw new Exception(pg_last_error($db));

    $stats = pg_fetch_all($result);

    $sql = "SET search_path TO library;";
    pg_prepare($db, 'set-sp', $sql);
    pg_execute($db, 'set-sp', array());

    $sql = " SELECT * FROM library.delays('$id') ";
    pg_prepare($db, 'branch-delays', $sql);
    $result = pg_execute($db, 'branch-delays', array());

    if (!$result) throw new Exception(pg_last_error($db));
    close_connection($db);

    $stats['delays'] = pg_fetch_all($result);

    return $stats;
}

/**
 * @throws Exception
 */
function add_branch($city, $address): void
{
    $db = open_connection();
    $sql = "
        INSERT INTO library.branch (address, city)
        VALUES ('$address', '$city')
    ";

    pg_prepare($db, 'add-branch', $sql);

    @ $result = pg_execute($db, 'add-branch', array());

    if (!$result) throw new Exception(pg_last_error($db));

    close_connection($db);
}