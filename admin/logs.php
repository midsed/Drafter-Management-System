<?php 
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<?php include('dbconnect.php'); ?>
<link rel="stylesheet" href="css/style.css">

<!-- Import Poppins and FontAwesome if you need icons (optional) -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="dashboard.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
            <h1 style="margin: 0;">Logs</h1>
        </a>
    </div>
    
    <!-- Left-aligned quick search + Search button -->
    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
        <button class="red-button" onclick="searchTable()">Search</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Action By</th>
                    <th>Action Type</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT LogsID, ActionBy, ActionType, Timestamp FROM logs";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>#{$row['LogsID']}</td>";
                    echo "<td>" . htmlspecialchars($row['ActionBy']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ActionType']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Timestamp']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No logs found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .main-content {
        padding: 20px;
        margin-top: 70px;
    }

    .header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .search-container {
        text-align: left;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px; 
    }
    .search-container input {
        width: 250px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    .red-button {
        background: #E10F0F;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        transition: background 0.3s ease;
        text-decoration: none;
    }
    .red-button:hover {
        background: darkred;
    }

    .table-container {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #ccc;
    }
    th, td {
        padding: 10px;
        text-align: left; 
    }
</style>
