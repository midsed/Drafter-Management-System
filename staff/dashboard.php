<?php session_start(); 
include('dbconnect.php'); 

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

// Get total parts added and their value
$totalPartsQuery = $conn->query("SELECT COUNT(*) as count, SUM(Price * Quantity) as value FROM part WHERE Archived = 0");
$totalPartsData = $totalPartsQuery->fetch_assoc();
$totalPartsAdded = $totalPartsData['count'];
$totalInventoryValue = $totalPartsData['value'] ?? 0;

// Get total parts retrieved and their value
$retrievalQuery = $conn->query("SELECT SUM(r.Quantity) as quantity, SUM(r.Quantity * p.Price) as value 
FROM receipt r JOIN part p ON r.PartID = p.PartID");
$retrievalData = $retrievalQuery->fetch_assoc();
$totalPartsRetrieved = $retrievalData['quantity'] ?? 0;
$totalRetrievalValue = $retrievalData['value'] ?? 0;

// Get total parts archived
$totalPartsArchived = $conn->query("SELECT COUNT(*) FROM part WHERE Archived = 1")->fetch_row()[0];

// Get total users with roles
$totalUsers = $conn->query("SELECT COUNT(*) FROM user")->fetch_row()[0];

// Get total suppliers
$totalSuppliers = $conn->query("SELECT COUNT(*) FROM supplier")->fetch_row()[0];

// Get total services
$totalServices = $conn->query("SELECT COUNT(*) FROM service")->fetch_row()[0];

// Get stock value by category
$categoryValueQuery = "SELECT Category, SUM(Price * Quantity) as value FROM part WHERE Archived = 0 GROUP BY Category";
$categoryValueResult = $conn->query($categoryValueQuery);
$categoryValues = [];
while ($row = $categoryValueResult->fetch_assoc()) {
    $categoryValues[$row['Category']] = $row['value'];
}

// Get parts movement trends
$movementQuery = "SELECT DATE(r.RetrievedDate) as date, SUM(r.Quantity) as quantity 
FROM receipt r GROUP BY DATE(r.RetrievedDate) 
ORDER BY date DESC LIMIT 30";
$movementResult = $conn->query($movementQuery);
$movementTrends = [];
while ($row = $movementResult->fetch_assoc()) {
    $movementTrends[$row['date']] = $row['quantity'];
}

$lowStockQuery = "SELECT * FROM part WHERE Quantity < 2";
$lowStockResult = mysqli_query($conn, $lowStockQuery);
$recentReceiptsQuery = "SELECT r.ReceiptID, CONCAT(r.RetrievedBy, ' (', u.RoleType, ')') AS RetrievedByRole, 
                        r.RetrievedDate, r.PartID, r.Location, r.Quantity, r.DateAdded, 
                        p.Name AS PartName, p.Price AS PartPrice 
                        FROM receipt r 
                        LEFT JOIN part p ON r.PartID = p.PartID 
                        LEFT JOIN user u ON r.UserID = u.UserID 
                        ORDER BY r.RetrievedDate DESC LIMIT 5";
$recentReceiptsResult = mysqli_query($conn, $recentReceiptsQuery);
$recentReceiptsData = [];
while ($row = mysqli_fetch_assoc($recentReceiptsResult)) {
    $recentReceiptsData[] = $row;
}
$recentPartsQuery = "SELECT * FROM part ORDER BY LastUpdated DESC LIMIT 5";
$recentPartsResult = mysqli_query($conn, $recentPartsQuery);

$stockLevelsQuery = "SELECT Name, Quantity FROM part";
$stockLevelsResult = mysqli_query($conn, $stockLevelsQuery);
$stockLevels = [];
while ($row = mysqli_fetch_assoc($stockLevelsResult)) {
    $stockLevels[$row['Name']] = $row['Quantity'];
}

$partsAddedQuery = "SELECT DATE(DateAdded) as date, COUNT(*) as count FROM part GROUP BY DATE(DateAdded) ORDER BY date ASC";
$partsAddedResult = mysqli_query($conn, $partsAddedQuery);
$partsAddedData = [];
while ($row = mysqli_fetch_assoc($partsAddedResult)) {
    $partsAddedData[$row['date']] = intval($row['count']);
}

$recentReceiptsQuery = "SELECT r.ReceiptID, CONCAT(r.RetrievedBy, ' (', u.RoleType, ')') AS RetrievedByRole, 
                        r.RetrievedDate, r.PartID, r.Location, r.Quantity, r.DateAdded, 
                        p.Name AS PartName, p.Price AS PartPrice 
                        FROM receipt r 
                        LEFT JOIN part p ON r.PartID = p.PartID 
                        LEFT JOIN user u ON r.UserID = u.UserID 
                        ORDER BY r.RetrievedDate DESC LIMIT 10";
$recentReceiptsResult = mysqli_query($conn, $recentReceiptsQuery);
if (!$recentReceiptsResult) {
    die("SQL Error: " . mysqli_error($conn));
}

$checkoutQuery = "SELECT DATE(RetrievedDate) as date, SUM(Quantity) as total 
                 FROM receipt 
                 WHERE RetrievedDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(RetrievedDate)
                 ORDER BY date ASC";
$checkoutResult = mysqli_query($conn, $checkoutQuery);
$checkoutData = [];
while ($row = mysqli_fetch_assoc($checkoutResult)) {
    $checkoutData[$row['date']] = intval($row['total']);
}

$totalPartsQuery = "SELECT COUNT(*) as total, SUM(Price * Quantity) as totalValue FROM part";
$totalPartsResult = mysqli_query($conn, $totalPartsQuery);
$totalPartsData = mysqli_fetch_assoc($totalPartsResult);

$categoryBreakdownQuery = "SELECT Category, COUNT(*) as count FROM part GROUP BY Category";
$categoryBreakdownResult = mysqli_query($conn, $categoryBreakdownQuery);
$categoryBreakdown = [];
while ($row = mysqli_fetch_assoc($categoryBreakdownResult)) {
    $categoryBreakdown[$row['Category']] = $row['count'];
}

$monthlySummaryQuery = "SELECT 
    DATE_FORMAT(RetrievedDate, '%Y-%m') as month, 
    COUNT(*) as transactionCount, 
    SUM(Quantity) as totalQuantity 
    FROM receipt 
    WHERE RetrievedDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(RetrievedDate, '%Y-%m')
    ORDER BY month DESC";
$monthlySummaryResult = mysqli_query($conn, $monthlySummaryQuery);
$monthlySummary = [];
while ($row = mysqli_fetch_assoc($monthlySummaryResult)) {
    $monthlySummary[$row['month']] = [
        'count' => $row['transactionCount'],
        'quantity' => $row['totalQuantity']
    ];
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/x-icon" href="images/New Drafter Logo Cropped.png">

<div class="main-content">
    <div class="content">
        <div class="metrics-container">
            <div class="metric-card" onclick="window.location.href='parts.php'">
                <div class="metric-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h2>Active Parts</h2>
                <div class="metric-value"><?php echo number_format($totalPartsAdded); ?></div>
                <div class="metric-subtitle">Value: ₱<?php echo number_format($totalInventoryValue, 2); ?></div>
            </div>
            <div class="metric-card" onclick="window.location.href='receipts.php'">
                <div class="metric-icon">
                    <i class="fas fa-arrow-alt-circle-down"></i>
                </div>
                <h2>Parts Retrieved</h2>
                <div class="metric-value"><?php echo number_format($totalPartsRetrieved); ?></div>
                <div class="metric-subtitle">Value: ₱<?php echo number_format($totalRetrievalValue, 2); ?></div>
            </div>
            <div class="metric-card" onclick="window.location.href='partsarchive.php'">
                <div class="metric-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <h2>Archived Parts</h2>
                <div class="metric-value"><?php echo number_format($totalPartsArchived); ?></div>
                <div class="metric-subtitle">Inactive Inventory</div>
            </div>
            <div class="metric-card" onclick="window.location.href='supplier.php'">
                <div class="metric-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h2>Active Suppliers</h2>
                <div class="metric-value"><?php echo number_format($totalSuppliers); ?></div>
                <div class="metric-subtitle">Parts Providers</div>
            </div>
            <div class="metric-card" onclick="window.location.href='service.php'">
                <div class="metric-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h2>Available Services</h2>
                <div class="metric-value"><?php echo number_format($totalServices); ?></div>
                <div class="metric-subtitle">Active Services</div>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-box">
                <h2>Stock Levels</h2>
                <div class="chart-controls">
                    <select id="stockTimePeriod" onchange="updateStockChart()">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <canvas id="stockLevelChart"></canvas>
            </div>
            <div class="chart-box">
                <h2>Parts Added Over Time</h2>
                <div class="chart-controls">
                    <select id="timePeriod" onchange="updateLineChart()">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <canvas id="recentUpdatesChart"></canvas>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-box">
                <h2>Recent Parts Retrieved</h2>
                <div class="chart-controls">
                    <select id="checkoutTimePeriod" onchange="updateCheckoutChart()">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <canvas id="checkoutTrendChart"></canvas>
            </div>
            <div class="chart-box">
                <h2>Inventory Value by Category</h2>
                <div class="chart-controls">
                    <select id="valueTimePeriod" onchange="updateValueChart()">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <canvas id="valueChart"></canvas>
            </div>
        </div>
        <div class="transaction-history">
            <h2>Recent Parts Retrieved</h2>
            <div class="table-responsive">
                <table>
                    <tr>
                        <th>Receipt ID</th>
                        <th>Retrieved By</th>
                        <th>Part Name</th>
                        <th>Quantity</th>
                        <th>Part Price</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                    <?php if (mysqli_num_rows($recentReceiptsResult) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recentReceiptsResult)) { 
                            $totalPrice = 0;
                            if ($row['PartPrice']) $totalPrice = $row['PartPrice'] * $row['Quantity'];
                        ?>
                            <tr>
                                <td>#<?php echo $row['ReceiptID']; ?></td>
                                <td><?php echo htmlspecialchars($row['RetrievedByRole']); ?></td>
                                <td><?php echo ($row['PartID'] !== NULL) ? $row['PartName'] : '<i>Unknown</i>'; ?></td>
                                <td><?php echo $row['Quantity']; ?></td>
                                <td>₱<?php echo number_format($row['PartPrice'], 2); ?></td>
                                <td>₱<?php echo number_format($totalPrice, 2); ?></td>
                                <td>
                                    <a href="receipt_view.php?id=<?php echo $row['ReceiptID']; ?>" target="_blank" class="print-receipt-button">View</a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">No recent transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <div class="low-stock-alerts">
            <h2>Low Stock Alerts</h2>
            <div class="table-responsive">
                <table>
                    <tr>
                        <th>Part ID</th>
                        <th>Part Name</th>
                        <th>Category</th>
                        <th>Condition</th>
                        <th>Quantity</th>
                        <th>Details</th>
                    </tr>
                    <?php 
                    $lowStockCount = 0;
                    while ($row = mysqli_fetch_assoc($lowStockResult)) { 
                        $lowStockCount++;
                    ?>
                        <tr>
                            <td>#<?php echo $row['PartID']; ?></td>
                            <td><?php echo $row['Name']; ?></td>
                            <td><?php echo $row['Category']; ?></td>
                            <td><?php echo $row['PartCondition']; ?></td>
                            <td><?php echo $row['Quantity']; ?></td>
                            <td><a href="partdetail.php?id=<?php echo $row['PartID']; ?>" class="print-receipt-button">More Details</a></td>
                        </tr>
                    <?php } ?>
                    <?php if ($lowStockCount === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No low stock items found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <div class="new-updated-parts">
            <h2>New and Recently Updated Parts</h2>
            <div class="table-responsive">
                <table>
                    <tr>
                        <th>Part ID</th>
                        <th>Part Name</th>
                        <th>Category</th>
                        <th>Condition</th>
                        <th>Quantity</th>
                        <th>Details</th>
                    </tr>
                    <?php 
                    $recentPartsCount = 0;
                    while ($row = mysqli_fetch_assoc($recentPartsResult)) { 
                        $recentPartsCount++;
                    ?>
                        <tr>
                            <td>#<?php echo $row['PartID']; ?></td>
                            <td><?php echo $row['Name']; ?></td>
                            <td><?php echo $row['Category']; ?></td>
                            <td><?php echo $row['PartCondition']; ?></td>
                            <td><?php echo $row['Quantity']; ?></td>
                            <td><a href="partdetail.php?id=<?php echo $row['PartID']; ?>" class="print-receipt-button">More Details</a></td>
                        </tr>
                    <?php } ?>
                    <?php if ($recentPartsCount === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No recent parts found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.main-content {
    padding: 25px;
    transition: margin-left 0.3s, padding 0.3s;
    margin-left: 250px;
    margin-right: 50px;
    background-color: #f8f9fa;
    min-height: calc(100vh - 60px);
}

/* Dashboard Header Styling */
.header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid rgba(225, 15, 15, 0.1);
}

.header h1 {
    margin: 0;
    color: #E10F0F;
    font-size: 28px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.metrics-container {
    display: grid;
    margin-top:100px;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.8rem;
    margin-bottom: 2.5rem;
}

.metric-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
    padding: 1.8rem;
    position: relative;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    border: 1px solid rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, #E10F0F, #FF5757);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}

.metric-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.metric-card:hover::before {
    transform: scaleX(1);
}

.metric-icon {
    font-size: 2.5rem;
    color: #E10F0F;
    margin-bottom: 1.2rem;
    background: rgba(225, 15, 15, 0.08);
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.metric-card:hover .metric-icon {
    transform: scale(1.1);
    background: rgba(225, 15, 15, 0.12);
}

.metric-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0.6rem 0;
    transition: color 0.3s ease;
}

.metric-card:hover .metric-value {
    color: #E10F0F;
}

.metric-subtitle {
    font-size: 0.95rem;
    color: #6c757d;
    margin-top: 0.6rem;
    font-weight: 500;
}

.metric-card h2 {
    font-size: 1.2rem;
    color: #343a40;
    margin: 0;
    font-weight: 600;
    transition: color 0.3s ease;
}

.metric-card:hover h2 {
    color: #E10F0F;
}

/* Chart Container Styling */
.chart-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 2.2rem;
    margin-bottom: 2.5rem;
}

.chart-box {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
    padding: 1.8rem;
    position: relative;
    height: 380px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0, 0, 0, 0.03);
}

.chart-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.chart-box h2 {
    margin-top: 0;
    margin-bottom: 18px;
    font-size: 20px;
    text-align: center;
    color: #2c3e50;
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
}

.chart-box h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #E10F0F, #FF5757);
    border-radius: 3px;
}

.chart-controls {
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
}

.chart-controls select {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    outline: none;
}

.chart-controls select:hover {
    border-color: #E10F0F;
}

.chart-controls select:focus {
    border-color: #E10F0F;
    box-shadow: 0 0 0 3px rgba(225, 15, 15, 0.1);
}

/* Table Styling */
.transaction-history, .low-stock-alerts, .new-updated-parts {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
    padding: 1.8rem;
    margin-bottom: 2.5rem;
    border: 1px solid rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
}

.transaction-history:hover, .low-stock-alerts:hover, .new-updated-parts:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.transaction-history h2, .low-stock-alerts h2, .new-updated-parts h2 {
    color: #2c3e50;
    font-size: 20px;
    margin-top: 0;
    margin-bottom: 20px;
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
    display: inline-block;
}

.transaction-history h2::after, .low-stock-alerts h2::after, .new-updated-parts h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #E10F0F, #FF5757);
    border-radius: 3px;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 10px;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 14px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background-color: #f4f6f8;
    font-weight: 600;
    color: #2c3e50;
    position: sticky;
    top: 0;
    z-index: 10;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background-color: rgba(225, 15, 15, 0.03);
}

