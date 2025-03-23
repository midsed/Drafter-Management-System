<?php 
ob_start();
session_start();
require_once "dbconnect.php"; 

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') { 
    header("Location: /Drafter-Management-System/login.php"); 
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
    
    .error-message {
        color: red;
        font-size: 0.9em;
        margin-top: 5px;
        display: none;
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add User</h1>
    </div>

    <!-- Centered container for the form -->
    <div class="center-container">
        <form id="userForm" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="No special character and Number allowed.">
                <span id="firstname-error" class="error-message">Please match the requested format, No special character and Number allowed.</span>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="No special character and Number allowed.">
                <span id="lastname-error" class="error-message">Please match the requested format, No special character and Number allowed.</span>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required maxlength="64">
                <span id="email-error" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <span id="password-error" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <span id="username-error" class="error-message" style="color: red; display: none;"></span>
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
    // Toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    // Modified validateNameField to check for empty value as well as pattern match.
    function validateNameField(fieldId, errorId, fieldName) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const pattern = /^[A-Za-z\s]+$/; // one or more letters/spaces

        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });

        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = fieldName + " is required.";
            } else if (!pattern.test(field.value)) {
                errorElem.style.display = "block";
                errorElem.textContent = "Please match the requested format, No special character and Number allowed.";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    // Modified validateEmailField to check for required value.
    function validateEmailField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });
        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = "Email is required.";
            } else if (!emailRegex.test(field.value.trim())) {
                errorElem.style.display = "block";
                errorElem.textContent = "Please enter a valid email address (e.g., sample@sample.com).";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    // Generic function to check that a required field is not empty.
    function validateRequiredField(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        
        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });
        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = message;
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    // Validate form submission (additional checks can be added as needed)
    function validateFormSubmission() {
        const emailField = document.getElementById("email");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value.trim())) {    
            document.getElementById("email-error").style.display = "block";
            document.getElementById("email-error").textContent = "Please enter a valid email address.";
            return false;
        }
        // Additional required checks could be performed here if desired.
        return true;
    }

    function validateUsernameField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const usernamePattern = /^[A-Za-z0-9]+$/;  // only letters and numbers allowed

        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });

        // Real-time validation (remove invalid characters immediately)
        field.addEventListener("input", function() {
            const cleaned = field.value.replace(/[^A-Za-z0-9]/g, '');
            if (field.value !== cleaned) {
                field.value = cleaned;
                errorElem.style.display = "block";
                errorElem.textContent = "No spaces or special characters allowed.";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });

        // Final validation on blur
        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = "Username is required.";
            } else if (!usernamePattern.test(field.value.trim())) {
                errorElem.style.display = "block";
                errorElem.textContent = "Username must not contain spaces or special characters.";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }


    function validatePasswordField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });

        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = "Password is required.";
            } else if (!passwordPattern.test(field.value.trim())) {
                errorElem.style.display = "block";
                errorElem.textContent = "Password must be at least 8 characters with uppercase, lowercase, number, and special character.";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    function resetForm() {
        Swal.fire({
            title: "Are you sure?",
            text: "This will reset all informations.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, reset it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d63031",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector("form").reset();
                document.querySelectorAll("input").forEach(input => input.value = "");
                Swal.fire({
                    title: "Reset!",
                    text: "The form has been reset.",
                    icon: "success",
                    confirmButtonColor: "#32CD32"
                });
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Validate name fields with required check
        validateNameField("firstname", "firstname-error", "First name");
        validateNameField("lastname", "lastname-error", "Last name");
        // Validate email field
        validateEmailField("email", "email-error");
        // Validate username field
        validateUsernameField("username", "username-error");
        // Validate password field
        validatePasswordField("password", "password-error");
        
        document.getElementById("user-form").addEventListener("submit", function(e) {
            if (!validateFormSubmission()) {
                e.preventDefault();
            }
        });
    });
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
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $password)) {
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

            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<style>
                @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
                .swal2-popup { font-family: "Inter", sans-serif !important; }
                .swal2-title { font-weight: 700 !important; }
                .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
                .swal2-confirm { font-weight: bold !important; background-color: #32CD32 !important; color: white !important; }
            </style>';
            
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'User added successfully!',
                    icon: 'success',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#32CD32'
                }).then(() => {
                    window.location = 'users.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Error adding user: " . addslashes($add->error) . "',
                    icon: 'error',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#32CD32'
                });
            </script>";
        }
        $add->close();
    }

    $conn->close();
}
?>
