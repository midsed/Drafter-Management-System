<?php
session_start();
include('dbconnect.php');

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];
$roleType = $_SESSION['RoleType'];

$data = json_decode(file_get_contents('php://input'), true);
$partIds = $data['partIds'] ?? [];
$response = ['success' => false, 'message' => ''];

if (empty($partIds)) {
    $response['message'] = 'No part IDs provided.';
    echo json_encode($response);
    exit();
}

$conn->begin_transaction();
$successful = true;

foreach ($partIds as $partId) {
    if (!is_numeric($partId)) {
        $response['message'] = "Invalid part ID: $partId.";
        $conn->rollback();
        echo json_encode($response);
        exit();
    }
    
    $partId = intval($partId);
    
    // Get part name for logging
    $nameStmt = $conn->prepare("SELECT Name FROM part WHERE PartID = ?");
    $nameStmt->bind_param("i", $partId);
    $nameStmt->execute();
    $nameResult = $nameStmt->get_result();
    $partName = "Unknown Part";
    
    if ($row = $nameResult->fetch_assoc()) {
        $partName = $row['Name'];
    }
    $nameStmt->close();
    
    // Archive the part
    $archiveStmt = $conn->prepare("UPDATE part SET archived = 1 WHERE PartID = ?");
    $archiveStmt->bind_param("i", $partId);
    
    if (!$archiveStmt->execute()) {
        $response['message'] = "Failed to archive part ID: $partId. " . $archiveStmt->error;
        $successful = false;
        $archiveStmt->close();
        $conn->rollback();
        echo json_encode($response);
        exit();
    }
    $archiveStmt->close();
    
    // Log the action
    $timestamp = date("Y-m-d H:i:s");
    $actionType = "Archived part: $partName";
    
    $logStmt = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, Timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    $logStmt->bind_param("isssis", $userID, $username, $roleType, $actionType, $partId, $timestamp);
    
    if (!$logStmt->execute()) {
        $response['message'] = "Failed to log archive action for part ID: $partId. " . $logStmt->error;
        $successful = false;
        $logStmt->close();
        $conn->rollback();
        echo json_encode($response);
        exit();
    }
    $logStmt->close();
}

if ($successful) {
    $conn->commit();
    $count = count($partIds);
    $response['success'] = true;
    $response['message'] = $count . ' part' . ($count != 1 ? 's' : '') . ' archived successfully.';
}

echo json_encode($response);
$conn->close();
?>