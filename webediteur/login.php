<?php
session_start();

// 1. Establish and check connection
$connection = new mysqli("localhost", "root", "", "paymedia");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$email = ""; // Initialize to prevent undefined variable notices

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Removed the 'name' requirement for login logic
    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION["editor_id"] = $editor["id"];
                $_SESSION["editor_company_name"] = $editor["company_name"];
                header("Location: index.php");
                exit();
            } else {
                $error_msg = "Invalid password.";
            }
        } else {
            $error_msg = "No user found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayMedia Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 450px; margin-top: 100px; }
        .input-group-text { cursor: pointer; }
    </style>
</head>
<body>

<div class="container login-container">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h3 class="text-center mb-3">Login</h3>
            <p class="text-muted text-center small mb-4">Welcome to PayMedia. Please log in to manage your dashboard.</p>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold small">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                        <span class="input-group-text" id="togglePassword">
                            <i class='bx bx-show' id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Login</button>
                If you don't have an account, <a href="register.php">register here</a>.
            </form>
        </div>
    </div>
</div>

<script>
    // Functional Password Toggle Script
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const toggleIcon = document.querySelector('#toggleIcon');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle icon classes
        if (type === 'text') {
            toggleIcon.classList.replace('bx-show', 'bx-hide');
        } else {
            toggleIcon.classList.replace('bx-hide', 'bx-show');
        }
    });
</script>
</body>
</html>