<?php
$current_page = basename($_SERVER['PHP_SELF']); // This will give you the name of the current file
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .navbar-custom {
            border-radius: 15px;
        }
    </style>
</head>

<nav class="navbar navbar-expand-lg bg-body-tertiary navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Library</a>

        <!-- Toggler for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left-aligned links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'book_catalog.php') ? 'active' : ''; ?>"
                       href="book_catalog.php">Book catalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_loans.php') ? 'active' : ''; ?>"
                       href="manage_loans.php">Manage loans</a>
                </li>
            </ul>
        </div>

        <!-- Right-aligned button -->
        <button class="btn ms-auto" type="button">
            <i class="bi bi-person" style="font-size: 1.5rem;"></i>
        </button>
    </div>
</nav>
