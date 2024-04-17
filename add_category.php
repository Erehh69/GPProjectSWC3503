<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['new_verification_code'])) {
    // Redirect to the login page if user ID or verification code is not set
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['category_name'];

    // Insert category into the database (replace with prepared statements)
    $insert_query = "INSERT INTO categories (name) VALUES ('$category_name')";
    $conn->query($insert_query);

    header("Location: admin.php");
    exit();
}
?>
