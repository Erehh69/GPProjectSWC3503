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
    $category_id = $_POST['category'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    // Insert ledger entry into the database (replace with prepared statements)
    $insert_query = "INSERT INTO ledger_entries (category_id, description, amount, date) 
                     VALUES ('$category_id', '$description', '$amount', '$date')";
    $conn->query($insert_query);

    header("Location: dashboard.php");
    exit();
}
?>
