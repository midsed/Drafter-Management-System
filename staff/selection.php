<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        <h1 style="font-family: 'Poppins', sans-serif;">Parts Selection List</h1>
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
                    <tr>
                        <td>
                            <img src="images/brand1_model1.png" alt="Brand 1 Model 1" class="product-image">
                            <div>
                                <strong style="font-family: 'Poppins', sans-serif;">Brand 1</strong><br>
                                <span style="font-family: 'Poppins', sans-serif;">Model 1</span><br>
                                <span class="price" style="font-family: 'Poppins', sans-serif;">Php 25,000</span>
                                <div class="button-container">
                                    <button class="remove-btn">Remove</button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="qty-btn">-</button>
                            <input type="text" value="1" readonly style="font-family: 'Poppins', sans-serif;">
                            <button class="qty-btn">+</button>
                        </td>
                        <td style="font-family: 'Poppins', sans-serif;">Php 25,000</td>
                    </tr>
                    <tr>
                        <td>
                            <img src="images/brand3_model3.png" alt="Brand 3 Model 3" class="product-image">
                            <div>
                                <strong style="font-family: 'Poppins', sans-serif;">Brand 3</strong><br>
                                <span style="font-family: 'Poppins', sans-serif;">Model 3</span><br>
                                <span class="price" style="font-family: 'Poppins', sans-serif;">Php 30,000</span>
                                <div class="button-container">
                                    <button class="remove-btn">Remove</button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="qty-btn">-</button>
                            <input type="text" value="2" readonly style="font-family: 'Poppins', sans-serif;">
                            <button class="qty-btn">+</button>
                        </td>
                        <td style="font-family: 'Poppins', sans-serif;">Php 60,000</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="summary">
            <h2 style="font-family: 'Poppins', sans-serif;">Selected List Summary</h2>
            <p style="font-family: 'Poppins', sans-serif;">No. of Items: <strong>3</strong></p>
            <p style="font-family: 'Poppins', sans-serif;">Total Cost: <strong>Php 85,000</strong></p>
            <button class="confirm-btn">Confirm and Update Stock</button>
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
        color: white;
        border: none;
        padding: 5px 12px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        border-radius: 5px;
    }

    .qty-btn:hover {
        background-color: darkred;
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
        margin-top: 20px; /* Adjusted spacing */
        margin-bottom: 30px; /* Adjusted spacing */
    }

    .confirm-btn:hover {
        background-color: darkred;
    }
</style>
