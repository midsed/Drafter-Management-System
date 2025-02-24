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
        <h1 style="margin: 0;">Supplier</h1>
        <div class="actions">
            <a href="supplierarchive.php" class="btn btn-archive">Archives</a>
            <a href="addsupplier.php" class="btn btn-add">+ Add Supplier</a>
        </div>
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
                    <th>Edit Supplier</th>
                    <th>Archive</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#7676</td>
                    <td>3</td>
                    <td>toyotacarsmindanaoave@gmail.com</td>
                    <td>Toyota Cars Mindanao Ave.</td>
                    <td>445-4865</td>
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

<style>
.actions a.btn, .actions button.btn {
    color: white !important;
}

.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    color: white;
}

.btn-archive, .btn-add, .btn-edit {
    background-color: #E10F0F;
    color: white;
}

.actions {
    text-align: right;
    width: 100%;
}

.actions .btn {
    margin-left: 10px;
}

.table-container {
    margin-top: 20px;
    width: 100%;
}

.supplier-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; 
    margin-top: 10px;
}

.supplier-table th,
.supplier-table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
    word-wrap: break-word;
}

.supplier-table th {
    background-color: #f4f4f4;
}

.supplier-table td a, .supplier-table td button {
    display: inline-block;
    width: 80px;
    text-align: center;
    padding: 6px;
}
/* Adjusted Container */
.search-container input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    font-size: 14px;
}
</style>
