<?php
ob_start();
session_start();
require_once "dbconnect.php";

// Make sure only Admin can edit
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

// Get user info
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

// If no part ID is provided, redirect
if (!isset($_GET['id'])) {
    header("Location: /Drafter-Management-System/admin/parts.php");
    exit();
}

$part_id = $_GET['id'];

// Fetch the part (and its supplier info, if any)
$query = $conn->prepare("
    SELECT part.*,
           COALESCE(supplier.CompanyName, '')  AS CompanyName,
           COALESCE(supplier.Email, '')        AS Email,
           COALESCE(supplier.PhoneNumber, '')  AS PhoneNumber,
           COALESCE(supplier.Address, '')      AS Address
      FROM part
      LEFT JOIN supplier ON part.SupplierID = supplier.SupplierID
     WHERE part.PartID = ?
");
$query->bind_param("i", $part_id);
$query->execute();
$result = $query->get_result();
$part = $result->fetch_assoc();
$query->close();

// If part not found, redirect or show error
if (!$part) {
    die("Error: Part not found.");
}

// For the image preview below, check if there's an existing image:
$mediaPaths = [];
$mediaJson = json_decode($part['Media'] ?? '', true);
if (is_array($mediaJson)) {
    $mediaPaths = $mediaJson;
} else if (!empty($part['Media'])) {
    // If it wasn't a JSON array, treat it as a single path
    $mediaPaths[] = $part['Media'];
}

// Include navigation
include('navigation/sidebar.php');
include('navigation/topbar.php');
?>
<link rel="stylesheet" href="css/style.css">

<style>
    /* Same general container and styling from partsadd.php */
    .center-container {
        width: 80%; 
        max-width: 1200px; 
        margin: 0 auto; 
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }
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
        width: calc(100% - 20px); /* Adjusted for padding */
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
        font-weight: bold;

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
    /* Add background color and styling for quantity fields */
    .quantity-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f4f4f9;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
        padding: 8px;
        border-radius: 3px;
        border: 1px solid #ccc;
    }

    /* Style Total Quantity */
    .quantity-total {
        background-color: #DFF7E5;
        border: 1px solid #92D88B;
    }

    /* Style Quantity Left */
    .quantity-left {
        background-color:rgb(255, 236, 224);
        border: 1px solid #f77e82;
    }

    /* Style Quantity Right */
    .quantity-right {
        background-color:rgb(255, 236, 224);
        border: 1px solid #f77e82;
    }

    .image-preview {
        display: flex; 
        justify-content: center; 
        align-items: center; 
        margin-bottom: 15px;
        flex-wrap: wrap;  /* in case multiple images exist */
        gap: 10px;
    }
    .image-preview img {
        max-width: 300px; 
        height: auto; 
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    }
    /* New styles for the grid layout, same as the add form */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px; /* Space between fields */
    }
    .form-row .form-group {
        flex: 1 1 calc(33.333% - 10px); /* Typically 3 fields per row, adjustable */
        min-width: 250px; /* Minimum width for responsiveness */
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Edit Parts</h1>
    </div>
    <div class="center-container">
        <!-- Note: You’ll likely POST this form to partsedit_process.php or something similar -->
        <form action="partsedit_process.php" method="POST" enctype="multipart/form-data">
            <!-- Required hidden field for the PartID -->
            <input type="hidden" name="part_id" value="<?php echo (int)$part['PartID']; ?>">

            <!-- SINGLE .form-group for Part Name (like add form) -->
            <div class="form-group">
                <label for="part_name">Part Name:</label>
                <input type="text" id="part_name" name="part_name" 
                       value="<?php echo htmlspecialchars($part['Name']); ?>" required>
            </div>

            <!-- .form-row for Part Price, Quantity, Location (3 fields) -->
            <div class="form-row">
                <div class="form-group">
                    <label for="part_price">Part Price:</label>
                    <input type="number" placeholder="0.00" id="part_price" name="part_price" 
                           value="<?php echo htmlspecialchars($part['Price']); ?>"
                           step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" 
                           value="<?php echo htmlspecialchars($part['Location']); ?>" required>
                </div>
                </div>
                <div class="form-row">
    <!-- Total Quantity -->
    <div class="form-group">
        <label for="quantity">Total Quantity:</label>
        <div class="quantity-container quantity-total">
            <button type="button" onclick="decreaseTotalQuantity()">−</button>
            <input type="number" id="quantity" name="quantity" 
                value="<?php echo isset($part['Quantity']) ? (int)$part['Quantity'] : 0; ?>" min="0" required>
            <button type="button" onclick="increaseTotalQuantity()">+</button>
        </div>
    </div>

    <!-- Quantity Left -->
    <div class="form-group">
        <label for="quantity_left">Quantity Left:</label>
        <div class="quantity-container quantity-left">
            <button type="button" onclick="decreaseQuantityLeft()">−</button>
            <input type="number" id="quantity_left" name="quantity_left" 
                   value="<?php echo isset($part['QuantityLeft']) ? (int)$part['QuantityLeft'] : 0; ?>" min="0" required>
            <button type="button" onclick="increaseQuantityLeft()">+</button>
        </div>
    </div>
    
    <!-- Quantity Right -->
    <div class="form-group">
        <label for="quantity_right">Quantity Right:</label>
        <div class="quantity-container quantity-right">
            <button type="button" onclick="decreaseQuantityRight()">−</button>
            <input type="number" id="quantity_right" name="quantity_right" 
                   value="<?php echo isset($part['QuantityRight']) ? (int)$part['QuantityRight'] : 0; ?>" min="0" required>
            <button type="button" onclick="increaseQuantityRight()">+</button>
        </div>
    </div>
</div>


            <!-- .form-row for Make, Model, Year Model -->
            <div class="form-row">
                <div class="form-group">
                    <label for="make">Make:</label>
                    <input type="text" id="make" name="make" 
                           value="<?php echo htmlspecialchars($part['Make']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" 
                           value="<?php echo htmlspecialchars($part['Model']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="year_model">Year Model:</label>
                    <input type="text" id="year_model" name="year_model" 
                           value="<?php echo htmlspecialchars($part['YearModel']); ?>"
                           maxlength="4" pattern="\d{4}" required 
                           title="Enter a year">
                </div>
            </div>

            <!-- .form-row with 5 fields: Chassis Number, Category, Authenticity, Condition, Item Status -->
            <div class="form-row">
                <div class="form-group">
                    <label for="chassis_number">Chassis Number:</label>
                    <input type="text" id="chassis_number" name="chassis_number" maxlength="20"
                           value="<?php echo htmlspecialchars($part['ChassisNumber'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="" disabled>Select Category</option>
                        <option value="Accessories"
                          <?php echo ($part['Category'] == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                        <option value="Body Panel"
                          <?php echo ($part['Category'] == 'Body Panel') ? 'selected' : ''; ?>>Body Panel</option>
                        <option value="Brakes"
                          <?php echo ($part['Category'] == 'Brakes') ? 'selected' : ''; ?>>Brakes</option>
                        <option value="Engine & Transmission"
                          <?php echo ($part['Category'] == 'Engine & Transmission') ? 'selected' : ''; ?>>
                            Engine & Transmission</option>
                        <option value="Interior"
                          <?php echo ($part['Category'] == 'Interior') ? 'selected' : ''; ?>>Interior</option>
                        <option value="Lights"
                          <?php echo ($part['Category'] == 'Lights') ? 'selected' : ''; ?>>Lights</option>
                        <option value="Suspension"
                          <?php echo ($part['Category'] == 'Suspension') ? 'selected' : ''; ?>>Suspension</option>
                        <option value="Wheels & Tires"
                          <?php echo ($part['Category'] == 'Wheels & Tires') ? 'selected' : ''; ?>>
                            Wheels & Tires</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="authenticity">Authenticity:</label>
                    <select id="authenticity" name="authenticity" required>
                        <option value="" disabled>Select Authenticity</option>
                        <option value="Genuine"
                          <?php echo ($part['Authenticity'] == 'Genuine') ? 'selected' : ''; ?>>Genuine</option>
                        <option value="Replacement"
                          <?php echo ($part['Authenticity'] == 'Replacement') ? 'selected' : ''; ?>>Replacement</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="condition">Condition:</label>
                    <select id="condition" name="condition" required>
                        <option value="" disabled>Select Condition</option>
                        <option value="Used"
                          <?php echo ($part['PartCondition'] == 'Used') ? 'selected' : ''; ?>>Used</option>
                        <option value="New"
                          <?php echo ($part['PartCondition'] == 'New') ? 'selected' : ''; ?>>New</option>
                        <option value="For Repair"
                          <?php echo ($part['PartCondition'] == 'For Repair') ? 'selected' : ''; ?>>
                            For Repair</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="item_status">Item Status:</label>
                    <select id="item_status" name="item_status" required>
                        <option value="" disabled>Select Status</option>
                        <option value="Available"
                          <?php echo ($part['ItemStatus'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                        <option value="Used for Service"
                          <?php echo ($part['ItemStatus'] == 'Used for Service') ? 'selected' : ''; ?>>
                            Used for Service</option>
                        <option value="Surrendered"
                          <?php echo ($part['ItemStatus'] == 'Surrendered') ? 'selected' : ''; ?>>Surrendered</option>
                    </select>
                </div>
            </div>

            <!-- .form-row with 2 columns: Description, Upload Image -->
            <div class="form-row">
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?php
                        echo htmlspecialchars($part['Description'] ?? '');
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label for="part_image">Upload Image:</label>
                    <div class="image-preview" id="existingPreview">
                        <?php if (!empty($mediaPaths)): ?>
                            <?php foreach ($mediaPaths as $imagePath): 
                                $imageFull = '../' . ltrim($imagePath, '/');
                                if (file_exists($imageFull)): ?>
                                    <img src="<?php echo htmlspecialchars($imageFull); ?>" alt="Existing Image">
                                <?php else: ?>
                                    <img src="images/no-image.png" alt="No Image Available">
                                    <p style="color: #999;">(File not found: <?php echo htmlspecialchars($imageFull); ?>)</p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <img id="previewImage" src="images/no-image.png" alt="No Image Available">
                        <?php endif; ?>
                    </div>
                    <input type="file" id="part_image" name="part_image" accept="image/*"
                           onchange="previewFile(event)">
                </div>
            </div>

            <!-- Supplier Details -->
            <h2>Supplier Details</h2>
            <div class="form-group">
                <label for="supplier_name">Supplier Name:</label>
                <input type="text" id="supplier_name" name="supplier_name" 
                       value="<?php echo htmlspecialchars($part['CompanyName']); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_email">Supplier Email:</label>
                <input type="email" id="supplier_email" name="supplier_email"
                       value="<?php echo htmlspecialchars($part['Email']); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_phone">Supplier Phone Number:</label>
                <input type="text" id="supplier_phone" name="supplier_phone"
                       value="<?php echo htmlspecialchars($part['PhoneNumber']); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_address">Supplier Address:</label>
                <textarea id="supplier_address" name="supplier_address"><?php
                    echo htmlspecialchars($part['Address']);
                ?></textarea>
            </div>

            <div class="actions">
                <!-- Update button (submit) -->
                <button type="submit" class="black-button btn">Update</button>

                <!-- Clear button with SweetAlert confirmation -->
                <button type="button" class="red-button btn" id="clearButton">Clear</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
    // Initialize total quantity calculation
    updateTotalQuantity();
});

function decreaseTotalQuantity() {
    var totalQty = parseInt(document.getElementById('quantity').value) || 0;
    if (totalQty > 0) {
        document.getElementById('quantity').value = totalQty - 1;
    }
}

function increaseTotalQuantity() {
    var totalQty = parseInt(document.getElementById('quantity').value) || 0;
    document.getElementById('quantity').value = totalQty + 1;
}

// Existing functions for left and right quantities
function decreaseQuantityLeft() {
    var qtyLeft = parseInt(document.getElementById('quantity_left').value) || 0;
    if (qtyLeft > 0) {
        document.getElementById('quantity_left').value = qtyLeft - 1;
        updateTotalQuantity(); // Update total quantity if needed
    }
}

function increaseQuantityLeft() {
    var qtyLeft = parseInt(document.getElementById('quantity_left').value) || 0;
    document.getElementById('quantity_left').value = qtyLeft + 1;
    updateTotalQuantity(); // Update total quantity if needed
}

function decreaseQuantityRight() {
    var qtyRight = parseInt(document.getElementById('quantity_right').value) || 0;
    if (qtyRight > 0) {
        document.getElementById('quantity_right').value = qtyRight - 1;
        updateTotalQuantity(); // Update total quantity if needed
    }
}

function increaseQuantityRight() {
    var qtyRight = parseInt(document.getElementById('quantity_right').value) || 0;
    document.getElementById('quantity_right').value = qtyRight + 1;
    updateTotalQuantity(); // Update total quantity if needed
}

// Update total quantity function if needed
function updateTotalQuantity() {
    // This function can be used if you want to display the sum of left and right quantities somewhere else
    var quantityLeft = parseInt(document.getElementById('quantity_left').value) || 0;
    var quantityRight = parseInt(document.getElementById('quantity_right').value) || 0;
    var totalQuantity = quantityLeft + quantityRight;
    // If you want to display this somewhere, you can do it here
}

// Preview new image (like in add form)
function previewFile(event) {
    const previewContainer = document.getElementById('existingPreview');
    previewContainer.innerHTML = '';  // Clear out old images

    const fileInput = event.target;
    const file = fileInput.files[0];

    // Strictly allow only JPG, PNG (plus optionally HEIC/JPEG if you want)
    const allowedExtensions = /\.(jpg|jpeg|png|heic)$/i;

    if (file) {
        if (!allowedExtensions.test(file.name)) {
            alert("Invalid file type! JPG, JPEG, HEIC, and PNG are allowed.");
            fileInput.value = "";
            previewContainer.innerHTML = '<img src="images/no-image.png" alt="No Image Available">';
            return;
        }

        // Preview the valid image
        const reader = new FileReader();
        reader.onload = function() {
            const img = document.createElement("img");
            img.src = reader.result;
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.innerHTML = '<img src="images/no-image.png" alt="No Image Available">';
    }
}

// Clear entire form with SweetAlert confirmation
document.getElementById('clearButton').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: "Are you sure?",
        text: "This will clear all the information in the form.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, clear it!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#32CD32",
        cancelButtonColor: "#6c5ce7"
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector("form").reset();
            // Reset image preview
            document.getElementById('existingPreview').innerHTML =
                '<img src="images/no-image.png" alt="No Image Available">';
        }
    });
});

