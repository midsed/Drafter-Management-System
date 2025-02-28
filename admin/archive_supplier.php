<?php
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID'])) {
    die(json_encode(['status' => 'error', 'message' => 'Access Denied: Please log in.']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplierID = $_POST['id'];

    // Update the archived status
    $sql = "UPDATE supplier SET archived = 1 WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Error preparing the query: ' . $conn->error]));
    }
    $stmt->bind_param("i", $supplierID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Supplier archived successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error archiving supplier: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>