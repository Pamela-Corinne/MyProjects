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

$id = $_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];

    if ($_FILES["image"]["name"]) {
        $image = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $image);
        $conn->query("UPDATE products SET name='$name', price='$price', stock='$stock', image='$image' WHERE id=$id");
    } else {
        $conn->query("UPDATE products SET name='$name', price='$price', stock='$stock' WHERE id=$id");
    }

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
</head>
<body>
    <h2>Edit Product</h2>
    <form action="" method="post" enctype="multipart/form-data">
        Name: <input type="text" name="name" value="<?= $product['name'] ?>" required><br>
        Price: <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>
        Stock: <input type="number" name="stock" value="<?= $product['stock'] ?>" required><br>
        Image: <input type="file" name="image"><br>
        <button type="submit">Update Product</button>
    </form>
</body>
</html>

