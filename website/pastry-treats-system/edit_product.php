<?php
include 'config.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']); // Convert to integer to prevent SQL injection
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();

if (!$product) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? '');
    $price = floatval($_POST["price"] ?? 0);
    $stock = intval($_POST["stock"] ?? 0);

    // Validate inputs
    if (empty($name)) {
        $error = "Product name is required.";
    } elseif ($price <= 0) {
        $error = "Price must be greater than 0.";
    } elseif ($stock < 0) {
        $error = "Stock cannot be negative.";
    } else {
        // Handle file upload if provided
        if ($_FILES["image"] ["name"]) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($_FILES["image"] ["name"], PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed)) {
                $error = "Only JPG, PNG, and GIF files are allowed.";
            } elseif ($_FILES["image"] ["size"] > 5000000) { // 5MB limit
                $error = "File size must be less than 5MB.";
            } else {
                // Generate unique filename to prevent overwriting
                $image = uniqid() . '_' . basename($_FILES["image"] ["name"]);
                if (move_uploaded_file($_FILES["image"] ["tmp_name"], "uploads/" . $image)) {
                    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, image=? WHERE id=?");
                    $stmt->bind_param("sdisi", $name, $price, $stock, $image, $id);
                    if ($stmt->execute()) {
                        $success = "Product updated successfully!";
                        $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
                    } else {
                        $error = "Database error: " . $conn->error;
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            }
        } else {
            // Update without image
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=? WHERE id=?");
            $stmt->bind_param("sdii", $name, $price, $stock, $id);
            if ($stmt->execute()) {
                $success = "Product updated successfully!";
                $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="product-container">
        <h2>Edit Product</h2>
        
        <?php if ($error): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br>
            
            <label>Price:</label>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required><br>
            
            <label>Stock:</label>
            <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required><br>
            
            <label>Image:</label>
            <input type="file" name="image" accept="image/*"><br>
            
            <button type="submit">Update Product</button>
            <a href="dashboard.php"><button type="button">Cancel</button></a>
        </form>
    </div>
</body>
</html>