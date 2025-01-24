<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendMail($to, $subject, $otp) {
    $mail = new PHPMailer(true);

    try {
        // SMTP server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'drafterautotechmail@gmail.com'; // Your email
        $mail->Password = 'zzhd bkid czlu uuxh';            // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email headers
        $mail->setFrom('drafterautotechmail@gmail.com', 'Drafter Autotech');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<p>Hello,</p>
                       <p>Your OTP code for verification is: <strong>$otp</strong></p>
                       <p>Please use this code to reset your password. This code is valid for 10 minutes.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
