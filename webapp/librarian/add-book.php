<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $authors = array_map('trim', explode(',', $_POST['authors']));
    $authors = array_filter($authors, 'is_numeric');

    try {
        add_book($_POST['isbn'], $_POST['title'], $_POST['blurb'], $_POST['publisher'], $authors);
        $result = ['ok' => true, 'msg' => 'Book successfully added to the catalog.'];
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
</head>
<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php
    include '../librarian/navbar.php';
    ?>
</div>

<div class="container my-4">

    <?php if ($result): ?>
        <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
             role="alert">
            <?php echo htmlspecialchars($result['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h5 class="mb-4">Add a new book to the catalog</h5>

    <form method="POST" action="">

        <!-- ISBN -->
        <div class="mb-3">
            <label for="isbn" class="form-label">ISBN</label>
            <input required type="text" name="isbn" class="form-control" id="isbn" aria-describedby="isbnHelp">
            <div id="isbnHelp" class="form-text">
                Be careful with this field, it cannot be modified once the book is inserted.
            </div>
        </div>

        <!-- Title -->
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input required type="text" name="title" class="form-control" id="title">
        </div>

        <!-- Publisher -->
        <div class="mb-3">
            <label for="publisher" class="form-label">Publisher</label>
            <select id="publisher" name="publisher" class="form-select">

                <?php

                $publishers = get_publishers();

                foreach ($publishers as $publisher) {
                    $pubName = $publisher['name'];
                    echo '<option value="' . $pubName . '">' . $pubName . '</option>';
                }

                ?>

            </select>
        </div>

        <!-- Author(s) -->
        <div class="mb-3">
            <label for="authors" class="form-label">Author(s)</label>
            <input required type="text" name="authors" class="form-control" id="authors"
                   aria-describedby="authorsHelp">
            <div id="authorsHelp" class="form-text">
                Insert author id's separated by commas (e.g. 123, 456, 789). Spaces are ignored.
            </div>
        </div>

        <!-- Blurb -->
        <div class="mb-3">
            <label for="blurb" class="form-label">Blurb</label>
            <textarea required id="blurb" name="blurb" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>