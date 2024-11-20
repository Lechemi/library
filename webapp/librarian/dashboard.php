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
    <title>Book catalog</title>
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
            <!-- Manage Users -->
            <div class="col-md-4 mb-4">
                <a href="manage-users.php" class="text-decoration-none card-link">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title text-dark">Manage Users</h5>
                            <p class="card-text text-muted">Add, edit, or remove users from the system and assign roles as needed.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Manage Branches -->
            <div class="col-md-4 mb-4">
                <a href="manage-branches.php" class="text-decoration-none card-link">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title text-dark">Manage Branches</h5>
                            <p class="card-text text-muted">Organize and oversee branches, update details, or create new ones.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Manage Catalog -->
            <div class="col-md-4 mb-4">
                <a href="manage-catalog.php" class="text-decoration-none card-link">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title text-dark">Manage Catalog</h5>
                            <p class="card-text text-muted">Update product listings, manage categories, and oversee inventory.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>