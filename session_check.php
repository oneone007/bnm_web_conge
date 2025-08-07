<?php
// Session termination checker - Include this in pages that require login
// This checks if the user's session has been force-logged out by an admin

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['session_log_id'])) {
    
    // Database connection
    $configPath = __DIR__ . '/db_config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
        
        try {
            $conn = new mysqli($host, $user, $pass, $dbname);
            
            if (!$conn->connect_error) {
                // Check if this session has been force-logged out
                $check_sql = "SELECT logout_time FROM user_sessions WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                
                if ($check_stmt) {
                    $check_stmt->bind_param("i", $_SESSION['session_log_id']);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        
                        // If logout_time is set, this session has been terminated
                        if (!empty($row['logout_time'])) {
                            // Clear session and redirect to login
                            $_SESSION = array();
                            
                            // Delete session cookie
                            if (ini_get("session.use_cookies")) {
                                $params = session_get_cookie_params();
                                setcookie(session_name(), '', time() - 42000,
                                    $params["path"], $params["domain"],
                                    $params["secure"], $params["httponly"]
                                );
                            }
                            
                            session_destroy();
                            
                            // Redirect with message
                            header("Location: BNM?message=session_terminated");
                            exit();
                        }
                    }
                    
                    $check_stmt->close();
                }
                
                $conn->close();
            }
        } catch (Exception $e) {
            // Log error but don't break the application
            error_log("Session check error: " . $e->getMessage());
        }
    }
}
?>
