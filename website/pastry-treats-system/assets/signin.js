document.getElementById("loginForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("login.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            window.location.href = "index.php";
        }
    })
    .catch(error => console.error("Error:", error));
});
