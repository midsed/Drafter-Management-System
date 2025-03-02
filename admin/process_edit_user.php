<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = $_POST['UserID'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $role = $_POST['user_role'];

    // Retrieve the user making the edit
    $editedByUserID = $_SESSION['UserID']; // Who edited
    $editedByUsername = $_SESSION['Username']; // Who edited
    $editedByRole = $_SESSION['RoleType']; // Role of the editor
    $timestamp = date("Y-m-d H:i:s");

    $conn->begin_transaction(); // Start transaction to ensure both update & logging

    try {
        // Update user details
        $sql = "UPDATE user SET FName = ?, LName = ?, Email = ?, Username = ?, RoleType = ? WHERE UserID = ?";
        $updateUser = $conn->prepare($sql);
        $updateUser->bind_param("sssssi", $firstname, $lastname, $email, $username, $role, $userID);
        
        if (!$updateUser->execute()) {
            throw new Exception("Failed to update user: " . $updateUser->error);
        }
        $updateUser->close();

        // Log the action
        $logSql = "INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, NULL, ?)";
        $log = $conn->prepare($logSql);
        $actionType = "Update User";
        $log->bind_param("sssis", $editedByUsername, $actionType, $timestamp, $editedByUserID, $editedByRole);

        if (!$log->execute()) {
            throw new Exception("Failed to log action: " . $log->error);
        }
        $log->close();

        $conn->commit(); // Commit changes if both operations succeed

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Success!",
                    text: "User updated successfully!",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location = "users.php";
                });
            });
        </script>';
    } catch (Exception $e) {
        $conn->rollback(); // Rollback in case of failure
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "Error updating user: ' . addslashes($e->getMessage()) . '",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';
    }

    $conn->close();
}
?>
