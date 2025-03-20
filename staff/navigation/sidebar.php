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
                <img src="images/parts1.png" alt="Parts Icon" class="nav-icon">
                <span>Parts</span> 
            </a>
        </li>
        <li class="<?php echo ($current_page == 'receipts.php') ? 'active' : ''; ?>">
            <a href="receipts.php">
                <img src="images/receipts1.png" alt="Transactions Icon" class="nav-icon">
                <span>Receipts</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'service.php') ? 'active' : ''; ?>">
            <a href="service.php">
                <img src="images/service.png" alt="Services Icon" class="nav-icon">
                <span>Services</span> 
            </a>
        </li>
        <li class="<?php echo ($current_page == 'supplier.php') ? 'active' : ''; ?>">
            <a href="supplier.php">
                <img src="images/supplier.png" alt="Suppliers Icon" class="nav-icon">
                <span>Suppliers</span> 
            </a>
        </li>
        <li class="<?php echo ($current_page == 'faq.php') ? 'active' : ''; ?>">
            <a href="faq.php">
                <img src="images/supplier.png" alt="Suppliers Icon" class="nav-icon">
                <span>FAQ</span> 
            </a>
        </li>
    </ul>
</div>
<style>
.sidebar li.active a span {
    color:rgb(255, 255, 255);
    font-weight: 700;
}

.sidebar li.active {
    border-left: 4px solid #E10F0F
}
</style>