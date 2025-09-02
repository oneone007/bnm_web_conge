<?php
/**
 * Navigation Helper Functions
 * This file contains functions to load and render navigation from JSON
 */

/**
 * Load navigation data from JSON file
 */
function loadNavigationData() {
    $navigationFile = __DIR__ . '/navigation.json';
    
    if (file_exists($navigationFile)) {
        $jsonContent = file_get_contents($navigationFile);
        if ($jsonContent !== false) {
            $data = json_decode($jsonContent, true);
            if ($data !== null && isset($data['navigation'])) {
                return $data['navigation'];
            }
        }
    }
    
    // Return empty array if file doesn't exist or is invalid
    return [];
}

/**
 * Check if user has required role for navigation item
 */
function hasRequiredRole($item, $userRole) {
    if (!isset($item['roleRequired']) || empty($item['roleRequired'])) {
        return true; // No role restriction
    }
    
    return in_array($userRole, $item['roleRequired']);
}

/**
 * Check if page is allowed based on permissions
 */
function isItemAllowed($item, $userRole, $roleAllowedPages) {
    // Check role requirements first
    if (!hasRequiredRole($item, $userRole)) {
        return false;
    }
    
    // Check permissions if item has a permission page
    if (isset($item['permissionPage'])) {
        return is_page_allowed($item['permissionPage'], $userRole, $roleAllowedPages);
    }
    
    return true;
}

/**
 * Render a navigation item
 */
function renderNavigationItem($item, $userRole, $roleAllowedPages, $level = 0) {
    // Debug: Check item structure
    if (!isset($item['type'])) {
        return '<li><span class="text-red-500">Error: Item missing type</span></li>';
    }
    
    if ($item['type'] === 'separator') {
        return '<hr class="my-2 border-gray-200 dark:border-gray-600">';
    }
    
    if (!isItemAllowed($item, $userRole, $roleAllowedPages)) {
        return '';
    }
    
    $html = '';
    $hoverClass = $item['hoverClass'] ?? 'hover:bg-gray-200 dark:hover:bg-gray-700';
    
    if ($item['type'] === 'section' && isset($item['submenu'])) {
        // Render section with submenu
        $submenuId = $item['id'] . '-submenu';
        $html .= '<li>';
        $html .= '<button onclick="toggleSubmenu(\'' . $submenuId . '\')" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg ' . $hoverClass . '">';
        $html .= '<div class="flex items-center text-left gap-3 flex-1">';
        if (isset($item['icon'])) {
            $html .= '<i class="' . htmlspecialchars($item['icon']) . ' icon"></i>';
        }
        $html .= '<span class="flex-1">' . htmlspecialchars($item['title'] ?? $item['id']) . '</span>';
        $html .= '</div>';
        $html .= '<i class="fas fa-chevron-right chevron text-xs"></i>';
        $html .= '</button>';
        
        // Render submenu items
        $html .= '<ul id="' . $submenuId . '" class="submenu pl-4">';
        foreach ($item['submenu'] as $subItem) {
            $html .= renderNavigationItem($subItem, $userRole, $roleAllowedPages, $level + 1);
        }
        $html .= '</ul>';
        $html .= '</li>';
    } else if ($item['type'] === 'page') {
        // Render single page item
        $disabled = !isItemAllowed($item, $userRole, $roleAllowedPages);
        $disabledClass = $disabled ? ' disabled' : '';
        $onclick = $disabled ? '' : 'onclick="navigateTo(\'' . htmlspecialchars($item['page'] ?? $item['id']) . '\')"';
        
        $html .= '<li>';
        $html .= '<button ' . $onclick . ' class="w-full flex items-center gap-3 px-4 py-3 rounded-lg ' . $hoverClass . $disabledClass . '">';
        if (isset($item['icon'])) {
            $html .= '<i class="' . htmlspecialchars($item['icon']) . ' icon"></i>';
        }
        $html .= '<span>' . htmlspecialchars($item['title'] ?? $item['id']) . '</span>';
        $html .= '</button>';
        $html .= '</li>';
    }
    
    return $html;
}

/**
 * Render the complete navigation menu
 */
function renderNavigationMenu($userRole, $roleAllowedPages) {
    $navigationData = loadNavigationData();
    
    // Debug: Check if navigation data is loaded
    if (empty($navigationData)) {
        return '<ul class="space-y-1"><li><span class="text-red-500">Error: Navigation data not found</span></li></ul>';
    }
    
    $html = '<ul class="space-y-1">';
    
    foreach ($navigationData as $item) {
        $html .= renderNavigationItem($item, $userRole, $roleAllowedPages);
    }
    
    $html .= '</ul>';
    return $html;
}

/**
 * Get toggle submenu JavaScript function
 */
function getToggleSubmenuScript() {
    return '
    function toggleSubmenu(submenuId) {
        const submenu = document.getElementById(submenuId);
        const button = submenu.previousElementSibling;
        const chevron = button.querySelector(".chevron");
        
        submenu.classList.toggle("show");
        chevron.classList.toggle("rotate");
    }
    ';
}
?>
