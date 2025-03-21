<?php
session_start();
include('dbconnect.php');

// Check if the user is logged in
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] != 'Staff') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}

// Check if the part ID is set in the POST request
if (isset($_POST['id'])) {
    $partID = intval($_POST['id']);

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT Name, Make, Model, Price, Media, Location FROM part WHERE PartID = ? AND archived = 0");
    $stmt->bind_param("i", $partID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the part exists
    if ($part = $result->fetch_assoc()) {
        // Initialize the cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if the part is already in the cart
        if (isset($_SESSION['cart'][$partID])) {
            // Increase the quantity if it is already in the cart
            $_SESSION['cart'][$partID]['Quantity'] += 1;
        } else {
            // Add the part to the cart
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

        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Part not found or archived']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>