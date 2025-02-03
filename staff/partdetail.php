<?php
session_start();

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

<?php
// Mock database data (Replace with DB query)
$part = [
    "id" => 1,
    "name" => "Battery",
    "category" => "Category 1",
    "condition" => "Brand New",
    "location" => "Shelf A",
    "quantity" => 32,
    "authenticity" => "Replacement",
    "make" => "Make",
    "model" => "Model",
    "year_model" => "2002",
    "price" => 100.00,
    "date_added" => "11/12/12",
    "last_updated" => "11/12/12",
    "description" => "Battery description",
];
?>

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
        <h1><?php echo $part["name"]; ?></h1>
    </div>

    <table class="details-table">
        <tr><td>Part ID</td><td>#<?php echo $part["id"]; ?></td></tr>
        <tr><td>Category</td><td><?php echo $part["category"]; ?></td></tr>
        <tr><td>Condition</td><td><?php echo $part["condition"]; ?></td></tr>
        <tr><td>Location</td><td><?php echo $part["location"]; ?></td></tr>
        <tr><td>Quantity</td><td><?php echo $part["quantity"]; ?></td></tr>
        <tr><td>Authenticity</td><td><?php echo $part["authenticity"]; ?></td></tr>
        <tr><td>Make</td><td><?php echo $part["make"]; ?></td></tr>
        <tr><td>Model</td><td><?php echo $part["model"]; ?></td></tr>
        <tr><td>Year Model</td><td><?php echo $part["year_model"]; ?></td></tr>
        <tr><td>Part Price</td><td>$<?php echo $part["price"]; ?></td></tr>
        <tr><td>Date Added</td><td><?php echo $part["date_added"]; ?></td></tr>
        <tr><td>Last Updated</td><td><?php echo $part["last_updated"]; ?></td></tr>
        <tr><td>Description</td><td><?php echo $part["description"]; ?></td></tr>
    </table>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('collapsed');
    }
</script>

<style>
.part-detail-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
}
    </style>