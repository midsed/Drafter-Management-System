<div class="topbar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="topbar-logo">
        <img src="images/Drafter Logo Cropped.png" alt="Logo">
    </div>
    <div class="username">
        <span>
            <?php
            // Display the username if available; otherwise, show Guest
            echo isset($_SESSION['Username']) ? htmlspecialchars($_SESSION['Username']) : 'Guest';
            ?>
        </span>
        <a href="javascript:void(0);" onclick="confirmLogout()">
            <img src="https://i.ibb.co/gwbp9KZ/logout.png" alt="Logout" class="logout-icon">
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of your session!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log me out'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/DrafterAutotech/Drafter-Management-System/logout.php';
            }
        });
    }
</script>
