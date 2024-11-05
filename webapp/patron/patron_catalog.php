<?php

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/book_functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book catalog</title>

    <style>
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
    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $catalog = get_catalog();

        if ($catalog === false) {
            echo "Error in query execution.";
            exit;
        }

        $num_rows = pg_num_rows($catalog);

        if ($num_rows == 0) echo 'No rows found';

        foreach (pg_fetch_all($catalog) as $book):
            $title_link = '../catalog/book_page.php' . '?isbn=' . $book['isbn'];
            $author_link = '../catalog/author_page.php' . '?author=' . $book['author'];

            ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a class="link-opacity-100-hover hover-lighten" href=<?php echo $title_link; ?>>
                                <?php echo htmlspecialchars($book['title']); ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted">
                            <a class="link-opacity-100-hover hover-lighten" href=<?php echo $author_link; ?>>
                                <?php echo htmlspecialchars($book['first_name'] . ' ' . $book['last_name']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>



