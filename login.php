<?php
session_start();
require_once 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $verification_code = $_POST['verification_code'];

    // Retrieve user data including the verification code and email verification status
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Compare the provided verification code with the one stored in the database
            if ($verification_code === $user['email_verification_code'] && $user['email_verified'] == 1) {
                // Verification code matches and email is verified, proceed with login
                $_SESSION['user_id'] = $user['id'];
                // Redirect to dashboard or any other authenticated page
                header("Location: dashboard.php");
                exit();
            } else {
                // Invalid verification code or email not verified
                $error = "Invalid verification code or email not verified";
            }
        } else {
            // Invalid username or password
            $error = "Invalid username or password";
        }
    } else {
        // User not found
        $error = "Invalid username or password";
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
            <input type="text" placeholder="Username" name="username" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
            <input type="password" placeholder="Password" name="password" required>
            <i class='bx bxs-lock-alt'></i>
        </div>
        <div class="input-box">
            <input type="text" placeholder="Verification Code" name="verification_code" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </form>
    </div>
</body>
</html>
