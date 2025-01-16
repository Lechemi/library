<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/branch-functions.php');
include_once('../lib/redirect.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
} else {
    redirect('../lib/error.php');
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch = $_POST['branch'];
    $quantity = $_POST['quantity'];
    try {
        $branchInfo = get_branch($branch)[0];
    } catch (Exception) {

    }
    $location = $branchInfo['city'] . ' - ' . $branchInfo['address'];

    if ($_POST['action'] == 'remove') {
        try {
            remove_copies($branch, $isbn, $quantity);
            $result = ['ok' => true, 'msg' => "Correctly removed $quantity copies from the branch in $location."];
        } catch (Exception $e) {
            $result = ['ok' => false, 'msg' => $e->getMessage()];
        }
    } elseif ($_POST['action'] == 'add') {
        try {
            add_copies($branch, $isbn, $quantity);
            $result = ['ok' => true, 'msg' => "Correctly added $quantity copies to the branch in $location."];
        } catch (Exception $e) {
            $result = ['ok' => false, 'msg' => $e->getMessage()];
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
    <style>
        .compact-info p {
            margin-bottom: 0.3rem;
        }
    </style>
</head>

<h5 class="mb-1">Manage copies for this book</h5>

<form class="row g-3 mt-2" method="POST" action="">
    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>">

    <!-- Form Row -->
    <div class="row align-items-end">

        <!-- Branch selection -->
        <div class="col-md-3">
            <label for="branch" class="form-label mb-1">Branch</label>
            <select name="branch" id="branch" class="form-select" aria-label="Default select example">
                <?php
                try {
                    $branches = get_branches();
                    foreach ($branches as $branch) {
                        $branchString = $branch['city'] . ' - ' . $branch['address'];
                        echo '<option value="' . $branch['id'] . '">' . $branchString . '</option>';
                    }
                } catch (Exception $e) {
                    redirect('../lib/error.php');
                }
                ?>
            </select>
        </div>

        <!-- Quantity of copies -->
        <div class="col-md-2">
            <label for="quantity" class="form-label mb-1">Number of copies</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="Enter quantity"
                   required>
        </div>

        <!-- Action (Add/Remove) -->
        <div class="col-md-2">
            <label class="form-label mb-1">Action</label>
            <div class="btn-group w-100" role="group" aria-label="Action toggle">
                <input type="radio" class="btn-check" id="add" name="action" value="add" required>
                <label class="btn btn-outline-primary" for="add">Add</label>

                <input type="radio" class="btn-check" id="remove" name="action" value="remove" required>
                <label class="btn btn-outline-primary" for="remove">Remove</label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-md-1 text-end">
            <button type="submit" name="submitButton" class="btn btn-primary w-100">Apply</button>
        </div>
    </div>

    <div class="form-text mt-1">Keep in mind you can only remove copies that are not being loaned.</div>
</form>

<?php if ($result): ?>
    <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
         role="alert">
        <?php echo htmlspecialchars($result['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>