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

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Edit Parts</h1>
    </div>

    <div class="center-container">
        <form action="partsadd_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="part_name">Part Name:</label>
                <input type="text" id="part_name" name="part_name" required>
            </div>

            <div class="form-group">
                <label for="part_price">Part Price:</label>
                <input type="number" id="part_price" name="part_price" required>
            </div>

            <div class="form-group">
                <label for="make">Make:</label>
                <input type="text" id="make" name="make" required>
            </div>

            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" id="model" name="model" required>
            </div>

            <div class="form-group">
                <label for="year_model">Year Model:</label>
                <input type="text" id="year_model" name="year_model" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="authenticity">Authenticity:</label>
                <select id="authenticity" name="authenticity">
                    <option value="Genuine">Genuine</option>
                    <option value="Replacement">Replacement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="Engine Suspension">Engine Suspension</option>
                    <option value="Body Panel">Body Panel</option>
                    <option value="Interior">Interior</option>
                </select>
            </div>

            <div class="form-group">
                <label for="condition">Condition:</label>
                <select id="condition" name="condition">
                    <option value="New">New</option>
                    <option value="Used">Used</option>
                    <option value="For Repair">For Repair</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Item Status:</label>
                <select id="status" name="status">
                    <option value="Available">Available</option>
                    <option value="Used for Service">Used for Service</option>
                    <option value="Surrendered">Surrendered</option>
                </select>
            </div>

            <div class="form-group">
                <label for="part_image">Upload Image:</label>
                <input type="file" id="part_image" name="part_image">
            </div>

            <div class="actions">
                <button type="submit" class="black-button btn">Update</button>
                <button type="reset" class="red-button btn">Clear</button>
            </div>
        </form>
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

<style> /* Adjusted Style */
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input, select, textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 14px;
    }
    textarea {
        resize: vertical;
        height: 100px;
    }
    .btn {
        background-color: #272727;
        font-family: 'Poppins', sans-serif;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .black-button {
        background-color: #272727;
    }
    .black-button:hover {
        background-color: #444;
    }
    .red-button {
        background-color: red;
    }
    .red-button:hover {
        background-color: darkred;
    }
    .actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .center-container {
        width: 50%;
        max-width: 1000px;
        margin: 0 auto; 
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }
</style>
