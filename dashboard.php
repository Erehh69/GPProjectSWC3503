<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories for display
$category_query = "SELECT * FROM categories";
$categories_result = $conn->query($category_query);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Fetch ledger entries for display
$ledger_query = "SELECT * FROM ledger_entries";
$ledger_result = $conn->query($ledger_query);
$ledger_entries = $ledger_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ledger Website</title>
</head>
<body>
    <h1>Welcome to the Dashboard</h1>
    <p>User: <?php echo $_SESSION['user_id']; ?></p>

    <h2>Categories</h2>
    <ul>
        <?php foreach ($categories as $category) { ?>
            <li><?php echo $category['name']; ?></li>
        <?php } ?>
    </ul>

    <h2>Ledger Entries</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ledger_entries as $entry) { ?>
                <tr>
                    <td><?php echo $entry['category_id']; ?></td>
                    <td><?php echo $entry['description']; ?></td>
                    <td><?php echo $entry['amount']; ?></td>
                    <td><?php echo $entry['date']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Add a form for adding new ledger entries -->
    <h2>Add New Ledger Entry</h2>
    <form action="add_entry.php" method="post">
        <label for="category">Category:</label>
        <select name="category" required>
            <?php foreach ($categories as $category) { ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <label for="description">Description:</label>
        <input type="text" name="description" required>
        <br>
        <label for="amount">Amount:</label>
        <input type="number" name="amount" step="0.01" required>
        <br>
        <label for="date">Date:</label>
        <input type="date" name="date" required>
        <br>
        <button type="submit">Add Entry</button>
    </form>

    <a href="logout.php">Logout</a>
</body>
</html>
