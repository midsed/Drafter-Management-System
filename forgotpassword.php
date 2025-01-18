<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Forgot Your Password?</h1>
            <p>Please enter the email address associated with your account. We will email a verification code.</p>
            <form action="verify_code.php" method="post">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <a href="javascript:void(0)" class="send-otp-link" onclick="sendOTP()">Send OTP</a>
                </div>

                <label for="otp">OTP</label>
                <input type="number" id="otp" name="otp" placeholder="Enter your OTP" required>

                <button type="submit">Verify Code</button>
                <p class="center-text">
                    <span>Didn't Receive OTP?</span> 
                    <a href="javascript:void(0)" onclick="resendOTP()" class="resend-code">Resend Code</a>
                </p>
                <p class="center-text">
                    <a href="login.php">Back to Login Page</a>
                </p>
            </form>
        </div>
        <div class="right-section">
            <img src="images/Drafter Logo Cropped.png" alt="Drafter AutoTech Logo">
        </div>
    </div>

    <script>
        function sendOTP() {
            alert('OTP sent to your email.');
        }
        function resendOTP() {
            alert('OTP resent to your email.');
        }
    </script>
</body>
</html>
