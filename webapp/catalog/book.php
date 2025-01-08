<?php

include_once('../lib/book-functions.php');
include_once('../lib/redirect.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $result = get_books($isbn);

    if (!$result) {
        echo "Error in query execution.";
        exit;
    }

    $bookDetails = group_authors($result)[$isbn];
    if ($_SESSION['user']['type'] == 'patron')
        $bookDetails['available_copies'] = get_available_copies($isbn, null);
} else {
    echo "Error, no book.";
    exit;
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
    <h1 class="mb-4"><?php echo htmlspecialchars($bookDetails['title']); ?></h1>


    <div class="card mb-5 pb-4">
        <div class="card-body">
            <h5 class="card-title">Book Details</h5>

            <?php if ($_SESSION['user']['type'] == 'librarian'): ?>
                <a href="../librarian/edit-book.php?isbn=<?php echo $isbn; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit book details
                </a>
            <?php endif; ?>

            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($isbn); ?></p>
            <p>
                <strong>Author:</strong>
                <?php
                foreach ($bookDetails['authors'] as $author) {
                    echo htmlspecialchars($author['name']);
                    if ($author !== end($bookDetails['authors'])) {
                        echo ', ';
                    }
                }
                ?>
            </p>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($bookDetails['publisher']); ?></p>
            <p><strong>Blurb:</strong> <?php echo htmlspecialchars($bookDetails['blurb']); ?></p>

            <?php

            if (isset($bookDetails['available_copies'])) {
                $copyCount = count($bookDetails['available_copies']);

                if ($copyCount == 0) {
                    echo '<p><strong>There are no available copies.</strong></p>';
                } else if ($copyCount == 1) {
                    echo '<p><strong>There\'s one available copy.</strong></p>';
                } else {
                    echo '<p><strong>There are ' . htmlspecialchars($copyCount) . ' available copies.</strong></p>';
                }

            }
            ?>

            <?php if ($_SESSION['user']['type'] == 'patron') include '../patron/loan-request-form.php' ?>

            <?php if ($_SESSION['user']['type'] == 'librarian') include '../librarian/manage-copies-form.php' ?>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
