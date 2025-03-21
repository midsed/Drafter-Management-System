<?php
session_start();
require_once "dbconnect.php";

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplierID = $_POST['id'];

    // Update the archived status
    $sql = "UPDATE supplier SET archived = 0 WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Error preparing the query: ' . $conn->error]));
    }
    $stmt->bind_param("i", $supplierID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Supplier re-listed successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error re-listing supplier: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>