<?php
session_start();
date_default_timezone_set('Asia/Manila');
include('dbconnect.php');

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID       = $_POST['UserID'];
    $newFName     = trim($_POST['firstname']);
    $newLName     = trim($_POST['lastname']);
    $newEmail     = trim($_POST['email']);
    $newUsername  = trim($_POST['username']);
    $newRole      = $_POST['user_role'];
    $newPassword  = trim($_POST['password']); // New password input (if any)

    // Retrieve the current user details
    $stmt = $conn->prepare("SELECT FName, LName, Email, Username, RoleType FROM user WHERE UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 0) {
        echo "User not found.";
        exit();
    }
    $currentUser = $result->fetch_assoc();
    $stmt->close();

    // Compare each field (using case-insensitive comparisons for text)
    $changes = array();
    if (strcasecmp($newFName, $currentUser['FName']) !== 0) {
         $changes[] = "First Name changed from '{$currentUser['FName']}' to '$newFName'";
    }
    if (strcasecmp($newLName, $currentUser['LName']) !== 0) {
         $changes[] = "Last Name changed from '{$currentUser['LName']}' to '$newLName'";
    }
    if (strcasecmp($newEmail, $currentUser['Email']) !== 0) {
         $changes[] = "Email changed from '{$currentUser['Email']}' to '$newEmail'";
    }
    if (strcasecmp($newUsername, $currentUser['Username']) !== 0) {
         $changes[] = "Username changed from '{$currentUser['Username']}' to '$newUsername'";
    }
    if (strcasecmp($newRole, $currentUser['RoleType']) !== 0) {
         $changes[] = "Role changed from '{$currentUser['RoleType']}' to '$newRole'";
    }
    // For password, if a new password is provided then it's considered a change.
    if (!empty($newPassword)) {
         $changes[] = "Password updated";
    }

    // If no changes detected, notify the user and do not update.
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
                    window.location = "users.php";
                });
            });
        </script>';
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // If new password provided, validate and update it
        if (!empty($newPassword)) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
                throw new Exception("Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.");
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePasswordStmt = $conn->prepare("UPDATE user SET Password = ? WHERE UserID = ?");
            $updatePasswordStmt->bind_param("si", $hashedPassword, $userID);
            if (!$updatePasswordStmt->execute()) {
                throw new Exception("Failed to update password: " . $updatePasswordStmt->error);
            }
            $updatePasswordStmt->close();
        }

        // Update other user details
        $updateUserStmt = $conn->prepare("UPDATE user SET FName = ?, LName = ?, Email = ?, Username = ?, RoleType = ? WHERE UserID = ?");
        $updateUserStmt->bind_param("sssssi", $newFName, $newLName, $newEmail, $newUsername, $newRole, $userID);
        if (!$updateUserStmt->execute()) {
            throw new Exception("Failed to update user: " . $updateUserStmt->error);
        }
        $updateUserStmt->close();

        // Log the action with a dynamic message detailing the changes.
        // Now the log message will include the updated user's first and last name.
        $logMessage = "Update User: $newFName $newLName: " . implode("; ", $changes);
        $editedByUsername = $_SESSION['Username'];
        $editedByUserID   = $_SESSION['UserID'];
        $editedByRole     = $_SESSION['RoleType'];
        $timestamp = date("Y-m-d H:i:s");

        $logStmt = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, NULL, ?)");
        $logStmt->bind_param("sssis", $editedByUsername, $logMessage, $timestamp, $editedByUserID, $editedByRole);
        if (!$logStmt->execute()) {
            throw new Exception("Failed to log action: " . $logStmt->error);
        }
        $logStmt->close();

        $conn->commit();

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
             document.addEventListener("DOMContentLoaded", function() {
                 Swal.fire({
                     title: "Success!",
                     text: "User updated successfully!",
                     icon: "success",
                     confirmButtonText: "OK",
                     confirmButtonColor: "#32CD32"
                 }).then(() => {
                     window.location = "users.php";
                 });
             });
         </script>';

    } catch (Exception $e) {
         $conn->rollback();
         echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
         echo '<script>
             document.addEventListener("DOMContentLoaded", function() {
                 Swal.fire({
                     title: "Error!",
                     text: "' . addslashes($e->getMessage()) . '",
                     icon: "error",
                     confirmButtonText: "OK",
                     confirmButtonColor: "#32CD32"
                 });
             });
         </script>';
    }

    $conn->close();
}
?>
