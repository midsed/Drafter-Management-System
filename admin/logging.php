<?php
// logging.php

function logAction($conn, $userId, $actionType, $ipAddress, $pageURL) {
    $stmt = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, IPAddress, PageURL, Timestamp) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $userId, $actionType, $ipAddress, $pageURL); // 'i' for integer, 's' for string
    $stmt->execute();
    $stmt->close();
}
?>