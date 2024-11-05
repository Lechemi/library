<?php
ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL);
include_once('connection.php');
include_once('redirect.php');

session_start();

/*
 * Retrieves the user with email $usr and password $psw.
 */
function retrieve_user($usr, $psw): array
{
    $db = open_connection();

    $params = array($usr, $psw);
    $sql =
        "SELECT * FROM library.user
         WHERE email = $1 AND password = $2";

    pg_prepare($db, 'login', $sql);
    $result = pg_execute($db, 'login', $params);

    close_connection($db);

    return ($user = pg_fetch_assoc($result)) ? array(true, $user) : array(false, null);
}

if (isset($_POST) && !empty($_POST['usr']) && !empty($_POST['psw'])) {

    $email = $_POST['usr'];
    $psw = $_POST['psw'];

    $result = retrieve_user($email, $psw);
    $ok = $result[0];
    $user = $result[1];
    $user_type = $user['type'];
    if (!is_null($user)) $_SESSION['user'] = $user;
    $_SESSION['feedback'] = $ok;

    if ($ok) {
        switch ($user_type) {
            case 'librarian':
                redirect('../librarian/patron_catalog.php');
            case 'patron':
                redirect('../patron/patron_catalog.php');
        }
    }
}

redirect('../index.php');