<?php
ob_start();
session_start();
require_once "dbconnect.php"; 

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
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

if (!isset($_GET['id'])) {
    die("Error: No part ID provided.");
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
                    <input type="number" id="quantity" name="quantity" value="0" min="0" required>
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
                    <option value="Engine" <?php echo ($part['Category'] == 'Engine' ? 'selected' : ''); ?>>Engine</option>
                    <option value="Suspension" <?php echo ($part['Category'] == 'Suspension' ? 'selected' : ''); ?>>Suspension</option>
                    <option value="Body Panel" <?php echo ($part['Category'] == 'Body Panel' ? 'selected' : ''); ?>>Body Panel</option>
                    <option value="Interior" <?php echo ($part['Category'] == 'Interior' ? 'selected' : ''); ?>>Interior</option>
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
                <input type="text" id="supplier_phone" name="supplier_phone" value="<?php echo htmlspecialchars($part['PhoneNumber'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="supplier_address">Supplier Address:</label>
                <textarea id="supplier_address" name="supplier_address"><?php echo htmlspecialchars($part['Address'] ?? ''); ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="black-button btn">Update</button>
                <button type="button" class="red-button btn" onclick="clearForm();">Clear</button>
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
        if (quantity.value > 0) {
            quantity.value = parseInt(quantity.value) - 1;
        }
    }
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
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
</script>