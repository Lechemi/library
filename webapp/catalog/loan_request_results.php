<?php

include_once('../lib/book_functions.php');
include_once('../lib/redirect.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$result = null;
const noPreferenceStr = 'No preference';
$branches = pg_fetch_all(get_branches());

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
    <title>Loan Request Result</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
                    print('BOOK: ' . $_POST['isbn']);
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

            <a class="btn btn-secondary mt-3" href=<?php echo 'book_page.php?isbn=' . trim($_POST['isbn']); ?>>Back to
                Book Page</a>
            <a href="../patron/patron_catalog.php" class="btn btn-secondary mt-3">Back to catalog</a>
        </div>
    </div>
</div>

<!-- Optional: Bootstrap JS for styling if needed -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
