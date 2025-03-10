<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Parts List</h1>
    </div>

    <div class="search-actions">
        <div class="search-container">
            <input type="text" placeholder="Quick search" id="searchInput">
            <button onclick="searchParts()" class="red-button">Search</button>
            <div class="filter-container">
                <span>Filter</span>
                <div class="dropdown">
                    <button id="filterButton" class="filter-icon" title="Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content">
                        <!-- Make Filter -->
                        <div class="filter-section">
                            <h4>Make</h4>
                            <div class="filter-options" id="makeFilter">
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Toyota"> Toyota</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Honda"> Honda</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Ford"> Ford</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Chevrolet"> Chevrolet</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Nissan"> Nissan</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="BMW"> BMW</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Mercedes-Benz"> Mercedes-Benz</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Audi"> Audi</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Hyundai"> Hyundai</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Kia"> Kia</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Volkswagen"> Volkswagen</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Subaru"> Subaru</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Mazda"> Mazda</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Lexus"> Lexus</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Jeep"> Jeep</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Tesla"> Tesla</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="BYD"> BYD</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Suzuki"> Suzuki</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Mitsubishi"> Mitsubishi</label>
                                <label><input type="checkbox" class="filter-option" data-filter="make" value="Isuzu"> Isuzu</label>
                            </div>
                        </div>

                        <!-- Model Filter -->
                        <div class="filter-section">
                            <h4>Model</h4>
                            <div class="filter-options" id="modelFilter">
                                <!-- Models will be dynamically populated here -->
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="filter-actions">
                            <button id="applyFilter" class="red-button">Apply</button>
                            <button id="clearFilter" class="red-button">Clear</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sort Dropdown -->
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
            <a href="partsarchive.php" class="red-button">Archives</a>
            <a href="partsadd.php" class="red-button">+ New Stock</a>
        </div>
    </div>

    <div class="parts-container" id="partsList">
        <?php
        $limit = 8;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $totalQuery = "SELECT COUNT(*) AS total FROM part";
        $totalResult = $conn->query($totalQuery);
        $totalRow = $totalResult->fetch_assoc();
        $totalPages = ceil($totalRow['total'] / $limit);

        $sql = "SELECT PartID, Name, Make, Model, Location, Quantity, Media FROM part WHERE archived = 0 LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($part = $result->fetch_assoc()) {
                $imageSrc = !empty($part['Media']) ? $part['Media'] : 'images/no-image.png';
                echo "
                    <div class='part-card'>
                        <a href='partdetail.php?id={$part['PartID']}'><img src='$imageSrc' alt='Part Image'></a>
                        <p><strong>Name:</strong> {$part['Name']}</p>
                        <p><strong>Make:</strong> {$part['Make']}</p>
                        <p><strong>Model:</strong> {$part['Model']}</p>
                        <p><strong>Location:</strong> {$part['Location']}</p>
                        <p><strong>Quantity:</strong> {$part['Quantity']}</p>
                        <div class='actions'>
                            <a href='partsedit.php?id={$part['PartID']}' class='red-button'>Edit</a>
                            <button class='red-button' onclick='archivePart({$part['PartID']})'>Archive</button>
                            <button class='red-button' onclick='addToCart({$part['PartID']}, \"{$part['Name']}\", \"{$part['Make']}\", \"{$part['Model']}\")'>Add to Cart</button>
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
            <a href="?page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="pagination-button">Next</a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
    .swal2-popup { font-family: "Inter", sans-serif !important; }
    .swal2-title { font-weight: 700 !important; }
    .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
    .swal2-confirm { font-weight: bold !important; background-color: #6c5ce7 !important; color: white !important; }
    .swal2-cancel { font-weight: bold !important; background-color: #d63031 !important; color: white !important; }
</style>

<script>
function archivePart(partID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to archive this part?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#6c5ce7",
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
                    confirmButtonColor: "#6c5ce7"
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
                    confirmButtonColor: "#d63031"
                });
            });
        }
    });
}

// Search functionality
function searchParts() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const parts = document.querySelectorAll(".part-card");

    parts.forEach(part => {
        const text = part.textContent.toLowerCase();
        part.style.display = text.includes(input) ? "" : "none";
    });
}

