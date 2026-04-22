<?php 
session_start();
header('Content-Type: application/json');

// Debugging: Log received POST data
file_put_contents("debug_remove_log.txt", "Received product_id: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Ensure the cart exists in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Initialize the cart if it's not set
}

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    echo json_encode(["success" => false, "message" => "Cart is empty."]);
    exit;
}

// Check if product ID is provided
if (!isset($_POST['product_id'])) {
    echo json_encode(["success" => false, "message" => "No product ID provided."]);
    exit;
}

$product_id = $_POST['product_id'];

// Check if the product exists in the cart
if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(["success" => false, "message" => "Item not found in cart.", "cart" => $_SESSION['cart']]);
    exit;
}

// Get the quantity being removed
$quantity = $_SESSION['cart'][$product_id]['quantity'];

// Restore the stock in the database
include 'config.php';
$update_stock_query = "UPDATE products SET stock = stock + ? WHERE id = ?";
$update_stock_stmt = $conn->prepare($update_stock_query);
$update_stock_stmt->bind_param("ii", $quantity, $product_id);
if (!$update_stock_stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Error restoring stock: " . $update_stock_stmt->error]);
    exit;
}

// Remove the product from the cart
unset($_SESSION['cart'][$product_id]);

// Send the updated cart data in the JSON response
echo json_encode([
    "success" => true, 
    "message" => "Item removed successfully and stock restored!",
    "cart" => $_SESSION['cart'] // Include updated cart data
]);

file_put_contents("debug_remove_log.txt", "Final response: " . json_encode([
    "success" => true, 
    "message" => "Item removed successfully and stock restored!",
    "cart" => $_SESSION['cart'] // Include updated cart data
]) . "\n", FILE_APPEND);

?>