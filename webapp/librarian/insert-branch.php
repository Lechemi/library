<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['branch_name'] ?? '';
    $city = $_POST['branch_city'] ?? '';
    $address = $_POST['branch_address'] ?? '';

    try {
        add_branch($city, $address, $name);
        $message = "Branch added successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "danger";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert branch</title>
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

    <div class="container d-flex justify-content-center">
        <div class="w-50">
            <form method="post">
                <div class="mb-3">
                    <label for="branchName" class="form-label">Branch name</label>
                    <input type="text" class="form-control" id="branchName" name="branch_name" placeholder="Enter branch name" required>
                </div>
                <div class="mb-3">
                    <label for="branchCity" class="form-label">City</label>
                    <input type="text" class="form-control" id="branchCity" name="branch_city" placeholder="Enter city" required>
                </div>
                <div class="mb-3">
                    <label for="branchAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" id="branchAddress" name="branch_address" placeholder="Enter address" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <!-- Placeholder for bottom alert -->
    <div class="mt-4">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType) ?> mt-5 alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>