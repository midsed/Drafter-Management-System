<?php
session_start();
require_once "dbconnect.php";

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];
$roleType = $_SESSION['RoleType'];

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service_id'])) {
    $service_id = intval($_POST['service_id']);

    $serviceType = "Unknown"; 

    $serviceQuery = $conn->prepare("SELECT Type FROM service WHERE ServiceID = ?");
    
    if (!$serviceQuery) {
        die("Error preparing service retrieval statement: " . $conn->error);
    }

    $serviceQuery->bind_param("i", $service_id);
    $serviceQuery->execute();
    $serviceQuery->bind_result($serviceType);
    $serviceQuery->fetch();
    $serviceQuery->close();

    $archiveService = $conn->prepare("UPDATE service SET Archived = 1 WHERE ServiceID = ?");
    
    if (!$archiveService) {
        die("Error preparing archive statement: " . $conn->error);
    }

    $archiveService->bind_param("i", $service_id);

    if ($archiveService->execute()) {
        $actionType = "Archived Service: " . $serviceType;
        logAction($conn, $userID, $username, $roleType, $actionType);
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
