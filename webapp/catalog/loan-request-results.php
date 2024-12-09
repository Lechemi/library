<?php

include_once('../lib/book-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$result = null;
const noPreferenceStr = 'No preference';
try {
    $branches = get_branches();
} catch (Exception $e) {

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['isbn'])) {

    $isbn = trim($_POST['isbn']);

    if ($_POST['branch-city'] != noPreferenceStr) {
        if (!empty($_POST['branch-address'])) {
            $preferredBranch = array($_POST['branch-address']);
            $result = make_loan($isbn, $_SESSION['user']['id'], $preferredBranch);
        } else {
            $preferredBranches = [];

            foreach ($branches as $branch) {
                if ($branch['city'] === $_POST['branch-city']) {
                    $preferredBranches[] = $branch['id'];
                }
            }

            $result = make_loan($isbn, $_SESSION['user']['id'], $preferredBranches);
        }
    } else {
        $result = make_loan($isbn, $_SESSION['user']['id'], null);
    }
} else {
    echo 'Some error occurred';
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan request results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Loan Request Result</h5>

            <?php if ($result['ok']): ?>
                <div class="alert alert-success mt-4" role="alert">
                    <?php
                    print_r($result);
                    ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger mt-4" role="alert">
                    Something went wrong with your loan request.
                    <?php
                    print($result['error']);
                    ?>
                </div>
            <?php endif; ?>

            <a class="btn btn-secondary mt-3" href=<?php echo 'book.php?isbn=' . trim($_POST['isbn']); ?>>Back to
                Book Page</a>
            <a href="catalog.php" class="btn btn-secondary mt-3">Back to catalog</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
