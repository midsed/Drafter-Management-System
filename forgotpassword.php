<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Forgot Your Password?</h1>
            <p>Please enter the email address associated with your account. We will email a verification code.</p>
            <form id="forgot-password-form" method="post">
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
<script>
    let attemptCount = 0;
    let isBlocked = false;
    let countdownTimer;

    function sendOTP(isResend = false) {
        let email = document.getElementById("email").value;
        let sendOtpLink = document.querySelector(".send-otp-link");
        let resendCodeLink = document.querySelector(".resend-code");

        if (!email) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Email!',
                text: 'Please enter your email first.',
            });
            return;
        }

        fetch("send_otp.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "email=" + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "blocked") {
                isBlocked = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Too Many Attempts!',
                    text: 'You have exceeded the maximum attempts. Try again in 24 hours.',
                });
                resendCodeLink.style.display = "none";
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent!',
                    text: 'Check your email for the OTP.',
                });

                attemptCount = data.attempts;

                if (!isResend) {
                    sendOtpLink.style.display = "none"; 
                }

                startCountdown(resendCodeLink);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred. Please try again.',
            });
        });
    }

    function resendOTP() {
        if (!isBlocked) {
            sendOTP(true);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Blocked!',
                text: 'You have reached the maximum attempts. Try again later.',
            });
        }
    }

    function startCountdown(button) {
        let countdown = 120; 
        button.style.pointerEvents = "none";
        button.style.color = "gray";

        if (countdownTimer) {
            clearInterval(countdownTimer);
        }

        countdownTimer = setInterval(() => {
            button.innerText = `Resend Code (${countdown}s)`;
            countdown--;

            if (countdown < 0) {
                clearInterval(countdownTimer);
                button.innerText = "Resend Code";
                button.style.pointerEvents = "auto";
                button.style.color = "";
            }
        }, 1000);
    }

    document.getElementById("forgot-password-form").addEventListener("submit", function (event) {
        event.preventDefault();

        let email = document.getElementById("email").value;
        let otp = document.getElementById("otp").value;

        if (!email || !otp) {
            Swal.fire({
                icon: "warning",
                title: "Missing Fields!",
                text: "Please enter your email and OTP.",
            });
            return;
        }

        fetch("verifyotp.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "OTP Verified!",
                    text: "Redirecting to reset password page...",
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = "resetpassword.php";
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Invalid OTP!",
                    text: "Please try again.",
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire({
                icon: "error",
                title: "Server Error!",
                text: "Something went wrong. Try again later.",
            });
        });
    });
</script>


