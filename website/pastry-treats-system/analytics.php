<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Fetch product data
$query = "SELECT name, stock FROM products";
$result = $conn->query($query);

$productNames = [];
$stockLevels = [];

while ($row = $result->fetch_assoc()) {
    $productNames[] = $row['name'];
    $stockLevels[] = $row['stock'];
}

// Convert data to JSON for JavaScript
$productNamesJSON = json_encode($productNames);
$stockLevelsJSON = json_encode($stockLevels);

// Fetch sales data
$salesQuery = "SELECT product_name, SUM(quantity) as total_quantity, SUM(total_price) as total_sales, order_date FROM orders GROUP BY product_name, order_date ORDER BY order_date DESC";
$salesResult = $conn->query($salesQuery);
$salesData = [];
while ($row = $salesResult->fetch_assoc()) {
    $salesData[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics | PastryTreats</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </ul>
</div>

<div class="main-content">
    <h1>Product Analytics</h1>
    
    <div class="sales-summary">
        <h2>Sales Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity Sold</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesData as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['total_quantity']); ?></td>
                        <td>$<?php echo number_format($sale['total_sales'], 2); ?></td>
                        <td><?php echo htmlspecialchars($sale['order_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="print-btn" onclick="printSalesSummary()">Print Sales Summary</button>
    </div>
    
    <div class="chart-container">
        <!-- <div class="chart-wrapper">
            <h2>Stock Distribution (Pie Chart)</h2>
            <canvas id="stockPieChart"></canvas>
        </div> -->

        <div class="chart-wrapper">
            <h2>Product Stock Levels (Bar Graph)</h2>
            <canvas id="stockBarChart"></canvas>
        </div>
    </div>

<script>
const productNames = <?php echo $productNamesJSON; ?>;
const stockLevels = <?php echo $stockLevelsJSON; ?>;

const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9966FF', '#FF9F40', '#C9CBCF', '#8E44AD', '#1ABC9C', '#D35400'];

// const ctxPie = document.getElementById('stockPieChart').getContext('2d');
// const pieChart = new Chart(ctxPie, {
//     type: 'pie',
//     data: {
//         labels: productNames,
//         datasets: [{
//             data: stockLevels,
//             backgroundColor: colors,
//         }]
//     },
//     options: {
//         responsive: true,
//         maintainAspectRatio: false
//     }
// });

const ctxBar = document.getElementById('stockBarChart').getContext('2d');
const barChart = new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: productNames,
        datasets: [{
            label: 'Stock Levels',
            data: stockLevels,
            backgroundColor: '#36A2EB'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

function printSalesSummary() {
    window.print();
}
</script>

<style>
    .sales-summary {
        background: #f9f9f9;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    .sales-summary table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .sales-summary th, .sales-summary td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    .sales-summary th {
        background-color: #ffb6c1;
        color: white;
    }
    .print-btn {
        margin-top: 10px;
        padding: 10px 15px;
        background-color: #36A2EB;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        border-radius: 5px;
    }
    .print-btn:hover {
        background-color: #1E88E5;
    }
    @media print {
    .sidebar {
        display: none; /* Hide sidebar when printing */
    }
    .main-content {
        width: 100%; /* Expand main content */
    }
}
    
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
    .sidebar ul li a { text-decoration: none; color: white; font-size: 18px; 
    }
    .main-content {
        flex: 1;
        padding: 20px;
        text-align: center;
    }
    .chart-container {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    .chart-wrapper {
        width: 45%;
        min-width: 200px;
        height: 200px;
    }
    canvas {
        width: 100% !important;
        height: 100% !important;
    }
</style>

</body>
</html>
