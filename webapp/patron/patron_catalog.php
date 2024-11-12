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

        /* Custom styles for book list */
        .book-title {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .book-author {
            font-size: 0.9rem;
            color: #555;
        }

        .book-isbn {
            font-size: 0.8rem;
            color: #888;
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<!-- Search bar -->
<div class="container my-3">
    <h2 class="text-center mb-4">Explore our catalog</h2>
    <form class="d-flex justify-content-center" method="GET" action="">
        <div class="input-group rounded-4" style="max-width: 400px;">
            <input class="form-control rounded-4" type="search" placeholder="Enter a title, an ISBN code or an author"
                   aria-label="Search" name="searchInput">
            <button class="btn rounded-4" type="submit">Search</button>
        </div>
    </form>

    <!-- TODO: clear search button -->
</div>

<!-- Displayed book(s) -->
<div class="container">
    <ul class="list-group list-group-flush rounded-4">
        <?php

        if (!empty($_GET['searchInput'])) {
            $result = get_book($_GET['searchInput']);
        } else {
            $result = get_catalog();
        }

        if ($result === false) {
            echo "Error in query execution.";
            exit;
        }

        if (pg_num_rows($result) == 0) echo 'No books found';

        foreach (pg_fetch_all($result) as $book):
            $title_link = '../catalog/book_page.php' . '?isbn=' . $book['isbn'];
            $author_link = '../catalog/author_page.php' . '?author=' . $book['author'];

            ?>

            <!-- Book Item -->
            <li class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center">
            <span class="book-title">
                <a class="link-opacity-100-hover hover-lighten" href=<?php echo $title_link; ?>>
                    <?php echo htmlspecialchars($book['title']); ?>
                </a>
            </span>
                <span class="mx-2">•</span>
                <span class="book-author">
                <a class="link-opacity-100-hover hover-lighten" href=<?php echo $author_link; ?>>
                    <?php echo htmlspecialchars($book['first_name'] . ' ' . $book['last_name']); ?>
                </a>
            </span>
                <span class="mx-2">•</span>
                <span class="book-isbn">
                <?php echo htmlspecialchars($book['isbn']); ?>
            </span>
            </li>

        <?php endforeach; ?>
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>



