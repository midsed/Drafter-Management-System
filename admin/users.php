<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

// Handle search and role filter
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : []; // Role filter array
$limit = 10; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total user count for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM user WHERE 1=1";
if (!empty($search)) {
    $totalQuery .= " AND (FName LIKE '%$search%' OR LName LIKE '%$search%' OR RoleType LIKE '%$search%' OR Email LIKE '%$search%')";
}
if (!empty($roleFilter)) {
    $roleFilterSQL = implode("','", $roleFilter);
    $totalQuery .= " AND RoleType IN ('$roleFilterSQL')";
}
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);

// Fetch users with pagination & filtering
$sql = "SELECT UserID, CONCAT(FName, ' ', LName) AS Name, RoleType, Email, Status, LastLogin 
        FROM user 
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (FName LIKE '%$search%' OR LName LIKE '%$search%' OR RoleType LIKE '%$search%' OR Email LIKE '%$search%')";
}
if (!empty($roleFilter)) {
    $sql .= " AND RoleType IN ('$roleFilterSQL')";
}

$sql .= " ORDER BY UserID DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">

<div class="main-content">
  <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
    <h1 style="margin: 0;">Users</h1>
    <a href="usersadd.php" class="add-user-btn btn">+ Add User</a>
  </div>

  <!-- Search container -->
  <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
  </div>

   <!-- Role Filter -->
   <div class="filter-container">
            <span>Filter by Role:</span>
            <label><input type="checkbox" class="role-filter" value="Admin" <?= in_array("Admin", $roleFilter) ? "checked" : "" ?>> Admin</label>
            <label><input type="checkbox" class="role-filter" value="Staff" <?= in_array("Staff", $roleFilter) ? "checked" : "" ?>> Staff</label>
            <button id="applyFilter" class="btn btn-filter">Apply</button>
        </div>

  <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
            <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#{$row['UserID']}</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['RoleType']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['LastLogin'] ?? 'Never') . "</td>";
                        echo '<td><a href="usersedit.php?UserID=' . $row['UserID'] . '" class="btn btn-edit">Edit</a></td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found.</td></tr>";
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

            $visiblePages = 5; // Number of pages to display
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $startPage + $visiblePages - 1);

            if ($endPage - $startPage < $visiblePages - 1) {
                $startPage = max(1, $endPage - $visiblePages + 1);
            }
            ?>

            <!-- First Button -->
            <?php if ($page > 1): ?>
                <a href="?<?= $queryString ?>&page=1" class="pagination-button">First</a>
            <?php endif; ?>

            <!-- Previous Button -->
            <?php if ($page > 1): ?>
                <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
            <?php endif; ?>

            <!-- Page Number Buttons -->
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?<?= $queryString ?>&page=<?= $i ?>" class="pagination-button <?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <!-- Next Button -->
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
            <?php endif; ?>

            <!-- Last Button -->
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $queryString ?>&page=<?= $totalPages ?>" class="pagination-button">Last</a>
            <?php endif; ?>
        </div>
    </div>

<script>
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
  // 🔍 Real-time search function
  function searchTable() {
      const searchInput = document.getElementById("searchInput").value.toLowerCase();
      const rows = document.querySelectorAll("#userTable tbody tr");

      rows.forEach(row => {
          const rowText = row.textContent.toLowerCase();
          row.style.display = rowText.includes(searchInput) ? "" : "none";
      });
  }

  document.getElementById("searchInput").addEventListener("input", searchTable);
</script>

<style>
  .search-container {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
  }

  .search-container input[type="text"] {
      width: 300px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
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

  .add-user-btn {
    background-color: #E10F0F; 
    color: white !important; /* Ensure text remains white */
    padding: 10px 15px; 
    border: none;
    border-radius: 5px; 
    cursor: pointer; 
    margin-left: auto; 
    text-decoration: none; 
    font-family: 'Poppins', sans-serif; 
    font-size: 14px;
}

  .btn-filter {
    background-color: #E10F0F;
    color: white;
    padding: 10px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    text-decoration: none;
}

.btn-filter:hover {
    background-color: #C00D0D;
}
</style>
