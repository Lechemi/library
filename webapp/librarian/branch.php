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

<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">
    <h1 class="text-center">Branch "<?= $stats[0]['name'] ?>"</h1>

    <!-- Branch Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h2>Branch Details</h2>
        </div>
        <div class="card-body">
            <p><strong>Address:</strong> <?= $stats[0]['address'] ?>, <?= $stats[0]['city'] ?></p>
            <p><strong>Number of currently loaned copies:</strong> <?= $stats[0]['active_loans'] ?></p>
            <p>This branch manages <?= $stats[0]['n_copies'] ?> copies, for a total of <?= $stats[0]['n_books'] ?> different books.</p>
        </div>
    </div>

    <!-- Overdue Loans -->
    <div class="card">
        <div class="card-header">
            <h2>Overdue Loans</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($stats['delays'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Copy</th>
                            <th>Patron</th>
                            <th>Email</th>
                            <th>Was due</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($stats['delays'] as $delay):
                            try {
                                $due = new DateTime($delay['due']);
                            } catch (DateMalformedStringException $e) {
                                echo 'Some error occurred';
                            }
                            $due = $due->format('Y-m-d H:i:s');
                            ?>

                            <tr>
                                <td><?= $delay['book'] ?></td>
                                <td><?= $delay['title'] ?></td>
                                <td><?= $delay['copy'] ?></td>
                                <td><?= $delay['first_name'] . ' ' . $delay['last_name'] ?></td>
                                <td><?= $delay['email'] ?></td>
                                <td><?= $due ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No overdue loans.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</html>
