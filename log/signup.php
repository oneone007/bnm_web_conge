<?php
include 'db_connect.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate if passwords match
    if ($password !== $confirmPassword) {
        echo json_encode(["success" => false, "error" => "Passwords do not match."]);
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Username already exists!"]);
    } else {
        // Insert the new user
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "redirect" => "login.html"]);
        } else {
            echo json_encode(["success" => false, "error" => "Error during signup. Please try again."]);
        }
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>
