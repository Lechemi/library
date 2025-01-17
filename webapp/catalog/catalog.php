<?php

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/catalog-functions.php');
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

        .book-isbn {
            font-size: 0.8rem;
            color: #888;
        }

    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
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

<!-- Search bar -->
<div class="container my-3">
    <form class="d-flex justify-content-center" method="GET" action="">
        <div class="input-group" style="max-width: 500px;">
            <input
                    class="form-control rounded-3 me-2"
                    type="search"
                    placeholder="Enter a title, an ISBN code, or an author"
                    aria-label="Search"
                    name="searchInput"
                    value="<?= htmlspecialchars($_GET['searchInput'] ?? '') ?>"
            >
            <button class="btn btn-primary rounded-3 me-1" type="submit"><i class="bi bi-search"></i></button>
            <a href="?" class="btn btn-secondary rounded-3">Clear</a>
        </div>
    </form>
</div>


<div class="container">

    <?php if ($_SESSION['user']['type'] == 'librarian'): ?>
        <a href="../librarian/add-book.php" class="btn btn-primary"> <i class="bi bi-plus-square"></i> Add a new
            book</a>
        <a href="../librarian/search-authors.php" class="btn btn-primary"> <i class="bi bi-person-vcard"></i> Search and
            add authors</a>
        <a href="../librarian/add-publisher.php" class="btn btn-primary"> <i class="bi bi-bank"></i> Add a new publisher</a>
    <?php endif; ?>

    <!-- Displayed book(s) -->
    <ul class="list-group list-group-flush rounded-4">
        <?php

        $searchInput = $_GET['searchInput'] ?? '';
        try {
            $result = get_books($searchInput);
        } catch (Exception $e) {
            redirect('../lib/error.php');
        }

        if (empty($result)) echo 'No books found';

        foreach ($result as $isbn => $details):
            $title_link = '../catalog/book.php' . '?isbn=' . $isbn;
            ?>

            <!-- Book Item -->
            <li class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center">
            <span class="book-title">
                <a class="link-opacity-100-hover hover-lighten" href=<?php echo $title_link; ?>>
                    <?php echo htmlspecialchars($details['title']); ?>
                </a>
            </span>
                <span class="mx-2">•</span>
                <span class="book-author">
                    <?php
                    foreach ($details['authors'] as $author) {
                        $author_link = '../catalog/author.php' . '?author=' . $author['id'];

                        echo '<a class="link-opacity-100-hover hover-lighten" href="' . $author_link . '">' . $author['name'] . '</a>';
                        if ($author !== end($details['authors'])) {
                            echo ', ';
                        }
                    }
                    ?>
            </span>
                <span class="mx-2">•</span>
                <span class="book-isbn">
                <?php echo htmlspecialchars($isbn); ?>
            </span>
            </li>

        <?php endforeach; ?>
    </ul>
</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>



