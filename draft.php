<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)
$inactive_time = 3600;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: BNM"); // Redirect to login page
  exit();
}
 
// Define JSON file path
define('FEEDBACK_JSON_FILE', __DIR__ . '/feedback.json');

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  $data = json_decode(file_get_contents('php://input'), true);

  $type = $data['type'] ?? null;
  $content = $data['content'] ?? '';
  $rating = $data['rating'] ?? null;
  $page = $data['page'] ?? null;
  $user_id = $_SESSION['user_id'];
  $username = $_SESSION['username'] ?? 'Unknown';

  if (!$type || !in_array($type, ['bug', 'suggestion', 'rating'])) {
      echo json_encode(['success' => false, 'message' => 'Invalid feedback type']);
      exit;
  }

  try {
      // Prepare feedback data
      $feedbackData = [
          'timestamp' => date('Y-m-d H:i:s'),
          'user_id' => $user_id,
          'username' => $username,
          'type' => $type,
          'content' => $content,
          'rating' => $rating,
          'page' => $page
      ];
      
      // Load existing feedback or create new array
      $feedbackArray = [];
      if (file_exists(FEEDBACK_JSON_FILE)) {
          $jsonContent = file_get_contents(FEEDBACK_JSON_FILE);
          if (!empty($jsonContent)) {
              $feedbackArray = json_decode($jsonContent, true);
          }
          if (!is_array($feedbackArray)) {
              $feedbackArray = []; // Reset if file is corrupted
          }
      }
      
      // Add new feedback
      $feedbackArray[] = $feedbackData;
      
      // Save to JSON file
      file_put_contents(FEEDBACK_JSON_FILE, json_encode($feedbackArray, JSON_PRETTY_PRINT));
      
      echo json_encode(['success' => true]);
  } catch (Exception $e) {
      error_log('Error saving feedback: ' . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Error saving feedback']);
  }
  exit;
}

// ... [rest of your existing session management code] ...


// Get session details
$user_id = $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';
$login_time = isset($_SESSION['login_time']) ? date("Y-m-d H:i:s", $_SESSION['login_time']) : 'Unknown';
$ip_address = $_SERVER['REMOTE_ADDR'];
$current_time = date("Y-m-d H:i:s");

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    $session_lifetime = time() - $_SESSION['last_activity'];
    if ($session_lifetime > $inactive_time) {
        session_unset();
        session_destroy();
        $log_entry = "$current_time - User ID: $user_id - Username: $username - IP: $ip_address - Session Expired\n";
        file_put_contents(__DIR__ . "/login_logs.txt", $log_entry, FILE_APPEND);
        header("Location: BNM?session_expired=1");
        exit();
    }
}

// Update last activity
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
        <title>BNM Web</title>
        <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
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

.card {
  border-radius: 10px;
  width: 150px;
  height: 200px;
  transition: all 0.3s;
  position: absolute;
  transition-delay: 0.3s;
}

.card:hover {
  width: 500px;
  height: 250px;
  transition-delay: 0s;
}

.card::after {
  /* content: "\2193  Hover me \2193"; */
  width: 100%;
  height: 100%;
  text-align: center;
  position: absolute;
  top: -30px;
  left: 0;
  opacity: 1;
  visibility: visible;
  transition: opacity 0.3s, visibility 0.3s;
  transition-delay: 0.3s;
}

.card:hover::after {
  opacity: 0;
  visibility: hidden;
  transition-delay: 0s;
}

.image {
  width: 100%;
  float: left;
  transition: all 0.3s;
  margin: 0%;
  transition-delay: 0.3s;
}