table td button, table td a {
    background: linear-gradient(135deg, #E10F0F, #FF5757);
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    display: inline-block;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(225, 15, 15, 0.2);
}

table td button:hover, table td a:hover {
    background: linear-gradient(135deg, #c90d0d, #e64c4c);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(225, 15, 15, 0.3);
}

/* Responsive Adjustments */
@media (max-width: 1200px) {
    .chart-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 992px) {
    .metrics-container {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px 15px;
        padding-top: 80px;
    }
    
    .metrics-container {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.2rem;
    }
    
    .chart-box {
        height: 320px;
        padding: 1.2rem;
    }
    
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .report-button {
        align-self: flex-start;
    }
}

@media (max-width: 576px) {
    .metrics-container {
        grid-template-columns: 1fr;
    }
    
    .metric-card {
        padding: 1.5rem;
    }
    
    .chart-box h2, .transaction-history h2, .low-stock-alerts h2, .new-updated-parts h2 {
        font-size: 18px;
    }
}

/* Animation Styles */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.metric-card, .chart-box, .transaction-history, .low-stock-alerts, .new-updated-parts {
    opacity: 0;
}

.animate-in {
    animation: fadeInUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

/* Enhanced hover effects */
.metric-card:hover .metric-icon i {
    transform: scale(1.2) rotate(5deg);
    transition: transform 0.3s ease;
}

/* Pulse animation for report button */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 163, 0, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 163, 0, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 163, 0, 0);
    }
}

