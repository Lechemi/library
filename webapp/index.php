<?php
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);
include_once('lib/redirect.php');
include_once('lib/account-functions.php');
session_start();

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['usr'];
    $psw = $_POST['psw'];

    $user = [];

    try {
        $user = authenticate($email, $psw);
        $_SESSION['user'] = $user;
        redirect('catalog/catalog.php');
    } catch (Exception $e) {
        $result = ['ok' => false, 'msg' => $e->getMessage()];
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .custom-card {
            background-color: #f8f9fa;
            border: none;
            border-radius: 0.75rem;
            padding: 1rem;
            position: relative;
        }
    </style>

</head>
<body class="d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 custom-card">

            <h2><strong>Login</strong></h2>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="inputEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="inputEmail" aria-describedby="emailHelp"
                           name="usr" required>
                </div>
                <div class="mb-3">
                    <label for="inputPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="inputPassword" name="psw" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <?php if ($result): ?>
                <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-3"
                     role="alert">
                    <?php echo htmlspecialchars($result['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>