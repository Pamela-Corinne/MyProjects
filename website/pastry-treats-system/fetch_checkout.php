<?php
session_start();

if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    echo "<p>Your cart is empty!</p>";
    exit;
}

$total = 0;
foreach ($_SESSION["cart"] as $product) {
    echo "<p>{$product['name']} x {$product['quantity']} - ₱" . number_format($product['price'] * $product['quantity'], 2) . "</p>";
    $total += $product['price'] * $product['quantity'];
}

echo "<hr><p><strong>Total: ₱" . number_format($total, 2) . "</strong></p>";
?>
