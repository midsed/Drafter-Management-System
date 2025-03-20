<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
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

// Prepare and secure the SQL query
$placeholders = implode(',', array_fill(0, count($partIds), '?'));
$sql = "UPDATE part SET archived = 0 WHERE PartID IN ($placeholders)";

$stmt = $conn->prepare($sql);

// Bind the part IDs to the prepared statement
$types = str_repeat('i', count($partIds)); // 'i' for integer
$stmt->bind_param($types, ...$partIds);

$result = $stmt->execute();
$count = $stmt->affected_rows;

$stmt->close();
$conn->close();

header('Content-Type: application/json');

if ($result) {
    echo json_encode(['success' => true, 'count' => $count]);
} else {
    echo json_encode(['error' => 'An error occurred', 'count' => 0]);
}
?>