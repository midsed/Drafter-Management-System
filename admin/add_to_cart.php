<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized";
    exit();
}

if (isset($_POST['id'], $_POST['name'], $_POST['make'], $_POST['model'], $_POST['price'], $_POST['media'], $_POST['location'])) {
    $partID = $_POST['id'];
    $name = $_POST['name'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $price = $_POST['price'];
    $media = $_POST['media'];
    $location = $_POST['location'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$partID])) {
        $_SESSION['cart'][$partID]['Quantity'] += 1;
    } else {
        $_SESSION['cart'][$partID] = [
            'Name' => $name,
            'Make' => $make,
            'Model' => $model,
            'Price' => $price,
            'Media' => $media,
            'Location' => $location,
            'Quantity' => 1
        ];
    }

    echo "Item added to cart";
} else {
    echo "Invalid request";
}
?>
