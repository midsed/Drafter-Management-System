<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
        <h1>Archived Supplier</h1>
    </div>
    <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
    </div>
    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Part ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#7676</td>
                    <td>3</td>
                    <td>toyotacarsmindanaoave@gmail.com</td>
                    <td>Toyota Cars Mindanao Ave.</td>
                    <td>445-4865</td>
                    <td><button class="btn btn-unarchive">Unarchive</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
</script>
