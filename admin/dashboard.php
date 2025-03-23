<?php session_start(); 
include('dbconnect.php'); 

if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
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
    <div class="header">
        <button id="generateReportBtn" class="report-button">Generate Report</button>
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

<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Customize Report</h2>
        <div class="report-sections">
            <h3>Select Sections to Include</h3>
            <div class="checkbox-group">
                <label><input type="checkbox" name="reportSection" value="stockLevels" checked> Stock Levels</label>
                <label><input type="checkbox" name="reportSection" value="partsAdded" checked> Parts Added Over Time</label>
                <label><input type="checkbox" name="reportSection" value="checkoutTrend" checked> Recent Checkouts</label>
                <label><input type="checkbox" name="reportSection" value="valueByCategory" checked> Inventory Value by Category</label>
                <label><input type="checkbox" name="reportSection" value="lowStock" checked> Low Stock Alerts</label>
                <label><input type="checkbox" name="reportSection" value="recentUpdates" checked> Recently Updated Parts</label>
                <label><input type="checkbox" name="reportSection" value="monthlySummary" checked> Monthly Transaction Summary</label>
            </div>
        </div>
        <div class="report-customization">
            <div class="customization-group">
                <h3>Layout & Style</h3>
                <div class="form-group">
                    <label for="reportTitle">Report Title:</label>
                    <input type="text" id="reportTitle" value="Inventory Dashboard Report">
                </div>
                <div class="form-group">
                    <label for="reportLayout">Page Layout:</label>
                    <select id="reportLayout">
                        <option value="landscape">Landscape</option>
                        <option value="portrait">Portrait</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reportPrimaryColor">Primary Color:</label>
                    <input type="color" id="reportPrimaryColor" value="#E10F0F">
                </div>
                <div class="form-group">
                    <label for="reportAccentColor">Accent Color:</label>
                    <input type="color" id="reportAccentColor" value="#4A90E2">
                </div>
                <div class="form-group">
                    <label for="reportLogo">Include Company Logo:</label>
                    <input type="checkbox" id="reportLogo" checked>
                </div>
            </div>
            <div class="customization-group">
                <h3>Data Options</h3>
                <div class="form-group">
                    <label for="dataTimeRange">Time Range:</label>
                    <select id="dataTimeRange">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last Year</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="chartStyle">Chart Style:</label>
                    <select id="chartStyle">
                        <option value="modern">Modern</option>
                        <option value="classic">Classic</option>
                        <option value="minimal">Minimal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="includeRawData">Include Raw Data Tables:</label>
                    <input type="checkbox" id="includeRawData" checked>
                </div>
            </div>
        </div>
        <div class="report-preview">
            <h3>Preview</h3>
            <div id="reportPreview" class="preview-container">
                <div class="preview-placeholder">
                    <p>Your report will include the selected sections with your customized styling.</p>
                    <p>The preview will update as you change options.</p>
                </div>
            </div>
        </div>
        <div class="report-actions">
            <button id="previewReport" class="action-button preview-button">Update Preview</button>
            <button id="exportExcel" class="action-button excel-button">Export to Excel</button>
            <button id="exportPDF" class="action-button pdf-button">Export to PDF</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    // Global chart objects for later reference
    let stockChart, partsAddedChart, checkoutChart, valueChart;
    
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
        
        const stockLabels = Object.keys(stockLevels);
        const stockDataValues = Object.values(stockLevels);

        const backgroundColors = stockDataValues.map(qty => qty < 5 ? colors.lowStock : colors.normal);

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
    
    const doc = new jspdf.jsPDF({
        orientation: reportLayout,
        unit: 'mm',
        format: 'a4'
    });
    
    let yPos = 15;
    
    doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
    doc.setFontSize(18);
    doc.text(reportTitle, 15, yPos);
    
    yPos += 10;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 15, yPos);
    
    yPos += 15;
    
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
        
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Stock Levels", 15, yPos);
        yPos += 8;
        
        const stockData = Object.entries(stockLevels).map(([name, qty]) => [name, qty.toString()]);
        doc.autoTable({
            head: [['Part Name', 'Quantity']],
            body: stockData,
            startY: yPos,
            theme: 'grid',
            headStyles: { fillColor: [hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b] },
            margin: { top: yPos }
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
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
        
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Low Stock Alerts", 15, yPos);
        yPos += 8;
        
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
            headStyles: { fillColor: [hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b] },
            margin: { top: yPos }
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
    }
    
    if (yPos > 270 && selectedSections.some(section => ['recentUpdates', 'checkoutTrend', 'valueByCategory', 'monthlySummary'].includes(section))) {
        doc.addPage();
        yPos = 15;
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
        
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Recently Updated Parts", 15, yPos);
        yPos += 8;
        
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
            headStyles: { fillColor: [hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b] },
            margin: { top: yPos }
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
    }
    
    if (selectedSections.includes('monthlySummary')) {
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Monthly Summary", 15, yPos);
        yPos += 8;
        
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
            headStyles: { fillColor: [hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b] },
            margin: { top: yPos }
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
    }
    
    if (selectedSections.includes('partsAdded') && partsAddedChart) {
        if (yPos > 220) {
            doc.addPage();
            yPos = 15;
        }
        
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Parts Added Trend", 15, yPos);
        yPos += 8;
        
        const chartImg = partsAddedChart.toBase64Image();
        doc.addImage(chartImg, 'PNG', 15, yPos, 180, 80);
        yPos += 90;
    }
    
    if (selectedSections.includes('checkoutTrend') && checkoutChart) {
        if (yPos > 220) {
            doc.addPage();
            yPos = 15;
        }
        
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Checkout Trend", 15, yPos);
        yPos += 8;
        
        const chartImg = checkoutChart.toBase64Image();
        doc.addImage(chartImg, 'PNG', 15, yPos, 180, 80);
        yPos += 90;
    }
    
    if (selectedSections.includes('valueByCategory') && valueChart) {
        if (yPos > 220) {
            doc.addPage();
            yPos = 15;
        }
        
        doc.setFontSize(14);
        doc.setTextColor(hexToRgb(primaryColor).r, hexToRgb(primaryColor).g, hexToRgb(primaryColor).b);
        doc.text("Value by Category", 15, yPos);
        yPos += 8;
        
        const chartImg = valueChart.toBase64Image();
        doc.addImage(chartImg, 'PNG', 15, yPos, 180, 80);
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
.report-button {
    background-color: rgb(230, 37, 37);
    color: white;
    border: none;
    padding: 20px 15px;
    border-radius: 5px;
    margin-bottom: 10px;
    margin-top: 60px;
    cursor: pointer;
    transition: background-color 0.3s;
}
.report-button:hover {
    background-color: rgb(116, 11, 11);
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
    width: 80%;
}
.report-sections,
.report-customization,
.report-preview,
.report-actions {
    margin-bottom: 20px;
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
    background-color:     #E10F0F;
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