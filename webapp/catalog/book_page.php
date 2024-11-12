<?php

include_once('../lib/book_functions.php');

if (!empty($_GET['isbn'])) {
    $result = get_book($_GET['isbn']);

    if ($result === false) {
        echo "Error in query execution.";
        exit;
    }

    $bookDetails = pg_fetch_all($result)[0];
    $bookDetails['available_copies'] = 3;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<!-- Back Button -->
<button onclick="history.back()" class="btn btn-outline-secondary mb-4">
    &larr; Back
</button>


<div class="container mt-5">
    <h1 class="mb-4"><?php echo htmlspecialchars($bookDetails['title']); ?></h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Book Details</h5>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($bookDetails['isbn']); ?></p>
            <p>
                <strong>Author:</strong> <?php echo htmlspecialchars($bookDetails['first_name'] . ' ' . $bookDetails['last_name']); ?>
            </p>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($bookDetails['publisher']); ?></p>
            <p><strong>Blurb:</strong> <?php echo htmlspecialchars($bookDetails['blurb']); ?></p>
            <p><strong>Available
                    Copies:</strong> <?php echo ($bookDetails['available_copies'] > 0) ? htmlspecialchars($bookDetails['available_copies']) : 'None'; ?>
            </p>

            <form action="" method="POST">
                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($bookDetails['isbn']); ?>">
                <button type="submit"
                        class="btn btn-primary" <?php echo ($bookDetails['available_copies'] <= 0) ? 'disabled' : ''; ?>>
                    Request Loan
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
