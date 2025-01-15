<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');

session_start();
if (!isset($_SESSION['user'])) redirect('../index.php');

if (!empty($_GET['author'])) {

    try {
        $authorDetails = get_authors($_GET['author'])[0];
    } catch (Exception $e) {
        redirect('../lib/error.php');
    }

    $fullName = $authorDetails['first_name'] . ' ' . $authorDetails['last_name'];

    $birthString = "Unknown";
    if ($authorDetails['birth_date']) {
        try {
            $date = new DateTime($authorDetails['birth_date']);
            $birthString = $date->format('F d, Y');
        } catch (DateMalformedStringException $e) {
            $birthString = $authorDetails['birth_date'];
        }
    }

    $deathString = "Present";
    if ($authorDetails['alive'] == 'f') {
        if ($authorDetails['death_date']) {
            try {
                $date = new DateTime($authorDetails['death_date']);
                $deathString = $date->format('F d, Y');
            } catch (DateMalformedStringException $e) {
                $deathString = $authorDetails['death_date'];
            }
        } else {
            $deathString = "Unknown";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .custom-card {
            background-color: #f8f9fa; /* Very light grey background */
            border: none; /* No border */
            border-radius: 0.75rem; /* Rounded corners */
            padding: 1rem; /* Padding for content */
            position: relative; /* Relative positioning for the button */
        }

        .compact-info p {
            margin-bottom: 0.3rem; /* Reduced spacing */
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php
    if ($_SESSION['user']['type'] == 'patron') {
        include '../patron/navbar.php';
    } else {
        include '../librarian/navbar.php';
    }
    ?>
</div>

<div class="container my-4">
    <div class="custom-card">
        <div class="card-body compact-info">
            <h2 class="card-title mb-1">
                <strong><?= htmlspecialchars($fullName) ?></strong>
            </h2>

            <p><i class="bi bi-person-badge"></i> <strong>Author id</strong>: <?= $authorDetails['id'] ?></p>
            <p><?= htmlspecialchars($authorDetails['bio'] ?? 'No bio available.') ?></p>
            <p class="text-muted"><?= $birthString . ' - ' . $deathString ?></p>
        </div>

        <div class="card-footer mt-4">
            <?php if ($_SESSION['user']['type'] == 'librarian'): ?>
                <a href="../librarian/edit-author.php?author=<?= $authorDetails['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit author details
                </a>
            <?php endif; ?>

            <a href="../catalog/catalog.php?searchInput=<?= urlencode($fullName); ?>"
               class="btn btn-primary">
                <i class="bi bi-book"></i> View books
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>
</html>

