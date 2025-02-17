<?php
session_start();
require_once "dbconnect.php";

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure these fields are passed from partsedit.php
    $part_id = $_POST['part_id'];  
    $part_name = $_POST['part_name'];  // Rename according to the table column
    $part_price = $_POST['part_price'];
    $quantity = $_POST['quantity'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year_model = $_POST['year_model'];
    $description = $_POST['description'];
    $part_condition = $_POST['part_condition']; // Ensure this field is populated
    
    // Image upload handling
    if (!empty($_FILES['part_image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["part_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["part_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                die("Error uploading file.");
            }
        } else {
            die("Invalid file type. Allowed types: JPG, JPEG, PNG, GIF.");
        }
    } else {
        // If no new image is uploaded, keep the existing image
        $query = $conn->prepare("SELECT Media FROM part WHERE PartID = ?");
        $query->bind_param("i", $part_id);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $image_path = $row['Media']; // Use the column name Media
    }

    // Prepare update query with error handling
    $stmt = $conn->prepare("UPDATE part SET Name = ?, Price = ?, Quantity = ?, Make = ?, Model = ?, YearModel = ?, PartCondition = ?, Description = ?, Media = ? WHERE PartID = ?");
    
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }

    // Bind the parameters correctly
    $stmt->bind_param("sdissssssi", $part_name, $part_price, $quantity, $make, $model, $year_model, $part_condition, $description, $image_path, $part_id);

    // Execute and check if it worked
    if ($stmt->execute()) {
        echo "<script>alert('Part updated successfully!'); window.location.href='parts.php';</script>";
    } else {
        echo "<script>alert('Error updating part.'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>
