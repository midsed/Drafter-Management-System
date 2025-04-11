<?php
session_start();
include('dbconnect.php');

if (!isset($_SESSION['UserID']) || $_SESSION['RoleType'] != 'Admin') {
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

$sql = "SELECT p.*, s.CompanyName, s.Email, s.PhoneNumber 
       FROM part p 
       LEFT JOIN supplier s ON p.SupplierID = s.SupplierID 
       WHERE p.PartID = ?";
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
                    <div class="detail-label">Chassis Number</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["ChassisNumber"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Condition</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["PartCondition"]); ?></div>
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
                    <div class="detail-label">Category</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["Category"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Authenticity</div>
                    <div class="detail-value"><?php echo htmlspecialchars($part["Authenticity"]); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Part Price</div>
                    <div class="detail-value price-value">₱ <?php echo number_format($part["Price"], 2); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Total Quantity</div>
                    <div class="detail-value"><?php echo $part["Quantity"]; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Quantity Left</div>
                    <div class="detail-value"><?php echo $part["QuantityLeft"]; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Quantity Right</div>
                    <div class="detail-value"><?php echo $part["QuantityRight"]; ?></div>
                </div>
            </div>
            <div class="details-grid1">
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

            <div class="supplier-section">
                <h3>Supplier Information</h3>
                <div class="supplier-details">
                    <div class="supplier-item">
                        <div class="supplier-label">Company Name</div>
                        <div class="supplier-value"><?php echo htmlspecialchars($part["CompanyName"] ?? 'Not Available'); ?></div>
                    </div>
                    <div class="supplier-item">
                        <div class="supplier-label">Email</div>
                        <div class="supplier-value"><?php echo htmlspecialchars($part["Email"] ?? 'Not Available'); ?></div>
                    </div>
                    <div class="supplier-item">
                        <div class="supplier-label">Phone Number</div>
                        <div class="supplier-value"><?php echo htmlspecialchars($part["PhoneNumber"] ?? 'Not Available'); ?></div>
                    </div>
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

        // Save the sidebar state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }

    function checkSidebarState() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        // Apply the saved state on page load
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('collapsed');
        }
    }

    // Check the sidebar state when the page loads
    document.addEventListener("DOMContentLoaded", function () {
        checkSidebarState();
    });
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
    background: rgb(213, 58, 58);
    background: linear-gradient(to right, rgb(228, 93, 93), rgb(43, 8, 3));
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

.details-grid1 {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 50px;
}

.detail-item {
    background: #ffffff; /* White background for clarity */
    border: 1px solid #ccc; /* Light gray border for distinction */
    border-radius: 8px;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative; /* For positioning pseudo-elements */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

.detail-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
}

.detail-item::before {
    content: ""; /* Pseudo-element for decorative effect */
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px; /* Height of the decorative line */
    background:rgba(255, 0, 0, 0.55); /* Blue color for the line */
    border-radius: 8px 8px 0 0; /* Rounded top corners */
}

.detail-label {
    font-size: 14px;
    color: #495057; /* Darker gray for better readability */
    margin-bottom: 5px;
    font-weight: 600; /* Slightly bolder for emphasis */
}

.detail-value {
    font-size: 16px;
    color: #333; /* Darker color for value text */
    font-weight: 500;
}

.price-value {
    color: #28a745; /* Green for price */
    font-weight: 700;
    font-size: 18px;
}

.description-section {
    margin-top: 30px;
    background: #f8f9fa; /* Light gray background for description */
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
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
    color: #495057; /* Darker gray for description text */
}

.supplier-section {
    margin-top: 30px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.supplier-section h3 {
    color: #333;
    font-size: 20px;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.supplier-details {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.supplier-item {
    background: #ffffff;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.supplier-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.supplier-item::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: rgba(255, 0, 0, 0.55);
    border-radius: 8px 8px 0 0;
}

.supplier-label {
    font-size: 14px;
    color: #495057;
    margin-bottom: 5px;
    font-weight: 600;
}

.supplier-value {
    font-size: 16px;
    color: #333;
    font-weight: 500;
}

</style>