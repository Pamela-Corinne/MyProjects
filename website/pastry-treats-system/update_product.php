<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Check if a new image was uploaded
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imageName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $imageName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validate image file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                // Update with new image
                $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, image=? WHERE id=?");
                $stmt->bind_param("sdisi", $name, $price, $stock, $imageName, $id);
            } else {
                die("Error uploading image.");
            }
        } else {
            die("Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.");
        }
    } else {
        // Update without changing the image
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=? WHERE id=?");
        $stmt->bind_param("sdii", $name, $price, $stock, $id);
    }

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=Product updated successfully.");
        exit();
    } else {
        die("Error updating product.");
    }
}
?>
