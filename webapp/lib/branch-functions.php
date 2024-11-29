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
        FROM library.branch
    ";

    pg_prepare($db, 'branches', $sql);
    $result = pg_execute($db, 'branches', array());
    close_connection($db);

    if ($result) return pg_fetch_all($result);

    throw new Exception(pg_last_error($db));
}