<?php
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
include('dbconnect.php');

// Variables for search, filter, sort
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$types  = isset($_GET['type'])   ? explode(',', $_GET['type']) : [];
$staffs = isset($_GET['staff'])  ? explode(',', $_GET['staff']) : [];
$sort   = isset($_GET['sort'])   ? $_GET['sort'] : '';

// Pagination
$limit  = 10;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Base queries
$sql      = "SELECT ServiceID, Type, Price, ClientEmail, StaffName, PartName FROM service WHERE Archived = 0";
$countSql = "SELECT COUNT(*) AS total FROM service WHERE Archived = 0";

// Filters
if (!empty($types)) {
    $escapedTypes = array_map([$conn, 'real_escape_string'], $types);
    $sql      .= " AND Type IN ('" . implode("','", $escapedTypes) . "')";
    $countSql .= " AND Type IN ('" . implode("','", $escapedTypes) . "')";
}
if (!empty($staffs)) {
    $escapedStaffs = array_map([$conn, 'real_escape_string'], $staffs);
    $sql      .= " AND StaffName IN ('" . implode("','", $escapedStaffs) . "')";
    $countSql .= " AND StaffName IN ('" . implode("','", $escapedStaffs) . "')";
}
if (!empty($search)) {
    $sql      .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%' OR PartName LIKE '%$search%')";
    $countSql .= " AND (Type LIKE '%$search%' OR ClientEmail LIKE '%$search%' OR StaffName LIKE '%$search%' OR PartName LIKE '%$search%')";
}

// Count total records for pagination
$totalResult = $conn->query($countSql);
$totalRow    = $totalResult->fetch_assoc();
$totalRecords= $totalRow['total'];
$totalPages  = ceil($totalRecords / $limit);

// Sorting
if ($sort === 'asc') {
    $sql .= " ORDER BY Type ASC";
} elseif ($sort === 'desc') {
    $sql .= " ORDER BY Type DESC";
} else {
    $sql .= " ORDER BY ServiceID DESC";
}

