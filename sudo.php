<?php
session_start();

// Include permission system - it will automatically check if the user has access to this page
require_once 'check_permission.php';

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bnm';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submissions
$message = '';
$messageType = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_user') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $custom_role = $_POST['custom_role'] ?? '';
    
    // If custom role is provided and not empty, use it instead of the selected role
    if (!empty($custom_role)) {
        $role = $custom_role;
    }
    
    // Never allow creating users with Developer role
    if (strcasecmp($role, 'Developer') === 0) {
        $message = "Error: Creating users with Developer role is not allowed!";
        $messageType = "error";
    }
    else if (!empty($username) && !empty($password) && !empty($role)) {
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "Username already exists!";
            $messageType = "error";
        } else {
            // Hash password using bcrypt
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, Role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $message = "User created successfully!";
                $messageType = "success";
            } else {
                $message = "Error creating user: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
        $checkStmt->close();
    } else {
        $message = "Username, password, and role are required!";
        $messageType = "error";
    }
}

// Handle user edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_user') {
    $userId = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $custom_role = $_POST['custom_role'] ?? '';
    
    // If custom role is provided and not empty, use it instead of the selected role
    if (!empty($custom_role)) {
        $role = $custom_role;
    }
    
    if (!empty($userId) && !empty($username) && !empty($password) && !empty($role)) {
        // Check if the user being edited has Developer role
        $checkStmt = $conn->prepare("SELECT Role FROM users WHERE id = ?");
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $userData = $checkResult->fetch_assoc();
        $checkStmt->close();
        
        // Only allow Developer role to be edited by users with Developer role
        $canEdit = true;
        if ($userData && $userData['Role'] === 'Developer') {
            if ($_SESSION['Role'] !== 'Developer') {
                $message = "Error: Only users with Developer role can modify Developer accounts!";
                $messageType = "error";
                $canEdit = false;
            }
        }
        
        if ($canEdit) {
            // Hash password using bcrypt
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Update user
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, Role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $hashed_password, $role, $userId);
            
            if ($stmt->execute()) {
                $message = "User updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating user: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    } else {
        $message = "All fields are required!";
        $messageType = "error";
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $userId = $_POST['user_id'] ?? '';
    
    if (!empty($userId)) {
        // Check if the user being deleted has Developer role
        $checkStmt = $conn->prepare("SELECT Role FROM users WHERE id = ?");
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $userData = $checkResult->fetch_assoc();
        $checkStmt->close();
        
        // Only allow Developer role to be deleted by users with Developer role
        $canDelete = true;
        if ($userData && $userData['Role'] === 'Developer') {
            if ($_SESSION['Role'] !== 'Developer') {
                $message = "Error: Only users with Developer role can delete Developer accounts!";
                $messageType = "error";
                $canDelete = false;
            }
        }
        
        if ($canDelete) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                $message = "User deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error deleting user: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    } else {
        $message = "User ID is required!";
        $messageType = "error";
    }
}

// Fetch all users for display
$users = [];
$query = "SELECT * FROM users ORDER BY Role, username";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
}

// Fetch website schema (pages and roles)
$roleAccess = [];
$permissionsJsonPath = __DIR__ . '/permissions.json';
if (file_exists($permissionsJsonPath)) {
    $jsonContent = file_get_contents($permissionsJsonPath);
    if ($jsonContent !== false) {
        $roleAccess = json_decode($jsonContent, true);
    }
}

// If JSON reading fails, set default permissions
if (empty($roleAccess)) {
    $roleAccess = [
        'Admin' => 'all',
        'Developer' => 'all',
        'DRH' => 'all'
    ];
}

