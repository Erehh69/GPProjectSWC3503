<?php
// Include your database connection code
require_once 'config.php';

// Your update queries
//$updateQuery1 = "UPDATE users SET role = 'user' WHERE id = 4;";
//$updateQuery2 = "UPDATE some_table SET column_name = 'new_value' WHERE condition;";

// Execute the update queries
if ($conn->query($updateQuery1) === TRUE) {
    echo "Query 1 executed successfully.<br>";
} else {
    echo "Error executing query 1: " . $conn->error . "<br>";
}

/*if ($conn->query($updateQuery2) === TRUE) {
    echo "Query 2 executed successfully.<br>";
} else {
    echo "Error executing query 2: " . $conn->error . "<br>";
}*/

// Close the database connection
$conn->close();
?>
