<?php

include_once('../lib/book-functions.php');
include_once('../lib/branch-functions.php');
include_once('../lib/redirect.php');

if (!empty($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
} else {
    echo "Error, no book.";
    exit;
}

try {
    $branches = get_branches();
} catch (Exception $e) {

}
$branchesJson = json_encode($branches);
const noPreferenceStr = 'No preference';
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<form method="POST" action="loan-request-results.php">
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