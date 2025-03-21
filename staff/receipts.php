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

$totalQuery = "SELECT COUNT(*) AS total FROM receipt r JOIN part p ON r.PartID = p.PartID WHERE r.UserID = ?";
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
$queryReceipts .= " ORDER BY $sortField $sortOrder"; // Dynamic sorting based on sort_field and sort
$queryReceipts .= " LIMIT ? OFFSET ?";

// Prepare and execute the statement
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

$categoryQuery = "SELECT DISTINCT p.Category FROM receipt r JOIN part p ON r.PartID = p.PartID WHERE r.UserID = ? ORDER BY p.Category";
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
<body>
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
                    <div id="sortDropdown" class="dropdown-content">
                        <h4>Sort By:</h4>
                        <select id="sortField">
                            <option value="ReceiptID">Receipt ID</option>
                            <option value="RetrievedBy">Retrieved By</option>
                            <option value="PartName">Part Name</option>
                            <option value="RetrievedDate">Retrieved Date</option>
                        </select>
                        <button class="sort-option red-button" data-sort="asc">Ascending</button>
                        <button class="sort-option red-button" data-sort="desc">Descending</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="receipt-container">
        <div class="receipt-card">
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
        </div>
    </div>

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
    button.addEventListener('click', function(event) {
        const receiptId = this.getAttribute('data-receipt-id');
        window.location.href = 'receipt_view.php?id=' + receiptId; // Redirect to the receipt view page
    });
});

function searchReceipts() {
    const input = document.getElementById("searchInput").value.trim().toLowerCase();
    const receipts = document.querySelectorAll("#receipt-table tbody tr");

    if (input === "") {
        window.location.href = window.location.pathname; // Reload page to reset results
        return;
    }

    receipts.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

document.getElementById("searchInput").addEventListener("input", function () {
    searchReceipts();
});

document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown = document.getElementById("sortDropdown");
    const filterButton = document.getElementById("filterButton");
    const sortButton = document.getElementById("sortButton");
    const applyFilterButton = document.getElementById("applyFilter");
    const clearFilterButton = document.getElementById("clearFilter");
    const sortOptions = document.querySelectorAll(".sort-option");

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
        if (!event.target.matches('.filter-icon') && !event.target.matches('.sort-icon')) {
            filterDropdown.classList.remove("show");
            sortDropdown.classList.remove("show");
        }
    });

    // Prevent dropdown from closing when clicking checkboxes
    filterDropdown.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    // Apply filter
    applyFilterButton.addEventListener("click", function () {
        const selectedCategories = Array.from(document.querySelectorAll('.filter-option[data-filter="category"]:checked')).map(checkbox => checkbox.value);
        console.log("Selected Categories:", selectedCategories); // Debugging line
        filterParts(selectedCategories);
    });

    // Clear filter
    clearFilterButton.addEventListener("click", function () {
        document.querySelectorAll('.filter-option').forEach(checkbox => checkbox.checked = false);
        filterParts([]);
    });

    // Sort functionality
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function () {
            sortParts(option.dataset.sort);
            sortDropdown.classList.remove("show");
        });
    });

    // Preserve selected sort field after reload
    const urlParams = new URLSearchParams(window.location.search);
    const selectedSortField = urlParams.get('sort_field');
    if (selectedSortField) {
        document.getElementById('sortField').value = selectedSortField;
    }
});

// Define the filterParts function
function filterParts(selectedCategories) {
    const receipts = document.querySelectorAll("#receipt-table tbody tr");

    receipts.forEach(row => {
        const category = row.getAttribute("data-category");

        // If no categories are selected, show all rows
        if (selectedCategories.length === 0) {
            row.style.display = "";
        } else {
            // Show the row if its category is in the selected categories
            if (selectedCategories.includes(category)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}

// Define the sortParts function
function sortParts(sortOrder) {
    const url = new URL(window.location.href);
    const sortField = document.getElementById("sortField").value; // Get the selected sort field
    url.searchParams.set("sort", sortOrder);
    url.searchParams.set("sort_field", sortField); // Include the sort_field parameter
    window.location.href = url.toString();
}

// Define the filterParts function
function filterParts(selectedCategories) {
    const receipts = document.querySelectorAll("#receipt-table tbody tr");

    receipts.forEach(row => {
        const category = row.getAttribute("data-category");

        // If no categories are selected, show all rows
        if (selectedCategories.length === 0) {
            row.style.display = "";
        } else {
            // Show the row if its category is in the selected categories
            if (selectedCategories.includes(category)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}

// Define the sortParts function (if not already defined)
function sortParts(sortOrder) {
    const url = new URL(window.location.href);
    const sortField = document.getElementById("sortField").value; // Get the selected sort field
    url.searchParams.set("sort", sortOrder);
    url.searchParams.set("sort_field", sortField); // Include the sort_field parameter
    window.location.href = url.toString();
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}
</script>
<style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .container {
            margin: 20px;
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

    .right-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
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
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .red-button:hover {
        background: darkred;
    }

    .cart-icon {
        color: #E10F0F;
        font-size: 20px;
        cursor: pointer;
        transition: color 0.3s ease;
        text-decoration: none;
    }

    .cart-icon:hover {
        color: darkred;
    }

    .cart-count {
        position: relative;
        top: -13px;
        right: 10px;
        background-color: green;
        color: white;
        border-radius: 50%;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: bold;
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
        border: none;
    }

    .filter-icon:hover, .sort-icon:hover {
        color: darkred;
    }

    .parts-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .part-card {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .part-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
    }

    .part-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
    }

    .part-card p {
        margin: 8px 0;
        font-size: 14px;
    }

    .part-card .actions {
        display: flex;
        justify-content: space-around;
        margin-top: 10px;
    }

    .part-card .actions button {
        padding: 6px 12px;
        font-size: 13px;
    }

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
        padding: 6px 12px;
        border-radius: 4px;
        background: black;
        color: white;
        font-weight: bold;
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

    </style>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const sortButtons = document.querySelectorAll('.sort-option');
        const sortFieldSelect = document.getElementById('sortField');

        sortButtons.forEach(button => {
            button.addEventListener('click', () => {
                const sortOrder = button.getAttribute('data-sort');
                const sortField = sortFieldSelect.value;

                // Reload page with sorting parameters
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortOrder);
                url.searchParams.set('sort_field', sortField);
                window.location.href = url.href;
            });
        });

        // Preserve selected sort field after reload
        const urlParams = new URLSearchParams(window.location.search);
        const selectedSortField = urlParams.get('sort_field');
        if (selectedSortField) {
            sortFieldSelect.value = selectedSortField;
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const sortButtons = document.querySelectorAll('.sort-option');
        const sortFieldSelect = document.getElementById('sortField');
        const sortDropdown = document.getElementById('sortDropdown');

        // Prevent dropdown from closing when selecting an option
        sortFieldSelect.addEventListener('click', (event) => {
            event.stopPropagation(); // Stop the click from bubbling up to the parent
        });

        sortButtons.forEach(button => {
            button.addEventListener('click', () => {
                const sortOrder = button.getAttribute('data-sort');
                const sortField = sortFieldSelect.value;

                // Reload page with sorting parameters
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortOrder);
                url.searchParams.set('sort_field', sortField);
                window.location.href = url.href;
            });
        });

        // Preserve selected sort field after reload
        const urlParams = new URLSearchParams(window.location.search);
        const selectedSortField = urlParams.get('sort_field');
        if (selectedSortField) {
            sortFieldSelect.value = selectedSortField;
        }
    });
    </script>
</body>
</html>