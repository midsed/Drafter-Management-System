<?php
// logging.php

function logAction($conn, $userId, $actionType) {
    $stmt = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $userId, $actionType); // 'i' for integer (userId), 's' for string (actionType)
    $stmt->execute();
    $stmt->close();
}
?>