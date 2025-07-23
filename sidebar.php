<?php
session_start();



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Store username and role in variables
$username = $_SESSION['username'] ?? 'Guest';
$Role = $_SESSION['Role'] ?? 'Uknown'; // Default role as 'user'

// Define allowed pages for specific roles (add more as needed)
$role_allowed_pages = [
    'Admin' => 'all', // admin can access all
    'Developer' => 'all',   // dev can access all
    'DRH' => 'all',   // drh can access all
    'Sup Achat' => [
         'Etatstock', 'Product', 'Rotation', 'Recap_Achat', 'DETTE_F',
        'Annual_Recap_A', 'Recap_Vente', 'Annual_Recap_V','ETAT_F', 'ETAT_F_CUMULE','rot_men_achat','rot_men_vente','inventory/inv'
    ],
    'Sup Vente' => [
     'Etatstock', 'Product', 'Rotation', 'Quota', 
        'Recap_Achat', 'Annual_Recap_A', 'Recap_Vente', 'Annual_Recap_V','CONFIRMED_ORDERS' ,'simuler' ,'rot_men_vente'
    ],
    'Comptable' => [
        'mony', 'bank', 'DETTE_F',
        'recap_achat_facturation', 'Recap_Vente_Facturation'
        , 'Journal_Vente','ETAT_F', 'ETAT_F_CUMULE','print','charge'
    ],
    'gestion stock' => [
        'inventory/inv'
    ],

        'stock' => [
        'inventory/inv'
    ],
    'saisie' => [
        'inventory/inv_saisie','inventory/inv'
    ],
];


