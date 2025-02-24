<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)
$inactive_time = 3600;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Get session details
$user_id = $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';
$login_time = isset($_SESSION['login_time']) ? date("Y-m-d H:i:s", $_SESSION['login_time']) : 'Unknown';
$ip_address = $_SERVER['REMOTE_ADDR'];
$current_time = date("Y-m-d H:i:s");

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate session lifetime
    $session_lifetime = time() - $_SESSION['last_activity'];

    if ($session_lifetime > $inactive_time) {
        session_unset(); // Unset session variables
        session_destroy(); // Destroy the session

        // Log session expiration
        $log_entry = "$current_time - User ID: $user_id - Username: $username - IP: $ip_address - Session Expired\n";
        file_put_contents(__DIR__ . "/login_logs.txt", $log_entry, FILE_APPEND);

        header("Location: BNM?session_expired=1"); // Redirect to login page with message
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Log session activity
$log_entry = "$current_time - User ID: $user_id - Username: $username - Login Time: $login_time - IP: $ip_address - Active Session\n";
file_put_contents(__DIR__ . "/login_logs.txt", $log_entry, FILE_APPEND);
?>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BNM</title>
        <link rel="icon" href="tab.png" sizes="128x128" type="image/png">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
            <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>

        
        <style>
 body {
    font-family: 'Inter', sans-serif;
}


/* Sidebar Styling */
.sidebar {
    min-width: 200px;
    max-width: 250px;
    background-color: #f9fafb;
    border-right: 1px solid #e5e7eb;
    transition: transform 0.3s ease-in-out;
    position: fixed;
    height: 100vh;
    z-index: 40;
    padding-top: 60px;
}

.sidebar-hidden {
    transform: translateX(-100%);
}

/* Content */
.content {
    margin-left: 250px;
    transition: margin-left 0.3s ease-in-out;
    width: calc(100% - 250px);
    padding: 20px;
}

.content-full {
    margin-left: 0;
    width: 100%;
}

/* Dark Mode */
html.dark .sidebar {
    background-color: #1f2937;
    border-right-color: #374151;
}

html.dark body {
    background-color: #111827;
    color: white;
}

/* Dark Mode Toggle - Styled Checkbox */
.checkbox {
    display: none;
}

/* Toggle Background */
.checkbox-label {
    width: 60px;
    height: 30px;
    background: #ddd;
    display: flex;
    border-radius: 50px;
    align-items: center;
    position: relative;
    cursor: pointer;
    padding: 5px;
    transition: background 0.3s ease-in-out;
}

/* Ball as Sun (Default) */
.ball {
    width: 22px;
    height: 22px;
    background: #facc15; /* Sun color (yellow) */
    position: absolute;
    border-radius: 50%;
    transition: transform 0.3s ease-in-out, background 0.3s ease-in-out;
    left: 5px;
    box-shadow: 0 0 5px 2px #facc15; /* Sun glow effect */
}

/* Add Sun Rays */
.ball::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background: inherit;
    border-radius: 50%;
    transform: scale(1.4);
    opacity: 0.5;
}

/* Moon Shape */
html.dark .ball {
    transform: translateX(30px);
    background: #1e40af; /* Moon color (blue) */
    box-shadow: none; /* Remove glow */
}

/* Crescent Moon Effect */
html.dark .ball::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background: white;
    border-radius: 50%;
    left: 5px; /* Shift left to create crescent effect */
}

/* Dark Mode Background */
html.dark .checkbox-label {
    background: #333;
}

/* Positioning the Dark Mode Toggle on Top Right */
#themeSwitcher {
    position: fixed;
    top: 10px;
    right: 20px;
    z-index: 50;
}
#ram-animation {
    position: relative;  /* Keep it inside the sidebar */
    width: 100%;  /* Fit inside sidebar */
    height: 100px; /* Adjust height as needed */
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none; /* Prevent interaction */
    background: transparent;
}
/* Hide Default Checkbox */
.checkbox {
    display: none;
}

/* Toggle Background */
.checkbox-label {
    width: 60px;
    height: 30px;
    background: #f97316; /* Light Mode Orange */
    display: flex;
    align-items: center;
    border-radius: 50px;
    position: relative;
    cursor: pointer;
    padding: 5px;
    transition: background 0.3s ease-in-out;
}

/* Ball */
.ball {
    width: 24px;
    height: 24px;
    background: white;
    position: absolute;
    border-radius: 50%;
    transition: transform 0.3s ease-in-out;
    left: 5px;
}

