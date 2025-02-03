<?php
session_start();
require_once 'dbconnect.php';

header("Content-Type: application/json");

if (!isset($_SESSION['verified_email'])) {
    echo json_encode(["status" => "error", "message" => "Invalid access."]);
    exit();
}

$email = $_SESSION['verified_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $new_password = trim($_POST['new_password']);

    if (strlen($new_password) < 6) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters."]);
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $query = "UPDATE user SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("ss", $hashed_password, $email);
    if ($stmt->execute()) {
        unset($_SESSION['verified_email']);

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating password: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn->close();
?>
