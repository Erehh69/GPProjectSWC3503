<?php
// Include your database connection code
require_once 'config.php';

// Your combined update query
$combinedUpdateQuery = "
    UPDATE users SET role = 'user' WHERE id = 2;
    UPDATE users SET role = 'user' WHERE id = 4;

";
//UPDATE some_table SET column_name = 'new_value' WHERE condition;

// Execute the combined update query
if ($conn->multi_query($combinedUpdateQuery)) {
    echo "Combined update queries executed successfully.<br>";
} else {
    echo "Error executing combined update queries: " . $conn->error . "<br>";
}

// Close the database connection
$conn->close();
?>
