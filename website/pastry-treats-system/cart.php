<!-- Cart Content -->
<div id="cart-content"></div>

<!-- Pop-up Notification -->
<div id="popup" class="popup"></div>

<script>
    // Function to load cart dynamically

    // Function to show pop-up notification
    function showPopup(message) {
        let popup = document.getElementById("popup");
        if (!popup) { // ✅ Prevents error if pop-up element is missing
            console.error("Popup element not found!");
            return;
        }
        popup.textContent = message;
        popup.style.display = "block";
        popup.style.opacity = "1";

        setTimeout(() => {
            popup.style.opacity = "0";
            setTimeout(() => popup.style.display = "none", 500);
        }, 1500);
    }

    // Attach event listeners to remove buttons
    function attachRemoveListeners() {
        document.querySelectorAll(".remove-from-cart").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault(); // ✅ Prevents default form submission
                
                let productId = this.getAttribute("data-product-id");

                fetch("remove_from_cart.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "product_id=" + encodeURIComponent(productId) // ✅ Safer data handling
                })
                .then(response => response.json())  // Parse JSON
                .then(data => {
                    console.log("Response from remove_from_cart.php:", data); // Debugging log
                    if (data.success) {
                        showPopup(data.message); // ✅ Show pop-up message
                        loadCart(); // ✅ Reload cart dynamically
                    } else {
                        alert("Error: " + data.message); // Show an alert if removal fails
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });
    }

    // Load cart on page load
    document.addEventListener("DOMContentLoaded", loadCart);
</script>
<style>
/* Pop-up notification styling */
.popup {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    opacity: 0;
    font-weight: bold;
    transition: opacity 0.5s ease-in-out;
    text-align: center;
    z-index: 1000;
    display: none;
}
</style>
