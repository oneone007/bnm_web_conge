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
        /* Added styles for grid layout */
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

        /* Added styles for year summary layout */
        #yearSummaryContainer {
            display: grid;
            grid-template-columns: repeat(4, 1fr);  /* Fixed 4 columns for years */
            gap: 1rem;
            margin-bottom: 2rem;
        }

        #yearSummaryContainer .table-container {
            margin-bottom: 0;
            width: 100%;  /* Ensure full width */
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

        /* Autocomplete suggestions styling */
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
            background-color: white; /* Keep white background in dark mode */
            color: #000; /* Keep text black in dark mode */
        }

        .dark .autocomplete-suggestions div:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">Product Purchase Recap</h1>
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
            
            <!-- Rest of your existing search controls -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 recap-grid">
                <div>
                    <label for="recap_fournisseur" class="block text-sm font-medium recap-label">Fournisseur</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_fournisseur" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="fournisseur_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>

                <div>
                    <label for="recap_product" class="block text-sm font-medium recap-label">Product</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_product" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="product_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
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
            yearSummaryContainer: document.getElementById('yearSummaryContainer')
        };

        // Constants
        const API_ENDPOINTS = {
            fetchProductData: API_CONFIG.getApiUrl('/fetchProductRecapAchat'),
            listFournisseur: API_CONFIG.getApiUrl('/listfournisseur'),
            listProduct: API_CONFIG.getApiUrl('/listproduct')
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
            const fournisseur = elements.inputs.fournisseur.value;
            const product = elements.inputs.product.value;

            if (!years.length || !fournisseur) {
                document.getElementById('dataContainer').innerHTML = `
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100" role="alert">
                        <span class="block sm:inline">Please select at least one year and a supplier</span>
                    </div>`;
                elements.yearSummaryContainer.innerHTML = '';
                return;
            }

            showLoading(true);

            try {
                let url = `${API_ENDPOINTS.fetchProductData}?years=${years.join(',')}&fournisseur=${encodeURIComponent(fournisseur)}`;
                if (product) {
                    url += `&product=${encodeURIComponent(product)}`;
                }

                const response = await fetch(url);
                const data = await response.json();

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
                
                // Create a grid container for all months
                const monthGrid = document.createElement('div');
                monthGrid.className = 'month-grid';
                container.appendChild(monthGrid);

                // Create tables for each month (1-12)
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthName = monthNames[month - 1];
                    
                    const monthSection = document.createElement('div');
                    monthSection.className = 'month-section';
                    
                    // Create a table for each year
                    years.forEach(year => {
                        const yearData = data[year] || {};
                        const monthData = yearData[monthNum] || { details: [], total: { QTY: 0, CHIFFRE: 0 } };
                        
                        const tableContainer = document.createElement('div');
                        tableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
                        tableContainer.dataset.year = year;
                        tableContainer.dataset.month = monthNum;
                        
                        const tableHTML = `
                            <div class="table-container flex-1 bg-white dark:bg-gray-800 rounded-lg shadow">
                                <div class="month-header">${monthName}</div>
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="text-left px-4 py-3 bg-gray-50 dark:bg-gray-700 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/2">Product</th>
                                            <th class="text-right px-4 py-3 bg-gray-50 dark:bg-gray-700 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Quantity</th>
                                            <th class="text-right px-4 py-3 bg-gray-50 dark:bg-gray-700 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Amount</th>
                                        </tr>
                                        <tr class="bg-blue-50 dark:bg-blue-900 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">Monthly Total (${year})</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">${formatNumber(monthData.total.QTY)}</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">${formatNumber(monthData.total.CHIFFRE)}</td>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        ${monthData.details.map(item => `
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 break-words">${item.PRODUIT}</td>
                                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">${formatNumber(item.QTY)}</td>
                                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">${formatNumber(item.CHIFFRE)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                        
                        tableContainer.innerHTML = tableHTML;
                        monthSection.appendChild(tableContainer);
                    });
                    
                    monthGrid.appendChild(monthSection);
                }
                
                elements.resetBtn.classList.remove('hidden');
            } catch (error) {
                document.getElementById('dataContainer').innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-100" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"> ${error.message}</span>
                    </div>`;
            } finally {
                showLoading(false);
            }
        }

        // Initialize autocomplete for fournisseur and product
        async function initAutocomplete() {
            // Load fournisseurs
            try {
                const response = await fetch(API_ENDPOINTS.listFournisseur);
                const fournisseurs = await response.json();
                
                elements.inputs.fournisseur.addEventListener('input', () => {
                    const value = elements.inputs.fournisseur.value.toLowerCase();
                    const filtered = fournisseurs.filter(f => f.toLowerCase().includes(value));
                    
                    if (filtered.length > 0) {
                        elements.suggestionBoxes.fournisseur.innerHTML = filtered.map(f => 
                            `<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">${f}</div>`
                        ).join('');
                        elements.suggestionBoxes.fournisseur.classList.remove('hidden');
                    } else {
                        elements.suggestionBoxes.fournisseur.classList.add('hidden');
                    }
                });
                
                elements.suggestionBoxes.fournisseur.addEventListener('click', (e) => {
                    if (e.target && e.target.textContent) {
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
                const products = await response.json();
                
                elements.inputs.product.addEventListener('input', () => {
                    const value = elements.inputs.product.value.toLowerCase();
                    const filtered = products.filter(p => p.toLowerCase().includes(value));
                    
                    if (filtered.length > 0) {
                        elements.suggestionBoxes.product.innerHTML = filtered.map(p => 
                            `<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">${p}</div>`
                        ).join('');
                        elements.suggestionBoxes.product.classList.remove('hidden');
                    } else {
                        elements.suggestionBoxes.product.classList.add('hidden');
                    }
                });
                
                elements.suggestionBoxes.product.addEventListener('click', (e) => {
                    if (e.target && e.target.textContent) {
                        elements.inputs.product.value = e.target.textContent;
                        elements.suggestionBoxes.product.classList.add('hidden');
                    }
                });
            } catch (error) {
                console.error('Error loading products:', error);
            }
            
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
            document.getElementById('dataContainer').innerHTML = '';
            elements.resetBtn.classList.add('hidden');
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize autocomplete
            initAutocomplete();
            
            // Set current year as default
            const currentYear = new Date().getFullYear();
            document.querySelector(`.year-checkbox[value="${currentYear}"]`).checked = true;
            
            // Add event listeners
            elements.applyBtn.addEventListener('click', loadData);
            elements.resetBtn.addEventListener('click', resetFilters);
        });
    </script>
</body>
</html>