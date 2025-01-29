<?php include('navigation/sidebar.php'); ?>
<?php include('navigation/topbar.php'); ?>
<?php include('dbconnect.php'); ?>

<link rel="stylesheet" href="css/style.css">

<div class="main-content">
    <div class="header">
    <a href="javascript:void(0);" onclick="window.history.back();" style="text-decoration: none;">
      <img src="https://i.ibb.co/M68249k/go-back-arrow.png" alt="Back" style="width: 35px; height: 35px; margin-right: 20px;">
    </a>
        <h1>Edit User</h1>
    </div>

    <?php
    if (isset($_GET['UserID'])) {
        $userID = $_GET['UserID'];
        $sql = "SELECT UserID, FName, LName, Email, Username, RoleType, Status FROM user WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            echo "<p>User not found.</p>";
            exit;
        }
    } else {
        echo "<p>Invalid request.</p>";
        exit;
    }
    ?>

    <form action="process_edit_user.php" method="post">
        <input type="hidden" name="UserID" value="<?php echo $user['UserID']; ?>">

        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['FName']); ?>" required>
        </div>

        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['LName']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
        </div>

        <div class="form-group">
            <label for="user_role">User Role:</label>
            <select id="user_role" name="user_role" required>
                <option value="user" <?php echo $user['RoleType'] == 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo $user['RoleType'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status:</label>
            <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($user['Status']); ?>" disabled>
        </div>

        <button type="submit" class="btn">Update</button>

        <?php if ($user['Status'] == 'Active') { ?>
            <a href="process_user_status.php?UserID=<?php echo $user['UserID']; ?>&status=Inactive">
                <button type="button" class="btn" style="background-color: #C00F0C;">Mark as Inactive</button>
            </a>
        <?php } else { ?>
            <a href="process_user_status.php?UserID=<?php echo $user['UserID']; ?>&status=Active">
                <button type="button" class="btn" style="background-color: #28a745;">Mark as Active</button>
            </a>
        <?php } ?>
    </form>
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
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    .btn {
        background-color: #272727;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .btn:focus {
        outline: none;
    }
</style>
