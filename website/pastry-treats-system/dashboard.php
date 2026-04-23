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

<!-- Edit Product Modal - Updated -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Product</h2>
        
        <?php if (isset($_SESSION['edit_error'])): ?>
            <div style="background: #ffe6e6; color: #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?= htmlspecialchars($_SESSION['edit_error']) ?>
            </div>
            <?php unset($_SESSION['edit_error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['edit_success'])): ?>
            <div style="background: #e6ffe6; color: #006600; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?= htmlspecialchars($_SESSION['edit_success']) ?>
            </div>
            <?php unset($_SESSION['edit_success']); ?>
        <?php endif; ?>
        
        <form id="editForm" method="POST" action="update_product.php" enctype="multipart/form-data">
            <input type="hidden" name="id" id="editId">
            
            <label style="display: block; margin: 10px 0 5px; font-weight: bold;">Product Name</label>
            <input type="text" name="name" id="editName" required 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            
            <label style="display: block; margin: 10px 0 5px; font-weight: bold;">Price (₱)</label>
            <input type="number" name="price" id="editPrice" required step="0.01" 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            
            <label style="display: block; margin: 10px 0 5px; font-weight: bold;">Stock Quantity</label>
            <input type="number" name="stock" id="editStock" required 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            
            <label style="display: block; margin: 10px 0 5px; font-weight: bold;">Current Image</label>
            <img id="editImagePreview" src="" alt="Current product image" 
                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 5px; border: 2px solid #ddd; display: block; margin: 10px auto;">
            
            <label style="display: block; margin: 10px 0 5px; font-weight: bold;">Upload New Image (optional)</label>
            <input type="file" name="image" accept="image/*" 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            <small style="color: #666; display: block; margin-top: 5px;">Leave empty to keep current image</small>
            
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" 
                        style="background-color: #ff69b4; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-right: 10px;">
                    Update Product
                </button>
                <button type="button" class="close-btn" 
                        style="background-color: #999; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Combined Styles -->
<style>
    body { display: flex; margin: 0; }
    .sidebar { 
        width: 200px; 
        background: #ffb6c1; 
        padding: 20px;
        min-height: 100vh;
        overflow: hidden;
        transition: width 0.3s ease-in-out;
    }
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar ul li { margin: 20px 0; }
    .sidebar ul li a { text-decoration: none; color: white; font-size: 18px; }
    .main-content { flex: 1; padding: 20px; }
    .product-container { display: flex; gap: 20px; flex-wrap: wrap; }
    .product-card { border: 1px solid #ddd; padding: 10px; width: 200px; text-align: center; }
    .product-card img { max-width: 100%; height: auto; }
    .out-of-stock { border: 2px solid red; }
    .actions { margin-top: 10px; }
    .edit-btn, .delete-btn { text-decoration: none; padding: 5px 10px; margin: 5px; display: inline-block; cursor: pointer; }
    .edit-btn { background-color: #4CAF50; color: white; border: none; }
    .delete-btn { background-color: #FF0000; color: white; }

    /* Enhanced Modal Styles */
    .modal { 
        display: none; 
        position: fixed; 
        left: 0; 
        top: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.5); 
        z-index: 1000;
        overflow-y: auto;
    }

    .modal-content { 
        background: white; 
        padding: 30px; 
        margin: 5% auto; 
        width: 90%; 
        max-width: 450px; 
        border-radius: 10px; 
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    .close { 
        position: absolute; 
        right: 20px; 
        top: 15px; 
        font-size: 28px; 
        font-weight: bold; 
        cursor: pointer; 
        color: #aaa;
        transition: color 0.3s;
    }

    .close:hover { 
        color: #000; 
    }

    .modal-content label {
        color: #333;
        font-size: 14px;
    }

    .modal-content input[type="text"],
    .modal-content input[type="number"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .modal-content input:focus {
        outline: none;
        border-color: #ff69b4;
    }

    .modal-content button[type="submit"] {
        background-color: #ff69b4 !important;
        color: white !important;
        padding: 12px 30px !important;
        border: none !important;
        border-radius: 5px !important;
        cursor: pointer !important;
        font-size: 16px !important;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .modal-content button[type="submit"]:hover {
        background-color: #ff1493 !important;
    }

    .modal-content button.close-btn {
        background-color: #999 !important;
        color: white !important;
        padding: 12px 30px !important;
        border: none !important;
        border-radius: 5px !important;
        cursor: pointer !important;
        font-size: 16px !important;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .modal-content button.close-btn:hover {
        background-color: #666 !important;
    }
</style>

<script>
// Enhanced modal functionality
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editName').value = this.dataset.name;
        document.getElementById('editPrice').value = this.dataset.price;
        document.getElementById('editStock').value = this.dataset.stock;
        document.getElementById('editImagePreview').src = this.dataset.image;
        document.getElementById('editModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
});

// Close modal handlers
document.querySelector('.close').addEventListener('click', closeModal);
document.querySelector('.close-btn').addEventListener('click', closeModal);

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close when clicking outside modal
window.addEventListener('click', function(e) {
    if (e.target == document.getElementById('editModal')) {
        closeModal();
    }
});
</script>

</body>
</html>