<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account-functions.php');
include_once('../lib/catalog-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
$searchResults = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['searchInput'])) {
        try {
            $searchResults = get_authors($_POST['searchInput']);
        } catch (Exception $e) {
            redirect('../lib/error.php');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search authors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        .hover-lighten {
            color: #000;
            transition: color 0.2s ease-in-out;
            text-decoration: none;
        }

        .hover-lighten:hover {
            color: #555;
            text-decoration: none;
        }

        .book-title {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .book-author {
            font-size: 0.9rem;
            color: #555;
        }
    </style>
</head>

<body>
<!-- Navbar -->
<div class="container mt-3">
    <?php include '../librarian/navbar.php'; ?>
</div>

<div class="container my-4">

    <!-- Search bar -->
    <div class="container d-flex justify-content-center">
        <div class="w-50">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="searchInput" class="form-label d-block">
                        Search for an author or
                        <a href="add-author.php" class="text-primary">add a new one</a>
                    </label>
                    <input type="text" class="form-control" name="searchInput" id="searchInput"
                           placeholder="Enter an author's name or id" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <!-- Found authors -->
    <?php if ($searchResults !== null): ?>

        <ul class="list-group list-group-flush rounded-4">
            <?php

            if (empty($searchResults)) {
                echo 'No results found.';
            }

            foreach ($searchResults as $author):
                $author_link = '../catalog/author.php' . '?author=' . $author['id'];
                $full_name = $author['first_name'] . ' ' . $author['last_name'];
                ?>

                <!-- Book Item -->
                <li class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center">
            <span class="book-title">
                <a class="link-opacity-100-hover hover-lighten" href=<?php echo $author_link; ?>>
                    <?php echo htmlspecialchars($full_name); ?>
                </a>
            </span>
                    <span class="mx-2">•</span>
                    <span class="book-author">
                <?php echo $author['id']; ?>
            </span>
                </li>

            <?php endforeach; ?>
        </ul>

    <?php endif; ?>

</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</html>
