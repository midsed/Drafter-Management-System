<?php 
session_start();
include('dbconnect.php');
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') { 
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
    <h1 style="font-family: 'Poppins', sans-serif;">Parts List</h1>
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
                $categoryQuery = "SELECT DISTINCT Category FROM part WHERE archived = 0";
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
      <a href="cart.php" class="cart-icon" title="Cart">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
      </a>
      <button id="selectModeBtn" class="red-button"><i class="fas fa-check-square"></i> Select Mode</button>
      <button id="selectAllBtn" class="red-button" style="display: none;">Select All</button>
      <button id="archiveSelectedBtn" class="red-button" style="display: none;"><i class="fas fa-archive"></i> Archive Selected</button>
      <button id="cancelSelectBtn" class="red-button" style="display: none;"><i class="fas fa-times"></i> Cancel</button>
      <a href="partsarchive.php" class="red-button">Archives</a>
      <a href="partsadd.php" class="red-button new-stock-btn">+ New Stock</a>
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
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
    $sql = "SELECT PartID, Name, Make, Model, Location, Quantity, Media, Category FROM part WHERE archived = 0";
    $countSql = "SELECT COUNT(*) AS total FROM part WHERE archived = 0";
    if (!empty($categories)) {
        $escapedCategories = array_map([$conn, 'real_escape_string'], $categories);
        $categoryList = "'" . implode("','", $escapedCategories) . "'";
        $sql .= " AND Category IN ($categoryList)";
        $countSql .= " AND Category IN ($categoryList)";
    }
    if (!empty($search)) {
        $sql .= " AND (Name LIKE '%$search%' OR Make LIKE '%$search%' OR Model LIKE '%$search%' OR Category LIKE '%$search%')";
        $countSql .= " AND (Name LIKE '%$search%' OR Make LIKE '%$search%' OR Model LIKE '%$search%' OR Category LIKE '%$search%')";
    }
    // Out of stock items at the end
    $sql .= " ORDER BY CASE WHEN Quantity = 0 THEN 1 ELSE 0 END, DateAdded DESC";
    $sql .= " LIMIT $limit OFFSET $offset";
    $totalResult = $conn->query($countSql);
    $totalRow = $totalResult->fetch_assoc();
    $totalPages = ceil($totalRow['total'] / $limit);
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($part = $result->fetch_assoc()) {
            $imageSrc = !empty($part['Media']) ? '/Drafter-Management-System/' . $part['Media'] : 'images/no-image.png';
            $isOutOfStock = $part['Quantity'] <= 0;
            echo "
                <div class='part-card' data-part-id='{$part['PartID']}'>
                    <div class='select-checkbox' style='display: none;'>
                        <input type='checkbox' class='part-checkbox' data-part-id='{$part['PartID']}' data-part-name='{$part['Name']}'>
                    </div>
                    <div class='image-container'>
                        <a href='partdetail.php?id={$part['PartID']}' class='part-link'><img src='$imageSrc' alt='Part Image'></a>
                        " . ($isOutOfStock ? "<div class='out-of-stock-overlay'><img src='images/outofstock.png' alt='Out of Stock'><span>OUT OF STOCK</span></div>" : "") . "
                    </div>
                    <p><strong>Name:</strong> {$part['Name']}</p>
                    <p><strong>Make:</strong> {$part['Make']}</p>
                    <p><strong>Model:</strong> {$part['Model']}</p>
                    <p><strong>Category:</strong> {$part['Category']}</p>
                    <p><strong>Location:</strong> {$part['Location']}</p>
                    <p><strong>Quantity:</strong></p>
                    <div class='actions'>
                        <button class='qty-btn' onclick='decreaseQuantity({$part['PartID']})'>-</button>
                        <input type='text' id='quantity_{$part['PartID']}' value='{$part['Quantity']}'" . ($isOutOfStock ? " class='quantity-input zero-quantity'" : " class='quantity-input'") . " readonly>
                        <button class='qty-btn' onclick='increaseQuantity({$part['PartID']})'>+</button>
                    </div>
                    <div class='actions card-actions'>
                        <a href='partsedit.php?id={$part['PartID']}' class='red-button edit-btn'>Edit</a>
                        <button class='red-button add-to-cart-btn" . ($isOutOfStock ? " disabled-btn" : "") . "' " . ($isOutOfStock ? "disabled" : "") . " onclick='addToCart({$part['PartID']}, \"{$part['Name']}\", \"{$part['Make']}\", \"{$part['Model']}\")'>Add to Cart</button>
                    </div>
                </div>
            ";
        }
    } else {
        echo "<p>No parts found.</p>";
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
let selectMode = false;
const selectedParts = new Set();

function toggleSelectMode() {
    selectMode = !selectMode;
    document.getElementById('selectModeBtn').style.display = selectMode ? 'none' : 'inline-block';
    document.getElementById('archiveSelectedBtn').style.display = selectMode ? 'inline-block' : 'none';
    document.getElementById('cancelSelectBtn').style.display = selectMode ? 'inline-block' : 'none';
    document.getElementById('selectAllBtn').style.display = selectMode ? 'inline-block' : 'none';
    document.getElementById('selectionSummary').style.display = selectMode ? 'block' : 'none';

    const cardActions = document.querySelectorAll('.card-actions');
    cardActions.forEach(action => {
        action.style.display = selectMode ? 'none' : 'flex';
    });

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

function archiveSelectedParts() {
    if (selectedParts.size === 0) {
        Swal.fire({
            title: "No parts selected",
            text: "Please select at least one part to archive.",
            icon: "warning",
            confirmButtonColor: "#32CD32"
        });
        return;
    }
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to archive ${selectedParts.size} selected part${selectedParts.size !== 1 ? 's' : ''}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#32CD32",
        cancelButtonColor: "#d63031",
        confirmButtonText: "Yes, archive them!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('archive_multiple_parts.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    partIds: Array.from(selectedParts)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "Archived!",
                        text: data.message,
                        icon: "success",
                        confirmButtonColor: "#32CD32"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: data.message || "Something went wrong!",
                        icon: "error",
                        confirmButtonColor: "#32CD32"
                    });
                }
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
}

