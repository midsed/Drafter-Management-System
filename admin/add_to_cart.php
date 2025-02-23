<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized";
    exit();
}

if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['make']) && isset($_POST['model']) && isset($_POST['price']) && isset($_POST['image']) && isset($_POST['location'])) {
    $partID = $_POST['id'];
    $name = $_POST['name'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $price = $_POST['price']; // Ensure price is passed
    $image = $_POST['image']; // Ensure image URL is passed
    $location = $_POST['location']; // Ensure location is passed

    // Initialize cart if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item to cart or update quantity if already exists
    if (isset($_SESSION['cart'][$partID])) {
        $_SESSION['cart'][$partID]['Quantity'] += 1;
    } else {
        $_SESSION['cart'][$partID] = [
            'Name' => $name,
            'Make' => $make,
            'Model' => $model,
            'Price' => $price,
            'Image' => $image,
            'Location' => $location,
            'Quantity' => 1
        ];
    }

    echo "Item added to cart";
} else {
    echo "Invalid request";
}
?>