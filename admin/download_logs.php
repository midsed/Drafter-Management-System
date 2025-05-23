<?php
session_start();
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('dbconnect.php');

// --- Insert a log record for the "Download Logs" action ---
$userID = $_SESSION['UserID'];
// Ensure you have stored the username in session (change the key if needed)
$username = isset($_SESSION['Username']) ? $_SESSION['Username'] : 'Unknown';
$role = $_SESSION['RoleType'];
$actionTypeDownload = "Download Logs";
$timestamp = date("Y-m-d H:i:s");

$sqlLog = "INSERT INTO logs (`UserID`, `ActionBy`, `RoleType`, `ActionType`, `Timestamp`) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sqlLog);
if ($stmt) {
    $stmt->bind_param("issss", $userID, $username, $role, $actionTypeDownload, $timestamp);
    $stmt->execute();
    $stmt->close();
} else {
    // Optionally handle errors here or log them
    error_log("Log insert error: " . $conn->error);
}

// --- Continue with CSV download ---
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="logs_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// CSV header
fputcsv($output, ['Log ID', 'Action By', 'Action Type', 'Timestamp']);

// Build the SQL query (same as logs.php but without LIMIT)
$sql = "SELECT l.LogsID, u.Username AS ActionBy, l.ActionType, l.Timestamp 
        FROM logs l
        JOIN user u ON l.UserID = u.UserID
        WHERE 1=1";

// Apply filters from URL parameters
if (!empty($_GET['action_type'])) {
    // Escape the action_type parameter; note that if multiple values are passed,
    // you might need to modify this further. For now, we assume a single value.
    $actionType = $conn->real_escape_string($_GET['action_type']);
    $sql .= " AND l.ActionType IN ('$actionType')";
}

if (!empty($_GET['username'])) {
    $usernameFilter = $conn->real_escape_string($_GET['username']);
    $sql .= " AND u.Username LIKE '%$usernameFilter%'";
}

if (!empty($_GET['start_date'])) {
    $startDate = $conn->real_escape_string($_GET['start_date']);
    $sql .= " AND l.Timestamp >= '$startDate'";
}

if (!empty($_GET['end_date'])) {
    $endDate = $conn->real_escape_string($_GET['end_date']);
    $sql .= " AND l.Timestamp <= '$endDate 23:59:59'";
}

$sql .= " ORDER BY l.Timestamp DESC";

// Execute the query
$result = $conn->query($sql);

// Write data to CSV
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No logs found.']);
}

// Close resources
fclose($output);
$conn->close();
exit();
?>
