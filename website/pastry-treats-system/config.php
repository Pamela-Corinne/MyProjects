<?php
// Check if a session is already active before modifying session settings
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Leave empty if using XAMPP
$database = "pstrytreats_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
