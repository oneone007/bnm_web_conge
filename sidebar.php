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
        <link rel="stylesheet" href="sidebar.css">
    </head>
    <script src="js/sidebar.js"></script> <!-- Your sidebar.js script -->

    <!-- <button id="sidebarToggle"
class="fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg shadow-lg hover:bg-blue-700">
☰
</button> -->


<div id="sidebar" class="sidebar p-4">
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


    <img src="assets/log.png" alt="Logo" class="logo">
    <!-- <div>
    <span style="margin-left: 10px; font-weight: bold;">
        </span>
    </div> -->


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
    <nav>
    
        <ul>
            <li class="mb-3">
                <button onclick="location.href='Main'" class="w-full flex items-center p-2 rounded-lg hover:bg-gray-300 transition">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.5L12 3l9 6.5M4 10v10h16V10" />
                    </svg>
                    Accueil
                </button>
            </li>
            
            <!-- <li class="mb-3">
                <button onclick="location.href='Coming'" class="w-full flex items-center p-2 rounded-lg hover:bg-gray-300 transition">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                    Parcourir les données
                </button>
            </li> -->
            <li class="mb-3">
                <button onclick="location.href='Coming'" class="w-full flex items-center p-2 rounded-lg hover:bg-gray-300 transition">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                    Nos analyses
                </button>
            </li>

            <li class="mb-3 flex flex-col">
                <div id="fond-toggle" class="flex items-center cursor-pointer p-2 rounded-lg hover:bg-gray-300 transition  ">
                  <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18v4H3V4zM3 10h18v10H3V10zM8 14h8" />
                  </svg>
                  FONDS PROPRE
                </div>
              
                <ul id="fond-submenu" class="mt-2 ml-4 hidden">
                  <li>
                    <button onclick="location.href='mony'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                    Data
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='bank'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      Banks
                    </button>
                  </li>
                </ul>
              </li>



        <li class="mb-3">
  <button onclick="location.href='ETAT_Fourniseeur'" class="w-full flex items-center p-2 rounded-lg hover:bg-gray-300 transition">
    <svg class="w-5 h-5 mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M5 10v10h14V10M3 6h18v4H3V6z" />
    </svg>
    <span class="text-base">CREANCES/DETTES</span>
  </button>
</li>

            
            <li class="mb-3 flex flex-col">
                <div id="products-toggle" class="flex items-center cursor-pointer p-2 rounded-lg hover:bg-gray-300 transition  ">
                  <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18v4H3V4zM3 10h18v10H3V10zM8 14h8" />
                  </svg>
                  PRODUCTS
                </div>
              
                <ul id="products-submenu" class="mt-2 ml-4 hidden">
                  <li>
                    <button onclick="location.href='Etatstock'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      ÉTAT DE STOCK
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='Product'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      PRODUCTS
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='Rotation'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      ROTATION
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='Quota'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      
PRODUIT QUOTA
                    </button>
                  </li>
                </ul>
              </li>
              
      
            <li class="mb-3 flex flex-col">
                <div id="recaps-toggle" class="flex items-center cursor-pointer p-2 rounded-lg hover:bg-gray-300 transition">
                  <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18v16H3zM7 8h10M7 12h7M7 16h4" />
                  </svg>
                  RECAPS
                </div>
              
                <ul id="recaps-submenu" class="mt-2 ml-4 hidden">
                  <li>
                    <button onclick="location.href='Recap_Achat'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                        Recap Achat
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='Recap_Vente'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      Recap Vente
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='recap_achat_facturation'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      Recap Achat F
                    </button>
                  </li>
                  <li>
                    <button onclick="location.href='Recap_Vente_Facturation'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition text-gray-300 hover:text-black">
                      Recap Vente F
                    </button>
                  </li>
                </ul>
              </li>
              
              
                        <!-- <button onclick="location.href='Recap_Vente_Facturation'" class="w-full text-left p-2 rounded-lg hover:bg-gray-300 transition">Récap Vente FACTURATION</button> -->
                

           
            <li class="mb-3">
                <button onclick="location.href='Journal_Vente'" class="w-full flex items-center p-2 rounded-lg hover:bg-gray-300 transition">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4zM8 8h8M8 12h5M8 16h3" />
                    </svg>
                    Journal de Vente FACT
                </button>
                
            </li>

            <li class="mb-3">
                <button onclick="location.href='CONFIRMED_ORDERS'" class="w-full flex items-center p-2 rounded-lg hover:bg-gray-300 transition">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4zM8 8h8M8 12h5M8 16h3" />
                    </svg>
                    Confirm Order
                </button>
                
            </li>
            
        </ul>




        
    </nav>





    <div class="background background--light">
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
          <span class="button-text">Log Out</span>
        </button>
      </div>
  
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


</html>