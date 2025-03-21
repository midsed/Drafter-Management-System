<?php
session_start();
if (!isset($_SESSION['verified_email'])) {
    header("Location: forgotpassword.php");
    exit();
}
$email = $_SESSION['verified_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Reset Your Password</h1>
            <form id="reset-password-form">
                <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                
                <label for="new_password">Enter Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="New Password" required>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Verify Password" required>

                <button type="submit">Reset Password</button>

                <p class="center-text">
                    <a href="login.php">Back to Login Page</a>
                </p> 
            </form>
        </div>
        <div class="right-section">
            <img src="images/New Drafter Logo Cropped.png" alt="Drafter AutoTech Logo">
        </div>
    </div>
</body>
</html>

<script>
document.getElementById("reset-password-form").addEventListener("submit", function (event) {
    event.preventDefault();

    let email = document.getElementById("email").value;
    let newPassword = document.getElementById("new_password").value;
    let confirmPassword = document.getElementById("confirm_password").value;

    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/;

    if (!newPassword || !confirmPassword) {
        Swal.fire({
            icon: "warning",
            title: "Missing Fields!",
            text: "Please fill in all password fields.",
        });
        return;
    }

    if (!passwordPattern.test(newPassword)) {
        Swal.fire({
            icon: "error",
            title: "Weak Password!",
            text: "Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, and one number.",
        });
        return;
    }

    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: "error",
            title: "Password Mismatch!",
            text: "Passwords do not match. Please try again.",
        });
        return;
    }

    fetch("reset_password_process.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `email=${encodeURIComponent(email)}&new_password=${encodeURIComponent(newPassword)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            Swal.fire({
                icon: "success",
                title: "Password Reset Successful!",
                text: "You can now log in with your new password.",
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = "login.php";
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: data.message,
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Server Error!",
            text: "Something went wrong. Try again later.",
        });
    });
});
</script>
