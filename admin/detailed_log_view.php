<?php
session_start();
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
    header("Location: /Drafter-Management-System/login.php");
    exit();
}   

include('dbconnect.php');
include('navigation/sidebar.php');
include('navigation/topbar.php');

// Get log ID from URL parameter
$logID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($logID <= 0) {
    echo "<script>alert('Invalid log ID'); window.location.href='logs.php';</script>";
    exit();
}

// Fetch detailed log information
$sql = "SELECT l.LogsID, CONCAT(u.FName, ' ', u.LName) AS UserName, u.RoleType, 
        l.ActionType, l.Timestamp, l.PartID, l.OldValue, l.NewValue, l.FieldName
        FROM logs l
        JOIN user u ON l.UserID = u.UserID
        WHERE l.LogsID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $logID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Log not found'); window.location.href='logs.php';</script>";
    exit();
}

$log = $result->fetch_assoc();
$stmt->close();

// Determine if this log has a part associated with it
$hasPart = !empty($log['PartID']);
$partDetails = null;

if ($hasPart) {
    // Fetch part details
    $partSql = "SELECT * FROM part WHERE PartID = ?";
    $partStmt = $conn->prepare($partSql);
    $partStmt->bind_param("i", $log['PartID']);
    $partStmt->execute();
    $partResult = $partStmt->get_result();
    
    if ($partResult->num_rows > 0) {
        $partDetails = $partResult->fetch_assoc();
    }
    $partStmt->close();
}

// Determine action type category for styling
$actionCategory = "other";
if (strpos($log['ActionType'], 'Add') !== false) {
    $actionCategory = "add";
} elseif (strpos($log['ActionType'], 'Edit') !== false || strpos($log['ActionType'], 'Update') !== false) {
    $actionCategory = "edit";
} elseif (strpos($log['ActionType'], 'Archive') !== false) {
    $actionCategory = "archive";
} elseif (strpos($log['ActionType'], 'Re-list') !== false) {
    $actionCategory = "relist";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="font-family: 'Poppins', sans-serif;">

<div class="main-content">
    <div class="header">
        <a href="logs.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
            <h1 style="margin: 0;">Log Details</h1>
        </a>
    </div>

    <div class="log-detail-container">
        <div class="log-header <?php echo $actionCategory; ?>">
            <h2>Log #<?php echo $log['LogsID']; ?></h2>
            <span class="log-timestamp"><?php echo date("F j, Y, g:i A", strtotime($log['Timestamp'])); ?></span>
        </div>
        
        <div class="log-info-grid">
            <div class="log-info-item">
                <span class="info-label">Action By:</span>
                <span class="info-value"><?php echo htmlspecialchars($log['UserName']); ?> (<?php echo $log['RoleType']; ?>)</span>
            </div>
            
            <div class="log-info-item">
                <span class="info-label">Action Type:</span>
                <span class="info-value action-type <?php echo $actionCategory; ?>"><?php echo htmlspecialchars($log['ActionType']); ?></span>
            </div>
            
            <?php if (!empty($log['FieldName'])): ?>
            <div class="log-info-item">
                <span class="info-label">Field Modified:</span>
                <span class="info-value"><?php echo htmlspecialchars($log['FieldName']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($log['OldValue']) && !empty($log['NewValue'])): ?>
            <div class="log-changes">
                <div class="change-comparison">
                    <div class="old-value">
                        <h3>Previous Value</h3>
                        <div class="value-box"><?php echo htmlspecialchars($log['OldValue']); ?></div>
                    </div>
                    <div class="change-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="new-value">
                        <h3>New Value</h3>
                        <div class="value-box"><?php echo htmlspecialchars($log['NewValue']); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($hasPart && $partDetails): ?>
        <div class="part-details-section">
            <h3>Related Part Information</h3>
            <div class="part-details-grid">
                <div class="part-info-item">
                    <span class="info-label">Part Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($partDetails['Name']); ?></span>
                </div>
                <div class="part-info-item">
                    <span class="info-label">Part ID:</span>
                    <span class="info-value">#<?php echo $partDetails['PartID']; ?></span>
                </div>
                <div class="part-info-item">
                    <span class="info-label">Price:</span>
                    <span class="info-value">₱<?php echo number_format($partDetails['Price'], 2); ?></span>
                </div>
                <div class="part-info-item">
                    <span class="info-label">Make/Model:</span>
                    <span class="info-value"><?php echo htmlspecialchars($partDetails['Make'] . ' ' . $partDetails['Model']); ?></span>
                </div>
            </div>
            
            <div class="part-actions">
                <a href="partsedit.php?id=<?php echo $partDetails['PartID']; ?>" class="view-part-btn">
                    <i class="fas fa-edit"></i> View/Edit Part
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

function checkSidebarState() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
    }
}

document.addEventListener("DOMContentLoaded", function () {
    checkSidebarState();
});
</script>

<style>
body, button, select, input, a {
    font-family: 'Poppins', sans-serif;
}

.header a {
    color: black;
}

.log-detail-container {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    overflow: hidden;
}

.log-header {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    color: white;
}

.log-header.add {
    background-color: #4CAF50;
}

.log-header.edit {
    background-color: #2196F3;
}

.log-header.archive {
    background-color: #F44336;
}

.log-header.relist {
    background-color: #FF9800;
}

.log-header.other {
    background-color: #9E9E9E;
}

.log-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.log-timestamp {
    font-size: 0.9rem;
    opacity: 0.9;
}

.log-info-grid {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.log-info-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.info-label {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 5px;
}

.info-value {
    font-size: 1rem;
    font-weight: 500;
}

.action-type {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    color: white;
    font-weight: 500;
}

.action-type.add {
    background-color: #4CAF50;
}

.action-type.edit {
    background-color: #2196F3;
}

.action-type.archive {
    background-color: #F44336;
}

.action-type.relist {
    background-color: #FF9800;
}

.action-type.other {
    background-color: #9E9E9E;
}

.log-changes {
    grid-column: 1 / -1;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.change-comparison {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.old-value, .new-value {
    flex: 1;
}

.old-value h3, .new-value h3 {
    font-size: 1rem;
    margin-bottom: 10px;
    color: #555;
}

.value-box {
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
    border: 1px solid #eee;
    min-height: 50px;
    word-break: break-word;
}

.old-value .value-box {
    background-color: #FFEBEE;
    border-color: #FFCDD2;
}

.new-value .value-box {
    background-color: #E8F5E9;
    border-color: #C8E6C9;
}

.change-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #757575;
}

.part-details-section {
    margin-top: 20px;
    padding: 20px;
    border-top: 1px solid #eee;
    background-color: #f9f9f9;
}

.part-details-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.part-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.part-info-item {
    display: flex;
    flex-direction: column;
}

.part-actions {
    display: flex;
    justify-content: flex-end;
}

.view-part-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #E10F0F;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s;
}

.view-part-btn:hover {
    background-color: darkred;
}

@media (max-width: 768px) {
    .change-comparison {
        flex-direction: column;
    }
    
    .change-arrow {
        transform: rotate(90deg);
        margin: 15px 0;
    }
}
</style>

</body>
</html>