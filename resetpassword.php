<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Reset Your Password</h1>
            <form action="reset_password_process.php?email=<?php echo isset($_GET['email']) ? $_GET['email'] : ''; ?>" method="post">
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
            <img src="images/Drafter Logo Cropped.png" alt="Drafter AutoTech Logo">
        </div>
    </div>
</body>
</html>
<?php
// Fetch user email and OTP from query string or session
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    // Optionally, use $email to prefill the form or verify the user
} else {
    echo "Invalid access.";
}
?>
