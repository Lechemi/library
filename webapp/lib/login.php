<?php
ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL);
include_once('redirect.php');
include_once('account-functions.php');

session_start();

if (isset($_POST) && !empty($_POST['usr']) && !empty($_POST['psw'])) {

    $email = $_POST['usr'];
    $psw = $_POST['psw'];

    $user = [];

    unset($_SESSION['login_error']);

    try {
        $user = retrieve_user($email, $psw);
        $_SESSION['user'] = $user;
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
    }

    $user_type = $user['type'];

    if (!isset($_SESSION['login_error'])) {
        redirect('../catalog/catalog.php');
    }
}

redirect('../index.php');