.card:hover .image {
  transition-delay: 0s;
  width: 50%;
  margin: 0 15px 0 0;
  filter: drop-shadow(-5px 5px 4px #000000aa);
}

.heading,
.icons {
  opacity: 0;
  visibility: hidden;
  overflow: hidden;
  transition: opacity 0.3s, visibility 0.3s;
  transition-delay: 0s;
}

.heading {
  display: block;
  font-size: 30px;
  font-weight: bold;
  font-family: Montserrat, sans-serif;
  margin: 25px 20px;
  text-align: right;
  position: relative;
  z-index: 1;
  color: white;
  text-shadow: 1px 1px 3px #0004;
  user-select: none;
  background: linear-gradient(
    130deg,
    pink 20%,
    rgb(196, 91, 196) 50%,
    rgb(85, 183, 228) 100%
  );
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.card:hover .heading {
  transition-delay: 0.3s;
  opacity: 1;
  visibility: visible;
}

.heading::after {
  content: "!";
}

.card:hover .icons {
  transition-delay: 0.3s;
  opacity: 1;
  visibility: visible;
}

.icons {
  text-align: center;
  transform: translateX(-10px);
}

.icons a {
  text-decoration: none;
}

.icons svg {
  width: 50px;
  height: 50px;
  margin: 10px;
  transition: transform 0.3s;
}

.icons svg:hover {
  transform: translateY(-5px);
  transform-origin: center -10px;
}

.icons svg:active {
  transform: scale(0.9);
}

.icons svg path {
  stroke: black;
  opacity: 0.6;
  transition: opacity 0.6s;
}

.icons svg:hover path {
  opacity: 1;
}

/* Drag-and-Drop Styles */
.tab-button {
    position: relative;
}

.tab-button.drag-before::before,
.tab-button.drag-after::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #007bff;
}

.tab-button.drag-before::before {
    left: -1px;
}

.tab-button.drag-after::after {
    right: -1px;
}

.tab-button[draggable="true"]:hover {
    cursor: grab;
}

.tab-button[draggable="true"]:active {
    cursor: grabbing;
}

        </style>
    </head>
    <body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    

  <!-- Dark Mode Toggle (Top Right) -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
<script>
    lottie.loadAnimation({
        container: document.getElementById("lottieContainer"),
        renderer: "svg",
        loop: true,
        autoplay: true,
        path: "json_files/r.json" // Replace with actual path to your .rjson file
    });
</script>

<!-- Sidebar -->

<!-- Sidebar -->



<style>
  .chart-controls {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

#dataChart {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dark #dataChart {
    background-color: #374151;
}
body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
    }



    /* Main content area */
    .main {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
    }

    /* Tab bar */
    #tabs {
      display: flex;
      background-color: #eee;
      border-bottom: 1px solid #ccc;
    }

    .tab-button {
      padding: 8px 12px;
      margin-right: 2px;
      border: none;
      background-color: #ccc;
      cursor: pointer;
      position: relative;
    }

    .tab-button.active {
      background-color: #ffffff;
      border-bottom: 2px solid green;
      font-weight: bold;
    }

    .tab-button span {
      margin-left: 5px;
      cursor: pointer;
      color: red;
    }

    /* Tab content area */
    #tab-contents {
      flex-grow: 1;
      overflow: auto;
    }

    .tab-pane {
      display: none;
      width: 100%;
      height: 100%;
    }

    .tab-pane iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
    .main {
  color: black;
}


  </style>

<style>
.tabs-wrapper {
  overflow-x: auto;
  white-space: nowrap;
  border-bottom: 1px solid #ccc;
  padding: 4px;
}

.tabs-scroll {
  display: inline-flex;
  min-width: 100%;
}

.tab-button {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  margin-right: 4px;
  border: none;
  background-color: #f0f0f0;
  cursor: pointer;
  position: relative;
  border-radius: 5px;
}

.tab-button.active {
  background-color: #007bff;
  color: white;
}

.tab-button span {
  margin-left: 8px;
  color: red;
  font-weight: bold;
  cursor: pointer;
}
</style>

    <div id="sidebar-container"></div>

    <script>
    fetch("side")
      .then(response => response.text())
      .then(html => {
        const container = document.getElementById("sidebar-container");
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = html;
        container.innerHTML = tempDiv.innerHTML;
    
        // After DOM injection, dynamically load sidebar script
        const script = document.createElement('script');
        script.src = 'sid.js'; // Move all logic into sid.js
        document.body.appendChild(script);
      })
      .catch(error => console.error("Error loading sidebar:", error));
    
    
    </script>

<div class="main">
  <div class="tabs-wrapper">
    <div id="tabs" class="tabs-scroll">
      <button id="tab-whatsnew" class="tab-button" onclick="navigateTo('whatsnew')">🆕 What's New?</button>
    </div>
  </div>
  <div id="tab-contents"></div>
</div>


  <script>
    // Initialize with What's New tab on page load
document.addEventListener('DOMContentLoaded', function() {
  navigateTo('whatsnew');
});

