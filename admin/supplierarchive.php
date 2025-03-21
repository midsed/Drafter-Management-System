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
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Archived Suppliers</h1>
    </div>
    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
    </div>
    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Part Name</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Re-list</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch archived suppliers (archived = 1)
                $sql = "SELECT s.SupplierID, s.CompanyName, s.Email, s.PhoneNumber, p.PartID, p.Name AS PartName 
                    FROM supplier s
                    LEFT JOIN part p ON s.SupplierID = p.SupplierID
                    WHERE s.archived = 1
                    ORDER BY s.SupplierID DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "
                            <tr>
                                <td>{$row['SupplierID']}</td>
                                <td>{$row['PartName']}</td> <!-- Updated from PartID to PartName -->
                                <td>{$row['Email']}</td>
                                <td>{$row['CompanyName']}</td>
                                <td>{$row['PhoneNumber']}</td>
                                <td><button class='btn btn-relist' onclick='relistSupplier({$row['SupplierID']})'>Re-list</button></td>
                            </tr>
                            ";
                    }
                } else {
                    echo "<tr><td colspan='6'>No archived suppliers found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.supplier-table tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Re-list supplier functionality with SweetAlert
    function relistSupplier(supplierID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to re-list this supplier!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745', // Green color for re-list
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, re-list it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('relist_supplier.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${supplierID}`
                })
                .then(response => response.json()) // Parse the response as JSON
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Re-listed!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Remove the re-listed supplier row from the table
                            const row = document.querySelector(`tr[data-supplier-id="${supplierID}"]`);
                            if (row) {
                                row.remove();
                            }
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
                        text: 'An error occurred while re-listing the supplier.',
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
.btn {
    font-family: 'Poppins', sans-serif;
}

.btn-relist {
    background-color: #28a745; /* Green color */
    color: white;
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

.search-container {
    margin-bottom: 10px;
}

.search-container input[type="text"] {
    width: 100%;
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>