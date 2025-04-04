<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once 'dbconnect.php';

header("Content-Type: application/json");

if (!isset($_SESSION['verified_email'])) {
    echo json_encode(["status" => "error", "message" => "Invalid access."]);
    exit();
}

$email = $_SESSION['verified_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $new_password = trim($_POST['new_password']);

    if (strlen($new_password) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters."]);
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

        // Retrieve user details for logging
        $userQuery = "SELECT UserID, Username, RoleType FROM user WHERE email = ?";
        $userStmt = $conn->prepare($userQuery);
        if ($userStmt) {
            $userStmt->bind_param("s", $email);
            $userStmt->execute();
            $resultUser = $userStmt->get_result();
            $userRow = $resultUser->fetch_assoc();
            $userStmt->close();

            if ($userRow) {
                $actionBy = $userRow['Username'];
                $userID   = $userRow['UserID'];
                $roleType = $userRow['RoleType'];
            } else {
                $actionBy = $email;
                $userID   = 0; // default to 0 if not found
                $roleType = 'Staff';
            }
        } else {
            $actionBy = $email;
            $userID   = 0;
            $roleType = 'Staff';
        }

        $timestamp  = date("Y-m-d H:i:s");
        $actionType = "Reset Password for: " . $email;

        // Insert log entry (PartID is not applicable so we use NULL)
        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, NULL, ?)");
        if ($log) {
            // "sssis" means: string, string, string, integer, string
            $log->bind_param("sssis", $actionBy, $actionType, $timestamp, $userID, $roleType);
            $log->execute();
            $log->close();
        } else {
            // Optionally, you could log this error elsewhere
            error_log("Log prepare failed: " . $conn->error);
        }

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
