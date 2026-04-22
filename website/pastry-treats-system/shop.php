<?php
session_start();
include 'cart.php';
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch products from the database
$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PastryTreats - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>


    </style>
</head>

<body>

<!-- Navbar -->
<nav>
    <ul class="nav-left">
        <!-- <li><a href="faq.php">FAQ</a></li>
        <li><a href="gallery.php">Gallery</a></li> -->
    </ul>
    
    <div class="nav-center">
        <input type="text" placeholder="Search pastries..." id="searchBar">
        <button onclick="search()">🔍</button>
    </div>

    <ul class="nav-right">
        <!-- <?php if (isset($_SESSION['username'])): ?>
            <li>Welcome, <?= $_SESSION['username'] ?>!</li> -->
            <li><button class="nav-cart" onclick="openCart()">🛒 Cart</button></li>
            <li><button onclick="openModal()">Edit Account</button></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><button class="nav-cart" onclick="openCart()">🛒 Cart</button></li>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Hero Section -->
<header class="hero">
    <h1 class="logo-text">PastryTreats</h1>
    <p>Delicious pastries, made with love! 🧁🍰</p>
</header>

<!-- Product Display -->
<section class="products">
    <h2>Our Best Pastries</h2>
    <div class="product-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="uploads/<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                <h3><?= $row['name'] ?></h3>
                <p>Price: ₱<?= number_format($row['price'], 2) ?></p>
                <p><strong>Stock: <?= $row['stock'] > 0 ? $row['stock'] : "<span style='color: red;'>*Not available</span>" ?></strong></p>

                <?php if ($row['stock'] > 0): ?>
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <label>Qty:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $row['stock'] ?>" required>
                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <button disabled style="background-color: gray; cursor: not-allowed;">Out of Stock</button>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- Cart Modal -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Your Shopping Cart</h2>
            <button class="close" onclick="closeCart()">✖</button>
        </div>
        <div id="cartItems"></div>
        <div id="checkoutLoading" style="display: none;">
            Processing your order... <div class="spinner"></div> </div>  
        <button id="checkoutButton" onclick="checkout()" class="checkout-btn" disabled>Proceed to Checkout</button>
    </div>
    </div>
</div>

 <!-- Edit Account Modal -->
 <div id="editAccountModal" class="modal">
        <div class="modal-content">
            <?php
            //This entire block must be inside the modal
            if (isset($_SESSION['user'])) {
                $userId = $_SESSION['user'];
                $userQuery = "SELECT * FROM users WHERE id = ?";
                $userStmt = $conn->prepare($userQuery);
                $userStmt->bind_param("i", $userId);
                if ($userStmt->execute()) {
                    $userResult = $userStmt->get_result();
                    $user = $userResult->fetch_assoc();
                    if (!$user) {
                        echo "<p>Error: User not found.</p>";
                        exit;
                    }
                } else {
                    echo "<p>Database error fetching user: " . $conn->error . "</p>";
                    exit;
                }
                $userStmt->close();

                // Generate CSRF token (inside the modal's PHP block)
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <h2>Edit Account Details</h2>
                <form action="update_useraccount.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required><br>

                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required><br>

                    <label>Mobile:</label>
                    <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']); ?>" required><br>

                    <label>Username:</label>
                    <input type="text" name="new_username" value="<?= htmlspecialchars($user['username']); ?>" required><br>

                    <label>New Password (leave blank to keep current):</label>
                    <input type="password" name="password"><br>

                    <div class="modal-buttons">
                        <button type="submit" class="update-btn">Update</button>
                        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                    </div>
                </form>
            <?php } else {
                echo "<p>Error: User not logged in.</p>";
            } ?>
        </div>
    </div>


<div id="popup" class="popup"></div> 
<script>
function loadCart() {
    fetch('fetch_cart.php')
        .then(response => response.text())
        .then(data => {
            console.log("Cart Data Received:", data); // Debugging

            // ✅ Always update the cart modal
            updateCartModal(data);
            attachRemoveListeners();

            const isEmpty = data.includes("Your cart is empty.");
            enableCheckoutButton(!isEmpty);
        })
        .catch(error => console.error("Error loading cart:", error));
}



function openCart() {
    document.getElementById("cartModal").style.display = "flex";
    loadCart();
}

function closeCart() {
    document.getElementById("cartModal").style.display = "none";
}

