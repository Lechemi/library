<?php

include_once('../lib/book_functions.php');
include_once('../lib/redirect.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $result = get_books($isbn);

    if ($result === false) {
        echo "Error in query execution.";
        exit;
    }

    $bookDetails = group_authors($result)[$isbn];
    $bookDetails['available_copies'] = pg_fetch_all(get_available_copies($isbn));
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
<!-- Back Button -->
<button onclick="history.back()" class="btn btn-outline-secondary mb-4">
    &larr; Back
</button>


<div class="container mt-5">
    <h1 class="mb-4"><?php echo htmlspecialchars($bookDetails['title']); ?></h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Book Details</h5>
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
            <p><strong>Available
                    Copies:</strong>
                <?php
                $copyCount = count($bookDetails['available_copies']);
                echo ($copyCount > 0) ?
                    htmlspecialchars($copyCount)
                    : 'None';
                ?>
            </p>

            <form>
                <div class="mb-3">
                    <label for="input-datalist" class="form-label">Do you have a preferred
                        branch?</label>
                    <input type="text" class="form-control"
                           placeholder="Insert a location" list="list-branches" id="input-datalist">
                    <datalist id="list-branches">
                        <option selected>No preference</option>

                        <?php

                        $branches = pg_fetch_all(get_branches());
                        print_r($branches);
                        foreach ($branches as $branch) {
                            echo '<option>' . $branch['city'] . ' - ' . $branch['address'] . '</option>';
                        }

                        ?>
                    </datalist>
                    <div class="form-text">If no preference is specified, a copy can be
                        provided from any branch.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Request</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        ('#input-datalist').autocomplete()
    }, false);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
