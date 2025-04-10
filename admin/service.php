<?php 
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   
include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$types  = isset($_GET['type']) ? explode(',', $_GET['type']) : [];
$staffs = isset($_GET['staff']) ? explode(',', $_GET['staff']) : [];
$sort   = isset($_GET['sort']) ? $_GET['sort'] : '';

$limit  = 20;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql      = "SELECT ServiceID, Type, Price, ClientEmail, StaffName, PartName FROM service WHERE Archived = 0";
$countSql = "SELECT COUNT(*) AS total FROM service WHERE Archived = 0";

if (!empty($types)) {
    $escapedTypes = array_map([$conn, 'real_escape_string'], $types);
    $sql      .= " AND Type IN ('" . implode("','", $escapedTypes) . "')";
    $countSql .= " AND Type IN ('" . implode("','", $escapedTypes) . "')";
}

if (!empty($staffs)) {
    $escapedStaffs = array_map([$conn, 'real_escape_string'], $staffs);
    $sql      .= " AND StaffName IN ('" . implode("','", $escapedStaffs) . "')";
    $countSql .= " AND StaffName IN ('" . implode("','", $escapedStaffs) . "')";
}

if (!empty($search)) {
    $sql      .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%' OR PartName LIKE '%$search%')";
    $countSql .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%' OR PartName LIKE '%$search%')";
}

$totalResult = $conn->query($countSql);
$totalRow    = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages   = ceil($totalRecords / $limit);

if ($sort === 'asc') {
    $sql .= " ORDER BY Type ASC";
} elseif ($sort === 'desc') {
    $sql .= " ORDER BY Type DESC";
} else {
    $sql .= " ORDER BY ServiceID DESC";
}

