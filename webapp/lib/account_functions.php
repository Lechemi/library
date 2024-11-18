<?php

use PgSql\Result;

include_once('../lib/connection.php');

/*
 * Retrieves the patron associated with userId.
 */
function get_patron($userId): Result|false
{
    $db = open_connection();
    $sql = "
        SELECT *
        FROM library.patron
        WHERE patron.user = '$userId'
    ";

    pg_prepare($db, 'patron', $sql);
    $result = pg_execute($db, 'patron', array());
    close_connection($db);
    return $result;
}

function change_password($userID, $currentPassword, $newPassword): array
{
    $db = open_connection();
    $sql = "
        UPDATE library.user u
        SET password = '$newPassword'
        WHERE id = '$userID' and password = '$currentPassword'
    ";

    pg_prepare($db, 'change-password', $sql);
    $result = pg_execute($db, 'change-password', array());

    if ($result) {
        if (pg_affected_rows($result) != 1) {
            $result = ['ok' => false, 'error' => 'Incorrect password.'];
        } else {
            $result = ['ok' => true];
        }
    } else {
        $result = ['ok' => false, 'error' => pg_last_error($db)];
    }

    close_connection($db);
    return $result;
}