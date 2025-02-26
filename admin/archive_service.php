<?php
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'];

    $archiveService = $conn->prepare("UPDATE service SET Archived = 1 WHERE ServiceID = ?");
    $archiveService->bind_param("i", $service_id);

    if ($archiveService->execute()) {
        echo "Service archived successfully!";
    } else {
        echo "Error archiving service.";
    }

    $archiveService->close();
}
$conn->close();
?>
