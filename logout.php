<?php
session_start();
session_unset();
session_destroy();
header("Location: \Drafter-Management-System\login.php");
exit();
?>