.report-button {
    animation: pulse 2s infinite;
}

/* Preview Content Styling */
.preview-loading {
    text-align: center;
    padding: 30px;
    color: #6c757d;
}

.preview-loading i {
    font-size: 36px;
    margin-bottom: 15px;
    color: #E10F0F;
}

.preview-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.preview-header {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

.preview-logo {
    height: 60px;
    margin-bottom: 10px;
}

.preview-header h2 {
    margin: 10px 0;
    color: #2c3e50;
    font-size: 22px;
}

.preview-header p {
    color: #6c757d;
    font-size: 14px;
    margin: 0;
}

.preview-body {
    padding: 20px;
}

.preview-section {
    margin-bottom: 20px;
}

.preview-section h3 {
    color: #2c3e50;
    font-size: 18px;
    margin-top: 0;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid rgba(225, 15, 15, 0.1);
}

.preview-metrics {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.preview-metric {
    text-align: center;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
    flex: 1;
    margin: 0 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.metric-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #E10F0F;
    margin-bottom: 5px;
}

.metric-label {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
}
</style>

<div id="reportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Customize Report</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="report-sections">
                <h3>Select Sections to Include</h3>
                <div class="checkbox-group">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="reportSection" value="stockLevels" checked>
                        <span class="checkmark"></span>
                        <span>Stock Levels</span>
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="reportSection" value="partsAdded" checked>
                        <span class="checkmark"></span>
                        <span>Parts Added Over Time</span>
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="reportSection" value="checkoutTrend" checked>
                        <span class="checkmark"></span>
                        <span>Recent Parts Retrieved</span>
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="reportSection" value="valueByCategory" checked>
                        <span class="checkmark"></span>
                        <span>Inventory Value by Category</span>
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="reportSection" value="monthlySummary" checked>
                        <span class="checkmark"></span>
                        <span>Monthly Transaction Summary</span>
                    </label>
                </div>
            </div>
            <div class="report-customization">
                <div class="customization-group">
                    <h3>Layout & Style</h3>
                    <div class="form-group">
                        <label for="reportTitle">Report Title:</label>
                        <input type="text" id="reportTitle" value="Inventory Dashboard Report" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="reportPrimaryColor">Primary Color:</label>
                        <input type="color" id="reportPrimaryColor" value="#E10F0F" class="color-picker">
                    </div>
                    <div class="form-group">
                        <label for="reportAccentColor">Accent Color:</label>
                        <input type="color" id="reportAccentColor" value="#4A90E2" class="color-picker">
                    </div>
                    <div class="form-group">
                        <label for="reportLogo" class="toggle-label">Include Company Logo:
                            <div class="toggle-switch">
                                <input type="checkbox" id="reportLogo" checked>
                                <span class="toggle-slider"></span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="customization-group">
                    <h3>Data Options</h3>
                    <div class="form-group">
                        <label for="dataTimeRange">Time Range:</label>
                        <select id="dataTimeRange" class="form-control">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="180">Last 6 Months</option>
                            <option value="365">Last Year</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="includeRawData" class="toggle-label">Include Raw Data Tables:
                            <div class="toggle-switch">
                                <input type="checkbox" id="includeRawData" checked>
                                <span class="toggle-slider"></span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="report-preview">
                <h3>Preview</h3>
                <div id="reportPreview" class="preview-container">
                    <div class="preview-placeholder">
                        <i class="fas fa-file-alt preview-icon"></i>
                        <p>Your report will include the selected sections with your customized styling.</p>
                        <p>The preview will update as you change options.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="report-actions">
            <button id="previewReport" class="action-button preview-button">
                <i class="fas fa-sync-alt"></i> Update Preview
            </button>
            <button id="exportExcel" class="action-button excel-button">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
            <button id="exportPDF" class="action-button pdf-button">
                <i class="fas fa-file-pdf"></i> Export to PDF
            </button>
        </div>
    </div>
</div>

<!-- Modal Styling -->
<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity 0.3s ease;
    backdrop-filter: blur(5px);
}

.modal.show {
    opacity: 1;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    width: 80%;
    max-width: 900px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: translateY(-50px);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal.show .modal-content {
    transform: translateY(0);
    opacity: 1;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
}

.modal-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 24px;
    font-weight: 600;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #E10F0F;
}

.modal-body {
    padding: 25px;
    max-height: 70vh;
    overflow-y: auto;
}

.report-sections, .report-customization, .report-preview {
    margin-bottom: 30px;
}

.report-sections h3, .report-customization h3, .report-preview h3 {
    color: #2c3e50;
    font-size: 18px;
    margin-top: 0;
    margin-bottom: 15px;
    font-weight: 600;
    position: relative;
    padding-bottom: 8px;
    display: inline-block;
}

.report-sections h3::after, .report-customization h3::after, .report-preview h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, #E10F0F, #FF5757);
    border-radius: 3px;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
}

.custom-checkbox {
    display: flex;
    align-items: center;
    position: relative;
    padding-left: 35px;
    cursor: pointer;
    font-size: 15px;
    user-select: none;
    margin-bottom: 12px;
}

.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 22px;
    width: 22px;
    background-color: #f1f1f1;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.custom-checkbox:hover input ~ .checkmark {
    background-color: #e0e0e0;
}

.custom-checkbox input:checked ~ .checkmark {
    background-color: #E10F0F;
    border-color: #E10F0F;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.custom-checkbox input:checked ~ .checkmark:after {
    display: block;
}

