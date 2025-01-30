<?php
session_start();
require_once "dbconnect.php";
require_once "mail_function.php"; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT Email FROM user WHERE Email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Database error. Please try again later.");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Email not found.";
        exit;
    }

    // Generate a 6-digit OTP
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = time() + 120; // OTP expires in 2 minutes

    // Store OTP in database
    $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_timestamp = ? WHERE Email = ?");
    if (!$stmt) {
        error_log("Prepare failed (Update): " . $conn->error);
        die("Database error. Please try again later.");
    }

    $stmt->bind_param("sis", $otp, $expiry, $email);
    if ($stmt->execute()) {
        $_SESSION['email'] = $email;
        echo "OTP sent to $email.";
    } else {
        error_log("Failed to store OTP: " . $stmt->error);
        die("Database error. Please try again later.");
    }

    // Send OTP via email
    $subject = "Your OTP Code";
    $message = "<p>Your OTP for password reset is: <strong>$otp</strong>. It is valid for 2 minutes.</p>";

    if (sendMail($email, $subject, $message)) {
        echo "OTP sent successfully.";
    } else {
        error_log("Failed to send OTP email.");
        die("Error sending email. Please check mail logs.");
    }

    $stmt->close();
    $conn->close();
}
?>
