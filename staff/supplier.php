<?php
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

require_once "dbconnect.php"; // Include the database connection

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}

include('navigation/sidebar.php');
include('navigation/topbar.php');

// ----- GET PARAMETERS -----
$search    = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$partIDs   = isset($_GET['part']) ? explode(',', $_GET['part']) : [];
$companies = isset($_GET['company']) ? explode(',', $_GET['company']) : [];

$sort    = isset($_GET['sort']) ? $_GET['sort'] : '';
$limit   = 10; // pagination limit
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset  = ($page - 1) * $limit;

// ----- BASE QUERIES -----
$sql = "SELECT
            s.SupplierID,
            s.CompanyName,
            s.Email,
            s.PhoneNumber,
            p.PartID,
            p.Name AS PartName
        FROM supplier s
        LEFT JOIN part p ON s.SupplierID = p.SupplierID
        WHERE s.archived = 0 AND p.PartID IS NOT NULL";

$countSql = "SELECT COUNT(*) AS total
             FROM supplier s
             LEFT JOIN part p ON s.SupplierID = p.SupplierID
             WHERE s.archived = 0 AND p.PartID IS NOT NULL";

// ----- APPLY FILTERS -----
// (For this example, filtering is done by Company Name.)
if (!empty($companies)) {
    $escapedCompanies = array_map([$conn, 'real_escape_string'], $companies);
    $sql      .= " AND s.CompanyName IN ('" . implode("','", $escapedCompanies) . "')";
    $countSql .= " AND s.CompanyName IN ('" . implode("','", $escapedCompanies) . "')";
}

// ----- QUICK SEARCH -----
if (!empty($search)) {
    $sql .= " AND (s.CompanyName LIKE '%$search%'
                  OR s.Email LIKE '%$search%'
                  OR s.PhoneNumber LIKE '%$search%'
                  OR p.PartID LIKE '%$search%')";
    $countSql .= " AND (s.CompanyName LIKE '%$search%'
                        OR s.Email LIKE '%$search%'
                        OR s.PhoneNumber LIKE '%$search%'
                        OR p.PartID LIKE '%$search%')";
}

// ----- SORTING -----
if ($sort === 'asc') {
    $sql .= " ORDER BY s.CompanyName ASC";
} elseif ($sort === 'desc') {
    $sql .= " ORDER BY s.CompanyName DESC";
} else {
    $sql .= " ORDER BY s.SupplierID DESC";
}

$sql .= " LIMIT $limit OFFSET $offset";

// ----- EXECUTE QUERIES -----
$totalResult = $conn->query($countSql);
$totalRow    = $totalResult->fetch_assoc();
$totalPages  = ceil($totalRow['total'] / $limit);

$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">
    <!-- Header with Back Arrow & Title ONLY -->
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back"
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Supplier</h1>
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
                        <div class="filter-section">
                            <h4>Company Name</h4>
                            <div class="filter-options">
                                <?php
                                $companyQuery = "SELECT DISTINCT s.CompanyName
                                                 FROM supplier s
                                                 WHERE s.archived = 0
                                                 ORDER BY s.CompanyName";
                                $companyResult = $conn->query($companyQuery);
                                while ($cmp = $companyResult->fetch_assoc()) {
                                    $compName = htmlspecialchars($cmp['CompanyName']);
                                    echo "<label><input type='checkbox' class='filter-option' data-filter='company' value='{$compName}'> {$compName}</label>";
                                }
                                ?>
                            </div>
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

        <!-- Right Group: Action Buttons (Archives, + Add Supplier) -->
        <div class="actions">
            <a href="supplierarchive.php" class="btn btn-archive">Archives</a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Part Name</th>
                    <th>Email</th>
                    <th>Company Name</th>
                    <th>Phone Number</th>
                    <th>Edit Supplier</th>
                    <th>Archive</th>
                </tr>
            </thead>
            <tbody id="supplierTableBody">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['SupplierID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['PartName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['CompanyName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['PhoneNumber']) . "</td>";
                        echo "<td><a href='supplieredit.php?id=" . $row['SupplierID'] . "' class='btn btn-edit'>Edit</a></td>";
                        echo "<td><button class='btn btn-archive' onclick='archiveSupplier(" . $row['SupplierID'] . ")'>Archive</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No suppliers with associated parts found.</td></tr>";
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
        $startPage = max(1, $page - 2);
        $endPage   = min($totalPages, $startPage + $visiblePages - 1);

        if ($endPage - $startPage < $visiblePages - 1) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }
        ?>

        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>&page=1" class="pagination-button">First</a>
            <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?<?= $queryString ?>&page=<?= $i ?>"
               class="pagination-button <?= ($i == $page) ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>&page=<?= ($page + 1) ?>" class="pagination-button">Next</a>
            <a href="?<?= $queryString ?>&page=<?= $totalPages ?>" class="pagination-button">Last</a>
        <?php endif; ?>
    </div>
</div>

<!-- JS for Filter, Sort, Live Search, and Archive -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
function toggleSidebar() {
    document.querySelector('.sidebar')?.classList.toggle('collapsed');
    document.querySelector('.main-content')?.classList.toggle('collapsed');
}

// Real-time search
document.getElementById("searchInput").addEventListener("input", function() {
    const searchValue = this.value.trim();
    const currentUrl  = new URL(window.location.href);

    if (searchValue) {
        currentUrl.searchParams.set("search", searchValue);
    } else {
        currentUrl.searchParams.delete("search");
    }
    currentUrl.searchParams.set("page", "1");

    // Fetch updated content
    fetch(currentUrl.toString())
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById("supplierTableBody").innerHTML =
                doc.getElementById("supplierTableBody").innerHTML;
            document.querySelector(".pagination").innerHTML =
                doc.querySelector(".pagination").innerHTML;
        })
        .catch(error => console.error("Error updating search results:", error));
});

