<?php 
ob_start();
session_start();
require_once "dbconnect.php"; 

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

$user_id = $_SESSION['UserID'];
$check = $conn->prepare("SELECT UserID, RoleType, Username FROM user WHERE UserID = ?");
$check->bind_param("i", $user_id);
$check->execute();
$result = $check->get_result();
$user = $result->fetch_assoc();
$check->close();

if (!$user) {
    die("Access Denied: Invalid user session. Please log in again.");
}

$_SESSION['UserID'] = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];
$username = $user['Username'];
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>

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
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = trim($_POST['firstname'] ?? ''); 
    $lastname = trim($_POST['lastname'] ?? ''); 
    $email = trim($_POST['email'] ?? '');    
    $username = trim($_POST['username'] ?? ''); 
    $user_role = trim($_POST['user_role'] ?? '');
    $password = trim($_POST['password'] ?? ''); 
    $date_created = date('Y-m-d H:i:s');
    
    $check = $conn->prepare("SELECT COUNT(*) FROM user WHERE Email = ? OR Username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

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
            VALUES (NOW(), ?, ?, ?, ?, ?, ?)"
        );
        $add->bind_param("ssssss", $email, $firstname, $lastname, $username, $user_role, $hashedPassword);
        
        if ($add->execute()) {
            $new_user_id = $conn->insert_id;
            
            $actionType = "Added new user: " . $username;
            $log = $conn->prepare(
                "INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) 
                VALUES (?, ?, NOW(), ?, NULL, ?)"
            );
            $log->bind_param("ssis", $_SESSION['Username'], $actionType, $_SESSION['UserID'], $_SESSION['RoleType']); 
            $log->execute();
            $log->close();

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
                    text: 'Error adding user: " . $add->error . "',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            </script>";
        }

        $add->close();
    }
    
    $conn->close();
}
?>