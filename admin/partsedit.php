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

$_SESSION['UserID']   = $user['UserID'];
$_SESSION['RoleType'] = $user['RoleType'];
$_SESSION['Username'] = $user['Username'];

if (!isset($_GET['id'])) {
    header("Location: /Drafter-Management-System/admin/parts.php");
    exit();
}

$part_id = $_GET['id'];
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

if (!$part) {
    die("Error: Part not found.");
}

// Define the upload directory
$uploadDir = 'partimages/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<style>
.center-container {
    width: 80%;
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 15px;
    flex: 1; /* This ensures items share space in a row */
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

/* If you want a full-width field, just wrap it alone in .form-row */
.full-width {
    flex: 100% !important;
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

.black-button   { background-color: #272727; }
.black-button:hover { background-color: #444; }
.red-button     { background-color: red; }
.red-button:hover   { background-color: darkred; }
.gray-button    { background-color: #6c757d; }
.gray-button:hover  { background-color: #5a6268; }

.actions {
    margin-top: 20px;
    display: flex;
    gap: 15px;
    justify-content: center;
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

/* Error message styling */
.error-message {
    color: red;
    font-size: 0.9em;
}
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png"
                 alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Edit Part</h1>
    </div>

    <div class="center-container">
        <form action="partsedit_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="part_id" value="<?php echo $part['PartID']; ?>">

            <!-- PART NAME: alone at top (full width) -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="part_name">Part Name:</label>
                    <input type="text" id="part_name" name="part_name"
                           value="<?php echo htmlspecialchars($part['Name']); ?>" required>
                </div>
            </div>

            <!-- PART PRICE & QUANTITY -->
            <div class="form-row">
                <div class="form-group">
                    <label for="part_price">Part Price:</label>
                    <input type="number" id="part_price" placeholder="0.00" name="part_price"
                           value="<?php echo htmlspecialchars($part['Price']); ?>" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <div class="quantity-container">
                        <button type="button" onclick="decreaseQuantity()">−</button>
                        <input type="number" id="quantity" name="quantity"
                               value="<?php echo htmlspecialchars($part['Quantity'] ?? 0); ?>" min="0" required>
                        <button type="button" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
            </div>

            <!-- MAKE & MODEL -->
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
            </div>

            <!-- YEAR MODEL & CATEGORY -->
            <div class="form-row">
                <div class="form-group">
                    <label for="year_model">Year Model:</label>
                    <input type="text" id="year_model" name="year_model"
                           value="<?php echo htmlspecialchars($part['YearModel']); ?>"
                           pattern="^\d{4}$"
                           title="Year Model must be 4 digits (e.g. 2024)"
                           maxlength="4" required>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="" disabled>Select Category</option>
                        <option value="Accessories"
                            <?php echo ($part['Category'] === 'Accessories') ? 'selected' : ''; ?>>
                            Accessories
                        </option>
                        <option value="Body Panel"
                            <?php echo ($part['Category'] === 'Body Panel') ? 'selected' : ''; ?>>
                            Body Panel
                        </option>
                        <option value="Brakes"
                            <?php echo ($part['Category'] === 'Brakes') ? 'selected' : ''; ?>>
                            Brakes
                        </option>
                        <option value="Engine & Transmission"
                            <?php echo ($part['Category'] === 'Engine & Transmission') ? 'selected' : ''; ?>>
                            Engine & Transmission
                        </option>
                        <option value="Interior"
                            <?php echo ($part['Category'] === 'Interior') ? 'selected' : ''; ?>>
                            Interior
                        </option>
                        <option value="Lights"
                            <?php echo ($part['Category'] === 'Lights') ? 'selected' : ''; ?>>
                            Lights
                        </option>
                        <option value="Suspension"
                            <?php echo ($part['Category'] === 'Suspension') ? 'selected' : ''; ?>>
                            Suspension
                        </option>
                        <option value="Wheels & Tires"
                            <?php echo ($part['Category'] === 'Wheels & Tires') ? 'selected' : ''; ?>>
                            Wheels & Tires
                        </option>
                    </select>
                </div>
            </div>

            <!-- AUTHENTICITY & CONDITION -->
            <div class="form-row">
                <div class="form-group">
                    <label for="authenticity">Authenticity:</label>
                    <select id="authenticity" name="authenticity" required>
                        <option value="Genuine"
                            <?php echo ($part['Authenticity'] === 'Genuine') ? 'selected' : ''; ?>>
                            Genuine
                        </option>
                        <option value="Replacement"
                            <?php echo ($part['Authenticity'] === 'Replacement') ? 'selected' : ''; ?>>
                            Replacement
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="part_condition">Condition:</label>
                    <select id="part_condition" name="part_condition" required>
                        <option value="Used"
                            <?php echo ($part['PartCondition'] ?? '') === 'Used' ? 'selected' : ''; ?>>
                            Used
                        </option>
                        <option value="New"
                            <?php echo ($part['PartCondition'] ?? '') === 'New' ? 'selected' : ''; ?>>
                            New
                        </option>
                        <option value="For Repair"
                            <?php echo ($part['PartCondition'] ?? '') === 'For Repair' ? 'selected' : ''; ?>>
                            For Repair
                        </option>
                    </select>
                </div>
            </div>

            <!-- ITEM STATUS & LOCATION -->
            <div class="form-row">
                <div class="form-group">
                    <label for="item_status">Item Status:</label>
                    <select id="item_status" name="item_status" required>
                        <option value="Available"
                            <?php echo ($part['ItemStatus'] === 'Available') ? 'selected' : ''; ?>>
                            Available
                        </option>
                        <option value="Used for Service"
                            <?php echo ($part['ItemStatus'] === 'Used for Service') ? 'selected' : ''; ?>>
                            Used for Service
                        </option>
                        <option value="Surrendered"
                            <?php echo ($part['ItemStatus'] === 'Surrendered') ? 'selected' : ''; ?>>
                            Surrendered
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location"
                           value="<?php echo htmlspecialchars($part['Location']); ?>" required>
                </div>
            </div>

            <!-- DESCRIPTION: alone (full width) -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?php
                        echo htmlspecialchars($part['Description']);
                    ?></textarea>
                </div>
            </div>

            <!-- CURRENT & NEW IMAGE(S) -->
            <div class="form-row">
                <div class="form-group">
                    <label>Current Image(s):</label>
                    <div class="image-preview">
                        <?php
                        $media = json_decode($part['Media'], true);
                        if (is_array($media) && count($media) > 0):
                            foreach ($media as $image):
                                $filePath = "../" . $image; 
                                if (file_exists($filePath)): ?>
                                    <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Part Image">
                                <?php else: ?>
                                    <p class="no-image">
                                      Image not found: <?php echo htmlspecialchars($filePath); ?>
                                    </p>
                                <?php endif;
                            endforeach;
                        elseif (!empty($part['Media'])):
                            $filePath = "../" . $part['Media'];
                            if (file_exists($filePath)): ?>
                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Part Image">
                            <?php else: ?>
                                <p class="no-image">
                                  Image not found: <?php echo htmlspecialchars($filePath); ?>
                                </p>
                            <?php endif;
                        else: ?>
                            <p class="no-image">No image available</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="part_image">Upload New Image:</label>
                    <input type="file" id="part_image" name="part_image" accept="image/*"
                           onchange="previewFile(event)">
                </div>
            </div>

            <!-- SUPPLIER DETAILS -->
            <h2>Supplier Details</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="supplier_name">Supplier Name:</label>
                    <input type="text" id="supplier_name" name="supplier_name"
                           value="<?php echo htmlspecialchars($part['CompanyName'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="supplier_email">Supplier Email:</label>
                    <input type="email" id="supplier_email" name="supplier_email"
                           value="<?php echo htmlspecialchars($part['Email'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="supplier_phone">Supplier Phone:</label>
                    <input type="text" id="supplier_phone" name="supplier_phone"
                           value="<?php echo htmlspecialchars($part['PhoneNumber'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="supplier_address">Supplier Address:</label>
                    <textarea id="supplier_address" name="supplier_address"><?php
                        echo htmlspecialchars($part['Address'] ?? '');
                    ?></textarea>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
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
    checkSidebarState();

    // Basic validation references
    const partNameInput  = document.getElementById("part_name");
    const partPriceInput = document.getElementById("part_price");
    const makeInput      = document.getElementById("make");
    const modelInput     = document.getElementById("model");
    const yearModelInput = document.getElementById("year_model");
    const categoryInput  = document.getElementById("category");
    const authInput      = document.getElementById("authenticity");
    const condInput      = document.getElementById("part_condition");
    const statusInput    = document.getElementById("item_status");
    const locationInput  = document.getElementById("location");
    const partImageInput = document.getElementById("part_image");

    // Example of blur events...
    partNameInput.addEventListener("blur", validatePartName);
    partPriceInput.addEventListener("blur", validatePartPrice);
    makeInput.addEventListener("blur", validateMake);
    modelInput.addEventListener("blur", validateModel);
    yearModelInput.addEventListener("blur", validateYearModel);
    categoryInput.addEventListener("blur", () => validateRequired(categoryInput));
    authInput.addEventListener("blur", () => validateRequired(authInput));
    condInput.addEventListener("blur", () => validateRequired(condInput));
    statusInput.addEventListener("blur", () => validateRequired(statusInput));
    locationInput.addEventListener("blur", () => validateRequired(locationInput));
    partImageInput.addEventListener("blur", () => validateRequired(partImageInput));

    document.querySelector("button[type='submit']").addEventListener("click", function(e) {
        if (!validateAllFields()) {
            e.preventDefault();
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

// Example combined validator
function validateAllFields() {
    let isValid = true;
    if (!validatePartName()) isValid = false;
    if (!validatePartPrice()) isValid = false;
    if (!validateMake())  isValid = false;
    if (!validateModel()) isValid = false;
    if (!validateYearModel()) isValid = false;

    if (!validateRequired(document.getElementById("category")))        isValid = false;
    if (!validateRequired(document.getElementById("authenticity")))    isValid = false;
    if (!validateRequired(document.getElementById("part_condition")))  isValid = false;
    if (!validateRequired(document.getElementById("item_status")))     isValid = false;
    if (!validateRequired(document.getElementById("location")))        isValid = false;
    // If you do NOT require new images every time, remove this:
    if (!validateRequired(document.getElementById("part_image")))      isValid = false;

    return isValid;
}

// Your typical field checks:
function validatePartName() {
    const input = document.getElementById("part_name");
    if (input.value.trim() === "") {
        showError(input, "Please fill out this field.");
        return false;
    } else {
        clearError(input);
        return true;
    }
}
function validatePartPrice() {
    const input = document.getElementById("part_price");
    let value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) {
        showError(input, "Price must be greater than 0.00.");
        input.value = '0.00';
        return false;
    } else {
        clearError(input);
        return true;
    }
}
function validateMake() {
    const input = document.getElementById("make");
    if (input.value.trim() === "") {
        showError(input, "Please fill out this field.");
        return false;
    } else {
        clearError(input);
        return true;
    }
}
function validateModel() {
    const input = document.getElementById("model");
    if (input.value.trim() === "") {
        showError(input, "Please fill out this field.");
        return false;
    } else {
        clearError(input);
        return true;
    }
}
function validateYearModel() {
    const input = document.getElementById("year_model");
    if (input.value.length !== 4) {
        showError(input, "Year must be exactly 4 digits.");
        return false;
    } else {
        clearError(input);
        return true;
    }
}
function validateRequired(input) {
    if (input.value.trim() === "") {
        showError(input, "Please fill out this field.");
        return false;
    } else {
        clearError(input);
        return true;
    }
}

// Increase/Decrease quantity
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

// Preview uploaded image
function previewFile(event) {
    const previewContainer = document.querySelector('.image-preview');
    previewContainer.innerHTML = "";
    const files = event.target.files;
    if (files.length > 0) {
        for (const file of files) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    } else {
        previewContainer.innerHTML = "<p class='no-image'>No image available</p>";
    }
}

// Clear entire form
function clearForm() {
    document.querySelectorAll("input, select, textarea").forEach(el => {
        if (el.type === "file") {
            el.value = "";
        } else if (el.tagName === "SELECT") {
            el.selectedIndex = 0;
        } else {
            el.value = "";
        }
    });
}

// Cancel editing
function cancelEdit() {
    window.location.href = "partslist.php";
}

// Show/hide validation error
function showError(input, message) {
    let errorSpan = input.nextElementSibling;
    if (!errorSpan || !errorSpan.classList.contains("error-message")) {
        errorSpan = document.createElement("span");
        errorSpan.classList.add("error-message");
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

// Sidebar collapse stuff
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
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
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
