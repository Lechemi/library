<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage branches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Basic table styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Step 2: Custom CSS for Hover Effect */
        .hover-lighten {
            color: #000; /* Default color: black */
            transition: color 0.2s ease-in-out;
            text-decoration: none; /* Ensure no underline by default */
        }

        .hover-lighten:hover {
            color: #555; /* Lighter shade on hover */
            text-decoration: none; /* Ensure no underline on hover */
        }

        /* Custom styles for book list */
        .branch-name {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .branch-city {
            font-size: 0.9rem;
            color: #555;
        }

        .branch-address {
            font-size: 0.8rem;
            color: #888;
        }
    </style>
</head>

<body>
<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">



    <!-- Displayed branches -->
    <div class="container">
        <a href="add-branch.php" class="btn btn-primary"> <i class="bi bi-plus-square"></i> Insert a new branch</a>

        <ul class="list-group list-group-flush rounded-4">
            <?php

            try {
                $branches = get_branches();
            } catch (Exception $e) {
                echo 'An error occurred...';
            }

            foreach ($branches as $branch => $details):
                $branch_link = 'branch.php' . '?id=' . $details['id'];
                ?>

                <!-- Branch Item -->
                <li class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center">

                    <span class="branch-name">
                        <a class="link-opacity-100-hover hover-lighten" href=<?php echo $branch_link; ?>>
                            <?php echo htmlspecialchars($details['name']); ?>
                        </a>
                    </span>

                    <span class="mx-2">•</span>

                    <span class="branch-city">
                        <?php echo htmlspecialchars($details['city']); ?>
                    </span>

                    <span class="mx-2">•</span>

                    <span class="branch-address">
                        <?php echo htmlspecialchars($details['address']); ?>
                    </span>
                </li>

            <?php endforeach; ?>
        </ul>
    </div>


</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>