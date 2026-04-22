<?php
session_start();
include 'config.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status']; // "Done" or "Pending"

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php"); // Refresh page
    exit();
}

// Handle order deletion
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php"); // Refresh page
    exit();
}

// Fetch all orders
$query = "SELECT * FROM orders ORDER BY order_id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | PastryTreats</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
    <li><a href="dashboard.php">Inventory</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="analytics.php">Analytics</a></li>
        <li><a href="adminaccount.php">Account</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <h1>Orders</h1>

    <?php if ($result->num_rows > 0) { ?>
        <table class="table">
            <tr>
                <th>User Email</th>
                <th>Mobile No.</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Order Date Placed</th> <!-- New Column -->
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_email']) ?></td>
                    <td><?= htmlspecialchars($row['mobile_no']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td>
                        <span class="<?= ($row['status'] == 'Pending') ? 'pending' : 'done' ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td> <!-- New Data -->
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                            <select name="new_status">
                                <option value="Pending" <?= ($row['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="Done" <?= ($row['status'] == 'Done') ? 'selected' : '' ?>>Done</option>
                            </select>
                            <button type="submit" name="update_status">✔ Update</button>
                        </form>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                            <button type="submit" name="delete_order" onclick="return confirm('Are you sure?')">🗑️ Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p class="no-orders">There are no orders at the moment.</p>
    <?php } ?>
</div>

<style>
    body {
        display: flex;
        margin: 0;
    }
    .sidebar { 
        width: 200px; background: #ffb6c1; padding: 20px;
        min-height: 100vh;
        overflow: hidden;
    transition: width 0.3s ease-in-out; /* Smooth transition */}
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar ul li { margin: 20px 0; }
    .sidebar ul li a { text-decoration: none; color: white; font-size: 18px; }
    
    .main-content {
        flex: 1;
        padding: 20px;
    }
    .table {
    width: 100%;
    border-collapse: collapse; /* Ensures borders don't double */
    border: 1px solid #ddd; /* Adds outer border */
}
.no-orders {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        color: #000;
        padding: 20px;
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
        margin-top: 20px;
}

.table th, .table td {
    border: 1px solid #bbb; /* Gridlines for each cell */
    padding: 10px;
    text-align: center;
}

.table th {
    background:rgb(0, 0, 0);
    color: white;
}
    .pending {
        color: red;
        font-weight: bold;
    }
    .done {
        color: green;
        font-weight: bold;
    }
    button {
        margin: 5px;
        padding: 5px 10px;
        cursor: pointer;
    }
  

</style>

</body>
</html>
