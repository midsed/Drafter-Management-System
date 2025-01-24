<?php
session_start();
require_once "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['otp'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    // Check if the OTP entered matches the one stored in session
    if (isset($_SESSION['otp']) && $_SESSION['otp'] == $otp && $_SESSION['email'] == $email) {
        // Redirect to reset password page if OTP matches
        header("Location: resetpassword.php?email=" . urlencode($email));
        exit;
    } else {
        echo "Invalid OTP. Please try again.";
    }
} else {
    echo "Invalid request.";
}
?>
