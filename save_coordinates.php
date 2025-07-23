<?php
header('Content-Type: application/json');

// Set the path for the JSON file
$filePath = __DIR__ . '/coordinates.json';

// Handle POST request to save coordinates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the request body
    $jsonData = file_get_contents('php://input');
    
    // Decode the JSON to verify it's valid
    $data = json_decode($jsonData);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit;
    }
    
    // Write the JSON data to the file
    if (file_put_contents($filePath, $jsonData) === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to write to file']);
        exit;
    }
    
    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Coordinates saved successfully']);
} 
// Handle GET request to load coordinates
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Coordinates file not found']);
        exit;
    }
    
    // Read the file contents
    $jsonData = file_get_contents($filePath);
    if ($jsonData === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to read file']);
        exit;
    }
    
    // Return the JSON data
    echo $jsonData;
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>
