<?php
session_start();
date_default_timezone_set('Asia/Manila');

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
$limit  = 20;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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
    <style>
        /* Ensure dropdowns show when 'show' class is added */
        .dropdown-content.show {
            display: block !important;
        }
    </style>
</head>
<body style="font-family: 'Poppins', sans-serif;">

<div class="main-content">

    <!-- Header with ONLY Back Arrow & Title -->
    <div class="header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" 
                 alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Users</h1>
    </div>

    <!-- Combined Toolbar Row: Left = Search, Filter, Sort; Right = + Add User -->
    <div class="search-actions" 
         style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
         
        <!-- Left group: search, filter, sort -->
        <div class="left-group" style="display: inline-flex; align-items: center; gap: 20px;">
            <!-- Search Container -->
            <div class="search-container" style="display: flex; align-items: center; gap: 10px;">
                <input type="text" placeholder="Quick search" id="searchInput" 
                       value="<?= htmlspecialchars($search) ?>"
                       style="width: 300px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px;">
            </div>

            <!-- Filter Container (Dropdown) -->
            <div class="filter-container" style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <span style="font-size: 14px; color: #333;">Filter</span>
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <button id="filterButton" class="filter-icon" title="Filter"
                            style="color: #E10F0F; font-size: 20px; background: none; border: none; cursor: pointer;">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content"
                         style="display: none; position: absolute; background-color: #fff; min-width: 220px; box-shadow: 0px 4px 12px rgba(0,0,0,0.1); z-index: 1000; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0 0 10px; font-size: 16px; font-weight: 600; color: #333;">Filter by Role</h4>
                        <select id="roleSelect" 
                                style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; margin-bottom: 10px;">
                            <option value="">All</option>
                            <option value="Admin"  <?= $role === 'Admin'  ? 'selected' : '' ?>>Admin</option>
                            <option value="Staff"  <?= $role === 'Staff'  ? 'selected' : '' ?>>Staff</option>
                        </select>
                        <div class="filter-actions" 
                             style="display: flex; gap: 10px; justify-content: center;">
                            <button id="applyFilter" class="red-button" 
                                    style="background-color: #E10F0F; color: white; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                                Apply
                            </button>
                            <button id="clearFilter" class="clear-button" 
                                    style="background-color: #CCCCCC; color: black; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sort Container (Dropdown) -->
            <div class="sort-container" style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <span style="font-size: 14px; color: #333;">Sort By</span>
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <button id="sortButton" class="sort-icon" title="Sort"
                            style="color: #E10F0F; font-size: 20px; background: none; border: none; cursor: pointer;">
                        <i class="fas fa-sort-alpha-down"></i>
                    </button>
                    <div id="sortDropdown" class="dropdown-content"
                         style="display: none; position: absolute; background-color: #fff; min-width: 220px; box-shadow: 0px 4px 12px rgba(0,0,0,0.1); z-index: 1000; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0 0 10px; font-size: 16px; font-weight: 600; color: #333;">Sort By</h4>
                        <select id="sortField" 
                                style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; margin-bottom: 10px;">
                            <option value="UserID"    <?= $sortField === 'UserID'    ? 'selected' : '' ?>>User ID</option>
                            <option value="Name"      <?= $sortField === 'Name'      ? 'selected' : '' ?>>Name</option>
                            <option value="RoleType"  <?= $sortField === 'RoleType'  ? 'selected' : '' ?>>Role</option>
                            <option value="Email"     <?= $sortField === 'Email'     ? 'selected' : '' ?>>Email</option>
                            <option value="Status"    <?= $sortField === 'Status'    ? 'selected' : '' ?>>Status</option>
                            <option value="LastLogin" <?= $sortField === 'LastLogin' ? 'selected' : '' ?>>Last Login</option>
                        </select>
                        <div class="sort-options" style="display: flex; gap: 10px; justify-content: center;">
                            <button class="sort-option red-button" data-sort="asc"
                                    style="background-color: #E10F0F; color: white; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                                Ascending
                            </button>
                            <button class="sort-option red-button" data-sort="desc"
                                    style="background-color: #E10F0F; color: white; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                                Descending
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right group: + Add User (pushed down with margin-top) -->
        <div class="actions" style="margin-top: 15px;">
            <a href="usersadd.php" class="add-user-btn btn" 
               style="background-color: #00A300; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-size: 14px;">
               + Add User
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container" style="overflow-x: auto;">
        <table class="supplier-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background-color: #f2f2f2; font-weight: 600;">
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">User ID</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Name</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Role</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Email Address</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Status</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Last Login</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr style='cursor: pointer;'>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>#{$row['UserID']}</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['RoleType']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['Email']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['Status']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . ($row['LastLogin'] ? htmlspecialchars($row['LastLogin']) : 'Never') . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>
                            <a href='usersedit.php?UserID={$row['UserID']}' class='btn btn-edit' 
                               style='background-color: #E10F0F; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none;'>
                               Edit
                            </a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>No users found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px;">
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
            echo '<a href="?' . $queryString . '&page=1" class="pagination-button" 
                  style="padding:6px 12px; border-radius:4px; background:white; border:1px solid black; color:black;">First</a>';
        }

        // Previous button
        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-button" 
                  style="padding:6px 12px; border-radius:4px; background:white; border:1px solid black; color:black;">Previous</a>';
        }

        // Page number buttons
        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page) ? 'active-page' : '';
            $activeStyle = ($i == $page) ? 'background:black; color:white; font-weight:bold;' : '';
            echo '<a href="?' . $queryString . '&page=' . $i . '" 
                  class="pagination-button ' . $activeClass . '" 
                  style="padding:6px 12px; border-radius:4px; background:white; border:1px solid black; color:black; ' . $activeStyle . '">' 
                  . $i . '</a>';
        }

        // Next button
        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-button" 
                  style="padding:6px 12px; border-radius:4px; background:white; border:1px solid black; color:black;">Next</a>';
        }

        // Last button
        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-button" 
                  style="padding:6px 12px; border-radius:4px; background:white; border:1px solid black; color:black;">Last</a>';
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

    // Save the sidebar state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

function checkSidebarState() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Apply the saved state on page load
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
    }
}

// Check the sidebar state when the page loads
document.addEventListener("DOMContentLoaded", function () {
    checkSidebarState();
});

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

</body>
</html>
