<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized";
    exit();
}

if (isset($_POST['partID'])) {
    $partID = $_POST['partID'];
    if (isset($_SESSION['cart'][$partID])) {
        unset($_SESSION['cart'][$partID]);
        echo "Item removed";
    } else {
        echo "Item not found in cart";
    }
} else {
    echo "Invalid request";
}
?>