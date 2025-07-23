<?php
// Start the session

// Path to recipients JSON file
$file = __DIR__ . '/recipients.json';

// Make sure the directory is writable
if (!is_writable(__DIR__)) {
    error_log("Directory is not writable: " . __DIR__);
}

// Handle GET request to retrieve recipients
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($file)) {
        // Return the recipients JSON
        header('Content-Type: application/json');
        echo file_get_contents($file);
    } else {
        // Return empty array if file doesn't exist
        header('Content-Type: application/json');
        echo json_encode([]);
    }
}

// Handle POST request to save recipient
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    if ($data && isset($data->recipient)) {
        $recipient = trim($data->recipient);
        
        // Read existing recipients or create new array
        $recipients = [];
        if (file_exists($file)) {
            $recipients = json_decode(file_get_contents($file), true);
        }
        
        // Add recipient if it doesn't exist
        if (!in_array($recipient, $recipients)) {
            $recipients[] = $recipient;
            
            // Save updated recipients
            if (file_put_contents($file, json_encode($recipients))) {
                // Return success
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success']);
            } else {
                // Return error
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Failed to save recipient']);
            }
        } else {
            // Recipient already exists
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Recipient already exists']);
        }
    } else {
        // Invalid data
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
}

// Handle DELETE request to remove recipient
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    if ($data && isset($data->recipient)) {
        $recipient = $data->recipient;
        
        // Read existing recipients
        if (file_exists($file)) {
            $recipients = json_decode(file_get_contents($file), true);
            
            // Find and remove recipient
            $index = array_search($recipient, $recipients);
            if ($index !== false) {
                array_splice($recipients, $index, 1);
                
                // Save updated recipients
                if (file_put_contents($file, json_encode($recipients))) {
                    // Return success
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success']);
                } else {
                    // Return error
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update recipients']);
                }
            } else {
                // Recipient not found
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Recipient not found']);
            }
        } else {
            // File doesn't exist
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No recipients exist']);
        }
    } else {
        // Invalid data
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
}
?>
