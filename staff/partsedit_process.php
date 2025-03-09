<?php
session_start();
require_once "dbconnect.php"; // Database connection

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$userID = $_SESSION['UserID'];
$userQuery = $conn->prepare("SELECT Username, RoleType FROM user WHERE UserID = ?");
$userQuery->bind_param("i", $userID);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userRow = $userResult->fetch_assoc();
$userQuery->close();

if (!$userRow) {
    die("Error: User not found.");
}

$username = $userRow['Username'];
$roleType = $userRow['RoleType'];

/**
 * Function to log actions
 */
function logAction($conn, $userID, $username, $roleType, $actionType, $partID = NULL) {
    $timestamp = date("Y-m-d H:i:s");
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, Timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    $logQuery->bind_param("isssis", $userID, $username, $roleType, $actionType, $partID, $timestamp);
    $logQuery->execute();
    $logQuery->close();
}

// Process POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Part Details
    $part_id = $_POST['part_id'];
    $part_name = trim($_POST['part_name']);
    $part_price = floatval($_POST['part_price']);
    $quantity = intval($_POST['quantity']);
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $year_model = trim($_POST['year_model']);
    $category = trim($_POST['category']);
    $authenticity = trim($_POST['authenticity']);
    $part_condition = trim($_POST['part_condition']);
    $item_status = trim($_POST['item_status']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    // Supplier Details
    $supplier_name = trim($_POST['supplier_name']);
    $supplier_email = trim($_POST['supplier_email']);
    $supplier_phone = trim($_POST['supplier_phone']);
    $supplier_address = trim($_POST['supplier_address']);

    $partUpdated = false;
    $supplierUpdated = false;

    // Retrieve current part details
    $partQuery = $conn->prepare("SELECT * FROM part WHERE PartID = ?");
    $partQuery->bind_param("i", $part_id);
    $partQuery->execute();
    $existingPart = $partQuery->get_result()->fetch_assoc();
    $partQuery->close();

    if (!$existingPart) {
        die("Error: Part not found.");
    }

    // Compare and update Part Details
    if ($existingPart['Name'] !== $part_name ||
        $existingPart['Price'] != $part_price ||
        $existingPart['Quantity'] != $quantity ||
        $existingPart['Make'] !== $make ||
        $existingPart['Model'] !== $model ||
        $existingPart['YearModel'] !== $year_model ||
        $existingPart['Category'] !== $category ||
        $existingPart['Authenticity'] !== $authenticity ||
        $existingPart['PartCondition'] !== $part_condition ||
        $existingPart['ItemStatus'] !== $item_status ||
        $existingPart['Location'] !== $location ||
        $existingPart['Description'] !== $description) {
        
        $updatePartQuery = $conn->prepare("UPDATE part SET Name = ?, Price = ?, Quantity = ?, Make = ?, Model = ?, YearModel = ?, Category = ?, Authenticity = ?, PartCondition = ?, ItemStatus = ?, Location = ?, Description = ? WHERE PartID = ?");
        $updatePartQuery->bind_param("sdisssssssssi", $part_name, $part_price, $quantity, $make, $model, $year_model, $category, $authenticity, $part_condition, $item_status, $location, $description, $part_id);
        $updatePartQuery->execute();
        $updatePartQuery->close();
        
        logAction($conn, $userID, $username, $roleType, "Update Parts", $part_id);
        $partUpdated = true;
    }

    // Check if the user has provided a supplier name
    if (!empty($supplier_name)) {
        // Check if the supplier already exists
        $supplierQuery = $conn->prepare("SELECT SupplierID FROM supplier WHERE CompanyName = ?");
        $supplierQuery->bind_param("s", $supplier_name);
        $supplierQuery->execute();
        $supplierQuery->store_result();
        
        if ($supplierQuery->num_rows > 0) {
            // Supplier exists, fetch its ID
            $supplierQuery->bind_result($supplier_id);
            $supplierQuery->fetch();
        } else {
            // Supplier does not exist, insert a new one
            $insertSupplierQuery = $conn->prepare("INSERT INTO supplier (CompanyName, Email, PhoneNumber, Address) VALUES (?, ?, ?, ?)");
            $insertSupplierQuery->bind_param("ssss", $supplier_name, $supplier_email, $supplier_phone, $supplier_address);
            $insertSupplierQuery->execute();
            $supplier_id = $insertSupplierQuery->insert_id;
            $insertSupplierQuery->close();
            
            logAction($conn, $userID, $username, $roleType, "Add New Supplier", null);
        }
        $supplierQuery->close();

        // Update supplier details if needed
        $updateSupplierQuery = $conn->prepare("UPDATE supplier SET Email = ?, PhoneNumber = ?, Address = ? WHERE SupplierID = ?");
        $updateSupplierQuery->bind_param("sssi", $supplier_email, $supplier_phone, $supplier_address, $supplier_id);
        $updateSupplierQuery->execute();
        $updateSupplierQuery->close();
        
        logAction($conn, $userID, $username, $roleType, "Update Supplier", null);
        $supplierUpdated = true;

        // Ensure the part is linked to the correct supplier
        $updatePartSupplierQuery = $conn->prepare("UPDATE part SET SupplierID = ? WHERE PartID = ?");
        $updatePartSupplierQuery->bind_param("ii", $supplier_id, $part_id);
        $updatePartSupplierQuery->execute();
        $updatePartSupplierQuery->close();
    }

    if ($partUpdated || $supplierUpdated) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<style>
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
            .swal2-popup { font-family: "Inter", sans-serif !important; }
            .swal2-title { font-weight: 700 !important; }
            .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
            .swal2-confirm { font-weight: bold !important; background-color: #6c5ce7 !important; color: white !important; }
        </style>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Success!",
                    text: "Part details updated successfully!",
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6c5ce7"
                }).then(() => {
                    window.location = "parts.php";
                });
            });
        </script>';
    }
    exit();
}

$conn->close();
?>
