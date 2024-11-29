<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .card-link .card {
            transition: transform 0.2s, background-color 0.2s;
        }
        .card-link .card:hover {
            transform: translateY(-5px);
            background-color: lightgrey;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="container mt-3">
        <?php include 'navbar.php'; ?>
    </div>

    <div class="container py-4">
        <div class="row">

            <?php

                $pages = [
                        'Manage Users' => ['manage-users.php', 'Insert or remove users, reset patron delays and update due dates for loans.'],
                        'Manage Branches' => ['manage-branches.php', 'View all branches and insert new ones.'],
                        'Manage Catalog' => ['manage-catalog.php', 'Insert new books and authors, add or remove book copies.'],
                ];

                foreach ($pages as $title => $details) {
                    echo '
                        <div class="col-md-4 mb-4">
                            <a href=' . $details[0] . ' class="text-decoration-none card-link">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-dark">' . $title . '</h5>
                                        <p class="card-text text-muted">' . $details[1] . '</p>
                                    </div>
                                </div>
                            </a>
                        </div>';
                }

            ?>
        </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>