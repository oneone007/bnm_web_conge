<?php
session_start();
$_SESSION['user_id'] = 'test_user';
$_SESSION['Role'] = 'Admin';

require_once __DIR__ . '/sidebar/navigation_helper.php';

echo "Testing navigation loading...\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Navigation file path: " . __DIR__ . '/sidebar/navigation.json' . "\n";
echo "File exists: " . (file_exists(__DIR__ . '/sidebar/navigation.json') ? 'YES' : 'NO') . "\n";

$navigationData = loadNavigationData();
echo "Navigation data loaded: " . (empty($navigationData) ? 'NO (empty)' : 'YES (' . count($navigationData) . ' items)') . "\n";

if (empty($navigationData)) {
    echo "Debug: Checking direct file read...\n";
    $content = file_get_contents(__DIR__ . '/sidebar/navigation.json');
    if ($content === false) {
        echo "ERROR: Cannot read file\n";
    } else {
        $data = json_decode($content, true);
        if ($data === null) {
            echo "ERROR: JSON decode failed: " . json_last_error_msg() . "\n";
        } else {
            echo "JSON decoded successfully, has 'navigation' key: " . (isset($data['navigation']) ? 'YES' : 'NO') . "\n";
            if (isset($data['navigation'])) {
                echo "Navigation array count: " . count($data['navigation']) . "\n";
            }
        }
    }
}
?>
