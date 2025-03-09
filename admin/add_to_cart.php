<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized";
    exit();
}

if (isset($_POST['id'])) {
    $partID = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT Name, Make, Model, Price, Media, Location FROM part WHERE PartID = ? AND archived = 0");
    $stmt->bind_param("i", $partID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($part = $result->fetch_assoc()) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$partID])) {
            $_SESSION['cart'][$partID]['Quantity'] += 1;
        } else {
            $_SESSION['cart'][$partID] = [
                'Name' => $part['Name'],
                'Make' => $part['Make'],
                'Model' => $part['Model'],
                'Price' => $part['Price'],
                'Media' => $part['Media'],
                'Location' => $part['Location'],
                'Quantity' => 1
            ];
        }

        echo "Item added to cart";
    } else {
        echo "Part not found or archived";
    }

    $stmt->close();
} else {
    echo "Invalid request";
}
?>
