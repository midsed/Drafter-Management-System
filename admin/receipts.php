<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['UserID'])) {
    echo "User ID is not set.";
    exit();
}
include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');
$userID = $_SESSION['UserID'];

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filterCategory = isset($_GET['category']) ? $_GET['category'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'desc';
$allowedSortFields = ['ReceiptID', 'RetrievedBy', 'PartName', 'RetrievedDate'];
$sortField = (isset($_GET['sort_field']) && in_array($_GET['sort_field'], $allowedSortFields))
                ? $_GET['sort_field']
                : 'RetrievedDate';

$totalQuery = "SELECT COUNT(*) AS total 
               FROM receipt r 
               JOIN part p ON r.PartID = p.PartID 
               WHERE r.UserID = ?";
if ($search) {
    $totalQuery .= " AND (r.RetrievedBy LIKE ? OR p.Name LIKE ?)";
}
$stmtTotal = $conn->prepare($totalQuery);

if ($search) {
    $searchParam = '%' . $search . '%';
    $stmtTotal->bind_param("iss", $userID, $searchParam, $searchParam);
} else {
    $stmtTotal->bind_param("i", $userID);
}
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);

// Fetch receipts with pagination & filtering
$queryReceipts = "SELECT r.ReceiptID, r.RetrievedBy, r.RetrievedDate, r.PartID, r.Location, r.Quantity, 
                         p.Name AS PartName, p.Price AS PartPrice, p.Category,
                         (r.Quantity * p.Price) AS TotalPrice 
                  FROM receipt r
                  JOIN part p ON r.PartID = p.PartID
                  WHERE r.UserID = ?";

if ($search) {
    $queryReceipts .= " AND (r.RetrievedBy LIKE ? OR p.Name LIKE ?)";
}
if ($filterCategory) {
    $queryReceipts .= " AND p.Category = ?";
}
$queryReceipts .= " ORDER BY $sortField $sortOrder LIMIT ? OFFSET ?";

$stmtReceipts = $conn->prepare($queryReceipts);

if ($search && $filterCategory) {
    $searchParam = '%' . $search . '%';
    $stmtReceipts->bind_param("issiii", $userID, $searchParam, $searchParam, $filterCategory, $limit, $offset);
} elseif ($search) {
    $searchParam = '%' . $search . '%';
    $stmtReceipts->bind_param("issii", $userID, $searchParam, $searchParam, $limit, $offset);
} elseif ($filterCategory) {
    $stmtReceipts->bind_param("isii", $userID, $filterCategory, $limit, $offset);
} else {
    $stmtReceipts->bind_param("iii", $userID, $limit, $offset);
}
$stmtReceipts->execute();
$resultReceipts = $stmtReceipts->get_result();
$receipts = $resultReceipts->fetch_all(MYSQLI_ASSOC);

$categoryQuery = "SELECT DISTINCT p.Category 
                  FROM receipt r 
                  JOIN part p ON r.PartID = p.PartID 
                  WHERE r.UserID = ? 
                  ORDER BY p.Category";
