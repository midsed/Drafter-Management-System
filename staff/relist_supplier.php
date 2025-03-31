<?php
session_start();
require_once "dbconnect.php";

// Ensure the user is logged in as Admin
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

/**
 * Function to log actions
 */
function logAction($conn, $userID, $username, $roleType, $actionType) {
    $timestamp = date("Y-m-d H:i:s");
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, Timestamp) VALUES (?, ?, ?, ?, ?)");
    if (!$logQuery) {
        die("Error preparing log statement: " . $conn->error);
    }
    $logQuery->bind_param("issss", $userID, $username, $roleType, $actionType, $timestamp);
    $logQuery->execute();
    $logQuery->close();
}

// Process the request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $supplierID = intval($_POST['id']);

    // Retrieve the company name for this supplier
    $supplierQuery = $conn->prepare("SELECT CompanyName FROM supplier WHERE SupplierID = ?");
    if (!$supplierQuery) {
        die(json_encode(['status' => 'error', 'message' => 'Error preparing supplier retrieval statement: ' . $conn->error]));
    }
    $supplierQuery->bind_param("i", $supplierID);
    $supplierQuery->execute();
    $result = $supplierQuery->get_result();
    $row = $result->fetch_assoc();
    $companyName = isset($row['CompanyName']) ? $row['CompanyName'] : "Unknown Supplier";
    $supplierQuery->close();

    // Update the archived status (re-list the supplier)
    $sql = "UPDATE supplier SET archived = 0 WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Error preparing re-list query: ' . $conn->error]));
    }
    $stmt->bind_param("i", $supplierID);

    if ($stmt->execute()) {
        // Retrieve session variables for logging
        $userID   = $_SESSION['UserID'];
        $username = $_SESSION['Username'];
        $roleType = $_SESSION['RoleType'];
        // Log the re-list action with the company name
        $actionType = "Re-list Supplier: " . $companyName;
        logAction($conn, $userID, $username, $roleType, $actionType);
        echo json_encode(['status' => 'success', 'message' => "Supplier '$companyName' re-listed successfully!"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error re-listing supplier: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