function navigateTo(pageId) {
  const tabId = `tab-${pageId}`;
  const contentId = `content-${pageId}`;

  if (!document.getElementById(tabId) && pageId !== 'whatsnew') {
    const tab = document.createElement('button');
    tab.id = tabId;
    tab.className = 'tab-button';
    tab.textContent = pageId.replace('.html', '');
    tab.onclick = () => showTab(pageId);

    const closeBtn = document.createElement('span');
    closeBtn.textContent = '×';
    closeBtn.onclick = (e) => {
      e.stopPropagation();
      document.getElementById(tabId).remove();
      document.getElementById(contentId).remove();
    };
    tab.appendChild(closeBtn);
    document.getElementById('tabs').appendChild(tab);
  }

  if (!document.getElementById(contentId)) {
    const content = document.createElement('div');
    content.id = contentId;
    content.className = 'tab-pane';
    content.style.display = 'none';
    document.getElementById('tab-contents').appendChild(content);

    fetch(pageId)
      .then(res => res.text())
      .then(html => {
        // Inject HTML
        content.innerHTML = html;

        // Apply dark mode if needed
        if (document.body.classList.contains('dark-mode')) {
          content.classList.add('dark-mode');
        }

        // Extract and execute <script> tags manually
        const scripts = Array.from(content.querySelectorAll('script'));
        scripts.forEach(script => {
          const newScript = document.createElement('script');
          newScript.text = script.textContent;
          script.parentNode.replaceChild(newScript, script);
        });

        showTab(pageId);
      });
  } else {
    showTab(pageId);
  }
}

  function showTab(pageId) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(div => div.style.display = 'none');

    document.getElementById(`tab-${pageId}`).classList.add('active');
    document.getElementById(`content-${pageId}`).style.display = 'block';
  }

  const toggle = document.getElementById('themeToggle');

  // Apply saved theme on load
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
    toggle.checked = true;
  }

  toggle.addEventListener('change', () => {
    const isDark = toggle.checked;
    document.body.classList.toggle('dark-mode', isDark);
    localStorage.setItem('theme', isDark ? 'dark' : 'light');

    // Apply to all open tab contents
    document.querySelectorAll('.tab-pane').forEach(tab => {
      tab.classList.toggle('dark-mode', isDark);
    });
  });

  
</script>

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




  <!-- Dark Mode Toggle (Top Right) -->