function addToCart(partID, name, make, model, price, image, location) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${partID}&name=${encodeURIComponent(name)}&make=${encodeURIComponent(make)}&model=${encodeURIComponent(model)}&price=${price}&image=${encodeURIComponent(image)}&location=${encodeURIComponent(location)}`
    })
    .then(response => response.text())
    .then(data => {
        Swal.fire({
            title: "Added to Cart!",
            text: data,
            icon: "success",
            confirmButtonColor: "#6c5ce7"
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
            confirmButtonColor: "#d63031"
        });
    });
}

// DOMContentLoaded event listener
document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown = document.getElementById("sortDropdown");
    const filterButton = document.getElementById("filterButton");
    const sortButton = document.getElementById("sortButton");
    const applyFilterButton = document.getElementById("applyFilter");
    const clearFilterButton = document.getElementById("clearFilter");
    const makeFilter = document.getElementById("makeFilter");
    const modelFilter = document.getElementById("modelFilter");

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
        const selectedMakes = Array.from(document.querySelectorAll('.filter-option[data-filter="make"]:checked')).map(checkbox => checkbox.value);
        const selectedModels = Array.from(document.querySelectorAll('.filter-option[data-filter="model"]:checked')).map(checkbox => checkbox.value);
        filterParts(selectedMakes, selectedModels);
    });

    // Clear filter
    clearFilterButton.addEventListener("click", function () {
        document.querySelectorAll('.filter-option').forEach(checkbox => checkbox.checked = false);
        filterParts([], []);
    });

    // Sort functionality
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function () {
            sortParts(option.dataset.sort);
            sortDropdown.classList.remove("show");
        });
    });

    // Dynamic model filtering
    makeFilter.addEventListener("change", function (event) {
        const selectedMake = event.target.value;
        if (event.target.checked) {
            updateModelFilter(selectedMake);
        } else {
            updateModelFilter();
        }
    });

    // Initial model filter update
    updateModelFilter();
});

// Filter parts based on selected makes and models
function filterParts(selectedMakes, selectedModels) {
    const parts = document.querySelectorAll(".part-card");
    parts.forEach(part => {
        const make = part.querySelector("p:nth-child(2)").textContent.split(": ")[1].trim().toLowerCase();
        const model = part.querySelector("p:nth-child(3)").textContent.split(": ")[1].trim().toLowerCase();

        const lowerSelectedMakes = selectedMakes.map(make => make.toLowerCase());
        const lowerSelectedModels = selectedModels.map(model => model.toLowerCase());

        const matchesMake = lowerSelectedMakes.length === 0 || lowerSelectedMakes.includes(make);
        const matchesModel = lowerSelectedModels.length === 0 || lowerSelectedModels.includes(model);

        part.style.display = matchesMake && matchesModel ? "" : "none";
    });
}

// Sort parts by name (ascending or descending)
function sortParts(order) {
    const partsContainer = document.getElementById("partsList");
    const partsArray = Array.from(partsContainer.children);

    partsArray.sort((a, b) => {
        const nameA = a.querySelector("p").textContent.toLowerCase(); // Get the part name from the first <p> tag
        const nameB = b.querySelector("p").textContent.toLowerCase(); // Get the part name from the first <p> tag

        if (order === "asc") {
            return nameA.localeCompare(nameB); // Sort in ascending order
        } else if (order === "desc") {
            return nameB.localeCompare(nameA); // Sort in descending order
        }
    });

    // Clear the current parts list
    partsContainer.innerHTML = "";

    // Append the sorted parts back to the container
    partsArray.forEach(part => partsContainer.appendChild(part));
}

// Update model filter based on selected makes
function updateModelFilter() {
    const modelFilter = document.getElementById("modelFilter");
    modelFilter.innerHTML = "";

    const models = {
        Toyota: ["Camry", "Corolla", "Vios", "Fortuner", "Innova", "Hilux", "RAV4", "Land Cruiser"],
        Honda: ["Civic", "Accord", "CR-V", "City", "BR-V", "HR-V", "Jazz", "Brio"],
        Ford: ["Mustang", "Ranger", "Everest", "EcoSport", "Explorer", "Fiesta", "Focus", "Territory"],
        Chevrolet: ["Trailblazer", "Colorado", "Spark", "Cruze", "Trax", "Captiva", "Malibu", "Tahoe"],
        Nissan: ["Altima", "Navara", "Terra", "Sentra", "Juke", "Patrol", "Kicks", "Almera"],
        BMW: ["3 Series", "5 Series", "X1", "X3", "X5", "X7", "7 Series", "M3"],
        MercedesBenz: ["C-Class", "E-Class", "S-Class", "GLC", "GLE", "GLA", "A-Class", "G-Class"],
        Audi: ["A4", "A6", "Q3", "Q5", "Q7", "A3", "A5", "Q2"],
        Hyundai: ["Elantra", "Tucson", "Santa Fe", "Accent", "Creta", "Kona", "Staria", "Venue"],
        Kia: ["Seltos", "Sorento", "Sportage", "Stonic", "Picanto", "Carnival", "Rio", "Forte"],
        Volkswagen: ["Golf", "Tiguan", "Santana", "Lavida", "Passat", "Touareg", "Polo", "Teramont"],
        Subaru: ["Outback", "Forester", "XV", "Impreza", "BRZ", "Legacy", "Crosstrek", "WRX"],
        Mazda: ["CX-5", "CX-9", "Mazda3", "Mazda6", "CX-30", "MX-5", "BT-50", "CX-8"],
        Lexus: ["RX", "NX", "ES", "LS", "UX", "GX", "LX", "IS"],
        Jeep: ["Wrangler", "Cherokee", "Compass", "Renegade", "Gladiator", "Grand Cherokee", "Commander", "Patriot"],
        Tesla: ["Model 3", "Model S", "Model X", "Model Y", "Cybertruck", "Roadster"],
        BYD: ["Han", "Tang", "Song", "Qin", "Yuan", "Dolphin", "Seal", "Atto 3"],
        Suzuki: ["Ertiga", "Swift", "Vitara", "Jimny", "Ciaz", "APV", "Carry", "XL7"],
        Mitsubishi: ["Montero Sport", "Mirage", "Strada", "Xpander", "Outlander", "Eclipse Cross", "L300", "Pajero"],
        Isuzu: ["D-Max", "MU-X", "Traviz", "N-Series", "F-Series", "Rodeo", "Alterra", "Crosswind"]
    };

    const selectedMakes = Array.from(document.querySelectorAll('.filter-option[data-filter="make"]:checked')).map(cb => cb.value);
    const modelSet = new Set();

    selectedMakes.forEach(make => {
        if (models[make]) {
            models[make].forEach(model => {
                modelSet.add(model);
            });
        }
    });

    modelSet.forEach(model => {
        modelFilter.innerHTML += `<label><input type="checkbox" class="filter-option" data-filter="model" value="${model}"> ${model}</label>`;
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
    min-width: 500px;
    max-height: 500px;
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

.sort-option.red-button {
    display: block;
    width: 100%;
    text-align: left;
    margin: 5px 0;
    background-color: white;
    color: #E10F0F;
    border: 1px solid #E10F0F;
}
</style>
