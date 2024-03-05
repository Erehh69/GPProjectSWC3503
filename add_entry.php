<?php
session_start();
require_once 'config.php';

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
