<?php
// settings.php content
session_start();
$connection = new mysqli("localhost", "root", "", "paymedia");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
// 2. CSRF Token Generation (for future form actions)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userName = htmlspecialchars($_SESSION["name"] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayMedia Settings</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --background-color: #f8f9fa;
            --text-color: #212529;
            --color-success: #28a745;
            --color-error: #dc3545;
            --color-warning: #ffc107;
            --color-dark: #343a40;
            --color-light: #f8f9fa;
        }
        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .container {
            margin-top: 100px;
}
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            width: 100%;
        }
        .card {
            margin-top: 20px;
            
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Settings</h2>
        <p>Welcome, <?= $userName ?>!</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>