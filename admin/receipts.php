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
include('dbconnect.php');
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
                    <td><button class="print-receipt-button" data-receipt-id="#7676">Print</button></td>
                </tr>
                <tr>
                    <td>#7677</td>
                    <td>Staff - Name N.</td>
                    <td>2024-11-13 10:00:00</td>
                    <td><button class="print-receipt-button" data-receipt-id="#7677">Print</button></td>
                </tr>
                <tr>
                    <td>#7678</td>
                    <td>Admin - Name N.</td>
                    <td>2024-11-13 10:00:00</td>
                    <td><button class="print-receipt-button" data-receipt-id="#7678">Print</button></td>
                </tr>
                <tr>
                    <td>#7679</td>
                    <td>Admin - Name N.</td>
                    <td>2024-11-6 10:00:00</td>
                    <td><button class="print-receipt-button" data-receipt-id="#7679">Print</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="print-section" style="display: none;">
    <div class="receipt">
        <h2>Retrieve Part Receipt</h2>
        <p><strong>Transaction ID:</strong> <span id="receipt-id"></span></p>
        <p><strong>Action by:</strong> <span id="action-by"></span></p>
        <p><strong>Timestamp:</strong> <span id="timestamp"></span></p>
        <p><strong>Part:</strong></p>
        <ul id="item-list"></ul>
        <p><strong>Reason for Retrieval:</strong> To be used for service</p>
    </div>
</div>

<script>
    document.querySelector(".print-button").addEventListener("click", function () {
        window.print();
    });

    document.querySelectorAll(".print-receipt-button").forEach(button => {
        button.addEventListener("click", function () {
            const receiptId = this.getAttribute("data-receipt-id");
            const row = this.closest('tr');
            const actionBy = row.cells[1].innerText;
            const timestamp = row.cells[2].innerText;

            document.getElementById("receipt-id").innerText = receiptId;
            document.getElementById("action-by").innerText = actionBy;
            document.getElementById("timestamp").innerText = timestamp;

            const items = [
                "Part A",
                "Part B"
            ];
            const itemList = document.getElementById("item-list");
            itemList.innerHTML = "";
            items.forEach(item => {
                const li = document.createElement("li");
                li.innerText = item;
                itemList.appendChild(li);
            });

            document.getElementById("print-section").style.display = "block";
            document.querySelector(".main-content").style.display = "none";

            window.print();

            document.getElementById("print-section").style.display = "none";
            document.querySelector(".main-content").style.display = "block";
        });
    });
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

    th:nth-child(4),
    td:nth-child(4) {
        text-align: center;
    }
    
    .print-receipt-button {
        padding: 5px 10px;
        border-radius: 3px;
        background-color: #E10F0F;
        color: white;
    }

    #print-section {
        display: none;
        padding: 20px;
        border: 1px solid #ddd;
        margin: 20px;
        background-color: #fff;
    }

    .receipt {
        font-family: 'Poppins', sans-serif;
        text-align: left;
    }

    .receipt h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .receipt p {
        margin: 5px 0;
    }

    .receipt ul {
        list-style-type: none;
        padding: 0;
    }

    @media print {
        .main-content {
            visibility: hidden;
        }

        #print-section {
            display: block;
        }

        body {
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            page-break-after: avoid;
        }

        /* Hide sidebar and topbar during print */
        .sidebar, .topbar {
            display: none;
        }
    }
</style>