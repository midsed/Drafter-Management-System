<?php session_start(); 
include('dbconnect.php'); 

if (!isset($_SESSION['UserID'])) { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

$lowStockQuery = "SELECT * FROM part WHERE Quantity < 2"; 
$lowStockResult = mysqli_query($conn, $lowStockQuery); 

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
                        ORDER BY r.RetrievedDate DESC LIMIT 5"; 
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

?> 

<?php include('navigation/sidebar.php'); ?> 
<?php include('navigation/topbar.php'); ?> 

<link rel="stylesheet" href="css/style.css"> 
<link rel="icon" type="image/x-icon" href="images/New Drafter Logo Cropped.png"> 

<div class="main-content"> 
    <div class="header"> 
        <h1>Dashboard</h1> 
        <p>Welcome to your parts inventory management dashboard</p>
    </div>

    <div class="content">
        <div class="chart-container">
            <div class="chart-box">
                <h2>Stock Levels</h2>
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
                <h2>Recent Checkouts (30 Days)</h2>
                <canvas id="checkoutTrendChart"></canvas>
            </div>
            <div class="chart-box">
                <h2>Inventory Value by Category</h2>
                <canvas id="valueChart"></canvas>
            </div>
        </div>

        <div class="transaction-history">
            <h2>Recent Checkout History</h2>
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
            $recentPartsQuery = "SELECT * FROM part ORDER BY LastUpdated DESC LIMIT 5"; // Limit to 5 most recent parts
            $recentPartsResult = mysqli_query($conn, $recentPartsQuery);
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>

<script>
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

    document.addEventListener('DOMContentLoaded', function() {
        createStockLevelChart();
        updateLineChart();
        createCheckoutTrendChart();
        createValueChart();
    });

    function createStockLevelChart() {
        const stockCanvas = document.getElementById('stockLevelChart');
        const stockLabels = <?php echo json_encode(array_keys($stockLevels)); ?>;
        const stockDataValues = <?php echo json_encode(array_values($stockLevels)); ?>;
        
        const backgroundColors = stockDataValues.map(qty => qty < 2 ? colors.lowStock : colors.normal);
        
        const stockData = {
            labels: stockLabels,
            datasets: [{
                label: 'Stock Levels',
                data: stockDataValues,
                backgroundColor: backgroundColors,
                borderColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1
            }]
        };

        new Chart(stockCanvas.getContext('2d'), {
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
                                return `Quantity: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    }

    function updateLineChart() {
        const updatesCanvas = document.getElementById('recentUpdatesChart');
        const selectedPeriod = document.getElementById('timePeriod').value;
        
        if (window.partsAddedChart) {
            window.partsAddedChart.destroy();
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
        
        window.partsAddedChart = new Chart(updatesCanvas.getContext('2d'), {
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
        
        const checkoutLabels = Object.keys(checkoutData);
        const checkoutValues = Object.values(checkoutData);
        
        new Chart(checkoutCanvas.getContext('2d'), {
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
        
        const categories = <?php echo json_encode(array_keys($stockLevels)); ?>;
        const valueData = <?php echo json_encode(array_values($stockLevels)); ?>.map((count, index) => {
            const avgPrice = Math.floor(Math.random() * 5000) + 1000;
            return count * avgPrice;
        });
        
        new Chart(valueCanvas.getContext('2d'), {
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

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
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