<?php 
session_start();
include('dbconnect.php');
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   
include('navigation/sidebar.php');
include('navigation/topbar.php');
?>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
  <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
    <h1 style="font-family: 'Poppins', sans-serif;">Archived Parts List</h1>
  </div>

  <div class="search-actions">
    <div class="search-container">
      <input type="text" placeholder="Quick search" id="searchInput">
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
                <?php
                $categoryQuery = "SELECT DISTINCT Category FROM part WHERE archived = 1";
                $categoryResult = $conn->query($categoryQuery);
                if ($categoryResult->num_rows > 0) {
                    while ($category = $categoryResult->fetch_assoc()) {
                        echo "<label><input type='checkbox' class='filter-option' data-filter='category' value='{$category['Category']}'> {$category['Category']}</label>";
                    }
                } else {
                    echo "<p>No categories found.</p>";
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
          <button id="sortButton" class="sort-icon" title="Sort">
            <i class="fas fa-sort-alpha-down"></i>
          </button>
          <div id="sortDropdown" class="dropdown-content">
            <button class="sort-option red-button" data-sort="asc">Ascending</button>
            <button class="sort-option red-button" data-sort="desc">Descending</button>
          </div>
        </div>
      </div>
    </div>
    <div class="right-actions">
      <button id="selectModeBtn" class="select-button"><i class="fas fa-check-square"></i> Select Parts to Re-list</button>
      <button id="selectAllBtn" class="red-button" style="display: none;">Select All</button>
      <button id="relistSelectedBtn" class="green-button" style="display: none;"><i class="fas fa-archive"></i> Re-list Selected</button>
      <button id="cancelSelectBtn" class="red-button" style="display: none;"><i class="fas fa-times"></i> Cancel</button>
    </div>
  </div>

  <div class="selection-summary" id="selectionSummary" style="display: none;">
    <span id="selectedCount">0 items selected</span>
  </div>
  <div class="parts-container" id="partsList">
    <?php
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $categories = isset($_GET['category']) ? explode(',', $_GET['category']) : [];
    $sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : '';

    $sql = "SELECT PartID, Name, Make, Model, Location, Quantity, Media FROM part WHERE archived = 1";
    $countSql = "SELECT COUNT(*) AS total FROM part WHERE archived = 1";

    if (!empty($categories)) {
        $escapedCategories = array_map([$conn, 'real_escape_string'], $categories);
        $categoryList = "'" . implode("','", $escapedCategories) . "'";
        $sql .= " AND Category IN ($categoryList)";
        $countSql .= " AND Category IN ($categoryList)";
    }

    if (!empty($search)) {
        $sql .= " AND (Name LIKE '%$search%' OR Make LIKE '%$search%' OR Model LIKE '%$search%')";
        $countSql .= " AND (Name LIKE '%$search%' OR Make LIKE '%$search%' OR Model LIKE '%$search%')";
    }

    if ($page == 1 && empty($sort)) {
        $sql .= " ORDER BY DateAdded DESC";
    } elseif ($sort === 'asc') {
        $sql .= " ORDER BY Name ASC";
    } elseif ($sort === 'desc') {
        $sql .= " ORDER BY Name DESC";
    } else {
        $sql .= " ORDER BY DateAdded DESC";
    }

    $sql .= " LIMIT $limit OFFSET $offset";
    $totalResult = $conn->query($countSql);
    $totalRow = $totalResult->fetch_assoc();
    $totalPages = ceil($totalRow['total'] / $limit);
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($part = $result->fetch_assoc()) {
            $imageSrc = !empty($part['Media']) ? '/Drafter-Management-System/' . $part['Media'] : 'images/no-image.png';
            echo "
                <div class='part-card' data-part-id='{$part['PartID']}'>
                    <div class='select-checkbox' style='display: none;'>
                        <input type='checkbox' class='part-checkbox' data-part-id='{$part['PartID']}'>
                    </div>
                    <a href='partdetail.php?id={$part['PartID']}'><img src='$imageSrc' alt='Part Image'></a>
                    <p><strong>Name:</strong> {$part['Name']}</p>
                    <p><strong>Make:</strong> {$part['Make']}</p>
                    <p><strong>Model:</strong> {$part['Model']}</p>
                    <p><strong>Location:</strong> {$part['Location']}</p>
                    <p><strong>Quantity:</strong> {$part['Quantity']}</p>
                </div>
            ";
        }
    } else {
        echo "<p>No archived parts found.</p>";
    }
    ?>
  </div>

  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search='.$search : '' ?><?= !empty($categories) ? '&category='.implode(',', $categories) : '' ?><?= !empty($sort) ? '&sort='.$sort : '' ?>" class="pagination-button">Previous</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?><?= !empty($search) ? '&search='.$search : '' ?><?= !empty($categories) ? '&category='.implode(',', $categories) : '' ?><?= !empty($sort) ? '&sort='.$sort : '' ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search='.$search : '' ?><?= !empty($categories) ? '&category='.implode(',', $categories) : '' ?><?= !empty($sort) ? '&sort='.$sort : '' ?>" class="pagination-button">Next</a>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// -- Sidebar Toggle and State Persistence --

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

