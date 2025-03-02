<?php
session_start();

if (!isset($_SESSION['User  ID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('dbconnect.php');

// Set headers to download the file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="logs_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, ['Log ID', 'Action By', 'Action Type', 'Timestamp']);

// Fetch logs from the database
$sql = "SELECT l.LogsID, u.Username AS ActionBy, l.ActionType, l.Timestamp 
        FROM logs l
        JOIN user u ON l.ActionBy = u.UserID
        ORDER BY l.Timestamp DESC";

$result = $conn->query($sql);

// Check if there are results and write to CSV
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// Close output stream
fclose($output);
exit();
?>