.custom-checkbox .checkmark:after {
    left: 8px;
    top: 4px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.report-customization {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.customization-group {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #E10F0F;
    box-shadow: 0 0 0 3px rgba(225, 15, 15, 0.1);
    outline: none;
}

.color-picker {
    width: 100%;
    height: 40px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
}

.toggle-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #E10F0F;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.preview-container {
    background-color: #f8f9fa;
    border: 1px dashed #ddd;
    border-radius: 12px;
    padding: 25px;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-placeholder {
    text-align: center;
    color: #6c757d;
}

.preview-icon {
    font-size: 48px;
    color: #E10F0F;
    margin-bottom: 15px;
    opacity: 0.5;
}

.report-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding: 20px 25px;
    background-color: #f8f9fa;
    border-top: 1px solid #eee;
}

.action-button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.preview-button {
    background-color: #6c757d;
    color: white;
}

.preview-button:hover {
    background-color: #5a6268;
}

.excel-button {
    background-color: #217346;
    color: white;
}

.excel-button:hover {
    background-color: #1a5c38;
}

.pdf-button {
    background-color: #E10F0F;
    color: white;
}

.pdf-button:hover {
    background-color: #c90d0d;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .report-customization {
        grid-template-columns: 1fr;
    }
    
    .checkbox-group {
        grid-template-columns: 1fr;
    }
    
    .report-actions {
        flex-direction: column;
    }
    
    .action-button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    // Global chart objects for later reference
    let stockChart, partsAddedChart, checkoutChart, valueChart;
    
    // Add animation class to metric cards on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Animate metric cards with a staggered delay
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('animate-in');
            }, 100 * index);
        });
        
        // Animate chart boxes with a staggered delay
        const chartBoxes = document.querySelectorAll('.chart-box');
        chartBoxes.forEach((box, index) => {
            setTimeout(() => {
                box.classList.add('animate-in');
            }, 300 + (100 * index));
        });
        
        // Animate tables with a staggered delay
        const tables = document.querySelectorAll('.transaction-history, .low-stock-alerts, .new-updated-parts');
        tables.forEach((table, index) => {
            setTimeout(() => {
                table.classList.add('animate-in');
            }, 600 + (100 * index));
        });
        
        // Modal functionality
        const modal = document.getElementById('reportModal');
        const btn = document.getElementById('generateReportBtn');
        const span = document.getElementsByClassName('close')[0];
        
        // When the user clicks the button, open the modal
        btn.onclick = function() {
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }
        
        // Preview report button functionality
        document.getElementById('previewReport').addEventListener('click', function() {
            const previewContainer = document.getElementById('reportPreview');
            previewContainer.innerHTML = '<div class="preview-loading"><i class="fas fa-spinner fa-spin"></i><p>Generating preview...</p></div>';
            
            setTimeout(() => {
                previewContainer.innerHTML = `
                    <div class="preview-content">
                        <div class="preview-header">
                            <img src="images/New Drafter Logo Cropped.png" alt="Drafter Logo" class="preview-logo">
                            <h2>${document.getElementById('reportTitle').value}</h2>
                            <p>Generated on ${new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        <div class="preview-body">
                            <div class="preview-section">
                                <h3>Dashboard Summary</h3>
                                <p>This report provides an overview of your inventory management system.</p>
                                <div class="preview-metrics">
                                    <div class="preview-metric">
                                        <span class="metric-number">${document.querySelector('.metrics-container .metric-card:nth-child(1) .metric-value').textContent}</span>
                                        <span class="metric-label">Active Parts</span>
                                    </div>
                                    <div class="preview-metric">
                                        <span class="metric-number">${document.querySelector('.metrics-container .metric-card:nth-child(2) .metric-value').textContent}</span>
                                        <span class="metric-label">Parts Retrieved</span>
                                    </div>
                                    <div class="preview-metric">
                                        <span class="metric-number">${document.querySelector('.metrics-container .metric-card:nth-child(3) .metric-value').textContent}</span>
                                        <span class="metric-label">Archived Parts</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }, 1500);
        });
    });
    
    // Function to update stock chart based on time period
    function updateStockChart() {
        const selectedPeriod = document.getElementById('stockTimePeriod')?.value || 'daily';
        fetch(`get_chart_data.php?chart=stock&period=${selectedPeriod}`)
            .then(response => response.json())
            .then(data => {
                if (stockChart) {
                    stockChart.data.labels = data.labels;
                    stockChart.data.datasets[0].data = data.values;
                    stockChart.update();
                }
            })
            .catch(error => console.error('Error updating stock chart:', error));
    }

    // Function to update checkout chart based on time period
    function updateCheckoutChart() {
        const selectedPeriod = document.getElementById('checkoutTimePeriod')?.value || 'daily';
        fetch(`get_chart_data.php?chart=checkout&period=${selectedPeriod}`)
            .then(response => response.json())
            .then(data => {
                if (checkoutChart) {
                    checkoutChart.data.labels = data.labels;
                    checkoutChart.data.datasets[0].data = data.values;
                    checkoutChart.update();
                }
            })
            .catch(error => console.error('Error updating checkout chart:', error));
    }

    // Function to update value chart based on time period
    function updateValueChart() {
        const selectedPeriod = document.getElementById('valueTimePeriod')?.value || 'daily';
        fetch(`get_chart_data.php?chart=value&period=${selectedPeriod}`)
            .then(response => response.json())
            .then(data => {
                if (valueChart) {
                    valueChart.data.labels = data.labels;
                    valueChart.data.datasets[0].data = data.values;
                    valueChart.update();
                }
            })
            .catch(error => console.error('Error updating value chart:', error));
    }
    
    const colors = {
        primary: '#4A90E2',
        secondary: '#50E3C2', 
        tertiary: '#F5A623',
        background: 'rgba(74, 144, 226, 0.1)',
        lowStock: '#E10F0F',
        normal: '#0F71E1'
    };
    
    let partsAddedData = <?php echo json_encode($partsAddedData); ?>;
    let checkoutData = <?php echo json_encode($checkoutData); ?>;
    
    // Sample data for testing if PHP variables aren't available
    if (!partsAddedData || Object.keys(partsAddedData).length === 0) {
        partsAddedData = {
            '2025-01-01': 12,
            '2025-01-05': 8,
            '2025-01-10': 15,
            '2025-01-15': 10,
            '2025-01-20': 7,
            '2025-02-01': 14,
            '2025-02-10': 9,
            '2025-02-20': 16,
            '2025-03-01': 11,
            '2025-03-10': 13
        };
    }
    
    if (!checkoutData || Object.keys(checkoutData).length === 0) {
        checkoutData = {
            '2025-01-02': 5,
            '2025-01-07': 7,
            '2025-01-12': 3,
            '2025-01-18': 8,
            '2025-01-25': 6,
            '2025-02-05': 9,
            '2025-02-15': 4,
            '2025-02-25': 7,
            '2025-03-05': 8,
            '2025-03-15': 5
        };
    }
    
    // Create monthly summary data if not available
    const monthlySummary = {};
    Object.entries(partsAddedData).forEach(([date, count]) => {
        const month = date.substring(0, 7);
        if (!monthlySummary[month]) {
            monthlySummary[month] = { count: 0, quantity: 0 };
        }
        monthlySummary[month].count++;
        monthlySummary[month].quantity += count;
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        createStockLevelChart();
        updateLineChart();
        createCheckoutTrendChart();
        createValueChart();
        
        // Add event listener for time period selector
        const timePeriodSelect = document.getElementById('timePeriod');
        if (timePeriodSelect) {
            timePeriodSelect.addEventListener('change', updateLineChart);
        }
        
        // Setup modal events
        setupModalEvents();
    });

    function createStockLevelChart() {
    const stockCanvas = document.getElementById('stockLevelChart');
    if (!stockCanvas) return;

    // Get stock levels and category values from PHP
    let stockLevels = {};
    let categoryValues = {};
    try {
        stockLevels = <?php echo json_encode($stockLevels); ?>;
        categoryValues = <?php echo json_encode($categoryValues); ?>;
    } catch (e) {
        console.error('Error loading stock data:', e);
        return;
    }

    // Prepare data for visualization
    const categories = Object.keys(stockLevels);
    const quantities = Object.values(stockLevels);
    const values = categories.map(cat => categoryValues[cat] || 0);

    const stockLabels = Object.keys(stockLevels);
    const stockDataValues = Object.values(stockLevels);

    // Prepare data for low stock and normal stock
    const lowStockData = stockDataValues.map(qty => qty < 2 ? qty : 0); // Low stock values
    const normalStockData = stockDataValues.map(qty => qty >= 2 ? qty : 0); // Normal stock values

    const stockData = {
        labels: stockLabels,
        datasets: [
            {
                label: 'Low Stock',
                data: lowStockData,
                backgroundColor: colors.lowStock, // Red for low stock
                borderColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                stack: 'combined' // Stack this dataset
            },
            {
                label: 'Normal Stock',
                data: normalStockData,
                backgroundColor: colors.normal, // Blue for normal stock
                borderColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                stack: 'combined' // Stack this dataset
            }
        ]
    };

    stockChart = new Chart(stockCanvas.getContext('2d'), {
        type: 'bar',
        data: stockData,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity'
                    }
                },
                x: {
                    stacked: true, // Stack bars on the x-axis
                    ticks: {
                        autoSkip: true,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

    function updateLineChart() {
        const updatesCanvas = document.getElementById('recentUpdatesChart');
        if (!updatesCanvas) return;
        
        const selectedPeriod = document.getElementById('timePeriod')?.value || 'daily';

        if (partsAddedChart) {
            partsAddedChart.destroy();
        }

        const dates = Object.keys(partsAddedData);
        const counts = Object.values(partsAddedData);

        let processedData = {};

        if (selectedPeriod === 'daily') {
            processedData = partsAddedData;
        } 
        else if (selectedPeriod === 'weekly') {
            dates.forEach(date => {
                const momentDate = moment(date);
                const weekKey = momentDate.year() + '-W' + momentDate.week();
                if (!processedData[weekKey]) {
                    processedData[weekKey] = 0;
                }
                processedData[weekKey] += partsAddedData[date];
            });
        }
        else if (selectedPeriod === 'monthly') {
            dates.forEach(date => {
                const monthKey = date.substring(0, 7);
                if (!processedData[monthKey]) {
                    processedData[monthKey] = 0;
                }
                processedData[monthKey] += partsAddedData[date];
            });
        } 
        else if (selectedPeriod === 'yearly') {
            dates.forEach(date => {
                const yearKey = date.substring(0, 4);
                if (!processedData[yearKey]) {
                    processedData[yearKey] = 0;
                }
                processedData[yearKey] += partsAddedData[date];
            });
        }

        const chartLabels = Object.keys(processedData);
        const chartData = Object.values(processedData);

        partsAddedChart = new Chart(updatesCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Parts Added',
                    data: chartData,
                    backgroundColor: colors.background,
                    borderColor: colors.primary,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.primary,
                    pointRadius: 4
                }]
            },
            options: {
                scales: { 
                    y: { 
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Parts'
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: true, position: 'top' }
                }
            }
        });
    }

    function createCheckoutTrendChart() {
        const checkoutCanvas = document.getElementById('checkoutTrendChart');
        if (!checkoutCanvas) return;

        const checkoutLabels = Object.keys(checkoutData);
        const checkoutValues = Object.values(checkoutData);

        checkoutChart = new Chart(checkoutCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: checkoutLabels,
                datasets: [{
                    label: 'Parts Checked Out',
                    data: checkoutValues,
                    backgroundColor: 'rgba(15, 113, 225, 0.1)',
                    borderColor: colors.secondary,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.secondary,
                    pointRadius: 4
                }]
            },
            options: {
                scales: { 
                    y: { 
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    },
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'MMM D, YYYY',
                            displayFormats: {
                                day: 'MMM D'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: true, position: 'top' }
                }
            }
        });
    }

    function createValueChart() {
        const valueCanvas = document.getElementById('valueChart');
        if (!valueCanvas) return;

        // Get stock levels from PHP or use sample data
        let stockLevels = {};
        try {
            stockLevels = <?php echo json_encode($stockLevels); ?>;
        } catch (e) {
            // Sample data if PHP variables aren't available
            stockLevels = {
                "Processor": 15,
                "Memory": 28,
                "SSD": 12,
                "HDD": 8,
                "Power Supply": 5,
                "Motherboard": 3,
                "Graphics Card": 6,
                "Case": 10,
                "Monitor": 7,
                "Keyboard": 25
            };
        }

        const categories = Object.keys(stockLevels);
        const valueData = Object.values(stockLevels).map((count, index) => {
            const avgPrice = Math.floor(Math.random() * 5000) + 1000;
            return count * avgPrice;
        });

        valueChart = new Chart(valueCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: categories,
                datasets: [{
                    label: 'Estimated Value (₱)',
                    data: valueData,
                    backgroundColor: colors.tertiary,
                    borderColor: 'rgba(0, 0, 0, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { 
                    y: { 
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Value (₱)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    function setupModalEvents() {
        const generateReportBtn = document.getElementById('generateReportBtn');
        if (generateReportBtn) {
            generateReportBtn.onclick = function() {
                document.getElementById('reportModal').style.display = 'block';
            }
        }
        
        const closeBtn = document.querySelector('.close');
        if (closeBtn) {
            closeBtn.onclick = function() {
                document.getElementById('reportModal').style.display = 'none';
            }
        }
        
        window.onclick = function(event) {
            const reportModal = document.getElementById('reportModal');
            if (reportModal && event.target == reportModal) {
                reportModal.style.display = 'none';
            }
        }
        
        const previewReportBtn = document.getElementById('previewReport');
        if (previewReportBtn) {
            previewReportBtn.onclick = generateReportPreview;
        }
        
        const exportExcelBtn = document.getElementById('exportExcel');
        if (exportExcelBtn) {
            exportExcelBtn.onclick = exportToExcel;
        }
        
        const exportPDFBtn = document.getElementById('exportPDF');
        if (exportPDFBtn) {
            exportPDFBtn.onclick = exportToPDF;
        }
    }

    function generateReportPreview() {
        // Get stock levels from PHP or use sample data
        let stockLevels = {};
        try {
            stockLevels = <?php echo json_encode($stockLevels); ?>;
        } catch (e) {
            // Sample data if PHP variables aren't available
            stockLevels = {
                "Processor": 15,
                "Memory": 28,
                "SSD": 12,
                "HDD": 8,
                "Power Supply": 5,
                "Motherboard": 3,
                "Graphics Card": 6,
                "Case": 10,
                "Monitor": 7,
                "Keyboard": 25
            };
        }
        
        // Get low stock items
        let lowStockResult = [];
        try {
            lowStockResult = <?php echo mysqli_num_rows($lowStockResult) > 0 ? json_encode(mysqli_fetch_all($lowStockResult, MYSQLI_ASSOC)) : '[]'; ?>;
        } catch (e) {
            // Sample data if PHP variables aren't available
            lowStockResult = [
                { PartID: "101", Name: "Graphics Card", Category: "Components", PartCondition: "New", Quantity: 1 },
                { PartID: "203", Name: "Power Supply", Category: "Components", PartCondition: "New", Quantity: 2 },
                { PartID: "305", Name: "Motherboard", Category: "Components", PartCondition: "Refurbished", Quantity: 1 }
            ];
        }
        
        // Get recent parts
        let recentPartsResult = [];
        try {
            recentPartsResult = <?php echo mysqli_num_rows($recentPartsResult) > 0 ? json_encode(mysqli_fetch_all($recentPartsResult, MYSQLI_ASSOC)) : '[]'; ?>;
        } catch (e) {
            // Sample data if PHP variables aren't available
            recentPartsResult = [
                { PartID: "567", Name: "SSD 1TB", Category: "Storage", PartCondition: "New", Quantity: 8 },
                { PartID: "621", Name: "Memory 16GB", Category: "Memory", PartCondition: "New", Quantity: 15 },
                { PartID: "489", Name: "Processor i7", Category: "Processor", PartCondition: "New", Quantity: 5 }
            ];
        }
        
        const selectedSections = Array.from(document.querySelectorAll('input[name="reportSection"]:checked')).map(checkbox => checkbox.value);
        const reportTitle = document.getElementById('reportTitle').value || "Inventory Report";
        const primaryColor = document.getElementById('reportPrimaryColor').value || "#4A90E2";
        const accentColor = document.getElementById('reportAccentColor').value || "#50E3C2";
        const includeLogo = document.getElementById('reportLogo')?.checked || false;
        const reportPreview = document.getElementById('reportPreview');
        
        if (!reportPreview) return;
        
        let previewContent = `
            <div style="font-family: 'Poppins', sans-serif; max-width: 100%;">
                ${includeLogo ? '<img src="../images/Drafter Black.png" style="height: 120px;">' : ''}
                <h2 style="color: ${primaryColor};">${reportTitle}</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
        `;
        
        // Add stock levels section if selected
        if (selectedSections.includes('stockLevels')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Stock Levels</h3>
                <div style="max-height: 150px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: ${primaryColor}; color: black;">
                            <th style="padding: 8px; text-align: left;">Part Name</th>
                            <th style="padding: 8px; text-align: right;">Quantity</th>
                        </tr>
                        ${Object.entries(stockLevels).map(([name, qty], index) => 
                            `<tr style="background-color: ${index % 2 === 0 ? '#f9f9f9' : 'white'};">
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${name}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${qty}</td>
                            </tr>`
                        ).join('')}
                    </table>
                </div>
            </div>`;
        }
        
        // Add low stock alerts section if selected
        if (selectedSections.includes('lowStock')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Low Stock Alerts</h3>
                <div style="max-height: 150px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: ${primaryColor}; color: black;">
                            <th style="padding: 8px; text-align: left;">Part ID</th>
                            <th style="padding: 8px; text-align: left;">Name</th>
                            <th style="padding: 8px; text-align: left;">Category</th>
                            <th style="padding: 8px; text-align: right;">Quantity</th>
                        </tr>
                        ${lowStockResult.length > 0 ? 
                            lowStockResult.map((row, index) => 
                                `<tr style="background-color: ${index % 2 === 0 ? '#f9f9f9' : 'black'};">
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">#${row.PartID}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">${row.Name}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">${row.Category}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${row.Quantity}</td>
                                </tr>`
                            ).join('') : 
                            '<tr><td colspan="4" style="padding: 8px; text-align: center;">No low stock items found.</td></tr>'
                        }
                    </table>
                </div>
            </div>`;
        }
        
        // Add recent updates section if selected
        if (selectedSections.includes('recentUpdates')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Recently Updated Parts</h3>
                <div style="max-height: 150px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: ${primaryColor}; color: black;">
                            <th style="padding: 8px; text-align: left;">Part ID</th>
                            <th style="padding: 8px; text-align: left;">Name</th>
                            <th style="padding: 8px; text-align: left;">Category</th>
                            <th style="padding: 8px; text-align: right;">Quantity</th>
                        </tr>
                        ${recentPartsResult.length > 0 ? 
                            recentPartsResult.map((row, index) => 
                                `<tr style="background-color: ${index % 2 === 0 ? '#f9f9f9' : 'white'};">
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">#${row.PartID}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">${row.Name}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">${row.Category}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${row.Quantity}</td>
                                </tr>`
                            ).join('') : 
                            '<tr><td colspan="4" style="padding: 8px; text-align: center;">No recent updates found.</td></tr>'
                        }
                    </table>
                </div>
            </div>`;
        }
        
        // Add chart previews if selected
        if (selectedSections.includes('partsAdded') && document.getElementById('recentUpdatesChart')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Parts Added Trend</h3>
                <div style="height: 200px; width: 100%;">
                    <img src="${partsAddedChart.toBase64Image()}" style="max-width: 100%; max-height: 200px;">
                </div>
            </div>`;
        }
        
        if (selectedSections.includes('checkoutTrend') && document.getElementById('checkoutTrendChart')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Checkout Trend</h3>
                <div style="height: 200px; width: 100%;">
                    <img src="${checkoutChart.toBase64Image()}" style="max-width: 100%; max-height: 200px;">
                </div>
            </div>`;
        }
        
        if (selectedSections.includes('valueByCategory') && document.getElementById('valueChart')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Value by Category</h3>
                <div style="height: 200px; width: 100%;">
                    <img src="${valueChart.toBase64Image()}" style="max-width: 100%; max-height: 200px;">
                </div>
            </div>`;
        }
        
        // Add monthly summary if selected
        if (selectedSections.includes('monthlySummary')) {
            previewContent += `<div style="margin-top: 20px;">
                <h3 style="color: ${accentColor};">Monthly Summary</h3>
                <div style="max-height: 150px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: ${primaryColor}; color: black;">
                            <th style="padding: 8px; text-align: left;">Month</th>
                            <th style="padding: 8px; text-align: right;">Transactions</th>
                            <th style="padding: 8px; text-align: right;">Total Quantity</th>
                        </tr>
                        ${Object.entries(monthlySummary).map(([month, data], index) => 
                            `<tr style="background-color: ${index % 2 === 0 ? '#f9f9f9' : 'white'};">
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${month}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${data.count}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${data.quantity}</td>
                            </tr>`
                        ).join('')}
                    </table>
                </div>
            </div>`;
        }
        
        previewContent += '</div>';
        reportPreview.innerHTML = previewContent;
    }

document.getElementById('exportExcel').onclick = function() {
    const selectedSections = Array.from(document.querySelectorAll('input[name="reportSection"]:checked')).map(checkbox => checkbox.value);
    const reportTitle = document.getElementById('reportTitle').value || "Inventory Report";
    
    const workbook = XLSX.utils.book_new();
    
    if (selectedSections.includes('stockLevels')) {
        let stockLevels = {};
        try {
            stockLevels = JSON.parse('<?php echo json_encode($stockLevels); ?>');
        } catch (e) {
            stockLevels = {
                "Processor": 15,
                "Memory": 28,
                "SSD": 12,
                "HDD": 8,
                "Power Supply": 5,
                "Motherboard": 3,
                "Graphics Card": 6,
                "Case": 10,
                "Monitor": 7,
                "Keyboard": 25
            };
        }
        
        const stockData = Object.entries(stockLevels).map(([name, qty]) => ({
            'Part Name': name,
            'Quantity': qty
        }));
        const stockSheet = XLSX.utils.json_to_sheet(stockData);
        XLSX.utils.book_append_sheet(workbook, stockSheet, "Stock Levels");
    }
    
    if (selectedSections.includes('partsAdded')) {
        const partsData = Object.entries(partsAddedData).map(([date, count]) => ({
            'Date': date,
            'Count': count
        }));
        const partsSheet = XLSX.utils.json_to_sheet(partsData);
        XLSX.utils.book_append_sheet(workbook, partsSheet, "Parts Added");
    }
    
    if (selectedSections.includes('checkoutTrend')) {
        const checkoutTrendData = Object.entries(checkoutData).map(([date, total]) => ({
            'Date': date,
            'Total Checked Out': total
        }));
        const checkoutSheet = XLSX.utils.json_to_sheet(checkoutTrendData);
        XLSX.utils.book_append_sheet(workbook, checkoutSheet, "Checkout Trend");
    }
    
    if (selectedSections.includes('lowStock')) {
        let lowStockResult = [];
        try {
            lowStockResult = JSON.parse('<?php echo mysqli_num_rows($lowStockResult) > 0 ? json_encode(mysqli_fetch_all($lowStockResult, MYSQLI_ASSOC)) : "[]"; ?>');
        } catch (e) {
            lowStockResult = [
                { PartID: "101", Name: "Graphics Card", Category: "Components", PartCondition: "New", Quantity: 1 },
                { PartID: "203", Name: "Power Supply", Category: "Components", PartCondition: "New", Quantity: 2 },
                { PartID: "305", Name: "Motherboard", Category: "Components", PartCondition: "Refurbished", Quantity: 1 }
            ];
        }
        
        const lowStockData = lowStockResult.map(row => ({
            'Part ID': row.PartID,
            'Name': row.Name,
            'Category': row.Category,
            'Condition': row.PartCondition,
            'Quantity': row.Quantity
        }));
        const lowStockSheet = XLSX.utils.json_to_sheet(lowStockData);
        XLSX.utils.book_append_sheet(workbook, lowStockSheet, "Low Stock");
    }
    
    if (selectedSections.includes('recentUpdates')) {
        let recentPartsResult = [];
        try {
            recentPartsResult = JSON.parse('<?php echo mysqli_num_rows($recentPartsResult) > 0 ? json_encode(mysqli_fetch_all($recentPartsResult, MYSQLI_ASSOC)) : "[]"; ?>');
        } catch (e) {
            recentPartsResult = [
                { PartID: "567", Name: "SSD 1TB", Category: "Storage", PartCondition: "New", Quantity: 8 },
                { PartID: "621", Name: "Memory 16GB", Category: "Memory", PartCondition: "New", Quantity: 15 },
                { PartID: "489", Name: "Processor i7", Category: "Processor", PartCondition: "New", Quantity: 5 }
            ];
        }
        
        const recentUpdatesData = recentPartsResult.map(row => ({
            'Part ID': row.PartID,
            'Name': row.Name,
            'Category': row.Category,
            'Condition': row.PartCondition,
            'Quantity': row.Quantity
        }));
        const recentUpdatesSheet = XLSX.utils.json_to_sheet(recentUpdatesData);
        XLSX.utils.book_append_sheet(workbook, recentUpdatesSheet, "Recent Updates");
    }
    
    if (selectedSections.includes('monthlySummary')) {
        const monthlySummaryData = Object.entries(monthlySummary).map(([month, data]) => ({
            'Month': month,
            'Transaction Count': data.count,
            'Total Quantity': data.quantity
        }));
        const monthlySummarySheet = XLSX.utils.json_to_sheet(monthlySummaryData);
        XLSX.utils.book_append_sheet(workbook, monthlySummarySheet, "Monthly Summary");
    }
    
    XLSX.writeFile(workbook, `${reportTitle.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}.xlsx`);
};
document.getElementById('exportPDF').onclick = function() {
    const selectedSections = Array.from(document.querySelectorAll('input[name="reportSection"]:checked')).map(checkbox => checkbox.value);
    const reportTitle = document.getElementById('reportTitle').value || "Inventory Report";
    const reportLayout = document.getElementById('reportLayout')?.value || "portrait";
    const primaryColor = document.getElementById('reportPrimaryColor')?.value || "#4A90E2";
    
    if (typeof jspdf === 'undefined' || typeof jspdf.jsPDF === 'undefined') {
        alert('PDF generation library not loaded. Please check your jsPDF inclusion.');
        return;
    }
    
    const doc = new jspdf.jsPDF({
        orientation: reportLayout,
        unit: 'mm',
        format: 'a4'
    });
    
    let yPos = 15;
    const rgbColor = hexToRgb(primaryColor);
    
    const logoImg = new Image();
    logoImg.src = '../images/Drafter Black.png'; 
    logoImg.onload = function() {
        const logoWidth = 50; 
        const logoHeight = 50; 
        const xPos = (doc.internal.pageSize.width - logoWidth) / 2; 
        doc.addImage(logoImg, 'PNG', xPos, yPos, logoWidth, logoHeight); 
        yPos += logoHeight + 10; 
        
        doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
        doc.setFontSize(18);
        doc.text(reportTitle, 15, yPos);
        
        yPos += 10;
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(`Generated on: ${new Date().toLocaleString()}`, 15, yPos);
        
        yPos += 15;

        function checkPageBreak(neededSpace = 30) {
            const pageHeight = doc.internal.pageSize.height;
            if (yPos + neededSpace > pageHeight - 15) {
                doc.addPage();
                yPos = 15;
                return true;
            }
            return false;
        }

        if (selectedSections.includes('stockLevels')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Stock Levels", 15, yPos);
            yPos += 8;

            const stockLevels = <?php echo json_encode($stockLevels); ?>;
            const stockData = Object.entries(stockLevels).map(([name, qty]) => [name, qty.toString()]);
            doc.autoTable({
                head: [['Part Name', 'Quantity']],
                body: stockData,
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;
        }

        if (selectedSections.includes('partsAdded')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Parts Added Over Time", 15, yPos);
            yPos += 8;

            const partsAddedData = <?php echo json_encode($partsAddedData); ?>;
            const partsData = Object.entries(partsAddedData).map(([date, count]) => [date, count.toString()]);
            doc.autoTable({
                head: [['Date', 'Parts Added']],
                body: partsData,
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;

            try {
                const partsAddedChartImg = partsAddedChart.toBase64Image();
                checkPageBreak(90);
                doc.addImage(partsAddedChartImg, 'PNG', 15, yPos, 180, 80);
                yPos += 90;
            } catch (e) {
                console.error('Error adding Parts Added chart:', e);
            }
        }

        if (selectedSections.includes('checkoutTrend')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Checkout Trend", 15, yPos);
            yPos += 8;

            const checkoutData = <?php echo json_encode($checkoutData); ?>;
            const checkoutTrendData = Object.entries(checkoutData).map(([date, total]) => [date, total.toString()]);
            doc.autoTable({
                head: [['Date', 'Total Checked Out']],
                body: checkoutTrendData,
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;

            try {
                const checkoutChartImg = checkoutChart.toBase64Image();
                checkPageBreak(90);
                doc.addImage(checkoutChartImg, 'PNG', 15, yPos, 180, 80);
                yPos += 90;
            } catch (e) {
                console.error('Error adding Checkout Trend chart:', e);
            }
        }

        if (selectedSections.includes('valueByCategory')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Inventory Value by Category", 15, yPos);
            yPos += 8;

            const valueByCategory = <?php echo json_encode($categoryBreakdown); ?>;
            const valueByCategoryData = Object.entries(valueByCategory).map(([category, count]) => [category, count.toString()]);
            doc.autoTable({
                head: [['Category', 'Count']],
                body: valueByCategoryData,
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;
        }

        if (selectedSections.includes('lowStock')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Low Stock Alerts", 15, yPos);
            yPos += 8;

            let lowStockResult = <?php echo mysqli_num_rows($lowStockResult) > 0 ? json_encode(mysqli_fetch_all($lowStockResult, MYSQLI_ASSOC)) : '[]'; ?>;
            const lowStockData = lowStockResult.map(row => [
                '#' + row.PartID,
                row.Name,
                row.Category,
                row.PartCondition,
                row.Quantity.toString()
            ]);
            doc.autoTable({
                head: [['Part ID', 'Name', 'Category', 'Condition', 'Quantity']],
                body: lowStockData.length > 0 ? lowStockData : [['No low stock items found.', '', '', '', '']],
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;
        }

        if (selectedSections.includes('recentUpdates')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Recently Updated Parts", 15, yPos);
            yPos += 8;

            let recentPartsResult = <?php echo mysqli_num_rows($recentPartsResult) > 0 ? json_encode(mysqli_fetch_all($recentPartsResult, MYSQLI_ASSOC)) : '[]'; ?>;
            const recentUpdatesData = recentPartsResult.map(row => [
                '#' + row.PartID,
                row.Name,
                row.Category,
                row.PartCondition,
                row.Quantity.toString()
            ]);
            doc.autoTable({
                head: [['Part ID', 'Name', 'Category', 'Condition', 'Quantity']],
                body: recentUpdatesData.length > 0 ? recentUpdatesData : [['No recent updates found.', '', '', '', '']],
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;
        }

        if (selectedSections.includes('monthlySummary')) {
            checkPageBreak(60);
            doc.setFontSize(14);
            doc.setTextColor(rgbColor.r, rgbColor.g, rgbColor.b);
            doc.text("Monthly Summary", 15, yPos);
            yPos += 8;

            let monthlySummary = <?php echo json_encode($monthlySummary); ?>;
            const monthlySummaryData = Object.entries(monthlySummary).map(([month, data]) => [
                month,
                data.count.toString(),
                data.quantity.toString()
            ]);
            doc.autoTable({
                head: [['Month', 'Transaction Count', 'Total Quantity']],
                body: monthlySummaryData,
                startY: yPos,
                theme: 'grid',
                headStyles: { fillColor: [rgbColor.r, rgbColor.g, rgbColor.b] },
                margin: { top: yPos }
            });
            yPos = doc.lastAutoTable.finalY + 15;
        }

        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Page ${i} of ${pageCount}`, doc.internal.pageSize.width / 2, doc.internal.pageSize.height - 10, {
                align: 'center'
            });
        }

        doc.save(`${reportTitle.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}.pdf`);
    };

    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
    }
};
function getTimeRangeText(range) {
    const rangeMap = {
        '7': 'Last 7 Days',
        '30': 'Last 30 Days',
        '90': 'Last 90 Days',
        '180': 'Last 6 Months',
        '365': 'Last Year',
        'all': 'All Time'
    };
    return rangeMap[range] || 'Last 30 Days';
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');

    // Save the sidebar state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

function checkSidebarState() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Apply the saved state on page load
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
    }
}

