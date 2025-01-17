<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();
if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

function has_changes($oldData, $newData, $fieldMapping): bool
{
    foreach ($fieldMapping as $oldKey => $newKey) {
        $oldValue = $oldData[$oldKey] ?? null;
        $newValue = $newData[$newKey] ?? null;

        if ($oldKey === 'alive') {
            $oldValue = !($oldValue === 'f');
            $newValue = (bool)$newValue;
        }

        if ($oldValue != $newValue) {
            return true;
        }
    }
    return false;
}

$author = '';
if (!empty($_GET['author'])) {
    $author = $_GET['author'];

    try {
        $authorDetails = get_authors($_GET['author'])[0];
    } catch (Exception $e) {
        redirect('../lib/error.php');
    }

} else {
    redirect('../lib/error.php');
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $alive = !isset($_POST['dead']);
    unset($_POST['dead']);
    $_POST['alive'] = $alive;

    $fieldMapping = [
        'first_name' => 'firstName',
        'last_name' => 'lastName',
        'bio' => 'bio',
        'birth_date' => 'birthdate',
        'death_date' => 'deathDate',
        'alive' => 'alive'
    ];

    if (has_changes($authorDetails, $_POST, $fieldMapping)) {
        try {
            update_author(
                $_GET['author'],
                $_POST['firstName'],
                $_POST['lastName'],
                $_POST['bio'],
                $_POST['birthdate'],
                $_POST['deathDate'],
                $alive
            );
            $result = ['ok' => true, 'msg' => 'Author\'s info correctly updated. Refresh this page or go back to the catalog to see it.'];
        } catch (Exception $e) {
            $result = ['ok' => false, 'msg' => $e->getMessage()];
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit author</title>
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
    <?php
    include '../librarian/navbar.php';
    ?>
</div>

<div class="container my-4">
    <div class="custom-card">
        <h2 class=""><strong>Edit <?= $authorDetails['first_name'] . ' ' . $authorDetails['last_name'] ?>'s information</strong></h2>

        <?php if ($result): ?>
            <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
                 role="alert">
                <?php echo htmlspecialchars($result['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="container">

            <!-- Row for First and Last Name -->
            <div class="row mb-3">
                <!-- First name -->
                <div class="col-md-6">
                    <label for="firstName" class="form-label">First name</label>
                    <input required type="text" name="firstName" class="form-control" id="firstName"
                           value="<?php echo $authorDetails['first_name'] ?>">
                </div>
                <!-- Last name -->
                <div class="col-md-6">
                    <label for="lastName" class="form-label">Last name</label>
                    <input required type="text" name="lastName" class="form-control" id="lastName"
                           value="<?php echo $authorDetails['last_name'] ?>">
                </div>
            </div>

            <!-- Row for Birthdate and Death Date -->
            <div class="row mb-3">
                <!-- Birthdate -->
                <div class="col-md-6">
                    <label for="birthdate" class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" class="form-control" id="birthdate"
                           value="<?php echo $authorDetails['birth_date'] ?>">
                </div>
                <!-- Death date -->
                <div class="col-md-6">
                    <label for="deathDate" class="form-label">Death date</label>
                    <input type="date" name="deathDate" class="form-control" id="deathDate"
                           value="<?php echo $authorDetails['death_date'] ?>">
                </div>
            </div>

            <!-- Alive Checkbox -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="dead"
                       name="dead" <?= $authorDetails['alive'] == 'f' ? 'checked' : '' ?>>
                <label class="form-check-label" for="dead">
                    This author is dead
                </label>
            </div>

            <!-- Bio -->
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea required id="bio" name="bio" class="form-control"
                          rows="4"><?php echo $authorDetails['bio'] ?></textarea>
            </div>

            <!-- Submit and Cancel Buttons -->
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="../catalog/author.php?author=<?= $author ?>" class="btn btn-primary">Cancel</a>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deathDateInput = document.getElementById('deathDate');
        const deadCheckbox = document.getElementById('dead');

        deathDateInput.addEventListener('input', function () {
            deadCheckbox.checked = deathDateInput.value.trim() !== '';
        });
    });
</script>

</body>
</html>