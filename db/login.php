<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) { // Plain text comparison
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['Role'] = $user['Role'];  // Store role in session
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../Main"); // Admin page
            } else {
                header("Location: ../Main"); // Normal user page
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect password";
        }
    } else {
        $_SESSION['login_error'] = "Username not found";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../BNM"); // Redirect back to login page with error
    exit();
}
