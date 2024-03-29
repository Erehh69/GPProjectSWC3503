<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php'; // Include the GoogleAuthenticator library

use PHPGangsta\GoogleAuthenticator\GoogleAuthenticator;

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate username (alphanumeric characters only)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $error = "Invalid username format (alphanumeric characters only)";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Validate username uniqueness (you should do more checks)
        $check_query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($check_query);

        if ($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Hash the password using SHA-256
            $hashed_password = hash('sha256', $password);

            // Set default role to 'user'
            $role = 'user';

            // For simplicity, the first registered user will be assigned the role of 'admin'
            if ($_SESSION['user_count'] === 0) {
                $role = 'admin';
            }

            // Insert user into the database (replace with prepared statements)
            $insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
            $result = $conn->query($insert_query);

            if (!$result) {
                $error = "Error registering the user. Please try again.";
            } else {
                $_SESSION['user_count']++;

                header("Location: login.php");
                exit();
            }
        }
    }
    // Generate secret key for 2FA
    $ga = new GoogleAuthenticator();
    $secret = $ga->createSecret();

    // Store the secret key in the database
    $hashed_password = hash('sha256', $password);
    $insert_query = "INSERT INTO users (username, password, secret_key) VALUES ('$username', '$hashed_password', '$secret')";
    $result = $conn->query($insert_query);

    if ($result) {
        // Display QR code for the user to scan
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('LedgerWebsite', $secret);
        echo "Scan the QR code below using an authenticator app:<br>";
        echo "<img src='$qrCodeUrl' alt='QR Code'><br>";

        echo "Secret Key (for manual entry): $secret<br>";

        echo "<a href='login.php'>Proceed to Login</a>";
        exit();
    } else {
        $error = "Error registering the user. Please try again.";
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
        <label for="username">(Alphanumeric characters only):</label>
        <input type="text" name="username" placeholder="Username" pattern="[a-zA-Z0-9]+" required>
        </div>
        <div class="input-box">
        <i class='bx bxs-lock-alt' ></i>
        <br>
        <label for="password">Password (at least 8 characters):</label>
        <input type="password" name="password" placeholder="Password" minlength="8" required>
        </div>
        <div class="input-box">
        <i class='bx bxs-envelope'></i>
        <input type="email" name="Email" placeholder="Email">
        </div>
        <div class="button">
            <input type="submit" class="btn" value="Register">
        </div>
        <div class="group">
            <span><a href="#">Forget password</a></span>
            <span><a href="login.php">Login</a></span>
        </div>
    </form>
</body>
</html>
