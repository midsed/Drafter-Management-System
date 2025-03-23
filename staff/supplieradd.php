

<?php
session_start();
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 
include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

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

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
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

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
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
        <a href="javascript:void(0);" onclick="window.history.back();">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add Supplier</h1>
    </div>

    <div class="center-container">
        <form id="entryForm">

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required maxlength="64" title="Please enter a valid email address (e.g., sample@sample.com).">
                <span id="email-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="supplier">Supplier Name:</label>
                <input type="text" id="supplier" name="supplier" required maxlength="40" title="Service is Required.">
                <span id="supplier-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="part">Part:</label>
                <input type="text" id="part" name="part" required title="Part is required.">
                <span id="part-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="phone">Supplier Phone Number:</label>
                <input type="text" id="phone" name="phone" required 
                       pattern="^09\d{9}$" 
                       value="09" maxlength="11" placeholder="e.g. 09171234567">
                <span id="phone-error" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Add</button>
                <button type="reset" class="btn" style="background-color: red;">Reset</button>
            </div>

        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    document.addEventListener("DOMContentLoaded", function() {
        validateNameField("supplier", "supplier-error", "Supplier Name");
        validateEmailField("email", "email-error");
        validatePhoneField("phone", "phone-error");
        validateRequiredField("part", "part-error", "Part is required.");

        document.getElementById("entryForm").addEventListener("submit", function(e) {
            if (!validateFormSubmission()) e.preventDefault();
        });

        function validateFormSubmission() {
            const fields = ["supplier", "email", "phone", "part"];
            let valid = true;

            fields.forEach(id => {
                const elem = document.getElementById(id);
                if (elem.value.trim() === "") {
                    const errorElem = document.getElementById(id + "-error");
                    errorElem.style.display = "block";
                    errorElem.textContent = "*";
                    valid = false;
                }
            });

            return valid;
        }

        function validateNameField(fieldId, errorId, fieldName) {
            const field = document.getElementById(fieldId);
            const errorElem = document.getElementById(errorId);
            const pattern = /^[A-Za-z\s]+$/;

            field.addEventListener("blur", function() {
                if (field.value.trim() === "") {
                    errorElem.style.display = "block";
                    errorElem.textContent = fieldName + " is required.";
                } else if (!pattern.test(field.value.trim())) {
                    errorElem.style.display = "block";
                    errorElem.textContent = "Only letters and spaces allowed.";
                } else {
                    errorElem.style.display = "none";
                }
            });

            field.addEventListener("focus", function() {
                errorElem.style.display = "none";
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

        function validateRequiredField(fieldId, errorId, message) {
            const field = document.getElementById(fieldId);
            const errorElem = document.getElementById(errorId);

            field.addEventListener("blur", function() {
                if (field.value.trim() === "") {
                    errorElem.style.display = "block";
                    errorElem.textContent = message;
                } else {
                    errorElem.style.display = "none";
                }
            });
        }
    });
</script>