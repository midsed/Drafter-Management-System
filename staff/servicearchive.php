<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
        <a href="service.php" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1>Archived Services</h1>
    </div>

    <?php
        $sql = "SELECT 
                    s.ServiceID, 
                    s.Type, 
                    s.Price, 
                    c.FName AS CustomerFName, 
                    c.LName AS CustomerLName, 
                    s.ClientEmail, 
                    c.PhoneNumber, 
                    s.StaffName, 
                    COALESCE(p.Name, 'N/A') AS PartName
                FROM service s
                LEFT JOIN client c ON s.ClientEmail = c.ClientEmail
                LEFT JOIN part p ON s.PartID = p.PartID
                WHERE s.Archived = 1";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) { ?>
            <div class="table-container">
                <table class="supplier-table">
                    <thead>
                        <tr>
                            <th>Service ID</th>
                            <th>Service Type</th>
                            <th>Service Price</th>
                            <th>Customer</th>
                            <th>Staff</th>
                            <th>Part Name</th>
                            <th>Relist</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['ServiceID']); ?></td>
                                <td><?php echo htmlspecialchars($row['Type']); ?></td>
                                <td><?php echo htmlspecialchars($row['Price']); ?></td>
                                <td><?php echo htmlspecialchars($row['CustomerFName'] . ' ' . $row['CustomerLName']); ?></td>
                                <td><?php echo htmlspecialchars($row['StaffName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['PartName']); ?></td>
                                <td><button class='btn btn-relist' onclick='relistService(<?php echo $row['ServiceID']; ?>)'>Relist</button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
    <?php } else { ?>
        <p style="text-align: center; font-size: 18px; padding: 20px;">No archived services found.</p>
    <?php } ?>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");
    .swal2-popup { font-family: "Inter", sans-serif !important; }
    .swal2-title { font-weight: 700 !important; }
    .swal2-content { font-weight: 500 !important; font-size: 18px !important; }
    .swal2-confirm { font-weight: bold !important; background-color: #28a745 !important; color: white !important; }
    .swal2-cancel { font-weight: bold !important; background-color: #d63031 !important; color: white !important; }
</style>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}

function relistService(serviceID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to relist this service?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, relist it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('relist_service.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'service_id=' + serviceID
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Success!",
                    text: data,
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#28a745"
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: "Error!",
                    text: "Something went wrong!",
                    icon: "error",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#d33"
                });
            });
        }
    });
}
</script>

<style>
    .btn-relist {
        background-color: #28a745;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-relist:hover {
        background-color: #218838;
    }
</style>
