<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}
include('dbconnect.php');
include('navigation/sidebar.php');
include('navigation/topbar.php');

// Include the logging function
include('logging.php');

$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$limit = 10; // Results per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Sort and date range parameters
$sortField = isset($_GET['sort_field']) ? $conn->real_escape_string($_GET['sort_field']) : 'Timestamp'; // Default sort by Timestamp
$sortOrder = isset($_GET['sort_order']) ? $conn->real_escape_string($_GET['sort_order']) : 'DESC'; // Default sort order
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$actionType = isset($_GET['action_type']) ? $conn->real_escape_string($_GET['action_type']) : '';

// Build SQL query with search, sorting, and date range filtering
$sql = "SELECT l.LogsID, u.Username AS ActionBy, l.ActionType, l.Timestamp 
        FROM logs l
        JOIN user u ON l.UserID = u.UserID
        WHERE 1=1";

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (u.Username LIKE '%$search%' 
                  OR l.ActionType LIKE '%$search%' 
                  OR l.Timestamp LIKE '%$search%')";
}

// Apply action type filter
if (!empty($actionType)) {
    $sql .= " AND l.ActionType = '$actionType'";
}

// Apply date range filter
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND l.Timestamp BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}

// Apply sorting
$sql .= " ORDER BY $sortField $sortOrder LIMIT $limit OFFSET $offset";

// Get total logs count for pagination
$totalResult = $conn->query(str_replace("LIMIT $limit OFFSET $offset", "", $sql));
$totalLogs = $totalResult->num_rows;
$totalPages = ceil($totalLogs / $limit);

// Fetch logs
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="main-content">
    <div class="header">
        <a href="dashboard.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
            <h1 style="margin: 0;">Logs</h1>
        </a>
    </div>

    <div class="search-actions">
        <div class="search-container">
            <input type="text" placeholder="Quick search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
            
            <div class="filter-container">
                <span>Filter</span>
                <div class="dropdown">
                    <button id="filterButton" class="filter-icon" title="Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content">
                        <div class="date-filters">
                            <h4>Date Range</h4>
                            <input type="date" id="start_date" value="<?= htmlspecialchars($startDate) ?>">
                            <input type="date" id="end_date" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        <div class="filter-actions">
                            <button id="applyFilter" class="red-button">Apply</button>
                            <button id="clearFilter" class="red-button">Clear</button>
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
                        <h4>Sort By:</h4>
                        <select id="sortField">
                            <option value="LogsID" <?= $sortField === 'LogsID' ? 'selected' : '' ?>>Log ID</option>
                            <option value="ActionBy" <?= $sortField === 'ActionBy' ? 'selected' : '' ?>>Action By</option>
                            <option value="Timestamp" <?= $sortField === 'Timestamp' ? 'selected' : '' ?>>Timestamp</option>
                        </select>
                        <button class="sort-option red-button" data-sort="asc">Ascending</button>
                        <button class="sort-option red-button" data-sort="desc">Descending</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
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

    <!-- Pagination -->
    <div class="pagination">
        <?php
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams);

        $visiblePages = 5; // Number of pages to display
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $startPage + $visiblePages - 1);

        if ($endPage - $startPage < $visiblePages - 1) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }
        ?>

        <!-- First Button -->
        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>&page=1" class="pagination-button">First</a>
        <?php endif; ?>

        <!-- Previous Button -->
        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <!-- Page Number Buttons -->
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?<?= $queryString ?>&page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <!-- Next Button -->
        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
        <?php endif; ?>

        <!-- Last Button -->
        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>&page=<?= $totalPages ?>" class="pagination-button">Last</a>
        <?php endif; ?>
    </div>
</div>

<script>
// Search functionality
document.getElementById("searchInput").addEventListener("input", function () {
    const searchValue = this.value.trim();
    const currentUrl = new URL(window.location.href);

    if (searchValue) {
        currentUrl.searchParams.set("search", searchValue);
    } else {
        currentUrl.searchParams.delete("search");
    }

    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

// Sort functionality
document.getElementById("sortButton").addEventListener("click", function (event) {
    event.stopPropagation();
    document.getElementById("sortDropdown").classList.toggle("show");
});

document.querySelectorAll(".sort-option").forEach(option => {
    option.addEventListener("click", function () {
        const selectedSort = this.dataset.sort;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set("sort_field", document.getElementById("sortField").value);
        currentUrl.searchParams.set("sort_order", selectedSort === 'asc' ? 'ASC' : 'DESC');
        currentUrl.searchParams.set("page", "1");
        window.location.href = currentUrl.toString();
    });
});

// Filter functionality
document.getElementById("filterButton").addEventListener("click", function (event) {
    event.stopPropagation();
    document.getElementById("filterDropdown").classList.toggle("show");
});

document.getElementById("applyFilter").addEventListener("click", function () {
    const actionType = document.getElementById("actionType").value;
    const startDate = document.getElementById("start_date").value;
    const endDate = document.getElementById("end_date").value;
    const currentUrl = new URL(window.location.href);

    if (actionType) {
        currentUrl.searchParams.set("action_type", actionType);
    } else {
        currentUrl.searchParams.delete("action_type");
    }

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

// Clear filter functionality
document.getElementById("clearFilter").addEventListener("click", function () {
    document.getElementById("actionType").value = '';
    document.getElementById("start_date").value = '';
    document.getElementById("end_date").value = '';
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete("action_type");
    currentUrl.searchParams.delete("start_date");
    currentUrl.searchParams.delete("end_date");
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

// Close dropdowns when clicking outside
window.addEventListener("click", function (event) {
    if (!event.target.closest(".dropdown-content") && !event.target.closest(".filter-icon") && !event.target.closest(".sort-icon")) {
        document.getElementById("filterDropdown").classList.remove("show");
        document.getElementById("sortDropdown").classList.remove("show");
    }
});
</script>
</body>
</html>

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

.filter-container, .sort-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-icon, .sort-icon {
    color: #E10F0F;
    font-size: 20px;
    transition: color 0.3s ease;
    background: none;
    border: none;
    cursor: pointer;
}

.filter-icon:hover, .sort-icon:hover {
    color: darkred;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 500px;
    max-height: 500px;
    overflow-y: auto;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    padding: 15px;
    border-radius: 8px;
}

.dropdown-content.show {
    display: block;
}

.filter-section {
    margin-bottom: 15px;
}

.filter-section h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.filter-actions button {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    background-color: #E10F0F;
    color: white;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.filter-actions button:hover {
    background-color: darkred;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background-color:rgb(255, 255, 255);
}

tr:hover {
    background-color:rgb(218, 218, 218);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
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