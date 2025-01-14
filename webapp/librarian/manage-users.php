<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account-functions.php');
include_once('../lib/catalog-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['userEmail'])) {
        $_SESSION['userEmail'] = $_POST['userEmail'];
    }

    try {
        if (isset($_POST['removeUser'])) {
            remove_user($_POST['removeUser']);
            $result = ['ok' => true, 'msg' => 'User has been removed successfully.'];
        }

        if (isset($_POST['restoreUser'])) {
            restore_user($_POST['restoreUser']);
            $result = ['ok' => true, 'msg' => 'User has been restored successfully.'];
        }

        if (isset($_POST['resetDelays'])) {
            reset_delays($_POST['resetDelays']);
            $result = ['ok' => true, 'msg' => 'Reset delays successfully.'];
        }

        if (isset($_POST['returnCopy'])) {
            return_copy($_POST['returnCopy']);
            $result = ['ok' => true, 'msg' => 'Returned copy successfully.'];
        }

        if (isset($_POST['selectedCategory'])) {
            change_patron_category($_POST['changingPatron'], $_POST['selectedCategory']);
            $result = ['ok' => true, 'msg' => 'Patron\'s category was changed successfully.'];
        }

        if (isset($_POST['postponeDue'])) {
            $loanId = $_POST['postponeDue'];
            $days = $_POST['postponeDays'];
            postpone_due($loanId, $days);
            $result = ['ok' => true, 'msg' => 'Due date was postponed successfully.'];
        }

    } catch (Exception $e) {
        $result = ['ok' => false, 'msg' => $e->getMessage()];
    }

    $alertClass = $result['ok'] ? 'alert-success' : 'alert-danger';
}

$email = $_SESSION['userEmail'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .list-group-item {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            margin-bottom: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
        }

        .loan-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.4rem;
        }

        .loan-card-header button {
            margin-left: auto;
        }

        .scrollable-loans {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 0.5rem;
        }

        .card-body p {
            margin-bottom: 0.3rem;
        }

    </style>
</head>
<body>
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">

    <div class="container d-flex justify-content-center">
        <div class="w-50">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="userEmail" class="form-label d-block">
                        Search for a user or
                        <a href="add-user.php?form=patron" class="text-primary">add a new one</a>
                    </label>
                    <input type="email" class="form-control" name="userEmail" id="userEmail"
                           placeholder="Enter user's email" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <?php if ($result): ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($result['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    if ($email) {
        $userInfo = null;
        try {
            $userInfo = get_user_with_email($email);
        } catch (Exception) {
        }

        if ($userInfo) {

            echo '<div class="card mt-4">';
            if ($userInfo['removed'] == 'f') {

                echo '<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">';
                echo htmlspecialchars($userInfo['email']);
                if ($_SESSION['user']['id'] != $userInfo['id'])
                    echo '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#removeUserModal">Remove User</button>';
                echo '</div>';
                echo '<div class="card-body">';

                echo '<p><strong>Name:</strong> ' . htmlspecialchars($userInfo['first_name']) . ' ' . htmlspecialchars($userInfo['last_name']) . '</p>';
                echo '<p><strong>Type:</strong> ' . htmlspecialchars($userInfo['type']) . '</p>';

                if (isset($userInfo['patronInfo'])) {

                    try {
                        $loans = get_loans($userInfo['id']);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }

                    echo '<p><strong>Tax Code:</strong> ' . htmlspecialchars($userInfo['patronInfo']['tax_code']) . '</p>';

                    echo '<div class="d-flex justify-content-between align-items-center">';
                    echo '<p><strong>Number of Delays:</strong> ' . htmlspecialchars($userInfo['patronInfo']['n_delays']) . '</p>';
                    if ($userInfo['patronInfo']['n_delays'] > 0) {
                        echo '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetDelaysModal">Reset Delays</button>';
                    }
                    echo '</div>';

                    echo '<div class="d-flex justify-content-between align-items-center">';
                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($userInfo['patronInfo']['category']) . '</p>';
                    echo '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#changeCategoryModal">Change Category</button>';
                    echo '</div>';

                    if (!empty($loans)) {
                        echo '<h5 class="mt-3">Loans</h5>';
                        echo '<div class="list-group scrollable-loans">';
                        foreach ($loans as $loan) {
                            try {
                                $start = new DateTime($loan['start']);
                                $due = new DateTime($loan['due']);
                                $start = $start->format('Y-m-d H:i:s');
                                $due = $due->format('Y-m-d H:i:s');
                                $returned = null;
                                if ($loan['returned'] != null) {
                                    $returned = new DateTime($loan['returned']);
                                    $returned = $returned->format('Y-m-d H:i:s');
                                }
                            } catch (Exception $e) {
                                echo 'Some error occurred with the dates.';
                            }

                            $branch = $loan['address'] . ' - ' . $loan['city'];
                            $isbn = $loan['isbn'];
                            $titleWithIsbn = "{$loan['title']} <span class='isbn'>{$isbn}</span>";

                            echo '<div class="list-group-item">';
                            echo '<div class="loan-card-header">';
                            echo "<h4>{$titleWithIsbn}</h4>";

                            if (!$returned) {
                                echo '<button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#returnCopyModal" data-loan-id="' . htmlspecialchars($loan['id']) . '"><i class="bi bi-box-arrow-in-down-left"></i> Return copy</button>';
                            }

                            if (!$returned) {
                                echo '<button class="btn btn-warning btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#postponeDueModal" data-loan-id="' . htmlspecialchars($loan['id']) . '"><i class="bi bi-clock"></i> Postpone due</button>';
                            }

                            echo '</div>';
                            echo '<div class="loan-card-body">';
                            echo '<p><strong>Branch:</strong> ' . $branch . '</p>';
                            echo '<p><strong>Start Date:</strong> ' . $start . '</p>';
                            echo '<p><strong>Due Date:</strong> ' . $due . '</p>';
                            if ($returned) {
                                echo '<p><strong>Returned on:</strong> ' . $returned . '</p>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No loans.</p>';
                    }

                }
                echo '</div>';
                echo '</div>';

            } else {

                echo '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#restoreUserModal">Restore this user</button>';
                echo '<div class="card-header bg-primary text-white">User ' . htmlspecialchars($userInfo['email']) . '</div>';
                echo '<div class="card-body">';
                echo '<p>This user has been removed</p>';
            }
        } else {
            echo '<div class="mt-4 alert alert-danger">No user found with the given email address.</div>';
        }
    }
    ?>

</div>

<?php include_once 'modals/removeUserModal.php' ?>
<?php include_once 'modals/restoreUserModal.php' ?>
<?php include_once 'modals/resetDelaysModal.php' ?>
<?php include_once 'modals/changePatronCategoryModal.php' ?>
<?php include_once 'modals/returnCopyModal.php' ?>
<?php include_once 'modals/postPoneDueModal.php' ?>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</html>