<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    echo "You must be logged in to add items to your cart.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partID = $_POST['id'];
    $name = $_POST['name'];
    $make = $_POST['make'];
    $model = $_POST['model'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][$partID] = [
        'Name' => $name,
        'Make' => $make,
        'Model' => $model
    ];

    echo "Item added to cart.";
}
?>