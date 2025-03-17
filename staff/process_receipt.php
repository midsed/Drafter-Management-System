<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('dbconnect.php'); 

// Initialize variables
$receiptID = 0;
$errorMessage = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retrievedBy'])) {
    $retrievedBy = $_POST['retrievedBy'];
    $userID = $_SESSION['UserID'];
    $roleType = $_SESSION['RoleType'] ?? 'Staff';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Process each item in cart
        foreach ($_SESSION['cart'] as $partID => $part) {
            // Add to receipt
            $stmt = $conn->prepare("INSERT INTO receipt (RetrievedBy, RetrievedDate, PartID, DateAdded, Location, Quantity, RoleType, UserID) 
                                  VALUES (?, NOW(), ?, NOW(), ?, ?, ?, ?)");
            $stmt->bind_param("sisssi", $retrievedBy, $partID, $part['Location'], $part['Quantity'], $roleType, $userID);
            $stmt->execute();
            
            if (!$receiptID) {
                $receiptID = $conn->insert_id; // Get the first receipt ID
            }
            
            // Update inventory
            $stmt = $conn->prepare("UPDATE part SET Quantity = Quantity - ?, LastUpdated = NOW() WHERE PartID = ?");
            $stmt->bind_param("ii", $part['Quantity'], $partID);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        $success = true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Drafter Autotech</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
            margin-bottom: -90px;
            margin-top: -20px;
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
        .receipt-info-item {
            flex: 1;
            margin-right: 20px;
        }
        .receipt-info-item:last-child {
            margin-right: 0;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #E10F0F;
        }
        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            font-weight: bold;
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .total-row {
            font-weight: 600;
            background-color: #e9e9e9;
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
        .button-container {
            text-align: center;
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
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
        <?php if ($success): ?>
            
            <div class="receipt-header">
                <img src="../images/Drafter Black.png" alt="Drafter Autotech Black Logo" class="company-logo">
                <div class="company-name">Inventory System</div>
                <div class="company-address">Extension, B113 L12 Mindanao Avenue, corner Regalado Hwy, Quezon City, 1100</div>
            </div>
            
            <div class="receipt-info">
                <div class="receipt-info-item">
                    <p><strong>Receipt ID:</strong> <?php echo $receiptID; ?></p>
                </div>
                <div class="receipt-info-item">
                    <p><strong>Retrieved By:</strong> <?php echo htmlspecialchars($retrievedBy); ?></p>
                </div>
                <div class="receipt-info-item">
                    <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
            </div>
            
            <div class="receipt-title">Parts Retrieved</div>
            <table>
                <thead>
                    <tr>
                        <th>Part Name</th>
                        <th>Model</th>
                        <th>Location</th>
                        <th>Qty.</th>
                        <th>Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalAmount = 0;
                    foreach ($_SESSION['cart'] as $partID => $part): 
                        $totalForPart = $part['Price'] * $part['Quantity'];
                        $totalAmount += $totalForPart;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($part['Name']); ?></td>
                        <td><?php echo htmlspecialchars($part['Model']); ?></td>
                        <td><?php echo htmlspecialchars($part['Location']); ?></td>
                        <td><?php echo htmlspecialchars($part['Quantity']); ?></td>
                        <td>₱<?php echo number_format($part['Price'], 2); ?></td>
                        <td>₱<?php echo number_format($totalForPart, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                        <td><strong>₱<?php echo number_format($totalAmount, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Permitted By</p>
                    <p>Signature Over Printed Name</p>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Retrieved By: <?php echo htmlspecialchars($retrievedBy); ?></p>
                    <p>Signature Over Printed Name</p>
                </div>
            </div>
            
            <div class="footer">
                Thank you for using Drafter Autotech's Inventory System!
            </div>
            
            <?php
            // Clear the cart after successful receipt creation
            $_SESSION['cart'] = [];
            ?>
            
            <div class="no-print button-container">
                <button onclick="window.print();" class="button">Print Receipt</button>
                <a href="parts.php" class="button">Return to Parts</a>
            </div>
            
        <?php else: ?>
            <div class="error">Error creating receipt: <?php echo htmlspecialchars($errorMessage); ?></div>
            <div class="button-container">
                <a href="cart.php" class="button">Return to Cart</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-print if successful
        <?php if ($success): ?>
        window.onload = function() {
            window.print();
        };
        <?php endif; ?>
    </script>
</body>
</html>