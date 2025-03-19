<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

// Handle search
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$limit = 10; // Number of parts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total part count for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM part WHERE archived = 1";
if (!empty($search)) {
    $totalQuery .= " AND (Name LIKE '%$search%' OR Make LIKE '%$search%' OR Model LIKE '%$search%')";
}
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);

// Fetch parts with pagination & filtering
$sql = "SELECT PartID, Name, Make, Model, Location, Quantity, Media FROM part WHERE archived = 1";
if (!empty($search)) {
    $sql .= " AND (Name LIKE '%$search%' OR Make LIKE '%$search%' OR Model LIKE '%$search%')";
}
$sql .= " ORDER BY DateAdded DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
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
            <input type="text" placeholder="Quick search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
        </div>
    </div>

    <div class="card-container">
        <?php
        if ($result->num_rows > 0) {
            while ($part = $result->fetch_assoc()) {
                $imageSrc = !empty($part['Media']) ? '/Drafter-Management-System/' . $part['Media'] : 'images/no-image.png';
                echo "
                    <div class='part-card'>
                        <a href='partdetail.php?id={$part['PartID']}'><img src='$imageSrc' alt='Part Image'></a>
                        <p><strong>Name:</strong> {$part['Name']}</p>
                        <p><strong>Make:</strong> {$part['Make']}</p>
                        <p><strong>Model:</strong> {$part['Model']}</p>
                        <p><strong>Location:</strong> {$part['Location']}</p>
                        <p><strong>Quantity:</strong> {$part['Quantity']}</p>
                        <div class='actions'>
                            <button class='red-button' onclick='relistPart({$part['PartID']})'>Re-list</button>
                        </div>
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
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function relistPart(partID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to re-list this part?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#6c5ce7",
        cancelButtonColor: "#d63031",
        confirmButtonText: "Yes, re-list it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('relist_part.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + partID
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Success!",
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

document.getElementById("searchInput").addEventListener("input", function () {
    const searchValue = this.value.trim();
    const currentUrl = new URL(window.location.href);

    if (searchValue) {
        currentUrl.searchParams.set("search", searchValue);
    } else {
        currentUrl.searchParams.delete("search");
    }

    currentUrl.searchParams.set("page", "1");

    // Update the URL without reloading the page
    window.history.replaceState({}, '', currentUrl.toString());

    // Fetch the updated parts
    fetch(currentUrl.toString())
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            document.querySelector('.card-container').innerHTML = doc.querySelector('.card-container').innerHTML;
            document.querySelector('.pagination').innerHTML = doc.querySelector('.pagination').innerHTML;
        })
        .catch(error => console.error("Error updating search results:", error));
});
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
.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    padding: 20px;
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
.actions {
    margin-top: 10px;
}
.red-button {
    background: rgb(88, 186, 35);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease;
}
.red-button:hover {
    background: rgb(48, 100, 20);
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
</style>