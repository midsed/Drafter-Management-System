<?php
session_start();
require_once "dbconnect.php"; // Include the database connection

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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Supplier</h1>
        <div class="actions">
            <a href="supplierarchive.php" class="btn btn-archive">Archives</a>
            <a href="supplieradd.php" class="btn btn-add">+ Add Supplier</a>
        </div>
    </div>
    
    <!-- Quick Search on the left side (Input + Search button) -->
    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
    </div>
    
    <!-- Table below the search -->
    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Part ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Edit Supplier</th>
                    <th>Archive</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch active suppliers (archived = 0)
                $sql = "SELECT s.SupplierID, s.CompanyName, s.Email, s.PhoneNumber, p.PartID 
                        FROM supplier s
                        LEFT JOIN part p ON s.SupplierID = p.SupplierID
                        WHERE s.archived = 0
                        ORDER BY s.SupplierID DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "
                        <tr>
                            <td>{$row['SupplierID']}</td>
                            <td>{$row['PartID']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['CompanyName']}</td>
                            <td>{$row['PhoneNumber']}</td>
                            <td><a href='supplieredit.php?id={$row['SupplierID']}' class='btn btn-edit'>Edit</a></td>
                            <td><button class='btn btn-archive' onclick='archiveSupplier({$row['SupplierID']})'>Archive</button></td>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='7'>No active suppliers found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Search functionality: filters table rows based on input
    function searchTable() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.supplier-table tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    // Add event listener to the search input for real-time search
    document.getElementById('searchInput').addEventListener('input', searchTable);

    // Archive supplier functionality with SweetAlert
    function archiveSupplier(supplierID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to archive this supplier!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E10F0F',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('archive_supplier.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${supplierID}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Archived!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while archiving the supplier.',
                        icon: 'error',
                        confirmButtonText: 'OK'
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
    body {
        font-family: 'Poppins', sans-serif;
    }

    .actions a.btn,
    .actions button.btn {
        color: white !important;
    }

    .btn {
        font-family: 'Poppins', sans-serif;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        color: white;
    }
    .btn-archive, .btn-add, .btn-edit {
        background-color: #E10F0F;
    }

    .actions {
        text-align: right;
        width: 100%;
    }
    .actions .btn {
        margin-left: 10px;
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
        transition: background 0.3s ease;
        text-decoration: none;
    }
    .red-button:hover {
        background: darkred;
    }

    .table-container {
        margin-top: 20px;
    }

    .supplier-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .supplier-table th,
    .supplier-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .supplier-table th {
        background-color: #f4f4f4;
    }
    .supplier-table tr:hover {
        background-color: #f1f1f1;
    }

    .supplier-table th:nth-child(6),
    .supplier-table td:nth-child(6) {
        text-align: center;
    }
</style>
