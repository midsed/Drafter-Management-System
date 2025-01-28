<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Login</h1>
            <form action="" method="post">
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>

                <label for="password">Password*</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>

                <p class="center-text">
                    <label><input type="checkbox" onclick="togglePassword()"> Show Password</label>
                </p>

                <p>
                    <label><input type="checkbox" name="remember_me"> Remember Me</label>
                </p>

                <button type="submit" name="login">Login</button> 
                <p class="center-text">
                    <a href="forgotpassword.php">Forgot Password?</a>
                </p> 
            </form>
        </div>
        <div class="right-section">
            <img src="images/Drafter Logo Cropped.png" alt="Drafter AutoTech Logo">
        </div>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>

<?php
session_start();
require_once "dbconnect.php"; 

if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lockout_time'] = null;
}

if (isset($_POST['login'])) {
    if ($_SESSION['lockout_time'] && time() < $_SESSION['lockout_time']) {
        $remaining_time = $_SESSION['lockout_time'] - time();
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Account Locked!',
                text: 'Please try again after $remaining_time seconds.',
                timer: 1500,
                showConfirmButton: false
            });
        </script>";
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password']; 

    $stmt = $conn->prepare("SELECT * FROM user WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Wrong Username or Password!',
                text: 'Try again.',
                showConfirmButton: false,
                timer: 1500
            });
        </script>";
    } else {
        $user = $result->fetch_assoc();
        if ($user['Password'] !== $password) {
            // Password is incorrect
            $_SESSION['failed_attempts']++;

            if ($_SESSION['failed_attempts'] >= 5) {
                $_SESSION['lockout_time'] = time() + 60; // Lock for 1 minute
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Too Many Attempts!',
                        text: 'Your account is locked for 1 minute.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                </script>";
            } else {
                $remaining_attempts = 5 - $_SESSION['failed_attempts'];
                echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Wrong Password!',
                        text: 'You have $remaining_attempts attempts left.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>";
            }
        } else {
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['lockout_time'] = null;

            $user_id = $user['UserID'];
            $role = $user['RoleType'];

            $_SESSION['UserID'] = $user_id;
            $_SESSION['RoleType'] = $role;

            if (isset($_POST['remember_me'])) {
                setcookie("UserID", $user_id, time() + (86400 * 30), "/");
                setcookie("RoleType", $role, time() + (86400 * 30), "/");
            }

            $redirect_path = match($role) {
                'Admin' => './admin/dashboard.php',
                'Staff' => './staff/dashboard.php',
                default => './login.php'
            };

            header("Location: $redirect_path");
            exit();
        }
    }
}
?>
</body>
</html>
