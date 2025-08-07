<?php
/**
 * Permission Guard
 * A simplified wrapper for the permission system
 * Include this file at the beginning of each page that needs permission check
 */

// Define the page name (use the file name without extension by default)
$guardPage = basename($_SERVER['PHP_SELF'], '.php');

// Include the permission check system
require_once __DIR__ . '/check_permission.php';

// Verify permission
verify_permission($guardPage);
?>
