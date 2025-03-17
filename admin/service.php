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
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : ''; 
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total service count
$totalQuery = "SELECT COUNT(*) AS total FROM service WHERE Archived = 0";
if (!empty($search)) {
    $totalQuery .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%')";
}
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);

$sql = "SELECT 
            s.ServiceID, 
            s.Type, 
            s.Price, 
            c.FName AS CustomerFName, 
            c.LName AS CustomerLName, 
            s.ClientEmail, 
            c.PhoneNumber, 
            s.StaffName, 
            COALESCE(p.Name, 'N/A') AS PartName
        FROM service s
        LEFT JOIN client c ON s.ClientEmail = c.ClientEmail
        LEFT JOIN part p ON s.PartID = p.PartID
        WHERE s.Archived = 0";

if (!empty($search)) {
    $sql .= " AND (s.Type LIKE '%$search%' OR c.FName LIKE '%$search%' OR c.LName LIKE '%$search%' OR s.ClientEmail LIKE '%$search%')";
}

$sql .= " ORDER BY s.ServiceID DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

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
    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
    </div>

    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Service Type</th>
                    <th>Service Price</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Customer Number</th>
                    <th>Staff Name</th>
                    <th>Part Name</th>
                    <th>Edit</th>
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
                        echo "<td>" . htmlspecialchars($row['CustomerFName'] . ' ' . $row['CustomerLName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ClientEmail'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['PhoneNumber'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['StaffName'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['PartName']) . "</td>";
                        echo '<td><a href="serviceedit.php?id=' . $row['ServiceID'] . '" class="btn btn-edit">Edit</a></td>';
                        echo '<td><button class="btn btn-archive" onclick="archiveService(' . $row['ServiceID'] . ')">Archive</button></td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No services found.</td></tr>";
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
    .swal2-confirm { font-weight: bold !important; background-color: #6c5ce7 !important; color: white !important; }
    .swal2-cancel { font-weight: bold !important; background-color: #d63031 !important; color: white !important; }
</style>

<script>
// 🟢 Improved Sidebar Toggle Function
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

    window.history.replaceState({}, '', currentUrl.toString());

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


// 🗂️ Archive Service with SweetAlert2
function archiveService(serviceID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to archive this service?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
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
                    confirmButtonColor: "#28a745"
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

    .supplier-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .supplier-table th,
    .supplier-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
    .supplier-table th {
        background-color: #f4f4f4;
    }
    .supplier-table tr:hover {
        background-color: #f1f1f1;
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
