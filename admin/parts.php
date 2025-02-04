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
        </div>
        <div class="right-actions">
            <button class="red-button">Cart</button>
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

        $sql = "SELECT PartID, Name, Make, Model, Location, Quantity, Media FROM part LIMIT $limit OFFSET $offset";
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
                            <a href='partsedit.php?id={$part['PartID']}' class='edit-button'>Edit</a>
                            <button class='red-button'>Archive</button>
                            <button class='red-button'>Add to Cart</button>
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

<script>
function searchParts() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const parts = document.querySelectorAll(".part-card");

    parts.forEach(part => {
        const text = part.textContent.toLowerCase();
        part.style.display = text.includes(input) ? "" : "none";
    });
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
    gap: 10px;
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

.white-button {
    background: white;
    color: black;
    border: 1px solid #ccc;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: background 0.3s ease;
}

.white-button:hover {
    background: #f0f0f0;
    border-color: #888;
}

.edit-button {
    background: grey;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
}

.edit-button:hover {
    background: #555;
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
</style>
