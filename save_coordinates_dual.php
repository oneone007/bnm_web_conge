<?php
header('Content-Type: application/json');

// Set the paths for the JSON files
$canvaFilePath = __DIR__ . '/canva.json';
$coordinatesFilePath = __DIR__ . '/coordinates.json';

// Handle POST request to save coordinates to both files
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
    
    // Write the JSON data to both files
    $canvaSuccess = file_put_contents($canvaFilePath, $jsonData);
    $coordinatesSuccess = file_put_contents($coordinatesFilePath, $jsonData);
    
    if ($canvaSuccess === false || $coordinatesSuccess === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to write to one or both files']);
        exit;
    }
    
    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Coordinates saved to both canva.json and coordinates.json successfully']);
} 
// Handle GET request to load coordinates
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check which file to load from based on the 'type' parameter
    $type = isset($_GET['type']) ? $_GET['type'] : 'canva'; // Default to canva for display
    $filePath = ($type === 'print') ? $coordinatesFilePath : $canvaFilePath;
    
    // Check if the file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => ucfirst($type) . ' file not found']);
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
