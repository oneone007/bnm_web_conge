<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate if passwords match
    if ($password !== $confirmPassword) {
        echo json_encode(["success" => false, "error" => "Passwords do not match."]);
        exit();
    }

    // Check if the username already exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Username already exists!"]);
        exit();
    }

    // Insert the new user (storing password as plain text)
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "redirect" => "BNM"]);
    } else {
        echo json_encode(["success" => false, "error" => "Signup failed: " . $conn->error]);
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>
