<?php
// Demonstration script for the new session tracking system
echo "🔧 New Session Tracking System - One Row Per IP\n";
echo "==============================================\n\n";

// Database connection
$host = 'localhost';
$dbname = 'bnm';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli('localhost', $user, $pass, $dbname, 3306, '/opt/lampp/var/mysql/mysql.sock');
} catch (Exception $e) {
    $conn = new mysqli($host, $user, $pass, $dbname);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "📊 Current session tracking approach:\n";
echo "====================================\n";
echo "✅ Each IP gets its own row\n";
echo "✅ User Agent tracked per session\n";
echo "✅ Multiple IPs = Multiple rows for same user\n";
echo "✅ Same IP login again = Update existing row\n\n";

echo "📋 Example scenarios:\n";
echo "====================\n";
echo "1. Admin logs in from 192.168.1.10 (Chrome)\n";
echo "   → Creates Row 1: admin | 192.168.1.10 | Chrome | 08:30:15 | NULL\n\n";

echo "2. Admin logs in from 127.0.0.1 (Firefox) while still active on first IP\n";
echo "   → Creates Row 2: admin | 127.0.0.1 | Firefox | 09:45:20 | NULL\n";
echo "   → Row 1 stays: admin | 192.168.1.10 | Chrome | 08:30:15 | NULL\n\n";

echo "3. Admin logs out from 192.168.1.10\n";
echo "   → Updates Row 1: admin | 192.168.1.10 | Chrome | 08:30:15 | 17:30:15\n";
echo "   → Row 2 stays: admin | 127.0.0.1 | Firefox | 09:45:20 | NULL\n\n";

echo "4. Admin logs in again from 192.168.1.10 (Chrome)\n";
echo "   → Updates Row 1: admin | 192.168.1.10 | Chrome | 19:15:30 | NULL\n";
echo "   → Row 2 stays: admin | 127.0.0.1 | Firefox | 09:45:20 | NULL\n\n";

echo "📋 Current session data:\n";
echo "========================\n";

$sql = "SELECT username, ip_address, 
               SUBSTRING(user_agent, 1, 30) as short_agent,
               login_time, logout_time,
               CASE WHEN logout_time IS NULL THEN 'Active' ELSE 'Ended' END as status
        FROM user_sessions 
        ORDER BY username, login_time DESC 
        LIMIT 10";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    printf("%-10s %-15s %-30s %-19s %-19s %-8s\n", 
           "Username", "IP", "User Agent", "Login", "Logout", "Status");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-10s %-15s %-30s %-19s %-19s %-8s\n",
               $row['username'],
               $row['ip_address'],
               $row['short_agent'] . '...',
               $row['login_time'],
               $row['logout_time'] ?: 'NULL',
               $row['status']
        );
    }
} else {
    echo "No session data found.\n";
}

echo "\n🎯 Key Benefits:\n";
echo "================\n";
echo "✅ Clear separation of sessions per device/IP\n";
echo "✅ No data mixing between different devices\n";
echo "✅ Easy to track which device is active\n";
echo "✅ User agent helps identify browser/app used\n";
echo "✅ Logout only affects the specific device\n\n";

echo "🔗 View results at: session.php\n";

$conn->close();
?>