<!-- From Uiverse.io by Galahhad --> 
<label class="theme-switch" >
<input type="checkbox" class="theme-switch__checkbox" id="themeToggle">

  <div class="theme-switch__container">
    <div class="theme-switch__clouds"></div>
    <div class="theme-switch__stars-container">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 144 55" fill="none">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M135.831 3.00688C135.055 3.85027 134.111 4.29946 133 4.35447C134.111 4.40947 135.055 4.85867 135.831 5.71123C136.607 6.55462 136.996 7.56303 136.996 8.72727C136.996 7.95722 137.172 7.25134 137.525 6.59129C137.886 5.93124 138.372 5.39954 138.98 5.00535C139.598 4.60199 140.268 4.39114 141 4.35447C139.88 4.2903 138.936 3.85027 138.16 3.00688C137.384 2.16348 136.996 1.16425 136.996 0C136.996 1.16425 136.607 2.16348 135.831 3.00688ZM31 23.3545C32.1114 23.2995 33.0551 22.8503 33.8313 22.0069C34.6075 21.1635 34.9956 20.1642 34.9956 19C34.9956 20.1642 35.3837 21.1635 36.1599 22.0069C36.9361 22.8503 37.8798 23.2903 39 23.3545C38.2679 23.3911 37.5976 23.602 36.9802 24.0053C36.3716 24.3995 35.8864 24.9312 35.5248 25.5913C35.172 26.2513 34.9956 26.9572 34.9956 27.7273C34.9956 26.563 34.6075 25.5546 33.8313 24.7112C33.0551 23.8587 32.1114 23.4095 31 23.3545ZM0 36.3545C1.11136 36.2995 2.05513 35.8503 2.83131 35.0069C3.6075 34.1635 3.99559 33.1642 3.99559 32C3.99559 33.1642 4.38368 34.1635 5.15987 35.0069C5.93605 35.8503 6.87982 36.2903 8 36.3545C7.26792 36.3911 6.59757 36.602 5.98015 37.0053C5.37155 37.3995 4.88644 37.9312 4.52481 38.5913C4.172 39.2513 3.99559 39.9572 3.99559 40.7273C3.99559 39.563 3.6075 38.5546 2.83131 37.7112C2.05513 36.8587 1.11136 36.4095 0 36.3545ZM56.8313 24.0069C56.0551 24.8503 55.1114 25.2995 54 25.3545C55.1114 25.4095 56.0551 25.8587 56.8313 26.7112C57.6075 27.5546 57.9956 28.563 57.9956 29.7273C57.9956 28.9572 58.172 28.2513 58.5248 27.5913C58.8864 26.9312 59.3716 26.3995 59.9802 26.0053C60.5976 25.602 61.2679 25.3911 62 25.3545C60.8798 25.2903 59.9361 24.8503 59.1599 24.0069C58.3837 23.1635 57.9956 22.1642 57.9956 21C57.9956 22.1642 57.6075 23.1635 56.8313 24.0069ZM81 25.3545C82.1114 25.2995 83.0551 24.8503 83.8313 24.0069C84.6075 23.1635 84.9956 22.1642 84.9956 21C84.9956 22.1642 85.3837 23.1635 86.1599 24.0069C86.9361 24.8503 87.8798 25.2903 89 25.3545C88.2679 25.3911 87.5976 25.602 86.9802 26.0053C86.3716 26.3995 85.8864 26.9312 85.5248 27.5913C85.172 28.2513 84.9956 28.9572 84.9956 29.7273C84.9956 28.563 84.6075 27.5546 83.8313 26.7112C83.0551 25.8587 82.1114 25.4095 81 25.3545ZM136 36.3545C137.111 36.2995 138.055 35.8503 138.831 35.0069C139.607 34.1635 139.996 33.1642 139.996 32C139.996 33.1642 140.384 34.1635 141.16 35.0069C141.936 35.8503 142.88 36.2903 144 36.3545C143.268 36.3911 142.598 36.602 141.98 37.0053C141.372 37.3995 140.886 37.9312 140.525 38.5913C140.172 39.2513 139.996 39.9572 139.996 40.7273C139.996 39.563 139.607 38.5546 138.831 37.7112C138.055 36.8587 137.111 36.4095 136 36.3545ZM101.831 49.0069C101.055 49.8503 100.111 50.2995 99 50.3545C100.111 50.4095 101.055 50.8587 101.831 51.7112C102.607 52.5546 102.996 53.563 102.996 54.7273C102.996 53.9572 103.172 53.2513 103.525 52.5913C103.886 51.9312 104.372 51.3995 104.98 51.0053C105.598 50.602 106.268 50.3911 107 50.3545C105.88 50.2903 104.936 49.8503 104.16 49.0069C103.384 48.1635 102.996 47.1642 102.996 46C102.996 47.1642 102.607 48.1635 101.831 49.0069Z" fill="currentColor"></path>
      </svg>
    </div>
    <div class="theme-switch__circle-container">
      <div class="theme-switch__sun-moon-container">
        <div class="theme-switch__moon">
          <div class="theme-switch__spot"></div>
          <div class="theme-switch__spot"></div>
          <div class="theme-switch__spot"></div>
        </div>
      </div>
    </div>
  </div>
</label>


<style>



.theme-switch {
    position: sticky;
    top: 10px;
    right: 10px;
    padding: 10px;
    z-index: 50;
  --toggle-size: 20px; /* Reduced from 30px */
  
  --container-width: 3.75em;   /* Reduced from 5.625em */
  --container-height: 1.7em;   /* Reduced from 2.5em */
  --container-radius: 4em;     /* Scaled down proportionally */
  
  --container-light-bg: #3D7EAE;
  --container-night-bg: #1D1F2C;
  
  --circle-container-diameter: 2.3em;  /* Reduced from 3.375em */
  --sun-moon-diameter: 1.5em;          /* Reduced from 2.125em */
  
  --sun-bg: #ECCA2F;
  --moon-bg: #C4C9D1;
  --spot-color: #959DB1;
  
  --circle-container-offset: calc((var(--circle-container-diameter) - var(--container-height)) / 2 * -1);
  
  --stars-color: #fff;
  --clouds-color: #F3FDFF;
  --back-clouds-color: #AACADF;
  
  --transition: .5s cubic-bezier(0, -0.02, 0.4, 1.25);
  --circle-transition: .3s cubic-bezier(0, -0.02, 0.35, 1.17);
  
}

.theme-switch, .theme-switch *, .theme-switch *::before, .theme-switch *::after {
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-size: var(--toggle-size);
}

