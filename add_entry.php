<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and verified
if (!isset($_SESSION['user_id']) || !isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    // Redirect to the verification page if the user is not logged in or verified
    header("Location: unauthorized.php");
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
