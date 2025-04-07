<?php
session_start();
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

include('dbconnect.php');
include('navigation/sidebar.php');
include('navigation/topbar.php');
include('logging.php');

// Retrieve filter parameters
$search     = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$limit      = 10;
$page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset     = ($page - 1) * $limit;
$sortField  = isset($_GET['sort_field']) ? $conn->real_escape_string($_GET['sort_field']) : 'Timestamp';
$sortOrder  = isset($_GET['sort_order']) ? $conn->real_escape_string($_GET['sort_order']) : 'DESC';
$startDate  = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate    = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$roleFilter = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';

// For Action Type, expect an array from the checkboxes
$actionTypes = array();
if (isset($_GET['action_type'])) {
    if (is_array($_GET['action_type'])) {
        foreach ($_GET['action_type'] as $atype) {
            $actionTypes[] = $conn->real_escape_string($atype);
        }
    } else {
        $actionTypes[] = $conn->real_escape_string($_GET['action_type']);
    }
}

$sql = "SELECT l.LogsID, CONCAT(u.FName, ' ', u.LName, ' (', u.RoleType, ')') AS ActionBy, l.ActionType, l.Timestamp
        FROM logs l
        JOIN user u ON l.UserID = u.UserID
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (CONCAT(u.FName, ' ', u.LName, ' (', u.RoleType, ')') LIKE '%$search%'
                   OR l.ActionType LIKE '%$search%'
                   OR l.Timestamp LIKE '%$search%')";
}

// Apply the Action Type filter if any checkboxes are selected.
// Build multiple LIKE conditions combined with OR.
if (!empty($actionTypes)) {
    $likeClauses = array();
    foreach ($actionTypes as $atype) {
        $likeClauses[] = "l.ActionType LIKE '%$atype%'";
    }
    $sql .= " AND (" . implode(" OR ", $likeClauses) . ")";
}

// Filter by Role Type if chosen and not "All"
if (!empty($roleFilter) && $roleFilter !== "All") {
    $sql .= " AND u.RoleType = '$roleFilter'";
}

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND l.Timestamp BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}

$sql .= " ORDER BY $sortField $sortOrder LIMIT $limit OFFSET $offset";

$totalResult = $conn->query(str_replace("LIMIT $limit OFFSET $offset", "", $sql));
$totalLogs   = $totalResult->num_rows;
$totalPages  = ceil($totalLogs / $limit);

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logs</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="font-family: 'Poppins', sans-serif;">

