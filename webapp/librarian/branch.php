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
        redirect('../lib/error.php');
    }

} else {
    redirect('../lib/error.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeBranch'])) {
    try {
        remove_branch($branch);
        redirect("manage-branches.php");
    } catch (Exception $e) {
        $removal_error = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch page</title>
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
            <h2>Branch details</h2>
        </div>
        <div class="card-body">
            <p><strong>Address:</strong> <?= $stats[0]['address'] ?>, <?= $stats[0]['city'] ?></p>
            <p><strong>Number of currently loaned copies:</strong> <?= $stats[0]['active_loans'] ?></p>
            <p>This branch manages <?= $stats[0]['n_copies'] ?> copies, for a total of <?= $stats[0]['n_books'] ?>
                different books.</p>
        </div>
    </div>

    <!-- Remove branch button -->
    <?php if ($stats[0]['n_books'] == 0): ?>
        <div class="mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#removeBranchModal">Remove branch
            </button>
        </div>
    <?php endif; ?>

    <!-- Display removal-related exceptions -->
    <?php if (isset($removal_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($removal_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Overdue Loans -->
    <div class="card">
        <div class="card-header">
            <h2>Overdue loans</h2>
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
                                redirect('../lib/error.php');
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

<!-- Modal for removing branch confirmation -->
<div class="modal fade" id="removeBranchModal" tabindex="-1" aria-labelledby="removeBranchModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeBranchModalLabel">Confirm remove branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove this branch? This action cannot be undone.
                <!-- todo make this next sentence more visible -->
                If the operation is successful, you will be taken back to the 'Branches' page.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="removeBranch" value="<?= $branch ?? '' ?>">
                    <button type="submit" class="btn btn-danger">Confirm removal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>
</html>
