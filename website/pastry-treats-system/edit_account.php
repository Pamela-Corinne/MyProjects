<?php
session_start();

// Check if user ID is set in the session (more secure than username)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$userId = $_SESSION['user']; // Use user ID for security

// Fetch user details using user ID (safer)
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);

if (!$stmt->execute()) {
    die("Database error: " . $conn->error); // Improved error handling
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // Redirect to an error page or login page instead of just displaying a message
    header("Location: error.php?message=UserNotFound"); // Example error handling
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (Meta tags and script remain largely the same) ... -->
    <script>
        // ... (JavaScript validation - consider improving password strength check) ...
    </script>
</head>
<body>
    <h2>Edit Account Details</h2>
    <form name="editForm" method="POST" action="update_useraccount.php" onsubmit="return validateForm();">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">  <!-- CSRF protection -->
        <!-- ... (rest of the form fields) ... -->
    </form>
</body>
</html>

<?php
// Generate CSRF token (only once per session)
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>