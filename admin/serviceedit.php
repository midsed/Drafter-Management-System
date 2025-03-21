<?php
ob_start();
session_start();
require_once "dbconnect.php";

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No Service ID provided.");
}

$service_id = $_GET['id'];
$query = $conn->prepare("SELECT s.ServiceID, s.Type, s.Price, s.ClientEmail, s.PartName, c.FName, c.LName, c.PhoneNumber 
                         FROM service s
                         LEFT JOIN client c ON s.ClientEmail = c.ClientEmail
                         WHERE s.ServiceID = ?");
$query->bind_param("i", $service_id);
$query->execute();
$result = $query->get_result();
$service = $result->fetch_assoc();
$query->close();

if (!$service) {
    die("Error: Service not found.");
}
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
        <h1>Edit Service</h1>
    </div>

    <div class="center-container">
        <form action="serviceedit_process.php" method="POST">
            <input type="hidden" name="service_id" value="<?php echo $service['ServiceID']; ?>">

            <div class="form-group">
                <label for="partName">Part Name:</label>
                <input type="text" id="part_name" name="part_name" 
                    value="<?php echo htmlspecialchars($service['PartName'] ?? ''); ?>" 
                    required pattern="^[A-Za-z\s]+$" title="Invalid name format">
            </div>

            <div class="form-group">
                <label for="fName">Customer First Name:</label>
                <input type="text" id="fName" name="fName" 
                       value="<?php echo htmlspecialchars($service['FName'] ?? ''); ?>" required 
                       pattern="^[A-Za-z\s]+$" title="Invalid name format">
            </div>

            <div class="form-group">
                <label for="lName">Customer Last Name:</label>
                <input type="text" id="lName" name="lName" 
                       value="<?php echo htmlspecialchars($service['LName'] ?? ''); ?>" required
                       pattern="^[A-Za-z\s]+$" title="Invalid name format">
                       
            </div>

            <div class="form-group">
                <label for="client_email">Customer Email:</label>
                <input type="email" id="client_email" name="client_email" 
                       value="<?php echo htmlspecialchars($service['ClientEmail']); ?>" required>
            </div>

            <div class="form-group">
                <label for="pNumber">Customer Phone Number:</label>
                <input type="text" id="pNumber" name="pNumber" 
                        value="<?php echo htmlspecialchars($service['PhoneNumber'] ?? ''); ?>" 
                        required maxlength="11" pattern="\d{11}"
                        title="Invalid phone number">
            </div>

            <div class="form-group">
                <label for="type">Service Type:</label>
                <input type="text" id="type" name="type" 
                       value="<?php echo htmlspecialchars($service['Type']); ?>" 
                       required oninput="checkPartMatch()" pattern="^[A-Za-z\s]+$" title="Invalid  format.">
            </div>

            <div class="form-group">
                <label for="price">Service Price:</label>
                <input type="number" id="price" name="price" 
                       value="<?php echo htmlspecialchars($service['Price']); ?>" required>
            </div>

            <input type="hidden" id="part_id" name="part_id" 
                   value="<?php echo htmlspecialchars($service['PartID'] ?? ''); ?>">

            <div class="actions">
                <button type="submit" class="black-button btn">Update</button>
                <button type="button" class="red-button btn" onclick="resetForm()">Reset</button>
            </div>
        </form>
    </div>
</div>

<script>
    function checkPartMatch() {
        let type = document.getElementById("type").value;
        fetch("check_part.php?type=" + encodeURIComponent(type))
            .then(response => response.text())
            .then(data => {
                if (data === "" || data === "N/A") {
                    document.getElementById("part_id").value = "";
                } else {
                    document.getElementById("part_id").value = data;
                }
            })
            .catch(error => console.error("Error fetching part data:", error));
    }
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
    function resetForm() {
        document.querySelector("form").reset();
        document.querySelectorAll("input").forEach(input => input.value = "");
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Real-time name validation for first and last name
        function watchNameField(fieldId, errorId) {
            const field = document.getElementById(fieldId);
            const errorElem = document.getElementById(errorId);
            const pattern = /^[A-Za-z\s]+$/;

            // Show/hide error in real-time as user types
            field.addEventListener("input", function () {
                if (!pattern.test(field.value)) {
                    errorElem.style.display = "block";  // show error
                } else {
                    errorElem.style.display = "none";   // hide error
                }
            });
        }
        watchNameField("fName", "fName-error");
        watchNameField("lName", "lName-error");

        // Email validation on blur
        function validateEmail(input) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email!',
                    text: 'Please enter a valid email address.',
                    confirmButtonColor: '#d63031'
                });
                input.value = ""; // Clear invalid input
            }
        }
        document.getElementById("client_email").addEventListener("blur", function () {
            validateEmail(this);
        });

        // Prevent form submission if email is invalid
        document.querySelector("form").addEventListener("submit", function (event) {
            const email = document.getElementById("client_email").value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Form Error!',
                    text: 'Please enter a valid email before submitting.',
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
