<?php
session_start();

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

include('dbconnect.php');

// Set headers for CSV download
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
    $actionType = $conn->real_escape_string($_GET['action_type']);
    $sql .= " AND l.ActionType IN ('$actionType')";
}

if (!empty($_GET['username'])) {
    $username = $conn->real_escape_string($_GET['username']);
    $sql .= " AND u.Username LIKE '%$username%'";
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