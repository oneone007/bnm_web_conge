<?php
session_start();

// Check if user is admin
$isAdmin = false;
if (isset($_SESSION['username']) && isset($_SESSION['Role'])) {
    $isAdmin = (
      $_SESSION['username'] === 'hichem' ||
      $_SESSION['Role'] === 'Developer' ||
      $_SESSION['Role'] === 'Sup Achat' ||
      $_SESSION['Role'] === 'Sup Vente'
    );
}

if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

// Clear all log files
$logDir = __DIR__ . '/vente_track/';
if (is_dir($logDir)) {
    $files = glob($logDir . '*_activity.log');
    foreach ($files as $file) {
        unlink($file);
    }
}

header('Location: view_logs.php');
exit;
?>
