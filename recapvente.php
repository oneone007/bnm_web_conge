<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for 'Comptable'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
    header("Location: Acess_Denied");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recap Vente V2</title>
    <script src="main.js" defer></script>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="recap_achat.css">
    <script src="theme.js"></script>
    <script src="api_config.js"></script>
    <style>
        .table-container {
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .table-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .table-full-width {
            grid-column: 1 / -1;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .selected-row {
            background-color: #e3f2fd !important;
        }
        
        .row-selectable {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .row-selectable:hover {
            background-color: #f5f5f5 !important;
        }
        
        .dark .row-selectable:hover {
            background-color: #374151 !important;
        }
        
        .row-selected {
            background-color: #dbeafe !important;
            border-left: 4px solid #3b82f6 !important;
        }
        
        .dark .row-selected {
            background-color: #1e3a8a !important;
            border-left: 4px solid #60a5fa !important;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-primary.active {
            background-color: #dc2626;
        }
        .btn-primary.active:hover {
            background-color: #b91c1c;
        }
        .btn-excel {
            background-color: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }
        .btn-excel:hover {
            background-color: #059669;
        }
        
        /* Resizable columns */
        .resizable-table th {
            position: relative;
            border-right: 2px solid #ddd;
        }
        
        .resizable-table th:hover {
            border-right-color: #007cba;
        }
        
        .column-resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
            cursor: col-resize;
            background: transparent;
            z-index: 1;
        }
        
        .column-resizer:hover {
            background: #007cba;
        }
        
        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.125rem;
            padding: 0.25rem;
        }
        
        .pagination button {
            padding: 0.125rem 0.25rem;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            border-radius: 0.125rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.625rem;
            min-width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pagination button:hover:not(:disabled) {
            background: #f3f4f6;
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination button.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .pagination .page-info {
            margin: 0 0.25rem;
            font-size: 0.625rem;
            color: #6b7280;
        }
        
        .dark .pagination button {
            background: #374151;
            color: #d1d5db;
            border-color: #4b5563;
        }
        
        .dark .pagination button:hover:not(:disabled) {
            background: #4b5563;
        }
        
        .dark .pagination .page-info {
            color: #9ca3af;
        }
        
        /* Suggestions dropdown styles */
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .suggestions-dropdown.show {
            display: block;
        }
        
        .suggestion-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
        }
        
        .suggestion-item:hover,
        .suggestion-item.selected {
            background-color: #f3f4f6;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .dark .suggestions-dropdown {
            background: #374151;
            border-color: #4b5563;
            color: #d1d5db;
        }
        
        .dark .suggestion-item {
            border-bottom-color: #4b5563;
        }
        
        .dark .suggestion-item:hover,
        .dark .suggestion-item.selected {
            background-color: #4b5563;
        }
        
        /* Search input with icon styles */
        .search-input-container {
            position: relative;
        }
        
        .search-input-container input {
            padding-left: 2.5rem;
            padding-right: 2.5rem;
        }
        
        /* Force black text in search inputs even in dark mode */
        .dark .search-input-container input {
            color: #000000 !important;
        }
        
        .search-input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.875rem;
            pointer-events: none;
            z-index: 1;
        }
        
        .dark .search-input-icon {
            color: #9ca3af;
        }
        
        .search-clear-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #dc2626;
            font-size: 0.875rem;
            cursor: pointer;
            z-index: 2;
            opacity: 1;
            transition: all 0.2s;
            padding: 0.25rem;
            border-radius: 50%;
            display: none;
        }
        
        .search-clear-icon:hover {
            background-color: #fee2e2;
            color: #b91c1c;
            transform: translateY(-50%) scale(1.1);
        }
        
        .dark .search-clear-icon {
            color: #ef4444;
        }
        
        .dark .search-clear-icon:hover {
            background-color: #7f1d1d;
            color: #fca5a5;
            transform: translateY(-50%) scale(1.1);
        }
        
        .search-input-container input:not(:placeholder-shown) ~ .search-clear-icon {
            display: block;
        }
    </style>
</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <!-- Page Title -->
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">Recap Vente V2</h1>
        </div>

        <!-- Date Selection and Controls -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label for="start-date" class="dark:text-white">Begin Date:</label>
                    <input type="date" id="start-date" class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>
                
                <div class="flex items-center space-x-2">
                    <label for="end-date" class="dark:text-white">End Date:</label>
                    <input type="date" id="end-date" class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>
                
                <button id="fetch-data-btn" class="btn-primary">
                    <i class="fas fa-download mr-2"></i>
                    Fetch Data
                </button>
                
                <button id="refresh-btn" class="btn-primary">
                    <i class="fas fa-refresh mr-2"></i>
                    Refresh
                </button>
            </div>
            
        </div>

        <!-- Total Recap Table -->
        <div class="table-container bg-white dark:bg-gray-800">
            <div class="p-4 border-b dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold dark:text-white">Total Recap</h2>
                    <button id="download-total-excel" class="btn-excel">
                        <i class="fas fa-file-excel"></i>
                        Download Excel
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="border px-4 py-3 dark:border-gray-600">Date</th>
                            <th class="border px-4 py-3 dark:border-gray-600">CHIFFRE</th>
                            <th class="border px-4 py-3 dark:border-gray-600">QTY</th>
                            <th class="border px-4 py-3 dark:border-gray-600">MARGE</th>
                            <th class="border px-4 py-3 dark:border-gray-600">POURCENTAGE</th>
                        </tr>
                    </thead>
                    <tbody id="total-recap-table">
                        <tr>
                            <td colspan="5" class="text-center p-4">
                                <div class="loading-spinner"></div>
                                <span class="ml-2">Loading...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Two Column Layout for Fournisseur and Product -->
        <div class="table-wrapper">
            <!-- RECAP PAR FOURNISSEUR -->
            <div class="table-container bg-white dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold dark:text-white">RECAP PAR FOURNISSEUR</h2>
                        <button id="download-fournisseur-excel" class="btn-excel">
                            <i class="fas fa-file-excel"></i>
                            Download
                        </button>
                    </div>
                    <div class="relative search-input-container">
                        <i class="fas fa-truck search-input-icon"></i>
                        <input type="text" id="search-fournisseur" placeholder="Search Fournisseur..." 
                               class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                        <div id="suggestions-fournisseur" class="suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('fournisseur', 'FOURNISSEUR')">
                                    Fournisseur <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('fournisseur', 'TOTAL')">
                                    Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('fournisseur', 'QTY')">
                                    QTY <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('fournisseur', 'MARGE')">
                                    Marge <i class="fas fa-sort ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="fournisseur-table">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <div class="loading-spinner"></div>
                                    <span class="ml-2">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="fournisseur-pagination" class="p-2 border-t dark:border-gray-700"></div>
            </div>

            <!-- RECAP PAR PRODUIT -->
            <div class="table-container bg-white dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold dark:text-white">RECAP PAR PRODUIT</h2>
                        <button id="download-product-excel" class="btn-excel">
                            <i class="fas fa-file-excel"></i>
                            Download
                        </button>
                    </div>
                    <div class="relative search-input-container">
                        <i class="fas fa-box search-input-icon"></i>
                        <input type="text" id="search-product" placeholder="Search Product..." 
                               class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                        <div id="suggestions-product" class="suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('product', 'PRODUCT')">
                                    Product <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('product', 'TOTAL')">
                                    Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('product', 'QTY')">
                                    QTY <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('product', 'MARGE')">
                                    Marge <i class="fas fa-sort ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="product-table">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <div class="loading-spinner"></div>
                                    <span class="ml-2">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="product-pagination" class="p-2 border-t dark:border-gray-700"></div>
            </div>
        </div>

        <!-- Two Column Layout for Zone and Client -->
        <div class="table-wrapper">
            <!-- RECAP PAR ZONE -->
            <div class="table-container bg-white dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold dark:text-white">RECAP PAR ZONE</h2>
                        <div class="flex gap-2">
