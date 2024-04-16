<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['new_verification_code'])) {
    header("Location: login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = $_POST['verification_code'];
    $stored_verification_code = $_SESSION['new_verification_code'];

    // Verify the entered verification code
    if ($verification_code === $stored_verification_code) {
        // Fetch role from the database based on user ID
        $user_id = $_SESSION['user_id'];
        $role_query = "SELECT role FROM users WHERE id = ?";
        $stmt = $conn->prepare($role_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $_SESSION['role'] = $row['role'];
        }

        // Redirect based on user role
        if ($_SESSION['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid verification code";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify - Ledger Website</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <?php if (!empty($error)) { ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php } ?>

    <div class="wrapper">
    <form id="verify-form" action="verify.php" method="post">
        <h1>Enter Verification Code</h1>
        <div class="input-box">
            <input type="text" id="verification_code" placeholder="Verification Code" name="verification_code" required>
            <i class='bx bxs-key'></i>
        </div>
        <button type="submit" class="btn">Verify</button>
    </form>
    </div>

</body>
</html>