.theme-switch__container {
  width: var(--container-width);
  height: var(--container-height);
  background-color: var(--container-light-bg);
  border-radius: var(--container-radius);
  overflow: hidden;
  cursor: pointer;
  -webkit-box-shadow: 0em -0.062em 0.062em rgba(0, 0, 0, 0.25), 0em 0.062em 0.125em rgba(255, 255, 255, 0.94);
  box-shadow: 0em -0.062em 0.062em rgba(0, 0, 0, 0.25), 0em 0.062em 0.125em rgba(255, 255, 255, 0.94);
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
  position: relative;
}

.theme-switch__container::before {
  content: "";
  position: absolute;
  z-index: 1;
  inset: 0;
  -webkit-box-shadow: 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset, 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset;
  box-shadow: 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset, 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset;
  border-radius: var(--container-radius)
}

.theme-switch__checkbox {
  display: none;
}

.theme-switch__circle-container {
  width: var(--circle-container-diameter);
  height: var(--circle-container-diameter);
  background-color: rgba(255, 255, 255, 0.1);
  position: absolute;
  left: var(--circle-container-offset);
  top: var(--circle-container-offset);
  border-radius: var(--container-radius);
  -webkit-box-shadow: inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), 0 0 0 0.625em rgba(255, 255, 255, 0.1), 0 0 0 1.25em rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), 0 0 0 0.625em rgba(255, 255, 255, 0.1), 0 0 0 1.25em rgba(255, 255, 255, 0.1);
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-transition: var(--circle-transition);
  -o-transition: var(--circle-transition);
  transition: var(--circle-transition);
  pointer-events: none;
}

.theme-switch__sun-moon-container {
  pointer-events: auto;
  position: relative;
  z-index: 2;
  width: var(--sun-moon-diameter);
  height: var(--sun-moon-diameter);
  margin: auto;
  border-radius: var(--container-radius);
  background-color: var(--sun-bg);
  -webkit-box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #a1872a inset;
  box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #a1872a inset;
  -webkit-filter: drop-shadow(0.062em 0.125em 0.125em rgba(0, 0, 0, 0.25)) drop-shadow(0em 0.062em 0.125em rgba(0, 0, 0, 0.25));
  filter: drop-shadow(0.062em 0.125em 0.125em rgba(0, 0, 0, 0.25)) drop-shadow(0em 0.062em 0.125em rgba(0, 0, 0, 0.25));
  overflow: hidden;
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
}

.theme-switch__moon {
  -webkit-transform: translateX(100%);
  -ms-transform: translateX(100%);
  transform: translateX(100%);
  width: 100%;
  height: 100%;
  background-color: var(--moon-bg);
  border-radius: inherit;
  -webkit-box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #969696 inset;
  box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #969696 inset;
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
  position: relative;
}

.theme-switch__spot {
  position: absolute;
  top: 0.75em;
  left: 0.312em;
  width: 0.75em;
  height: 0.75em;
  border-radius: var(--container-radius);
  background-color: var(--spot-color);
  -webkit-box-shadow: 0em 0.0312em 0.062em rgba(0, 0, 0, 0.25) inset;
  box-shadow: 0em 0.0312em 0.062em rgba(0, 0, 0, 0.25) inset;
}

.theme-switch__spot:nth-of-type(2) {
  width: 0.375em;
  height: 0.375em;
  top: 0.937em;
  left: 1.375em;
}

.theme-switch__spot:nth-last-of-type(3) {
  width: 0.25em;
  height: 0.25em;
  top: 0.312em;
  left: 0.812em;
}

