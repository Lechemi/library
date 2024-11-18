<?php

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account_functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$result = get_patron($_SESSION['user']['id']);

if (!$result) {
    echo "Some error occurred...";
    exit;
}

$patron = pg_fetch_all($result)[0];
$user = $_SESSION['user'];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $message = "Failed to confirm new password.";
        $messageType = "danger";
    } else {
        try {
            change_password($user['id'], $currentPassword, $newPassword);
            $message = "Password changed successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = "danger";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="container py-4">

<a href="../patron/patron_catalog.php">Back to catalog</a>

<div class="card mb-4">
    <div class="card-body">
        <!-- Main User Info -->
        <h2 class="card-title mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
        <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>

        <!-- Other Information -->
        <p><strong>Tax Code:</strong> <?= htmlspecialchars($patron['tax_code']) ?></p>
        <p><strong>Number of Delays:</strong> <?= htmlspecialchars($patron['n_delays']) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($patron['category']) ?></p>
    </div>
</div>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
    Change Password
</button>

<!-- Change Password Modal -->
<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Password change form -->
                <form id="changePasswordForm" method="post">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password"
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Placeholder for bottom alert -->
<div class="mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?> mt-5 alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>


</body>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>




