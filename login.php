<?php
session_start();
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php'; // Corrected path to autoload.php

use PHPGangsta\GoogleAuthenticator;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user information to get the role
    $user_query = "SELECT * FROM users WHERE id = '$user_id'";
    $user_result = $conn->query($user_query);

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();

        if ($user['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        } else {
            header("Location: dashboard.php");
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $totp_code = $_POST['totp_code']; // Added TOTP code field

    // Validate credentials
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $hashed_password = hash('sha256', $password);

        if (hash('sha256', $password) === $user['password']) {
            // Verify TOTP code
            $ga = new GoogleAuthenticator();
            $isValidTotp = $ga->verifyCode($user['secret_key'], $totp_code, 2); // 2 is the tolerance window

            if ($isValidTotp) {
                $_SESSION['user_id'] = $user['id'];
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid credentials";
            }
        } else {
            $error = "Invalid credentials";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ledger Website</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <?php if (isset($error)) { ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php } ?>

    <div class="wrapper">
    <form action="login.php" method="post">
        <h1>Login</h1>
        <div class="input-box">
            <input type="text" placeholder="username" name="username" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
            <input type="password" placeholder="password" name="password" required>
            <i class='bx bxs-lock-alt'></i>
        </div>
        <div class="input-box">
            <input type="text" placeholder="TOTP Code" name="totp_code" required>
        </div>
        <div class="remember-forgot">
            <label><input type="checkbox"> Remember me</label>
            <a href="#">Forgot password?</a>
        </div>
        <button type="submit" class="btn">Login</button>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </form>
    </div>
</body>
</html>
