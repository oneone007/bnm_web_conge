<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Session is NOT working!";
} else {
    echo "Session is working! User ID: " . $_SESSION['user_id'];
}
?>
