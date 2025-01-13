<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$alertMessage = "";
$alertType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submissions
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $type = $_POST['type'] ?? '';
    $taxCode = $_POST['taxCode'] ?? null; // Optional

    if ($type === 'librarian') {
        $email = $email . '@librarian.com';
    }

    try {
        add_user($email, $firstName, $lastName, $type, $taxCode);
        $alertMessage = "User added successfully!";
        $alertType = "success";
    } catch (Exception $e) {
        $alertMessage = $e->getMessage();
        $alertType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add user</title>
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

    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($alertMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Buttons -->
    <div class="mb-4">
        <a href="?form=librarian" class="btn btn-primary me-2">Add Librarian</a>
        <a href="?form=patron" class="btn btn-secondary">Add Patron</a>
    </div>

    <?php if (isset($_GET['form']) && $_GET['form'] === 'librarian'): ?>
        <!-- Add Librarian Form -->
        <form method="post" action="">
            <input type="hidden" name="type" value="librarian">

            <div class="input-group mb-3">
                <input type="text" name="email" id="email" class="form-control" placeholder="Librarian's name" required">
                <span class="input-group-text" id="basic-addon2">@librarian.com</span>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">First and last name</span>
                <input type="text" name="firstName" id="firstName" class="form-control" required>
                <input type="text" name="lastName" id="lastName" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Librarian</button>
        </form>

    <?php elseif (isset($_GET['form']) && $_GET['form'] === 'patron'): ?>
        <!-- Add Patron Form -->
        <form method="post" action="">
            <input type="hidden" name="type" value="patron">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">First and last name</span>
                <input type="text" name="firstName" id="firstName" class="form-control" required>
                <input type="text" name="lastName" id="lastName" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="taxCode" class="form-label">Tax Code</label>
                <input type="text" name="taxCode" id="taxCode" class="form-control">
            </div>

            <button type="submit" class="btn btn-secondary">Add Patron</button>
        </form>

    <?php else: ?>
        <p>Please select a form to add a user.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>