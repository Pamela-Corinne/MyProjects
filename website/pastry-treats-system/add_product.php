<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_FILES['image'];

    // Define upload directory
    $targetDir = "uploads/";

    // Check if 'uploads/' folder exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);  // Create with full permissions
    }

    if ($image['error'] === 0) {
        $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));

        // Generate unique filename
        $newFilename = uniqid() . "." . $imageFileType;
        $targetFile = $targetDir . $newFilename;

        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            // Store the unique image name in the database
            $stmt = $conn->prepare("INSERT INTO products (name, price, stock, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $name, $price, $stock, $newFilename);

            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Error adding product.";
            }
        } else {
            echo "Error uploading image.";
        }
    } else {
        echo "Image upload failed.";
    }
}
?>
