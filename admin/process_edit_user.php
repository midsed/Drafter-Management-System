<?php
include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = $_POST['UserID'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $role = $_POST['user_role'];

    $sql = "UPDATE user SET FName = ?, LName = ?, Email = ?, Username = ?, RoleType = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $lastname, $email, $username, $role, $userID);
    $stmt->execute();

    header('Location: users.php');
}
?>
