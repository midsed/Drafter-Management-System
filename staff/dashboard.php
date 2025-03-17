<?php
session_start();
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

$partsAddedQuery = "SELECT DATE(DateAdded) as date, COUNT(*) as count FROM part GROUP BY DATE(DateAdded)";
$partsAddedResult = mysqli_query($conn, $partsAddedQuery);
$partsAddedData = [];
while ($row = mysqli_fetch_assoc($partsAddedResult)) {
    $partsAddedData[$row['date']] = $row['count'];
}

$recentReceiptsQuery = "SELECT r.ReceiptID, 
                               CONCAT(r.RetrievedBy, ' (', u.RoleType, ')') AS RetrievedByRole,
                               r.RetrievedDate, 
                               r.PartID, 
                               r.Location, 
                               r.Quantity, 
                               r.DateAdded, 
                               p.Name AS PartName, 
                               p.Price AS PartPrice, 
                               s.ServiceID, 
                               s.Type AS ServiceType, 
                               s.Price AS ServicePrice
                        FROM receipt r
                        LEFT JOIN part p ON r.PartID = p.PartID
                        LEFT JOIN user u ON r.UserID = u.UserID
                        LEFT JOIN service s ON s.PartID = p.PartID 
                        ORDER BY r.RetrievedDate DESC LIMIT 5";

$recentReceiptsResult = mysqli_query($conn, $recentReceiptsQuery);

if (!$recentReceiptsResult) {
    die("SQL Error: " . mysqli_error($conn));
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
        <h1>Dashboard</h1>
    </div>

    <div class="content">
        <div class="chart-container">
            <div class="chart-box">
                <h2>Stock Levels</h2>
                <canvas id="stockLevelChart"></canvas>
            </div>
            <div class="chart-box">
                <h2>Parts Added</h2>
                <select id="timePeriod" onchange="updateLineChart()">
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <canvas id="recentUpdatesChart"></canvas>
            </div>
        </div>

        <div class="transaction-history">
            <h2>Recent Checkout History</h2>
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
                        if ($row['PartPrice']) $totalPrice += $row['PartPrice'];
                        if ($row['ServicePrice']) $totalPrice += $row['ServicePrice'];
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

        <div class="low-stock-alerts">
            <h2>Low Stock Alerts</h2>
            <table>
                <tr>
                    <th>Part ID</th>
                    <th>Part Name</th>
                    <th>Category</th>
                    <th>Condition</th>
                    <th>Quantity</th>
                    <th>Details</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($lowStockResult)) { ?>
                    <tr>
                        <td>#<?php echo $row['PartID']; ?></td>
                        <td><?php echo $row['Name']; ?></td>
                        <td><?php echo $row['Category']; ?></td>
                        <td><?php echo $row['PartCondition']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td><a href="partdetail.php?id=<?php echo $row['PartID']; ?>">More Details</a></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div class="new-updated-parts">
            <h2>New and Recently Updated Parts</h2>
            <table>
                <tr>
                    <th>Part ID</th>
                    <th>Part Name</th>
                    <th>Category</th>
                    <th>Condition</th>
                    <th>Quantity</th>
                    <th>Details</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($recentPartsResult)) { ?>
                    <tr>
                        <td>#<?php echo $row['PartID']; ?></td>
                        <td><?php echo $row['Name']; ?></td>
                        <td><?php echo $row['Category']; ?></td>
                        <td><?php echo $row['PartCondition']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td><a href="parts.php?part_id=<?php echo $row['PartID']; ?>">More Details</a></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let partsAddedData = <?php echo json_encode($partsAddedData); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const stockCanvas = document.getElementById('stockLevelChart');
        const stockLabels = <?php echo json_encode(array_keys($stockLevels)); ?>;
        const stockDataValues = <?php echo json_encode(array_values($stockLevels)); ?>;

        const backgroundColors = stockDataValues.map(qty => qty < 2 ? '#EE5D5D' : '#90B0DF');

        const stockData = {
            labels: stockLabels,
            datasets: [{
                label: 'Stock Levels',
                data: stockDataValues,
                backgroundColor: backgroundColors,
                borderColor: 'rgba(0, 0, 0, 1)',
                borderWidth: 1
            }]
        };

        new Chart(stockCanvas.getContext('2d'), {
            type: 'bar',
            data: stockData,
            options: {
                scales: { y: { beginAtZero: true } },
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: true, position: 'top' } }
            }
        });

        updateLineChart();
    });

    function updateLineChart() {
        const updatesCanvas = document.getElementById('recentUpdatesChart');
        const labels = Object.keys(partsAddedData);
        const data = Object.values(partsAddedData);

        const selectedPeriod = document.getElementById('timePeriod').value;
        let filteredData = [];
        let filteredLabels = [];

        if (selectedPeriod === 'daily') {
            filteredData = data; 
            filteredLabels = labels;
        } else if (selectedPeriod === 'monthly') {
            const monthlyData = {};
            labels.forEach(date => {
                const month = date.substring(0, 7);
                monthlyData[month] = (monthlyData[month] || 0) + partsAddedData[date];
            });
            filteredLabels = Object.keys(monthlyData);
            filteredData = Object.values(monthlyData);
        } else if (selectedPeriod === 'yearly') {
            const yearlyData = {};
            labels.forEach(date => {
                const year = date.substring(0, 4);
                yearlyData[year] = (yearlyData[year] || 0) + partsAddedData[date];
            });
            filteredLabels = Object.keys(yearlyData);
            filteredData = Object.values(yearlyData);
        }

        new Chart(updatesCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: filteredLabels,
                datasets: [{
                    label: 'Parts Added',
                    data: filteredData,
                    backgroundColor: '#90B0DF',
                    borderColor: '#90B0DF',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } },
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: true, position: 'top' } }
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
        background-color: #f8f9fa;
    }

    .main-content {
        padding: 20px;
    }

    .header {
        margin-bottom: 20px;
    }

    .chart-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .chart-box {
        flex: 1;
        margin: 0 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 15px;
        position: relative; /* Added for better positioning */
    }

    .chart-box h2 {
        margin-bottom: 15px;
        font-size: 18px;
        text-align: center;
    }

    .transaction-history,
    .low-stock-alerts,
    .new-updated-parts {
        margin-top: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 15px;
    }

    .transaction-history h2,
    .low-stock-alerts h2,
    .new-updated-parts h2 {
        font-size: 20px;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f4f4f4;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .transaction-history table th:nth-child(4),
    .transaction-history table td:nth-child(4) {
        text-align: center;
    }

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
    }

    button:hover {
        background-color: darkred;
    }

    a {
        color: #007bff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    select {
        margin-bottom: 10px;
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
        width: 100%;
    }
</style>