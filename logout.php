<?php
session_start();
require_once "dbconnect.php";
require_once "shared/detailed_logging.php";

// Log the logout action if user is logged in
if (isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];
    $username = $_SESSION['Username'] ?? 'Unknown User';
    $roleType = $_SESSION['RoleType'] ?? 'Unknown Role';
    
    // Log the logout action
    $timestamp = date("Y-m-d H:i:s");
    $actionType = "Logged Out";
    
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, Timestamp) VALUES (?, ?, ?, ?, ?)");
    $logQuery->bind_param("issss", $userID, $username, $roleType, $actionType, $timestamp);
    $logQuery->execute();
    $logQuery->close();
}

// Clear session and redirect
session_unset();
session_destroy();
header("Location: \Drafter-Management-System\login.php");
exit();
?>
