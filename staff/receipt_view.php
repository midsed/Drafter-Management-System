<?php
session_start();
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

include('dbconnect.php');

$receiptID = isset($_GET['id']) ? intval($_GET['id']) : 0;

$queryReceipt = "SELECT r.ReceiptID, r.RetrievedBy, r.RetrievedDate, r.PartID, r.Location, r.Quantity, 
                        p.Name AS PartName, p.Price AS PartPrice, 
                        (r.Quantity * p.Price) AS TotalPrice 
                 FROM receipt r
                 JOIN part p ON r.PartID = p.PartID
                 WHERE r.ReceiptID = ?";

$stmtReceipt = $conn->prepare($queryReceipt);
$stmtReceipt->bind_param("i", $receiptID);
$stmtReceipt->execute();
$resultReceipt = $stmtReceipt->get_result();
$receiptDetails = $resultReceipt->fetch_all(MYSQLI_ASSOC);

if (empty($receiptDetails)) {
    echo "No receipt found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - Drafter Autotech</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #E10F0F;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-logo {
            max-width: 240px;
            margin: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: 600;
            color: #E10F0F;
            margin: 5px 0;
        }
        .company-address {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 600;
            color: #333;
        }
        .total-row {
            font-weight: 600;
            background-color: #e9e9e9;
        }
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        .button {
            background-color: #E10F0F;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            font-weight: 500;
            transition: background-color 0.3s;
            min-width: 150px;
            text-align: center;
        }
        .button:hover {
            background-color: #c20d0d;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            margin-bottom: 30px;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #666;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                background-color: white;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <img src="../images/Drafter Black.png" alt="Drafter Autotech Black Logo" class="company-logo" style="width: 240px; margin: 20px; margin-bottom: -90px; margin-top: -20px;">
            <div class="company-name">Inventory System</div>
            <div class="company-address">Extension, B113 L12 Mindanao Avenue, corner Regalado Hwy, Quezon City, 1100</div>
        </div>

        <div class="receipt-info">
            <div>
                <strong>Receipt ID:</strong> <?= $receiptID ?>
            </div>
            <div>
                <strong>Retrieved By:</strong> <?= htmlspecialchars($receiptDetails[0]['RetrievedBy']) ?>
            </div>
            <div>
                <strong>Date:</strong> <?= date('F d, Y h:i A', strtotime($receiptDetails[0]['RetrievedDate'])) ?>
            </div>
        </div>

        <h3 style="color: #e40000;">Parts Retrieved</h3>
        <table>
            <thead>
                <tr>
                    <th>Part Name</th>
                    <th>Location</th>
                    <th>Quantity</th>
                    <th>Part Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalAmount = 0;
                foreach ($receiptDetails as $detail): 
                    $totalForPart = $detail['TotalPrice'];
                    $totalAmount += $totalForPart;
                ?>
                <tr>
                    <td><?= htmlspecialchars($detail['PartName']) ?></td>
                    <td><?= htmlspecialchars($detail['Location']) ?></td>
                    <td><?= htmlspecialchars($detail['Quantity']) ?></td>
                    <td>₱<?= number_format($detail['PartPrice'], 2) ?></td>
                    <td>₱<?= number_format($totalForPart, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>₱<?= number_format($totalAmount, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>Permitted By:</p>
                <p>Signature Over Printed Name</p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>Retrieved By: <?= htmlspecialchars($receiptDetails[0]['RetrievedBy']) ?></p>
                <p>Signature Over Printed Name</p>
            </div>
        </div>

        <div class="footer">
            Thank you for using Drafter Autotech's Inventory System!
        </div>

        <div class="no-print button-container">
            <button onclick="window.print();" class="button">Print Receipt</button>
            <a href="receipts.php" class="button">Back to Receipts</a>
        </div>
    </div>

    <script>
        // Auto-print if successful
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>