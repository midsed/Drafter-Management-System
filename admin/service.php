<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');
?>

<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Service</h1>
        <div class="actions">
            <a href="servicearchive.php" class="btn btn-archive">Archives</a>
            <a href="serviceadd.php" class="btn btn-add">+ Add Service</a>
        </div>
    </div>

    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
    </div>

    <div class="table-container">
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Service Type</th>
                    <th>Service Price</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Customer Number</th>
                    <th>Staff Name</th>
                    <th>Part Name</th>
                    <th>Edit Service</th>
                    <th>Archive</th>
                </tr>
            </thead>
            <tbody>
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
                        WHERE s.Archived = 0";

                $result = $conn->query($sql);

                if (!$result) {
                    echo "<tr><td colspan='10'>SQL Error: " . $conn->error . "</td></tr>";
                } elseif ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ServiceID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Type']); ?></td>
                    <td><?php echo htmlspecialchars($row['Price']); ?></td>
                    <td><?php echo htmlspecialchars($row['CustomerFName'] . ' ' . $row['CustomerLName']); ?></td>
                    <td><?php echo htmlspecialchars($row['ClientEmail'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['PhoneNumber'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['StaffName'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['PartName']); ?></td>
                    <td>
                        <a href="serviceedit.php?id=<?php echo $row['ServiceID']; ?>" class="btn btn-edit">Edit</a>
                    </td>
                    <td>
                        <button class="btn btn-archive" onclick="archiveService(<?php echo $row['ServiceID']; ?>)">Archive</button>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='10'>No services found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>

function searchTable() {
    const searchInput = document.getElementById("searchInput").value.toLowerCase();
    const table = document.querySelector(".supplier-table");
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) { 
        let cells = rows[i].getElementsByTagName("td");
        let match = false;

        for (let j = 0; j < cells.length; j++) {
            if (cells[j] && cells[j].textContent.toLowerCase().includes(searchInput)) {
                match = true;
                break;
            }
        }

        rows[i].style.display = match ? "" : "none";
    }
}

    document.getElementById("searchInput").addEventListener("keyup", searchTable);


    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    }
    
    function archiveService(serviceID) {
        if (confirm("Are you sure you want to archive this service?")) {
            window.location.href = "servicearchive.php?archive=" + serviceID;
        }
    }

    function archiveService(serviceID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to archive this service?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, archive it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('archive_service.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'service_id=' + serviceID
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Success!",
                    text: data,
                    icon: "success"
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire("Error", "Something went wrong!", "error");
            });
        }
    });
}
</script>

<style>
    .btn {
        font-family: 'Poppins', sans-serif;
    }

    .actions a.btn,
    .actions button.btn {
        color: white !important;
    }

    .btn {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        color: white;
    }

    .btn-archive {
        background-color: #E10F0F;
        color: white;
    }

    .btn-add {
        background-color: #E10F0F;
        color: white;
    }

    .btn-edit {
        background-color: #E10F0F;
        color: white;
    }

    .actions {
        text-align: right;
        width: 100%;
    }

    .actions .btn {
        margin-left: 10px;
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

    .search-box {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        font-size: 14px;
    }
</style>
