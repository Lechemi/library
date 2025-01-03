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

    $sql = " 
        SELECT d.book, b.title, d.copy, u.first_name, u.last_name, u.email, d.due
        FROM library.delays('$id') d
            INNER JOIN library.user u on d.patron = u.id
            INNER JOIN library.patron p on d.patron = p.user
            INNER JOIN library.book b on d.book = b.isbn
    ";
    pg_prepare($db, 'branch-delays', $sql);
    $result = pg_execute($db, 'branch-delays', array());

    if (!$result) throw new Exception(pg_last_error($db));
    close_connection($db);

    $stats['delays'] = pg_fetch_all($result);

    return $stats;
}

/*
 * Adds a branch with specified fields.
 */
/**
 * @throws Exception
 */
function add_branch($city, $address, $name): void
{
    $db = open_connection();
    $sql = "
        INSERT INTO library.branch (address, city, name)
        VALUES ('$address', '$city', '$name')
    ";

    pg_prepare($db, 'add-branch', $sql);

    @ $result = pg_execute($db, 'add-branch', array());

    if (!$result) throw new Exception(pg_last_error($db));

    close_connection($db);
}

/*
 * Removes the branch with specified id.
 */
/**
 * @throws Exception
 */
function remove_branch($id): void
{
    $db = open_connection();
    $sql = "
        DELETE FROM library.branch
        WHERE id = '$id'
    ";

    pg_prepare($db, 'remove-branch', $sql);

    @ $result = pg_execute($db, 'remove-branch', array());

    if (!$result) throw new Exception(pg_last_error($db));

    close_connection($db);
}

/*
 * Adds $toAdd new copies of the specified book ($isbn) to the branch
 * with id $branchId.
 */
/**
 * @throws Exception
 */
function add_copies($branchId, $isbn, $toAdd): void
{

    if ($toAdd <= 0) {
        throw new Exception("Number of copies must be positive.");
    }

    $db = open_connection();
    setSearchPath($db);

    try {

        pg_query($db, "BEGIN");

        for ($i = 0; $i < $toAdd; $i++) {
            $sql = "
            INSERT INTO library.book_copy (branch, book)
            VALUES ('$branchId', '$isbn')
        ";

            pg_prepare($db, 'add-copy' . $i, $sql);
            @ $result = pg_execute($db, 'add-copy' . $i, array());
            if (!$result) throw new Exception(pg_last_error($db));
        }

        pg_query($db, "COMMIT");
    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        throw new Exception('Error while inserting new copies. ' . $e->getMessage());
    }

    close_connection($db);
}