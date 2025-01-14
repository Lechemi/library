<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];

    try {
        $bookDetails = get_books($isbn)[$isbn];
    } catch (Exception $e) {
        redirect('../lib/error.php');
    }

    if ($_SESSION['user']['type'] == 'patron')
        $bookDetails['available_copies'] = get_available_copies($isbn, null);
} else {
    redirect('../lib/error.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .compact-info p {
            margin-bottom: 0.3rem; /* Reduced spacing */
        }

        .custom-card {
            background-color: #f8f9fa; /* Very light grey background */
            border: none; /* No border */
            border-radius: 0.75rem; /* Rounded corners */
            padding: 1.5rem; /* Padding for content */
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
        <h2 class="mb-2"><strong><?php echo htmlspecialchars($bookDetails['title']); ?></strong></h2>

        <div class="card-body compact-info">
            <?php if ($_SESSION['user']['type'] == 'librarian'): ?>
                <a href="../librarian/edit-book.php?isbn=<?php echo $isbn; ?>" class="btn btn-primary mb-3">
                    <i class="bi bi-pencil"></i> Edit book details
                </a>
            <?php endif; ?>

            <p class="text-muted"><?php echo htmlspecialchars($isbn); ?></p>

            <p><?php echo htmlspecialchars($bookDetails['blurb']); ?></p>

            <p><strong><i class="bi bi-vector-pen"></i> Author:</strong>
                <?php
                foreach ($bookDetails['authors'] as $author) {
                    echo htmlspecialchars($author['name']);
                    if ($author !== end($bookDetails['authors'])) {
                        echo ', ';
                    }
                }
                ?>
            </p>
            <p><strong><i class="bi bi-bank"></i>
                    Publisher:</strong> <?php echo htmlspecialchars($bookDetails['publisher']); ?></p>
        </div>

        <?php
        if (isset($bookDetails['available_copies'])) {
            $copyCount = count($bookDetails['available_copies']);

            if ($copyCount == 0) {
                echo '<p><strong>There are no available copies.</strong></p>';
            } else if ($copyCount == 1) {
                echo '<p><strong>There\'s one available copy.</strong></p>';
                if ($_SESSION['user']['type'] == 'patron')
                    include '../patron/loan-request-form.php';
            } else {
                echo '<p><strong>There are ' . htmlspecialchars($copyCount) . ' available copies.</strong></p>';
                if ($_SESSION['user']['type'] == 'patron')
                    include '../patron/loan-request-form.php';
            }
        }

        if ($_SESSION['user']['type'] == 'librarian') include '../librarian/manage-copies-form.php';
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
