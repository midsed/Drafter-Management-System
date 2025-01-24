<?php
session_start();
require_once "dbconnect.php";
require_once "mail_function.php"; // Assuming sendMail function is here

header("Access-Control-Allow-Origin: *"); // Allow cross-origin requests
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT Email FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the email exists in the database
    if ($result->num_rows === 0) {
        echo "Email not found in our system.";
        exit;
    }

    // Generate a 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Store OTP in session and update database
    $_SESSION['otp'] = $otp;
    $_SESSION['email'] = $email;

    $stmt = $conn->prepare("UPDATE user SET OTP = ? WHERE Email = ?");
    $stmt->bind_param("ss", $otp, $email);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Send OTP via email
        $subject = "Your OTP Code";
        
        // Try sending the OTP via email
        if (sendMail($email, $subject, $otp)) {
            echo "OTP sent successfully to your email.";
        } else {
            error_log("Failed to send OTP to: $email");  // Log the failure for debugging
            echo "Failed to send OTP. Please check error logs.";
        }
    } else {
        // If OTP failed to store in DB
        error_log("Failed to update OTP in database for: $email");  // Log the failure for debugging
        echo "Failed to store OTP. Ensure email exists in the system.";
    }

    // Close database connection
    $stmt->close();
    $conn->close();
} else {
    // Invalid request
    echo "Invalid request.";
}
?>
