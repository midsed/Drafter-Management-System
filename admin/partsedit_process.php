<?php
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Part Details
    $part_id = $_POST['part_id'];  
    $part_name = $_POST['part_name'];  
    $part_price = $_POST['part_price'];
    $quantity = $_POST['quantity'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year_model = $_POST['year_model'];
    $description = $_POST['description'];
    $part_condition = $_POST['part_condition']; 
    
    // Supplier Details
    $supplier_name = $_POST['supplier_name'];
    $supplier_email = $_POST['supplier_email'];
    $supplier_phone = $_POST['supplier_phone'];
    $supplier_address = $_POST['supplier_address'];

    // Image upload handling
    if (!empty($_FILES['part_image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["part_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["part_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                die("Error uploading file.");
            }
        } else {
            die("Invalid file type. Allowed types: JPG, JPEG, PNG, GIF.");
        }
    } else {
        // If no new image is uploaded, keep the existing image
        $query = $conn->prepare("SELECT Media FROM part WHERE PartID = ?");
        $query->bind_param("i", $part_id);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $image_path = $row['Media']; // Use the column name Media
    }

    // Update Part Details
    $stmt = $conn->prepare("UPDATE part SET Name = ?, Price = ?, Quantity = ?, Make = ?, Model = ?, YearModel = ?, PartCondition = ?, Description = ?, Media = ? WHERE PartID = ?");
    
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }

    $stmt->bind_param("sdissssssi", $part_name, $part_price, $quantity, $make, $model, $year_model, $part_condition, $description, $image_path, $part_id);

    if ($stmt->execute()) {
        // Fetch the SupplierID associated with the part
        $supplier_query = $conn->prepare("SELECT SupplierID FROM part WHERE PartID = ?");
        $supplier_query->bind_param("i", $part_id);
        $supplier_query->execute();
        $supplier_result = $supplier_query->get_result();
        $supplier_row = $supplier_result->fetch_assoc();
        $supplier_id = $supplier_row['SupplierID'];
        $supplier_query->close();

        // Update Supplier Details
        $supplier_stmt = $conn->prepare("UPDATE supplier SET CompanyName = ?, Email = ?, PhoneNumber = ?, Address = ? WHERE SupplierID = ?");
        
        if ($supplier_stmt === false) {
            die('Error preparing supplier statement: ' . $conn->error);
        }

        $supplier_stmt->bind_param("ssssi", $supplier_name, $supplier_email, $supplier_phone, $supplier_address, $supplier_id);

        if ($supplier_stmt->execute()) {
            echo "<script>alert('Part and supplier updated successfully!'); window.location.href='parts.php';</script>";
        } else {
            echo "<script>alert('Error updating supplier.'); window.history.back();</script>";
        }

        $supplier_stmt->close();
    } else {
        echo "<script>alert('Error updating part.'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>