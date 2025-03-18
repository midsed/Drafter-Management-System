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

$partQuery = $conn->query("SELECT PartID, Name FROM part WHERE ItemStatus = 'Used for Service' AND ServiceID IS NULL");
$parts = $partQuery->fetch_all(MYSQLI_ASSOC);
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>

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
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
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
    
    textarea {
        resize: vertical;
        height: 100px;
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
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add Service</h1>
    </div>

    <div class="center-container">
        <form action="" method="POST">
            <div class="form-group">
                <label for="part">Select Part:</label>
                <select id="part" name="partID" required>
                    <option value="">-- Select a Part --</option>
                    <?php foreach ($parts as $part) { ?>
                        <option value="<?php echo $part['PartID']; ?>"> 
                            <?php echo htmlspecialchars($part['Name']); ?> 
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fName">Customer First Name:</label>
                <input type="text" id="fName" name="fName" required maxlength="40" 
                pattern="^[A-Za-z\s]+$" title="Invalid name format.">
            </div>
            
            <div class="form-group">
                <label for="lName">Customer Last Name:</label>
                <input type="text" id="lName" name="lName" required maxlength="40" 
                pattern="^[A-Za-z\s]+$" title="Invalid name format.">
            </div>
            
            <div class="form-group">
                <label for="cEmail">Customer Email:</label>
                <input type="email" id="cEmail" name="cEmail" required>
            </div>
            
            <div class="form-group">
                <label for="pNumber">Customer Phone Number:</label>
                <input type="number" id="pNumber" name="pNumber" required maxlength="11" pattern="\d{11}"
                title="Invalid phone number.">
            </div>
            
            <div class="form-group">
                <label for="type">Service Type:</label>
                <input type="text" id="type" name="type" required pattern="^[A-Za-z\s]+$" title="Invalid  format.">
            </div>
            
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" placeholder="0.00"  required>
            </div>
            
            <div class="actions">
                <button type="submit" class="black-button btn">Add</button>
                <button type="reset" class="red-button btn" onclick="resetForm()">Reset</button>
            </div>
        </form>
    </div>
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
    $partID = $_POST['partID'];

    $checkClient = $conn->prepare("SELECT ClientEmail FROM client WHERE ClientEmail = ?");
    $checkClient->bind_param("s", $cEmail);
    $checkClient->execute();
    $checkClient->store_result();

    if ($checkClient->num_rows == 0) {
        $insertClient = "INSERT INTO client (ClientEmail, FName, LName, PhoneNumber) VALUES (?, ?, ?, ?)";
        $addClient = $conn->prepare($insertClient);
        $addClient->bind_param("ssss", $cEmail, $fName, $lName, $pNumber);
        $addClient->execute();
        $addClient->close();
    }
    $checkClient->close();

    $sql = "INSERT INTO service (Type, Date, Price, ClientEmail, PartID, StaffName) VALUES (?, ?, ?, ?, ?, ?)";
    $add = $conn->prepare($sql);
    $add->bind_param("ssssss", $type, $date_added, $price, $cEmail, $partID, $username);

    if ($add->execute()) {
        $serviceID = $add->insert_id;

        $updatePart = $conn->prepare("UPDATE part 
                                      SET ServiceID = ? 
                                      WHERE PartID = ? 
                                      AND Name = (SELECT Name FROM part WHERE PartID = ?)");
        $updatePart->bind_param("iii", $serviceID, $partID, $partID);
        $updatePart->execute();
        $updatePart->close();

        $timestamp = date("Y-m-d H:i:s");
        $adminId = $_SESSION['UserID'];
        $actionBy = $_SESSION['Username'];
        $actionType = "Added new Service";

        $log = $conn->prepare("INSERT INTO logs (ActionBy, ActionType, Timestamp, UserID, PartID) 
                               VALUES (?, ?, ?, ?, ?)");
        $log->bind_param("sssii", $actionBy, $actionType, $timestamp, $adminId, $partID);
        $log->execute();
        $log->close();

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<style>
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
            .swal2-popup { font-family: "Inter", sans-serif !important; }
            .swal2-title { font-weight: 700 !important; }
            .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
            .swal2-confirm { font-weight: bold !important; background-color: #6c5ce7 !important; color: white !important; }
        </style>';
        
        echo '<script>
            Swal.fire({
                title: "Success!",
                text: "Service added successfully!",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#6c5ce7"
            }).then(() => {
                window.location = "service.php";
            });
        </script>';
        } else {
            echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "Error adding service",
                    icon: "error",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#d63031"
                });
            </script>';
    }

    $add->close();
    $conn->close();
}
?>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    document.addEventListener("DOMContentLoaded", function () {
    function validateEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email!',
                text: 'Please enter a valid email address.',
                confirmButtonColor: '#d63031'
            });
            input.value = "";
        }
    }

    document.getElementById("cEmail").addEventListener("blur", function () {
        validateEmail(this);
    });

    document.querySelector("form").addEventListener("submit", function (event) {
        const email = document.getElementById("cEmail").value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email Format!',
                text: 'Please enter a valid email address before submitting.',
                confirmButtonColor: '#d63031'
            });
            event.preventDefault();
        }
    });
});

function resetForm() {
    Swal.fire({
        title: "Are you sure?",
        text: "This will reset all informations.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, reset it!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#d63031",
        cancelButtonColor: "#6c757d"
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector("form").reset();
            document.querySelectorAll("input").forEach(input => input.value = "");
            Swal.fire({
                title: "Reset!",
                text: "The form has been reset.",
                icon: "success",
                confirmButtonColor: "#6c5ce7"
            });
        }
    });
}
</script>
