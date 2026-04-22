<?php
include 'config.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM products WHERE id = $id");
}

header("Location: dashboard.php");
exit();
?>
