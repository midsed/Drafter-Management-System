<?php 
ob_start();
session_start();
date_default_timezone_set('Asia/Manila');
require_once "dbconnect.php"; 

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

$user_id = $_SESSION['UserID'];
$check = $conn->prepare("SELECT UserID, RoleType, Username FROM user WHERE UserID = ?");
$check->bind_param("i", $user_id);
$check->execute();
$result = $check->get_result();
$user = $result->fetch_assoc();
$check->close();

if (!$user) {
    header("Location: /Drafter-Management-System/login.php");
    exit(); 
}

$_SESSION['UserID']   = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];
$username            = $user['Username'];
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
    }

    /* Main content padding so header doesn't overlap the form */
    .main-content {
        padding: 20px;
    }

    /* Keep header at top */
    .header {
        display: flex;
        align-items: center;
        margin-top: 60px;  /* newly added to bring it down the page */
        margin-bottom: 20px;
    }
    .header img {
        cursor: pointer;
    }
    .header h1 {
        margin: 0;
    }

    /*
      Center the .center-container horizontally,
      with some top spacing so it's not pinned at top.
    */
    .center-container {
        width: 80%;
        max-width: 1200px;
        margin: 40px auto; /* 40px from top, auto center horizontally */
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input, select, textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 400;
    }

    textarea {
        resize: vertical;
        height: 100px;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn {
        font-weight: bold;
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .btn:hover {
        background-color: #444;
    }
    .red-button {
        background-color: red;
    }
    .red-button:hover {
        background-color: darkred;
    }

    .error-message {
        color: red;
        font-size: 0.9em;
        display: none;
        margin-top: 5px;
    }

    /* Grid-like layout for the form: three columns per row */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .form-row .form-group {
        flex: 1 1 calc(33.333% - 10px);
        min-width: 250px;
    }
</style>

<div class="main-content">
    <!-- HEADER AT TOP -->
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add Service</h1>
    </div>

    <!-- ONLY THE FORM IS CENTERED (horizontally) BELOW -->
    <div class="center-container">
        <form id="service-form" action="" method="POST">
            <!-- Row 1: Part Name, Customer First Name, Customer Last Name -->
            <div class="form-row">
                <div class="form-group">
                    <label for="partName">Part Name:</label>
                    <input 
                        type="text" 
                        id="partName" 
                        name="partName" 
                        required 
                        maxlength="100"
                        pattern="^[A-Za-z0-9\s\-\_\.\,\!\?\@\#\$\%\^\&\*\(\)\+\=\[\]\{\}\|\:\;\'\"]+$">
                    <span id="partName-error" class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="fName">Customer First Name:</label>
                    <input 
                        type="text" 
                        id="fName" 
                        name="fName" 
                        required 
                        maxlength="40"
                        pattern="^[A-Za-z\s]+$" 
                        title="Invalid name format.">
                    <span id="fName-error" class="error-message">
                        Invalid name format. Only letters and spaces allowed.
                    </span>
                </div>

                <div class="form-group">
                    <label for="lName">Customer Last Name:</label>
                    <input 
                        type="text" 
                        id="lName" 
                        name="lName" 
                        required 
                        maxlength="40"
                        pattern="^[A-Za-z\s]+$" 
                        title="Invalid name format.">
                    <span id="lName-error" class="error-message">
                        Invalid name format. Only letters and spaces allowed.
                    </span>
                </div>
            </div>
            
            <!-- Row 2: Customer Email, Phone Number, Service Type -->
            <div class="form-row">
                <div class="form-group">
                    <label for="cEmail">Customer Email:</label>
                    <input 
                        type="email" 
                        id="cEmail" 
                        name="cEmail" 
                        required
                        pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" 
                        title="Please enter a valid email address (e.g., sample@sample.com).">
                    <span id="cEmail-error" class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="pNumber">Customer Phone Number:</label>
                    <input 
                        type="text" 
                        id="pNumber" 
                        name="pNumber" 
                        required
                        pattern="^09\d{9}$" 
                        title="Invalid phone number format. Must start with 09 and be exactly 11 digits."
                        value="09" 
                        maxlength="11" 
                        placeholder="e.g. 09171234567">
                    <span id="pNumber-error" class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="type">Service Type:</label>
                    <input 
                        type="text" 
                        id="type" 
                        name="type" 
                        required
                        pattern="^[A-Za-z\s]+$" 
                        title="Invalid format.">
                    <span id="type-error" class="error-message"></span>
                </div>
            </div>

            <!-- Row 3: Price alone -->
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        placeholder="0.00" 
                        required>
                    <span id="price-error" class="error-message"></span>
                </div>
            </div>

            <!-- Actions row -->
            <div class="actions">
                <button type="submit" class="btn">Add</button>
                <button type="reset" class="btn red-button" onclick="resetForm()">Reset</button>
            </div>
        </form>
    </div>
</div>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type      = $_POST['type']; 
    $fName     = $_POST['fName'];
    $lName     = $_POST['lName'];
    $pNumber   = $_POST['pNumber'];
    $cEmail    = $_POST['cEmail'];
    $price     = $_POST['price'];
    $partName  = trim($_POST['partName']);
    $date_added = date('Y-m-d H:i:s');

    $checkClient = $conn->prepare("SELECT ClientEmail FROM client WHERE ClientEmail = ?");
    $checkClient->bind_param("s", $cEmail);
    $checkClient->execute();
    $checkClient->store_result();

    if ($checkClient->num_rows == 0) {
        $insertClient = "INSERT INTO client (ClientEmail, FName, LName, PhoneNumber) VALUES (?, ?, ?, ?)";
        $addClient = $conn->prepare($insertClient);
        $addClient->bind_param("ssss", $cEmail, $fName, $lName, $pNumber);
        $addClient->execute();
        $addClient->close();
    }
    $checkClient->close();

    $sql = "INSERT INTO service (Type, Date, Price, ClientEmail, PartName, StaffName) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $add = $conn->prepare($sql);
    $add->bind_param("ssssss", $type, $date_added, $price, $cEmail, $partName, $username);

    if ($add->execute()) {
        $timestamp  = date("Y-m-d H:i:s");
        $adminId    = $_SESSION['UserID'];
        $actionBy   = $_SESSION['Username']; 
        $actionType = "Added new Service: " . $type;

        $log = $conn->prepare("INSERT INTO logs (ActionType, Timestamp, UserID, ActionBy) 
                               VALUES (?, ?, ?, ?)");
        $log->bind_param("ssis", $actionType, $timestamp, $user_id, $actionBy);
        $log->execute();
        $log->close();

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Success!",
                text: "Service added successfully!",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#6c5ce7"
            }).then(() => {
                window.location = "service.php";
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Error adding service",
                icon: "error",
                confirmButtonText: "OK",
                confirmButtonColor: "#d63031"
            });
        </script>';
    }

    $add->close();
    $conn->close();
}
?>

