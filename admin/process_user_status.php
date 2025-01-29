<?php
include('dbconnect.php');

if (isset($_GET['UserID']) && isset($_GET['status'])) {
    $userID = $_GET['UserID'];
    $status = $_GET['status'];

    $sql = "UPDATE user SET Status = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $userID);
    $stmt->execute();

    header('Location: usersedit.php?UserID=' . $userID);
}
?>
