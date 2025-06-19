<?php
session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat','Sup Vente', 'Comptable'])) {
    header("Location: Acess_Denied");    
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movement</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="etatstck.css">    <style>
        /* Custom CSS for date inputs in dark mode */
        .dark input[type="date"] {
            color-scheme: dark;
            background-color: rgb(55 65 81); /* gray-700 */
            border-color: rgb(75 85 99); /* gray-600 */
            color: white;
        }
        
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-text {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-month-field {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-day-field {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-year-field {
            color: white;
        }
        
        /* Force date labels to be white in dark mode */
        .dark .date-filter label {
            color: white !important;
        }
        
        /* Custom dark mode styles for product summary */
        .dark #product-summary {
            background-color: rgb(31 41 55) !important; /* gray-800 */
            border-color: rgb(75 85 99) !important; /* gray-600 */
        }
        
        .dark #product-summary h3 {
            color: white !important;
        }
        
        /* Dark mode for summary cards */
        .dark #product-summary .bg-blue-50 {
            background-color: rgba(30, 58, 138, 0.4) !important; /* blue-900 with opacity */
            border-color: rgb(29 78 216) !important; /* blue-700 */
        }
        
        .dark #product-summary .bg-green-50 {
            background-color: rgba(20, 83, 45, 0.4) !important; /* green-900 with opacity */
            border-color: rgb(21 128 61) !important; /* green-700 */
        }
        
        .dark #product-summary .bg-red-50 {
            background-color: rgba(127, 29, 29, 0.4) !important; /* red-900 with opacity */
            border-color: rgb(185 28 28) !important; /* red-700 */
        }
        
        .dark #product-summary .bg-purple-50 {
            background-color: rgba(88, 28, 135, 0.4) !important; /* purple-900 with opacity */
            border-color: rgb(126 34 206) !important; /* purple-700 */
        }
        
        /* Dark mode text colors */
        .dark #product-summary .text-blue-600 {
            color: rgb(147 197 253) !important; /* blue-300 */
        }
        
        .dark #product-summary .text-blue-700 {
            color: rgb(196 181 253) !important; /* blue-200 */
        }
        
        .dark #product-summary .text-green-600 {
            color: rgb(134 239 172) !important; /* green-300 */
        }
        
        .dark #product-summary .text-green-700 {
            color: rgb(187 247 208) !important; /* green-200 */
        }
        
        .dark #product-summary .text-red-600 {
            color: rgb(252 165 165) !important; /* red-300 */
        }
        
        .dark #product-summary .text-red-700 {
            color: rgb(254 202 202) !important; /* red-200 */
        }
        
        .dark #product-summary .text-purple-600 {
            color: rgb(196 181 253) !important; /* purple-300 */
        }
        
        .dark #product-summary .text-purple-700 {
            color: rgb(221 214 254) !important; /* purple-200 */
        }
        
        /* Dark mode colors for emplacement breakdown */
        .dark #product-summary .bg-yellow-50 {
            background-color: rgba(133, 77, 14, 0.4) !important; /* yellow-900 with opacity */
            border-color: rgb(217 119 6) !important; /* yellow-600 */
        }
        
        .dark #product-summary .bg-orange-50 {
            background-color: rgba(154, 52, 18, 0.4) !important; /* orange-900 with opacity */
            border-color: rgb(234 88 12) !important; /* orange-600 */
        }
        
        .dark #product-summary .bg-teal-50 {
            background-color: rgba(19, 78, 74, 0.4) !important; /* teal-900 with opacity */
            border-color: rgb(13 148 136) !important; /* teal-600 */
        }
        
        .dark #product-summary .bg-indigo-50 {
            background-color: rgba(54, 47, 120, 0.4) !important; /* indigo-900 with opacity */
            border-color: rgb(79 70 229) !important; /* indigo-600 */
        }
        
        .dark #product-summary .text-yellow-600 {
            color: rgb(250 204 21) !important; /* yellow-400 */
        }
        
        .dark #product-summary .text-yellow-700 {
            color: rgb(254 240 138) !important; /* yellow-200 */
        }
        
        .dark #product-summary .text-orange-600 {
            color: rgb(251 146 60) !important; /* orange-400 */
        }
        
        .dark #product-summary .text-orange-700 {
            color: rgb(254 215 170) !important; /* orange-200 */
        }
        
        .dark #product-summary .text-teal-600 {
            color: rgb(45 212 191) !important; /* teal-400 */
        }
        
        .dark #product-summary .text-teal-700 {
            color: rgb(153 246 228) !important; /* teal-200 */
        }
        
        .dark #product-summary .text-indigo-600 {
            color: rgb(129 140 248) !important; /* indigo-400 */
        }
        
        .dark #product-summary .text-indigo-700 {
            color: rgb(199 210 254) !important; /* indigo-200 */
        }
        
        /* Product Search Table Styles */
        .products-table-container {
        width: 100%;
        max-height: 300px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .products-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        }

        .products-table thead {
        background-color: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        }

        .products-table th {
        padding: 8px 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-right: 1px solid #e5e7eb;
        }

        .products-table th:last-child {
        border-right: none;
        }

        .products-table tbody {
        max-height: 200px;
        overflow-y: auto;
        }

        .products-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #f3f4f6;
        border-right: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background-color 0.2s;
        }

        .products-table td:last-child {
        border-right: none;
        }

        .products-table tbody tr:hover {
        background-color: #f9fafb;
        }

        .table-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background-color: #f9fafb;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 8px 8px;
        }

        .table-pagination button {
        padding: 4px 8px;
        border: 1px solid #d1d5db;
        background-color: white;
        color: #374151;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
        }

        .table-pagination button:hover:not(:disabled) {
        background-color: #f3f4f6;
        border-color: #9ca3af;
        }

        .table-pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        }

        .table-pagination #page-info {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
        }

        /* Dark mode for product search table */
        .dark .products-table-container {
        background-color: #374151;
        border-color: #4b5563;
        }

        .dark .products-table thead {
        background-color: #4b5563;
        border-bottom-color: #6b7280;
        }

        .dark .products-table th {
        color: #f9fafb;
        border-right-color: #6b7280;
        }

        .dark .products-table td {
        color: #f9fafb;
        border-bottom-color: #4b5563;
        border-right-color: #4b5563;
        }

        .dark .products-table tbody tr:hover {
        background-color: #4b5563;
        }

        .dark .table-pagination {
        background-color: #4b5563;
        border-top-color: #6b7280;
        }

        .dark .table-pagination button {
        background-color: #374151;
        border-color: #6b7280;
        color: #f9fafb;
        }

        .dark .table-pagination button:hover:not(:disabled) {
        background-color: #4b5563;
        border-color: #9ca3af;
        }

        .dark .table-pagination #page-info {
        color: #d1d5db;
        }
    </style>
    <script src="theme.js" defer></script>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">
                Mouvement de Stock 
            </h1>
        </div>
          <!-- Date Filter Section -->        <div class="filters-section mb-6 flex gap-4 items-center flex-wrap">
            <div class="date-filter">
                <label for="start-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Début:</label>
                <input type="date" id="start-date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition duration-150">
            </div>
            
            <div class="date-filter">
                <label for="end-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Fin:</label>
                <input type="date" id="end-date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition duration-150">
            </div>
        </div>

        <!-- Search Sections -->
        <div class="search-container relative" style="max-width: 700px; width: 100%;">
            <input type="text" id="recap_product" placeholder="Search Product">
            <div id="product-dropdown" class="dropdown">
                <div class="products-table-container" id="products-table-container" style="display: none;">
                    <table class="products-table" id="products-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                            </tr>
                        </thead>
                        <tbody id="products-table-body">
                            <!-- Products will be loaded here -->
                        </tbody>
                    </table>
                    <div class="table-pagination" id="table-pagination">
                        <button id="prev-page">Previous</button>
                        <span id="page-info">Page 1 of 1</span>
                        <button id="next-page">Next</button>
                    </div>
                </div>
            </div>
        </div>
        <br>

        <!-- Emplacement Dropdown -->
        <div class="dropdown-container bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-4">
            <label for="emplacementDropdown" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Emplacement</label>
            <select id="emplacementDropdown" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Default (Préparation + Hangar + Réserve)</option>
            </select>
        </div>        <!-- Refresh and Export Buttons -->
        <div class="flex gap-4 mb-4">
            <button id="applyFilterButton" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 font-medium">
                🔍 Apply Filters
            </button>
            
            
            
            <button class="Btn center-btn" id="movement_excel">
                <div class="svgWrapper">
                    <img src="assets/excel.png" alt="Excel Icon" class="excelIcon" />
                    <div class="text">&nbsp;Download</div>
                </div>
            </button>
        </div>

        <!-- Product Summary Section (hidden by default) -->
        <div id="product-summary" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-4 border border-gray-200 dark:border-gray-700" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Product Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-200 dark:border-blue-700">
                    <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Stock Initial</div>
                    <div id="stock-initial" class="text-xl font-bold text-blue-700 dark:text-blue-300">0</div>
                    <!-- Emplacement breakdown inside Stock Initial card -->
                    <div id="emplacement-breakdown-inline" class="mt-2 space-y-1" style="display: none;">
                        <div class="text-xs text-blue-500 dark:text-blue-400">
                            <span class="font-medium">Stock Préparation</span>
                            <span id="preparation-stock-inline" class="float-right font-bold">0</span>
                        </div>
                        <div class="text-xs text-blue-500 dark:text-blue-400">
                            <span class="font-medium">Stock HANGAR</span>
                            <span id="hangar-stock-inline" class="float-right font-bold">0</span>
                        </div>
                        <div class="text-xs text-blue-500 dark:text-blue-400">
                            <span class="font-medium">Stock Dépot Hangar réserve</span>
                            <span id="depot-hangar-stock-inline" class="float-right font-bold">0</span>
                        </div>
                        <div class="text-xs text-blue-500 dark:text-blue-400">
                            <span class="font-medium">Stock Dépot réserve</span>
                            <span id="depot-reserve-stock-inline" class="float-right font-bold">0</span>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/30 p-3 rounded-lg border border-green-200 dark:border-green-700">
                    <div class="text-sm text-green-600 dark:text-green-400 font-medium">Total Entrée</div>
                    <div id="total-entree" class="text-xl font-bold text-green-700 dark:text-green-300">0</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/30 p-3 rounded-lg border border-red-200 dark:border-red-700">
                    <div class="text-sm text-red-600 dark:text-red-400 font-medium">Total Sortie</div>
                    <div id="total-sortie" class="text-xl font-bold text-red-700 dark:text-red-300">0</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/30 p-3 rounded-lg border border-purple-200 dark:border-purple-700">
                    <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Stock Final</div>
                    <div id="stock-final" class="text-xl font-bold text-purple-700 dark:text-purple-300">0</div>
                </div>
            </div>
        </div><!-- Data Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">MOUVEMENT DE STOCK</h2>                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th data-column="MOVEMENTDATE" onclick="sortTable('MOVEMENTDATE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Date
                                <div class="resizer"></div>
                            </th>
                            <th data-column="DOCUMENTNO" onclick="sortTable('DOCUMENTNO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Document N°
                                <div class="resizer"></div>
                            </th>
                            <th data-column="NAME" onclick="sortTable('NAME')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Name
                                <div class="resizer"></div>
                            </th>
                            <th data-column="PRODUCTNAME" onclick="sortTable('PRODUCTNAME')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Product
                                <div class="resizer"></div>
                            </th>
                            <th data-column="ENTREE" onclick="sortTable('ENTREE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Entrée
                                <div class="resizer"></div>
                            </th>
                            <th data-column="SORTIE" onclick="sortTable('SORTIE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Sortie
                                <div class="resizer"></div>
                            </th>
                            
                            <th data-column="LOT" onclick="sortTable('LOT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Lot
                                <div class="resizer"></div>
                            </th>
                            <th data-column="LOCATOR_FROM" onclick="sortTable('LOCATOR_FROM')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Locator From
                                <div class="resizer"></div>
                            </th>
                            <th data-column="LOCATOR_TO" onclick="sortTable('LOCATOR_TO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Locator To
                                <div class="resizer"></div>
                            </th>
                            <th data-column="DOCSTATUS" onclick="sortTable('DOCSTATUS')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                Doc Status
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

        <!-- Pagination -->
        <div id="pagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
            <button id="firstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
            <button id="prevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
            <span id="pageIndicator"></span>
            <button id="nextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
            <button id="lastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
        </div>
    </div>    <script>
        let allData = [];
        let currentPage = 1;
        const rowsPerPage = 10;
        let selectedEmplacement = null;
        let filtersApplied = false; // Track if filters have been applied
        let productList = []; // Cache for product list

        // Debounce function to limit API calls
        function debounce(func, delay) {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        }        // Initialize the application
        document.addEventListener("DOMContentLoaded", () => {
            initializeFilters();
            loadProductList(); // Load product list on page load
            // Don't auto-fetch data on page load - wait for user to click Apply Filter
            setupEventListeners();
        });// Initialize filters and dropdowns
        function initializeFilters() {
            // Set default dates (last 3 days)
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 1);
            
            document.getElementById("end-date").value = endDate.toISOString().split('T')[0];
            document.getElementById("start-date").value = startDate.toISOString().split('T')[0];
            
            // Load emplacements dropdown
            loadEmplacements();
        }        // Setup event listeners
        function setupEventListeners() {
            // Apply Filter button - main way to fetch data
            document.getElementById("applyFilterButton").addEventListener("click", () => {
                console.log("Applying filters...");
                fetchFilteredData();
            });

          

            document.getElementById('movement_excel').addEventListener('click', exportToExcel);
            
            // Remove automatic fetching - only fetch when Apply Filter is clicked
            // Date changes no longer trigger automatic fetch
            // document.getElementById("start-date").addEventListener("change", fetchFilteredData);
            // document.getElementById("end-date").addEventListener("change", fetchFilteredData);
            
            // Emplacement changes no longer trigger automatic fetch
            document.getElementById("emplacementDropdown").addEventListener("change", function() {
                selectedEmplacement = this.value || null;
                // Don't auto-fetch, wait for Apply Filter button
            });

            setupProductSearch();
        }

        // Load emplacements dropdown
        async function loadEmplacements() {
            const dropdown = document.getElementById("emplacementDropdown");
            try {
                const response = await fetch("http://192.168.1.94:5000/fetch-emplacements-stock");
                if (!response.ok) throw new Error("Failed to load emplacements");
                
                const data = await response.json();
                dropdown.innerHTML = '<option value="">Default (Préparation + Hangar + Réserve)</option>';
                
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

        // Load product list once on page load
        async function loadProductList() {
            try {
                const response = await fetch("http://192.168.1.94:5000/listproduct");
                if (!response.ok) throw new Error("Failed to load products");
                
                const products = await response.json();
                productList = products || []; // Cache the product list
                console.log(`Loaded ${productList.length} products for search`);
            } catch (error) {
                console.error("Error loading product list:", error);
                productList = []; // Set empty array on error
            }
        }        // Global variable to store emplacement breakdown
        let emplacementBreakdown = {};

        // Fetch data with current filters - matches etatstock.php pattern
        async function fetchData(fournisseur = "", emplacement = null, product = null) {
            try {
                // Show loading state
                showLoading(true);
                
                const url = new URL("http://192.168.1.94:5000/fetch-stock-movement-data");
                
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                
                if (startDate) url.searchParams.append("start_date", startDate);
                if (endDate) url.searchParams.append("end_date", endDate);
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (emplacement) url.searchParams.append("emplacement", emplacement);
                if (product) url.searchParams.append("product", product);

                const response = await fetch(url);
                if (!response.ok) throw new Error('Network response was not ok');

                const responseData = await response.json();
                
                // Handle new response format
                if (responseData && responseData.error) {
                    console.error("API Error:", responseData.error);
                    allData = [];
                    emplacementBreakdown = {};
                } else if (responseData && responseData.data) {
                    // New format with data and emplacement_breakdown
                    allData = Array.isArray(responseData.data) ? responseData.data : [];
                    emplacementBreakdown = responseData.emplacement_breakdown || {};
                    console.log("Emplacement breakdown received:", emplacementBreakdown);
                } else if (Array.isArray(responseData)) {
                    // Old format (backward compatibility)
                    allData = responseData;
                    emplacementBreakdown = {};
                } else {
                    console.error("Unexpected data format:", responseData);
                    allData = [];
                    emplacementBreakdown = {};
                }
                
                currentPage = 1; // Reset to first page
                renderTable();
            } catch (error) {
                console.error("Error fetching data:", error);
                allData = [];
                emplacementBreakdown = {};
                renderTable(); // Still render table (empty)
            } finally {
                // Hide loading state
                showLoading(false);
            }
        }        // Fetch filtered data - matches etatstock.php pattern
        function fetchFilteredData() {
            const product = document.getElementById("recap_product").value.trim();
            filtersApplied = true; // Mark that filters have been applied
            fetchData("", selectedEmplacement, product || null);
        }        // Render table with pagination
        function renderTable() {
            const tableBody = document.getElementById("data-table");
            
            // Ensure allData is an array
            if (!Array.isArray(allData)) {
                console.warn("allData is not an array, setting to empty array");
                allData = [];
            }

            // Clear existing content
            tableBody.innerHTML = "";

            // Check if a product is selected and show/hide summary
            const selectedProduct = document.getElementById("recap_product").value.trim();
            const summarySection = document.getElementById("product-summary");
            
            if (selectedProduct && allData.length > 0) {
                // Calculate totals for the selected product (includes all data, even RE status)
                calculateAndShowSummary();
                summarySection.style.display = "block";
            } else {
                summarySection.style.display = "none";
            }

            // Show empty state if no data
            if (allData.length === 0) {
                showEmptyState();
                updatePagination(0);
                return;
            }

            // Filter out RE status rows for display only
            const displayData = allData.filter(row => row.DOCSTATUS !== 'RE');

            // Show empty state if no displayable data
            if (displayData.length === 0) {
                showEmptyState();
                updatePagination(0);
                return;
            }

            const startIndex = (currentPage - 1) * rowsPerPage;
            const paginatedData = displayData.slice(startIndex, startIndex + rowsPerPage);

            paginatedData.forEach(row => {
                const tr = createTableRow(row);
                tableBody.appendChild(tr);
            });

            updatePagination(displayData.length);
        }

        // Calculate and show product summary
        function calculateAndShowSummary() {
            let stockInitial = 0;
            let totalEntree = 0;
            let totalSortie = 0;

            // Calculate totals from all data
            allData.forEach(row => {
                // Get stock initial from first row (they should all be the same for the same product)
                if (row.STOCKINITIAL && stockInitial === 0) {
                    stockInitial = parseFloat(row.STOCKINITIAL) || 0;
                }
                
                // Handle rows with DOCSTATUS 'RE' differently
                if (row.DOCSTATUS === 'RE') {
                    // RE rows don't count in total entree and total sortie
                    // But they affect stock initial
                    const entree = parseFloat(row.ENTREE) || 0;
                    const sortie = parseFloat(row.SORTIE) || 0;
                    
                    if (entree > 0) {
                        // If RE row is entree, add to stock initial
                        stockInitial += entree;
                    }
                    if (sortie > 0) {
                        // If RE row is sortie, subtract from stock initial
                        stockInitial -= sortie;
                    }
                } else {
                    // Normal rows: count in total entree and total sortie
                    totalEntree += parseFloat(row.ENTREE) || 0;
                    totalSortie += parseFloat(row.SORTIE) || 0;
                }
            });

            // Calculate stock final: Stock Initial + Total Entree - Total Sortie
            const stockFinal = stockInitial + totalEntree - totalSortie;

            // Format numbers with thousand separators
            const formatNumber = (num) => parseInt(num).toLocaleString('en-US');

            // Update the main display
            document.getElementById("stock-initial").textContent = formatNumber(stockInitial);
            document.getElementById("total-entree").textContent = formatNumber(totalEntree);
            document.getElementById("total-sortie").textContent = formatNumber(totalSortie);
            document.getElementById("stock-final").textContent = formatNumber(stockFinal);
            
            // Show/hide and update emplacement breakdown inline
            const breakdownSection = document.getElementById("emplacement-breakdown-inline");
            if (emplacementBreakdown && Object.keys(emplacementBreakdown).length > 0) {
                // Show emplacement breakdown section inside Stock Initial card
                breakdownSection.style.display = "block";
                
                // Update each emplacement stock with inline elements
                document.getElementById("preparation-stock-inline").textContent = 
                    formatNumber(emplacementBreakdown['Préparation'] || 0);
                document.getElementById("hangar-stock-inline").textContent = 
                    formatNumber(emplacementBreakdown['HANGAR'] || 0);
                document.getElementById("depot-hangar-stock-inline").textContent = 
                    formatNumber(emplacementBreakdown['Dépot Hangar réserve'] || 0);
                document.getElementById("depot-reserve-stock-inline").textContent = 
                    formatNumber(emplacementBreakdown['Dépot réserve'] || 0);
                    
                console.log("Updated inline emplacement breakdown display:", emplacementBreakdown);
            } else {
                // Hide emplacement breakdown section
                breakdownSection.style.display = "none";
            }
        }

        // Show loading state
        function showLoading(isLoading) {
            const tableBody = document.getElementById("data-table");
            
            if (isLoading) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="border px-4 py-8 dark:border-gray-600 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                <span class="text-gray-600 dark:text-gray-300">Loading data...</span>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        // Show empty state
        function showEmptyState() {
            const tableBody = document.getElementById("data-table");
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="border px-4 py-8 dark:border-gray-600 text-center">
                        <div class="flex flex-col items-center justify-center space-y-2">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V9a2 2 0 00-2-2H9a2 2 0 00-2 2v.01"></path>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-300 text-lg font-medium">No data found</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Try adjusting your filters or date range</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        // Create table row
        function createTableRow(row) {
            const tr = document.createElement("tr");
            tr.classList.add('table-row', 'dark:bg-gray-700');

            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                return date.toLocaleDateString('fr-FR');
            };            
            const formatNumber = (num) => {
                    // Convert null/undefined to 0, then format
                    const numberValue = num === null || num === undefined ? 0 : parseInt(num);
                    return numberValue.toLocaleString('en-US');
                };


            tr.innerHTML = `
                <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.MOVEMENTDATE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.DOCUMENTNO || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCTNAME || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.ENTREE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.SORTIE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOT || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOCATOR_FROM || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOCATOR_TO || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.DOCSTATUS || ''}</td>
            `;
            return tr;
        }

        // Update pagination
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
        }        // Export to Excel function
        function exportToExcel() {
            // Check if filters have been applied
            if (!filtersApplied) {
                alert('Please apply filters first before downloading Excel file.');
                return;
            }

            const product = document.getElementById('recap_product').value.trim() || null;
            const startDate = document.getElementById("start-date").value;
            const endDate = document.getElementById("end-date").value;

            let url = 'http://192.168.1.94:5000/download-stock-movement-excel?';
            if (startDate) url += `start_date=${startDate}&`;
            if (endDate) url += `end_date=${endDate}&`;
            if (product) url += `product=${encodeURIComponent(product)}&`;
            
            // Handle emplacement parameter properly
            if (selectedEmplacement !== null && selectedEmplacement !== undefined) {
                url += `emplacement=${encodeURIComponent(selectedEmplacement)}&`;
            } else {
                // Send empty string for default behavior (Préparation + HANGAR)
                url += `emplacement=&`;
            }

            if (url.endsWith('&')) url = url.slice(0, -1);
            window.location.href = url;
        }        // Product search functionality - no auto-fetch
        function setupProductSearch() {
            const productInput = document.getElementById("recap_product");
            const productDropdown = document.getElementById("product-dropdown");
            let searchCurrentPage = 1;
            const searchRowsPerPage = 10;
            let currentFilteredProducts = [];
            let isDropdownVisible = false;

            function clearProductSearch() {
                productInput.value = "";
                hideDropdown();
                // Don't auto-fetch, wait for Apply Filter button
            }

            function hideDropdown() {
                const tableContainer = document.getElementById("products-table-container");
                if (tableContainer) {
                    tableContainer.style.display = "none";
                }
                productDropdown.style.display = "none";
                isDropdownVisible = false;
            }

            function showDropdown() {
                const tableContainer = document.getElementById("products-table-container");
                if (tableContainer) {
                    tableContainer.style.display = "block";
                }
                productDropdown.style.display = "block";
                isDropdownVisible = true;
            }

            productInput.addEventListener("input", debounce(function () {
                const searchValue = this.value.trim().toLowerCase();
                if (searchValue) {
                    showProductDropdown(searchValue);
                } else {
                    hideDropdown();
                    currentFilteredProducts = [];
                }
            }, 100));

            // Only clear if field is empty when focused
            productInput.addEventListener("focus", function() {
                const searchValue = this.value.trim().toLowerCase();
                if (searchValue && currentFilteredProducts.length > 0) {
                    showDropdown();
                } else if (!searchValue) {
                    clearProductSearch();
                }
            });

            // Hide dropdown when clicking outside (but not inside the dropdown itself)
            document.addEventListener("click", function(event) {
                if (!productInput.contains(event.target) && !productDropdown.contains(event.target)) {
                    hideDropdown();
                }
            });

            // Prevent dropdown from closing when clicking inside it
            productDropdown.addEventListener("click", function(event) {
                event.stopPropagation();
            });

            function showProductDropdown(searchValue) {
                const tableContainer = document.getElementById("products-table-container");
                
                // If product list is not loaded yet, show loading message
                if (productList.length === 0) {
                    productDropdown.innerHTML = "";
                    showDropdown();
                    const loadingOption = document.createElement("div");
                    loadingOption.classList.add("dropdown-item");
                    loadingOption.style.fontStyle = "italic";
                    loadingOption.style.color = "#666";
                    loadingOption.textContent = "Loading products...";
                    productDropdown.appendChild(loadingOption);
                    
                    // Try to reload product list
                    loadProductList();
                    return;
                }

                // Filter products from cached list
                currentFilteredProducts = productList.filter(product => 
                    product && product.toLowerCase().includes(searchValue)
                );

                if (currentFilteredProducts.length === 0) {
                    hideDropdown();
                    return;
                }

                // Ensure table container is in the dropdown
                if (!productDropdown.contains(tableContainer)) {
                    productDropdown.innerHTML = "";
                    productDropdown.appendChild(tableContainer);
                }
                
                // Reset to first page
                searchCurrentPage = 1;
                
                // Show dropdown and render table
                showDropdown();
                renderSearchTable();
                
                // Setup pagination event listeners (only once)
                const prevPageBtn = document.getElementById("prev-page");
                const nextPageBtn = document.getElementById("next-page");
                
                if (!prevPageBtn.hasAttribute('data-listeners-attached')) {
                    prevPageBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (searchCurrentPage > 1) {
                            searchCurrentPage--;
                            renderSearchTable();
                        }
                    });
                    
                    nextPageBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const totalPages = Math.ceil(currentFilteredProducts.length / searchRowsPerPage);
                        if (searchCurrentPage < totalPages) {
                            searchCurrentPage++;
                            renderSearchTable();
                        }
                    });
                    
                    prevPageBtn.setAttribute('data-listeners-attached', 'true');
                    nextPageBtn.setAttribute('data-listeners-attached', 'true');
                }
            }

            function renderSearchTable() {
                const tableBody = document.getElementById("products-table-body");
                const prevPageBtn = document.getElementById("prev-page");
                const nextPageBtn = document.getElementById("next-page");
                const pageInfo = document.getElementById("page-info");
                
                if (!tableBody) return;
                
                // Clear table body
                tableBody.innerHTML = "";
                
                // Calculate pagination
                const totalProducts = currentFilteredProducts.length;
                const totalPages = Math.ceil(totalProducts / searchRowsPerPage);
                const startIndex = (searchCurrentPage - 1) * searchRowsPerPage;
                const endIndex = Math.min(startIndex + searchRowsPerPage, totalProducts);
                const currentPageProducts = currentFilteredProducts.slice(startIndex, endIndex);
                
                // Populate table
                currentPageProducts.forEach((product, index) => {
                    const row = document.createElement("tr");
                    
                    const numberCell = document.createElement("td");
                    numberCell.textContent = startIndex + index + 1;
                    row.appendChild(numberCell);
                    
                    const nameCell = document.createElement("td");
                    nameCell.textContent = product;
                    row.appendChild(nameCell);
                    
                    // Add click handler to select product
                    row.addEventListener("click", function(e) {
                        e.stopPropagation();
                        productInput.value = product;
                        hideDropdown();
                    });
                    
                    tableBody.appendChild(row);
                });
                
                // Update pagination controls
                if (prevPageBtn && nextPageBtn && pageInfo) {
                    prevPageBtn.disabled = searchCurrentPage === 1;
                    nextPageBtn.disabled = searchCurrentPage === totalPages || totalPages === 0;
                    pageInfo.textContent = `Page ${searchCurrentPage} of ${Math.max(1, totalPages)} (${totalProducts} results)`;
                }
            }
        }        // Dark/Light Mode Toggle (if theme.js is available)
        const htmlElement = document.documentElement;
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'true') {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }

        // Resizable columns functionality
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("th.resizable").forEach(function (th) {
                const resizer = th.querySelector(".resizer");

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
            });
        });

        // Sorting functionality
        let sortDirection = {};
        function sortTable(column) {
            if (!allData || allData.length === 0) return;
            
            // Toggle sort direction
            sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
            
            allData.sort((a, b) => {
                let valueA = a[column] || '';
                let valueB = b[column] || '';
                
                // Handle numeric columns
                if (['ENTREE', 'SORTIE'].includes(column)) {
                    valueA = parseFloat(valueA) || 0;
                    valueB = parseFloat(valueB) || 0;
                } else if (column === 'MOVEMENTDATE') {
                    valueA = new Date(valueA);
                    valueB = new Date(valueB);
                } else {
                    valueA = valueA.toString().toLowerCase();
                    valueB = valueB.toString().toLowerCase();
                }
                
                if (sortDirection[column] === 'asc') {
                    return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
                } else {
                    return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
                }
            });
            
            renderTable();
        }
    </script>
</body>
</html>
