<?php
ob_start();
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
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
    header("Location: /Drafter-Management-System/staff/service.php"); 
    exit(); 
}

$_SESSION['UserID'] = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];
$username = $user['Username'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /Drafter-Management-System/admin/service.php"); 
    exit(); 
}

$service_id = $_GET['id'];
$query = $conn->prepare("SELECT s.*, c.FName, c.LName, c.PhoneNumber 
                         FROM service s
                         LEFT JOIN client c ON s.ClientEmail = c.ClientEmail
                         WHERE s.ServiceID = ?");
$query->bind_param("i", $service_id);
$query->execute();
$result = $query->get_result();
$service = $result->fetch_assoc();
$query->close();

if (!$service) {
    echo '<script>window.location.reload();</script>';
    exit();
}
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
        <h1>Edit Service</h1>
    </div>

    <div class="center-container">
        <form id="service-form" action="serviceedit_process.php" method="POST">
            <!-- Hidden field for service_id -->
            <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service['ServiceID']); ?>">

            <!-- Part Name Field -->
            <div class="form-group">
                <label for="part_name">Part Name:</label>
                <input type="text" id="part_name" name="part_name" required maxlength="100" 
                    pattern="^[A-Za-z0-9\s\-\_\.\,\!\?\@\#\$\%\^\&\*\(\)\+\=\[\]\{\}\|\:\;\'\"
                    title="Enter a valid part name. Special characters and numbers are allowed."
                    value="<?php echo htmlspecialchars($service['PartName'] ?? ''); ?>">
                    <span id="part_name-error" class="error-message"></span>
            </div>

            <!-- Customer First Name Field -->
            <div class="form-group">
                <label for="fName">Customer First Name:</label>
                <input type="text" id="fName" name="fName" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="Invalid name format."
                       value="<?php echo htmlspecialchars($service['FName'] ?? ''); ?>">
                <span id="fName-error" class="error-message"></span>
            </div>

            <!-- Customer Last Name Field -->
            <div class="form-group">
                <label for="lName">Customer Last Name:</label>
                <input type="text" id="lName" name="lName" required maxlength="40" 
                       pattern="^[A-Za-z\s]+$" title="Invalid name format."
                       value="<?php echo htmlspecialchars($service['LName'] ?? ''); ?>">
                <span id="lName-error" class="error-message"></span>
            </div>
            
            <!-- Customer Email Field (Any valid email ending with .com) -->
            <div class="form-group">
                <label for="client_email">Customer Email:</label>
                <input type="email" id="client_email" name="client_email" required
                    pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" 
                    title="Please enter a valid email address (e.g., sample@sample.com)." 
                    value="<?php echo htmlspecialchars($service['ClientEmail']); ?>">
                <span id="client_email-error" class="error-message"></span>
            </div>
            
            <!-- Customer Phone Number Field -->
            <div class="form-group">
                <label for="pNumber">Customer Phone Number:</label>
                <!-- Use type="text" for JS control; prepopulate with existing phone number -->
                <input type="text" id="pNumber" name="pNumber" required 
                       value="<?php echo htmlspecialchars($service['PhoneNumber'] ?? '09'); ?>" 
                       maxlength="11" placeholder="e.g., 09171234567">
                <span id="pNumber-error" class="error-message"></span>
            </div>
            
            <!-- Service Type Field -->
            <div class="form-group">
                <label for="type">Service Type:</label>
                <input type="text" id="type" name="type" required pattern="^[A-Za-z\s]+$" 
                       title="Invalid format. Only letters and spaces allowed."
                       value="<?php echo htmlspecialchars($service['Type']); ?>">
                <span id="type-error" class="error-message"></span>
            </div>
            
            <!-- Price Field -->
            <div class="form-group">
                <label for="price">Service Price:</label>
                <input type="number" id="price" name="price" placeholder="0.00" required
                    title=""
                       value="<?php echo htmlspecialchars($service['Price']); ?>">
                <span id="price-error" class="error-message"></span>
            </div>
            
            <!-- Hidden Part ID Field (if needed) -->
            <input type="hidden" id="part_id" name="part_id" value="<?php echo htmlspecialchars($service['PartID'] ?? ''); ?>">

            <div class="actions">
                <button type="submit" class="black-button btn">Update</button>
                <button type="button" class="red-button btn" onclick="resetForm()">Reset</button>
            </div>
        </form>
    </div>
</div>

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
        const emailField = document.getElementById("client_email");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value.trim())) {
            document.getElementById("client_email-error").style.display = "block";
            document.getElementById("client_email-error").textContent = "Please enter a valid email address.";
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
        validateEmailField("client_email", "client_email-error");
        // Validate phone field.
        validatePhoneField("pNumber", "pNumber-error");
        // Validate required for Part Name and Service Type.
        validateRequiredField("part_name", "part_name-error", "Part Name is required.");
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