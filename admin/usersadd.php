<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
        <h1>Add User</h1>
    </div>

    <form action="" method="post">
        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required>
        </div>

        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required>
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

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
</script>

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
</body>
</html>

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

    if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($username) && !empty($user_role) && !empty($password)) {
        try {
            // Check if email or username already exists
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE Email = :Email OR Username = :Username");
            $checkStmt->bindParam(':Email', $email);
            $checkStmt->bindParam(':Username', $username);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn();

            if ($exists > 0) {
                echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Entry',
                        text: 'The email or username is already taken. Please use a different one.'
                    });
                </script>
                ";
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

                echo "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'User Added Successfully',
                        text: 'The user has been registered successfully.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'users.php'; // Redirect to users page
                        }
                    });
                </script>
                ";
            }
        } catch (PDOException $e) {
            echo "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Database Error',
                    text: 'An error occurred: " . $e->getMessage() . "'
                });
            </script>
            ";
        }
    } else {
        echo "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'All fields are required!'
            });
        </script>
        ";
    }
}

?>
