<?php
include('dbconnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "UPDATE part SET archived = 0 WHERE PartID = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "Part re-listed successfully.";
        } else {
            echo "Failed to re-list part.";
        }
        $stmt->close();
    }
} else {
    echo "Invalid request.";
}
?>
