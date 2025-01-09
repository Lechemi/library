<?php

include_once('../lib/book-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');
include_once('../lib/author-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

if (!empty($_GET['author'])) {

    try {
        $authorDetails = get_authors($_GET['author'])[0];
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

} else {
    echo "Error, no author.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $dead = isset($_POST['dead']);

    try {
        update_author($_GET['author'], $_POST['firstName'], $_POST['lastName'], $_POST['bio'], $_POST['birthdate'], $_POST['deathDate'], !$dead);
        header("Refresh:0");
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
    <title>Edit book</title>
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
    <h5 class="mb-4">Editing author</h5>

    <form method="POST" action="">

        <!-- First name -->
        <div class="mb-3">
            <label for="firstName" class="form-label">First name</label>
            <input type="text" name="firstName" class="form-control" id="firstName"
                   value="<?php echo $authorDetails['first_name'] ?>">
        </div>

        <!-- Last name -->
        <div class="mb-3">
            <label for="lastName" class="form-label">Last name</label>
            <input type="text" name="lastName" class="form-control" id="lastName"
                   value="<?php echo $authorDetails['last_name'] ?>">
        </div>

        <!-- Birthdate -->
        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" name="birthdate" class="form-control" id="birthdate"
                   value="<?php echo $authorDetails['birth_date'] ?>">
        </div>

        <!-- Death date -->
        <div class="mb-3">
            <label for="deathDate" class="form-label">Death date</label>
            <input type="date" name="deathDate" class="form-control" id="deathDate"
                   value="<?php echo $authorDetails['death_date'] ?>">
        </div>

        <!-- Alive -->
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="dead"
                   name="dead" <?= $authorDetails['alive'] == 'f' ? 'checked' : '' ?> >
            <label class="form-check-label" for="dead">
                This author is dead
            </label>
        </div>

        <!-- Bio -->
        <div class="mb-3">
            <label for="bio" class="form-label">Bio</label>
            <textarea id="bio" name="bio" class="form-control"><?php echo $authorDetails['bio'] ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>