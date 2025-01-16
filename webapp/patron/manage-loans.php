<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/catalog-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

try {
    $activeLoans = get_loans($_SESSION['user']['id']);
} catch (Exception $e) {
    redirect('../lib/error.php');
}

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
        table {
            font-size: 0.9em; /* Make all text smaller */
            width: 100%; /* Ensure the table takes up available space */
            table-layout: fixed; /* Makes sure the columns obey the defined widths */
        }

        th, td {
            padding: 8px; /* Add some padding for readability */
            text-align: left;
            word-wrap: break-word; /* Break long words to prevent overflow */
        }

        th {
            background-color: #f2f2f2; /* Optional: Light background for header */
        }

        .book-column {
            width: 40%; /* Limit the width of the 'Book' column */
            white-space: nowrap;
            overflow: scroll;
        }

        /* Reduce the width for the 'Started', 'Due', and 'Returned' columns */
        .date-column {
            width: 10%; /* Allocate less space to the date columns */
        }

        .isbn {
            font-size: 0.8em; /* Smaller font size */
            color: #888; /* Lighter color */
        }

        .custom-card {
            background-color: #f8f9fa; /* Very light grey background */
            border: none; /* No border */
            border-radius: 0.75rem; /* Rounded corners */
            padding: 1rem; /* Padding for content */
            position: relative; /* Relative positioning for the button */
        }
    </style>

</head>

<body>

<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">

    <div class="custom-card">
        <table>
            <thead>
            <tr>
                <th class="book-column">Book</th>
                <th>Branch</th>
                <th class="date-column">Started</th>
                <th class="date-column">Due</th>
                <th class="date-column">Returned</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($activeLoans as $loan) {
                try {
                    $start = new DateTime($loan['start']);
                    $due = new DateTime($loan['due']);
                    $start = $start->format('Y-m-d');
                    $due = $due->format('Y-m-d');

                    $returned = 'Not returned';
                    if ($loan['returned'] != null) {
                        $returned = new DateTime($loan['returned']);
                        $returned = $returned->format('Y-m-d');
                    }

                } catch (DateMalformedStringException $e) {
                    redirect('../lib/error.php');
                }

                $branch = $loan['address'] . ' - ' . $loan['city'];

                // Include ISBN next to the title
                $isbn = $loan['isbn'];
                $titleWithIsbn = "{$loan['title']} • <span class='isbn'>{$isbn}</span>";

                echo "<tr>";
                echo "<td class='book-column'>{$titleWithIsbn}</td>";
                echo "<td>{$branch}</td>";
                echo "<td class='date-column'>{$start}</td>";
                echo "<td class='date-column'>{$due}</td>";
                echo "<td class='date-column'>{$returned}</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<body>