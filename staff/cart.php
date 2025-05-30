<?php
session_start();
include('dbconnect.php'); 

// Allow only Staff and Admin roles to access the cart page
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partID'], $_POST['change'])) {
    $partID = $_POST['partID'];
    $change = intval($_POST['change']);

    // Fetch the available stock for the part
    $stockQuery = "SELECT Quantity FROM part WHERE PartID = ?";
    $stmt = $conn->prepare($stockQuery);
    $stmt->bind_param("i", $partID);
    $stmt->execute();
    $result = $stmt->get_result();
    $part = $result->fetch_assoc();

    if ($part) {
        $availableStock = $part['Quantity'];
    
        if (isset($_SESSION['cart'][$partID])) {
            $newQuantity = $_SESSION['cart'][$partID]['Quantity'] + $change;
    
            // Check if the new quantity exceeds available stock
            if ($newQuantity > $availableStock) {
                echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock.']);
                exit();
            }
    
            // Update the quantity in the cart
            if ($newQuantity <= 0) {
                // Set the item to "Out of Stock"
                $_SESSION['cart'][$partID]['Quantity'] = 0; // Set quantity to 0
                $_SESSION['cart'][$partID]['Status'] = 'Out of Stock'; // Add a status field
            } else {
                $_SESSION['cart'][$partID]['Quantity'] = $newQuantity; // Update to new quantity
                unset($_SESSION['cart'][$partID]['Status']); // Remove status if quantity is above 0
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Part not found.']);
        exit();
    }

    echo json_encode(['success' => true]);
    exit();
}
?>

<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<!-- Rest of your HTML and PHP code -->

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="font-family: 'Poppins', sans-serif;">Your Parts</h1>
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
                    // Ensure the image path is correct
                    $imageSrc = !empty($part['Media']) ? '/Drafter-Management-System/' . $part['Media'] : 'images/no-image.png';
                    $totalPrice = floatval($part['Price']) * intval($part['Quantity']);
                    echo "
                    <div class='part-card'>
                        <a href='partdetail.php?id=$partID'><img src='$imageSrc' alt='Part Image'></a>
                        <p><strong>Name:</strong> {$part['Name']}</p>
                        <p><strong>Make:</strong> {$part['Make']}</p>
                        <p><strong>Model:</strong> {$part['Model']}</p>
                        <p><strong>Location:</strong> {$part['Location']}</p>
                        <p><strong>Price:</strong> ₱ " . number_format(floatval($part['Price']), 2) . "</p>
                        <p><strong>Quantity:</strong> {$part['Quantity']}</p>
                        <p><strong>Total:</strong> ₱ " . number_format($totalPrice, 2) . "</p>
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
                echo "<p>Your parts is empty.</p>";
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

    function printReceipt() {
        Swal.fire({
            title: 'Retriever Name',
            text: 'Please enter the name of the person retrieving these parts:',
            input: 'text', // This creates an input field
            inputPlaceholder: 'Enter name here...',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Retriever name is required!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const retrievedBy = result.value;

                // Create and submit the form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_receipt.php';
                form.style.display = 'none';

                const retrievedByInput = document.createElement('input');
                retrievedByInput.type = 'hidden';
                retrievedByInput.name = 'retrievedBy';
                retrievedByInput.value = retrievedBy;
                form.appendChild(retrievedByInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
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
            } else {
                swal({
                    title: "Error!",
                    text: data.message,
                    type: "error",
                    confirmButtonText: "OK"
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
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

    document.getElementById("searchInput").addEventListener("input", function () {
        searchCart();
    });
</script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .parts-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .part-card {
        width: 250px; /* Adjust as needed */
        background: #fff;
        border-radius: 8px;
        margin-top: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 15px;
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

    .remove-btn {
        background-color: red;
        color: white;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        border-radius: 5px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
    }

    .remove-btn:hover {
        background-color: gray;
    }

    .qty-btn {
        background-color: #d8dcde;
        border: 1px;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-weight: 900;
    }

    .quantity-input {
        width: 50px;
        text-align: center;
        font-family: 'Poppins', sans-serif;
        border: 2px solid #ccc;
        border-radius: 5px;
        margin: 0 5px;
    }

    .summary {
        margin-top: 20px;
        text-align: center;
    }

    .confirm-btn {
        background-color: #E10F0F;
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

    .red-button:hover {
        background: darkred;
    }
</style>