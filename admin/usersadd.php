<?php 
ob_start();
session_start();
require_once "dbconnect.php"; 

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System/login.php");
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
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    .center-container {
        width: 50%; 
        max-width: 1000px; 
        margin: 0 auto; 
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif;
    }

    .header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .header img {
        cursor: pointer;
    }
    .header h1 {
        margin: 0;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 15px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 400;
    }

    .btn {
        font-weight: bold;
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #444;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add User</h1>
    </div>

    <!-- Centered container for the form -->
    <div class="center-container">
        <form id="userForm" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="No special characters allowed.">
            </div>

            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="No special characters allowed.">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required maxlength="64">
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

            <!-- Actions container for buttons -->
            <div class="actions">
                <button type="submit" class="btn">Register</button>
                <button type="reset" class="btn" style="background-color: red;">Reset</button>
            </div>
        </form>
    </div>
</div>

<script>
function validateForm() {
    let password = document.getElementById("password").value;
    let email = document.getElementById("email").value;
    
    let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!passwordRegex.test(password)) {
        Swal.fire({
            title: "Invalid Password!",
            text: "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.",
            icon: "error",
            confirmButtonText: "Ok"
        });
        return false;
    }

    if (!emailRegex.test(email)) {
        Swal.fire({
            title: "Invalid Email!",
            text: "Please enter a valid email address.",
            icon: "error",
            confirmButtonText: "Ok"
        });
        return false;
    }

    return true;
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}
</script>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = trim($_POST['firstname'] ?? ''); 
    $lastname = trim($_POST['lastname'] ?? ''); 
    $email = trim($_POST['email'] ?? '');    
    $username = trim($_POST['username'] ?? ''); 
    $user_role = trim($_POST['user_role'] ?? '');
    $password = trim($_POST['password'] ?? ''); 
    $date_created = date('Y-m-d H:i:s');

    // Check password again on the server side
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        echo "<script>
            Swal.fire({
                title: 'Invalid Password!',
                text: 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        </script>";
        exit();
    }

    // Check if email or username is already taken
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
            $timestamp = date("Y-m-d H:i:s");
            $adminId = $_SESSION['UserID'];

            $actionBy = $_SESSION['Username'];
            $actionType = "Added new user";
            $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID) VALUES (?, ?, ?, ?)");
            $log->bind_param("sssi", $actionBy, $actionType, $timestamp, $adminId);
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
