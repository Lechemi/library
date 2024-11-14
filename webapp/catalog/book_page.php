<?php

include_once('../lib/book_functions.php');
include_once('../lib/redirect.php');

session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $result = get_books($isbn);

    if ($result === false) {
        echo "Error in query execution.";
        exit;
    }

    $bookDetails = group_authors($result)[$isbn];
    $bookDetails['available_copies'] = pg_fetch_all(get_available_copies($isbn));
} else {
    echo "Error, no book.";
    exit;
}

$branches = pg_fetch_all(get_branches());
$branchesJson = json_encode($branches);
const noPreferenceStr = 'No preference';
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

<a href="../patron/patron_catalog.php">Back to catalog</a>

<div class="container mt-5">
    <h1 class="mb-4"><?php echo htmlspecialchars($bookDetails['title']); ?></h1>

    <div class="container my-5">
        <div class="card mb-5 pb-4">
            <div class="card-body">
                <h5 class="card-title">Book Details</h5>
                <p><strong>ISBN:</strong> <?php echo htmlspecialchars($isbn); ?></p>
                <p>
                    <strong>Author:</strong>
                    <?php
                    foreach ($bookDetails['authors'] as $author) {
                        echo htmlspecialchars($author['name']);
                        if ($author !== end($bookDetails['authors'])) {
                            echo ', ';
                        }
                    }
                    ?>
                </p>
                <p><strong>Publisher:</strong> <?php echo htmlspecialchars($bookDetails['publisher']); ?></p>
                <p><strong>Blurb:</strong> <?php echo htmlspecialchars($bookDetails['blurb']); ?></p>
                <p><strong>Available
                        Copies:</strong>
                    <?php
                    $copyCount = count($bookDetails['available_copies']);
                    echo ($copyCount > 0) ?
                        htmlspecialchars($copyCount)
                        : 'None';
                    ?>
                </p>

                <form method="POST" action="loan_request_results.php">
                    <div class="mb-3">
                        <input type="hidden" name="isbn" value=" <?php echo htmlspecialchars($isbn); ?> ">

                        <!-- City selection -->
                        <label for="branch-city" class="form-label">Do you have a preferred
                            city?</label>
                        <select onchange="updateBranches()" name="branch-city" id="branch-city" class="form-select"
                                aria-label="Default select example">
                            <option selected> <?php echo noPreferenceStr ?> </option>

                            <?php

                            // Extract all city names
                            $cities = array_column($branches, 'city');

                            // Remove duplicates to get unique cities
                            $uniqueCities = array_unique($cities);
                            foreach ($uniqueCities as $city) {
                                echo '<option>' . $city . '</option>';
                            }

                            ?>
                        </select>

                        <!-- Address selection -->
                        <label for="branch-address" class="form-label">Do you have a preferred
                            branch?</label>
                        <select name="branch-address" id="branch-address" class="form-select"
                                aria-label="Default select example">
                            <option value=""> <?php echo noPreferenceStr ?> </option>
                        </select>

                        <div class="form-text">If no preference is specified, a copy can be
                            provided from any branch.
                        </div>
                    </div>
                    <button type="submit" name="submitButton" class="btn btn-primary">Request</button>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
    // Parse PHP array into JavaScript object
    const branches = <?php echo $branchesJson; ?>;

    function updateBranches() {
        // Get the selected city
        const selectedCity = document.getElementById('branch-city').value;

        // Get the branch select element
        const branchSelect = document.getElementById('branch-address');

        // Clear previous branch options
        branchSelect.innerHTML = '<option value=""> <?php echo noPreferenceStr ?> </option>';

        // Filter branches based on the selected city and populate the branch dropdown
        branches.forEach(branch => {
            if (branch.city === selectedCity) {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = branch.address;
                branchSelect.appendChild(option);
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
