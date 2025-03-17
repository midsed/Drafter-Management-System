<?php
session_start();
include('dbconnect.php'); // Ensure database connection

if (!isset($_SESSION['UserID'])) {
    header("Location: \Drafter-Management-System\login.php");
    exit();
}

if (!isset($_SESSION['Username'])) {
    $_SESSION['Username'];
}

include('navigation/sidebar.php');
include('navigation/topbar.php');
?>

<link rel="stylesheet" href="css/style.css">

<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid Part ID.</p>";
    exit();
}

$partID = $_GET['id'];

$sql = "SELECT * FROM part WHERE PartID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p>Part not found.</p>";
    exit();
}

$part = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<div class="main-content">
    <div class="header">
        <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
            <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
        </a>
        <h1><?php echo htmlspecialchars($part["Name"]); ?></h1>
    </div>

    <div class="part-media-container">
        <?php
        // Check if Media is a JSON array or a single path
        $media = json_decode($part["Media"], true);
        if (is_array($media) && count($media) > 0): 
            foreach ($media as $image): ?>
                <img src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Part Image">
            <?php endforeach; 
        elseif (!empty($part["Media"])): ?>
            <img src="uploads/<?php echo htmlspecialchars($part["Media"]); ?>" alt="Part Image">
        <?php else: ?>
            <p class="no-image">No image available</p>
        <?php endif; ?>
    </div>

    <table class="details-table">
        <tr><td>Part ID</td><td>#<?php echo $part["PartID"]; ?></td></tr>
        <tr><td>Category</td><td><?php echo htmlspecialchars($part["Category"]); ?></td></tr>
        <tr><td>Condition</td><td><?php echo htmlspecialchars($part["PartCondition"]); ?></td></tr>
        <tr><td>Location</td><td><?php echo htmlspecialchars($part["Location"]); ?></td></tr>
        <tr><td>Quantity</td><td><?php echo $part["Quantity"]; ?></td></tr>
        <tr><td>Authenticity</td><td><?php echo htmlspecialchars($part["Authenticity"]); ?></td></tr>
        <tr><td>Make</td><td><?php echo htmlspecialchars($part["Make"]); ?></td></tr>
        <tr><td>Model</td><td><?php echo htmlspecialchars($part["Model"]); ?></td></tr>
        <tr><td>Year Model</td><td><?php echo htmlspecialchars($part["YearModel"]); ?></td></tr>
        <tr><td>Part Price</td><td>₱ <?php echo number_format($part["Price"], 2); ?></td></tr>
        <tr><td>Date Added</td><td><?php echo htmlspecialchars($part["DateAdded"]); ?></td></tr>
        <tr><td>Last Updated</td><td><?php echo htmlspecialchars($part["LastUpdated"]); ?></td></tr>
        <tr><td>Description</td><td><?php echo nl2br(htmlspecialchars($part["Description"])); ?></td></tr>
    </table>
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
.part-detail-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.part-media-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.part-media-container img {
    max-width: 30%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease-in-out;
}

.part-media-container img:hover {
    transform: scale(1.05);
}

.details-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #f9f9f9;
    border-radius: 10px;
    overflow: hidden;
}

.details-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    font-size: 16px;
    text-align: left;
    color: #333;
}

.details-table tr:last-child td {
    border-bottom: none;
}

.no-image {
    font-size: 16px;
    color: #888;
    font-style: italic;
}
</style>