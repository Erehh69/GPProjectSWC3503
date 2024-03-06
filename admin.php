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

    // Delete User
    if (isset($_POST['delete_user_id'])) {
        $delete_user_id = $_POST['delete_user_id'];

        // Delete user from the database
        $delete_query = "DELETE FROM users WHERE id = '$delete_user_id'";
        $conn->query($delete_query);
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
        $new_type = $_POST['new_type'];
        $new_date = $_POST['new_date'];

        // Update ledger entry in the database
        $update_ledger_query = "UPDATE ledger_entries
                                SET description = '$new_description', amount = '$new_amount', type = '$new_type', date = '$new_date'
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: #fff;
        }

        h2 {
            color: #333;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #45a049;
        }

        a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
        }

        a:hover {
            color: #4caf50;
        }

        .debit-section,
        .credit-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .admin-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .admin-actions form {
            width: 30%;
        }

        .admin-actions select {
            width: 100%;
            margin-bottom: 10px;
        }

        .admin-actions button {
            width: 100%;
        }

        .ledger-table {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .ledger-table form {
            width: 100%;
        }

        .ledger-table select {
            margin-bottom: 10px;
        }

        .ledger-table button {
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
        <p>User: <?php echo $_SESSION['user_id']; ?> (Admin)</p>
        <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
    </header>

    <section>
        <div class="admin-actions">
            <form action="admin.php" method="post">
                <h2>Promote Users to Admin</h2>
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

            <form action="admin.php" method="post">
                <h2>Delete User</h2>
                <label for="delete_user_id">Select User:</label>
                <select name="delete_user_id" required>
                <?php foreach ($users as $user) { ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php echo $user['username']; ?> (<?php echo $user['role']; ?>)
                    </option>
                <?php } ?>
                </select>
                <br>
                <button type="submit">Delete User</button>
            </form>
        </div>

        <div class="admin-actions">
            <form action="admin.php" method="post">
                <h2>Add Category</h2>
                <label for="category_name">Category Name:</label>
                <input type="text" name="category_name" required>
                <br>
                <button type="submit" name="add_category">Add Category</button>
            </form>

            <form action="admin.php" method="post">
                <h2>Edit Ledger Entry</h2>
                <label for="entry_id">Select Entry to Edit:</label>
                <select name="entry_id" required>
                    <?php foreach ($ledger_entries as $entry) { ?>
                        <option value="<?php echo $entry['id']; ?>">
                            <?php echo $entry['description']; ?> - <?php echo $entry['amount']; ?> MYR
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
                <label for="new_type">New Type:</label>
                <select name="new_type" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
                <br>
                <label for="new_date">New Date:</label>
                <input type="datetime-local" name="new_date" required>
                <br>
                <button type="submit" name="edit_entry">Edit Entry</button>
            </form>
        </div>

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

        <h2>Ledger Entries</h2>
        <div class="ledger-table">
            <form action="admin.php" method="post">
                <h3>Debit Entries</h3>
                <select name="entry_id" required>
                    <?php foreach ($ledger_entries as $entry) { ?>
                        <?php if ($entry['type'] === 'debit') { ?>
                            <option value="<?php echo $entry['id']; ?>">
                                <?php echo $entry['description']; ?> - <?php echo $entry['amount']; ?> MYR
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <br>
                <label for="new_description">New Description:</label>
                <input type="text" name="new_description" required>
                <br>
                <label for="new_amount">New Amount:</label>
                <input type="number" name="new_amount" step="0.01" required>
                <br>
                <label for="new_type">New Type:</label>
                <select name="new_type" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
                <br>
                <label for="new_date">New Date:</label>
                <input type="datetime-local" name="new_date" required>
                <br>
                <button type="submit" name="edit_entry">Edit Entry</button>
            </form>

            <form action="admin.php" method="post">
                <h3>Credit Entries</h3>
                <select name="entry_id" required>
                    <?php foreach ($ledger_entries as $entry) { ?>
                        <?php if ($entry['type'] === 'credit') { ?>
                            <option value="<?php echo $entry['id']; ?>">
                                <?php echo $entry['description']; ?> - <?php echo $entry['amount']; ?> MYR
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <br>
                <label for="new_description">New Description:</label>
                <input type="text" name="new_description" required>
                <br>
                <label for="new_amount">New Amount:</label>
                <input type="number" name="new_amount" step="0.01" required>
                <br>
                <label for="new_type">New Type:</label>
                <select name="new_type" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
                <br>
                <label for="new_date">New Date:</label>
                <input type="datetime-local" name="new_date" required>
                <br>
                <button type="submit" name="edit_entry">Edit Entry</button>
            </form>
        </div>

        <h2>Ledger Entries</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            <?php foreach ($ledger_entries as $entry) { ?>
                <tr>
                    <td><?php echo $entry['id']; ?></td>
                    <td><?php echo $entry['description']; ?></td>
                    <td><?php echo $entry['amount']; ?> MYR</td>
                    <td><?php echo $entry['type']; ?></td>
                    <td><?php echo $entry['date']; ?></td>
                </tr>
            <?php } ?>
        </table>

        <!-- Add New Ledger Entry form as before -->
        <form action="dashboard.php" method="post">
            <h2>Add Ledger Entry</h2>
            <label for="description">Description:</label>
            <input type="text" name="description" required>
            <br>
            <label for="amount">Amount:</label>
            <input type="number" name="amount" step="0.01" required>
            <br>
            <label for="type">Type:</label>
            <select name="type" required>
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
            </select>
            <br>
            <label for="category">Category:</label>
            <select name="category" required>
                <?php
                $category_query = "SELECT * FROM categories";
                $category_result = $conn->query($category_query);

                if ($category_result) {
                    $categories = $category_result->fetch_all(MYSQLI_ASSOC);

                    foreach ($categories as $category) {
                        echo "<option value='" . $category['id'] . "'>" . $category['name'] . "</option>";
                    }
                } else {
                    // Handle query error
                    echo "Error executing category query: " . $conn->error;
                }
                ?>
            </select>
            <br>
            <label for="date">Date:</label>
            <input type="datetime-local" name="date" required>
            <br>
            <button type="submit" name="add_entry">Add Entry</button>


        </form>
    </section>
</body>
</html>