$stmtCategory = $conn->prepare($categoryQuery);
$stmtCategory->bind_param("i", $userID);
$stmtCategory->execute();
$resultCategory = $stmtCategory->get_result();
$categories = $resultCategory->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipts - Drafter Autotech</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="font-family: 'Poppins', sans-serif;">
<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" style="width: 35px; height: 35px;">
        </a>
        <h1>Receipts</h1>
    </div>
    
    <div class="search-actions">
        <div class="search-container">
            <input type="text" placeholder="Search by name or retrieved by..." id="searchInput" value="<?= htmlspecialchars($search) ?>">
            
            <div class="filter-container">
                <span>Filter</span>
                <div class="dropdown">
                    <button id="filterButton" class="filter-icon" title="Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content">
                        <div class="filter-section">
                            <h4>Category</h4>
                            <div class="filter-options" id="categoryFilter">
                                <?php foreach ($categories as $category): ?>
                                    <label>
                                        <input type="checkbox" class="filter-option" data-filter="category" 
                                               value="<?= htmlspecialchars($category['Category']) ?>"
                                               <?= ($filterCategory === $category['Category']) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($category['Category']) ?>
                                    </label>
                                <?php endforeach; ?>
                                <?php if (empty($categories)): ?>
                                    <p>No categories found.</p>
                                <?php endif; ?>
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
                    <button id="sortButton" class="sort-icon" title="Sort">
                        <i class="fas fa-sort-alpha-down"></i>
                    </button>
                    <!-- Premium style for this dropdown -->
                    <div id="sortDropdown" class="dropdown-content">
                        <h4>Sort By:</h4>
                        <!-- Keep the native arrow, but style the select nicely -->
                        <select id="sortField">
                            <option value="ReceiptID">Receipt ID</option>
                            <option value="RetrievedBy">Retrieved By</option>
                            <option value="PartName">Part Name</option>
                            <option value="RetrievedDate">Retrieved Date</option>
                        </select>
                        <div class="sort-options">
                            <button class="sort-option red-button" data-sort="asc">Ascending</button>
                            <button class="sort-option red-button" data-sort="desc">Descending</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table only, no .receipt-container or .receipt-card wrappers -->
    <table id="receipt-table">
        <thead>
            <tr>
                <th>Receipt ID</th>
                <th>Retrieved By</th>
                <th>Retrieved Date</th>
                <th>Part Name</th>
                <th>Quantity</th>
                <th>Location</th>
                <th>Part Price</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($receipts)): ?>
                <tr>
                    <td colspan="9" class="no-results">No receipts found matching your criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($receipts as $receipt): ?>
                <tr data-category="<?= htmlspecialchars($receipt['Category']) ?>">
                    <td>#<?= $receipt['ReceiptID'] ?></td>
                    <td><?= htmlspecialchars($receipt['RetrievedBy']) ?></td>
                    <td><?= date('F d, Y h:i A', strtotime($receipt['RetrievedDate'])) ?></td>
                    <td><?= htmlspecialchars($receipt['PartName']) ?></td>
                    <td><?= $receipt['Quantity'] ?></td>
                    <td><?= htmlspecialchars($receipt['Location']) ?></td>
                    <td>₱<?= number_format($receipt['PartPrice'], 2) ?></td>
                    <td>₱<?= number_format($receipt['TotalPrice'], 2) ?></td>
                    <td>
                        <button class="view-receipt-button" data-receipt-id="<?= $receipt['ReceiptID'] ?>">
                            View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>&sort=<?= $sortOrder ?>&sort_field=<?= $sortField ?>" class="pagination-button">Previous</a>
        <?php endif; ?>
        
        <?php 
        $maxPagesToShow = 5;
        $startPage = max(1, min($page - floor($maxPagesToShow / 2), $totalPages - $maxPagesToShow + 1));
        $endPage = min($startPage + $maxPagesToShow - 1, $totalPages);
        
        if ($startPage > 1): ?>
            <a href="?page=1&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>&sort=<?= $sortOrder ?>&sort_field=<?= $sortField ?>" class="pagination-button">1</a>
            <?php if ($startPage > 2): ?>
                <span class="pagination-ellipsis">...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>&sort=<?= $sortOrder ?>&sort_field=<?= $sortField ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <span class="pagination-ellipsis">...</span>
            <?php endif; ?>
            <a href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>&sort=<?= $sortOrder ?>&sort_field=<?= $sortField ?>" class="pagination-button"><?= $totalPages ?></a>
        <?php endif; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>&sort=<?= $sortOrder ?>&sort_field=<?= $sortField ?>" class="pagination-button">Next</a>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.view-receipt-button').forEach(button => {
    button.addEventListener('click', function() {
        const receiptId = this.getAttribute('data-receipt-id');
        window.location.href = 'receipt_view.php?id=' + receiptId; 
    });
});

