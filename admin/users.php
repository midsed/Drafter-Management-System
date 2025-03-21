<?php
session_start();
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Admin') { 
    header("Location: /Drafter-Management-System/login.php"); 
    exit(); 
} 

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

// Grab search
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';

// Grab role filter (single dropdown)
$role = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';

// Sorting
$sortField = isset($_GET['sort_field']) ? $conn->real_escape_string($_GET['sort_field']) : 'UserID';
$sortOrder = isset($_GET['sort_order']) ? $conn->real_escape_string($_GET['sort_order']) : 'DESC';

// Pagination
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT 
            UserID, 
            CONCAT(FName, ' ', LName) AS Name, 
            RoleType, 
            Email, 
            Status, 
            LastLogin 
        FROM user
        WHERE 1=1";

// Search filter
if (!empty($search)) {
    $sql .= " AND (FName LIKE '%$search%' 
                   OR LName LIKE '%$search%' 
                   OR RoleType LIKE '%$search%' 
                   OR Email LIKE '%$search%')";
}

// Role filter
if (!empty($role)) {
    $sql .= " AND RoleType = '$role'";
}

// Order / sort
$sql .= " ORDER BY $sortField $sortOrder LIMIT $limit OFFSET $offset";

// Get total records (for pagination)
$countSql = "SELECT COUNT(*) as totalCount FROM user WHERE 1=1";
if (!empty($search)) {
    $countSql .= " AND (FName LIKE '%$search%' 
                        OR LName LIKE '%$search%' 
                        OR RoleType LIKE '%$search%' 
                        OR Email LIKE '%$search%')";
}
if (!empty($role)) {
    $countSql .= " AND RoleType = '$role'";
}
$countResult  = $conn->query($countSql);
$totalRow     = $countResult->fetch_assoc();
$totalRecords = $totalRow['totalCount'];
$totalPages   = ceil($totalRecords / $limit);

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Users</title>
    <link rel="stylesheet" href="css/style.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="font-family: 'Poppins', sans-serif;">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back"
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Users</h1>
        <a href="usersadd.php" class="add-user-btn btn">+ Add User</a>
    </div>

    <!-- Search, Filter, and Sort aligned to the left -->
    <div class="search-actions">
        <!-- Search Container -->
        <div class="search-container">
            <input type="text" placeholder="Quick search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
        </div>

        <!-- Filter Container (Dropdown) -->
        <div class="filter-container">
            <span>Filter</span>
            <div class="dropdown">
                <button id="filterButton" class="filter-icon" title="Filter">
                    <i class="fas fa-filter"></i>
                </button>
                <div id="filterDropdown" class="dropdown-content">
                    <h4>Filter by Role</h4>
                    <select id="roleSelect">
                        <option value="">All</option>
                        <option value="Admin"  <?= $role === 'Admin'  ? 'selected' : '' ?>>Admin</option>
                        <option value="Staff"  <?= $role === 'Staff'  ? 'selected' : '' ?>>Staff</option>
                    </select>
                    <div class="filter-actions">
                        <button id="applyFilter" class="red-button">Apply</button>
                        <button id="clearFilter" class="clear-button">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sort Container (Dropdown) -->
        <div class="sort-container">
            <span>Sort By</span>
            <div class="dropdown">
                <button id="sortButton" class="sort-icon" title="Sort">
                    <i class="fas fa-sort-alpha-down"></i>
                </button>
                <div id="sortDropdown" class="dropdown-content">
                    <h4>Sort By</h4>
                    <select id="sortField">
                        <option value="UserID"    <?= $sortField === 'UserID'    ? 'selected' : '' ?>>User ID</option>
                        <option value="Name"      <?= $sortField === 'Name'      ? 'selected' : '' ?>>Name</option>
                        <option value="RoleType"  <?= $sortField === 'RoleType'  ? 'selected' : '' ?>>Role</option>
                        <option value="Email"     <?= $sortField === 'Email'     ? 'selected' : '' ?>>Email</option>
                        <option value="Status"    <?= $sortField === 'Status'    ? 'selected' : '' ?>>Status</option>
                        <option value="LastLogin" <?= $sortField === 'LastLogin' ? 'selected' : '' ?>>Last Login</option>
                    </select>
                    <div class="sort-options">
                        <button class="sort-option red-button" data-sort="asc">Ascending</button>
                        <button class="sort-option red-button" data-sort="desc">Descending</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>#{$row['UserID']}</td>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['RoleType']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                    echo "<td>" . ($row['LastLogin'] ? htmlspecialchars($row['LastLogin']) : 'Never') . "</td>";
                    echo "<td><a href='usersedit.php?UserID={$row['UserID']}' class='btn btn-edit'>Edit</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No users found.</td></tr>";
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

        $visiblePages = 5;
        $startPage    = max(1, $page - 2);
        $endPage      = min($totalPages, $startPage + $visiblePages - 1);

        if ($endPage - $startPage < $visiblePages - 1) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }

        // First button
        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=1" class="pagination-button">First</a>';
        }

        // Previous button
        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-button">Previous</a>';
        }

        // Page number buttons
        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page) ? 'active-page' : '';
            echo '<a href="?' . $queryString . '&page=' . $i . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
        }

        // Next button
        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-button">Next</a>';
        }

        // Last button
        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-button">Last</a>';
        }
        ?>
    </div>
