<?php
$current_page = basename($_SERVER['PHP_SELF']); // Name of the current file
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<nav class="navbar navbar-expand-lg bg-body-tertiary navbar-custom rounded-4">
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
                    <a class="nav-link <?php echo (in_array($current_page, ['catalog.php', 'book.php'])) ? 'active' : ''; ?>"
                       href="../catalog/catalog.php">Catalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (in_array($current_page, ['manage-users.php', 'add-user.php'])) ? 'active' : ''; ?>"
                       href="../librarian/manage-users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (in_array($current_page, ['manage-branches.php', 'branch.php', 'add-branch.php'])) ? 'active' : ''; ?>"
                       href="../librarian/manage-branches.php">Branches</a>
                </li>
            </ul>
        </div>

        <!-- Link to account management page -->
        <a href="../manage-account/manage-account.php" class="btn ms-auto">
            <i class="bi bi-person" style="font-size: 1.5rem;"></i>
        </a>
    </div>
</nav>
