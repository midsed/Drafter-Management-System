<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Login</h1>
            <form action="login_process.php" method="post">
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>

                <label for="password">Password*</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>

                <p class="center-text">
                    <label><input type="checkbox" onclick="togglePassword()"> Show Password</label>
                </p> 

                <button type="submit">Login</button>
                <p class="center-text">
                    <a href="forgotpassword.php">Forgot Password?</a>
                </p> 
            </form>
        </div>
        <div class="right-section">
            Drafter AutoTech
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
</body>
</html>
