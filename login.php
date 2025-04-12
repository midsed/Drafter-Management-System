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
<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once "dbconnect.php";
require_once "mail_function.php";

if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lockout_time'] = null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['step']) && $_POST['step'] == 'otp') {
        $entered_otp = trim($_POST['otp']);
        if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']) {
            $pending_user = $_SESSION['pending_user'];
            $_SESSION['UserID'] = $pending_user['UserID'];
            $_SESSION['RoleType'] = $pending_user['RoleType'];
            $_SESSION['Username'] = $pending_user['Username'];
            unset($_SESSION['otp']);
            unset($_SESSION['pending_user']);
            $log_username = $_SESSION['Username'];
            $log_action = "Logged In";
            $timestamp = date("Y-m-d H:i:s");
            $user_id = $_SESSION['UserID'];
            $role_type = $_SESSION['RoleType'];
            $part_id = NULL;
            $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, ?, ?)");
            $log->bind_param("sssiss", $log_username, $log_action, $timestamp, $user_id, $part_id, $role_type);
            $log->execute();
            $updateLoginTimeQuery = "UPDATE user SET LastLogin = NOW() WHERE UserID = $user_id";
            $conn->query($updateLoginTimeQuery);
            if (isset($_SESSION['remember_me_login']) && $_SESSION['remember_me_login'] == true) {
                setcookie("UserID", $pending_user['UserID'], time() + (86400 * 30), "/");
                setcookie("RoleType", $pending_user['RoleType'], time() + (86400 * 30), "/");
            }
            $redirect_path = ($pending_user['RoleType'] == 'Admin') ? './admin/dashboard.php' : (($pending_user['RoleType'] == 'Staff') ? './staff/dashboard.php' : './login.php');
            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Welcome, {$pending_user['RoleType']}!',
                text: 'Redirecting...',
                timer: 2000,
                showConfirmButton: false
            }).then(() => { window.location.href = '$redirect_path'; });
            </script>";
            exit();
        } else {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid Access Code!',
                text: 'The access code you entered is incorrect.',
                showConfirmButton: true
            });
            </script>";
            exit();
        }
    } else if (isset($_POST['login'])) {
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
        if ($user['Status'] === 'Inactive') {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Account Inactive!',
                text: 'Your account is inactive. Please contact the administrator.',
                showConfirmButton: true
            });
            </script>";
            exit();
        }
        $stored_password = $user['Password'];
        if (password_verify($password, $stored_password) || $password === $stored_password) {
            if (isset($_POST['remember_me'])) {
                $_SESSION['remember_me_login'] = true;
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['lockout_time'] = null;
                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['RoleType'] = $user['RoleType'];
                $_SESSION['Username'] = $user['Username'];
                $log_username = $user['Username'];
                $log_action = "Logged In";
                $timestamp = date("Y-m-d H:i:s");
                $user_id = $user['UserID'];
                $role_type = $user['RoleType'];
                $part_id = NULL;
                $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, ?, ?)");
                $log->bind_param("sssiss", $log_username, $log_action, $timestamp, $user_id, $part_id, $role_type);
                $log->execute();
                $updateLoginTimeQuery = "UPDATE user SET LastLogin = NOW() WHERE UserID = $user_id";
                $conn->query($updateLoginTimeQuery);
                $redirect_path = ($user['RoleType'] == 'Admin') ? './admin/dashboard.php' : (($user['RoleType'] == 'Staff') ? './staff/dashboard.php' : './login.php');
                echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome, {$user['RoleType']}!',
                    text: 'Redirecting...',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => { window.location.href = '$redirect_path'; });
                </script>";
                exit();
            } else {
                $otp = rand(1000, 9999);
                $_SESSION['otp'] = $otp;
                $_SESSION['pending_user'] = $user;
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['lockout_time'] = null;
                
                $mailSent = sendMail($user['Email'], "Your Access Code", $otp);
                if (!$mailSent) {
                    echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Error!',
                        text: 'There was an error sending the access code. Please try again later.',
                        showConfirmButton: true
                    });
                    </script>";
                    exit();
                }
                
                echo "<script>
                Swal.fire({
                    title: 'Enter Verification Code',
                    html: '<p>Enter the 4-digit access code sent to your email.</p><input id=\"swal-input\" type=\"text\" maxlength=\"4\" class=\"swal2-input\" placeholder=\"XXXX\" style=\"color: black; font-weight: bold; font-size: 18px; text-align: center;\">',
                    focusConfirm: false,
                    preConfirm: () => {
                        const otp = document.getElementById('swal-input').value;
                        if (!otp || otp.length !== 4) {
                            Swal.showValidationMessage('Please enter a valid 4-digit code');
                            return false;
                        }
                        return otp;
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    customClass: {
                        popup: 'custom-popup-class',
                        input: 'custom-input-class'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        var inputStep = document.createElement('input');
                        inputStep.type = 'hidden';
                        inputStep.name = 'step';
                        inputStep.value = 'otp';
                        form.appendChild(inputStep);
                        var inputOtp = document.createElement('input');
                        inputOtp.type = 'hidden';
                        inputOtp.name = 'otp';
                        inputOtp.value = result.value;
                        form.appendChild(inputOtp);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
                </script>";
            }
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
}
?>
<div class="container">
  <div class="form-container">
    <h1>Login</h1>
    <form action="" method="post" id="login-form">
      <label for="username">Username*</label>
      <input type="text" id="username" name="username" placeholder="Enter your username" maxlength="30" required>
      <label for="password">Password*</label>
      <div class="input-container">
        <input type="password" id="password" name="password" placeholder="Enter your password" maxlength="30" required>
        <img src="images/showpass1.png" id="togglePassword" class="eye-icon" alt="Show/Hide Password">
      </div>
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
function togglePassword(){
    var passwordInput = document.getElementById("password"),
        toggleIcon = document.getElementById("togglePassword");
    if(passwordInput.type === "password"){
        passwordInput.type = "text";
        toggleIcon.src = "images/showpass2.png";
    } else {
        passwordInput.type = "password";
        toggleIcon.src = "images/showpass1.png";
    }
}
document.getElementById("togglePassword").addEventListener("click", togglePassword);
var granimInstance = new Granim({
    element: "#canvas-basic",
    direction: "diagonal",
    isPausedWhenNotInView: true,
    image: {
        source: "./images/Drafter BG2.png", 
        blendingMode: "darken"
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
<style>
.custom-popup-class {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}
.swal2-input {
    color: black !important;
    font-weight: bold !important;
    font-size: 22px !important;
    text-align: center !important;
    letter-spacing: 4px !important;
    background-color: #f5f5f5 !important;
    border: 2px solid #ddd !important;
    border-radius: 8px !important;
    padding: 12px !important;
    margin: 15px auto !important;
    width: 160px !important;
}
.swal2-input::placeholder {
    color: #aaa !important;
}
.swal2-input:focus {
    border-color: #4a90e2 !important;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25) !important;
}
</style>
</body>
</html>
