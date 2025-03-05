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
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" 
                 style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Service</h1>
        <div class="actions">
            <a href="servicearchive.php" class="btn btn-archive">Archives</a>
            <a href="serviceadd.php" class="btn btn-add">+ Add Service</a>
        </div>
    </div>

    <!-- Quick Search: Left-aligned input and search button -->
    <div class="search-container">
        <input type="text" placeholder="Quick search" id="searchInput">
        <button class="red-button" onclick="searchTable()">Search</button>
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
    // Search functionality: Filter table rows based on the search input.
    function searchTable() {
        const searchInput = document.getElementById("searchInput").value.toLowerCase();
        const table = document.querySelector(".supplier-table");
        const rows = table.getElementsByTagName("tr");

        // Start from index 1 to skip the header row
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
    
    // Archive service functionality with SweetAlert
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

    /* Mimic quick search and search button styling like parts.php */
    .search-container {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .search-container input[type="text"] {
        width: 250px;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
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
        text-align: center;
    }
    .supplier-table th {
        background-color: #f4f4f4;
    }
    .supplier-table tr:hover {
        background-color: #f1f1f1;
    }
</style>
