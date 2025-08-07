<?php
session_start();

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'db_connect.php';

// Verify connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

try {
    // Only proceed if we have a session log ID to update
    if (isset($_SESSION['session_log_id'])) {
        $logout_time = date('Y-m-d H:i:s');
        $session_log_id = $_SESSION['session_log_id'];
        
        // Update the session log
        $update_sql = "UPDATE user_sessions SET logout_time = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("si", $logout_time, $session_log_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Execute failed: " . $update_stmt->error);
        }
        
        $update_stmt->close();
    }

    // Clear all session data
    $_SESSION = array();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: ../BNM");
    exit();

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    
    // Still attempt to destroy session even if DB update failed
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Redirect with error message
    $_SESSION['logout_error'] = "Logout completed with minor issues";
    header("Location: ../BNM");
    exit();
}

// Final fallback (should never reach here)
die("Logout completed. <a href='../BNM'>Click here</a> if not redirected.");
?>