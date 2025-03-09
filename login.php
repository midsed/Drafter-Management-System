<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script src="https://cdn.jsdelivr.net/npm/granim/dist/granim.min.js"></script>
</head>
<body>
    <canvas id="canvas-basic"></canvas>
    <div class="container">
        <div class="form-container">
            <h1>Login</h1>
            <form action="" method="post">
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" maxlength="30" required>

                <label for="password">Password*</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" maxlength="30" required>

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
            <img src="images/New Drafter Logo Cropped.png" alt="Drafter AutoTech Logo">
        </div>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("password");
            x.type = (x.type === "password") ? "text" : "password";
        }
    
        var granimInstance = new Granim({
    element: '#canvas-basic',
    direction: 'diagonal',
    isPausedWhenNotInView: true,
    image: {
        source: './images/Drafter BG2.png', 
        blendingMode: 'darken' 
    },
    states: {
        "default-state": {
            gradients: [
                ['#29323c', '#485563'],
                ['#FF6B6B', '#FFF5E1'],
                ['#80d3fe', '#7ea0c4']
            ],
            transitionSpeed: 5000
        }
    }
});
    </script>

<?php
session_start();
require_once "dbconnect.php"; 

if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lockout_time'] = null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    if ($_SESSION['lockout_time'] && time() < $_SESSION['lockout_time']) {
        $remaining_time = $_SESSION['lockout_time'] - time();
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Account Locked!',
                text: 'Try again after $remaining_time seconds.',
                timer: 3000,
                showConfirmButton: false
            });
        </script>";
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']); 

    if (empty($username)) {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Username Required!',
                text: 'Please enter your username.',
                showConfirmButton: true
            });
        </script>";
        exit();
    }

    if (empty($password)) {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Password Required!',
                text: 'Please enter your password.',
                showConfirmButton: true
            });
        </script>";
        exit();
    }

    $check = $conn->prepare("SELECT * FROM user WHERE Username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid Username!',
                text: 'This username does not exist.',
                                showConfirmButton: true
            });
        </script>";
        exit();
    }

    $user = $result->fetch_assoc();
    $stored_password = $user['Password'];

    if (password_verify($password, $stored_password) || $password === $stored_password) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['lockout_time'] = null;

        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['RoleType'] = $user['RoleType'];
        $_SESSION['Username'] = $user['Username'];

        if (isset($_POST['remember_me'])) {
            setcookie("UserID", $user['UserID'], time() + (86400 * 30), "/");
            setcookie("RoleType", $user['RoleType'], time() + (86400 * 30), "/");
        }

        $log_username = $user['Username'];
        $log_action = "Logged In";
        $timestamp = date("Y-m-d H:i:s");
        $user_id = $user['UserID'];
        $role_type = $user['RoleType'];
        $part_id = NULL;

        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, ?, ?)");
        $log->bind_param("sssiss", $log_username, $log_action, $timestamp, $user_id, $part_id, $role_type);
        $log->execute();

        $redirect_path = match($user['RoleType']) {
            'Admin' => './admin/dashboard.php',
            'Staff' => './staff/dashboard.php',
            default => './login.php'
        };

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Welcome, {$user['RoleType']}!',
                text: 'Redirecting...',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '$redirect_path';
            });
        </script>";
        exit();
    } else {
        $_SESSION['failed_attempts']++;

        if ($_SESSION['failed_attempts'] >= 5) {
            $_SESSION['lockout_time'] = time() + 60;
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Too Many Attempts!',
                    text: 'Your account is locked for 1 minute.',
                    timer: 2500,
                    showConfirmButton: false
                });
            </script>";
        } else {
            $remaining_attempts = 5 - $_SESSION['failed_attempts'];
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Incorrect Password!',
                    text: 'You have $remaining_attempts attempts left.',
                    timer: 2500,
                    showConfirmButton: false
                });
            </script>";
        }
        exit();
    }
}
?>
</body>
</html>