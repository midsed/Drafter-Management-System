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
        <p>Welcome to Drafter Management System, your trusted platform for auto parts management and services. These terms and conditions govern your use of our website, inventory management system, and related services. By accessing or using our platform, you agree to comply with these terms.</p>

        <h2>2. Acceptance of Terms</h2>
        <p>By accessing this website and using our services, you acknowledge that you have read, understood, and agree to be bound by these terms and conditions. If you disagree with any part of these terms, you must not use our website or services.</p>

        <h2>3. Services Overview</h2>
        <p>Our platform provides the following services:</p>
        <ul>
            <li>Auto parts inventory management and tracking</li>
            <li>Parts availability monitoring</li>
            <li>Service records management</li>
            <li>Supplier information management</li>
            <li>User role-based access control</li>
        </ul>

        <h2>4. User Accounts and Security</h2>
        <p>As a user of our system, you are responsible for:</p>
        <ul>
            <li>Maintaining the confidentiality of your account credentials</li>
            <li>Providing accurate and up-to-date information</li>
            <li>Promptly reporting any unauthorized access or security breaches</li>
            <li>Ensuring all account activities comply with applicable laws and regulations</li>
            <li>Logging out from your account after each session</li>
        </ul>

        <h2>5. Parts and Services Management</h2>
        <p>Regarding our auto parts and services system:</p>
        <ul>
            <li>All parts entries include detailed specifications and descriptions</li>
            <li>Inventory levels are tracked and monitored in real-time</li>
            <li>Service records are maintained with complete documentation</li>
            <li>Parts can be marked as archived or active as needed</li>
            <li>Multiple parts can be managed simultaneously</li>
        </ul>

        <h2>6. System Access and Usage</h2>
        <p>Our system access policies include:</p>
        <ul>
            <li>Role-based access control for administrators and staff</li>
            <li>Secure login system with password protection</li>
            <li>User activity logging for accountability</li>
            <li>Regular system updates and maintenance</li>
            <li>Technical support for system users</li>
        </ul>

        <h2>7. Data Management</h2>
        <p>Our data management practices:</p>
        <ul>
            <li>Regular backup of system data</li>
            <li>Secure storage of parts and service information</li>
            <li>Organized supplier contact management</li>
            <li>Efficient search and filtering capabilities</li>
            <li>Data accuracy and integrity maintenance</li>
        </ul>

        <h2>8. Data Protection and Privacy</h2>
        <p>We are committed to protecting your data:</p>
        <ul>
            <li>Personal information is collected and processed in accordance with privacy laws</li>
            <li>Customer data is used only for service delivery and communication</li>
            <li>We implement industry-standard security measures</li>
            <li>Third-party access to data is strictly controlled</li>
            <li>You have the right to access and correct your personal information</li>
        </ul>

        <h2>9. Intellectual Property</h2>
        <p>All content, including but not limited to logos, images, text, and software on this website is protected by intellectual property rights. Users may not:</p>
        <ul>
            <li>Copy or reproduce any content without permission</li>
            <li>Use our trademarks or branding</li>
            <li>Modify or create derivative works</li>
            <li>Distribute or commercially exploit the content</li>
        </ul>

        <h2>10. Limitation of Liability</h2>
        <p>We limit our liability for:</p>
        <ul>
            <li>Direct or indirect losses arising from system use</li>
            <li>Service interruptions or technical issues</li>
            <li>Accuracy of parts information and availability</li>
            <li>Third-party services or products</li>
            <li>Force majeure events</li>
        </ul>

        <h2>11. Dispute Resolution</h2>
        <p>Any disputes shall be resolved through:</p>
        <ul>
            <li>Initial informal negotiation</li>
            <li>Formal written complaint process</li>
            <li>Mediation if necessary</li>
            <li>Legal proceedings as a last resort</li>
        </ul>

        <h2>12. Governing Law</h2>
        <p>These terms are governed by Philippine law. Any disputes shall be subject to the exclusive jurisdiction of Philippine courts.</p>

        <h2>13. Changes to Terms</h2>
        <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to the website. Continued use of the system constitutes acceptance of modified terms.</p>

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