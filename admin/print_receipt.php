<?php
session_start();
include('dbconnect.php');

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] === 'Staff') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

$receiptID = $_GET['id'];

$query = "SELECT r.ReceiptID, 
                 r.RetrievedBy, 
                 r.RetrievedDate, 
                 r.PartID, 
                 r.Location, 
                 r.Quantity, 
                 r.DateAdded, 
                 p.Name AS PartName, 
                 p.Price AS PartPrice, 
                 u.RoleType, 
                 s.ServiceID,
                 s.Type AS ServiceType, 
                 s.Price AS ServicePrice
          FROM receipt r
          LEFT JOIN part p ON r.PartID = p.PartID
          LEFT JOIN user u ON r.UserID = u.UserID
          LEFT JOIN service s ON s.PartID = r.PartID 
          WHERE r.ReceiptID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $receiptID);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();

if (!$receipt) {
    die("Receipt not found.");
}

$totalAmount = 0;
if ($receipt['PartPrice']) $totalAmount += $receipt['PartPrice'];
if ($receipt['ServicePrice']) $totalAmount += $receipt['ServicePrice'];
?>

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
            background: #f0f0f0;
        }

        .receipt-container {
            width: 21cm;
            height: 29.7cm;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
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

        @media print {
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
<body onload="window.print(); window.onafterprint = function() { window.close(); }">

<div class="receipt-container">
    <div class="receipt-header">
        <h2>Drafter Autotech Inventory System</h2>
        <p>Official Receipt</p>
    </div>

    <div class="receipt-details">
        <p><strong>Transaction ID:</strong> #<?= $receipt['ReceiptID'] ?></p>
        <p><strong>Retrieved By:</strong> <?= $receipt['RetrievedBy'] ?></p>
        <p><strong>Date:</strong> <?= $receipt['RetrievedDate'] ?></p>
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
            <?php if (!empty($receipt['PartID'])): ?>
                <tr>
                    <td><?= htmlspecialchars($receipt['PartName']); ?></td>
                    <td>₱<?= number_format($receipt['PartPrice'], 2) ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($receipt['ServiceID'])): ?>
                <tr>
                    <td><?= htmlspecialchars($receipt['ServiceType']); ?></td>
                    <td>₱<?= number_format($receipt['ServicePrice'], 2) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="total-amount">Total Amount: ₱<?= number_format($totalAmount, 2) ?></p>

    <p><strong>Reason for Retrieval:</strong> To be used for service</p>
</div>

</body>
</html>