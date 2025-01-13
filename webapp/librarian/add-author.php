<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');
include_once('../lib/author-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
// todo check that the user is a librarian

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $dead = isset($_POST['dead']);

    try {
        add_author($_POST['firstName'], $_POST['lastName'], !$dead, $_POST['bio'], $_POST['birthdate'], $_POST['deathDate']);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add author</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php
    include '../librarian/navbar.php';
    ?>
</div>

<div class="container my-4">
    <h5 class="mb-4">Adding author</h5>

    <form method="POST" action="">

        <!-- First name -->
        <div class="mb-3">
            <label for="firstName" class="form-label">First name</label>
            <input type="text" name="firstName" class="form-control" id="firstName"
        </div>

        <!-- Last name -->
        <div class="mb-3">
            <label for="lastName" class="form-label">Last name</label>
            <input type="text" name="lastName" class="form-control" id="lastName"
        </div>

        <!-- Birthdate -->
        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" name="birthdate" class="form-control" id="birthdate" min="1600-01-01" max="9999-12-31">
        </div>

        <!-- Death date -->
        <div class="mb-3">
            <label for="deathDate" class="form-label">Death date</label>
            <input type="date" name="deathDate" class="form-control" id="deathDate"
        </div>

        <!-- Alive -->
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="dead" name="dead" >
            <label class="form-check-label" for="dead">
                This author is dead
            </label>
        </div>

        <!-- Bio -->
        <div class="mb-3">
            <label for="bio" class="form-label">Bio</label>
            <textarea id="bio" name="bio" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>