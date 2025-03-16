<?php
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

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

function logAction($conn, $userID, $username, $roleType, $actionType, $partID = NULL) {
    $timestamp = date("Y-m-d H:i:s");
    $logQuery = $conn->prepare("INSERT INTO logs (UserID, ActionBy, RoleType, ActionType, PartID, Timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    $logQuery->bind_param("isssis", $userID, $username, $roleType, $actionType, $partID, $timestamp);
    $logQuery->execute();
    $logQuery->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $supplier_name = trim($_POST['supplier_name']);
    $supplier_email = trim($_POST['supplier_email']);
    $supplier_phone = trim($_POST['supplier_phone']);
    $supplier_address = trim($_POST['supplier_address']);

    $partUpdated = false;
    $supplierUpdated = false;

    $partQuery = $conn->prepare("SELECT Media FROM part WHERE PartID = ?");
    $partQuery->bind_param("i", $part_id);
    $partQuery->execute();
    $existingPart = $partQuery->get_result()->fetch_assoc();
    $partQuery->close();

    if (!$existingPart) {
        die("Error: Part not found.");
    }

    $imageName = $existingPart['Media'];

    if (!empty($_FILES["part_image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
    
        $imageFileType = strtolower(pathinfo($_FILES["part_image"]["name"], PATHINFO_EXTENSION));
        $imageName = basename($_FILES["part_image"]["name"]);
        $target_file = $target_dir . $imageName;
    
        $allowedTypes = ["jpg", "jpeg", "png"];
        if (!in_array($imageFileType, $allowedTypes)) {
            die("Error: Invalid file type. Only JPG, JPEG, & PNG allowed.");
        }
    
        if ($_FILES["part_image"]["error"] !== UPLOAD_ERR_OK) {
            die("Error: Upload failed with error code " . $_FILES["part_image"]["error"]);
        }
    
        if (move_uploaded_file($_FILES["part_image"]["tmp_name"], $target_file)) {
            $imageName = $target_file;
        } else {
            die("Error: Failed to move uploaded file.");
        }
    }
    

    $updatePartQuery = $conn->prepare("UPDATE part SET Name = ?, Price = ?, Quantity = ?, Make = ?, Model = ?, YearModel = ?, Category = ?, Authenticity = ?, PartCondition = ?, ItemStatus = ?, Location = ?, Description = ?, Media = ? WHERE PartID = ?");
    $updatePartQuery->bind_param("sdissssssssssi", $part_name, $part_price, $quantity, $make, $model, $year_model, $category, $authenticity, $part_condition, $item_status, $location, $description, $imageName, $part_id);
    $updatePartQuery->execute();
    $updatePartQuery->close();

    logAction($conn, $userID, $username, $roleType, "Update Parts", $part_id);
    $partUpdated = true;

    if (!empty($supplier_name)) {
        $supplierQuery = $conn->prepare("SELECT SupplierID FROM supplier WHERE CompanyName = ?");
        $supplierQuery->bind_param("s", $supplier_name);
        $supplierQuery->execute();
        $supplierQuery->store_result();

        if ($supplierQuery->num_rows > 0) {
            $supplierQuery->bind_result($supplier_id);
            $supplierQuery->fetch();
        } else {
            $insertSupplierQuery = $conn->prepare("INSERT INTO supplier (CompanyName, Email, PhoneNumber, Address) VALUES (?, ?, ?, ?)");
            $insertSupplierQuery->bind_param("ssss", $supplier_name, $supplier_email, $supplier_phone, $supplier_address);
            $insertSupplierQuery->execute();
            $supplier_id = $insertSupplierQuery->insert_id;
            $insertSupplierQuery->close();
            logAction($conn, $userID, $username, $roleType, "Add New Supplier", null);
        }
        $supplierQuery->close();

        $updateSupplierQuery = $conn->prepare("UPDATE supplier SET Email = ?, PhoneNumber = ?, Address = ? WHERE SupplierID = ?");
        $updateSupplierQuery->bind_param("sssi", $supplier_email, $supplier_phone, $supplier_address, $supplier_id);
        $updateSupplierQuery->execute();
        $updateSupplierQuery->close();
        
        logAction($conn, $userID, $username, $roleType, "Update Supplier", null);
        $supplierUpdated = true;

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
