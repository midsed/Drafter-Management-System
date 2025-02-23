<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}
?>

<?php
include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
    <h1>Receipts</h1>
    </div>

    <div class="container">
        <div class="filter-container">
            <button class="filter-button">Filter</button>
            <button class="print-button">Print Inventory Report</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Action by</th>
                    <th>Timestamp</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#7676</td>
                    <td>Admin - Name N.</td>
                    <td>2024-11-15 10:00:00</td>
                    <td><button class="print-receipt-button">Print</button></td>
                </tr>
                <tr>
                    <td>#7677</td>
                    <td>Staff - Name N.</td>
                    <td>2024-11-13 10:00:00</td>
                    <td><button class="print-receipt-button">Print</button></td>
                </tr>
                <tr>
                    <td>#7678</td>
                    <td>Admin - Name N.</td>
                    <td>2024-11-13 10:00:00</td>
                    <td><button class="print-receipt-button">Print</button></td>
                </tr>
                <tr>
                    <td>#7679</td>
                    <td>Admin - Name N.</td>
                    <td>2024-11-6 10:00:00</td>
                    <td><button class="print-receipt-button">Print</button></td>
                </tr>
            </tbody>
        </table>
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

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .container {
        margin: 20px;
    }

    .filter-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .print-button, .filter-button, .print-receipt-button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
    }

    .print-button {
        background-color: #E10F0F;
        color: white;
        margin-left: 10px;
    }

    .filter-button {
        background-color: rgb(218, 218, 218);
        color: black;
        margin-left: 10px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .print-receipt-button {
        padding: 5px 10px;
        border-radius: 3px;
        background-color: #E10F0F;
        color: white;
    }
</style>
