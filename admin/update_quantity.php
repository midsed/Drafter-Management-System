<?php
session_start();
include('dbconnect.php');
include('../shared/detailed_logging.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partID = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($partID > 0 && $quantity > 0) {
        // Get the current quantity for logging purposes
        $currentQtyStmt = $conn->prepare("SELECT Quantity, Name FROM part WHERE PartID = ?");
        $currentQtyStmt->bind_param("i", $partID);
        $currentQtyStmt->execute();
        $currentQtyResult = $currentQtyStmt->get_result();
        $partData = $currentQtyResult->fetch_assoc();
        $currentQtyStmt->close();
        
        $oldQuantity = $partData['Quantity'];
        $partName = $partData['Name'];
        
        // Update the quantity
        $stmt = $conn->prepare("UPDATE part SET Quantity = ?, LastUpdated = NOW() WHERE PartID = ?");
        $stmt->bind_param("ii", $quantity, $partID);
        
        if ($stmt->execute()) {
            // Log the quantity change
            $userID = $_SESSION['UserID'];
            $userQuery = $conn->prepare("SELECT Username, RoleType FROM user WHERE UserID = ?");
            $userQuery->bind_param("i", $userID);
            $userQuery->execute();
            $userResult = $userQuery->get_result();
            $userRow = $userResult->fetch_assoc();
            $userQuery->close();
            
            $username = $userRow['Username'];
            $roleType = $userRow['RoleType'];
            
            // Log the action with detailed information
            $actionType = "Update Part Quantity";
            $fieldName = "Quantity";
            logDetailedAction($conn, $userID, $username, $roleType, $actionType, $partID, $oldQuantity, $quantity, $fieldName);
            
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