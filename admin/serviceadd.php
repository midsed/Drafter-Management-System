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
            <label for="fName">Customer First Name:</label>
            <input type="text" id="fName" name="fName" required>
        </div>

        <div class="form-group">
            <label for="lName">Customer Last Name:</label>
            <input type="text" id="lName" name="lName" required>
        </div>

        <div class="form-group">
            <label for="cEmail">Customer Email:</label>
            <input type="email" id="cEmail" name="cEmail" required>
        </div>

        <div class="form-group">
            <label for="pNumber">Customer Phone Number:</label>
            <input type="number" id="pNumber" name="pNumber" required maxlength="11">
        </div>

        <div class="form-group">
            <label for="type">Type:</label>
            <input type="text" id="type" name="type" required>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" required>
        </div>
        
        <button type="submit" class="btn">Add</button>
    </form>
</div>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type']; 
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $pNumber = $_POST['pNumber'];
    $cEmail = $_POST['cEmail'];
    $price = $_POST['price'];
    $date_added = date('Y-m-d H:i:s');

    // Check if client exists
    $checkClient = $conn->prepare("SELECT ClientEmail FROM client WHERE ClientEmail = ?");
    $checkClient->bind_param("s", $cEmail);
    $checkClient->execute();
    $checkClient->store_result();

    if ($checkClient->num_rows == 0) {
        $insertClient = "INSERT INTO client (ClientEmail, FName, LName, PhoneNumber) VALUES (?, ?, ?, ?)";
        $addClient = $conn->prepare($insertClient);
        if ($addClient === false) {
            die("Error preparing client insert query: " . $conn->error);
        }
        $addClient->bind_param("ssss", $cEmail, $fName, $lName, $pNumber);
        if (!$addClient->execute()) {
            die("Error inserting client: " . $addClient->error);
        }
        $addClient->close();
    }
    $checkClient->close();

    $partCheck = $conn->prepare("SELECT PartID FROM part WHERE Description = ? AND PartCondition = 'Used' LIMIT 1");
    $partCheck->bind_param("s", $type);
    $partCheck->execute();
    $partResult = $partCheck->get_result();
    $part = $partResult->fetch_assoc();
    $partCheck->close();

    $partID = $part ? $part['PartID'] : NULL;

    $sql = "INSERT INTO service (Type, Date, Price, ClientEmail, PartID, StaffName) VALUES (?, ?, ?, ?, ?, ?)";
    $add = $conn->prepare($sql);

    if ($add === false) {
        die("Error preparing service insert query: " . $conn->error);
    }

    $add->bind_param("ssssss", $type, $date_added, $price, $cEmail, $partID, $username);

    if ($add->execute()) {
        $serviceID = $add->insert_id;

        if ($partID) {
            $updatePart = $conn->prepare("UPDATE part SET ServiceID = ? WHERE PartID = ?");
            $updatePart->bind_param("ii", $serviceID, $partID);
            $updatePart->execute();
            $updatePart->close();
        }

        $timestamp = date("Y-m-d H:i:s");
        $adminId = $_SESSION['UserID'];
        $actionBy = $_SESSION['Username'];
        $actionType = "Added new Service";

        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID) VALUES (?, ?, ?, ?, ?)");
        $log->bind_param("sssii", $actionBy, $actionType, $timestamp, $adminId, $partID);
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