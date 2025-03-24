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
    header("Location: /Drafter-Management-System//admin/parts.php");
    exit();
}

$_SESSION['UserID'] = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];
$username = $user['Username'];

if (!isset($_GET['id'])) {
    header("Location: /Drafter-Management-System/admin/parts.php");
    exit();
}

$part_id = $_GET['id'];
$query = $conn->prepare("SELECT part.*, 
                                COALESCE(supplier.CompanyName, '') AS CompanyName, 
                                COALESCE(supplier.Email, '') AS Email, 
                                COALESCE(supplier.PhoneNumber, '') AS PhoneNumber, 
                                COALESCE(supplier.Address, '') AS Address 
                         FROM part 
                         LEFT JOIN supplier ON part.SupplierID = supplier.SupplierID 
                         WHERE part.PartID = ?");
$query->bind_param("i", $part_id);
$query->execute();
$result = $query->get_result();
$part = $result->fetch_assoc();
$query->close();

if (!$part) {
    die("Error: Part not found.");
}

// Define the upload directory
$uploadDir = 'partimages/'; // Updated to 'partimages'
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<style>
    .form-group {
        margin-bottom: 15px;
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
        font-weight: bold;
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

    .gray-button {
        background-color: #6c757d; /* Gray color */
    }
    .gray-button:hover {
        background-color: #5a6268; /* Darker gray on hover */
    }

    .image-preview {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 15px;
    }
    .image-preview img {
        max-width: 300px;
        height: auto;
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
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

</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Edit Part</h1>
    </div>
    <div class="center-container">
        <form action="partsedit_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="part_id" value="<?php echo $part['PartID']; ?>">

            <!-- Part Details -->
            <div class="form-group">
                <label for="part_name">Part Name:</label>
                <input type="text" id="part_name" name="part_name" value="<?php echo htmlspecialchars($part['Name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="part_price">Part Price:</label>
                <input type="number" id="part_price" placeholder="0.00" name="part_price" value="<?php echo htmlspecialchars($part['Price']); ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <div class="quantity-container">
                    <button type="button" onclick="decreaseQuantity()">−</button>
                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($part['Quantity'] ?? 0); ?>" min="0" required>
                    <button type="button" onclick="increaseQuantity()">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="make">Make:</label>
                <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($part['Make']); ?>" required>
            </div>

            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($part['Model']); ?>" required>
            </div>

            <div class="form-group">
                <label for="year_model">Year Model:</label>
                <input type="text" id="year_model" name="year_model" value="<?php echo htmlspecialchars($part['YearModel']); ?>" pattern="^\d{4}$" title="Year Model must be a 4-digit number (e.g., 2024)" maxlength="4" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="" selected disabled>Select Category</option>
                    <option value="Accessories" <?php echo ($part['Category'] == 'Accessories' ? 'selected' : ''); ?>>Accessories</option>
                    <option value="Body Panel" <?php echo ($part['Category'] == 'Body Panel' ? 'selected' : ''); ?>>Body Panel</option>
                    <option value="Brakes" <?php echo ($part['Category'] == 'Brakes' ? 'selected' : ''); ?>>Brakes</option>
                    <option value="Engine & Transmission" <?php echo ($part['Category'] == 'Engine & Transmission' ? 'selected' : ''); ?>>Engine & Transmission</option>
                    <option value="Interior" <?php echo ($part['Category'] == 'Interior' ? 'selected' : ''); ?>>Interior</option>
                    <option value="Lights" <?php echo ($part['Category'] == 'Lights' ? 'selected' : ''); ?>>Lights</option>
                    <option value="Suspension" <?php echo ($part['Category'] == 'Suspension' ? 'selected' : ''); ?>>Suspension</option>
                    <option value="Wheels & Tires" <?php echo ($part['Category'] == 'Wheels & Tires' ? 'selected' : ''); ?>>Wheels & Tires</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="authenticity">Authenticity:</label>
                <select id="authenticity" name="authenticity" required>
                    <option value="Genuine" <?php echo ($part['Authenticity'] == 'Genuine' ? 'selected' : ''); ?>>Genuine</option>
                    <option value="Replacement" <?php echo ($part['Authenticity'] == 'Replacement' ? 'selected' : ''); ?>>Replacement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="part_condition">Condition:</label>
                <select id="part_condition" name="part_condition" required>
                    <option value="Used" <?php echo ($part['PartCondition'] ?? '') == 'Used' ? 'selected' : ''; ?>>Used</option>
                    <option value="New" <?php echo ($part['PartCondition'] ?? '') == 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="For Repair" <?php echo ($part['PartCondition'] ?? '') == 'For Repair' ? 'selected' : ''; ?>>For Repair</option>
                </select>
            </div>

            <div class="form-group">
                <label for="item_status">Item Status:</label>
                <select id="item_status" name="item_status" required>
                    <option value="Available" <?php echo ($part['ItemStatus'] == 'Available' ? 'selected' : ''); ?>>Available</option>
                    <option value="Used for Service" <?php echo ($part['ItemStatus'] == 'Used for Service' ? 'selected' : ''); ?>>Used for Service</option>
                    <option value="Surrendered" <?php echo ($part['ItemStatus'] == 'Surrendered' ? 'selected' : ''); ?>>Surrendered</option>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($part['Location']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($part['Description']); ?></textarea>
            </div>
    
            <div class="form-group">
                <label for="part_image">Current Image(s):</label>
                <div class="image-preview">
                    <?php
                    // Check if Media is a JSON array or a single path
                    $media = json_decode($part['Media'], true);
                    if (is_array($media) && count($media) > 0): 
                        foreach ($media as $image): 
                            $filePath = "../" . $image; // Navigate out of the admin folder
                            if (file_exists($filePath)): ?>
                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Part Image">
                            <?php else: ?>
                                <p class="no-image">Image not found: <?php echo htmlspecialchars($filePath); ?></p>
                            <?php endif; 
                        endforeach;
                    elseif (!empty($part['Media'])): 
                        $filePath = "../" . $part['Media']; // Navigate out of the admin folder
                        if (file_exists($filePath)): ?>
                            <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Part Image">
                        <?php else: ?>
                            <p class="no-image">Image not found: <?php echo htmlspecialchars($filePath); ?></p>
                        <?php endif; 
                    else: ?>
                        <p class="no-image">No image available</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="part_image">Upload New Image:</label>
                <input type="file" id="part_image" name="part_image" accept="image/*" onchange="previewFile(event)">
            </div>

            <!-- Supplier Details -->
            <h2>Supplier Details</h2>
            <div class="form-group">
                <label for="supplier_name">Supplier Name:</label>
                <input type="text" id="supplier_name" name="supplier_name" value="<?php echo htmlspecialchars($part['CompanyName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_email">Supplier Email:</label>
                <input type="email" id="supplier_email" name="supplier_email" value="<?php echo htmlspecialchars($part['Email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_phone">Supplier Phone Number:</label>
                <input type="text" id="supplier_phone" name="supplier_phone" maxlength="11" value="09" value="<?php echo htmlspecialchars($part['PhoneNumber'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_address">Supplier Address:</label>
                <textarea id="supplier_address" name="supplier_address"><?php echo htmlspecialchars($part['Address'] ?? ''); ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="black-button btn">Update</button>
                <button type="button" class="red-button btn" onclick="clearForm();">Clear</button>
                <button type="button" class="btn gray-button" onclick="cancelEdit();">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>

    document.addEventListener("DOMContentLoaded", function () {
        const partNameInput = document.getElementById("part_name");
        const partPriceInput = document.getElementById("part_price");
        const makeInput = document.getElementById("make");
        const modelInput = document.getElementById("model");
        const yearModelInput = document.getElementById("year_model");
        const categoryInput = document.getElementById("category");
        const authenticityInput = document.getElementById("authenticity");
        const conditionInput = document.getElementById("part_condition");
        const itemStatusInput = document.getElementById("item_status");
        const locationInput = document.getElementById("location");
        const partImageInput = document.getElementById("part_image");

        // Validate Part Name
        function validatePartName() {
            if (partNameInput.value.trim() === "") {
                showError(partNameInput, "Please fill out this field.");
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

        // Validate Make (Required)
        function validateMake() {
            if (makeInput.value.trim() === "") {
                showError(makeInput, "Please fill out this field.");
                return false;
            } else {
                clearError(makeInput);
                return true;
            }
        }

        // Validate Model (Required)
        function validateModel() {
            if (modelInput.value.trim() === "") {
                showError(modelInput, "Please fill out this field.");
                return false;
            } else {
                clearError(modelInput);
                return true;
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
                showError(input, "Please fill out this field.");
                return false;
            } else {
                clearError(input);
                return true;
            }
        }

        // Event Listeners for Blur Events
        partNameInput.addEventListener("blur", validatePartName);
        partPriceInput.addEventListener("blur", validatePartPrice);
        makeInput.addEventListener("blur", validateMake);
        modelInput.addEventListener("blur", validateModel);
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
            let isValid = validateAllFields();

            if (!isValid) {
                event.preventDefault(); // Prevent form submission
                Swal.fire({
                    title: "Error!",
                    text: "Please fill out all required fields.",
                    icon: "error",
                    confirmButtonText: "Ok",
                    confirmButtonColor: "#d63031"
                });
            }
        });
    });

    function validateAllFields() {
        let isValid = true;

        if (!validatePartName()) isValid = false;
        if (!validatePartPrice()) isValid = false;
        if (!validateMake()) isValid = false;
        if (!validateModel()) isValid = false;
        if (!validateYearModel()) isValid = false;
        if (!validateRequired(categoryInput)) isValid = false;
        if (!validateRequired(authenticityInput)) isValid = false;
        if (!validateRequired(conditionInput)) isValid = false;
        if (!validateRequired(itemStatusInput)) isValid = false;
        if (!validateRequired(locationInput)) isValid = false;
        if (!validateRequired(partImageInput)) isValid = false;

        return isValid;
    }

    function increaseQuantity() {
        let quantity = document.getElementById('quantity');
        quantity.value = parseInt(quantity.value) + 1;
    }

    function decreaseQuantity() {
        let quantity = document.getElementById('quantity');
        if (quantity.value > 0) {
            quantity.value = parseInt(quantity.value) - 1;
        }
    }
    

    function clearForm() {
        document.querySelectorAll("input, select, textarea").forEach(element => {
            if (element.type === "file") {
                element.value = ""; // Clear file input
            } else if (element.tagName === "SELECT") {
                element.selectedIndex = 0; // Reset select to first option
            } else {
                element.value = ""; // Clear all other fields
            }
        });
    }

    function previewFile(event) {
        const previewContainer = document.querySelector('.image-preview');
        previewContainer.innerHTML = "";
        
        const fileInput = event.target;
        const files = fileInput.files;
        
        if (files.length > 0) {
            for (const file of files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = "New Image Preview";
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        } else {
            previewContainer.innerHTML = "<p class='no-image'>No image available</p>";
        }
    }

    function cancelEdit() {
        // Redirect to the previous page or a specific page
        window.location.href = "partslist.php"; // Replace with your desired URL
    }

    function increaseQuantity() {
        let quantity = document.getElementById('quantity');
        quantity.value = parseInt(quantity.value) + 1;
    }

    function decreaseQuantity() {
        let quantity = document.getElementById('quantity');
        if (quantity.value > 0) {
            quantity.value = parseInt(quantity.value) - 1;
        }
    }

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

    // Apply the saved state on page load
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
    }
}

// Check the sidebar state when the page loads
document.addEventListener("DOMContentLoaded", function () {
    checkSidebarState();
});f

    function clearForm() {
        document.querySelectorAll("input, select, textarea").forEach(element => {
            if (element.type === "file") {
                element.value = ""; // Clear file input
            } else if (element.tagName === "SELECT") {
                element.selectedIndex = 0; // Reset select to first option
            } else {
                element.value = ""; // Clear all other fields
            }
        });
    }

    function previewFile(event) {
        const previewContainer = document.querySelector('.image-preview');
        previewContainer.innerHTML = "";
        
        const fileInput = event.target;
        const files = fileInput.files;
        
        if (files.length > 0) {
            for (const file of files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = "New Image Preview";
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        } else {
            previewContainer.innerHTML = "<p class='no-image'>No image available</p>";
        }
    }

    function cancelEdit() {
        window.history.back(); // Go back to the previous page
    }

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
    document.addEventListener("DOMContentLoaded", function () {
    const phoneInput = document.getElementById("supplier_phone");

    // Always ensure it starts with 09
    phoneInput.addEventListener("input", function () {
        // Remove non-digits and enforce max length
        let digits = this.value.replace(/[^0-9]/g, '').slice(0, 11);

        // Enforce starting with "09"
        if (!digits.startsWith("09")) {
            digits = "09" + digits.slice(2);
        }

        this.value = digits;
    });

    // Prevent backspacing or deleting the "09"
    phoneInput.addEventListener("keydown", function (e) {
        const caretPos = this.selectionStart;

        if ((e.key === "Backspace" || e.key === "Delete") && caretPos <= 2) {
            e.preventDefault();
        }
    });
});
</script>