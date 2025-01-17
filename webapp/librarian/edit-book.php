<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];

    try {
        $result = get_books($isbn);
    } catch (Exception $e) {
        redirect('../lib/error.php');
    }

    $bookDetails = $result[$isbn];
    $authorString = '';
    $authorList = [];
    foreach ($bookDetails['authors'] as $author) {
        $authorString .= $author['id'] . ', ';
        $authorList[] = $author['id'];
    }

    $authorString = substr($authorString, 0, -2);
    $publishers = get_publishers();
} else {
    redirect('../lib/error.php');
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = $_POST['title'] != $bookDetails['title'] ? $_POST['title'] : null;
    $publisher = $_POST['publisher'] != $bookDetails['publisher'] ? $_POST['publisher'] : null;
    $blurb = $_POST['blurb'] != $bookDetails['blurb'] ? $_POST['blurb'] : null;

    $authors = array_map('trim', explode(',', $_POST['authors']));
    $authors = array_filter($authors, 'is_numeric');

    try {
        if (array_diff($authors, $authorList) or array_diff($authorList, $authors))
            update_authors($isbn, $authors);

        if ($title or $publisher or $blurb)
            update_book($isbn, $title, $blurb, $publisher);

        $result = ['ok' => true, 'msg' => 'Book\'s info correctly updated. Refresh this page or go back to the catalog to see it.'];
    } catch (Exception $e) {
        $result = ['ok' => false, 'msg' => $e->getMessage()];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .custom-card {
            background-color: #f8f9fa;
            border: none;
            border-radius: 0.75rem;
            padding: 1rem;
            position: relative;
        }
    </style>

</head>
<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php
    include '../librarian/navbar.php';
    ?>
</div>

<div class="container my-4">
    <div class="custom-card">
        <h2><strong>Edit book <?php echo htmlspecialchars($isbn); ?></strong></h2>

        <?php if ($result): ?>
            <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
                 role="alert">
                <?php echo htmlspecialchars($result['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="container">
            <!-- Row for Title and Publisher -->
            <div class="row mb-3">
                <!-- Title -->
                <div class="col-md-6">
                    <label for="title" class="form-label">Title</label>
                    <input required type="text" name="title" class="form-control" id="title"
                           value="<?php echo htmlspecialchars($bookDetails['title']); ?>">
                </div>
                <!-- Publisher -->
                <div class="col-md-6">
                    <label for="publisher" class="form-label">Publisher</label>
                    <select id="publisher" name="publisher" class="form-select">
                        <option selected> <?php echo htmlspecialchars($bookDetails['publisher']); ?> </option>
                        <?php
                        foreach ($publishers as $publisher) {
                            $pubName = $publisher['name'];
                            if ($pubName != $bookDetails['publisher']) {
                                echo '<option value="' . htmlspecialchars($pubName) . '">' . htmlspecialchars($pubName) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Author(s) -->
            <div class="mb-3">
                <label for="authors" class="form-label">Author(s)</label>
                <input required type="text" name="authors" class="form-control" id="authors"
                       value="<?php echo htmlspecialchars($authorString); ?>"
                       aria-describedby="authorsHelp">
                <div id="authorsHelp" class="form-text">
                    Insert author ID's separated by commas (e.g., 123, 456, 789). Spaces are ignored.
                </div>
            </div>

            <!-- Blurb -->
            <div class="mb-3">
                <label for="blurb" class="form-label">Blurb</label>
                <textarea required id="blurb" name="blurb" class="form-control" rows="4"
                          placeholder="Write a brief description of the book..."><?php echo htmlspecialchars($bookDetails['blurb']); ?></textarea>
            </div>

            <!-- Submit and Cancel Buttons -->
            <div class="d-flex justify-content-start">
                <button type="submit" class="btn btn-primary me-2">Submit</button>
                <a href="../catalog/book.php?isbn=<?= $isbn ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>