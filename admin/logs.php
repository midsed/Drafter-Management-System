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
include('logging.php'); // Make sure this file exists and contains the logAction function

// Example usage of logging actions
if (isset($_SESSION['UserID'])) {
    $userId = $_SESSION['UserID'];

    // Log actions based on your application logic
    // For example, you might log a user logging in or performing an action
    // logAction($conn, $userId, 'User  Logged In'); // Uncomment and use as needed
}

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
            <input type="text" placeholder="Quick search" id="searchInput">
            <button onclick="searchLogs()" class="red-button">Search</button>
            <div class="filter-container">
                <span>Filter</span>
                <div class="dropdown">
                    <button id="filterButton" class="filter-icon" title="Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content">
                        <div class="filter-section">
                            <h4>Action Types</h4>
                            <div class="filter-options">
                                <?php
                                // Fetch distinct action types from the database
                                $actionTypesResult = $conn->query("SELECT DISTINCT ActionType FROM logs");
                                while ($actionTypeRow = $actionTypesResult->fetch_assoc()) {
                                    echo '<label><input type="checkbox" class="filter-option" value="' . htmlspecialchars($actionTypeRow['ActionType']) . '"> ' . htmlspecialchars($actionTypeRow['ActionType']) . '</label>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="date-filters">
                            <input type="date" name="start_date" id="start_date" placeholder="From Date">
                            <input type="date" name="end_date" id="end_date" placeholder="To Date">
                        </div>
                        <div class="sort-container">
                            <span>Sort By</span>
                            <div class="dropdown">
                                <button id="sortButton" class="sort-icon" title="Sort">
                                    <i class="fas fa-sort"></i>
                                </button>
                                <div id="sortDropdown" class="dropdown-content">
                                    <button class="sort-option red-button" data-sort="asc">Ascending</button>
                                    <button class="sort-option red-button" data-sort="desc">Descending</button>
                                </div>
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button id="applyFilter" class="red-button">Apply</button>
                            <button id="clearFilter" class="red-button">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Logs Button -->
    <!-- Download Logs Button -->
<div class="action-buttons">
    <a href="download_logs.php?<?= http_build_query(array_intersect_key($_GET, array_flip(['action_type', 'username', 'start_date', 'end_date']))) ?>" class="red-button">Download Logs (CSV)</a>
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
                // Build the SQL query based on filters
                $sql = "SELECT l.LogsID, u.Username AS ActionBy, l.ActionType, l.Timestamp 
                        FROM logs l
                        JOIN user u ON l.UserID = u.UserID
                        WHERE 1=1";

                if (!empty($_GET['action_type'])) {
                    $actionType = $conn->real_escape_string($_GET['action_type']);
                    $sql .= " AND l.ActionType IN ('$actionType')";
                }

                if (!empty($_GET['username'])) {
                    $username = $conn->real_escape_string($_GET['username']);
                    $sql .= " AND u.Username LIKE '%$username%'";
                }

                if (!empty($_GET['start_date'])) {
                    $startDate = $conn->real_escape_string($_GET['start_date']);
                    $sql .= " AND l.Timestamp >= '$startDate'";
                }

                if (!empty($_GET['end_date'])) {
                    $endDate = $conn->real_escape_string($_GET['end_date']);
                    $sql .= " AND l.Timestamp <= '$endDate 23:59:59'";
                }

                $sql .= " ORDER BY l.Timestamp DESC";

                // Pagination
                $resultsPerPage = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $resultsPerPage;

                // Get total logs for pagination
                $result = $conn->query($sql);
                $totalLogs = $result->num_rows;
                $totalPages = ceil($totalLogs / $resultsPerPage);

                // Apply limit for pagination
                $sql .= " LIMIT $offset, $resultsPerPage";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#{$row['LogsID']}</td>";
                        echo "<td>" . htmlspecialchars($row['ActionBy']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ActionType']) . "</td>";
                        echo "<td>" . date("F j, Y, g:i A", strtotime($row['Timestamp'])) . "</td>"; // Format the timestamp
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No logs found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
    <?php
    // Preserve existing query parameters except for 'page'
    $queryParams = $_GET;
    unset($queryParams['page']); // Remove the existing page number
    $queryString = http_build_query($queryParams); // Build query string for filters
    ?>

<?php if ($page > 1): ?>
        <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?<?= $queryString ?>&page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
    <?php endif; ?>
</div>

<script>
        function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
// Search functionality
function searchLogs() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#logsTableBody tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

// Filter functionality
document.getElementById("applyFilter").addEventListener("click", function () {
    const selectedActions = Array.from(document.querySelectorAll('.filter-option:checked')).map(checkbox => checkbox.value);
    const actionTypeQuery = selectedActions.length > 0 ? selectedActions.join(',') : '';
    const username = document.getElementById("searchInput").value;
    const startDate = document.getElementById("start_date").value;
    const endDate = document.getElementById("end_date").value;

    // Redirect with filters applied
    window.location.href = `logs.php?action_type=${encodeURIComponent(actionTypeQuery)}&username=${encodeURIComponent(username)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
});

// Clear filter
document.getElementById("clearFilter").addEventListener("click", function () {
    document.querySelectorAll('.filter-option').forEach(checkbox => checkbox.checked = false);
    document.getElementById("searchInput").value = '';
    document.getElementById("start_date").value = '';
    document.getElementById("end_date").value = '';
    window.location.href = 'logs.php'; // Reload the page without filters
});

// Sort functionality
document.querySelectorAll(".sort-option").forEach(option => {
    option.addEventListener("click", function () {
        const order = option.dataset.sort;
        sortLogs(order);
        document.getElementById("sortDropdown").classList.remove("show");
    });
});

function sortLogs(order) {
    const rows = Array.from(document.querySelectorAll("#logsTableBody tr"));
    rows.sort((a, b) => {
        const timestampA = new Date(a.cells[3].textContent);
        const timestampB = new Date(b.cells[3].textContent);
        return order === "asc" ? timestampA - timestampB : timestampB - timestampA;
    });
    const tbody = document.getElementById("logsTableBody");
    tbody.innerHTML = "";
    rows.forEach(row => tbody.appendChild(row));
}

// Toggle filter dropdown
document.getElementById("filterButton").addEventListener("click", function (event) {
    event.stopPropagation();
    document.getElementById("filterDropdown").classList.toggle("show");
});

// Toggle sort dropdown
document.getElementById("sortButton").addEventListener("click", function (event) {
    event.stopPropagation();
    document.getElementById("sortDropdown").classList.toggle("show");
});

// Prevent dropdown from closing when interacting with its contents
document.getElementById("filterDropdown").addEventListener("click", function(event) {
    event.stopPropagation();
});

// Close dropdowns when clicking outside
window.addEventListener("click", function (event) {
    if (!event.target.matches('.filter-icon') && !event.target.matches('.sort-icon')) {
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

    .search-container input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
    }

    .filter-container, .sort-container {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }

    .filter-container span, .sort-container span {
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        color: #333;
    }

    .filter-icon, .sort-icon {
        color: #E10F0F;
        font-size: 20px;
        transition: color 0.3s ease;
    }

    .filter-icon:hover, .sort-icon:hover {
        color: darkred;
    }

    .date-filters {
        display: flex;
        gap: 10px;
    }

    .date-filters input[type="date"] {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
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
        font-family: 'Poppins', sans-serif;
        transition: background 0.3s ease;
        text-decoration: none;
    }

    .red-button:hover {
        background: darkred;
    }

    /* Dropdown Menu */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 500px; /* Increased width */
        max-height: 500px; /* Increased height */
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

    .filter-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-options label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #555;
        cursor: pointer;
    }

    .filter-options input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        position: sticky;
        bottom: 0;
        background: white;
        padding: 10px 0;
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

    /* Sort Buttons */
    .sort-option.red-button {
        display: block;
        width: 100%;
        text-align: left;
        margin: 5px 0;
        background-color: white; /* Changed to white */
        color: #E10F0F; /* Changed text color */
        border: 1px solid #E10F0F; /* Added border */
    }

    .sort-option.red-button:hover {
        background-color: #f0f0f0; /* Light gray on hover */
    }

    /* Pagination Styles */
    .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
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
    background: #000000;
    color: white;
    font-weight: bold;
}

</style>