// -- Select Mode for Archived Parts --

let selectMode = false;
const selectedParts = new Set();

function toggleSelectMode() {
    selectMode = !selectMode;
    document.getElementById('selectModeBtn').style.display = selectMode ? 'none' : 'inline-block';
    document.getElementById('relistSelectedBtn').style.display = selectMode ? 'inline-block' : 'none';
    document.getElementById('cancelSelectBtn').style.display = selectMode ? 'inline-block' : 'none';
    document.getElementById('selectAllBtn').style.display = selectMode ? 'inline-block' : 'none';
    document.getElementById('selectionSummary').style.display = selectMode ? 'block' : 'none';

    if (!selectMode) {
        selectedParts.clear();
        updateSelectedCount();
        document.querySelectorAll('.part-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.part-card').forEach(card => {
            card.classList.remove('selected-card');
        });
    }
}

function selectAllParts() {
    const checkboxes = document.querySelectorAll('.part-checkbox');
    const allSelected = selectedParts.size === checkboxes.length;
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allSelected;
        togglePartSelection(checkbox.dataset.partId, checkbox);
    });
}

document.getElementById('selectAllBtn').addEventListener('click', selectAllParts);

function updateSelectedCount() {
    const count = selectedParts.size;
    document.getElementById('selectedCount').textContent = `${count} item${count !== 1 ? 's' : ''} selected`;
}

function togglePartSelection(partId, checkbox) {
    const partCard = document.querySelector(`.part-card[data-part-id="${partId}"]`);
    if (checkbox.checked) {
        selectedParts.add(partId);
        partCard.classList.add('selected-card');
    } else {
        selectedParts.delete(partId);
        partCard.classList.remove('selected-card');
    }
    updateSelectedCount();
}

document.getElementById("selectModeBtn").addEventListener("click", toggleSelectMode);
document.getElementById("cancelSelectBtn").addEventListener("click", toggleSelectMode);

document.querySelectorAll('.part-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function(e) {
        togglePartSelection(this.dataset.partId, this);
        e.stopPropagation();
    });
});

document.querySelectorAll('.part-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (selectMode) {
            e.preventDefault();
            const partId = this.dataset.partId;
            const checkbox = this.querySelector('.part-checkbox');
            checkbox.checked = !checkbox.checked;
            togglePartSelection(partId, checkbox);
        } else {
            const partId = this.dataset.partId;
            window.location.href = `partdetail.php?id=${partId}`;
        }
    });
});

document.getElementById("relistSelectedBtn").addEventListener("click", function() {
    const selectedPartsArray = Array.from(document.querySelectorAll('.part-checkbox:checked')).map(checkbox => checkbox.dataset.partId);
    if (selectedPartsArray.length === 0) {
        Swal.fire({
            title: "No parts selected",
            text: "Please select at least one part to re-list.",
            icon: "warning",
            confirmButtonColor: "#32CD32"
        });
        return;
    }
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to re-list ${selectedPartsArray.length} selected part${selectedPartsArray.length !== 1 ? 's' : ''}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#32CD32",
        cancelButtonColor: "#d63031",
        confirmButtonText: "Yes, re-list them!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('relist_multiple_parts.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ partIds: selectedPartsArray })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: "Success!",
                    text: `${data.count} parts have been re-listed successfully.`,
                    icon: "success",
                    confirmButtonColor: "#32CD32"
                }).then(() => {
                    selectedParts.clear();
                    toggleSelectMode();
                    location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: "Error",
                    text: "Something went wrong!",
                    icon: "error",
                    confirmButtonColor: "#32CD32"
                });
            });
        }
    });
});

