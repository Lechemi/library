<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/book_functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$result = get_active_loans($_SESSION['user']['id']);

if ($result === false) {
    echo "Error in query execution.";
    exit;
}

$activeLoans = pg_fetch_all($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">
    <?php foreach ($activeLoans as $loan): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($loan['title']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">ISBN: <?php echo htmlspecialchars($loan['isbn']); ?></h6>

                <p class="card-text">
                    <strong>Branch:</strong> <?php echo $loan['address'] . ' - ' . $loan['city']; ?><br>
                    <strong>Loan started:</strong> <?php
                    try {
                        $start = new DateTime($loan['start']);
                        echo $start->format('Y-m-d H:i:s');
                    } catch (DateMalformedStringException $e) {
                        echo 'Some error occurred';
                    }
                    ?><br>
                    <strong>You have to return this book by:</strong> <?php
                    try {
                        $due = new DateTime($loan['due']);
                        echo $due->format('Y-m-d H:i:s');
                    } catch (DateMalformedStringException $e) {
                        echo 'Some error occurred';
                    }
                    ?><br>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>