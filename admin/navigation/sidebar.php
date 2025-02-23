<?php 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <button class="toggle-btn">&#9776;</button>
    <ul>
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <img src="images/sideicon.png" alt="Dashboard Icon" class="nav-icon">
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'parts.php') ? 'active' : ''; ?>">
            <a href="parts.php">
                <img src="images/sideicon.png" alt="Parts Icon" class="nav-icon">
                <span>Parts</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'receipts.php') ? 'active' : ''; ?>">
            <a href="receipts.php">
                <img src="images/sideicon.png" alt="Transactions Icon" class="nav-icon">
                <span>Receipts</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>">
            <a href="logs.php">
                <img src="images/sideicon.png" alt="Logs Icon" class="nav-icon">
                <span>Logs</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <a href="users.php">
                <img src="images/sideicon.png" alt="Users Icon" class="nav-icon">
                <span>Users</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'supplier.php') ? 'active' : ''; ?>">
            <a href="supplier.php">
                <img src="images/sideicon.png" alt="Suppliers Icon" class="nav-icon">
                <span>Suppliers</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'service.php') ? 'active' : ''; ?>">
            <a href="service.php">
                <img src="images/sideicon.png" alt="Services Icon" class="nav-icon">
                <span>Services</span>
            </a>
        </li>
    </ul>
</div>
<style>
 .sidebar li.active a span {
    color: #ff0000;
    font-weight: 700;
}

.sidebar li.active {
    border-left: 4px solid #ff0000;
}
</style>