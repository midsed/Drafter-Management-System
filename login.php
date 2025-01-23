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

    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; 

        $loginsql = "SELECT * FROM user WHERE Username = '$username' AND Password = '$password'";
        $result = $conn->query($loginsql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['UserID'];
            $role = $row['RoleType'];

            $_SESSION['UserID'] = $user_id;
            $_SESSION['RoleType'] = $role;

            if ($role == "Admin") {
                header("Location: ./admin/dashboard.php");
                exit(); 
            } elseif ($role == "Staff") {
                header("Location: ./staff/dashboard.php");
                exit(); 
            } else {
                $_SESSION['error_message'] = "Invalid Role!";
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
            $_SESSION['error_message'] = "Wrong Username or Password!";
            echo "<script>
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Wrong Username and Password!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = './login.php';
                });
            </script>";
        }
    }
    ?>

</body>
</html>