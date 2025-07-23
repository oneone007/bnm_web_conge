<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output, but log them
ini_set('log_errors', 1);

session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['username'])) {
    // If it's an AJAX request, return JSON error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit();
    }
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Vente', 'Comptable'])) {
    // If it's an AJAX request, return JSON error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit();
    }
    header("Location: Acess_Denied");    
    exit();
}

// Handle POST request for saving inventory data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start output buffering to catch any unexpected output
    ob_start();
    
    // Clean output buffer and set content type to JSON
    ob_clean();
    header('Content-Type: application/json');
    
    try {
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }
        
        // Validate required fields
        if (!isset($data['title']) || !isset($data['items']) || !is_array($data['items'])) {
            throw new Exception('Missing required fields: title and items');
        }
        
        // Add created_by from session
        $data['created_by'] = $_SESSION['username'];
        
        // Call Python Flask API to save inventory
        $pythonApiUrl = 'http://localhost:5003/inventory/save';
        
        // Prepare cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('Failed to connect to Python API');
        }
        
        $result = json_decode($response, true);
        
        if (!$result) {
            throw new Exception('Invalid response from Python API: ' . $response);
        }
        
        // Return the result from Python API
        ob_clean();
        echo json_encode($result);
        exit();
        
    } catch (Exception $e) {
        error_log("Error in inv.php: " . $e->getMessage());
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        /* Custom styles for the inventory page */
        .inventory-container {
            max-width: 1400px;
            margin: 0 auto;

        }

        
        .section-header {
            background-color: #f2f2f2;
            color: #333;
            padding: 1rem;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            text-align: center;
            font-size: 1.2rem;
            border: 1px solid #ddd;
            border-bottom: none;
        }
        
        .entry-header {
            background-color: #f2f2f2;
            color: #333;
        }
        
        .sortie-header {
            background-color: #f2f2f2;
            color: #333;
        }
        
        .search-container {
            position: relative;
            width: 100%;
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Category filter active state */
        .category-filter-active {
            border-color: #f59e0b !important;
            background-color: #fef3c7 !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1) !important;
        }
        
        .dark .category-filter-active {
            background-color: #451a03 !important;
            border-color: #f59e0b !important;
        }
        
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        
        .dropdown-item {
            padding: 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f9fafb;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        /* Pagination styles for dropdown */
        .dropdown-pagination {
            padding: 0.5rem;
            text-align: center;
            background-color: #f8f9fa;
        }
        
        .dark .dropdown-pagination {
            background-color: #374151;
        }
        
        .pagination-btn {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0 0.125rem;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .pagination-btn:active {
            transform: translateY(0);
        }
        
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
            background-color: white;
        }
        
        .inventory-table th,
        .inventory-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            word-wrap: break-word;
            white-space: normal;
        }
        
        .inventory-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            cursor: pointer;
        }
        
        .inventory-table th:hover {
            background-color: #e5e7eb;
        }
        
        .inventory-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .inventory-table tbody tr:hover {
            background-color: #eff6ff;
            transition: background-color 0.2s ease;
        }
        
        /* Table container styling to match quota.php */
        .table-container {
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .dark .table-container {
            background-color: #1f2937;
        }

        
        /* Column specific widths for separate tables */
        .inventory-table th:nth-child(1), /* Product */
        .inventory-table td:nth-child(1) {
            width: 25%;
            min-width: 150px;
        }
        
        .inventory-table th:nth-child(2), /* QTY */
        .inventory-table td:nth-child(2) {
            width: 12%;
            text-align: center;
        }
        
        .inventory-table th:nth-child(3), /* Date */
        .inventory-table td:nth-child(3) {
            width: 15%;
        }
        
        .inventory-table th:nth-child(4), /* Lot */
        .inventory-table td:nth-child(4) {
            width: 15%;
            position: relative;
        }
        
        .inventory-table th:nth-child(5), /* PPA */
        .inventory-table td:nth-child(5) {
            width: 12%;
            text-align: right;
        }
        
        .inventory-table th:nth-child(6), /* QTY_DISPO */
        .inventory-table td:nth-child(6) {
            width: 12%;
            text-align: center;
        }
        
        .inventory-table th:nth-child(7), /* Action */
        .inventory-table td:nth-child(7) {
            width: 9%;
            text-align: center;
        }
        
        /* Direct data display in table cells */
        .data-display {
            display: block;
            padding: 0.375rem;
            font-size: 0.875rem;
            color: #1f2937;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .dark .data-display {
            color: #e5e7eb;
        }
        
        /* Product details table styling - match quota table exactly */
        .product-details-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .product-details-row:hover {
            background-color: #f9f9f9;
        }
        
        .product-details-row.selected {
            background-color: #3b82f6 !important;
            color: white !important;
        }
        
        .product-details-row.selected:hover {
            background-color: #2563eb !important;
        }
        
        .product-details-row td {
            color: #000;
        }
        
        .product-details-row.selected td {
            color: white !important;
        }
        
        .select-radio {
            cursor: pointer;
            width: 16px;
            height: 16px;
            accent-color: #3b82f6;
        }
        
        /* Header styling for main categories - clean professional look */
        .product-header {
            background-color: #f2f2f2;
            color: #333;
        }
        
        .entry-header {
            background-color: #f2f2f2;
            color: #333;
            text-align: center;
        }
        
        /* Responsive design for smaller screens */
        @media (max-width: 1279px) {
            .inventory-container {
                padding: 0.5rem;
            }
            
            .grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .inventory-table {
                font-size: 0.875rem;
            }
            
            .inventory-table th,
            .inventory-table td {
                padding: 0.5rem 0.25rem;
            }
            
            .input-field {
                padding: 0.375rem;
                font-size: 0.875rem;
            }
            
            .section-header {
                font-size: 1rem;
                padding: 0.75rem;
            }
        }
        
        @media (max-width: 768px) {
            .inventory-table {
                font-size: 0.75rem;
            }
            
            .inventory-table th,
            .inventory-table td {
                padding: 0.375rem 0.125rem;
            }
            
            .input-field {
                padding: 0.25rem;
                font-size: 0.75rem;
            }
            
            .btn-remove {
                padding: 0.25rem;
                font-size: 0.75rem;
            }
        }
        
        /* Improved stacked table layout */
        .table-container {
            width: 100%;
            margin-bottom: 1.5rem;
        }
        
        .table-container:last-child {
            margin-bottom: 0;
        }
        
        /* Enhanced section headers for stacked layout */
        .section-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1e293b;
            padding: 1.25rem;
            border-radius: 12px 12px 0 0;
            font-weight: 600;
            text-align: center;
            font-size: 1.25rem;
            border: 2px solid #e2e8f0;
            border-bottom: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .dark .section-header {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            color: #f1f5f9;
            border-color: #4b5563;
        }
        
        /* Distinguish between ENTRY and SORTIE tables */
        .entry-table .section-header {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
            border-color: #10b981;
        }
        
        .sortie-table .section-header {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-color: #f59e0b;
        }
        
        .dark .entry-table .section-header {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: #a7f3d0;
            border-color: #10b981;
        }
        
        .dark .sortie-table .section-header {
            background: linear-gradient(135deg, #451a03 0%, #92400e 100%);
            color: #fde68a;
            border-color: #f59e0b;
        }
        
        /* Better table styling for stacked layout */
        .inventory-table {
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }
        
        .table-container .overflow-x-auto {
            border-radius: 0 0 12px 12px;
        }
        
        /* Mobile-first responsive improvements */
        @media (max-width: 640px) {
            .inventory-container {
                padding: 0.25rem;
            }
            
            .section-header {
                font-size: 1rem;
                padding: 1rem;
            }
            
            .inventory-table {
                font-size: 0.75rem;
            }
            
            .inventory-table th,
            .inventory-table td {
                padding: 0.25rem 0.125rem;
                min-width: 60px;
            }
            
            .input-field {
                padding: 0.25rem;
                font-size: 0.75rem;
                min-width: 50px;
            }
            
            .btn-remove {
                padding: 0.125rem 0.25rem;
                font-size: 0.75rem;
                min-width: 30px;
            }
            
            /* Stack action buttons vertically on very small screens */
            .flex-wrap {
                flex-direction: column;
            }
            
            .min-w-\[200px\] {
                min-width: 100%;
            }
            
            .min-w-\[120px\],
            .min-w-\[140px\] {
                min-width: 100%;
            }
        }
        
        /* Tablet improvements */
        @media (min-width: 641px) and (max-width: 1024px) {
            .inventory-container {
                padding: 1rem;
            }
            
            .section-header {
                font-size: 1.125rem;
                padding: 1.25rem;
            }
            
            .inventory-table {
                font-size: 0.875rem;
            }
            
            .inventory-table th,
            .inventory-table td {
                padding: 0.5rem 0.375rem;
            }
            
            .input-field {
                padding: 0.375rem;
                font-size: 0.875rem;
            }
        }
        
        .sortie-header {
            background-color: #f2f2f2;
            color: #333;
            text-align: center;
        }
        
        .action-header {
            background-color: #f2f2f2;
            color: #333;
        }
        
        /* Make only QTY fields editable with visual distinction */
        .qty-editable {
            background-color: #fef9c3 !important;
            border: 2px solid #ca8a04 !important;
            font-weight: 600;
            color: #854d0e !important;
        }
        
        .qty-editable:focus {
            background-color: #fef3c7 !important;
            border-color: #d97706 !important;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.2) !important;
        }
        
        /* Manual editable fields styling */
        .manual-editable {
            background-color: #fef3c7 !important;
            border: 2px solid #f59e0b !important;
            font-weight: 500;
            color: #92400e !important;
        }
        
        .manual-editable:focus {
            background-color: #fef3c7 !important;
            border-color: #d97706 !important;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.2) !important;
        }
        
        .manual-editable::placeholder {
            color: #a78bfa !important;
        }
        
        /* Enhanced styling for manual product name field */
        .manual-editable[name="product"] {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Manual entry row styling */
        .manual-entry-row {
            background-color: #fef3c7 !important;
        }
        
        .manual-entry-row:hover {
            background-color: #fef3c7 !important;
        }
        
        /* Read-only field styling - show text directly without input appearance */
        .readonly-field {
            background-color: transparent !important;
            color: #1f2937 !important;
            cursor: default !important;
            border: none !important;
            padding: 0.375rem 0 !important;
            font-weight: 500;
        }
        
        .input-field {
            width: 100%;
            padding: 0.375rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        /* Additional styling for direct text display */
        .table-text {
            display: block;
            padding: 0.375rem 0;
            font-size: 0.875rem;
            color: #000;
        }
        
        .dark .table-text {
            color: #e5e7eb;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .input-field[readonly] {
            background-color: #f9fafb;
            cursor: not-allowed;
        }
        
        /* Lot warning styling */
        .lot-warning {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.7rem;
            padding: 0.25rem;
            border-radius: 0 0 4px 4px;
            border: 1px solid #f59e0b;
            border-top: none;
            z-index: 10;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-style: italic;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-remove {
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        
        .btn-remove:hover {
            background: #dc2626;
        }
        
        /* Manual entry button styling */
        .btn-manual {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-manual:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        }
        
        /* Enhanced manual entry row styling */
        .manual-entry-row {
            background-color: #fef3c7 !important;
            border-left: 4px solid #f59e0b !important;
        }
        
        .manual-entry-row:hover {
            background-color: #fef3c7 !important;
        }
        
        .dark .manual-entry-row {
            background-color: #451a03 !important;
            border-left-color: #f59e0b !important;
        }
        
        .dark .manual-entry-row:hover {
            background-color: #451a03 !important;
        }
        
        /* Dark mode styles - improved for better readability */
        .dark .search-input {
            background-color: #374151;
            border-color: #4b5563;
            color: white;
        }
        
        .dark .search-input:focus {
            border-color: #60a5fa;
        }
        
        .dark .dropdown {
            background-color: #374151;
            border-color: #4b5563;
        }
        
        .dark .dropdown-item {
            color: white;
            border-bottom-color: #4b5563;
        }
        
        .dark .dropdown-item:hover {
            background-color: #4b5563;
        }
        
        .dark .inventory-table {
            background-color: #1f2937;
        }
        
        .dark .inventory-table th {
            background-color: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }
        
        .dark .inventory-table th:hover {
            background-color: #4b5563;
        }
        
        .dark .inventory-table td {
            background-color: #1f2937;
            color: #e5e7eb;
            border-color: #4b5563;
        }
        
        .dark .inventory-table tbody tr:nth-child(even) {
            background-color: #262f3f;
        }
        
        .dark .inventory-table tbody tr:hover {
            background-color: #374151;
        }
        
        .dark .section-header,
        .dark .entry-header,
        .dark .sortie-header {
            background-color: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }
        
        .dark .input-field {
            background-color: #4b5563;
            border-color: #6b7280;
            color: white;
        }
        
        .dark .input-field:focus {
            border-color: #60a5fa;
        }
        
        .dark .input-field[readonly] {
            background-color: #6b7280;
            color: #d1d5db;
        }
        
        .dark .lot-warning {
            background: #451a03;
            color: #fbbf24;
            border-color: #f59e0b;
        }
        
        /* Dark mode updates for qty editable */
        .dark .qty-editable {
            background-color: #422006 !important;
            border-color: #d97706 !important;
            color: #fcd34d !important;
        }
        
        .dark .qty-editable:focus {
            background-color: #451a03 !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
        }
        
        /* Dark mode updates for read-only fields */
        .dark .readonly-field {
            background-color: transparent !important;
            color: #e5e7eb !important;
            border: none !important;
        }
        
        /* Dark mode updates for manual editable fields */
        .dark .manual-editable {
            background-color: #451a03 !important;
            border-color: #f59e0b !important;
            color: #fbbf24 !important;
        }
        
        .dark .manual-editable:focus {
            background-color: #451a03 !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
        }
        
        .dark .manual-editable::placeholder {
            color: #c084fc !important;
        }
        
        .dark .manual-entry-row {
            background-color: #451a03 !important;
        }
        
        .dark .manual-entry-row:hover {
            background-color: #451a03 !important;
        }
        
        /* PDF specific styling improvements */
        .pdf-warning {
            color: #d97706 !important;
            font-weight: bold;
            font-size: 0.8em;
        }
        
        .manual-entry-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 0.25rem;
            margin-top: 0.25rem;
            border-radius: 0 4px 4px 0;
        }
        
        .dark .manual-entry-warning {
            background-color: #451a03;
            border-left-color: #f59e0b;
        }
        
        /* Empty table state styling */
        .empty-table-message {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-style: italic;
            background-color: #f9fafb;
        }
        
        .dark .empty-table-message {
            background-color: #1f2937;
            color: #9ca3af;
        }

        /* Dark mode body background */
        .dark body {
            background-color: #1f2937 !important;
        }

        .dark .onwan {
            color: white;
        }
        
        /* Loading indicator animations */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        /* Loading indicator positioning */
        .loading-indicator {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #3b82f6;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .loading-indicator.hidden {
            display: none !important;
        }
    </style>
    <script src="theme.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen w-full">
    <div class="inventory-container p-6 w-full max-w-none">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 onwan  mb-2">
               BNM Inventory Management
            </h1>

        </div>
        
        <!-- Search Section -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 onwan mb-4 text-center">PRODUCT SEARCH</h2>
            
            <!-- Inventory Title Input -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 onwan mb-2">
                    Inventory Title:
                </label>
                <div class="search-container">
                    <input 
                        type="text" 
                        id="inventory-title" 
                        class="search-input dark:bg-gray-700 dark:text-white dark:border-gray-600" 
                        placeholder="Type inventory title (e.g., PERMUTATION, MOUVEMENT STOCK)"
                        value=""
                    >
                    <div id="title-dropdown" class="dropdown dark:bg-gray-700 dark:border-gray-600">
                        <!-- Title suggestions will be populated here -->
                    </div>
                </div>
            </div>
            
            <!-- Notes Input -->
            <!-- <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 onwan mb-2">
                    Notes:
                </label>
                <textarea 
                    id="inventory-notes" 
                    class="search-input dark:bg-gray-700 dark:text-white dark:border-gray-600" 
                    rows="3"
                    placeholder="Enter any notes or comments for this inventory report..."
                ></textarea>
            </div> -->
            
            <!-- Category Filter -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 onwan mb-2">
                    Category Filter: <span id="category-indicator" class="hidden text-orange-600 text-xs">🔍 Active</span>
                </label>
                <select 
                    id="category-filter" 
                    class="search-input dark:bg-gray-700 dark:text-white dark:border-gray-600"
                >
                    <option value="preparation">Preparation</option>
                    <option value="tempo">Tempo</option>
                </select>
            </div>
            
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <div class="search-container flex-1">
                    <label class="block text-sm font-medium text-gray-700 onwan mb-2">
                        Search Product:
                    </label>
                    <input 
                        type="text" 
                        id="product-search" 
                        class="search-input dark:bg-gray-700 dark:text-white dark:border-gray-600" 
                        placeholder="Type to search products..."
                    >
                    <div id="product-dropdown" class="dropdown dark:bg-gray-700 dark:border-gray-600">
                        <!-- Dropdown items will be populated here -->
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 items-end">
                    <button id="add-to-entry" class="btn-add entry-header min-w-[120px]" disabled>
                        Add to Entry
                    </button>
                    <button id="add-to-sortie" class="btn-add sortie-header min-w-[120px]" disabled>
                        Add to Sortie
                    </button>
                    <button id="manual-entry" class="btn-add bg-orange-600 hover:bg-orange-700 min-w-[140px]">
                        📝 Manual Entry
                    </button>
                </div>
            </div>
            
            <!-- Product Details Table -->
            <div id="product-details-container" class="hidden">
                <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300">Product Details (Select one):</h3>
                <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm text-left inventory-table dark:text-white">
                            <thead>
                                <tr class="table-header dark:bg-gray-700">
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">Select</th>
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">Product</th>
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">Lot</th>
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">PPA</th>
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">P_REVIENT</th>
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">QTY_DISPO</th>
                                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">Guarantee Date</th>
                                </tr>
                            </thead>
                            <tbody id="product-details-body" class="dark:bg-gray-800">
                                <!-- Product details will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Tables -->
        <div class="flex flex-col gap-8">
            <!-- ENTRY Table -->
            <div class="table-container entry-table rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center section-header">ENTRY</h2>
                    <table class="min-w-full border-collapse text-sm text-left inventory-table dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Product</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Date</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Lot</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PPA</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">P_REVIENT</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY_DISPO</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody id="entry-table-body" class="dark:bg-gray-800">
                            <!-- Entry rows will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Visual separator -->
            <div class="flex items-center justify-center py-4">
                <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                <div class="px-4 text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-full py-2">
                    ⇅ INVENTORY FLOW ⇅
                </div>
                <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            
            <!-- SORTIE Table -->
            <div class="table-container sortie-table rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center section-header">SORTIE</h2>
                    <table class="min-w-full border-collapse text-sm text-left inventory-table dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Product</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Date</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Lot</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PPA</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">P_REVIENT</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY_DISPO</th>
                                <th class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody id="sortie-table-body" class="dark:bg-gray-800">
                            <!-- Sortie rows will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-center mt-8 gap-4">
            <button id="save-draft" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors min-w-[200px]">
                💾 Save as Pending
            </button>
            <button id="save-inventory" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors min-w-[200px]">
                📄 Save as PDF
            </button>
            <button id="print-inventory" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors min-w-[200px]">
                🖨️ Print PDF
            </button>
        </div>
    </div>

    <script>
        // Global variables
        const currentUser = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : 'inventory_user' ?>';
        let productList = [];
        let entryRowCounter = 0;
        let sortieRowCounter = 0;
        let selectedProductDetails = null;
        
        // Debounce function
        function debounce(func, delay) {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        }
        
        // Load product list from API
        async function loadProductList() {
            try {
                const response = await fetch("http://192.168.1.94:5003/listproduct_inv");
                if (!response.ok) throw new Error("Failed to load products");
                
                const products = await response.json();
                productList = products || [];
                console.log(`Loaded ${productList.length} products for search`);
            } catch (error) {
                console.error("Error loading product list:", error);
                productList = [];
            }
        }
        
        // Setup product search functionality
        function setupProductSearch() {
            const productSearch = document.getElementById("product-search");
            const productDropdown = document.getElementById("product-dropdown");
            
            // Show all products when field is clicked (empty search)
            productSearch.addEventListener("click", function() {
                const searchValue = this.value.trim().toLowerCase();
                if (!searchValue && productList.length > 0) {
                    showAllProductsInitial(productDropdown);
                } else if (searchValue) {
                    showProductDropdown(searchValue, productDropdown);
                }
            });
            
            // Setup unified search with debounce
            productSearch.addEventListener("input", debounce(function() {
                const searchValue = this.value.trim().toLowerCase();
                if (searchValue) {
                    showProductDropdown(searchValue, productDropdown);
                } else {
                    showAllProductsInitial(productDropdown);
                }
            }, 300));
            
            // Hide dropdown when clicking outside
            document.addEventListener("click", function(event) {
                if (!productSearch.contains(event.target) && !productDropdown.contains(event.target)) {
                    hideDropdown(productDropdown);
                }
            });
        }
        
        // Setup category filter functionality
        function setupCategoryFilter() {
            const categoryFilter = document.getElementById("category-filter");
            
            // Function to update visual state based on category
            function updateCategoryVisualState() {
                const selectedCategory = categoryFilter.value;
                const categoryIndicator = document.getElementById('category-indicator');
                
                // Always show visual indicator since we only have specific categories
                categoryFilter.classList.add('category-filter-active');
                categoryIndicator.classList.remove('hidden');
                categoryIndicator.textContent = `🔍 Active: ${selectedCategory}`;
            }
            
            // Initial state
            updateCategoryVisualState();
            
            // Refresh product details when category changes
            categoryFilter.addEventListener("change", function() {
                const productSearch = document.getElementById("product-search");
                const currentProductName = productSearch.value.trim();
                
                console.log(`Category filter changed to: ${this.value}`);
                console.log(`Current product in search: "${currentProductName}"`);
                console.log(`Product list length: ${productList.length}`);
                
                // Update visual state immediately
                updateCategoryVisualState();
                
                // Check if we have a currently selected product to refresh
                const hasSelectedProduct = currentProductName && currentProductName.length > 0;
                
                if (hasSelectedProduct && productList.length > 0) {
                    console.log(`Product selected, refreshing with new category: ${this.value}`);
                    
                    // Show loading indicator
                    showLoadingIndicator(`Refreshing "${currentProductName}" details with ${this.value} category filter...`);
                    
                    // Find the product in the list by name (case-insensitive)
                    const product = productList.find(p => 
                        p && p.name && p.name.toLowerCase().trim() === currentProductName.toLowerCase().trim()
                    );
                    
                    if (product) {
                        console.log(`Found product in list:`, product);
                        console.log(`Auto-refreshing product details for: ${product.name} with new category: ${this.value}`);
                        
                        // Call selectProduct to refresh with new category
                        selectProduct(product);
                    } else {
                        console.log(`Product "${currentProductName}" not found in productList`);
                        console.log(`Available products (first 5):`, productList.slice(0, 5)); // Log first 5 products for debugging
                        
                        hideLoadingIndicator();
                        hideProductDetails();
                    }
                } else {
                    console.log(`No product selected or productList empty, hiding details`);
                    // No product selected, just hide details and loading if showing
                    hideLoadingIndicator();
                    hideProductDetails();
                }
            });
        }
        
        // Show all products initially (when clicked with empty search)
        function showAllProductsInitial(dropdown) {
            if (productList.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">Loading products...</div>';
                dropdown.style.display = 'block';
                return;
            }
            
            const itemsPerPage = 15; // Show more items initially
            const totalPages = Math.ceil(productList.length / itemsPerPage);
            let currentPage = 1;
            
            function renderInitialPage(page) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const pageProducts = productList.slice(startIndex, endIndex);
                
                dropdown.innerHTML = '';
                
                // Add search hint at the top
                const hintDiv = document.createElement('div');
                hintDiv.className = 'dropdown-item bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-center font-medium border-b-2 border-blue-200 dark:border-blue-700';
                hintDiv.innerHTML = `💡 Type to search through ${productList.length} products`;
                dropdown.appendChild(hintDiv);
                
                // Add products for current page
                pageProducts.forEach(product => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item dark:text-white dark:hover:bg-gray-600';
                    item.textContent = product.name;
                    item.addEventListener('click', function() {
                        selectProduct(product);
                        hideDropdown(dropdown);
                    });
                    dropdown.appendChild(item);
                });
                
                // Add pagination controls
                if (totalPages > 1) {
                    const paginationDiv = document.createElement('div');
                    paginationDiv.className = 'dropdown-pagination border-t border-gray-200 dark:border-gray-600 pt-2 mt-2';
                    
                    // Previous button
                    if (page > 1) {
                        const prevBtn = document.createElement('button');
                        prevBtn.className = 'pagination-btn bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs mr-1';
                        prevBtn.textContent = '← Prev';
                        prevBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            currentPage = page - 1;
                            renderInitialPage(currentPage);
                        });
                        paginationDiv.appendChild(prevBtn);
                    }
                    
                    // Page info
                    const pageInfo = document.createElement('span');
                    pageInfo.className = 'text-xs text-gray-600 dark:text-gray-400 mx-2';
                    pageInfo.textContent = `Page ${page} of ${totalPages} (${productList.length} total)`;
                    paginationDiv.appendChild(pageInfo);
                    
                    // Next button
                    if (page < totalPages) {
                        const nextBtn = document.createElement('button');
                        nextBtn.className = 'pagination-btn bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs ml-1';
                        nextBtn.textContent = 'Next →';
                        nextBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            currentPage = page + 1;
                            renderInitialPage(currentPage);
                        });
                        paginationDiv.appendChild(nextBtn);
                    }
                    
                    dropdown.appendChild(paginationDiv);
                }
            }
            
            // Initial render
            renderInitialPage(currentPage);
            dropdown.style.display = 'block';
        }
        
        // Setup title suggestion functionality
        function setupTitleSuggestions() {
            const titleInput = document.getElementById("inventory-title");
            const titleDropdown = document.getElementById("title-dropdown");
            
            const titleSuggestions = [
                "PERMUTATION",
                "MOUVEMENT STOCK",
                "CORRECTION INV ",
                "INV TEMPORAIRE "
            ];
            
            // Show suggestions when input is focused or clicked
            titleInput.addEventListener("focus", function() {
                showAllTitleSuggestions(titleDropdown, titleSuggestions);
            });
            
            titleInput.addEventListener("click", function() {
                showAllTitleSuggestions(titleDropdown, titleSuggestions);
            });
            
            // Setup title input listener for filtering
            titleInput.addEventListener("input", debounce(function() {
                const searchValue = this.value.trim().toLowerCase();
                if (searchValue) {
                    showTitleSuggestions(searchValue, titleDropdown, titleSuggestions);
                } else {
                    showAllTitleSuggestions(titleDropdown, titleSuggestions);
                }
            }, 300));
            
            // Hide dropdown when clicking outside
            document.addEventListener("click", function(event) {
                if (!titleInput.contains(event.target) && !titleDropdown.contains(event.target)) {
                    hideDropdown(titleDropdown);
                }
            });
        }
        
        // Show all title suggestions (when focused or clicked)
        function showAllTitleSuggestions(dropdown, suggestions) {
            dropdown.innerHTML = '';
            suggestions.forEach(suggestion => {
                const item = document.createElement('div');
                item.className = 'dropdown-item dark:text-white dark:hover:bg-gray-600';
                item.textContent = suggestion;
                item.addEventListener('click', function() {
                    document.getElementById('inventory-title').value = suggestion;
                    hideDropdown(dropdown);
                });
                dropdown.appendChild(item);
            });
            dropdown.style.display = 'block';
        }
        
        // Show title suggestions dropdown (filtered)
        function showTitleSuggestions(searchValue, dropdown, suggestions) {
            // Filter suggestions based on user input
            const filteredSuggestions = suggestions.filter(suggestion => 
                suggestion.toLowerCase().includes(searchValue)
            );
            
            if (filteredSuggestions.length === 0) {
                hideDropdown(dropdown);
                return;
            }
            
            dropdown.innerHTML = '';
            filteredSuggestions.forEach(suggestion => {
                const item = document.createElement('div');
                item.className = 'dropdown-item dark:text-white dark:hover:bg-gray-600';
                item.textContent = suggestion;
                item.addEventListener('click', function() {
                    document.getElementById('inventory-title').value = suggestion;
                    hideDropdown(dropdown);
                });
                dropdown.appendChild(item);
            });
            
            dropdown.style.display = 'block';
        }
        
        // Show product dropdown with filtered results and pagination
        function showProductDropdown(searchValue, dropdown) {
            if (productList.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">Loading products...</div>';
                dropdown.style.display = 'block';
                return;
            }
            
            // Filter products by name
            const filteredProducts = productList.filter(product => 
                product && product.name && product.name.toLowerCase().includes(searchValue)
            );
            
            if (filteredProducts.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">No products found</div>';
                dropdown.style.display = 'block';
                return;
            }
            
            // Pagination settings
            const itemsPerPage = 10;
            const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
            let currentPage = 1;
            
            function renderPage(page) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const pageProducts = filteredProducts.slice(startIndex, endIndex);
                
                dropdown.innerHTML = '';
                
                // Add products for current page
                pageProducts.forEach(product => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item dark:text-white dark:hover:bg-gray-600';
                    item.textContent = product.name;
                    item.addEventListener('click', function() {
                        selectProduct(product);
                        hideDropdown(dropdown);
                    });
                    dropdown.appendChild(item);
                });
                
                // Add pagination controls if more than one page
                if (totalPages > 1) {
                    const paginationDiv = document.createElement('div');
                    paginationDiv.className = 'dropdown-pagination border-t border-gray-200 dark:border-gray-600 pt-2 mt-2';
                    
                    // Previous button
                    if (page > 1) {
                        const prevBtn = document.createElement('button');
                        prevBtn.className = 'pagination-btn bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs mr-1';
                        prevBtn.textContent = '← Prev';
                        prevBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            currentPage = page - 1;
                            renderPage(currentPage);
                        });
                        paginationDiv.appendChild(prevBtn);
                    }
                    
                    // Page info
                    const pageInfo = document.createElement('span');
                    pageInfo.className = 'text-xs text-gray-600 dark:text-gray-400 mx-2';
                    pageInfo.textContent = `Page ${page} of ${totalPages} (${filteredProducts.length} total)`;
                    paginationDiv.appendChild(pageInfo);
                    
                    // Next button
                    if (page < totalPages) {
                        const nextBtn = document.createElement('button');
                        nextBtn.className = 'pagination-btn bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs ml-1';
                        nextBtn.textContent = 'Next →';
                        nextBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            currentPage = page + 1;
                            renderPage(currentPage);
                        });
                        paginationDiv.appendChild(nextBtn);
                    }
                    
                    dropdown.appendChild(paginationDiv);
                }
                
                // Add "Show All" option for small result sets
                if (filteredProducts.length <= 50 && totalPages > 1) {
                    const showAllDiv = document.createElement('div');
                    showAllDiv.className = 'dropdown-item border-t border-gray-200 dark:border-gray-600 text-center bg-gray-50 dark:bg-gray-700 font-medium';
                    showAllDiv.innerHTML = `<button class="text-blue-600 dark:text-blue-400 hover:underline">Show All ${filteredProducts.length} Results</button>`;
                    showAllDiv.addEventListener('click', function() {
                        showAllProducts(filteredProducts, dropdown);
                    });
                    dropdown.appendChild(showAllDiv);
                }
            }
            
            // Initial render
            renderPage(currentPage);
            dropdown.style.display = 'block';
        }
        
        // Show all products without pagination (for smaller result sets)
        function showAllProducts(products, dropdown) {
            dropdown.innerHTML = '';
            
            // Add all products
            products.forEach(product => {
                const item = document.createElement('div');
                item.className = 'dropdown-item dark:text-white dark:hover:bg-gray-600';
                item.textContent = product.name;
                item.addEventListener('click', function() {
                    selectProduct(product);
                    hideDropdown(dropdown);
                });
                dropdown.appendChild(item);
            });
            
            // Add back to pagination option if more than 10 items
            if (products.length > 10) {
                const backToPaginationDiv = document.createElement('div');
                backToPaginationDiv.className = 'dropdown-item border-t border-gray-200 dark:border-gray-600 text-center bg-gray-50 dark:bg-gray-700 font-medium';
                backToPaginationDiv.innerHTML = `<button class="text-gray-600 dark:text-gray-400 hover:underline">Back to Pagination</button>`;
                backToPaginationDiv.addEventListener('click', function() {
                    const searchValue = document.getElementById("product-search").value.trim().toLowerCase();
                    showProductDropdown(searchValue, dropdown);
                });
                dropdown.appendChild(backToPaginationDiv);
            }
        }
        
        // Hide dropdown
        function hideDropdown(dropdown) {
            dropdown.style.display = 'none';
        }
        
        // Show loading indicator
        function showLoadingIndicator(message = "Loading product details...") {
            const container = document.getElementById('product-details-container');
            const loadingIndicator = document.getElementById('loading-indicator') || createLoadingIndicator();
            
            // Update loading message
            const loadingMessage = loadingIndicator.querySelector('.loading-message');
            if (loadingMessage) {
                loadingMessage.textContent = message;
            }
            
            // Show loading indicator
            loadingIndicator.classList.remove('hidden');
            
            // If product details container is already visible, show loading inside it
            if (!container.classList.contains('hidden')) {
                const header = container.querySelector('h3');
                if (header) {
                    header.innerHTML = `<span class="text-blue-600">🔄 ${message}</span>`;
                }
            }
        }
        
        // Hide loading indicator
        function hideLoadingIndicator() {
            const loadingIndicator = document.getElementById('loading-indicator');
            if (loadingIndicator) {
                loadingIndicator.classList.add('hidden');
            }
        }
        
        // Create loading indicator element
        function createLoadingIndicator() {
            const loadingIndicator = document.createElement('div');
            loadingIndicator.id = 'loading-indicator';
            loadingIndicator.className = 'hidden fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center space-x-2';
            loadingIndicator.innerHTML = `
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                <span class="loading-message">Loading...</span>
            `;
            
            // Insert at the beginning of the body
            document.body.insertBefore(loadingIndicator, document.body.firstChild);
            
            return loadingIndicator;
        }
        
        // Hide product details
        function hideProductDetails() {
            document.getElementById('product-details-container').classList.add('hidden');
            document.getElementById('add-to-entry').disabled = true;
            document.getElementById('add-to-sortie').disabled = true;
            selectedProductDetails = null;
        }
        
        // Select product and fetch details
        async function selectProduct(product) {
            document.getElementById("product-search").value = product.name;
            
            // Show loading state
            const container = document.getElementById('product-details-container');
            const addEntryBtn = document.getElementById('add-to-entry');
            const addSortieBtn = document.getElementById('add-to-sortie');
            
            // Disable buttons during loading
            addEntryBtn.disabled = true;
            addSortieBtn.disabled = true;
            
            try {
                // Get the selected category filter
                const categoryFilter = document.getElementById('category-filter').value;
                
                // Log for debugging
                console.log(`Fetching product details for: ${product.name}, category: ${categoryFilter}`);
                
                // Show loading indicator
                showLoadingIndicator(`Loading ${product.name} details for ${categoryFilter} category...`);
                
                // Show loading indicator in product details header if container is visible
                if (!container.classList.contains('hidden')) {
                    const header = container.querySelector('h3');
                    if (header) {
                        header.innerHTML = `<span class="text-blue-600">🔄 Loading ${product.name} details...</span>`;
                    }
                }
                
                const response = await fetch(`http://192.168.1.94:5003/inventory-products?product_id=${encodeURIComponent(product.id)}&category=${encodeURIComponent(categoryFilter)}`);
                
                // Hide loading indicator
                hideLoadingIndicator();
                
                if (!response.ok) throw new Error('Failed to fetch product details');
                const data = await response.json();
                
                if (Array.isArray(data) && data.length > 0) {
                    showProductDetails(data);
                } else {
                    // No details found - show manual entry option
                    console.log(`No inventory found for product ${product.name} in category ${categoryFilter}`);
                    showManualProductEntry(product.name);
                }
            } catch (error) {
                console.error('Error fetching product details:', error);
                
                // Hide loading indicator on error
                hideLoadingIndicator();
                
                // Clear loading state on error
                hideProductDetails();
                
                // Show manual entry option on error as well
                showManualProductEntry(product.name);
                
                // Show user-friendly error message
                const container = document.getElementById('product-details-container');
                if (!container.classList.contains('hidden')) {
                    const header = container.querySelector('h3');
                    if (header) {
                        header.innerHTML = `<span class="text-red-600">⚠️ Error loading product details. Please try again or use manual entry.</span>`;
                    }
                }
            }
        }
        
        // Show product details in table
        function showProductDetails(details) {
            const container = document.getElementById('product-details-container');
            const tbody = document.getElementById('product-details-body');
            
            // Get the current category filter for display
            const categoryFilter = document.getElementById('category-filter').value;
            const categoryDisplayName = categoryFilter.charAt(0).toUpperCase() + categoryFilter.slice(1);
            
            // Update the header to show the category and count
            const header = container.querySelector('h3');
            const itemCount = details.length;
            const categoryText = `${categoryDisplayName} Category`;
            header.innerHTML = `Product Details - ${categoryText} (${itemCount} item${itemCount !== 1 ? 's' : ''} found) - Select one:`;
            header.className = 'text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300';
            
            // Debug: log the API response to see what we're getting
            console.log(`Displaying ${itemCount} product details for ${categoryText}:`, details);
            
            tbody.innerHTML = '';
            
            details.forEach((detail, index) => {
                const row = document.createElement('tr');
                row.className = 'product-details-row';
                
                // Handle different possible property names from API and show actual values
                const productName = detail.PRODUCT_NAME || detail.PRODUCT || detail.NAME || detail.name || '';
                const lot = detail.LOT || detail.lot || '';
                const ppa = detail.PPA || detail.ppa || '';
                const pRevient = detail.P_REVIENT || detail.p_revient || '';
                const qtyDispo = detail.QTY_DISPO || detail.qty_dispo || detail.QTY || detail.qty || 0;
                const guaranteeDate = detail.GUARANTEEDATE || detail.guarantee_date || detail.GUARANTEE_DATE || detail.date || '';
                
                // Format PPA if it exists - always show 0.00 instead of empty
                const formattedPPA = ppa ? parseFloat(ppa).toFixed(2) : '0.00';
                
                // Format P_REVIENT if it exists - always show 0.00 instead of empty  
                const formattedPRevient = pRevient ? parseFloat(pRevient).toFixed(2) : '0.00';
                
                // Format date if it exists
                const formattedDate = guaranteeDate ? new Date(guaranteeDate).toLocaleDateString() : '';
                
                // Debug: log each processed item
                console.log('Processing item:', {
                    productName, lot, ppa, pRevient, qtyDispo, guaranteeDate,
                    formattedPPA, formattedPRevient, formattedDate
                });
                
                row.innerHTML = `
                    <td>
                        <input type="radio" name="product-select" value="${index}" class="select-radio">
                    </td>
                    <td>${productName}</td>
                    <td>${lot}</td>
                    <td>${formattedPPA}</td>
                    <td>${formattedPRevient}</td>
                    <td>${qtyDispo || 0}</td>
                    <td>${formattedDate}</td>
                `;
                
                // Add click handler for row selection
                row.addEventListener('click', function() {
                    const radio = row.querySelector('input[type="radio"]');
                    radio.checked = true;
                    selectProductDetail(index, details);
                    
                    // Update visual selection
                    document.querySelectorAll('.product-details-row').forEach(r => r.classList.remove('selected'));
                    row.classList.add('selected');
                });
                
                tbody.appendChild(row);
            });
            
            container.classList.remove('hidden');
        }
        
        // Show manual product entry for products with no details
        function showManualProductEntry(productName) {
            const container = document.getElementById('product-details-container');
            const tbody = document.getElementById('product-details-body');
            
            // Get current category for context
            const categoryFilter = document.getElementById('category-filter').value;
            const categoryDisplayName = categoryFilter.charAt(0).toUpperCase() + categoryFilter.slice(1);
            const categoryText = `${categoryDisplayName} Category`;
            
            tbody.innerHTML = '';
            
            // Create a manual entry row
            const row = document.createElement('tr');
            row.className = 'product-details-row manual-entry-row';
            
            row.innerHTML = `
                <td>
                    <input type="radio" name="product-select" value="manual" class="select-radio" checked>
                </td>
                <td>
                    <span class="font-medium text-red-600 dark:text-red-400">${productName}</span>
                    <br>
                    <small class="text-red-500 dark:text-red-400 font-semibold">⚠️ No details found in ${categoryText} - Manual entry required</small>
                </td>
                <td colspan="4">
                    <div class="text-orange-600 dark:text-orange-400 font-semibold text-sm">
                        ⚠️ Warning: this product does not have any details available in the ${categoryText} inventory system.
                        <br>
                        <small class="text-gray-600 dark:text-gray-400">Try changing the category filter or use manual entry mode.</small>
                    </div>
                </td>
            `;
            
            tbody.appendChild(row);
            
            // Set selected product for manual entry
            selectedProductDetails = {
                PRODUCT_NAME: productName,
                isManualEntry: true
            };
            
            // Enable add buttons
            document.getElementById('add-to-entry').disabled = false;
            document.getElementById('add-to-sortie').disabled = false;
            
            // Show the container
            container.classList.remove('hidden');
            
            // Update the subtitle to indicate manual entry with category context
            const subtitle = container.querySelector('h3');
            if (subtitle) {
                subtitle.innerHTML = `<span class="text-orange-600 dark:text-orange-400">Manual Product Entry (${categoryText}) - Fill all required fields</span>`;
                subtitle.className = 'text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300';
            }
        }
        
        // Select specific product detail
        function selectProductDetail(index, details) {
            selectedProductDetails = details[index];
            document.getElementById('add-to-entry').disabled = false;
            document.getElementById('add-to-sortie').disabled = false;
        }
        
        // Add entry row
        function addEntryRow(productDetails) {
            if (!productDetails) return; // Only allow adding rows with product details
            
            entryRowCounter++;
            const tableBody = document.getElementById('entry-table-body');
            const row = document.createElement('tr');
            row.id = `entry-row-${entryRowCounter}`;

            // Check if this is a manual entry product
            const isManualEntry = productDetails && productDetails.isManualEntry;
            
            // Use provided product details
            // Handle different possible property names from API
            const product = productDetails.PRODUCT_NAME || productDetails.PRODUCT || productDetails.NAME || productDetails.name || '';
            const lot = productDetails && !isManualEntry ? 
                (productDetails.LOT || productDetails.lot || '') : '';
            const ppa = productDetails && !isManualEntry && (productDetails.PPA || productDetails.ppa) ? 
                parseFloat(productDetails.PPA || productDetails.ppa).toFixed(2) : '';
            const pRevient = productDetails && !isManualEntry && (productDetails.P_REVIENT || productDetails.p_revient) ? 
                parseFloat(productDetails.P_REVIENT || productDetails.p_revient).toFixed(2) : '';
            const qtyDispo = productDetails && !isManualEntry ? 
                (productDetails.QTY_DISPO || productDetails.qty_dispo || productDetails.QTY || productDetails.qty || 0) : 0;
            const guaranteeDate = productDetails && !isManualEntry && (productDetails.GUARANTEEDATE || productDetails.guarantee_date || productDetails.GUARANTEE_DATE || productDetails.date) ? 
                new Date(productDetails.GUARANTEEDATE || productDetails.guarantee_date || productDetails.GUARANTEE_DATE || productDetails.date).toISOString().split('T')[0] : 
                new Date().toISOString().split('T')[0];
            
            // Format date for display
            const displayDate = guaranteeDate ? new Date(guaranteeDate).toLocaleDateString() : '';

            if (isManualEntry) {
                // Manual entry with editable fields
                row.innerHTML = `
                    <td>
                        <span class="font-medium text-red-600 dark:text-red-400">${product}</span>
                        <input type="hidden" name="product" value="${product}">
                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                            ⚠️ No Qty Found in The System
                        </div>
                    </td>
                    <td>
                        <input type="number" class="input-field qty-editable" name="qty"
                               placeholder="0" min="0" step="1">
                    </td>
                    <td>
                        <input type="date" class="input-field manual-editable" name="date" 
                               value="${guaranteeDate}" required>
                    </td>
                    <td>
                        <input type="text" class="input-field manual-editable" name="lot" 
                               placeholder="Enter lot number" required>
                    </td>
                    <td>
                        <input type="number" class="input-field manual-editable" name="ppa" 
                               placeholder="0.00" min="0" step="0.01">
                    </td>
                    <td>
                        <input type="number" class="input-field manual-editable" name="p_revient" 
                               placeholder="0.00" min="0" step="0.01">
                    </td>
                    <td>
                        <input type="number" class="input-field manual-editable" name="qty_dispo" 
                               placeholder="0" min="0" step="1">
                    </td>
                    <td>
                        <button class="btn-remove" onclick="removeRow('entry-row-${entryRowCounter}')" title="Remove this row">
                            ✕
                        </button>
                    </td>
                `;
            } else {
                // Regular entry with display-only fields (except QTY)
                row.innerHTML = `
                    <td>
                        <span table-text>${product}</span>
                        <input type="hidden" name="product" value="${product}">
                    </td>
                    <td>
                        <input type="number" class="input-field qty-editable" name="qty"
                               placeholder="0" min="0" step="1">
                    </td>
                    <td>
                        <span class="table-text">${displayDate}</span>
                        <input type="hidden" name="date" value="${guaranteeDate}">
                    </td>
                    <td>
                        <span class="table-text">${lot}</span>
                        <input type="hidden" name="lot" value="${lot}">
                    </td>
                    <td>
                        <span class="table-text">${ppa || '0.00'}</span>
                        <input type="hidden" name="ppa" value="${ppa || '0.00'}">
                    </td>
                    <td>
                        <span class="table-text">${pRevient || '0.00'}</span>
                        <input type="hidden" name="p_revient" value="${pRevient || '0.00'}">
                    </td>
                    <td>
                        <span class="table-text font-medium text-green-700 dark:text-green-400">${qtyDispo || 0}</span>
                        <input type="hidden" name="qty_dispo" value="${qtyDispo || 0}">
                    </td>
                    <td>
                        <button class="btn-remove" onclick="removeRow('entry-row-${entryRowCounter}')" title="Remove this row">
                            ✕
                        </button>
                    </td>
                `;
            }

            tableBody.appendChild(row);

            // Focus on QTY field
            const qtyInput = row.querySelector('.qty-editable');
            qtyInput.focus();
        }
        
        // Add sortie row
        function addSortieRow(productDetails) {
            if (!productDetails) return; // Only allow adding rows with product details
            
            sortieRowCounter++;
            const tableBody = document.getElementById('sortie-table-body');
            const row = document.createElement('tr');
            row.id = `sortie-row-${sortieRowCounter}`;

            // Check if this is a manual entry product
            const isManualEntry = productDetails && productDetails.isManualEntry;
            
            // Use provided product details
            // Handle different possible property names from API
            const product = productDetails.PRODUCT_NAME || productDetails.PRODUCT || productDetails.NAME || productDetails.name || '';
            const lot = productDetails && !isManualEntry ? 
                (productDetails.LOT || productDetails.lot || '') : '';
            const ppa = productDetails && !isManualEntry && (productDetails.PPA || productDetails.ppa) ? 
                parseFloat(productDetails.PPA || productDetails.ppa).toFixed(2) : '';
            const pRevient = productDetails && !isManualEntry && (productDetails.P_REVIENT || productDetails.p_revient) ? 
                parseFloat(productDetails.P_REVIENT || productDetails.p_revient).toFixed(2) : '';
            const qtyDispo = productDetails && !isManualEntry ? 
                (productDetails.QTY_DISPO || productDetails.qty_dispo || productDetails.QTY || productDetails.qty || 0) : 0;
            const guaranteeDate = productDetails && !isManualEntry && (productDetails.GUARANTEEDATE || productDetails.guarantee_date || productDetails.GUARANTEE_DATE || productDetails.date) ? 
                new Date(productDetails.GUARANTEEDATE || productDetails.guarantee_date || productDetails.GUARANTEE_DATE || productDetails.date).toISOString().split('T')[0] : 
                new Date().toISOString().split('T')[0];
                
            // Format date for display
            const displayDate = guaranteeDate ? new Date(guaranteeDate).toLocaleDateString() : '';

            if (isManualEntry) {
                // Manual entry with editable fields
                row.innerHTML = `
                    <td>
                        <span table-text>${product}</span>
                        <input type="hidden" name="product" value="${product}">
                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                            ⚠️ No Qty Found in The System
                        </div>
                    </td>
                    <td>
                        <input type="number" class="input-field qty-editable" name="qty"
                               placeholder="0" min="0" step="1">
                    </td>
                    <td>
                        <input type="date" class="input-field manual-editable" name="date" 
                               value="${guaranteeDate}" required>
                    </td>
                    <td>
                        <input type="text" class="input-field manual-editable" name="lot" 
                               placeholder="Enter lot number" required>
                    </td>
                    <td>
                        <input type="number" class="input-field manual-editable" name="ppa" 
                               placeholder="0.00" min="0" step="0.01">
                    </td>
                    <td>
                        <input type="number" class="input-field manual-editable" name="p_revient" 
                               placeholder="0.00" min="0" step="0.01">
                    </td>
                    <td>
                        <input type="number" class="input-field manual-editable" name="qty_dispo" 
                               placeholder="0" min="0" step="1" readonly style="background-color: #f9f9f9; cursor: not-allowed;" 
                               title="QTY_DISPO is calculated automatically in manual entries">
                    </td>
                    <td>
                        <button class="btn-remove" onclick="removeRow('sortie-row-${sortieRowCounter}')" title="Remove this row">
                            ✕
                        </button>
                    </td>
                `;
            } else {
                // Regular entry with display-only fields (except QTY)
                row.innerHTML = `
                    <td>
                        <span table-text>${product}</span>
                        <input type="hidden" name="product" value="${product}">
                    </td>
                    <td>
                        <input type="number" class="input-field qty-editable" name="qty"
                               placeholder="0" min="0" max="${qtyDispo || 0}" step="1" title="Maximum available: ${qtyDispo || 0}">
                    </td>
                    <td>
                        <span class="table-text">${displayDate}</span>
                        <input type="hidden" name="date" value="${guaranteeDate}">
                    </td>
                    <td>
                        <span class="table-text">${lot}</span>
                        <input type="hidden" name="lot" value="${lot}">
                    </td>
                    <td>
                        <span class="table-text">${ppa || '0.00'}</span>
                        <input type="hidden" name="ppa" value="${ppa || '0.00'}">
                    </td>
                    <td>
                        <span class="table-text">${pRevient || '0.00'}</span>
                        <input type="hidden" name="p_revient" value="${pRevient || '0.00'}">
                    </td>
                    <td>
                        <span class="table-text font-medium text-amber-700 dark:text-amber-400">${qtyDispo}</span>
                        <input type="hidden" name="qty_dispo" value="${qtyDispo}">
                    </td>
                    <td>
                        <button class="btn-remove" onclick="removeRow('sortie-row-${sortieRowCounter}')" title="Remove this row">
                            ✕
                        </button>
                    </td>
                `;
            }

            tableBody.appendChild(row);

            // Focus on QTY field
            const qtyInput = row.querySelector('.qty-editable');
            qtyInput.focus();
            
            // Add validation for QTY input in SORTIE rows
            qtyInput.addEventListener('input', function() {
                const enteredQty = parseInt(this.value) || 0;
                const row = this.closest('tr');
                const isManualEntry = row.classList && row.classList.contains('manual-entry-row');
                
                if (isManualEntry) {
                    // For manual entries, validate against qty_dispo input value
                    const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                    const maxQty = qtyDispoInput ? parseInt(qtyDispoInput.value) || 0 : 0;
                    
                    if (enteredQty > maxQty) {
                        this.style.borderColor = '#ef4444';
                        this.style.backgroundColor = '#fef2f2';
                        this.title = `⚠️ Cannot exceed QTY_DISPO: ${maxQty}`;
                        
                        // Show warning message
                        let warningMsg = this.parentNode.querySelector('.qty-warning');
                        if (!warningMsg) {
                            warningMsg = document.createElement('div');
                            warningMsg.className = 'qty-warning text-xs text-red-600 mt-1';
                            this.parentNode.appendChild(warningMsg);
                        }
                        warningMsg.textContent = `⚠️ Max QTY_DISPO: ${maxQty}`;
                    } else {
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                        this.title = `Maximum QTY_DISPO: ${maxQty}`;
                        
                        // Remove warning message
                        const warningMsg = this.parentNode.querySelector('.qty-warning');
                        if (warningMsg) {
                            warningMsg.remove();
                        }
                    }
                } else {
                    // For regular entries, use existing logic with max attribute
                    const maxQty = parseInt(this.getAttribute('max')) || 0;
                    
                    if (enteredQty > maxQty) {
                        this.style.borderColor = '#ef4444';
                        this.style.backgroundColor = '#fef2f2';
                        this.title = `⚠️ Cannot exceed available quantity: ${maxQty}`;
                        
                        // Show warning message
                        let warningMsg = this.parentNode.querySelector('.qty-warning');
                        if (!warningMsg) {
                            warningMsg = document.createElement('div');
                            warningMsg.className = 'qty-warning text-xs text-red-600 mt-1';
                            this.parentNode.appendChild(warningMsg);
                        }
                        warningMsg.textContent = `⚠️ Max: ${maxQty}`;
                    } else {
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                        this.title = `Maximum available: ${maxQty}`;
                        
                        // Remove warning message
                        const warningMsg = this.parentNode.querySelector('.qty-warning');
                        if (warningMsg) {
                            warningMsg.remove();
                        }
                    }
                }
            });
            
            // Add validation for qty_dispo changes in manual entries (for SORTIE table)
            if (isManualEntry && tableBody.id === 'sortie-table-body') {
                const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                if (qtyDispoInput) {
                    qtyDispoInput.addEventListener('input', function() {
                        // Trigger QTY validation when qty_dispo changes
                        const qtyInputElement = row.querySelector('input.qty-editable');
                        if (qtyInputElement) {
                            qtyInputElement.dispatchEvent(new Event('input'));
                        }
                    });
                }
            }
        }
        
        // Add manual entry row for ENTRY table only
        function addManualEntryRow() {
            entryRowCounter++;
            const tableBody = document.getElementById('entry-table-body');
            const row = document.createElement('tr');
            row.id = `entry-row-${entryRowCounter}`;
            row.className = 'manual-entry-row';
            
            // Get current date for default
            const currentDate = new Date().toISOString().split('T')[0];
            
            // Get the product name from the search input if available
            const productSearchInput = document.getElementById('product-search');
            const selectedProductName = productSearchInput ? productSearchInput.value.trim() : '';
            
            // Create fully manual entry row with all fields editable
            row.innerHTML = `
                <td>
                    <input type="text" class="input-field manual-editable" name="product" 
                           value="${selectedProductName}" placeholder="Enter product name" required>
                    <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                        📝 Manual Entry
                    </div>
                </td>
                <td>
                    <input type="number" class="input-field qty-editable" name="qty"
                           placeholder="0" min="0" step="1" required>
                </td>
                <td>
                    <input type="date" class="input-field manual-editable" name="date" 
                           value="${currentDate}" required>
                </td>
                <td>
                    <input type="text" class="input-field manual-editable" name="lot" 
                           placeholder="Enter lot number" required>
                </td>
                <td>
                    <input type="number" class="input-field manual-editable" name="ppa" 
                           placeholder="0.00" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="input-field manual-editable" name="qty_dispo" 
                           placeholder="0" min="0" step="1" readonly style="background-color: #f9f9f9; cursor: not-allowed;" 
                           title="QTY_DISPO is calculated automatically in manual entries" required>
                </td>
                <td>
                    <button class="btn-remove" onclick="removeRow('entry-row-${entryRowCounter}')" title="Remove this row">
                        ✕
                    </button>
                </td>
            `;
            
            tableBody.appendChild(row);
            
            // Focus on the QTY field if product name is already filled, otherwise focus on product name
            if (selectedProductName) {
                const qtyInput = row.querySelector('input[name="qty"]');
                qtyInput.focus();
                // Clear the search after using it
                clearSearch();
            } else {
                const productInput = row.querySelector('input[name="product"]');
                productInput.focus();
            }
        }
        
        // Remove row
        function removeRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
            }
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Add to entry/sortie buttons
            document.getElementById('add-to-entry').addEventListener('click', function() {
                if (selectedProductDetails) {
                    addEntryRow(selectedProductDetails);
                    clearSearch();
                }
            });
            
            document.getElementById('add-to-sortie').addEventListener('click', function() {
                if (selectedProductDetails) {
                    addSortieRow(selectedProductDetails);
                    clearSearch();
                }
            });
            
            // Manual entry button
            document.getElementById('manual-entry').addEventListener('click', function() {
                addManualEntryRow();
            });
            
            // Save inventory button
            document.getElementById('save-inventory').addEventListener('click', saveInventory);
            
            // Print inventory button
            document.getElementById('print-inventory').addEventListener('click', printInventory);
            
            // Save as pending button
            document.getElementById('save-draft').addEventListener('click', saveAsPending);
        }
        
        // Clear search and hide details
        function clearSearch() {
            document.getElementById("product-search").value = '';
            hideProductDetails();
        }
        
        // Set suggestion for inventory title
        function setSuggestion(title) {
            document.getElementById('inventory-title').value = title;
            // Add visual feedback
            const titleInput = document.getElementById('inventory-title');
            titleInput.focus();
            titleInput.select();
        }
        
        // Save inventory data and generate PDF
        function saveInventory() {
            const entryData = [];
            const sortieData = [];
            
            // Collect entry data
            const entryRows = document.querySelectorAll('#entry-table-body tr');
            entryRows.forEach(row => {
                const qtyInput = row.querySelector('input.qty-editable');
                const qty = qtyInput ? qtyInput.value : '';
                
                // Check if this is a manual product by looking for the warning div or manual-entry-row class
                const isManualProduct = row.querySelector('.text-orange-600, .dark\\:text-orange-400') !== null || 
                                      row.classList.contains('manual-entry-row');
                
                // Get values from inputs with name attributes (both visible and hidden)
                const productInput = row.querySelector('input[name="product"]');
                const dateInput = row.querySelector('input[name="date"]');
                const lotInput = row.querySelector('input[name="lot"]');
                const ppaInput = row.querySelector('input[name="ppa"]');
                const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                
                const product = productInput ? productInput.value.trim() : '';
                const date = dateInput ? dateInput.value : '';
                const lot = lotInput ? lotInput.value : '';
                const ppa = ppaInput ? ppaInput.value : '';
                const qtyDispo = qtyDispoInput ? qtyDispoInput.value : '';
                
                if (product && qty && qty > 0) {
                    entryData.push({
                        product: product,
                        qty: parseInt(qty),
                        date: date ? new Date(date).toLocaleDateString() : '',
                        lot: lot,
                        ppa: ppa ? parseFloat(ppa).toFixed(2) : '0.00',
                        qty_dispo: qtyDispo || '0',
                        isManual: isManualProduct                });
            }
            });
            
            // Collect sortie data
            const sortieRows = document.querySelectorAll('#sortie-table-body tr');
            const validationErrors = [];
            
            sortieRows.forEach((row, index) => {
                const qtyInput = row.querySelector('input.qty-editable');
                const qty = qtyInput ? qtyInput.value : '';
                
                // Check if this is a manual product by looking for the warning div
                const isManualProduct = row.querySelector('.text-orange-600, .dark\\:text-orange-400') !== null;
                
                // Get values from hidden inputs with name attributes
                const productInput = row.querySelector('input[name="product"]');
                const dateInput = row.querySelector('input[name="date"]');
                const lotInput = row.querySelector('input[name="lot"]');
                const ppaInput = row.querySelector('input[name="ppa"]');
                const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                
                const product = productInput ? productInput.value.trim() : '';
                const qtyValue = parseInt(qty) || 0;
                const qtyDispo = qtyDispoInput ? parseInt(qtyDispoInput.value) || 0 : 0;
                
                // Validate QTY <= QTY_DISPO for manual entries
                if (qtyValue > 0 && isManualProduct && qtyValue > qtyDispo) {
                    validationErrors.push(`SORTIE Row ${index + 1}: Quantity (${qtyValue}) cannot exceed QTY_DISPO (${qtyDispo}) for product "${product}"`);
                }
                
                // Validate against max attribute for regular entries
                if (qtyValue > 0 && !isManualProduct) {
                    const maxQty = qtyInput ? parseInt(qtyInput.getAttribute('max')) || 0 : 0;
                    if (qtyValue > maxQty) {
                        validationErrors.push(`SORTIE Row ${index + 1}: Quantity (${qtyValue}) cannot exceed available quantity (${maxQty}) for product "${product}"`);
                    }
                }
                
                if (product && qty && qty > 0) {
                    sortieData.push({
                        product: product,
                        qty: parseInt(qty),
                        date: dateInput ? new Date(dateInput.value).toLocaleDateString() : '',
                        lot: lotInput ? lotInput.value : '',
                        ppa: ppaInput ? parseFloat(ppaInput.value).toFixed(2) : '0.00',
                        qty_dispo: qtyDispoInput ? qtyDispoInput.value : '0',
                        isManual: isManualProduct
                    });
                }
            });
            
            // Check for validation errors before proceeding
            if (validationErrors.length > 0) {
                alert('Validation Errors:\n\n' + validationErrors.join('\n\n') + '\n\nPlease correct these errors before saving.');
                return;
            }
            
            // Check if there's any data to save
            if (entryData.length === 0 && sortieData.length === 0) {
                alert('⚠️ No inventory data to save. Please add some products to the entry or sortie tables first.');
                return;
            }
            
            // Generate PDF
            generateInventoryPDF(entryData, sortieData);
            
            // Calculate monetary ecarts for success message
            const totalEntryAmount = entryData.reduce((sum, item) => sum + (item.qty * (parseFloat(item.p_revient) || 0)), 0);
            const totalSortieAmount = sortieData.reduce((sum, item) => sum + (item.qty * (parseFloat(item.p_revient) || 0)), 0);
            const totalAmountEcart = totalEntryAmount - totalSortieAmount;
            const totalQtyEcart = (entryData.reduce((sum, item) => sum + item.qty, 0)) - (sortieData.reduce((sum, item) => sum + item.qty, 0));
            
            // Show success message with both quantity and monetary ecarts
            alert(`✅ Inventory saved successfully as PDF!\nEntries: ${entryData.length}\nSorties: ${sortieData.length}\nTotal Qty Ecart: ${totalQtyEcart} units\nTotal Amount Ecart: ${totalAmountEcart.toFixed(2)} DA`);
        }
        
        // Print inventory data - generates PDF and opens print dialog
        function printInventory() {
            const entryData = [];
            const sortieData = [];
            
            // Collect entry data
            const entryRows = document.querySelectorAll('#entry-table-body tr');
            entryRows.forEach(row => {
                const qtyInput = row.querySelector('input.qty-editable');
                const qty = qtyInput ? qtyInput.value : '';
                
                // Check if this is a manual product by looking for the warning div or manual-entry-row class
                const isManualProduct = row.querySelector('.text-orange-600, .dark\\:text-orange-400') !== null || 
                                      row.classList.contains('manual-entry-row');
                
                // Get values from inputs with name attributes (both visible and hidden)
                const productInput = row.querySelector('input[name="product"]');
                const dateInput = row.querySelector('input[name="date"]');
                const lotInput = row.querySelector('input[name="lot"]');
                const ppaInput = row.querySelector('input[name="ppa"]');
                const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                
                const product = productInput ? productInput.value.trim() : '';
                const date = dateInput ? dateInput.value : '';
                const lot = lotInput ? lotInput.value : '';
                const ppa = ppaInput ? ppaInput.value : '';
                const qtyDispo = qtyDispoInput ? qtyDispoInput.value : '';
                
                // Get P_REVIENT from hidden input if available
                const pRevientInput = row.querySelector('input[name="p_revient"]');
                const pRevient = pRevientInput ? pRevientInput.value : '';
                
                if (product && qty && qty > 0) {
                    entryData.push({
                        product: product,
                        qty: parseInt(qty),
                        date: date ? new Date(date).toLocaleDateString() : '',
                        lot: lot,
                        ppa: ppa ? parseFloat(ppa).toFixed(2) : '0.00',
                        qty_dispo: qtyDispo || '0',
                        p_revient: pRevient ? parseFloat(pRevient).toFixed(2) : '0.00',
                        isManual: isManualProduct
                    });
                }
            });
            
            // Collect sortie data
            const sortieRows = document.querySelectorAll('#sortie-table-body tr');
            const validationErrors = [];
            
            sortieRows.forEach((row, index) => {
                const qtyInput = row.querySelector('input.qty-editable');
                const qty = qtyInput ? qtyInput.value : '';
                
                // Check if this is a manual product by looking for the warning div
                const isManualProduct = row.querySelector('.text-orange-600, .dark\\:text-orange-400') !== null;
                
                // Get values from hidden inputs with name attributes
                const productInput = row.querySelector('input[name="product"]');
                const dateInput = row.querySelector('input[name="date"]');
                const lotInput = row.querySelector('input[name="lot"]');
                const ppaInput = row.querySelector('input[name="ppa"]');
                const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                const pRevientInput = row.querySelector('input[name="p_revient"]');
                
                const product = productInput ? productInput.value.trim() : '';
                const qtyValue = parseInt(qty) || 0;
                const qtyDispo = qtyDispoInput ? parseInt(qtyDispoInput.value) || 0 : 0;
                
                // Validate QTY <= QTY_DISPO for manual entries
                if (qtyValue > 0 && isManualProduct && qtyValue > qtyDispo) {
                    validationErrors.push(`SORTIE Row ${index + 1}: Quantity (${qtyValue}) cannot exceed QTY_DISPO (${qtyDispo}) for product "${product}"`);
                }
                
                // Validate against max attribute for regular entries
                if (qtyValue > 0 && !isManualProduct) {
                    const maxQty = qtyInput ? parseInt(qtyInput.getAttribute('max')) || 0 : 0;
                    if (qtyValue > maxQty) {
                        validationErrors.push(`SORTIE Row ${index + 1}: Quantity (${qtyValue}) cannot exceed available quantity (${maxQty}) for product "${product}"`);
                    }
                }
                
                if (product && qty && qty > 0) {
                    sortieData.push({
                        product: product,
                        qty: parseInt(qty),
                        date: dateInput ? new Date(dateInput.value).toLocaleDateString() : '',
                        lot: lotInput ? lotInput.value : '',
                        ppa: ppaInput ? parseFloat(ppaInput.value).toFixed(2) : '0.00',
                        qty_dispo: qtyDispoInput ? qtyDispoInput.value : '0',
                        p_revient: pRevientInput ? parseFloat(pRevientInput.value).toFixed(2) : '0.00',
                        isManual: isManualProduct
                    });
                }
            });
            
            // Check for validation errors before proceeding
            if (validationErrors.length > 0) {
                alert('Validation Errors:\n\n' + validationErrors.join('\n\n') + '\n\nPlease correct these errors before printing.');
                return;
            }
            
            // Check if there's any data to print
            if (entryData.length === 0 && sortieData.length === 0) {
                alert('⚠️ No inventory data to print. Please add some products to the entry or sortie tables first.');
                return;
            }
            
            // Generate PDF for printing
            generateInventoryPDFForPrint(entryData, sortieData);
        }
        
        // Save inventory as pending to database
        async function saveAsPending() {
            try {
                // Show loading state
                const saveButton = document.getElementById('save-draft');
                const originalText = saveButton.textContent;
                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
                
                const inventoryTitle = document.getElementById('inventory-title').value.trim() || 'Inventory Report';
                const inventoryNotes = null; // Notes field is commented out, so always null
                const items = [];
                
                // Collect entry data
                const entryRows = document.querySelectorAll('#entry-table-body tr');
                entryRows.forEach(row => {
                    const qtyInput = row.querySelector('input.qty-editable');
                    const qty = qtyInput ? parseInt(qtyInput.value) : 0;
                    
                    if (qty > 0) {
                        // Get values from inputs
                        const productInput = row.querySelector('input[name="product"]');
                        const dateInput = row.querySelector('input[name="date"]');
                        const lotInput = row.querySelector('input[name="lot"]');
                        const ppaInput = row.querySelector('input[name="ppa"]');
                        const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                        
                        const product = productInput ? productInput.value.trim() : '';
                        
                        if (product) {
                            items.push({
                                product: product,
                                qty: qty,
                                date: dateInput ? dateInput.value : null,
                                lot: lotInput ? lotInput.value : null,
                                ppa: ppaInput ? parseFloat(ppaInput.value) || 0 : 0,
                                qty_dispo: qtyDispoInput ? parseInt(qtyDispoInput.value) || 0 : 0,
                                type: 'entry',
                                is_manual_entry: row.classList.contains('manual-entry-row')
                            });
                        }
                    }
                });
                
                // Collect sortie data
                const sortieRows = document.querySelectorAll('#sortie-table-body tr');
                const validationErrors = [];
                
                sortieRows.forEach((row, index) => {
                    const qtyInput = row.querySelector('input.qty-editable');
                    const qty = qtyInput ? parseInt(qtyInput.value) : 0;
                    
                    if (qty > 0) {
                        // Get values from inputs
                        const productInput = row.querySelector('input[name="product"]');
                        const dateInput = row.querySelector('input[name="date"]');
                        const lotInput = row.querySelector('input[name="lot"]');
                        const ppaInput = row.querySelector('input[name="ppa"]');
                        const qtyDispoInput = row.querySelector('input[name="qty_dispo"]');
                        
                        const product = productInput ? productInput.value.trim() : '';
                        const qtyDispo = qtyDispoInput ? parseInt(qtyDispoInput.value) || 0 : 0;
                        
                        // Validate QTY <= QTY_DISPO for manual entries in SORTIE
                        if (row.classList && row.classList.contains('manual-entry-row')) {
                            // For manual entries, check if qty > qty_dispo
                            if (qty > qtyDispo) {
                                validationErrors.push(`Row ${index + 1}: Quantity (${qty}) cannot exceed QTY_DISPO (${qtyDispo}) for product "${product}"`);
                            }
                        } else {
                            // For regular entries, check against the max attribute
                            const maxQty = qtyInput ? parseInt(qtyInput.getAttribute('max')) || 0 : 0;
                            if (qty > maxQty) {
                                validationErrors.push(`Row ${index + 1}: Quantity (${qty}) cannot exceed available quantity (${maxQty}) for product "${product}"`);
                            }
                        }
                        
                        if (product) {
                            items.push({
                                product: product,
                                qty: qty,
                                date: dateInput ? dateInput.value : null,
                                lot: lotInput ? lotInput.value : null,
                                ppa: ppaInput ? parseFloat(ppaInput.value) || 0 : 0,
                                qty_dispo: qtyDispo,
                                type: 'sortie',
                                is_manual_entry: row.classList.contains('manual-entry-row')
                            });
                        }
                    }
                });
                
                // Check for validation errors before proceeding
                if (validationErrors.length > 0) {
                    alert('Validation Errors:\n\n' + validationErrors.join('\n\n') + '\n\nPlease correct these errors before saving.');
                    // Restore button state
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    return;
                }
                
                if (items.length === 0) {
                    alert('No items to save. Please add some inventory items first.');
                    return;
                }
                
                // Check if tempo category is selected to set casse field
                const categoryFilter = document.getElementById('category-filter').value;
                const isCasse = categoryFilter === 'tempo' ? 'yes' : null;
                
                // Prepare data object
                const dataToSend = {
                    title: inventoryTitle,
                    notes: inventoryNotes,
                    created_by: currentUser,
                    items: items
                };
                
                // Add casse field if tempo is selected
                if (isCasse) {
                    dataToSend.casse = isCasse;
                }
                
                // Debug: log the data being sent
                console.log('Sending data:', dataToSend);
                
                // Send data to Python API
                const response = await fetch('http://192.168.1.94:5003/inventory/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dataToSend)
                });
                
                // Check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 100)}`);
                }
                
                // Get response text first to check if it's valid JSON
                const responseText = await response.text();
                console.log('Server response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response text:', responseText);
                    throw new Error(`Invalid server response: ${responseText.substring(0, 100)}...`);
                }
                
                if (result.success) {
                    alert(`✅ ${result.message}\n\nInventory ID: ${result.inventory_id}\nTotal Items: ${result.total_items}`);
                    
                    // Refresh the page to clear all fields
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(result.error || 'Unknown error occurred');
                }
                
            } catch (error) {
                console.error('Save error:', error);
                alert('❌ Error saving inventory: ' + error.message);
            } finally {
                // Restore button state
                const saveButton = document.getElementById('save-draft');
                saveButton.disabled = false;
                saveButton.textContent = originalText;
            }
        }
        
        // Generate PDF with comprehensive formatting
        async function generateInventoryPDF(entryData, sortieData) {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            // Get title and notes
            const inventoryTitle = document.getElementById('inventory-title').value.trim() || 'Inventory Report';
            const inventoryNotes = ''; // Notes field is commented out, so always empty
            
            // Calculate ecarts (variances)
            const totalEntryEcart = entryData.reduce((sum, item) => sum + item.qty, 0);
            const totalSortieEcart = sortieData.reduce((sum, item) => sum + item.qty, 0);
            const totalEcarts = totalEntryEcart - totalSortieEcart;
            
            // Calculate monetary ecarts
            const totalEntryAmount = entryData.reduce((sum, item) => sum + (item.qty * (parseFloat(item.p_revient) || 0)), 0);
            const totalSortieAmount = sortieData.reduce((sum, item) => sum + (item.qty * (parseFloat(item.p_revient) || 0)), 0);
            const totalAmountEcart = totalEntryAmount - totalSortieAmount;
            
            // Load and add BNM logo
            try {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function() {
                    generatePDFContent(pdf, img, inventoryTitle, inventoryNotes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount, totalSortieAmount, totalAmountEcart);
                };
                img.onerror = function() {
                    // Generate PDF without logo if loading fails
                    generatePDFContent(pdf, null, inventoryTitle, inventoryNotes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount, totalSortieAmount, totalAmountEcart);
                };
                img.src = 'assets/bnm.png';
            } catch (error) {
                // Generate PDF without logo if error occurs
                generatePDFContent(pdf, null, inventoryTitle, inventoryNotes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount, totalSortieAmount, totalAmountEcart);
            }
        }
        
        // Generate PDF for printing
        async function generateInventoryPDFForPrint(entryData, sortieData) {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            // Get title and notes
            const inventoryTitle = document.getElementById('inventory-title').value.trim() || 'Inventory Report';
            const inventoryNotes = ''; // Notes field is commented out, so always empty
            
            // Calculate ecarts (variances)
            const totalEntryEcart = entryData.reduce((sum, item) => sum + item.qty, 0);
            const totalSortieEcart = sortieData.reduce((sum, item) => sum + item.qty, 0);
            const totalEcarts = totalEntryEcart - totalSortieEcart;
            
            // Calculate monetary ecarts
            const totalEntryAmount = entryData.reduce((sum, item) => sum + (item.qty * (parseFloat(item.p_revient) || 0)), 0);
            const totalSortieAmount = sortieData.reduce((sum, item) => sum + (item.qty * (parseFloat(item.p_revient) || 0)), 0);
            const totalAmountEcart = totalEntryAmount - totalSortieAmount;
            
            // Load and add BNM logo
            try {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function() {
                    generatePDFContent(pdf, img, inventoryTitle, inventoryNotes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount, totalSortieAmount, totalAmountEcart, true);
                };
                img.onerror = function() {
                    // Generate PDF without logo if loading fails
                    generatePDFContent(pdf, null, inventoryTitle, inventoryNotes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount, totalSortieAmount, totalAmountEcart, true);
                };
                img.src = 'assets/bnm.png';
            } catch (error) {
                // Generate PDF without logo if error occurs
                generatePDFContent(pdf, null, inventoryTitle, inventoryNotes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount, totalSortieAmount, totalAmountEcart, true);
            }
        }
        
        // Core PDF content generation function
        function generatePDFContent(pdf, logoImg, title, notes, entryData, sortieData, totalEntryEcart, totalSortieEcart, totalEcarts, totalEntryAmount = 0, totalSortieAmount = 0, totalAmountEcart = 0, isPrint = false) {
            // Handle backward compatibility - if isPrint is passed as the 10th parameter
            if (typeof totalEntryAmount === 'boolean') {
                isPrint = totalEntryAmount;
                totalEntryAmount = 0;
                totalSortieAmount = 0;
                totalAmountEcart = 0;
            }
            let yPosition = 20;
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            
            // Header with logo and title
            if (logoImg) {
                // Add logo (left side)
                pdf.addImage(logoImg, 'PNG', 15, yPosition, 25, 25);
            }
            
            // Company title (center)
            pdf.setFontSize(20);
            pdf.setFont('helvetica', 'bold');
            pdf.text('BNM INVENTORY MANAGEMENT', pageWidth / 2, yPosition + 10, { align: 'center' });
            
            // Date (right side)
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'normal');
            const currentDate = new Date().toLocaleDateString();
            pdf.text(`Date: ${currentDate}`, pageWidth - 15, yPosition + 8, { align: 'right' });
            
            yPosition += 35;
            
            // Inventory Title
            if (title) {
                pdf.setFontSize(16);
                pdf.setFont('helvetica', 'bold');
                pdf.text(`Report: ${title}`, 15, yPosition);
                yPosition += 10;
            }
            
            // Notes section
            if (notes) {
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.text('Notes:', 15, yPosition);
                yPosition += 6;
                
                // Split notes into multiple lines if needed
                const noteLines = pdf.splitTextToSize(notes, pageWidth - 30);
                pdf.text(noteLines, 15, yPosition);
                yPosition += noteLines.length * 4 + 5;
            }
            
            // Summary section with ecarts
            yPosition += 5;
            pdf.setFontSize(12);
            pdf.setFont('helvetica', 'bold');
            pdf.text('INVENTORY SUMMARY', 15, yPosition);
            yPosition += 8;
            
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'normal');
            
            // Entry ecart (+)
            pdf.setTextColor(34, 139, 34); // Green color
            pdf.text(`+ Entry Ecart: ${totalEntryEcart} units`, 15, yPosition);
            yPosition += 6;
            
            // Sortie ecart (-)
            pdf.setTextColor(220, 20, 60); // Red color
            pdf.text(`- Sortie Ecart: ${totalSortieEcart} units`, 15, yPosition);
            yPosition += 6;
            
            // Total ecarts
            pdf.setTextColor(0, 0, 139); // Blue color
            pdf.setFont('helvetica', 'bold');
            pdf.text(`Total Ecarts: ${totalEcarts >= 0 ? '+' : ''}${totalEcarts} units`, 15, yPosition);
            yPosition += 8;
            
            // Entry amount ecart (+)
            pdf.setTextColor(34, 139, 34); // Green color
            pdf.setFont('helvetica', 'normal');
            pdf.text(`+ Entry Amount: ${totalEntryAmount.toFixed(2)} DA`, 15, yPosition);
            yPosition += 6;
            
            // Sortie amount ecart (-)
            pdf.setTextColor(220, 20, 60); // Red color
            pdf.text(`- Sortie Amount: ${totalSortieAmount.toFixed(2)} DA`, 15, yPosition);
            yPosition += 6;
            
            // Total amount ecart
            pdf.setTextColor(0, 0, 139); // Blue color
            pdf.setFont('helvetica', 'bold');
            pdf.text(`Total Amount Ecart: ${totalAmountEcart >= 0 ? '+' : ''}${totalAmountEcart.toFixed(2)} DA`, 15, yPosition);
            pdf.setTextColor(0, 0, 0); // Reset to black
            yPosition += 15;
            
            // ENTRY Table
            if (entryData.length > 0) {
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(34, 139, 34); // Green
                pdf.text('ENTRY (+)', 15, yPosition);
                pdf.setTextColor(0, 0, 0); // Reset to black
                yPosition += 8;
                
                // Prepare entry table data
                const entryTableData = entryData.map(item => [
                    item.product + (item.isManual ? '\n⚠️ No Qty Found in The System' : ''),
                    item.qty.toString(),
                    item.date,
                    item.lot || 'N/A',
                    `${item.ppa} DA`,
                    item.qty_dispo.toString()
                ]);
                
                pdf.autoTable({
                    startY: yPosition,
                    head: [['Product', 'QTY', 'Date', 'Lot', 'PPA', 'QTY_DISPO']],
                    body: entryTableData,
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [34, 139, 34], // Green header
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    bodyStyles: { fontSize: 9 },
                    columnStyles: {
                        0: { cellWidth: 45 }, // Product
                        1: { cellWidth: 20, halign: 'center' }, // QTY
                        2: { cellWidth: 25 }, // Date
                        3: { cellWidth: 25 }, // Lot
                        4: { cellWidth: 25, halign: 'right' }, // PPA
                        5: { cellWidth: 25, halign: 'center' } // QTY_DISPO
                    },
                    didParseCell: function(data) {
                        // Highlight manual entries with warning
                        if (data.cell.text[0] && data.cell.text[0].includes('⚠️')) {
                            data.cell.styles.fillColor = [255, 248, 220]; // Light yellow
                            data.cell.styles.textColor = [139, 69, 19]; // Brown text
                        }
                    }
                });
                
                yPosition = pdf.lastAutoTable.finalY + 15;
            } else {
                // Show empty state for entry
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(34, 139, 34); // Green
                pdf.text('ENTRY (+)', 15, yPosition);
                pdf.setTextColor(0, 0, 0); // Reset to black
                yPosition += 8;
                
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'italic');
                pdf.setTextColor(128, 128, 128); // Gray
                pdf.text('No entry records found.', 15, yPosition);
                pdf.setTextColor(0, 0, 0); // Reset to black
                yPosition += 15;
            }
            
            // Check if we need a new page
            if (yPosition > pageHeight - 80) {
                pdf.addPage();
                yPosition = 20;
            }
            
            // SORTIE Table
            if (sortieData.length > 0) {
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(220, 20, 60); // Red
                pdf.text('SORTIE (-)', 15, yPosition);
                pdf.setTextColor(0, 0, 0); // Reset to black
                yPosition += 8;
                
                // Prepare sortie table data
                const sortieTableData = sortieData.map(item => [
                    item.product + (item.isManual ? '\n⚠️ No Qty Found in The System' : ''),
                    item.qty.toString(),
                    item.date,
                    item.lot || 'N/A',
                    `${item.ppa} DA`,
                    item.qty_dispo.toString()
                ]);
                
                pdf.autoTable({
                    startY: yPosition,
                    head: [['Product', 'QTY', 'Date', 'Lot', 'PPA', 'QTY_DISPO']],
                    body: sortieTableData,
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [220, 20, 60], // Red header
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    bodyStyles: { fontSize: 9 },
                    columnStyles: {
                        0: { cellWidth: 45 }, // Product
                        1: { cellWidth: 20, halign: 'center' }, // QTY
                        2: { cellWidth: 25 }, // Date
                        3: { cellWidth: 25 }, // Lot
                        4: { cellWidth: 25, halign: 'right' }, // PPA
                        5: { cellWidth: 25, halign: 'center' } // QTY_DISPO
                    },
                    didParseCell: function(data) {
                        // Highlight manual entries with warning
                        if (data.cell.text[0] && data.cell.text[0].includes('⚠️')) {
                            data.cell.styles.fillColor = [255, 248, 220]; // Light yellow
                            data.cell.styles.textColor = [139, 69, 19]; // Brown text
                        }
                    }
                });
            } else {
                // Show empty state for sortie
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(220, 20, 60); // Red
                pdf.text('SORTIE (-)', 15, yPosition);
                pdf.setTextColor(0, 0, 0); // Reset to black
                yPosition += 8;
                
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'italic');
                pdf.setTextColor(128, 128, 128); // Gray
                pdf.text('No sortie records found.', 15, yPosition);
                pdf.setTextColor(0, 0, 0); // Reset to black
                yPosition += 15;
            }
            
            // Footer on all pages
            const pageCount = pdf.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                pdf.setPage(i);
                
                // Footer line
                pdf.setDrawColor(128, 128, 128);
                pdf.line(15, pageHeight - 20, pageWidth - 15, pageHeight - 20);
                
                // Footer content
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(128, 128, 128);
                
                // Left: BNM System
                if (logoImg) {
                    pdf.addImage(logoImg, 'PNG', 15, pageHeight - 18, 8, 8);
                    pdf.text('BNM System', 26, pageHeight - 12);
                } else {
                    pdf.text('BNM System', 15, pageHeight - 12);
                }
                
                // Center: Generation date and time
                const timestamp = new Date().toLocaleString();
                pdf.text(`Generated: ${timestamp}`, pageWidth / 2, pageHeight - 12, { align: 'center' });
                
                // Right: Page number
                pdf.text(`Page ${i} of ${pageCount}`, pageWidth - 15, pageHeight - 12, { align: 'right' });
            }
            
            pdf.setTextColor(0, 0, 0); // Reset text color
            
            // Save or print the PDF
            const filename = `inventory_${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_${new Date().toISOString().split('T')[0]}.pdf`;
            
            if (isPrint) {
                // Open print dialog
                const pdfBlob = pdf.output('blob');
                const pdfUrl = URL.createObjectURL(pdfBlob);
                const printWindow = window.open(pdfUrl);
                if (printWindow) {
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                } else {
                    // Fallback: download if popup blocked
                    pdf.save(filename);
                }
            } else {
                // Save to downloads
                pdf.save(filename);
            }
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize loading indicator
            createLoadingIndicator();
            
            // Load product list and setup functionality
            loadProductList();
            setupProductSearch();
            setupCategoryFilter();
            setupTitleSuggestions();
            setupEventListeners();
            
            // Don't add initial empty rows - they will be added when products are selected
        });
        

    </script>
</body>
</html>