function is_page_allowed($page, $role, $role_allowed_pages) {
    if (($role_allowed_pages[$role] ?? null) === 'all') {
        return true;
    }
    $allowed = $role_allowed_pages[$role] ?? [];
    return in_array($page, $allowed);
}


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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-text: #1f2937;
            --sidebar-hover: #f3f4f6;
            --sidebar-active: #e5e7eb;
            --sidebar-border: #e5e7eb;
            --sidebar-icon: #4b5563;
        }

        .dark {
            --sidebar-bg: #1f2937;
            --sidebar-text: #f3f4f6;
            --sidebar-hover: #374151;
            --sidebar-active: #4b5563;
            --sidebar-border: #374151;
            --sidebar-icon: #9ca3af;
        }

        .sidebar {
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s ease;
        }

        .sidebar-nav button {
            color: var(--sidebar-text);
        }

        .sidebar-nav button:hover {
            background-color: var(--sidebar-hover);
        }

        .sidebar-nav button.active {
            background-color: var(--sidebar-active);
        }

        .sidebar-nav .icon {
            color: var(--sidebar-icon);
        }

        .sidebar hr {
            border-color: var(--sidebar-border);
        }

        /* Submenu animation */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .submenu.show {
            max-height: 500px;
            transition: max-height 0.5s ease-in;
        }

        .submenu li {
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }

        .submenu.show li {
            opacity: 1;
            transform: translateX(0);
        }

        .submenu.show li:nth-child(1) { transition-delay: 0.1s; }
        .submenu.show li:nth-child(2) { transition-delay: 0.2s; }
        .submenu.show li:nth-child(3) { transition-delay: 0.3s; }
        .submenu.show li:nth-child(4) { transition-delay: 0.4s; }

        /* Chevron rotation */
        .chevron {
            transition: transform 0.3s ease;
        }

        .chevron.rotate {
            transform: rotate(90deg);
        }

        /* Mode switch */
        .mode-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .mode-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 30px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4b5563;
        }

        input:checked + .slider:before {
            transform: translateX(30px);
        }

        /* Notification styling */
        .notification {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 4px solid rgb(59, 130, 246);
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }

        .notification-text {
            font-weight: 500;
        }

        .highlight {
            color: rgb(59, 130, 246);
            font-weight: 600;
        }

        /* Logout button */
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
            background-color: var(--sidebar-hover);
        }

        .logout-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .logout-btn:hover .icon {
            color: #ef4444;
        }

        /* Sidebar toggle button */
        #sidebarToggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            border: 1px solid var(--sidebar-border);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        #sidebarToggle:hover {
            background-color: var(--sidebar-hover);
        }

        /* Disabled button styling */
        .sidebar-nav button.disabled {
            opacity: 0.5;
            pointer-events: none;
            cursor: not-allowed;
        }

        /* Auto-hide sidebar */
        .sidebar-auto-hide {
            transition: transform 0.3s;
        }
        @media (max-width: 768px) {
            .sidebar-auto-hide {
                transform: none !important;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 999;
                width: 280px;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            #sidebarToggle.open {
                left: 300px;
            }
        }

        .dark .text-xs{
            color: var(--sidebar-text) !important;
        }

        /* Fix mode toggle backgrounds in dark mode */
        .dark .mode-toggle {
            background-color: #1f2937;
            color: var(--sidebar-text) !important;
        }

        /* Ensure all mode toggle spans use correct color */
        .mode-toggle span {
            color: var(--sidebar-text) !important;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle" class="dark:bg-gray-800 dark:text-white dark:border-gray-600">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar w-64 h-screen fixed overflow-y-auto p-4 shadow-lg">
        <!-- Logo and User Info -->
        <div class="flex flex-col items-center mb-6">
            <img src="assets/log.png" alt="Logo" class="w-40 h-auto mb-4">
            
            <div class="notification w-full">
                <div class="notification-info">
                    <p class="notification-text">
                        <span>Welcome:</span>
                        <span><?php echo htmlspecialchars($username); ?></span>
                        <span class="highlight">(<?php echo htmlspecialchars($Role); ?>)</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Dark/Light Mode Toggle -->
        <div class="mode-toggle mb-6 flex items-center justify-between px-2 py-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                <i class="fas fa-moon mr-2"></i>Theme
            </span>
            <label class="mode-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
        <!-- Sidebar Mode Toggle -->
        <div class="mode-toggle mb-6 flex items-center justify-between px-2 py-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                <i class="fas fa-compass mr-2"></i> Mode
            </span>
            <div class="flex gap-2">
                <input type="radio" id="sidebarModeManual" name="sidebarMode" value="manual" checked>
                <label for="sidebarModeManual" class="text-xs">Manual</label>
                <input type="radio" id="sidebarModeAuto" name="sidebarMode" value="auto">
                <label  for="sidebarModeAuto" class="text-xs ">Auto</label>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="space-y-1">
                <!-- Admin Section -->
                <li>
                    <?php $page = 'admin'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                    <button <?php if (!$disabled) {?>onclick="navigateTo('admin')"<?php } ?> class="w-full flex items-center text-left gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                        <i class="fas fa-tools icon"></i>
                        <span class="flex-1">Admin</span>
                    </button>
                </li>

                <hr class="my-2 border-gray-200 dark:border-gray-600">

                <!-- FONDS PROPRE Section -->
                <li>
                    <button onclick="toggleSubmenu('fond-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                        <div class="flex items-center text-left gap-3 flex-1">
                            <i class="fas fa-coins icon"></i>
                            <span class="flex-1">FONDS PROPRE</span>
                        </div>
                        <i class="fas fa-chevron-right chevron text-xs"></i>
                    </button>
                    <ul id="fond-submenu" class="submenu pl-4">
                        <li>
                            <?php $page = 'mony'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('mony2')"<?php } ?> class="w-full flex items-center text-left gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-chart-line icon text-sm"></i>
                                <span class="">Analysis</span>
                            </button>

                        </li>
                        
                        <li>
                            <?php $page = 'print'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('print')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-print icon text-sm"></i>
                                <span class="">Print</span>
                            </button>
                        </li>                        
                        <li>
                            <?php $page = 'bank'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('bank')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-university icon text-sm"></i>
                                <span class="">Banks</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'recouverement'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('recouverement')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-money-bill-wave icon text-sm"></i>
                                <span class="">Recouvrement</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'charge'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('charge')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-credit-card icon text-sm"></i>
                                <span class="">Charges</span>
                            </button>
                        </li>
                    </ul>
                </li>

                <!-- DETTES Section -->
                <li>
                    <button onclick="toggleSubmenu('dettes-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-handshake icon"></i>
                            <span class="flex-1">DETTES</span>
                        </div>
                        <i class="fas fa-chevron-right chevron text-xs"></i>
                    </button>
                    <ul id="dettes-submenu" class="submenu pl-4">
                        <li>
                            <?php $page = 'DETTE_F'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('DETTE_F')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-file-invoice-dollar icon text-sm"></i>
                                <span class="">DETTE_F</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'ETAT_F'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('ETAT_F')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-chart-line icon text-sm"></i>
                                <span class="">ETAT_F</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'ETAT_F_CUMULE'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('ETAT_F_CUMULE')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-file-invoice icon text-sm"></i>
                                <span class="">ETAT_F_CUMULE</span>
                            </button>
                        </li>
                    </ul>
                </li>

                <hr class="my-2 border-gray-200 dark:border-gray-600">

                <!-- PRODUCTS Section -->
                <li>
                    <button onclick="toggleSubmenu('products-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-bag icon"></i>
                            <span class="flex-1">PRODUCTS</span>
                        </div>
                        <i class="fas fa-chevron-right chevron text-xs"></i>
                    </button>
                    <ul id="products-submenu" class="submenu pl-4">
                        <li>
                            <?php $page = 'Etatstock'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Etatstock')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-boxes icon text-sm"></i>
                                <span class=" ">État de Stock</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Mouvement Stock'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Mouvement_Stock')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-exchange-alt icon"></i>
                                <span>Mouvement Stock</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Product'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Product')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-percentage icon text-sm"></i>
                                <span class="">Marge</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Rotation'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Rotation')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-sync-alt icon text-sm"></i>
                                <span class="">Rotation</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Quota'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Quota')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-bullseye icon text-sm"></i>
                                <span class="">Quota Produit</span>
                            </button>
                        </li>
                    </ul>
                </li>

                <hr class="my-2 border-gray-200 dark:border-gray-600">

                <!-- RECAPS ACHAT Section -->
                <li>
                    <button onclick="toggleSubmenu('recapsa-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cart-plus icon"></i>
                            <span class="flex-1">RECAPS ACHAT</span>
                        </div>
                        <i class="fas fa-chevron-right chevron text-xs"></i>
                    </button>
                    <ul id="recapsa-submenu" class="submenu pl-4">
                        <li>
                            <?php $page = 'Recap_Achat'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Recap_Achat')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-shopping-cart icon text-sm"></i>
                                <span class="">Recap Achat</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'recap_achat_facturation'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('recap_achat_facturation')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-file-invoice icon text-sm"></i>
                                <span class="">Recap Achat F</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Annual_Recap_A'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Annual_Recap_A')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-calendar-alt icon text-sm"></i>
                                <span class="">Annual Recap</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'rot_men_achat'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('rot_men_achat')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-calendar-alt icon text-sm"></i>
                                <span class="">rot_men_achat</span>
                            </button>
                        </li>
                    </ul>
                </li>

                <!-- RECAPS VENTE Section -->
                <li>
                    <button onclick="toggleSubmenu('recapsv-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cash-register icon"></i>
                            <span class="flex-1">RECAPS VENTE</span>
                        </div>
                        <i class="fas fa-chevron-right chevron text-xs"></i>
                    </button>
                    <ul id="recapsv-submenu" class="submenu pl-4">
                        <li>
                            <?php $page = 'Recap_Vente'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Recap_Vente')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-money-bill-wave icon text-sm"></i>
                                <span class="">Recap Vente</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Recap_Vente_Facturation'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Recap_Vente_Facturation')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-file-invoice-dollar icon text-sm"></i>
                                <span class="">Recap Vente F</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'Annual_Recap_V'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('Annual_Recap_V')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-calendar-check icon text-sm"></i>
                                <span class="">Annual Recap</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'CONFIRMED_ORDERS'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('CONFIRMED_ORDERS')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-check-circle icon text-sm"></i>
                                <span class="text-sm">Confirm Order</span>
                            </button>
                        </li>
                   
                        <li>
                            <?php $page = 'rot_men_vente'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('rot_men_vente')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-calendar-alt icon text-sm"></i>
                                <span class="">rot_men_vente</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'simulation'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('simuler')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-calculator icon text-sm"></i>
                                <span class="">Simulation</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'retour ORM'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('retour')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-undo icon text-sm"></i>
                                <span class="">retour ORM</span>
                            </button>
                        </li>
                    </ul>
                </li>

                <hr class="my-2 border-gray-200 dark:border-gray-600">

                <!-- INVENTORY Section -->
                <li>
                    <button onclick="toggleSubmenu('inventory-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-clipboard-list icon"></i>
                            <span class="flex-1">INVENTORY</span>
                        </div>
                        <i class="fas fa-chevron-right chevron text-xs"></i>
                    </button>
                    <ul id="inventory-submenu" class="submenu pl-4">
                        <li>
                            <?php $page = 'inventory/inv'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('inv')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-plus-circle icon text-sm"></i>
                                <span class="">Create Inventory</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'inventory/inv_admin'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('inv_admin')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-cogs icon text-sm"></i>
                                <span class="">Manage Inventory</span>
                            </button>
                        </li>
                        <li>
                            <?php $page = 'inventory/inv_saisie'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                            <button <?php if (!$disabled) {?>onclick="navigateTo('inv_saisie')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                                <i class="fas fa-edit icon text-sm"></i>
                                <span class="">Saisie Inventory</span>
                            </button>
                        </li>

                    </ul>
                </li>

                <hr class="my-2 border-gray-200 dark:border-gray-600">

                <!-- Journal de Vente Section -->
                <li>
                    <?php $page = 'Journal_Vente'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                    <button <?php if (!$disabled) {?>onclick="navigateTo('Journal_Vente')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                        <i class="fas fa-book icon"></i>
                        <span>Journal de Vente</span>
                    </button>
                </li>

                <hr class="my-2 border-gray-200 dark:border-gray-600">

                <!-- Affectation Section -->
                <li>
                    <?php $page = 'AFFECTATION'; $disabled = !is_page_allowed($page, $Role, $role_allowed_pages); ?>
                    <button <?php if (!$disabled) {?>onclick="navigateTo('AFFECTATION')"<?php } ?> class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700<?php if($disabled) echo ' disabled'; ?>">
                        <i class="fas fa-tasks icon"></i>
                        <span>Affectation</span>
                    </button>
                </li>
                
            </ul>

            <!-- Logout Button -->
            <button onclick="logout()" class="logout-btn mt-8">
                <i class="fas fa-sign-out-alt icon"></i>
                <span>Logout</span>
            </button>
        </nav>
    </div>

    <script>
    
  
        // Navigation function
        function navigateTo(page) {
            // Map page names to actual file names
            const pageMap = {
                'inv': 'inventory/inv.php',
                'inv_admin': 'inventory/inv_admin.php'
            };
            
            const targetPage = pageMap[page] || page;
            window.location.href = targetPage;
        }

        // Logout function
        function logout() {
            window.location.href = 'db/logout.php';
        }



      

        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            sidebarToggle.classList.toggle('open');
            
            if (sidebar.classList.contains('open')) {
                sidebarToggle.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !sidebarToggle.contains(event.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                sidebarToggle.classList.remove('open');
                sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });

        // Make current page active in sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop().split('.')[0];
            const buttons = document.querySelectorAll('nav button');
            
            buttons.forEach(button => {
                if (button.getAttribute('onclick')?.includes(currentPage)) {
                    button.classList.add('active');
                    
                    // Open parent submenu if this is a submenu item
                    const submenuItem = button.closest('.submenu');
                    if (submenuItem) {
                        const parentButton = submenuItem.previousElementSibling;
                        submenuItem.classList.add('show');
                        parentButton.querySelector('.chevron').classList.add('rotate');
                    }
                }
            });
        });

        // Sidebar Mode Toggle Logic
        const sidebarModeManual = document.getElementById('sidebarModeManual');
        const sidebarModeAuto = document.getElementById('sidebarModeAuto');

        function setSidebarMode(mode) {
            if (mode === 'auto') {
                sidebar.classList.add('sidebar-auto-hide');
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('open');
                sidebar.style.transform = 'translateX(-100%)';
                document.addEventListener('mousemove', handleSidebarReveal);
                // Add mouseleave event to sidebar to hide it when mouse leaves
                sidebar.addEventListener('mouseleave', handleSidebarAutoHide);
            } else {
                sidebar.classList.remove('sidebar-auto-hide');
                sidebarToggle.style.display = '';
                sidebar.style.transform = '';
                document.removeEventListener('mousemove', handleSidebarReveal);
                sidebar.removeEventListener('mouseleave', handleSidebarAutoHide);
            }
        }

        function handleSidebarReveal(e) {
            if (e.clientX < 30) {
                sidebar.style.transform = 'translateX(0)';
            }
        }

        function handleSidebarAutoHide(e) {
            // Only hide if in auto mode and mouse is not over sidebar
            if (sidebar.classList.contains('sidebar-auto-hide')) {
                sidebar.style.transform = 'translateX(-100%)';
            }
        }

        sidebarModeManual.addEventListener('change', function() {
            if (this.checked) setSidebarMode('manual');
        });
        sidebarModeAuto.addEventListener('change', function() {
            if (this.checked) setSidebarMode('auto');
        });

        // Set initial mode
        if (sidebarModeAuto.checked) {
            setSidebarMode('auto');
        } else {
            setSidebarMode('manual');
        }
    </script>
</body>
</html>
