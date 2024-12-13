<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/account-functions.php');
include_once('../lib/book-functions.php');
session_start();

// Redirect if no user is logged in
if (!isset($_SESSION['user'])) redirect('../index.php');

// Handle form submission and reset delays logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['userEmail'])) {
        // Store the email in session to persist it across page reloads
        $_SESSION['userEmail'] = $_POST['userEmail'];
    }

    // If resetting delays, call the reset function and then redirect to refresh the page
    if (isset($_POST['resetDelays'])) {
        try {
            reset_delays($_POST['resetDelays']);  // Reset the delays based on user ID
        } catch (Exception $e) {
            // Handle error, you can log the error or show a message
        }
        // Redirect to reload the page after reset
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();  // Make sure no further code is executed
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
</head>

<body>
<!-- Navbar -->
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
                        <a href="add-user.php" class="text-primary">add a new one</a>
                    </label>
                    <input type="email" class="form-control" name="userEmail" id="userEmail" placeholder="Enter user's email" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <!-- Display User Information -->
    <?php
    if ($email) {
        $userInfo = null;
        try {
            $userInfo = get_user_with_email($email);
        } catch (Exception $e) {}

        if ($userInfo) {
            echo '<div class="card mt-4">';
            echo '  <div class="card-header bg-primary text-white">User ' . htmlspecialchars($userInfo['email']) . '</div>';
            echo '  <div class="card-body">';
            echo '    <p><strong>Name:</strong> ' . htmlspecialchars($userInfo['first_name']) . ' ' . htmlspecialchars($userInfo['last_name']) . '</p>';
            echo '    <p><strong>Type:</strong> ' . htmlspecialchars($userInfo['type']) . '</p>';

            // If user is a patron, display additional patronInfo fields
            if (isset($userInfo['patronInfo'])) {

                $result = get_active_loans($userInfo['id']);
                if ($result === false) {
                    echo "Error in query execution.";
                    exit;
                }
                $activeLoans = pg_fetch_all($result);

                echo '<p><strong>Tax Code:</strong> ' . htmlspecialchars($userInfo['patronInfo']['tax_code']) . '</p>';

                // Display Number of Delays with a reset button
                echo '<p><strong>Number of Delays:</strong> ' . htmlspecialchars($userInfo['patronInfo']['n_delays']) . '</p>';

                if ($userInfo['patronInfo']['n_delays'] > 0) {
                    echo '    <form method="post" action="" style="display:inline;">';
                    echo '      <input type="hidden" name="resetDelays" value=' . $userInfo['id'] . '>';
                    echo '      <button type="submit" class="btn btn-danger btn-sm">Reset Delays</button>';
                    echo '    </form>';
                }

                echo '<p><strong>Category:</strong> ' . htmlspecialchars($userInfo['patronInfo']['category']) . '</p>';

                if (!empty($activeLoans)) {
                    echo '<h5 class="mt-3">Active Loans</h5>';
                    echo '<div class="list-group">';

                    foreach ($activeLoans as $loan) {
                        try {
                            $start = new DateTime($loan['start']);
                            $due = new DateTime($loan['due']);
                            $start = $start->format('Y-m-d H:i:s');
                            $due = $due->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            echo 'Some error occurred with the dates.';
                        }

                        $branch = $loan['address'] . ' - ' . $loan['city'];
                        $isbn = $loan['isbn'];
                        $titleWithIsbn = "{$loan['title']} <span class='isbn'>{$isbn}</span>";

                        // Display the loan information in a card-like format
                        echo '<div class="list-group-item">';
                        echo "<h4>{$titleWithIsbn}</h4>";
                        echo '  <p><strong>Branch:</strong> ' . $branch . '</p>';
                        echo '  <p><strong>Start Date:</strong> ' . $start . '</p>';
                        echo '  <p><strong>Due Date:</strong> ' . $due . '</p>';
                        echo '</div>';
                    }

                    echo '</div>';
                } else {
                    echo '<p>No active loans.</p>';
                }

            }

            echo '  </div>';
            echo '</div>';
        } else {
            echo '<div class="mt-4 alert alert-danger">No user found with the given email address.</div>';
        }
    }

    ?>

</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</html>
