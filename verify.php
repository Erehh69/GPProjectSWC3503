<?php
// Start Server Session
session_start();

// Display All Errors (For Easier Development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the verification code is set in the session
if (!isset($_SESSION['new_verification_code'])) {
    // If not set, return an error response
    $response = new stdClass();
    $response->error = "Verification code not found in session";
    echo json_encode($response);
    exit;
}

// Include the necessary files
require_once 'config.php';

// Retrieve user data from the session
$user_id = $_SESSION['user_id'];

// Retrieve user role from the database
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $user_role = $user['role'];

    // Clear the stored verification code from session
    unset($_SESSION['new_verification_code']);

    // Redirect based on user role
    if ($user_role == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
} else {
    // If user not found or error in retrieving role, return an error response
    $response = new stdClass();
    $response->error = "User not found or error retrieving role";
    echo json_encode($response);
    exit;
}
?>