</div>

<script>
// SEARCH 
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
            document.getElementById("userTableBody").innerHTML = doc.getElementById("userTableBody").innerHTML;
            const newPagination = doc.querySelector(".pagination");
            if (newPagination) {
                document.querySelector(".pagination").innerHTML = newPagination.innerHTML;
            }
        })
        .catch(error => console.error("Error updating search results:", error));
});

// FILTER: ROLE 
document.getElementById("filterButton").addEventListener("click", function (event) {
    event.stopPropagation();
    document.getElementById("filterDropdown").classList.toggle("show");
});

document.getElementById("applyFilter").addEventListener("click", function () {
    const selectedRole = document.getElementById("roleSelect").value;
    const currentUrl    = new URL(window.location.href);

    if (selectedRole) {
        currentUrl.searchParams.set("role", selectedRole);
    } else {
        currentUrl.searchParams.delete("role");
    }
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

document.getElementById("clearFilter").addEventListener("click", function () {
    document.getElementById("roleSelect").value = "";
    const currentUrl = new URL(window.location.href);

    currentUrl.searchParams.delete("role");
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
});

// SORT 
document.getElementById("sortButton").addEventListener("click", function (event) {
    event.stopPropagation();
    document.getElementById("sortDropdown").classList.toggle("show");
});

document.querySelectorAll(".sort-option").forEach(option => {
    option.addEventListener("click", function () {
        const selectedSort = this.dataset.sort;
        const currentUrl   = new URL(window.location.href);

        currentUrl.searchParams.set("sort_field", document.getElementById("sortField").value);
        currentUrl.searchParams.set("sort_order", selectedSort === 'asc' ? 'ASC' : 'DESC');
        currentUrl.searchParams.set("page", "1");
        window.location.href = currentUrl.toString();
    });
});

// Close dropdowns if clicking outside them
window.addEventListener("click", function (event) {
    if (!event.target.closest(".dropdown-content") && 
        !event.target.closest(".filter-icon") && 
        !event.target.closest(".sort-icon")) {
        document.getElementById("filterDropdown").classList.remove("show");
        document.getElementById("sortDropdown").classList.remove("show");
    }
});
</script>

<style>
body, button, select, input, a {
    font-family: 'Poppins', sans-serif;
}

.add-user-btn {
    background-color:  #32CD32;
    color: white !important;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: auto;
    text-decoration: none;
    font-size: 14px;
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
    transition: color 0.3s ease;
    background: none;
    border: none;
    cursor: pointer;
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
    min-width: 220px;
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
    margin: 0 0 10px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

#roleSelect, #sortField {
    width: 100%;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    margin-bottom: 10px;
}

/* Filter & Sort actions */
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

.supplier-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.supplier-table th, .supplier-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.supplier-table th {
    background-color: #f2f2f2;
    font-weight: 600;
}

.supplier-table tr:hover {
    background-color: rgb(218, 218, 218);
}

.supplier-table th:nth-child(7),
.supplier-table td:nth-child(7) {
    text-align: center;
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

</body>
</html>