function searchReceipts() {
    const input = document.getElementById("searchInput").value.trim().toLowerCase();
    const receipts = document.querySelectorAll("#receipt-table tbody tr");

    if (input === "") {
        window.location.href = window.location.pathname;
        return;
    }

    receipts.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

document.getElementById("searchInput").addEventListener("input", function() {
    searchReceipts();
});

document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown   = document.getElementById("sortDropdown");
    const filterButton   = document.getElementById("filterButton");
    const sortButton     = document.getElementById("sortButton");
    const applyFilterButton  = document.getElementById("applyFilter");
    const clearFilterButton  = document.getElementById("clearFilter");
    const sortOptions        = document.querySelectorAll(".sort-option");

    // Toggle filter dropdown
    filterButton.addEventListener("click", function (event) {
        event.stopPropagation();
        filterDropdown.classList.toggle("show");
        sortDropdown.classList.remove("show");
    });

    // Toggle sort dropdown
    sortButton.addEventListener("click", function (event) {
        event.stopPropagation();
        sortDropdown.classList.toggle("show");
        filterDropdown.classList.remove("show");
    });

    // Close dropdowns when clicking outside
    window.addEventListener("click", function (event) {
        if (!event.target.closest(".dropdown-content") && 
            !event.target.closest(".filter-icon") && 
            !event.target.closest(".sort-icon")) {
            filterDropdown.classList.remove("show");
            sortDropdown.classList.remove("show");
        }
    });

    // Prevent filter dropdown from closing when clicking inside it
    filterDropdown.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    // Apply filter
    applyFilterButton.addEventListener("click", function () {
        const selectedCategories = Array.from(document.querySelectorAll('.filter-option[data-filter="category"]:checked'))
                                        .map(checkbox => checkbox.value);
        filterParts(selectedCategories);
    });

    // Clear filter
    clearFilterButton.addEventListener("click", function () {
        document.querySelectorAll('.filter-option').forEach(checkbox => checkbox.checked = false);
        filterParts([]);
    });

    // Sort options
    sortOptions.forEach(option => {
        option.addEventListener("click", function () {
            sortParts(option.dataset.sort);
            sortDropdown.classList.remove("show");
        });
    });

    // Preserve the selected sort field after reload
    const urlParams = new URLSearchParams(window.location.search);
    const selectedSortField = urlParams.get('sort_field');
    if (selectedSortField) {
        document.getElementById('sortField').value = selectedSortField;
    }
});

function filterParts(selectedCategories) {
    const receipts = document.querySelectorAll("#receipt-table tbody tr");
    receipts.forEach(row => {
        const category = row.getAttribute("data-category");
        if (selectedCategories.length === 0) {
            row.style.display = "";
        } else {
            row.style.display = selectedCategories.includes(category) ? "" : "none";
        }
    });
}

function sortParts(sortOrder) {
    const url = new URL(window.location.href);
    const sortField = document.getElementById("sortField").value;
    url.searchParams.set("sort", sortOrder);
    url.searchParams.set("sort_field", sortField);
    window.location.href = url.toString();
}
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
}
/* Filter & Sort containers */
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
/* Premium style for Filter & Sort dropdowns */
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
/* Filter checkboxes */
.filter-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}
.filter-actions {
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
/* Sort By dropdown select */
.dropdown-content select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 15px;
}
/* Sort options container */
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
    padding: 10px 20px; 
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.sort-option:hover {
    background-color: darkred; /* Darker red on hover */
}

/* The table (no .receipt-container or .receipt-card) */
#receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
#receipt-table th, #receipt-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
#receipt-table th {
    background-color: #f2f2f2;
    font-weight: 600;
}
#receipt-table tr:hover {
    background-color: rgb(218, 218, 218);
}
.no-results {
    text-align: center; /* Center the "No receipts found..." message */
    font-style: italic;
}
.view-receipt-button {
    background: #E10F0F;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    transition: background 0.3s ease;
}
.view-receipt-button:hover {
    background: darkred;
}
/* Pagination */
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
.pagination-ellipsis {
    color: #777;
}
</style>
</body>
</html>
