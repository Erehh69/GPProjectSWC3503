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
    $category_name = $_POST['category_name'];

    // Insert category into the database (replace with prepared statements)
    $insert_query = "INSERT INTO categories (name) VALUES ('$category_name')";
    $conn->query($insert_query);

    header("Location: admin.php");
    exit();
}
?>
