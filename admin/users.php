<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

$limit = 10; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total user count
$totalQuery = "SELECT COUNT(*) AS total FROM user";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);

// Fetch users with pagination
$sql = "SELECT UserID, CONCAT(FName, ' ', LName) AS Name, RoleType, Email, Status, LastLogin 
        FROM user 
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">

<div class="main-content">
  <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
    <h1 style="margin: 0;">Users</h1>
    <a href="usersadd.php" class="add-user-btn">+ Add User</a>
  </div>

  <!-- Search container -->
  <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
  </div>

  <div class="table-container">
    <table id="userTable">
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
      <tbody>
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
          echo "<td><a href='usersedit.php?UserID={$row['UserID']}'><button>Edit</button></a></td>";
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
      color: #fff;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-left: auto;
  }
</style>