=
                            <button id="download-zone-excel" class="btn-excel">
                                <i class="fas fa-file-excel"></i>
                                Download
                            </button>
                        </div>
                    </div>
                    <div class="relative search-input-container">
                        <i class="fas fa-map-marker-alt search-input-icon"></i>
                        <input type="text" id="search-zone" placeholder="Search Zone..." 
                               class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                        <div id="suggestions-zone" class="suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('zone', 'ZONE')">
                                    Zone <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('zone', 'TOTAL')">
                                    Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('zone', 'QTY')">
                                    QTY <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('zone', 'MARGE')">
                                    Marge <i class="fas fa-sort ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="zone-table">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <div class="loading-spinner"></div>
                                    <span class="ml-2">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="zone-pagination" class="p-2 border-t dark:border-gray-700"></div>
            </div>

            <!-- RECAP CLIENT -->
            <div class="table-container bg-white dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold dark:text-white">RECAP CLIENT</h2>
                        <button id="download-client-excel" class="btn-excel">
                            <i class="fas fa-file-excel"></i>
                            Download
                        </button>
                    </div>
                    <div class="relative search-input-container">
                        <i class="fas fa-user search-input-icon"></i>
                        <input type="text" id="search-client" placeholder="Search Client..." 
                               class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                        <div id="suggestions-client" class="suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('client', 'CLIENT')">
                                    Client <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('client', 'TOTAL')">
                                    Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('client', 'QTY')">
                                    QTY <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('client', 'MARGE')">
                                    Marge <i class="fas fa-sort ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="client-table">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <div class="loading-spinner"></div>
                                    <span class="ml-2">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="client-pagination" class="p-2 border-t dark:border-gray-700"></div>
            </div>
        </div>

        <!-- Two Column Layout for Operateur and BCCB Client -->
        <div class="table-wrapper">
            <!-- RECAP PAR OPÉRATEUR -->
            <div class="table-container bg-white dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold dark:text-white">RECAP PAR OPÉRATEUR</h2>
                        <div class="flex gap-2">

                            <button id="download-operateur-excel" class="btn-excel">
                                <i class="fas fa-file-excel"></i>
                                Download
                            </button>
                        </div>
                    </div>
                    <div class="relative search-input-container">
                        <i class="fas fa-user-tie search-input-icon"></i>
                        <input type="text" id="search-operateur" placeholder="Search Operateur..." 
                               class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                        <div id="suggestions-operateur" class="suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('operateur', 'OPERATEUR')">
                                    Opérateur <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('operateur', 'TOTAL')">
                                    Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('operateur', 'QTY')">
                                    QTY <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('operateur', 'MARGE')">
                                    Marge <i class="fas fa-sort ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="operateur-table">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <div class="loading-spinner"></div>
                                    <span class="ml-2">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="operateur-pagination" class="p-2 border-t dark:border-gray-700"></div>
            </div>

            <!-- BCCB CLIENT -->
            <div class="table-container bg-white dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold dark:text-white">BCCB CLIENT</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Click on a row to view products</p>
                        <button id="download-bccb-excel" class="btn-excel">
                            <i class="fas fa-file-excel"></i>
                            Download
                        </button>
                    </div>
                    <div class="flex gap-2 mb-3">
                        <button id="retour-filter-btn" class="btn-primary text-sm" onclick="toggleRetourFilter()">
                            <i class="fas fa-undo mr-1"></i>
                            <span id="retour-filter-text">Show Retour Only</span>
                        </button>
                        <button id="clear-bccb-filters" class="btn-primary text-sm" onclick="clearBccbFilters()">
                            <i class="fas fa-times mr-1"></i>
                            Clear Filters
                        </button>
                    </div>
                    <div class="relative search-input-container">
                        <i class="fas fa-file-invoice search-input-icon"></i>
                        <input type="text" id="search-bccb" placeholder="Search BCCB..." 
                               class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                        <div id="suggestions-bccb" class="suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccb', 'DOCUMENTNO')">
                                    Document No <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccb', 'DATEORDERED')">
                                    Date Order <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccb', 'GRANDTOTAL')">
                                    Grand Total <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccb', 'MARGE')">
                                    Marge (%) <i class="fas fa-sort ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="bccb-table">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <div class="loading-spinner"></div>
                                    <span class="ml-2">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="bccb-pagination" class="p-2 border-t dark:border-gray-700"></div>
            </div>
        </div>

        <!-- BCCB Product Recap - Full Width -->
        <div class="table-container table-full-width bg-white dark:bg-gray-800">
            <div class="p-4 border-b dark:border-gray-700">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold dark:text-white">BCCB Product Recap</h2>
                    <button id="download-bccb-product-excel" class="btn-excel">
                        <i class="fas fa-file-excel"></i>
                        Download Excel
                    </button>
                </div>
                <div class="relative search-input-container">
                    <i class="fas fa-cube search-input-icon"></i>
                    <input type="text" id="search-bccb-product" placeholder="Search BCCB Product..." 
                           class="p-2 border border-gray-300 rounded text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 w-full">
                    <div id="suggestions-bccb-product" class="suggestions-dropdown"></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white resizable-table">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccbProduct', 'PRODUCT')">
                                Product <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccbProduct', 'QTY')">
                                QTY <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccbProduct', 'REMISE')">
                                REMISE <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="border px-4 py-3 dark:border-gray-600 cursor-pointer" onclick="sortTable('bccbProduct', 'MARGE')">
                                MARGE <i class="fas fa-sort ml-1"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="bccb-product-table">
                        <tr>
                            <td colspan="4" class="text-center p-4">
                                <div class="loading-spinner"></div>
                                <span class="ml-2">Loading...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="bccbProduct-pagination" class="p-2 border-t dark:border-gray-700"></div>
        </div>


    </div>

    <!-- JavaScript -->
    <script>
        // Global variables
        let currentData = {};
        let sortOrders = {};
        let selectedRows = {
            fournisseur: null,
            product: null,
            zone: null,
            client: null,
            operateur: null,
            bccb: null,
            bccbProduct: null
        };
        let suggestionsData = {
            fournisseur: [],
            product: [],
            zone: [],
            client: [],
            operateur: [],
            bccb: [],
            bccbProduct: []
        };
        
        // Filter states
        let bccbFilters = {
            retourOnly: false,
            searchText: ''
        };
        
        // Utility function to format margin percentage
        function formatMargin(marge) {
            if (marge === null || marge === undefined) {
                return 'N/A';
            }
            
            // Convert to number if it's a string
            const margeNum = parseFloat(marge);
            
            // If the value is between 0 and 1, it's likely a decimal (e.g., 0.15 for 15%)
            // If the value is greater than 1, it's likely already a percentage (e.g., 15 for 15%)
            if (margeNum >= 0 && margeNum <= 1) {
                return (margeNum * 100).toFixed(2) + '%';
            } else {
                return margeNum.toFixed(2) + '%';
            }
        }
        let pagination = {
            fournisseur: { currentPage: 1, itemsPerPage: 20, filteredData: [] },
            product: { currentPage: 1, itemsPerPage: 20, filteredData: [] },
            zone: { currentPage: 1, itemsPerPage: 20, filteredData: [] },
            client: { currentPage: 1, itemsPerPage: 20, filteredData: [] },
            operateur: { currentPage: 1, itemsPerPage: 20, filteredData: [] },
            bccb: { currentPage: 1, itemsPerPage: 20, filteredData: [] },
            bccbProduct: { currentPage: 1, itemsPerPage: 20, filteredData: [] }
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
            setupEventListeners();
        });

        function initializePage() {
            // Set today's date as default
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start-date').value = today;
            document.getElementById('end-date').value = today;
            
            // Add resizable-table class to all tables
            setTimeout(() => {
                document.querySelectorAll('table').forEach(table => {
                    table.classList.add('resizable-table');
                });
                makeTablesResizable();
            }, 100);
        }

        function setupEventListeners() {
            // Fetch data button
            document.getElementById('fetch-data-btn').addEventListener('click', fetchAllData);
            
            // Refresh button
            document.getElementById('refresh-btn').addEventListener('click', refreshData);
            
            // Download buttons
            document.getElementById('download-total-excel').addEventListener('click', () => downloadExcel('total'));
            document.getElementById('download-fournisseur-excel').addEventListener('click', () => downloadExcel('fournisseur'));
            document.getElementById('download-product-excel').addEventListener('click', () => downloadExcel('product'));
            document.getElementById('download-zone-excel').addEventListener('click', () => downloadExcel('zone'));
            document.getElementById('download-client-excel').addEventListener('click', () => downloadExcel('client'));
            document.getElementById('download-operateur-excel').addEventListener('click', () => downloadExcel('operateur'));
            document.getElementById('download-bccb-excel').addEventListener('click', () => downloadExcel('bccb'));
            document.getElementById('download-bccb-product-excel').addEventListener('click', () => downloadExcel('bccb-product'));
            
   
            // Setup autocomplete for search inputs
            setupAutocomplete('search-fournisseur', 'fournisseur');
            setupAutocomplete('search-product', 'product');
            setupAutocomplete('search-zone', 'zone');
            setupAutocomplete('search-client', 'client');
            setupAutocomplete('search-operateur', 'operateur');
            setupAutocomplete('search-bccb', 'bccb');
            setupAutocomplete('search-bccb-product', 'bccbProduct');
            
            // Initialize resizable tables
            setTimeout(makeTablesResizable, 100);
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.relative')) {
                    hideAllSuggestions();
                }
            });
        }

        function toggleRetourFilter() {
            const btn = document.getElementById('retour-filter-btn');
            const text = document.getElementById('retour-filter-text');
            
            bccbFilters.retourOnly = !bccbFilters.retourOnly;
            
            if (bccbFilters.retourOnly) {
                btn.classList.add('active');
                text.textContent = 'Show All Documents';
            } else {
                btn.classList.remove('active');
                text.textContent = 'Show Retour Only';
            }
            
            // Reset pagination and apply filters
            pagination.bccb.currentPage = 1;
            applyBccbFilters();
        }

        function clearBccbFilters() {
            // Reset all BCCB filters
            bccbFilters.retourOnly = false;
            bccbFilters.searchText = '';
            
            // Update UI
            const retourBtn = document.getElementById('retour-filter-btn');
            const retourText = document.getElementById('retour-filter-text');
            const searchInput = document.getElementById('search-bccb');
            
            retourBtn.classList.remove('active');
            retourText.textContent = 'Show Retour Only';
            searchInput.value = '';
            
            // Hide suggestions
            const suggestionsDiv = document.getElementById('suggestions-bccb');
            if (suggestionsDiv) {
                suggestionsDiv.classList.remove('show');
            }
            
            // Reset pagination and apply filters
            pagination.bccb.currentPage = 1;
            applyBccbFilters();
        }

        function applyBccbFilters() {
            if (!currentData.bccb || currentData.bccb.length === 0) {
                return;
            }

            let filteredData = [...currentData.bccb];
            
            // Remove Total row from filtering
            const totalRow = filteredData.find(row => row.DOCUMENTNO === 'Total');
            filteredData = filteredData.filter(row => row.DOCUMENTNO !== 'Total');
            
            // Apply retour filter (documents starting with "ORM")
            if (bccbFilters.retourOnly) {
                filteredData = filteredData.filter(row => 
                    row.DOCUMENTNO && row.DOCUMENTNO.toString().startsWith('ORM')
                );
            }
            
            // Apply search filter
            if (bccbFilters.searchText && bccbFilters.searchText.trim() !== '') {
                const searchTerm = bccbFilters.searchText.toLowerCase();
                filteredData = filteredData.filter(row =>
                    (row.DOCUMENTNO && row.DOCUMENTNO.toString().toLowerCase().includes(searchTerm)) ||
                    (row.DATEORDERED && row.DATEORDERED.toString().toLowerCase().includes(searchTerm)) ||
                    (row.GRANDTOTAL && row.GRANDTOTAL.toString().toLowerCase().includes(searchTerm)) ||
                    (row.MARGE && row.MARGE.toString().toLowerCase().includes(searchTerm))
                );
            }
            
            // Store filtered data and update pagination
            pagination.bccb.filteredData = filteredData;
            updateBccbTableDisplay(filteredData, totalRow);
            updatePagination('bccb', filteredData, filteredData.length);
        }

        function updateBccbTableDisplay(filteredData, totalRow) {
            const tableBody = document.getElementById('bccb-table');
            const paginatedData = getPaginatedData('bccb');
            
            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow && !bccbFilters.retourOnly) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.DOCUMENTNO || 'Total'}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.DATEORDERED || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.GRANDTOTAL)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.DOCUMENTNO || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.DATEORDERED)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.GRANDTOTAL)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('bccb', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            setTimeout(makeTablesResizable, 50);
        }

        async function fetchAllData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            
            showLoadingState();
            
            try {
                await Promise.allSettled([
                    fetchTotalRecap(),
                    fetchFournisseurData(),
                    fetchProductData(),
                    fetchZoneData(),
                    fetchClientData(),
                    fetchOperateurData(),
                    fetchBccbData(),
                    fetchBccbProductData()
                ]);
                
                // Extract suggestions after fetching data
                extractSuggestionsFromData();
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        function showLoadingState() {
            const tableIds = ['total-recap-table', 'fournisseur-table', 'product-table', 'zone-table', 'client-table', 'operateur-table', 'bccb-table', 'bccb-product-table'];
            
            tableIds.forEach(id => {
                const table = document.getElementById(id);
                if (table) {
                    const colCount = table.closest('table').querySelectorAll('thead th').length;
                    table.innerHTML = `
                        <tr>
                            <td colspan="${colCount}" class="text-center p-4">
                                <div class="loading-spinner"></div>
                                <span class="ml-2">Loading...</span>
                            </td>
                        </tr>
                    `;
                }
            });
        }

        async function fetchTotalRecap() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const response = await fetch(API_CONFIG.getApiUrl(`/fetchTotalrecapData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`));
                const data = await response.json();
                updateTotalRecapTable(data);
            } catch (error) {
                console.error('Error fetching total recap:', error);
                document.getElementById('total-recap-table').innerHTML = '<tr><td colspan="5" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchFournisseurData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const response = await fetch(API_CONFIG.getApiUrl(`/fetchFournisseurData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`));
                const data = await response.json();
                updateFournisseurTable(data);
                currentData.fournisseur = data;
            } catch (error) {
                console.error('Error fetching fournisseur data:', error);
                document.getElementById('fournisseur-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchProductData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const response = await fetch(API_CONFIG.getApiUrl(`/fetchProductData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`));
                const data = await response.json();
                updateProductTable(data);
                currentData.product = data;
            } catch (error) {
                console.error('Error fetching product data:', error);
                document.getElementById('product-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchZoneData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const response = await fetch(API_CONFIG.getApiUrl(`/fetchZoneRecap?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`));
                const data = await response.json();
                updateZoneTable(data);
                currentData.zone = data;
            } catch (error) {
                console.error('Error fetching zone data:', error);
                document.getElementById('zone-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchClientData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const url = API_CONFIG.getApiUrl(`/fetchClientRecap?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`);
                console.log('Fetching client data from:', url);
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Client data received:', data);
                updateClientTable(data);
                currentData.client = data;
            } catch (error) {
                console.error('Error fetching client data:', error);
                document.getElementById('client-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchOperateurData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const url = API_CONFIG.getApiUrl(`/fetchOperatorRecap?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`);
                console.log('Fetching operateur data from:', url);
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Operateur data received:', data);
                updateOperateurTable(data);
                currentData.operateur = data;
            } catch (error) {
                console.error('Error fetching operateur data:', error);
                document.getElementById('operateur-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchBccbData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                const url = API_CONFIG.getApiUrl(`/fetchBCCBRecap?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`);
                console.log('Fetching BCCB data from:', url);
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('BCCB data received:', data);
                updateBccbTable(data);
                currentData.bccb = data;
            } catch (error) {
                console.error('Error fetching BCCB data:', error);
                document.getElementById('bccb-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        async function fetchBccbProductData() {
            // BCCB Product requires a specific BCCB document number to fetch data
            // On initial load, show a message instead of making an API call
            document.getElementById('bccb-product-table').innerHTML = '<tr><td colspan="4" class="text-center p-4">Please select a BCCB from the search filter to view products</td></tr>';
            currentData.bccbProduct = [];
        }

        async function fetchBccbProductWithSpecificBccb(bccbDocumentNo) {
            if (!bccbDocumentNo) return;
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchBCCBProduct"));
                url.searchParams.append("bccb", bccbDocumentNo);
                url.searchParams.append("ad_org_id", "1000000");
                
                console.log('Fetching BCCB Product data for:', bccbDocumentNo);
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('BCCB Product data received:', data);
                updateBccbProductTable(data);
                currentData.bccbProduct = data;
                
                // Extract suggestions for BCCB products
                if (data && data.length > 0) {
                    suggestionsData.bccbProduct = [...new Set(
                        data
                            .filter(item => (item.PRODUCT || item.PRODUIT) && item.PRODUCT !== 'Total' && item.PRODUIT !== 'Total')
                            .map(item => item.PRODUCT || item.PRODUIT)
                    )].sort();
                }
            } catch (error) {
                console.error('Error fetching BCCB product data:', error);
                document.getElementById('bccb-product-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }

        function updateTotalRecapTable(data) {
            const tableBody = document.getElementById('total-recap-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">No data available</td></tr>';
                return;
            }
            
            const row = data[0];
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            tableBody.innerHTML = `
                <tr>
                    <td class="border px-4 py-2 dark:border-gray-600 font-bold">From ${startDate} to ${endDate}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.MARGE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatPercentage(row.POURCENTAGE)}</td>
                </tr>
            `;
        }

        function updateFournisseurTable(data) {
            const tableBody = document.getElementById('fournisseur-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('fournisseur', [], 0);
                return;
            }

            // Separate Total row from regular data for pagination
            const totalRow = data.find(row => row.FOURNISSEUR === 'Total');
            const regularData = data.filter(row => row.FOURNISSEUR !== 'Total');

            // Store only regular data for pagination
            pagination.fournisseur.filteredData = regularData;
            const paginatedData = getPaginatedData('fournisseur');
            
            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE || totalRow.TOTAL)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE || row.TOTAL)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('fournisseur', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            updatePagination('fournisseur', regularData, regularData.length);
            setTimeout(makeTablesResizable, 50);
        }

        function updateProductTable(data) {
            const tableBody = document.getElementById('product-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('product', [], 0);
                return;
            }

            // Separate Total row from regular data for pagination
            const totalRow = data.find(row => row.PRODUIT === 'Total' || row.PRODUCT === 'Total');
            const regularData = data.filter(row => row.PRODUIT !== 'Total' && row.PRODUCT !== 'Total');

            // Store only regular data for pagination
            pagination.product.filteredData = regularData;
            const paginatedData = getPaginatedData('product');

            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT || totalRow.PRODUCT || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.TOTAL || totalRow.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || row.PRODUCT || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TOTAL || row.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('product', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            updatePagination('product', regularData, regularData.length);
            setTimeout(makeTablesResizable, 50);
        }

        function updateZoneTable(data) {
            const tableBody = document.getElementById('zone-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('zone', [], 0);
                return;
            }

            // Separate Total row from regular data for pagination
            const totalRow = data.find(row => row.ZONE === 'Total');
            const regularData = data.filter(row => row.ZONE !== 'Total');

            // Store only regular data for pagination
            pagination.zone.filteredData = regularData;
            const paginatedData = getPaginatedData('zone');

            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.ZONE || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.TOTAL || totalRow.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.ZONE || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TOTAL || row.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('zone', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            updatePagination('zone', regularData, regularData.length);
            setTimeout(makeTablesResizable, 50);
        }

        function updateClientTable(data) {
            const tableBody = document.getElementById('client-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('client', [], 0);
                return;
            }

            // Separate Total row from regular data for pagination
            const totalRow = data.find(row => row.CLIENT === 'Total');
            const regularData = data.filter(row => row.CLIENT !== 'Total');

            // Store only regular data for pagination
            pagination.client.filteredData = regularData;
            const paginatedData = getPaginatedData('client');

            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.CLIENT || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.TOTAL || totalRow.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.CLIENT || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TOTAL || row.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('client', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            updatePagination('client', regularData, regularData.length);
            setTimeout(makeTablesResizable, 50);
        }

        function updateOperateurTable(data) {
            const tableBody = document.getElementById('operateur-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('operateur', [], 0);
                return;
            }

            // Separate Total row from regular data for pagination
            const totalRow = data.find(row => row.OPERATEUR === 'Total');
            const regularData = data.filter(row => row.OPERATEUR !== 'Total');

            // Store only regular data for pagination
            pagination.operateur.filteredData = regularData;
            const paginatedData = getPaginatedData('operateur');

            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.OPERATEUR || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.TOTAL || totalRow.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.OPERATEUR || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TOTAL || row.CHIFFRE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('operateur', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            updatePagination('operateur', regularData, regularData.length);
            setTimeout(makeTablesResizable, 50);
        }

        function updateBccbTable(data) {
            const tableBody = document.getElementById('bccb-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('bccb', [], 0);
                return;
            }

            // Store the original data
            currentData.bccb = data;
            
            // Apply current filters
            applyBccbFilters();
        }

        function updateBccbProductTable(data) {
            const tableBody = document.getElementById('bccb-product-table');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No data available</td></tr>';
                updatePagination('bccbProduct', [], 0);
                return;
            }

            // Separate Total row from regular data for pagination
            const totalRow = data.find(row => 
                (row.PRODUCT === 'Total' || row.PRODUIT === 'Total')
            );
            const regularData = data.filter(row => 
                (row.PRODUCT !== 'Total' && row.PRODUIT !== 'Total')
            );

            // Store only regular data for pagination
            pagination.bccbProduct.filteredData = regularData;
            const paginatedData = getPaginatedData('bccbProduct');

            tableBody.innerHTML = '';
            
            // Always add Total row first if it exists (not paginated)
            if (totalRow) {
                const tr = document.createElement('tr');
                tr.className = 'bg-blue-100 dark:bg-blue-900 font-bold';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUCT || totalRow.PRODUIT || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.REMISE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(totalRow.MARGE)}</td>
                `;
                tableBody.appendChild(tr);
            }
            
            // Add paginated rows
            paginatedData.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 row-selectable';
                tr.innerHTML = `
                    <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || row.PRODUIT || ''}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.REMISE)}</td>
                    <td class="border px-4 py-2 dark:border-gray-600">${formatMargin(row.MARGE)}</td>
                `;
                
                // Add click event for row selection
                tr.addEventListener('click', function() {
                    selectTableRow('bccbProduct', row, tr);
                });
                
                tableBody.appendChild(tr);
            });
            
            updatePagination('bccbProduct', regularData, regularData.length);
            setTimeout(makeTablesResizable, 50);
        }

        function formatNumber(value) {
            if (value === null || value === undefined || isNaN(value)) return "";
            return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('en-GB', options);
        }

        function formatPercentage(value) {
            if (value === null || value === undefined || isNaN(value)) return "";
            return (parseFloat(value) * 100).toFixed(2) + "%";
        }

        function sortTable(tableType, column) {
            if (!currentData[tableType] || !currentData[tableType].length) return;
            
            // Toggle sort order
            if (!sortOrders[tableType]) sortOrders[tableType] = {};
            sortOrders[tableType][column] = sortOrders[tableType][column] === 'asc' ? 'desc' : 'asc';
            
            // Separate Total row from regular data
            const totalRow = currentData[tableType].find(row => {
                switch(tableType) {
                    case 'fournisseur': return row.FOURNISSEUR === 'Total';
                    case 'product': return row.PRODUIT === 'Total' || row.PRODUCT === 'Total';
                    case 'zone': return row.ZONE === 'Total';
                    case 'client': return row.CLIENT === 'Total';
                    case 'operateur': return row.OPERATEUR === 'Total';
                    case 'bccb': return row.DOCUMENTNO === 'Total';
                    case 'bccbProduct': return row.PRODUCT === 'Total' || row.PRODUIT === 'Total';
                    default: return false;
                }
            });
            
            const regularData = currentData[tableType].filter(row => {
                switch(tableType) {
                    case 'fournisseur': return row.FOURNISSEUR !== 'Total';
                    case 'product': return row.PRODUIT !== 'Total' && row.PRODUCT !== 'Total';
                    case 'zone': return row.ZONE !== 'Total';
                    case 'client': return row.CLIENT !== 'Total';
                    case 'operateur': return row.OPERATEUR !== 'Total';
                    case 'bccb': return row.DOCUMENTNO !== 'Total';
                    case 'bccbProduct': return row.PRODUCT !== 'Total' && row.PRODUIT !== 'Total';
                    default: return true;
                }
            });
            
            const sortedRegularData = [...regularData].sort((a, b) => {
                const aVal = a[column] || '';
                const bVal = b[column] || '';
                
                // Handle numeric values
                if (!isNaN(aVal) && !isNaN(bVal)) {
                    return sortOrders[tableType][column] === 'asc' ? 
                        parseFloat(aVal) - parseFloat(bVal) : 
                        parseFloat(bVal) - parseFloat(aVal);
                }
                
                // Handle string values
                return sortOrders[tableType][column] === 'asc' ? 
                    aVal.toString().localeCompare(bVal.toString()) : 
                    bVal.toString().localeCompare(aVal.toString());
            });
            
            // Combine Total row with sorted data
            const sortedData = totalRow ? [totalRow, ...sortedRegularData] : sortedRegularData;
            
            // Update the table with sorted data
            switch(tableType) {
                case 'fournisseur':
                    updateFournisseurTable(sortedData);
                    break;
                case 'product':
                    updateProductTable(sortedData);
                    break;
                case 'zone':
                    updateZoneTable(sortedData);
                    break;
                case 'client':
                    updateClientTable(sortedData);
                    break;
                case 'operateur':
                    updateOperateurTable(sortedData);
                    break;
                case 'bccb':
                    updateBccbTable(sortedData);
                    break;
                case 'bccbProduct':
                    updateBccbProductTable(sortedData);
                    break;
            }
        }

        function filterTable(tableType, searchTerm) {
            if (!currentData[tableType] || !currentData[tableType].length) return;
            
            searchTerm = searchTerm.toLowerCase();
            
            // Separate Total row from regular data
            const totalRow = currentData[tableType].find(row => {
                switch(tableType) {
                    case 'fournisseur': return row.FOURNISSEUR === 'Total';
                    case 'product': return row.PRODUIT === 'Total' || row.PRODUCT === 'Total';
                    case 'zone': return row.ZONE === 'Total';
                    case 'client': return row.CLIENT === 'Total';
                    case 'operateur': return row.OPERATEUR === 'Total';
                    case 'bccb': return row.DOCUMENTNO === 'Total';
                    case 'bccbProduct': return row.PRODUCT === 'Total' || row.PRODUIT === 'Total';
                    default: return false;
                }
            });
            
            const regularData = currentData[tableType].filter(row => {
                switch(tableType) {
                    case 'fournisseur': return row.FOURNISSEUR !== 'Total';
                    case 'product': return row.PRODUIT !== 'Total' && row.PRODUCT !== 'Total';
                    case 'zone': return row.ZONE !== 'Total';
                    case 'client': return row.CLIENT !== 'Total';
                    case 'operateur': return row.OPERATEUR !== 'Total';
                    case 'bccb': return row.DOCUMENTNO !== 'Total';
                    case 'bccbProduct': return row.PRODUCT !== 'Total' && row.PRODUIT !== 'Total';
                    default: return true;
                }
            });
            
            // Filter regular data
            const filteredRegularData = regularData.filter(row => {
                return Object.values(row).some(value => 
                    value && value.toString().toLowerCase().includes(searchTerm)
                );
            });
            
            // Combine Total row with filtered data for display
            const filteredData = totalRow ? [totalRow, ...filteredRegularData] : filteredRegularData;
            
            // Reset to first page when filtering
            pagination[tableType].currentPage = 1;
            
            // Update the table with filtered data
            switch(tableType) {
                case 'fournisseur':
                    updateFournisseurTable(filteredData);
                    break;
                case 'product':
                    updateProductTable(filteredData);
                    break;
                case 'zone':
                    updateZoneTable(filteredData);
                    break;
                case 'client':
                    updateClientTable(filteredData);
                    break;
                case 'operateur':
                    updateOperateurTable(filteredData);
                    break;
                case 'bccb':
                    updateBccbTable(filteredData);
                    break;
                case 'bccbProduct':
                    updateBccbProductTable(filteredData);
                    break;
            }
        }

        function getPaginatedData(tableType) {
            const { currentPage, itemsPerPage, filteredData } = pagination[tableType];
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            return filteredData.slice(startIndex, endIndex);
        }

        function updatePagination(tableType, data, totalItems) {
            const paginationContainer = document.getElementById(`${tableType}-pagination`);
            
            if (!paginationContainer) {

                console.warn(`Pagination container not found for table type: ${tableType}`);
                return;
            }
            
            const { currentPage, itemsPerPage } = pagination[tableType];
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            if (totalPages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }
            
            let paginationHTML = '<div class="pagination">';
            
            // First button
            paginationHTML += `<button onclick="changePage('${tableType}', 1)" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-angle-double-left mr-1"></i>First
            </button>`;
            
            // Previous button
            paginationHTML += `<button onclick="changePage('${tableType}', ${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-angle-left mr-1"></i>Previous
            </button>`;
            
            // Page info
            paginationHTML += `<div class="page-info">Page ${currentPage} of ${totalPages}</div>`;
            
            // Next button
            paginationHTML += `<button onclick="changePage('${tableType}', ${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                Next<i class="fas fa-angle-right ml-1"></i>
            </button>`;
            
            // Last button
            paginationHTML += `<button onclick="changePage('${tableType}', ${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}>
                Last<i class="fas fa-angle-double-right ml-1"></i>
            </button>`;
            
            // Items info
            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, totalItems);
            paginationHTML += `<div class="page-info ml-4">Showing ${start}-${end} of ${totalItems} items</div>`;
            
            paginationHTML += '</div>';
            paginationContainer.innerHTML = paginationHTML;
        }

        function changePage(tableType, newPage) {
            const totalPages = Math.ceil(pagination[tableType].filteredData.length / pagination[tableType].itemsPerPage);
            
            if (newPage < 1 || newPage > totalPages) return;
            
            pagination[tableType].currentPage = newPage;
            
            // Update the table with new page data using the original data (including Total row)
            switch(tableType) {
                case 'fournisseur':
                    updateFournisseurTable(currentData[tableType]);
                    break;
                case 'product':
                    updateProductTable(currentData[tableType]);
                    break;
                case 'zone':
                    updateZoneTable(currentData[tableType]);
                    break;
                case 'client':
                    updateClientTable(currentData[tableType]);
                    break;
                case 'operateur':
                    updateOperateurTable(currentData[tableType]);
                    break;
                case 'bccb':
                    updateBccbTable(currentData[tableType]);
                    break;
                case 'bccbProduct':
                    updateBccbProductTable(currentData[tableType]);
                    break;
            }
        }

        function downloadExcel(type) {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select dates first.');
                return;
            }
            
            // Get filter parameters for advanced download functionality
            const filterParams = getFilterParams();
            
            // For BCCB Product download, we need a BCCB document selected
            if (type === 'bccb-product' && (!filterParams.bccb || filterParams.bccb.trim() === '')) {
                alert('Please select a BCCB document first to download its products.');
                return;
            }
            
            // Construct URL with filters
            const url = new URL(API_CONFIG.getApiUrl(`/download-${type}-excel`));
            url.searchParams.append("start_date", startDate);
            url.searchParams.append("end_date", endDate);
            url.searchParams.append("ad_org_id", "1000000");
            
            // Add filter parameters if they exist
            if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
            if (filterParams.product) url.searchParams.append("product", filterParams.product);
            if (filterParams.client) url.searchParams.append("client", filterParams.client);
            if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
            if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
            if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
            
            window.location.href = url;
        }


        function makeTablesResizable() {
            const tables = document.querySelectorAll('.resizable-table');
            
            tables.forEach(table => {
                const headers = table.querySelectorAll('th');
                
                headers.forEach((header, index) => {
                    if (index < headers.length - 1) { // Don't add resizer to last column
                        const resizer = document.createElement('div');
                        resizer.className = 'column-resizer';
                        header.appendChild(resizer);
                        
                        let startX, startWidth;
                        
                        resizer.addEventListener('mousedown', (e) => {
                            startX = e.clientX;
                            startWidth = parseInt(document.defaultView.getComputedStyle(header).width, 10);
                            document.addEventListener('mousemove', doResize);
                            document.addEventListener('mouseup', stopResize);
                            e.preventDefault();
                        });
                        
                        function doResize(e) {
                            const newWidth = startWidth + e.clientX - startX;
                            if (newWidth > 50) { // Minimum width
                                header.style.width = newWidth + 'px';
                            }
                        }
                        
                        function stopResize() {
                            document.removeEventListener('mousemove', doResize);
                            document.removeEventListener('mouseup', stopResize);
                        }
                    }
                });
            });
        }

        function refreshData() {
            // Reset pagination but keep filtered data intact
            Object.keys(pagination).forEach(tableType => {
                pagination[tableType].currentPage = 1;
            });
            
            // Add resizable-table class to all tables if not already present
            document.querySelectorAll('table:not(.resizable-table)').forEach(table => {
                table.classList.add('resizable-table');
            });
            
            // Refetch data if dates are selected
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (startDate && endDate) {
                // Always use filtered fetch to preserve current filters
                fetchAllDataWithFilters();
            }
        }

        // Autocomplete functionality
        function setupAutocomplete(inputId, dataType) {
            const input = document.getElementById(inputId);
            const suggestionsDiv = document.getElementById(`suggestions-${dataType}`);
            let selectedIndex = -1;
            
            // Add click event to clear input and trigger search
            input.addEventListener('click', function(e) {
                // Only clear if there's content in the input
                if (e.target.value.trim() !== '') {
                    e.target.value = '';
                    suggestionsDiv.classList.remove('show');
                    selectedIndex = -1;
                    
                    // Reset pagination to first page
                    if (pagination[dataType]) {
                        pagination[dataType].currentPage = 1;
                    }
                    
                    // Apply filter (which will show all data since search is empty)
                    if (dataType === 'bccb') {
                        bccbFilters.searchText = '';
                        applyBccbFilters();
                    } else {
                        autoApplyFilters();
                    }
                    
                    // Provide visual feedback
                    e.target.style.backgroundColor = '#fff3cd';
                    setTimeout(() => {
                        e.target.style.backgroundColor = '';
                    }, 1000);
                }
            });
            
            input.addEventListener('input', function(e) {
                const value = e.target.value.toLowerCase().trim();
                selectedIndex = -1;
                
                if (value.length < 1) {
                    suggestionsDiv.classList.remove('show');
                    // Auto-apply filters when input is cleared
                    autoApplyFilters();
                    return;
                }
                
                const suggestions = getSuggestions(dataType, value);
                displaySuggestions(suggestionsDiv, suggestions, input);
            });
            
            input.addEventListener('click', function(e) {
                const value = e.target.value.trim();
                // If input has content, clear it and trigger search
                if (value.length > 0) {
                    e.target.value = '';
                    suggestionsDiv.classList.remove('show');
                    
                    // Visual feedback
                    e.target.style.backgroundColor = '#fef3cd';
                    setTimeout(() => {
                        e.target.style.backgroundColor = '';
                    }, 1000);
                    
                    // Reset pagination to first page
                    if (pagination[dataType]) {
                        pagination[dataType].currentPage = 1;
                    }
                    
                    // Trigger search/filter
                    autoApplyFilters();
                }
            });
            
            input.addEventListener('focus', function(e) {
                const value = e.target.value.trim();
                // Only show suggestions if input has content and we're not clearing it
                if (value.length >= 1) {
                    const suggestions = getSuggestions(dataType, value.toLowerCase());
                    displaySuggestions(suggestionsDiv, suggestions, input);
                }
            });
            
            input.addEventListener('keydown', function(e) {
                const suggestionItems = suggestionsDiv.querySelectorAll('.suggestion-item');
                
                if (suggestionItems.length === 0) return;
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, suggestionItems.length - 1);
                        updateSelectedSuggestion(suggestionItems, selectedIndex);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, -1);
                        updateSelectedSuggestion(suggestionItems, selectedIndex);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (selectedIndex >= 0 && suggestionItems[selectedIndex]) {
                            suggestionItems[selectedIndex].click();
                        } else {
                            // If no suggestion is selected, just hide suggestions and apply filter
                            suggestionsDiv.classList.remove('show');
                            autoApplyFilters();
                        }
                        break;
                    case 'Escape':
                        suggestionsDiv.classList.remove('show');
                        selectedIndex = -1;
                        break;
                }
            });
            
            // Hide suggestions when user leaves the input
            input.addEventListener('blur', function(e) {
                // Small delay to allow for suggestion clicks
                setTimeout(() => {
                    suggestionsDiv.classList.remove('show');
                }, 150);
            });
        }
        
        function updateSelectedSuggestion(items, selectedIndex) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            });
        }
        
        function getSuggestions(dataType, searchTerm) {
            const data = suggestionsData[dataType] || [];
            return data.filter(item => 
                item.toLowerCase().includes(searchTerm)
            ).slice(0, 10); // Limit to 10 suggestions
        }
        
        function displaySuggestions(suggestionsDiv, suggestions, input) {
            if (suggestions.length === 0) {
                suggestionsDiv.classList.remove('show');
                return;
            }
            
            suggestionsDiv.innerHTML = suggestions.map((suggestion, index) => {
                const escapedSuggestion = suggestion.replace(/'/g, '&#39;').replace(/"/g, '&quot;');
                return `<div class="suggestion-item" onclick="selectSuggestion('${input.id}', ${index})" data-value="${escapedSuggestion}">${suggestion}</div>`;
            }).join('');
            
            suggestionsDiv.classList.add('show');
        }
        
        function selectSuggestion(inputId, suggestionIndex) {
            const suggestionsDiv = document.getElementById(inputId).parentElement.querySelector('.suggestions-dropdown');
            const suggestionItems = suggestionsDiv.querySelectorAll('.suggestion-item');
            
            if (typeof suggestionIndex === 'string') {
                // Backward compatibility: if a string is passed, use it directly (but decode HTML entities)
                const decodedValue = suggestionIndex.replace(/&#39;/g, "'").replace(/&quot;/g, '"');
                document.getElementById(inputId).value = decodedValue;
            } else if (suggestionItems[suggestionIndex]) {
                // Use the original text content, not the escaped data-value
                document.getElementById(inputId).value = suggestionItems[suggestionIndex].textContent;
            }
            
            hideAllSuggestions();
            
            // Optional: Provide visual feedback that a suggestion was selected
            const input = document.getElementById(inputId);
            input.style.backgroundColor = '#e6f3ff';
            setTimeout(() => {
                input.style.backgroundColor = '';
            }, 1000);
            
            // Auto-apply filters when a suggestion is selected
            autoApplyFilters();
        }

        function selectTableRow(tableType, rowData, rowElement) {
            // Remove previous selection in this table
            const tableBody = rowElement.closest('tbody');
            tableBody.querySelectorAll('.row-selected').forEach(row => {
                row.classList.remove('row-selected');
            });
            
            // Add selection to current row
            rowElement.classList.add('row-selected');
            
            // Store selected row data
            selectedRows[tableType] = rowData;
            
            // Update the corresponding search input
            let searchValue = '';
            let inputId = '';
            
            switch(tableType) {
                case 'fournisseur':
                    searchValue = rowData.FOURNISSEUR;
                    inputId = 'search-fournisseur';
                    break;
                case 'product':
                    searchValue = rowData.PRODUIT || rowData.PRODUCT;
                    inputId = 'search-product';
                    break;
                case 'zone':
                    searchValue = rowData.ZONE;
                    inputId = 'search-zone';
                    break;
                case 'client':
                    searchValue = rowData.CLIENT;
                    inputId = 'search-client';
                    break;
                case 'operateur':
                    searchValue = rowData.OPERATEUR;
                    inputId = 'search-operateur';
                    break;
                case 'bccb':
                    searchValue = rowData.DOCUMENTNO;
                    inputId = 'search-bccb';
                    // For BCCB, also fetch products
                    if (searchValue && searchValue !== 'Total') {
                        fetchBccbProductWithSpecificBccb(searchValue);
                    }
                    break;
                case 'bccbProduct':
                    searchValue = rowData.PRODUCT || rowData.PRODUIT;
                    inputId = 'search-bccb-product';
                    break;
            }
            
            // Update search input if we have a valid value
            if (searchValue && searchValue !== 'Total' && inputId) {
                document.getElementById(inputId).value = searchValue;
            }
            
            // Auto-apply filters
            autoApplyFilters();
        }

        function applySearchFilter(tableType, searchValue) {
            if (!currentData[tableType]) {
                return;
            }
            
            let filteredData = currentData[tableType];
            
            // Apply search filter if there's a search value
            if (searchValue && searchValue.trim() !== '') {
                const searchTerm = searchValue.toLowerCase().trim();
                
                filteredData = currentData[tableType].filter(item => {
                    switch(tableType) {
                        case 'fournisseur':
                            return item.FOURNISSEUR && item.FOURNISSEUR.toLowerCase().includes(searchTerm);
                        case 'product':
                            const productName = item.PRODUIT || item.PRODUCT;
                            return productName && productName.toLowerCase().includes(searchTerm);
                        case 'zone':
                            return item.ZONE && item.ZONE.toLowerCase().includes(searchTerm);
                        case 'client':
                            return item.CLIENT && item.CLIENT.toLowerCase().includes(searchTerm);
                        case 'operateur':
                            return item.OPERATEUR && item.OPERATEUR.toLowerCase().includes(searchTerm);
                        case 'bccb':
                            return (item.DOCUMENTNO && item.DOCUMENTNO.toLowerCase().includes(searchTerm)) ||
                                   (item.DATEORDERED && item.DATEORDERED.toLowerCase().includes(searchTerm));
                        case 'bccbProduct':
                            const bccbProductName = item.PRODUCT || item.PRODUIT;
                            return bccbProductName && bccbProductName.toLowerCase().includes(searchTerm);
                        default:
                            return true;
                    }
                });
            }
            
            // Update the table with filtered data
            switch(tableType) {
                case 'fournisseur':
                    updateFournisseurTable(filteredData);
                    break;
                case 'product':
                    updateProductTable(filteredData);
                    break;
                case 'zone':
                    updateZoneTable(filteredData);
                    break;
                case 'client':
                    updateClientTable(filteredData);
                    break;
                case 'operateur':
                    updateOperateurTable(filteredData);
                    break;
                case 'bccb':
                    updateBccbTable(filteredData);
                    break;
                case 'bccbProduct':
                    updateBccbProductTable(filteredData);
                    break;
            }
        }

        function autoApplyFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            // Only apply filters if we have date range and data has been fetched
            if (!startDate || !endDate || !currentData.fournisseur) {
                return;
            }
            
            // Handle BCCB search input specially
            const bccbInput = document.getElementById('search-bccb');
            if (bccbInput) {
                bccbFilters.searchText = bccbInput.value.trim();
                applyBccbFilters();
            }
            
            // Debounce the filter application to avoid rapid successive calls for other tables
            clearTimeout(window.autoFilterTimeout);
            window.autoFilterTimeout = setTimeout(() => {
                fetchAllDataWithFilters();
            }, 300);
        }

        function hideAllSuggestions() {
            document.querySelectorAll('.suggestions-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
        
        function extractSuggestionsFromData() {
            // Extract unique values for autocomplete from fetched data
            if (currentData.fournisseur) {
                suggestionsData.fournisseur = [...new Set(
                    currentData.fournisseur
                        .filter(item => item.FOURNISSEUR && item.FOURNISSEUR !== 'Total')
                        .map(item => item.FOURNISSEUR)
                )].sort();
            }
            
            if (currentData.product) {
                suggestionsData.product = [...new Set(
                    currentData.product
                        .filter(item => (item.PRODUIT || item.PRODUCT) && item.PRODUIT !== 'Total' && item.PRODUCT !== 'Total')
                        .map(item => item.PRODUIT || item.PRODUCT)
                )].sort();
            }
            
            if (currentData.zone) {
                suggestionsData.zone = [...new Set(
                    currentData.zone
                        .filter(item => item.ZONE && item.ZONE !== 'Total')
                        .map(item => item.ZONE)
                )].sort();
            }
            
            if (currentData.client) {
                suggestionsData.client = [...new Set(
                    currentData.client
                        .filter(item => item.CLIENT && item.CLIENT !== 'Total')
                        .map(item => item.CLIENT)
                )].sort();
            }
            
            if (currentData.operateur) {
                suggestionsData.operateur = [...new Set(
                    currentData.operateur
                        .filter(item => item.OPERATEUR && item.OPERATEUR !== 'Total')
                        .map(item => item.OPERATEUR)
                )].sort();
            }
            
            if (currentData.bccb) {
                suggestionsData.bccb = [...new Set(
                    currentData.bccb
                        .filter(item => item.DOCUMENTNO && item.DOCUMENTNO !== 'Total')
                        .map(item => item.DOCUMENTNO)
                )].sort();
            }
            
            if (currentData.bccbProduct) {
                suggestionsData.bccbProduct = [...new Set(
                    currentData.bccbProduct
                        .filter(item => (item.PRODUCT || item.PRODUIT) && item.PRODUCT !== 'Total' && item.PRODUIT !== 'Total')
                        .map(item => item.PRODUCT || item.PRODUIT)
                )].sort();
            }
        }
        
        function getFilterParams() {
            return {
                fournisseur: document.getElementById('search-fournisseur').value.trim(),
                product: document.getElementById('search-product').value.trim(),
                zone: document.getElementById('search-zone').value.trim(),
                client: document.getElementById('search-client').value.trim(),
                operateur: document.getElementById('search-operateur').value.trim(),
                bccb: document.getElementById('search-bccb').value.trim(),
                bccbProduct: document.getElementById('search-bccb-product').value.trim()
            };
        }
        
        function applyFiltersAndFetch() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            
            fetchAllDataWithFilters();
        }
        
        async function fetchAllDataWithFilters() {
            showLoadingState();
            
            try {
                await Promise.allSettled([
                    fetchTotalRecap(),
                    fetchFournisseurDataWithFilters(),
                    fetchProductDataWithFilters(),
                    fetchZoneDataWithFilters(),
                    fetchClientDataWithFilters(),
                    fetchOperateurDataWithFilters(),
                    fetchBccbDataWithFilters(),
                    fetchBccbProductDataWithFilters()
                ]);
                
                // Extract suggestions after fetching data
                extractSuggestionsFromData();
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }
        
        async function fetchFournisseurDataWithFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filterParams = getFilterParams();
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchFournisseurData"));
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000");
                
                if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
                if (filterParams.product) url.searchParams.append("product", filterParams.product);
                if (filterParams.client) url.searchParams.append("client", filterParams.client);
                if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
                if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
                if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Network response was not ok");
                
                const data = await response.json();
                updateFournisseurTable(data);
                currentData.fournisseur = data;
            } catch (error) {
                console.error('Error fetching fournisseur data with filters:', error);
                document.getElementById('fournisseur-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }
        
        async function fetchProductDataWithFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filterParams = getFilterParams();
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchProductData"));
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000");
                
                if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
                if (filterParams.product) url.searchParams.append("product", filterParams.product);
                if (filterParams.client) url.searchParams.append("client", filterParams.client);
                if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
                if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
                if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Network response was not ok");
                
                const data = await response.json();
                updateProductTable(data);
                currentData.product = data;
            } catch (error) {
                console.error('Error fetching product data with filters:', error);
                document.getElementById('product-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }
        
        async function fetchZoneDataWithFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filterParams = getFilterParams();
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchZoneRecap"));
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000");
                
                if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
                if (filterParams.product) url.searchParams.append("product", filterParams.product);
                if (filterParams.client) url.searchParams.append("client", filterParams.client);
                if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
                if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
                if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Network response was not ok");
                
                const data = await response.json();
                updateZoneTable(data);
                currentData.zone = data;
            } catch (error) {
                console.error('Error fetching zone data with filters:', error);
                document.getElementById('zone-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }
        
        async function fetchClientDataWithFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filterParams = getFilterParams();
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchClientRecap"));
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000");
                
                if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
                if (filterParams.product) url.searchParams.append("product", filterParams.product);
                if (filterParams.client) url.searchParams.append("client", filterParams.client);
                if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
                if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
                if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Network response was not ok");
                
                const data = await response.json();
                updateClientTable(data);
                currentData.client = data;
            } catch (error) {
                console.error('Error fetching client data with filters:', error);
                document.getElementById('client-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }
        
        async function fetchOperateurDataWithFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filterParams = getFilterParams();
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchOperatorRecap"));
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000");
                
                if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
                if (filterParams.product) url.searchParams.append("product", filterParams.product);
                if (filterParams.client) url.searchParams.append("client", filterParams.client);
                if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
                if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
                if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Network response was not ok");
                
                const data = await response.json();
                updateOperateurTable(data);
                currentData.operateur = data;
            } catch (error) {
                console.error('Error fetching operateur data with filters:', error);
                document.getElementById('operateur-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }
        
        async function fetchBccbDataWithFilters() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filterParams = getFilterParams();
            
            try {
                const url = new URL(API_CONFIG.getApiUrl("/fetchBCCBRecap"));
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000");
                
                if (filterParams.fournisseur) url.searchParams.append("fournisseur", filterParams.fournisseur);
                if (filterParams.product) url.searchParams.append("product", filterParams.product);
                if (filterParams.client) url.searchParams.append("client", filterParams.client);
                if (filterParams.operateur) url.searchParams.append("operateur", filterParams.operateur);
                if (filterParams.bccb) url.searchParams.append("bccb", filterParams.bccb);
                if (filterParams.zone) url.searchParams.append("zone", filterParams.zone);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Network response was not ok");
                
                const data = await response.json();
                updateBccbTable(data);
                currentData.bccb = data;
            } catch (error) {
                console.error('Error fetching BCCB data with filters:', error);
                document.getElementById('bccb-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data</td></tr>';
            }
        }
        
        async function fetchBccbProductDataWithFilters() {
            const filterParams = getFilterParams();
            
            console.log('fetchBccbProductDataWithFilters called with filterParams:', filterParams);
            
            // BCCB Product endpoint only needs the specific BCCB document number
            if (!filterParams.bccb || filterParams.bccb.trim() === '') {
                // If no BCCB filter is set, clear the table
                console.log('No BCCB filter set, showing instruction message');
                document.getElementById('bccb-product-table').innerHTML = '<tr><td colspan="4" class="text-center p-4">Please select a BCCB to view products</td></tr>';
                currentData.bccbProduct = [];
                return;
            }
            
            try {
                const bccbValue = filterParams.bccb.trim();
                
                // Debug API_CONFIG
                console.log('API_CONFIG check:', typeof API_CONFIG !== 'undefined' ? 'defined' : 'undefined');
                if (typeof API_CONFIG === 'undefined') {
                    throw new Error('API_CONFIG is not defined');
                }
                
                const url = new URL(API_CONFIG.getApiUrl("/fetchBCCBProduct"));
                url.searchParams.append("bccb", bccbValue);
                url.searchParams.append("ad_org_id", "1000000");
                
                console.log('Fetching BCCB Product data with filters for BCCB:', bccbValue);
                console.log('Full URL:', url.toString());
                
                // Show loading state for BCCB product table specifically
                document.getElementById('bccb-product-table').innerHTML = '<tr><td colspan="4" class="text-center p-4"><div class="loading-spinner"></div><span class="ml-2">Loading products...</span></td></tr>';
                
                const response = await fetch(url);
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }
                
                const data = await response.json();
                console.log('BCCB Product data received:', data);
                console.log('Data length:', data ? data.length : 'null');
                
                updateBccbProductTable(data);
                currentData.bccbProduct = data;
                
                // Extract suggestions for BCCB products if data exists
                if (data && data.length > 0) {
                    suggestionsData.bccbProduct = [...new Set(
                        data
                            .filter(item => (item.PRODUCT || item.PRODUIT) && item.PRODUCT !== 'Total' && item.PRODUIT !== 'Total')
                            .map(item => item.PRODUCT || item.PRODUIT)
                    )].sort();
                    console.log('Updated BCCB Product suggestions:', suggestionsData.bccbProduct);
                }
            } catch (error) {
                console.error('Error fetching BCCB product data with filters:', error);
                console.error('Error details:', error.message);
                console.error('Error stack:', error.stack);
                document.getElementById('bccb-product-table').innerHTML = '<tr><td colspan="4" class="text-center text-red-500">Error loading data: ' + error.message + '</td></tr>';
            }
        }
    </script>
</body>
</html>