.theme-switch__clouds {
  width: 1.25em;
  height: 1.25em;
  background-color: var(--clouds-color);
  border-radius: var(--container-radius);
  position: absolute;
  bottom: -0.625em;
  left: 0.312em;
  -webkit-box-shadow: 0.937em 0.312em var(--clouds-color), -0.312em -0.312em var(--back-clouds-color), 1.437em 0.375em var(--clouds-color), 0.5em -0.125em var(--back-clouds-color), 2.187em 0 var(--clouds-color), 1.25em -0.062em var(--back-clouds-color), 2.937em 0.312em var(--clouds-color), 2em -0.312em var(--back-clouds-color), 3.625em -0.062em var(--clouds-color), 2.625em 0em var(--back-clouds-color), 4.5em -0.312em var(--clouds-color), 3.375em -0.437em var(--back-clouds-color), 4.625em -1.75em 0 0.437em var(--clouds-color), 4em -0.625em var(--back-clouds-color), 4.125em -2.125em 0 0.437em var(--back-clouds-color);
  box-shadow: 0.937em 0.312em var(--clouds-color), -0.312em -0.312em var(--back-clouds-color), 1.437em 0.375em var(--clouds-color), 0.5em -0.125em var(--back-clouds-color), 2.187em 0 var(--clouds-color), 1.25em -0.062em var(--back-clouds-color), 2.937em 0.312em var(--clouds-color), 2em -0.312em var(--back-clouds-color), 3.625em -0.062em var(--clouds-color), 2.625em 0em var(--back-clouds-color), 4.5em -0.312em var(--clouds-color), 3.375em -0.437em var(--back-clouds-color), 4.625em -1.75em 0 0.437em var(--clouds-color), 4em -0.625em var(--back-clouds-color), 4.125em -2.125em 0 0.437em var(--back-clouds-color);
  -webkit-transition: 0.5s cubic-bezier(0, -0.02, 0.4, 1.25);
  -o-transition: 0.5s cubic-bezier(0, -0.02, 0.4, 1.25);
  transition: 0.5s cubic-bezier(0, -0.02, 0.4, 1.25);
}

.theme-switch__stars-container {
  position: absolute;
  color: var(--stars-color);
  top: -100%;
  left: 0.312em;
  width: 2.75em;
  height: auto;
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
}

/* actions */

