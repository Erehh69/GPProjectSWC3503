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
    $entry_id = $_POST['entry_id'];
    $new_description = $_POST['new_description'];
    $new_amount = $_POST['new_amount'];

    // Update ledger entry in the database (replace with prepared statements)
    $update_query = "UPDATE ledger_entries 
                     SET description = '$new_description', amount = '$new_amount' 
                     WHERE id = '$entry_id'";
    $conn->query($update_query);

    header("Location: admin.php");
    exit();
}
?>
