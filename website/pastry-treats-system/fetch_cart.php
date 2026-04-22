<?php
session_start();
include 'config.php';

$total_price = 0;

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $cartContent = "<div id='empty-cart-message'><p>Your cart is empty.</p><p>Total: ₱0.00</p></div>";
} else {
    $cartContent = "<table id='cart-table'>";
    $cartContent .= "<tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr>";

    foreach ($_SESSION['cart'] as $id => $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $total_price += $subtotal;
        $cartContent .= "<tr>
                <td>{$item['name']}</td>
                <td>{$item['quantity']}</td>
                <td>₱" . number_format($item['price'], 2) . "</td>
                <td>₱" . number_format($subtotal, 2) . "</td>
                <td>
                    <form method='POST' action='remove_from_cart.php'>
                        <input type='hidden' name='product_id' value='{$id}'>
                        <button class='remove-from-cart' data-product-id='{$id}'>Remove</button>
                    </form>
                </td>
              </tr>";
    }
    $cartContent .= "</table>";
    $cartContent .= "<p>Total: ₱" . number_format($total_price, 2) . "</p>";
}

echo $cartContent;
?>