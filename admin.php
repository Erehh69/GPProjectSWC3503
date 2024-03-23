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

// Fetch all categories for display
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);
$categories = $category_result->fetch_all(MYSQLI_ASSOC);

// Fetch all ledger entries for display
$ledger_query = "SELECT * FROM ledger_entries";
$ledger_result = $conn->query($ledger_query);
$ledger_entries = $ledger_result->fetch_all(MYSQLI_ASSOC);

// Function to display pop-up form
function displayPopupForm($entry_id, $description, $amount, $type, $date) {
    echo "<div id='popupForm' class='popup'>";
    echo "<form class='popup-content' action='update_entry.php' method='post'>";
    echo "<h2>Edit Ledger Entry</h2>";
    echo "<input type='hidden' name='entry_id' value='$entry_id'>";
    echo "<label for='edit_description'>Description:</label>";
    echo "<input type='text' id='edit_description' name='edit_description' value='$description'>";
    echo "<label for='edit_amount'>Amount:</label>";
    echo "<input type='text' id='edit_amount' name='edit_amount' value='$amount'>";
    echo "<label for='edit_type'>Type:</label>";
    echo "<select id='edit_type' name='edit_type'>";
    echo "<option value='debit' " . ($type == 'debit' ? 'selected' : '') . ">Debit</option>";
    echo "<option value='credit' " . ($type == 'credit' ? 'selected' : '') . ">Credit</option>";
    echo "</select>";
    echo "<label for='edit_date'>Date:</label>";
    echo "<input type='date' id='edit_date' name='edit_date' value='$date'>";
    echo "<button type='submit' name='update_entry'>Update Entry</button>";
    echo "</form>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Ledger Website</title>
    <style>
        /* CSS styles */
        /* This section contains the CSS styles for the entire page. */

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
            width: calc(50% - 20px); /* Set width to 50% minus margin */
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
            padding: 12px;
            text-align: left;
        }

        .admin-actions th {
            background-color: #333;
            color: #fff;
        }

        .admin-actions button {
            width: 48%; /* Adjust button width */
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
            width: 40%;
            border-radius: 8px;
        }

        .popup-content input[type=text],
        .popup-content input[type=date],
        .popup-content select {
            width: 100%;
            padding: 12px;
            margin: 6px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .popup-content button[type=submit] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .popup-content button[type=submit]:hover {
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
                                <?php
                                    // Display pop-up form for editing
                                    displayPopupForm($entry['id'], $entry['description'], $entry['amount'], $entry['type'], $entry['date']);
                                ?>
                                <button onclick="document.getElementById('popupForm').style.display='block'">Edit Entry</button>
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
    </section>
</body>
</html>

