<?php
include('dbconnect.php');
session_start();

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

    // Retrieve the part name before re-listing
    $partName = "Unknown Part";
    $getPartQuery = $conn->prepare("SELECT Name FROM part WHERE PartID = ?");
    
    if ($getPartQuery) {
        $getPartQuery->bind_param("i", $partID);
        $getPartQuery->execute();
        $getPartQuery->store_result(); // Prevents sync issues
        $getPartQuery->bind_result($partName);
        $getPartQuery->fetch();
        $getPartQuery->close();
    }

    // Update the part status (re-list)
    $query = "UPDATE part SET archived = 0 WHERE PartID = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $partID);
        if ($stmt->execute()) {
            // Log the re-list action
            logAction($conn, $userID, $username, $roleType, "Re-list $partName", $partID);
            
            echo "Part '$partName' re-listed successfully.";
        } else {
            echo "Failed to re-list part.";
        }
        $stmt->close();
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
