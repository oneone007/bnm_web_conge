<?php
/**
 * Page Protection System
 * 
 * This file should be included at the top of any page that requires role-based access control.
 * It checks if the user has permission to access the current page based on their role.
 * 
 * Usage: include 'protection.php'; at the top of your PHP file
 * 
 * To specify a custom page identifier, define $page_identifier before including this file:
 * Example: $page_identifier = 'Product'; include 'protection.php';
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function checkUserLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['Role'])) {
        // Check if headers haven't been sent yet before redirecting
        if (!headers_sent()) {
            header("Location: 403");
            exit();
        } else {
            // If headers were already sent, use JavaScript redirect
            echo '<script>window.location.href="403";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=403"></noscript>';
            echo 'Access Denied. <a href="403">Click here</a> if you are not redirected.';
            exit();
        }
    }
}

/**
 * Load permissions - hardcoded permissions array
 */
function loadPermissions() {
    return [
        "Admin" => "all",
        "Developer" => "all",
        "DRH" => "all",
        "Sup Achat" => [
            "Annual_Recap_A",
            "Annual_Recap_V",
            "DETTE_F",
            "ETAT_F",
            "ETAT_F_CUMULE",
            "Etatstock",
            "Product",
            "Recap_Achat",
            "Recap_Vente",
            "Rotation",
            "inventory/inv",
            "rot_men_achat",
            "rot_men_vente"
        ],
        "Sup Vente" => [
            "Annual_Recap_A",
            "Annual_Recap_V",
            "CONFIRMED_ORDERS",
            "Etatstock",
            "Product",
            "Quota",
            "Recap_Achat",
            "Recap_Vente",
            "Rotation",
            "rot_men_vente",
            "simuler"
        ],
        "Comptable" => [
            "DETTE_F",
            "ETAT_F",
            "ETAT_F_CUMULE",
            "Journal_Vente",
            "Recap_Vente_Facturation",
            "bank",
            "charge",
            "mony",
            "print",
            "recap_achat_facturation"
        ],
        "gestion stock" => [
            "inventory/inv"
        ],
        "stock" => [
            "inventory/inv"
        ],
        "saisie" => [
            "inventory/inv",
            "inventory/inv_saisie"
        ]
    ];
}

/**
 * Check if a page is allowed for a specific role
 */
function isPageAllowed($page, $role, $permissions) {
    // Check if role exists in permissions
    if (!isset($permissions[$role])) {
        return false;
    }
    
    // If role has 'all' access, allow everything
    if ($permissions[$role] === 'all') {
        return true;
    }
    
    // Check if page is in the allowed pages array for this role
    $allowedPages = $permissions[$role];
    if (is_array($allowedPages)) {
        return in_array($page, $allowedPages);
    }
    
    return false;
}

/**
 * Get page identifier from filename or custom identifier
 */
function getPageIdentifier() {
    global $page_identifier;
    
    // If custom page identifier is set, use it
    if (isset($page_identifier) && !empty($page_identifier)) {
        return $page_identifier;
    }
    
    // Get the current file name without extension
    $currentFile = basename($_SERVER['PHP_SELF'], '.php');
    
    // Map common file names to permission identifiers
    $pageMapping = [
        'annual_recap_a' => 'Annual_Recap_A',
        'annual_recap_v' => 'Annual_Recap_V',
        'dette_f' => 'DETTE_F',
        'etat_f' => 'ETAT_F',
        'etat_f_cumule' => 'ETAT_F_CUMULE',
        'Etatstock' => 'Etatstock',
        'product' => 'Product',
        'recap_achat' => 'Recap_Achat',
        'recap_vente' => 'Recap_Vente',
        'recapvente' => 'Recap_Vente',
        'rotation' => 'Rotation',
        'rot_men_achat' => 'rot_men_achat',
        'rot_men_vente' => 'rot_men_vente',
        'confirmed_orders' => 'CONFIRMED_ORDERS',
        'quota' => 'Quota',
        'simuler' => 'simuler',
        'simulation' => 'simuler',
        'journal' => 'Journal_Vente',
        'recap_vente_facturation' => 'Recap_Vente_Facturation',
        'recapvente_fact' => 'Recap_Vente_Facturation',
        'bank' => 'bank',
        'charge' => 'charge',
        'charges_dashboard' => 'charge',
        'mony' => 'mony',
        'moneyv2' => 'mony',
        'print' => 'print',
        'recap_achat_facturation' => 'recap_achat_facturation'
    ];
    
    // Check if there's a mapping for the current file
    $lowerFile = strtolower($currentFile);
    if (isset($pageMapping[$lowerFile])) {
        return $pageMapping[$lowerFile];
    }
    
    // Handle inventory pages
    if (strpos($lowerFile, 'inv') !== false) {
        if (strpos($lowerFile, 'saisie') !== false) {
            return 'inventory/inv_saisie';
        }
        return 'inventory/inv';
    }
    
    // Return the filename as is if no mapping found
    return $currentFile;
}

/**
 * Main protection function
 */
function protectPage() {
    // Check if user is logged in
    checkUserLogin();
    
    // Get user role
    $userRole = $_SESSION['Role'];
    
    // Load permissions
    $permissions = loadPermissions();
    
    // Get page identifier
    $pageId = getPageIdentifier();
    
    // Check if page is allowed for this role
    if (!isPageAllowed($pageId, $userRole, $permissions)) {
        // Log access attempt for security monitoring (only if possible)
        $logMessage = date('Y-m-d H:i:s') . " - Access denied for user: " . 
                     ($_SESSION['username'] ?? 'Unknown') . 
                     " (Role: $userRole) trying to access: $pageId" . PHP_EOL;
        
        // Try to log, but don't fail if we can't
        $logFile = __DIR__ . '/access_denied.log';
        if (is_writable(dirname($logFile)) || (file_exists($logFile) && is_writable($logFile))) {
            @error_log($logMessage, 3, $logFile);
        }
        
        // Check if headers haven't been sent yet before redirecting
        if (!headers_sent()) {
            header("Location: 403");
            exit();
        } else {
            // If headers were already sent, use JavaScript redirect
            echo '<script>window.location.href="403";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=403"></noscript>';
            echo 'Access Denied. <a href="403">Click here</a> if you are not redirected.';
            exit();
        }
    }
}

// Auto-run protection unless explicitly disabled
if (!defined('DISABLE_AUTO_PROTECTION') || DISABLE_AUTO_PROTECTION !== true) {
    protectPage();
}

// Make functions available globally for manual use
$GLOBALS['protectPage'] = 'protectPage';
$GLOBALS['isPageAllowed'] = 'isPageAllowed';
$GLOBALS['loadPermissions'] = 'loadPermissions';
?>
