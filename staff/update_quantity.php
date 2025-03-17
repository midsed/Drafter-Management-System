<?php
session_start();
include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partID = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($partID > 0 && $quantity > 0) {
        $stmt = $conn->prepare("UPDATE part SET Quantity = ? WHERE PartID = ?");
        $stmt->bind_param("ii", $quantity, $partID);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>