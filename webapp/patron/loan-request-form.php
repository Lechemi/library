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

try {
    $branches = get_branches();
} catch (Exception $e) {

}

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['isbn'])) {
    $isbn = trim($_POST['isbn']);
    if ($_POST['branch'] !== 'noPreference' and $_POST['branch']) {
        $preferredBranch = array($_POST['branch']);
        $result = make_loan($isbn, $_SESSION['user']['id'], $preferredBranch);
    } else {
        $result = make_loan($isbn, $_SESSION['user']['id'], null);
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

<form method="POST" action="">
    <div class="mb-3">
        <input type="hidden" name="isbn" value=" <?php echo htmlspecialchars($isbn); ?> ">

        <!-- Branch selection -->
        <div>
            <label for="branch" class="form-label">Do you have a preferred branch?</label>
            <select name="branch" id="branch" class="form-select"
                    aria-label="Default select example">
                <option selected value="noPreference">No preference</option>
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

    <?php if ($result): ?>
        <?php if ($result['ok']): ?>
            <div class="alert alert-success mt-4 alert-dismissible fade show" role="alert">
                <?php print_r($result); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-4  alert-dismissible fade show" role="alert">
                Something went wrong with your loan request.
                <?php print($result['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>