<?php
session_start();
include('dbconnect.php');
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') {
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
        return false;
    }

    $logQuery->bind_param("isssis", $userID, $username, $roleType, $actionType, $partID, $timestamp);
    $result = $logQuery->execute();
    $logQuery->close();
    return $result;
}

// Get the JSON data from the request
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$partIds = $data['partIds'] ?? [];
if (empty($partIds)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No parts selected', 'count' => 0]);
    exit();
}

// Get part names for logging
$partDetails = [];
$placeholders = implode(',', array_fill(0, count($partIds), '?'));
$detailsSql = "SELECT PartID, Name FROM part WHERE PartID IN ($placeholders)";
$detailsStmt = $conn->prepare($detailsSql);

if ($detailsStmt) {
    $types = str_repeat('i', count($partIds));
    $detailsStmt->bind_param($types, ...$partIds);
    $detailsStmt->execute();
    $result = $detailsStmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $partDetails[$row['PartID']] = $row['Name'];
    }
    
    $detailsStmt->close();
}

// Prepare and secure the SQL query
$sql = "UPDATE part SET archived = 0 WHERE PartID IN ($placeholders)";
$stmt = $conn->prepare($sql);

// Bind the part IDs to the prepared statement
$types = str_repeat('i', count($partIds));
$stmt->bind_param($types, ...$partIds);
$result = $stmt->execute();
$count = $stmt->affected_rows;
$stmt->close();

// Log each part that was re-listed
$logSuccess = true;
if ($result && $count > 0) {
    foreach ($partIds as $partID) {
        $partName = $partDetails[$partID] ?? "Unknown Part";
        $logResult = logAction($conn, $userID, $username, $roleType, "Re-list $partName", $partID);
        if (!$logResult) {
            $logSuccess = false;
        }
    }
}

$conn->close();

header('Content-Type: application/json');
if ($result) {
    echo json_encode([
        'success' => true, 
        'count' => $count,
        'logStatus' => $logSuccess ? 'success' : 'partialFailure'
    ]);
} else {
    echo json_encode(['error' => 'An error occurred', 'count' => 0]);
}
?>