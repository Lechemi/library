<?php
$current_page = basename($_SERVER['PHP_SELF']);

$catalogRelatedPages = [
    'catalog.php',
    'book.php',
    'add-book.php',
    'add-publisher.php',
    'edit-author.php',
    'edit-book.php',
    'author.php',
    'add-author.php',
    'search-authors.php'
];
$userRelatedPages = ['manage-users.php', 'add-user.php'];
$branchRelatedPages = ['manage-branches.php', 'branch.php', 'add-branch.php'];
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

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php
                    echo (in_array($current_page, $catalogRelatedPages)) ? 'active' : ''; ?>"
                       href="../catalog/catalog.php">Catalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php
                    echo (in_array($current_page, $userRelatedPages)) ? 'active' : ''; ?>"
                       href="../librarian/manage-users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php
                    echo (in_array($current_page, $branchRelatedPages)) ? 'active' : ''; ?>"
                       href="../librarian/manage-branches.php">Branches</a>
                </li>
            </ul>
        </div>

        <a href="../manage-account/manage-account.php" class="btn ms-auto">
            <i class="bi bi-person" style="font-size: 1.5rem;"></i>
        </a>
    </div>
</nav>
