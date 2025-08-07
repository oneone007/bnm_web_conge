<?php
session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
//     header("Location: Acess_Denied");    
//     exit();
// }
$page_identifier = 'rot_men_vente';

require_once 'check_permission.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales Recap</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="recap_achat.css">
    <script src="theme.js"></script>
    <script src="api_config.js"></script>
    <style>
        .year-tab {
            padding: 8px 16px;
            margin-right: 4px;
            border-radius: 4px;
            cursor: pointer;
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .year-tab.active {
            background-color: #3b82f6;
            color: white;
        }
        .month-table {
            display: none;
            max-height: 80vh;
            overflow: auto;
        }
        .month-table.active {
            display: block;
            max-height: 80vh;
            overflow: auto;
        }
        .year-selector {
            display: flex;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        .month-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .month-section {
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .month-grid {
                grid-template-columns: 1fr;
            }
        }
        .month-header {
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            padding: 8px 16px;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        #yearSummaryContainer {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
            height: 400px;
            overflow: visible;
        }
        #yearSummaryContainer .table-container {
            margin-bottom: 0;
            width: 100%;
            height: 100%;
            overflow: visible;
        }
        @media (max-width: 1200px) {
            #yearSummaryContainer {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            #yearSummaryContainer {
                grid-template-columns: 1fr;
            }
        }
        .autocomplete-suggestions {
            background-color: black;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .autocomplete-suggestions div {
            color: #000;
            background-color: white;
        }
        .autocomplete-suggestions div:hover {
            background-color: #f3f4f6;
        }
        .dark .autocomplete-suggestions {
            background-color: #374151;
            border-color: #4b5563;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .dark .autocomplete-suggestions div {
            color: #f9fafb;
            padding: 8px 12px;
        }
        .dark .autocomplete-suggestions div:hover {
            background-color: #4b5563;
        }
        .dark .autocomplete-suggestions div {
            background-color: white;
            color: #000;
        }
        .dark .autocomplete-suggestions div:hover {
            background-color: #f3f4f6;
        }
        /* New styles for product supplier dropdown */
        #productSupplierContainer, #zoneClientContainer {
            transition: all 0.3s ease;
        }
        #recap_product_supplier, #recap_zone_client {
            background-color: white;
            color: black;
        }
        .dark #recap_product_supplier, .dark #recap_zone_client {
            background-color: #374151;
            color: white;
            border-color: #4b5563;
        }

        /* Select All option styling */
        #recap_product_supplier option[value="SELECT_ALL"],
        #recap_zone_client option[value="SELECT_ALL"] {
            background-color: #e3f2fd !important;
            font-weight: bold !important;
            border-bottom: 1px solid #90caf9;
        }
        
        .dark #recap_product_supplier option[value="SELECT_ALL"],
        .dark #recap_zone_client option[value="SELECT_ALL"] {
            background-color: #1e3a8a !important;
            color: #e3f2fd !important;
        }

        
        /* PDF download button styles */
        .pdf-download-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .pdf-download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 140px;
        }
        .pdf-download-btn:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }
        .pdf-download-btn:active {
            transform: translateY(0);
        }
        .pdf-download-btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        .pdf-icon, .spinner {
            display: flex;
            align-items: center;
        }
        .spinner svg {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .hidden {
            display: none;
        }
        .error-message {
            color: #e74c3c;
            font-size: 13px;
            text-align: center;
            max-width: 200px;
        }
        
        /* Enhanced scrolling styles */
        .table-container {
            max-height: 70vh;
            overflow: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        .dark .table-container {
            border-color: #4b5563;
        }
        
        /* Year summary container - fixed height, no scrolling */
        #yearSummaryContainer {
            height: 400px;
            overflow: visible;
            padding-right: 0.5rem;
        }
        
        /* Individual year summary table - no scrolling, show all data */
        #yearSummaryContainer .table-container {
            height: 100%;
            overflow: visible;
            margin-bottom: 1rem;
        }
        
        /* Year summary tables - no horizontal scrolling */
        #yearSummaryContainer .overflow-x-auto {
            overflow-x: visible !important;
            overflow-y: visible !important;
        }
        
        /* Year summary table styling - compact and fully visible */
        #yearSummaryContainer table {
            width: 100% !important;
            min-width: auto !important;
            table-layout: auto;
        }
        
        #yearSummaryContainer table th,
        #yearSummaryContainer table td {
            padding: 4px 6px !important;
            font-size: 0.8rem !important;
            white-space: nowrap;
            overflow: visible;
            text-overflow: clip;
        }
        
        /* Distribute column widths evenly for year summary */
        #yearSummaryContainer table th:first-child,
        #yearSummaryContainer table td:first-child {
            width: 20%;
            min-width: 80px;
        }
        
        #yearSummaryContainer table th:not(:first-child),
        #yearSummaryContainer table td:not(:first-child) {
            width: auto;
            min-width: 90px;
        }
        
        /* Monthly tables scrolling */
        .month-table {
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .month-table.active {
            display: block;
            max-height: 80vh;
            overflow: auto;
        }
        
        /* Enhanced table scrolling */
        .overflow-x-auto {
            overflow-x: auto;
            overflow-y: visible;
            max-width: 100%;
        }
        
        /* Compact table cell styling */
        .compact-cell {
            white-space: nowrap;
            min-width: 120px;
            padding: 4px 8px !important;
            font-size: 0.875rem;
            line-height: 1.2;
        }
        
        /* Month data formatting */
        .month-data {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 100px;
            white-space: nowrap;
        }
        
        .month-data-item {
            font-size: 0.8rem;
            padding: 1px 4px;
            border-radius: 2px;
            text-align: right;
        }
        
        .qty-item {
            background-color: rgba(59, 130, 246, 0.1);
            color: #1e40af;
        }
        
        .total-item {
            background-color: rgba(34, 197, 94, 0.1);
            color: #166534;
        }
        
        .marge-item {
            background-color: rgba(245, 158, 11, 0.1);
            color: #92400e;
        }
        
        .dark .qty-item {
            background-color: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }
        
        .dark .total-item {
            background-color: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }
        
        .dark .marge-item {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
        
        /* Sticky headers for better navigation */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: inherit;
        }
        
        /* Sticky first column */
        .sticky-left {
            position: sticky;
            left: 0;
            z-index: 15;
            background-color: inherit;
        }
        
        /* Scrollbar styling */
        .table-container::-webkit-scrollbar,
        #yearSummaryContainer::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .table-container::-webkit-scrollbar-track,
        #yearSummaryContainer::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        .dark .table-container::-webkit-scrollbar-track,
        .dark #yearSummaryContainer::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .table-container::-webkit-scrollbar-thumb,
        #yearSummaryContainer::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .dark .table-container::-webkit-scrollbar-thumb,
        .dark #yearSummaryContainer::-webkit-scrollbar-thumb {
            background: #6b7280;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover,
        #yearSummaryContainer::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .dark .table-container::-webkit-scrollbar-thumb:hover,
        .dark #yearSummaryContainer::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Table wrapper for better mobile display */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .dark .table-wrapper {
            border-color: #4b5563;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        /* Ensure tables take full width within scrollable container */
        .table-wrapper table {
            min-width: 100%;
            white-space: nowrap;
        }
        
        /* Table horizontal scroll improvements */
        .table-container {
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 70vh;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        
        /* Minimum table width to ensure readability */
        .table-container table {
            min-width: 1600px; /* Ensure table is wide enough for product + supplier + 12 months */
        }
        
        /* Better mobile responsiveness for year tabs */
        .year-selector {
            display: flex;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
            scroll-behavior: smooth;
        }
        
        /* Improved scrollbar for year selector */
        .year-selector::-webkit-scrollbar {
            height: 6px;
        }
        
        .year-selector::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .year-selector::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .year-selector::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Pagination styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-top: 1px solid #e5e7eb;
            margin-top: 16px;
        }
        
        .dark .pagination-container {
            border-color: #4b5563;
        }
        
        .pagination-info {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .dark .pagination-info {
            color: #9ca3af;
        }
        
        .pagination-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .pagination-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background-color: white;
            color: #374151;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .dark .pagination-btn {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .dark .pagination-btn:hover:not(:disabled) {
            background-color: #4b5563;
            border-color: #6b7280;
        }
        
        .dark .pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .page-size-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }
        
        .page-size-selector select {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
            color: #374151;
        }
        
        .dark .page-size-selector select {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .client-section {
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: white;
        }
        
        .dark .client-section {
            border-color: #4b5563;
            background-color: #1f2937;
        }
        
        .client-section.hidden {
            display: none;
        }

        /* Individual client table pagination */
        .client-table-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-top: 2px solid #e5e7eb;
            background-color: #f8fafc;
            font-size: 0.8rem;
            border-radius: 0 0 8px 8px;
        }
        
        .dark .client-table-pagination {
            border-color: #4b5563;
            background-color: #334155;
        }
        
        .client-pagination-info {
            color: #4b5563;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .dark .client-pagination-info {
            color: #d1d5db;
        }
        
        .client-pagination-controls {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        
        .client-pagination-btn {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            background-color: white;
            color: #374151;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 24px;
            text-align: center;
        }
        
        .client-pagination-btn:hover:not(:disabled) {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .client-pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .client-pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .dark .client-pagination-btn {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .dark .client-pagination-btn:hover:not(:disabled) {
            background-color: #4b5563;
            border-color: #6b7280;
        }
        
        .dark .client-pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .client-page-size-selector {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .client-page-size-selector select {
            padding: 2px 6px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
            color: #374151;
            font-size: 0.7rem;
        }
        
        .dark .client-page-size-selector select {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .product-row.hidden {
            display: none;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">Rotation Mensuelle des Ventes</h1>
        </div>

        <!-- Filters -->
        <div class="dashboard-container ycheffck">
            <div class="search-controls bg-white dark:bg-gray-900 p-4 rounded-lg shadow-md mb-6">
                <!-- Year Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 years">Select Years</label>
                    <div class="flex flex-wrap gap-4">
                        <?php
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i >= $currentYear - 3; $i--) {
                            echo '<label class="inline-flex items-center">
                                <input type="checkbox" class="year-checkbox" value="'.$i.'">
                                <span class="ml-2 year-label years">'.$i.'</span>
                            </label>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Search Controls -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4 recap-grid">
                <!-- Product Search -->
                <div>
                    <label for="recap_product" class="block text-sm font-medium recap-label">Product</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_product" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="product_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>
                
                <!-- Product-Specific Suppliers Dropdown -->
                <div id="productSupplierContainer" class="hidden">
                    <label for="recap_product_supplier" class="block text-sm font-medium recap-label">Suppliers for Product</label>
                    <select id="recap_product_supplier" class="w-full p-2 border rounded recap-input" style="color:black" multiple size="4">
                        <option value="">Loading suppliers...</option>
                    </select>
                    <div class="mt-1 text-xs text-black-500">Click to select/deselect multiple suppliers. Use "Select All" to select all at once.</div>
                </div>

                <!-- Zone Search -->
                <div>
                    <label for="recap_zone" class="block text-sm font-medium recap-label">Zone</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_zone" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="zone_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>
                
                <!-- Zone-Specific Clients Dropdown -->
                <div id="zoneClientContainer" class="hidden">
                    <label for="recap_zone_client" class="block text-sm font-medium recap-label">Clients for Zone</label>
                    <select id="recap_zone_client" class="w-full p-2 border rounded recap-input" style="color:black" multiple size="4">
                        <option value="">Loading clients...</option>
                    </select>
                    <div class="mt-1 text-xs text-black-500">Click to select/deselect multiple clients. Use "Select All" to select all at once.</div>
                </div>

                <!-- All Suppliers Search -->
                <div>
                    <label for="recap_fournisseur" class="block text-sm font-medium recap-label">All Suppliers</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_fournisseur" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="fournisseur_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>

                <!-- All Clients Search -->
                <div>
                    <label for="recap_client" class="block text-sm font-medium recap-label">All Clients</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_client" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="client_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>
            </div>
            
            <button id="applyFilters" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                Apply Filters
            </button>
            <button id="resetFilters" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded transition hidden">
                Reset
            </button>
        </div>

        <!-- PDF Download Button -->
        <div class="pdf-download-container">
            <button class="pdf-download-btn" id="exportPdf">
                <span class="pdf-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.523 10.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.082.038a1 1 0 0 1-.146.05q-.327.11-.658 0a1 1 0 0 1-.31-.123 1 1 0 0 1-.165-.153 1 1 0 0 1-.123-.31q-.11-.327 0-.658a1 1 0 0 1 .05-.146l.038-.082q.056-.137.572-.635.27-.31.606-.645a8 8 0 0 1 .238-.459l-2.36-2.36a8 8 0 0 1-.725.725L.5 9.5l.5.5 1.642-1.642a8 8 0 0 1 .725-.725l2.36 2.36Z"/>
                        <path d="M14.5 3.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13Zm-13-1A1.5 1.5 0 0 0 0 4v9a1.5 1.5 0 0 0 1.5 1.5h13a1.5 1.5 0 0 0 1.5-1.5v-9a1.5 1.5 0 0 0-1.5-1.5h-13Z"/>
                    </svg>
                </span>
                <span class="btn-text">Download PDF</span>
                <span class="spinner hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                        <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                    </svg>
                </span>
            </button>
            <div class="error-message hidden" id="pdfError"></div>
        </div>

        <!-- Loading Animation -->
        <div id="loading-animation" class="flex justify-center items-center my-8 hidden">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Year Summary Tables -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-center dark:text-white">Year Summary</h2>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">
                📊 Scroll to view all year summaries
            </div>
            <div id="yearSummaryContainer" class="mb-8">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Data Container with Year Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
            <h2 class="text-xl font-semibold mb-4 text-center dark:text-white">Monthly Product Rotation</h2>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">
                � Products are listed with their suppliers in separate columns for better readability<br>
                �📄 Tables are paginated to show 20 products at a time by default. Use pagination controls below to navigate through all products.
            </div>
            <div id="dataContainer" class="space-y-8">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        <br> <br>
    </div>


    <script>



        // DOM Elements
        const elements = {
            applyBtn: document.getElementById('applyFilters'),
            resetBtn: document.getElementById('resetFilters'),
            inputs: {
                fournisseur: document.getElementById('recap_fournisseur'),
                product: document.getElementById('recap_product'),
                zone: document.getElementById('recap_zone'),
                client: document.getElementById('recap_client')
            },
            suggestionBoxes: {
                fournisseur: document.getElementById('fournisseur_suggestions'),
                product: document.getElementById('product_suggestions'),
                zone: document.getElementById('zone_suggestions'),
                client: document.getElementById('client_suggestions')
            },
            yearCheckboxes: document.querySelectorAll('.year-checkbox'),
            yearSummaryContainer: document.getElementById('yearSummaryContainer'),
            productSupplierContainer: document.getElementById('productSupplierContainer'),
            productSupplierSelect: document.getElementById('recap_product_supplier'),
            zoneClientContainer: document.getElementById('zoneClientContainer'),
            zoneClientSelect: document.getElementById('recap_zone_client')
        };

        // Constants
        const API_ENDPOINTS = {
            download_pdf: API_CONFIG.getApiUrl('/rotation_monthly_vente_pdf'),
            fetchProductData: API_CONFIG.getApiUrl('/rot_mont_vente'),
            listFournisseur: API_CONFIG.getApiUrl('/listfournisseur'),
            listProduct: API_CONFIG.getApiUrl('/fetch-rotation-product-data'),
            fetchSuppliersByProduct: API_CONFIG.getApiUrl('/fetchSuppliersByProduct'),
            listRegion: API_CONFIG.getApiUrl('/listregion'),
            listClient: API_CONFIG.getApiUrl('/listclient'),
            fetchZoneClients: API_CONFIG.getApiUrl('/fetchZoneClients')
        };


        // Store product mapping (name -> id)
        let productMap = {};

        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        function formatNumber(num, locale = 'fr-FR') {
            return new Intl.NumberFormat(locale, {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            }).format(num);
        }

        // Pagination state
        let currentPage = 1;
        let itemsPerPage = 20; // More items per page for product list
        let totalItems = 0;
        let allProductRows = [];

        // Initialize pagination for product rows
        function initializePagination() {
            const activeTable = document.querySelector('.month-table.active');
            if (!activeTable) return;
            
            const productRows = activeTable.querySelectorAll('.product-row:not(.totals-row)');
            allProductRows = Array.from(productRows);
            totalItems = allProductRows.length;
            updatePagination();
        }

        // Update pagination display
        function updatePagination() {
            if (totalItems === 0) return;
            
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Show/hide product rows based on current page
            allProductRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });

            // Update pagination controls
            updatePaginationControls(totalPages);
        }

        // Update pagination controls
        function updatePaginationControls(totalPages) {
            let existingContainer = document.querySelector('.pagination-container');
            if (existingContainer) {
                existingContainer.remove();
            }

            if (totalPages <= 1) return;

            const container = document.createElement('div');
            container.className = 'pagination-container';

            // Pagination info
            const startItem = (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, totalItems);
            const info = document.createElement('div');
            info.className = 'pagination-info';
            info.textContent = `Showing ${startItem}-${endItem} of ${totalItems} products`;

            // Pagination controls
            const controls = document.createElement('div');
            controls.className = 'pagination-controls';

            // Page size selector
            const pageSizeContainer = document.createElement('div');
            pageSizeContainer.className = 'page-size-selector';
            pageSizeContainer.innerHTML = `
                <span>Show:</span>
                <select id="pageSize">
                    <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
                    <option value="20" ${itemsPerPage === 20 ? 'selected' : ''}>20</option>
                    <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
                    <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100</option>
                </select>
            `;

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'pagination-btn';
            prevBtn.textContent = '← Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            };

            // Page number buttons
            const maxButtons = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);
            
            if (endPage - startPage + 1 < maxButtons) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => {
                    currentPage = i;
                    updatePagination();
                };
                controls.appendChild(pageBtn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = 'pagination-btn';
            nextBtn.textContent = 'Next →';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            };

            controls.appendChild(prevBtn);
            controls.appendChild(nextBtn);

            container.appendChild(info);
            container.appendChild(pageSizeContainer);
            container.appendChild(controls);

            // Insert pagination after the active year table container
            const dataContainer = document.getElementById('dataContainer');
            dataContainer.appendChild(container);

            // Add page size change handler
            document.getElementById('pageSize').addEventListener('change', (e) => {
                itemsPerPage = parseInt(e.target.value);
                currentPage = 1;
                updatePagination();
            });
        }

        // Reset pagination on new data
        function resetPagination() {
            currentPage = 1;
            initializePagination();
        }

        function showLoading(show) {
            document.getElementById('loading-animation').classList.toggle('hidden', !show);
            document.getElementById('dataContainer').classList.toggle('opacity-50', show);
        }

        function getSelectedYears() {
            const selectedYears = [];
            elements.yearCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedYears.push(checkbox.value);
                }
            });
            return selectedYears;
        }

        function createYearTabs(years) {
            const container = document.createElement('div');
            container.className = 'year-selector sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4';
            
            years.forEach(year => {
                const tab = document.createElement('div');
                tab.className = 'year-tab';
                tab.textContent = year;
                tab.dataset.year = year;
                tab.addEventListener('click', () => switchYear(year));
                container.appendChild(tab);
            });
            
            // Activate first tab by default
            if (years.length > 0) {
                container.querySelector('.year-tab').classList.add('active');
            }
            
            return container;
        }

        function switchYear(year) {
            // Save current scroll position
            const currentActiveTable = document.querySelector('.month-table.active');
            let scrollPosition = { top: 0, left: 0 };
            if (currentActiveTable) {
                scrollPosition = {
                    top: currentActiveTable.scrollTop,
                    left: currentActiveTable.scrollLeft
                };
            }
            
            // Update active tab
            document.querySelectorAll('.year-tab').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.year === year);
            });
            
            // Show tables for selected year
            document.querySelectorAll('.month-table').forEach(table => {
                table.classList.toggle('active', table.dataset.year === year);
            });
            
            // Update pagination for the new active year
            setTimeout(() => {
                resetPagination();
                
                // Restore scroll position for new active table
                const newActiveTable = document.querySelector('.month-table.active');
                if (newActiveTable) {
                    newActiveTable.scrollTop = scrollPosition.top;
                    newActiveTable.scrollLeft = scrollPosition.left;
                }
            }, 100);
        }

        function updateYearSummaryTables(data, years) {
            const container = elements.yearSummaryContainer;
            container.innerHTML = '';
            
            years.forEach(year => {
                const yearData = data[year] || {};
                
                // Skip if no data for this year
                if (Object.keys(yearData).length === 0) return;
                
                // Create year section
                const yearSection = document.createElement('div');
                yearSection.className = 'table-container rounded-lg bg-white shadow-md dark:bg-gray-800';
                
                // Create year header
                const yearHeader = document.createElement('h2');
                yearHeader.className = 'text-lg font-semibold p-2 dark:text-white text-center bg-blue-50 dark:bg-blue-900';
                yearHeader.textContent = `Year ${year}`;
                
                // Create table
                let tableHTML = `
                    <div class="h-full">
                        <table class="w-full border-collapse text-sm text-left dark:text-white">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="table-header">
                                    <th class="border px-2 py-1 bg-white dark:bg-gray-700">Month</th>
                                    <th class="border px-2 py-1 text-right">Qty</th>
                                    <th class="border px-2 py-1 text-right">Total</th>
                                    <th class="border px-2 py-1 text-right">Marge %</th>
                                </tr>
                            </thead>
                            <tbody class="dark:bg-gray-800">`;
                
                let yearlyTotals = { QTY: 0, TOTAL: 0, MARGE: 0 };
                
                // Track valid months for average calculation
                let validMonthsCount = 0;
                let yearlyMargeSum = 0;
                
                // Add rows for each month - Calculate totals from details
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = yearData[monthNum] || { details: [] };
                    
                    // Calculate month totals from all products/details
                    let monthTotals = { QTY: 0, TOTAL: 0, MARGE: 0 };
                    let supplierCount = 0;
                    
                    if (monthData.details && Array.isArray(monthData.details)) {
                        // Calculate margin from aggregated totals (same as backend logic)
                        let monthTotalConsomation = 0;
                        
                        monthData.details.forEach(item => {
                            monthTotals.QTY += (item.QTY || 0);
                            monthTotals.TOTAL += (item.TOTAL || 0);
                            
                            // Calculate CONSOMATION = TOTAL / (1 + MARGE)
                            const itemTotal = item.TOTAL || 0;
                            const itemMarge = item.MARGE || 0;
                            const itemConsomation = itemMarge > 0 ? itemTotal / (1 + itemMarge) : itemTotal;
                            monthTotalConsomation += itemConsomation;
                        });
                        
                        // Calculate margin from aggregated totals: (TOTAL - CONSOMATION) / CONSOMATION
                        if (monthTotalConsomation > 0) {
                            monthTotals.MARGE = (monthTotals.TOTAL - monthTotalConsomation) / monthTotalConsomation;
                            supplierCount = 1; // We have valid data
                        }
                    }
                    
                    // Add to yearly totals
                    yearlyTotals.QTY += monthTotals.QTY;
                    yearlyTotals.TOTAL += monthTotals.TOTAL;
                    
                    // Only count months with suppliers for marge average
                    if (supplierCount > 0) {
                        yearlyMargeSum += monthTotals.MARGE;
                        validMonthsCount++;
                    }
                    
                    tableHTML += `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border px-2 py-1 bg-white dark:bg-gray-800">${monthNames[month - 1]}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(monthTotals.QTY)}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(monthTotals.TOTAL)}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(monthTotals.MARGE * 100)}%</td>
                        </tr>`;
                }
                
                // Calculate yearly average marge
                yearlyTotals.MARGE = validMonthsCount > 0 ? yearlyMargeSum / validMonthsCount : 0;
                
                // Add yearly total row
                tableHTML += `
                            <tr class="bg-blue-50 dark:bg-blue-900 font-semibold">
                                <td class="border px-2 py-1 bg-blue-50 dark:bg-blue-900">TOTAL</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotals.QTY)}</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotals.TOTAL)}</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotals.MARGE * 100)}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>`;
                
                yearSection.appendChild(yearHeader);
                yearSection.insertAdjacentHTML('beforeend', tableHTML);
                container.appendChild(yearSection);
            });
        }