$sql .= " LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
    <!-- Header with Back Arrow & Title -->
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Service</h1>
    </div>

    <!-- Combined Toolbar: Left = Search, Filter, Sort; Right = Actions -->
    <div class="search-actions">
        <div class="left-group">
            <div class="search-container">
                <input type="text" placeholder="Quick search" id="searchInput">
            </div>
            <div class="filter-container">
                <span>Filter</span>
                <div class="dropdown">
                    <button id="filterButton" class="filter-icon">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content">
                        <div class="filter-options">
                            <h4>Service Type</h4>
                            <?php
                            $typeQuery = "SELECT DISTINCT Type FROM service WHERE Archived = 0";
                            $typeResult = $conn->query($typeQuery);
                            while ($type = $typeResult->fetch_assoc()) {
                                $checked = in_array($type['Type'], $types) ? "checked" : "";
                                echo "<label><input type='checkbox' class='filter-option' data-filter='type' value='{$type['Type']}' {$checked}> {$type['Type']}</label>";
                            }
                            ?>
                        </div>
                        <div class="filter-options">
                            <h4>Name</h4>
                            <?php
                            $staffQuery = "SELECT DISTINCT StaffName FROM service WHERE Archived = 0";
                            $staffResult = $conn->query($staffQuery);
                            while ($staff = $staffResult->fetch_assoc()) {
                                $checked = in_array($staff['StaffName'], $staffs) ? "checked" : "";
                                echo "<label><input type='checkbox' class='filter-option' data-filter='staff' value='{$staff['StaffName']}' {$checked}> {$staff['StaffName']}</label>";
                            }
                            ?>
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
                    <button id="sortButton" class="sort-icon">
                        <i class="fas fa-sort-alpha-down"></i>
                    </button>
                    <div id="sortDropdown" class="dropdown-content">
                        <button class="sort-option red-button" data-sort="asc">Ascending</button>
                        <button class="sort-option red-button" data-sort="desc">Descending</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Right Group: Action Buttons (pushed down with a top margin) -->
        <div class="actions">
            <a href="servicearchive.php" class="btn btn-archive">Archives</a>
            <a href="serviceadd.php" class="btn btn-add">+ Add Service</a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Service Type</th>
                    <th>Service Price</th>
                    <th>Customer Email</th>
                    <th>Staff</th>
                    <th>Part Name</th>
                    <th>Edit Service</th>
                    <th>Archive</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['ServiceID']) . "</td>
                            <td>" . htmlspecialchars($row['Type']) . "</td>
                            <td>" . htmlspecialchars($row['Price']) . "</td>
                            <td>" . htmlspecialchars($row['ClientEmail'] ?? 'N/A') . "</td>
                            <td>" . htmlspecialchars($row['StaffName'] ?? 'N/A') . "</td>
                            <td>" . htmlspecialchars($row['PartName']) . "</td>
                            <td><a href='serviceedit.php?id=" . $row['ServiceID'] . "' class='btn btn-edit'>Edit</a></td>
                            <td><button class='btn btn-archive' onclick='archiveService(" . $row['ServiceID'] . ")'>Archive</button></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No services found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="pagination">
    <?php 
    $queryParams = $_GET;
    unset($queryParams['page']);
    $queryString = http_build_query($queryParams);
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $page) ? 'active-page' : '';
        echo "<a href='?$queryString&page=$i' class='pagination-button $activeClass'>$i</a>";
    }
    ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
    body {
        font-family: 'Poppins', sans-serif;
    }
    .actions a.btn,
    .actions button.btn {
        color: white !important;
        text-decoration: none !important
    }
    .btn {
        font-family: 'Poppins', sans-serif;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        color: white;
    }
    .btn-archive, .btn-edit {
        background-color: #E10F0F;
    }
    .btn-add {
        background-color: #00A300 !important;
    }
    /* Toolbar combined into one row */
    .search-actions {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .left-group,
    .search-container,
    .filter-container,
    .sort-container {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    /* Actions: push down with top margin (only affecting the right group) */
    .actions {
        margin-top: 15px;
    }
    .actions .btn {
        margin-left: 10px;
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
        min-width: 300px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        padding: 15px;
        border-radius: 8px;
    }
    .dropdown-content.show {
        display: block;
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
    .filter-actions #clearFilter {
        background-color: #ccc;
        color: #333;
    }
    .filter-actions #clearFilter:hover {
        background-color: #bbb;
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
        margin-top: 20px;
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
        background-color: rgb(255, 255, 255);
    }
    tr:hover {
        background-color: rgb(218, 218, 218);
    }
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 350px;
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
    .sort-option {
        background-color: #E10F0F;
        color: white;
        border: none;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
        padding: 10px 20px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .sort-option:hover {
        background-color: darkred;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

document.getElementById("searchInput").addEventListener("input", function () {
    const searchValue = this.value.trim();
    const currentUrl = new URL(window.location.href);
    if (searchValue) {
        currentUrl.searchParams.set("search", searchValue);
    } else {
        currentUrl.searchParams.delete("search");
    }
    currentUrl.searchParams.set("page", "1");
    fetch(currentUrl.toString())
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById("logsTableBody").innerHTML = doc.getElementById("logsTableBody").innerHTML;
            document.querySelector(".pagination").innerHTML = doc.querySelector(".pagination")?.innerHTML || "";
        })
        .catch(error => console.error("Error updating search results:", error));
});

function archiveService(serviceID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to archive this service?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#32CD32",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, archive it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('archive_service.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `service_id=${serviceID}`
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Archived!",
                    text: data,
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#32CD32"
                }).then(() => location.reload());
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    title: "Error!",
                    text: "Something went wrong!",
                    icon: "error",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#d33"
                });
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown = document.getElementById("sortDropdown");
    const filterButton = document.getElementById("filterButton");
    const sortButton = document.getElementById("sortButton");
    const applyFilterButton = document.getElementById("applyFilter");
    const clearFilterButton = document.getElementById("clearFilter");
    const searchInput = document.getElementById("searchInput");

    filterButton.addEventListener("click", function (event) {
        event.stopPropagation();
        filterDropdown.classList.toggle("show");
        sortDropdown.classList.remove("show");
    });
    sortButton.addEventListener("click", function (event) {
        event.stopPropagation();
        sortDropdown.classList.toggle("show");
        filterDropdown.classList.remove("show");
    });
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".dropdown-content") && 
            !event.target.closest(".filter-icon") && 
            !event.target.closest(".sort-icon")) {
            filterDropdown.classList.remove("show");
            sortDropdown.classList.remove("show");
        }
    });
    
    applyFilterButton.addEventListener("click", function () {
        const selectedTypes = Array.from(document.querySelectorAll('.filter-option[data-filter="type"]:checked'))
            .map(checkbox => checkbox.value);
        const selectedStaff = Array.from(document.querySelectorAll('.filter-option[data-filter="staff"]:checked'))
            .map(checkbox => checkbox.value);
        const searchQuery = searchInput.value.trim();
        const queryParams = new URLSearchParams(window.location.search);
        queryParams.set("page", "1");
        if (selectedTypes.length > 0) {
            queryParams.set("type", selectedTypes.join(","));
        } else {
            queryParams.delete("type");
        }
        if (selectedStaff.length > 0) {
            queryParams.set("staff", selectedStaff.join(","));
        } else {
            queryParams.delete("staff");
        }
        if (searchQuery) {
            queryParams.set("search", searchQuery);
        } else {
            queryParams.delete("search");
        }
        window.location.search = queryParams.toString();
    });
    
    clearFilterButton.addEventListener("click", function () {
        window.location.href = window.location.pathname;
    });
    
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function () {
            const selectedSort = this.dataset.sort;
            const queryParams = new URLSearchParams(window.location.search);
            queryParams.set("sort", selectedSort);
            queryParams.set("page", "1");
            window.location.search = queryParams.toString();
        });
    });
});
</script>
