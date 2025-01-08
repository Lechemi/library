<?php

include_once('../lib/book-functions.php');
include_once('../lib/branch-functions.php');
include_once('../lib/redirect.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
} else {
    echo "Error, no book.";
    exit;
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<form method="POST" action="loan-request-results.php">
    <div class="mb-3">
        <input type="hidden" name="isbn" value=" <?php echo htmlspecialchars($isbn); ?> ">

        <!-- Branch selection -->
        <div>
            <label for="branch" class="form-label">Do you have a preferred branch?</label>
            <select name="branch" id="branch" class="form-select"
                    aria-label="Default select example">
                <?php

                try {
                    $branches = get_branches();
                } catch (Exception $e) {
                    echo 'Error fetching branches: ' . $e->getMessage();
                }

                foreach ($branches as $branch) {
                    $branchString = $branch['city'] . ' - ' . $branch['address'];
                    echo '<option value="' . $branch['id'] . '">' . $branchString . '</option>';
                }

                ?>
            </select>
        </div>

        <div class="form-text">If no preference is specified, a copy can be
            provided from any branch.
        </div>
    </div>
    <button type="submit" name="submitButton" class="btn btn-primary">Request</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>