<script>
    // Toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');

        // Save the sidebar state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }

    function checkSidebarState() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('collapsed');
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        checkSidebarState();
    });

    /* ALL JS VALIDATIONS UNCHANGED */

    function validateNameField(fieldId, errorId, fieldName) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const pattern = /^[A-Za-z\s]+$/;

        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });

        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = fieldName + " is required.";
            } else if (!pattern.test(field.value)) {
                errorElem.style.display = "block";
                errorElem.textContent = "Invalid name format. Only letters and spaces allowed.";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    function validatePhoneField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);

        field.addEventListener("keydown", function(e) {
            if (field.value.startsWith("09")) {
                const start = field.selectionStart;
                const end   = field.selectionEnd;
                if ((e.key === "Backspace" && start <= 2) ||
                    (e.key === "Delete" && start < 2) ||
                    (start < 2 && end > 0)) {
                    e.preventDefault();
                }
            }
        });
        field.addEventListener("keypress", function(e) {
            const char = String.fromCharCode(e.which);
            if (!/^\d$/.test(char)) {
                e.preventDefault();
            }
        });
        field.addEventListener("input", function() {
            let value = field.value;
            if (value === "") {
                value = "09";
            }
            value = value.replace(/\D/g, "");
            if (!value.startsWith("09")) {
                value = "09" + value;
            }
            field.value = value.slice(0, 11);
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });
        field.addEventListener("blur", function() {
            const value = field.value;
            if (value.trim() === "" || value === "09") {
                errorElem.style.display = "block";
                errorElem.textContent = "Phone number is required.";
            } else if (!/^09\d{9}$/.test(value)) {
                errorElem.style.display = "block";
                errorElem.textContent = "Invalid phone number. Must be exactly 11 digits.";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    function validateEmailField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });
        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = "Email is required.";
            } else if (!emailRegex.test(field.value.trim())) {
                errorElem.style.display = "block";
                errorElem.textContent = "Please enter a valid email address (e.g., sample@sample.com).";
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    function validateRequiredField(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);

        field.addEventListener("focus", function() {
            errorElem.style.display = "none";
            errorElem.textContent = "";
        });
        field.addEventListener("blur", function() {
            if (field.value.trim() === "") {
                errorElem.style.display = "block";
                errorElem.textContent = message;
            } else {
                errorElem.style.display = "none";
                errorElem.textContent = "";
            }
        });
    }

    function validateFormSubmission() {
        const phoneField = document.getElementById("pNumber");
        const phoneError = document.getElementById("pNumber-error");
        if (!/^09\d{9}$/.test(phoneField.value)) {
            phoneError.style.display = "block";
            phoneError.textContent = "Invalid phone number. Must be exactly 11 digits.";
            return false;
        }

        const emailField = document.getElementById("cEmail");
        const emailError = document.getElementById("cEmail-error");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value.trim())) {
            emailError.style.display = "block";
            emailError.textContent = "Please enter a valid email address.";
            return false;
        }
        return true;
    }

    function resetForm() {
        Swal.fire({
            title: "Are you sure?",
            text: "This will reset all informations.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, reset it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d63031",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector("form").reset();
                document.querySelectorAll("input").forEach(input => input.value = "");
                Swal.fire({
                    title: "Reset!",
                    text: "The form has been reset.",
                    icon: "success",
                    confirmButtonColor: "#6c5ce7"
                });
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        validateNameField("fName", "fName-error", "First name");
        validateNameField("lName", "lName-error", "Last name");
        validateEmailField("cEmail", "cEmail-error");
        validatePhoneField("pNumber", "pNumber-error");

        validateRequiredField("partName", "partName-error", "Part Name is required.");
        validateRequiredField("type", "type-error", "Service Type is required.");
        validateRequiredField("price", "price-error", "Price is required.");

        document.getElementById("service-form").addEventListener("submit", function(e) {
            if (!validateFormSubmission()) {
                e.preventDefault();
            }
        });
    });
</script>
