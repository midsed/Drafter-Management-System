<?php 
ob_start();
session_start();
require_once "dbconnect.php"; 

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') { 
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
    die("Access Denied: Invalid user session. Please log in again.");
}

$_SESSION['UserID'] = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];
$username = $user['Username'];
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<style>
    .form-group {
        margin-bottom: 15px;
    }

    .error-message {
    color: red;
    font-size: 12px;
    margin-top: 5px;
    display: block;
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
    }

    textarea {
        resize: vertical;
        height: 100px;
    }

    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
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
    
    .center-container {
        width: 50%; 
        max-width: 1000px; 
        margin: 0 auto; 
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .quantity-container {
        display: flex;
        align-items: center;
    }

    .quantity-container button {
        background-color: #272727;
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        font-size: 18px;
        cursor: pointer;
        margin: 0 5px;
        border-radius: 3px;
    }

    .quantity-container button:hover {
        background-color: #444;
    }

    .quantity-container input {
        text-align: center;
        width: 60px;
    }

    .image-preview {
        display: flex; justify-content: center; align-items: center; margin-bottom: 15px;
    }
    .image-preview img {
        max-width: 300px; height: auto; border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add Parts</h1>
    </div>
    <div class="center-container">
        <form action="" method="POST" enctype="multipart/form-data">
            <!-- Part Details -->
            <div class="form-group">
                <label for="part_name">Part Name:</label>
                <input type="text" id="part_name" name="part_name" required>
            </div>
            
            <div class="form-group">
                <label for="part_price">Part Price:</label>
                <input type="number" placeholder="0.00" id="part_price" name="part_price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <div class="quantity-container">
                    <button type="button" onclick="decreaseQuantity()">−</button>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    <button type="button" onclick="increaseQuantity()">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="make">Make:</label>
                <input type="text" id="make" name="make" required>
            </div>

            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" id="model" name="model" required>
            </div>

            <div class="form-group">
                <label for="year_model">Year Model:</label>
                <input type="text" id="year_model" name="year_model" maxlength="4" pattern="\d{4}" required 
                    title="Enter a year">
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="" selected disabled>Select Category</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Body Panel">Body Panel</option>
                    <option value="Brakes">Brakes</option>
                    <option value="Engine & Transmission">Engine & Transmission</option>
                    <option value="Interior">Interior</option>
                    <option value="Lights">Lights</option>
                    <option value="Suspension">Suspension</option>
                    <option value="Wheels & Tires">Wheels & Tires</option>
                </select>
            </div>

            <div class="form-group">
                <label for="authenticity">Authenticity:</label>
                <select id="authenticity" name="authenticity" required>
                    <option value="" selected disabled>Select Authenticity</option>
                    <option value="Genuine">Genuine</option>
                    <option value="Replacement">Replacement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="condition">Condition:</label>
                <select id="condition" name="condition" required>
                    <option value="" selected disabled>Select Condition</option>
                    <option value="Used">Used</option>
                    <option value="New">New</option>
                    <option value="For Repair">For Repair</option>
                </select>
            </div>

            <div class="form-group">
                <label for="item_status">Item Status:</label>
                <select id="item_status" name="item_status" required>
                    <option value="" selected disabled>Select Status</option>
                    <option value="Available">Available</option>
                    <option value="Used for Service">Used for Service</option>
                    <option value="Surrendered">Surrendered</option>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location"  name="location" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="part_image">Upload Image:</label>
                <div class="image-preview">
                    <img id="previewImage" src="images/no-image.png" alt="No Image Available">
                </div>
                <input type="file" id="part_image" name="part_image" accept="image/*" required onchange="previewFile(event)">
            </div>

            <!-- Supplier Details -->
            <h2>Supplier Details</h2>
            <div class="form-group">
                <label for="supplier_name">Supplier Name:</label>
                <input type="text" id="supplier_name" name="supplier_name">
            </div>

            <div class="form-group">
                <label for="supplier_email">Supplier Email:</label>
                <input type="email" id="supplier_email" name="supplier_email">
            </div>

            <div class="form-group">
                <label for="supplier_phone">Supplier Phone Number:</label>
                <input type="text" id="supplier_phone" name="supplier_phone">
            </div>

            <div class="form-group">
                <label for="supplier_address">Supplier Address:</label>
                <textarea id="supplier_address" name="supplier_address" placeholder="Extension, B113 L12 Mindanao Avenue, corner Regalado Hwy, Quezon City, 1100"></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="black-button btn">Add</button>
                <button type="reset" class="red-button btn">Clear</button>
            </div>
        </form>
    </div>
</div>

<script>
    function increaseQuantity() {
        let quantity = document.getElementById('quantity');
        quantity.value = parseInt(quantity.value) + 1;
    }

    function decreaseQuantity() {
        let quantity = document.getElementById('quantity');
        if (quantity.value > 1) {
            quantity.value = parseInt(quantity.value) - 1;
        }
    }
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    function previewFile(event) {
    const preview = document.getElementById('previewImage');
    const fileInput = event.target;
    const file = fileInput.files[0];

    // Strictly allow only JPG and PNG
    const allowedExtensions = /\.(jpg|jpeg|png|heic)$/i;

    if (file) {
        if (!allowedExtensions.test(file.name)) {
            alert("Invalid file type! JPG, JPEG, HEIC and PNG are allowed.");
            fileInput.value = "";
            preview.src = "images/no-image.png";
            return;
        }

        // Preview the valid image
        const reader = new FileReader();
        reader.onload = function() {
            preview.src = reader.result;
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = "images/no-image.png";
    }
}

// Show and clear error functions
function showError(input, message) {
    let errorSpan = input.nextElementSibling;
    if (!errorSpan || !errorSpan.classList.contains("error-message")) {
        errorSpan = document.createElement("span");
        errorSpan.classList.add("error-message");
        errorSpan.style.color = "red";
        errorSpan.style.fontSize = "12px";
        input.parentNode.appendChild(errorSpan);
    }
    errorSpan.textContent = message;
}

function clearError(input) {
    let errorSpan = input.nextElementSibling;
    if (errorSpan && errorSpan.classList.contains("error-message")) {
        errorSpan.remove();
    }
}

    document.addEventListener("DOMContentLoaded", function() {
        const clearButton = document.querySelector(".red-button");
        if (clearButton) {
            clearButton.addEventListener("click", function(event) {
                event.preventDefault(); // Prevent immediate clearing
                
                Swal.fire({
                    title: "Are you sure?",
                    text: "This will clear all the informations.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, clear it!",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#d63031",
                    cancelButtonColor: "#6c5ce7"
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.querySelector("form").reset();
                        document.getElementById('previewImage').src = "images/no-image.png"; // Reset image preview
                    }
                });
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const partNameInput = document.getElementById("part_name");
        const partPriceInput = document.getElementById("part_price");
        const makeInput = document.getElementById("make");
        const modelInput = document.getElementById("model");
        const yearModelInput = document.getElementById("year_model");
        const categoryInput = document.getElementById("category");
        const authenticityInput = document.getElementById("authenticity");
        const conditionInput = document.getElementById("condition");
        const itemStatusInput = document.getElementById("item_status");
        const locationInput = document.getElementById("location");
        const partImageInput = document.getElementById("part_image");

        // Validate Part Name
        function validatePartName() {
            if (partNameInput.value.trim() === "") {
                showError(partNameInput, "Part Name is required.");
                return false;
            } else {
                clearError(partNameInput);
                return true;
            }
        }

        // Validate Part Price
        function validatePartPrice() {
            let value = parseFloat(partPriceInput.value);
            if (isNaN(value) || value <= 0) {
                showError(partPriceInput, "Price must be greater than 0.00.");
                partPriceInput.value = '0.00';
                return false;
            } else {
                clearError(partPriceInput);
                return true;
            }
        }

        // Validate Make and Model (No special characters)
        function validateMakeAndModel(input) {
            let value = input.value;
            let validValue = value.replace(/[^a-zA-Z0-9\s]/g, "");
            
            if (value !== validValue) {
                input.value = validValue;
                showError(input, "No special characters allowed.");
            } else {
                clearError(input);
            }
        }

        // Validate Year Model (Only 4 digits)
        function validateYearModel() {
            if (yearModelInput.value.length !== 4) {
                showError(yearModelInput, "Year must be exactly 4 digits.");
                return false;
            } else {
                clearError(yearModelInput);
                return true;
            }
        }

        // Validate Required Fields
        function validateRequired(input) {
            if (input.value.trim() === "") {
                showError(input, "This field is required.");
                return false;
            } else {
                clearError(input);
                return true;
            }
        }

        // Event Listeners for Blur Events
        partNameInput.addEventListener("blur", validatePartName);
        partPriceInput.addEventListener("blur", validatePartPrice);
        makeInput.addEventListener("blur", () => validateMakeAndModel(makeInput));
        modelInput.addEventListener("blur", () => validateMakeAndModel(modelInput));
        yearModelInput.addEventListener("blur", validateYearModel);
        categoryInput.addEventListener("blur", () => validateRequired(categoryInput));
        authenticityInput.addEventListener("blur", () => validateRequired(authenticityInput));
        conditionInput.addEventListener("blur", () => validateRequired(conditionInput));
        itemStatusInput.addEventListener("blur", () => validateRequired(itemStatusInput));
        locationInput.addEventListener("blur", () => validateRequired(locationInput));
        partImageInput.addEventListener("blur", () => validateRequired(partImageInput));

        // Submit Button Validation
        const submitButton = document.querySelector("button[type='submit']");
        submitButton.addEventListener("click", function (event) {
            let isValid = true;

            if (!validatePartName()) isValid = false;
            if (!validatePartPrice()) isValid = false;
            if (!validateMakeAndModel(makeInput)) isValid = false;
            if (!validateMakeAndModel(modelInput)) isValid = false;
            if (!validateYearModel()) isValid = false;
            if (!validateRequired(categoryInput)) isValid = false;
            if (!validateRequired(authenticityInput)) isValid = false;
            if (!validateRequired(conditionInput)) isValid = false;
            if (!validateRequired(itemStatusInput)) isValid = false;
            if (!validateRequired(locationInput)) isValid = false;
            if (!validateRequired(partImageInput)) isValid = false;

            if (!isValid) {
                event.preventDefault();
            }
        });
    });

documentaddEventListener("DOMContentLoaded", function () {
    const supplierNameInput = document.getElementById("supplier_name");
    const supplierEmailInput = document.getElementById("supplier_email");
    const supplierPhoneInput = document.getElementById("supplier_phone");
    const supplierAddressInput = document.getElementById("supplier_address");

    // Validate Supplier Name (only when input is provided)
    supplierNameInput.addEventListener("input", function () {
        let value = supplierNameInput.value.trim();
        if (value !== "") {
            // Validate characters (only letters and spaces allowed)
            let validValue = value.replace(/[^a-zA-Z\s]/g, "");
            if (value !== validValue) {
                supplierNameInput.value = validValue;
                showError(supplierNameInput, "Only letters and spaces are allowed.");
            } else {
                clearError(supplierNameInput);
            }

            // Make other fields required
            supplierEmailInput.setAttribute("required", "required");
            supplierPhoneInput.setAttribute("required", "required");
            supplierAddressInput.setAttribute("required", "required");
        } else {
            // Clear errors and remove required attributes
            clearError(supplierNameInput);
            supplierEmailInput.removeAttribute("required");
            supplierPhoneInput.removeAttribute("required");
            supplierAddressInput.removeAttribute("required");
        }
    });

    // Validate Supplier Email (only when input is provided)
    supplierEmailInput.addEventListener("input", function () {
        if (supplierEmailInput.value.trim() !== "") {
            if (!validateEmail(supplierEmailInput.value)) {
                showError(supplierEmailInput, "Invalid email format.");
            } else {
                clearError(supplierEmailInput);
            }
        } else {
            clearError(supplierEmailInput);
        }
    });

    // Validate Supplier Phone (only when input is provided)
    supplierPhoneInput.addEventListener("input", function () {
        if (supplierPhoneInput.value.trim() !== "") {
            let value = supplierPhoneInput.value.replace(/[^0-9]/g, "");
            if (value.length !== 11) {
                showError(supplierPhoneInput, "Phone number must be exactly 11 digits.");
            } else {
                clearError(supplierPhoneInput);
            }
        } else {
            clearError(supplierPhoneInput);
        }
    });

    // Helper function to validate email format
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
});

</script>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Part Details
    $name = $_POST['part_name'];
    $price = $_POST['part_price'];
    $quantity = $_POST['quantity'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year_model = $_POST['year_model'];
    $category = $_POST['category'];
    $authenticity = $_POST['authenticity'];
    $condition = $_POST['condition'];
    $item_status = $_POST['item_status'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $date_added = date('Y-m-d H:i:s');
    $last_updated = date('Y-m-d H:i:s');
    $media = '';

    // Supplier Details
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $supplier_name = trim($_POST['supplier_name']);
        $supplier_email = trim($_POST['supplier_email']);
        $supplier_phone = trim($_POST['supplier_phone']);
        $supplier_address = trim($_POST['supplier_address']);
    
        // Validate supplier fields only if any field is filled
        if (!empty($supplier_name) || !empty($supplier_email) || !empty($supplier_phone) || !empty($supplier_address)) {
            // Ensure all fields are filled
            if (empty($supplier_name) || empty($supplier_email) || empty($supplier_phone) || empty($supplier_address)) {
                die("<script>Swal.fire('Error!', 'All supplier fields must be filled if any are entered.', 'error');</script>");
            }
    
            // Validate email format
            if (!filter_var($supplier_email, FILTER_VALIDATE_EMAIL)) {
                die("<script>Swal.fire('Error!', 'Invalid email format.', 'error');</script>");
            }
    
            // Validate phone number (exactly 11 digits)
            if (!preg_match('/^\d{11}$/', $supplier_phone)) {
                die("<script>Swal.fire('Error!', 'Phone number must be exactly 11 digits.', 'error');</script>");
            }
    
            // Validate supplier name (max 100 characters)
            if (strlen($supplier_name) > 100) {
                die("<script>Swal.fire('Error!', 'Supplier name must not exceed 100 characters.', 'error');</script>");
            }
        }
    }

    // Handle Image Upload
    $upload_dir = '../partimages/'; 
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); 
    }

    if (!empty($_FILES['part_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/heic'];
        $file_type = $_FILES['part_image']['type'];
        if (in_array($file_type, $allowed_types)) {
            $file_name = basename($_FILES['part_image']['name']);
            $target_file = 'C:/xampp/htdocs/Drafter-Management-System/partimages/' . time() . "_" . $file_name; // Correct path
    
            // Move the uploaded file to the correct directory
            if (move_uploaded_file($_FILES['part_image']['tmp_name'], $target_file)) {
                $media = 'partimages/' . time() . "_" . $file_name; // Relative path for database
            } else {
                die("<script>Swal.fire('Error!', 'Failed to upload image.', 'error');</script>");
            }
        } else {
            die("<script>Swal.fire('Error!', 'Invalid file type! Only JPG, JPEG, HEIC, and PNG are allowed.', 'error');</script>");
        }
    }

    // Check if the supplier already exists
    $supplier_id = null; // Initialize supplier_id as null

    if (!empty($supplier_name) || !empty($supplier_email) || !empty($supplier_phone) || !empty($supplier_address)) {
        $supplier_sql = "SELECT SupplierID FROM supplier WHERE Email = ?";
        $supplier_stmt = $conn->prepare($supplier_sql);
        if ($supplier_stmt === false) {
            die("Error preparing supplier query: " . $conn->error);
        }
        $supplier_stmt->bind_param("s", $supplier_email);
        $supplier_stmt->execute();
        $supplier_result = $supplier_stmt->get_result();

        if ($supplier_result->num_rows > 0) {
            // Supplier exists, use the existing SupplierID
            $supplier_row = $supplier_result->fetch_assoc();
            $supplier_id = $supplier_row['SupplierID'];
        } else {
            // Supplier does not exist, insert a new supplier
            $insert_supplier_sql = "INSERT INTO supplier (CompanyName, Email, PhoneNumber, Address, TransactionDate) 
                                    VALUES (?, ?, ?, ?, ?)";
            $insert_supplier_stmt = $conn->prepare($insert_supplier_sql);
            if ($insert_supplier_stmt === false) {
                die("Error preparing supplier insert query: " . $conn->error);
            }
            $insert_supplier_stmt->bind_param("sssss", $supplier_name, $supplier_email, $supplier_phone, $supplier_address, $transaction_date);

            if ($insert_supplier_stmt->execute()) {
                $supplier_id = $conn->insert_id; // Get the new SupplierID
            } else {
                die("<script>Swal.fire('Error!', 'Failed to add supplier: " . addslashes($insert_supplier_stmt->error) . "', 'error');</script>");
            }
            $insert_supplier_stmt->close();
        }
        $supplier_stmt->close();
    }

    // Insert Part
    $part_sql = "INSERT INTO part (PartCondition, ItemStatus, Description, DateAdded, LastUpdated, Media, UserID, Location, Name, Price, Quantity, Category, Make, Model, YearModel, SupplierID)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $part_stmt = $conn->prepare($part_sql);
    if ($part_stmt === false) {
        die("Error preparing part query: " . $conn->error);
    }
    $part_stmt->bind_param("sssssssssssssssi", 
        $condition, $item_status, $description, $date_added, $last_updated, $media, $user_id, $location, 
        $name, $price, $quantity, $category, $make, $model, $year_model, $supplier_id
    );

    if ($part_stmt->execute()) {
        $partID = $conn->insert_id; 
        $timestamp = date("Y-m-d H:i:s");
        $adminId = $_SESSION['UserID'];
        $actionBy = $_SESSION['Username'];
        $actionType = "Added new Part";
    
        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID) VALUES (?, ?, ?, ?, ?)");
        if ($log === false) {
            die("Error preparing log query: " . $conn->error);
        }
    
        $log->bind_param("sssii", $actionBy, $actionType, $timestamp, $adminId, $partID);
        $log->execute();
        $log->close();
    
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<style>
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
            .swal2-popup { font-family: "Inter", sans-serif !important; }
            .swal2-title { font-weight: 700 !important; !important; }
            .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
            .swal2-confirm { font-weight: bold !important; background-color: #6c5ce7 !important; color: white !important; }
        </style>';
        
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Part added successfully!',
                icon: 'success',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#6c5ce7'
            }).then(() => {
                window.location = 'parts.php';
            });
        </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Error adding part: " . addslashes($part_stmt->error) . "',
                    icon: 'error',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#d63031'
                });
            </script>";
        }
        

    $part_stmt->close();
    $conn->close();
}
?>