<div class="main-content">
    <div class="header">
        <a href="dashboard.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
            <h1 style="margin: 0;">Logs</h1>
        </a>
    </div>

    <div class="search-actions">
        <div class="search-container">
            <input 
                type="text" 
                placeholder="Quick search" 
                id="searchInput" 
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>

        <div class="filter-container">
            <span>Filter</span>
            <div class="dropdown">
                <button id="filterButton" class="filter-icon" title="Filter">
                    <i class="fas fa-filter"></i>
                </button>
                <div id="filterDropdown" class="dropdown-content">
                    <h4>Date Range</h4>
                    <div class="date-filters">
                        <input type="date" id="start_date" value="<?= htmlspecialchars($startDate) ?>">
                        <input type="date" id="end_date" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <h4>User Role</h4>
                    <div class="role-filter">
                        <select id="role_filter">
                            <option value="">All</option>
                            <option value="admin" <?= ($roleFilter=="admin") ? "selected" : "" ?>>Admin</option>
                            <option value="staff" <?= ($roleFilter=="staff") ? "selected" : "" ?>>Staff</option>
                        </select>
                    </div>
                    <h4>Action Type</h4>
                    <div class="action-type-filter">
                        <label class="checkbox-label">
                            <input type="checkbox" name="action_type[]" value="Add" <?= (in_array("Add", $actionTypes)) ? "checked" : "" ?>>
                            Add
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="action_type[]" value="Update" <?= (in_array("Update", $actionTypes)) ? "checked" : "" ?>>
                            Update
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="action_type[]" value="Archive" <?= (in_array("Archive", $actionTypes)) ? "checked" : "" ?>>
                            Archive
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="action_type[]" value="Re-list" <?= (in_array("Re-list", $actionTypes)) ? "checked" : "" ?>>
                            Re-list
                        </label>
                    </div>
                    <div class="filter-actions">
                        <button id="applyFilter" class="red-button">Apply</button>
                        <button id="clearFilter" class="clear-button">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sort-container">
            <span>Sort By</span>
            <div class="dropdown">
                <button id="sortButton" class="sort-icon" title="Sort">
                    <i class="fas fa-sort-alpha-down"></i>
                </button>
                <div id="sortDropdown" class="dropdown-content">
                    <h4>Sort By</h4>
                    <select id="sortField">
                        <option value="LogsID"     <?= $sortField === 'LogsID'     ? 'selected' : '' ?>>Log ID</option>
                        <option value="ActionBy"   <?= $sortField === 'ActionBy'   ? 'selected' : '' ?>>Action By</option>
                        <option value="Timestamp"  <?= $sortField === 'Timestamp'  ? 'selected' : '' ?>>Timestamp</option>
                    </select>
                    <div class="sort-options">
                        <button class="sort-option red-button" data-sort="asc">Ascending</button>
                        <button class="sort-option red-button" data-sort="desc">Descending</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="download-container">
        <a href="download_logs.php?<?= http_build_query($_GET) ?>" class="red-button">Download Logs (Excel)</a>
    </div>
    <div class="table-container">
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Action By</th>
                    <th>Action Type</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['LogsID'] ?></td>
                        <td><?= htmlspecialchars($row['ActionBy']) ?></td>
                        <td><?= htmlspecialchars($row['ActionType']) ?></td>
                        <td><?= date("F j, Y, g:i A", strtotime($row['Timestamp'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No logs found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams);

        $visiblePages = 5; 
        $startPage    = max(1, $page - 2);
        $endPage      = min($totalPages, $startPage + $visiblePages - 1);

        if ($endPage - $startPage < $visiblePages - 1) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }

        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=1" class="pagination-button">First</a>';
            echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-button">Previous</a>';
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page) ? 'active-page' : '';
            echo '<a href="?' . $queryString . '&page=' . $i . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
        }

        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-button">Next</a>';
            echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-button">Last</a>';
        }
        ?>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

function checkSidebarState() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
    }
}

document.addEventListener("DOMContentLoaded", function () {
    checkSidebarState();
});

document.getElementById("searchInput").addEventListener("input", function () {
    const searchValue = this.value.trim();
    const currentUrl = new URL(window.location.href);
    if (searchValue) {
        currentUrl.searchParams.set("search", searchValue);
    } else {
        currentUrl.searchParams.delete("search");
    }
    currentUrl.searchParams.set("page", "1");
    currentUrl.searchParams.set("ajax", "1");
    fetch(currentUrl.toString())
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById("logsTableBody").innerHTML = doc.getElementById("logsTableBody").innerHTML;
            const newPagination = doc.querySelector(".pagination");
            if (newPagination) {
                document.querySelector(".pagination").innerHTML = newPagination.innerHTML;
            }
        })
        .catch(error => console.error("Error updating search results:", error));
});

// Sorting functionality
document.getElementById("sortButton").addEventListener("click", function(e) {
    e.stopPropagation();
    document.getElementById("sortDropdown").classList.toggle("show");
});

document.querySelectorAll(".sort-option").forEach(option => {
    option.addEventListener("click", function() {
        const sortVal = this.dataset.sort;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set("sort_field", document.getElementById("sortField").value);
        currentUrl.searchParams.set("sort_order", sortVal === 'asc' ? 'ASC' : 'DESC');
        currentUrl.searchParams.set("page", "1");
        window.location.href = currentUrl.toString();
    });
});

