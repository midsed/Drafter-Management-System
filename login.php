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

    // Initialize failed attempts counter
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['lockout_time'] = null;
    }

    if (isset($_POST['login'])) {
        // Check if account is locked
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

        $loginsql = "SELECT * FROM user WHERE Username = '$username' AND Password = '$password'";
        $result = $conn->query($loginsql);

        if ($result->num_rows > 0) {
            // Reset failed attempts on successful login
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['lockout_time'] = null;

            $row = $result->fetch_assoc();
            $user_id = $row['UserID'];
            $role = $row['RoleType'];

            // Set session or cookie if 'Remember Me' is checked
            if (isset($_POST['remember_me'])) {
                setcookie("UserID", $user_id, time() + (86400 * 30), "/"); // 30 days
                setcookie("RoleType", $role, time() + (86400 * 30), "/");
            } else {
                $_SESSION['UserID'] = $user_id;
                $_SESSION['RoleType'] = $role;
            }

            if ($role == "Admin") {
                header("Location: ./admin/dashboard.php");
                exit(); 
            } elseif ($role == "Staff") {
                header("Location: ./staff/dashboard.php");
                exit(); 
            } else {
                echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Invalid Role!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = './login.php';
                    });
                </script>";
            }
        } else {
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
                        title: 'Wrong Username or Password!',
                        text: 'You have $remaining_attempts attempts left.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>";
            }
        }
    }
    ?>
</body>
</html>
