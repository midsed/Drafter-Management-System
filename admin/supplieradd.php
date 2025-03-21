

<?php
session_start();
if (isset($_SESSION['UserID']) && $_SESSION['RoleType'] === 'Staff') {
    echo "<script>
            alert('Unauthorized access.');
            window.location.href = '/Drafter-Management-System/login.php';
          </script>";
    exit();
}
include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

    .center-container {
        width: 50%;
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif;
    }

    .header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .header img {
        cursor: pointer;
    }

    .header h1 {
        margin: 0;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 15px;
    }

    input, select, textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 400; 
    }

    .btn {
        font-weight: bold;
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #444;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
</style>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back"
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Add Supplier</h1>
    </div>

    <!-- Centered container for the form, matching serviceedit.php style -->
    <div class="center-container">
        <form id="entryForm">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required maxlength="64">
            </div>

            <div class="form-group">
                <label for="part">Part:</label>
                <input type="text" id="part" name="part" required>
            </div>

            <div class="form-group">
                <label for="supplier">Supplier Name:</label>
                <input type="text" id="supplier" name="supplier" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Add</button>
                <button type="reset" class="btn" style="background-color: red;">Reset</button>
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
