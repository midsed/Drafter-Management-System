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
                    title="Please match the requested format, No special character and Number allowed." 
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
                    title="Please match the requested format, No special character and Number allowed." 
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
            </div>

            <div class="form-group">
                <label for="password">New Password:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                >
                <small>(Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number, and one special character.)</small>
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
    // Function to validate input fields in real time
    function validateInputField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);

        // Prevent invalid key presses
        field.addEventListener("keypress", function(e) {
            let char = String.fromCharCode(e.which);
            if (!/^[A-Za-z\s]$/.test(char)) {
                e.preventDefault();
                errorElem.style.display = "block";
                return false;
            }
        });

        // Remove invalid characters from pasted input and update error message
        field.addEventListener("input", function(e) {
            let cleaned = field.value.replace(/[^A-Za-z\s]/g, "");
            if (field.value !== cleaned) {
                field.value = cleaned;
                errorElem.style.display = "block";
            } else {
                errorElem.style.display = "none";
            }
        });
    }

    // Attach real-time validations for first name and last name
    validateInputField("firstname", "firstname-error");
    validateInputField("lastname", "lastname-error");

    // Existing form submission validation for all fields and password pattern
    document.getElementById("editUserForm").addEventListener("submit", function(event) {
        let firstname = document.getElementById("firstname").value.trim();
        let lastname  = document.getElementById("lastname").value.trim();
        let email     = document.getElementById("email").value.trim();
        let username  = document.getElementById("username").value.trim();
        let password  = document.getElementById("password").value.trim();

        if (firstname.length === 0 || lastname.length === 0 || email.length === 0 || username.length === 0) {
            Swal.fire("Error", "All fields must be filled out.", "error");
            event.preventDefault();
            return;
        }

        if (firstname.length > 40 || lastname.length > 40) {
            Swal.fire("Error", "First Name and Last Name cannot exceed 40 characters.", "error");
            event.preventDefault();
            return;
        }

        if (email.length > 64) {
            Swal.fire("Error", "Email cannot exceed 64 characters.", "error");
            event.preventDefault();
            return;
        }

        if (password.length > 0) {
            let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

            if (!passwordPattern.test(password)) {
                Swal.fire({
                    icon: "error",
                    title: "Invalid Password!",
                    text: "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.",
                    showConfirmButton: true
                });
                event.preventDefault();
            }
        }
    });

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
</script>
