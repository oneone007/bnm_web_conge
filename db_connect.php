

// $host = 'localhost'; // Database host
// $dbname = 'bnm'; // Database name
// $username = 'root'; // Default username for XAMPP
// $password = ''; // Default password for XAMPP (empty)

// // Create connection
// $conn = new mysqli($host, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }




<?php
$host = 'localhost'; // Database host
$dbname = 'bnm_web'; // Database name
$username = 'bmk'; // Default username for XAMPP
$password = 'bnm911'; // Default password for XAMPP (empty)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>