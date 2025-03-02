<?php
session_start();
require_once "dbconnect.php";

// Ensure the user is logged in
if (!isset($_SESSION['UserID'])) {
    die("Error: Unauthorized access.");
}

// Get user details from session
$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];
$roleType = $_SESSION['RoleType'];

/**
 * Function to log actions
 */
function logAction($conn, $userID, $username, $roleType, $actionType, $partID = null) {
    $timestamp = date("Y-m-d H:i:s");

    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, Timestamp) 
                                VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$logQuery) {
        die("Error preparing log statement: " . $conn->error);
    }

    $logQuery->bind_param("isssis", $userID, $username, $roleType, $actionType, $partID, $timestamp);
    $logQuery->execute();
    $logQuery->close();
}

// Process the request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service_id'])) {
    $service_id = intval($_POST['service_id']);

    // Retrieve Service Type and PartID
    $serviceType = "Unknown"; // Default in case of failure
    $partID = null;

    $serviceQuery = $conn->prepare("SELECT Type, PartID FROM service WHERE ServiceID = ?");
    
    if (!$serviceQuery) {
        die("Error preparing service retrieval statement: " . $conn->error);
    }

    $serviceQuery->bind_param("i", $service_id);
    $serviceQuery->execute();
    $serviceQuery->bind_result($serviceType, $partID);
    $serviceQuery->fetch();
    $serviceQuery->close();

    // Archive the service
    $archiveService = $conn->prepare("UPDATE service SET Archived = 1 WHERE ServiceID = ?");
    
    if (!$archiveService) {
        die("Error preparing archive statement: " . $conn->error);
    }

    $archiveService->bind_param("i", $service_id);

    if ($archiveService->execute()) {
        $actionType = "Archive " . $serviceType; // "Archive Oil Change", etc.
        logAction($conn, $userID, $username, $roleType, $actionType, $partID);
        echo "Service archived successfully!";
    } else {
        echo "Error archiving service: " . $conn->error;
    }

    $archiveService->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
