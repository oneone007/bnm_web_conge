<?php
session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// // Restrict access for certain roles
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Vente'])) {
//     header("Location: Acess_Denied");    
//     exit();
// }



$page_identifier = 'ETAT_F_CUMULE';

require_once 'check_permission.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État Fournisseur Cumulé</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="etatstock.css">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
        
        /* Custom dark mode styles for supplier summary */
        .dark #supplier-summary {
            background-color: rgb(31 41 55) !important; /* gray-800 */
            border-color: rgb(75 85 99) !important; /* gray-600 */
        }
        
        .dark #supplier-summary h3 {
            color: white !important;
        }
        
        /* Dark mode for summary cards */
        .dark #supplier-summary .bg-blue-50 {
            background-color: rgba(30, 58, 138, 0.4) !important; /* blue-900 with opacity */
            border-color: rgb(29 78 216) !important; /* blue-700 */
        }
        
        .dark #supplier-summary .bg-green-50 {
            background-color: rgba(20, 83, 45, 0.4) !important; /* green-900 with opacity */
            border-color: rgb(21 128 61) !important; /* green-700 */
        }
        
        .dark #supplier-summary .bg-red-50 {
            background-color: rgba(127, 29, 29, 0.4) !important; /* red-900 with opacity */
            border-color: rgb(185 28 28) !important; /* red-700 */
        }
        
        /* Dark mode text colors */
        .dark #supplier-summary .text-blue-600 {
            color: rgb(147 197 253) !important; /* blue-300 */
        }
        
        .dark #supplier-summary .text-blue-700 {
            color: rgb(196 181 253) !important; /* blue-200 */
        }
        
        .dark #supplier-summary .text-green-600 {
            color: rgb(134 239 172) !important; /* green-300 */
        }
        
        .dark #supplier-summary .text-green-700 {
            color: rgb(187 247 208) !important; /* green-200 */
        }
        
        .dark #supplier-summary .text-red-600 {
            color: rgb(252 165 165) !important; /* red-300 */
        }
        
        .dark #supplier-summary .text-red-700 {
            color: rgb(254 202 202) !important; /* red-200 */
        }

        /* Supplier dropdown styles */
        .supplier-dropdown-container {
            width: 100%;
            max-height: 300px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .supplier-dropdown {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .supplier-dropdown thead {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .supplier-dropdown th {
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-right: 1px solid #e5e7eb;
        }

        .supplier-dropdown th:last-child {
            border-right: none;
        }

        .supplier-dropdown tbody {
            max-height: 200px;
            overflow-y: auto;
        }

        .supplier-dropdown td {
            padding: 8px 12px;
            border-bottom: 1px solid #f3f4f6;
            border-right: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .supplier-dropdown td:last-child {
            border-right: none;
        }

        .supplier-dropdown tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Dark mode for supplier dropdown */
        .dark .supplier-dropdown-container {
            background-color: #374151;
            border-color: #4b5563;
        }

        .dark .supplier-dropdown thead {
            background-color: #4b5563;
            border-bottom-color: #6b7280;
        }

        .dark .supplier-dropdown th {
            color: #f9fafb;
            border-right-color: #6b7280;
        }

        .dark .supplier-dropdown td {
            color: #f9fafb;
            border-bottom-color: #4b5563;
            border-right-color: #4b5563;
        }

        .dark .supplier-dropdown tbody tr:hover {
            background-color: #4b5563;
        }

        /* Balance columns styling */
        .balance-positive {
            color: #059669; /* green-600 */
            font-weight: 600;
        }

        .balance-negative {
            color: #dc2626; /* red-600 */
            font-weight: 600;
        }

        .balance-zero {
            color: #6b7280; /* gray-500 */
        }

        .dark .balance-positive {
            color: #10b981; /* green-500 */
        }

        .dark .balance-negative {
            color: #ef4444; /* red-500 */
        }

        .dark .balance-zero {
            color: #9ca3af; /* gray-400 */
        }

        /* Export button styles */
        .Btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            background-color: #217346;
        }
        
        .svgWrapper {
            width: 100%;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .excelIcon {
            width: 24px;
            height: 24px;
        }
        
        .text {
            position: absolute;
            right: 0;
            width: 0;
            opacity: 0;
            color: white;
            font-size: 1.1em;
            font-weight: 600;
            transition: 0.3s;
            white-space: nowrap;
        }
        
        .Btn:hover {
            width: 140px;
            border-radius: 40px;
        }
        
        .Btn:hover .svgWrapper {
            width: 30%;
            padding-left: 20px;
        }
        
        .Btn:hover .text {
            opacity: 1;
            width: 70%;
            padding-right: 10px;
        }
        
        .Btn:active {
            transform: translate(2px, 2px);
        }

        /* Statistics card styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .dark .stat-card {
            background-color: #1f2937;
        }
    </style>
    <script src="theme.js" defer></script>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">
                État Fournisseur Cumulé
            </h1>
        </div>

        <!-- Date Filter Section -->
        <div class="filters-section mb-6 flex gap-4 items-center flex-wrap">
            <div class="date-filter">
                <label for="start-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                    Date Début:
                </label>
                <input type="date" id="start-date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition duration-150">
            </div>
            
            <div class="date-filter">
                <label for="end-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                    Date Fin:
                </label>
                <input type="date" id="end-date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition duration-150">
            </div>
        </div>

        <!-- Supplier Search Section -->
        <div class="search-container relative mb-6" style="max-width: 700px; width: 100%;">
            <label for="supplier-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                <i class="fas fa-truck text-purple-600 mr-1"></i>
                Fournisseur:
            </label>
            <input type="text" id="supplier-search" placeholder="Rechercher un fournisseur..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <div id="supplier-dropdown" class="dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; margin-top: 4px;">
                <div class="supplier-dropdown-container" id="supplier-dropdown-container">
                    <table class="supplier-dropdown" id="supplier-dropdown-table">
                        <thead>
                            <tr>
                                <th>Nom du Fournisseur</th>
                            </tr>
                        </thead>
                        <tbody id="supplier-dropdown-body">
                            <!-- Suppliers will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Apply Filter Button -->
        <div class="flex gap-4 mb-4">
            <button id="applyFilterButton" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <i class="fas fa-search mr-2"></i>
                Appliquer les Filtres
            </button>
            
            <button class="Btn" id="exportExcel">
                <div class="svgWrapper">
                    <img src="assets/excel.png" alt="Excel Icon" class="excelIcon" />
                    <div class="text">&nbsp;Excel</div>
                </div>
            </button>
            
            <button class="Btn" id="exportPDF" style="background-color: #dc3545;">
                <div class="svgWrapper">
                    <i class="fas fa-file-pdf text-white"></i>
                    <div class="text">&nbsp;PDF</div>
                </div>
            </button>
        </div>

        <!-- Supplier Summary Section (hidden by default) -->
        <div id="supplier-summary" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-4 border border-gray-200 dark:border-gray-700" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                Résumé Fournisseur
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-200 dark:border-blue-700">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-play-circle text-blue-600 dark:text-blue-400 text-xl mr-2"></i>
                        <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Solde Début</div>
                    </div>
                    <div id="solde-debut" class="text-xl font-bold text-blue-700 dark:text-blue-300">0.00 DA</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/30 p-3 rounded-lg border border-green-200 dark:border-green-700">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exchange-alt text-green-600 dark:text-green-400 text-xl mr-2"></i>
                        <div class="text-sm text-green-600 dark:text-green-400 font-medium">Total Transactions</div>
                    </div>
                    <div id="total-transactions" class="text-xl font-bold text-green-700 dark:text-green-300">0</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/30 p-3 rounded-lg border border-red-200 dark:border-red-700">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-stop-circle text-red-600 dark:text-red-400 text-xl mr-2"></i>
                        <div class="text-sm text-red-600 dark:text-red-400 font-medium">Solde Fin</div>
                    </div>
                    <div id="solde-fin" class="text-xl font-bold text-red-700 dark:text-red-300">0.00 DA</div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">ÉTAT FOURNISSEUR CUMULÉ</h2>
                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th data-column="DATETRX" onclick="sortTable('DATETRX')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                                DATE
                                <div class="resizer"></div>
                            </th>
                            <th data-column="DOC_ID" onclick="sortTable('DOC_ID')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-file-invoice text-purple-600 mr-1"></i>
                                DOC. N°
                                <div class="resizer"></div>
                            </th>
                            <th data-column="N_BL" onclick="sortTable('N_BL')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-receipt text-orange-600 mr-1"></i>
                                N° BL
                                <div class="resizer"></div>
                            </th>
                            <th data-column="DOC_TYPE" onclick="sortTable('DOC_TYPE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-tags text-indigo-600 mr-1"></i>
                                TYPE DOC
                                <div class="resizer"></div>
                            </th>
                            <th data-column="DESCRIPTION" onclick="sortTable('DESCRIPTION')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-info-circle text-gray-600 mr-1"></i>
                                DESCRIPTION
                                <div class="resizer"></div>
                            </th>
                            <th data-column="MONTANT" onclick="sortTable('MONTANT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                MONTANT
                                <div class="resizer"></div>
                            </th>
                            <th data-column="BALANCE" onclick="sortTable('BALANCE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-balance-scale text-yellow-600 mr-1"></i>
                                BAL. EN COURS
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
    </div>

    <!-- Scripts -->
    <!-- API Configuration -->
    <script src="api_config.js"></script>
    <!-- jsPDF and jsPDF-AutoTable for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <!-- SheetJS for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        let allData = [];
        let currentPage = 1;
        const rowsPerPage = 25;
        let selectedSupplier = null;
        let supplierList = [];
        let runningBalance = 0;
        let openingBalance = 0;

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
            initializeFilters();
            loadSupplierList();
            setupEventListeners();
        });

        // Initialize filters
        function initializeFilters() {
            // Set default dates (current month)
            const endDate = new Date();
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - 1);
            
            document.getElementById("end-date").value = endDate.toISOString().split('T')[0];
            document.getElementById("start-date").value = startDate.toISOString().split('T')[0];
        }

        // Setup event listeners
        function setupEventListeners() {
            // Apply Filter button
            document.getElementById("applyFilterButton").addEventListener("click", () => {
                if (!selectedSupplier) {
                    alert("Veuillez sélectionner un fournisseur.");
                    return;
                }
                
                fetchSupplierTransactions();
            });

            document.getElementById('exportExcel').addEventListener('click', exportToExcel);
            document.getElementById('exportPDF').addEventListener('click', exportToPDF);
            
            setupSupplierSearch();
        }
        

        

        // Load supplier list
        async function loadSupplierList() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl("/listfournisseur_etat"));
                if (!response.ok) throw new Error("Failed to load suppliers");
                
                const suppliers = await response.json();
                supplierList = suppliers || [];
                
            } catch (error) {
                console.error("Error loading supplier list:", error);
                supplierList = [];
            }
        }

        // Setup supplier search functionality
        function setupSupplierSearch() {
            const searchInput = document.getElementById("supplier-search");
            const dropdown = document.getElementById("supplier-dropdown");
            const dropdownBody = document.getElementById("supplier-dropdown-body");

            // Show dropdown on focus
            searchInput.addEventListener("focus", () => {
                if (supplierList.length > 0) {
                    populateSupplierDropdown(supplierList);
                    dropdown.style.display = "block";
                }
            });

            // Filter suppliers as user types
            searchInput.addEventListener("input", debounce(() => {
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                if (searchTerm === "") {
                    populateSupplierDropdown(supplierList);
                } else {
                    const filteredSuppliers = supplierList.filter(supplier => 
                        supplier.name.toLowerCase().includes(searchTerm)
                    );
                    populateSupplierDropdown(filteredSuppliers);
                }
                
                dropdown.style.display = supplierList.length > 0 ? "block" : "none";
            }, 300));

            // Hide dropdown when clicking outside
            document.addEventListener("click", (e) => {
                if (!e.target.closest(".search-container")) {
                    dropdown.style.display = "none";
                }
            });

            // Handle supplier selection
            dropdownBody.addEventListener("click", (e) => {
                const row = e.target.closest("tr");
                if (row) {
                    const supplierId = row.getAttribute("data-supplier-id");
                    const supplierName = row.getAttribute("data-supplier-name");
                    
                    selectedSupplier = {
                        id: supplierId,
                        name: supplierName
                    };
                    
                    searchInput.value = supplierName;
                    dropdown.style.display = "none";
                    
                   
                }
            });
        }

        // Populate supplier dropdown
        function populateSupplierDropdown(suppliers) {
            const dropdownBody = document.getElementById("supplier-dropdown-body");
            dropdownBody.innerHTML = "";

            if (suppliers.length === 0) {
                dropdownBody.innerHTML = '<tr><td class="text-center">Aucun fournisseur trouvé</td></tr>';
                return;
            }

            suppliers.slice(0, 50).forEach(supplier => { // Limit to 50 results
                const row = document.createElement("tr");
                row.setAttribute("data-supplier-id", supplier.id);
                row.setAttribute("data-supplier-name", supplier.name);
                row.innerHTML = `
                    <td>${supplier.name}</td>
                `;
                dropdownBody.appendChild(row);
            });
        }

        // Fetch opening balance
        async function fetchOpeningBalance() {
            if (!selectedSupplier) {
                return 0;
            }

            try {
                const url = new URL(API_CONFIG.getApiUrl("/sold_initial_etat_cum"));
                
                const startDate = document.getElementById("start-date").value;
                
                // Convert date format from YYYY-MM-DD to DD-MM-YYYY for the API
                const formatDateForAPI = (dateString) => {
                    const [year, month, day] = dateString.split('-');
                    return `${day}-${month}-${year}`;
                };
                
                url.searchParams.append("c_bpartner_id", selectedSupplier.id);
                url.searchParams.append("start_date", formatDateForAPI(startDate));

               

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();

                
                return parseFloat(data.opening_balance || 0);
                
            } catch (error) {
                console.error("Error fetching opening balance:", error);
                return 0;
            }
        }

        // Fetch supplier transactions
        async function fetchSupplierTransactions() {
            if (!selectedSupplier) {
                console.error("No supplier selected");
                return;
            }

            try {
                showLoading(true);
                
                // Fetch opening balance first
                openingBalance = await fetchOpeningBalance();

                
                const url = new URL(API_CONFIG.getApiUrl("/fetch_etat_fournisseur_cumule"));
                
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                
                // Convert date format from YYYY-MM-DD to DD-MM-YYYY for the API
                const formatDateForAPI = (dateString) => {
                    const [year, month, day] = dateString.split('-');
                    return `${day}-${month}-${year}`;
                };
                
                url.searchParams.append("c_bpartner_id", selectedSupplier.id);
                url.searchParams.append("start_date", formatDateForAPI(startDate));
                url.searchParams.append("end_date", formatDateForAPI(endDate));

             

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
               
                
                allData = Array.isArray(data) ? data : [];
                
                // Calculate running balance
                calculateRunningBalance();
                
                // Update summary
                updateSupplierSummary();
                
                // Reset pagination
                currentPage = 1;
                renderTable();
                
            } catch (error) {
                console.error("Error fetching supplier transactions:", error);
                allData = [];
                renderTable();
                alert("Erreur lors du chargement des données: " + error.message);
            } finally {
                showLoading(false);
            }
        }

        // Calculate running balance for each transaction
        function calculateRunningBalance() {
            runningBalance = openingBalance; // Start with opening balance
            
            allData.forEach((transaction, index) => {
                runningBalance += parseFloat(transaction.MONTANT || 0);
                transaction.BALANCE = runningBalance;
            });
        }

        // Update supplier summary
        function updateSupplierSummary() {
            const summaryDiv = document.getElementById("supplier-summary");
            
            if (allData.length > 0 || openingBalance !== 0) {
                const totalTransactions = allData.length;
                const soldeDebut = openingBalance;
                const soldeFin = runningBalance;
                
                document.getElementById("solde-debut").textContent = `${soldeDebut.toLocaleString('fr-DZ')} DA`;
                document.getElementById("total-transactions").textContent = totalTransactions;
                document.getElementById("solde-fin").textContent = `${soldeFin.toLocaleString('fr-DZ')} DA`;
                
                summaryDiv.style.display = "block";
            } else {
                summaryDiv.style.display = "none";
            }
        }

        // Show/hide loading state
        function showLoading(show) {
            const button = document.getElementById("applyFilterButton");
            if (show) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Chargement...';
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-search mr-2"></i>Appliquer les Filtres';
            }
        }

        // Render table with pagination
        function renderTable() {
            const tableBody = document.getElementById("data-table");
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const pageData = allData.slice(startIndex, endIndex);

            tableBody.innerHTML = "";

            if (pageData.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            ${allData.length === 0 ? "Aucune donnée disponible. Sélectionnez un fournisseur et appliquez les filtres." : "Aucune donnée pour cette page."}
                        </td>
                    </tr>
                `;
            } else {
                pageData.forEach((row, index) => {
                    const actualIndex = startIndex + index;
                    const balance = parseFloat(row.BALANCE || 0);
                    const amount = parseFloat(row.MONTANT || 0);
                    
                    let balanceClass = "balance-zero";
                    if (balance > 0) balanceClass = "balance-positive";
                    else if (balance < 0) balanceClass = "balance-negative";
                    
                    const tableRow = document.createElement("tr");
                    tableRow.className = "hover:bg-gray-50 dark:hover:bg-gray-700";
                    tableRow.innerHTML = `
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${row.DATETRX || ""}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${row.DOC_ID || ""}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${row.N_BL || ""}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${row.DOC_TYPE || ""}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${row.DESCRIPTION || ""}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600 text-right">${amount.toLocaleString('fr-DZ')} DA</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-600 text-right ${balanceClass}">${balance.toLocaleString('fr-DZ')} DA</td>
                    `;
                    tableBody.appendChild(tableRow);
                });
            }

            updatePagination();
        }

        // Update pagination controls
        function updatePagination() {
            const totalPages = Math.ceil(allData.length / rowsPerPage);
            const pageIndicator = document.getElementById("pageIndicator");
            
            pageIndicator.textContent = `Page ${currentPage} of ${totalPages} (${allData.length} résultats)`;
            
            document.getElementById("firstPage").disabled = currentPage === 1;
            document.getElementById("prevPage").disabled = currentPage === 1;
            document.getElementById("nextPage").disabled = currentPage === totalPages || totalPages === 0;
            document.getElementById("lastPage").disabled = currentPage === totalPages || totalPages === 0;
        }

        // Pagination event listeners
        document.getElementById("firstPage").addEventListener("click", () => {
            currentPage = 1;
            renderTable();
        });

        document.getElementById("prevPage").addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const totalPages = Math.ceil(allData.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });

        document.getElementById("lastPage").addEventListener("click", () => {
            const totalPages = Math.ceil(allData.length / rowsPerPage);
            currentPage = totalPages;
            renderTable();
        });

        // Export to Excel functionality
        function exportToExcel() {
            if (allData.length === 0) {
                alert("Aucune donnée à exporter.");
                return;
            }

            // Prepare data for export
            const exportData = allData.map(row => ({
                'Date': row.DATETRX || '',
                'Doc. N°': row.DOC_ID || '',
                'N° BL': row.N_BL || '',
                'Type Doc': row.DOC_TYPE || '',
                'Description': row.DESCRIPTION || '',
                'Montant': parseFloat(row.MONTANT || 0),
                'Balance en Cours': parseFloat(row.BALANCE || 0)
            }));

            // Add summary row
            const summaryData = [
                {},
                {
                    'Date': 'RÉSUMÉ',
                    'Doc. N°': '',
                    'N° BL': '',
                    'Type Doc': '',
                    'Description': `Fournisseur: ${selectedSupplier ? selectedSupplier.name : 'N/A'}`,
                    'Montant': 'Total Transactions: ' + allData.length,
                    'Balance en Cours': ''
                },
                {
                    'Date': '',
                    'Doc. N°': '',
                    'N° BL': '',
                    'Type Doc': '',
                    'Description': 'Solde Début',
                    'Montant': openingBalance,
                    'Balance en Cours': ''
                },
                {
                    'Date': '',
                    'Doc. N°': '',
                    'N° BL': '',
                    'Type Doc': '',
                    'Description': 'Solde Fin',
                    'Montant': runningBalance,
                    'Balance en Cours': ''
                }
            ];

            const finalData = [...exportData, ...summaryData];

            // Create workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(finalData);

            // Auto-width columns
            const colWidths = [];
            Object.keys(exportData[0]).forEach(key => {
                colWidths.push({wch: Math.max(key.length, 15)});
            });
            ws['!cols'] = colWidths;

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, "État Fournisseur Cumulé");

            // Generate filename
            const date = new Date();
            const supplierName = selectedSupplier ? selectedSupplier.name.replace(/[^a-zA-Z0-9]/g, '_') : 'supplier';
            const filename = `etat_fournisseur_cumule_${supplierName}_${date.getFullYear()}_${(date.getMonth()+1).toString().padStart(2,'0')}_${date.getDate().toString().padStart(2,'0')}.xlsx`;

            // Save file
            XLSX.writeFile(wb, filename);
            alert('Export Excel terminé avec succès');
        }

        // Helper function to format currency for PDF (avoiding locale issues)
        function formatCurrencyForPDF(amount) {
            if (!amount || isNaN(amount)) return '0,00 DA';
            
            // Format number with French-style formatting (spaces for thousands, comma for decimals)
            const formattedNumber = Math.abs(amount).toFixed(2).replace('.', ',');
            const parts = formattedNumber.split(',');
            
            // Add spaces for thousands separator
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            
            const result = parts.join(',');
            return (amount < 0 ? '-' : '') + result + ' DA';
        }

        // Export to PDF functionality
        function exportToPDF() {
            if (allData.length === 0) {
                alert("Aucune donnée à exporter.");
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape'); // Use landscape orientation for better table layout

            // Add title
            doc.setFontSize(24);
            doc.setTextColor(102, 126, 234);
            doc.text('ÉTAT FOURNISSEUR CUMULÉ', doc.internal.pageSize.getWidth() / 2, 20, { align: 'center' });

            // Add supplier name and date range
            doc.setFontSize(16);
            doc.setTextColor(60, 60, 60);
            const supplierInfo = `Fournisseur: ${selectedSupplier ? selectedSupplier.name : 'N/A'}`;
            doc.text(supplierInfo, doc.internal.pageSize.getWidth() / 2, 30, { align: 'center' });

            const dateRange = `Période: ${document.getElementById("start-date").value} au ${document.getElementById("end-date").value}`;
            doc.text(dateRange, doc.internal.pageSize.getWidth() / 2, 37, { align: 'center' });

            // Add generation date
            const generatedDate = `Généré le: ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}`;
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(generatedDate, doc.internal.pageSize.getWidth() / 2, 44, { align: 'center' });

            // Add Statistics section
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('RÉSUMÉ FINANCIER', 14, 55);

            // Create statistics table
            const statsData = [
                ['Solde Début', formatCurrencyForPDF(openingBalance)],
                ['Total Transactions', allData.length.toString()],
                ['Solde Fin', formatCurrencyForPDF(runningBalance)],
                ['Variation', formatCurrencyForPDF(runningBalance - openingBalance)]
            ];

            doc.autoTable({
                body: statsData,
                startY: 60,
                theme: 'grid',
                styles: {
                    fontSize: 11,
                    cellPadding: 3,
                    textColor: [0, 0, 0]
                },
                columnStyles: {
                    0: { fontStyle: 'bold', fillColor: [240, 248, 255] },
                    1: { halign: 'right', fontStyle: 'bold' }
                },
                margin: { left: 14, right: 14 },
                tableWidth: 'auto'
            });

            // Get the Y position after the statistics table
            let finalY = doc.lastAutoTable.finalY + 15;

            // Add main data table title
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('DÉTAIL DES TRANSACTIONS', 14, finalY);

            // Prepare table data
            const tableData = allData.map(row => [
                row.DATETRX || '',
                row.DOC_ID || '',
                row.N_BL || '',
                row.DOC_TYPE || '',
                (row.DESCRIPTION || '').substring(0, 30) + ((row.DESCRIPTION || '').length > 30 ? '...' : ''), // Truncate long descriptions
                formatCurrencyForPDF(parseFloat(row.MONTANT || 0)),
                formatCurrencyForPDF(parseFloat(row.BALANCE || 0))
            ]);

            // Add main data table
            doc.autoTable({
                head: [['Date', 'Doc. N°', 'N° BL', 'Type Doc', 'Description', 'Montant', 'Balance en Cours']],
                body: tableData,
                startY: finalY + 5,
                theme: 'striped',
                styles: {
                    fontSize: 8,
                    cellPadding: 2,
                    overflow: 'linebreak'
                },
                headStyles: {
                    fillColor: [102, 126, 234],
                    textColor: 255,
                    fontStyle: 'bold',
                    fontSize: 9
                },
                alternateRowStyles: {
                    fillColor: [248, 249, 250]
                },
                columnStyles: {
                    0: { cellWidth: 25 }, // Date
                    1: { cellWidth: 25 }, // Doc N°
                    2: { cellWidth: 20 }, // N° BL
                    3: { cellWidth: 25 }, // Type Doc
                    4: { cellWidth: 45 }, // Description
                    5: { cellWidth: 30, halign: 'right' }, // Montant
                    6: { cellWidth: 35, halign: 'right' } // Balance
                },
                margin: { left: 14, right: 14 }
            });

            // Add footer with page numbers
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(100, 100, 100);
                doc.text(
                    `Page ${i} sur ${pageCount}`,
                    doc.internal.pageSize.getWidth() / 2,
                    doc.internal.pageSize.getHeight() - 10,
                    { align: 'center' }
                );
                
                // Add company name or system name in footer
                doc.text(
                    'BNM - Système de Gestion',
                    14,
                    doc.internal.pageSize.getHeight() - 10
                );
                
                // Add generation timestamp
                doc.text(
                    generatedDate,
                    doc.internal.pageSize.getWidth() - 14,
                    doc.internal.pageSize.getHeight() - 10,
                    { align: 'right' }
                );
            }

            // Generate filename
            const date = new Date();
            const supplierName = selectedSupplier ? selectedSupplier.name.replace(/[^a-zA-Z0-9]/g, '_') : 'supplier';
            const filename = `etat_fournisseur_cumule_${supplierName}_${date.getFullYear()}_${(date.getMonth()+1).toString().padStart(2,'0')}_${date.getDate().toString().padStart(2,'0')}.pdf`;

            // Save PDF
            doc.save(filename);
            alert('Export PDF terminé avec succès');
        }

        // Sort table functionality
        let sortColumn = "";
        let sortDirection = "asc";

        function sortTable(column) {
            if (sortColumn === column) {
                sortDirection = sortDirection === "asc" ? "desc" : "asc";
            } else {
                sortColumn = column;
                sortDirection = "asc";
            }

            allData.sort((a, b) => {
                let aVal = a[column] || "";
                let bVal = b[column] || "";

                // Handle numeric columns
                if (column === "MONTANT" || column === "BALANCE") {
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }

                if (aVal < bVal) return sortDirection === "asc" ? -1 : 1;
                if (aVal > bVal) return sortDirection === "asc" ? 1 : -1;
                return 0;
            });

            // Recalculate running balance after sorting
            if (sortColumn === "DATETRX") {
                calculateRunningBalance();
            }

            renderTable();
        }
    </script>
</body>
</html>
