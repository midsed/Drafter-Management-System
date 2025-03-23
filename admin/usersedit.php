<?php 
session_start();

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');
?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    .container {
        max-width: 600px;
        margin: 40px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif;
    }

    .container label {
        font-weight: bold;
    }
    .container .btn {
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-family: 'Poppins', sans-serif;
    }
    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-family: 'Poppins', sans-serif;
    }

    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }
    .btn:focus {
        outline: none;
    }
    .actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    .main-content .header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-family: 'Poppins', sans-serif;
    }
    .main-content .header img {
        cursor: pointer;
    }
    .main-content .header h1 {
        margin: 0;
    }
    
    /* Error message styling */
    .error-message {
        color: red;
        font-size: 0.9em;
        font-family: 'Poppins', sans-serif;
        display: none;
        margin-top: 3px;
    }

    body {
        font-family: 'Poppins', sans-serif; /* Set the font family for the entire body */
    }

    .container, .header, .form-group, label, input, select, .btn, .error-message {
        font-family: 'Poppins', sans-serif; /* Ensure all relevant elements use the same font */
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Edit User</h1>
    </div>

    <?php
    if (isset($_GET['UserID'])) {
        $userID = $_GET['UserID'];
        $sql = "SELECT UserID, FName, LName, Email, Username, RoleType, Status FROM user WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            echo "<script>Swal.fire('Error', 'User not found.', 'error');</script>";
            exit;
        }
    } else {
        echo "<script>Swal.fire('Error', 'Invalid request.', 'error');</script>";
        exit;
    }
    ?>

    <div class="container">
        <form id="editUserForm" action="process_edit_user.php" method="post">
            <input type="hidden" name="UserID" value="<?php echo $user['UserID']; ?>">

            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input 
                    type="text" 
                    id="firstname" 
                    name="firstname"
                    value="<?php echo htmlspecialchars($user['FName']); ?>" 
                    maxlength="40" 
                    pattern="^[A-Za-z\s]+$" 
                    title="No special character and Number allowed." 
                    required
                >
                <span id="firstname-error" class="error-message">Please match the requested format, No special character and Number allowed.</span>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input 
                    type="text" 
                    id="lastname" 
                    name="lastname"
                    value="<?php echo htmlspecialchars($user['LName']); ?>" 
                    maxlength="40" 
                    pattern="^[A-Za-z\s]+$" 
                    title="No special character and Number allowed." 
                    required
                >
                <span id="lastname-error" class="error-message">Please match the requested format, No special character and Number allowed.</span>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    value="<?php echo htmlspecialchars($user['Email']); ?>" 
                    maxlength="64" 
                    required
                >
                <span id="email-error" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username"
                    value="<?php echo htmlspecialchars($user['Username']); ?>" 
                    required
                >
                <span id="username-error" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="form-group">
                <label for="password">New Password:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                >
                <span id="password-error" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="form-group">
                <label for="user_role">User Role:</label>
                <select id="user_role" name="user_role" required>
                    <option value="staff" <?php echo $user['RoleType'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                    <option value="admin" <?php echo $user['RoleType'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <input 
                    type="text" 
                    id="status" 
                    name="status"
                    value="<?php echo htmlspecialchars($user['Status']); ?>" 
                    disabled
                >
            </div>

            <div class="actions">
                <button type="submit" class="btn">Update</button>
                <?php if ($user['Status'] == 'Active') { ?>
                    <a href="process_user_status.php?UserID=<?php echo $user['UserID']; ?>&status=Inactive">
                        <button type="button" class="btn" style="background-color: #C00F0C;">Mark as Inactive</button>
                    </a>
                <?php } else { ?>
                    <a href="process_user_status.php?UserID=<?php echo $user['UserID']; ?>&status=Active">
                        <button type="button" class="btn" style="background-color: #28a745;">Mark as Active</button>
                    </a>
                <?php } ?>
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
                errorElem.textContent = "No special character allowed.";
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
            const password = field.value.trim();
            if (password !== "") {
                if (!passwordPattern.test(password)) {
                    errorElem.style.display = "block";
                    errorElem.textContent = "Password must be at least 8 characters with uppercase, lowercase, number, and special character.";
                } else {
                    errorElem.style.display = "none";
                    errorElem.textContent = "";
                }
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
            confirmButtonColor: "#32CD32",
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