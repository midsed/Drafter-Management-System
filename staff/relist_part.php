<?php
include('dbconnect.php');
session_start();

if (!isset($_SESSION['UserID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];
$roleType = $_SESSION['RoleType'];

function logAction($conn, $userID, $username, $roleType, $actionType, $partID) {
    $timestamp = date("Y-m-d H:i:s");
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, Timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$logQuery) {
        return false;
    }
    $logQuery->bind_param("isssis", $userID, $username, $roleType, $actionType, $partID, $timestamp);
    $result = $logQuery->execute();
    $logQuery->close();
    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $partID = intval($_POST['id']);
    $partName = "Unknown Part";
    
    $getPartQuery = $conn->prepare("SELECT Name FROM part WHERE PartID = ? AND archived = 1");
    
    if ($getPartQuery) {
        $getPartQuery->bind_param("i", $partID);
        $getPartQuery->execute();
        $getPartQuery->store_result();
        
        if ($getPartQuery->num_rows > 0) {
            $getPartQuery->bind_result($partName);
            $getPartQuery->fetch();
            $getPartQuery->close();

            $query = "UPDATE part SET archived = 0 WHERE PartID = ?";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $partID);
                if ($stmt->execute()) {
                    logAction($conn, $userID, $username, $roleType, "Re-list $partName", $partID);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => "Part '$partName' re-listed successfully."]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => "Failed to re-list part: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => "Error preparing update statement: " . $conn->error]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Part not found or is not archived."]);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => "Error preparing select statement: " . $conn->error]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => "Invalid request."]);
}

$conn->close();
?>