<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('../lib/redirect.php');
include_once('../lib/branch-functions.php');
session_start();

if (!isset($_SESSION['user'])) redirect('../index.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage branches</title>
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
</head>

<body>
<!-- Navbar -->
<div class="container mt-3">
    <?php include 'navbar.php'; ?>
</div>

<div class="container my-4">

    <?php
    try {
        $branches = get_branches();

    } catch (Exception $e) {
        echo 'An error occurred...';
    }
    ?>

    <table>
        <thead>
        <tr>
            <th>Id</th>
            <th>City</th>
            <th>Address</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Loop through each row of data
        foreach ($branches as $branch) {
            $id = $branch['id'];
            $city = $branch['city'];
            $address = $branch['address'];

            echo "<tr>";
            echo "<td>{$id}</td>";
            echo "<td>{$city}</td>";
            echo "<td>{$address}</td>";
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