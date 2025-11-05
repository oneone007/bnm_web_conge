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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify required fields exist
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['login_error'] = "Username and password are required";
        header("Location: ../BNM");
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Get user from database
        $sql = "SELECT id, username, password, Role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using password_verify for hashed passwords
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['Role'] = $user['Role']; // Case matches your database
                
                // Log session information
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $login_time = date('Y-m-d H:i:s');
                
                // Check if this specific IP already has an active session for this user
                $check_sql = "SELECT id FROM user_sessions WHERE username = ? AND ip_address = ? AND logout_time IS NULL";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ss", $username, $ip_address);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // This IP already has an active session - update it
                    $row = $check_result->fetch_assoc();
                    $update_sql = "UPDATE user_sessions SET login_time = ?, user_agent = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssi", $login_time, $user_agent, $row['id']);
                    $update_stmt->execute();
                    $_SESSION['session_log_id'] = $row['id'];
                    $update_stmt->close();
                } else {
                    // This is a new IP or first login - create new session row
                    $insert_sql = "INSERT INTO user_sessions (username, ip_address, user_agent, login_time) VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ssss", $username, $ip_address, $user_agent, $login_time);
                    $insert_stmt->execute();
                    $_SESSION['session_log_id'] = $insert_stmt->insert_id;
                    $insert_stmt->close();
                }
                
                $check_stmt->close();
                
                // Redirect based on role
                header("Location: ../Main");
                exit();
            } else {
                $_SESSION['login_error'] = "Incorrect password";
            }
        } else {
            $_SESSION['login_error'] = "Username not found";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['login_error'] = "System error: Please try again later";
        error_log("Login error: " . $e->getMessage());
    }
}

// Close connection and redirect on failure
$conn->close();
header("Location: ../BNM");
exit();
?>