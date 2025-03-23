<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Staff') {
    header("Location: /Drafter-Management-System/login.php");
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

    <div class="part-detail-container">
        <div class="media-location-wrapper">
            <div class="part-media-container">
                <?php
                $media = json_decode($part["Media"], true);
                if (is_array($media) && count($media) > 0): 
                    foreach ($media as $image): ?>
                        <img src="<?php echo '../' . htmlspecialchars($image); ?>" alt="Part Image">
                    <?php endforeach; 
                elseif (!empty($part["Media"])): ?>
                    <img src="<?php echo '../' . htmlspecialchars($part["Media"]); ?>" alt="Part Image">
                <?php else: ?>
                    <div class="no-image">No image available</div>
                <?php endif; ?>
            </div>
            
            <div class="location-badge">
                <div class="location-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="location-text">
                    <?php echo htmlspecialchars($part["Location"]); ?>
                </div>
            </div>
        </div>

        <div class="details-section">
            <h2>Part Details</h2>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Part ID</div>
                    <div class="detail-value">#<?php echo $part["PartID"]; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Category</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["Category"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Condition</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["PartCondition"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Quantity</div>
                    <div class="detail-value"><?php echo $part["Quantity"]; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Authenticity</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["Authenticity"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Make</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["Make"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Model</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["Model"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Year Model</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["YearModel"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Part Price</div>
                    <div class="detail-value price-value">₱ <?php echo number_format($part["Price"], 2); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date Added</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["DateAdded"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["LastUpdated"]); ?></div>
                </div>
            </div>
            
            <div class="description-section">
                <h3>Description</h3>
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($part["Description"])); ?>
                </div>
            </div>
        </div>
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
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

.part-detail-container {
    max-width: 1100px;
    margin: 20px auto;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.media-location-wrapper {
    position: relative;
    margin-bottom: 30px;
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
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.part-media-container img:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.location-badge {
    position: absolute;
    bottom: -85px;
    left: 50%;
    transform: translateX(-50%);
    background:rgb(213, 58, 58);
    background: linear-gradient(to right,rgb(228, 93, 93),rgb(43, 8, 3));
    color: white;
    padding: 12px 25px;
    border-radius: 50px;
    font-size: 18px;
    font-weight: bold;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 10;
    min-width: 200px;
    justify-content: center;
}

.location-icon {
    margin-right: 10px;
    font-size: 22px;
}

.location-text {
    letter-spacing: 0.5px;
}

.no-image {
    width: 100%;
    padding: 50px;
    background: #f5f5f5;
    border-radius: 10px;
    color: #888;
    font-style: italic;
    font-size: 18px;
    text-align: center;
}

.details-section {
    margin-top: 40px;
    padding-top: 20px;
}

.details-section h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    text-align: left;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.detail-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.detail-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.detail-label {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 5px;
    font-weight: 500;
}

.detail-value {
    font-size: 16px;
    color: #333;
    font-weight: 500;
}

.price-value {
    color: #28a745;
    font-weight: 700;
    font-size: 18px;
}

.description-section {
    margin-top: 30px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.description-section h3 {
    color: #333;
    font-size: 20px;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.description-content {
    line-height: 1.6;
    color: #495057;
}
</style>