function openModal() {
    document.getElementById("editAccountModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("editAccountModal").style.display = "none";
}

function checkout() {
    const checkoutButton = document.querySelector('.checkout-btn'); // Select the button
    checkoutButton.disabled = true; // Disable the button
    checkoutButton.textContent = "Processing..."; // Change button text

    fetch("checkout.php", {
        method: "POST",
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        checkoutButton.disabled = false; // Re-enable the button
        checkoutButton.textContent = "Proceed to Checkout"; // Restore original text

        if (data.success) {
            showPopup(data.message);
            location.reload();
        } else {
            showPopup(data.message);
        }
    })
    .catch(error => {
        checkoutButton.disabled = false; // Re-enable the button on error
        checkoutButton.textContent = "Proceed to Checkout"; // Restore original text
        console.error("Checkout error:", error);
        showPopup("An error occurred during checkout. Please check your internet connection or try again later.");
    });
}

function search() {
    let searchTerm = document.getElementById("searchBar").value.toLowerCase();
    showPopup("Search function is not implemented yet! You searched: " + searchTerm);
}
function showPopup(message) {
    const popup = document.getElementById("popup");
    popup.textContent = message;
    popup.style.display = "block";
    popup.style.opacity = 1;
    setTimeout(() => {
        popup.style.opacity = 0;
        setTimeout(() => (popup.style.display = "none"), 500);
    }, 1500);
}

function handleCartUpdate(response) {
    alert(response.message); // Show success/error message

    if (response.success && response.refresh) {
        location.reload(); // Refresh shop.php automatically
    }
}

function updateCartModal(data) {
    const cartItemsDiv = document.getElementById('cartItems');
    cartItemsDiv.innerHTML = data; // Update the modal's cart items
}

function enableCheckoutButton(isEnabled) {
    let checkoutButton = document.querySelector('.checkout-btn');
    if (checkoutButton) {
        checkoutButton.disabled = !isEnabled;
    }
}


// function updateCartModal(data) {
//             // Extract the relevant cart information from the data (adjust as needed)
//             const cartItemsDiv = document.getElementById('cartItems');
//             cartItemsDiv.innerHTML = data; // Update the modal's cart items
//         }
//         document.addEventListener("DOMContentLoaded", function () {
//     const addToCartButtons = document.querySelectorAll(".add-to-cart");

//     addToCartButtons.forEach(button => {
//         button.addEventListener("click", function () {
//             let productId = this.dataset.productId;
//             let quantity = document.querySelector(`#quantity-${productId}`).value;

//             fetch("add_to_cart.php", {
//                 method: "POST",
//                 headers: {
//                     "Content-Type": "application/x-www-form-urlencoded"
//                 },
//                 body: `product_id=${productId}&quantity=${quantity}`
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     alert(data.message); // Display success message
//                     updateProductInventory(data.products); // Refresh inventory dynamically
//                 } else {
//                     alert(data.message); // Display error message
//                 }
//             })
//             .catch(error => console.error("Error:", error));
//         });
//     });

//     function updateProductInventory(products) {
//         products.forEach(product => {
//             let productRow = document.querySelector(`#product-${product.id}`);
//             if (productRow) {
//                 productRow.querySelector(".stock").textContent = product.stock; // Update stock display
//                 if (product.stock <= 0) {
//                     productRow.querySelector(".add-to-cart").disabled = true; // Disable button if out of stock
//                     productRow.querySelector(".status").textContent = "Not Available"; // Update status
//                 } else {
//                     productRow.querySelector(".add-to-cart").disabled = false;
//                     productRow.querySelector(".status").textContent = "Available";
//                 }
//             }
//         });
//     }
// });
document.addEventListener("DOMContentLoaded", function () {
    const addToCartButtons = document.querySelectorAll(".add-to-cart");
    const removeFromCartButtons = document.querySelectorAll(".remove-from-cart");

    // Function to update the product stock in the UI
    function updateStockDisplay(updatedProducts) {
        updatedProducts.forEach(product => {
            let stockElement = document.querySelector(`#stock-${product.id}`);
            if (stockElement) {
                stockElement.textContent = `Stock: ${product.stock}`;
            }
        });
    }

    // Function to handle add-to-cart AJAX request
    function handleAddToCart(event) {
        let productId = this.dataset.productId;
        let quantity = document.querySelector(`#quantity-${productId}`).value;

        fetch("add_to_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Show success/error message
            if (data.success) {
                updateStockDisplay(data.products); // Update stock in UI
            }
        })
        .catch(error => console.error("Error:", error));
    }

    // Function to handle remove-from-cart AJAX request
    function handleRemoveFromCart(event) {
        let productId = this.dataset.productId;

        fetch("remove_from_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Show success/error message
            if (data.success) {
                updateStockDisplay(data.products); // Update stock in UI
            }
        })
        .catch(error => console.error("Error:", error));
    }

    // Attach event listeners to all "Add to Cart" buttons
    addToCartButtons.forEach(button => {
        button.addEventListener("click", handleAddToCart);
    });

    // Attach event listeners to all "Remove from Cart" buttons
    removeFromCartButtons.forEach(button => {
        button.addEventListener("click", handleRemoveFromCart);
    });
});






// Add to cart handling (updated)
document.querySelectorAll('form[action="add_to_cart.php"]').forEach(form => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(handleCartUpdate);
    });
});

