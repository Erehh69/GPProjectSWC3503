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

// Retrieve the username from the query parameters
$username = $_GET['username'];

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

// Generate QR code URL for the authenticator app
$ga = new GoogleAuthenticator();
$qrCodeUrl = $ga->getQRCodeGoogleUrl($username, $secret_key, 'LedgerWebsite');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA - Ledger Website</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h1>Setup Two-Factor Authentication (2FA)</h1>
        <p>Scan the QR code below using your authenticator app or manually enter the code to set up 2FA.</p>
        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
        <p>Manual setup code: <?php echo $secret_key; ?></p>
        <p><strong>Note:</strong> Store this secret key in a safe place. It will be used to recover your account if you lose access to your authenticator app.</p>
        <form action="verify_2fa.php" method="post">
            <input type="text" name="totp_code" placeholder="Enter TOTP code" required>
            <input type="hidden" name="username" value="<?php echo $username; ?>">
            <button type="submit">Verify and Complete Setup</button>
        </form>
    </div>
</body>
</html>
