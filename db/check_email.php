<?php
include 'db_connect.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the username from the POST request
    $username = $_POST['username'];

    // Check if the username exists in the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Username exists, allow login
        echo 'exists'; // Username exists
    } else {
        // Username does not exist
        echo 'not_found'; // Username is not found
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>