// Check the sidebar state when the page loads
document.addEventListener("DOMContentLoaded", function () {
    checkSidebarState();
});

</script>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}
.main-content {
    padding: 20px;
    transition: margin-left 0.3s;
}
.header {
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}
.header h1 {
    margin-bottom: 5px;
    color: #4A90E2;
}
.header p {
    color: #666;
    margin-top: 0;
}

#generateReportBtn {
    display: block;
    margin: 3rem auto 1rem;
    margin-right: 0;
    background-color: #E10F0F;
    font-family: 'Poppins', sans-serif;
    color: #fff;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
#generateReportBtn:hover {
    background-color: #c00d0d;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
#generateReportBtn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    animation: pulsate 1.5s infinite;
}
#generateReportBtn::after {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    transform: skewX(-45deg);
    transition: left 0.5s ease;
}
#generateReportBtn:hover::after {
    left: 100%;
}
@keyframes pulsate {
    0% { box-shadow: 0 0 0 0 rgba(225, 57, 57, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(225, 57, 57, 0); }
    100% { box-shadow: 0 0 0 0 rgba(225, 57, 57, 0); }
}

.metrics-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.metric-card {
    flex: 1;
    min-width: 200px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}
.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    background-color: #f8f8f8;
}

.metric-icon {
    font-size: 24px;
    color: #E10F0F;
    margin-bottom: 10px;
}

