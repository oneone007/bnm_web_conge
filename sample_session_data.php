<?php
// Sample script to demonstrate how to insert session data in the new format
require_once 'db_config.php';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example data insertion
$sample_data = [
    [
        'username' => 'admin',
        'ip_address' => '192.168.1.10,127.0.0.1,10.0.0.5',
        'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0,Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36,Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X)',
        'login_time' => '192.168.1.10: 2025-08-06 08:30:15, 127.0.0.1: 2025-08-06 09:45:20, 10.0.0.5: 2025-08-06 11:15:30',
        'logout_time' => '192.168.1.10: 2025-08-06 17:30:15, 127.0.0.1: 2025-08-06 18:00:00'
    ],
    [
        'username' => 'user1',
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'login_time' => '192.168.1.100: 2025-08-06 14:42:10',
        'logout_time' => null // Still active
    ]
];

foreach ($sample_data as $data) {
    $stmt = $conn->prepare("INSERT INTO user_sessions (username, ip_address, user_agent, login_time, logout_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", 
        $data['username'], 
        $data['ip_address'], 
        $data['user_agent'], 
        $data['login_time'], 
        $data['logout_time']
    );
    
    if ($stmt->execute()) {
        echo "✅ Inserted session data for user: " . $data['username'] . "\n";
    } else {
        echo "❌ Error inserting data for user: " . $data['username'] . " - " . $stmt->error . "\n";
    }
    $stmt->close();
}

echo "\n📊 Sample data format explanation:\n";
echo "================================\n";
echo "IP Addresses: Comma-separated list (e.g., '192.168.1.10,127.0.0.1')\n";
echo "User Agents: Comma-separated list (e.g., 'Agent1,Agent2')\n";
echo "Login Times: IP:Time format, comma-separated (e.g., '192.168.1.10: 2025-08-06 08:30:15, 127.0.0.1: 2025-08-06 09:45:20')\n";
echo "Logout Times: Same format as login times, but can be NULL for active sessions\n\n";

echo "🔍 View the results by accessing: session.php\n";

$conn->close();
?>
