<?php

session_start();
require_once 'config.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to unauthorized.php
    header("Location: unauthorized.php");
    exit(); // Stop further execution
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_query);

// Check if the query was successful before fetching
if ($user_result) {
    $user_data = $user_result->fetch_assoc();
    $user_role = $user_data['role'];
} else {
    // Handle query error (You might want to log or display an error message)
    die("Error executing user query: " . $conn->error);
}

// Calculate total debit amount
$total_debit = 0;
// Debit query to retrieve debit entries
$debit_query = "SELECT le.id, le.description, le.amount, c.name AS category_name, le.date
                FROM ledger_entries le
                JOIN categories c ON le.category_id = c.id
                WHERE le.type = 'debit'";
$debit_result = $conn->query($debit_query);

if ($debit_result) {
    $debit_entries = $debit_result->fetch_all(MYSQLI_ASSOC);

    foreach ($debit_entries as $entry) {
        $total_debit += $entry['amount'];
    }
} else {
    // Handle query error
    echo "Error executing debit query: " . $conn->error;
}

// Calculate total credit amount
$total_credit = 0;
// Credit query to retrieve credit entries
$credit_query = "SELECT le.id, le.description, le.amount, c.name AS category_name, le.date
                FROM ledger_entries le
                JOIN categories c ON le.category_id = c.id
                WHERE le.type = 'credit'";
$credit_result = $conn->query($credit_query);

if ($credit_result) {
    $credit_entries = $credit_result->fetch_all(MYSQLI_ASSOC);

    foreach ($credit_entries as $entry) {
        $total_credit += $entry['amount'];
    }
} else {
    // Handle query error
    echo "Error executing credit query: " . $conn->error;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle adding new ledger entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category_id = $_POST['category'];
    $date = $_POST['date']; // Use the provided date input field

    // Insert new ledger entry
    $insert_query = "INSERT INTO ledger_entries (description, amount, type, category_id, date)
                     VALUES ('$description', '$amount', '$type', '$category_id', '$date')";
    $insert_result = $conn->query($insert_query);

    // Check if the query was successful
    if (!$insert_result) {
        // Handle query error (You might want to log or display an error message)
        die("Error adding ledger entry: " . $conn->error);
    }

    // Refresh the page to display the updated ledger
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Ledger Website</title>
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
    </style>
</head>
<body>
    <header>
        <h1>User Dashboard</h1>
        <p>User: <?php echo $user_data['username']; ?> (<?php echo $user_role; ?>)</p>
        <a href="?logout">Logout</a>
    </header>

    <section>
        <div class="debit-section">
            <h2>Debit Entries</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Date</th>
                </tr>
                <?php
                if ($debit_result) {
                    foreach ($debit_entries as $entry) {
                        ?>
                        <tr>
                            <td><?php echo $entry['id']; ?></td>
                            <td><?php echo $entry['description']; ?></td>
                            <td>MYR <?php echo $entry['amount']; ?></td>
                            <td><?php echo $entry['category_name']; ?></td>
                            <td><?php echo $entry['date']; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    // Handle query error
                    echo "Error executing debit query: " . $conn->error;
                }
                ?>
            </table>
        </div>

        <div class="credit-section">
            <h2>Credit Entries</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Description</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Date</th>
            </tr>
            <?php
            if ($credit_result) {
                foreach ($credit_entries as $entry) {
                    ?>
                    <tr>
                        <td><?php echo $entry['id']; ?></td>
                        <td><?php echo $entry['description']; ?></td>
                        <td>MYR <?php echo $entry['amount']; ?></td>
                        <td><?php echo $entry['category_name']; ?></td>
                        <td><?php echo $entry['date']; ?></td>
                    </tr>
                    <?php
                }
            } else {
                // Handle query error
                echo "Error executing credit query: " . $conn->error;
            }
            ?>
        </table>
    </div>

    <div class="totals-section">
        <h2>Total Debit and Credit</h2>
        <p>Total Debit: MYR <?php echo $total_debit; ?></p>
        <p>Total Credit: MYR <?php echo $total_credit; ?></p>
    </div>

    <?php if ($user_role === 'admin' || $user_role === 'user') { ?>
        <!-- All users can add new ledger entries -->
        <h2>Add Ledger Entry</h2>
        <form action="dashboard.php" method="post">
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
    <?php } ?>
</section>
</body>
</html>

