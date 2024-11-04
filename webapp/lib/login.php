<?php
ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL);
include_once('connection.php');
include_once('redirect.php');

session_start();

/*
 * Retrieves id and type for the user with email $usr and password $psw.
 */
function retrieve_user($usr, $psw): array
{
    $db = open_connection();

    $params = array($usr, $psw);
    $sql =
        "SELECT id, type FROM library.user
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
    $id = $result[1]['id'];
    $user_type = $result[1]['type'];
    if (!is_null($id)) $_SESSION['id'] = $id;
    $_SESSION['feedback'] = $ok;

    if ($ok) {
        switch ($user_type) {
            case 'librarian':
                redirect('../librarian/home.php');
            case 'patron':
                redirect('../patron/home.php');
        }
    }
}

redirect('../index.php');