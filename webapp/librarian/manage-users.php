<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account-functions.php');
include_once('../lib/book-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

$errorMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['userEmail'])) {
        // Store the email in session to persist it across page reloads
        $_SESSION['userEmail'] = $_POST['userEmail'];
    }

    try {
        if (isset($_POST['removeUser'])) {
            remove_user($_POST['removeUser']);
        }

        if (isset($_POST['restoreUser'])) {
            restore_user($_POST['restoreUser']);
        }

        if (isset($_POST['resetDelays'])) {
            reset_delays($_POST['resetDelays']);
        }

        if (isset($_POST['returnCopy'])) {
            return_copy($_POST['returnCopy']);
        }

        if (isset($_POST['postponeDue'])) {
            $loanId = $_POST['postponeDue'];
            $days = $_POST['postponeDays'];
            if (!filter_var($days, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 30]])) {
                throw new Exception("Invalid number of days for postponing due date. Enter a value between 1 and 30.");
            }
            postpone_due($loanId, $days);
        }

        if (isset($_POST['selectedCategory'])) {
            change_patron_category($_POST['changingPatron'], $_POST['selectedCategory']);
        }

    } catch (Exception $e) {
        $errorMessage = $e->getMessage(); // Capture the error message
    }

}

// Check if email is set in session, otherwise handle empty state
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
        /* Custom styles for loan cards */
        .list-group-item {
            font-size: 0.9rem; /* Smaller font size */
            padding: 0.5rem 1rem; /* Reduce padding */
            margin-bottom: 0.5rem; /* Less separation between cards */
            border: 1px solid #ddd; /* Optional: to add a border */
            border-radius: 0.25rem;
        }

        .list-group-item h4 {
            font-size: 1rem; /* Smaller font for titles */
            margin: 0; /* Remove margin for compactness */
        }

        .loan-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem; /* Space between header and content */
        }

        .loan-card-header button {
            margin-left: auto;
        }

        .loan-card-body p {
            margin: 0.25rem 0; /* Reduce spacing between lines */
        }
    </style>
</head>

<body>
<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">

    <!-- Search bar -->
    <div class="container d-flex justify-content-center">
        <div class="w-50">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="userEmail" class="form-label d-block">
                        Search for a user or
                        <a href="add-user.php" class="text-primary">add a new one</a>
                    </label>
                    <input type="email" class="form-control" name="userEmail" id="userEmail"
                           placeholder="Enter user's email" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <!-- Error alert -->
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Display User Information -->
    <?php

    if ($email) {
        $userInfo = null;
        try {
            $userInfo = get_user_with_email($email);
        } catch (Exception $e) {
        }

        if ($userInfo) {

            if ($userInfo['removed'] == 'f') {

                echo '<div class="card mt-4">';
                echo '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#removeUserModal">Remove this user</button>';
                echo '<div class="card-header bg-primary text-white">User ' . htmlspecialchars($userInfo['email']) . '</div>';
                echo '<div class="card-body">';
                echo '<p><strong>Name:</strong> ' . htmlspecialchars($userInfo['first_name']) . ' ' . htmlspecialchars($userInfo['last_name']) . '</p>';
                echo '<p><strong>Type:</strong> ' . htmlspecialchars($userInfo['type']) . '</p>';

                // If user is a patron, display additional patronInfo fields
                if (isset($userInfo['patronInfo'])) {

                    $result = get_loans($userInfo['id']);
                    if ($result === false) {
                        echo "Error in query execution.";
                        exit;
                    }
                    $loans = pg_fetch_all($result);

                    echo '<p><strong>Tax Code:</strong> ' . htmlspecialchars($userInfo['patronInfo']['tax_code']) . '</p>';

                    // Display Number of Delays with a reset button
                    echo '<p><strong>Number of Delays:</strong> ' . htmlspecialchars($userInfo['patronInfo']['n_delays']) . '</p>';
                    if ($userInfo['patronInfo']['n_delays'] > 0) {
                        echo '    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetDelaysModal">Reset Delays</button>';
                    }

                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($userInfo['patronInfo']['category']) . '</p>';
                    echo '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#changeCategoryModal">Change category</button>';

                    if (!empty($loans)) {
                        echo '<h5 class="mt-3">Loans</h5>';
                        echo '<div class="list-group">';
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
                            echo '  <div class="loan-card-header">';
                            echo "    <h4>{$titleWithIsbn}</h4>";

                            // "Return Copy" button
                            if (!$returned) {
                                echo '    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#returnCopyModal" data-loan-id="' . htmlspecialchars($loan['id']) . '">Return Copy</button>';
                            }

                            // "Postpone Due" button
                            // TODO also check if we're past the due date
                            if (!$returned) {
                                echo '    <button class="btn btn-warning btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#postponeDueModal" data-loan-id="' . htmlspecialchars($loan['id']) . '">Postpone Due</button>';
                            }

                            echo '  </div>';
                            echo '  <div class="loan-card-body">';
                            echo '    <p><strong>Branch:</strong> ' . $branch . '</p>';
                            echo '    <p><strong>Start Date:</strong> ' . $start . '</p>';
                            echo '    <p><strong>Due Date:</strong> ' . $due . '</p>';
                            if ($returned) {
                                echo '    <p><strong>Returned on:</strong> ' . $returned . '</p>';
                            }
                            echo '  </div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No loans.</p>';
                    }
                }
                echo '  </div>';
                echo '</div>';

            } else {

                // User has been removed
                echo '<div class="card mt-4">';
                echo '    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#restoreUserModal">Restore this user</button>';
                echo '    <div class="card-header bg-primary text-white">User ' . htmlspecialchars($userInfo['email']) . '</div>';
                echo '    <div class="card-body">';
                echo '        <p>This user has been removed</p>';
            }
        } else {
            echo '<div class="mt-4 alert alert-danger">No user found with the given email address.</div>';
        }
    }

    ?>

