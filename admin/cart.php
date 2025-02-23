<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="font-family: 'Poppins', sans-serif;">Your Cart</h1>
    </div>

    <div class="search-container">
    <input type="text" placeholder="Quick search" id="searchInput">
    <button onclick="searchCart()" class="red-button">Search</button>
</div>

    <div class="content">
        <div class="selection-list">
            <table>
                <thead>
                    <tr>
                        <th style="font-family: 'Poppins', sans-serif;">Product Details</th>
                        <th style="font-family: 'Poppins', sans-serif;">Quantity</th>
                        <th style="font-family: 'Poppins', sans-serif;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $partID => $part) {
                            // Calculate total price for the item
                            $totalPrice = (isset($part['Price']) ? $part['Price'] : 0) * (isset($part['Quantity']) ? $part['Quantity'] : 0);
                            echo "
                            <tr>
                                <td>
                                    <img src='" . (isset($part['Image']) ? $part['Image'] : 'default_image.png') . "' alt='" . (isset($part['Name']) ? $part['Name'] : 'Product') . "' class='product-image'>
                                    <div>
                                        <strong style='font-family: \"Poppins\", sans-serif;'>" . (isset($part['Name']) ? $part['Name'] : 'Unknown') . "</strong><br>
                                        <span style='font-family: \"Poppins\", sans-serif;'>" . (isset($part['Make']) ? $part['Make'] : 'Unknown') . " " . (isset($part['Model']) ? $part['Model'] : 'Unknown') . "</span><br>
                                        <span class='price' style='font-family: \"Poppins\", sans-serif;'>Php " . number_format((isset($part['Price']) ? $part['Price'] : 0), 2) . "</span><br>
                                        <span class='location' style='font-family: \"Poppins\", sans-serif;'>Location: " . (isset($part['Location']) ? $part['Location'] : 'Unknown') . "</span>
                                        <div class='button-container'>
                                            <button class='remove-btn' onclick='removeFromCart(\"$partID\")'>Remove</button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button class='qty-btn' onclick='updateQuantity(\"$partID\", -1)'>-</button>
                                    <input type='text' value='" . (isset($part['Quantity']) ? $part['Quantity'] : '0') . "' readonly class='quantity-input'>
                                    <button class='qty-btn' onclick='updateQuantity(\"$partID\", 1)'>+</button>
                                </td>
                                <td style='font-family: \"Poppins\", sans-serif;'>Php " . number_format($totalPrice, 2) . "</td>
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Your cart is empty.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="summary">
            <h2 style="font-family: 'Poppins', sans-serif;">Selected List Summary</h2>
            <p style="font-family: 'Poppins', sans-serif;">No. of Items: <strong><?php echo count($_SESSION['cart']); ?></strong></p>
            <p style="font-family: 'Poppins', sans-serif;">Total Cost: <strong>Php <?php echo number_format(array_sum(array_map(function($part) { return (isset($part['Price']) ? $part['Price'] : 0) * (isset($part['Quantity']) ? $part['Quantity'] : 0); }, $_SESSION['cart'])), 2); ?></strong></p>

            <button class="confirm-btn">Print Receipt</button>
        </div>
    </div>
</div>

<script>
    function removeFromCart(partID) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'partID=' + partID
        })
        .then(response => response.text())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function updateQuantity(partID, change) {
        fetch('update_quantity.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'partID=' + partID + '&change=' + change
        })
        .then(response => response.text())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    function searchCart() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll(".selection-list tbody tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .button-container {
        margin-top: 10px;
    }

    .remove-btn {
        background-color: gray;
        color: white;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        border-radius: 5px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
    }

    .remove-btn:hover {
        background-color: darkgray;
    }

    .qty-btn {
        background-color: red;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
    }

    .quantity-input {
        width: 40px;
        text-align: center; /* Center the text in the input */
        font-family: 'Poppins', sans-serif;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin: 0 5px;
    }

    .summary {
        margin-top: 30px;
        text-align: center;
    }

    .confirm-btn {
        background-color: red;
        color: white;
        font-size: 20px;
        padding: 15px 30px;
        border: none;
        cursor: pointer;
        border-radius: 8px;
        width: 100%;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        margin-top: 15px; 
        margin-bottom: 15px; 
    }

    .confirm-btn:hover {
        background-color: darkred;
    }

    .product-image {
        width: 100px; /* Set a fixed width for the image */
        height: auto; /* Maintain aspect ratio */
        margin-right: 10px; /* Space between image and text */
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
    }
    .search-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-container input[type="text"] {
    width: 300px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.search-container input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
}
.red-button {
    background: #E10F0F;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    transition: background 0.3s ease;
}
</style>