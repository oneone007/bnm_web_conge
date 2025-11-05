<?php
session_start();
$page_identifier = 'Etatstock';

// Include permission system - this will handle both login check and role permissions
require_once 'check_permission.php';

// Call the function to check session timeout




// // Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etat Stock</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="etatstock.css">
    <script src="theme.js" defer></script>
        <script src="api_config.js"></script>

    <style>
        /* Dark mode styles for Search & Filter container */
        .dark-mode .search-filter-container {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }

        /* Search input dark mode */
        .dark-mode input[id="recap_fournisseur"],
        .dark-mode input[id="recap_product"] {
            background-color: #374151 !important;
            color: #ffffff !important;
            border-color: #4b5563 !important;
        }

        .dark-mode input[id="recap_fournisseur"]:focus,
        .dark-mode input[id="recap_product"]:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5) !important;
        }

        /* Label dark mode */
        .dark-mode label[for="recap_fournisseur"],
        .dark-mode label[for="recap_product"] {
            color: #d1d5db !important;
        }

        /* Dropdown dark mode */
        .dark-mode .dropdown {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }

        .dark-mode .dropdown-item {
            color: #ffffff !important;
        }

        .dark-mode .dropdown-item:hover {
            background-color: #4b5563 !important;
        }

        /* Active filters dark mode */
        .dark-mode #active-filters {
            color: #d1d5db !important;
        }

        /* Clear button dark mode */
        .dark-mode #clear-fournisseur:hover,
        .dark-mode #clear-product:hover {
            background-color: #4b5563 !important;
        }

        /* Search icon dark mode */
        .dark-mode .search-icon svg {
            color: #9ca3af !important;
        }

        .dark-mode input[id="recap_fournisseur"]:focus ~ .search-icon svg,
        .dark-mode input[id="recap_product"]:focus ~ .search-icon svg {
            color: #60a5fa !important;
        }

        /* Filter tags dark mode */
        .dark-mode .bg-blue-100 {
            background-color: #1e3a8a !important;
        }

        .dark-mode .text-blue-800 {
            color: #bfdbfe !important;
        }

        .dark-mode .bg-green-100 {
            background-color: #14532d !important;
        }

        .dark-mode .text-green-800 {
            color: #bbf7d0 !important;
        }

        /* Download button text dark mode */
        .dark-mode #stock_excel span {
            color: #ffffff !important;
        }

        /* Search & Filter heading dark mode */
        .dark-mode h2[class*="text-xl"][class*="font-semibold"] {
            color: #ffffff !important;
        }
        /* Quantity badges for product suggestions */
        .qty-badge {
            display: inline-block;
            font-size: 0.72rem;
            padding: 0.12rem 0.35rem;
            border-radius: 0.35rem;
            margin-left: 0.35rem;
            color: #111827; /* dark text */
            background: #f3f4f6; /* subtle neutral */
            border: 1px solid #e5e7eb;
        }
        /* DISP remains blue (informational), RES switched to neutral gray to avoid strong red/green) */
        .qty-badge--dispo { background: #eef2ff; border-color: #e0e7ff; color: #1e3a8a; }
        .qty-badge--reserved { background: #f3f4f6; border-color: #e5e7eb; color: #374151; }

        /* Dark mode for badges */
        .dark .qty-badge { color: #e5e7eb; background: #374151; border-color: rgba(255,255,255,0.06); }
        .dark .qty-badge--dispo { background: #374151; color: #e6eefc; }
        .dark .qty-badge--reserved { background: #4b5563; color: #e5e7eb; }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">



 
    

    

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4 pb-16">
        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Etat de Stock 
            </h1>
        </div>
        



<div class="search-filter-container bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="flex items-center justify-center mb-4">
        <div class="flex items-center space-x-2">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Search & Filter</h2>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product Search -->
        <div class="relative">
            <label for="recap_product" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Search Product
            </label>
            <div class="relative">
                <input type="text" id="recap_product" placeholder="Type to search products..." 
                       class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm transition-all duration-200" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none search-icon">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button id="clear-product" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors duration-200" style="display: none;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="product-dropdown" class="dropdown absolute left-0 right-0 z-20 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-b-lg shadow-xl mt-1 hidden max-h-60 overflow-y-auto"></div>
        </div>
        <!-- Fournisseur Search -->
        <div class="relative">
            <label for="recap_fournisseur" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Search Fournisseur
            </label>
            <div class="relative">
                <input type="text" id="recap_fournisseur" placeholder="Type to search fournisseurs..." 
                       class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm transition-all duration-200" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none search-icon">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button id="clear-fournisseur" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors duration-200" style="display: none;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="fournisseur-dropdown" class="dropdown absolute left-0 right-0 z-20 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-b-lg shadow-xl mt-1 hidden max-h-60 overflow-y-auto"></div>
        </div>


    </div>

    <!-- Active Filters Display -->
    <div id="active-filters" class="mt-4 flex flex-wrap gap-2" style="display: none;">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active filters:</span>
        <div id="filter-tags" class="flex flex-wrap gap-2"></div>
    </div>
</div>






        <br>
        <br>
        <!-- Data Table -->

                <div class="table-wrapper">
                    <div class="table-container rounded-2xl bg-white shadow-lg dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <!-- Table will be rendered here by JS -->
                        </div>
                    </div>
                </div>

        <!-- Pagination -->


                <div class="tables-wrapper flex flex-col md:flex-row gap-4">
                    <!-- Magasins Dropdown -->
                    <div class="dropdown-container flex-1 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <label for="magasinDropdown" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Magasin</label>
                        <select id="magasinDropdown" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-400 focus:outline-none transition-all">
                            <option value="">Loading magasins...</option>
                        </select>
                    </div>
                    <!-- Emplacements Dropdown -->
                    <div class="dropdown-container flex-1 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <label for="emplacementDropdown" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Emplacement</label>
                        <select id="emplacementDropdown" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-400 focus:outline-none transition-all" disabled>
                            <option value="">Select magasin first</option>
                        </select>
                    </div>
                </div>


        <br>

                <div class="flex flex-wrap gap-3 mb-6 mt-4">
                    <button id="refreshButton" class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg shadow hover:bg-blue-100 dark:bg-gray-700 dark:text-white dark:hover:bg-blue-900 border border-gray-300 dark:border-gray-600 transition-all">
                        🔄 Refresh
                    </button>
                    <button id="showDesactivatedLotBtn" class="px-4 py-2 bg-orange-100 text-orange-900 rounded-lg shadow hover:bg-orange-200 dark:bg-orange-700 dark:text-white dark:hover:bg-orange-600 border border-orange-300 dark:border-orange-600 transition-all">
                        🟧 Show Desactivated Lot
                    </button>
                    <button id="stock_excel" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-900 rounded-lg shadow hover:bg-green-200 dark:bg-green-700 dark:text-white dark:hover:bg-green-600 border border-green-300 dark:border-green-600 transition-all">
                        <img src="assets/excel.png" alt="Excel Icon" class="w-5 h-5" />
                        <span>Download</span>
                    </button>
                </div>





<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800" style="max-height:528px;">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">ETAT DE STOCK</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">Click on any row to view detailed product information</p>


                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead class="sticky top-0 z-20">
                        <tr class="table-header dark:bg-gray-700 bg-gray-100">
                            <th data-column="FOURNISSEUR" onclick="sortTable('FOURNISSEUR')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                Fournisseur
                                <div class="resizer"></div>
                            </th>
                            <th data-column="NAME" onclick="sortTable('NAME')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                NAME
                                <div class="resizer"></div>
                            </th>
                            <th data-column="QTY" onclick="sortTable('QTY')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                QTY
                                <div class="resizer"></div>
                            </th>
                            <th data-column="QTY_DISPO" onclick="sortTable('QTY_DISPO')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                QTY_DISPO
                                <div class="resizer"></div>
                            </th>
                            <th data-column="QTY_RESERVED" onclick="sortTable('QTY_RESERVED')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                QTY_RESERVED
                                <div class="resizer"></div>
                            </th>
                            <th data-column="PRIX" onclick="sortTable('PRIX')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                PRIX
                                <div class="resizer"></div>
                            </th>
                            <th data-column="PRIX_DISPO" onclick="sortTable('PRIX_DISPO')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                PRIX_DISPO
                                <div class="resizer"></div>
                            </th>
                            <th data-column="PLACE" onclick="sortTable('PLACE')" class="resizable border border-gray-300 px-4 py-3 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">
                                PLACE
                                <div class="resizer"></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="data-table" class="dark:bg-gray-800">
                        <!-- Dynamic Rows -->
                    </tbody>
                </table>

    </div>
</div>
<div class="w-full flex justify-center">
        <div id="pagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
            <button id="firstPage" class="px-4 py-1 border rounded-lg dark:border-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">First</button>
            <button id="prevPage" class="px-4 py-1 border rounded-lg dark:border-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">Previous</button>
            <span id="pageIndicator"></span>
            <button id="nextPage" class="px-4 py-1 border rounded-lg dark:border-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">Next</button>
            <button id="lastPage" class="px-4 py-1 border rounded-lg dark:border-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900 transition-all">Last</button>
        </div>
</div>


<!-- Product Details Table (Initially Hidden) -->
<div id="productDetailsContainer" class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mt-6 mb-16 pb-16" style="display: none;">
    <div class="overflow-x-auto">
        <div class="flex justify-between items-center mb-4 p-4">
            <h2 id="productDetailsTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Product Details</h2>
            <div class="flex gap-2">
                <button id="downloadProductDetailsExcel" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600" style="display: none;">
                    Download Excel
                </button>
                <!-- See reserved product button (hidden/shown dynamically) -->
                <button id="seeReservedProductBtn" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600" style="display: none;">
                    See reserved product
                </button>
                <button id="closeProductDetails" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600" onclick="closeProductDetails()">
                    Close
                </button>
            </div>
        </div>
        
        <style>
        /* Product Details Table Only */
        .product-details-table th {
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            padding: 4px 8px;
        }
        .product-details-table th[data-column="MARGE"],
        .product-details-table th[data-column="QTY"],
        .product-details-table th[data-column="QTY_DISPO"],
        .product-details-table th[data-column="QTY_RESERVED"],
        .product-details-table th[data-column="P_ACHAT"],
        .product-details-table th[data-column="REM_ACHAT"],
        .product-details-table th[data-column="BON_ACHAT"],
        .product-details-table th[data-column="P_REVIENT"],
        .product-details-table th[data-column="P_VENTE"],
        .product-details-table th[data-column="REM_VENTE"],
        .product-details-table th[data-column="BON_VENTE"],
        .product-details-table th[data-column="REMISE_AUTO"],
        .product-details-table th[data-column="BONUS_AUTO"],
        .product-details-table th[data-column="PPA"] {
            width: 60px;
            white-space: nowrap;
        }
        .product-details-table th[data-column="LOCATION"],
        .product-details-table th[data-column="LOT"],
        .product-details-table th[data-column="GUARANTEEDATE"] {
            width: 80px;
        }
        .product-details-table th[data-column="FOURNISSEUR"],
        .product-details-table th[data-column="PRODUCT"] {
            width: 120px;
        }
        
        /* Inactive lot styling */
        .lot-inactive {
            background-color: #fed7aa !important; /* Light orange background */
        }
        .dark .lot-inactive {
            background-color: #c2410c !important; /* Darker orange for dark mode */
            color: #fef3f2 !important; /* Light text for better contrast */
        }
        </style>
        <table class="product-details-table min-w-full border-collapse text-sm text-left dark:text-white">
            <thead>
                <tr class="table-header dark:bg-gray-700">
                    <th data-column="FOURNISSEUR" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Fournisseur
                        <div class="resizer"></div>
                    </th>
                    <th data-column="PRODUCT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Product
                        <div class="resizer"></div>
                    </th>
                    <th data-column="MARGE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Marge
                        <div class="resizer"></div>
                    </th>
                    <th data-column="QTY" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Qty
                        <div class="resizer"></div>
                    </th>
                    <th data-column="QTY_DISPO" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Qty_Dispo
                        <div class="resizer"></div>
                    </th>
                    <th data-column="QTY_RESERVED" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Qty_Reserved
                        <div class="resizer"></div>
                    </th>
                    <th data-column="P_ACHAT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        P_Achat
                        <div class="resizer"></div>
                    </th>
                    <th data-column="REM_ACHAT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Rem_Achat
                        <div class="resizer"></div>
                    </th>
                    <th data-column="BON_ACHAT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Bon_Achat
                        <div class="resizer"></div>
                    </th>
                    <th data-column="P_REVIENT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        P_Revient
                        <div class="resizer"></div>
                    </th>
                    <th data-column="P_VENTE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        P_Vente
                        <div class="resizer"></div>
                    </th>
                    <th data-column="REM_VENTE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Rem_Vente
                        <div class="resizer"></div>
                    </th>
                    <th data-column="BON_VENTE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Bon_Vente
                        <div class="resizer"></div>
                    </th>
                    <th data-column="REMISE_AUTO" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Remise_Auto
                        <div class="resizer"></div>
                    </th>
                    <th data-column="BONUS_AUTO" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Bonus_Auto
                        <div class="resizer"></div>
                    </th>
                    <th data-column="PPA" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        PPA
                        <div class="resizer"></div>
                    </th>
                    <th data-column="LOCATION" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Location
                        <div class="resizer"></div>
                    </th>
                    <th data-column="LOT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Lot
                        <div class="resizer"></div>
                    </th>
                    <th data-column="GUARANTEEDATE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Guarantee Date
                        <div class="resizer"></div>
                    </th>
                </tr>
            </thead>
            <tbody id="product-details-table" class="dark:bg-gray-800">
                <!-- Dynamic Product Details Rows -->
            </tbody>
        </table>
    </div>
</div>


<script>
    // Initialize resizer functionality for all resizable table headers
    function initializeResizers() {
        document.querySelectorAll("th.resizable").forEach(function (th) {
            const resizer = th.querySelector(".resizer");
            
            if (resizer && !resizer.hasListener) {
                resizer.hasListener = true; // Prevent duplicate listeners
                resizer.addEventListener("mousedown", function initResize(e) {
                    e.preventDefault();
                    window.addEventListener("mousemove", resizeColumn);
                    window.addEventListener("mouseup", stopResize);

                    function resizeColumn(e) {
                        const newWidth = e.clientX - th.getBoundingClientRect().left;
                        th.style.width = newWidth + "px";
                    }

                    function stopResize() {
                        window.removeEventListener("mousemove", resizeColumn);
                        window.removeEventListener("mouseup", stopResize);
                    }
                });
            }
        });
    }
    
    // Initialize resizers when page loads
    document.addEventListener("DOMContentLoaded", initializeResizers);
</script>




 <script>
  let allData = [];
let sortOrders = {}; // track asc/desc per column
let selectedMagasin = null;
let selectedEmplacement = null;
let useDesactivatedLotEndpoint = false;
let cachedProducts = []; // Store products locally for suggestions

// Sort table by column (toggles asc/desc). Keeps 'Total' row at top.
function sortTable(column) {
    if (!allData || allData.length === 0) return;

    // Toggle order
    if (!sortOrders[column]) sortOrders[column] = 'asc';
    else sortOrders[column] = sortOrders[column] === 'asc' ? 'desc' : 'asc';

    const totalRow = allData.find(r => r.FOURNISSEUR && r.FOURNISSEUR.toString().toLowerCase() === 'total');
    const regular = allData.filter(r => !(r.FOURNISSEUR && r.FOURNISSEUR.toString().toLowerCase() === 'total'));

    regular.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];

        // Normalize undefined/null
        if (aVal === undefined || aVal === null) aVal = '';
        if (bVal === undefined || bVal === null) bVal = '';

        // Try numeric compare first
        const aNum = parseFloat(aVal);
        const bNum = parseFloat(bVal);
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return sortOrders[column] === 'asc' ? aNum - bNum : bNum - aNum;
        }

        // Fallback to string compare
        return sortOrders[column] === 'asc' ? aVal.toString().localeCompare(bVal.toString()) : bVal.toString().localeCompare(aVal.toString());
    });

    allData = totalRow ? [totalRow, ...regular] : regular;

    updateSortIndicators(column, sortOrders[column]);
    currentPage = 1; // reset to first page when sorting
    renderTable();
}

function updateSortIndicators(activeColumn, order) {
    // Remove existing indicators
    document.querySelectorAll('th[data-column]').forEach(th => {
        let indicator = th.querySelector('.sort-indicator');
        if (indicator) indicator.remove();
    });

    // Add indicator to active column
    const th = document.querySelector(`th[data-column="${activeColumn}"]`);
    if (th) {
        const span = document.createElement('span');
        span.className = 'sort-indicator ml-2';
        span.style.fontSize = '0.8rem';
        span.textContent = order === 'asc' ? '▲' : '▼';
        th.appendChild(span);
    }
}

function updateDesactivatedLotBtn() {
    const btn = document.getElementById("showDesactivatedLotBtn");
    if (useDesactivatedLotEndpoint) {
        btn.classList.add("ring", "ring-orange-500", "font-bold");
        btn.textContent = "✅ Showing Desactivated Lot";
    } else {
        btn.classList.remove("ring", "ring-orange-500", "font-bold");
        btn.textContent = "🟧 Show Desactivated Lot";
    }
}

// Debounce function to limit API calls
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
    initializeDropdowns();
    fetchData(); // Initial fetch without any filters
    updateDesactivatedLotBtn();

    // Set up other event listeners
    document.getElementById("refreshButton").addEventListener("click", () => {
        useDesactivatedLotEndpoint = false;
        updateDesactivatedLotBtn();
        const fournisseur = document.getElementById("recap_fournisseur").value.trim();
        const product = document.getElementById("recap_product").value.trim();
        fetchData(fournisseur, selectedMagasin, selectedEmplacement, product || null);
    });

    document.getElementById("showDesactivatedLotBtn").addEventListener("click", () => {
        useDesactivatedLotEndpoint = !useDesactivatedLotEndpoint;
        updateDesactivatedLotBtn();
        const fournisseur = document.getElementById("recap_fournisseur").value.trim();
        const product = document.getElementById("recap_product").value.trim();
        fetchData(fournisseur, selectedMagasin, selectedEmplacement, product || null);
    });

    document.getElementById('stock_excel').addEventListener('click', exportToExcel);
    setupFournisseurSearch();
    setupProductSearch();
    updateActiveFilters();
    setupThemeToggle();
    
    // Add close button event listener for product details
    const closeBtn = document.getElementById("closeProductDetails");
    if (closeBtn) {
        closeBtn.addEventListener("click", closeProductDetails);
    }
    
    // Add Excel download button event listener for product details
    const downloadBtn = document.getElementById("downloadProductDetailsExcel");
    if (downloadBtn) {
        downloadBtn.addEventListener("click", downloadProductDetailsExcel);
    }
});

