<?php
// Enable error reporting for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['Role'], ['Developer', 'Admin'])) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Check if this is a POST request with permissions data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['permissions'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Get permissions data
$permissions = json_decode($_POST['permissions'], true);
if (!is_array($permissions)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Invalid permissions format']);
    exit();
}

// Path to sidebar.php and permissions.json
$sidebarPath = __DIR__ . '/sidebar.php';
$permissionsJsonPath = __DIR__ . '/permissions.json';

// Check if sidebar.php exists and is writable
if (!file_exists($sidebarPath)) {
    echo json_encode(['success' => false, 'message' => 'sidebar.php not found']);
    exit();
}

if (!is_writable($sidebarPath)) {
    echo json_encode(['success' => false, 'message' => 'sidebar.php is not writable. Check file permissions.']);
    exit();
}

// Save the permissions to the JSON file
$jsonContent = json_encode($permissions, JSON_PRETTY_PRINT);
$writeResult = @file_put_contents($permissionsJsonPath, $jsonContent);
if ($writeResult === false) {
    $error = error_get_last();
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to write permissions to JSON file: ' . ($error ? $error['message'] : 'Unknown error'),
        'file' => $permissionsJsonPath,
        'is_writable' => is_writable($permissionsJsonPath),
        'dir_writable' => is_writable(dirname($permissionsJsonPath))
    ]);
    exit();
}

// Read the sidebar file
$sidebarContent = file_get_contents($sidebarPath);
if ($sidebarContent === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to read sidebar.php']);
    exit();
}

// Find and replace the permissions array in the file with code to read from JSON
$newCode = <<<'PHP'
$role_allowed_pages = [];
$permissionsJsonPath = __DIR__ . '/permissions.json';
if (file_exists($permissionsJsonPath)) {
    $jsonContent = file_get_contents($permissionsJsonPath);
    if ($jsonContent !== false) {
        $role_allowed_pages = json_decode($jsonContent, true);
    }
}

// If JSON reading fails, set default permissions
if (empty($role_allowed_pages)) {
    $role_allowed_pages = [
        'Admin' => 'all',
        'Developer' => 'all'
    ];
}
PHP;

$pattern = '/\$role_allowed_pages\s*=\s*\[\s*.*?\s*\]\s*;/s';
$newContent = preg_replace($pattern, $newCode, $sidebarContent);

if ($newContent === null || $newContent === $sidebarContent) {
    echo json_encode(['success' => false, 'message' => 'Failed to update permissions in sidebar.php']);
    exit();
}

// Write the updated content back to the file
if (file_put_contents($sidebarPath, $newContent) === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to write updated content to sidebar.php']);
    exit();
}

echo json_encode([
    'success' => true, 
    'message' => 'Permissions updated successfully. Saved to JSON file and sidebar.php was updated to read from it.'
]);
