<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Status</title>
</head>
<body>
    <h2>Session Information</h2>
    
    <?php if (isset($_SESSION['username'])): ?>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['Role'] ?? 'Not set'); ?></p>
        
        <?php
        $isAdmin = (
          $_SESSION['username'] === 'hichem' ||
          $_SESSION['Role'] === 'Developer' ||
          $_SESSION['Role'] === 'Sup Achat' ||
          $_SESSION['Role'] === 'Sup Vente'
        );
        ?>
        <p><strong>Is Admin:</strong> <?php echo $isAdmin ? 'YES' : 'NO'; ?></p>
        <p><strong>Will be logged:</strong> <?php echo $isAdmin ? 'NO (admin excluded)' : 'YES'; ?></p>
    <?php else: ?>
        <p><strong>Status:</strong> Not logged in</p>
    <?php endif; ?>
    
    <h3>Test Logging</h3>
    <button onclick="testLog()">Test Log Function</button>
    
    <h3>Debug Log</h3>
    <?php
    $debugFile = __DIR__ . '/debug.log';
    if (file_exists($debugFile)) {
        echo '<pre>' . htmlspecialchars(file_get_contents($debugFile)) . '</pre>';
    } else {
        echo '<p>No debug log found</p>';
    }
    ?>
    
    <script>
    function testLog() {
        let data = new FormData();
        data.append('action', 'TEST_BUTTON');
        data.append('details', 'Manual test from status page');
        
        fetch('simple_log.php', {
            method: 'POST',
            body: data
        }).then(response => response.text())
        .then(data => {
            console.log('Response:', data);
            alert('Test sent, check debug log');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error);
        });
    }
    </script>
</body>
</html>