.theme-switch__checkbox:checked + .theme-switch__container {
  background-color: var(--container-night-bg);
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__circle-container {
  left: calc(100% - var(--circle-container-offset) - var(--circle-container-diameter));
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__circle-container:hover {
  left: calc(100% - var(--circle-container-offset) - var(--circle-container-diameter) - 0.187em)
}

.theme-switch__circle-container:hover {
  left: calc(var(--circle-container-offset) + 0.187em);
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__moon {
  -webkit-transform: translate(0);
  -ms-transform: translate(0);
  transform: translate(0);
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__clouds {
  bottom: -4.062em;
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__stars-container {
  top: 50%;
  -webkit-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
}
</style>




  <style>
    /* Chatbot styles from your original code go here */
    .chatbot-container {
      position: fixed; bottom: 20px; right: 20px; z-index: 1000;
    }
    .chatbot-button {
      background-color: #4CAF50; color: white; border: none; padding: 10px 15px;
      border-radius: 25px; cursor: pointer; font-size: 16px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .chatbot-button:hover { background-color: #45a049; }
    .chatbot-window {
      display: flex; flex-direction: column; width: 300px; height: 700px; max-height: 90vh;
      background-color: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); overflow: hidden;
    }
    .chatbot-header {
      background-color: #4CAF50; color: white; padding: 10px 15px;
      display: flex; justify-content: space-between; align-items: center; font-weight: bold;
    }
    .close-btn {
      background: none; border: none; color: white; font-size: 20px; cursor: pointer;
    }
    .chatbot-messages {
      flex: 1; overflow-y: auto; padding: 10px; background-color: #f9f9f9; display: flex; flex-direction: column;
    }
    .chatbot-input {
      padding: 10px; border-top: 1px solid #ddd; background-color: white; display: flex; flex-direction: column; gap: 5px;
    }
    .quick-options {
      display: flex; flex-wrap: wrap; gap: 5px;
    }
    .quick-option {
      background-color: #e7f3fe; border: 1px solid #b8daff; border-radius: 15px;
      padding: 5px 10px; font-size: 12px; cursor: pointer;
    }
    .quick-option:hover {
      background-color: #d0e7ff;
    }
    .message {
      margin-bottom: 10px; padding: 8px 12px; border-radius: 18px; max-width: 80%; word-wrap: break-word;
    }
    .bot-message {
      background-color: #e5e5ea; align-self: flex-start; margin-right: auto;
    }
    .user-message {
      background-color: #4CAF50; color: white; align-self: flex-end; margin-left: auto;
    }
    #userInput {
      width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
      resize: none; font-family: inherit; font-size: 14px; height: 60px;
    }
    #sendButton {
      width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none;
      border-radius: 4px; cursor: pointer; font-size: 14px;
    }
    #sendButton:hover {
      background-color: #45a049;
    }
    .rating-container {
      display: flex; justify-content: center; margin-top: 10px;
    }
    .rating-star {
      font-size: 24px; color: #ddd; cursor: pointer; margin: 0 2px;
    }
    .rating-star:hover,
    .rating-star.active {
      color: #ffcc00;
    }
    #userInput:disabled {
      background-color: #eee; color: #888;
    }
    #sendButton:disabled {
      background-color: #a5d6a7; cursor: not-allowed;
    }
  </style>


<div class="chatbot-container">
  <button class="chatbot-button" onclick="toggleChatbot()">💬 Need Help?</button>
  <div class="chatbot-window" id="chatbotWindow" style="display: none;">
    <div class="chatbot-header">
      <span>BNM Parapharm Bot</span>
      <button class="close-btn" onclick="toggleChatbot()">×</button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages"></div>
    <div class="chatbot-input">
      <div class="quick-options" id="quickOptions"></div>
      <textarea id="userInput" placeholder="Type your message here..." rows="2" disabled></textarea>
      <button id="sendButton" onclick="sendMessage()" disabled>Send</button>
    </div>
  </div>
</div>

<script>
let currentState = 'initial';
let selectedPage = '';

function toggleChatbot() {
  const chatbotWindow = document.getElementById('chatbotWindow');
  if (chatbotWindow.style.display === 'block') {
    chatbotWindow.style.display = 'none';
  } else {
    chatbotWindow.style.display = 'block';
    if (currentState === 'initial') showInitialMessage();
  }
}

function showInitialMessage() {
  const messagesDiv = document.getElementById('chatbotMessages');
  messagesDiv.innerHTML = '';
  addBotMessage("Hi! I'm BNM Parapharm Bot. How can I assist you today?");
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = `
    <div class="quick-option" onclick="selectOption('report_bug')">Report a Bug</div>
    <div class="quick-option" onclick="selectOption('make_suggestion')">Make a Suggestion</div>
    <div class="quick-option" onclick="selectOption('give_opinion')">Give Opinion</div>
  `;
}

function selectOption(option) {
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = '';
  // Keep inputs disabled until typing is complete
  document.getElementById('userInput').disabled = true;
  document.getElementById('sendButton').disabled = true;

  if (option === 'report_bug') {
    currentState = 'select_page';
    addBotMessage("Please select the page where you encountered the bug:");
    // Show page options after message is fully typed (estimate 1.5 seconds for this message)
    setTimeout(() => {
      quickOptions.innerHTML = `
        <div class="quick-option" onclick="selectPage('Main')">🏠 Accueil</div>
        <div class="quick-option" onclick="selectPage('mony')">📈 FONDS Analysis</div>
        <div class="quick-option" onclick="selectPage('bank')">🏦 Banks</div>
        <div class="quick-option" onclick="selectPage('ETAT_Fourniseeur')">🤝 CREANCES/DETTES</div>
        <div class="quick-option" onclick="selectPage('Etatstock')">📦 ÉTAT DE STOCK</div>
        <div class="quick-option" onclick="selectPage('Product')">🛍️ PRODUCTS</div>
        <div class="quick-option" onclick="selectPage('Rotation')">🔄 ROTATION</div>
        <div class="quick-option" onclick="selectPage('Quota')">🎯 PRODUIT QUOTA</div>
        <div class="quick-option" onclick="selectPage('Recap_Achat')">🛒 Recap Achat</div>
        <div class="quick-option" onclick="selectPage('recap_achat_facturation')">🧾 Recap Achat F</div>
        <div class="quick-option" onclick="selectPage('Annual_Recap_A')">📆 Annual Recap</div>
        <div class="quick-option" onclick="selectPage('Recap_Vente')">💰 Recap Vente</div>
        <div class="quick-option" onclick="selectPage('Recap_Vente_Facturation')">🧾 Recap Vente F</div>
        <div class="quick-option" onclick="selectPage('Annual_Recap_V')">📆 Annual Recap</div>
        <div class="quick-option" onclick="selectPage('Journal_Vente')">📝 Journal de Vente</div>
        <div class="quick-option" onclick="selectPage('CONFIRMED_ORDERS')">✅ Confirm Order</div>
      `;
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }, 1500); // Adjust timing based on message length

  } else if (option === 'make_suggestion') {
    currentState = 'suggestion';
    addBotMessage("Please type your suggestion below:");
    // Enable input after message is fully typed (estimate 1.5 seconds for this message)
    setTimeout(() => {
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }, 1500);

  } else if (option === 'give_opinion') {
    currentState = 'opinion';
    addBotMessage("Please rate your experience (1-5 stars):");
    // Show rating stars after message is fully typed (estimate 1.5 seconds for this message)
    setTimeout(() => {
      quickOptions.innerHTML = `
        <div class="rating-container">
          <span class="rating-star" onclick="rateExperience(1)">★</span>
          <span class="rating-star" onclick="rateExperience(2)">★</span>
          <span class="rating-star" onclick="rateExperience(3)">★</span>
          <span class="rating-star" onclick="rateExperience(4)">★</span>
          <span class="rating-star" onclick="rateExperience(5)">★</span>
        </div>
      `;
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }, 1500);
  }
}
function selectPage(page) {
  selectedPage = page;
  currentState = 'describe_bug';
  addBotMessage(`You selected: ${page}. Please describe the bug:`);
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = '';
}

function sendMessage() {
  const userInput = document.getElementById('userInput');
  const message = userInput.value.trim();
  if (!message) return;

  addUserMessage(message);

  let feedbackData = {
    content: message,
    type: '',
    page: selectedPage
  };

  if (currentState === 'describe_bug') {
    feedbackData.type = 'bug';
    sendFeedback(feedbackData, "Thank you for reporting this bug. We'll work on it!");
  } else if (currentState === 'suggestion') {
    feedbackData.type = 'suggestion';
    sendFeedback(feedbackData, "Thank you for your suggestion! We appreciate it.");
  }
}

function sendFeedback(data, successMessage) {
  fetch('', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    addBotMessage(successMessage);
    setTimeout(() => {
      currentState = 'initial';
      showInitialMessage();
      document.getElementById('userInput').disabled = true;
      document.getElementById('sendButton').disabled = true;
    }, 1500);
  })
  .catch(error => {
    console.error('Error:', error);
    addBotMessage("There was an issue submitting your data. Please try again.");
  });
}

