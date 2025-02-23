<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

if (isset($_GET['id'])) {
    $partID = $_GET['id'];

    // Remove the part from the cart
    if (isset($_SESSION['cart'][$partID])) {
        unset($_SESSION['cart'][$partID]);
    }

    header("Location: cart.php");
    exit();
}
?>