<?php
session_start();

// Include navigation helper
require_once __DIR__ . '/navigation_helper.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Store username and role in variables
$username = $_SESSION['username'] ?? 'Guest';
$Role = $_SESSION['Role'] ?? 'Uknown'; // Default role as 'user'

// Define allowed pages for specific roles (add more as needed)
// Use shared loader from navigation_helper.php
$role_allowed_pages = load_permissions();


function is_page_allowed($page, $role, $role_allowed_pages) {
    if (($role_allowed_pages[$role] ?? null) === 'all') {
        return true;
    }
    $allowed = $role_allowed_pages[$role] ?? [];
    return in_array($page, $allowed);
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
            --sidebar-bg: #f1f5fb;
            --sidebar-text: #1e293b;
            --sidebar-hover: #f8fafc;
            --sidebar-active: #6b7280;
            --sidebar-border: #e2e8f0;
            --sidebar-icon: #64748b;
            --sidebar-accent: #3b82f6;
            --sidebar-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        .dark {
            --sidebar-bg: #1e293b;
            --sidebar-text: #f1f5f9;
            --sidebar-hover: #334155;
            --sidebar-active: #6b7280;
            --sidebar-border: #334155;
            --sidebar-icon: #94a3b8;
            --sidebar-accent: #9ca3af;
            --sidebar-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            --glass-bg: rgba(30, 41, 59, 0.95);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        body.dark-mode {
            background: #0f172a;
        }

        .sidebar {
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--sidebar-border);
            box-shadow: var(--sidebar-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 100vh;
            scrollbar-width: thin;
            scrollbar-color: var(--sidebar-border) transparent;
            scroll-behavior: smooth;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: rgba(107, 114, 128, 0.02);
            pointer-events: none;
        }

        /* Navigation Menu Container */
        .sidebar-nav {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--sidebar-border);
            border-radius: 2px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: var(--sidebar-accent);
        }

        .sidebar-nav ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .sidebar-nav button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 0.875rem;
            margin-bottom: 0.125rem;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .sidebar-nav button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
        }

        .sidebar-nav button:hover {
            background: var(--sidebar-hover);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .sidebar-nav button.active {
            background: var(--sidebar-active);
            color: white;
            border: 1px solid var(--sidebar-accent);
            position: relative;
        }

        .sidebar-nav button.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 50%;
            background: white;
            border-radius: 0 2px 2px 0;
        }

        .sidebar-nav .icon {
            color: var(--sidebar-icon);
            font-size: 1.1rem;
        }

        .sidebar hr {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--sidebar-border), transparent);
            margin: 1.5rem 0;
        }

        /* Logo Section - More Compact */
        .logo-section {
            background-color: #f1f5fb;
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .logo-section:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .dark .logo-section {
            background-color: #1e293b;
        }

        .logo-section img {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            width: 120px;
            height: auto;
        }

        .logo-section img:hover {
        }

        /* Notification Styling - More Compact */
        .notification {
            background: rgba(107, 114, 128, 0.05);
            border: 1px solid rgba(107, 114, 128, 0.1);
            border-left: 3px solid var(--sidebar-accent);
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 2px 12px rgba(59, 130, 246, 0.08);
            transition: all 0.3s ease;
        }

        .notification:hover {
            box-shadow: 0 2px 8px rgba(107, 114, 128, 0.1);
        }

        .notification-text {
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--sidebar-text);
            line-height: 1.4;
        }

        .highlight {
            color: var(--sidebar-accent);
            font-weight: 700;
        }

        /* Submenu Animation */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: 1rem;
            border-left: 2px solid var(--sidebar-accent);
            padding-left: 1rem;
            margin-top: 0.5rem;
        }

        .submenu.show {
            max-height: 500px;
        }

        .submenu li {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .submenu.show li {
            opacity: 1;
        }

        /* Chevron Animation */
        .chevron {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--sidebar-accent);
        }

        .chevron.rotate {
            transform: rotate(90deg);
        }

        /* Enhanced Mode Toggles - More Compact */
        .mode-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            background-color: #f1f5fb;
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .mode-toggle:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .dark .mode-toggle {
            background-color: #1e293b;
        }

        .mode-toggle .mode-label {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--sidebar-text);
            gap: 0.5rem;
        }

        .mode-toggle .mode-label i {
            color: var(--sidebar-accent);
            font-size: 1rem;
        }

        .mode-toggle .mode-options {
            display: flex;
            gap: 0.25rem;
            align-items: center;
        }

        .mode-toggle .mode-switch {
            display: flex;
            align-items: center;
        }

        .mode-toggle input[type="radio"] {
            display: none;
        }

        .mode-toggle input[type="radio"] + label {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--sidebar-text);
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--sidebar-border);
            cursor: pointer;
        }

        .dark .mode-toggle input[type="radio"] + label {
            background: rgba(30, 41, 59, 0.5);
            color: var(--sidebar-text);
        }

        .mode-toggle input[type="radio"]:checked + label {
            background: var(--sidebar-accent);
            color: white;
            box-shadow: 0 2px 8px rgba(107, 114, 128, 0.2);
        }

        /* Enhanced Theme Switch - More Compact */
        .mode-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
            background: #e2e8f0;
            border-radius: 26px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .dark .mode-switch {
            background: #334155;
        }

        .mode-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 1px;
            left: 1px;
            right: 1px;
            bottom: 1px;
            background: #ffffff;
            border-radius: 50px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background: var(--sidebar-accent);
            border-radius: 50%;
            box-shadow: 0 1px 4px rgba(107, 114, 128, 0.2);
        }

        input:checked + .slider {
            background: var(--sidebar-bg);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
            background: var(--sidebar-accent);
            box-shadow: 0 1px 4px rgba(107, 114, 128, 0.2);
        }

        /* Logout Button - More Compact */
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            text-align: left;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            font-weight: 600;
            box-shadow: 0 2px 12px rgba(239, 68, 68, 0.08);
            position: relative;
            overflow: hidden;
            font-size: 0.875rem;
        }

        .logout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.15);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }

        .logout-btn:hover .icon {
            color: #b91c1c;
        }

        /* Sidebar Toggle Button */
        #sidebarToggle {
            background: var(--glass-bg);
            color: var(--sidebar-text);
            border: 1px solid var(--glass-border);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            font-size: 1rem;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1010;
        }

        /* Position toggle button next to sidebar when sidebar is visible */
        .sidebar-visible #sidebarToggle {
            left: 276px; /* 16px + 256px (sidebar width w-64 = 16rem = 256px) + 4px gap */
        }

        #sidebarToggle:hover {
            background: var(--sidebar-hover);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #sidebarToggle:active {
        }

        /* Disabled Button Styling */
        .sidebar-nav button.disabled {
            opacity: 0.4;
            pointer-events: none;
            cursor: not-allowed;
            filter: grayscale(100%);
        }

        /* Auto-hide Sidebar */
        .sidebar-auto-hide {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar-auto-hide {
                transform: none !important;
            }

            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1000;
                width: 280px;
                box-shadow: 0 0 50px rgba(0, 0, 0, 0.2);
                border-radius: 0 1rem 1rem 0;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            #sidebarToggle.open {
                left: 300px;
            }

            #sidebarToggle {
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                width: 44px;
                height: 44px;
                top: 20px;
                left: 20px;
            }

            /* Smaller screens - adjust sidebar width */
            .sidebar {
                width: 260px;
                padding: 1rem;
            }

            /* Compact logo section on mobile */
            .logo-section {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }

            .logo-section img {
                width: 60px;
                height: auto;
            }

            /* Compact notification on mobile */
            .notification {
                padding: 0.5rem;
            }

            .notification-text {
                font-size: 0.75rem;
            }

            /* Compact mode toggles on mobile */
            .mode-toggle {
                padding: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .mode-toggle .mode-label {
                font-size: 0.8rem;
            }

            /* Compact navigation buttons on mobile */
            .sidebar-nav button {
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
            }

            /* Compact logout button on mobile */
            .logout-btn {
                padding: 0.75rem;
                font-size: 0.8rem;
            }
        }

        /* Extra small screens */
        @media (max-width: 480px) {
            .sidebar {
                width: 240px;
            }

            #sidebarToggle {
                width: 40px;
                height: 40px;
                top: 16px;
                left: 16px;
            }

            #sidebarToggle.open {
                left: 260px;
            }

            .sidebar {
                padding: 0.75rem;
            }

            .logo-section img {
                width: 50px;
            }

            .notification-text {
                font-size: 0.7rem;
            }

            .mode-toggle .mode-label {
                font-size: 0.75rem;
            }

            .sidebar-nav button {
                padding: 0.5rem 0.625rem;
                font-size: 0.75rem;
            }

            .logout-btn {
                padding: 0.625rem;
                font-size: 0.75rem;
            }
        }

        /* Tablet screens */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .sidebar-visible #sidebarToggle {
                left: 260px; /* Adjust for smaller sidebar width */
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--sidebar-border);
            border-radius: 4px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--sidebar-accent);
            background-clip: content-box;
        }

        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: var(--sidebar-border) transparent;
        }

        /* Ensure smooth scrolling */
        .sidebar {
            scroll-behavior: smooth;
        }

        /* Focus States for Accessibility */
        .sidebar-nav button:focus,
        .mode-toggle input:focus + label,
        .mode-switch:focus {
            outline: 2px solid var(--sidebar-accent);
            outline-offset: 2px;
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }

        .loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200px 100%;
            animation: shimmer 1.5s infinite;
        }

        .dark .loading {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
            background-size: 200px 100%;
        }

        /* Custom Dark Mode Styles */
        body.dark-mode #sidebarToggle {
            background: var(--glass-bg);
            color: var(--sidebar-text);
            border-color: var(--glass-border);
        }

        /* Enhanced Typography */
        .sidebar-nav button,
        .notification-text,
        .mode-toggle label {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
        }

        /* Subtle Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar > * {
        }
    </style>
</head>
<body class="bg-gray-100 transition-colors duration-300">
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle" class="group">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar w-64 h-screen fixed flex flex-col p-5 shadow-xl">
        <!-- Logo and User Info -->
        <div class="logo-section flex flex-col items-center mb-6">
            <img src="assets/log.png" alt="Logo" class="w-20 h-auto mb-4 rounded-lg">
            
            <div class="notification w-full">
                <div class="notification-info">
                    <p class="notification-text text-center">
                        <span class="block font-semibold"><?php echo htmlspecialchars($username); ?></span>
                        <span class="highlight block text-xs">(<?php echo htmlspecialchars($Role); ?>)</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Dark/Light Mode Toggle -->
        <div class="mode-toggle">
            <span class="mode-label">
                <i class="fas fa-circle-half-stroke"></i> Theme
            </span>
            <label class="mode-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
        <!-- Sidebar Mode Toggle -->
        <div class="mode-toggle">
            <span class="mode-label">
                <i class="fas fa-compass"></i> Mode
            </span>
            <div class="mode-options">
                <input type="radio" id="sidebarModeManual" name="sidebarMode" value="manual" checked>
                <label for="sidebarModeManual" class="text-xs">Manual</label>
                <input type="radio" id="sidebarModeAuto" name="sidebarMode" value="auto">
                <label for="sidebarModeAuto" class="text-xs">Auto</label>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav flex-1 overflow-y-auto">
            <?php echo renderNavigationMenu($Role, $role_allowed_pages); ?>
        </nav>

        <!-- Logout Button -->
        <button onclick="logout()" class="logout-btn group mt-auto">
            <i class="fas fa-sign-out-alt icon text-lg"></i>
            <span class="font-semibold">Logout</span>
        </button>
    </div>

    <script>
    
  
        // Navigation function
        function navigateTo(page) {
            // Map page names to actual file names
            const pageMap = {
                'inv': 'inventory/inv.php',
                'inv_admin': 'inventory/inv_admin.php',
                'editnavbar': 'sidebar/editnavbar.php',
                'mail_dashboard': 'mail/dashboard.php',
                'mail_templates': 'mail/templates.php',
                'mail_contacts': 'mail/contacts.php',
                'send_mail': 'mail/send.php',
                'mail_logs': 'mail/logs.php',
                'mail_settings': 'mail/settings.php'
            };
            
            const targetPage = pageMap[page] || page;
            window.location.href = targetPage;
        }

        // Logout function
        function logout() {
            window.location.href = 'db/logout.php';
        }

        // Toggle submenu function
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const button = submenu.previousElementSibling;
            const chevron = button.querySelector(".chevron");
            
            submenu.classList.toggle("show");
            chevron.classList.toggle("rotate");
        }



      

        // Enhanced mobile sidebar toggle with smooth animations
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        function toggleSidebar() {
            const isOpen = sidebar.classList.contains('open');
            const screenWidth = window.innerWidth;

            if (screenWidth <= 768) {
                // Mobile/tablet behavior
                if (isOpen) {
                    sidebar.classList.remove('open');
                    sidebarToggle.classList.remove('open');
                    sidebarToggle.innerHTML = '<i class="fas fa-bars text-lg"></i>';
                    document.body.classList.remove('sidebar-visible');
                    // Reset toggle button position
                    if (screenWidth <= 480) {
                        sidebarToggle.style.left = '16px';
                    } else {
                        sidebarToggle.style.left = '20px';
                    }
                } else {
                    sidebar.classList.add('open');
                    sidebarToggle.classList.add('open');
                    sidebarToggle.innerHTML = '<i class="fas fa-times text-lg"></i>';
                    document.body.classList.add('sidebar-visible');
                    // Move toggle button next to sidebar
                    if (screenWidth <= 480) {
                        sidebarToggle.style.left = '260px';
                    } else {
                        sidebarToggle.style.left = '300px';
                    }
                }
            } else {
                // Desktop manual mode behavior
                if (sidebar.style.transform === 'translateX(-100%)' || sidebar.style.transform === '') {
                    // Show sidebar
                    sidebar.style.transform = 'translateX(0)';
                    sidebarToggle.innerHTML = '<i class="fas fa-times text-lg group-hover:scale-110 transition-transform duration-200"></i>';
                    document.body.classList.add('sidebar-visible');
                } else {
                    // Hide sidebar
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebarToggle.innerHTML = '<i class="fas fa-bars text-lg group-hover:scale-110 transition-transform duration-200"></i>';
                    document.body.classList.remove('sidebar-visible');
                }
            }
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !sidebarToggle.contains(event.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                sidebarToggle.classList.remove('open');
                sidebarToggle.innerHTML = '<i class="fas fa-bars text-lg group-hover:scale-110 transition-transform duration-200"></i>';
                document.body.classList.remove('sidebar-visible');
                // Reset toggle button position
                if (window.innerWidth <= 480) {
                    sidebarToggle.style.left = '16px';
                } else {
                    sidebarToggle.style.left = '20px';
                }
            }
        });

        // Handle window resize for responsive behavior
        window.addEventListener('resize', function() {
            const screenWidth = window.innerWidth;
            
            // Reset sidebar state on resize if needed
            if (screenWidth > 768) {
                // Desktop mode
                sidebar.classList.remove('open');
                sidebarToggle.classList.remove('open');
                sidebarToggle.style.left = ''; // Reset to CSS default
                if (document.body.classList.contains('sidebar-visible')) {
                    sidebar.style.transform = 'translateX(0)';
                    sidebarToggle.innerHTML = '<i class="fas fa-times text-lg group-hover:scale-110 transition-transform duration-200"></i>';
                } else {
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebarToggle.innerHTML = '<i class="fas fa-bars text-lg group-hover:scale-110 transition-transform duration-200"></i>';
                }
            } else {
                // Mobile/tablet mode
                sidebar.style.transform = '';
                if (sidebar.classList.contains('open')) {
                    if (screenWidth <= 480) {
                        sidebarToggle.style.left = '260px';
                    } else {
                        sidebarToggle.style.left = '300px';
                    }
                } else {
                    if (screenWidth <= 480) {
                        sidebarToggle.style.left = '16px';
                    } else {
                        sidebarToggle.style.left = '20px';
                    }
                }
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
                document.body.classList.remove('sidebar-visible');
                document.addEventListener('mousemove', handleSidebarReveal);
                // Add mouseleave event to sidebar to hide it when mouse leaves
                sidebar.addEventListener('mouseleave', handleSidebarAutoHide);
            } else {
                sidebar.classList.remove('sidebar-auto-hide');
                sidebarToggle.style.display = '';
                sidebar.style.transform = 'translateX(0)'; // Show sidebar by default in manual mode
                sidebarToggle.innerHTML = '<i class="fas fa-times text-lg group-hover:scale-110 transition-transform duration-200"></i>'; // Show close icon
                document.body.classList.add('sidebar-visible'); // Sidebar is visible by default in manual mode
                document.removeEventListener('mousemove', handleSidebarReveal);
                sidebar.removeEventListener('mouseleave', handleSidebarAutoHide);
            }
        }

        function handleSidebarReveal(e) {
            if (e.clientX < 30) {
                sidebar.style.transform = 'translateX(0)';
                document.body.classList.add('sidebar-visible');
            }
        }

        function handleSidebarAutoHide(e) {
            // Only hide if in auto mode and mouse is not over sidebar
            if (sidebar.classList.contains('sidebar-auto-hide')) {
                sidebar.style.transform = 'translateX(-100%)';
                document.body.classList.remove('sidebar-visible');
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

        // Theme Toggle Logic with enhanced animations
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const sidebar = document.getElementById('sidebar');

        // Load saved theme with smooth transition
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            sidebar.classList.add('dark');
            themeToggle.checked = true;
        }

        // Add loading state during theme transition
        function toggleTheme() {
            const isDark = this.checked;
            
            // Add transition class for smooth animation
            body.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            sidebar.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            
            setTimeout(() => {
                if (isDark) {
                    body.classList.add('dark-mode');
                    sidebar.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    body.classList.remove('dark-mode');
                    sidebar.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
                
                // Remove transition after animation completes
                setTimeout(() => {
                    body.style.transition = '';
                    sidebar.style.transition = '';
                }, 400);
            }, 50);
        }

        themeToggle.addEventListener('change', toggleTheme);
    </script>
</body>
</html>
