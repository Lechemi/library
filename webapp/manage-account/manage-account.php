<?php

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$user = $_SESSION['user'];
$userType = $user['type'];
$categoryString = '';
$taxCode = '';
$nDelays = '';
if ($userType === 'patron') {
    try {
        $patronInfo = get_patron($user['id'])[0];
        $categoryString = $patronInfo['category'] === 'premium' ? 'Premium patron' : 'Patron';
        $taxCode = htmlspecialchars($patronInfo['tax_code']);
        $nDelays = htmlspecialchars($patronInfo['n_delays']);
    } catch (Exception $e) {
        redirect('../lib/error.php');
    }
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $result = ['ok' => false, 'msg' => 'Failed to confirm new password.'];
    } else {
        try {
            change_password($user['id'], $currentPassword, $newPassword);
            $result = ['ok' => true, 'msg' => 'Password changed successfully.'];
        } catch (Exception $e) {
            $result = ['ok' => false, 'msg' => "Failed to change password. " . $e->getMessage()];
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

    <style>
        .custom-card {
            background-color: #f8f9fa;
            border: none;
            border-radius: 0.75rem;
            padding: 1rem;
            position: relative;
        }

        .compact-info p {
            margin-bottom: 0.3rem;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php
    if ($_SESSION['user']['type'] == 'patron') {
        include '../patron/navbar.php';
    } else {
        include '../librarian/navbar.php';
    }
    ?>
</div>

<div class="container my-4">

    <div class="custom-card">
        <div class="card-body compact-info">

            <h2 class="card-title">
                <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong></h2>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>

            <?php if ($userType === 'patron'): ?>
                <p><strong><i class="bi bi-person-bounding-box"></i> <?= $categoryString; ?></strong></p>
                <p><strong>
                        <i class="bi bi-card-text"></i>
                        Tax Code:</strong> <?= $taxCode; ?></p>
                <p class="mb-0"><strong><i class="bi bi-hourglass-bottom"></i> Number of late returns:</strong> <?= $nDelays; ?></p>
                <p class="form-text mt-0">
                    A loan can be granted only if the requesting patron has fewer than 5 late returns on record.
                </p>
            <?php else: ?>
                <p><strong><i class="bi bi-person-bounding-box"></i> Librarian</strong></p>
            <?php endif; ?>

        </div>

        <div class="card-footer mt-3">
            <!-- Logout button -->
            <a class="btn btn-primary" href="../lib/logout.php">
                Logout
            </a>

            <!-- Change password button -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                Change Password
            </button>
        </div>

    </div>

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

    <?php if ($result): ?>
        <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
             role="alert">
            <?php echo htmlspecialchars($result['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>




