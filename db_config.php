<?php
$host = 'localhost';
$dbname = 'bnm';
$user = 'root';
$pass = '';
try {
    // Try socket connection first, then fallback to regular connection
    $dsn = "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    try {
        // Fallback to regular host connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        die("Database connection failed: " . $e2->getMessage());
    }
}