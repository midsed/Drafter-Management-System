<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once "dbconnect.php";

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id   = $_POST['service_id'];
    $type         = trim($_POST['type']);
    $price        = $_POST['price'];
    $client_email = trim($_POST['client_email']); 
    $fName        = trim($_POST['fName']);
    $lName        = trim($_POST['lName']);
    $pNumber      = trim($_POST['pNumber']);
    $part_name    = isset($_POST['part_name']) ? trim($_POST['part_name']) : NULL;
    $staff_name   = $_SESSION['Username'];

    // Get the existing email for this ServiceID
    $checkServiceEmail = $conn->prepare("SELECT ClientEmail FROM service WHERE ServiceID = ?");
    $checkServiceEmail->bind_param("i", $service_id);
    $checkServiceEmail->execute();
    $serviceEmailResult = $checkServiceEmail->get_result();
    $existingEmail = trim($serviceEmailResult->fetch_assoc()['ClientEmail']);
    $checkServiceEmail->close();

    // If the email was changed, check if it exists for another ServiceID
    if ($client_email !== $existingEmail) {
        $checkEmailQuery = $conn->prepare("SELECT ServiceID FROM service WHERE ClientEmail = ? AND ServiceID != ?");
        $checkEmailQuery->bind_param("si", $client_email, $service_id);
        $checkEmailQuery->execute();
        $checkEmailQuery->store_result();

        if ($checkEmailQuery->num_rows > 0) {
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

    // Retrieve existing customer first and last name (if any) before updating/inserting client details
    $oldFName = "";
    $oldLName = "";
    $clientExists = false;
    $clientQuery = $conn->prepare("SELECT FName, LName FROM client WHERE ClientEmail = ?");
    $clientQuery->bind_param("s", $client_email);
    $clientQuery->execute();
    $resultClient = $clientQuery->get_result();
    if ($resultClient->num_rows > 0) {
         $clientExists = true;
         $clientRow = $resultClient->fetch_assoc();
         $oldFName = trim($clientRow['FName']);
         $oldLName = trim($clientRow['LName']);
    }
    $clientQuery->close();

    // Update or insert client details
    $checkClientQuery = $conn->prepare("SELECT ClientEmail FROM client WHERE ClientEmail = ?");
    $checkClientQuery->bind_param("s", $client_email);
    $checkClientQuery->execute();
    $checkClientQuery->store_result();

    if ($checkClientQuery->num_rows == 0) {
        $insertClientQuery = $conn->prepare("INSERT INTO client (ClientEmail, FName, LName, PhoneNumber) VALUES (?, ?, ?, ?)");
        $insertClientQuery->bind_param("ssss", $client_email, $fName, $lName, $pNumber);
        $insertClientQuery->execute();
        $insertClientQuery->close();
    } else {
        $updateClientQuery = $conn->prepare("UPDATE client SET FName = ?, LName = ?, PhoneNumber = ? WHERE ClientEmail = ?");
        $updateClientQuery->bind_param("ssss", $fName, $lName, $pNumber, $client_email);
        $updateClientQuery->execute();
        $updateClientQuery->close();
    }
    $checkClientQuery->close();

    // Retrieve the current service data to compare changes
    $checkServiceQuery = $conn->prepare("SELECT Type, Price, ClientEmail, PartName, StaffName FROM service WHERE ServiceID = ?");
    $checkServiceQuery->bind_param("i", $service_id);
    $checkServiceQuery->execute();
    $existingService = $checkServiceQuery->get_result()->fetch_assoc();
    $checkServiceQuery->close();

    // Compare new values with existing values
    $changes = array();
    // Use strcasecmp for case-insensitive comparison.
    if (strcasecmp($type, trim($existingService['Type'])) !== 0) {
        $changes[] = "Type changed from '" . trim($existingService['Type']) . "' to '$type'";
    }
    // Compare price as floats.
    if ((float)$price !== (float)$existingService['Price']) {
        $changes[] = "Price changed from '{$existingService['Price']}' to '$price'";
    }
    if (strcasecmp($client_email, trim($existingService['ClientEmail'])) !== 0) {
        $changes[] = "Client Email changed from '" . trim($existingService['ClientEmail']) . "' to '$client_email'";
    }
    // Handle Part Name: treat NULL and empty string as equivalent.
    $existingPartName = isset($existingService['PartName']) ? trim($existingService['PartName']) : '';
    $newPartName = $part_name !== NULL ? $part_name : '';
    if (strcasecmp($newPartName, $existingPartName) !== 0) {
        $changes[] = "Part Name changed from '" . $existingPartName . "' to '" . $newPartName . "'";
    }
    // Compare customer first and last names
    if ($clientExists) {
         if (strcasecmp($fName, $oldFName) !== 0) {
             $changes[] = "Customer First Name changed from '$oldFName' to '$fName'";
         }
         if (strcasecmp($lName, $oldLName) !== 0) {
             $changes[] = "Customer Last Name changed from '$oldLName' to '$lName'";
         }
    } else {
         // If no existing record, consider it as new customer details.
         $changes[] = "Customer details update: First Name '$fName', Last Name '$lName'";
    }

    // If no changes detected, exit without updating or logging.
    if (empty($changes)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "No Changes",
                    text: "No updates were made as no changes were detected.",
                    icon: "info",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location = "service.php";
                });
            });
        </script>';
        exit();
    }

    // Update the service record
    $updateQuery = "UPDATE service SET Type = ?, Price = ?, ClientEmail = ?, PartName = ?, StaffName = ? WHERE ServiceID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssi", $type, $price, $client_email, $part_name, $staff_name, $service_id);

    if ($stmt->execute()) {
        // Build a dynamic log message detailing the changes.
        $logMessage = "Update {$type}: " . implode("; ", $changes);
        $timestamp  = date("Y-m-d H:i:s");
        $user_id    = $_SESSION['UserID'];
        $username   = $_SESSION['Username'];

        // Insert the log message into the logs table using the ActionType column.
        $log = $conn->prepare("INSERT INTO logs (ActionType, Timestamp, UserID, ActionBy) VALUES (?, ?, ?, ?)");
        $log->bind_param("ssis", $logMessage, $timestamp, $user_id, $username);
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