// Apply pagination limit if there are more than 10 records
if ($totalRecords > 10) {
    $sql .= " LIMIT $limit OFFSET $offset";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Dropdown content hidden by default, shown when "show" class is added */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 200px;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            padding: 15px;
            border-radius: 8px;
        }
        .dropdown-content.show {
            display: block;
        }
        /* Pagination styling */
        .pagination-button {
            padding: 6px 12px;
            border-radius: 4px;
            background: white;
            border: 1px solid black;
            color: black;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .pagination-button:hover {
            background: #f0f0f0;
        }
        .active-page {
            background: black;
            color: white;
            font-weight: bold;
        }
        /* Button styling */
        .btn {
            font-family: 'Poppins', sans-serif;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            color: white;
        }
        .btn-archive, .btn-edit {
            background-color: #E10F0F;
        }
        .btn-add {
            background-color: #00A300 !important;
        }
        .actions a.btn {
            margin-left: 10px;
            text-decoration: none;
        }
        /* Table row hover */
        tr:hover {
            background-color: rgb(218, 218, 218);
        }
        /* Icon styling */
        .filter-icon, .sort-icon {
            color: #E10F0F;
            font-size: 20px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .filter-icon:hover, .sort-icon:hover {
            color: darkred;
        }
        .sort-option {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body style="font-family: 'Poppins', sans-serif;">

<div class="main-content">
    <!-- Header with Back Arrow & Title -->
    <div class="header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1 style="margin: 0;">Service</h1>
    </div>

    <!-- Combined Toolbar Row -->
    <div class="search-actions" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <!-- Left group: search, filter, sort -->
        <div class="left-group" style="display: inline-flex; align-items: center; gap: 20px;">
            <!-- Search Container -->
            <div class="search-container" style="display: flex; align-items: center; gap: 10px;">
                <input type="text" placeholder="Quick search" id="searchInput"
                       style="width: 300px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px;">
            </div>

            <!-- Filter Container -->
            <div class="filter-container" style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <span style="font-size: 14px; color: #333;">Filter</span>
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <button id="filterButton" class="filter-icon" style="color: #E10F0F; font-size: 20px; background: none; border: none; cursor: pointer;">
                        <i class="fas fa-filter"></i>
                    </button>
                    <div id="filterDropdown" class="dropdown-content">
                        <div class="filter-options" style="display: flex; flex-direction: column; gap: 8px;">
                            <h4 style="margin: 0 0 10px; font-size: 16px; color: #333;">Service Type</h4>
                            <?php
                            $typeQuery = "SELECT DISTINCT Type FROM service WHERE Archived = 0";
                            $typeResult = $conn->query($typeQuery);
                            while ($type = $typeResult->fetch_assoc()) {
                                $checked = in_array($type['Type'], $types) ? "checked" : "";
                                echo "<label style='display: flex; align-items: center; gap: 8px; font-size: 14px; color: #555; cursor: pointer;'>
                                        <input type='checkbox' class='filter-option' data-filter='type' value='{$type['Type']}' $checked style='margin: 0; cursor: pointer;'>
                                        {$type['Type']}
                                      </label>";
                            }
                            ?>
                        </div>
                        <div class="filter-options" style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                            <h4 style="margin: 0 0 10px; font-size: 16px; color: #333;">Name</h4>
                            <?php
                            $staffQuery = "SELECT DISTINCT StaffName FROM service WHERE Archived = 0";
                            $staffResult = $conn->query($staffQuery);
                            while ($staff = $staffResult->fetch_assoc()) {
                                $checked = in_array($staff['StaffName'], $staffs) ? "checked" : "";
                                echo "<label style='display: flex; align-items: center; gap: 8px; font-size: 14px; color: #555; cursor: pointer;'>
                                        <input type='checkbox' class='filter-option' data-filter='staff' value='{$staff['StaffName']}' $checked style='margin: 0; cursor: pointer;'>
                                        {$staff['StaffName']}
                                      </label>";
                            }
                            ?>
                        </div>
                        <div class="filter-actions" style="display: flex; gap: 10px; margin-top: 15px; justify-content: center;">
                            <button id="applyFilter" class="red-button" style="background-color: #E10F0F; color: #fff; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                                Apply
                            </button>
                            <button id="clearFilter" class="red-button" style="background-color: #E10F0F; color: #fff; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sort Container -->
            <div class="sort-container" style="display: flex; white-space: nowrap; align-items: center; gap: 5px; cursor: pointer;">
                <span style="font-size: 14px; color: #333;">Sort By</span>
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <button id="sortButton" class="sort-icon" style="color: #E10F0F; font-size: 20px; background: none; border: none; cursor: pointer;">
                        <i class="fas fa-sort-alpha-down"></i>
                    </button>
                    <div id="sortDropdown" class="dropdown-content">
                        <h4 style="margin: 0 0 10px; font-family: 'Poppins', sans-serif; font-size: 16px; color: #333;"></h4>
                        <button class="sort-option red-button" data-sort="asc" style="background-color: #E10F0F; color: #fff; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">
                            Ascending
                        </button>
                        <button class="sort-option red-button" data-sort="desc" style="background-color: #E10F0F; color: #fff; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                            Descending
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right group: Archives & + Add Service -->
        <div class="actions" style="margin-top: 15px;">
            <a href="servicearchive.php" class="btn btn-archive" style="margin-left: 10px; background-color: #E10F0F; text-decoration: none;">
               Archives
            </a>
            <a href="serviceadd.php" class="btn btn-add" style="margin-left: 10px; background-color: #00A300 !important; text-decoration: none;">
               + Add Service
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container" style="margin-top: 20px;">
        <table class="supplier-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background-color: #fff;">
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Service ID</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Service Type</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Service Price</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Customer Email</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Staff</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Part Name</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Edit Service</th>
                    <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">Archive</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr style='cursor: pointer;'>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['ServiceID']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['Type']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['Price']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['ClientEmail'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['StaffName'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($row['PartName']) . "</td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>
                            <a href='serviceedit.php?id=" . $row['ServiceID'] . "' class='btn btn-edit' style='text-decoration: none;'>
                               Edit
                            </a>
                          </td>";
                    echo "<td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>
                            <button class='btn btn-archive' onclick='archiveService(" . $row['ServiceID'] . ")'>
                                Archive
                            </button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>No services found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="pagination" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
    <?php 
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams); 

        $visiblePages = 5;
        $startPage = max(1, $page - 2);
        $endPage   = min($totalPages, $startPage + $visiblePages - 1);

        if ($endPage - $startPage < $visiblePages - 1) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }

        if ($page > 1) {
            echo '<a href="?' . $queryString . '&page=1" class="pagination-button" style="padding: 6px 12px; border-radius: 4px; background: white; border: 1px solid black; color: black;">First</a>';
            echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-button" style="padding: 6px 12px; border-radius: 4px; background: white; border: 1px solid black; color: black;">Previous</a>';
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page) ? 'active-page' : '';
            $activeStyle = ($i == $page) ? 'background: black; color: white; font-weight: bold;' : '';
            echo '<a href="?' . $queryString . '&page=' . $i . '" class="pagination-button ' . $activeClass . '" style="padding: 6px 12px; border-radius: 4px; background: white; border: 1px solid black; color: black; ' . $activeStyle . '">' . $i . '</a>';
        }

        if ($page < $totalPages) {
            echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-button" style="padding: 6px 12px; border-radius: 4px; background: white; border: 1px solid black; color: black;">Next</a>';
            echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-button" style="padding: 6px 12px; border-radius: 4px; background: white; border: 1px solid black; color: black;">Last</a>';
        }
    ?>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function archiveService(serviceID) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to archive this service?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#32CD32",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, archive it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('archive_service.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `service_id=${serviceID}`
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: "Archived!",
                    text: data,
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#32CD32"
                }).then(() => location.reload());
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