// -- Filter and Sort Functionality --

document.getElementById("applyFilter").addEventListener("click", function() {
    const selectedCategories = Array.from(document.querySelectorAll('.filter-option:checked')).map(checkbox => checkbox.value);
    const searchQuery = document.getElementById("searchInput").value.trim();
    const queryParams = new URLSearchParams(window.location.search);
    queryParams.set("page", "1");
    if (selectedCategories.length > 0) {
        queryParams.set("category", selectedCategories.join(","));
    } else {
        queryParams.delete("category");
    }
    if (searchQuery) {
        queryParams.set("search", searchQuery);
    } else {
        queryParams.delete("search");
    }
    window.location.search = queryParams.toString();
});

document.getElementById("clearFilter").addEventListener("click", function() {
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

// -- Dropdown Functionality --

document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown = document.getElementById("sortDropdown");
    const filterButton = document.getElementById("filterButton");
    const sortButton = document.getElementById("sortButton");
    const applyFilterButton = document.getElementById("applyFilter");
    const clearFilterButton = document.getElementById("clearFilter");
    const searchInput = document.getElementById("searchInput");
    const queryParams = new URLSearchParams(window.location.search);

    const selectedCategories = queryParams.get("category") ? queryParams.get("category").split(",") : [];
    document.querySelectorAll('.filter-option[data-filter="category"]').forEach(checkbox => {
        if (selectedCategories.includes(checkbox.value)) {
            checkbox.checked = true; 
        }
    });

    const searchTerm = queryParams.get("search");
    if (searchTerm) {
        searchInput.value = searchTerm;
    }

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

    filterDropdown.addEventListener("click", function(event) {
        event.stopPropagation();
    });

    window.addEventListener("click", function () {
        filterDropdown.classList.remove("show");
        sortDropdown.classList.remove("show");
    });

    applyFilterButton.addEventListener("click", function () {
        const selectedCategories = Array.from(document.querySelectorAll('.filter-option[data-filter="category"]:checked'))
            .map(checkbox => checkbox.value);
        const searchQuery = searchInput.value.trim();
        queryParams.set("page", "1");
        if (selectedCategories.length > 0) {
            queryParams.set("category", selectedCategories.join(","));
        } else {
            queryParams.delete("category");
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
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

/* ---------- Keyframes ---------- */
@keyframes scaleUp {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes cardPulseGlow {
    0%   { box-shadow: 0 0 6px rgba(255,77,77,0.3); }
    50%  { box-shadow: 0 0 16px rgba(255,77,77,0.5); }
    100% { box-shadow: 0 0 6px rgba(255,77,77,0.3); }
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255,0,0,0.7);
    }
    70% {
        transform: scale(1.02);
        box-shadow: 0 0 10px 5px rgba(255,0,0,0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255,0,0,0);
    }
}

/* ---------- Base Body / Font ---------- */
body {
    font-family: 'Poppins', sans-serif;
}

/* ---------- Parts Container ---------- */
.parts-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 40px;
    padding: 20px;  /* Extra padding to make the container appear larger than the cards */
    
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(25px);
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
    
    animation: scaleUp 1s ease forwards;  /* Zooms in once on page load */
    transition: box-shadow 0.5s ease;
    position: relative;
    overflow: hidden;
}

/* On hover, the container only changes its shadow */
.parts-container:hover {
    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.5);
}

/* ---------- Optional Second Container (if needed) ---------- */
.parts-container2 {
    transform: scale(1.1);
    transform-origin: center center;
}

/* ---------- Part Card ---------- */
.part-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    display: flex;
    flex-direction: column;    /* Stack image, text, and actions vertically */
    min-height: 280px;         /* Consistent card height */
    transform-origin: center center;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border 0.3s ease;
    position: relative;
}

/* On hover, the card scales up and shows a subtle pulse glow */
.part-card:hover {
    transform: scale(1.05);
    animation: cardPulseGlow 1.5s infinite;
}

/* ---------- Selected Card Highlight ---------- */
.selected-card {
    border: 6px solid rgba(225,15,15,0.7);
    animation: pulse 0.5s ease-out;
}

/* ---------- Image Container & Out-of-Stock Overlay ---------- */
.image-container {
    position: relative;
    width: 100%;
    height: 150px;
    margin-bottom: 10px;
}
.part-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}
.out-of-stock-overlay {
    position: absolute;
    top: 0; 
    left: 0; 
    right: 0; 
    bottom: 0;
    background: rgba(22,22,22,0.64);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    font-weight: bold;
    border-radius: 4px;
}
.out-of-stock-overlay img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.5;
    z-index: -1;
}

