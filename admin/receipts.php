<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

$userID = $_SESSION['UserID'];

$queryUser = "SELECT UserID, FName, LName, Email, Username, RoleType, LastLogin FROM user WHERE UserID = ?";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

$queryTransactions = "SELECT s.ServiceID, s.Type AS ServiceType, s.Price AS ServicePrice, 
                             p.PartID, p.Name AS PartName, p.Price AS PartPrice, 
                             p.Quantity, p.Location,
                             (p.Price + s.Price) AS TotalPrice, 
                             DATE_FORMAT(s.Date, '%M %d, %Y %h:%i %p') AS FormattedDate, 
                             CONCAT(u.FName, ' ', u.LName, ' (', u.RoleType, ')') AS ActionBy
                      FROM service s
                      JOIN part p ON s.PartID = p.PartID
                      JOIN user u ON s.StaffName = u.Username
                      WHERE u.UserID = ? AND p.archived = 0 AND s.archived = 0";

$stmtTrans = $conn->prepare($queryTransactions);
$stmtTrans->bind_param("i", $userID);
$stmtTrans->execute();
$resultTrans = $stmtTrans->get_result();
$transactions = $resultTrans->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipts - Drafter Autotech</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
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

        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover {
            background-color: #f9f9f9;
        }

        .print-receipt-button {
            padding: 5px 10px;
            border-radius: 3px;
            background-color: #E10F0F;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" style="width: 35px; height: 35px;">
        </a>
        <h1>Receipts</h1>
    </div>

    <div class="container">
        <table id="receipt-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Action by</th>
                    <th>Date</th>
                    <th>Part Name</th>
                    <th>Quantity</th>
                    <th>Location</th>
                    <th>Part Price</th>
                    <th>Service Type</th>
                    <th>Service Price</th>
                    <th>Total Price</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                <tr class="clickable-row" data-href="receipt_view.php?id=<?= $transaction['ServiceID'] ?>">
                    <td>#<?= $transaction['ServiceID'] ?></td>
                    <td><?= $transaction['ActionBy'] ?></td>
                    <td><?= $transaction['FormattedDate'] ?></td>
                    <td><?= $transaction['PartName'] ?></td>
                    <td><?= $transaction['Quantity'] ?></td>
                    <td><?= $transaction['Location'] ?></td>
                    <td>₱<?= number_format($transaction['PartPrice'], 2) ?></td>
                    <td><?= $transaction['ServiceType'] ?></td>
                    <td>₱<?= number_format($transaction['ServicePrice'], 2) ?></td>
                    <td>₱<?= number_format($transaction['TotalPrice'], 2) ?></td>
                    <td>
                        <button class="print-receipt-button" data-receipt-id="<?= $transaction['ServiceID'] ?>">
                            Print
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.print-receipt-button').forEach(button => {
    button.addEventListener('click', function(event) {
        event.stopPropagation();
        const receiptId = this.getAttribute('data-receipt-id');
        const printTab = window.open('receipt_view.php?id=' + receiptId, '_blank');
        printTab.onload = function() {
            printTab.print();
            printTab.onafterprint = function() {
                printTab.close();
            };
        };
    });
});

document.querySelectorAll(".clickable-row").forEach(row => {
    row.addEventListener("click", function (event) {
        if (!event.target.classList.contains("print-receipt-button")) {
            window.location.href = this.getAttribute("data-href");
        }
    });
});
</script>
</body>
</html>
