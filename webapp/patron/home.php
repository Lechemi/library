<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once ('../lib/redirect.php');
session_start();

if (!isset($_SESSION['id'])) redirect('../index.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Patron Homepage</title>
    <!-- Bootstrap CSS -->
    <style>
        /* Additional custom styles */
        .rounded-card {
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .top-link {
            margin-bottom: 30px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <!-- Account Page Link on Top -->
    <div class="text-right top-link">
        <a href="" class="btn btn-primary">Account Page</a>
    </div>

    <h1 class="text-center mb-5">Welcome to the Library</h1>

    <div class="row">
        <!-- Book Catalogue Section -->
        <div class="col-md-6 mb-4">
            <div class="rounded-card bg-light text-center">
                <h2>Book Catalog</h2>
                <p>Explore our extensive collection of books.</p>
                <a href="" class="btn btn-secondary">Go to Catalog</a>
            </div>
        </div>

        <!-- Loan Situation Section -->
        <div class="col-md-6 mb-4">
            <div class="rounded-card bg-light text-center">
                <h2>Loan Situation</h2>
                <p>Check your current loans and due dates.</p>
                <a href="" class="btn btn-secondary">View Loan Details</a>
            </div>
        </div>
    </div>
</div>

