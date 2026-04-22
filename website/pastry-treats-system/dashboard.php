<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
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
    <h1>Inventory Management</h1>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" name="price" placeholder="Price" required step="0.01">
        <input type="number" name="stock" placeholder="Stock Quantity" required>
        <input type="file" name="image" required>
        <button type="submit">Add Product</button>
    </form>

    <h2>Product List</h2>
    <div class="product-container">
        <?php
        $result = $conn->query("SELECT * FROM products");

        while ($row = $result->fetch_assoc()) {
            $stockClass = ($row['stock'] == 0) ? 'out-of-stock' : '';
            $imagePath = (!empty($row['image']) && file_exists("uploads/" . $row['image'])) 
                        ? "uploads/" . htmlspecialchars($row['image']) 
                        : "assets/img/default.jpg"; 

            echo "<div class='product-card $stockClass'>
                    <img src='$imagePath' alt='" . htmlspecialchars($row['name']) . "' width='100' height='100'>
                    <h3>" . htmlspecialchars($row['name']) . "</h3>
                    <p>₱" . htmlspecialchars($row['price']) . "</p>
                    <p>Stock: " . htmlspecialchars($row['stock']) . "</p>
                    <div class='actions'>
                        <button class='edit-btn' data-id='{$row['id']}' 
                                data-name='" . htmlspecialchars($row['name']) . "' 
                                data-price='" . htmlspecialchars($row['price']) . "' 
                                data-stock='" . htmlspecialchars($row['stock']) . "' 
                                data-image='$imagePath'>✏️ Edit</button>
                        <a href='delete_product.php?id={$row['id']}' class='delete-btn' onclick='return confirm(\"Are you sure?\")'>🗑️ Delete</a>
                    </div>
                  </div>";
        }
        ?>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Product</h2>
        <form id="editForm" method="POST" action="update_product.php" enctype="multipart/form-data">
            <input type="hidden" name="id" id="editId">
            <label>Product Name</label>
            <input type="text" name="name" id="editName" required>
            <label>Price</label>
            <input type="number" name="price" id="editPrice" required step="0.01">
            <label>Stock Quantity</label>
            <input type="number" name="stock" id="editStock" required>
            <label>Current Image</label>
            <img id="editImagePreview" src="" width="100" height="100">
            <label>Upload New Image</label>
            <input type="file" name="image">
            <button type="submit">Update Product</button>
        </form>
    </div>
</div>

<!-- Modal Styling -->
<style>
    body { display: flex; margin: 0; }
    .sidebar { 
        width: 200px; background: #ffb6c1; padding: 20px;
        min-height: 100vh;
        overflow: hidden;
    transition: width 0.3s ease-in-out; /* Smooth transition */}
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar ul li { margin: 20px 0; }
    .sidebar ul li a { text-decoration: none; color: white; font-size: 18px; 
    }
    .main-content { flex: 1; padding: 20px; }
    .product-container { display: flex; gap: 20px; flex-wrap: wrap; }
    .product-card { border: 1px solid #ddd; padding: 10px; width: 200px; text-align: center; }
    .product-card img { max-width: 100%; height: auto; }
    .out-of-stock { border: 2px solid red; }
    .actions { margin-top: 10px; }
    .edit-btn, .delete-btn { text-decoration: none; padding: 5px 10px; margin: 5px; display: inline-block; cursor: pointer; }
    .edit-btn { background-color: #4CAF50; color: white; border: none; }
    .delete-btn { background-color: #FF0000; color: white; }
    .modal { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { background: white; padding: 20px; margin: 10% auto; width: 300px; border-radius: 5px; }
    .close { float: right; font-size: 20px; cursor: pointer; }
</style>

<!-- Modal & Edit Logic -->
<script>
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editName').value = this.dataset.name;
        document.getElementById('editPrice').value = this.dataset.price;
        document.getElementById('editStock').value = this.dataset.stock;
        document.getElementById('editImagePreview').src = this.dataset.image;
        document.getElementById('editModal').style.display = 'block';
    });
});

document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('editModal').style.display = 'none';
});
</script>

</body>
</html>
