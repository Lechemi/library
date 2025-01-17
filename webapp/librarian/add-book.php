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
    <title>Add book</title>
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
        <h2><strong>Add a new book to the catalog</strong></h2>

        <?php if ($result): ?>
            <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
                 role="alert">
                <?php echo htmlspecialchars($result['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="container">
            <!-- Row for ISBN and Title -->
            <div class="row mb-3">
                <!-- ISBN -->
                <div class="col-md-6">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input required type="text" name="isbn" class="form-control" id="isbn" aria-describedby="isbnHelp">
                    <div id="isbnHelp" class="form-text">
                        Be careful with this field, it cannot be modified once the book is inserted.
                    </div>
                </div>
                <!-- Title -->
                <div class="col-md-6">
                    <label for="title" class="form-label">Title</label>
                    <input required type="text" name="title" class="form-control" id="title">
                </div>
            </div>

            <!-- Row for Publisher and Author(s) -->
            <div class="row">
                <!-- Publisher -->
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label for="authors" class="form-label">Author(s)</label>
                    <input required type="text" name="authors" class="form-control" id="authors"
                           aria-describedby="authorsHelp">
                    <div id="authorsHelp" class="form-text">
                        Insert author ID's separated by commas (e.g., 123, 456, 789). Spaces are ignored.
                    </div>
                </div>
            </div>

            <!-- Blurb -->
            <div class="mb-3">
                <label for="blurb" class="form-label">Blurb</label>
                <textarea required id="blurb" name="blurb" class="form-control" rows="4"
                          placeholder="Write a brief description of the book..."></textarea>
            </div>

            <!-- Submit and Cancel Buttons -->
            <div class="d-flex justify-content-start">
                <button type="submit" class="btn btn-primary me-2">Submit</button>
                <a href="../catalog/catalog.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>