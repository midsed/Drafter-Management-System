<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

?>

<?php
$userID = $_SESSION['UserID'];
$queryUser = "SELECT UserID, FName, LName, Email, Username, RoleType, LastLogin FROM user WHERE UserID = ?";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

$queryTransactions = "SELECT s.ServiceID, s.Type AS ServiceType, s.Price AS ServicePrice, 
                             p.PartID, p.Name AS PartName, p.Price AS PartPrice, 
                             (p.Price + s.Price) AS TotalPrice, 
                             s.Date AS Timestamp, 
                             CONCAT(u.FName, ' ', u.LName, ' (', u.RoleType, ')') AS ActionBy
                      FROM service s
                      JOIN part p ON s.PartID = p.PartID
                      JOIN user u ON s.StaffName = u.Username
                      WHERE u.UserID = ?";

$stmtTrans = $conn->prepare($queryTransactions);
$stmtTrans->bind_param("i", $userID);
$stmtTrans->execute();
$resultTrans = $stmtTrans->get_result();
$transactions = $resultTrans->fetch_all(MYSQLI_ASSOC);
?>


<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Receipts</h1>
    </div>

    <div class="container">
        <div class="filter-container">
            <button class="filter-button">Filter</button>
        </div>

        <table>
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th>Action by</th>
            <th>Timestamp</th>
            <th>Part Name</th>
            <th>Part Price</th>
            <th>Service Type</th>
            <th>Service Price</th>
            <th>Total Price</th>
            <th>Receipt</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td>#<?= $transaction['ServiceID'] ?></td>
                <td><?= $transaction['ActionBy'] ?></td>
                <td><?= $transaction['Timestamp'] ?></td>
                <td><?= $transaction['PartName'] ?></td>
                <td>₱<?= number_format($transaction['PartPrice'], 2) ?></td>
                <td><?= $transaction['ServiceType'] ?></td>
                <td>₱<?= number_format($transaction['ServicePrice'], 2) ?></td>
                <td>₱<?= number_format($transaction['TotalPrice'], 2) ?></td>
                <td>
                    <button class="print-receipt-button" 
                            data-receipt-id="#<?= $transaction['ServiceID'] ?>"
                            data-action-by="<?= $transaction['ActionBy'] ?>"
                            data-timestamp="<?= $transaction['Timestamp'] ?>"
                            data-part-name="<?= $transaction['PartName'] ?>"
                            data-part-price="<?= $transaction['PartPrice'] ?>"
                            data-service-type="<?= $transaction['ServiceType'] ?>"
                            data-service-price="<?= $transaction['ServicePrice'] ?>"
                            data-total-price="<?= $transaction['TotalPrice'] ?>">
                        Print
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>
</div>

<!-- Hidden print section -->
<div id="print-section" style="display: none;">
    <div class="receipt">
        <h2>Drafter Autotech Inventory System</h2>
        <p><strong>Transaction ID:</strong> <span id="receipt-id"></span></p>
        <p><strong>Action by:</strong> <span id="action-by"></span></p>
        <p><strong>Timestamp:</strong> <span id="timestamp"></span></p>

        <h3>Item Details</h3>
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="part-name"></td>
                    <td>₱<span id="part-price"></span></td>
                </tr>
                <tr>
                    <td id="service-type"></td>
                    <td>₱<span id="service-price"></span></td>
                </tr>
            </tbody>
        </table>

        <h3>Total Amount: ₱<span id="total-amount"></span></h3>
        <p><strong>Reason for Retrieval:</strong> To be used for service</p>
    </div>
</div>

<script>
    document.querySelectorAll(".print-receipt-button").forEach(button => {
    button.addEventListener("click", function () {
        document.getElementById("receipt-id").innerText = this.getAttribute("data-receipt-id");
        document.getElementById("action-by").innerText = this.getAttribute("data-action-by");
        document.getElementById("timestamp").innerText = this.getAttribute("data-timestamp");
        document.getElementById("part-name").innerText = this.getAttribute("data-part-name");
        document.getElementById("part-price").innerText = parseFloat(this.getAttribute("data-part-price")).toFixed(2);
        document.getElementById("service-type").innerText = this.getAttribute("data-service-type");
        document.getElementById("service-price").innerText = parseFloat(this.getAttribute("data-service-price")).toFixed(2);
        document.getElementById("total-amount").innerText = parseFloat(this.getAttribute("data-total-price")).toFixed(2);

        document.getElementById("print-section").style.display = "block";
        document.querySelector(".main-content").style.display = "none";

        window.print();

        document.getElementById("print-section").style.display = "none";
        document.querySelector(".main-content").style.display = "block";
    });
});
</script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .container {
        margin: 20px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .print-receipt-button {
        padding: 5px 10px;
        border-radius: 3px;
        background-color: #E10F0F;
        color: white;
        border: none;
        cursor: pointer;
    }

    #print-section {
        display: none;
        padding: 20px;
        border: 1px solid #ddd;
        margin: 20px;
        background-color: #fff;
    }

    .receipt {
        font-family: 'Poppins', sans-serif;
        text-align: left;
    }

    .receipt h2, .receipt h3 {
        text-align: center;
        margin-bottom: 20px;
    }

    .receipt-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
}

.receipt-table th, .receipt-table td {
    border: 1px solid #000;
    padding: 8px;
    text-align: center;
}
    @media print {
    body * {
        visibility: hidden;
    }

    #print-section, #print-section * {
        visibility: visible;
    }

    #print-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .sidebar, .topbar {
        display: none !important;
    }
}