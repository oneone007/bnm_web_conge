<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Logs Viewer</title>
        <script src="theme.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            transition: color 0.3s ease;
        }
        
        /* Dark mode styles */
        body.dark-mode {
            background-color: #111827;
            color: #F3F4F6;
        }
        body.dark-mode .container {
            background: #1F2937;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        body.dark-mode h1 {
            color: #F3F4F6;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        /* Dark mode stat cards */
        body.dark-mode .stat-card {
            background: #374151;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Dark mode table styles */
        body.dark-mode th,
        body.dark-mode td {
            border-bottom: 1px solid #4B5563;
        }
        body.dark-mode th {
            background-color: #374151;
            color: #F3F4F6;
        }
        body.dark-mode tr:hover {
            background-color: #2D3748;
        }
        .session-id {
            font-family: monospace;
            background: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }
        .ip-address {
            font-family: monospace;
            color: #dc3545;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .timestamp {
            font-family: monospace;
            color: #28a745;
            transition: color 0.3s ease;
        }
        .user-agent {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .refresh-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        .refresh-btn:hover {
            background: #218838;
        }
        .error {
            color: #dc3545;
            text-align: center;
            padding: 20px;
            transition: color 0.3s ease;
        }
        
        /* Dark mode specific styles */
        body.dark-mode .session-id {
            background: #4B5563;
            color: #F3F4F6;
        }
        body.dark-mode .ip-address {
            color: #EF4444;
        }
        body.dark-mode .timestamp {
            color: #10B981;
        }
        body.dark-mode .refresh-btn {
            background: #10B981;
        }
        body.dark-mode .refresh-btn:hover {
            background: #059669;
        }
        body.dark-mode .error {
            color: #EF4444;
        }
        
        /* Theme toggle button */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .theme-toggle:hover {
            background: #0056b3;
        }
        body.dark-mode .theme-toggle {
            background: #374151;
        }
        body.dark-mode .theme-toggle:hover {
            background: #4B5563;
        }
    </style>
   
</head>
<body>
    
    <div class="container">
        <h1>🚨 403 Access Attempts Log Viewer</h1>
        
        <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Data</button>
        
        <?php
        $json_file = '403.json';
        
        if (!file_exists($json_file)) {
            echo '<div class="error">No 403.json file found. No unauthorized access attempts logged yet.</div>';
            exit;
        }
        
        $json_content = file_get_contents($json_file);
        $data = json_decode($json_content, true);
        
        if (!$data) {
            echo '<div class="error">Invalid JSON format in 403.json file.</div>';
            exit;
        }
        
        $total_attempts = count($data);
        $unique_ips = count(array_unique(array_column($data, 'ip_address')));
        $unique_sessions = count(array_unique(array_column($data, 'session_id')));
        $last_attempt = end($data)['timestamp'] ?? 'Never';
        
        echo '<div class="stats">';
        echo '<div class="stat-card"><h3>' . $total_attempts . '</h3><p>Total Attempts</p></div>';
        echo '<div class="stat-card"><h3>' . $unique_ips . '</h3><p>Unique IP Addresses</p></div>';
        echo '<div class="stat-card"><h3>' . $unique_sessions . '</h3><p>Unique Sessions</p></div>';
        echo '<div class="stat-card"><h3>' . $last_attempt . '</h3><p>Last Attempt</p></div>';
        echo '</div>';
        
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Timestamp</th>';
        echo '<th>IP Address</th>';
        echo '<th>Session ID</th>';
        echo '<th>User Agent</th>';
        echo '<th>Referer</th>';
        echo '<th>Request URI</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        // Sort by timestamp descending (newest first)
        usort($data, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        foreach ($data as $entry) {
            echo '<tr>';
            echo '<td class="timestamp">' . htmlspecialchars($entry['timestamp'] ?? 'N/A') . '</td>';
            echo '<td class="ip-address">' . htmlspecialchars($entry['ip_address'] ?? 'N/A') . '</td>';
            echo '<td><span class="session-id">' . htmlspecialchars(substr($entry['session_id'] ?? 'N/A', 0, 12)) . '...</span></td>';
            echo '<td class="user-agent" title="' . htmlspecialchars($entry['user_agent'] ?? 'N/A') . '">' . htmlspecialchars(substr($entry['user_agent'] ?? 'N/A', 0, 50)) . '...</td>';
            echo '<td>' . htmlspecialchars($entry['referer'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($entry['request_uri'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        ?>
    </div>
</body>
</html>
