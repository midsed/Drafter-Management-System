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
            <h2>Stock Levels</h2>
            <canvas id="stockLevelChart"></canvas>
        </div>

        <div class="transaction-history">
            <h2>Recent Transaction History</h2>
            <table>
                <tr>
                    <th>Transaction ID</th>
                    <th>Action By</th>
                    <th>Timestamp</th>
                    <th>Print</th>
                </tr>
                <tr>
                    <td>#7676</td>
                    <td>Admin - Jade</td>
                    <td>2024-11-15 10:00:00</td>
                    <td><button>Print</button></td>
                </tr>
                <tr>
                    <td>#7677</td>
                    <td>Staff - Name N.</td>
                    <td>2024-11-3 10:00:00</td>
                    <td><button>Print</button></td>
                </tr>
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
                        <td><a href="parts.php?part_id=<?php echo $row['PartID']; ?>">More Details</a></td>
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

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('stockLevelChart').getContext('2d');
        const stockLevelChart = new Chart(ctx, {
            type: 'bar', // You can change this to 'line', 'pie', etc.
            data: {
                labels: ['Inverter', 'Battery', 'Part 3', 'Part 4', 'Part 5'], // Example labels
                datasets: [{
                    label: 'Stock Levels',
                    data: [21, 32, 15, 10, 25], // Example data
                    backgroundColor: 'rgb(59, 59, 59)', // Black with some transparency
                    borderColor: 'rgb(0, 0, 0)', // Red border
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    });
</script>