<?php
session_start();
include('dbconnect.php');

// Ensure the user is logged in
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 
// Get user details from session
$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];
$roleType = $_SESSION['RoleType'];

/**
 * Function to log actions
 */
function logAction($conn, $userID, $username, $roleType, $actionType) {
    $timestamp = date("Y-m-d H:i:s");

    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, Timestamp) 
                                VALUES (?, ?, ?, ?, ?)");

    if (!$logQuery) {
        die("Error preparing log statement: " . $conn->error);
    }

    $logQuery->bind_param("issss", $userID, $username, $roleType, $actionType, $timestamp);
    $logQuery->execute();
    $logQuery->close();
}

// Process the request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service_id'])) {
    $serviceID = intval($_POST['service_id']);

    // Retrieve the service type before re-listing
    $serviceType = "Unknown Service";
    $getServiceQuery = $conn->prepare("SELECT Type FROM service WHERE ServiceID = ?");
    
    if ($getServiceQuery) {
        $getServiceQuery->bind_param("i", $serviceID);
        $getServiceQuery->execute();
        $getServiceQuery->store_result(); // Prevents sync issues
        $getServiceQuery->bind_result($serviceType);
        $getServiceQuery->fetch();
        $getServiceQuery->close();
    }

    // Update the service status (re-list)
    $relistServiceQuery = $conn->prepare("UPDATE service SET Archived = 0 WHERE ServiceID = ?");
    
    if ($relistServiceQuery) {
        $relistServiceQuery->bind_param("i", $serviceID);
        if ($relistServiceQuery->execute()) {
            // Log the re-list action (WITHOUT ServiceID)
            logAction($conn, $userID, $username, $roleType, "Re-list $serviceType");
            
            echo "Service '$serviceType' re-listed successfully!";
        } else {
            echo "Failed to re-list service.";
        }
        $relistServiceQuery->close();
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
