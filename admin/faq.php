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
        <div class="faq-item">
            <h2 class="faq-question"><span class="icon">></span> How do I print the part retrieval receipt?</h2>
            <div class="faq-answer">
                <p>Go to Parts page > Add to Cart > Cart Icon at the top right corner > Print Receipt.
                </p>
            </div>
        </div>
        <div class="faq-item">
            <h2 class="faq-question"><span class="icon">></span> How do I archive multiple parts?</h2>
            <div class="faq-answer">
                <p>Go to Parts page > Select Mode > Select All or Click individual parts > Archive Selected > Confirm > Archive.
                </p>
            </div>
        </div>
        <div class="faq-item">
            <h2 class="faq-question"><span class="icon">></span> How do I reset my password?</h2>
            <div class="faq-answer">
                <p>To reset your password, click 'Forgot Password?'. Enter your E-mail and click 'Send OTP'. Check your E-mail and enter the OTP code and click the 'Verify Code' button. Then add your New Password.</p>
            </div>
        </div>
        <div class="faq-item">
            <h2 class="faq-question"><span class="icon">></span> Where can I find your terms and conditions?</h2>
            <div class="faq-answer">
                <p>Our terms and conditions can be found in the footer of our website or by clicking on the 'Terms' link.</p>
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