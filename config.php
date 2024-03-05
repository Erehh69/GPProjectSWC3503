<?php
// Replace with your MySQL database configuration
$host = "localhost";
$username = "root";
$password = "mysql";
$database = "ledgerapp";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
