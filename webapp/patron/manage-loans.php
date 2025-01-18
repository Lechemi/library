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
            font-size: 0.9em;
            width: 100%;
            table-layout: fixed;
        }

        th, td {
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: transparent;
        }

        .book-column {
            width: 40%;
            white-space: nowrap;
            overflow: scroll;
        }

        .date-column {
            width: 10%;
        }

        .isbn {
            font-size: 0.8em;
            color: #888;
        }

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

                    $returned = null;
                    if ($loan['returned'] != null) {
                        $returned = new DateTime($loan['returned']);
                    }

                } catch (DateMalformedStringException $e) {
                    redirect('../lib/error.php');
                }

                $branch = $loan['address'] . ' - ' . $loan['city'];

                $isbn = $loan['isbn'];
                $titleWithIsbn = "{$loan['title']} • <span class='isbn'>{$isbn}</span>";

                $today = new DateTime('today');
                $dueTextColor = (!$returned and $due < $today) ? 'text-danger' : '';
                $returnedTextColor = ($returned and $returned > $due) ? 'text-danger' : '';
                $returned = $returned ? $returned->format('Y-m-d') : 'Not returned';

                echo "<tr>";
                echo "<td class='book-column'>{$titleWithIsbn}</td>";
                echo "<td>{$branch}</td>";
                echo "<td class='date-column'>{$start->format('Y-m-d')}</td>";
                echo "<td class='date-column " . $dueTextColor . "'>{$due->format('Y-m-d')}</td>";
                echo "<td class='date-column " . $returnedTextColor . "'>{$returned}</td>";
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