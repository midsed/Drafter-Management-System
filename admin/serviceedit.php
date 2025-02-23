<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
</style>

<div class="main-content">
        <div class="header">
            <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
                <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
            </a>
            <h1>Edit Service</h1>
        </div>

        <form id="entryForm">
            <div class="form-group">
                <label for="serviceid">Service ID:</label>
                <input type="text" id="serviceid" name="serviceid" required maxlength="64">
            </div>
            
            <div class="form-group">
                <label for="part">Part:</label>
                <input type="text" id="part" name="part" required>
            </div>
            
            <div class="form-group">
                <label for="customer">Customer</label>
                <input type="text" id="customer" name="customer" required>
            </div>
            
            <div class="form-group">
                <label for="price">Service Price:</label>
                <input type="text" id="price" name="price" required>
            </div>
            
            <button type="submit" class="btn">Update</button>
        </form>
    </div>

