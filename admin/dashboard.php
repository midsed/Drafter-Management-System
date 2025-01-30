<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

// Ensure the 'Username' key exists in session
if (!isset($_SESSION['Username'])) {
    $_SESSION['Username']; // Default value if not set
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
            <h2>Stock Levels</h2>
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
                <tr>
                    <td>#1</td>
                    <td>Inverter</td>
                    <td>cat1</td>
                    <td>Brand New</td>
                    <td>21</td>
                    <td><a href="#">More Details</a></td>
                </tr>
                <tr>
                    <td>#2</td>
                    <td>Battery</td>
                    <td>cat2</td>
                    <td>Brand New</td>
                    <td>32</td>
                    <td><a href="#">More Details</a></td>
                </tr>
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
                <tr>
                    <td>#1</td>
                    <td>Inverter</td>
                    <td>cat1</td>
                    <td>Brand New</td>
                    <td>21</td>
                    <td><a href="#">More Details</a></td>
                </tr>
                <tr>
                    <td>#2</td>
                    <td>Battery</td>
                    <td>cat2</td>
                    <td>Brand New</td>
                    <td>32</td>
                    <td><a href="#">More Details</a></td>
                </tr>
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
