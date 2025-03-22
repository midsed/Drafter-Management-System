<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="font-family: 'Poppins', sans-serif;">Terms and Conditions</h1>
    </div>

    <div class="terms-content">
        <h2>1. Introduction</h2>
        <p>Welcome to our inventory management system. These terms and conditions outline the rules and regulations for the use of our website and services. By accessing or using our services, you agree to comply with these terms.</p>

        <h2>2. Acceptance of Terms</h2>
        <p>By accessing this website, you accept these terms and conditions in full. If you disagree with any part of these terms, you must not use our website or services.</p>

        <h2>3. Changes to Terms</h2>
        <p>We may revise these terms from time to time. The revised terms will apply to the use of our website from the date of publication. It is your responsibility to review these terms periodically for updates.</p>

        <h2>4. User Responsibilities</h2>
        <p>As a user of our inventory management system, you agree to:</p>
        <ul>
            <li>Provide accurate and complete information when creating an account.</li>
            <li>Maintain the confidentiality of your account credentials and notify us immediately of any unauthorized use of your account.</li>
            <li>Use the system only for lawful purposes and in accordance with applicable laws and regulations.</li>
            <li>Not to engage in any activity that could harm the system or its users, including but not limited to hacking, spamming, or distributing malware.</li>
        </ul>

        <h2>5. Limitation of Liability</h2>
        <p>We will not be liable for any loss or damage arising from your use of the website or services, including but not limited to:</p>
        <ul>
            <li>Loss of data or profits.</li>
            <li>Any indirect, incidental, or consequential damages.</li>
            <li>Errors or omissions in the content provided on the website.</li>
        </ul>

        <h2>6. Intellectual Property</h2>
        <p>All content, trademarks, and other intellectual property on this website are owned by us or our licensors. You may not reproduce, distribute, or create derivative works from any content without our express written permission.</p>

        <h2>7. Governing Law</h2>
        <p>These terms will be governed by and construed in accordance with the laws of [Your Country/State]. Any disputes arising under or in connection with these terms shall be subject to the exclusive jurisdiction of the courts of [Your Country/State].</p>

        <h2>8. Termination</h2>
        <p>We reserve the right to suspend or terminate your access to the website and services at our discretion, without notice, for conduct that we believe violates these terms or is harmful to other users or the website.</p>

        <h2>9. Contact Us</h2>
        <p>If you have any questions about these terms, please contact us at:</p>
        <p>Email: support@example.com</p>
        <p>Phone: (123) 456-7890</p>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
</script>

<style>
    .terms-content {
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
        margin: 20px 0;
    }

    .terms-content h2 {
        margin-top: 20px;
        color: #333;
    }

    .terms-content p {
        margin: 10px 0;
        color: #555;
    }

    .terms-content ul {
        margin: 10px 0;
        padding-left: 20px;
        color: #555;
    }
</style>