// Initialize dropdown functionality
function initializeDropdowns() {
    // Load magasins dropdown
    loadMagasinsDropdown();
    
    // Set up dropdown event listeners
    document.getElementById("magasinDropdown").addEventListener("change", function() {
        selectedMagasin = this.value || null;
        updateEmplacementDropdown();
        fetchFilteredData();
    });
    
    document.getElementById("emplacementDropdown").addEventListener("change", function() {
        selectedEmplacement = this.value || null;
        fetchFilteredData();
    });
}

// Load magasins into dropdown
async function loadMagasinsDropdown() {
    const dropdown = document.getElementById("magasinDropdown");
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/fetch-magasins'));
        if (!response.ok) throw new Error("Failed to load magasins");
        
        const data = await response.json();
        dropdown.innerHTML = '<option value="">Default Magasins</option>';
        
        data.forEach(magasin => {
            const option = document.createElement("option");
            option.value = magasin.MAGASIN;
            option.textContent = magasin.MAGASIN || "Unknown";
            dropdown.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading magasins:", error);
        dropdown.innerHTML = '<option value="">Error loading magasins</option>';
    }
}

// Update emplacement dropdown based on selected magasin
async function updateEmplacementDropdown() {
    const dropdown = document.getElementById("emplacementDropdown");
    
    if (!selectedMagasin) {
        dropdown.innerHTML = '<option value="">Select magasin first</option>';
        dropdown.disabled = true;
        return;
    }
    
    dropdown.innerHTML = '<option value="">Loading emplacements...</option>';
    dropdown.disabled = false;
    
    try {
        const url = new URL(API_CONFIG.getApiUrl('/fetch-emplacements'));
        url.searchParams.append("magasin", selectedMagasin);
        
        const response = await fetch(url);
        if (!response.ok) throw new Error("Failed to load emplacements");
        
        const data = await response.json();
        dropdown.innerHTML = '<option value="">All Emplacements</option>';
        
        data.forEach(emplacement => {
            const option = document.createElement("option");
            option.value = emplacement.EMPLACEMENT;
            option.textContent = emplacement.EMPLACEMENT || "Unknown";
            dropdown.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading emplacements:", error);
        dropdown.innerHTML = '<option value="">Error loading emplacements</option>';
    }
}

// Fetch data with current filters
async function fetchData(fournisseur = "", magasin = null, emplacement = null, name = null) {
    try {
        let endpoint = useDesactivatedLotEndpoint
            ? "fetch_desactivated_lot_data"
            : "fetch-stock-data";
        const url = new URL(API_CONFIG.getApiUrl(`/${endpoint}`));
        if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
        if (magasin) url.searchParams.append("magasin", magasin);
        if (emplacement) url.searchParams.append("emplacement", emplacement);
        if (name) url.searchParams.append("name", name); // add product name as filter

        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');

        allData = await response.json();
        
        // Cache products locally for suggestions (only when no product filter is applied)
        if (!name) {
            cacheProductsLocally(allData);
        }
        
        currentPage = 1; // Reset to first page
        renderTable();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

// Cache unique products with their quantities for dropdown suggestions
function cacheProductsLocally(data) {
    const productMap = new Map();
    
    data.forEach(row => {
        if (!row.NAME || row.FOURNISSEUR?.toLowerCase() === 'total') return;
        
        const productName = row.NAME;
        if (!productMap.has(productName)) {
            productMap.set(productName, {
                name: productName,
                qty: 0,
                qty_dispo: 0,
                qty_reserved: 0
            });
        }
        
        const product = productMap.get(productName);
        product.qty += Number(row.QTY || 0);
        product.qty_dispo += Number(row.QTY_DISPO || 0);
        product.qty_reserved += Number(row.QTY_RESERVED || 0);
    });
    
    cachedProducts = Array.from(productMap.values());
    console.log(`Cached ${cachedProducts.length} unique products locally`);
}



let currentPage = 1;
const rowsPerPage = 10;

function renderTable() {
    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    let totalRow = allData.find(row => row.FOURNISSEUR?.toLowerCase() === "total");
    let filteredData = allData.filter(row => row.FOURNISSEUR?.toLowerCase() !== "total");

    const startIndex = (currentPage - 1) * rowsPerPage;
    const paginatedData = filteredData.slice(startIndex, startIndex + rowsPerPage);

    if (totalRow) {
        const tr = createTableRow(totalRow, true);
        tableBody.appendChild(tr);
    }

    paginatedData.forEach(row => {
        const tr = createTableRow(row);
        tableBody.appendChild(tr);
    });

    updatePagination(filteredData.length);
}

function updatePagination(totalItems) {
    const pageIndicator = document.getElementById("pageIndicator");
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const firstBtn = document.getElementById("firstPage");
    const lastBtn = document.getElementById("lastPage");

    const totalPages = Math.ceil(totalItems / rowsPerPage);
    pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
    firstBtn.disabled = currentPage === 1;
    lastBtn.disabled = currentPage === totalPages;

    prevBtn.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    };

    nextBtn.onclick = () => {
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    };

    firstBtn.onclick = () => {
        currentPage = 1;
        renderTable();
    };

    lastBtn.onclick = () => {
        currentPage = totalPages;
        renderTable();
    };
}


// Alias for fetchData to maintain compatibility

// Export to Excel function
function exportToExcel() {
    const fournisseur = document.getElementById('recap_fournisseur').value.trim() || null;
    const magasin = selectedMagasin;
    const emplacement = selectedEmplacement;

    let url = API_CONFIG.getApiUrl('/download-stock-excel?');
    if (fournisseur) url += `fournisseur=${fournisseur}&`;
    if (magasin) url += `magasin=${magasin}&`;
    if (emplacement) url += `emplacement=${emplacement}&`;

    if (url.endsWith('&')) url = url.slice(0, -1);
    window.location.href = url;
}

// Fournisseur search functionality
function setupFournisseurSearch() {
    const fournisseurInput = document.getElementById("recap_fournisseur");
    const fournisseurDropdown = document.getElementById("fournisseur-dropdown");
    const clearButton = document.getElementById("clear-fournisseur");

    function clearSearch() {
        fournisseurInput.value = "";
        fournisseurDropdown.style.display = "none";
        clearButton.style.display = "none";
        updateActiveFilters();
        fetchData("", selectedMagasin, selectedEmplacement);
    }

    function updateClearButton() {
        clearButton.style.display = fournisseurInput.value.trim() ? "flex" : "none";
    }

    fournisseurInput.addEventListener("input", debounce(function() {
        const searchValue = this.value.trim().toLowerCase();
        updateClearButton();
        updateActiveFilters();
        if (searchValue) {
            showFournisseurDropdown(searchValue);
        } else {
            fournisseurDropdown.style.display = "none";
            fetchData("", selectedMagasin, selectedEmplacement);
        }
    }, 300));

    fournisseurInput.addEventListener("click", function() {
        if (!this.value.trim()) {
            clearSearch();
        }
    });

    clearButton.addEventListener("click", clearSearch);
}

// Show fournisseur dropdown
function showFournisseurDropdown(searchValue) {
    const dropdown = document.getElementById("fournisseur-dropdown");
    dropdown.innerHTML = "";
    dropdown.style.display = "block";

    const uniqueFournisseurs = [...new Set(allData.map(row => row.FOURNISSEUR))]
        .filter(f => f && f.toLowerCase().includes(searchValue));

    if (uniqueFournisseurs.length === 0) {
        dropdown.style.display = "none";
        return;
    }

    uniqueFournisseurs.forEach(fournisseur => {
        const option = document.createElement("div");
        option.classList.add("dropdown-item");
        option.textContent = fournisseur;
        option.addEventListener("click", () => {
            document.getElementById("recap_fournisseur").value = fournisseur;
            dropdown.style.display = "none";
            updateActiveFilters();
            fetchData(fournisseur, selectedMagasin, selectedEmplacement);
        });
        dropdown.appendChild(option);
    });
}


// Keep your existing renderTable and createTableRow functions exactly as they were


function createTableRow(row, isTotal = false) {
    const tr = document.createElement("tr");
    tr.classList.add('table-row', 'dark:bg-gray-700');

    if (isTotal) {
        tr.classList.add('font-bold', 'bg-gray-200', 'dark:bg-gray-800');
    } else {
        // Make non-total rows clickable
        tr.classList.add('cursor-pointer', 'hover:bg-gray-100', 'dark:hover:bg-gray-600');
        tr.addEventListener('click', () => {
            if (row.NAME && row.NAME.trim() !== '') {
                fetchProductDetails(row.NAME);
            }
        });
    }

    const formatNumber = (num, isInt = false) => {
        // Show 0 (zero) explicitly. Only return empty for null/undefined/empty-string
        if (num === null || num === undefined || num === '') return '';
        const n = Number(num);
        if (isNaN(n)) return '';
        if (isInt) return n.toLocaleString('en-US', { maximumFractionDigits: 0 });
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    tr.innerHTML = `
        <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY, true)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_DISPO, true)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_RESERVED, true)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX_DISPO)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${row.PLACE || ''}</td>
    `;
    return tr;
}


function setupProductSearch() {
    const productInput = document.getElementById("recap_product");
    const productDropdown = document.getElementById("product-dropdown");
    const clearButton = document.getElementById("clear-product");

    function clearProductSearch() {
        productInput.value = "";
        productDropdown.style.display = "none";
        clearButton.style.display = "none";
        // Clear any previously shown reserved results when product filter is removed
        clearReservedResults();
        updateActiveFilters();
        fetchFilteredData(); // Re-fetch data without product filter
    }

    function updateClearButton() {
        clearButton.style.display = productInput.value.trim() ? "flex" : "none";
    }

    productInput.addEventListener("input", debounce(function () {
        const searchValue = this.value.trim().toLowerCase();
        updateClearButton();
        updateActiveFilters();
        if (searchValue) {
            showProductDropdown(searchValue);
        } else {
            clearProductSearch();
        }
    }, 300));

    productInput.addEventListener("click", function() {
        if (!this.value.trim()) {
            clearProductSearch();
        }
    });

    clearButton.addEventListener("click", clearProductSearch);

    function showProductDropdown(searchValue) {
        productDropdown.innerHTML = "";
        productDropdown.style.display = "block";

        // Use cached products if available, otherwise fall back to allData
        let productsToShow = [];
        
        if (cachedProducts.length > 0) {
            // Filter cached products based on search value
            productsToShow = cachedProducts.filter(p => 
                p.name && p.name.toLowerCase().includes(searchValue)
            );
        } else {
            // Fallback: compute from allData if cache is empty
            const uniqueProducts = [...new Set(allData.map(row => row.NAME))]
                .filter(p => p && p.toLowerCase().includes(searchValue));
            
            productsToShow = uniqueProducts.map(productName => {
                let totalQty = 0, totalDispo = 0, totalReserved = 0;
                for (const r of allData) {
                    if (!r.NAME) continue;
                    if (r.NAME.toString().toLowerCase() === productName.toString().toLowerCase()) {
                        totalQty += Number(r.QTY || 0);
                        totalDispo += Number(r.QTY_DISPO || 0);
                        totalReserved += Number(r.QTY_RESERVED || 0);
                    }
                }
                return {
                    name: productName,
                    qty: totalQty,
                    qty_dispo: totalDispo,
                    qty_reserved: totalReserved
                };
            });
        }

        if (productsToShow.length === 0) {
            productDropdown.style.display = "none";
            return;
        }

        productsToShow.forEach(product => {
            const option = document.createElement("div");
            option.classList.add("dropdown-item", "flex", "justify-between", "items-center");

            const left = document.createElement('div');
            left.textContent = product.name;

            const right = document.createElement('div');
            right.className = 'flex items-center';
            
            const b1 = document.createElement('span'); 
            b1.className = 'qty-badge'; 
            b1.textContent = 'QTY: ' + (isNaN(product.qty) ? 0 : parseInt(product.qty));
            
            const b2 = document.createElement('span'); 
            b2.className = 'qty-badge qty-badge--dispo'; 
            b2.textContent = 'DISP: ' + (isNaN(product.qty_dispo) ? 0 : parseInt(product.qty_dispo));
            
            const b3 = document.createElement('span'); 
            b3.className = 'qty-badge qty-badge--reserved'; 
            b3.textContent = 'RES: ' + (isNaN(product.qty_reserved) ? 0 : parseInt(product.qty_reserved));
            
            right.appendChild(b1); 
            right.appendChild(b2); 
            right.appendChild(b3);

            option.appendChild(left);
            option.appendChild(right);

            option.addEventListener("click", () => {
                productInput.value = product.name;
                productDropdown.style.display = "none";
                updateClearButton();
                updateActiveFilters();
                fetchFilteredData(); // ✅ Fetch with backend param
            });
            productDropdown.appendChild(option);
        });
    }
}

function showProductDropdown(searchValue) {
    const dropdown = document.getElementById("product-dropdown");
    dropdown.innerHTML = "";
    dropdown.style.display = "block";

    // Use cached products if available, otherwise fall back to allData
    let productsToShow = [];
    
    if (cachedProducts.length > 0) {
        // Filter cached products based on search value
        productsToShow = cachedProducts.filter(p => 
            p.name && p.name.toLowerCase().includes(searchValue)
        );
    } else {
        // Fallback: compute from allData if cache is empty
        const uniqueProducts = [...new Set(allData.map(row => row.NAME))]
            .filter(p => p && p.toLowerCase().includes(searchValue));
        
        productsToShow = uniqueProducts.map(productName => {
            let totalQty = 0, totalDispo = 0, totalReserved = 0;
            for (const r of allData) {
                if (!r.NAME) continue;
                if (r.NAME.toString().toLowerCase() === productName.toString().toLowerCase()) {
                    totalQty += Number(r.QTY || 0);
                    totalDispo += Number(r.QTY_DISPO || 0);
                    totalReserved += Number(r.QTY_RESERVED || 0);
                }
            }
            return {
                name: productName,
                qty: totalQty,
                qty_dispo: totalDispo,
                qty_reserved: totalReserved
            };
        });
    }

    if (productsToShow.length === 0) {
        dropdown.style.display = "none";
        return;
    }

    productsToShow.forEach(product => {
        const option = document.createElement("div");
        option.classList.add("dropdown-item", "flex", "justify-between", "items-center");

        const left = document.createElement('div');
        left.textContent = product.name;

        const right = document.createElement('div');
        right.className = 'flex items-center';
        
        const b1 = document.createElement('span'); 
        b1.className = 'qty-badge'; 
        b1.textContent = 'QTY: ' + (isNaN(product.qty) ? 0 : parseInt(product.qty));
        
        const b2 = document.createElement('span'); 
        b2.className = 'qty-badge qty-badge--dispo'; 
        b2.textContent = 'DISP: ' + (isNaN(product.qty_dispo) ? 0 : parseInt(product.qty_dispo));
        
        const b3 = document.createElement('span'); 
        b3.className = 'qty-badge qty-badge--reserved'; 
        b3.textContent = 'RES: ' + (isNaN(product.qty_reserved) ? 0 : parseInt(product.qty_reserved));
        
        right.appendChild(b1); 
        right.appendChild(b2); 
        right.appendChild(b3);

        option.appendChild(left);
        option.appendChild(right);

        option.addEventListener("click", () => {
            document.getElementById("recap_product").value = product.name;
            dropdown.style.display = "none";
            filterByProduct(product.name);
        });
        dropdown.appendChild(option);
    });
}


function fetchFilteredData() {
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    const name = document.getElementById("recap_product").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement, name || null);
}

function updateActiveFilters() {
    const activeFiltersDiv = document.getElementById("active-filters");
    const filterTagsDiv = document.getElementById("filter-tags");
    const fournisseurValue = document.getElementById("recap_fournisseur").value.trim();
    const productValue = document.getElementById("recap_product").value.trim();

    filterTagsDiv.innerHTML = "";

    if (fournisseurValue || productValue) {
        activeFiltersDiv.style.display = "flex";

        if (fournisseurValue) {
            const tag = document.createElement("span");
            tag.className = "inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200";
            tag.innerHTML = `
                Fournisseur: ${fournisseurValue}
                <button class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-200 hover:bg-blue-300 dark:bg-blue-800 dark:hover:bg-blue-700 text-blue-600 dark:text-blue-300" onclick="clearFournisseurFilter()">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            filterTagsDiv.appendChild(tag);
        }

        if (productValue) {
            const tag = document.createElement("span");
            tag.className = "inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200";
            tag.innerHTML = `
                Product: ${productValue}
                <button class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-200 hover:bg-green-300 dark:bg-green-800 dark:hover:bg-green-700 text-green-600 dark:text-green-300" onclick="clearProductFilter()">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            filterTagsDiv.appendChild(tag);
        }
    } else {
        activeFiltersDiv.style.display = "none";
    }
}

function clearFournisseurFilter() {
    document.getElementById("recap_fournisseur").value = "";
    document.getElementById("clear-fournisseur").style.display = "none";
    updateActiveFilters();
    fetchData("", selectedMagasin, selectedEmplacement);
}

function clearProductFilter() {
    document.getElementById("recap_product").value = "";
    document.getElementById("clear-product").style.display = "none";
    clearReservedResults();
    updateActiveFilters();
    fetchFilteredData();
}

function filterByProduct(product) {
    let filtered = allData.filter(row =>
        row.FOURNISSEUR?.toLowerCase() !== "total" &&
        row.NAME?.toLowerCase().includes(product.toLowerCase())
    );

    let totalRow = allData.find(row => row.FOURNISSEUR?.toLowerCase() === "total");

    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    const startIndex = (currentPage - 1) * rowsPerPage;
    const paginated = filtered.slice(startIndex, startIndex + rowsPerPage);

    if (totalRow) {
        const tr = createTableRow(totalRow, true);
        tableBody.appendChild(tr);
    }

    paginated.forEach(row => {
        const tr = createTableRow(row);
        tableBody.appendChild(tr);
    });

    updatePagination(filtered.length);
}

// Fetch and display product details
async function fetchProductDetails(productName) {
    try {
        // Clear previously displayed reserved results immediately when fetching a new product
        clearReservedResults();
        const url = API_CONFIG.getApiUrl(`/fetch-product-details?product_name=${encodeURIComponent(productName)}`);
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.error) {
            console.error("API Error:", data.error);
            alert("Error fetching product details: " + data.error);
            return;
        }
        
        displayProductDetails(productName, data);
    } catch (error) {
        console.error("Error fetching product details:", error);
        alert("Failed to fetch product details. Please try again.");
    }
}

// Display product details in the table
function displayProductDetails(productName, data) {
    const container = document.getElementById("productDetailsContainer");
    const title = document.getElementById("productDetailsTitle");
    const tableBody = document.getElementById("product-details-table");
    const downloadButton = document.getElementById("downloadProductDetailsExcel");
    
    // Update title
    title.textContent = `Product Details - ${productName}`;
    
    // Clear previous data
    tableBody.innerHTML = "";
    
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="19" class="border px-4 py-2 text-center dark:border-gray-600">
                    No details found for this product
                </td>
            </tr>
        `;
        downloadButton.style.display = "none";
    } else {
        const formatNumber = (num, isInt = false) => {
            if (num === null || num === undefined || num === "") return 0;
            if (isInt) return parseInt(num, 10).toLocaleString('en-US');
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        const formatDate = (dateString) => {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR');
            } catch (e) {
                return dateString;
            }
        };
        
        data.forEach(row => {
            const tr = document.createElement("tr");
            tr.classList.add('table-row', 'dark:bg-gray-700', 'hover:bg-gray-100', 'dark:hover:bg-gray-600');
            
            // Check if lot is inactive and apply orange styling
            if (row.LOT_ACTIVE === "N") { 
                tr.classList.add('lot-inactive');
            }
            
            tr.innerHTML = `
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.MARGE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY, true)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_DISPO, true)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_RESERVED, true)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.P_ACHAT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.REM_ACHAT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.BON_ACHAT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.P_REVIENT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.P_VENTE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.REM_VENTE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.BON_VENTE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.REMISE_AUTO ?? 0}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.BONUS_AUTO || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PPA)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOCATION || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOT || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.GUARANTEEDATE)}</td>
            `;
            
            tableBody.appendChild(tr);
        });
        
        downloadButton.style.display = "inline-flex"; // Show the button
        downloadButton.onclick = () => downloadProductDetailsExcel(data);
    }
    
    // Show the container
    container.style.display = "block";
    
    // Show the download button
    const downloadBtn = document.getElementById("downloadProductDetailsExcel");
    if (downloadBtn) {
        downloadBtn.style.display = "inline-block";
    }
    
    // Re-initialize resizers for the product details table
    setTimeout(() => {
        initializeResizers();
    }, 100);
    
    // Scroll to the product details table
    container.scrollIntoView({ behavior: 'smooth' });

    // After rendering details, decide whether to show "See reserved product" button
    showReservedButtonIfNeeded(data, productName);
}

// Show the reserved button only if any row has QTY_RESERVED > 0
function showReservedButtonIfNeeded(data, productName) {
    const btn = document.getElementById('seeReservedProductBtn');
    if (!btn) return;

    const hasReserved = Array.isArray(data) && data.some(r => {
        const val = r.QTY_RESERVED || r.qty_reserved || r.qtyReserved || r.QTYRESERVED;
        return val !== null && val !== undefined && Number(val) > 0;
    });

    if (hasReserved) {
        btn.style.display = 'inline-block';
        // attach handler
        btn.onclick = () => fetchAndShowReserved(productName, data);
    } else {
        btn.style.display = 'none';
        btn.onclick = null;
    }
}

// Fetch reserved orders from backend and render them below product details
async function fetchAndShowReserved(productName, productData) {
    try {
        // Try to find m_product_id in productData rows if present
        let m_product_id = null;
        if (Array.isArray(productData)) {
            for (const r of productData) {
                if (r.M_PRODUCT_ID || r.productid || r.M_PRODUCTID || r.M_PRODUCT) {
                    m_product_id = r.M_PRODUCT_ID || r.productid || r.M_PRODUCTID || r.M_PRODUCT;
                    break;
                }
            }
        }

        // Build URL: prefer m_product_id, otherwise pass product_name
        const url = new URL(API_CONFIG.getApiUrl('/reserved_reserved_fromorder'));
        if (m_product_id) url.searchParams.append('m_product_id', m_product_id);
        else url.searchParams.append('product_name', productName);

        // Show loading indicator on button
        const btn = document.getElementById('seeReservedProductBtn');
        const oldText = btn.textContent;
        btn.textContent = 'Loading...';
        btn.disabled = true;

        const resp = await fetch(url);
        btn.textContent = oldText;
        btn.disabled = false;

        if (!resp.ok) {
            const err = await resp.json().catch(() => ({}));
            alert('Failed to fetch reserved orders: ' + (err.error || resp.statusText));
            return;
        }

        const data = await resp.json();
        if (!Array.isArray(data) || data.length === 0) {
            renderReservedResults([]); // show none found
        } else {
            renderReservedResults(data);
        }

    } catch (e) {
        console.error('Error fetching reserved:', e);
        alert('Error fetching reserved orders');
        const btn = document.getElementById('seeReservedProductBtn');
        if (btn) { btn.textContent = 'See reserved product'; btn.disabled = false; }
    }
}

// Render the reserved orders table below the product details
function renderReservedResults(rows) {
    // Create/replace a container under productDetailsContainer
    let container = document.getElementById('reservedResultsContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'reservedResultsContainer';
        container.className = 'table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mt-4 p-4';
        const parent = document.getElementById('productDetailsContainer');
        parent.appendChild(container);
    }

    if (!rows || rows.length === 0) {
        container.innerHTML = '<h3 class="text-md font-semibold">Reserved Orders</h3><p>No reserved orders found for this product.</p>';
        return;
    }

    let html = '<h3 class="text-md font-semibold mb-2">Reserved Orders</h3>';
    html += '<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr>';

    // We'll compute a friendly DocumentStatus and hide raw DOCACTION/DOCSTATUS fields
    const srcCols = Object.keys(rows[0]);
    // Build display columns: include all except DOCACTION and DOCSTATUS, and insert DocumentStatus
    const displayCols = srcCols.filter(c => c.toUpperCase() !== 'DOCACTION' && c.toUpperCase() !== 'DOCSTATUS');
    // Ensure DocumentStatus is shown near the end
    displayCols.push('DocumentStatus');

    // If backend returned client_name (alias CB.name AS client_name), move it to the front and show a friendly header
    const clientIdx = displayCols.findIndex(c => String(c).toLowerCase() === 'client_name' || String(c).toLowerCase() === 'client');
    if (clientIdx > -1) {
        const clientCol = displayCols.splice(clientIdx, 1)[0];
        displayCols.unshift(clientCol);
    }

    displayCols.forEach(c => {
        // Friendly label for client_name
        const label = (String(c).toLowerCase() === 'client_name' || String(c).toLowerCase() === 'client') ? 'Client' : c;
        html += `<th class="border px-2 py-1">${label}</th>`
    });
    html += '</tr></thead><tbody>';

    // helper: format reserved date strings to French weekday (4 letters) + day + short month + year
    function formatReservedDate(dateString) {
        if (!dateString) return '';
        try {
            const d = new Date(dateString);
            if (isNaN(d.getTime())) return dateString;
            const weekdaysFr = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
            const monthsShort = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
            const wd = (weekdaysFr[d.getDay()] || '').substring(0,4); // e.g. 'jeud'
            const day = String(d.getDate()).padStart(2, '0');
            const mon = monthsShort[d.getMonth()];
            const year = d.getFullYear();
            return `${wd} ${day} ${mon} ${year}`;
        } catch (e) {
            return dateString;
        }
    }

    rows.forEach(r => {
        // compute document status
        const docAction = (r.DOCACTION || r.docaction || '').toString();
        const docStatus = (r.DOCSTATUS || r.docstatus || '').toString();
        let documentStatus = 'unknown';
        const checkVal = (s) => s && ['PR', 'IP'].includes(s.toString().toUpperCase());
        if (checkVal(docAction) || checkVal(docStatus)) {
            documentStatus = 'reserved';
        } else if ((docAction && ['CO','CL'].includes(docAction.toString().toUpperCase())) || (docStatus && ['CO','CL'].includes(docStatus.toString().toUpperCase()))) {
            documentStatus = 'clotured';
        }

        html += '<tr class="hover:bg-gray-100 dark:hover:bg-gray-600">';
        // render all display columns except the synthetic one
        for (const c of displayCols) {
            if (c === 'DocumentStatus') {
                html += `<td class="border px-2 py-1">${documentStatus}</td>`;
                continue;
            }
            let v = r[c];
            if (v === null || v === undefined) v = '';
            // If column name contains DATE or value looks like a date, format it to French weekday + dd mmm yyyy
            const colNameUpper = String(c).toUpperCase();
            const isDateCol = colNameUpper.includes('DATE') || colNameUpper.includes('DATEORDER') || colNameUpper.includes('DATE_ORDER') || colNameUpper.includes('DATEORDERED');
            const looksLikeDate = typeof v === 'string' && /\b\d{1,2}\s+[A-Za-z]{3,}\s+\d{4}\b/.test(v) || typeof v === 'string' && v.includes('GMT');
            if (isDateCol || looksLikeDate) {
                try {
                    v = formatReservedDate(v);
                } catch (e) {
                    // keep original if formatting fails
                }
            }
            html += `<td class="border px-2 py-1">${v}</td>`;
        }
        html += '</tr>';
    });
    html += '</tbody></table></div>';

    container.innerHTML = html;
    container.scrollIntoView({ behavior: 'smooth' });
}

// Remove/hide reserved results and the reserved button
function clearReservedResults() {
    const container = document.getElementById('reservedResultsContainer');
    if (container) container.remove();
    const btn = document.getElementById('seeReservedProductBtn');
    if (btn) {
        btn.style.display = 'none';
        btn.onclick = null;
        btn.disabled = false;
        btn.textContent = 'See reserved product';
    }
}

// Close product details
function closeProductDetails() {
    console.log("Close button clicked");
    const container = document.getElementById("productDetailsContainer");
    if (container) {
        container.style.display = "none";
        console.log("Product details container hidden");
    } else {
        console.error("Product details container not found");
    }
}

// Download product details as Excel
function downloadProductDetailsExcel() {
    const productName = document.getElementById("productDetailsTitle").textContent.replace("Product Details - ", "");
    if (productName && productName !== "Product Details") {
        const url = API_CONFIG.getApiUrl(`/download-product-details-excel?product_name=${encodeURIComponent(productName)}`);
        window.location.href = url;
    }
}

// Setup theme toggle functionality
function setupThemeToggle() {
    // Dark/Light Mode Toggle Functionality
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement;

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            // Save the theme preference in localStorage
            const isDarkMode = htmlElement.classList.contains('dark');
            localStorage.setItem('darkMode', isDarkMode);
        });
    }

    // Check for saved theme preference
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        htmlElement.classList.add('dark');
    } else {
        htmlElement.classList.remove('dark');
    }
}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply initial theme
            const isDark = localStorage.getItem('theme') === 'dark';
            if (isDark) {
                document.documentElement.classList.add('dark');
            }

            // Listen for theme changes
            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') {
                    if (e.newValue === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        });
    </script>
</body>
</html>