document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filterDropdown");
    const sortDropdown   = document.getElementById("sortDropdown");
    const filterButton   = document.getElementById("filterButton");
    const sortButton     = document.getElementById("sortButton");
    const applyFilterBtn = document.getElementById("applyFilter");
    const clearFilterBtn = document.getElementById("clearFilter");
    const searchInput    = document.getElementById("searchInput");

    // Toggle Filter/Sort dropdowns
    filterButton.addEventListener("click", function (event) {
        event.stopPropagation();
        filterDropdown.classList.toggle("show");
        sortDropdown.classList.remove("show");
    });
    sortButton.addEventListener("click", function (event) {
        event.stopPropagation();
        sortDropdown.classList.toggle("show");
        filterDropdown.classList.remove("show");
    });
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".dropdown-content") &&
            !event.target.closest(".filter-icon") &&
            !event.target.closest(".sort-icon")) {
            filterDropdown.classList.remove("show");
            sortDropdown.classList.remove("show");
        }
    });

    // Apply Filter
    applyFilterBtn.addEventListener("click", function () {
        const selectedTypes = Array.from(document.querySelectorAll('.filter-option[data-filter="type"]:checked'))
            .map(checkbox => checkbox.value);
        const selectedStaff = Array.from(document.querySelectorAll('.filter-option[data-filter="staff"]:checked'))
            .map(checkbox => checkbox.value);
        const searchQuery = searchInput.value.trim();
        const queryParams = new URLSearchParams(window.location.search);

        queryParams.set("page", "1");
        if (selectedTypes.length > 0) {
            queryParams.set("type", selectedTypes.join(","));
        } else {
            queryParams.delete("type");
        }
        if (selectedStaff.length > 0) {
            queryParams.set("staff", selectedStaff.join(","));
        } else {
            queryParams.delete("staff");
        }
        if (searchQuery) {
            queryParams.set("search", searchQuery);
        } else {
            queryParams.delete("search");
        }
        window.location.search = queryParams.toString();
    });

    // Clear Filter
    clearFilterBtn.addEventListener("click", function () {
        window.location.href = window.location.pathname;
    });

    // Apply Sorting
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function () {
            const selectedSort = this.dataset.sort;
            const queryParams  = new URLSearchParams(window.location.search);
            queryParams.set("sort", selectedSort);
            queryParams.set("page", "1");
            window.location.search = queryParams.toString();
        });
    });

    // Live Search
    searchInput.addEventListener("input", function () {
        const searchValue = this.value.trim();
        const currentUrl  = new URL(window.location.href);
        if (searchValue) {
            currentUrl.searchParams.set("search", searchValue);
        } else {
            currentUrl.searchParams.delete("search");
        }
        currentUrl.searchParams.set("page", "1");

        fetch(currentUrl.toString())
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                document.getElementById("logsTableBody").innerHTML = doc.getElementById("logsTableBody").innerHTML;
                document.querySelector(".pagination").innerHTML = doc.querySelector(".pagination")?.innerHTML || "";
            })
            .catch(error => console.error("Error updating search results:", error));
    });
});
</script>

</body>
</html>
