<?php
session_start();

// Check for force logout by admin
include_once 'session_check.php';

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

            <script src="api_config.js"></script>
            <script src="api_config_inv.js"></script>

        <style>
 body {
    font-family: 'Inter', sans-serif;
    position: relative;
}

/* Background Logo Watermark and Decorative Shapes */
body::before {
    content: '';
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 600px;
    height: 600px;
    background-image: url('assets/log.png');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    opacity: 0.15;
    z-index: -999;
    pointer-events: none;
}

/* Dark mode adjustments for background logo */
body.dark-mode::before,
.dark body::before {
    opacity: 0.08;
}

/* Ensure ALL content is above decorative elements */
.main {
    position: relative;
    z-index: 10;
}

#sidebar-container {
    position: relative;
    z-index: 100;
}

.tab-button,
.tab-pane,
#tabs,
.tabs-wrapper {
    position: relative;
    z-index: 50;
}

#tab-contents {
    position: relative;
    z-index: 50;
}

.chatbot-container {
    z-index: 1000;
}

#invPendingNotification,
#invSaisieNotification {
    z-index: 9999;
}


/* Sidebar Styling */
.sidebar {
    min-width: 200px;
    max-width: 350px;
    background-color: #f9fafb;
    border-right: 1px solid #e5e7eb;
    transition: transform 0.3s ease-in-out;
    position: fixed;
    height: 100vh;
    z-index: 100;
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


html.dark body {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    color: #f9fafb;
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

/* Custom Dark Mode Styles */
body.dark-mode {
    background-color: #111827 !important;
    color: #f3f4f6 !important;
}

/* Theme toggle button */
#themeToggleBtn {
  position: fixed;
  top: 12px;
  right: 12px;
  z-index: 200;
  padding: 8px 10px;
  border-radius: 9999px;
  border: 1px solid rgba(0,0,0,0.1);
  background: #ffffffcc;
  color: #111827;
  backdrop-filter: blur(6px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.dark #themeToggleBtn,
body.dark-mode #themeToggleBtn {
  background: #111827cc;
  color: #f3f4f6;
  border-color: rgba(255,255,255,0.12);
}

        </style>
    </head>
    <body class="flex h-screen bg-gray-100">
    
  <!-- Dark Mode Toggle handled by theme.js -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>


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
    .dark #tabs {
      background-color: #2d3748;
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
      margin-right: 5px;
      cursor: pointer;
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
  padding-left: 250px;
  position: relative;
  min-height: 40px;
}

.dark .tabs-wrapper {
  border-bottom: 1px solid #555;
  background-color: #2d3748;

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

.dark .tab-button {
  background-color: #2d3748;
  color: #e2e8f0;
}

.tab-button.active {
  background-color:rgb(83, 118, 155);
  color: white;
}

.dark .tab-button.active {
  background-color: #4a5568;
  color: #fff;
}

.tab-button span {
  margin-left: 8px;
    margin-right: 8px;

  font-weight: bold;
  cursor: pointer;
}

.dark .tab-button span {
  color: green;
}

.tab-button {
    position: relative;
    padding-left: 25px;
    padding-right: 25px;
}

.tab-refresh, .tab-close {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    padding: 0 5px;
}

.tab-refresh {
    left: 5px;
}


.tab-close {
    right: 5px;
    color:red;
}

.tab-refresh:hover {
    color: #4CAF50;
}

.tab-close:hover {
    color: #f44336;
}

/* Theme Toggle Styles */
.theme-toggle-container {
    position: fixed;
    top: 12px;
    right: 12px;
    z-index: 1000;
}

.theme-toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.theme-icon {
    font-size: 16px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.theme-icon:hover {
    transform: scale(1.1);
}

.light-icon {
    color: #fbbf24;
}

.dark-icon {
    color: #60a5fa;
    opacity: 0;
}

input:checked ~ .dark-icon {
    opacity: 1;
}

input:checked ~ .light-icon {
    opacity: 0;
}

.mode-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background: #e9ecef;
    border-radius: 24px;
    cursor: pointer;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.mode-switch:hover {
    border-color: #adb5bd;
}

.dark .mode-switch {
    background: #475569;
    border-color: #64748b;
}

.dark .mode-switch:hover {
    border-color: #94a3b8;
}

.mode-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 2px;
    left: 2px;
    right: 2px;
    bottom: 2px;
    background: #ffffff;
    border-radius: 50px;
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.slider:before {
    position: absolute;
    content: "";
    height: 12px;
    width: 12px;
    left: 4px;
    bottom: 4px;
    background: #6c757d;
    border-radius: 50%;
    transition: all 0.3s ease;
}

input:checked + .slider {
    background: #7fb206;
    border-color: #7fb206;
}

input:checked + .slider:before {
    transform: translateX(16px);
    background: #ffffff;
}

.dark input:checked + .slider {
    background: #a8d633;
    border-color: #a8d633;
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
        script.src = 'side.js'; // Move all logic into sid.js
        document.body.appendChild(script);
      })
      .catch(error => console.error("Error loading sidebar:", error));
    
    
    </script>

<div class="main">
  <div class="tabs-wrapper">
    <div id="tabs" class="tabs-scroll">
    </div>
    <!-- Theme Toggle in top right -->
    <div class="theme-toggle-container">
      <div class="theme-toggle-wrapper">
        <i class="fas fa-sun theme-icon light-icon"></i>
        <label class="mode-switch">
          <input type="checkbox" id="themeToggle">
          <span class="slider"></span>
        </label>
        <i class="fas fa-moon theme-icon dark-icon"></i>
      </div>
    </div>
  </div>
  <div id="tab-contents"></div>
</div>


  <script>
    // Initialize with What's New tab on page load

    function navigateTo(pageId) {
        const tabId = `tab-${pageId}`;
        const contentId = `content-${pageId}`;

   if (!document.getElementById(tabId) && pageId !== 'whatsnew') {
    const tab = document.createElement('button');
    tab.id = tabId;
    tab.className = 'tab-button';
    tab.textContent = pageId.replace('.html', '');
    tab.onclick = () => showTab(pageId);

    // Create refresh button (left side) - green
    const refreshBtn = document.createElement('span');
    refreshBtn.innerHTML = '&#x21bb;'; // Refresh symbol
    refreshBtn.style.cssText = `
    
        color: #4CAF50;
    `;
    refreshBtn.onclick = (e) => {
        e.stopPropagation();
        refreshTab(pageId);
    };
    tab.insertBefore(refreshBtn, tab.firstChild);

    // Create close button (right side) - red
    const closeBtn = document.createElement('span');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
       
        color: #f44336;
    `;
    closeBtn.onclick = (e) => {
        e.stopPropagation();
        document.getElementById(tabId).remove();
        document.getElementById(contentId).remove();
    };
    tab.appendChild(closeBtn);

    // Style the tab to accommodate the buttons
    tab.style.cssText = `
        position: relative;
        padding-left: 25px;
        padding-right: 25px;
    `;

    document.getElementById('tabs').appendChild(tab);
}

        if (!document.getElementById(contentId)) {
            loadTabContent(pageId);
        } else {
            showTab(pageId);
        }
    }

    function loadTabContent(pageId) {
        const contentId = `content-${pageId}`;
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
    }

    function refreshTab(pageId) {
        const contentId = `content-${pageId}`;
        const content = document.getElementById(contentId);
        if (content) {
            content.innerHTML = ''; // Clear content while loading
            loadTabContent(pageId);
        }
    }

    function showTab(pageId) {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(div => div.style.display = 'none');

        const tab = document.getElementById(`tab-${pageId}`);
        const content = document.getElementById(`content-${pageId}`);
        
        if (tab) tab.classList.add('active');
        if (content) content.style.display = 'block';
    }

  // Theme handling moved to theme.js
  </script>
  <script src="theme.js"></script>

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












  <style>
    /* Chatbot styles - button moved to sidebar, keeping window styles */
    .chatbot-window {
      display: flex; flex-direction: column; width: 300px; height: 0; max-height: 700px;
      background-color: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); overflow: hidden;
      transition: height 0.3s ease;
    }
    .chatbot-window.open {
      height: 700px;
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
      max-height: 150px; overflow-y: auto; padding: 5px;
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
      background-color: #e5e5ea; align-self: flex-start; margin-right: auto; color: black;
    }
    .user-message {
      background-color: #4CAF50; color: white; align-self: flex-end; margin-left: auto;
    }
    #userInput {
      width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
      resize: none; font-family: inherit; font-size: 14px; height: 60px;
      background-color: white; color: black;
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

  <style>
    /* Position chatbot window - moved from floating button */
    .chatbot-window {
      position: fixed;
      top: 70px;
      right: 20px;
      z-index: 1000;
    }
  </style>


<!-- Enhanced Chatbot with Ticketing System (Window Only - Button moved to sidebar) -->
<div class="chatbot-window" id="chatbotWindow" style="display: flex;">
  <div class="chatbot-header">
    <span>BNM Support Ticket</span>
    <button class="close-btn" onclick="toggleChatbot()">×</button>
  </div>
  <div class="chatbot-messages" id="chatbotMessages"></div>
  <div class="chatbot-input">
    <div class="quick-options" id="quickOptions"></div>
    <textarea id="userInput" placeholder="Type your message here..." rows="2" disabled></textarea>
    <button id="sendButton" onclick="sendMessage()" disabled>Send</button>
  </div>
</div>

<!-- Articles modal with iframe -->
<div id="articlesModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
  <div style="background:#fff; width:90vw; max-width:1200px; height:80vh; border-radius:12px; overflow:hidden; position:relative;">
    <button id="articlesModalClose" style="position:absolute; right:12px; top:8px; z-index:10; background:none; border:none; font-size:22px;">&times;</button>
    <iframe id="articlesIframe" src="articles" style="width:100%; height:100%; border:none; display:block;" sandbox="allow-same-origin allow-scripts allow-forms"></iframe>
  </div>
</div>

<!-- Direct Articles popup (in-page) -->
<div id="articlesInlineModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2500; align-items:center; justify-content:center;">
  <div id="articlesInlineContent" style="background:#fff; width:92vw; max-width:1400px; max-height:92vh; border-radius:12px; overflow:hidden; position:relative; padding:0; display:flex; flex-direction:column; transition:width 220ms ease, max-width 220ms ease;">
    <button id="articlesInlineClose" style="position:absolute; right:12px; top:8px; z-index:40; background:none; border:none; font-size:22px;">&times;</button>
    <!-- Header: fixed controls -->
    <div id="articlesInlineHeader" style="flex:0 0 auto; padding:18px 22px; border-bottom:1px solid #e5e7eb; background:inherit; z-index:30;">
      <h2 style="margin:0 0 8px 0; font-size:18px;">Recherche Articles</h2>
      <div style="display:flex; gap:12px; align-items:flex-start;">
        <div style="flex:1; position:relative;">
          <label style="display:block; font-size:13px; color:inherit;">Produit</label>
          <input id="productInputMain" type="text" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; background:transparent; color:inherit;" placeholder="Entrer le nom du produit...">
          <div id="productSuggestionsMain" class="autocomplete-suggestions" style="display:none;"></div>
        </div>
        <div style="width:260px;">
          <label style="display:block; font-size:13px; color:inherit;">Magasin</label>
          <select id="magasinSelectMain" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; background:transparent; color:inherit;"></select>
        </div>
        <div style="width:140px; display:flex; align-items:end;">
          <button id="showEtatStockBtnMain" style="padding:8px 12px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer;">Voir</button>
        </div>
      </div>
    </div>
    <!-- Body: scrollable results area -->
    <div id="articlesInlineBody" style="flex:1 1 auto; overflow:auto; padding:16px 22px;">
      <div id="etatStockWrapperMain"></div>
    </div>
  </div>
</div>

<!-- Compact state: open popup narrower while loading / before data is shown -->
<style>
  #articlesInlineContent.articles-compact {
    width: 60vw !important;
    max-width: 900px !important;
  }
  /* Smaller inner padding in compact state to feel tighter */
  #articlesInlineContent.articles-compact #articlesInlineHeader { padding-left:12px !important; padding-right:12px !important; }
  #articlesInlineContent.articles-compact #articlesInlineBody { padding-left:12px !important; padding-right:12px !important; }
</style>

  <!-- Dark mode tweaks for inline articles modal -->
  <style>
    /* Support both body.dark-mode and html.dark class patterns used in the app */
    body.dark-mode #articlesInlineModal .modal-content,
    html.dark #articlesInlineModal .modal-content { }

    /* Make the inline modal content dark when dark-mode is active */
    body.dark-mode #articlesInlineModal > div,
    html.dark #articlesInlineModal > div {
      background: #1f2937 !important; /* gray-800 */
      color: #e5e7eb !important; /* gray-200 */
      border: 1px solid #374151 !important; /* gray-700 */
    }

    /* Inputs, selects, buttons */
    body.dark-mode #articlesInlineModal input,
    body.dark-mode #articlesInlineModal select,
    html.dark #articlesInlineModal input,
    html.dark #articlesInlineModal select {
      background: #111827 !important; /* gray-900 */
      color: #f9fafb !important;
      border: 1px solid #374151 !important;
    }
    body.dark-mode #articlesInlineModal input::placeholder,
    html.dark #articlesInlineModal input::placeholder {
      color: #9ca3af !important;
    }

    body.dark-mode #articlesInlineModal button,
    html.dark #articlesInlineModal button {
      color: inherit;
    }

    /* Suggestion dropdown */
    /* Light mode (default) */
    .autocomplete-suggestions {
      position: absolute;
      z-index: 99999;
      background: #ffffff !important;
      color: #111827 !important;
      border: 1px solid #d1d5db !important; /* gray-300 */
      border-radius: 6px;
      box-shadow: 0 6px 18px rgba(16,24,40,0.06);
      max-height: 260px;
      overflow-y: auto;
    }
    .autocomplete-suggestion {
      padding: 8px 10px;
      cursor: pointer;
      border-bottom: 1px solid rgba(0,0,0,0.04);
      color: inherit;
      background: transparent;
    }
    .autocomplete-suggestion:hover,
    .autocomplete-suggestion.active {
      background: #eef2ff; /* subtle light highlight */
      color: #0f172a;
    }

    /* Dark mode overrides */
    body.dark-mode .autocomplete-suggestions,
    html.dark .autocomplete-suggestions {
      background: #111827 !important;
      color: #f9fafb !important;
      border: 1px solid #374151 !important;
    }
    body.dark-mode .autocomplete-suggestion,
    html.dark .autocomplete-suggestion {
      color: #f9fafb;
      border-bottom: 1px solid rgba(255,255,255,0.03);
      background: transparent;
    }
    body.dark-mode .autocomplete-suggestion:hover,
    html.dark .autocomplete-suggestion:hover,
    body.dark-mode .autocomplete-suggestion.active,
    html.dark .autocomplete-suggestion.active {
      background: rgba(255,255,255,0.03);
      color: #fff;
    }
    /* Pagination controls inside suggestions (Prev/Next area) */
    body.dark-mode .autocomplete-suggestions .flex,
    html.dark .autocomplete-suggestions .flex,
    body.dark-mode .autocomplete-suggestions #suggPrevMain,
    body.dark-mode .autocomplete-suggestions #suggNextMain,
    html.dark .autocomplete-suggestions #suggPrevMain,
    html.dark .autocomplete-suggestions #suggNextMain {
      background: transparent !important;
      color: #f9fafb !important;
      border: none !important;
    }
    body.dark-mode .autocomplete-suggestions .text-sm,
    html.dark .autocomplete-suggestions .text-sm {
      color: #cbd5e1 !important; /* lighter text for page numbers */
    }
    body.dark-mode .autocomplete-suggestion:hover,
    html.dark .autocomplete-suggestion:hover,
    body.dark-mode .autocomplete-suggestion.active,
    html.dark .autocomplete-suggestion.active {
      background: #374151 !important;
    }

    /* Tables inside modal */
    body.dark-mode #articlesInlineModal table,
    html.dark #articlesInlineModal table {
      color: #e5e7eb !important;
    }
    body.dark-mode #articlesInlineModal th,
    body.dark-mode #articlesInlineModal td,
    html.dark #articlesInlineModal th,
    html.dark #articlesInlineModal td {
      border-color: #374151 !important;
    }

    /* Product details small vertical padding */
    #detailsMain {
      padding-top: 8px;
      padding-bottom: 8px;
    }

    /* Make the modal backdrop slightly lighter in dark mode for contrast */
    body.dark-mode #articlesInlineModal,
    html.dark #articlesInlineModal {
      background: rgba(0,0,0,0.65) !important;
    }

    /* Pagination controls (history/reserved) */
    body.dark-mode #articlesInlineModal .pageBtn,
    body.dark-mode #articlesInlineModal .resPageBtn,
    html.dark #articlesInlineModal .pageBtn,
    html.dark #articlesInlineModal .resPageBtn,
    body.dark-mode #articlesInlineModal #histPrev,
    body.dark-mode #articlesInlineModal #histNext,
    body.dark-mode #articlesInlineModal #histFirst,
    body.dark-mode #articlesInlineModal #histLast,
    html.dark #articlesInlineModal #histPrev,
    html.dark #articlesInlineModal #histNext,
    html.dark #articlesInlineModal #histFirst,
    html.dark #articlesInlineModal #histLast {
      background: #111827 !important;
      color: #f9fafb !important;
      border: 1px solid #374151 !important;
      box-shadow: none !important;
    }
    body.dark-mode #articlesInlineModal .pageBtn[disabled],
    body.dark-mode #articlesInlineModal .resPageBtn[disabled],
    html.dark #articlesInlineModal .pageBtn[disabled],
    html.dark #articlesInlineModal .resPageBtn[disabled] {
      opacity: 0.5 !important;
      pointer-events: none !important;
    }
    body.dark-mode #articlesInlineModal .pageBtn:hover,
    body.dark-mode #articlesInlineModal .resPageBtn:hover,
    html.dark #articlesInlineModal .pageBtn:hover,
    html.dark #articlesInlineModal .resPageBtn:hover {
      background: #374151 !important;
    }

    /* Adjust the primary action button in modal */
    body.dark-mode #showEtatStockBtnMain,
    html.dark #showEtatStockBtnMain {
      background: #2563eb; color: #fff; border: none;
    }
  </style>

  <style>
    /* Professional action/button styles for the Articles popup */
    .articles-action-btn, .showLotsMainBtn, .showReservedMainBtn, .showHistoryMainBtn {
      display: inline-block;
      padding: 6px 10px;
      margin: 0 4px 0 0;
      font-size: 0.88rem;
      line-height: 1;
      border-radius: 6px;
      border: 1px solid #d1d5db; /* neutral gray border */
      background: #f3f4f6; /* light gray background */
      color: #111827; /* dark text */
      cursor: pointer;
      transition: background 120ms ease, border-color 120ms ease, transform 80ms ease;
      box-shadow: none;
    }
    .articles-action-btn:hover, .showLotsMainBtn:hover, .showReservedMainBtn:hover, .showHistoryMainBtn:hover {
      background: #e5e7eb;
      border-color: #cbd5e1;
      transform: translateY(-1px);
    }
    .articles-action-btn:active, .showLotsMainBtn:active, .showReservedMainBtn:active, .showHistoryMainBtn:active {
      transform: translateY(0);
    }

    /* Pagination buttons */
    .pageBtn, .resPageBtn, #histPrev, #histNext, #histFirst, #histLast, #resPrev, #resNext, #resFirst, #resLast {
      padding: 6px 10px !important;
      border-radius: 6px !important;
      border: 1px solid #d1d5db !important;
      background: #ffffff !important;
      color: #111827 !important;
      box-shadow: none !important;
      cursor: pointer !important;
      transition: background 120ms ease, border-color 120ms ease, transform 80ms ease;
    }
    .pageBtn:hover, .resPageBtn:hover, #histPrev:hover, #histNext:hover, #histFirst:hover, #histLast:hover, #resPrev:hover, #resNext:hover, #resFirst:hover, #resLast:hover { background:#f3f4f6 !important; border-color:#cbd5e1 !important; transform: translateY(-1px); }
    .pageBtn[disabled], .resPageBtn[disabled] { opacity:0.5; pointer-events:none; }

    /* Active page button */
    .pageBtn[aria-current="true"], .pageBtn.active, .resPageBtn.active { background:#2563eb !important; color:#fff !important; border-color:#2563eb !important; }

    /* Dark mode overrides */
    body.dark-mode .articles-action-btn, html.dark .articles-action-btn,
    body.dark-mode .showLotsMainBtn, html.dark .showLotsMainBtn,
    body.dark-mode .showReservedMainBtn, html.dark .showReservedMainBtn,
    body.dark-mode .showHistoryMainBtn, html.dark .showHistoryMainBtn {
      background: #111827; color:#f9fafb; border:1px solid #374151;
    }
    body.dark-mode .articles-action-btn:hover, html.dark .articles-action-btn:hover,
    body.dark-mode .showLotsMainBtn:hover, html.dark .showLotsMainBtn:hover {
      background: #1f2937; border-color:#4b5563;
    }
    body.dark-mode .pageBtn, html.dark .pageBtn, body.dark-mode .resPageBtn, html.dark .resPageBtn { background:#111827 !important; color:#f9fafb !important; border:1px solid #374151 !important; }
    body.dark-mode .pageBtn:hover, html.dark .pageBtn:hover, body.dark-mode .resPageBtn:hover, html.dark .resPageBtn:hover { background:#1f2937 !important; }
  </style>

  <style>
    /* Global base size slightly reduced for denser layout */
    html, body {
      font-size: 14px !important;
    }
    /* On smaller screens, make it a bit smaller */
    @media (max-width: 1024px) {
      html, body { font-size: 13px !important; }
    }

    /* Make the inline articles modal and its children more compact */
    #articlesInlineModal, #articlesInlineModal * {
      font-size: 0.9rem !important;
    }

    #articlesInlineModal label { font-size: 12px !important; }
    #articlesInlineModal h2 { font-size: 16px !important; margin-bottom:4px; }

    /* Tighter controls in the header */
    #articlesInlineHeader input,
    #articlesInlineHeader select,
    #articlesInlineHeader button {
      padding: 6px 8px !important;
      font-size: 0.9rem !important;
      line-height: 1.1 !important;
    }

    /* Suggestion list compactness */
    .autocomplete-suggestions { font-size: 0.9rem !important; max-height: 220px !important; }
    .autocomplete-suggestion { padding: 6px 8px !important; }

    /* Tables in results: tighter cells */
    #etatStockWrapperMain table th,
    #etatStockWrapperMain table td {
      font-size: 0.85rem !important;
      padding: 6px 8px !important;
    }
    #etatStockWrapperMain table { font-size: 0.9rem !important; }

    /* Pagination and small buttons */
    .pageBtn, .resPageBtn, #histPrev, #histNext, #histFirst, #histLast {
      padding: 6px 8px !important;
      font-size: 0.85rem !important;
    }

    /* Floating Articles button slightly smaller */
    #articlesFloatingBtn { width: 48px !important; height: 48px !important; border-radius: 10px !important; }
    #articlesFloatingBtn svg { width: 18px !important; height: 18px !important; }

    /* Tabs text slightly smaller */
    .tab-button span { font-size: 13px !important; }

    /* Ensure inputs across app are slightly smaller for compactness */
    input[type="text"], select, textarea {
      font-size: 0.95rem !important;
    }
  </style>

<script>
let currentState = 'initial';
let selectedPage = '';
let ticketType = '';
let ticketPriority = 'medium';

function toggleChatbot() {
  const chatbotWindow = document.getElementById('chatbotWindow');
  
  // Toggle using class instead of direct style manipulation
  chatbotWindow.classList.toggle('open');
  
  // Force display block when opening
  if (chatbotWindow.classList.contains('open')) {
    chatbotWindow.style.display = 'block';
    if (currentState === 'initial') showInitialMessage();
  } else {
    chatbotWindow.style.display = 'none';
  }
}

function showInitialMessage() {
  const messagesDiv = document.getElementById('chatbotMessages');
  messagesDiv.innerHTML = '';
  addBotMessage("Hi! I'm BNM Support Bot. How can I help you today?");
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = `
    <div style="color:black" class="quick-option" onclick="selectOption('report_bug')">🐛 Report a Bug</div>
    <div style="color:black" class="quick-option" onclick="selectOption('feature_request')">💡 Request a Feature</div>
    <div style="color:black" class="quick-option" onclick="selectOption('technical_issue')">🔧 Technical Issue</div>
    <div style="color:black" class="quick-option" onclick="selectOption('general_question')">❓ General Question</div>
  `;
}

function selectOption(option) {
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = '';
  document.getElementById('userInput').disabled = true;
  document.getElementById('sendButton').disabled = true;

  ticketType = option;
  
  if (option === 'report_bug') {
    currentState = 'select_priority';
    addBotMessage("Please select the priority level for this bug:");
    setTimeout(() => {
      quickOptions.innerHTML = `
        <div class="quick-option" onclick="selectPriority('high')">🔴 Critical (System Down)</div>
        <div class="quick-option" onclick="selectPriority('medium')">🟡 Medium (Major Issue)</div>
        <div class="quick-option" onclick="selectPriority('low')">🟢 Low (Minor Issue)</div>
      `;
    }, 1000);
  } else if (option === 'feature_request') {
    currentState = 'describe_feature';
    addBotMessage("Please describe the feature you'd like to request:");
    setTimeout(() => {
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }, 1500);
  } else if (option === 'technical_issue') {
    currentState = 'select_priority';
    addBotMessage("Please select the priority level for this technical issue:");
    setTimeout(() => {
      quickOptions.innerHTML = `
        <div class="quick-option" onclick="selectPriority('high')">🔴 High (Cannot Work)</div>
        <div class="quick-option" onclick="selectPriority('medium')">🟡 Medium (Workaround Available)</div>
        <div class="quick-option" onclick="selectPriority('low')">🟢 Low (Minor Inconvenience)</div>
      `;
    }, 1000);
  } else if (option === 'general_question') {
    currentState = 'ask_question';
    addBotMessage("Please type your question below:");
    setTimeout(() => {
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }, 1500);
  }
}
function selectPriority(priority) {
  if (ticketType === 'technical_issue') {
    ticketPriority = priority;
    currentState = 'describe_issue';
    addBotMessage(`Priority set to: ${priority}. Please describe the technical issue:`);
     const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = '';
  setTimeout(() => {
      document.getElementById('userInput').disabled = false;
      document.getElementById('sendButton').disabled = false;
    }, 1500);
  } else {
  ticketPriority = priority;
  currentState = 'select_page';
  addBotMessage(`Priority set to: ${priority}. Please select the related page:`);
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = '';
  
  setTimeout(() => {
    quickOptions.innerHTML = `
      <div class="quick-option" onclick="selectPage('Main')">🏠 Accueil</div>
      <div class="quick-option" onclick="selectPage('mony')">📈 FONDS Analysis</div>
      <div class="quick-option" onclick="selectPage('print')">🖨️ Print</div>
      <div class="quick-option" onclick="selectPage('bank')">🏦 Banks</div>
      <div class="quick-option" onclick="selectPage('recouverement')">💰 Recouvrement</div>
      <div class="quick-option" onclick="selectPage('charge')">💳 Charges</div>
      <div class="quick-option" onclick="selectPage('DETTE_F')">💸 DETTE_F</div>
      <div class="quick-option" onclick="selectPage('ETAT_F')">📊 ETAT_F</div>
      <div class="quick-option" onclick="selectPage('ETAT_F_CUMULE')">📈 ETAT_F_CUMULE</div>
      <div class="quick-option" onclick="selectPage('Etatstock')">📦 État de Stock</div>
      <div class="quick-option" onclick="selectPage('Mouvement_Stock')">🔄 Mouvement Stock</div>
      <div class="quick-option" onclick="selectPage('Product')">🛍️ Marge</div>
      <div class="quick-option" onclick="selectPage('Rotation')">🔄 Rotation</div>
      <div class="quick-option" onclick="selectPage('Quota')">🎯 Quota Produit</div>
      <div class="quick-option" onclick="selectPage('Recap_Achat')">🛒 Recap Achat</div>
      <div class="quick-option" onclick="selectPage('recap_achat_facturation')">🧾 Recap Achat F</div>
      <div class="quick-option" onclick="selectPage('Annual_Recap_A')">📊 Annual Recap A</div>
      <div class="quick-option" onclick="selectPage('rot_men_achat')">📅 rot_men_achat</div>
      <div class="quick-option" onclick="selectPage('Recap_Vente')">💰 Recap Vente</div>
      <div class="quick-option" onclick="selectPage('portf')">👥 Client Portfeuille</div>
      <div class="quick-option" onclick="selectPage('Recap_Vente_Facturation')">🧾 Recap Vente F</div>
      <div class="quick-option" onclick="selectPage('Annual_Recap_V')">📊 Annual Recap V</div>
      <div class="quick-option" onclick="selectPage('CONFIRMED_ORDERS')">✅ Confirm Order</div>
      <div class="quick-option" onclick="selectPage('rot_men_vente')">📅 rot_men_vente</div>
      <div class="quick-option" onclick="selectPage('simuler')">🧮 Simulation</div>
      <div class="quick-option" onclick="selectPage('retour')">↩️ retour ORM</div>
      <div class="quick-option" onclick="selectPage('inv')">➕ Create Inventory</div>
      <div class="quick-option" onclick="selectPage('inv_admin')">⚙️ Manage Inventory</div>
      <div class="quick-option" onclick="selectPage('inv_saisie')">✏️ Saisie Inventory</div>
      <div class="quick-option" onclick="selectPage('Journal_Vente')">📝 Journal de Vente</div>
      <div class="quick-option" onclick="selectPage('AFFECTATION')">📋 Affectation</div>
      <div class="quick-option" onclick="selectPage('other')">🔗 Other/General</div>
    `;
    document.getElementById('userInput').disabled = false;
    document.getElementById('sendButton').disabled = false;
  }, 1500);
  }
}
function selectPage(page) {
  selectedPage = page;
  
  if (ticketType === 'report_bug' || ticketType === 'technical_issue') {
    currentState = 'describe_issue';
    addBotMessage(`You selected: ${page}. Please describe the issue in detail:`);
  } else {
    currentState = 'describe_request';
    addBotMessage(`You selected: ${page}. Please provide more details:`);
  }
  
  const quickOptions = document.getElementById('quickOptions');
  quickOptions.innerHTML = '';
}

function sendMessage() {
  const userInput = document.getElementById('userInput');
  const message = userInput.value.trim();
  if (!message) return;

  addUserMessage(message);
  userInput.value = '';

  let feedbackData = {
    content: message,
    type: 'ticket',
    page: selectedPage,
    ticket_type: ticketType,
    priority: ticketPriority,
    status: 'open'
  };

  if (currentState === 'describe_issue' || currentState === 'describe_request' || 
      currentState === 'describe_feature' || currentState === 'ask_question') {
    
    sendTicket(feedbackData, "Thank you! Your ticket has been submitted. Ticket ID: #" + generateTicketId());
  }
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


function generateTicketId() {
  return 'T' + Date.now().toString().slice(-6);
}

function sendTicket(data, successMessage) {

  
  // Save to database
  fetch('save_feedback.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      addBotMessage(successMessage);
      setTimeout(() => {
        currentState = 'initial';
        showInitialMessage();
        document.getElementById('userInput').disabled = true;
        document.getElementById('sendButton').disabled = true;
      }, 2000);
    } else {
      throw new Error(result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    addBotMessage("There was an issue submitting your ticket. Please try again.");
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
</script>

<!-- Inventory Pending Notification Popup (Admin/Developer) -->
<div id="invPendingNotification" style="display:none; position:fixed; bottom:30px; right:30px; z-index:9999; min-width:320px; max-width:400px; background:#fff; color:#222; border-radius:10px; box-shadow:0 4px 16px #0002; padding:20px 24px 16px 24px; border-left:6px solid #f59e42;">
  <div style="display:flex; justify-content:space-between; align-items:center;">
    <span style="font-weight:bold; color:#f59e42; font-size:18px;">Inventory Alert</span>
    <button onclick="closeInvPendingNotification()" style="background:none; border:none; font-size:20px; color:#888; cursor:pointer;">&times;</button>
  </div>
  <div id="invPendingNotificationBody" onclick="gotoInvAdmin()" style="margin-top:10px; cursor:pointer;">
    <span>⚠️ You have untreated inventory. Please check it.</span>
    <div id="invPendingCount" style="font-size:13px; color:#666; margin-top:4px;"></div>
  </div>
</div>

<!-- Inventory Saisie Notification Popup (Saisie role) -->
<div id="invSaisieNotification" style="display:none; position:fixed; bottom:30px; right:30px; z-index:9999; min-width:320px; max-width:400px; background:#fff; color:#222; border-radius:10px; box-shadow:0 4px 16px #0002; padding:20px 24px 16px 24px; border-left:6px solid #3b82f6;">
  <div style="display:flex; justify-content:space-between; align-items:center;">
    <span style="font-weight:bold; color:#3b82f6; font-size:18px;">Inventory To Do</span>
    <button onclick="closeInvSaisieNotification()" style="background:none; border:none; font-size:20px; color:#888; cursor:pointer;">&times;</button>
  </div>
  <div id="invSaisieNotificationBody" onclick="gotoInvSaisie()" style="margin-top:10px; cursor:pointer;">
    <span>📋 You have an inventory to do.</span>
    <div id="invSaisieCount" style="font-size:13px; color:#666; margin-top:4px;"></div>
  </div>
</div>

<?php
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Admin', 'Developer', 'DRH'])) :
?>
<script>
// Inventory Pending Notification Logic (Admins/Developers)
function checkPendingInventory() {
  try {
    const url = (typeof API_CONFIGinv !== 'undefined' && API_CONFIGinv && typeof API_CONFIGinv.getApiUrl === 'function')
      ? API_CONFIGinv.getApiUrl('/inventory/pending_count')
      : '/inventory/pending_count';

    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.success && data.pending_count > 0) {
          showInvPendingNotification(data.pending_count);
        } else {
          closeInvPendingNotification();
        }
      })
      .catch(() => { /* ignore errors */ });
  } catch (e) {
    fetch('/inventory/pending_count')
      .then(res => res.json())
      .then(data => {
        if (data.success && data.pending_count > 0) {
          showInvPendingNotification(data.pending_count);
        } else {
          closeInvPendingNotification();
        }
      })
      .catch(() => { /* ignore errors */ });
  }
}

function showInvPendingNotification(count) {
  const notif = document.getElementById('invPendingNotification');
  notif.style.display = 'block';
  document.getElementById('invPendingCount').textContent = `Pending inventories: ${count}`;
}

function closeInvPendingNotification() {
  document.getElementById('invPendingNotification').style.display = 'none';
}

function gotoInvAdmin() {
  // Try to trigger the sidebar button for Manage Inventory
  const btns = document.querySelectorAll('button');
  for (let btn of btns) {
    if (btn.innerText && btn.innerText.replace(/\s+/g, '').toLowerCase().includes('manageinventory')) {
      btn.click();
      closeInvPendingNotification();
      return;
    }
  }
  // Fallback: direct navigation
  if (typeof navigateTo === 'function') {
    navigateTo('inv_admin');
    closeInvPendingNotification();
  } else {
    window.location.href = 'inventory/inv_admin';
  }
}

// Start polling every 5 minutes
setInterval(checkPendingInventory, 5 * 60 * 1000);
// Also check on page load
checkPendingInventory();
</script>
<?php endif; ?>

<?php if (isset($_SESSION['Role']) && strtolower($_SESSION['Role']) === 'saisie') : ?>
<script>
// Inventory Saisie Notification Logic (Saisie role)
function checkSaisieInventory() {
  try {
    const url = (typeof API_CONFIGinv !== 'undefined' && API_CONFIGinv && typeof API_CONFIGinv.getApiUrl === 'function')
      ? API_CONFIGinv.getApiUrl('/inventory/confirmed_casse_count')
      : '/inventory/confirmed_casse_count';

    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.success && data.confirmed_casse_count > 0) {
          showInvSaisieNotification(data.confirmed_casse_count);
        } else {
          closeInvSaisieNotification();
        }
      })
      .catch(() => { /* ignore errors */ });
  } catch (e) {
    fetch('/inventory/confirmed_casse_count')
      .then(res => res.json())
      .then(data => {
        if (data.success && data.confirmed_casse_count > 0) {
          showInvSaisieNotification(data.confirmed_casse_count);
        } else {
          closeInvSaisieNotification();
        }
      })
      .catch(() => { /* ignore errors */ });
  }
}

function showInvSaisieNotification(count) {
  const notif = document.getElementById('invSaisieNotification');
  notif.style.display = 'block';
  document.getElementById('invSaisieCount').textContent = `Inventories to do: ${count}`;
}

function closeInvSaisieNotification() {
  document.getElementById('invSaisieNotification').style.display = 'none';
}

function gotoInvSaisie() {
  // Try to trigger the sidebar button for Saisie Inventory
  const btns = document.querySelectorAll('button');
  for (let btn of btns) {
    if (btn.innerText && btn.innerText.replace(/\s+/g, '').toLowerCase().includes('saisieinventory')) {
      btn.click();
      closeInvSaisieNotification();
      return;
    }
  }
  // Fallback: direct navigation
  if (typeof navigateTo === 'function') {
    navigateTo('inv_saisie');
    closeInvSaisieNotification();
  } else {
    window.location.href = 'inventory/inv_saisie';
  }
}

// Start polling every 5 minutes
setInterval(checkSaisieInventory, 5 * 60 * 1000);
// Also check on page load
checkSaisieInventory();
</script>
<?php endif; ?>
<!-- Session Timeout Handler -->
<script>
// Session timeout handling
let sessionTimeout = <?php echo $inactive_time * 1000; ?>; // Convert to milliseconds
let lastActivityTime = Date.now();
let sessionCheckInterval;

// Update last activity time on user interactions
function updateActivity() {
    lastActivityTime = Date.now();
}

// Add event listeners for user activity
document.addEventListener('mousemove', updateActivity);
document.addEventListener('keypress', updateActivity);
document.addEventListener('click', updateActivity);
document.addEventListener('scroll', updateActivity);
document.addEventListener('touchstart', updateActivity);
document.addEventListener('focus', updateActivity);

// Check session status every 30 seconds
sessionCheckInterval = setInterval(function() {
    let timeSinceLastActivity = Date.now() - lastActivityTime;
    
    // If user has been inactive for more than the timeout period
    if (timeSinceLastActivity >= sessionTimeout) {
        // Clear the interval
        clearInterval(sessionCheckInterval);
        
        // Redirect to login page immediately without warning
        window.location.href = 'BNM?session_expired=1';
        return;
    }
    
    // Check with server every 2 minutes to ensure session is still valid
    if (Math.floor(timeSinceLastActivity / 120000) !== Math.floor((timeSinceLastActivity - 30000) / 120000)) {
        fetch(window.location.href, {
            method: 'HEAD',
            cache: 'no-cache'
        }).then(response => {
            if (response.redirected && response.url.includes('BNM')) {
                // Session expired on server side
                clearInterval(sessionCheckInterval);
                window.location.href = 'BNM?session_expired=1';
            }
        }).catch(error => {
            console.log('Session check failed:', error);
        });
    }
}, 30000); // Check every 30 seconds

// Handle page visibility changes (when user switches tabs or minimizes browser)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Page became visible, check if session is still valid
        let timeSinceLastActivity = Date.now() - lastActivityTime;
        if (timeSinceLastActivity >= sessionTimeout) {
            clearInterval(sessionCheckInterval);
            alert('Your session has expired due to inactivity. You will be redirected to the login page.');
            window.location.href = 'BNM?session_expired=1';
        }
    }
});

// Handle beforeunload to clean up
window.addEventListener('beforeunload', function() {
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval);
    }
});

// Theme Toggle Logic
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    if (themeToggle) {
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            themeToggle.checked = true;
        }

        // Update icon visibility based on initial state
        updateThemeIcons(themeToggle.checked);

        // Theme toggle function
        function toggleTheme() {
            const isDark = this.checked;
            updateThemeIcons(isDark);

            if (isDark) {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        }

        function updateThemeIcons(isDark) {
            const lightIcon = document.querySelector('.light-icon');
            const darkIcon = document.querySelector('.dark-icon');

            if (lightIcon && darkIcon) {
                if (isDark) {
                    lightIcon.style.opacity = '0';
                    darkIcon.style.opacity = '1';
                } else {
                    lightIcon.style.opacity = '1';
                    darkIcon.style.opacity = '0';
                }
            }
        }

        themeToggle.addEventListener('change', toggleTheme);
    }
});
</script>

  <script>
  // Articles inline modal logic — can be triggered from sidebar button
  const articlesInlineModal = document.getElementById('articlesInlineModal');
  const articlesInlineClose = document.getElementById('articlesInlineClose');

  const productInputMain = document.getElementById('productInputMain');
  const productSuggestionsMain = document.getElementById('productSuggestionsMain');
  const magasinSelectMain = document.getElementById('magasinSelectMain');
  const showEtatStockBtnMain = document.getElementById('showEtatStockBtnMain');
  const etatStockWrapperMain = document.getElementById('etatStockWrapperMain');

  let productListMain = [];
  let suggestionPageMain = 1;
  const SUG_PER_PAGE_MAIN = 6;
  let latestMatchesMain = [];
  let latestTotalPagesMain = 1;
  let lastRenderedProductMain = '';

  // portal suggestions to body so they overlay modal
  try {
    if (productSuggestionsMain && productSuggestionsMain.parentNode !== document.body) {
      document.body.appendChild(productSuggestionsMain);
      productSuggestionsMain.style.position = 'absolute';
      productSuggestionsMain.style.zIndex = '99999';
      productSuggestionsMain.style.display = 'none';
    }
  } catch (e) { console.warn('portal suggestions main failed', e); }

  // Floating button: add brief pulse on load and open modal handler
  try {
    if (articlesBtn) {
      // Add pulse class for a short time to draw attention
      articlesBtn.classList.add('pulse');
      setTimeout(()=>{ articlesBtn.classList.remove('pulse'); }, 4000);

      articlesBtn.addEventListener('click', (ev)=>{
        try {
          // toggle inline modal
          if (articlesInlineModal) {
            const isOpen = window.getComputedStyle(articlesInlineModal).display !== 'none';
            articlesInlineModal.style.display = isOpen ? 'none' : 'flex';
            if (!isOpen) {
              // focus input for quick search
              setTimeout(()=>{ try{ productInputMain && productInputMain.focus(); } catch(e){} }, 120);
            }
          }
        } catch(e){ console.warn('articlesBtn click failed', e); }
      });
    }
  } catch(e){}

  function positionSuggestionsMain() {
    if (!productInputMain || !productSuggestionsMain || productSuggestionsMain.style.display === 'none') return;
    const r = productInputMain.getBoundingClientRect();
    const left = r.left + window.scrollX;
    const belowTop = r.bottom + window.scrollY;
    productSuggestionsMain.style.left = left + 'px';
    productSuggestionsMain.style.top = belowTop + 'px';
    productSuggestionsMain.style.width = Math.max(220, r.width) + 'px';
  }
  window.addEventListener('resize', positionSuggestionsMain);
  window.addEventListener('scroll', positionSuggestionsMain, true);

  async function fetchMagasinsMain() {
    magasinSelectMain.disabled = true;
    magasinSelectMain.innerHTML = '<option>Chargement...</option>';
    try {
      const resp = await fetch(API_CONFIG.getApiUrl('/fetch-magasins'));
      if (!resp.ok) throw new Error('Erreur');
      let data = await resp.json();
      if (data && !Array.isArray(data) && typeof data === 'object') {
        for (const k in data) if (Array.isArray(data[k])) { data = data[k]; break; }
      }
      magasinSelectMain.innerHTML = '<option value="">Choisir un magasin</option>';
      if (Array.isArray(data) && data.length) {
        data.forEach(m => {
          const opt = document.createElement('option');
          if (typeof m === 'string') { opt.value = m; opt.textContent = m; }
          else if (typeof m === 'object' && m !== null) {
            const keys = Object.keys(m);
            let label = '';
            const tryKeys = ['MAGASIN','magasin','LABEL','label','NOM','nom','CODE','code','ID','id','name','NAME'];
            for (const k of tryKeys) { if (k in m && (typeof m[k] === 'string' || typeof m[k] === 'number')) { label = String(m[k]); break; } }
            if (!label) for (const k of keys) { if (typeof m[k] === 'string' || typeof m[k] === 'number') { label = String(m[k]); break; } }
            if (!label) label = JSON.stringify(m);
            // If the server provided M_WAREHOUSE_ID, use it as the option value and keep label as text
            // Prefer keeping option.value as the readable label so server-side filters by name continue to work.
            // Store the numeric warehouse id in a data attribute when available so we can send it to other endpoints.
            let warehouseId = null;
            if ('M_WAREHOUSE_ID' in m) warehouseId = m['M_WAREHOUSE_ID'];
            else if ('m_warehouse_id' in m) warehouseId = m['m_warehouse_id'];
            else if ('M_WAREHOUSEId' in m) warehouseId = m['M_WAREHOUSEId'];
            else if ('ID' in m) warehouseId = m['ID'];
            else if ('id' in m) warehouseId = m['id'];
            opt.value = label;
            if (warehouseId !== null && warehouseId !== undefined) opt.dataset.warehouseId = String(warehouseId);
            opt.textContent = label;
          }
          magasinSelectMain.appendChild(opt);
        });
        // try set default: prefer exact '1-Depot Principal' or value '1', otherwise fallback to 'depot principal' heuristic
        const opts = Array.from(magasinSelectMain.options).map((o,i) => ({ index:i, value: String(o.value||'').trim(), text: String(o.textContent||'').trim() }));
        console.debug('fetchMagasinsMain: magasin options', opts);
        const normalize = (s) => {
          if (!s) return '';
          // remove diacritics
          const from = 'ÀÁÂÃÄÅàáâãäåĀāÉÈÊËéèêëĒēÍÌÎÏíìîïĪīÓÒÔÕÖóòôõöŌōÙÚÛÜùúûüŪūÇçÑñ';
          const to   = 'AAAAAAaaaaaaAaEEEEeeeeEeIIIIiiiiIoOOOOOoooooOoUUUUuuuuUuCcNn';
          let out = String(s).normalize('NFKD').replace(/\p{Diacritic}/gu, '');
          out = out.replace(/[^a-zA-Z0-9\-\s]/g, '');
          out = out.toLowerCase().replace(/[\s\-_.]+/g, ' ').trim();
          return out;
        };
        // find by normalized '1 depot principal' or value '1'
        let foundIdx = opts.findIndex(o => {
          const nv = normalize(o.value);
          const nt = normalize(o.text);
          if (nv === '1' || nv === '1 depot principal' || nt === '1 depot principal') return true;
          return false;
        });
        if (foundIdx === -1) {
          // fallback: contains both depot and principal in normalized form
          foundIdx = opts.findIndex(o => {
            const combined = normalize((o.value||'') + ' ' + (o.text||''));
            return combined.includes('depot') && combined.includes('principal');
          });
        }
        if (foundIdx > -1) {
          console.debug('fetchMagasinsMain: selecting magasin index', foundIdx, 'option', opts[foundIdx]);
          magasinSelectMain.selectedIndex = foundIdx; // set by index to be robust
        } else {
          console.debug('fetchMagasinsMain: preferred magasin not found, leaving default');
        }
        magasinSelectMain.disabled = false;
      } else {
        magasinSelectMain.innerHTML = '<option value="">Aucun magasin</option>';
        magasinSelectMain.disabled = true;
      }
    } catch (e) {
      console.warn('fetchMagasinsMain failed', e);
      magasinSelectMain.innerHTML = '<option value="">Erreur</option>';
      magasinSelectMain.disabled = true;
    }
  }

  async function fetchProductListMain() {
    try {
      const resp = await fetch(API_CONFIG.getApiUrl('/fetch-rotation-product-data'));
      const data = await resp.json();
      if (Array.isArray(data)) productListMain = data;
      else if (data && Array.isArray(data.products)) productListMain = data.products;
      else productListMain = [];
    } catch (e) { productListMain = []; }
  }

  productInputMain.addEventListener('input', function() {
    const rawVal = this.value || '';
    const val = rawVal.trim().toLowerCase();
    // If product input cleared, wipe displayed tables/details and remove saved search
    if (!val) {
      try {
        productSuggestionsMain.innerHTML = '';
        productSuggestionsMain.style.display = 'none';
        etatStockWrapperMain.innerHTML = '';
        const details = document.getElementById('detailsMain'); if (details) details.innerHTML = '';
        // remove saved last search so popup won't auto-restore
        try { localStorage.removeItem('articles_last_search'); } catch(e) {}
      } catch(e) { console.warn('clear on empty product failed', e); }
      return;
    }
    // If user typed a different product than the one last rendered, clear displayed results so they don't show old product
    try {
      if (lastRenderedProductMain && val !== String(lastRenderedProductMain).trim().toLowerCase()) {
        etatStockWrapperMain.innerHTML = '';
        const details = document.getElementById('detailsMain'); if (details) details.innerHTML = '';
        try { localStorage.removeItem('articles_last_search'); } catch(e) {}
        // do not update lastRenderedProductMain here; it will be set when a search runs
      }
    } catch(e) { console.warn('sync clear failed', e); }
    if (!val || !productListMain.length) { productSuggestionsMain.style.display = 'none'; return; }
    const matches = productListMain.filter(p => {
      let name = p.NAME || p.name || p.LABEL || p.label || p.PRODUCT || p.product || '';
      return name.toLowerCase().includes(val);
    });
    suggestionPageMain = 1;
    const totalPages = Math.max(1, Math.ceil(matches.length / SUG_PER_PAGE_MAIN));
    latestMatchesMain = matches; latestTotalPagesMain = totalPages;
    const start = (suggestionPageMain - 1) * SUG_PER_PAGE_MAIN;
    const pageItems = matches.slice(start, start + SUG_PER_PAGE_MAIN);
    const makeHtml = (items) => {
      let html = items.map((p,i) => { let name = p.NAME || p.name || p.LABEL || p.label || p.PRODUCT || p.product || ''; return `<div class="autocomplete-suggestion${i===0?' active':''}" data-name="${(name+'').replace(/"/g,'&quot;')}">${name}</div>`; }).join('');
      if (totalPages > 1) html += `<div class="flex justify-between items-center p-2 border-t mt-2 bg-gray-50"><button id="suggPrevMain" class="px-2 py-1 text-sm">Prev</button><span class="text-sm">${suggestionPageMain}/${totalPages}</span><button id="suggNextMain" class="px-2 py-1 text-sm">Next</button></div>`;
      return html;
    };
    if (!matches.length) { productSuggestionsMain.style.display = 'none'; return; }
    productSuggestionsMain.innerHTML = makeHtml(pageItems);
    productSuggestionsMain.style.display = 'block';
    setTimeout(positionSuggestionsMain, 0);
  });

  // delegated handler for main suggestions
  productSuggestionsMain.addEventListener('mousedown', function(e) {
    const t = e.target;
    if (t && t.id === 'suggPrevMain') {
      e.preventDefault(); if (suggestionPageMain > 1) { suggestionPageMain--; const start2 = (suggestionPageMain-1)*SUG_PER_PAGE_MAIN; const pageItems2 = latestMatchesMain.slice(start2, start2+SUG_PER_PAGE_MAIN); productSuggestionsMain.innerHTML = pageItems2.map((p,i)=>{let name=p.NAME||p.name||p.LABEL||p.label||p.PRODUCT||p.product||''; return `<div class="autocomplete-suggestion${i===0?' active':''}" data-name="${(name+'').replace(/"/g,'&quot;')}">${name}</div>`;}).join('') + `<div class="flex justify-between items-center p-2 border-t mt-2 bg-gray-50"><button id="suggPrevMain" class="px-2 py-1 text-sm">Prev</button><span class="text-sm">${suggestionPageMain}/${latestTotalPagesMain}</span><button id="suggNextMain" class="px-2 py-1 text-sm">Next</button></div>`; positionSuggestionsMain(); }
      return;
    }
    if (t && t.id === 'suggNextMain') {
      e.preventDefault(); if (suggestionPageMain < latestTotalPagesMain) { suggestionPageMain++; const start2=(suggestionPageMain-1)*SUG_PER_PAGE_MAIN; const pageItems2 = latestMatchesMain.slice(start2, start2+SUG_PER_PAGE_MAIN); productSuggestionsMain.innerHTML = pageItems2.map((p,i)=>{let name=p.NAME||p.name||p.LABEL||p.label||p.PRODUCT||p.product||''; return `<div class="autocomplete-suggestion${i===0?' active':''}" data-name="${(name+'').replace(/"/g,'&quot;')}">${name}</div>`;}).join('') + `<div class="flex justify-between items-center p-2 border-t mt-2 bg-gray-50"><button id="suggPrevMain" class="px-2 py-1 text-sm">Prev</button><span class="text-sm">${suggestionPageMain}/${latestTotalPagesMain}</span><button id="suggNextMain" class="px-2 py-1 text-sm">Next</button></div>`; positionSuggestionsMain(); } return; }
    const sugg = t.closest && t.closest('.autocomplete-suggestion') ? t.closest('.autocomplete-suggestion') : (t.classList && t.classList.contains('autocomplete-suggestion') ? t : null);
    if (sugg) { productInputMain.value = sugg.dataset.name; productSuggestionsMain.style.display = 'none'; productInputMain.focus(); }
  });

  productInputMain.addEventListener('blur', function(e) { const related = e.relatedTarget || document.activeElement; if (related && productSuggestionsMain.contains(related)) return; setTimeout(()=>{ productSuggestionsMain.style.display='none'; },150); });

  productInputMain.addEventListener('keydown', function(e) {
    const items = Array.from(productSuggestionsMain.children);
    if (!items.length) return;
    let idx = items.findIndex(x => x.classList && x.classList.contains('active'));
    if (e.key === 'ArrowDown') { if (idx < items.length - 1) { if (idx >= 0) items[idx].classList.remove('active'); items[++idx].classList.add('active'); } e.preventDefault(); }
    else if (e.key === 'ArrowUp') { if (idx > 0) { items[idx].classList.remove('active'); items[--idx].classList.add('active'); } e.preventDefault(); }
    else if (e.key === 'Enter') { if (idx >= 0) { productInputMain.value = items[idx].dataset.name; productSuggestionsMain.style.display='none'; e.preventDefault(); } }
  });

  async function showEtatStockMain(product, magasin) {
    // remember which product is currently rendered
    try { lastRenderedProductMain = product ? String(product).trim() : ''; } catch(e){}
    etatStockWrapperMain.innerHTML = `<h3 style="margin:0 0 8px 0;">ETAT DE STOCK</h3><div id="etatTableMain">Loading...</div><div id="detailsMain"></div>`;
    try {
      const url = new URL(API_CONFIG.getApiUrl('/fetch-articles-data'));
      if (product) url.searchParams.append('name', product);
      if (magasin) url.searchParams.append('magasin', magasin);
      const resp = await fetch(url);
      if (!resp.ok) { document.getElementById('etatTableMain').innerHTML = '<p>Error loading stock</p>'; return; }
      const data = await resp.json();
    const rowsRaw = Array.isArray(data) ? data : (data && Array.isArray(data.rows) ? data.rows : (data ? [data] : []));
    // remove 'Total' rows for normal listing, but if that leaves nothing and raw contains rows (e.g. only zeros), fall back to raw
    const rowsFiltered = rowsRaw.filter(r => { try { return !(r && r.FOURNISSEUR && String(r.FOURNISSEUR).toLowerCase() === 'total'); } catch(e) { return true; } });
    const rows = (rowsFiltered && rowsFiltered.length) ? rowsFiltered : rowsRaw;
    if (!rows || !rows.length) { document.getElementById('etatTableMain').innerHTML = '<p>No stock data found.</p>'; return; }
  let html = '<div style="overflow:auto"><table style="width:100%; border-collapse:collapse; font-size:13px;">';
  html += '<thead><tr><th style="border:1px solid #ddd;padding:6px;">Produit</th><th style="border:1px solid #ddd;padding:6px;">Qty dispo</th><th style="border:1px solid #ddd;padding:6px;">Qty</th><th style="border:1px solid #ddd;padding:6px;">Reserved</th><th style="border:1px solid #ddd;padding:6px;">Actions</th></tr></thead><tbody>';
      rows.forEach(r => {
        const prod = r.NAME || r.name || r.PRODUCT || r.product || r.MAGASIN || r.MAGASIN_NAME || r.PRODUCT_NAME || '';
      // Prefer returning a warehouse id if available in the row (server now returns M_WAREHOUSE_ID)
      const mag = (r.M_WAREHOUSE_ID || r.M_WAREHOUSEId || r.m_warehouse_id) ? (r.M_WAREHOUSE_ID || r.M_WAREHOUSEId || r.m_warehouse_id) : (r.MAGASIN || r.magasin || r.CODE_MAGASIN || r.mag || r.store || '');
    // default numeric quantities to 0 so zeros are visible
    const qtyDispo = (r.QTY_DISPO ?? r.QTY_AVAILABLE ?? r.qty_dispo ?? r.qty_available ?? r.QTY ?? r.qty);
    const qty = (r.QTY ?? r.qty ?? r.TOTAL ?? r.total);
    const qtyReserved = (r.QTY_RESERVED ?? r.qty_reserved ?? r.RESERVED ?? r.reserved);
    const qtyDispoVal = (qtyDispo === null || qtyDispo === undefined || qtyDispo === '') ? 0 : qtyDispo;
    const qtyVal = (qty === null || qty === undefined || qty === '') ? 0 : qty;
    const qtyReservedVal = (qtyReserved === null || qtyReserved === undefined || qtyReserved === '') ? 0 : qtyReserved;
  html += `<tr><td style="border:1px solid #eee;padding:6px;">${prod}</td><td style="border:1px solid #eee;padding:6px;">${qtyDispoVal}</td><td style="border:1px solid #eee;padding:6px;">${qtyVal}</td><td style="border:1px solid #eee;padding:6px;">${qtyReservedVal}</td><td style="border:1px solid #eee;padding:6px;"><button class="showLotsMainBtn" data-prod="${prod}" data-mag="${mag}" style="margin-right:6px;">Lots</button><button class="showReservedMainBtn" style="margin-right:6px;">Reserved</button><button class="showHistoryMainBtn">History</button></td></tr>`;
      });
      html += '</tbody></table></div>';
      document.getElementById('etatTableMain').innerHTML = html;
      // attach handlers
      Array.from(document.querySelectorAll('.showLotsMainBtn')).forEach(b=>{ b.addEventListener('click', (ev)=>{ showLotsMain(b.dataset.prod, b.dataset.mag); }); });
      Array.from(document.querySelectorAll('.showReservedMainBtn')).forEach((b,i)=>{ b.addEventListener('click', ()=>{ showReservedMain(rows[i]); }); });
      Array.from(document.querySelectorAll('.showHistoryMainBtn')).forEach((b,i)=>{ b.addEventListener('click', ()=>{ const prod = rows[i].NAME||rows[i].name||rows[i].PRODUCT||rows[i].product||''; showHistoryMain(prod); }); });
    } catch (e) { document.getElementById('etatTableMain').innerHTML = '<p>Error loading stock.</p>'; console.error(e); }
    finally {
      // expand modal back to normal once fetch/render completes (success or failure)
      try { const content = document.getElementById('articlesInlineContent'); if (content) content.classList.remove('articles-compact'); } catch(e){}
    }
  }

  async function showLotsMain(product, magasin) {
    const container = document.getElementById('detailsMain');
    container.innerHTML = '<p>Loading product details...</p>';
    try {
  const url = new URL(API_CONFIG.getApiUrl('/fetch-article-product-details'));
  url.searchParams.append('product_name', product);
  // send warehouse id to the new endpoint. `magasin` may be an id or a label.
  let warehouseToSend = null;
  if (magasin) {
    // if caller passed a numeric id already, use it
    if (!isNaN(Number(magasin))) warehouseToSend = magasin;
    else {
      // try to find an option in magasin select that matches the label and has dataset.warehouseId
      try {
        const sel = document.getElementById('magasinSelectMain');
        if (sel) {
          // first check selected option
          const selected = sel.options[sel.selectedIndex];
          if (selected && selected.textContent.trim() === String(magasin).trim() && selected.dataset && selected.dataset.warehouseId) {
            warehouseToSend = selected.dataset.warehouseId;
          } else {
            // fallback: search options for matching label
            for (const opt of Array.from(sel.options)) {
              if (opt.textContent && opt.textContent.trim() === String(magasin).trim() && opt.dataset && opt.dataset.warehouseId) {
                warehouseToSend = opt.dataset.warehouseId; break;
              }
            }
          }
        }
      } catch(e) { /* ignore */ }
    }
  } else {
    // if magasin not provided, try to use the currently selected magasin option's warehouse id
    try {
      const sel = document.getElementById('magasinSelectMain');
      if (sel && sel.options && sel.selectedIndex >= 0) {
        const selected = sel.options[sel.selectedIndex];
        if (selected && selected.dataset && selected.dataset.warehouseId) warehouseToSend = selected.dataset.warehouseId;
      }
    } catch(e) {}
  }

  if (warehouseToSend) url.searchParams.append('warehouse_id', warehouseToSend);

      const resp = await fetch(url);
      if (!resp.ok) throw new Error('Failed to fetch product details');
      const data = await resp.json();

      // Normalize to array
      const rows = Array.isArray(data) ? data : (data && Array.isArray(data.rows) ? data.rows : (data ? [data] : []));

      if (!rows || rows.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-600">No product details found.</p>';
        return;
      }

      const formatNumber = (n) => {
        if (n === null || n === undefined || n === '') return '';
        const num = Number(n);
        if (isNaN(num)) return String(n);
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      };
      const formatInteger = (n) => {
        if (n === null || n === undefined || n === '') return '';
        const num = Number(n);
        if (isNaN(num)) return String(n);
        return Math.round(num).toLocaleString('en-US', { maximumFractionDigits: 0 });
      };
      const formatDate = (s) => {
        if (!s) return '';
        try { return new Date(s).toLocaleDateString('fr-FR'); } catch(e) { return s; }
      };

      const preferred = ['PRODUCT_NAME','PRODUCT_LABEL','LOT','LOCATION','QTY','QTY_DISPO','QTY_RESERVED','P_ACHAT','P_REVIENT','P_VENTE','GUARANTEEDATE'];
      const srcCols = Object.keys(rows[0] || {});
      const hideCols = new Set(['LOT_ID','LABO','FOURNISSEUR','PRODUCT'].map(c => c.toUpperCase()));
      const cols = [];
      preferred.forEach(k => { if (srcCols.includes(k) && !hideCols.has(k.toUpperCase())) cols.push(k); });
      srcCols.forEach(k => { if (!cols.includes(k) && !hideCols.has(String(k).toUpperCase())) cols.push(k); });

      let html = '<h3 style="margin:0 0 8px 0;">Product Details</h3>';
      html += '<div style="overflow-x:auto"><table style="width:100%; font-size:13px; border-collapse:collapse">';
      html += '<thead><tr>';
      const displayNames = {
        'QTY_DISPO': 'qty dis', 'P_ACHAT': 'P_A', 'BON_VENTE': 'Bo  n_V', 'REMISE_AUTO': 'Remise_Aut', 'REM_ACHAT': 'Remise_A',
        'QTY_RESERVED': 'qty res', 'P_REVIENT': 'P_R', 'P_VENTE': 'P_V', 'GUARANTEEDATE': 'Guarantee Date', 'BONUS_AUTO': 'Bonus_Aut', 'BON_ACHAT': 'Bon_A', 'REM_VENTE': 'Remise_V'
      };
      const prettyLabel = (k) => { const key = String(k).toUpperCase(); if (displayNames[key]) return displayNames[key]; return String(k).replace(/_/g,' ').replace(/\b\w/g, ch => ch.toUpperCase()); };
      cols.forEach(c => html += `<th style="border:1px solid #ddd;padding:6px">${prettyLabel(c)}</th>`);
      html += '</tr></thead><tbody>';

      rows.forEach(r => {
        html += '<tr>';
        cols.forEach(c => {
          let v = r[c];
          if (typeof v === 'number' || (!isNaN(Number(v)) && v !== null && v !== '')) {
            if (['QTY','QTY_DISPO','QTY_RESERVED'].includes(c)) v = formatInteger(v);
            else if (['P_ACHAT','P_REVIENT','P_VENTE'].includes(c)) v = formatNumber(v);
          }
          if (c === 'GUARANTEEDATE' || c.toLowerCase().includes('date')) v = formatDate(v);
          html += `<td style="border:1px solid #eee;padding:6px">${v !== null && v !== undefined ? v : ''}</td>`;
        });
        html += '</tr>';
      });

      html += '</tbody></table></div>';
      container.innerHTML = html;
    } catch (e) {
      console.error('Error fetching product details:', e);
      container.innerHTML = '<p class="text-sm text-red-600">Error loading product details.</p>';
    }
  }

  async function showHistoryMain(product) {
    const container = document.getElementById('detailsMain');
    container.innerHTML = '<p>Loading history...</p>';
    try {
      const url = new URL(API_CONFIG.getApiUrl('/history_articles'));
      url.searchParams.append('product_name', product);
      const resp = await fetch(url);
      if (!resp.ok) throw new Error('Failed to fetch history');
      const data = await resp.json();
      if (!Array.isArray(data) || data.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-600">No history found for this product.</p>';
        return;
      }

      const srcCols = Object.keys(data[0]);
      const mapFind = (candidates) => {
        for (const cand of candidates) {
          if (srcCols.includes(cand)) return cand;
          const found = srcCols.find(s => s.toUpperCase() === cand.toUpperCase()); if (found) return found;
        }
        return null;
      };

      const colOrder = [];
      colOrder.push(mapFind(['TIERS','tiers','CLIENT','client','BPARTNER','bpartner']));
      colOrder.push(mapFind(['DOCUMENT','document','DOCUMENTNO','documentno','DOCNO']));
      colOrder.push(mapFind(['DOCUMENT_TYPE','document_type','DOCTYPE','doctype','DOCUMENTTYPE']));
      colOrder.push(mapFind(['DATE_INVOICE','date_invoice','DATEINVOCE','DATE','dateinvoce','DATEINVOICE','DATEINVOICES']));
      const discountCol = mapFind(['DISCOUNT_PCT','discount_pct','DISCOUNT','discount','REMise','REM']);
      colOrder.push(discountCol);
      colOrder.push(mapFind(['PRIX_UNITAIRE','prix_unitaire','PRICEENTERED','priceentered','PRIX']));
      colOrder.push(mapFind(['QTY_FACTURE','qty_facture','QTYINVOICED','qtyinvoiced','QTY_FACT','QTY']));
      colOrder.push(mapFind(['Lot','LOT','lot','ATTRIBUTESETINSTANCE_ID','M_ATTRIBUTESETINSTANCE_ID','lot_no']));
      srcCols.forEach(c => { if (!colOrder.includes(c)) colOrder.push(c); });

      const formatSimpleDate = (s) => { if (!s) return ''; try { const d = new Date(s); if (isNaN(d.getTime())) return s; const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; return `${String(d.getDate()).padStart(2,'0')} ${months[d.getMonth()]} ${d.getFullYear()}`; } catch(e){return s;} };
  const formatNumberSimple = (v) => { if (v === null || v === undefined || v === '') return ''; const n = Number(v); if (isNaN(n)) return v; return n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); };
  const formatNumberInteger = (v) => { if (v === null || v === undefined || v === '') return ''; const n = Number(v); if (isNaN(n)) return v; return n.toLocaleString('en-US', {maximumFractionDigits:0}); };

      // Determine which columns correspond to TIERS and DOCUMENT TYPE (may be null)
      const tiersCol = colOrder[0];
      const docTypeCol = colOrder[2];
      const pageSize = 15;
      let currentPage = 1;

      const prettyHeader = (c) => { if (!c) return ''; if (discountCol && c === discountCol) return 'Remise'; return String(c).replace(/_/g,' ').replace(/\b\w/g, ch => ch.toUpperCase()); };

      // Build filter controls: TIERS (search) and DOCUMENT TYPE (select)
      const distinctDocTypes = [];
      if (docTypeCol) {
        const seen = new Set();
        data.forEach(r => {
          const v = r[docTypeCol];
          if (v !== null && v !== undefined && String(v).trim() !== '') {
            const s = String(v);
            if (!seen.has(s)) { seen.add(s); distinctDocTypes.push(s); }
          }
        });
      }

      // renderPage uses current filter input values
      const getFilteredData = () => {
        const tierFilterEl = document.getElementById('historyTiersFilter');
        const docTypeEl = document.getElementById('historyDocTypeFilter');
        const tierVal = tierFilterEl ? String(tierFilterEl.value || '').trim().toLowerCase() : '';
        const docTypeVal = docTypeEl ? String(docTypeEl.value || '') : '';

        return data.filter(row => {
          // TIERS filter
          if (tierVal && tiersCol) {
            const rv = row[tiersCol] || '';
            if (!String(rv).toLowerCase().includes(tierVal)) return false;
          }
          // DOCUMENT TYPE filter
          if (docTypeVal && docTypeCol) {
            const dv = row[docTypeCol] || '';
            if (String(dv) !== docTypeVal) return false;
          }
          return true;
        });
      };

      // initial render of controls + table
      const renderPage = (page) => {
        // preserve current filter values and caret position so we don't lose focus while re-rendering
        let prevTierVal = '';
        let prevTierPos = null;
        let prevDocTypeVal = '';
        const existingTierEl = document.getElementById('historyTiersFilter');
        if (existingTierEl) {
          try { prevTierVal = existingTierEl.value || ''; prevTierPos = existingTierEl.selectionStart; } catch(e) { prevTierPos = null; }
        }
        const existingDocTypeEl = document.getElementById('historyDocTypeFilter');
        if (existingDocTypeEl) prevDocTypeVal = existingDocTypeEl.value || '';

        const filtered = getFilteredData();
        const totalRows = filtered.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
        if (page > totalPages) page = totalPages;
        currentPage = page;
        const start = (page-1)*pageSize; const end = Math.min(start+pageSize, totalRows);

        let html = '<h3 style="margin:0 0 8px 0;">History</h3>';
        // Filter controls row
        html += '<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">';
        // TIERS search
        if (tiersCol) html += `<input id="historyTiersFilter" placeholder="Search TIERS" style="padding:6px;border:1px solid #ddd;border-radius:6px;min-width:160px" />`;
        // DOCUMENT TYPE select
        if (docTypeCol) {
          html += `<select id="historyDocTypeFilter" style="padding:6px;border:1px solid #ddd;border-radius:6px"><option value="">All</option>`;
          distinctDocTypes.forEach(dt => { html += `<option value="${dt}">${dt}</option>`; });
          html += `</select>`;
        }
        html += '</div>';

        html += `<div style="margin-bottom:8px;font-size:13px;color:#6b7280">Showing ${totalRows===0?0:start+1} - ${end} of ${totalRows}</div>`;
        html += '<div style="overflow-x:auto"><table style="width:100%;font-size:13px;border-collapse:collapse"><thead><tr>';
        colOrder.forEach(c => { if (c) html += `<th style="border:1px solid #ddd;padding:6px">${prettyHeader(c)}</th>`; });
        html += '</tr></thead><tbody>';

        for (let i = start; i < end; i++) {
          const row = filtered[i]; html += '<tr>';
          colOrder.forEach(c => { if (!c) return; let v = row[c]; const upper = String(c).toUpperCase(); if (upper.includes('DATE') || (typeof v === 'string' && /\d{4}-\d{2}-\d{2}/.test(v))) v = formatSimpleDate(v); if (discountCol && c === discountCol) { const num = Number(v); if (!isNaN(num)) v = `${num}%`; else if (v === null || v === undefined || v === '') v = ''; } if (upper === 'PRIX_UNITAIRE' || upper === 'PRICEENTERED' || upper === 'PRIX' || upper.includes('PRIX')) v = formatNumberSimple(v); if (upper === 'QTY_FACTURE' || upper === 'QTYINVOICED' || upper === 'QTY_FACT' || upper === 'QTY') v = formatNumberInteger(v); html += `<td style="border:1px solid #eee;padding:6px">${v !== null && v !== undefined ? v : ''}</td>`; }); html += '</tr>';
        }

        html += '</tbody></table></div>';
        html += '<div style="margin-top:8px;display:flex;gap:6px;align-items:center">';
        html += `<button id="histFirst" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage<=1? 'opacity:0.5;pointer-events:none;':''}">First</button>`;
        html += `<button id="histPrev" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage<=1? 'opacity:0.5;pointer-events:none;':''}">Prev</button>`;
        const pageBlockStart = Math.max(1, page - 3); const pageBlockEnd = Math.min(totalPages, page + 3);
        for (let p = pageBlockStart; p <= pageBlockEnd; p++) html += `<button data-p="${p}" class="pageBtn" style="padding:6px;border:1px solid #ddd;border-radius:6px;${p===page? 'background:#2563eb;color:#fff;':''}">${p}</button>`;
        html += `<button id="histNext" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage>=totalPages? 'opacity:0.5;pointer-events:none;':''}">Next</button>`;
        html += `<button id="histLast" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage>=totalPages? 'opacity:0.5;pointer-events:none;':''}">Last</button>`;
        html += '</div>';
        container.innerHTML = html;

        // restore filter values and focus to maintain typing experience
        const tierElRestore = document.getElementById('historyTiersFilter');
        if (tierElRestore) {
          try { tierElRestore.value = prevTierVal || ''; if (prevTierPos !== null && typeof tierElRestore.setSelectionRange === 'function') tierElRestore.setSelectionRange(prevTierPos, prevTierPos); tierElRestore.focus(); } catch(e) {}
        }
        const docTypeElRestore = document.getElementById('historyDocTypeFilter');
        if (docTypeElRestore) { try { if (prevDocTypeVal) docTypeElRestore.value = prevDocTypeVal; } catch(e) {} }

        // attach filter listeners
        const tierEl = document.getElementById('historyTiersFilter');
        const docTypeEl = document.getElementById('historyDocTypeFilter');
        if (tierEl) { tierEl.addEventListener('input', () => { currentPage = 1; renderPage(1); }); }
        if (docTypeEl) { docTypeEl.addEventListener('change', () => { currentPage = 1; renderPage(1); }); }

        const prev = document.getElementById('histPrev'); const next = document.getElementById('histNext'); const firstBtn = document.getElementById('histFirst'); const lastBtn = document.getElementById('histLast');
        if (prev) prev.onclick = () => { if (currentPage > 1) { currentPage--; renderPage(currentPage); } };
        if (next) next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderPage(currentPage); } };
        if (firstBtn) firstBtn.onclick = () => { if (currentPage !== 1) { currentPage = 1; renderPage(1); } };
        if (lastBtn) lastBtn.onclick = () => { if (currentPage !== totalPages) { currentPage = totalPages; renderPage(totalPages); } };
        Array.from(container.querySelectorAll('.pageBtn')).forEach(b => { b.onclick = () => { const p = Number(b.getAttribute('data-p')); if (!isNaN(p)) { currentPage = p; renderPage(currentPage); } }; });
      };

      // Initial render
      renderPage(1);
    } catch (e) { console.error('Error fetching history:', e); container.innerHTML = '<p style="color:#b91c1c;">Error loading history.</p>'; }
  }

  async function showReservedMain(row) {
    const container = document.getElementById('detailsMain');
    container.innerHTML = '<p>Loading reserved orders...</p>';
    try {
      let m_product_id = null;
      const candidates = ['M_PRODUCT_ID','m_product_id','M_PRODUCTID','m_productid','M_PRODUCT','productid','product_id'];
      for (const k of Object.keys(row)) {
        if (!m_product_id && candidates.includes(k) && row[k]) m_product_id = row[k];
      }

      const url = new URL(API_CONFIG.getApiUrl('/reserved_reserved_fromorder'));
      if (m_product_id) url.searchParams.append('m_product_id', m_product_id);
      else {
        const prodName = row.NAME || row.name || row.PRODUCT || row.product || '';
        if (!prodName) { container.innerHTML = '<p class="text-sm text-gray-600">No product identifier to fetch reserved orders.</p>'; return; }
        url.searchParams.append('product_name', prodName);
      }

      const resp = await fetch(url);
      if (!resp.ok) { const err = await resp.json().catch(()=>({})); container.innerHTML = `<p style="color:#b91c1c;">Failed to load reserved orders: ${err.error || resp.statusText}</p>`; return; }

      const data = await resp.json();
      if (!Array.isArray(data) || data.length === 0) { container.innerHTML = '<p class="text-sm text-gray-600">No reserved orders found for this product.</p>'; return; }

      const srcCols = Object.keys(data[0]);
      const displayCols = srcCols.filter(c => c.toUpperCase() !== 'DOCACTION' && c.toUpperCase() !== 'DOCSTATUS');
      displayCols.push('DocumentStatus');
      const clientIdx = displayCols.findIndex(c => String(c).toLowerCase() === 'client_name' || String(c).toLowerCase() === 'client');
      if (clientIdx > -1) { const clientCol = displayCols.splice(clientIdx,1)[0]; displayCols.unshift(clientCol); }

      function formatReservedDate(dateString) {
        if (!dateString) return '';
        try {
          const d = new Date(dateString);
          if (isNaN(d.getTime())) return dateString;
          const weekdaysFr = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
          const monthsShort = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
          const wd = (weekdaysFr[d.getDay()] || '').substring(0,4);
          const day = String(d.getDate()).padStart(2,'0');
          const mon = monthsShort[d.getMonth()];
          const year = d.getFullYear();
          return `${wd} ${day} ${mon} ${year}`;
        } catch (e) { return dateString; }
      }

      const pageSize = 15; let currentPage = 1; const totalRows = data.length; const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

      const renderReservedPage = (page) => {
        const start = (page - 1) * pageSize; const end = Math.min(start + pageSize, totalRows);
        let html = '<h3 style="margin:0 0 8px 0;">Reserved Orders</h3>';
        html += `<div style="margin-bottom:8px;font-size:13px;color:#6b7280">Showing ${start+1} - ${end} of ${totalRows}</div>`;
        html += '<div style="overflow-x:auto"><table style="width:100%;font-size:13px;border-collapse:collapse"><thead><tr>';
        displayCols.forEach(c => { const label = (String(c).toLowerCase() === 'client_name' || String(c).toLowerCase() === 'client') ? 'Client' : c; html += `<th style="border:1px solid #ddd;padding:6px">${label}</th>`; });
        html += '</tr></thead><tbody>';

        for (let i = start; i < end; i++) {
          const r = data[i]; const docAction = (r.DOCACTION || r.docaction || '').toString(); const docStatus = (r.DOCSTATUS || r.docstatus || '').toString(); let documentStatus = 'unknown'; const checkVal = (s) => s && ['PR','IP'].includes(s.toString().toUpperCase()); if (checkVal(docAction) || checkVal(docStatus)) documentStatus = 'reserved'; else if ((docAction && ['CO','CL'].includes(docAction.toString().toUpperCase())) || (docStatus && ['CO','CL'].includes(docStatus.toString().toUpperCase()))) documentStatus = 'Achevé';

          html += '<tr style="background:transparent">';
          for (const c of displayCols) {
            if (c === 'DocumentStatus') { html += `<td style="border:1px solid #eee;padding:6px">${documentStatus}</td>`; continue; }
            let v = r[c]; if (v === null || v === undefined) v = ''; const colNameUpper = String(c).toUpperCase(); const isDateCol = colNameUpper.includes('DATE') || colNameUpper.includes('DATEORDER') || colNameUpper.includes('DATE_ORDER') || colNameUpper.includes('DATEORDERED'); const looksLikeDate = typeof v === 'string' && /\b\d{1,2}\s+[A-Za-z]{3,}\s+\d{4}\b/.test(v) || (typeof v === 'string' && v.includes('GMT')); if (isDateCol || looksLikeDate) v = formatReservedDate(v); html += `<td style="border:1px solid #eee;padding:6px">${v}</td>`; }
          html += '</tr>';
        }

        html += '</tbody></table></div>';
        html += '<div style="margin-top:8px;display:flex;gap:6px;align-items:center">';
        html += `<button id="resFirst" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage<=1? 'opacity:0.5;pointer-events:none;':''}">First</button>`;
        html += `<button id="resPrev" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage<=1? 'opacity:0.5;pointer-events:none;':''}">Prev</button>`;
        const pageBlockStart = Math.max(1, page - 3); const pageBlockEnd = Math.min(totalPages, page + 3);
        for (let p = pageBlockStart; p <= pageBlockEnd; p++) html += `<button data-p="${p}" class="resPageBtn" style="padding:6px;border:1px solid #ddd;border-radius:6px;${p===page? 'background:#2563eb;color:#fff;':''}">${p}</button>`;
        html += `<button id="resNext" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage>=totalPages? 'opacity:0.5;pointer-events:none;':''}">Next</button>`;
        html += `<button id="resLast" style="padding:6px;border-radius:6px;border:1px solid #ddd;${currentPage>=totalPages? 'opacity:0.5;pointer-events:none;':''}">Last</button>`;
        html += '</div>';

        container.innerHTML = html;

        const prev = document.getElementById('resPrev'); const next = document.getElementById('resNext'); const firstBtn = document.getElementById('resFirst'); const lastBtn = document.getElementById('resLast');
        if (prev) prev.onclick = () => { if (currentPage > 1) { currentPage--; renderReservedPage(currentPage); } };
        if (next) next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderReservedPage(currentPage); } };
        if (firstBtn) firstBtn.onclick = () => { if (currentPage !== 1) { currentPage = 1; renderReservedPage(1); } };
        if (lastBtn) lastBtn.onclick = () => { if (currentPage !== totalPages) { currentPage = totalPages; renderReservedPage(totalPages); } };
        Array.from(container.querySelectorAll('.resPageBtn')).forEach(b => { b.onclick = () => { const p = Number(b.getAttribute('data-p')); if (!isNaN(p)) { currentPage = p; renderReservedPage(currentPage); } }; });
      };
      renderReservedPage(currentPage);
    } catch (e) { console.error('Error fetching reserved orders:', e); container.innerHTML = '<p style="color:#b91c1c;">Error loading reserved orders.</p>'; }
  }

  // open inline modal and initialize data (globally accessible from sidebar)
  window.openArticlesInline = async function() {
    // start in compact mode while we fetch data / before user interacts
    const content = document.getElementById('articlesInlineContent');
    if (content) content.classList.add('articles-compact');
    articlesInlineModal.style.display = 'flex';
    // fetch magasins and products for suggestions
    await Promise.all([ fetchMagasinsMain(), fetchProductListMain() ]);
    // focus
    setTimeout(()=> productInputMain.focus(), 50);
  }

  // Close button handler
  articlesInlineClose.addEventListener('click', () => {
    articlesInlineModal.style.display = 'none';
    // clear fields
    try { productInputMain.value = ''; productSuggestionsMain.innerHTML=''; productSuggestionsMain.style.display='none'; magasinSelectMain.selectedIndex = 0; etatStockWrapperMain.innerHTML = ''; lastRenderedProductMain = ''; } catch(e){}
    // ensure compact state removed for next open
    try { const content = document.getElementById('articlesInlineContent'); if (content) content.classList.remove('articles-compact'); } catch(e){}
  });

  // wire show button
  showEtatStockBtnMain.addEventListener('click', async () => {
    const prod = productInputMain.value.trim(); const mag = magasinSelectMain.value;
    if (!prod || !mag) { alert('Entrer un produit et choisir un magasin.'); return; }
    await showEtatStockMain(prod, mag);
  });

  // close if clicking outside content area
  articlesInlineModal.addEventListener('click', (e)=>{ if (e.target === articlesInlineModal) { articlesInlineModal.style.display='none'; } });
  </script>

<!-- Scary Warning Popup for Saisie User -->
<!-- Scary Warning Popup for Saisie User with Specific IP -->



    
    </body>
    </html>
