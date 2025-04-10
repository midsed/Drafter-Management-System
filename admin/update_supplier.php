<?php
session_start();
require_once "dbconnect.php";
include('../shared/detailed_logging.php');

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

if (!isset($_GET['id'])) {
    die("Supplier ID not provided.");
}

$supplierID = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $companyName = $_POST['supplier'];
    $phoneNumber = $_POST['phone'];

    // Validate inputs
    if (empty($email) || empty($companyName) || empty($phoneNumber)) {
        die("All fields are required.");
    }

    // Update supplier details
    $sql = "UPDATE supplier SET Email = ?, CompanyName = ?, PhoneNumber = ? WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing the query: " . $conn->error);
    }
    $stmt->bind_param("sssi", $email, $companyName, $phoneNumber, $supplierID);

    if ($stmt->execute()) {
        echo "Success: Supplier updated successfully!";
    } else {
        echo "Error: Failed to update supplier. " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    die("Invalid request method.");
}
?>