document.addEventListener('click', (event) => {
    if (event.target.classList.contains('remove-from-cart')) {
        event.preventDefault();
        const productId = event.target.dataset.productId;

        fetch("remove_from_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "product_id=" + encodeURIComponent(productId)
        })
        .then(response => response.json())
        .then(data => {
            console.log("✅ Fetch Response:", data);  // Debug response

            alert(data.message);  // Show success/error message

            // Reload the page after a short delay, regardless of success or failure
            setTimeout(() => {
                window.location.reload();
            }, 500); // Small delay for better user experience

        })
        .catch(error => console.error("❌ Fetch error:", error));
    }
});

// ... rest of your shop.php JavaScript code ...

// Function to update the cart display (you'll need to implement this)
function updateCartDisplay(cart) {
    const cartItemsDiv = document.getElementById('cartItems');
    cartItemsDiv.innerHTML = ''; // Clear existing cart items

    if (Object.keys(cart).length === 0) {
        cartItemsDiv.innerHTML = '<p>Your cart is empty.</p>';
    } else {
        // Add your logic to create HTML for each cart item and append it to cartItemsDiv
        // ... (Your cart item HTML generation code) ...
    }
}







//Rest of the Javascript code remains the same
</script>

</body>
</html>




<script>
function search() {
    let searchTerm = document.getElementById("searchBar").value.toLowerCase();
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        let productName = product.querySelector('h3').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            product.style.display = "block";
        } else {
            product.style.display = "none";
        }
    });
}

</script>

</body>
</html>
<style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f8f8;
}

nav {
    display: flex;
    justify-content: space-between;
    background: #ffb6c1;
    padding: 15px;
}

.nav-left, .nav-right {
    display: flex;
    list-style: none;
    padding: 0;
}

.nav-left li, .nav-right li {
    margin: 0 10px;
}

.nav-left a, .nav-right a {
    text-decoration: none;
    color: #fff;
    font-weight: bold;
}

.nav-center {
    display: flex;
}

.nav-center input {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.hero {
    text-align: center;
    padding: 50px 0;
    background-color:rgb(0, 0, 0);
    color: white;
}

.logo-text {
    font-size: 3rem;
    font-family: 'Cursive', sans-serif;
}

.products {
    text-align: center;
    padding: 20px;
}

.product-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.product-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 15px;
    margin: 10px;
    width: 200px;
    text-align: center;
}

.product-card img {
    width: 100%;
    height: 150px;
    border-radius: 10px;
}
.hero {
    text-align: center;
    padding: 50px 0;
    
    color: white;
}

.logo-text {
    font-size: 3rem;
    font-family: 'Cursive', sans-serif;
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
}

.hero p {
    font-size: 1.2rem;
    font-weight: bold;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
}
.popup {
    position: fixed;
    top: 20px; /* 🔺 Adjusted to appear at the top */
    left: 50%;
    transform: translateX(-50%); /* 🔹 Centers the pop-up */
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    opacity: 1;
    font-weight: bold;
    transition: opacity 0.5s ease-in-out;
    text-align: center;
    z-index: 1000; /* Ensure it stays on top */
}
.product-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px; /* Space between products */
}

.product-card {
    width: 220px; /* Set a fixed width */
    height: 350px; /* Set a fixed height */
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 15px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Evenly distribute content */
}

.product-card img {
    width: 100%;
    height: 150px; /* Fixed image height */
    object-fit: cover; /* Ensures images are uniform */
    border-radius: 10px;
}

.product-card h3 {
    font-size: 1.2rem;
    margin: 5px 0;
}

.product-card p {
    margin: 5px 0;
}

.product-card form {
    margin-top: auto; /* Pushes button to the bottom */
}

.product-card button {
    width: 100%;
    padding: 10px;
    background-color: #ffb6c1;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.product-card button:disabled {
    background-color: gray;
    cursor: not-allowed;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(255, 182, 193, 0.8); /* Light pink shadow */
    width: 45%;
    max-width: 500px; /* Prevents excessive width */
    text-align: left; /* Aligns text properly */
}

.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 10px; /* Adds spacing between fields */
}

.modal-content label {
    font-weight: bold;
}


.close {
    position: absolute;
    top: 150x; /* Move it closer to the pop-up */
    right: 440px; /* Move it inside the pop-up */
    font-size: 24px;
    cursor: pointer;
    background: black;
    border: 2px solid #ffb6c1; /* Light pink border */
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
}


button {
    padding: 10px;
    background-color: #ffb6c1;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:disabled {
    background-color: gray;
    cursor: not-allowed;
}
.nav-right button {
    background: none; /* Remove default button styling */
    border: none;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    padding: 0; /* Remove extra padding */
    margin: 0 10px; /* Keep spacing consistent */
}

.nav-right button:hover {
    text-decoration: underline; /* Add hover effect like other links */
}


</style>