<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Drafter Autotech</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding-top: 80px;
            padding-left: 220px;
            background: #f0f0f0;
        }

        .back-button-container {
            position: fixed;
            top: 90px;
            left: 240px;
            z-index: 999;
        }

        .back-button-container img {
            width: 35px;
            height: 35px;
            cursor: pointer;
        }

        .receipt-container {
            width: 21cm;
            height: 29.7cm;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 24px;
        }

        .receipt-details {
            margin-bottom: 20px;
            text-align: left;
        }

        .receipt-details p {
            margin: 5px 0;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .receipt-table th, 
        .receipt-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .total-amount {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .print-button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #E10F0F;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }

        @media print {
            .sidebar, .topbar, .back-button-container, .print-button {
                display: none !important;
            }

            body, html {
                width: 21cm;
                height: 29.7cm;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }

            .receipt-container {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 20px;
                border: none;
            }
        }
    </style>
</head>
<body>
<?php
session_start();
include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$serviceID = $_GET['id'];
$queryReceipt = "SELECT s.ServiceID, s.Type AS ServiceType, s.Price AS ServicePrice, 
                        p.PartID, p.Name AS PartName, p.Price AS PartPrice, 
                        p.Quantity, p.Location,
                        (p.Price + s.Price) AS TotalPrice, 
                        DATE_FORMAT(s.Date, '%M %d, %Y %h:%i %p') AS FormattedDate, 
                        CONCAT(u.FName, ' ', u.LName, ' (', u.RoleType, ')') AS ActionBy
                 FROM service s
                 JOIN part p ON s.PartID = p.PartID
                 JOIN user u ON s.StaffName = u.Username
                 WHERE s.ServiceID = ? AND p.archived = 0 AND s.archived = 0";

$stmt = $conn->prepare($queryReceipt);
$stmt->bind_param("i", $serviceID);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();

if (!$receipt) {
    die("Receipt not found.");
}
?>

<div class="receipt-container">
    <div class="receipt-header">
        <h2>Drafter Autotech Inventory System</h2>
        <p>Official Receipt</p>
    </div>

    <div class="receipt-details">
        <p><strong>Transaction ID:</strong> #<?= $receipt['ServiceID'] ?></p>
        <p><strong>Retrieved By:</strong> <?= $receipt['ActionBy'] ?></p>
        <p><strong>Date:</strong> <?= $receipt['FormattedDate'] ?></p>
    </div>

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
                <td><?= $receipt['PartName'] ?></td>
                <td>₱<?= number_format($receipt['PartPrice'], 2) ?></td>
            </tr>
            <tr>
                <td><?= $receipt['ServiceType'] ?></td>
                <td>₱<?= number_format($receipt['ServicePrice'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <p class="total-amount">Total Amount: ₱<?= number_format($receipt['TotalPrice'], 2) ?></p>

    <p><strong>Reason for Retrieval:</strong> To be used for service</p>

    <button class="print-button" onclick="window.print()">Print</button>
</div>

</body>
</html>
