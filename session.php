<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify developer access (case-sensitive match)
if (!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Developer'], true)) {
    header('Location: 403');
    exit();
}

// Database connection with verification
$configPath = __DIR__ . '/db_config.php';
if (!file_exists($configPath)) {
    die("Database configuration file not found at: $configPath");
}

require_once $configPath;

// Verify required configuration variables exist
if (!isset($host, $user, $pass, $dbname)) {
    die("Missing database configuration variables");
}

// Establish database connection with error handling
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset to match your database
    if (!$conn->set_charset("utf8")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
} catch (Exception $e) {
    die("<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Database Error</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .error { color: #d32f2f; background: #ffebee; padding: 15px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <h1>Database Error</h1>
            <div class='error'>" . htmlspecialchars($e->getMessage()) . "</div>
            <p>Please contact the administrator.</p>
        </body>
        </html>");
}

// Filter logic with validation
$filter_username = isset($_GET['username']) ? trim($_GET['username']) : '';
$filter_date = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d'); // Default to today's date
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Remove pagination - load all records with scroll

// Build WHERE clause securely
$where = [];
if ($filter_username !== '') {
    $where[] = "username LIKE '" . $conn->real_escape_string($filter_username) . "%'";
}

if ($filter_date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
    $where[] = "(DATE(login_time) = '" . $conn->real_escape_string($filter_date) . "' OR DATE(logout_time) = '" . $conn->real_escape_string($filter_date) . "')";
}

if ($filter_status === 'active') {
    $where[] = "logout_time IS NULL";
} elseif ($filter_status === 'ended') {
    $where[] = "logout_time IS NOT NULL";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count for information display
$count_sql = "SELECT COUNT(*) as total FROM user_sessions $where_sql";
$count_result = $conn->query($count_sql);
$total_records = $count_result ? $count_result->fetch_assoc()['total'] : 0;

// Main query without pagination - load all records
$sql = "SELECT * FROM user_sessions $where_sql ORDER BY login_time DESC";

// Handle clean table action (Developer only)
if (isset($_POST['clean_table']) && $_SESSION['Role'] === 'Developer') {
    $clean_sql = "DELETE FROM user_sessions";
    
    if ($conn->query($clean_sql)) {
        $success_message = "All session records have been deleted successfully.";
    } else {
        $error_message = "Failed to clean the session table: " . $conn->error;
    }
    
    // Refresh the page to show updated data
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
    exit();
}

// Handle logout all users action (Developer only)
if (isset($_POST['logout_all_users']) && $_SESSION['Role'] === 'Developer') {
    $logout_time = date('Y-m-d H:i:s');
    $logout_all_sql = "UPDATE user_sessions SET logout_time = ? WHERE logout_time IS NULL";
    $logout_all_stmt = $conn->prepare($logout_all_sql);
    $logout_all_stmt->bind_param("s", $logout_time);
    
    if ($logout_all_stmt->execute()) {
        $affected_rows = $logout_all_stmt->affected_rows;
        $success_message = "Successfully logged out {$affected_rows} active user session(s).";
    } else {
        $error_message = "Failed to logout all user sessions: " . $conn->error;
    }
    $logout_all_stmt->close();
    
    // Refresh the page to show updated data
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
    exit();
}

// Handle force logout
if (isset($_POST['force_logout']) && isset($_POST['session_id'])) {
    $session_id = intval($_POST['session_id']);
    $logout_time = date('Y-m-d H:i:s');
    
    $logout_sql = "UPDATE user_sessions SET logout_time = ? WHERE id = ? AND logout_time IS NULL";
    $logout_stmt = $conn->prepare($logout_sql);
    $logout_stmt->bind_param("si", $logout_time, $session_id);
    
    if ($logout_stmt->execute()) {
        $success_message = "User session has been logged out successfully.";
    } else {
        $error_message = "Failed to logout user session.";
    }
    $logout_stmt->close();
    
    // Refresh the page to show updated data
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
    exit();
}

// Function to determine session status
function getSessionStatus($logout_time) {
    return empty($logout_time) ? 'Active' : 'Ended';
}

// Handle XLS download
if (isset($_GET['download']) && $_GET['download'] === 'xls') {
    // Use the same filters but get all records (no pagination for export)
    $export_sql = "SELECT * FROM user_sessions $where_sql ORDER BY login_time DESC";
    $result = $conn->query($export_sql);
    
    if (!$result) {
        die("Error executing query: " . $conn->error);
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="session_logs_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "Username\tIP Address\tUser Agent\tLogin Time\tLogout Time\tStatus\n";
    
    while ($row = $result->fetch_assoc()) {
        // Format data for Excel
        $user_agent_display = $row['user_agent'] ? str_replace(["\n", "\r", "\t"], ' ', $row['user_agent']) : 'N/A';
        $status = getSessionStatus($row['logout_time']);
        
        echo implode("\t", [
            $row['username'],
            $row['ip_address'],
            $user_agent_display,
            $row['login_time'],
            $row['logout_time'] ?: 'Active',
            $status
        ]) . "\n";
    }
    
    $conn->close();
    exit();
}

// Get data for HTML display
$result = $conn->query($sql);
$query_error = $result ? null : $conn->error;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Logs</title>
            <script src="theme.js"></script>

    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f9fafb;
            margin: 0;
            color: #1f2937;
            scroll-behavior: smooth;
            overflow-x: auto;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .container {
            max-width: 1400px;
            margin: 1rem auto;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            min-height: calc(100vh - 2rem);
            position: relative;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        h1 {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            position: sticky;
            top: 0;
            background: white;
            z-index: 20;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        
        /* Dark mode styles */
        body.dark-mode {
            background: #111827;
            color: #F3F4F6;
        }
        body.dark-mode .container {
            background: #1F2937;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        body.dark-mode h1 {
            background: #1F2937;
            border-bottom: 1px solid #4B5563;
            color: #F3F4F6;
        }
        .filter-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(248, 249, 250, 0.95);
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
            position: sticky;
            top: 80px;
            z-index: 15;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            backdrop-filter: blur(8px);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        input, button, .btn {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        input {
            width: 100%;
            border: 1px solid #e5e7eb;
        }
        
        /* Dark mode filter form */
        body.dark-mode .filter-form {
            background: rgba(55, 65, 81, 0.95);
            border: 1px solid #4B5563;
        }
        body.dark-mode label {
            color: #F3F4F6;
        }
        body.dark-mode input {
            background: #374151;
            border: 1px solid #4B5563;
            color: #F3F4F6;
        }
        body.dark-mode input::placeholder {
            color: #9CA3AF;
        }
        button, .btn {
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-danger:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        .btn-warning:hover {
            background: #d97706;
        }
        .btn-warning:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        thead {
            position: sticky;
            top: 160px;
            z-index: 10;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            background: white;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        th {
            background: #f3f4f6;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: background-color 0.3s ease;
        }
        tbody tr {
            transition: background-color 0.2s ease;
        }
        tbody tr:hover {
            background-color: #f8fafc;
        }
        tr.active-row {
            background: #e0f7ea;
        }
        tr.active-row:hover {
            background: #d4f4dd;
        }
        
        /* Dark mode table styles */
        body.dark-mode th,
        body.dark-mode td {
            border: 1px solid #4B5563;
            background: #1F2937;
        }
        body.dark-mode th {
            background: #374151;
            color: #F3F4F6;
        }
        body.dark-mode tbody tr:hover {
            background-color: #2D3748;
        }
        body.dark-mode tr.active-row {
            background: #064E3B;
        }
        body.dark-mode tr.active-row:hover {
            background: #065F46;
        }
        .status-active {
            color: #059669;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .status-ended {
            color: #ef4444;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .error-message {
            color: #ef4444;
            padding: 1rem;
            background: #fee2e2;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            transition: color 0.3s ease;
        }
        
        /* Dark mode status and messages */
        body.dark-mode .status-active {
            color: #10B981;
        }
        body.dark-mode .status-ended {
            color: #EF4444;
        }
        body.dark-mode .error-message {
            color: #FCA5A5;
            background: #7F1D1D;
        }
        body.dark-mode .no-data {
            color: #9CA3AF;
        }
        .ip-list {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        .user-agent-display {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            max-width: 200px;
            word-break: break-all;
            line-height: 1.2;
        }
        .expandable {
            cursor: pointer;
            color: #2563eb;
            text-decoration: underline;
            transition: color 0.3s ease;
        }
        .expanded-content {
            display: none;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            transition: background-color 0.3s ease;
        }
        .success-message {
            color: #059669;
            padding: 1rem;
            background: #d1fae5;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Dark mode content styles */
        body.dark-mode .expandable {
            color: #60A5FA;
        }
        body.dark-mode .expanded-content {
            background: #374151;
            color: #F3F4F6;
        }
        body.dark-mode .success-message {
            color: #6EE7B7;
            background: #064E3B;
        }
        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
        .logout-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #374151;
            transition: all 0.2s;
        }
        .pagination a:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        .pagination .current {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        .pagination .disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }
        .pagination-info {
            text-align: center;
            margin-top: 1rem;
            color: #6b7280;
            font-size: 0.875rem;
            transition: color 0.3s ease;
        }
        
        /* Dark mode pagination and info */
        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            border: 1px solid #4B5563;
            color: #F3F4F6;
            background: #374151;
        }
        body.dark-mode .pagination a:hover {
            background: #4B5563;
            border-color: #6B7280;
        }
        body.dark-mode .pagination .current {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        body.dark-mode .pagination .disabled {
            color: #6B7280;
        }
        body.dark-mode .pagination-info {
            color: #9CA3AF;
        }
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }
        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        .scroll-to-top:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        .records-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            color: #1e40af;
            font-size: 0.875rem;
            text-align: center;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        
        /* Dark mode final elements */
        body.dark-mode .scroll-to-top {
            background: #374151;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        body.dark-mode .scroll-to-top:hover {
            background: #4B5563;
        }
        body.dark-mode .records-info {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93C5FD;
        }
        body.dark-mode select {
            background: #374151;
            border: 1px solid #4B5563;
            color: #F3F4F6;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Session Logs</h1>
    
    <?php if (isset($success_message)): ?>
    <div class="success-message">
        <?= htmlspecialchars($success_message) ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
    <div class="error-message">
        <?= htmlspecialchars($error_message) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($query_error): ?>
    <div class="error-message">
        Database Error: <?= htmlspecialchars($query_error) ?>
    </div>
    <?php endif; ?>
    
    <form class="filter-form" method="get">
        <div class="filter-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" 
                   value="<?= htmlspecialchars($filter_username) ?>" 
                   placeholder="Filter by username">
        </div>
        <div class="filter-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" 
                   value="<?= htmlspecialchars($filter_date) ?>">
        </div>
        <div class="filter-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="" <?= $filter_status === '' ? 'selected' : '' ?>>All</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="ended" <?= $filter_status === 'ended' ? 'selected' : '' ?>>Ended</option>
            </select>
        </div>
        <div class="filter-group" style="align-self: flex-end;">
            <button type="submit" class="btn-primary">Apply Filter</button>
        </div>
        <div class="filter-group" style="align-self: flex-end; margin-left: auto;">
            <button type="submit" name="download" value="xls" class="btn-success">Download XLS</button>
        </div>
        <?php if (isset($_SESSION['Role']) && $_SESSION['Role'] === 'Developer'): ?>
        <div class="filter-group" style="align-self: flex-end;">
            <button type="button" onclick="confirmLogoutAll()" class="btn-warning">
                ⚠️ Logout All Users
            </button>
        </div>
        <div class="filter-group" style="align-self: flex-end;">
            <button type="button" onclick="confirmCleanTable()" class="btn-danger">
                🗑️ Clean Table
            </button>
        </div>
        <?php endif; ?>
    </form>

    <!-- Hidden forms for actions -->
    <?php if (isset($_SESSION['Role']) && $_SESSION['Role'] === 'Developer'): ?>
    <form id="logoutAllForm" method="post" style="display: none;">
        <input type="hidden" name="logout_all_users" value="1">
    </form>
    <form id="cleanTableForm" method="post" style="display: none;">
        <input type="hidden" name="clean_table" value="1">
    </form>
    <?php endif; ?>

    <!-- Records info display -->
    <?php if ($total_records > 0): ?>
    <div class="records-info">
        📊 Showing <?= $total_records ?> session record(s) 
        <?php if ($filter_date): ?>
            for date: <?= htmlspecialchars($filter_date) ?>
        <?php endif; ?>
        <?php if ($filter_username): ?>
            | Username: <?= htmlspecialchars($filter_username) ?>
        <?php endif; ?>
        <?php if ($filter_status): ?>
            | Status: <?= ucfirst($filter_status) ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                // Format user agent for display
                $user_agent = $row['user_agent'] ?? 'N/A';
                $user_agent_short = htmlspecialchars(substr($user_agent, 0, 50) . (strlen($user_agent) > 50 ? '...' : ''));
                $user_agent_full = htmlspecialchars($user_agent);
                
                $status = getSessionStatus($row['logout_time']);
            ?>
            <tr class="<?= $status === 'Active' ? 'active-row' : '' ?>">
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td class="ip-list"><?= htmlspecialchars($row['ip_address']) ?></td>
                <td class="user-agent-display">
                    <span class="expandable" onclick="toggleExpand(this)"><?= $user_agent_short ?></span>
                    <div class="expanded-content"><?= $user_agent_full ?></div>
                </td>
                <td><?= htmlspecialchars($row['login_time']) ?></td>
                <td><?= $row['logout_time'] ? htmlspecialchars($row['logout_time']) : '<span class="status-active">Active</span>' ?></td>
                <td>
                    <?php if ($status === 'Active'): ?>
                        <span class="status-active">Active</span>
                    <?php else: ?>
                        <span class="status-ended">Ended</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($status === 'Active'): ?>
                        <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to logout this user session?')">
                            <input type="hidden" name="session_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="force_logout" class="logout-btn">
                                Force Logout
                            </button>
                        </form>
                    <?php else: ?>
                        <span style="color: #9ca3af; font-size: 0.75rem;">Session Ended</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <?php elseif ($result): ?>
    <div class="no-data">
        <p>No session data found matching your criteria.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Scroll to top button -->
<button class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()">
    ↑
</button>

<?php
// Close connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<script>
function toggleExpand(element) {
    const expandedContent = element.nextElementSibling;
    if (expandedContent.style.display === 'none' || expandedContent.style.display === '') {
        expandedContent.style.display = 'block';
    } else {
        expandedContent.style.display = 'none';
    }
}

// Clean table confirmation
function confirmCleanTable() {
    if (confirm('⚠️ WARNING: This will permanently delete ALL session records from the database.\n\nThis action cannot be undone. Are you absolutely sure you want to proceed?')) {
        if (confirm('Final confirmation: Click OK to DELETE ALL session data, or Cancel to abort.')) {
            document.getElementById('cleanTableForm').submit();
        }
    }
}

// Logout all users confirmation
function confirmLogoutAll() {
    if (confirm('⚠️ WARNING: This will logout ALL active user sessions immediately.\n\nAll users will be forced to login again. Are you sure you want to proceed?')) {
        if (confirm('Final confirmation: Click OK to LOGOUT ALL ACTIVE USERS, or Cancel to abort.')) {
            document.getElementById('logoutAllForm').submit();
        }
    }
}

// Scroll to top functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide scroll to top button
window.addEventListener('scroll', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.add('visible');
    } else {
        scrollToTopBtn.classList.remove('visible');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add scroll indicators for large datasets
    const table = document.querySelector('table');
    if (table && table.rows.length > 20) {
        console.log('Large dataset detected - smooth scrolling enabled');
    }
});
</script>

</body>
</html>