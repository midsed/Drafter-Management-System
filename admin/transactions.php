<?php
include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
    <img src="https://i.ibb.co/J3LX32C/back.png" alt="Back" style="width: 20px; height: 20px; margin-right: 25px;">
        <h1>Transaction History</h1>
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
    .container {
        margin: 20px;
    }

    .filter-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .print-button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background-color: #E10F0F;
        color: white;
        cursor: pointer;
        margin-left: 10px;
    }

    .filter-button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background-color:rgb(218, 218, 218);
        color: black;
        cursor: pointer;
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
        border: none;
        border-radius: 3px;
        background-color: #E10F0F;
        color: white;
        cursor: pointer;
        
    }
</style>