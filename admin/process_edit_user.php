<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
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
    $newPassword = trim($_POST['password']); // Get new password input

    // Retrieve the user making the edit
    $editedByUserID = $_SESSION['UserID'];
    $editedByUsername = $_SESSION['Username'];
    $editedByRole = $_SESSION['RoleType'];
    $timestamp = date("Y-m-d H:i:s");

    $conn->begin_transaction();

    try {
        // Validate password if provided
        if (!empty($newPassword)) {
            // Updated password pattern to allow special characters
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
                throw new Exception("Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.");
            }
        
            // Hash the password before updating
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
            // Update password if valid
            $passwordSql = "UPDATE user SET Password = ? WHERE UserID = ?";
            $updatePassword = $conn->prepare($passwordSql);
            $updatePassword->bind_param("si", $hashedPassword, $userID);
        
            if (!$updatePassword->execute()) {
                throw new Exception("Failed to update password: " . $updatePassword->error);
            }
            $updatePassword->close();
        }

        // Update other user details
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