.metric-icon i {
    transition: transform 0.3s ease;
}

.metric-card:hover .metric-icon i {
    transform: scale(1.1);
}

.metric-card h2 {
    font-size: 16px;
    margin: 0 0 5px 0;
    color: #333;
}

.metric-value {
    font-size: 32px;
    font-weight: bold;
    color: #E10F0F;
}

.chart-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 20px;
}
.chart-box {
    flex: 1;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    position: relative;
    height: 300px;
}
.chart-box h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    text-align: center;
    color: #333;
}
.chart-controls {
    display: flex;
    justify-content: center;
    margin-bottom: 10px;
}
.transaction-history,
.low-stock-alerts,
.new-updated-parts {
    margin-top: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
}
.transaction-history h2,
.low-stock-alerts h2,
.new-updated-parts h2 {
    font-size: 20px;
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}
.table-responsive {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
th {
    background-color: #f4f4f4;
    font-weight: 600;
}
tr:hover {
    background-color: #f8f8f8;
}
.transaction-history table th:nth-child(4),
.transaction-history table td:nth-child(4),
.transaction-history table th:nth-child(5),
.transaction-history table td:nth-child(5),
.transaction-history table th:nth-child(6),
.transaction-history table td:nth-child(6) {
    text-align: right;
}
.transaction-history table th:nth-child(7),
.transaction-history table td:nth-child(7),
.low-stock-alerts table th:nth-child(6),
.low-stock-alerts table td:nth-child(6),
.new-updated-parts table th:nth-child(6),
.new-updated-parts table td:nth-child(6) {
    text-align: center;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
}
.report-sections {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}
.report-customization {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}
.report-preview {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.report-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}
.report-sections h3,
.customization-group h3,
.report-preview h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}
.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}
.checkbox-group label {
    display: flex;
    align-items: center;
    font-size: 14px;
    cursor: pointer;
}
.checkbox-group input {
    margin-right: 5px;
}
.report-customization {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.customization-group {
    flex: 1;
    min-width: 280px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}
.form-group input[type="text"],
.form-group input[type="email"],
.form-group select,
.form-group input[type="time"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: 'Poppins', sans-serif;
}
.preview-container {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.preview-placeholder {
    color: #888;
}
.report-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}
.action-button {
    padding: 10px 15px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}
.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.preview-button {
    background-color: #555;
    color: white;
}
.excel-button {
    background-color: #217346;
    color: white;
}
.pdf-button {
    background-color: #E10F0F;
    color: white;
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
button {
    background-color: #E10F0F;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}
button:hover {
    background-color: #c00d0d;
}
a {
    color: #E10F0F;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
.print-receipt-button {
    background-color: #E10F0F;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.2s;
}
.print-receipt-button:hover {
    background-color: #c00d0d;
    text-decoration: none;
}
select {
    padding: 6px 10px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: white;
    font-family: 'Poppins', sans-serif;
    min-width: 120px;
}
@media (max-width: 1024px) {
    .chart-container {
        flex-wrap: wrap;
    }
    .chart-box {
        flex: 0 0 100%;
        margin-bottom: 20px;
        height: 300px;
    }
}
@media (max-width: 768px) {
    .chart-container {
        flex-direction: column;
    }
    .chart-box {
        margin: 0 0 20px 0;
        width: 100%;
    }
    .transaction-history,
    .low-stock-alerts,
    .new-updated-parts {
        margin-top: 20px;
        padding: 15px;
    }
    table {
        font-size: 14px;
    }
    th, td {
        padding: 8px;
    }
    .header h1 {
        font-size: 24px;
    }
}
canvas {
    width: 100% !important;
    height: 250px !important;
}
</style>
</body>
</html>
