<?php 
ob_start();
session_start();
require_once "dbconnect.php"; 

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
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
            <div class="form-group">
                <label for="part_name">Part Name:</label>
                <input type="text" id="part_name" name="part_name" required>
            </div>
            
            <div class="form-group">
                <label for="part_price">Part Price:</label>
                <input type="number" id="part_price" name="part_price" required>
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
                <input type="text" id="year_model" name="year_model" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="part_image">Upload Image:</label>
                <input type="file" id="part_image" name="part_image">
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
</script>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['part_name'];
    $price = $_POST['part_price'];
    $quantity = $_POST['quantity'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year_model = $_POST['year_model'];
    $description = $_POST['description'];
    $date_added = date('Y-m-d H:i:s');
    $last_updated = date('Y-m-d H:i:s');
    $media = '';

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true); 
    }
    
    if (!empty($_FILES['part_image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['part_image']['name']);
        
        if (move_uploaded_file($_FILES['part_image']['tmp_name'], $target_file)) {
            $media = $target_file;
        }
    }

    $sql = "INSERT INTO part (Name, Price, Quantity, Make, Model, YearModel, Description, DateAdded, LastUpdated, Media, UserID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $add = $conn->prepare($sql);
    $add->bind_param("sdssssssssi", $name, $price, $quantity, $make, $model, $year_model, $description, $date_added, $last_updated, $media, $user_id);

    if ($add->execute()) {
        echo "<script>alert('Part added successfully!'); window.location.href = 'parts.php';</script>";
    } else {
        echo "<script>alert('Error adding part: " . $add->error . "');</script>";
    }

    $add->close();
    $conn->close();
}
?>
