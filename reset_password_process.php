<?php
session_start();
require_once 'dbconnect.php';

// Ensure the email is set in the session
if (!isset($_SESSION['verified_email'])) {
    die("Invalid access.");
}

$email = $_SESSION['verified_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'], $_POST['confirm_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }

    // Hash the new password securely
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Prepare update query
    $query = "UPDATE user SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Error preparing update query: " . $conn->error);
    }

    $stmt->bind_param("ss", $hashed_password, $email);
    if ($stmt->execute()) {
        // ✅ Clear session after successful reset
        unset($_SESSION['verified_email']);

        // Redirect to login page after success
        header("Location: login.php");
        exit();
    } else {
        die("Error updating password: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>