/* ---------- Card Text & Actions ---------- */
.part-card p {
    margin: 8px 0;
    font-size: 14px;
}
.part-card .card-actions {
    margin-top: auto;  /* Push actions to the bottom */
    display: flex;
    justify-content: space-around;
    align-items: center;
    gap: 10px;
}
.part-card .edit-btn {
    background: gray;
    color: white;
    transition: background 0.3s ease, color 0.3s ease;
}
.part-card .edit-btn:hover {
    background: #555555;
}
.part-card .add-to-cart-btn {
    background: #FFB52E;
    color: white;
    transition: background 0.3s ease, color 0.3s ease;
}
.part-card .add-to-cart-btn:hover {
    background: darkorange;
}

/* ---------- Search, Filter, Sort, and Pagination ---------- */
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

/* ---------- Buttons ---------- */
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
.green-button {
    background: rgb(88,186,35);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease;
}
.green-button:hover {
    background: rgb(48,100,20);
}
.new-stock-btn {
    background: black;
    color: white;
}
.select-button {
    background: rgb(88,186,35);
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
.select-button:hover {
    background: darkgreen;
}

/* ---------- Cart Icon ---------- */
.cart-icon {
    color: #FFB52E;
    font-size: 20px;
    cursor: pointer;
    transition: color 0.3s ease;
    text-decoration: none;
}
.cart-icon:hover {
    color: #e0a53a;
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

/* ---------- Filter & Sort Dropdowns ---------- */
.filter-container,
.sort-container {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.filter-container span,
.sort-container span {
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    color: #333;
}
.filter-icon,
.sort-icon {
    color: #E10F0F;
    font-size: 20px;
    transition: color 0.3s ease;
    background: none;
    border: none;
    cursor: pointer;
}
.filter-icon:hover,
.sort-icon:hover {
    color: darkred;
}
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 500px;
    max-height: 500px;
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
.filter-actions #clearFilter {
    background-color: #ccc;
    color: #333;
}
.filter-actions #clearFilter:hover {
    background-color: #bbb;
}

/* ---------- Sort Option ---------- */
.sort-options {
    display: flex;
    gap: 10px;
    justify-content: center;
}
.sort-option.red-button {
    display: block;
    width: 100%;
    text-align: left;
    margin: 5px 0;
    background-color: white;
    color: #E10F0F;
    border: 1px solid #E10F0F;
}
.sort-option.red-button:hover {
    background-color: #f8f8f8;
}

/* ---------- Pagination ---------- */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 40px;
    padding-bottom: 40px;
}
.pagination-button {
    padding: 8px 12px;
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

/* ---------- List View (Table) Styles ---------- */
.parts-list-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 20px;
}
.parts-list-container table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
}
.parts-list-container th,
.parts-list-container td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.parts-list-container th {
    background-color: #f8f9fa;
    font-weight: 600;
    position: sticky;
    top: 0;
}
.parts-list-container tr:hover {
    background-color: #f5f5f5;
}
.parts-list-container .out-of-stock {
    color: #E10F0F;
    font-weight: bold;
}
.parts-list-container .actions {
    white-space: nowrap;
}
.parts-list-container a {
    color: #0066cc;
    text-decoration: none;
}
.parts-list-container a:hover {
    text-decoration: underline;
}

/* ---------- Additional Buttons & View Toggle ---------- */
.cart-icon {
    color: #FFB52E;
}
.new-stock-btn {
    background: #00A300;
    color: white;
}
.view-toggle {
    display: flex;
    align-items: center;
    margin-left: 10px;
}
.view-button {
    background: #f0f0f0;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
    color: #555;
    transition: all 0.3s ease;
}
.view-button.active {
    background: #E10F0F;
    color: white;
}
.view-button:first-child {
    border-radius: 4px 0 0 4px;
}
.view-button:last-child {
    border-radius: 0 4px 4px 0;
}

</style>