</div>

<!-- Modal for removing user -->
<div class="modal fade" id="removeUserModal" tabindex="-1" aria-labelledby="removeUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeUserModalLabel">Confirm removing user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="removeUser" value="<?= isset($userInfo) ? $userInfo['id'] : '' ?>">
                    <button type="submit" class="btn btn-danger">Remove user</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for restoring user -->
<div class="modal fade" id="restoreUserModal" tabindex="-1" aria-labelledby="restoreUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreUserModalLabel">Confirm restoring user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to restore this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="restoreUser" value="<?= isset($userInfo) ? $userInfo['id'] : '' ?>">
                    <button type="submit" class="btn btn-danger">Restore user</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Resetting Delays -->
<div class="modal fade" id="resetDelaysModal" tabindex="-1" aria-labelledby="resetDelaysModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetDelaysModalLabel">Confirm Reset Delays</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to reset the number of delays for this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="resetDelays" value="<?= isset($userInfo) ? $userInfo['id'] : '' ?>">
                    <button type="submit" class="btn btn-danger">Confirm Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Returning Copy -->
<div class="modal fade" id="returnCopyModal" tabindex="-1" aria-labelledby="returnCopyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnCopyModalLabel">Confirm Return Copy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to mark this copy as returned?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="returnCopy" id="returnCopyInput">
                    <button type="submit" class="btn btn-success">Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Postponing Due -->
<div class="modal fade" id="postponeDueModal" tabindex="-1" aria-labelledby="postponeDueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postponeDueModalLabel">Postpone Due Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="postponeDays" class="form-label">Number of days to postpone (1-30):</label>
                        <input type="number" class="form-control" name="postponeDays" id="postponeDays" min="1" max="30"
                               required>
                    </div>
                    <input type="hidden" name="postponeDue" id="postponeDueInput">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Postpone</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for changing patron's category -->
<div class="modal fade" id="changeCategoryModal" tabindex="-1" aria-labelledby="changeCategoryModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeCategoryModalLabel">Change patron's category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="selectedCategory" class="form-label">Select the new category</label>
                        <select class="form-select" name="selectedCategory" id="selectedCategory"
                                aria-label="Default select example" required>
                            <?php
                            foreach (get_category_names() as $category) {
                                if ($category['name'] != $userInfo['patronInfo']['category']) {
                                    echo '<option value="' . $category['name'] . '">' . $category['name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="changingPatron" value="<?= isset($userInfo) ? $userInfo['id'] : '' ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Change</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const returnCopyModal = document.getElementById('returnCopyModal');
        returnCopyModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const loanId = button.getAttribute('data-loan-id'); // Extract loan ID
            const input = document.getElementById('returnCopyInput'); // Hidden input field
            input.value = loanId; // Set the value to the loan ID
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const postponeDueModal = document.getElementById('postponeDueModal');
        postponeDueModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const loanId = button.getAttribute('data-loan-id'); // Extract loan ID
            const input = document.getElementById('postponeDueInput'); // Hidden input field
            input.value = loanId; // Set the value to the loan ID
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</html>
