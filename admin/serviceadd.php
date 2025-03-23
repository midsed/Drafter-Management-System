<?php 
ob_start();
session_start();
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

$_SESSION['UserID'] = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];
$username = $user['Username'];
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    .center-container {
        width: 50%; 
        max-width: 1000px; 
        margin: 0 auto; 
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
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
    
    .form-group {
        margin-bottom: 15px;
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

    .black-button {
        background-color: #272727;
    }
    .black-button:hover {
        background-color: #444;
    }
    .red-button {
        background-color: red;
    }
    .red-button:hover {
        background-color: darkred;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    
    .header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .header img {
        cursor: pointer;
    }
    .header h1 {
        margin: 0;
    }
    .error-message {
        color: red;
        font-size: 0.9em;
        display: none;
        margin-top: 5px;
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add Service</h1>
    </div>

    <!-- Added id="service-form" -->
    <div class="center-container">
        <form id="service-form" action="" method="POST">
            <div class="form-group">
                <label for="partName">Part Name:</label>
                <input type="text" id="partName" name="partName" required maxlength="100" 
                    pattern="^[A-Za-z0-9\s\-\_\.\,\!\?\@\#\$\%\^\&\*\(\)\+\=\[\]\{\}\|\:\;\'\">
                <!-- Span for Part Name error -->
                <span id="partName-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="fName">Customer First Name:</label>
                <input type="text" id="fName" name="fName" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="Invalid name format.">
                <span id="fName-error" class="error-message">
                    Invalid name format. Only letters and spaces allowed.
                </span>
            </div>

            <div class="form-group">
                <label for="lName">Customer Last Name:</label>
                <input type="text" id="lName" name="lName" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="Invalid name format.">
                <span id="lName-error" class="error-message">
                    Invalid name format. Only letters and spaces allowed.
                </span>
            </div>
            
            <div class="form-group">
                <label for="cEmail">Customer Email:</label>
                <input type="email" id="cEmail" name="cEmail" required 
                       pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" 
                       title="Please enter a valid email address (e.g., sample@sample.com).">
                <span id="cEmail-error" class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="pNumber">Customer Phone Number:</label>
                <input type="text" id="pNumber" name="pNumber" required 
                       pattern="^09\d{9}$" 
                       title="Invalid phone number format. Must start with 09 and be exactly 11 digits." 
                       value="09" maxlength="11" placeholder="e.g. 09171234567">
                <span id="pNumber-error" class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="type">Service Type:</label>
                <input type="text" id="type" name="type" required pattern="^[A-Za-z\s]+$" title="Invalid format.">
                <!-- Span for Service Type error -->
                <span id="type-error" class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" placeholder="0.00" required>
                <span id="price-error" class="error-message"></span>
            </div>
            
            <div class="actions">
                <button type="submit" class="black-button btn">Add</button>
                <button type="reset" class="red-button btn" onclick="resetForm()">Reset</button>
            </div>
        </form>
    </div>
</div>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type']; 
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $pNumber = $_POST['pNumber'];
    $cEmail = $_POST['cEmail'];
    $price = $_POST['price'];
    $partName = trim($_POST['partName']);
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

    $sql = "INSERT INTO service (Type, Date, Price, ClientEmail, PartName, StaffName) VALUES (?, ?, ?, ?, ?, ?)";
    $add = $conn->prepare($sql);
    $add->bind_param("ssssss", $type, $date_added, $price, $cEmail, $partName, $username);

    if ($add->execute()) {
        $timestamp = date("Y-m-d H:i:s");
        $adminId = $_SESSION['UserID'];
        $actionBy = $_SESSION['Username'];
        $actionType = "Added new Service";

        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID) VALUES (?, ?, ?, ?)");
        $log->bind_param("sssi", $username, $actionType, $timestamp, $user_id);
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
    }

    // Modified validateNameField to check for empty value as well as pattern match.
    function validateNameField(fieldId, errorId, fieldName) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);
        const pattern = /^[A-Za-z\s]+$/; // one or more letters/spaces

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

    // Validate phone field (checks required and pattern) on blur.
    function validatePhoneField(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElem = document.getElementById(errorId);

        // Enforce digit-only input and "09" prefix while typing.
        field.addEventListener("keydown", function(e) {
            if (field.value.startsWith("09")) {
                const start = field.selectionStart;
                const end = field.selectionEnd;
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
            field.value = value.slice(0, 11); // Limit to 11 digits.
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

    // Modified validateEmailField to check for required value.
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

    // Generic function to check that a required field is not empty.
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

    // Validate form submission (additional checks can be added as needed)
    function validateFormSubmission() {
        const phoneField = document.getElementById("pNumber");
        const phoneError = document.getElementById("pNumber-error");
        if (!/^09\d{9}$/.test(phoneField.value)) {
            phoneError.style.display = "block";
            phoneError.textContent = "Invalid phone number. Must be exactly 11 digits.";
            return false;
        }
        const emailField = document.getElementById("cEmail");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value.trim())) {
            document.getElementById("cEmail-error").style.display = "block";
            document.getElementById("cEmail-error").textContent = "Please enter a valid email address.";
            return false;
        }
        // Additional required checks could be performed here if desired.
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
        // Validate name fields with required check.
        validateNameField("fName", "fName-error", "First name");
        validateNameField("lName", "lName-error", "Last name");
        // Validate email field.
        validateEmailField("cEmail", "cEmail-error");
        // Validate phone field.
        validatePhoneField("pNumber", "pNumber-error");
        // Validate required for Part Name and Service Type.
        validateRequiredField("partName", "partName-error", "Part Name is required.");
        validateRequiredField("type", "type-error", "Service Type is required.");
        // Validate required for Price.
        validateRequiredField("price", "price-error", "Price is required.");
        
        document.getElementById("service-form").addEventListener("submit", function(e) {
            if (!validateFormSubmission()) {
                e.preventDefault();
            }
        });
    });
</script>
