<?php
session_start();

if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    echo "No items in cart!";
    exit;
}

// Process order logic (store in database, send email, etc.)

unset($_SESSION["cart"]); // Clear cart after purchase
echo "Order placed successfully!";
?>
