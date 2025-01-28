<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="dashboard.php" style="text-decoration: none; display: flex; align-items: center;">
        <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        <h1 style="margin: 0;">Parts List</h1>
        </a>
    </div>

    <div class="search-actions">
        <div class="search-container">
            <input type="text" placeholder="Quick search">
            <button class="red-button">Search</button>
            <button class="white-button">Filter</button>
            <button class="white-button">Parts</button>
            <button class="white-button">Sort</button>
        </div>
        <div class="right-actions">
            <button class="red-button">Cart</button>
            <a href="partsarchive.php" class="red-button">Archives</a>
            <a href="partsadd.php" class="red-button">+ New Stock</a>
        </div>
    </div>

    <div class="parts-container">
        <?php
        $parts = [
            ["Brand 1", "Model 1", "Shelf A", "1"],
            ["Brand 2", "Model 2", "Shelf A", "2"],
            ["Brand 3", "Model 3", "Shelf B", "1"],
            ["Brand 4", "Model 4", "Shelf B", "0"],
        ];

        foreach ($parts as $index => $part) {
            echo "
                <div class='part-card'>
                    <a href='partdetail.php?id=$index'><img src='images/part$index.jpg' alt='Part Image'></a>
                    <p>Make</p>
                    <p>Model</p>
                    <p>Brand: {$part[0]}</p>
                    <p>Model: {$part[1]}</p>
                    <p>Location: {$part[2]}</p>
                    <p>Quantity: {$part[3]}</p>
                    <div class='actions'>
                        <a href='partsedit.php?id=$index' class='edit-button'>Edit</a>
                        <button class='red-button'>Archive</button>
                        <button class='red-button'>Add to Cart</button>
                    </div>
                </div>
            ";
        }
        ?>
    </div>

    <div class="pagination">
        <a href="#" class="pagination-button">Previous</a>
        <span class="active-page">1</span>
        <a href="#" class="pagination-button">2</a>
        <a href="#" class="pagination-button">3</a>
        <a href="#" class="pagination-button">Next</a>
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

    .right-actions {
        display: flex;
        gap: 10px;
    }

    .red-button, .white-button, .edit-button, .pagination-button {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease, color 0.3s ease;
    }

    .red-button {
        background: #E10F0F;
        color: white;
        border: none;
        text-decoration: none;
    }

    .red-button:hover {
        background: darkred;
    }

    .white-button {
        background: white;
        color: black;
        border: 1px solid #ccc;
    }

    .white-button:hover {
        background: #f0f0f0;
        border-color: #888;
    }

    .edit-button {
        background: grey;
        color: white;
        border: none;
    }

    .edit-button:hover {
        background: #555;
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

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
    }

    .pagination-button {
        background: white;
        border: 1px solid black;
        color: black;
        text-decoration: none;
    }

    .pagination-button:hover {
        background: #f0f0f0;
    }

    .active-page {
        padding: 6px 12px;
        border-radius: 4px;
        background: black;
        color: white;
        font-weight: bold;
    }
</style>
