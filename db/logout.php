<?php
session_start();
session_destroy();
header("Location: ../BNM"); // Redirect to login page after logout
exit();
?>