// Archive supplier with SweetAlert
function archiveSupplier(supplierID) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to archive this supplier!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#32CD32',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('archive_supplier.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + supplierID
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Archived!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while archiving the supplier.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown   = document.getElementById("sortDropdown");
    const filterButton   = document.getElementById("filterButton");
    const sortButton     = document.getElementById("sortButton");
    const applyFilterBtn = document.getElementById("applyFilter");
    const clearFilterBtn = document.getElementById("clearFilter");
    const searchInput    = document.getElementById("searchInput");

    // Toggle Filter dropdown
    filterButton.addEventListener("click", function(event) {
        event.stopPropagation();
        filterDropdown.classList.toggle("show");
        sortDropdown.classList.remove("show");
    });
    // Toggle Sort dropdown
    sortButton.addEventListener("click", function(event) {
        event.stopPropagation();
        sortDropdown.classList.toggle("show");
        filterDropdown.classList.remove("show");
    });
    // Close dropdowns if clicking outside
    document.addEventListener("click", function(event) {
        if (!event.target.closest(".dropdown-content") &&
            !event.target.closest(".filter-icon") &&
            !event.target.closest(".sort-icon")) {
            filterDropdown.classList.remove("show");
            sortDropdown.classList.remove("show");
        }
    });

    // Apply Filter
    applyFilterBtn.addEventListener("click", function() {
        const selectedParts = Array.from(document.querySelectorAll('.filter-option[data-filter="part"]:checked'))
            .map(checkbox => checkbox.value);
        const selectedCompanies = Array.from(document.querySelectorAll('.filter-option[data-filter="company"]:checked'))
            .map(checkbox => checkbox.value);

        const searchQuery = searchInput.value.trim();
        const queryParams = new URLSearchParams(window.location.search);

        queryParams.set("page", "1");
        if (selectedParts.length > 0) {
            queryParams.set("part", selectedParts.join(","));
        } else {
            queryParams.delete("part");
        }
        if (selectedCompanies.length > 0) {
            queryParams.set("company", selectedCompanies.join(","));
        } else {
            queryParams.delete("company");
        }
        if (searchQuery) {
            queryParams.set("search", searchQuery);
        } else {
            queryParams.delete("search");
        }

        window.location.search = queryParams.toString();
    });

    // Clear Filters
    clearFilterBtn.addEventListener("click", function() {
        window.location.href = window.location.pathname;
    });

    // Apply Sorting
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function() {
            const selectedSort = this.dataset.sort;
            const queryParams  = new URLSearchParams(window.location.search);

            queryParams.set("sort", selectedSort);
            queryParams.set("page", "1");
            window.location.search = queryParams.toString();
        });
    });
});
</script>

<script>
        // Check for the success flag in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');

        if (success === '1') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Supplier updated successfully.',
                showConfirmButton: false,
                timer: 1500 // Auto-close after 1.5 seconds
            }).then(() => {
                // Remove the success flag from the URL
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    </script>

<!-- STYLES -->
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    /* BUTTONS */
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

    .actions {
        text-align: right;
        width: 100%;
    }
    .actions .btn {
        margin-left: 10px;
        text-decoration: none;
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 20px;
        margin-bottom: 20px;
    }

    .search-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .left-group {
        display: inline-flex;
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

    /* FILTER / SORT STYLES */
    .filter-container, .sort-container {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        white-space: nowrap;
    }

    .filter-container span, .sort-container span {
        font-size: 14px;
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

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 300px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
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

    #clearFilter {
        background-color: #ccc;
        color: #333;
    }

    #clearFilter:hover {
        background-color: #bbb;
    }

    /* SORT OPTIONS */
    .sort-options {
        display: flex;
        gap: 10px;
        justify-content: center;
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

    .table-container {
        margin-top: 10px;
    }

    .supplier-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .supplier-table th, .supplier-table td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: center !important;
    }

    /* Revert to older light gray color for table headers */
    .supplier-table th {
        background-color: #f2f2f2; /* Light gray header background */
    }

    tr:hover {
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
</style>
