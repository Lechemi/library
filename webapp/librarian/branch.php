<?php

include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

if (!empty($_GET['id'])) {
    $branch = $_GET['id'];

    try {
        $stats = get_branch_stats($branch);
    } catch (Exception $e) {
        echo 'An error occurred: ' . $e->getMessage();
    }

    print_r($stats);

} else {
    echo "Error, no branch.";
    exit;
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

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
