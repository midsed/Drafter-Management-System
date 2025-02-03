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
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Reset Your Password</h1>
            <form action="reset_password_process.php" method="post">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                
                <label for="new_password">Enter Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="New Password" required>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Verify Password" required>

                <button type="submit" name="reset_password">Reset Password</button>

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
