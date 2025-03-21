<?php
session_start();
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] === 'Staff') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

include('dbconnect.php');
include('navigation/sidebar.php');
include('navigation/topbar.php');

// Include the logging function
include('logging.php');

// Grab query parameters
$search     = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$limit      = 10; // Results per page
$page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset     = ($page - 1) * $limit;

// Sorting & Date Range
$sortField  = isset($_GET['sort_field']) ? $conn->real_escape_string($_GET['sort_field']) : 'Timestamp';
$sortOrder  = isset($_GET['sort_order']) ? $conn->real_escape_string($_GET['sort_order']) : 'DESC';
$startDate  = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate    = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$actionType = isset($_GET['action_type']) ? $conn->real_escape_string($_GET['action_type']) : '';

// Build base query
$sql = "SELECT l.LogsID, u.Username AS ActionBy, l.ActionType, l.Timestamp
        FROM logs l
        JOIN user u ON l.UserID = u.UserID
        WHERE 1=1";

// Search filter
if (!empty($search)) {
    $sql .= " AND (u.Username LIKE '%$search%'
                   OR l.ActionType LIKE '%$search%'
                   OR l.Timestamp LIKE '%$search%')";
}

// Action Type filter (if used in the future)
if (!empty($actionType)) {
    $sql .= " AND l.ActionType = '$actionType'";
}

// Date range filter
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND l.Timestamp BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}

// Apply sorting
$sql .= " ORDER BY $sortField $sortOrder LIMIT $limit OFFSET $offset";

// Get total logs count for pagination
$totalResult = $conn->query(str_replace("LIMIT $limit OFFSET $offset", "", $sql));
$totalLogs   = $totalResult->num_rows;
$totalPages  = ceil($totalLogs / $limit);

// Fetch logs
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logs</title>
    <!-- Poppins font -->
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
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

    <!-- Search, Filter, Sort -->
    <div class="search-actions">
        <!-- Quick Search -->
        <div class="search-container">
            <input 
                type="text" 
                placeholder="Quick search" 
                id="searchInput" 
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>

        <!-- Filter (Date Range) -->
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
                    <div class="filter-actions">
                        <button id="applyFilter" class="red-button">Apply</button>
                        <!-- Clear button uses a separate class -->
                        <button id="clearFilter" class="clear-button">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sort By -->
        <div class="sort-container">
            <span>Sort By</span>
            <div class="dropdown">
                <button id="sortButton" class="sort-icon" title="Sort">
                    <i class="fas fa-sort-alpha-down"></i>
                </button>
                <div id="sortDropdown" class="dropdown-content">
                    <h4>Sort By</h4>
                    <!-- Show the native arrow -->
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

    <!-- Logs Table -->
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

    <!-- Pagination -->
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

        // First
        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=1" class="pagination-button">First</a>';
        }
        // Prev
        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-button">Previous</a>';
        }

        // Numbered pages
        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page) ? 'active-page' : '';
            echo '<a href="?' . $queryString . '&page=' . $i . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
        }

        // Next
        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-button">Next</a>';
        }
        // Last
        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-button">Last</a>';
        }
        ?>
    </div>
</div>

<!-- Scripts -->
<script>
// --- SEARCH ---
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
            // Update only the logs table body and pagination container
            document.getElementById("logsTableBody").innerHTML = doc.getElementById("logsTableBody").innerHTML;
            const newPagination = doc.querySelector(".pagination");
            if (newPagination) {
                document.querySelector(".pagination").innerHTML = newPagination.innerHTML;
            }
        })
        .catch(error => console.error("Error updating search results:", error));
});

// --- SORT ---
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

// --- FILTER: DATE RANGE ---
document.getElementById("filterButton").addEventListener("click", function(e) {
    e.stopPropagation();
    document.getElementById("filterDropdown").classList.toggle("show");
});

document.getElementById("applyFilter").addEventListener("click", function() {
    const startDate = document.getElementById("start_date").value;
    const endDate   = document.getElementById("end_date").value;
    const currentUrl = new URL(window.location.href);

    if (startDate && endDate) {
        currentUrl.searchParams.set("start_date", startDate);
        currentUrl.searchParams.set("end_date", endDate);
    } else {
        currentUrl.searchParams.delete("start_date");
        currentUrl.searchParams.delete("end_date");
    }
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

document.getElementById("clearFilter").addEventListener("click", function() {
    document.getElementById("start_date").value = "";
    document.getElementById("end_date").value   = "";
    
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete("start_date");
    currentUrl.searchParams.delete("end_date");
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

// Close dropdowns if clicking outside
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
/* Use Poppins font for everything */
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

/* Date Range inputs in Filter */
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

/* Pagination */
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

</body>
</html>
