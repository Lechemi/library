<?php
include_once('../lib/connection.php');
include_once('../lib/catalog-functions.php');

/**
 * Retrieves all branches, ordered by city, address.
 * @throws Exception
 */
function get_branches(): array
{
    $sql = "
        SELECT *
        FROM library.branch ORDER BY city, address
    ";

    $db = open_connection();
    pg_prepare($db, 'branches', $sql);
    $result = pg_execute($db, 'branches', array());
    close_connection($db);

    if ($result) return pg_fetch_all($result);

    throw new Exception(prettifyErrorMessages(pg_last_error($db)));
}

/**
 * Retrieves stats about the specified branch.
 * - number of managed books
 * - number of managed copies
 * - list of current delays
 * @throws Exception
 */
function get_branch_stats($id): array
{
    if (!$id) throw new Exception('Branch ID required');

    $db = open_connection();
    setSearchPathToLibrary($db);

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

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    $stats = pg_fetch_all($result);

    $sql = " 
        SELECT d.book, b.title, d.copy, u.first_name, u.last_name, u.email, d.due
        FROM library.delays('$id') d
            INNER JOIN library.user u on d.patron = u.id
            INNER JOIN library.patron p on d.patron = p.user
            INNER JOIN library.book b on d.book = b.isbn
    ";
    pg_prepare($db, 'branch-delays', $sql);
    $result = pg_execute($db, 'branch-delays', array());

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    close_connection($db);

    $stats['delays'] = pg_fetch_all($result);
    return $stats;
}

/**
 * Adds a branch with specified fields.
 * @throws Exception
 */
function add_branch($city, $address, $name): void
{
    if (!$city || !$address || !$name)
        throw new Exception('All fields are required');

    $sql = "
        INSERT INTO library.branch (address, city, name)
        VALUES ('$address', '$city', '$name')
    ";

    $db = open_connection();
    pg_prepare($db, 'add-branch', $sql);
    @ $result = pg_execute($db, 'add-branch', array());

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    close_connection($db);
}

/**
 * Removes the branch with specified id.
 * @throws Exception
 */
function remove_branch($id): void
{
    if (!$id) throw new Exception('Branch ID required');

    $sql = "
        DELETE FROM library.branch
        WHERE id = '$id'
    ";

    $db = open_connection();
    pg_prepare($db, 'remove-branch', $sql);
    @ $result = pg_execute($db, 'remove-branch', array());

    if (!$result)
        throw new Exception(prettifyErrorMessages(pg_last_error($db)));

    close_connection($db);
}

/**
 * Adds $toAdd new copies of the specified book ($isbn) to the branch
 * with id $branchId.
 * @throws Exception
 */
function add_copies($branchId, $isbn, $toAdd): void
{
    if (!$isbn || !$toAdd || !$branchId)
        throw new Exception('All fields are required');

    if ($toAdd <= 0)
        throw new Exception("Number of copies must be positive.");

    $db = open_connection();
    setSearchPathToLibrary($db);

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

/**
 * Removes $toRemove copies of the specified book ($isbn) to the branch
 * with id $branchId.
 * @throws Exception
 */
function remove_copies($branchId, $isbn, $toRemove): void
{
    if (!$isbn || !$toRemove || !$branchId)
        throw new Exception('All fields are required');

    if ($toRemove <= 0)
        throw new Exception("Number of copies must be positive.");

    // I can only remove copies that are not currently loaned
    $copies = get_available_copies($isbn, $branchId);

    $available = sizeof($copies);
    if ($available < $toRemove)
        throw new Exception("There are only $available available copies. You tried to remove $toRemove.");

    $db = open_connection();
    setSearchPathToLibrary($db);

    try {
        pg_query($db, "BEGIN");

        foreach ($copies as $copy) {
            if ($toRemove) {
                $copyId = $copy['id'];

                $sql = "
                    UPDATE library.book_copy
                    SET removed = true
                    WHERE id = $copyId
                ";

                pg_prepare($db, 'remove-copy' . $copyId, $sql);
                @ $result = pg_execute($db, 'remove-copy' . $copyId, array());
                if (!$result) throw new Exception(pg_last_error($db));

                $toRemove--;
            } else {
                break;
            }
        }

        pg_query($db, "COMMIT");
    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        throw new Exception('Error while removing copies. ' . $e->getMessage());
    }

    close_connection($db);
}