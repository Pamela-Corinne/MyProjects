<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Database connection

$admin_username = $_SESSION['admin'];

// Fetch admin details using username
$query = "SELECT * FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// If admin data is missing, show an error message
if (!$admin) {
    die("<p style='color: red;'>Error: Admin account not found. Check your session or database.</p>");
}

// Handle form submission
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    
    if (!empty($_POST['password'])) {
        $password = $_POST['password']; // No hashing applied
        $updateQuery = "UPDATE admins SET name = ?, email = ?, password = ? WHERE username = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssss", $name, $email, $password, $admin_username);
    } else {
        $updateQuery = "UPDATE admins SET name = ?, email = ? WHERE username = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sss", $name, $email, $admin_username);
    }

    if ($stmt->execute()) {
        $success_message = "Account updated successfully!";
        $admin['name'] = $name;
        $admin['email'] = $email;
        $_SESSION['admin'] = $admin_username; // Ensure session remains consistent
    } else {
        $error_message = "Error updating account.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | PastryTreats</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php">Inventory</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="analytics.php">Analytics</a></li>
        <li><a href="adminaccount.php">Account</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
<h1>Admin Account</h1>

<?php if ($success_message): ?>
    <p class="success"><?php echo $success_message; ?></p>
<?php elseif ($error_message): ?>
    <p class="error"><?php echo $error_message; ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>

    <label>New Password (optional):</label>
    <input type="password" name="password" placeholder="Leave blank to keep current password">

    <button type="submit">Update Account</button>
</form>
</div>
</div>


<style>
    body {
        display: flex;
        margin: 0;
    }
    .sidebar {
        width: 250px;
        background: #ffb6c1;
        padding: 20px;
        height: 100vh;
    }
    .sidebar ul {
        list-style: none;
        padding: 0;
    }
    .sidebar ul li {
        margin: 20px 0;
    }
    .sidebar ul li a {
        text-decoration: none;
        color: white;
        font-size: 18px;
    }
    .main-content {
    flex: 1;
    padding: 40px;
    background: #fff8f8;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

h1 {
    font-size: 28px;
    color: #333;
    margin-bottom: 20px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

form {
    width: 100%;
    max-width: 500px;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: left;
}

label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    color: #444;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: all 0.3s ease;
}

input:focus {
    border-color: #ff6699;
    outline: none;
    box-shadow: 0 0 8px rgba(255, 102, 153, 0.2);
}

button {
    width: 100%;
    padding: 12px;
    background: #ff6699;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-transform: uppercase;
    font-weight: bold;
    transition: all 0.3s ease;
}

button:hover {
    background: #e05585;
}

.success, .error {
    font-size: 14px;
    font-weight: bold;
    padding: 10px;
    width: 100%;
    text-align: center;
    border-radius: 5px;
    margin-bottom: 15px;
}

.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}


  

</style>

</body>
</html>
