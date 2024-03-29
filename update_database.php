<?php
// Include your database connection code
require_once 'config.php';

// Your combined update query
$combinedUpdateQuery = "

    ALTER TABLE users
    ADD totp_secret_key VARCHAR(50) NOT NULL,
    ADD totp_code VARCHAR(10) DEFAULT NULL;

";
//UPDATE some_table SET column_name = 'new_value' WHERE condition;
/*
    UPDATE users SET role = 'user' WHERE id = 2;
    UPDATE users SET role = 'user' WHERE id = 4;
*/

// Execute the combined update query
if ($conn->multi_query($combinedUpdateQuery)) {
    echo "Combined update queries executed successfully.<br>";
} else {
    echo "Error executing combined update queries: " . $conn->error . "<br>";
}

// Close the database connection
$conn->close();
?>
