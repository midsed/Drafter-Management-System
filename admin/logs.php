<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
  <div class="header">
    <a href="dashboard.php" style="text-decoration: none; display: flex; align-items: center;">
    <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    <h1 style="margin: 0;">Logs</h1>
    </a>
  </div>
  <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Log ID</th>
          <th>Action By</th>
          <th>Action Type</th>
          <th>Timestamp</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Admin Name N.</td>
          <td>#7676</td>
          <td>Admin Name N.</td>
          <td>New Location</td>
          <td>2024-11-15 10:00:00</td>
        </tr>
        <tr>
          <td>Staff Name N.</td>
          <td>#7677</td>
          <td>Staff Name N.</td>
          <td>Parts Archived</td>
          <td>2024-11-31 10:00:00</td>
        </tr>
        <tr>
          <td>Admin - Name N.</td>
          <td>#7678</td>
          <td>Admin - Name N.</td>
          <td>New Office Stoff Account</td>
          <td>2024-11-3 10:00:00</td>
        </tr>
        <tr>
          <td>Admin Name N</td>
          <td>#7679</td>
          <td>Admin Name N</td>
          <td>Account Archived</td>
          <td>2024-11-6 10:00:00</td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const mainContent = document.querySelector('.main-content');

      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('collapsed');
    }
  </script>
</div>

<style>
  .header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .header a {
    color: #000;
    text-decoration: none;
    display: flex;
    align-items: center;
  }

  .header img {
    margin-right: 10px;
  }

  .header h1 {
    margin: 0;
    font-size: 32px;
  }

  .search-container {
    text-align: center;
  }

  .search-container input[type="text"] {
    width: 300px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
  }

  .search-container input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
  }

  .search-container .fa-search {
    position: absolute;
    right: 10px;
    top: 12px;
    color: #999;
  }
</style>
