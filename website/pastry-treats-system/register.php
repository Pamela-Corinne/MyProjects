<?php
session_start();
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = "";
$success_message = "";

require 'vendor/autoload.php';

$firstName = $_POST['firstName'] ?? '';
$suffix = $_POST['suffix'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$gender = $_POST['gender'] ?? '';
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = filter_var(trim($firstName), FILTER_SANITIZE_STRING);
    $suffix = filter_var(trim($suffix), FILTER_SANITIZE_STRING);
    $lastName = filter_var(trim($lastName), FILTER_SANITIZE_STRING);
    $gender = filter_var(trim($gender), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    $username = filter_var(trim($username), FILTER_SANITIZE_STRING);

    if (empty($firstName) || empty($lastName) || empty($gender) || empty($email) || empty($mobile) || empty($username) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) != 11) {
            $error_message = "Invalid mobile number length. Please enter an 11-digit number.";
        } else {
            $mobile = "+63" . $mobile;

            if (strlen($username) < 5 || !preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
                $error_message = "Username must be at least 5 characters long and contain only letters, numbers, and underscores.";
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error_message = "Username or Email already exists. Please choose a different one.";
                } else {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error_message = "Invalid email format.";
                    } else {
                        if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[\W_]/", $password)) {
                            $error_message = "Password must be at least 8 characters long and contain uppercase, lowercase letters, numbers, and special characters.";
                        } else {
                            $name = trim("$firstName $suffix $lastName");

                            // Store password as plain text (TEMPORARY - REMOVE LATER)
                            $plain_password = $password;

                            // Insert user data into the database
                            $stmt = $conn->prepare("INSERT INTO users (name, gender, email, mobile, username, password) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ssssss", $name, $gender, $email, $mobile, $username, $plain_password);

                            if ($stmt->execute()) {
                                // Send welcome email
                                $mail = new PHPMailer(true);

                                try {
                                    $mail->isSMTP();
                                    $mail->Host = 'smtp.gmail.com';
                                    $mail->SMTPAuth = true;
                                    $mail->Username = 'pastrytreats76@gmail.com';
                                    $mail->Password = 'yozcxyvkejihpihs';
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port = 587;

                                    $mail->setFrom('pastrytreats76@gmail.com', 'PastryTreats');
                                    $mail->addAddress($email);

                                    $mail->isHTML(true);
                                    $mail->Subject = 'Welcome to PastryTreats!';
                                    $mail->Body = "
                                        <h3>Welcome to PastryTreats, $firstName!</h3>
                                        <p>We are delighted to have you as part of our bakery family. Enjoy delicious pastries and amazing treats!</p>
                                        <p>Happy shopping! 🍰</p>
                                        <p><strong>PastryTreats Team</strong></p>
                                    ";

                                    $mail->send();
                                    $success_message = "✅ Registration successful! A welcome email has been sent to your email address.";
                                } catch (Exception $e) {
                                    $error_message = "Email could not be sent. Error: " . $mail->ErrorInfo;
                                }
                            } else {
                                $error_message = "❌ Error registering user. Please try again. " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
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
    <title>Register | PastryTreats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-container">
    <h2>Register for PastryTreats</h2>
    <?php if (!empty($error_message)): ?>
        <p class="error"><?= $error_message ?></p>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <p class="success"><?= $success_message ?></p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" pattern="[A-Za-z]+" required>
        
        <label for="suffix">Suffix (Optional):</label>
        <input type="text" id="suffix" name="suffix" pattern="[A-Za-z\.]+">
        
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" pattern="[A-Za-z]+" required>
        
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="mobile">Mobile Number:</label>
        <input type="text" id="mobile" name="mobile" required>
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" minlength="5" pattern="[A-Za-z0-9_]+" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" minlength="8" required>
        
        <label for="show-password">Show Password</label>
        <input type="checkbox" id="show-password" onclick="togglePassword()">
        
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
    <p><a href="index.php">Back to Homepage</a></p>
</div>
<script>
function togglePassword() {
    var password = document.getElementById("password");
    var showPassword = document.getElementById("show-password");
    password.type = showPassword.checked ? "text" : "password";
}
</script>
</body>
</html>


<style>
body {
    font-family: 'Arial', sans-serif;
    background-color: #f7f7f7;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 150vh;
    margin: 0;
}

.login-container {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 100%;
    max-width: 400px;
}

h2 {
    color: #ff1493;
    margin-bottom: 20px;
    font-size: 1.8em;
}

label {
    display: block;
    margin-top: 10px;
    text-align: left;
    font-size: 14px;
    color: #333;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

input:focus {
    border-color: #ff1493;
    outline: none;
}
#show-password {
    margin-top: -500px; /* Adds space between the checkbox and the password field */
    margin-left: -375px; /* Adds a small margin to the left of the checkbox */
}

button {
    background-color: #ff1493;
    color: white;
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 8px;
    margin-top: 20px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #e60073;
}

small {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}

.error {
    color: #ff4d4d;
    font-size: 14px;
}

.success {
    color: #28a745;
    font-size: 14px;
}

a {
    color: #ff1493;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
