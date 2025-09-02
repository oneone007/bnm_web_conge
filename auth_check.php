<?php
// Authentication check for Flask API integration
// This file provides session data to initialize Flask authentication

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // User not logged in, redirect to login
    header('Location: BNM');
    exit();
}

// Function to get session data for Flask API
function getSessionDataForFlask() {
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['Role'] ?? 'user'
    ];
}

// Function to generate session data JavaScript
function generateSessionDataJS() {
    $sessionData = getSessionDataForFlask();
    return "
    <script>
        // Pass PHP session data to JavaScript for Flask authentication
        window.phpSessionData = " . json_encode($sessionData) . ";
    </script>
    ";
}
?>
