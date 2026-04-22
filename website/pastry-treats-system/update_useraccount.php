<?php
session_start();
include 'config.php';

// CSRF Protection: Check if the token matches
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('Invalid CSRF token. Please try again.'); window.history.back();</script>";
    exit();
}

// Rest of your existing code... (unchanged except for the addition below)

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Current username

// Fetch current user details
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "Error: User not found.";
    exit();
}

// Fetch input values, retaining old values if the field is left empty
$name = trim($_POST['name']) ?: $user['name'];
$email = trim($_POST['email']) ?: $user['email'];
$mobile = trim($_POST['mobile']) ?: $user['mobile'];
$new_username = trim($_POST['new_username']) ?: $username;
$password = trim($_POST['password']);

// **Check for duplicate email only if changed**
if ($email !== $user['email']) {
    $check_email = "SELECT id FROM users WHERE email = ? AND username != ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Error: This email is already in use by another account.'); window.history.back();</script>";
        exit();
    }
    $stmt->close();
}

// **Check for duplicate username only if changed**
if ($new_username !== $username) {
    $check_username = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_username);
    $stmt->bind_param("s", $new_username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Error: This username is already taken.'); window.history.back();</script>";
        exit();
    }
    $stmt->close();
}

// **Prepare update query dynamically**
$update_fields = [];
$params = [];
$types = "";

// Add only changed fields
if ($name !== $user['name']) {
    $update_fields[] = "name = ?";
    $params[] = $name;
    $types .= "s";
}
if ($email !== $user['email']) {
    $update_fields[] = "email = ?";
    $params[] = $email;
    $types .= "s";
}
if ($mobile !== $user['mobile']) {
    $update_fields[] = "mobile = ?";
    $params[] = $mobile;
    $types .= "s";
}
if ($new_username !== $username) {
    $update_fields[] = "username = ?";
    $params[] = $new_username;
    $types .= "s";
}
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update_fields[] = "password = ?";
    $params[] = $hashed_password;
    $types .= "s";
}

// Proceed only if there are fields to update
if (!empty($update_fields)) {
    $query = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE username = ?";
    $params[] = $username;
    $types .= "s";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // **FIX:** Update session username to prevent localhost error
        if ($new_username !== $username) {
            $_SESSION['username'] = $new_username;  // Update session with new username
        }

        echo "<script>
                alert('Account updated successfully!');
                window.location.href = 'shop.php';
              </script>";
    } else {
        echo "<script>alert('Error updating account.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No changes were made.'); window.history.back();</script>";
}

$stmt->close();
?>