// Example validations (similar to add form)
document.addEventListener("DOMContentLoaded", function () {
    const partNameInput = document.getElementById("part_name");
    const partPriceInput = document.getElementById("part_price");
    const makeInput = document.getElementById("make");
    const modelInput = document.getElementById("model");
    const yearModelInput = document.getElementById("year_model");
    const chassisNumberInput = document.getElementById("chassis_number");
    const categoryInput = document.getElementById("category");
    const authenticityInput = document.getElementById("authenticity");
    const conditionInput = document.getElementById("condition");
    const itemStatusInput = document.getElementById("item_status");
    const locationInput = document.getElementById("location");
    const partImageInput = document.getElementById("part_image");

    // Simple checks
    function validatePartName() {
        if (partNameInput.value.trim() === "") {
            showError(partNameInput, "Part Name is required.");
            return false;
        } else {
            clearError(partNameInput);
            return true;
        }
    }
    function validatePartPrice() {
        let value = parseFloat(partPriceInput.value);
        if (isNaN(value) || value < 0) {
            showError(partPriceInput, "Price must be 0.00 or greater.");
            return false;
        } else {
            clearError(partPriceInput);
            return true;
        }
    }
    function validateMake() {
        if (makeInput.value.trim() === "") {
            showError(makeInput, "Make is required.");
            return false;
        } else {
            clearError(makeInput);
            return true;
        }
    }
    function validateModel() {
        if (modelInput.value.trim() === "") {
            showError(modelInput, "Model is required.");
            return false;
        } else {
            clearError(modelInput);
            return true;
        }
    }
    function validateYearModel() {
        if (yearModelInput.value.length !== 4) {
            showError(yearModelInput, "Year must be exactly 4 digits.");
            return false;
        } else {
            clearError(yearModelInput);
            return true;
        }
    }
    function validateChassisNumber() {
        // Make chassis number optional
        if (chassisNumberInput.value.trim() !== "" && chassisNumberInput.value.length > 20) {
            showError(chassisNumberInput, "Chassis Number must not exceed 20 characters.");
            return false;
        } else {
            clearError(chassisNumberInput);
            return true;
        }
    }
    function validateRequired(input) {
        if (input.value.trim() === "") {
            showError(input, "This field is required.");
            return false;
        } else {
            clearError(input);
            return true;
        }
    }

    // Blur events
    partNameInput.addEventListener("blur", validatePartName);
    partPriceInput.addEventListener("blur", validatePartPrice);
    makeInput.addEventListener("blur", validateMake);
    modelInput.addEventListener("blur", validateModel);
    yearModelInput.addEventListener("blur", validateYearModel);
    chassisNumberInput.addEventListener("blur", validateChassisNumber);
    categoryInput.addEventListener("blur", () => validateRequired(categoryInput));
    authenticityInput.addEventListener("blur", () => validateRequired(authenticityInput));
    conditionInput.addEventListener("blur", () => validateRequired(conditionInput));
    itemStatusInput.addEventListener("blur", () => validateRequired(itemStatusInput));
    locationInput.addEventListener("blur", () => validateRequired(locationInput));
    // Remove the required validation for image upload since we want to keep existing images
    // partImageInput.addEventListener("blur", () => validateRequired(partImageInput));

    // On submit
    const submitButton = document.querySelector("button[type='submit']");
    submitButton.addEventListener("click", function (event) {
        if (!validateAllFields()) {
            event.preventDefault();
            Swal.fire({
                title: "Error!",
                text: "Please fill out all required fields.",
                icon: "error",
                confirmButtonText: "Ok",
                confirmButtonColor: "#d63031"
            });
        }
    });

    function validateAllFields() {
        let isValid = true;
        if (!validatePartName())          isValid = false;
        if (!validatePartPrice())         isValid = false;
        if (!validateMake())              isValid = false;
        if (!validateModel())             isValid = false;
        if (!validateYearModel())         isValid = false;
        if (!validateChassisNumber())     isValid = false;
        if (!validateRequired(categoryInput))       isValid = false;
        if (!validateRequired(authenticityInput))   isValid = false;
        if (!validateRequired(conditionInput))      isValid = false;
        if (!validateRequired(itemStatusInput))     isValid = false;
        if (!validateRequired(locationInput))       isValid = false;
        // Removed image validation to allow editing without requiring a new image upload
        // if (!validateRequired(partImageInput))      isValid = false;

        return isValid;
    }
});

// Show / clear errors
function showError(input, message) {
    let errorSpan = input.nextElementSibling;
    if (!errorSpan || !errorSpan.classList.contains("error-message")) {
        errorSpan = document.createElement("span");
        errorSpan.classList.add("error-message");
        errorSpan.style.color = "red";
        errorSpan.style.fontSize = "0.9em";
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
</script>

