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
    <title>Product Purchase Recap</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="recap_achat.css">
    <script src="theme.js"></script>
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
        }
        .month-table.active {
            display: block;
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
        }
        #yearSummaryContainer .table-container {
            margin-bottom: 0;
            width: 100%;
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
        #productSupplierContainer {
            transition: all 0.3s ease;
        }
        #recap_product_supplier {
            background-color: white;
            color: black;
        }
        .dark #recap_product_supplier {
            background-color: #374151;
            color: white;
            border-color: #4b5563;
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
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">Rotation Mensuelle des Achats</h1>
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 recap-grid">
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
                    <div class="mt-1 text-xs text-black-500">Hold Ctrl/Cmd to select multiple suppliers</div>
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
            <div id="yearSummaryContainer" class="mb-8">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Data Container with Year Tabs -->
        <div id="dataContainer" class="space-y-8">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>


    <script>



        // DOM Elements
        const elements = {
            applyBtn: document.getElementById('applyFilters'),
            resetBtn: document.getElementById('resetFilters'),
            inputs: {
                fournisseur: document.getElementById('recap_fournisseur'),
                product: document.getElementById('recap_product')
            },
            suggestionBoxes: {
                fournisseur: document.getElementById('fournisseur_suggestions'),
                product: document.getElementById('product_suggestions')
            },
            yearCheckboxes: document.querySelectorAll('.year-checkbox'),
            yearSummaryContainer: document.getElementById('yearSummaryContainer'),
            productSupplierContainer: document.getElementById('productSupplierContainer'),
            productSupplierSelect: document.getElementById('recap_product_supplier')
        };

        // Constants
        const API_ENDPOINTS = {
            download_pdf: 'http://192.168.1.94:5000/rotation_monthly_achat_pdf',
            fetchProductData: 'http://192.168.1.94:5000/rotation_monthly_achat',
            listFournisseur: 'http://192.168.1.94:5000/listfournisseur',
            listProduct: 'http://192.168.1.94:5000/listproduct',
            fetchSuppliersByProduct: 'http://192.168.1.94:5000/fetchSuppliersByProduct'
        };


        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        function formatNumber(num, locale = 'fr-FR') {
            return new Intl.NumberFormat(locale, {
                maximumFractionDigits: 2
            }).format(num);
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
            container.className = 'year-selector';
            
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
            // Update active tab
            document.querySelectorAll('.year-tab').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.year === year);
            });
            
            // Show tables for selected year
            document.querySelectorAll('.month-table').forEach(table => {
                table.classList.toggle('active', table.dataset.year === year);
            });
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
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                            <thead>
                                <tr class="table-header dark:bg-gray-700">
                                    <th class="border px-2 py-1">Month</th>
                                    <th class="border px-2 py-1 text-right">Qty</th>
                                    <th class="border px-2 py-1 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="dark:bg-gray-800">`;
                
                // Add rows for each month
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = yearData[monthNum] || { total: { QTY: 0, CHIFFRE: 0 } };
                    
                    tableHTML += `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border px-2 py-1">${monthNames[month - 1]}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(monthData.total.QTY)}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(monthData.total.CHIFFRE)}</td>
                        </tr>`;
                }
                
                // Add yearly total
                const yearlyTotal = {
                    QTY: Object.values(yearData)
                        .filter(month => month.total)
                        .reduce((sum, month) => sum + (month.total.QTY || 0), 0),
                    CHIFFRE: Object.values(yearData)
                        .filter(month => month.total)
                        .reduce((sum, month) => sum + (month.total.CHIFFRE || 0), 0)
                };
                
                tableHTML += `
                            <tr class="bg-blue-50 dark:bg-blue-900 font-semibold">
                                <td class="border px-2 py-1">TOTAL</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotal.QTY)}</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotal.CHIFFRE)}</td>
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
    const product = elements.inputs.product.value;

    if (!years.length || !fournisseurs.length) {
        document.getElementById('dataContainer').innerHTML = `
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100" role="alert">
                <span class="block sm:inline">Please select at least one year and one supplier</span>
            </div>`;
        elements.yearSummaryContainer.innerHTML = '';
        return;
    }

    showLoading(true);

    try {
        // Create URL with comma-separated suppliers
        let url = `${API_ENDPOINTS.fetchProductData}?years=${years.join(',')}&fournisseur=${fournisseurs.join(',')}`;
        if (product) {
            url += `&product=${encodeURIComponent(product)}`;
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
            
            // Create different organization based on number of suppliers
            if (fournisseurs.length === 1) {
                // Single supplier - show products directly
                const productMap = {};
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = yearData[monthNum] || { details: [] };
                    
                    monthData.details.forEach(item => {
                        if (!productMap[item.PRODUIT]) {
                            productMap[item.PRODUIT] = {
                                name: item.PRODUIT,
                                quantities: Array(12).fill(0),
                                amounts: Array(12).fill(0)
                            };
                        }
                        productMap[item.PRODUIT].quantities[month - 1] = item.QTY;
                        productMap[item.PRODUIT].amounts[month - 1] = item.CHIFFRE;
                    });
                }
                
                const yearTableContainer = document.createElement('div');
                yearTableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
                yearTableContainer.dataset.year = year;
                
                const products = Object.values(productMap);
                const tableContainer = createCombinedMonthlyTable(products, year);
                yearTableContainer.appendChild(tableContainer);
                container.appendChild(yearTableContainer);
                
            } else {
                // Multiple suppliers - create options for display
                const yearTableContainer = document.createElement('div');
                yearTableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
                yearTableContainer.dataset.year = year;
                
                // Option 1: Group by supplier (separate tables)
                const supplierGroups = {};
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = yearData[monthNum] || { details: [] };
                    
                    monthData.details.forEach(item => {
                        if (!supplierGroups[item.FOURNISSEUR]) {
                            supplierGroups[item.FOURNISSEUR] = new Map();
                        }
                        
                        if (!supplierGroups[item.FOURNISSEUR].has(item.PRODUIT)) {
                            supplierGroups[item.FOURNISSEUR].set(item.PRODUIT, {
                                name: item.PRODUIT,
                                quantities: Array(12).fill(0),
                                amounts: Array(12).fill(0)
                            });
                        }
                        
                        const productData = supplierGroups[item.FOURNISSEUR].get(item.PRODUIT);
                        productData.quantities[month - 1] = item.QTY;
                        productData.amounts[month - 1] = item.CHIFFRE;
                    });
                }
                
                // Option 2: Combined table with supplier info in product name
                const combinedProductMap = {};
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = yearData[monthNum] || { details: [] };
                    
                    monthData.details.forEach(item => {
                        const productKey = `${item.PRODUIT} (${item.FOURNISSEUR})`;
                        if (!combinedProductMap[productKey]) {
                            combinedProductMap[productKey] = {
                                name: productKey,
                                quantities: Array(12).fill(0),
                                amounts: Array(12).fill(0)
                            };
                        }
                        combinedProductMap[productKey].quantities[month - 1] = item.QTY;
                        combinedProductMap[productKey].amounts[month - 1] = item.CHIFFRE;
                    });
                }
                
                // Create tabs for different views
                const viewTabs = document.createElement('div');
                viewTabs.className = 'flex mb-4 border-b';
                
                const separateTab = document.createElement('button');
                separateTab.className = 'px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium';
                separateTab.textContent = 'By Supplier';
                separateTab.onclick = () => switchView('separate', year);
                
                const combinedTab = document.createElement('button');
                combinedTab.className = 'px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700';
                combinedTab.textContent = 'Combined View';
                combinedTab.onclick = () => switchView('combined', year);
                
                viewTabs.appendChild(separateTab);
                viewTabs.appendChild(combinedTab);
                yearTableContainer.appendChild(viewTabs);
                
                // Separate supplier tables
                const separateView = document.createElement('div');
                separateView.className = 'supplier-view-separate';
                separateView.dataset.year = year;
                
                Object.entries(supplierGroups).forEach(([supplier, productsMap]) => {
                    const supplierSection = document.createElement('div');
                    supplierSection.className = 'mb-8';
                    
                    const supplierHeader = document.createElement('h3');
                    supplierHeader.className = 'text-xl font-semibold mb-4 text-black bg-blue-100 dark:bg-blue-900 p-2 rounded';
                    supplierHeader.textContent = supplier;
                    supplierSection.appendChild(supplierHeader);
                    
                    const products = Array.from(productsMap.values());
                    const tableContainer = createCombinedMonthlyTable(products, year);
                    supplierSection.appendChild(tableContainer);
                    separateView.appendChild(supplierSection);
                });
                
                // Combined view
                const combinedView = document.createElement('div');
                combinedView.className = 'supplier-view-combined hidden';
                combinedView.dataset.year = year;
                
                const combinedProducts = Object.values(combinedProductMap);
                const combinedTableContainer = createCombinedMonthlyTable(combinedProducts, year);
                combinedView.appendChild(combinedTableContainer);
                
                yearTableContainer.appendChild(separateView);
                yearTableContainer.appendChild(combinedView);
                container.appendChild(yearTableContainer);
            }
        });
        
        elements.resetBtn.classList.remove('hidden');
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

// Add function to switch between views
function switchView(view, year) {
    const separateView = document.querySelector(`.supplier-view-separate[data-year="${year}"]`);
    const combinedView = document.querySelector(`.supplier-view-combined[data-year="${year}"]`);
    const separateTab = document.querySelector('.supplier-view-separate').parentElement.querySelector('button:first-child');
    const combinedTab = document.querySelector('.supplier-view-separate').parentElement.querySelector('button:last-child');
    
    if (view === 'separate') {
        separateView.classList.remove('hidden');
        combinedView.classList.add('hidden');
        separateTab.className = 'px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium';
        combinedTab.className = 'px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700';
    } else {
        separateView.classList.add('hidden');
        combinedView.classList.remove('hidden');
        separateTab.className = 'px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700';
        combinedTab.className = 'px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium';
    }
}

function createCombinedMonthlyTable(products, year) {
    const tableContainer = document.createElement('div');
    tableContainer.className = 'overflow-x-auto';

    // Create the table
    const table = document.createElement('table');
    table.className = 'min-w-full border-collapse text-sm';

    // Create header
    const thead = document.createElement('thead');
    const headerRow1 = document.createElement('tr');
    const headerRow2 = document.createElement('tr');

    // Product header cell
    const productHeader = document.createElement('th');
    productHeader.className = 'sticky left-0 z-10 bg-white dark:bg-gray-800 border px-4 py-2 text-left';
    productHeader.textContent = 'Product';
    productHeader.rowSpan = 2;
    headerRow1.appendChild(productHeader);

    // Month headers
    for (let month = 1; month <= 12; month++) {
        const monthHeader = document.createElement('th');
        monthHeader.className = 'border px-2 py-1 text-center bg-blue-50 dark:bg-blue-900 font-medium';
        monthHeader.colSpan = 2;
        monthHeader.textContent = monthNames[month - 1];
        headerRow1.appendChild(monthHeader);
    }

    // Qty/Amount subheaders
    for (let month = 1; month <= 12; month++) {
        const qtyHeader = document.createElement('th');
        qtyHeader.className = 'border px-2 py-1 text-center bg-blue-100 dark:bg-blue-800';
        qtyHeader.textContent = 'Qty';
        headerRow2.appendChild(qtyHeader);

        const amtHeader = document.createElement('th');
        amtHeader.className = 'border px-2 py-1 text-center bg-green-100 dark:bg-green-800';
        amtHeader.textContent = 'Amount';
        headerRow2.appendChild(amtHeader);
    }

    thead.appendChild(headerRow1);
    thead.appendChild(headerRow2);
    table.appendChild(thead);

    // Create table body
    const tbody = document.createElement('tbody');
    products.forEach(product => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';

        // Product name cell
        const nameCell = document.createElement('td');
        nameCell.className = 'sticky left-0 z-10 bg-white dark:bg-gray-800 border px-4 py-2';
        nameCell.textContent = product.name;
        row.appendChild(nameCell);

        // Data cells for each month
        for (let month = 0; month < 12; month++) {
            const qtyCell = document.createElement('td');
            qtyCell.className = 'border px-2 py-1 text-right bg-blue-50 dark:bg-blue-900/30';
            qtyCell.textContent = formatNumber(product.quantities[month] || 0);
            row.appendChild(qtyCell);

            const amtCell = document.createElement('td');
            amtCell.className = 'border px-2 py-1 text-right bg-green-50 dark:bg-green-900/30';
            amtCell.textContent = formatNumber(product.amounts[month] || 0);
            row.appendChild(amtCell);
        }

        tbody.appendChild(row);
    });

    // Create totals row
    const totalsRow = document.createElement('tr');
    totalsRow.className = 'font-bold bg-gray-100 dark:bg-gray-700';

    // Totals label
    const totalsLabel = document.createElement('td');
    totalsLabel.className = 'sticky left-0 z-10 bg-gray-100 dark:bg-gray-700 border px-4 py-2';
    totalsLabel.textContent = 'TOTAL';
    totalsRow.appendChild(totalsLabel);

    // Calculate and add totals for each month
    for (let month = 0; month < 12; month++) {
        const monthQtyTotal = products.reduce((sum, product) => sum + (product.quantities[month] || 0), 0);
        const monthAmtTotal = products.reduce((sum, product) => sum + (product.amounts[month] || 0), 0);

        const qtyTotalCell = document.createElement('td');
        qtyTotalCell.className = 'border px-2 py-1 text-right bg-blue-200 dark:bg-blue-900';
        qtyTotalCell.textContent = formatNumber(monthQtyTotal);
        totalsRow.appendChild(qtyTotalCell);

        const amtTotalCell = document.createElement('td');
        amtTotalCell.className = 'border px-2 py-1 text-right bg-green-200 dark:bg-green-900';
        amtTotalCell.textContent = formatNumber(monthAmtTotal);
        totalsRow.appendChild(amtTotalCell);
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
        const selectedValues = selectedOptions.map(option => option.value).filter(value => value !== '');
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

// Update product supplier selection event handler
elements.productSupplierSelect.addEventListener('change', function() {
    const selectedSuppliers = Array.from(this.selectedOptions).map(option => option.value);
    if (selectedSuppliers.length > 0) {
        elements.inputs.fournisseur.value = ''; // Clear the general supplier input
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
                allProducts = await response.json();
                
                elements.inputs.product.addEventListener('input', () => {
                    const value = elements.inputs.product.value.toLowerCase();
                    const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                    currentProductPage = 0;
                    showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                });
                
                // Handle product selection to load suppliers
                elements.inputs.product.addEventListener('change', async function() {
                    const product = this.value;
                    if (product) {
                        try {
                            elements.productSupplierSelect.disabled = true;
                            elements.productSupplierSelect.innerHTML = '<option value="">Loading suppliers...</option>';
                            
                            const response = await fetch(`${API_ENDPOINTS.fetchSuppliersByProduct}?product=${encodeURIComponent(product)}`);
                            const suppliers = await response.json();
                            
                            elements.productSupplierSelect.innerHTML = '<option value="">Select a supplier</option>';
                            if (suppliers.length > 0) {
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
                        elements.inputs.product.value = e.target.textContent;
                        elements.suggestionBoxes.product.classList.add('hidden');
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
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!elements.inputs.fournisseur.contains(e.target) && !elements.suggestionBoxes.fournisseur.contains(e.target)) {
                    elements.suggestionBoxes.fournisseur.classList.add('hidden');
                }
                if (!elements.inputs.product.contains(e.target) && !elements.suggestionBoxes.product.contains(e.target)) {
                    elements.suggestionBoxes.product.classList.add('hidden');
                }
            });
        }

        function resetFilters() {
            elements.yearCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            elements.inputs.fournisseur.value = '';
            elements.inputs.product.value = '';
            elements.productSupplierContainer.classList.add('hidden');

            document.getElementById('dataContainer').innerHTML = '';
            elements.resetBtn.classList.add('hidden');
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
        });


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
                const product = elements.inputs.product.value;

                // Validate required parameters
                if (!years.length || !fournisseurs.length) {
                    throw new Error('Please select at least one year and at least one supplier');
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
                let url = `${API_ENDPOINTS.download_pdf}?years=${years.join(',')}&fournisseur=${fournisseurs.join(',')}`;
                if (product) {
                    url += `&product=${encodeURIComponent(product)}`;
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

                    // Generate a descriptive filename
                    const timestamp = new Date().toISOString().split('T')[0];
                    const supplierText = fournisseurs.length > 1 ? `${fournisseurs.length}_suppliers` : fournisseurs[0];
                    const fileName = `purchase_recap_${supplierText}_${years.join('-')}_${product || 'all'}_${timestamp}.pdf`;
                    
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
    </script>
</body>
</html>