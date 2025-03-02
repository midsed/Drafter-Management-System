<?php
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID'])) {
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
    $part_id = isset($_POST['part_id']) ? $_POST['part_id'] : NULL;
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

    $checkPartQuery = $conn->prepare("SELECT PartID FROM part WHERE Description = ? AND ItemStatus = 'Used for Service' LIMIT 1");
    $checkPartQuery->bind_param("s", $type);
    $checkPartQuery->execute();
    $partResult = $checkPartQuery->get_result();
    $part = $partResult->fetch_assoc();
    $checkPartQuery->close();

    if (!$part) {
        $part_id = NULL;
    } else {
        $part_id = $part['PartID'];
    }

    // ✅ Update the service
    $updateQuery = "UPDATE service SET Type = ?, Price = ?, ClientEmail = ?, PartID = ?, StaffName = ? WHERE ServiceID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssi", $type, $price, $client_email, $part_id, $staff_name, $service_id);

    if ($stmt->execute()) {
        // ✅ If PartID is NULL, remove ServiceID from part table
        if ($part_id === NULL) {
            $clearPartQuery = $conn->prepare("UPDATE part SET ServiceID = NULL WHERE ServiceID = ?");
            $clearPartQuery->bind_param("i", $service_id);
            $clearPartQuery->execute();
            $clearPartQuery->close();
        }

        // ✅ Log update action
        $user_id = $_SESSION['UserID'];
        $username = $_SESSION['Username'];
        $actionType = "Updated Service";
        $timestamp = date("Y-m-d H:i:s");

        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID) VALUES (?, ?, ?, ?, ?)");
        $log->bind_param("sssii", $username, $actionType, $timestamp, $user_id, $part_id);
        $log->execute();
        $log->close();

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Success!",
                    text: "Service updated successfully!",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location = "service.php";
                });
            });
        </script>';
    } else {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "Error updating service: ' . addslashes($stmt->error) . '",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';
    }

    $stmt->close();
    $conn->close();
}
?>
