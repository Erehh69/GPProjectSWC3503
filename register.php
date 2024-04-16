<?php
session_start();
require_once 'config.php'; // Include the database configuration file

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
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
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username = ? OR user_email = ?";
        $stmt = $conn->prepare($check_query);
        if (!$stmt) {
            // Failed to prepare the statement
            $error = "Error preparing the statement: " . $conn->error;
        } else {
            // Continue with the execution of the prepared statement
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username or email already exists";
            } else {
                // Generate a random verification code
                $verification_code = generateVerificationCode();

                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
                $insert_query = "INSERT INTO users (username, password, user_email, email_verification_code, email_verified) VALUES (?, ?, ?, ?, 0)";
                $stmt = $conn->prepare($insert_query);
                if (!$stmt) {
                    // Failed to prepare the statement
                    $error = "Error preparing the statement: " . $conn->error;
                } else {
                    // Continue with the execution of the prepared statement
                    $stmt->bind_param("ssss", $username, $hashed_password, $email, $verification_code);
                    if ($stmt->execute()) {
                        // Registration successful
                        // Send verification email
                        sendVerificationEmail($email, $verification_code);
                        $_SESSION['user_count']++;
                        // Redirect to login page
                        header("Location: login.php");
                        exit();
                    } else {
                        // Registration failed
                        $error = "Error executing the prepared statement: " . $stmt->error;
                    }
                }
            }
        }
    }
}

// Function to generate a random verification code
function generateVerificationCode($length = 6) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

// Function to send verification email
function sendVerificationEmail($email, $verification_code) {
    $subject = "Verify Your Email";
    $message = "Thank you for registering. Your verification code is: $verification_code";

    // Send email
    if (mail($email, $subject, $message)) {
        // Email sent successfully
        return true;
    } else {
        // Email sending failed
        return false;
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

            <form action="register.php" method="post">
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
                    <input type="email" name="email" id="email" placeholder="Email" autocomplete="email" required>
                </div>
                <div class="button">
                    <input type="submit" class="btn" value="Register">
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
