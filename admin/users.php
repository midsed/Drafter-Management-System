<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
  <div class="header">
  <img src="https://i.ibb.co/J3LX32C/back.png" alt="Back" style="width: 20px; height: 20px; margin-right: 25px;">
  <h1 style="margin: 0;">Users</h1>
    <a href="usersadd.php" class="add-user-btn">+ Add User</a> </div>

  <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
  </div>

  <div class="table-container">
    <table>
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
        <tr>
          <td>#7676</td>
          <td>Johnny Wins</td>
          <td>Admin</td>
          <td>johnny43@email.com</td>
          <td>Active</td>
          <td>11/14/2024 10:22 AM</td>
          <td><button>Edit</button></td>
        </tr>
        <tr>
          <td>#7676</td>
          <td>Brim Stone</td>
          <td>Admin</td>
          <td>brimmy@email.com</td>
          <td>Inactive</td>
          <td>11/15/2024 09:45 AM</td>
          <td><button>Edit</button></td>
        </tr>
        <tr>
          <td>#7676</td>
          <td>Dante Gulapa</td>
          <td>Staff</td>
          <td>dantepogil23@gmail.com</td>
          <td>Active</td>
          <td>11/15/2024 09:45 AM</td>
          <td><button>Edit</button></td>
        </tr>
        <tr>
          <td>#7676</td>
          <td>Lerone James</td>
          <td>Staff</td>
          <td>lerone23@gmail.com</td>
          <td>Active</td>
          <td>11/15/2024 09:45 AM</td>
          <td><button>Edit</button></td>
        </tr>
        <tr>
          <td>#7676</td>
          <td>Santa Cruz</td>
          <td>Staff</td>
          <td>st2310@gmail.com</td>
          <td>Inactive</td>
          <td>11/15/2024 09:45 AM</td>
          <td><button>Edit</button></td>
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
    .header .add-user-btn {
    background-color: #F00;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: auto; 
    align-self: center;
  }
</style>