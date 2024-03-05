<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['category_name'];

    // Insert category into the database (replace with prepared statements)
    $insert_query = "INSERT INTO categories (name) VALUES ('$category_name')";
    $conn->query($insert_query);

    header("Location: admin.php");
    exit();
}
?>
