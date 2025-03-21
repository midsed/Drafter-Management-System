<?php
session_start();
include('dbconnect.php'); 

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] === 'Staff') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

// Fetch user information
$userID = $_SESSION['UserID'];
$userQuery = "SELECT UserName FROM user WHERE UserID = '$userID'";
$userResult = mysqli_query($conn, $userQuery);
$user = mysqli_fetch_assoc($userResult);
$userName = $user['UserName'] ?? 'User '; // Default to 'User ' if not found

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

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    // FAQ toggle functionality
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const icon = question.querySelector('.icon');

            // Toggle the height for smooth animation
            if (answer.style.maxHeight) {
                answer.style.maxHeight = null;
                icon.classList.remove('rotated');
            } else {
                answer.style.maxHeight = answer.scrollHeight + "px"; // Set to the scroll height for animation
                icon.classList.add('rotated');
            }
        });
    });

    // Quick search functionality
    document.getElementById("searchInput").addEventListener("keyup", function(event) {
        const filter = event.target.value.toLowerCase();
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const questionText = item.querySelector('.faq-question').textContent.toLowerCase();
            if (questionText.includes(filter)) {
                item.style.display = ""; // Show item
            } else {
                item.style.display = "none"; // Hide item
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
    margin-top: 10px; /* Space between title and search bar */
    margin-bottom: 20px; /* Space below the search bar */
    text-align: left; /* Align text to the left */
}

.search-container input[type="text"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    width: 100%; /* Make the search bar take the full width */
    max-width: 300px; /* Optional: Set a max width for the search bar */
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
    color: black; /* Set text color to black */
    margin: 0;
    display: flex; /* Use flexbox for alignment */
    align-items: center; /* Center items vertically */
    transition: color 0.3s;
}

.faq-answer {
    max-height: 0; /* Initially hide answers */
    overflow: hidden; /* Hide overflow */
    transition: max-height 0.3s ease; /* Smooth transition for height */
    color: black; /* Set answer text color to black */
}

.icon {
    margin-right: 10px; /* Space between icon and text */
    transition: transform 0.3s; /* Smooth rotation */
}

.icon.rotated {
    transform: rotate(90deg); /* Rotate icon when answer is shown */
}

/* Additional styles for the FAQ section */
.faq-item:hover .faq-question {
    color: #007bff; /* Change color on hover */
}
</style>