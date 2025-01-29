<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<?php include('dbconnect.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
  <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
    <h1 style="margin: 0;">Users</h1>
    <a href="usersadd.php" class="add-user-btn">+ Add User</a>
  </div>

  <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput" onkeyup="searchTable()">
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
      $sql = "SELECT UserID, CONCAT(FName, ' ', LName) AS Name, RoleType, Email, Status AS Status, LastLogin FROM user";
      $result = $conn->query($sql);

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

  <script>
    function searchTable() {
      const searchInput = document.getElementById("searchInput").value.toLowerCase();
      const table = document.getElementById("userTable");
      const rows = table.getElementsByTagName("tr");

      for (let i = 1; i < rows.length; i++) {  
        let cells = rows[i].getElementsByTagName("td");
        let match = false;

        for (let j = 0; j < cells.length; j++) {
          if (cells[j]) {
            if (cells[j].textContent.toLowerCase().indexOf(searchInput) > -1) {
              match = true;
              break;
            }
          }
        }

        if (match) {
          rows[i].style.display = "";
        } else {
          rows[i].style.display = "none";
        }
      }
    }
  </script>
</div>

<style>
  button, .add-user-btn {
    font-family: 'Poppins', sans-serif;
  }
  .header .add-user-btn {
    background-color: #E10F0F;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: auto;
    align-self: center;
  }

  .search-container {
    margin-bottom: 20px;
  }

  #searchInput {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
</style>
