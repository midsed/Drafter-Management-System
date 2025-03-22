<?php
session_start();
require_once "dbconnect.php";

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $client_email = $_POST['client_email']; 
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $pNumber = $_POST['pNumber'];
    $part_name = isset($_POST['part_name']) ? $_POST['part_name'] : NULL;
    $staff_name = $_SESSION['Username'];

    // ✅ Get the existing email for this ServiceID
    $checkServiceEmail = $conn->prepare("SELECT ClientEmail FROM service WHERE ServiceID = ?");
    $checkServiceEmail->bind_param("i", $service_id);
    $checkServiceEmail->execute();
    $serviceEmailResult = $checkServiceEmail->get_result();
    $existingEmail = $serviceEmailResult->fetch_assoc()['ClientEmail'];
    $checkServiceEmail->close();

    // ✅ If the email was changed, check if it exists for another ServiceID
    if ($client_email !== $existingEmail) {
        $checkEmailQuery = $conn->prepare("SELECT ServiceID FROM service WHERE ClientEmail = ? AND ServiceID != ?");
        $checkEmailQuery->bind_param("si", $client_email, $service_id);
        $checkEmailQuery->execute();
        $checkEmailQuery->store_result();

        if ($checkEmailQuery->num_rows > 0) {
            // ❌ Prevent email update if it's already linked to another service
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Error!",
                        text: "This email is already linked to another service. Please use a different email.",
                        icon: "error",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.history.back();
                    });
                });
            </script>';
            exit();
        }
        $checkEmailQuery->close();
    }

    // ✅ If email is new, insert it into the `client` table or update existing client
    $checkClientQuery = $conn->prepare("SELECT ClientEmail FROM client WHERE ClientEmail = ?");
    $checkClientQuery->bind_param("s", $client_email);
    $checkClientQuery->execute();
    $checkClientQuery->store_result();

    if ($checkClientQuery->num_rows == 0) {
        // Insert new client
        $insertClientQuery = $conn->prepare("INSERT INTO client (ClientEmail, FName, LName, PhoneNumber) VALUES (?, ?, ?, ?)");
        $insertClientQuery->bind_param("ssss", $client_email, $fName, $lName, $pNumber);
        $insertClientQuery->execute();
        $insertClientQuery->close();
    } else {
        // Update existing client details
        $updateClientQuery = $conn->prepare("UPDATE client SET FName = ?, LName = ?, PhoneNumber = ? WHERE ClientEmail = ?");
        $updateClientQuery->bind_param("ssss", $fName, $lName, $pNumber, $client_email);
        $updateClientQuery->execute();
        $updateClientQuery->close();
    }
    $checkClientQuery->close();

    // ✅ Update the service (Using PartName instead of PartID)
    $updateQuery = "UPDATE service SET Type = ?, Price = ?, ClientEmail = ?, PartName = ?, StaffName = ? WHERE ServiceID = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssi", $type, $price, $client_email, $part_name, $staff_name, $service_id);

    if ($stmt->execute()) {
        // ✅ Log update action
        $user_id = $_SESSION['UserID'];
        $username = $_SESSION['Username'];
        $actionType = "Updated Service";
        $timestamp = date("Y-m-d H:i:s");

        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID) VALUES (?, ?, ?, ?)");
        $log->bind_param("sssi", $username, $actionType, $timestamp, $user_id);
        $log->execute();
        $log->close();

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
                    text: "Service updated successfully!",
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6c5ce7"
                }).then(() => {
                    window.location = "service.php";
                });
            });
        </script>';
    }

    $stmt->close();
    $conn->close();
}
?>
