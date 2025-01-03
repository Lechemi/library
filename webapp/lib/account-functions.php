<?php

use PgSql\Connection;
use PgSql\Result;

include_once('../lib/connection.php');

/*
 * Retrieves the user with email $usr and password $psw.
 */
/**
 * @throws Exception
 */
function retrieve_user($usr, $psw): array
{
    $db = open_connection();

    $sql =
        "SELECT * FROM library.user
         WHERE email = '$usr'";

    pg_prepare($db, 'login', $sql);
    $result = pg_execute($db, 'login', array());

    close_connection($db);

    $user = pg_fetch_all($result);

    if (empty($user)) {
        throw new Exception("User not found!");
    }

    $user = $user[0];

    if ($psw != $user['password']) {
        throw new Exception("Password is incorrect!");
    }

    unset($user['password']);

    return $user;
}

/*
 * Retrieves info about the user with email $email.
 */
/**
 * @throws Exception
 */
function get_user_with_email($email)
{
    $db = open_connection();

    $sql =
        "SELECT * FROM library.user
         WHERE email = '$email'
    ";

    pg_prepare($db, 'user-info', $sql);
    $result = pg_execute($db, 'user-info', array());

    close_connection($db);

    $user = pg_fetch_all($result);

    if (empty($user)) {
        throw new Exception("No user with such email!");
    }

    $user = $user[0];

    if ($user['type'] == 'patron') {
        $patronInfo = pg_fetch_all(get_patron($user['id']))[0];
        unset($patronInfo['user']);
        $user['patronInfo'] = $patronInfo;
    }

    unset($user['password']);

    return $user;
}

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
/**
 * @throws Exception
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

/*
 * Resets the delay counter of the patron with id $patronId.
 */
/**
 * @throws Exception
 */
function reset_delays($patronId): void
{
    $db = open_connection();
    $sql = "
        UPDATE library.patron p
        SET n_delays = 0
        WHERE p.user = '$patronId'
    ";

    pg_prepare($db, 'reset-delays', $sql);
    @ $result = pg_execute($db, 'reset-delays', array());

    if (!$result) {
        throw new Exception('Cannot reset number of delays.');
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid patron id: ' . $patronId);
    }

    close_connection($db);
}

/*
 * Adds a new user with the specified fields.
 * Automatically sets the password to be identical to the email.
 */
/**
 * @throws Exception
 */
function add_user($email, $firstName, $lastName, $type, $taxCode): void
{
    $db = open_connection();

    setSearchPath($db);

    if ($type == 'patron') {
        $sql = "
            CALL library.create_patron('$email', '$email', '$firstName', '$lastName', '$taxCode', 'base')
        ";
    } else {
        $sql = "
        INSERT INTO library.user (email, password, first_name, last_name, type)
        VALUES ('$email', '$email', '$firstName', '$lastName', '$type')
    ";
    }

    pg_prepare($db, 'add-user', $sql);
    @ $result = pg_execute($db, 'add-user', array());

    if (!$result) {
        throw new Exception('Cannot insert user! ' . pg_last_error($db));
    }

    close_connection($db);
}

/*
 * Removes the user with the specified id.
 */
/**
 * @throws Exception
 */
function remove_user($id): void
{
    $db = open_connection();

    setSearchPath($db);

    $sql = "
        UPDATE library.user u
        SET removed = true
        WHERE u.id = '$id'
    ";

    pg_prepare($db, 'remove-user', $sql);
    @ $result = pg_execute($db, 'remove-user', array());

    if (!$result) {
        throw new Exception('Cannot remove this user. ' . pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid user id: ' . $id);
    }

    close_connection($db);
}

/*
 * Restores the user with the specified email.
 */
/**
 * @throws Exception
 */
function restore_user($id): void
{
    $db = open_connection();

    setSearchPath($db);

    $sql = "
        UPDATE library.user u
        SET removed = false
        WHERE u.id = '$id'
    ";

    pg_prepare($db, 'restore-user', $sql);
    @ $result = pg_execute($db, 'restore-user', array());

    if (!$result) {
        throw new Exception('Cannot restore this user. ' . pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid user id: ' . $id);
    }

    close_connection($db);
}

/*
 * Changes the category of the patron with the specified id.
 */
/**
 * @throws Exception
 */
function change_patron_category($patronId, $newCategory): void
{
    $db = open_connection();

    setSearchPath($db);

    $sql = "
        UPDATE library.patron p
        SET category = '$newCategory'
        WHERE p.user = '$patronId'
    ";

    pg_prepare($db, 'change-patron-category', $sql);
    @ $result = pg_execute($db, 'change-patron-category', array());

    if (!$result) {
        throw new Exception('Cannot change this patron\'s category. ' . pg_last_error($db));
    }

    if (pg_affected_rows($result) != 1) {
        throw new Exception('Invalid patron id: ' . $patronId);
    }

    close_connection($db);
}

/*
 * Returns the names of all possible patron categories.
 */
function get_category_names(): array
{
    $db = open_connection();

    $sql = "
        SELECT name
        FROM library.patron_category
    ";

    pg_prepare($db, 'categories', $sql);
    $result = pg_execute($db, 'categories', array());

    close_connection($db);

    return pg_fetch_all($result);
}