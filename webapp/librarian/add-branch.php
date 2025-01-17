<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['branch_name'] ?? '';
    $city = $_POST['branch_city'] ?? '';
    $address = $_POST['branch_address'] ?? '';

    try {
        add_branch($city, $address, $name);
        $result = ['ok' => true, 'msg' => 'Branch added successfully.'];
    } catch (Exception $e) {
        $result = ['ok' => false, 'msg' => $e->getMessage()];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert branch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .custom-card {
            background-color: #f8f9fa;
            border: none;
            border-radius: 0.75rem;
            padding: 1rem;
            position: relative;
        }
    </style>
</head>

<body>
<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">

    <div class="custom-card">
        <h2 class="book-title"><strong>Add a new branch</strong></h2>

        <form method="post">
            <div class="row">
                <!-- Branch Name -->
                <div class="col-md-4 mb-3">
                    <label for="branchName" class="form-label">Branch Name</label>
                    <input type="text" class="form-control" id="branchName" name="branch_name"
                           placeholder="Enter branch name" required>
                </div>
                <!-- City -->
                <div class="col-md-4 mb-3">
                    <label for="branchCity" class="form-label">City</label>
                    <input type="text" class="form-control" id="branchCity" name="branch_city" placeholder="Branch city" required>
                </div>
                <!-- Address -->
                <div class="col-md-4 mb-3">
                    <label for="branchAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" id="branchAddress" name="branch_address"
                           placeholder="Branch address" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-primary" href="../librarian/manage-branches.php">Cancel</a>
        </form>

        <?php if ($result): ?>
            <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
                 role="alert">
                <?php echo htmlspecialchars($result['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>