function rateExperience(rating) {
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach((star, index) => {
    star.classList.toggle('active', index < rating);
  });

  let feedback = '';
  switch (rating) {
    case 1:
      feedback = "We're very sorry to hear that. Is there anything we can improve?";
      break;
    case 2:
      feedback = "Thanks for your feedback. We’ll try to do better!";
      break;
    case 3:
      feedback = "Thanks for your average rating! We appreciate your honesty.";
      break;
    case 4:
      feedback = "Thank you for your good rating! We’re glad you liked us.";
      break;
    case 5:
      feedback = "Wow! Thank you for the perfect score. You made our day!";
      break;
  }

  addBotMessage(feedback);

  fetch('', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ type: 'rating', content: feedback, rating: rating })
  })
  .then(response => response.json())
  .then(() => {
    setTimeout(() => {
      currentState = 'initial';
      showInitialMessage();
    }, 2000);
  });
}


function addBotMessage(text) {
  const messagesDiv = document.getElementById('chatbotMessages');
  const messageDiv = document.createElement('div');
  messageDiv.className = 'message bot-message';
  messagesDiv.appendChild(messageDiv);
  
  // Disable input while typing
  document.getElementById('userInput').disabled = true;
  document.getElementById('sendButton').disabled = true;
  
  let i = 0;
  const typingSpeed = 20;
  
  function typeWriter() {
    if (i < text.length) {
      messageDiv.textContent += text.charAt(i);
      i++;
      setTimeout(typeWriter, typingSpeed);
      scrollToBottom();
    } else {
      // Re-enable input after typing is complete
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }
  }
  
  typeWriter();
}

function addUserMessage(text) {
  const messagesDiv = document.getElementById('chatbotMessages');
  const messageDiv = document.createElement('div');
  messageDiv.className = 'message user-message';
  messageDiv.textContent = text;
  messagesDiv.appendChild(messageDiv);
  scrollToBottom();
}

function scrollToBottom() {
  const messagesDiv = document.getElementById('chatbotMessages');
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

document.addEventListener('DOMContentLoaded', () => {
  const chatbotWindow = document.getElementById('chatbotWindow');
  chatbotWindow.style.display = 'none';

  document.getElementById('userInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
});
</script>



<SCRipt>
             // Dark Mode Toggle Functionality
             const themeToggle = document.getElementById('themeToggle');
            const htmlElement = document.documentElement;

            // Load Dark Mode Preference from Local Storage
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                htmlElement.classList.add('dark');
                themeToggle.checked = true;
            }

            // Toggle Dark Mode on Click
            themeToggle.addEventListener('change', () => {
                htmlElement.classList.toggle('dark');
                const isDarkMode = htmlElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDarkMode);
            });


</SCRipt>
    
    </body>
    </html>