// Filter dropdown toggle
document.getElementById("filterButton").addEventListener("click", function(e) {
    e.stopPropagation();
    document.getElementById("filterDropdown").classList.toggle("show");
});

// Apply filter button: gather all selected filters including multiple action_type checkboxes
document.getElementById("applyFilter").addEventListener("click", function() {
    const startDate = document.getElementById("start_date").value;
    const endDate   = document.getElementById("end_date").value;
    
    // Get all checked action type checkboxes using the name "action_type[]"
    const actionTypeCheckboxes = document.querySelectorAll('input[name="action_type[]"]:checked');
    let actionTypeValues = [];
    actionTypeCheckboxes.forEach(cb => {
        actionTypeValues.push(cb.value);
    });
    
    const roleFilter = document.getElementById("role_filter").value;
    const currentUrl = new URL(window.location.href);
    
    // Set or remove date filters
    if (startDate && endDate) {
        currentUrl.searchParams.set("start_date", startDate);
        currentUrl.searchParams.set("end_date", endDate);
    } else {
        currentUrl.searchParams.delete("start_date");
        currentUrl.searchParams.delete("end_date");
    }
    
    // Remove any existing "action_type[]" parameters and then append each checked value
    currentUrl.searchParams.delete("action_type[]");
    if (actionTypeValues.length > 0) {
        actionTypeValues.forEach(value => {
            currentUrl.searchParams.append("action_type[]", value);
        });
    }
    
    // Set or remove the role filter
    if (roleFilter) {
        currentUrl.searchParams.set("role", roleFilter);
    } else {
        currentUrl.searchParams.delete("role");
    }
    
    // Reset page to 1 and redirect
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

// Clear filter button: uncheck all checkboxes and clear filters
document.getElementById("clearFilter").addEventListener("click", function() {
    document.getElementById("start_date").value = "";
    document.getElementById("end_date").value   = "";
    document.querySelectorAll('input[name="action_type[]"]:checked').forEach(cb => cb.checked = false);
    document.getElementById("role_filter").value = "";
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete("start_date");
    currentUrl.searchParams.delete("end_date");
    currentUrl.searchParams.delete("action_type[]");
    currentUrl.searchParams.delete("role");
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

// Hide dropdowns if clicking outside
window.addEventListener("click", function(e) {
    if (!e.target.closest(".dropdown-content") && 
        !e.target.closest(".filter-icon") && 
        !e.target.closest(".sort-icon")) {
        document.getElementById("filterDropdown").classList.remove("show");
        document.getElementById("sortDropdown").classList.remove("show");
    }
});
</script>

<style>
body, button, select, input, a {
    font-family: 'Poppins', sans-serif;
}

.header a {
    color: black;
}

.search-actions {
    display: flex;
    justify-content: flex-start; 
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.search-container input[type="text"] {
    width: 300px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.filter-container, .sort-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    margin-bottom: 8px;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 8px;
}

.filter-icon, .sort-icon {
    color: #E10F0F;
    font-size: 20px;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.3s ease;
}

.filter-icon:hover, .sort-icon:hover {
    color: darkred;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff; 
    min-width: 260px;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: 20px;
    border-radius: 8px;
    text-align: center; 
}

.dropdown-content.show {
    display: block;
}

.dropdown-content h4 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.date-filters {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.date-filters input[type="date"] {
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.dropdown-content select {
    width: 100%;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
    margin-bottom: 15px;
}

.filter-actions, .sort-options {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.red-button {
    background-color: #E10F0F;
    color: white;
    border: none;
    padding-bottom: 10px;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.red-button:hover {
    background-color: darkred;
}

.clear-button {
    background-color: #CCCCCC;
    color: black;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.clear-button:hover {
    background-color: #999999; 
}

.table-container {
    overflow-x: auto;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th, .logs-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.logs-table th {
    background-color: #f2f2f2;
    font-weight: 600;
}

.logs-table tr:hover {
    background-color: rgb(218, 218, 218);
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
a {
    text-decoration: none;
}
.logs-table th, .logs-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
</style>

</body>
</html>