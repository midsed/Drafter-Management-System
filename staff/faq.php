<?php
session_start();
include('dbconnect.php'); 

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

$userID = $_SESSION['UserID'];
$userQuery = "SELECT UserName FROM user WHERE UserID = '$userID'";
$userResult = mysqli_query($conn, $userQuery);
$user = mysqli_fetch_assoc($userResult);
$userName = $user['UserName'] ?? 'User ';

include('navigation/sidebar.php'); 
include('navigation/topbar.php'); 
?>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="font-family: 'Poppins', sans-serif;">Frequently Asked Questions (FAQ)</h1>
    </div>

    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
    </div>

    <div class="faq-section">
        <!-- System Navigation -->
        <div class="faq-category">
            <h2>System Navigation</h2>
            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do I navigate the dashboard?</h2>
                <div class="faq-answer">
                    <p>The dashboard provides easy access to all features:</p>
                    <ol>
                        <li>Use the sidebar menu (☰) on the left to access different sections</li>
                        <li>Click the toggle button to expand/collapse the sidebar</li>
                        <li>View quick statistics and summaries in dashboard cards</li>
                        <li>Access your account information and notifications from the top bar</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Parts Management -->
        <div class="faq-category">
            <h2>Parts Management</h2>
            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do I add new parts to the system?</h2>
                <div class="faq-answer">
                    <ol>
                        <li>Go to the Parts section</li>
                        <li>Click "Add New Part"</li>
                        <li>Fill in the required details:
                            <ul>
                                <li>Part name and description</li>
                                <li>Category and specifications</li>
                                <li>Quantity and price</li>
                                <li>Upload images if available</li>
                            </ul>
                        </li>
                        <li>Click "Save" to add the part</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do I archive multiple parts?</h2>
                <div class="faq-answer">
                    <ol>
                        <li>Go to Parts page</li>
                        <li>Click "Select Mode"</li>
                        <li>Choose "Select All" or click individual parts</li>
                        <li>Click "Archive Selected"</li>
                        <li>Confirm the action</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Cart Management -->
        <div class="faq-category">
            <h2>Part Retrieval</h2>
            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do I use the part retrieve function?</h2>
                <div class="faq-answer">
                    <ol>
                        <li>Browse the Parts section</li>
                        <li>Click "Part Retrieve Icon" for desired items</li>
                        <li>Access your inventory using the retrieve part icon at the top right</li>
                        <li>Adjust quantities as needed</li>
                        <li>Click "Proceed" to complete the retreival process</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do I print the part retrieval receipt?</h2>
                <div class="faq-answer">
                    <ol>
                        <li>Go to Cart (icon at top right)</li>
                        <li>Review your items</li>
                        <li>Click "Print Receipt"</li>
                        <li>A printable receipt will be generated</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Account Management -->
        <div class="faq-category">
            <h2>Account Management</h2>
            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do I reset my password?</h2>
                <div class="faq-answer">
                    <ol>
                        <li>Click 'Forgot Password?' on the login page</li>
                        <li>Enter your email address</li>
                        <li>Click 'Send OTP'</li>
                        <li>Check your email for the OTP code</li>
                        <li>Enter the code and click 'Verify Code'</li>
                        <li>Set your new password</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> Where can I find your terms and conditions?</h2>
                <div class="faq-answer">
                    <p>Access our terms and conditions in two ways:</p>
                    <ul>
                        <li>Click the 'Terms and Conditions' link in the footer</li>
                        <li>Visit the Terms page through the sidebar menu</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>
        <a href="termsconditions.php">Terms and Conditions</a>
    </p>
</footer>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const icon = question.querySelector('.icon');

            if (answer.style.maxHeight) {
                answer.style.maxHeight = null;
                icon.classList.remove('rotated');
            } else {
                answer.style.maxHeight = answer.scrollHeight + "px";
                icon.classList.add('rotated');
            }
        });
    });

    document.getElementById("searchInput").addEventListener("keyup", function(event) {
        const filter = event.target.value.toLowerCase();
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const questionText = item.querySelector('.faq-question').textContent.toLowerCase();
            if (questionText.includes(filter)) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });
    });
</script>

<style>
body {
    font-family: 'Poppins', sans-serif;
}

.header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.header a {
    text-decoration: none;
}

.search-container {
    margin-top: 10px;
    margin-bottom: 20px;
    text-align: left; 
}

.search-container input[type="text"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    width: 100%;
    max-width: 300px;
}

.search-container input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
}

.faq-section {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 5px;
    margin: 20px 0;
}

.faq-item {
    margin-bottom: 15px;
    border-bottom: 1px solid #ddd;
    padding: 10px 0;
}

.faq-question {
    cursor: pointer;
    color: black;
    margin: 0;
    display: flex;
    align-items: center;
    transition: color 0.3s;
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    color: black;
}

.icon {
    margin-right: 10px;
    transition: transform 0.3s;
}

.icon.rotated {
    transform: rotate(90deg);
}

.faq-item:hover .faq-question {
    color: #007bff;
}

.footer {
    background-color: lightgrey;
    padding: 10px;
    text-align: center;
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    z-index: 1;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
}

.footer a {
    color: black;
    text-decoration: none;
}

.footer a:hover {
    text-decoration: underline;
}

.sidebar {
    z-index: 5;
}

.topbar {
    z-index: 10;
}
</style>