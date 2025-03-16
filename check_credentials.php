<?php
include 'db_connect.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Plain text comparison (No hashing)
        if ($password === $user['password']) {
            echo "valid";
        } else {
            echo "error: Incorrect password";
        }
    } else {
        echo "error: Username not found";
    }

    $stmt->close();
    $conn->close();
}
?>
