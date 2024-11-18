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

/*
 * Sets password to newPassword for user with id of userID.
 */
function change_password($userID, $currentPassword, $newPassword): void
{
    if ($newPassword == $currentPassword)
        throw new Exception("New and current password are identical.");

    $db = open_connection();
    $sql = "
        UPDATE library.user u
        SET password = '$newPassword'
        WHERE id = '$userID' and password = '$currentPassword'
    ";

    pg_prepare($db, 'change-password', $sql);
    @ $result = pg_execute($db, 'change-password', array());

    if (!$result) {
        throw new Exception('New password is too simple.');
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Current password is incorrect.');
    }

    close_connection($db);
}