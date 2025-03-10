<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Archived Parts List</h1>
    </div>

    <div class="card-container">
        <?php
        $sql = "SELECT PartID, Name, Make, Model, Location, Quantity, Media FROM part WHERE archived = 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($part = $result->fetch_assoc()) {
                $imageSrc = !empty($part['Media']) ? $part['Media'] : 'images/no-image.png';
                echo "
                    <div class='card'>
                        <img class='card-img' src='$imageSrc' alt='Part Image'>
                        <div class='card-body'>
                            <h2 class='card-title'>{$part['Name']}</h2>
                            <p><strong>Make:</strong> {$part['Make']}</p>
                            <p><strong>Model:</strong> {$part['Model']}</p>
                            <p><strong>Location:</strong> {$part['Location']}</p>
                            <p><strong>Quantity:</strong> {$part['Quantity']}</p>
                            <button class='btn relist-btn' onclick='relistPart({$part['PartID']})'>Re-list</button>
                        </div>
                    </div>
                ";
            }
        } else {
            echo "<p>No archived parts found.</p>";
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
    .swal2-popup { font-family: "Inter", sans-serif !important; }
    .swal2-title { font-weight: 700 !important; }
    .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
    .swal2-confirm { font-weight: bold !important; background-color: #6c5ce7 !important; color: white !important; }
    .swal2-cancel { font-weight: bold !important; background-color: #d63031 !important; color: white !important; }
</style>

<script>
function relistPart(partID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to re-list this part?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#6c5ce7",
        cancelButtonColor: "#d63031",
        confirmButtonText: "Yes, re-list it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('relist_part.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + partID
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Success!",
                    text: data,
                    icon: "success",
                    confirmButtonColor: "#6c5ce7"
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: "Error",
                    text: "Something went wrong!",
                    icon: "error",
                    confirmButtonColor: "#d63031"
                });
            });
        }
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}
</script>



<style>
.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.2s;
}

.card:hover {
    transform: scale(1.05);
}

.card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 15px;
    text-align: center;
}

.card-title {
    font-size: 1.2em;
    font-weight: bold;
}

.btn.relist-btn {
    background: #28a745;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn.relist-btn:hover {
    background: #218838;
}
</style>
