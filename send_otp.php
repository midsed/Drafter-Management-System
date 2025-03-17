<?php
session_start();
require_once "dbconnect.php";
require_once "mail_function.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $maxAttempts = 3;
    $lockoutDuration = 24 * 60 * 60; // 24 hours in seconds
    $currentTimestamp = time();

    // STEP 1: Check if Email Exists in the Database
    $stmt = $conn->prepare("SELECT UserID FROM user WHERE Email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die(json_encode(["status" => "error", "message" => "Database error."]));
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // If email is NOT found in the user table, return an error
        echo json_encode(["status" => "error", "message" => "This email is not registered."]);
        exit();
    }

    $user = $result->fetch_assoc();
    $userID = $user['UserID'];
    $stmt->close();

    // STEP 2: Check OTP Attempts & Lockout
    $stmt = $conn->prepare("SELECT otp_attempts, otp_timestamp FROM user WHERE UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $attempts = $user['otp_attempts'] ?? 0;
    $lastAttempt = strtotime($user['otp_timestamp'] ?? '0000-00-00 00:00:00');

    if ($attempts >= $maxAttempts && ($currentTimestamp - $lastAttempt) < $lockoutDuration) {
        // If too many failed attempts, block for 24 hours
        echo json_encode(["status" => "blocked", "message" => "Too many failed attempts. Try again after 24 hours."]);
        exit();
    }

    // STEP 3: Generate OTP
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date("Y-m-d H:i:s", $currentTimestamp + 120); // OTP expires in 2 minutes

    // STEP 4: Store OTP in Database
    if (($currentTimestamp - $lastAttempt) > $lockoutDuration) {
        // Reset attempts after 24 hours
        $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_attempts = 1, otp_timestamp = NOW() WHERE UserID = ?");
    } else {
        $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_attempts = otp_attempts + 1, otp_timestamp = NOW() WHERE UserID = ?");
    }

    $stmt->bind_param("si", $otp, $userID);
    $stmt->execute();
    $stmt->close();

    // STEP 5: Send OTP Email
    $subject = "Your OTP Code";
    $message = "<p>Your OTP for password reset is <strong>$otp</strong>. It is valid for 2 minutes.</p>";

    if (sendMail($email, $subject, $message)) {
        echo json_encode(["status" => "success", "message" => "OTP sent successfully!", "attempts" => $attempts + 1]);
    } else {
        error_log("Failed to send OTP email.");
        die(json_encode(["status" => "error", "message" => "Error sending email."]));
    }

    $conn->close();
}
?>
