<?php
session_start();
header('Content-Type: application/json');

// Debugging: Log received POST data
file_put_contents("debug_add_log.txt", "Received data: " . print_r($_POST, true) . "\n", FILE_APPEND);

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(["success" => false, "message" => "Missing product ID or quantity."]);
    exit;
}

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Fetch product details from the database
include 'config.php';
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product || $product['stock'] < $quantity) {
    echo json_encode(["success" => false, "message" => "Product not found or insufficient stock."]);
    exit;
}

// Update stock in the database
$update_stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
$update_stock_stmt = $conn->prepare($update_stock_query);
$update_stock_stmt->bind_param("ii", $quantity, $product_id);
if (!$update_stock_stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Error updating stock: " . $update_stock_stmt->error]);
    exit;
}

// Update cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity
    ];
}

// Fetch updated product data (after stock update)
$products_query = "SELECT * FROM products";
$products_result = $conn->query($products_query);
$updated_products = [];
while ($row = $products_result->fetch_assoc()) {
    $updated_products[] = $row;
}

// Send the updated product data and success message as JSON
echo json_encode([
    "success" => true,
    "message" => "Item added to cart successfully!",
    "products" => $updated_products,
    "refresh" => true // Add the refresh flag
]);
?>