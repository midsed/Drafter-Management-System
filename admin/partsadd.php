<?php 
ob_start();
session_start();
require_once "dbconnect.php"; 

if (!isset($_SESSION['UserID'])) {
    die("Access Denied: User not logged in. Please log in again.");
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
</style>

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
        <h1>Add Parts</h1>
    </div>

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
            <label for="authenticity">Authenticity:</label>
            <select id="authenticity" name="authenticity">
                <option value="Genuine">Genuine</option>
                <option value="Replacement">Replacement</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category">
                <option value="Engine Suspension">Engine Suspension</option>
                <option value="Body Panel">Body Panel</option>
                <option value="Interior">Interior</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="condition">Condition:</label>
            <select id="condition" name="condition">
                <option value="New">New</option>
                <option value="Used">Used</option>
                <option value="For Repair">For Repair</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="status">Item Status:</label>
            <select id="status" name="status">
                <option value="Available">Available</option>
                <option value="Used for Service">Used for Service</option>
                <option value="Surrendered">Surrendered</option>
            </select>
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

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['part_name'];
    $price = $_POST['part_price'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year_model = $_POST['year_model'];
    $description = $_POST['description'];
    $authenticity = $_POST['authenticity'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];
    $status = $_POST['status'];
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
        } else {
            die("Error: File upload failed!");
        }
    }

    $sql = "INSERT INTO part (Name, Price, Make, Model, YearModel, Description, Authenticity, Category, PartCondition, ItemStatus, DateAdded, LastUpdated, Media, UserID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $add = $conn->prepare($sql);
    $add->bind_param("sdssissssssssi", $name, $price, $make, $model, $year_model, $description, $authenticity, $category, $condition, $status, $date_added, $last_updated, $media, $user_id);
    
    
    if ($add->execute()) {
        $part_id = $conn->insert_id;
        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID, RoleType) VALUES (?, ?, ?, ?, ?, ?)");
        $action_type = "Added new part: " . $name;
        $timestamp = date('Y-m-d H:i:s');
        $role_type = $user['RoleType'];
        $log->bind_param("sssiss", $username, $action_type, $timestamp, $user_id, $part_id, $role_type);
        $log->execute();
        $log->close();
        
        echo "<script>alert('Part added successfully!'); window.location.href = 'parts.php';</script>";
    } else {
        echo "<script>alert('Error adding part: " . $add->error . "');</script>";
    }

    $add->close();
    $conn->close();
}
?>


