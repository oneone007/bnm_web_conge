<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)
$inactive_time = 3600;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Store username and role in variables
$username = $_SESSION['username'] ?? 'Guest';
$Role = $_SESSION['Role'] ?? 'Uknown'; // Default role as 'user'

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate session lifetime
    $session_lifetime = time() - $_SESSION['last_activity'];

    if ($session_lifetime > $inactive_time) {
        session_unset(); // Unset session variables
        session_destroy(); // Destroy the session
        header("Location: BNM?session_expired=1"); // Redirect to login page with message
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Role-based access control (example)
if ($Role !== 'admin' && basename($_SERVER['PHP_SELF']) === 'AdminDashboard.php') {
    header("Location: Main"); // Redirect non-admin users away from admin pages
    exit();
}


$host = 'localhost'; // Change if needed
$user = 'root'; // Change if needed
$pass = ''; // Change if needed
$dbname = 'bnm'; // Your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the rating submission if it's sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);

    // Ensure that the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session

        // Update the rating for the logged-in user
        $sql = "UPDATE users SET rating = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $rating, $user_id);

        if ($stmt->execute()) {
            echo "Rating updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "You must be logged in to submit a rating.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

        <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="sidebarr.css">
    </head>
    <script src="sid.js"></script> <!-- Your sid.js script -->
<style>

.logoutButton {
--figure-duration: 100ms;
--transform-figure: none;
--walking-duration: 100ms;
--transform-arm1: none;
--transform-wrist1: none;
--transform-arm2: none;
--transform-wrist2: none;
--transform-leg1: none;
--transform-calf1: none;
--transform-leg2: none;
--transform-calf2: none;
background: none;
border: 0;
color: grey;
cursor: pointer;
display: block;
font-family: 'Quicksand', sans-serif;
font-size: 14px;
font-weight: 500;
height: 60px;
outline: none;
padding: 0 0 0 20px;
perspective: 100px;
position: relative;
width: 140px;
-webkit-tap-highlight-color: transparent; }

.logoutButton::before {
background-color: white;
border-radius: 5px;
content: '';
display: block;
height: 100%;
left: 0;
position: absolute;
top: 0;
transform: none;
transition: transform 50ms ease;
width: 100%;
z-index: 2; }
.logoutButton:hover .door {
transform: rotateY(20deg); }
.logoutButton:active::before {
transform: scale(0.96); }
.logoutButton:active .door {
transform: rotateY(28deg); }
.logoutButton.clicked::before {
transform: none; }
.logoutButton.clicked .door {
transform: rotateY(35deg); }
.logoutButton.door-slammed .door {
transform: none;
transition: transform 100ms ease-in 250ms; }
.logoutButton.falling {
animation: shake 200ms linear; }
.logoutButton.falling .bang {
animation: flash 300ms linear; }
.logoutButton.falling .figure {
animation: spin 1000ms infinite linear;
bottom: -450px;
opacity: 0;
right: 1px;
transition: transform calc(var(--figure-duration) * 1ms) linear, bottom calc(var(--figure-duration) * 1ms) cubic-bezier(0.7, 0.1, 1, 1) 100ms, opacity calc(var(--figure-duration) * 0.25ms) linear calc(var(--figure-duration) * 0.75ms);
z-index: 1; }
.logoutButton--light::before {
background-color: grey; }
.logoutButton--light .button-text {
color: white; }
.logoutButton--light .door,
.logoutButton--light .doorway {
fill: white; }

.button-text {
color: rgb(10, 0, 0);
font-weight: 900;
font-size:25px;
position: relative;
z-index: 10; }

.logoutButton svg {
display: block;
position: absolute; }

.figure {
bottom: 5px;
fill: black;
right: 18px;
transform: var(--transform-figure);
transition: transform calc(var(--figure-duration) * 1ms) cubic-bezier(0.2, 0.1, 0.8, 0.9);
width: 30px;
z-index: 4; }

.door,
.doorway {
bottom: 4px;
fill: grey;
right: 12px;
width: 32px; }

.door {
transform: rotateY(20deg);
transform-origin: 100% 50%;
transform-style: preserve-3d;
transition: transform 200ms ease;
z-index: 5; }
.door path {
fill: black;
stroke: black;
stroke-width: 4; }

.doorway {
z-index: 3; }

.bang {
opacity: 0; }

.arm1, .wrist1, .arm2, .wrist2, .leg1, .calf1, .leg2, .calf2 {
transition: transform calc(var(--walking-duration) * 1ms) ease-in-out; }

.arm1 {
transform: var(--transform-arm1);
transform-origin: 52% 45%; }

.wrist1 {
transform: var(--transform-wrist1);
transform-origin: 59% 55%; }

.arm2 {
transform: var(--transform-arm2);
transform-origin: 47% 43%; }

.wrist2 {
transform: var(--transform-wrist2);
transform-origin: 35% 47%; }

.leg1 {
transform: var(--transform-leg1);
transform-origin: 47% 64.5%; }

.calf1 {
transform: var(--transform-calf1);
transform-origin: 55.5% 71.5%; }

.leg2 {
transform: var(--transform-leg2);
transform-origin: 43% 63%; }

.calf2 {
transform: var(--transform-calf2);
transform-origin: 41.5% 73%; }

@keyframes spin {
from {
transform: rotate(0deg) scale(0.94); }
to {
transform: rotate(359deg) scale(0.94); } }
@keyframes shake {
0% {
transform: rotate(-1deg); }
50% {
transform: rotate(2deg); }
100% {
transform: rotate(-1deg); } }
@keyframes flash {
0% {
opacity: 0.4; }
100% {
opacity: 0; } }



.logoutButton svg {
    width: 50px;
    height: 50px;
    /* Optional: add some margin if needed */
    margin: 0 5px;
  }

/**** Wrapper styles ****************/


/*# sourceMappingURL=c.css.map */   
</style>
    <!-- <button id="sidebarToggle"
class="fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg shadow-lg hover:bg-blue-700">
☰
</button> -->

    <!-- <div id="ram-animation"></div> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>

    <script>
        var animation = lottie.loadAnimation({
            container: document.getElementById('ram-animation'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: 'json_files/ram.json',
            rendererSettings: {
                clearCanvas: true,
                preserveAspectRatio: 'xMidYMid meet',
                progressiveLoad: true,
                hideOnTransparent: true
            }
        });
    </script> -->

<!--  
<div id="space"></div> -->

  
<!-- Sidebar Part --> 
<!-- <div class="sidebar-logo">
  <div class="scene">
    <div class="logo">
      <img src="assets/log.png" alt="Rotating Logo" class="rotating-img">
    </div>
  </div>
</div> -->

    <!-- <div>
    <span style="margin-left: 10px; font-weight: bold;">
        </span>
    </div> -->
<!-- Place this outside of <nav> and not inside any container -->
<button id="sidebarToggle" style="
    position: fixed;
    top: 20px;
    left: 260px;
    z-index: 1000;
    transition: left 0.3s ease;
">
    ☰
</button>


<div id="sidebar" class="sidebar p-4">



    <img src="assets/log.png" alt="Logo" class="logo">


    <!-- From Uiverse.io by alexruix --> 

  <div class="notification">    
    <div class="notification-info">
    <p class="notification-text">
  <!-- <span >Welcome:</span>  -->
  <span >
    <?php echo htmlspecialchars($username); ?>
</span>
<span class="highlight">
    (<?php echo htmlspecialchars($Role); ?>)
</span>
</p>
      <!-- <b>Admin!</b>  -->
    </p>
    </div>
  </div>
    <!-- <h1 class="text-xl font-bold mb-6">📂 Collections</h1> -->
    <style>
    nav {
        padding: 1rem;
    }
    nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    nav li {
        margin-bottom: 0.5rem;
    }
    nav button {
        width: 100%;
        text-align: left;
        padding: 0.5rem;
        border: none;
        background: none;
        display: flex; /* already good */
        align-items: center; /* makes icon and text centered vertically */
        gap: 0.5rem; /* adds space between icon and text */
        border-radius: 0.5rem;
        transition: background-color 0.3s;
        cursor: pointer;
        font-size: 1rem; /* ensure consistent size */
    }
    nav button:hover {
        background-color: #f3f4f6; /* light gray hover */
    }
    nav hr {
        margin: 1rem 0;
        border: 0;
        height: 1px;
        background-color: #e5e7eb;
    }

    .submenu button {
        padding-left: 1rem;
        font-size: 0.95rem;
        gap: 0.5rem; /* same gap between icon and text inside submenu */
    }
    .submenu.show {
        display: block;
    }

    /* Initially hide the submenus */
.submenu {
    display: none;
    list-style-type: none;
    padding-left: 20px; /* Indentation for submenu items */
}

.sidebar-mode-toggle {
  margin: 10px 0;
  text-align: center;
}

.mode-label {
  display: block;
  font-weight: bold;
  color: #222;
  margin-bottom: 8px;
  font-size: 14px;
}

.mode-buttons {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.mode-buttons input[type="radio"] {
  display: none;
}

.mode-btn {
  padding: 6px 14px;
  background-color: #f3f3f3;
  border: 2px solid #ccc;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: bold;
  font-size: 13px;
  box-shadow: 0 0 0 rgba(0, 0, 0, 0);
}

.mode-btn:hover {
  background-color: #e0e0e0;
  box-shadow: 0 0 6px rgba(0, 123, 255, 0.3);
}

.mode-buttons input[type="radio"]:checked + .mode-btn {
  background: linear-gradient(135deg, #007BFF, #00A6FF);
  color: white;
  border-color: #007BFF;
  box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
  transform: scale(1.03);
}


</style>



<nav>

<li class="sidebar-mode-toggle">
  <label class="mode-label">🧭 Sidebar Mode:</label>
  <div class="mode-buttons">
    <input type="radio" id="autoMode" name="mode" value="auto">
    <label for="autoMode" class="mode-btn">🤖 Auto</label>

    <input type="radio" id="manualMode" name="mode" value="manual">
    <label for="manualMode" class="mode-btn">🖐️ Manual</label>
  </div>
</li>


    <ul>

        <li>
            <button onclick="location.href='Main'">🏠 Accueil</button>
        </li>

        <!-- <li>
            <button onclick="location.href='Coming'">📊 Nos analyses</button>
        </li> -->

        <hr>



        <li>
            <button onclick="toggleSubmenu('fond-submenu')">🪙 FONDS PROPRE</button>
            <ul id="fond-submenu" class="submenu">
                <li><button onclick="location.href='mony'">📈 Analysis</button></li>
                <li><button onclick="location.href='bank'">🏦 Banks</button></li>
            </ul>
        </li>


        <li>
            <button onclick="location.href='ETAT_Fourniseeur'">🤝 CREANCES/DETTES</button>
        </li>

        <hr>



        <li>
        <button onclick="toggleSubmenu('products-submenu')">🛍️ PRODUCTS</button>
        <ul id="products-submenu" class="submenu">
                <li><button onclick="location.href='Etatstock'">📦 ÉTAT DE STOCK</button></li>
                <li><button onclick="location.href='Product'">🛍️ PRODUCTS</button></li>
                <li><button onclick="location.href='Rotation'">🔄 ROTATION</button></li>
                <li><button onclick="location.href='Quota'">🎯 PRODUIT QUOTA</button></li>
            </ul>
        </li>
        <hr>

        <li>
            <button onclick="toggleSubmenu('recapsa-submenu')">🛒 RECAPS ACHAT</button>
            <ul id="recapsa-submenu" class="submenu">

            
                <li><button onclick="location.href='Recap_Achat'">🛒 Recap Achat</button></li>
                <li><button onclick="location.href='recap_achat_facturation'">🧾 Recap Achat F</button></li>
                <li><button onclick="location.href='Annual_Recap_A'">📆 Annual Recap</button></li>

            </ul>
        </li>


        <li>
            <button onclick="toggleSubmenu('recapsv-submenu')">💰 RECAPS VENTE</button>
            <ul id="recapsv-submenu" class="submenu">

            
                <li><button onclick="location.href='Recap_Vente'">💰 Recap Vente</button></li>
                <li><button onclick="location.href='Recap_Vente_Facturation'">🧾 Recap Vente F</button></li>
                <li><button onclick="location.href='Annual_Recap_V'">📆 Annual Recap</button></li>

            </ul>
        </li>
        <hr>

        <li>
            <button onclick="location.href='Journal_Vente'">📝 Journal de Vente</button>
        </li>
        <hr>
        <li>
            <button onclick="location.href='CONFIRMED_ORDERS'">✅ Confirm Order</button>
        </li>

    </ul>
    <br><br>
    <button  class="logoutButton logoutButton--dark">
          <svg class="doorway" viewBox="0 0 100 100">
            <path d="M93.4 86.3H58.6c-1.9 0-3.4-1.5-3.4-3.4V17.1c0-1.9 1.5-3.4 3.4-3.4h34.8c1.9 0 3.4 1.5 3.4 3.4v65.8c0 1.9-1.5 3.4-3.4 3.4z" />
            <path class="bang" d="M40.5 43.7L26.6 31.4l-2.5 6.7zM41.9 50.4l-19.5-4-1.4 6.3zM40 57.4l-17.7 3.9 3.9 5.7z" />
          </svg>
          <svg class="figure" viewBox="0 0 100 100">
            <circle cx="52.1" cy="32.4" r="6.4" />
            <path d="M50.7 62.8c-1.2 2.5-3.6 5-7.2 4-3.2-.9-4.9-3.5-4-7.8.7-3.4 3.1-13.8 4.1-15.8 1.7-3.4 1.6-4.6 7-3.7 4.3.7 4.6 2.5 4.3 5.4-.4 3.7-2.8 15.1-4.2 17.9z" />
            <g class="arm1">
              <path d="M55.5 56.5l-6-9.5c-1-1.5-.6-3.5.9-4.4 1.5-1 3.7-1.1 4.6.4l6.1 10c1 1.5.3 3.5-1.1 4.4-1.5.9-3.5.5-4.5-.9z" />
              <path class="wrist1" d="M69.4 59.9L58.1 58c-1.7-.3-2.9-1.9-2.6-3.7.3-1.7 1.9-2.9 3.7-2.6l11.4 1.9c1.7.3 2.9 1.9 2.6 3.7-.4 1.7-2 2.9-3.8 2.6z" />
            </g>
            <g class="arm2">
              <path d="M34.2 43.6L45 40.3c1.7-.6 3.5.3 4 2 .6 1.7-.3 4-2 4.5l-10.8 2.8c-1.7.6-3.5-.3-4-2-.6-1.6.3-3.4 2-4z" />
              <path class="wrist2" d="M27.1 56.2L32 45.7c.7-1.6 2.6-2.3 4.2-1.6 1.6.7 2.3 2.6 1.6 4.2L33 58.8c-.7 1.6-2.6 2.3-4.2 1.6-1.7-.7-2.4-2.6-1.7-4.2z" />
            </g>
            <g class="leg1">
              <path d="M52.1 73.2s-7-5.7-7.9-6.5c-.9-.9-1.2-3.5-.1-4.9 1.1-1.4 3.8-1.9 5.2-.9l7.9 7c1.4 1.1 1.7 3.5.7 4.9-1.1 1.4-4.4 1.5-5.8.4z" />
              <path class="calf1" d="M52.6 84.4l-1-12.8c-.1-1.9 1.5-3.6 3.5-3.7 2-.1 3.7 1.4 3.8 3.4l1 12.8c.1 1.9-1.5 3.6-3.5 3.7-2 0-3.7-1.5-3.8-3.4z" />
            </g>
            <g class="leg2">
              <path d="M37.8 72.7s1.3-10.2 1.6-11.4 2.4-2.8 4.1-2.6c1.7.2 3.6 2.3 3.4 4l-1.8 11.1c-.2 1.7-1.7 3.3-3.4 3.1-1.8-.2-4.1-2.4-3.9-4.2z" />
              <path class="calf2" d="M29.5 82.3l9.6-10.9c1.3-1.4 3.6-1.5 5.1-.1 1.5 1.4.4 4.9-.9 6.3l-8.5 9.6c-1.3 1.4-3.6 1.5-5.1.1-1.4-1.3-1.5-3.5-.2-5z" />
            </g>
          </svg>
          <svg class="door" viewBox="0 0 100 100">
            <path d="M93.4 86.3H58.6c-1.9 0-3.4-1.5-3.4-3.4V17.1c0-1.9 1.5-3.4 3.4-3.4h34.8c1.9 0 3.4 1.5 3.4 3.4v65.8c0 1.9-1.5 3.4-3.4 3.4z" />
            <circle cx="66" cy="50" r="3.7" />
          </svg>
          <span class="button-text">Exit</span>
    </button>
</nav>




  
</div>


    <!-- Logout Button -->
    <!-- <div class="mt-auto">
        <button onclick="location.href='db/logout.php'" 
            class="block text-center py-3 mt-4 w-full rounded-lg shadow-lg 
            hover:bg-gray-300 transition-all duration-300 transform hover:scale-105">
            Logout
        </button>
    </div>
     -->
  
</div>

<script>
    
</script>
</html>