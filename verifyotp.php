<?php
session_start();
require_once "dbconnect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['otp'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

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
        $stmt = $conn->prepare("UPDATE user SET otp_attempts = 0 WHERE email = ?");
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $_SESSION['verified_email'] = $email; //change
        echo json_encode(["status" => "success"]);
        exit;
    } else {
        $otpAttempts++;
        $stmt = $conn->prepare("UPDATE user SET otp_attempts = ? WHERE email = ?");
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }
        $stmt->bind_param("is", $otpAttempts, $email);
        $stmt->execute();

        die("Invalid OTP. Attempts left: " . (3 - $otpAttempts));
    }
}
?>
