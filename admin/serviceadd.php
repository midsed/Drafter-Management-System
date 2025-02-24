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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
</style>

<div class="main-content">
        <div class="header">
            <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
                <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
            </a>
            <h1>Add Service</h1>
        </div>

        <form action="" method="POST">
            
            <div class="form-group">
                <label for="part">Part:</label>
                <input type="text" id="part" name="part" required>
            </div>

            <div class="form-group">
                <label for="name">Customer Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="price">Service Price:</label>
                <input type="text" id="price" name="price" required>
            </div>
            
            <button type="submit" class="btn">Add</button>
        </form>
    </div>
    <?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $part = $_POST['part'];
    $customer_name = $_POST['name'];
    $price = $_POST['price'];
    $date_added = date('Y-m-d H:i:s');


    $sql = "INSERT INTO service (Type, Date, Price, CustomerName) VALUES (?, ?, ?, ?)";
    
    $add = $conn->prepare($sql);
    if ($add === false) {
        die("Error preparing the SQL query: " . $conn->error);
    }

    $add->bind_param("ssss", $part, $date_added, $price, $customer_name);

    if ($add->execute()) {
            $timestamp = date("Y-m-d H:i:s");
             $adminId = $_SESSION['UserID'];

            $actionBy = $_SESSION['Username'];
            $actionType = "Added new Service";
            $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID) VALUES (?, ?, ?, ?)");
            $log->bind_param("sssi", $actionBy, $actionType, $timestamp, $adminId);
            $log->execute();
            $log->close();

        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Service added successfully!',
                icon: 'success',
                confirmButtonText: 'Ok'
            }).then(() => {
                window.location = 'service.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Error adding service: " . addslashes($add->error) . "',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        </script>";
    }

    $add->close();
    $conn->close();
}
?>
