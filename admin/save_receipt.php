<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID']) || empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or empty cart']);
    exit();
}

$userID = $_SESSION['UserID'];
$currentDateTime = date('Y-m-d H:i:s');

// ✅ Step 1: Retrieve User Information
$userQuery = $conn->prepare("SELECT FName, LName, RoleType FROM user WHERE UserID = ?");
$userQuery->bind_param("i", $userID);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();
$userQuery->close();

if (!$userData) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$userFullName = $userData['FName'] . ' ' . $userData['LName'];
$roleType = $userData['RoleType'];

// ✅ Step 2: Insert Receipt into `receipt` Table (Main Receipt Record)
$receiptStmt = $conn->prepare("INSERT INTO receipt (RetrievedBy, RetrievedDate, DateAdded, UserID, RoleType) VALUES (?, ?, ?, ?, ?)");
$receiptStmt->bind_param("sssis", $userFullName, $currentDateTime, $currentDateTime, $userID, $roleType);
$receiptStmt->execute();
$receiptID = $receiptStmt->insert_id;
$receiptStmt->close();

if (!$receiptID) {
    echo json_encode(['success' => false, 'message' => 'Failed to create receipt']);
    exit();
}

// ✅ Step 3: Store Each PartID, Location, and Quantity in `receipt` Table
foreach ($_SESSION['cart'] as $partID => $part) {
    $location = $part['Location'];  // ✅ Use location directly from session
    $quantity = $part['Quantity'];  // ✅ Use quantity directly from session

    // ✅ Store only PartID, Location, and Quantity in `receipt` table
    $stmt = $conn->prepare("INSERT INTO receipt (ReceiptID, PartID, Location, Quantity, RetrievedBy, RetrievedDate, UserID, RoleType) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisisiss", $receiptID, $partID, $location, $quantity, $userFullName, $currentDateTime, $userID, $roleType);
    $stmt->execute();
    $stmt->close();
}

// ✅ Step 4: Clear the Cart After Storing
unset($_SESSION['cart']);

echo json_encode(['success' => true, 'receiptID' => $receiptID]);
?>
