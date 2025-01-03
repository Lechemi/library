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

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch = $_POST['branch'];
    $quantity = $_POST['quantity'];

    if ($_POST['action'] == 'remove') {
        try {
            remove_copies($branch, $isbn, $quantity);
            $feedback = 'Correctly removed ' . $quantity . ' copies.';
        } catch (Exception $e) {
            $feedback = $e->getMessage();
        }
    } elseif ($_POST['action'] == 'add') {
        try {
            add_copies($branch, $isbn, $quantity);
            $feedback = 'Correctly added ' . $quantity . ' copies.';
        } catch (Exception $e) {
            $feedback = $e->getMessage();
        }
    }
}

?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage copies from</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<h4>Manage copies for this book</h4>

<!-- todo display feedback in a more decent manner -->
<p> <?php echo $feedback ?> </p>

<form method="POST" action="">
    <div class="mb-3">
        <input type="hidden" name="isbn" value=" <?php echo htmlspecialchars($isbn); ?> ">

        <!-- Branch selection -->
        <div class="mb-3">
            <label for="branch" class="form-label">Select the desired branch</label>
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

        <!-- Quantity of copies -->
        <div class="mb-3">
            <label for="quantity" class="form-label">Enter the number of copies</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="Enter quantity"
                   required>
        </div>

        <!-- Action (Add/Remove) -->
        <div class="mb-3">
            <div class="btn-group" role="group" aria-label="Action toggle">
                <input type="radio" class="btn-check" id="add" name="action" value="add" required>
                <label class="btn btn-outline-primary" for="add">Add</label>

                <input type="radio" class="btn-check" id="remove" name="action" value="remove" required>
                <label class="btn btn-outline-danger" for="remove">Remove</label>
            </div>
        </div>

        <div class="form-text">You can only remove copies that are not being loaned.</div>
    </div>
    <button type="submit" name="submitButton" class="btn btn-primary">Apply</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>