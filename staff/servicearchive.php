<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
        <h1>Archived Service</h1>
    </div>
    <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
    </div>
    <div class="table-container">
        <table class="supplier-table">
            <thead>
            <tr>
                    <th>Service Type</th>
                    <th>Service Price</th>
                    <th>Service ID</th>
                    <th>Customer</th>
                    <th>Staff</th>
                    <th>Part ID</th>
                    <th>Edit Supplier</th>
                    <th>Archive</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>sample</td>
                    <td>sample</td>
                    <td>sample</td>
                    <td>sample</td>
                    <td>sample</td>
                    <td>sample</td>
                    <td><a href="editsupplier.php?id=7676" class="btn btn-edit">Edit</a></td>
                    <td><button class="btn btn-archive">Archive</button></td>
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
