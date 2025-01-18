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

    if (isset($_POST['userEmail']))
        $_SESSION['userEmail'] = $_POST['userEmail'];

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
}

$email = $_SESSION['userEmail'] ?? null;

if ($email) {
    $userInfo = null;
    try {
        $userInfo = get_user_with_email($email);
    } catch (Exception) {
        redirect('../lib/error.php');
    }

    if ($userInfo) {
        $isRemoved = $userInfo['removed'] === 't';
        $email = htmlspecialchars($userInfo['email']);
        $name = htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']);
        $type = htmlspecialchars($userInfo['type']);
        $patronInfo = $userInfo['patronInfo'] ?? null;

        if (!$isRemoved) {
            $taxCode = $patronInfo ? htmlspecialchars($patronInfo['tax_code']) : null;
            $nDelays = $patronInfo ? htmlspecialchars($patronInfo['n_delays']) : 0;
            $category = $patronInfo ? htmlspecialchars($patronInfo['category']) : null;
            $categoryString = $category === 'premium' ? 'Premium patron' : 'Patron';

            try {
                $loans = $patronInfo ? get_loans($userInfo['id']) : [];
            } catch (Exception $e) {
                redirect('../lib/error.php');
            }
        }
    }
}
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
        .loan-card-header button {
            margin-left: auto;
        }

        .small-text {
            font-size: 0.85rem;
        }

        .scrollable-loans {
            max-height: 400px;
            overflow-y: auto;
        }

        .scrollable-loans table thead th {
            position: sticky;
            top: 0;
            background-color: #ffffff;
            z-index: 1;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.1);
        }

        .compact-info p {
            margin-bottom: 0.3rem;
        }

        .custom-card {
            background-color: #f8f9fa;
            border: none;
            border-radius: 0.75rem;
            padding: 1rem;
            position: relative;
        }

        .hover-lighten {
            color: #000;
            transition: color 0.2s ease-in-out;
            text-decoration: none;
        }

        .hover-lighten:hover {
            color: #555;
            text-decoration: none;
        }

        .title-column {
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

    </style>
</head>
<body>
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">

    <!-- Search bar -->
    <div class="container d-flex justify-content-center">
        <div class="w-50">
            <form method="post" class="row g-3" action="">
                <div class="row align-items-end">
                    <!-- Email Input -->
                    <div class="col-md-10">
                        <label for="userEmail" class="form-label d-block mt-3">
                            Search for a user or
                            <a href="add-user.php?form=patron" class="link-opacity-100-hover hover-lighten"><strong>add
                                    a new one</strong></a>
                        </label>
                        <input type="email" class="form-control" name="userEmail" id="userEmail"
                               placeholder="Enter user's email"
                               value="<?= htmlspecialchars($email ?? ($_POST['userEmail'] ?? '')) ?>" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback alert -->
    <?php if ($result): ?>
        <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
             role="alert">
            <?php echo htmlspecialchars($result['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($userInfo) and $userInfo): ?>
        <div class="custom-card mt-4">
            <?php if (!$isRemoved): ?>

                <div class="card-body compact-info">

                    <h2 class="card-title">
                        <strong><?= htmlspecialchars($name) ?></strong></h2>
                    <p class="text-muted"><?= htmlspecialchars($email) ?></p>

                    <?php if ($patronInfo): ?>
                        <p><strong><i class="bi bi-person-bounding-box"></i> <?= $categoryString; ?></strong></p>
                        <p><strong>
                                <i class="bi bi-card-text"></i>
                                Tax Code:</strong> <?= $taxCode; ?></p>
                        <p><strong><i class="bi bi-hourglass-bottom"></i>
                                Number of late returns:</strong> <?= $nDelays; ?></p>

                        <!-- Change category button -->
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#changeCategoryModal">
                            <i class="bi bi-person-fill-gear"></i> Change Category
                        </button>

                        <!-- Reset delays button -->
                        <?php if ($nDelays > 0): ?>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#resetDelaysModal">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Delays
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><strong><i class="bi bi-person-bounding-box"></i> Librarian</strong></p>
                    <?php endif; ?>

                    <!-- User removal button -->
                    <?php if ($_SESSION['user']['id'] != $userInfo['id']): ?>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#removeUserModal">
                            <i class="bi bi-person-fill-x"></i> Remove User
                        </button>
                    <?php endif; ?>

                </div>

                <div class="card-footer compact-info mt-3">
                    <?php if ($patronInfo): ?>

                        <!-- All loans -->
                        <?php if (!empty($loans)): ?>
                            <h4>Loans</h4>
                            <div class="table-responsive scrollable-loans rounded-4">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th class="title-column">Title</th>
                                        <th>ISBN</th>
                                        <th>Branch</th>
                                        <th>Start Date</th>
                                        <th>Due Date</th>
                                        <th>Returned On</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($loans as $loan): ?>
                                        <?php
                                        try {
                                            $start = new DateTime($loan['start']);
                                            $due = new DateTime($loan['due']);
                                            $returned = $loan['returned']
                                                ? new DateTime($loan['returned'])
                                                : null;
                                            $today = new DateTime('today');
                                            $dueTextColor = (!$returned and $due < $today) ? 'text-danger' : '';
                                            $returnedTextColor = ($returned and $returned > $due) ? 'text-danger' : '';

                                        } catch (Exception $e) {
                                            $start = $due = $returned = 'Error parsing date';
                                        }
                                        ?>
                                        <tr>
                                            <td class="title-column  small-text"><?= htmlspecialchars($loan['title']) ?></td>
                                            <td class="small-text"><?= htmlspecialchars($loan['isbn']) ?></td>
                                            <td class="small-text"><?= htmlspecialchars($loan['address'] . ' - ' . $loan['city']) ?></td>
                                            <td class="small-text"><?= $start->format('Y-m-d') ?></td>
                                            <td class="small-text <?= $dueTextColor ?>"><?= $due->format('Y-m-d') ?></td>
                                            <td class="small-text <?= $returnedTextColor ?>"><?= $returned ? $returned->format('Y-m-d') : 'Not Returned' ?></td>
                                            <td>
                                                <?php if (!$returned): ?>
                                                    <button class="btn btn-success btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#returnCopyModal"
                                                            data-loan-id="<?= htmlspecialchars($loan['id']) ?>">
                                                        <i class="bi bi-box-arrow-in-down-left"></i> Return
                                                    </button>
                                                    <button class="btn btn-warning btn-sm ms-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#postponeDueModal"
                                                            data-loan-id="<?= htmlspecialchars($loan['id']) ?>">
                                                        <i class="bi bi-clock"></i> Postpone
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="small-text"><strong>This patron has yet to make a loan.</strong></p>
                        <?php endif; ?>


                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Removed user -->
                <div class="">
                    <h5>User <?= $email ?> has been removed.</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#restoreUserModal">
                        Restore this user
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (isset($email)): ?>
        <div class="custom-card mt-4">
            <h6>No user was found with the given email address.</h6>
        </div>
    <?php endif; ?>

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