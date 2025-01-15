<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/branch-functions.php');
include_once('../lib/redirect.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
} else {
    redirect('../lib/error.php');
}

try {
    $branches = branches_with_book($isbn);
} catch (Exception $e) {
    redirect('../lib/error.php');
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['isbn'])) {
    $isbn = trim($_POST['isbn']);
    $preferredBranch = ($_POST['branch'] !== 'noPreference' and $_POST['branch'])
        ? array($_POST['branch']) : null;

    try {
        $loaned = make_loan($isbn, $_SESSION['user']['id'], $preferredBranch);
        $copy = $loaned['copy'];
        $branch = $loaned['branch'];
        $branchInfo = get_branch($branch)[0];
        $location = $branchInfo['city'] . ' - ' . $branchInfo['address'];
        $result = ['ok' => true, 'msg' => "Successfully loaned copy with id $copy from branch in $location."];
    } catch (Exception $e) {
        $result = ['ok' => false, 'msg' => "Loan was denied. " . $e->getMessage()];
    }
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


<h5 class="mb-1">Request a loan</h5>

<form method="POST" action="" class="row g-3 mt-1">
    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>">

    <!-- Form Row -->
    <div class="row align-items-end">

        <!-- Branch Selection -->
        <div class="col-md-4">
            <label for="branch" class="form-label mb-1">Do you have a preferred branch?</label>
            <select name="branch" id="branch" class="form-select" aria-label="Default select example">
                <option selected value="noPreference">No preference</option>
                <?php
                foreach ($branches as $branch) {
                    $branchString = $branch['city'] . ' - ' . $branch['address'];
                    echo '<option value="' . $branch['id'] . '">' . $branchString . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="col-md-2 text-end">
            <button type="submit" name="submitButton" class="btn btn-primary w-100">Request</button>
        </div>
    </div>

    <div class="form-text mt-1">If no preference is specified, a copy can be provided from any branch. Only available branches are shown.</div>

    <!-- Alert Message -->
    <?php if ($result): ?>
        <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
             role="alert">
            <?php echo htmlspecialchars($result['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>