/* Icons */
.icon {
    font-size: 16px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    transition: opacity 0.3s ease-in-out;
}

/* Sun (Left) */
.sun {
    left: 10px;
    color: white;
}

/* Moon (Right) */
.moon {
    right: 10px;
    color: white;
    opacity: 0; /* Hidden in Light Mode */
}

/* Dark Mode */
html.dark .checkbox-label {
    background: #1f2937; /* Dark Mode Gray */
}

html.dark .ball {
    transform: translateX(30px);
}

html.dark .sun {
    opacity: 0; /* Hide Sun */
}

html.dark .moon {
    opacity: 1; /* Show Moon */
}

/* Theme Switcher Position */
#themeSwitcher {
    position: sticky;
    top: 10px;
    right: 10px;
    padding: 10px;
    z-index: 50;
}



        </style>
    </head>
    <body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    

  <!-- Dark Mode Toggle (Top Right) -->
  <div id="themeSwitcher">
    <input type="checkbox" class="checkbox" id="themeToggle">
    <label for="themeToggle" class="checkbox-label">
        <span class="icon sun">☀️</span>
        <span class="icon moon">🌙</span>
    </label>
    <div id="lottieContainer" style="width: 250px; height: 200px; margin-top: 10px;"></div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
<script>
    lottie.loadAnimation({
        container: document.getElementById("lottieContainer"),
        renderer: "svg",
        loop: true,
        autoplay: true,
        path: "r.json" // Replace with actual path to your .rjson file
    });
</script>

<!-- Sidebar -->

<!-- Sidebar -->


<div id="sidebar-container"></div>

<script>

    
    // Fetch sidebar content dynamically
    fetch("side")
        .then(response => response.text())
        .then(html => {
            let container = document.getElementById("sidebar-container");
            let tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;

            // Insert the sidebar content into the page
            container.innerHTML = tempDiv.innerHTML;

            // Reattach event listeners for the submenu toggles (Products, Recaps)
            const productsToggle = document.getElementById("products-toggle");
            if (productsToggle) {
                productsToggle.addEventListener("click", function () {
                    let submenu = document.getElementById("products-submenu");
                    submenu.classList.toggle("hidden");
                });
            }

            const recapsToggle = document.getElementById("recaps-toggle");
            if (recapsToggle) {
                recapsToggle.addEventListener("click", function () {
                    let submenu = document.getElementById("recaps-submenu");
                    submenu.classList.toggle("hidden");
                });
            }

            // Initialize Lottie animation after sidebar is inserted
            const ramAnimation = document.getElementById('ram-animation');
            if (ramAnimation) {
                lottie.loadAnimation({
                    container: ramAnimation,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: 'ram.json',
                    rendererSettings: {
                        clearCanvas: true,
                        preserveAspectRatio: 'xMidYMid meet',
                        progressiveLoad: true,
                        hideOnTransparent: true
                    }
                });
            }

            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content');

            // Ensure sidebarToggle is initialized after sidebar is loaded
            if (sidebarToggle && sidebar && content) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('sidebar-hidden');
                    content.classList.toggle('content-full');

                    // Adjust button position when sidebar is hidden or shown
                    if (sidebar.classList.contains('sidebar-hidden')) {
                        sidebarToggle.style.left = '10px';  // Sidebar hidden
                    } else {
                        sidebarToggle.style.left = '260px'; // Sidebar visible
                    }
                });
            } else {
                console.error("Sidebar or Toggle Button not found!");
            }

        })
        .catch(error => console.error("Error loading sidebar:", error));
</script>

<div class="canvas-container">
    <!-- <div class="container arrow" onclick="location.href='c';">Arrow</div>
    <div class="container rabbit" onclick="location.href='b';">Rabbit</div> -->
</div>

<style>
   .canvas-container {
    display: flex;
    justify-content: space-around;
    align-items: center;
    width: 100%;
    height: 100vh; /* Full-page height */
}

.container {
    padding: 10px 20px; /* Reduced padding for smaller buttons */
    color: white;
    width: 150px;
    font-size: 18px; /* Smaller font size */
    text-transform: uppercase;
    cursor: pointer;
    border: 2px solid white;
    border-radius: 8px; /* Slightly reduced border radius */
    background: #555; /* Gray color */
    transition: transform 0.3s ease, background 0.3s ease;
}



    .container:hover {
        transform: scale(1.1); /* Hover effect */
    }

    .arrow {
        background: #ff5733; /* Arrow container color */
    }

    .rabbit {
        background: #33c3ff; /* Rabbit container color */
    }
</style>



    
    </body>
    </html>
    