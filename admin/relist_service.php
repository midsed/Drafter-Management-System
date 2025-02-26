<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'];

    $relistService = $conn->prepare("UPDATE service SET Archived = 0 WHERE ServiceID = ?");
    $relistService->bind_param("i", $service_id);

    if ($relistService->execute()) {
        echo "Service relisted successfully!";
    } else {
        echo "Error relisting service.";
    }

    $relistService->close();
}
$conn->close();
?>
