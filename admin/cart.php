<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">
<div class="main-content">
    <div class="header">
        <h1>Your Cart</h1>
    </div>

    <div class="cart-container">
        <?php
        // Check if the cart exists in the session
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            echo "<ul>";
            foreach ($_SESSION['cart'] as $partID => $part) {
                echo "<li>{$part['Name']} - {$part['Make']} {$part['Model']} <a href='remove_from_cart.php?id={$partID}' class='remove-button'>Remove</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Your cart is empty.</p>";
        }
        ?>
    </div>

    <div class="actions">
        <a href="partslist.php" class="red-button">Continue Shopping</a>
        <a href="checkout.php" class="red-button">Checkout</a>
    </div>
</div>

<style>
.cart-container {
    margin: 20px 0;
}

.remove-button {
    color: red;
    text-decoration: none;
}
</style>