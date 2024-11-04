<?php
ini_set ('display_errors', 'On');
ini_set('error_reporting', E_ALL);
session_start();

print('Librarian: ' . $_SESSION['id']);