document.getElementById("registerForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("register.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            window.location.href = "login.php";
        }
    })
    .catch(error => console.error("Error:", error));
});
