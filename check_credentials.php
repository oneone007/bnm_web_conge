<?php
include 'db_connect.php'; // Database connection

header('Content-Type: application/json');

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Compare as plain text (no hashing)
        if ($password === $user['password']) {
            echo json_encode(["valid" => true]);
        } else {
            echo json_encode(["valid" => false, "error" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["valid" => false, "error" => "Username not found"]);
    }

    $stmt->close();
    $conn->close();
}
?>
