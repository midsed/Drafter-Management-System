<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Add SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function sendOTP() {
            let email = document.getElementById("email").value;
            if (email) {
                fetch("send_otp.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "email=" + encodeURIComponent(email)
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data); // Debugging response
                    alert(data); // Temporary alert for debug
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred. Please try again.");
                });
            } else {
                alert("Please enter your email first.");
            }
        }

        function resendOTP() {
            sendOTP();
        }

        // This function will be triggered in case OTP is invalid in verifyotp.php
        function showInvalidOTPAlert() {
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: 'The OTP you entered is incorrect. Please try again.',
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Forgot Your Password?</h1>
            <p>Please enter the email address associated with your account. We will email a verification code.</p>
            <form action="verifyotp.php" method="post">
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
</body>
</html>
