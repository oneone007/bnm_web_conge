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
    max-width: 350px;
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
</script>




    
    </body>
    </html>
