<?php
session_start();

// Check if user has admin privileges first
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    header("Location: BNM");
    exit();
}

$Role = $_SESSION['Role'] ?? 'Unknown';

if ($Role !== 'Admin' && $Role !== 'Developer') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied. Admin/Developer role required.']);
        exit;
    }
    header("Location: Main");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $navigationFile = __DIR__ . '/navigation.json';
    
    // Debug: Log the path being used
    error_log("Navigation file path: " . $navigationFile);
    error_log("File exists: " . (file_exists($navigationFile) ? 'YES' : 'NO'));
    
    try {
        switch ($_POST['action']) {
            case 'load':
                if (file_exists($navigationFile)) {
                    $content = file_get_contents($navigationFile);
                    if ($content === false) {
                        echo json_encode(['error' => 'Failed to read navigation file']);
                    } else {
                        // Validate JSON before sending
                        $decoded = json_decode($content);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            echo json_encode(['error' => 'Invalid JSON in navigation file: ' . json_last_error_msg()]);
                        } else {
                            echo $content;
                        }
                    }
                } else {
                    echo json_encode(['error' => 'Navigation file not found', 'debug' => [
                        'file_path' => $navigationFile,
                        'current_dir' => __DIR__,
                        'file_exists' => file_exists($navigationFile),
                        'real_path' => realpath(__DIR__),
                        'files_in_dir' => scandir(__DIR__)
                    ]]);
                }
                exit;
                
            case 'save':
                $navigationData = $_POST['navigationData'] ?? '';
                if (empty($navigationData)) {
                    echo json_encode(['error' => 'No navigation data provided']);
                    exit;
                }
                
                $decoded = json_decode($navigationData);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
                } else {
                    // Check if file is writable
                    if (!is_writable($navigationFile)) {
                        echo json_encode(['error' => 'Navigation file is not writable. File: ' . $navigationFile]);
                        exit;
                    }
                    
                    // Check if directory is writable
                    $dir = dirname($navigationFile);
                    if (!is_writable($dir)) {
                        echo json_encode(['error' => 'Directory is not writable. Directory: ' . $dir]);
                        exit;
                    }
                    
                    $result = file_put_contents($navigationFile, $navigationData);
                    if ($result === false) {
                        $error = error_get_last();
                        echo json_encode(['error' => 'Failed to save navigation file. Error: ' . ($error['message'] ?? 'Unknown error')]);
                    } else {
                        echo json_encode(['success' => true, 'message' => 'Navigation saved successfully. Bytes written: ' . $result]);
                    }
                }
                exit;
                
            case 'add_item':
                if (!file_exists($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file not found']);
                    exit;
                }
                
                $navigation = json_decode(file_get_contents($navigationFile), true);
                if ($navigation === null) {
                    echo json_encode(['error' => 'Failed to parse navigation file']);
                    exit;
                }
                
                $newItem = json_decode($_POST['item'] ?? '{}', true);
                if ($newItem === null) {
                    echo json_encode(['error' => 'Invalid item data']);
                    exit;
                }
                
                $parentId = $_POST['parentId'] ?? null;
                
                if ($parentId) {
                    // Add to submenu
                    $found = false;
                    foreach ($navigation['navigation'] as &$section) {
                        if ($section['id'] === $parentId && isset($section['submenu'])) {
                            $section['submenu'][] = $newItem;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        echo json_encode(['error' => 'Parent section not found']);
                        exit;
                    }
                } else {
                    // Add as top-level item
                    $navigation['navigation'][] = $newItem;
                }
                
                if (!is_writable($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file is not writable']);
                    exit;
                }
                
                $result = file_put_contents($navigationFile, json_encode($navigation, JSON_PRETTY_PRINT));
                if ($result === false) {
                    $error = error_get_last();
                    echo json_encode(['error' => 'Failed to save navigation file. Error: ' . ($error['message'] ?? 'Unknown error')]);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Item added successfully']);
                }
                exit;
                
            case 'delete_item':
                if (!file_exists($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file not found']);
                    exit;
                }
                
                $navigation = json_decode(file_get_contents($navigationFile), true);
                if ($navigation === null) {
                    echo json_encode(['error' => 'Failed to parse navigation file']);
                    exit;
                }
                
                $itemId = $_POST['itemId'] ?? '';
                $parentId = $_POST['parentId'] ?? null;
                
                if (empty($itemId)) {
                    echo json_encode(['error' => 'Item ID required']);
                    exit;
                }
                
                if ($parentId) {
                    // Remove from submenu
                    $found = false;
                    foreach ($navigation['navigation'] as &$section) {
                        if ($section['id'] === $parentId && isset($section['submenu'])) {
                            $section['submenu'] = array_filter($section['submenu'], function($item) use ($itemId) {
                                return $item['id'] !== $itemId;
                            });
                            $section['submenu'] = array_values($section['submenu']);
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        echo json_encode(['error' => 'Parent section not found']);
                        exit;
                    }
                } else {
                    // Remove from top-level
                    $navigation['navigation'] = array_filter($navigation['navigation'], function($item) use ($itemId) {
                        return !isset($item['id']) || $item['id'] !== $itemId;
                    });
                    $navigation['navigation'] = array_values($navigation['navigation']);
                }
                
                if (!is_writable($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file is not writable']);
                    exit;
                }
                
                $result = file_put_contents($navigationFile, json_encode($navigation, JSON_PRETTY_PRINT));
                if ($result === false) {
                    $error = error_get_last();
                    echo json_encode(['error' => 'Failed to save navigation file. Error: ' . ($error['message'] ?? 'Unknown error')]);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
                }
                exit;
                
            case 'edit_item':
                if (!file_exists($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file not found']);
                    exit;
                }
                
                $navigation = json_decode(file_get_contents($navigationFile), true);
                if ($navigation === null) {
                    echo json_encode(['error' => 'Failed to parse navigation file']);
                    exit;
                }
                
                $updatedItem = json_decode($_POST['item'] ?? '{}', true);
                if ($updatedItem === null) {
                    echo json_encode(['error' => 'Invalid item data']);
                    exit;
                }
                
                $itemId = $_POST['originalId'] ?? '';
                $parentId = $_POST['originalParentId'] ?? null;
                
                if (empty($itemId)) {
                    echo json_encode(['error' => 'Item ID required']);
                    exit;
                }
                
                $itemFound = false;
                
                if ($parentId) {
                    // Update item in submenu
                    foreach ($navigation['navigation'] as &$section) {
                        if ($section['id'] === $parentId && isset($section['submenu'])) {
                            foreach ($section['submenu'] as &$subItem) {
                                if ($subItem['id'] === $itemId) {
                                    // Preserve existing submenu if updating a section
                                    if ($subItem['type'] === 'section' && isset($subItem['submenu'])) {
                                        $updatedItem['submenu'] = $subItem['submenu'];
                                    }
                                    $subItem = $updatedItem;
                                    $itemFound = true;
                                    break 2;
                                }
                            }
                        }
                    }
                } else {
                    // Update top-level item
                    foreach ($navigation['navigation'] as &$item) {
                        if (isset($item['id']) && $item['id'] === $itemId) {
                            // Preserve existing submenu if updating a section
                            if ($item['type'] === 'section' && isset($item['submenu'])) {
                                $updatedItem['submenu'] = $item['submenu'];
                            }
                            $item = $updatedItem;
                            $itemFound = true;
                            break;
                        }
                    }
                }
                
                if (!$itemFound) {
                    echo json_encode(['error' => 'Item not found']);
                    exit;
                }
                
                if (!is_writable($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file is not writable']);
                    exit;
                }
                
                $result = file_put_contents($navigationFile, json_encode($navigation, JSON_PRETTY_PRINT));
                if ($result === false) {
                    $error = error_get_last();
                    echo json_encode(['error' => 'Failed to save navigation file. Error: ' . ($error['message'] ?? 'Unknown error')]);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
                }
                exit;
                
            case 'reset_navigation':
                $originalFile = __DIR__ . '/navigation_original.json';
                
                if (!file_exists($originalFile)) {
                    echo json_encode(['error' => 'Original navigation file not found']);
                    exit;
                }
                
                $originalContent = file_get_contents($originalFile);
                if ($originalContent === false) {
                    echo json_encode(['error' => 'Failed to read original navigation file']);
                    exit;
                }
                
                if (!is_writable($navigationFile)) {
                    echo json_encode(['error' => 'Navigation file is not writable']);
                    exit;
                }
                
                $result = file_put_contents($navigationFile, $originalContent);
                if ($result === false) {
                    $error = error_get_last();
                    echo json_encode(['error' => 'Failed to reset navigation file. Error: ' . ($error['message'] ?? 'Unknown error')]);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Navigation reset to original successfully']);
                }
                exit;
                
            default:
                echo json_encode(['error' => 'Invalid action']);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Navigation - BNM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 16rem;
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        .json-editor {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .nav-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .nav-item.submenu-item {
            margin-left: 20px;
            border-left: 3px solid #3b82f6;
            background: #f8fafc;
        }
        
        .nav-item.separator {
            border: none;
            height: 2px;
            background: #e5e7eb;
            margin: 16px 0;
            padding: 0;
        }
        
        .dark .nav-item {
            background: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        
        .dark .nav-item.submenu-item {
            background: #1f2937;
        }
        
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .dark .form-control {
            background: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .dark .modal-content {
            background-color: #374151;
            color: #f3f4f6;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .dark .close:hover {
            color: #fff;
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .dark .alert-success {
            background: #064e3b;
            color: #a7f3d0;
            border-color: #047857;
        }
        
        .dark .alert-error {
            background: #7f1d1d;
            color: #fca5a5;
            border-color: #dc2626;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <div class="main-content">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-edit mr-3"></i>Edit Navigation
                </h1>
                <div class="flex gap-3">
                    <button onclick="addNewItem()" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Add New Item
                    </button>
                    <button onclick="saveNavigation()" class="btn btn-success">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                    <button onclick="loadNavigation()" class="btn btn-primary">
                        <i class="fas fa-refresh mr-2"></i>Reload
                    </button>
                    <button onclick="resetNavigation()" class="btn bg-orange-500 text-white hover:bg-orange-600">
                        <i class="fas fa-undo mr-2"></i>Reset to Original
                    </button>
                </div>
            </div>
            
            <div id="alerts"></div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Visual Editor -->
                <div>
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
                        <i class="fas fa-list mr-2"></i>Visual Editor
                    </h2>
                    <div id="visual-editor" class="border dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700 max-h-96 overflow-y-auto">
                        <!-- Navigation items will be loaded here -->
                    </div>
                </div>
                
                <!-- JSON Editor -->
                <div>
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
                        <i class="fas fa-code mr-2"></i>JSON Editor
                    </h2>
                    <textarea 
                        id="json-editor" 
                        class="json-editor form-control h-96 dark:bg-gray-700 dark:text-white font-mono text-sm"
                        placeholder="Navigation JSON will be loaded here..."
                    ></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-edit mr-2"></i><span id="modal-title">Add New Item</span>
            </h2>
            
            <form id="itemForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">ID:</label>
                        <input type="text" id="item-id" class="form-control" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Type:</label>
                        <select id="item-type" class="form-control" onchange="toggleFields()">
                            <option value="page">Page</option>
                            <option value="section">Section</option>
                            <option value="separator">Separator</option>
                        </select>
                    </div>
                    
                    <div id="title-field">
                        <label class="block text-sm font-medium mb-2">Title:</label>
                        <input type="text" id="item-title" class="form-control">
                    </div>
                    
                    <div id="icon-field">
                        <label class="block text-sm font-medium mb-2">Icon (FontAwesome class):</label>
                        <input type="text" id="item-icon" class="form-control" placeholder="e.g., fas fa-home">
                    </div>
                    
                    <div id="page-field">
                        <label class="block text-sm font-medium mb-2">Page:</label>
                        <input type="text" id="item-page" class="form-control">
                    </div>
                    
                    <div id="permission-field">
                        <label class="block text-sm font-medium mb-2">Permission Page:</label>
                        <input type="text" id="item-permission" class="form-control">
                    </div>
                    
                    <div id="hover-field" class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Hover Class (optional):</label>
                        <input type="text" id="item-hover" class="form-control" placeholder="e.g., hover:bg-blue-100 dark:hover:bg-blue-900">
                    </div>
                    
                    <div id="parent-field" class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Parent Section (for submenu items):</label>
                        <select id="item-parent" class="form-control">
                            <option value="">Top Level</option>
                        </select>
                    </div>
                    
                    <div id="roles-field" class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Required Roles (comma-separated):</label>
                        <input type="text" id="item-roles" class="form-control" placeholder="e.g., Admin,Developer">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn bg-gray-500 text-white hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let navigationData = {};
        let editingItem = null;
        let editingParent = null;
        let isEditMode = false; // Track if we're editing or adding
        let editingItemId = null; // Track the ID of the item being edited
        let editingParentId = null; // Track the parent ID of the item being edited
        
        // Load navigation data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNavigation();
        });
        
        function loadNavigation() {
            fetch('editnavbar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=load'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.error) {
                        showAlert('Error loading navigation: ' + data.error, 'error');
                    } else {
                        navigationData = data;
                        updateEditors();
                        showAlert('Navigation loaded successfully', 'success');
                    }
                } catch (e) {
                    console.error('Response text:', text);
                    showAlert('Invalid JSON response: ' + e.message + '. Check console for details.', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showAlert('Network error: ' + error.message, 'error');
            });
        }
        
        function saveNavigation() {
            const jsonData = document.getElementById('json-editor').value;
            
            if (!jsonData.trim()) {
                showAlert('No data to save', 'error');
                return;
            }
            
            try {
                JSON.parse(jsonData); // Validate JSON
                
                fetch('editnavbar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=save&navigationData=' + encodeURIComponent(jsonData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.error) {
                            showAlert('Error saving navigation: ' + data.error, 'error');
                        } else {
                            navigationData = JSON.parse(jsonData);
                            updateVisualEditor();
                            showAlert('Navigation saved successfully! Refresh your sidebar to see changes.', 'success');
                        }
                    } catch (e) {
                        console.error('Response text:', text);
                        showAlert('Invalid JSON response: ' + e.message + '. Check console for details.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showAlert('Network error: ' + error.message, 'error');
                });
            } catch (e) {
                showAlert('Invalid JSON format: ' + e.message, 'error');
            }
        }
        
        function updateEditors() {
            updateVisualEditor();
            updateJsonEditor();
        }
        
        function updateVisualEditor() {
            const container = document.getElementById('visual-editor');
            container.innerHTML = '';
            
            if (navigationData.navigation) {
                navigationData.navigation.forEach((item, index) => {
                    container.appendChild(createVisualItem(item, index));
                });
            }
        }
        
        function updateJsonEditor() {
            document.getElementById('json-editor').value = JSON.stringify(navigationData, null, 2);
        }
        
        function createVisualItem(item, index, parentId = null) {
            const div = document.createElement('div');
            
            if (item.type === 'separator') {
                div.className = 'nav-item separator';
                div.innerHTML = '<small class="text-gray-500">--- Separator ---</small>';
            } else {
                div.className = parentId ? 'nav-item submenu-item' : 'nav-item';
                div.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            ${item.icon ? `<i class="${item.icon}"></i>` : ''}
                            <span class="font-medium">${item.title || item.id}</span>
                            ${item.type === 'section' ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Section</span>' : ''}
                            ${item.type === 'page' ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Page</span>' : ''}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editItem('${item.id}', '${parentId || ''}')" class="btn btn-primary text-xs">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteItem('${item.id}', '${parentId || ''}')" class="btn btn-danger text-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${item.page ? `<div class="text-sm text-gray-600 mt-1">Page: ${item.page}</div>` : ''}
                    ${item.permissionPage ? `<div class="text-sm text-gray-600">Permission: ${item.permissionPage}</div>` : ''}
                `;
                
                // Add submenu items
                if (item.submenu && item.submenu.length > 0) {
                    const submenuContainer = document.createElement('div');
                    submenuContainer.className = 'mt-3';
                    item.submenu.forEach((subItem, subIndex) => {
                        submenuContainer.appendChild(createVisualItem(subItem, subIndex, item.id));
                    });
                    div.appendChild(submenuContainer);
                }
            }
            
            return div;
        }
        
        function addNewItem() {
            editingItem = null;
            editingParent = null;
            isEditMode = false;
            editingItemId = null;
            editingParentId = null;
            document.getElementById('modal-title').textContent = 'Add New Item';
            document.getElementById('itemForm').reset();
            populateParentSelect();
            toggleFields();
            document.getElementById('itemModal').style.display = 'block';
        }
        
        function editItem(itemId, parentId) {
            editingItem = itemId;
            editingParent = parentId || null;
            isEditMode = true;
            editingItemId = itemId;
            editingParentId = parentId || null;
            document.getElementById('modal-title').textContent = 'Edit Item';
            
            const item = findItem(itemId, parentId);
            if (item) {
                document.getElementById('item-id').value = item.id || '';
                document.getElementById('item-type').value = item.type || 'page';
                document.getElementById('item-title').value = item.title || '';
                document.getElementById('item-icon').value = item.icon || '';
                document.getElementById('item-page').value = item.page || '';
                document.getElementById('item-permission').value = item.permissionPage || '';
                document.getElementById('item-hover').value = item.hoverClass || '';
                document.getElementById('item-roles').value = item.roleRequired ? item.roleRequired.join(',') : '';
                
                populateParentSelect();
                document.getElementById('item-parent').value = parentId || '';
                toggleFields();
                document.getElementById('itemModal').style.display = 'block';
            }
        }
        
        function deleteItem(itemId, parentId) {
            if (confirm('Are you sure you want to delete this item?')) {
                fetch('editnavbar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_item&itemId=${itemId}&parentId=${parentId || ''}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showAlert('Error deleting item: ' + data.error, 'error');
                    } else {
                        showAlert('Item deleted successfully', 'success');
                        loadNavigation();
                    }
                })
                .catch(error => {
                    showAlert('Error: ' + error.message, 'error');
                });
            }
        }
        
        function resetNavigation() {
            if (confirm('Are you sure you want to reset navigation to original settings? This cannot be undone.')) {
                fetch('editnavbar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reset_navigation'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showAlert('Error resetting navigation: ' + data.error, 'error');
                    } else {
                        showAlert('Navigation reset to original settings successfully', 'success');
                        loadNavigation();
                    }
                })
                .catch(error => {
                    showAlert('Error: ' + error.message, 'error');
                });
            }
        }
        
        function findItem(itemId, parentId) {
            if (!navigationData.navigation) return null;
            
            if (parentId) {
                const parent = navigationData.navigation.find(item => item.id === parentId);
                if (parent && parent.submenu) {
                    return parent.submenu.find(item => item.id === itemId);
                }
            } else {
                return navigationData.navigation.find(item => item.id === itemId);
            }
            return null;
        }
        
        function populateParentSelect() {
            const select = document.getElementById('item-parent');
            select.innerHTML = '<option value="">Top Level</option>';
            
            if (navigationData.navigation) {
                navigationData.navigation.forEach(item => {
                    if (item.type === 'section' && item.id) {
                        select.innerHTML += `<option value="${item.id}">${item.title || item.id}</option>`;
                    }
                });
            }
        }
        
        function toggleFields() {
            const type = document.getElementById('item-type').value;
            const titleField = document.getElementById('title-field');
            const iconField = document.getElementById('icon-field');
            const pageField = document.getElementById('page-field');
            const permissionField = document.getElementById('permission-field');
            const hoverField = document.getElementById('hover-field');
            const rolesField = document.getElementById('roles-field');
            
            if (type === 'separator') {
                titleField.style.display = 'none';
                iconField.style.display = 'none';
                pageField.style.display = 'none';
                permissionField.style.display = 'none';
                hoverField.style.display = 'none';
                rolesField.style.display = 'none';
            } else {
                titleField.style.display = 'block';
                iconField.style.display = 'block';
                rolesField.style.display = 'block';
                
                if (type === 'page') {
                    pageField.style.display = 'block';
                    permissionField.style.display = 'block';
                    hoverField.style.display = 'block';
                } else {
                    pageField.style.display = 'none';
                    permissionField.style.display = 'none';
                    hoverField.style.display = 'block';
                }
            }
        }
        
        function closeModal() {
            document.getElementById('itemModal').style.display = 'none';
        }
        
        document.getElementById('itemForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newItem = {
                id: document.getElementById('item-id').value,
                type: document.getElementById('item-type').value
            };
            
            if (newItem.type !== 'separator') {
                newItem.title = document.getElementById('item-title').value;
                newItem.icon = document.getElementById('item-icon').value;
                
                if (newItem.type === 'page') {
                    newItem.page = document.getElementById('item-page').value;
                    newItem.permissionPage = document.getElementById('item-permission').value;
                }
                
                const hoverClass = document.getElementById('item-hover').value;
                if (hoverClass) newItem.hoverClass = hoverClass;
                
                const roles = document.getElementById('item-roles').value;
                if (roles) newItem.roleRequired = roles.split(',').map(r => r.trim());
                
                if (newItem.type === 'section') {
                    newItem.submenu = [];
                }
            }
            
            const parentId = document.getElementById('item-parent').value;
            
            const action = isEditMode ? 'edit_item' : 'add_item';
            let body = `action=${action}&item=${encodeURIComponent(JSON.stringify(newItem))}&parentId=${parentId}`;
            
            if (isEditMode) {
                body += `&originalId=${editingItemId}&originalParentId=${editingParentId || ''}`;
            }
            
            fetch('editnavbar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert('Error saving item: ' + data.error, 'error');
                } else {
                    const message = isEditMode ? 'Item updated successfully' : 'Item saved successfully';
                    showAlert(message, 'success');
                    loadNavigation();
                    closeModal();
                    // Reset edit mode
                    isEditMode = false;
                    editingItemId = null;
                    editingParentId = null;
                }
            })
            .catch(error => {
                showAlert('Error: ' + error.message, 'error');
            });
        });
        
        // Update JSON editor when typing
        document.getElementById('json-editor').addEventListener('input', function() {
            try {
                const data = JSON.parse(this.value);
                navigationData = data;
                updateVisualEditor();
            } catch (e) {
                // Invalid JSON, don't update visual editor
            }
        });
        
        function showAlert(message, type) {
            const alertsContainer = document.getElementById('alerts');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <div class="flex justify-between items-center">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-lg font-bold ml-4">&times;</button>
                </div>
            `;
            alertsContainer.appendChild(alert);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 5000);
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('itemModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
