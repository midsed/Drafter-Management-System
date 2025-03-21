<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}
include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');
?>

<?php
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$types = isset($_GET['type']) ? explode(',', $_GET['type']) : [];
$staffs = isset($_GET['staff']) ? explode(',', $_GET['staff']) : [];
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT ServiceID, Type, Price, ClientEmail, StaffName, PartName FROM service WHERE Archived = 0";
$countSql = "SELECT COUNT(*) AS total FROM service WHERE Archived = 0";

// Apply Filters
if (!empty($types)) {
    $escapedTypes = array_map([$conn, 'real_escape_string'], $types);
    $sql .= " AND Type IN ('" . implode("','", $escapedTypes) . "')";
    $countSql .= " AND Type IN ('" . implode("','", $escapedTypes) . "')";
}

if (!empty($staffs)) {
    $escapedStaffs = array_map([$conn, 'real_escape_string'], $staffs);
    $sql .= " AND StaffName IN ('" . implode("','", $escapedStaffs) . "')";
    $countSql .= " AND StaffName IN ('" . implode("','", $escapedStaffs) . "')";
}

if (!empty($search)) {
    $sql .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%' OR PartName LIKE '%$search%')";
    $countSql .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%' OR PartName LIKE '%$search%')";
}

// Get total results count
$totalResult = $conn->query($countSql);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

// Apply Sorting
if ($sort === 'asc') {
    $sql .= " ORDER BY Type ASC";
} elseif ($sort === 'desc') {
    $sql .= " ORDER BY Type DESC";
} else {
    $sql .= " ORDER BY ServiceID DESC";
}

if ($totalRecords > 10) {
    $sql .= " LIMIT $limit OFFSET $offset";
}

$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Service</h1>
        <div class="actions">
            <a href="servicearchive.php" class="btn btn-archive">Archives</a>
            <a href="serviceadd.php" class="btn btn-add">+ Add Service</a>
        </div>
    </div>

    <!-- Quick Search: Left-aligned input and search button -->
     <div class="search-actions">
        <div class="search-container">
            <input type="text" placeholder="Quick search" id="searchInput">
            
            <!-- Filter -->
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

            <!-- Sort -->
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
    </div>

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
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['ServiceID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Price']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ClientEmail'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['StaffName'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['PartName']) . "</td>";
                        echo "<td><a href='serviceedit.php?id=" . $row['ServiceID'] . "' class='btn btn-edit'>Edit</a></td>";
                        echo "<td><button class='btn btn-archive' onclick='archiveService(" . $row['ServiceID'] . ")'>Archive</button></td>";
                        echo "</tr>";
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

            $visiblePages = 5;
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $startPage + $visiblePages - 1);

            if ($endPage - $startPage < $visiblePages - 1) {
                $startPage = max(1, $endPage - $visiblePages + 1);
            }
        ?>

        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>&page=1" class="pagination-button">First</a>
            <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?<?= $queryString ?>&page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
            <a href="?<?= $queryString ?>&page=<?= $totalPages ?>" class="pagination-button">Last</a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
    .swal2-popup { font-family: "Inter", sans-serif !important; }
    .swal2-title { font-weight: 700 !important; }
    .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
    .swal2-confirm { background-color: #32CD32 !important; color: white !important; }
    .swal2-cancel { background-color: #d63031 !important; color: white !important; }
</style>

<script>
// Sidebar Toggle Function
function toggleSidebar() {
    document.querySelector('.sidebar')?.classList.toggle('collapsed');
    document.querySelector('.main-content')?.classList.toggle('collapsed');
}

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


// Archive Service 
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
                console.error('Error:', error);
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

    // Open/Close Filter and Sort Dropdowns
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

    // Apply Filter
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

    // Clear Filters
    clearFilterButton.addEventListener("click", function () {
        window.location.href = window.location.pathname;
    });

    // Apply Sorting
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function () {
            const selectedSort = this.dataset.sort;
            const queryParams = new URLSearchParams(window.location.search);
            queryParams.set("sort", selectedSort);
            queryParams.set("page", "1");

            window.location.search = queryParams.toString();
        });
    });

    // Live Search
    searchInput.addEventListener("input", function () {
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
                document.querySelector(".pagination").innerHTML = doc.querySelector(".pagination").innerHTML;
            })
            .catch(error => console.error("Error updating search results:", error));
    });
});
</script>


<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .actions a.btn,
    .actions button.btn {
        color: white !important;
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
    .btn-archive, .btn-add, .btn-edit {
        background-color: #E10F0F;
    }

    .actions {
        text-align: right;
        width: 100%;
    }
    .actions .btn {
        margin-left: 10px;
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

    /* Filter Actions */
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

    .search-container input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
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

    .sort-options {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.sort-option {
    background-color: #E10F0F; /* Red background */
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
    background-color: darkred; /* Darker red on hover */
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
</style>
