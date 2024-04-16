<?php
session_start();
require_once 'config.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to unauthorized.php
    header("Location: unauthorized.php");
    exit(); // Stop further execution
}

// Check if the user is an admin
$user_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM users WHERE id = '$user_id' AND role = 'admin'";
$admin_result = $conn->query($admin_query);

if ($admin_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

// Function to promote a user to admin
if (isset($_POST['promote_user'])) {
    $user_id = $_POST['promote_user'];
    $promote_query = "UPDATE users SET role = 'admin' WHERE id = '$user_id'";
    $conn->query($promote_query);
}

// Function to delete a user
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['delete_user'];
    $delete_query = "DELETE FROM users WHERE id = '$user_id'";
    $conn->query($delete_query);
}

// Function to add a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $insert_query = "INSERT INTO categories (name) VALUES ('$category_name')";
    $conn->query($insert_query);
}

// Function to update a ledger entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_entry'])) {
    $entry_id = $_POST['entry_id'];
    $description = $_POST['edit_description'];
    $amount = $_POST['edit_amount'];
    $type = $_POST['edit_type'];
    $date = $_POST['edit_date'];

    $update_query = "UPDATE ledger_entries SET description = '$description', amount = '$amount', type = '$type', date = '$date' WHERE id = '$entry_id'";
    $conn->query($update_query);
}

// Fetch all users for display
$user_query = "SELECT * FROM users";
$user_result = $conn->query($user_query);
$users = $user_result->fetch_all(MYSQLI_ASSOC);

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
        /* CSS styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        section {
            margin: 20px;
            display: flex; /* Use flexbox for layout */
            flex-wrap: wrap; /* Allow wrapping of flex items */
            justify-content: space-between; /* Distribute items evenly */
        }

        .admin-actions {
            width: 48%; /* Adjust width for better layout */
            margin-bottom: 20px;
        }

        .admin-actions form {
            width: 100%;
        }

        .admin-actions table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .admin-actions th, .admin-actions td {
            border: 1px solid #ddd;
            padding: 8px; /* Adjust padding */
            text-align: left;
        }

        .admin-actions th {
            background-color: #333;
            color: #fff;
        }

        .admin-actions button {
            width: calc(50% - 4px); /* Adjust button width */
            margin-right: 2%; /* Add margin between buttons */
        }

        .admin-actions:last-child {
            margin-right: 0; /* Remove margin for the last item */
        }

        /* Popup Form Styles */
        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%; /* Adjust width */
            border-radius: 8px;
        }

        .popup-content input[type=text],
        .popup-content input[type=date],
        .popup-content select {
            width: calc(100% - 24px); /* Adjust width */
            padding: 8px; /* Adjust padding */
            margin: 6px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .popup-content button[type=submit],
        .popup-content button[type=button] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px; /* Adjust padding */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .popup-content button[type=submit]:hover,
        .popup-content button[type=button]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Admin Panel</h1>
        <p>User: <?php echo $_SESSION['user_id']; ?> (Admin)</p>
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </header>

    <!-- Main Section -->
    <section>
        <!-- User Management -->
        <div class="admin-actions">
            <form action="admin.php" method="post">
                <h2>User Management</h2>
                <table>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td>
                                <?php echo $user['username']; ?> (<?php echo $user['role']; ?>)
                            </td>
                            <td>
                                <input type="hidden" name="promote_user" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="promote_user">Promote to Admin</button>
                                <button type="submit" name="delete_user">Delete User</button>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </form>
        </div>

        <!-- Edit Ledger Entry -->
        <div class="admin-actions">
            <h2>Edit Ledger Entry</h2>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($ledger_entries as $entry) { ?>
                    <tr>
                        <td><?php echo $entry['description']; ?></td>
                        <td><?php echo $entry['amount']; ?> MYR</td>
                        <td><?php echo $entry['type']; ?></td>
                        <td><?php echo $entry['date']; ?></td>
                        <td>
                            <button type="button" onclick="showPopup(<?php echo $entry['id']; ?>)">Edit Entry</button>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Add Category -->
        <div class="admin-actions">
            <form action="admin.php" method="post">
                <h2>Add Category</h2>
                <label for="category_name">Category Name:</label>
                <input type="text" name="category_name" required>
                <br>
                <button type="submit" name="add_category">Add Category</button>
            </form>
        </div>

        <!-- Categories -->
        <div class="admin-actions">
            <h2>Categories</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                </tr>
                <?php foreach ($categories as $category) { ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><?php echo $category['name']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Popup Form for Editing Ledger Entry -->
        <div id="popupForm" class="popup">
            <div class="popup-content">
                <form action="" method="post">
                    <h2>Edit Ledger Entry</h2>
                    <input type="hidden" id="edit_entry_id" name="entry_id" value="">
                    <label for="edit_description">Description:</label>
                    <input type="text" id="edit_description" name="edit_description" required>
                    <label for="edit_amount">Amount:</label>
                    <input type="text" id="edit_amount" name="edit_amount" required>
                    <label for="edit_type">Type:</label>
                    <select id="edit_type" name="edit_type" required>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                    <label for="edit_date">Date:</label>
                    <input type="date" id="edit_date" name="edit_date" required>
                    <button type="submit" name="update_entry">Update Entry</button>
                    <button type="button" onclick="hidePopup()">Close</button>
                </form>
            </div>
        </div>

        <!-- JavaScript to Show/Hide Popup -->
        <script>
            function showPopup(entry_id) {
                document.getElementById('edit_entry_id').value = entry_id;
                document.getElementById('popupForm').style.display = 'block';
            }
            function hidePopup() {
                document.getElementById('popupForm').style.display = 'none';
            }
        </script>
    </section>
</body>
</html>
