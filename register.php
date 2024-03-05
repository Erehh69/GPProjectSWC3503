<?php
session_start();
require_once 'config.php';

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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ledger Website</title>
</head>
<body>
    <h1>Register</h1>

    <?php if (isset($error)) { ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php } ?>

    <form action="register.php" method="post">
        <label for="username">Username (alphanumeric characters only):</label>
        <input type="text" name="username" pattern="[a-zA-Z0-9]+" required>
        <br>
        <label for="password">Password (at least 8 characters):</label>
        <input type="password" name="password" minlength="8" required>
        <br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
