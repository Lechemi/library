<?php

include_once('../lib/catalog-functions.php');
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');

session_start();

function has_changes($oldData, $newData, $fieldMapping): bool {
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

if (!isset($_SESSION['user'])) redirect('../index.php');
if ($_SESSION['user']['type'] != 'librarian') redirect('../index.php');

if (!empty($_GET['author'])) {

    try {
        $authorDetails = get_authors($_GET['author'])[0];
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

} else {
    echo "Error, no author.";
    exit;
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
    <title>Edit book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php
    include '../librarian/navbar.php';
    ?>
</div>

<div class="container my-4">

    <?php if ($result): ?>
        <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
             role="alert">
            <?php echo htmlspecialchars($result['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h5 class="mb-4">Editing author</h5>

    <form method="POST" action="">

        <!-- First name -->
        <div class="mb-3">
            <label for="firstName" class="form-label">First name</label>
            <input required type="text" name="firstName" class="form-control" id="firstName"
                   value="<?php echo $authorDetails['first_name'] ?>">
        </div>

        <!-- Last name -->
        <div class="mb-3">
            <label for="lastName" class="form-label">Last name</label>
            <input required type="text" name="lastName" class="form-control" id="lastName"
                   value="<?php echo $authorDetails['last_name'] ?>">
        </div>

        <!-- Birthdate -->
        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" name="birthdate" class="form-control" id="birthdate"
                   value="<?php echo $authorDetails['birth_date'] ?>">
        </div>

        <!-- Death date -->
        <div class="mb-3">
            <label for="deathDate" class="form-label">Death date</label>
            <input type="date" name="deathDate" class="form-control" id="deathDate"
                   value="<?php echo $authorDetails['death_date'] ?>">
        </div>

        <!-- Alive -->
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="dead"
                   name="dead" <?= $authorDetails['alive'] == 'f' ? 'checked' : '' ?> >
            <label class="form-check-label" for="dead">
                This author is dead
            </label>
        </div>

        <!-- Bio -->
        <div class="mb-3">
            <label for="bio" class="form-label">Bio</label>
            <textarea required id="bio" name="bio" class="form-control"><?php echo $authorDetails['bio'] ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deathDateInput = document.getElementById('deathDate');
        const deadCheckbox = document.getElementById('dead');

        // Listen for changes on the death date input
        deathDateInput.addEventListener('input', function () {
            deadCheckbox.checked = deathDateInput.value.trim() !== '';
        });
    });
</script>

</body>
</html>