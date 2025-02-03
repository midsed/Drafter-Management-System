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

    <form id="userForm">
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
            <input type="email" id="email" name="email" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Enter a valid email format.">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required pattern="^\S{8,}$" title="Password must be at least 8 characters, no spaces allowed.">
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required pattern="^[a-zA-Z0-9_]+$" title="No spaces or special characters allowed.">
        </div>

        <div class="form-group">
            <label for="user_role">User Role:</label>
            <select id="user_role" name="user_role" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
            </select>
        </div>

        <button type="button" class="btn" onclick="confirmRegistration()">Register</button>
    </form>
</div>

<script>
    function confirmRegistration() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to add this user?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Register!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById("userForm").submit(); // Submit form if confirmed
            } else {
                location.reload(); // Refresh the page if canceled
            }
        });
    }
</script>

<?php
require_once "dbconnect.php"; 

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

    try {
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE Email = :Email OR Username = :Username");
        $checkStmt->bindParam(':Email', $email);
        $checkStmt->bindParam(':Username', $username);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

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

            $stmt = $conn->prepare(
                "INSERT INTO user (DateCreated, Email, FName, LName, Username, RoleType, Password) 
                VALUES (NOW(), :Email, :FName, :LName, :Username, :RoleType, :Password)"
            );

            $stmt->bindParam(':Email', $email);
            $stmt->bindParam(':FName', $firstname);
            $stmt->bindParam(':LName', $lastname);
            $stmt->bindParam(':Username', $username);
            $stmt->bindParam(':RoleType', $user_role);
            $stmt->bindParam(':Password', $hashedPassword);

            $stmt->execute();

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
        }
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'There was an error with the database: " . $e->getMessage() . "',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        </script>";
    }
}
?>
