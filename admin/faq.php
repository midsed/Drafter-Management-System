<?php
session_start();
include('dbconnect.php'); 

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
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
            <div class="faq-item">
                <h2 class="faq-question"><span class="icon">></span> How do generate a report?</h2>
                <div class="faq-answer">
                    <ol>
                        <li>Click the "Generate Report" button on the top right corner</li>
                        <li>You can customize the elements you want to add in the PDF report</li>
                        <li>You can click "Update Preview" to preview the design of the report</li>
                        <li>Export via PDF or Excel</li>
                    </ol>
                </div>
            </div>
        </div>
         <!-- System Administration -->
         <div class="faq-category">
             <h2>System Administration</h2>
             <div class="faq-item">
                 <h2 class="faq-question"><span class="icon">></span> How do I manage user accounts?</h2>
                 <div class="faq-answer">
                     <ol>
                         <li>Access the Users section from the sidebar</li>
                         <li>View all user accounts and their roles</li>
                         <li>To add a new user:
                             <ul>
                                 <li>Click "Add New User"</li>
                                 <li>Fill in user details and assign role</li>
                                 <li>Set initial password</li>
                                 <li>Save the new account</li>
                             </ul>
                         </li>
                         <li>To modify existing users:
                             <ul>
                                 <li>Click the edit icon next to the user</li>
                                 <li>Update necessary information</li>
                                 <li>Save changes</li>
                             </ul>
                         </li>
                     </ol>
                 </div>
             </div>
         </div>

         <!-- Inventory Management -->
         <div class="faq-category">
             <h2>Inventory Management</h2>
             <div class="faq-item">
                 <h2 class="faq-question"><span class="icon">></span> How do I manage parts inventory?</h2>
                 <div class="faq-answer">
                     <ol>
                         <li>Navigate to the Parts section</li>
                         <li>Monitor inventory levels in real-time</li>
                         <li>Add new parts:
                             <ul>
                                 <li>Click "Add New Part"</li>
                                 <li>Enter part details and specifications</li>
                                 <li>Set initial quantity and price</li>
                                 <li>Upload images if available</li>
                             </ul>
                         </li>
                         <li>Update existing parts:
                             <ul>
                                 <li>Click edit on the desired part</li>
                                 <li>Modify details as needed</li>
                                 <li>Save changes</li>
                             </ul>
                         </li>
                     </ol>
                 </div>
             </div>

             <div class="faq-item">
                 <h2 class="faq-question"><span class="icon">></span> How do I archive multiple parts?</h2>
                 <div class="faq-answer">
                     <ol>
                         <li>Go to Parts Management</li>
                         <li>Enable "Select Mode"</li>
                         <li>Choose parts to archive</li>
                         <li>Click "Archive Selected"</li>
                         <li>Confirm the action</li>
                         <li>Parts will be moved to archive</li>
                     </ol>
                 </div>
             </div>
         </div>
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
                        <li>Go to Retrieve Part (icon at top right)</li>
                        <li>Review your items</li>
                        <li>Click "Print Receipt"</li>
                        <li>A printable receipt will be generated</li>
                    </ol>
                </div>
            </div>
        </div>
         <!-- Service Management -->
         <div class="faq-category">
             <h2>Service Records</h2>
             <div class="faq-item">
                 <h2 class="faq-question"><span class="icon">></span> How do I manage service records?</h2>
                 <div class="faq-answer">
                     <ol>
                         <li>Access the Services section</li>
                         <li>View all service records</li>
                         <li>Add new service record:
                             <ul>
                                 <li>Click "Add New Service"</li>
                                 <li>Enter service details</li>
                                 <li>Add parts used</li>
                                 <li>Record labor costs</li>
                             </ul>
                         </li>
                         <li>Generate service reports as needed</li>
                     </ol>
                 </div>
             </div>
         </div>

         <!-- System Settings -->
         <div class="faq-category">
             <h2>System Settings</h2>
             <div class="faq-item">
                 <h2 class="faq-question"><span class="icon">></span> How do I reset my password?</h2>
                 <div class="faq-answer">
                     <ol>
                         <li>Click 'Forgot Password?' on login page</li>
                         <li>Enter your email address</li>
                         <li>Click 'Send OTP'</li>
                         <li>Check email for OTP code</li>
                         <li>Enter code and verify</li>
                         <li>Set new password</li>
                     </ol>
                 </div>
             </div>

             <div class="faq-item">
                 <h2 class="faq-question"><span class="icon">></span> Where can I find system documentation?</h2>
                 <div class="faq-answer">
                     <p>Access system documentation through:</p>
                     <ul>
                         <li>Terms and Conditions in the footer</li>
                         <li>FAQ section (current page)</li>
                         <li>Help section in the sidebar</li>
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