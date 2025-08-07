
<?php
session_start();

// Get real IP address even behind proxy/cloudflare
function getRealIP() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

// Log the access attempt to a JSON file
$log_data = [
    'session_id' => session_id(),
    'ip_address' => getRealIP(),
    'timestamp' => date('Y-m-d H:i:s'),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'referer' => $_SERVER['HTTP_REFERER'] ?? 'DIRECT',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'UNKNOWN'
];

// Read existing JSON file or create empty array
$json_file = '403.json';
$existing_data = [];
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $existing_data = json_decode($json_content, true) ?: [];
}

// Add new log entry
$existing_data[] = $log_data;

// Save updated data to JSON file
file_put_contents($json_file, json_encode($existing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden | BNM</title>
    <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #660000 50%, #000000 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff0000;
            animation: backgroundPulse 3s ease-in-out infinite alternate;
        }
        
        @keyframes backgroundPulse {
            0% { background: linear-gradient(135deg, #1a1a1a 0%, #660000 50%, #000000 100%); }
            100% { background: linear-gradient(135deg, #000000 0%, #990000 50%, #330000 100%); }
        }
        
        .error-container {
            text-align: center;
            background: rgba(0, 0, 0, 0.8);
            padding: 60px 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 2px solid #ff0000;
            box-shadow: 0 0 50px rgba(255, 0, 0, 0.5), inset 0 0 20px rgba(255, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
            animation: containerGlow 2s ease-in-out infinite alternate;
        }
        
        @keyframes containerGlow {
            0% { 
                box-shadow: 0 0 30px rgba(255, 0, 0, 0.3), inset 0 0 15px rgba(255, 0, 0, 0.1);
                border-color: #ff0000;
            }
            100% { 
                box-shadow: 0 0 60px rgba(255, 0, 0, 0.7), inset 0 0 25px rgba(255, 0, 0, 0.3);
                border-color: #ff3333;
            }
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin: 0;
            line-height: 1;
            color: #ff0000;
            text-shadow: 0 0 20px #ff0000, 0 0 40px #ff0000, 0 0 80px #ff0000;
            animation: textFlicker 1.5s ease-in-out infinite alternate;
        }
        
        @keyframes textFlicker {
            0% { 
                text-shadow: 0 0 20px #ff0000, 0 0 40px #ff0000, 0 0 80px #ff0000;
                opacity: 1;
            }
            50% {
                text-shadow: 0 0 10px #ff0000, 0 0 20px #ff0000, 0 0 40px #ff0000;
                opacity: 0.8;
            }
            100% { 
                text-shadow: 0 0 30px #ff0000, 0 0 60px #ff0000, 0 0 100px #ff0000;
                opacity: 1;
            }
        }
        
        .error-title {
            font-size: 2.5rem;
            margin: 20px 0 10px;
            font-weight: 700;
            color: #ff0000;
            text-shadow: 0 0 10px #ff0000;
            letter-spacing: 2px;
        }
        
        .error-message {
            font-size: 1.2rem;
            margin: 20px 0 30px;
            opacity: 0.9;
            line-height: 1.6;
            color: #ffcccc;
            text-shadow: 0 0 5px #ff0000;
        }
        
        .warning-text {
            font-size: 1rem;
            margin: 30px 0;
            color: #ff6666;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: warningBlink 2s ease-in-out infinite;
        }
        
        @keyframes warningBlink {
            0%, 50% { opacity: 1; }
            25%, 75% { opacity: 0.3; }
        }
        
        .icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.8;
            animation: iconSpin 4s linear infinite;
        }
        
        @keyframes iconSpin {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(90deg) scale(1.1); }
            50% { transform: rotate(180deg) scale(1); }
            75% { transform: rotate(270deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
        }
        
        .skull-icon {
            font-size: 4rem;
            margin: 0 10px;
            display: inline-block;
            animation: skulls 3s ease-in-out infinite alternate;
        }
        
        @keyframes skulls {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-10px); }
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-container {
                padding: 40px 20px;
            }
            
            .icon {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <div class="skull-icon">�</div>
        <div class="skull-icon">☠️</div>
        <div class="skull-icon">💀</div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">⚠️ DANGER ZONE ⚠️</h2>
        <h3 class="error-title" style="font-size: 1.5rem;">ACCESS TERMINATED</h3>
        <p class="error-message">
            🚨 <strong>CRITICAL SECURITY BREACH DETECTED</strong> 🚨<br>
            Your access attempt has been <span style="color: #ff0000; font-weight: bold;">BLOCKED</span> and <span style="color: #ff0000; font-weight: bold;">LOGGED</span><br>
            Unauthorized access is <span style="color: #ff0000; font-weight: bold;">STRICTLY PROHIBITED</span><br>
            This incident will be reported to system administrators
        </p>
        <div class="warning-text">
            ⚠️ WARNING: RESTRICTED AREA ⚠️<br>
            UNAUTHORIZED ACCESS IS A CRIMINAL OFFENSE
        </div>
        <div style="margin-top: 30px; color: #ff6666; font-size: 0.9rem;">
            Session ID: <span style="color: #ff0000; font-family: monospace;"><?php echo session_id() ?: '[REDACTED]'; ?></span><br>
            IP Address: <span style="color: #ff0000; font-family: monospace;"><?php echo getRealIP(); ?></span><br>
            Timestamp: <span style="color: #ff0000; font-family: monospace;" id="timestamp"></span>
        </div>
    </div>
    
    <script>
        // Add some dangerous interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.error-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            // Show current timestamp
            const timestamp = new Date().toISOString();
            document.getElementById('timestamp').textContent = timestamp;
            
            setTimeout(() => {
                container.style.transition = 'all 0.5s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
            
            // Add screen shake effect
            let shakeCount = 0;
            const maxShakes = 10;
            
            function shakeScreen() {
                if (shakeCount < maxShakes) {
                    document.body.style.transform = `translate(${Math.random() * 4 - 2}px, ${Math.random() * 4 - 2}px)`;
                    shakeCount++;
                    setTimeout(() => {
                        document.body.style.transform = 'translate(0, 0)';
                        setTimeout(shakeScreen, 100);
                    }, 50);
                }
            }
            
            // Start screen shake after 2 seconds
            setTimeout(shakeScreen, 2000);
            
            // Play alert sound simulation (visual effect)
            setInterval(() => {
                document.body.style.filter = 'brightness(1.2)';
                setTimeout(() => {
                    document.body.style.filter = 'brightness(1)';
                }, 100);
            }, 3000);
            
            // Disable right-click and common shortcuts
            document.addEventListener('contextmenu', e => e.preventDefault());
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                    (e.ctrlKey && e.shiftKey && e.key === 'J') ||
                    (e.ctrlKey && e.key === 'U')) {
                    e.preventDefault();
                    alert('⚠️ SECURITY ALERT: Developer tools access is monitored and logged!');
                }
            });
        });
    </script>
</body>
</html>
