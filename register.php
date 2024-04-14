<?php
session_start();
require_once 'config.php'; // Include the database configuration file
require_once 'vendor/autoload.php'; // Include the GoogleAuthenticator library

use PHPGangsta\GoogleAuthenticator;

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully";
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Validate username (alphanumeric characters only)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $error = "Invalid username format (alphanumeric characters only)";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Check if username already exists
        $check_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Hash the password using SHA-256
            $hashed_password = hash('sha256', $password);

            // Set default values for MFA related fields
            $mfa_enabled = 0;
            $role = 'user';
            $totp_secret_key = '';
            $totp_code = '';

            // Insert user into the database using prepared statements
            $insert_query = "INSERT INTO users (username, password, email, mfa_enabled, role, totp_secret_key, totp_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            if ($stmt) {
                if ($stmt->execute([$username, $hashed_password, $email, $mfa_enabled, $role, $totp_secret_key, $totp_code])) {
                    // Registration successful
                    $_SESSION['user_count']++;
                    // Redirect to 2FA setup page
                    header("Location: setup_2fa.php?username=$username");
                    exit();
                } else {
                    // Registration failed
                    $error = "Error executing the prepared statement: " . $stmt->error;
                }
            } else {
                // Failed to prepare the statement
                $error = "Error preparing the statement: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ledger Website</title>
    <link rel="stylesheet" href="style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <?php if (isset($error)) { ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php } ?>

            <form action="register.php" name="Formfill" method="post">
                <h1>Register</h1>
                <div class="input-box">
                    <i class='bx bxs-user'></i>
                    <br>
                    <label for="username">Username (Alphanumeric characters only):</label>
                    <input type="text" name="username" id="username" placeholder="Username" pattern="[a-zA-Z0-9]+" required autocomplete="username">
                </div>
                <div class="input-box">
                    <i class='bx bxs-lock-alt' ></i>
                    <br>
                    <label for="password">Password (at least 8 characters):</label>
                    <input type="password" name="password" id="password" placeholder="Password" minlength="8" required autocomplete="new-password">
                </div>
                <div class="input-box">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" name="email" id="email" placeholder="Email" autocomplete="email">
                </div>
                <div class="button">
                    <input type="submit" class="btn" name="Register">
                </div>
                <div class="group">
                    <span><a href="#">Forget password</a></span>
                    <span><a href="login.php">Login</a></span>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
