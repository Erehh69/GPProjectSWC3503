<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is an admin
$user_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM users WHERE id = '$user_id' AND role = 'admin'";
$admin_result = $conn->query($admin_query);

if ($admin_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch all users for display
$user_query = "SELECT * FROM users";
$user_result = $conn->query($user_query);
$users = $user_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Promote Users to Admin
        if (isset($_POST['promote_user_id'])) {
            $promote_user_id = $_POST['promote_user_id'];

            // Update user's role to 'admin'
            $update_query = "UPDATE users SET role = 'admin' WHERE id = '$promote_user_id'";
            $conn->query($update_query);
        }

        // Add Category
        if (isset($_POST['add_category'])) {
            $category_name = $_POST['category_name'];

            // Insert category into the database
            $insert_category_query = "INSERT INTO categories (name) VALUES ('$category_name')";
            $conn->query($insert_category_query);
        }

        // Edit Ledger Entry
        if (isset($_POST['edit_entry'])) {
            $entry_id = $_POST['entry_id'];
            $new_description = $_POST['new_description'];
            $new_amount = $_POST['new_amount'];

            // Update ledger entry in the database
            $update_ledger_query = "UPDATE ledger_entries
                                    SET description = '$new_description', amount = '$new_amount'
                                    WHERE id = '$entry_id'";
            $conn->query($update_ledger_query);
        }


    // Add Category
    if (isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'];

        // Insert category into the database
        $insert_category_query = "INSERT INTO categories (name) VALUES ('$category_name')";
        $conn->query($insert_category_query);
    }

    // Edit Ledger Entry
    if (isset($_POST['edit_entry'])) {
        $entry_id = $_POST['entry_id'];
        $new_description = $_POST['new_description'];
        $new_amount = $_POST['new_amount'];

        // Update ledger entry in the database
        $update_ledger_query = "UPDATE ledger_entries
                                SET description = '$new_description', amount = '$new_amount'
                                WHERE id = '$entry_id'";
        $conn->query($update_ledger_query);
    }
}

// Fetch all categories for display
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);
$categories = $category_result->fetch_all(MYSQLI_ASSOC);

// Fetch all ledger entries for display
$ledger_query = "SELECT * FROM ledger_entries";
$ledger_result = $conn->query($ledger_query);
$ledger_entries = $ledger_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Ledger Website</title>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    <p>User: <?php echo $_SESSION['user_id']; ?> (Admin)</p>

    <h2>Promote Users to Admin</h2>
    <form action="admin.php" method="post">
        <label for="promote_user_id">Select User:</label>
        <select name="promote_user_id" required>
        <?php foreach ($users as $user) { ?>
            <option value="<?php echo $user['id']; ?>">
                <?php echo $user['username']; ?> (<?php echo $user['role']; ?>)
            </option>
        <?php } ?>
        </select>
    <br>
    <button type="submit">Promote to Admin</button>
    </form>


    <h2>Add Category</h2>
    <form action="admin.php" method="post">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <h2>Edit Ledger Entry</h2>
    <form action="admin.php" method="post">
        <label for="entry_id">Select Entry to Edit:</label>
        <select name="entry_id" required>
            <?php foreach ($ledger_entries as $entry) { ?>
                <option value="<?php echo $entry['id']; ?>">
                    <?php echo $entry['description']; ?> - <?php echo $entry['amount']; ?> USD
                </option>
            <?php } ?>
        </select>
        <br>
        <label for="new_description">New Description:</label>
        <input type="text" name="new_description" required>
        <br>
        <label for="new_amount">New Amount:</label>
        <input type="number" name="new_amount" step="0.01" required>
        <br>
        <button type="submit" name="edit_entry">Edit Entry</button>
    </form>

    <h2>Categories</h2>
    <!-- Display categories as before -->

    <h2>Ledger Entries</h2>
    <!-- Display ledger entries as before -->

    <!-- Add New Ledger Entry form as before -->

    <a href="logout.php">Logout</a>
</body>
</html>
