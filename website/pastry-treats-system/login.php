<?php
session_start();
include 'config.php';

$error_message = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize inputs
    $username = filter_var(trim($username), FILTER_SANITIZE_STRING);

    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    } else {
        // Check if user is an admin
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if ($password === $row['password']) { // Plain text password comparison
                    $_SESSION['admin'] = $username;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid admin credentials.";
                }
            }
            $stmt->close(); // Close only after checking admin
        } else {
            $error_message = "Database error: " . $conn->error;
        }

        // If admin login fails, check user login
        if (empty($_SESSION['admin'])) { // Proceed to user check only if admin login failed
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    if ($password === $row['password']) { // Plain text password comparison
                        $_SESSION['user'] = $row['id'];
                        $_SESSION['username'] = $username;
                        header("Location: shop.php");
                        exit();
                    } else {
                        $error_message = "Invalid username or password.";
                    }
                } else {
                    $error_message = "Invalid username or password.";
                }
                $stmt->close();
            } else {
                $error_message = "Database error: " . $conn->error;
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PastryTreats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Login Form -->
<div class="login-container">
    <h2>Login to PastryTreats</h2>

    <?php if (!empty($error_message)): ?>
        <p class="error"><?= $error_message ?></p> <!-- Display error message -->
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <p><a href="index.php">Back to Homepage</a></p>
</div>

</body>
</html>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f8f8f8;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 300px;
}

h2 {
    color: #ff69b4;
}

label {
    display: block;
    margin-top: 10px;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

button {
    background-color: #ff69b4;
    color: white;
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 5px;
    margin-top: 15px;
    cursor: pointer;
    box-sizing: border-box;
}

button:hover {
    background-color: #ff1493;
}

.error {
    color: red;
    font-size: 14px;
    margin-top: 10px;
    text-align: center;
    font-weight: bold;
}
</style>
