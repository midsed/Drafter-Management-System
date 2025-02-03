<?php
// Connect to the database
include 'dbconnect.php'; // Adjust based on your database connection

// Check if the email parameter is available in the URL
if (isset($_GET['email'])) {
    $email = $_GET['email'];
} elseif (isset($_POST['email'])) {
    $email = $_POST['email'];
} else {
    echo "Error: Email parameter is missing.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input fields
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($new_password === $confirm_password) {
        // Hash the new password for security
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Use prepared statements to prevent SQL injection
        $query = "UPDATE user SET password = ? WHERE email = ?";

        // Prepare the query
        if ($stmt = $conn->prepare($query)) {
            // Bind the parameters (the "ss" means 2 string parameters)
            $stmt->bind_param("ss", $hashed_password, $email);

            // Execute the query
            if ($stmt->execute()) {
                echo "Password reset successful.";
                // Redirect to login page after success
                header("Location: login.php");
                exit();
            } else {
                echo "Error resetting password: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error preparing query: " . $conn->error;
        }
    } else {
        echo "Passwords do not match.";
    }
}

// Close the database connection
$conn->close();
?>
