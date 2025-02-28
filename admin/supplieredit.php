<?php
session_start();
require_once "dbconnect.php"; // Include the database connection

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Supplier ID not provided.");
}

$supplierID = $_GET['id'];

// Fetch supplier details from the database
$sql = "SELECT SupplierID, CompanyName, Email, PhoneNumber FROM supplier WHERE SupplierID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the query: " . $conn->error);
}
$stmt->bind_param("i", $supplierID);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

if (!$supplier) {
    die("Supplier not found.");
}
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

    .center-container {
        width: 50%;
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Edit Supplier</h1>
    </div>
    <div class="center-container">
        <form id="entryForm" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($supplier['Email']); ?>" required maxlength="64">
            </div>
            
            <div class="form-group">
                <label for="supplier">Supplier Name:</label>
                <input type="text" id="supplier" name="supplier" value="<?php echo htmlspecialchars($supplier['CompanyName']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($supplier['PhoneNumber']); ?>" required>
            </div>
            
            <button type="submit" class="btn">Update</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('entryForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update_supplier.php?id=<?php echo $supplierID; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            Swal.fire({
                title: data.includes("Success") ? "Success!" : "Error!",
                text: data,
                icon: data.includes("Success") ? "success" : "error",
                confirmButtonText: "OK"
            }).then(() => {
                if (data.includes("Success")) {
                    window.location.href = "supplier.php";
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: "Error!",
                text: "An error occurred while updating the supplier.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    });
</script>