function increaseQuantity(partID) {
    let quantity = document.getElementById('quantity_' + partID);
    let newQuantity = parseInt(quantity.value) + 1;
    quantity.value = newQuantity;
    updateQuantity(partID);
    
    let card = quantity.closest('.part-card');
    let overlay = card.querySelector('.out-of-stock-overlay');
    let addToCartBtn = card.querySelector('.card-actions button:last-child');
    
    if (newQuantity > 0) {
        if (overlay) overlay.style.display = 'none';
        quantity.classList.remove('zero-quantity');
        addToCartBtn.classList.remove('disabled-btn');
        addToCartBtn.disabled = false;
    }
}

function decreaseQuantity(partID) {
    let quantity = document.getElementById('quantity_' + partID);
    let newQuantity = parseInt(quantity.value) - 1;
    
    if (newQuantity >= 0) {
        quantity.value = newQuantity;
        updateQuantity(partID);
        
        let card = quantity.closest('.part-card');
        let imageContainer = card.querySelector('.image-container');
        let overlay = card.querySelector('.out-of-stock-overlay');
        let addToCartBtn = card.querySelector('.card-actions button:last-child');
        
        if (newQuantity === 0) {
            if (!overlay) {
                let newOverlay = document.createElement('div');
                newOverlay.className = 'out-of-stock-overlay';
                newOverlay.innerHTML = '<img src="images/outofstock.png" alt="Out of Stock"><span>OUT OF STOCK</span>';
                imageContainer.appendChild(newOverlay);
            } else {
                overlay.style.display = 'flex';
            }
            quantity.classList.add('zero-quantity');
            addToCartBtn.classList.add('disabled-btn');
            addToCartBtn.disabled = true;
        }
    }
}

