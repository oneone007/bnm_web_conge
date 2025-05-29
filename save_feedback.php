<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Include DB config
require_once 'db_config.php'; // Adjust path accordingly

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$type = $data['type'] ?? null; // 'bug', 'suggestion', or 'rating'
$content = $data['content'] ?? '';
$rating = $data['rating'] ?? null;
$page = $data['page'] ?? null;

$user_id = $_SESSION['user_id'];

// Validate input
if (!$type || !in_array($type, ['bug', 'suggestion', 'rating'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid feedback type']);
    exit;
}

// Prepare SQL statement
$stmt = $pdo->prepare("INSERT INTO user_feedback (user_id, type, content, rating, page) VALUES (?, ?, ?, ?, ?)");

try {
    $stmt->execute([$user_id, $type, $content, $rating, $page]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}