async function loadData() {
    const years = getSelectedYears();
    const fournisseurs = getSelectedSuppliers();
    const clients = getSelectedClients();
    const productName = elements.inputs.product.value;
    const zone = elements.inputs.zone.value;

    if (!years.length) {
        document.getElementById('dataContainer').innerHTML = `
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100" role="alert">
                <span class="block sm:inline">Please select at least one year</span>
            </div>`;
        elements.yearSummaryContainer.innerHTML = '';
        return;
    }

    showLoading(true);

    try {
        // Create URL with parameters
        let url = `${API_ENDPOINTS.fetchProductData}?years=${years.join(',')}`;
        if (fournisseurs.length > 0) {
            url += `&fournisseur=${fournisseurs.join(',')}`;
        }
        if (productName) {
            const productId = productMap[productName];
            if (productId) {
                url += `&product_id=${encodeURIComponent(productId)}`;
            } else {
                console.warn(`No product ID found for: ${productName}, skipping product filter`);
            }
        }
        if (clients.length > 0) {
            url += `&client=${clients.join(',')}`;
        }
        if (zone) {
            url += `&zone=${encodeURIComponent(zone)}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        if (Object.keys(data).length === 0) {
            const noDataMessage = `
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100" role="alert">
                    <span class="block sm:inline">No data found for the selected criteria</span>
                </div>`;
            document.getElementById('dataContainer').innerHTML = noDataMessage;
            elements.yearSummaryContainer.innerHTML = noDataMessage;
            return;
        }

        // Update year summary tables
        updateYearSummaryTables(data, years);

        const container = document.getElementById('dataContainer');
        container.innerHTML = '';
        
        // Add year tabs
        const yearTabs = createYearTabs(years);
        container.appendChild(yearTabs);
        
        // Create a container for yearly tables
        years.forEach(year => {
            const yearData = data[year] || {};
            
            // Create year table container
            const yearTableContainer = document.createElement('div');
            yearTableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
            yearTableContainer.dataset.year = year;
            
            // Group all data by product-supplier combination
            const productGroups = {};
            for (let month = 1; month <= 12; month++) {
                const monthNum = month.toString().padStart(2, '0');
                const monthData = yearData[monthNum] || { details: [] };
                
                monthData.details.forEach(item => {
                    const productKey = `${item.PRODUIT}|${item.FOURNISSEUR}`;
                    if (!productGroups[productKey]) {
                        productGroups[productKey] = {
                            name: item.PRODUIT,
                            supplier: item.FOURNISSEUR,
                            quantities: Array(12).fill(0),
                            totals: Array(12).fill(0),
                            marges: Array(12).fill(0),
                            // Keep track of individual items for margin calculation
                            monthItems: Array(12).fill(null).map(() => [])
                        };
                    }
                    
                    const productData = productGroups[productKey];
                    productData.quantities[month - 1] += (item.QTY || 0);
                    productData.totals[month - 1] += (item.TOTAL || 0);
                    // Keep track of individual items for proper margin calculation
                    productData.monthItems[month - 1].push({
                        TOTAL: item.TOTAL || 0,
                        MARGE: item.MARGE || 0
                    });
                });
            }
            
            // Calculate correct margins for each product-supplier combination
            Object.values(productGroups).forEach(productData => {
                for (let month = 0; month < 12; month++) {
                    const monthItems = productData.monthItems[month];
                    if (monthItems.length > 0) {
                        // Calculate margin from aggregated totals for this product-supplier-month
                        let totalConsomation = 0;
                        let totalTotal = 0;
                        
                        monthItems.forEach(item => {
                            totalTotal += item.TOTAL;
                            // CONSOMATION = TOTAL / (1 + MARGE)
                            const consomation = item.MARGE > 0 ? item.TOTAL / (1 + item.MARGE) : item.TOTAL;
                            totalConsomation += consomation;
                        });
                        
                        // Calculate margin: (TOTAL - CONSOMATION) / CONSOMATION
                        productData.marges[month] = totalConsomation > 0 
                            ? (totalTotal - totalConsomation) / totalConsomation 
                            : 0;
                    }
                }
                // Clean up the temporary monthItems array
                delete productData.monthItems;
            });
            
            // Convert to array and create table
            const products = Object.values(productGroups);
            const tableContainer = createProductSupplierTable(products, year);
            yearTableContainer.appendChild(tableContainer);
            
            container.appendChild(yearTableContainer);
        });
        
        elements.resetBtn.classList.remove('hidden');
        
        // Initialize pagination after data is loaded
        setTimeout(() => {
            resetPagination();
        }, 100);
        
    } catch (error) {
        console.error('Error loading data:', error);
        document.getElementById('dataContainer').innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-100" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> ${error.message}</span>
            </div>`;
    } finally {
        showLoading(false);
    }
}

function createProductSupplierTable(products, year) {
    const tableContainer = document.createElement('div');
    tableContainer.className = 'table-container overflow-auto';

    // Create the table
    const table = document.createElement('table');
    table.className = 'min-w-full border-collapse text-sm';

    // Create header
    const thead = document.createElement('thead');
    thead.className = 'sticky-header';
    const headerRow = document.createElement('tr');

    // Product header cell
    const productHeader = document.createElement('th');
    productHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
    productHeader.textContent = 'Product';
    headerRow.appendChild(productHeader);

    // Supplier header cell
    const supplierHeader = document.createElement('th');
    supplierHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
    supplierHeader.style.left = '200px'; // Adjust based on product column width
    supplierHeader.textContent = 'Supplier';
    headerRow.appendChild(supplierHeader);

    // Month headers (one column per month with combined data)
    for (let month = 1; month <= 12; month++) {
        const monthHeader = document.createElement('th');
        monthHeader.className = 'border px-2 py-1 text-center bg-blue-50 dark:bg-blue-900 font-medium sticky-header compact-cell';
        monthHeader.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 4px;">${monthNames[month - 1]}</div>
            <div style="font-size: 0.7rem; color: #6b7280;">Qty | Total | Marge</div>
        `;
        headerRow.appendChild(monthHeader);
    }

    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table body
    const tbody = document.createElement('tbody');
    products.forEach(product => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 product-row';

        // Product name cell
        const nameCell = document.createElement('td');
        nameCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
        nameCell.style.minWidth = '200px';
        nameCell.innerHTML = `
            <div class="text-gray-900 dark:text-gray-100 font-medium">
                ${product.name}
            </div>
        `;
        row.appendChild(nameCell);

        // Supplier cell
        const supplierCell = document.createElement('td');
        supplierCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
        supplierCell.style.left = '200px';
        supplierCell.style.minWidth = '150px';
        supplierCell.innerHTML = `
            <div class="text-blue-600 dark:text-blue-400 font-bold">
                ${product.supplier || 'Unknown'}
            </div>
        `;
        row.appendChild(supplierCell);

        // Data cells for each month (combined format)
        for (let month = 0; month < 12; month++) {
            const dataCell = document.createElement('td');
            dataCell.className = 'border compact-cell text-center bg-gray-50 dark:bg-gray-800/50';
            
            const qty = formatNumber(product.quantities[month] || 0);
            const total = formatNumber(product.totals[month] || 0);
            const marge = formatNumber((product.marges[month] || 0) * 100);
            
            dataCell.innerHTML = `
                <div class="month-data">
                    <div class="month-data-item qty-item">${qty}</div>
                    <div class="month-data-item total-item">${total}</div>
                    <div class="month-data-item marge-item">${marge}%</div>
                </div>
            `;
            row.appendChild(dataCell);
        }

        tbody.appendChild(row);
    });

    // Create totals row
    const totalsRow = document.createElement('tr');
    totalsRow.className = 'font-bold bg-gray-100 dark:bg-gray-700 totals-row';

    // Totals label
    const totalsLabel = document.createElement('td');
    totalsLabel.className = 'sticky-left bg-gray-100 dark:bg-gray-700 border px-4 py-2 z-10';
    totalsLabel.textContent = 'TOTAL';
    totalsRow.appendChild(totalsLabel);

    // Empty supplier cell for totals row
    const emptySupplierCell = document.createElement('td');
    emptySupplierCell.className = 'sticky-left bg-gray-100 dark:bg-gray-700 border px-4 py-2 z-10';
    emptySupplierCell.style.left = '200px';
    emptySupplierCell.textContent = '';
    totalsRow.appendChild(emptySupplierCell);

    // Calculate and add totals for each month
    for (let month = 0; month < 12; month++) {
        const monthQtyTotal = products.reduce((sum, product) => sum + (product.quantities[month] || 0), 0);
        const monthTotalTotal = products.reduce((sum, product) => sum + (product.totals[month] || 0), 0);
        
        // Calculate marge from aggregated totals (same as backend logic)
        // First, calculate the aggregated CONSOMATION (TOTAL - MARGE*TOTAL) for the month
        let monthTotalConsomation = 0;
        products.forEach(product => {
            const productTotal = product.totals[month] || 0;
            const productMarge = product.marges[month] || 0;
            // CONSOMATION = TOTAL / (1 + MARGE)
            const productConsomation = productMarge > 0 ? productTotal / (1 + productMarge) : productTotal;
            monthTotalConsomation += productConsomation;
        });
        
        // Calculate margin from aggregated totals: (TOTAL - CONSOMATION) / CONSOMATION
        const monthMargeAverage = monthTotalConsomation > 0 
            ? (monthTotalTotal - monthTotalConsomation) / monthTotalConsomation
            : 0;

        const totalCell = document.createElement('td');
        totalCell.className = 'border compact-cell text-center bg-gray-200 dark:bg-gray-600';
        
        totalCell.innerHTML = `
            <div class="month-data">
                <div class="month-data-item qty-item font-bold">${formatNumber(monthQtyTotal)}</div>
                <div class="month-data-item total-item font-bold">${formatNumber(monthTotalTotal)}</div>
                <div class="month-data-item marge-item font-bold">${formatNumber(monthMargeAverage * 100)}%</div>
            </div>
        `;
        totalsRow.appendChild(totalCell);
    }

    tbody.appendChild(totalsRow);
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    return tableContainer;
}

// Update getSelectedSuppliers function to handle multiple selections properly
function getSelectedSuppliers() {
    const productSupplierSelect = elements.productSupplierSelect;
    const fournisseurInput = elements.inputs.fournisseur;
    
    // Check if product supplier dropdown is visible and has selections
    if (!elements.productSupplierContainer.classList.contains('hidden')) {
        const selectedOptions = Array.from(productSupplierSelect.selectedOptions);
        const selectedValues = selectedOptions
            .map(option => option.value)
            .filter(value => value !== '' && value !== 'SELECT_ALL'); // Filter out empty and SELECT_ALL
        if (selectedValues.length > 0) {
            return selectedValues;
        }
    }
    
    // Check fournisseur input as fallback
    if (fournisseurInput.value.trim()) {
        return [fournisseurInput.value.trim()];
    }
    
    return [];
}

// Get selected clients function to handle multiple selections properly
function getSelectedClients() {
    const zoneClientSelect = elements.zoneClientSelect;
    const clientInput = elements.inputs.client;
    
    // Check if zone client dropdown is visible and has selections
    if (!elements.zoneClientContainer.classList.contains('hidden')) {
        const selectedOptions = Array.from(zoneClientSelect.selectedOptions);
        const selectedValues = selectedOptions
            .map(option => option.value)
            .filter(value => value !== '' && value !== 'SELECT_ALL'); // Filter out empty and SELECT_ALL
        if (selectedValues.length > 0) {
            return selectedValues;
        }
    }
    
    // Check client input as fallback
    if (clientInput.value.trim()) {
        return [clientInput.value.trim()];
    }
    
    return [];
}

// Update product supplier selection event handler
elements.productSupplierSelect.addEventListener('change', function() {
    const selectedValues = Array.from(this.selectedOptions).map(option => option.value);
    
    // Handle "Select All" functionality
    if (selectedValues.includes('SELECT_ALL')) {
        // Select all supplier options except the "Select All" option itself
        Array.from(this.options).forEach(option => {
            if (option.value !== 'SELECT_ALL' && option.value !== '') {
                option.selected = true;
            } else if (option.value === 'SELECT_ALL') {
                option.selected = false; // Deselect the "Select All" option after use
            }
        });
        
        // Update the visual state
        const allSelectedSuppliers = Array.from(this.selectedOptions).map(option => option.value);
        if (allSelectedSuppliers.length > 0) {
            elements.inputs.fournisseur.value = ''; // Clear the general supplier input
        }
    } else {
        // Normal selection handling
        const selectedSuppliers = selectedValues.filter(value => value !== 'SELECT_ALL' && value !== '');
        if (selectedSuppliers.length > 0) {
            elements.inputs.fournisseur.value = ''; // Clear the general supplier input
        }
    }
});

// Update zone client selection event handler
elements.zoneClientSelect.addEventListener('change', function() {
    const selectedValues = Array.from(this.selectedOptions).map(option => option.value);
    
    // Handle "Select All" functionality
    if (selectedValues.includes('SELECT_ALL')) {
        // Select all client options except the "Select All" option itself
        Array.from(this.options).forEach(option => {
            if (option.value !== 'SELECT_ALL' && option.value !== '') {
                option.selected = true;
            } else if (option.value === 'SELECT_ALL') {
                option.selected = false; // Deselect the "Select All" option after use
            }
        });
        
        // Update the visual state
        const allSelectedClients = Array.from(this.selectedOptions).map(option => option.value);
        if (allSelectedClients.length > 0) {
            elements.inputs.client.value = ''; // Clear the general client input
        }
    } else {
        // Normal selection handling
        const selectedClients = selectedValues.filter(value => value !== 'SELECT_ALL' && value !== '');
        if (selectedClients.length > 0) {
            elements.inputs.client.value = ''; // Clear the general client input
        }
    }
});

// Handle autocomplete selection for fournisseur input
elements.suggestionBoxes.fournisseur.addEventListener('click', function(e) {
    if (e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
        elements.inputs.fournisseur.value = e.target.textContent;
        elements.productSupplierSelect.selectedIndex = -1; // Clear product supplier selections
        this.classList.add('hidden');
    }
});

        // Initialize autocomplete for fournisseur and product
        async function initAutocomplete() {
            const ITEMS_PER_PAGE = 10;
            let currentFournisseurPage = 0;
            let currentProductPage = 0;
            let allFournisseurs = [];
            let allProducts = [];
            
            function showPaginatedSuggestions(filteredItems, currentPage, suggestionBox) {
                const startIdx = currentPage * ITEMS_PER_PAGE;
                const paginatedItems = filteredItems.slice(startIdx, startIdx + ITEMS_PER_PAGE);
                
                if (paginatedItems.length > 0) {
                    suggestionBox.innerHTML = paginatedItems.map(item => 
                        `<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">${item}</div>`
                    ).join('');
                    
                    if (filteredItems.length > ITEMS_PER_PAGE) {
                        const totalPages = Math.ceil(filteredItems.length / ITEMS_PER_PAGE);
                        suggestionBox.innerHTML += `
                            <div class="flex justify-between p-2 border-t border-gray-200 dark:border-gray-600">
                                <button class="pagination-prev px-2 py-1 rounded ${currentPage === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-600'}" 
                                        ${currentPage === 0 ? 'disabled' : ''}>
                                    Previous
                                </button>
                                <span class="px-2 py-1">Page ${currentPage + 1} of ${totalPages}</span>
                                <button class="pagination-next px-2 py-1 rounded ${startIdx + ITEMS_PER_PAGE >= filteredItems.length ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-600'}" 
                                        ${startIdx + ITEMS_PER_PAGE >= filteredItems.length ? 'disabled' : ''}>
                                    Next
                                </button>
                            </div>
                        `;
                    }
                    
                    suggestionBox.classList.remove('hidden');
                } else {
                    suggestionBox.classList.add('hidden');
                }
            }
            
            // Load fournisseurs
            try {
                const response = await fetch(API_ENDPOINTS.listFournisseur);
                allFournisseurs = await response.json();
                
                elements.inputs.fournisseur.addEventListener('input', () => {
                    const value = elements.inputs.fournisseur.value.toLowerCase();
                    const filtered = allFournisseurs.filter(f => f.toLowerCase().includes(value));
                    currentFournisseurPage = 0;
                    showPaginatedSuggestions(filtered, currentFournisseurPage, elements.suggestionBoxes.fournisseur);
                });
                
                elements.suggestionBoxes.fournisseur.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentFournisseurPage > 0) {
                            currentFournisseurPage--;
                            const value = elements.inputs.fournisseur.value.toLowerCase();
                            const filtered = allFournisseurs.filter(f => f.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentFournisseurPage, elements.suggestionBoxes.fournisseur);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.fournisseur.value.toLowerCase();
                        const filtered = allFournisseurs.filter(f => f.toLowerCase().includes(value));
                        if ((currentFournisseurPage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentFournisseurPage++;
                            showPaginatedSuggestions(filtered, currentFournisseurPage, elements.suggestionBoxes.fournisseur);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        elements.inputs.fournisseur.value = e.target.textContent;
                        elements.suggestionBoxes.fournisseur.classList.add('hidden');
                    }
                });
            } catch (error) {
                console.error('Error loading fournisseurs:', error);
            }
            
            // Load products
            try {
                const response = await fetch(API_ENDPOINTS.listProduct);
                const productsData = await response.json();
                // Store both product ID and name, but only display names in UI
                productMap = {};
                productsData.forEach(product => {
                    productMap[product.NAME] = product.M_PRODUCT_ID;
                });
                allProducts = productsData.map(product => product.NAME);
                
                elements.inputs.product.addEventListener('input', () => {
                    const value = elements.inputs.product.value.toLowerCase();
                    const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                    currentProductPage = 0;
                    showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                });
                
                // Handle product selection to load suppliers
                elements.inputs.product.addEventListener('change', async function() {
                    const productName = this.value;
                    if (productName) {
                        try {
                            const productId = productMap[productName];
                            if (!productId) {
                                console.warn(`No product ID found for: ${productName}`);
                                elements.productSupplierContainer.classList.add('hidden');
                                return;
                            }
                            
                            elements.productSupplierSelect.disabled = true;
                            elements.productSupplierSelect.innerHTML = '<option value="">Loading suppliers...</option>';
                            
                            const response = await fetch(`${API_ENDPOINTS.fetchSuppliersByProduct}?product_id=${encodeURIComponent(productId)}`);
                            const suppliers = await response.json();
                            
                            elements.productSupplierSelect.innerHTML = '<option value="">Select a supplier</option>';
                            if (suppliers.length > 0) {
                                // Add "Select All" option
                                const selectAllOption = document.createElement('option');
                                selectAllOption.value = 'SELECT_ALL';
                                selectAllOption.textContent = '📋 Select All Suppliers';
                                selectAllOption.style.backgroundColor = '#fff3e0';
                                selectAllOption.style.fontWeight = 'bold';
                                elements.productSupplierSelect.appendChild(selectAllOption);
                                
                                // Add individual supplier options
                                suppliers.forEach(supplier => {
                                    const option = document.createElement('option');
                                    option.value = supplier;
                                    option.textContent = supplier;
                                    elements.productSupplierSelect.appendChild(option);
                                });
                                elements.productSupplierContainer.classList.remove('hidden');
                            } else {
                                elements.productSupplierContainer.classList.add('hidden');
                            }
                        } catch (error) {
                            console.error('Error fetching product suppliers:', error);
                            elements.productSupplierSelect.innerHTML = '<option value="">Error loading suppliers</option>';
                        } finally {
                            elements.productSupplierSelect.disabled = false;
                        }
                    } else {
                        elements.productSupplierContainer.classList.add('hidden');
                    }
                });
                
                elements.suggestionBoxes.product.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentProductPage > 0) {
                            currentProductPage--;
                            const value = elements.inputs.product.value.toLowerCase();
                            const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.product.value.toLowerCase();
                        const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                        if ((currentProductPage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentProductPage++;
                            showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        const selectedProductName = e.target.textContent;
                        const productId = productMap[selectedProductName];
                        
                        if (!productId) {
                            console.warn(`Invalid product selected: ${selectedProductName} - no ID found`);
                            alert(`Cannot find product ID for "${selectedProductName}". Please select a valid product.`);
                            return;
                        }
                        
                        elements.inputs.product.value = selectedProductName;
                        elements.suggestionBoxes.product.classList.add('hidden');
                        
                        // Show supplier options if product is valid
                        elements.productSupplierContainer.classList.remove('hidden');
                        
                        // Trigger the change event to load suppliers
                        const event = new Event('change');
                        elements.inputs.product.dispatchEvent(event);
                    }
                });
            } catch (error) {
                console.error('Error loading products:', error);
            }
            
            // Handle supplier selection from dropdown
            elements.productSupplierSelect.addEventListener('change', function() {
                if (this.value) {
                    elements.inputs.fournisseur.value = this.value;
                }
            });
            
            // Load zones
            try {
                const response = await fetch(API_ENDPOINTS.listRegion);
                const allZones = await response.json();
                let currentZonePage = 0;
                
                elements.inputs.zone.addEventListener('input', () => {
                    const value = elements.inputs.zone.value.toLowerCase();
                    const filtered = allZones.filter(z => z.toLowerCase().includes(value));
                    currentZonePage = 0;
                    showPaginatedSuggestions(filtered, currentZonePage, elements.suggestionBoxes.zone);
                });
                
                // Handle zone selection to load clients
                elements.inputs.zone.addEventListener('change', async function() {
                    const zone = this.value;
                    if (zone) {
                        try {
                            elements.zoneClientSelect.disabled = true;
                            elements.zoneClientSelect.innerHTML = '<option value="">Loading clients...</option>';
                            
                            const response = await fetch(`${API_ENDPOINTS.fetchZoneClients}?zone=${encodeURIComponent(zone)}`);
                            const clients = await response.json();
                            
                            elements.zoneClientSelect.innerHTML = '<option value="">Select a client</option>';
                            if (clients.length > 0) {
                                // Add "Select All" option
                                const selectAllOption = document.createElement('option');
                                selectAllOption.value = 'SELECT_ALL';
                                selectAllOption.textContent = '📋 Select All Clients';
                                selectAllOption.style.backgroundColor = '#e3f2fd';
                                selectAllOption.style.fontWeight = 'bold';
                                elements.zoneClientSelect.appendChild(selectAllOption);
                                
                                // Add individual client options
                                clients.forEach(client => {
                                    const option = document.createElement('option');
                                    option.value = client.CLIENT_NAME;
                                    option.textContent = client.CLIENT_NAME;
                                    elements.zoneClientSelect.appendChild(option);
                                });
                                elements.zoneClientContainer.classList.remove('hidden');
                            } else {
                                elements.zoneClientContainer.classList.add('hidden');
                            }
                        } catch (error) {
                            console.error('Error fetching zone clients:', error);
                            elements.zoneClientSelect.innerHTML = '<option value="">Error loading clients</option>';
                        } finally {
                            elements.zoneClientSelect.disabled = false;
                        }
                    } else {
                        elements.zoneClientContainer.classList.add('hidden');
                    }
                });
                
                elements.suggestionBoxes.zone.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentZonePage > 0) {
                            currentZonePage--;
                            const value = elements.inputs.zone.value.toLowerCase();
                            const filtered = allZones.filter(z => z.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentZonePage, elements.suggestionBoxes.zone);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.zone.value.toLowerCase();
                        const filtered = allZones.filter(z => z.toLowerCase().includes(value));
                        if ((currentZonePage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentZonePage++;
                            showPaginatedSuggestions(filtered, currentZonePage, elements.suggestionBoxes.zone);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        elements.inputs.zone.value = e.target.textContent;
                        elements.suggestionBoxes.zone.classList.add('hidden');
                        // Trigger the change event to load clients
                        const event = new Event('change');
                        elements.inputs.zone.dispatchEvent(event);
                    }
                });
            } catch (error) {
                console.error('Error loading zones:', error);
            }
            
            // Load clients
            try {
                const response = await fetch(API_ENDPOINTS.listClient);
                const allClients = await response.json();
                let currentClientPage = 0;
                
                elements.inputs.client.addEventListener('input', () => {
                    const value = elements.inputs.client.value.toLowerCase();
                    const filtered = allClients.filter(c => c.toLowerCase().includes(value));
                    currentClientPage = 0;
                    showPaginatedSuggestions(filtered, currentClientPage, elements.suggestionBoxes.client);
                });
                
                elements.suggestionBoxes.client.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentClientPage > 0) {
                            currentClientPage--;
                            const value = elements.inputs.client.value.toLowerCase();
                            const filtered = allClients.filter(c => c.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentClientPage, elements.suggestionBoxes.client);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.client.value.toLowerCase();
                        const filtered = allClients.filter(c => c.toLowerCase().includes(value));
                        if ((currentClientPage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentClientPage++;
                            showPaginatedSuggestions(filtered, currentClientPage, elements.suggestionBoxes.client);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        elements.inputs.client.value = e.target.textContent;
                        elements.suggestionBoxes.client.classList.add('hidden');
                    }
                });
            } catch (error) {
                console.error('Error loading clients:', error);
            }
            
            // Handle client selection from zone dropdown
            elements.zoneClientSelect.addEventListener('change', function() {
                if (this.value) {
                    elements.inputs.client.value = this.value;
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!elements.inputs.fournisseur.contains(e.target) && !elements.suggestionBoxes.fournisseur.contains(e.target)) {
                    elements.suggestionBoxes.fournisseur.classList.add('hidden');
                }
                if (!elements.inputs.product.contains(e.target) && !elements.suggestionBoxes.product.contains(e.target)) {
                    elements.suggestionBoxes.product.classList.add('hidden');
                }
                if (!elements.inputs.zone.contains(e.target) && !elements.suggestionBoxes.zone.contains(e.target)) {
                    elements.suggestionBoxes.zone.classList.add('hidden');
                }
                if (!elements.inputs.client.contains(e.target) && !elements.suggestionBoxes.client.contains(e.target)) {
                    elements.suggestionBoxes.client.classList.add('hidden');
                }
            });
        }

        function resetFilters() {
            elements.yearCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            elements.inputs.fournisseur.value = '';
            elements.inputs.product.value = '';
            elements.inputs.zone.value = '';
            elements.inputs.client.value = '';
            elements.productSupplierContainer.classList.add('hidden');
            elements.zoneClientContainer.classList.add('hidden');

            document.getElementById('dataContainer').innerHTML = '';
            elements.resetBtn.classList.add('hidden');
            
            // Clear pagination
            const existingPagination = document.querySelector('.pagination-container');
            if (existingPagination) {
                existingPagination.remove();
            }
            allProductRows = [];
            totalItems = 0;
            currentPage = 1;
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize autocomplete
            initAutocomplete();
            
            // Set current year as default
            const currentYear = new Date().getFullYear();
            const currentYearCheckbox = document.querySelector(`.year-checkbox[value="${currentYear}"]`);
            if (currentYearCheckbox) {
                currentYearCheckbox.checked = true;
            }
            
            // Add event listeners
            elements.applyBtn.addEventListener('click', loadData);
            elements.resetBtn.addEventListener('click', resetFilters);
            
            // Add custom multi-select behavior for product supplier dropdown
            setupCustomMultiSelect();
            
            // Add keyboard navigation for tables
            setupKeyboardNavigation();
            
            // Add scroll sync for horizontal scrolling
            setupScrollSync();
        });

        // Custom multi-select functionality
        function setupCustomMultiSelect() {
            // Setup for product supplier select
            setupMultiSelectFor(elements.productSupplierSelect);
            
            // Setup for zone client select
            setupMultiSelectFor(elements.zoneClientSelect);
        }
        
        function setupMultiSelectFor(select) {
            // Override the default mousedown behavior
            select.addEventListener('mousedown', function(e) {
                e.preventDefault();
                
                const option = e.target;
                if (option.tagName === 'OPTION') {
                    // Toggle the selected state
                    option.selected = !option.selected;
                    
                    // Trigger change event
                    const changeEvent = new Event('change', { bubbles: true });
                    select.dispatchEvent(changeEvent);
                }
                
                return false;
            });
            
            // Prevent the dropdown from closing after selection
            select.addEventListener('click', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Handle keyboard navigation
            select.addEventListener('keydown', function(e) {
                if (e.code === 'Space' || e.code === 'Enter') {
                    e.preventDefault();
                    const focusedOption = select.options[select.selectedIndex];
                    if (focusedOption) {
                        focusedOption.selected = !focusedOption.selected;
                        const changeEvent = new Event('change', { bubbles: true });
                        select.dispatchEvent(changeEvent);
                    }
                }
            });
        }
        
        // Setup keyboard navigation for tables
        function setupKeyboardNavigation() {
            document.addEventListener('keydown', function(e) {
                const activeTable = document.querySelector('.month-table.active');
                if (activeTable && (e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT')) {
                    const scrollAmount = 50;
                    
                    switch(e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            activeTable.scrollLeft -= scrollAmount;
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            activeTable.scrollLeft += scrollAmount;
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            activeTable.scrollTop -= scrollAmount;
                            break;
                        case 'ArrowDown':
                            e.preventDefault();
                            activeTable.scrollTop += scrollAmount;
                            break;
                        case 'Home':
                            e.preventDefault();
                            activeTable.scrollLeft = 0;
                            break;
                        case 'End':
                            e.preventDefault();
                            activeTable.scrollLeft = activeTable.scrollWidth;
                            break;
                        case 'PageUp':
                            e.preventDefault();
                            activeTable.scrollTop -= activeTable.clientHeight * 0.8;
                            break;
                        case 'PageDown':
                            e.preventDefault();
                            activeTable.scrollTop += activeTable.clientHeight * 0.8;
                            break;
                    }
                }
            });
        }
        
        // Setup scroll synchronization between year summary tables
        function setupScrollSync() {
            // This function can be expanded to sync scroll positions between related tables
            // For now, it adds smooth scrolling behavior
            const containers = document.querySelectorAll('.table-container, .month-table');
            containers.forEach(container => {
                container.style.scrollBehavior = 'smooth';
            });
        }


        document.getElementById('exportPdf').addEventListener('click', async function() {
            const btn = this;
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner');
            const pdfIcon = btn.querySelector('.pdf-icon');
            const errorElement = document.getElementById('pdfError');
            
            try {
                // Get selected parameters
                const years = getSelectedYears();
                const fournisseurs = getSelectedSuppliers();
                const clients = getSelectedClients();
                const productName = elements.inputs.product.value;
                const zone = elements.inputs.zone.value;

                // Validate required parameters - only years are required, suppliers are optional
                if (!years.length) {
                    throw new Error('Please select at least one year');
                }

                // Clear previous errors
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
                
                // Show loading state
                btn.disabled = true;
                pdfIcon.classList.add('hidden');
                spinner.classList.remove('hidden');
                btnText.textContent = 'Generating...';
                
                // Construct the URL with all parameters
                let url = `${API_ENDPOINTS.download_pdf}?years=${years.join(',')}`;
                if (fournisseurs.length > 0) {
                    url += `&fournisseur=${fournisseurs.join(',')}`;
                }
                if (productName) {
                    const productId = productMap[productName];
                    if (productId) {
                        url += `&product_id=${encodeURIComponent(productId)}`;
                    } else {
                        console.warn(`No product ID found for: ${productName}, skipping product filter`);
                    }
                }
                if (zone) {
                    url += `&zone=${encodeURIComponent(zone)}`;
                }
                if (clients.length > 0) {
                    url += `&client=${clients.join(',')}`;
                }

                // Try fetch approach first for better error handling
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(errorText || 'Failed to generate PDF');
                    }
                    
                    const blob = await response.blob();
                    if (blob.size === 0) {
                        throw new Error('Generated PDF is empty');
                    }

                    // Generate a descriptive filename with zone/client context if available
                    const timestamp = new Date().toISOString().split('T')[0];
                    const supplierText = fournisseurs.length > 1 ? `${fournisseurs.length}_suppliers` : fournisseurs[0];
                    const zoneText = zone ? `_${zone}` : '';
                    const clientText = clients.length > 0 ? `_${clients.length}clients` : '';
                    const fileName = `sales_recap_${supplierText}${zoneText}${clientText}_${years.join('-')}_${product || 'all'}_${timestamp}.pdf`;
                    
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(downloadUrl);
                } catch (fetchError) {
                    console.warn('Fetch download failed, trying window.open', fetchError);
                    window.open(url, '_blank');
                }
                
            } catch (error) {
                console.error('PDF download error:', error);
                errorElement.textContent = error.message;
                errorElement.classList.remove('hidden');
            } finally {
                // Reset button state
                btn.disabled = false;
                spinner.classList.add('hidden');
                pdfIcon.classList.remove('hidden');
                btnText.textContent = 'Download PDF';
            }
        });

        // Initialize autocomplete when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initAutocomplete();
            
            // Set up event listeners
            elements.applyBtn.addEventListener('click', loadData);
            
            elements.resetBtn.addEventListener('click', function() {
                // Reset all inputs
                elements.inputs.fournisseur.value = '';
                elements.inputs.product.value = '';
                elements.inputs.zone.value = '';
                elements.inputs.client.value = '';
                
                // Clear all checkboxes
                elements.yearCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Hide dropdowns
                elements.productSupplierContainer.classList.add('hidden');
                elements.zoneClientContainer.classList.add('hidden');
                
                // Clear containers
                document.getElementById('dataContainer').innerHTML = '';
                elements.yearSummaryContainer.innerHTML = '';
                
                // Hide reset button
                this.classList.add('hidden');
                
                // Hide all suggestion boxes
                Object.values(elements.suggestionBoxes).forEach(box => {
                    box.classList.add('hidden');
                });
            });
            
            // Set up dropdown selection handlers
            setupMultiSelectDropdown(elements.productSupplierSelect);
            setupMultiSelectDropdown(elements.zoneClientSelect);
        });
    </script>
</body>
</html>