// Get all pages available
$allPages = [
    'Annual_Recap_A', 'Annual_Recap_V', 'CONFIRMED_ORDERS', 'DETTE_F', 'ETAT_F', 
    'ETAT_F_CUMULE', 'Etatstock', 'Journal_Vente', 'Mouvement_Stock', 'Product', 
    'Quota', 'Recap_Achat', 'Recap_Vente', 'Recap_Vente_Facturation', 'Rotation', 
    'bank', 'charge', 'feedback', 'inventory/inv', 'inventory/inv_admin', 'inventory/inv_saisie', 
    'mony', 'portf', 'print', 'recap_achat_facturation', 'recouverement', 'retour',
    'rot_men_achat', 'rot_men_vente', 'sess', 'simuler', 'sudo', 'AFFECTATION'
];

// Add any missing pages from role access definitions
foreach ($roleAccess as $role => $pages) {
    if ($pages === 'all') continue;
    foreach ($pages as $page) {
        if (!in_array($page, $allPages)) {
            $allPages[] = $page;
        }
    }
}
sort($allPages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="theme.js"></script>

   <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .flash-message {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .flash-message.success {
            background-color: #dcfce7;
            color: #166534;
        }
        .flash-message.error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .search-highlight {
            background-color: #fef08a;
            padding: 0.125rem;
            border-radius: 0.125rem;
        }
        
        /* Custom Styles for Password Fields */
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        
        /* Animation for tabs */
        .tab-content {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s, transform 0.3s;
        }
        .tab-content.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Schema visualization styles */
        .schema-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .schema-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            transition: all 0.3s;
        }
        .schema-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        .page-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        /* Enhanced light mode styles */
        body:not(.dark-mode) {
            background-color: #f8fafc;
            color: #1e293b;
        }
        
        /* Light mode flash messages */
        body:not(.dark-mode) .flash-message.success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        body:not(.dark-mode) .flash-message.error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        /* Light mode search highlight */
        body:not(.dark-mode) .search-highlight {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        /* Light mode schema cards */
        body:not(.dark-mode) .schema-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        body:not(.dark-mode) .schema-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }
        
        /* Light mode forms and inputs */
        body:not(.dark-mode) input[type="text"],
        body:not(.dark-mode) input[type="password"],
        body:not(.dark-mode) select {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            font-weight: 500;
        }
        body:not(.dark-mode) input[type="text"]:focus,
        body:not(.dark-mode) input[type="password"]:focus,
        body:not(.dark-mode) select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #ffffff;
        }
        body:not(.dark-mode) input::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }
        
        /* Light mode search and filter inputs enhanced */
        body:not(.dark-mode) #userSearch {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        body:not(.dark-mode) #userSearch:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        body:not(.dark-mode) #roleFilter {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        body:not(.dark-mode) #roleFilter:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Light mode buttons */
        body:not(.dark-mode) .bg-blue-600 {
            background-color: #2563eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        body:not(.dark-mode) .bg-blue-600:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        body:not(.dark-mode) .bg-green-600 {
            background-color: #059669;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        body:not(.dark-mode) .bg-green-600:hover {
            background-color: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        body:not(.dark-mode) .bg-red-600 {
            background-color: #dc2626;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        body:not(.dark-mode) .bg-red-600:hover {
            background-color: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        /* Light mode action buttons in table */
        body:not(.dark-mode) .text-blue-600 {
            color: #2563eb;
        }
        body:not(.dark-mode) .text-blue-600:hover {
            color: #1d4ed8;
            transform: scale(1.1);
            transition: all 0.2s ease;
        }
        body:not(.dark-mode) .text-red-600 {
            color: #dc2626;
        }
        body:not(.dark-mode) .text-red-600:hover {
            color: #b91c1c;
            transform: scale(1.1);
            transition: all 0.2s ease;
        }
        
        /* Light mode tables */
        body:not(.dark-mode) .bg-white {
            background-color: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        body:not(.dark-mode) .bg-gray-100 {
            background-color: #f3f4f6;
        }
        
        /* Light mode table headers enhanced */
        body:not(.dark-mode) thead th {
            background-color: #f8fafc;
            color: #374151;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        
        /* Light mode table rows and hover */
        body:not(.dark-mode) tbody tr {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }
        body:not(.dark-mode) tbody tr:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        body:not(.dark-mode) tbody td {
            color: #374151;
            padding: 16px 24px;
        }
        
        /* Light mode form labels enhanced */
        body:not(.dark-mode) label {
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.025em;
        }
        
        /* Light mode headings enhanced */
        body:not(.dark-mode) h1,
        body:not(.dark-mode) h2,
        body:not(.dark-mode) h3,
        body:not(.dark-mode) h4 {
            color: #1f2937;
            font-weight: 700;
        }
        
        /* Light mode modal */
        body:not(.dark-mode) #editUserModal .bg-white {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Light mode modal form elements */
        body:not(.dark-mode) #editUserModal label {
            color: #374151;
            font-weight: 600;
        }
        body:not(.dark-mode) #editUserModal input,
        body:not(.dark-mode) #editUserModal select {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        body:not(.dark-mode) #editUserModal input:focus,
        body:not(.dark-mode) #editUserModal select:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Light mode tabs */
        body:not(.dark-mode) .tab-button {
            color: #6b7280;
        }
        body:not(.dark-mode) .tab-button:hover {
            color: #2563eb;
            border-color: #2563eb;
        }
        body:not(.dark-mode) .tab-button.active {
            color: #2563eb;
            border-color: #2563eb;
        }
        
        /* Light mode page tags */
        body:not(.dark-mode) .bg-gray-200 {
            background-color: #e5e7eb;
            color: #374151;
        }
        
        /* Light mode role badges enhanced colors */
        body:not(.dark-mode) .bg-red-100 { background-color: #fee2e2; color: #991b1b; }
        body:not(.dark-mode) .bg-purple-100 { background-color: #f3e8ff; color: #7c3aed; }
        body:not(.dark-mode) .bg-blue-100 { background-color: #dbeafe; color: #1d4ed8; }
        body:not(.dark-mode) .bg-green-100 { background-color: #dcfce7; color: #166534; }
        body:not(.dark-mode) .bg-yellow-100 { background-color: #fef3c7; color: #d97706; }
        body:not(.dark-mode) .bg-indigo-100 { background-color: #e0e7ff; color: #4338ca; }
        body:not(.dark-mode) .bg-pink-100 { background-color: #fce7f3; color: #be185d; }
        body:not(.dark-mode) .bg-gray-100 { background-color: #f3f4f6; color: #374151; }
        body:not(.dark-mode) .bg-orange-100 { background-color: #fed7aa; color: #c2410c; }
        
        /* Light mode warning boxes */
        body:not(.dark-mode) .bg-yellow-50 {
            background-color: #fffbeb;
            border-color: #f59e0b;
        }
        body:not(.dark-mode) .text-yellow-700 {
            color: #a16207;
        }
        body:not(.dark-mode) .bg-yellow-100 {
            background-color: #fef3c7;
            color: #92400e;
        }
        body:not(.dark-mode) .text-yellow-800 {
            color: #92400e;
        }
    </style>
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">
    <!-- Include Sidebar -->

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="container mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-3 py-1 rounded-full text-sm font-medium">
                        Logged in as: <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['Role']; ?>)
                    </span>
                    <!-- <button onclick="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button> -->
                </div>
            </div>

            <!-- Flash Message -->
            <?php if (!empty($message)): ?>
                <div class="flash-message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px" id="tabs">
                    <li class="mr-2">
                        <button onclick="switchTab('users')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 tab-button active" data-tab="users">
                            <i class="fas fa-users mr-2"></i> Manage Users
                        </button>
                    </li>
                    <li class="mr-2">
                        <button onclick="switchTab('schema')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 tab-button" data-tab="schema">
                            <i class="fas fa-sitemap mr-2"></i> Website Schema
                        </button>
                    </li>
                    <li class="mr-2">
                        <button onclick="switchTab('new-user')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 tab-button" data-tab="new-user">
                            <i class="fas fa-user-plus mr-2"></i> Create New User
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <!-- Users Tab -->
            <div id="users-tab" class="tab-content active">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">User Accounts</h2>
                        <div class="flex space-x-4">
                            <div class="relative">
                                <input type="text" id="userSearch" placeholder="Search users..." class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <select id="roleFilter" class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">All Roles</option>
                                <?php foreach (array_keys($roleAccess) as $role): ?>
                                    <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600" id="usersTableBody">
                                <?php foreach ($users as $user): ?>
                                <?php 
                                    $isProtectedUser = ($user['Role'] === 'Developer');
                                    $canEditProtected = ($_SESSION['Role'] === 'Developer');
                                    $disableActions = $isProtectedUser && !$canEditProtected;
                                ?>
                                <tr class="user-row hover:bg-gray-50 dark:hover:bg-gray-700" 
                                    data-role="<?php echo htmlspecialchars($user['Role']); ?>" 
                                    data-password="<?php echo htmlspecialchars($user['password']); ?>"
                                    data-protected="<?php echo $isProtectedUser ? '1' : '0'; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            $roleColors = [
                                                'Admin' => 'bg-red-100 text-red-800',
                                                'Developer' => 'bg-purple-100 text-purple-800',
                                                'DRH' => 'bg-blue-100 text-blue-800',
                                                'Sup Achat' => 'bg-green-100 text-green-800',
                                                'Sup Vente' => 'bg-yellow-100 text-yellow-800',
                                                'Comptable' => 'bg-indigo-100 text-indigo-800',
                                                'gestion stock' => 'bg-pink-100 text-pink-800',
                                                'stock' => 'bg-gray-100 text-gray-800',
                                                'saisie' => 'bg-orange-100 text-orange-800',
                                            ];
                                            echo $roleColors[$user['Role']] ?? 'bg-gray-100 text-gray-800';
                                            ?>">
                                            <?php echo htmlspecialchars($user['Role']); ?>
                                            <?php if ($isProtectedUser): ?>
                                                <i class="fas fa-shield-alt ml-1" title="Protected account"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 <?php echo $disableActions ? 'opacity-50 cursor-not-allowed' : ''; ?>" 
                                                    onclick="<?php echo $disableActions ? 'showProtectedUserMessage()' : 'editUser(' . $user['id'] . ')'; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="inline delete-form" onsubmit="return <?php echo $disableActions ? 'showProtectedUserMessage()' : 'confirm(\'Are you sure you want to delete this user?\')'; ?>;">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="<?php echo $disableActions ? 'button' : 'submit'; ?>" 
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 <?php echo $disableActions ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Total users: <span id="userCount"><?php echo count($users); ?></span>
                    </div>
                </div>
            </div>

            <!-- Website Schema Tab -->
            <div id="schema-tab" class="tab-content">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-6">Website Schema & Role Permissions</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">Role-Based Access Control</h3>
                        <div class="mb-4">
                            <button id="toggleEditMode" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300">
                                <i class="fas fa-edit mr-2"></i> Edit Role Permissions
                            </button>
                            <div id="savePermissionsContainer" class="hidden mt-4">
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                Changes will update permissions in sidebar.php. Make sure you have a backup before proceeding.
                                                <br><strong>Note:</strong> Only users with Developer or Admin role can modify permissions.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <button id="savePermissions" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-300 mr-2">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                                <button id="cancelPermissions" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-300">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </button>
                            </div>
                        </div>
                        <div class="schema-grid">
                            <?php foreach ($roleAccess as $role => $pages): ?>
                            <div class="schema-card" data-role="<?php echo $role; ?>">
                                <h4 class="font-bold text-lg mb-2">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2
                                        <?php 
                                        $roleColors = [
                                            'Admin' => 'bg-red-500',
                                            'Developer' => 'bg-purple-500',
                                            'DRH' => 'bg-blue-500',
                                            'Sup Achat' => 'bg-green-500',
                                            'Sup Vente' => 'bg-yellow-500',
                                            'Comptable' => 'bg-indigo-500',
                                            'gestion stock' => 'bg-pink-500',
                                            'stock' => 'bg-gray-500',
                                            'saisie' => 'bg-orange-500',
                                        ];
                                        echo $roleColors[$role] ?? 'bg-gray-500';
                                        ?>"></span>
                                    <?php echo $role; ?>
                                </h4>
                                <div class="text-sm mt-2 role-pages">
                                    <?php if ($pages === 'all'): ?>
                                        <p class="text-green-600 dark:text-green-400 font-medium">Has access to all pages</p>
                                        <div class="edit-pages hidden">
                                            <label class="inline-flex items-center mt-2">
                                                <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 all-access-checkbox" checked data-role="<?php echo $role; ?>">
                                                <span class="ml-2">Full Access to All Pages</span>
                                            </label>
                                            <div class="page-selection mt-3 hidden">
                                                <p class="mb-2 font-medium">Select specific pages:</p>
                                                <div class="max-h-48 overflow-y-auto">
                                                    <?php foreach ($allPages as $page): ?>
                                                    <label class="flex items-center block mb-3 py-1">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 page-checkbox" data-page="<?php echo $page; ?>" data-role="<?php echo $role; ?>">
                                                        <span class="ml-2 text-sm"><?php echo $page; ?></span>
                                                    </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="mb-1 font-medium">Access to <?php echo count($pages); ?> pages:</p>
                                        <div class="page-list">
                                            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400">
                                                <?php foreach ($pages as $page): ?>
                                                    <li><?php echo $page; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="edit-pages hidden">
                                            <label class="inline-flex items-center mt-2">
                                                <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 all-access-checkbox" data-role="<?php echo $role; ?>">
                                                <span class="ml-2">Full Access to All Pages</span>
                                            </label>
                                            <div class="page-selection mt-3">
                                                <p class="mb-2 font-medium">Select specific pages:</p>
                                                <div class="max-h-48 overflow-y-auto">
                                                    <?php foreach ($allPages as $page): ?>
                                                    <label class="flex items-center block mb-3 py-1">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 page-checkbox" 
                                                            data-page="<?php echo $page; ?>" 
                                                            data-role="<?php echo $role; ?>"
                                                            <?php echo (is_array($pages) && in_array($page, $pages)) ? 'checked' : ''; ?>>
                                                        <span class="ml-2 text-sm"><?php echo $page; ?></span>
                                                    </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <h3 class="text-lg font-medium mb-4">Pages Available in System</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            // Define all available pages in the system
                            $completePagesList = [
                                'Annual_Recap_A', 'Annual_Recap_V', 'CONFIRMED_ORDERS', 'DETTE_F', 'ETAT_F', 
                                'ETAT_F_CUMULE', 'Etatstock', 'Journal_Vente', 'Mouvement_Stock', 'Product', 
                                'Quota', 'Recap_Achat', 'Recap_Vente', 'Recap_Vente_Facturation', 'Rotation', 
                                'bank', 'charge', 'feedback', 'inventory/inv', 'inventory/inv_admin', 'inventory/inv_saisie', 
                                'mony', 'portf', 'print', 'recap_achat_facturation', 'recouverement', 'retour',
                                'rot_men_achat', 'rot_men_vente', 'sess', 'simuler', 'sudo', 'AFFECTATION'
                            ];
                            sort($completePagesList);
                            
                            // Add the new pages to the existing allPages array if not already there
                            foreach ($completePagesList as $page) {
                                if (!in_array($page, $allPages)) {
                                    $allPages[] = $page;
                                }
                            }
                            sort($allPages);
                            
                            foreach ($allPages as $page): ?>
                                <span class="bg-gray-200 dark:bg-gray-700 px-3 py-1 rounded-full text-sm">
                                    <?php echo $page; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    

                </div>
            </div>

            <!-- Create New User Tab -->
            <div id="new-user-tab" class="tab-content">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-6">Create New User</h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="create_user">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                <input type="text" id="username" name="username" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                <div class="password-container relative">
                                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <i class="fas fa-eye toggle-password"></i>
                                </div>
                            </div>
                            
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                                <select id="role" name="role" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" onchange="toggleCustomRoleField()">
                                    <option value="">Select a role</option>
                                    <?php foreach (array_keys($roleAccess) as $role): ?>
                                        <?php if ($role !== 'Developer'): ?>
                                            <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <option value="custom">Create New Role</option>
                                </select>
                            </div>
                            
                            <div id="custom_role_container" style="display: none;">
                                <label for="custom_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Role Name</label>
                                <input type="text" id="custom_role" name="custom_role" placeholder="Enter custom role name (Developer role not allowed)" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="text-xs text-red-500 mt-1">Note: Creating users with the "Developer" role is not allowed.</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-300">
                                <i class="fas fa-user-plus mr-2"></i> Create User
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium mb-4">Role Permissions Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($roleAccess as $role => $pages): ?>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="font-bold"><?php echo $role; ?></h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        <?php if ($pages === 'all'): ?>
                                            Has access to all pages
                                        <?php else: ?>
                                            Has access to <?php echo count($pages); ?> pages
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Edit User</h2>
            
            <div id="protected-user-warning" class="p-3 mb-4 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-200 hidden">
                Warning: You are editing a Developer account. These accounts are protected.
            </div>
            
            <form id="editUserForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <div class="password-container relative">
                        <input type="password" id="edit_password" name="password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div>
                    <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select id="edit_role" name="role" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" onchange="toggleEditCustomRoleField()">
                        <?php foreach (array_keys($roleAccess) as $role): ?>
                            <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                        <?php endforeach; ?>
                        <option value="custom">Create New Role</option>
                    </select>
                </div>
                
                <div id="edit_custom_role_container" style="display: none;">
                    <label for="edit_custom_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Role Name</label>
                    <input type="text" id="edit_custom_role" name="custom_role" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Include Chart.js for visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Tab switching functionality
        function switchTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabId + '-tab').classList.add('active');
            
            // Update active state for tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-600', 'text-blue-600');
                button.classList.add('border-transparent');
            });
            
            document.querySelector(`.tab-button[data-tab="${tabId}"]`).classList.add('active', 'border-blue-600', 'text-blue-600');
        }

        // Show protected user message
        function showProtectedUserMessage() {
            alert('This account is protected. Only users with Developer role can modify Developer accounts.');
            return false;
        }

        // Toggle password visibility
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('toggle-password')) {
                const passwordField = e.target.previousElementSibling;
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    e.target.classList.remove('fa-eye');
                    e.target.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    e.target.classList.remove('fa-eye-slash');
                    e.target.classList.add('fa-eye');
                }
            }
        });

        // Toggle custom role field
        function toggleCustomRoleField() {
            const roleSelect = document.getElementById('role');
            const customRoleContainer = document.getElementById('custom_role_container');
            
            if (roleSelect.value === 'custom') {
                customRoleContainer.style.display = 'block';
                document.getElementById('custom_role').setAttribute('required', 'required');
            } else {
                customRoleContainer.style.display = 'none';
                document.getElementById('custom_role').removeAttribute('required');
            }
        }
        
        // Prevent creating users with Developer role
        document.addEventListener('DOMContentLoaded', function() {
            const customRoleField = document.getElementById('custom_role');
            if (customRoleField) {
                customRoleField.addEventListener('input', function() {
                    // Check if the custom role is "Developer" (case insensitive)
                    if (this.value.toLowerCase() === 'developer') {
                        alert('Creating users with the Developer role is not allowed.');
                        this.value = ''; // Clear the field
                    }
                });
            }
            
            // Prevent form submission if custom role is Developer
            const newUserForm = document.querySelector('form[action="create_user"]');
            if (newUserForm) {
                newUserForm.addEventListener('submit', function(e) {
                    const roleSelect = document.getElementById('role');
                    const customRoleField = document.getElementById('custom_role');
                    
                    if (roleSelect.value === 'custom' && 
                        customRoleField.value.toLowerCase() === 'developer') {
                        e.preventDefault();
                        alert('Creating users with the Developer role is not allowed.');
                        customRoleField.value = '';
                        return false;
                    }
                });
            }
        });
        
        // Toggle custom role field in edit modal
        function toggleEditCustomRoleField() {
            const roleSelect = document.getElementById('edit_role');
            const customRoleContainer = document.getElementById('edit_custom_role_container');
            
            if (roleSelect.value === 'custom') {
                customRoleContainer.style.display = 'block';
                document.getElementById('edit_custom_role').setAttribute('required', 'required');
            } else {
                customRoleContainer.style.display = 'none';
                document.getElementById('edit_custom_role').removeAttribute('required');
            }
        }

        // User search functionality
        document.getElementById('userSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            filterUsers(searchTerm, roleFilter);
        });

        // Role filter functionality
        document.getElementById('roleFilter').addEventListener('change', function(e) {
            const roleFilter = e.target.value;
            const searchTerm = document.getElementById('userSearch').value.toLowerCase();
            filterUsers(searchTerm, roleFilter);
        });

        function filterUsers(searchTerm, roleFilter) {
            const rows = document.querySelectorAll('#usersTableBody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const role = row.getAttribute('data-role');
                
                const roleMatch = !roleFilter || role === roleFilter;
                const searchMatch = !searchTerm || username.includes(searchTerm);
                
                if (roleMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                    
                    // Highlight the matching text if there's a search term
                    if (searchTerm) {
                        highlightText(row.querySelector('td:nth-child(2)'), username, searchTerm);
                    } else {
                        // Remove highlights if search is cleared
                        row.querySelector('td:nth-child(2)').innerHTML = username;
                    }
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('userCount').textContent = visibleCount;
        }

        function highlightText(element, text, searchTerm) {
            if (!text || !searchTerm) return;
            
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            element.innerHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
        }

        // Edit user functionality
        function editUser(userId) {
            // Find the user data from the table
            const rows = document.querySelectorAll('#usersTableBody tr');
            let userRow = null;
            
            // Find the specific row for this user
            for (let i = 0; i < rows.length; i++) {
                const idCell = rows[i].querySelector('td:first-child');
                if (idCell && idCell.textContent.trim() == userId) {
                    userRow = rows[i];
                    break;
                }
            }
            
            if (!userRow) {
                console.error('User row not found for ID:', userId);
                return;
            }
            
            // Fill the form with the user data
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = userRow.querySelector('td:nth-child(2)').textContent.trim();
            document.getElementById('edit_password').value = userRow.getAttribute('data-password');
            
            const userRole = userRow.getAttribute('data-role');
            const roleSelect = document.getElementById('edit_role');
            const isProtected = userRow.getAttribute('data-protected') === '1';
            
            // Show warning for Developer accounts
            const warningDiv = document.getElementById('protected-user-warning');
            if (warningDiv) {
                if (isProtected) {
                    warningDiv.classList.remove('hidden');
                } else {
                    warningDiv.classList.add('hidden');
                }
            }
            
            // Check if the role exists in the select options
            let roleExists = false;
            for (let i = 0; i < roleSelect.options.length; i++) {
                if (roleSelect.options[i].value === userRole) {
                    roleSelect.selectedIndex = i;
                    roleExists = true;
                    break;
                }
            }
            
            // If role doesn't exist in the options, select "custom" and fill in the custom role field
            if (!roleExists && userRole) {
                for (let i = 0; i < roleSelect.options.length; i++) {
                    if (roleSelect.options[i].value === 'custom') {
                        roleSelect.selectedIndex = i;
                        document.getElementById('edit_custom_role').value = userRole;
                        break;
                    }
                }
                toggleEditCustomRoleField();
            }
            
            // Show the modal
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        // Logout function
        function logout() {
            window.location.href = 'logout.php';
        }

        // Role Permissions Editing Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleEditModeBtn = document.getElementById('toggleEditMode');
            const savePermissionsBtn = document.getElementById('savePermissions');
            const cancelPermissionsBtn = document.getElementById('cancelPermissions');
            const savePermissionsContainer = document.getElementById('savePermissionsContainer');
            
            if (toggleEditModeBtn) {
                toggleEditModeBtn.addEventListener('click', function() {
                    // Show the edit UI elements
                    document.querySelectorAll('.edit-pages').forEach(el => {
                        el.classList.remove('hidden');
                    });
                    
                    // Hide the regular view
                    document.querySelectorAll('.page-list').forEach(el => {
                        el.classList.add('hidden');
                    });
                    
                    // Show save/cancel buttons
                    savePermissionsContainer.classList.remove('hidden');
                    
                    // Hide edit button
                    toggleEditModeBtn.classList.add('hidden');
                });
            }
            
            if (cancelPermissionsBtn) {
                cancelPermissionsBtn.addEventListener('click', function() {
                    // Hide the edit UI elements
                    document.querySelectorAll('.edit-pages').forEach(el => {
                        el.classList.add('hidden');
                    });
                    
                    // Show the regular view
                    document.querySelectorAll('.page-list').forEach(el => {
                        el.classList.remove('hidden');
                    });
                    
                    // Hide save/cancel buttons
                    savePermissionsContainer.classList.add('hidden');
                    
                    // Show edit button
                    toggleEditModeBtn.classList.remove('hidden');
                });
            }
            
            // Handle "All Access" checkbox changes
            document.querySelectorAll('.all-access-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const roleCard = this.closest('.schema-card');
                    const role = roleCard.getAttribute('data-role');
                    const pageSelection = roleCard.querySelector('.page-selection');
                    
                    if (this.checked) {
                        // If "All Access" is checked, hide the page selection
                        pageSelection.classList.add('hidden');
                    } else {
                        // If "All Access" is unchecked, show the page selection
                        pageSelection.classList.remove('hidden');
                    }
                });
            });
            
            // Handle save permissions
            if (savePermissionsBtn) {
                savePermissionsBtn.addEventListener('click', function() {
                    // Show loading state
                    savePermissionsBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                    savePermissionsBtn.disabled = true;
                    
                    // Collect all permissions
                    const permissions = {};
                    
                    document.querySelectorAll('.schema-card').forEach(card => {
                        const role = card.getAttribute('data-role');
                        const allAccessCheckbox = card.querySelector('.all-access-checkbox');
                        
                        if (allAccessCheckbox.checked) {
                            permissions[role] = 'all';
                        } else {
                            const selectedPages = [];
                            card.querySelectorAll('.page-checkbox:checked').forEach(pageCheckbox => {
                                selectedPages.push(pageCheckbox.getAttribute('data-page'));
                            });
                            permissions[role] = selectedPages;
                        }
                    });
                    
                    // Send to server
                    fetch('update_permissions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'permissions=' + encodeURIComponent(JSON.stringify(permissions))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            alert('Permissions updated successfully! ' + data.message);
                            
                            // Reset UI
                            document.querySelectorAll('.edit-pages').forEach(el => {
                                el.classList.add('hidden');
                            });
                            
                            document.querySelectorAll('.page-list').forEach(el => {
                                el.classList.remove('hidden');
                            });
                            
                            savePermissionsContainer.classList.add('hidden');
                            toggleEditModeBtn.classList.remove('hidden');
                            
                            // Reload page to reflect changes
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving permissions:', error);
                        alert('Failed to save permissions. See console for details.');
                    })
                    .finally(() => {
                        // Reset button state
                        savePermissionsBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Changes';
                        savePermissionsBtn.disabled = false;
                    });
                });
            }
        });
    </script>
</body>
</html>
