<?php

include_once('../lib/author-functions.php');

if (!empty($_GET['author'])) {
    $result = get_author($_GET['author']);

    if ($result === false) {
        echo "Error in query execution.";
        exit;
    }

    $authorDetails = pg_fetch_all($result)[0];
    $authorDetails['full_name'] = $authorDetails['first_name'] . ' ' . $authorDetails['last_name'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<!-- Back Button -->
<button onclick="history.back()" class="btn btn-outline-secondary mb-4">
    &larr; Back
</button>

<div class="container profile-container">
    <div class="card">
        <div class="card-body">
            <h1 class="author-title">
                <?= htmlspecialchars($authorDetails['full_name']) ?>
            </h1>

            <p class="text-muted">
                Born: <?= $authorDetails['birth_date'] ?? 'unknown' ?> -
                Died: <?= $authorDetails['alive'] == 't' ? 'Present' : ($authorDetails['death_date'] ?? 'Unknown') ?>
            </p>

            <hr>

            <p>
                <?= htmlspecialchars($authorDetails['bio'] ?? 'No bio available.') ?>
            </p>

            <a href=<?php echo '../catalog/catalog.php?searchInput=' . $authorDetails['full_name'] ?>>View
                books</a>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
