<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}
include('dbconnect.php');
include('navigation/sidebar.php');
include('navigation/topbar.php');

$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$roleFilter = isset($_GET['role']) ? (array)$_GET['role'] : [];
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalQuery = "SELECT COUNT(*) AS total FROM logs l JOIN user u ON l.UserID = u.UserID WHERE 1=1";
if (!empty($search)) {
    $totalQuery .= " AND (u.Username LIKE '%$search%' OR l.ActionType LIKE '%$search%' OR l.Timestamp LIKE '%$search%')";
}
if (!empty($roleFilter)) {
    $roleFilterSQL = implode("','", $roleFilter);
    $totalQuery .= " AND RoleType IN ('$roleFilterSQL')";
}
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);

$sql = "SELECT l.LogsID, u.Username AS ActionBy, l.ActionType, l.Timestamp 
        FROM logs l
        JOIN user u ON l.UserID = u.UserID
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (u.Username LIKE '%$search%' OR l.ActionType LIKE '%$search%' OR l.Timestamp LIKE '%$search%')";
}
if (!empty($roleFilter)) {
    $sql .= " AND RoleType IN ('$roleFilterSQL')";
}
$sql .= " ORDER BY l.Timestamp DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Logs</h1>
    </div>

    <div class="search-actions">
        <div class="search-container">
            <input type="text" placeholder="Quick search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
            <div class="filter-container">
                <span>Filter by Role:</span>
                <label><input type="checkbox" class="role-filter" value="Admin" <?= in_array("Admin", $roleFilter) ? "checked" : "" ?>> Admin</label>
                <label><input type="checkbox" class="role-filter" value="Staff" <?= in_array("Staff", $roleFilter) ? "checked" : "" ?>> Staff</label>
                <button id="applyFilter" class="btn btn-filter">Apply</button>
            </div>
        </div>
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
            <tbody id="logsTableBody">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#{$row['LogsID']}</td>";
                        echo "<td>" . htmlspecialchars($row['ActionBy']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ActionType']) . "</td>";
                        echo "<td>" . date("F j, Y, g:i A", strtotime($row['Timestamp'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No logs found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php 
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams); 

        $visiblePages = 5;
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $startPage + $visiblePages - 1);

        if ($endPage - $startPage < $visiblePages - 1) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }
        ?>

        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>&page=1" class="pagination-button">First</a>
        <?php endif; ?>

        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?<?= $queryString ?>&page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>&page=<?= $totalPages ?>" class="pagination-button">Last</a>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById("searchInput").addEventListener("input", function () {
    const searchValue = this.value.trim();
    const currentUrl = new URL(window.location.href);

    if (searchValue) {
        currentUrl.searchParams.set("search", searchValue);
    } else {
        currentUrl.searchParams.delete("search");
    }

    currentUrl.searchParams.set("page", "1");
    window.history.replaceState({}, '', currentUrl.toString());

    fetch(currentUrl.toString())
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            document.getElementById("logsTableBody").innerHTML = doc.getElementById("logsTableBody").innerHTML;
            document.querySelector(".pagination").innerHTML = doc.querySelector(".pagination").innerHTML;
        })
        .catch(error => console.error("Error updating search results:", error));
});

document.getElementById("applyFilter").addEventListener("click", function () {
    const selectedRoles = Array.from(document.querySelectorAll('.role-filter:checked')).map(checkbox => checkbox.value);
    const currentUrl = new URL(window.location.href);

    if (selectedRoles.length > 0) {
        currentUrl.searchParams.set("role", selectedRoles);
    } else {
        currentUrl.searchParams.delete("role");
    }

    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});
</script>

<style>
body {
    font-family: 'Poppins', sans-serif;
}
.search-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.search-container {
    display: flex;
    align-items: center;
    gap: 10px;
}
.search-container input[type="text"] {
    width: 300px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}
.search-container input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
}
.filter-container {
    display: flex;
    align-items: center;
    gap: 10px;
}
.filter-container span {
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    color: #333;
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
.red-button {
    background: #E10F0F;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease;
}
.red-button:hover {
    background: darkred;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}
.pagination-button {
    padding: 6px 12px;
    border-radius: 4px;
    background: white;
    border: 1px solid black;
    color: black;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}
.pagination-button:hover {
    background: #f0f0f0;
}
.active-page {
    background: black;
    color: white;
    font-weight: bold;
}
</style>