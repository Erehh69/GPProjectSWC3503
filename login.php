<?php
session_start();
require_once 'config.php'; // Include the database configuration file

// Initialize error variable
$error = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username and password are provided
    if (!empty($username) && !empty($password)) {
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
                // Generate a new verification code
                $new_verification_code = generateVerificationCode();

                // Update the verification code in the database
                $update_query = "UPDATE users SET email_verification_code = ? WHERE username = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ss", $new_verification_code, $username);
                $update_stmt->execute();

                // Send verification code to user's email
                $to = $user['email'];
                $subject = 'Verification Code';
                $message = 'Your verification code is: ' . $new_verification_code;
                $headers = 'From: your_email@example.com';
                mail($to, $subject, $message, $headers);

                // Set session variables for the user ID and the new verification code
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['new_verification_code'] = $new_verification_code;

                // Redirect to verify.php for code verification
                header("Location: verify.php");
                exit();
            } else {
                // Invalid username or password
                $error = "Invalid username or password";
            }
        } else {
            // User not found
            $error = "Invalid username or password";
        }
    } else {
        // Username or password not provided
        $error = "Please enter username and password";
    }
}

// Function to generate a random verification code
function generateVerificationCode($length = 6) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
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

    <?php if (!empty($error)) { ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php } ?>

    <div class="wrapper">
    <form id="login-form" action="login.php" method="post">
        <h1>Login</h1>
        <div class="input-box">
            <input type="text" id="username" placeholder="Username" name="username" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
            <input type="password" id="password" placeholder="Password" name="password" required>
            <i class='bx bxs-lock-alt'></i>
        </div>
        <button type="submit" class="btn">Login</button>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </form>
    </div>

</body>
</html>
