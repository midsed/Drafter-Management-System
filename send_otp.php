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

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT OTP, otp_attempts, otp_timestamp FROM user WHERE Email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die(json_encode(["status" => "error", "message" => "Database error."]));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Email not found."]);
        exit();
    }

    $attempts = $user['otp_attempts'];
    $lastAttempt = strtotime($user['otp_timestamp']);

    // Check if user is blocked for 24 hours
    if ($attempts >= $maxAttempts && ($currentTimestamp - $lastAttempt) < $lockoutDuration) {
        echo json_encode(["status" => "blocked"]);
        exit();
    }

    // Generate a 6-digit OTP
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = $currentTimestamp + 120; // OTP expires in 2 minutes

    // Store OTP in database and update attempt count
    if (($currentTimestamp - $lastAttempt) > $lockoutDuration) {
        // Reset attempts after 24 hours
        $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_attempts = 1, otp_timestamp = NOW() WHERE Email = ?");
    } else {
        $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_attempts = otp_attempts + 1, otp_timestamp = NOW() WHERE Email = ?");
    }
    $stmt->bind_param("ss", $otp, $email);
    $stmt->execute();

    // Send OTP via email
    $subject = "Your OTP Code";
    $message = "<p>Your OTP for password reset is <strong>$otp</strong>. It is valid for 2 minutes.</p>";

    if (sendMail($email, $subject, $message)) {
        echo json_encode(["status" => "success", "attempts" => $attempts + 1]);
    } else {
        error_log("Failed to send OTP email.");
        die(json_encode(["status" => "error", "message" => "Error sending email."]));
    }

    $stmt->close();
    $conn->close();
}
?>
