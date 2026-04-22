<?php
session_start();
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent output before JSON response
ob_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}

$user = $_SESSION['username'];

// Fetch user details
$user_query = $conn->prepare("SELECT id, email, mobile FROM users WHERE username = ?");
$user_query->bind_param("s", $user);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

if (!$user_data) {
    echo json_encode(["success" => false, "message" => "User not found."]);
    exit();
}

$user_email = $user_data['email'];
$mobile_no = $user_data['mobile'];

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(["success" => false, "message" => "Your cart is empty."]);
    exit();
}

$items_ordered = [];
$total_order_price = 0;

foreach ($_SESSION['cart'] as $product_id => $item) {
    $quantity = $item['quantity'];

    // Fetch product details
    $product_query = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
    $product_query->bind_param("i", $product_id);
    $product_query->execute();
    $product_result = $product_query->get_result();
    $product_data = $product_result->fetch_assoc();

    if ($product_data) {
        $product_name = $product_data['name'];
        $price = $product_data['price'];
        $item_total = $price * $quantity;
        $total_order_price += $item_total;

        $items_ordered[] = [
            'name' => $product_name,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $item_total
        ];

        // Insert order into database
        $order_query = $conn->prepare("INSERT INTO orders (user_email, mobile_no, product_name, quantity, status, total_price) VALUES (?, ?, ?, ?, 'pending', ?)");
        $order_query->bind_param("sssdi", $user_email, $mobile_no, $product_name, $quantity, $item_total);
        $order_query->execute();
    }
}

// Check if any items were added to the order
if (empty($items_ordered)) {
    echo json_encode(["success" => false, "message" => "No items added to the order."]);
    exit();
}

// Clear cart after order placement
unset($_SESSION['cart']);

// Ensure no output before JSON response
ob_end_clean();

// Email confirmation
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = "your-email@gmail.com";
    $mail->Password = "your-app-password";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('pastrytreats76@gmail.com', 'PastryTreats');
    $mail->addAddress($user_email);
    $mail->Subject = 'Order Confirmation';

    $body = "Thank you for your order!\n\nOrder Summary:\n";
    foreach ($items_ordered as $item) {
        $body .= "{$item['quantity']}x {$item['name']} - ₱{$item['total']}\n";
    }
    $body .= "\nTotal: ₱$total_order_price\n\nYour order is now being processed.";

    $mail->Body = $body;
    $mail->send();

    echo json_encode(["success" => true, "emailSuccess" => true, "message" => "Order placed successfully! Confirmation email sent."]); // Indicate email success
} catch (Exception $e) {
    echo json_encode(["success" => true, "emailSuccess" => false, "message" => "Order placed successfully, but email failed to send: " . $mail->ErrorInfo]); //Indicate email failure
}

exit();
