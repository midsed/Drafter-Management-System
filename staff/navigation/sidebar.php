<?php 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <ul class="nav-list">
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <img src="images/sideicon.png" alt="Dashboard Icon" class="nav-icon">
                <span>Home</span> 
            </a>
        </li>
        <li class="<?php echo ($current_page == 'parts.php') ? 'active' : ''; ?>">
            <a href="parts.php">
                <img src="images/sideicon.png" alt="Parts Icon" class="nav-icon">
                <span>Parts</span> 
            </a>
        </li>
        <li class="<?php echo ($current_page == 'service.php') ? 'active' : ''; ?>">
            <a href="service.php">
                <img src="images/sideicon.png" alt="Services Icon" class="nav-icon">
                <span>Services</span> 
            </a>
        </li>
        <li class="<?php echo ($current_page == 'supplier.php') ? 'active' : ''; ?>">
            <a href="supplier.php">
                <img src="images/sideicon.png" alt="Suppliers Icon" class="nav-icon">
                <span>Suppliers</span> 
            </a>
        </li>
    </ul>
</div>
