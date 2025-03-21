<?php
session_start();
include('dbconnect.php');

// Ensure the user is logged in
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

// Get user details from session
$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];
$roleType = $_SESSION['RoleType'];

/**
 * Function to log actions
 */
function logAction($conn, $userID, $username, $roleType, $actionType, $partID) {
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $partID = intval($_POST['id']);

    // Retrieve the part name before archiving
    $partName = "Unknown Part";
    $getPartQuery = $conn->prepare("SELECT Name FROM part WHERE PartID = ?");
    
    if ($getPartQuery) {
        $getPartQuery->bind_param("i", $partID);
        $getPartQuery->execute();
        $getPartQuery->store_result(); // Ensures results are stored properly
        $getPartQuery->bind_result($partName);
        $getPartQuery->fetch();
        $getPartQuery->close();
    }

    // Archive the part
    $archivePartQuery = $conn->prepare("UPDATE part SET archived = 1 WHERE PartID = ?");
    
    if ($archivePartQuery) {
        $archivePartQuery->bind_param("i", $partID);
        if ($archivePartQuery->execute()) {
            // Log part archiving
            logAction($conn, $userID, $username, $roleType, "Archive $partName", $partID);
            echo "Part '$partName' archived successfully.";
        } else {
            echo "Failed to archive part.";
        }
        $archivePartQuery->close();
    } else {
        echo "Error preparing archive query.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>