<?php

include_once('../lib/book-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
// todo check that the user is a librarian

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $result = get_books($isbn);

    if (!$result) {
        echo "Error in query execution.";
        exit;
    }

    $bookDetails = group_authors($result)[$isbn];
    $authorString = '';
    $authorList = [];
    foreach ($bookDetails['authors'] as $author) {
        $authorString .= $author['id'] . ', ';
        $authorList[] = $author['id'];
    }

    $authorString = substr($authorString, 0, -2);

    $publishers = get_publishers();
} else {
    echo "Error, no book.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = $_POST['title'] != $bookDetails['title'] ? $_POST['title'] : null;
    $publisher = $_POST['publisher'] != $bookDetails['publisher'] ? $_POST['publisher'] : null;
    $blurb = $_POST['blurb'] != $bookDetails['blurb'] ? $_POST['blurb'] : null;

    if ($title != null or $publisher != null or $blurb != null) {
        try {
            update_book($isbn, $title, $blurb, $publisher);
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    $authors = array_map('trim', explode(',', $_POST['authors']));
    $authors = array_filter($authors, 'is_numeric');

    if (array_diff($authors, $authorList) || array_diff($authorList, $authors)) {
        try {
            update_authors($isbn, $authors);
        } catch (Exception $e) {
            print $e->getMessage();
        }
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
    <h5 class="mb-4"> Editing book <?php echo $isbn ?></h5>

    <form method="POST" action="">

        <!-- Title -->
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control" id="title" value="<?php echo $bookDetails['title'] ?>">
        </div>

        <!-- Publisher -->
        <div class="mb-3">
            <label for="publisher" class="form-label">Publisher</label>
            <select id="publisher" name="publisher" class="form-select">
                <option selected> <?php echo $bookDetails['publisher'] ?> </option>

                <?php

                foreach ($publishers as $publisher) {

                    $pubName = $publisher['name'];
                    if ($pubName != $bookDetails['publisher']) {
                        echo '<option value="' . $pubName . '">' . $pubName . '</option>';
                    }
                }

                ?>

            </select>
        </div>

        <!-- Author(s) -->
        <div class="mb-3">
            <label for="authors" class="form-label">Author(s)</label>
            <input type="text" name="authors" class="form-control" id="authors" value="<?php echo $authorString ?>"
                   aria-describedby="authorsHelp">
            <div id="authorsHelp" class="form-text">
                Insert author id's separated by commas (e.g. 123, 456, 789). Spaces are ignored.
            </div>
        </div>

        <!-- Blurb -->
        <div class="mb-3">
            <label for="blurb" class="form-label">Blurb</label>
            <textarea id="blurb" name="blurb" class="form-control"><?php echo $bookDetails['blurb'] ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>