function updateQuantity(partID) {
    let quantity = document.getElementById('quantity_' + partID).value;
    fetch('update_quantity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + partID + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Quantity updated successfully');
        } else {
            console.error('Error updating quantity:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function archivePart(partID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to archive this part?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#32CD32",
        cancelButtonColor: "#d63031",
        confirmButtonText: "Yes, archive it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('archive_part.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + partID
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Archived!",
                    text: data,
                    icon: "success",
                    confirmButtonColor: "#32CD32"
                }).then(() => {
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
}

function searchParts() {
    const input = document.getElementById("searchInput").value.trim().toLowerCase();
    const currentUrl = new URL(window.location.href);
    if (input === "") {
        currentUrl.searchParams.delete("search");
    } else {
        currentUrl.searchParams.set("search", input);
    }
    currentUrl.searchParams.set("page", "1");
    window.location.href = currentUrl.toString();
}

document.getElementById("searchInput").addEventListener("keyup", function(event) {
    if (event.key === "Enter") {
        searchParts();
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get("search");
    if (searchTerm) {
        document.getElementById("searchInput").value = searchTerm;
    }
    
    document.getElementById('selectModeBtn').addEventListener('click', toggleSelectMode);
    document.getElementById('cancelSelectBtn').addEventListener('click', toggleSelectMode);
    document.getElementById('archiveSelectedBtn').addEventListener('click', archiveSelectedParts);
    
    document.querySelectorAll('.part-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            togglePartSelection(this.dataset.partId, this);
        });
    });
    
    document.querySelectorAll('.part-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (selectMode && !e.target.closest('.part-checkbox')) {
                const partId = this.dataset.partId;
                const checkbox = this.querySelector('.part-checkbox');
                if (!e.target.closest('.part-checkbox')) {
                    e.preventDefault();
                    checkbox.checked = !checkbox.checked;
                    togglePartSelection(partId, checkbox);
                }
            }
        });
    });
});

function addToCart(partID) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(partID)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: "Added to Cart!",
                text: data.message,
                icon: "success",
                confirmButtonColor: "#32CD32"
            }).then(() => {
                location.reload(); 
            });
        } else {
            Swal.fire({
                title: "Error",
                text: data.message,
                icon: "error",
                confirmButtonColor: "#32CD32"
            });
        }
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
    filterDropdown.addEventListener("click", function (event) {
        event.stopPropagation();
    });
    sortDropdown.addEventListener("click", function (event) {
        event.stopPropagation();
    });
    window.addEventListener("click", function (event) {
        if (!event.target.closest(".dropdown-content") && 
            !event.target.closest(".filter-icon") && 
            !event.target.closest(".sort-icon")) {
            filterDropdown.classList.remove("show");
            sortDropdown.classList.remove("show");
        }
    });
    applyFilterButton.addEventListener("click", function () {
        const selectedCategories = Array.from(document.querySelectorAll('.filter-option[data-filter="category"]:checked'))
            .map(checkbox => checkbox.value);
        const searchQuery = searchInput.value.trim();
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
        const sortParam = queryParams.get("sort");
        if (!sortParam) {
            queryParams.delete("sort");
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
            const searchQuery = queryParams.get("search");
            const categoryParam = queryParams.get("category");
            queryParams.set("page", "1");
            window.location.search = queryParams.toString();
        });
    });
});

function filterParts(selectedCategories) {
    const parts = document.querySelectorAll(".part-card");
    parts.forEach(part => {
        const categoryElement = part.querySelector("p:nth-child(4)");
        if (!categoryElement) {
            console.error("Category element not found in part card:", part);
            return;
        }
        const category = categoryElement.textContent.split(": ")[1].trim().toLowerCase();
        const lowerSelectedCategories = selectedCategories.map(cat => cat.toLowerCase());
        const matchesCategory = lowerSelectedCategories.length === 0 || lowerSelectedCategories.includes(category);
        part.style.display = matchesCategory ? "" : "none";
    });
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
/* Updated Cart Icon Color */
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
.parts-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 40px;
}
.part-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border 0.3s ease;
    position: relative;
}
.part-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
}
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
    background: rgba(22, 22, 22, 0.64);
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
.part-card p {
    margin: 8px 0;
    font-size: 14px;
}
/* Center and compact the card actions */
.part-card .card-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}
.qty-btn {
    background-color: #d8dcde;
    border: 1px;
    border-radius: 5px;
    padding: 5px 20px;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-weight: 900;
}
.quantity-input {
    width: 50px;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    border: 2px solid #ccc;
    border-radius: 5px;
    margin: 0 5px;
}
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 40px;
    position: relative;
    width: 100%; 
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
    padding: 10px 20px; 
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.sort-option:hover {
    background-color: darkred; 
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
    background: darkorange
}

.cart-icon {
    color: #FFB52E;
}

.new-stock-btn {
    background: black;
    color: white;
}

.selected-card {
    border: 6px solid rgba(225, 15, 15, 0.7);
    animation: pulse 0.5s ease-out;
}
@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
    }
    70% {
        transform: scale(1.02);
        box-shadow: 0 0 10px 5px rgba(255, 0, 0, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
    }
}
</style>
