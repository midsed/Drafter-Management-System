<?php
session_start();
require_once "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['otp'])) {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Fetch OTP data
    $stmt = $conn->prepare("SELECT otp, otp_attempts, TIMESTAMPDIFF(MINUTE, otp_timestamp, NOW()) AS otp_age FROM user WHERE email = ?");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($storedOtp, $otpAttempts, $otpAge);
    $stmt->fetch();
    $stmt->close();

    if ($storedOtp === null) {
        die("Email not found.");
    }

    if ($otpAge > 10) {
        die("OTP expired. Request a new one.");
    }

    if ($otpAttempts >= 3) {
        die("Too many failed attempts. Try again after 24 hours.");
    }

    if ($otp == $storedOtp) {
        // ✅ Store verified email in session
        $_SESSION['verified_email'] = $email;

        // Reset OTP attempts after successful verification
        $stmt = $conn->prepare("UPDATE user SET otp_attempts = 0 WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: resetpassword.php");
        exit;
    } else {
        // Increment OTP attempts
        $otpAttempts++;
        $stmt = $conn->prepare("UPDATE user SET otp_attempts = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("is", $otpAttempts, $email);
            $stmt->execute();
            $stmt->close();
        }

        die("Invalid OTP. Attempts left: " . (3 - $otpAttempts));
    }
}
?>
