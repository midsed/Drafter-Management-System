<?php
session_start(); 
include('navigation/sidebar.php');
include('navigation/topbar.php');
require_once "dbconnect.php";
?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add User</h1>
    </div>

    <form id="userForm" method="POST">
        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required pattern="^[A-Za-z\s]+$" title="No special characters allowed.">
        </div>

        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required pattern="^[A-Za-z\s]+$" title="No special characters allowed.">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="user_role">User Role:</label>
            <select id="user_role" name="user_role" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
            </select>
        </div>

        <button type="submit" class="btn">Register</button>
    </form>
</div>

<?php
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = trim($_POST['firstname'] ?? ''); 
    $lastname = trim($_POST['lastname'] ?? ''); 
    $email = trim($_POST['email'] ?? '');    
    $username = trim($_POST['username'] ?? ''); 
    $user_role = trim($_POST['user_role'] ?? '');
    $password = trim($_POST['password'] ?? ''); 

    $logged_in_user_id = $_SESSION['UserID'] ?? null;
    $logged_in_username = $_SESSION['Username'] ?? 'Unknown';
    $logged_in_role = $_SESSION['RoleType'] ?? 'Unknown';

    try {
        $check = $conn->prepare("SELECT COUNT(*) FROM user WHERE Email = :Email OR Username = :Username");
        $check->bindParam(':Email', $email);
        $check->bindParam(':Username', $username);
        $check->execute();
        $exists = $check->fetchColumn();

        if ($exists > 0) {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'The email or username is already taken. Please use a different one.',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            </script>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $add = $conn->prepare(
                "INSERT INTO user (DateCreated, Email, FName, LName, Username, RoleType, Password) 
                VALUES (NOW(), :Email, :FName, :LName, :Username, :RoleType, :Password)"
            );

            $add->bindParam(':Email', $email);
            $add->bindParam(':FName', $firstname);
            $add->bindParam(':LName', $lastname);
            $add->bindParam(':Username', $username);
            $add->bindParam(':RoleType', $user_role);
            $add->bindParam(':Password', $hashedPassword);
            $add->execute();

            $actionType = "Added User";
            $log = $conn->prepare(
                "INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) 
                VALUES (:ActionBy, :ActionType, NOW(), :UserID, NULL, :RoleType)"
            );

            $log->bindParam(':ActionBy', $logged_in_username);
            $log->bindParam(':ActionType', $actionType);
            $log->bindParam(':UserID', $logged_in_user_id);
            $log->bindParam(':RoleType', $logged_in_role);

            if ($log->execute()) {
                echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'User added successfully!',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    }).then(() => {
                        window.location = 'users.php';
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to log the action.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                </script>";
            }
        }
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'Database error: " . $e->getMessage() . "',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        </script>";
    }
}
?>
