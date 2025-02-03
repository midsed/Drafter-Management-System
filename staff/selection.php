<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
        <a href="dashboard.php" style="text-decoration: none;"><i class="fa fa-arrow-left"></i> Back</a>
        <h1>Parts Selection List</h1>
    </div>

    <div class="content">
        <div class="selection-list">
            <table>
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <img src="images/brand1_model1.png" alt="Brand 1 Model 1" class="product-image">
                            <div>
                                <strong>Brand 1</strong><br>
                                Model 1<br>
                                <span class="price">Php 25,000</span>
                            </div>
                        </td>
                        <td>
                            <button>-</button>
                            <input type="text" value="1" readonly>
                            <button>+</button>
                        </td>
                        <td>Php 25,000</td>
                    </tr>
                    <tr>
                        <td>
                            <img src="images/brand3_model3.png" alt="Brand 3 Model 3" class="product-image">
                            <div>
                                <strong>Brand 3</strong><br>
                                Model 3<br>
                                <span class="price">Php 30,000</span>
                            </div>
                        </td>
                        <td>
                            <button>-</button>
                            <input type="text" value="2" readonly>
                            <button>+</button>
                        </td>
                        <td>Php 60,000</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="summary">
            <h2>Selected List Summary</h2>
            <p>No. of Items: <strong>3</strong></p>
            <p>Total Cost: <strong>Php 85,000</strong></p>
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
