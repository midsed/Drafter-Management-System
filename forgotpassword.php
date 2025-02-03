<?php
require_once "dbconnect.php";

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Add SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    sendOtpLink.style.display = "none"; // Hide "Send OTP"
                }

                // Start countdown for resend OTP
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
        let countdown = 120; // 2 minutes in seconds
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
</script>

<?php
require_once "mail_function.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $_SESSION['entered_email'] = $email; // Store email in session

    $maxAttempts = 3;
    $lockoutDuration = 24 * 60 * 60; // 24 hours
    $currentTimestamp = time();

    $stmt = $conn->prepare("SELECT OTP, otp_attempts, otp_timestamp FROM user WHERE Email = ?");
    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "Database error."]));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Email not found."]);
        exit();
    }

    $attempts = $user['otp_attempts'];
    $lastAttempt = strtotime($user['otp_timestamp']);

    if ($attempts >= $maxAttempts && ($currentTimestamp - $lastAttempt) < $lockoutDuration) {
        echo json_encode(["status" => "blocked"]);
        exit();
    }

    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = $currentTimestamp + 120;

    if (($currentTimestamp - $lastAttempt) > $lockoutDuration) {
        $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_attempts = 1, otp_timestamp = NOW() WHERE Email = ?");
    } else {
        $stmt = $conn->prepare("UPDATE user SET OTP = ?, otp_attempts = otp_attempts + 1, otp_timestamp = NOW() WHERE Email = ?");
    }
    $stmt->bind_param("ss", $otp, $email);
    $stmt->execute();

    $subject = "Your OTP Code";
    $message = "<p>Your OTP for password reset is <strong>$otp</strong>. It is valid for 2 minutes.</p>";

    if (sendMail($email, $subject, $message)) {
        echo json_encode(["status" => "success", "attempts" => $attempts + 1]);
    } else {
        die(json_encode(["status" => "error", "message" => "Error sending email."]));
    }

    $stmt->close();
    $conn->close();
}
?>



