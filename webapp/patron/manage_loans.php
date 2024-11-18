<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/book_functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

$result = get_active_loans($_SESSION['user']['id']);

if ($result === false) {
    echo "Error in query execution.";
    exit;
}

$activeLoans = pg_fetch_all($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Basic table styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>

    <!-- Add some CSS to style the ISBN -->
    <style>
        .isbn {
            font-size: 0.8em; /* Smaller font size */
            color: #888; /* Lighter color */
        }
    </style>

</head>

<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">


    <table>
        <thead>
        <tr>
            <th>Book</th>
            <th>Branch</th>
            <th>Start</th>
            <th>Due</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Loop through each row of data
        foreach ($activeLoans as $loan) {
            try {
                $start = new DateTime($loan['start']);
                $due = new DateTime($loan['due']);
                $start = $start->format('Y-m-d H:i:s');
                $due = $due->format('Y-m-d H:i:s');
            } catch (DateMalformedStringException $e) {
                echo 'Some error occurred';
            }

            $branch = $loan['address'] . ' - ' . $loan['city'];

            // Include ISBN next to the title
            $isbn = $loan['isbn'];
            $titleWithIsbn = "{$loan['title']} <span class='isbn'>{$isbn}</span>";

            echo "<tr>";
            echo "<td>{$titleWithIsbn}</td>";
            echo "<td>{$branch}</td>";
            echo "<td>{$start}</td>";
            echo "<td>{$due}</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>


</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>