<?php 
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');
?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Import Poppins font (regular + bold) */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    /* Centered container with Poppins font */
    .container {
        max-width: 600px;
        margin: 40px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif; /* Everything in the container uses Poppins */
    }

    /* Make labels and buttons bold, but keep input text normal */
    .container label {
        font-weight: bold;  
    }
    .container .btn {
        font-weight: bold;
    }

    /* (Optional) If you want the heading bold too, uncomment:
       .header h1 {
           font-family: 'Poppins', sans-serif;
           font-weight: bold;
       }
    */

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
        /* By default, inputs will have a normal font-weight unless overridden */
    }
    /* Button styling */
    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .btn:focus {
        outline: none;
    }
    /* Center the buttons */
    .actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    /* Header styles (back arrow + title) */
    .main-content .header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .main-content .header img {
        cursor: pointer;
    }
    .main-content .header h1 {
        margin: 0;
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
                    title="No special characters allowed." 
                    required
                >
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
                    title="No special characters allowed." 
                    required
                >
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
                <small>(Minimum 8 characters, at least one uppercase letter, one lowercase letter, and one number.)</small>
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
        function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
    document.getElementById("editUserForm").addEventListener("submit", function(event) {
        let firstname = document.getElementById("firstname").value.trim();
        let lastname  = document.getElementById("lastname").value.trim();
        let email     = document.getElementById("email").value.trim();
        let username  = document.getElementById("username").value.trim();
        let password  = document.getElementById("password").value.trim();

        // Basic client-side validation
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

        // If password is provided, validate its pattern
        if (password.length > 0) {
            let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/;
            if (!passwordPattern.test(password)) {
                Swal.fire(
                    "Error", 
                    "Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, and one number.", 
                    "error"
                );
                event.preventDefault();
            }
        }
    });
</script>
