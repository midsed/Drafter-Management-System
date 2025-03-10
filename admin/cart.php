<?php
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle quantity update request (AJAX call)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partID'], $_POST['change'])) {
    $partID = $_POST['partID'];
    $change = intval($_POST['change']);

    if (isset($_SESSION['cart'][$partID])) {
        $_SESSION['cart'][$partID]['Quantity'] += $change;

        // Prevent quantity from going below 1
        if ($_SESSION['cart'][$partID]['Quantity'] < 1) {
            $_SESSION['cart'][$partID]['Quantity'] = 1;
        }
    }

    echo json_encode(['success' => true]);
    exit();
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
        <div class="parts-container" id="partsList">
            <?php
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $partID => $part) {
                    $imageSrc = !empty($part['Media']) ? $part['Media'] : 'images/no-image.png';
                    $totalPrice = floatval($part['Price']) * intval($part['Quantity']);
                    echo "
                    <div class='part-card'>
                        <a href='partdetail.php?id=$partID'><img src='$imageSrc' alt='Part Image'></a>
                        <p><strong>Name:</strong> {$part['Name']}</p>
                        <p><strong>Make:</strong> {$part['Make']}</p>
                        <p><strong>Model:</strong> {$part['Model']}</p>
                        <p><strong>Location:</strong> {$part['Location']}</p>
                        <p><strong>Price:</strong> Php " . number_format(floatval($part['Price']), 2) . "</p>
                        <p><strong>Quantity:</strong> {$part['Quantity']}</p>
                        <p><strong>Total:</strong> Php " . number_format($totalPrice, 2) . "</p>
                        <div class='actions'>
                            <button class='qty-btn' onclick='updateQuantity(\"$partID\", -1)'>-</button>
                            <input type='text' value='{$part['Quantity']}' readonly class='quantity-input'>
                            <button class='qty-btn' onclick='updateQuantity(\"$partID\", 1)'>+</button>
                            <button class='remove-btn' onclick='removeFromCart(\"$partID\")'>Remove</button>
                        </div>
                    </div>
                    ";
                }
            } else {
                echo "<p>Your cart is empty.</p>";
            }
            ?>
        </div>

        <div class="summary">
            <h2 style="font-family: 'Poppins', sans-serif;">Selected List Summary</h2>
            <p style="font-family: 'Poppins', sans-serif;">No. of Parts: <strong><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></strong></p>
            <p style="font-family: 'Poppins', sans-serif;">Total Cost: <strong>Php <?php
            echo number_format(array_sum(array_map(function ($part) {
                return floatval($part['Price']) * intval($part['Quantity']);
            }, $_SESSION['cart'])), 2);
            ?></strong></p>

                <button class="confirm-btn" onclick="printReceipt()">Print Receipt</button>
        </div>
    </div>
</div>

<script>
        function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }

    function printReceipt(userFullName, receiptID) {
    const parts = document.querySelectorAll('.part-card');
    let receiptHTML = `
        <div style="font-family: 'Poppins', sans-serif; max-width: 600px; margin: auto; text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
            <img src="../images/Drafter Black.png" alt="Drafter Autotech Black Logo" style="width: 240px; margin-top:-20px; margin-bottom: -70px;">
            <p style="color: #555; font-weight:"bold"; font-size: 18px; margin-bottom: 100px;">Inventory Management System</p>
            <p><strong>Receipt ID:</strong> ${receiptID}</p>
            <p><strong>Retrieved By:</strong> ${userFullName}</p>
            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
            <hr style="margin: 15px 0;">
            <h3 style="color: #333;">Parts Retrieved</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr style="background: #f5f5f5;">
                    <th style="padding: 8px; border-bottom: 2px solid #ddd;">Part Name</th>
                    <th style="padding: 8px; border-bottom: 2px solid #ddd;">Model</th>
                    <th style="padding: 8px; border-bottom: 2px solid #ddd;">Location</th>
                    <th style="padding: 8px; border-bottom: 2px solid #ddd;">Qty.</th>
                    <th style="padding: 8px; border-bottom: 2px solid #ddd;">Total Price</th>
                </tr>`;

    let totalCost = 0;

    parts.forEach(part => {
        const partName = part.querySelector('p:nth-child(2)').textContent.split(': ')[1];
        const partModel = part.querySelector('p:nth-child(4)').textContent.split(': ')[1];
        const partLocation = part.querySelector('p:nth-child(5)').textContent.split(': ')[1];
        const partPrice = parseFloat(part.querySelector('p:nth-child(6)').textContent.replace('Price: Php ', '').replace(',', ''));
        const partQuantity = parseInt(part.querySelector('.quantity-input').value);
        const totalPrice = partPrice * partQuantity;

        totalCost += totalPrice;

        receiptHTML += `
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${partName}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${partModel}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${partLocation}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${partQuantity}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">Php ${totalPrice.toFixed(2)}</td>
            </tr>`;
    });

    receiptHTML += `
            </table>
            <h3 style="margin-top: 20px; color: #333;">Total Cost: Php ${totalCost.toFixed(2)}</h3>
            <p style="font-size: 12px; color: #888;">Thank you for using Drafter Autotech's Inventory System!</p>
        </div>`;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(receiptHTML);
    printWindow.document.close();
    printWindow.print();
}




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
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'partID=' + encodeURIComponent(partID) + '&change=' + encodeURIComponent(change)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
    function searchCart() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const parts = document.querySelectorAll(".part-card");

        parts.forEach(part => {
            const text = part.textContent.toLowerCase();
            part.style.display = text.includes(input) ? "" : "none";
        });
    }
</script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .parts-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .part-card {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .part-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
    }

    .part-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
    }

    .part-card p {
        margin: 8px 0;
        font-size: 14px;
    }

    .part-card .actions {
        display: flex;
        justify-content: space-around;
        margin-top: 10px;
    }

    .part-card .actions button {
        padding: 6px 12px;
        font-size: 13px;
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
        text-align: center;
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
    @media print {
    body {
        -webkit-print-color-adjust: exact;
    }

    .part-card {
        display: none; /* Hide regular cart items during print */
    }

    h2, h3 {
        margin: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }
}
</style>