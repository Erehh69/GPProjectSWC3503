<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php'; // Include the GoogleAuthenticator library

use PHPGangsta\GoogleAuthenticator;

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve form data
$username = $_POST['username'];
$totp_code = $_POST['totp_code'];

// Fetch the secret key associated with the user from the database
$fetch_query = "SELECT secret_key FROM users WHERE username = ?";
$stmt = $conn->prepare($fetch_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $secret_key = $row['secret_key'];
} else {
    // Handle error if user not found
    echo "User not found";
    exit();
}

// Verify the TOTP code
$ga = new GoogleAuthenticator();
$isValidTotp = $ga->verifyCode($secret_key, $totp_code);

if ($isValidTotp) {
    // Update the user's account to indicate that 2FA is enabled
    $update_query = "UPDATE users SET is_2fa_enabled = 1 WHERE username = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Redirect the user to the dashboard or another appropriate page
    header("Location: dashboard.php");
    exit();
} else {
    // Handle error if TOTP code is invalid
    echo "Invalid TOTP code";
    exit();
}
?>
