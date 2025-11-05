// global.js - Unified JavaScript for rot_men_global.php

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
    achatYearSummaryContainer: document.getElementById('achatYearSummaryContainer'),
    venteYearSummaryContainer: document.getElementById('venteYearSummaryContainer'),
    achatDataContainer: document.getElementById('achatDataContainer'),
    venteDataContainer: document.getElementById('venteDataContainer'),
    productSupplierContainer: document.getElementById('productSupplierContainer'),
    zoneClientContainer: document.getElementById('zoneClientContainer'),
    productSupplierSelect: document.getElementById('recap_product_supplier'),
    zoneClientSelect: document.getElementById('recap_zone_client')
};

// Constants
const API_ENDPOINTS = {
    achat: {
        download_pdf: API_CONFIG.getApiUrl('/rotation_monthly_achat_pdf'),
        fetchProductData: API_CONFIG.getApiUrl('/rotation_monthly_achat'),
        listFournisseur: API_CONFIG.getApiUrl('/listfournisseur'),
        listProduct: API_CONFIG.getApiUrl('/fetch-rotation-product-data'),
        fetchSuppliersByProduct: API_CONFIG.getApiUrl('/fetchSuppliersByProduct')
    },
    vente: {
        download_pdf: API_CONFIG.getApiUrl('/rotation_monthly_vente_pdf'),
        fetchProductData: API_CONFIG.getApiUrl('/rotation_monthly_vente'),
        listFournisseur: API_CONFIG.getApiUrl('/listfournisseur'),
        listProduct: API_CONFIG.getApiUrl('/fetch-rotation-product-data'),
        listRegion: API_CONFIG.getApiUrl('/listregion'),
        listClient: API_CONFIG.getApiUrl('/listclient'),
        fetchZoneClients: API_CONFIG.getApiUrl('/fetchZoneClients')
    }
};

// Store mappings
let productMap = {};
let allFournisseurs = [];
let allProducts = [];
let allRegions = [];
let allClients = [];

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const ITEMS_PER_PAGE = 10;

function formatNumber(num, locale = 'fr-FR') {
    return new Intl.NumberFormat(locale, {
        maximumFractionDigits: 2
    }).format(num);
}

function showLoading(show) {
    document.getElementById('loading-animation').classList.toggle('hidden', !show);
    document.getElementById('achatDataContainer').classList.toggle('opacity-50', show);
    document.getElementById('venteDataContainer').classList.toggle('opacity-50', show);
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

function getSelectedClients() {
    const zoneClientSelect = elements.zoneClientSelect;
    const clientInput = elements.inputs.client;

    // Check if zone client dropdown is visible and has selections
    if (!elements.zoneClientContainer.classList.contains('hidden')) {
        const selectedOptions = Array.from(zoneClientSelect.selectedOptions);
        const selectedValues = selectedOptions.map(option => option.value).filter(value => value !== '');
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

// Achat Module
const achatModule = {
    createYearTabs(years) {
        const container = document.createElement('div');
        container.className = 'year-selector';

        years.forEach(year => {
            const tab = document.createElement('div');
            tab.className = 'year-tab';
            tab.textContent = year;
            tab.dataset.year = year;
            tab.addEventListener('click', () => this.switchYear(year));
            container.appendChild(tab);
        });

        // Activate first tab by default
        if (years.length > 0) {
            container.querySelector('.year-tab').classList.add('active');
        }

        return container;
    },

    switchYear(year) {
        // Update active tab
        document.querySelectorAll('#achatDataContainer .year-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.year === year);
        });

        // Show tables for selected year
        document.querySelectorAll('#achatDataContainer .month-table').forEach(table => {
            table.classList.toggle('active', table.dataset.year === year);
        });
    },

    updateYearSummaryTables(data, years) {
        const container = elements.achatYearSummaryContainer;
        container.innerHTML = '';

        years.forEach(year => {
            const yearData = data[year] || {};

            // Calculate totals
            let totalQty = 0;
            let totalAmount = 0;

            Object.values(yearData).forEach(product => {
                if (product.quantities) {
                    product.quantities.forEach(qty => totalQty += qty || 0);
                }
                if (product.amounts) {
                    product.amounts.forEach(amt => totalAmount += amt || 0);
                }
            });

            const summaryCard = document.createElement('div');
            summaryCard.className = 'bg-blue-50 dark:bg-blue-900 p-4 rounded-lg';
            summaryCard.innerHTML = `
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-2">${year}</h3>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Quantity:</span>
                        <span class="font-medium">${formatNumber(totalQty)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Amount:</span>
                        <span class="font-medium">${formatNumber(totalAmount)} DA</span>
                    </div>
                </div>
            `;
            container.appendChild(summaryCard);
        });
    },

    createCombinedMonthlyTable(products, year) {
        const tableContainer = document.createElement('div');
        tableContainer.className = 'table-container achat-section';

        const table = document.createElement('table');
        table.className = 'min-w-full border-collapse text-sm';

        const thead = document.createElement('thead');
        thead.className = 'sticky-header';
        const headerRow = document.createElement('tr');

        const productHeader = document.createElement('th');
        productHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
        productHeader.textContent = 'Product';
        headerRow.appendChild(productHeader);

        for (let month = 1; month <= 12; month++) {
            const monthHeader = document.createElement('th');
            monthHeader.className = 'border px-2 py-1 text-center bg-blue-50 dark:bg-blue-900 font-medium sticky-header compact-cell';
            monthHeader.innerHTML = `
                <div style="font-size: 0.7rem; color: #6b7280;">${monthNames[month-1]}</div>
                <div style="font-size: 0.7rem; color: #6b7280;">Qty | Amount</div>
            `;
            headerRow.appendChild(monthHeader);
        }

        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        products.forEach(product => {
            const row = document.createElement('tr');
            row.className = 'product-row';

            const nameCell = document.createElement('td');
            nameCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10 product-name';
            nameCell.textContent = product.name;
            row.appendChild(nameCell);

            for (let month = 0; month < 12; month++) {
                const qty = product.quantities[month] || 0;
                const amt = product.amounts[month] || 0;

                const cell = document.createElement('td');
                cell.className = 'border compact-cell text-center';

                cell.innerHTML = `
                    <div class="month-data">
                        <div class="qty-item">${formatNumber(qty)}</div>
                        <div class="separator">|</div>
                        <div class="total-item">${formatNumber(amt)}</div>
                    </div>
                `;
                row.appendChild(cell);
            }

            tbody.appendChild(row);
        });

        // Totals row
        const totalsRow = document.createElement('tr');
        totalsRow.className = 'font-bold bg-blue-100 dark:bg-blue-800 totals-row';

        const totalsLabel = document.createElement('td');
        totalsLabel.className = 'sticky-left bg-blue-100 dark:bg-blue-800 border px-4 py-2 z-10';
        totalsLabel.textContent = 'TOTAL';
        totalsRow.appendChild(totalsLabel);

        for (let month = 0; month < 12; month++) {
            const monthQtyTotal = products.reduce((sum, product) => sum + (product.quantities[month] || 0), 0);
            const monthAmtTotal = products.reduce((sum, product) => sum + (product.amounts[month] || 0), 0);

            const totalCell = document.createElement('td');
            totalCell.className = 'border compact-cell text-center bg-blue-200 dark:bg-blue-700';

            totalCell.innerHTML = `
                <div class="month-data">
                    <div class="qty-item">${formatNumber(monthQtyTotal)}</div>
                    <div class="separator">|</div>
                    <div class="total-item">${formatNumber(monthAmtTotal)}</div>
                </div>
            `;
            totalsRow.appendChild(totalCell);
        }

        tbody.appendChild(totalsRow);
        table.appendChild(tbody);
        tableContainer.appendChild(table);
        return tableContainer;
    }
};

// Vente Module
const venteModule = {
    createYearTabs(years) {
        const container = document.createElement('div');
        container.className = 'year-selector';

        years.forEach(year => {
            const tab = document.createElement('div');
            tab.className = 'year-tab';
            tab.textContent = year;
            tab.dataset.year = year;
            tab.addEventListener('click', () => this.switchYear(year));
            container.appendChild(tab);
        });

        if (years.length > 0) {
            container.querySelector('.year-tab').classList.add('active');
        }

        return container;
    },

    switchYear(year) {
        document.querySelectorAll('#venteDataContainer .year-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.year === year);
        });

        document.querySelectorAll('#venteDataContainer .month-table').forEach(table => {
            table.classList.toggle('active', table.dataset.year === year);
        });
    },

    updateYearSummaryTables(data, years) {
        const container = elements.venteYearSummaryContainer;
        container.innerHTML = '';

        years.forEach(year => {
            const yearData = data[year] || {};

            let totalQty = 0;
            let totalAmount = 0;
            let totalMarge = 0;

            Object.values(yearData).forEach(product => {
                if (product.quantities) {
                    product.quantities.forEach(qty => totalQty += qty || 0);
                }
                if (product.amounts) {
                    product.amounts.forEach(amt => totalAmount += amt || 0);
                }
                if (product.marges) {
                    product.marges.forEach(marge => totalMarge += marge || 0);
                }
            });

            const summaryCard = document.createElement('div');
            summaryCard.className = 'bg-green-50 dark:bg-green-900 p-4 rounded-lg';
            summaryCard.innerHTML = `
                <h3 class="text-lg font-semibold text-green-600 dark:text-green-400 mb-2">${year}</h3>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Quantity:</span>
                        <span class="font-medium">${formatNumber(totalQty)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Amount:</span>
                        <span class="font-medium">${formatNumber(totalAmount)} DA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Margin:</span>
                        <span class="font-medium">${formatNumber(totalMarge)} DA</span>
                    </div>
                </div>
            `;
            container.appendChild(summaryCard);
        });
    },

    createProductSupplierTable(products, year) {
        const tableContainer = document.createElement('div');
        tableContainer.className = 'table-container vente-section';

        const table = document.createElement('table');
        table.className = 'min-w-full border-collapse text-sm';

        const thead = document.createElement('thead');
        thead.className = 'sticky-header';
        const headerRow = document.createElement('tr');

        const productHeader = document.createElement('th');
        productHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
        productHeader.textContent = 'Product';
        headerRow.appendChild(productHeader);

        const supplierHeader = document.createElement('th');
        supplierHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
        supplierHeader.style.left = '200px';
        supplierHeader.textContent = 'Supplier';
        headerRow.appendChild(supplierHeader);

        for (let month = 1; month <= 12; month++) {
            const monthHeader = document.createElement('th');
            monthHeader.className = 'border px-2 py-1 text-center bg-green-50 dark:bg-green-900 font-medium sticky-header compact-cell';
            monthHeader.innerHTML = `
                <div style="font-size: 0.7rem; color: #6b7280;">${monthNames[month-1]}</div>
                <div style="font-size: 0.7rem; color: #6b7280;">Qty | Total | Marge</div>
            `;
            headerRow.appendChild(monthHeader);
        }

        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        products.forEach(product => {
            const row = document.createElement('tr');
            row.className = 'product-row';

            const nameCell = document.createElement('td');
            nameCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10 product-name';
            nameCell.textContent = product.name;
            row.appendChild(nameCell);

            const supplierCell = document.createElement('td');
            supplierCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
            supplierCell.style.left = '200px';
            supplierCell.textContent = product.supplier || '';
            row.appendChild(supplierCell);

            for (let month = 0; month < 12; month++) {
                const qty = product.quantities[month] || 0;
                const amt = product.amounts[month] || 0;
                const marge = product.marges[month] || 0;

                const cell = document.createElement('td');
                cell.className = 'border compact-cell text-center';

                cell.innerHTML = `
                    <div class="month-data">
                        <div class="qty-item">${formatNumber(qty)}</div>
                        <div class="separator">|</div>
                        <div class="total-item">${formatNumber(amt)}</div>
                        <div class="separator">|</div>
                        <div class="marge-item">${formatNumber(marge)}</div>
                    </div>
                `;
                row.appendChild(cell);
            }

            tbody.appendChild(row);
        });

        // Totals row
        const totalsRow = document.createElement('tr');
        totalsRow.className = 'font-bold bg-green-100 dark:bg-green-800 totals-row';

        const totalsLabel = document.createElement('td');
        totalsLabel.className = 'sticky-left bg-green-100 dark:bg-green-800 border px-4 py-2 z-10';
        totalsLabel.textContent = 'TOTAL';
        totalsRow.appendChild(totalsLabel);

        const emptySupplierCell = document.createElement('td');
        emptySupplierCell.className = 'sticky-left bg-green-100 dark:bg-green-800 border px-4 py-2 z-10';
        emptySupplierCell.style.left = '200px';
        emptySupplierCell.textContent = '';
        totalsRow.appendChild(emptySupplierCell);

        for (let month = 0; month < 12; month++) {
            const monthQtyTotal = products.reduce((sum, product) => sum + (product.quantities[month] || 0), 0);
            const monthAmtTotal = products.reduce((sum, product) => sum + (product.amounts[month] || 0), 0);
            const monthMargeTotal = products.reduce((sum, product) => sum + (product.marges[month] || 0), 0);

            const totalCell = document.createElement('td');
            totalCell.className = 'border compact-cell text-center bg-green-200 dark:bg-green-700';

            totalCell.innerHTML = `
                <div class="month-data">
                    <div class="qty-item">${formatNumber(monthQtyTotal)}</div>
                    <div class="separator">|</div>
                    <div class="total-item">${formatNumber(monthAmtTotal)}</div>
                    <div class="separator">|</div>
                    <div class="marge-item">${formatNumber(monthMargeTotal)}</div>
                </div>
            `;
            totalsRow.appendChild(totalCell);
        }

        tbody.appendChild(totalsRow);
        table.appendChild(tbody);
        tableContainer.appendChild(table);
        return tableContainer;
    }
};

// Main loadData function
async function loadData() {
    const years = getSelectedYears();
    const fournisseurs = getSelectedSuppliers();
    const clients = getSelectedClients();
    const productName = elements.inputs.product.value.trim().replace(/\*$/, ''); // Remove trailing *
    const zone = elements.inputs.zone.value;

    if (!years.length) {
        alert('Please select at least one year.');
        return;
    }

    showLoading(true);

    try {
        // Fetch Achat data
        let achatUrl = `${API_ENDPOINTS.achat.fetchProductData}?years=${years.join(',')}&fournisseur=${fournisseurs.join(',')}`;
        if (productName) achatUrl += `&product=${encodeURIComponent(productName)}`;

        const achatResponse = await fetch(achatUrl);
        const achatData = await achatResponse.json();

        // Fetch Vente data
        let venteUrl = `${API_ENDPOINTS.vente.fetchProductData}?years=${years.join(',')}&fournisseur=${fournisseurs.join(',')}&client=${clients.join(',')}`;
        if (productName) venteUrl += `&product=${encodeURIComponent(productName)}`;
        if (zone) venteUrl += `&zone=${encodeURIComponent(zone)}`;

        const venteResponse = await fetch(venteUrl);
        const venteData = await venteResponse.json();

        // Update year summary tables
        achatModule.updateYearSummaryTables(achatData, years);
        venteModule.updateYearSummaryTables(venteData, years);

        // Clear containers
        elements.achatDataContainer.innerHTML = '';
        elements.venteDataContainer.innerHTML = '';

        // Create year tabs and tables for Achat
        const achatYearTabs = achatModule.createYearTabs(years);
        elements.achatDataContainer.appendChild(achatYearTabs);

        years.forEach(year => {
            const yearSection = document.createElement('div');
            yearSection.className = 'month-table';
            yearSection.dataset.year = year;

            const yearData = achatData[year] || {};
            const products = Object.values(yearData);

            if (products.length > 0) {
                const table = achatModule.createCombinedMonthlyTable(products, year);
                yearSection.appendChild(table);
            } else {
                yearSection.innerHTML = '<p class="text-center text-gray-500 py-8">No data available for this year</p>';
            }

            elements.achatDataContainer.appendChild(yearSection);
        });

        // Create year tabs and tables for Vente
        const venteYearTabs = venteModule.createYearTabs(years);
        elements.venteDataContainer.appendChild(venteYearTabs);

        years.forEach(year => {
            const yearSection = document.createElement('div');
            yearSection.className = 'month-table';
            yearSection.dataset.year = year;

            const yearData = venteData[year] || {};
            const products = Object.values(yearData);

            if (products.length > 0) {
                const table = venteModule.createProductSupplierTable(products, year);
                yearSection.appendChild(table);
            } else {
                yearSection.innerHTML = '<p class="text-center text-gray-500 py-8">No data available for this year</p>';
            }

            elements.venteDataContainer.appendChild(yearSection);
        });

        elements.resetBtn.classList.remove('hidden');
    } catch (error) {
        console.error('Error loading data:', error);
        alert('Error loading data. Please try again.');
    } finally {
        showLoading(false);
    }
}

// Autocomplete initialization
async function initAutocomplete() {
    let currentFournisseurPage = 0;
    let currentProductPage = 0;
    let currentZonePage = 0;
    let currentClientPage = 0;
    
    // Dummy data for testing
    allFournisseurs = ['Supplier A', 'Supplier B', 'Supplier C'];
    allProducts = ['Product 1', 'Product 2', 'Product 3'];
    allRegions = ['Region 1', 'Region 2', 'Region 3'];
    allClients = ['Client A', 'Client B', 'Client C'];

    // Load fournisseurs
    try {
        const response = await fetch(API_ENDPOINTS.achat.listFournisseur);
        const data = await response.json();
        if (Array.isArray(data)) allFournisseurs = data;
    } catch (error) {
        console.error('Error loading fournisseurs:', error);
    }

    // Load products
    try {
        const response = await fetch(API_ENDPOINTS.achat.listProduct);
        const data = await response.json();
        if (Array.isArray(data)) allProducts = data;
    } catch (error) {
        console.error('Error loading products:', error);
    }

    // Load regions
    try {
        const response = await fetch(API_ENDPOINTS.vente.listRegion);
        const data = await response.json();
        if (Array.isArray(data)) allRegions = data;
    } catch (error) {
        console.error('Error loading regions:', error);
    }

    // Load clients
    try {
        const response = await fetch(API_ENDPOINTS.vente.listClient);
        const data = await response.json();
        if (Array.isArray(data)) allClients = data;
    } catch (error) {
        console.error('Error loading clients:', error);
    }    // Setup autocomplete for each input
    setupAutocomplete(elements.inputs.fournisseur, elements.suggestionBoxes.fournisseur, allFournisseurs, 'fournisseur');
    setupAutocomplete(elements.inputs.product, elements.suggestionBoxes.product, allProducts, 'product');
    setupAutocomplete(elements.inputs.zone, elements.suggestionBoxes.zone, allRegions, 'zone');
    setupAutocomplete(elements.inputs.client, elements.suggestionBoxes.client, allClients, 'client');
}

function setupAutocomplete(input, suggestionBox, data, type) {
    let currentPage = 0;

    input.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const filtered = data.filter(item => {
            if (typeof item === 'string') {
                return item.toLowerCase().includes(query);
            } else if (item && typeof item === 'object' && item.name) {
                return item.name.toLowerCase().includes(query);
            }
            return false;
        });
        showPaginatedSuggestions(filtered, 0, suggestionBox);
        currentPage = 0;
    });

    input.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            showPaginatedSuggestions(data, currentPage, suggestionBox);
        }
    });

    suggestionBox.addEventListener('click', function(e) {
        if (e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
            input.value = e.target.textContent;
            suggestionBox.classList.add('hidden');
            // Trigger change for dropdowns
            if (type === 'product') {
                loadProductSuppliers();
            } else if (type === 'zone') {
                loadZoneClients();
            }
        }
    });

    // Pagination
    suggestionBox.addEventListener('click', function(e) {
        if (e.target.classList.contains('pagination-prev')) {
            currentPage = Math.max(0, currentPage - 1);
            const query = input.value.toLowerCase();
            const filtered = data.filter(item => {
                if (typeof item === 'string') {
                    return item.toLowerCase().includes(query);
                } else if (item && typeof item === 'object' && item.name) {
                    return item.name.toLowerCase().includes(query);
                }
                return false;
            });
            showPaginatedSuggestions(filtered, currentPage, suggestionBox);
        } else if (e.target.classList.contains('pagination-next')) {
            currentPage++;
            const query = input.value.toLowerCase();
            const filtered = data.filter(item => {
                if (typeof item === 'string') {
                    return item.toLowerCase().includes(query);
                } else if (item && typeof item === 'object' && item.name) {
                    return item.name.toLowerCase().includes(query);
                }
                return false;
            });
            showPaginatedSuggestions(filtered, currentPage, suggestionBox);
        }
    });
}

function showPaginatedSuggestions(filteredItems, currentPage, suggestionBox) {
    const start = currentPage * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;
    const pageItems = filteredItems.slice(start, end);

    suggestionBox.innerHTML = '';

    if (pageItems.length === 0) {
        suggestionBox.classList.add('hidden');
        return;
    }

    pageItems.forEach(item => {
        const text = typeof item === 'string' ? item : item.name || '';
        const div = document.createElement('div');
        div.textContent = text;
        suggestionBox.appendChild(div);
    });

    // Pagination controls
    if (filteredItems.length > ITEMS_PER_PAGE) {
        const pagination = document.createElement('div');
        pagination.className = 'pagination-container';

        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn pagination-prev';
        prevBtn.textContent = 'Previous';
        prevBtn.disabled = currentPage === 0;
        pagination.appendChild(prevBtn);

        const info = document.createElement('div');
        info.className = 'pagination-info';
        info.textContent = `Page ${currentPage + 1} of ${Math.ceil(filteredItems.length / ITEMS_PER_PAGE)}`;
        pagination.appendChild(info);

        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn pagination-next';
        nextBtn.textContent = 'Next';
        nextBtn.disabled = end >= filteredItems.length;
        pagination.appendChild(nextBtn);

        suggestionBox.appendChild(pagination);
    }

    suggestionBox.classList.remove('hidden');
}

// Load suppliers for selected product
async function loadProductSuppliers() {
    const productName = elements.inputs.product.value.trim();
    if (!productName) return;

    try {
        const response = await fetch(`${API_ENDPOINTS.achat.fetchSuppliersByProduct}?product=${encodeURIComponent(productName)}`);
        const suppliers = await response.json();

        elements.productSupplierSelect.innerHTML = '<option value="">Select suppliers...</option>';
        suppliers.forEach(supplier => {
            const option = document.createElement('option');
            option.value = supplier;
            option.textContent = supplier;
            elements.productSupplierSelect.appendChild(option);
        });

        elements.productSupplierContainer.classList.remove('hidden');
    } catch (error) {
        console.error('Error loading suppliers:', error);
    }
}

// Load clients for selected zone
async function loadZoneClients() {
    const zoneName = elements.inputs.zone.value.trim();
    if (!zoneName) return;

    try {
        const response = await fetch(`${API_ENDPOINTS.vente.fetchZoneClients}?zone=${encodeURIComponent(zoneName)}`);
        const clients = await response.json();

        elements.zoneClientSelect.innerHTML = '<option value="">Select clients...</option>';
        clients.forEach(client => {
            const option = document.createElement('option');
            option.value = client;
            option.textContent = client;
            elements.zoneClientSelect.appendChild(option);
        });

        elements.zoneClientContainer.classList.remove('hidden');
    } catch (error) {
        console.error('Error loading clients:', error);
    }
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

    elements.achatDataContainer.innerHTML = '';
    elements.venteDataContainer.innerHTML = '';
    elements.achatYearSummaryContainer.innerHTML = '';
    elements.venteYearSummaryContainer.innerHTML = '';
    elements.resetBtn.classList.add('hidden');
}

// Event listeners
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

    // Product change to load suppliers
    elements.inputs.product.addEventListener('blur', loadProductSuppliers);

    // Zone change to load clients
    elements.inputs.zone.addEventListener('blur', loadZoneClients);

    // Multi-select setup
    setupCustomMultiSelect(elements.productSupplierSelect);
    setupCustomMultiSelect(elements.zoneClientSelect);
});

function setupCustomMultiSelect(select) {
    select.addEventListener('mousedown', function(e) {
        e.preventDefault();
        const option = e.target;
        if (option.tagName === 'OPTION') {
            option.selected = !option.selected;
        }
        return false;
    });

    select.addEventListener('click', function(e) {
        e.preventDefault();
        return false;
    });
}

// PDF Export
document.getElementById('exportPdf').addEventListener('click', async function() {
    const btn = this;
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner');
    const pdfIcon = btn.querySelector('.pdf-icon');
    const errorElement = document.getElementById('pdfError');

    try {
        btn.disabled = true;
        btnText.textContent = 'Generating PDF...';
        spinner.classList.remove('hidden');
        pdfIcon.classList.add('hidden');
        errorElement.classList.add('hidden');

        const years = getSelectedYears();
        const fournisseurs = getSelectedSuppliers();
        const clients = getSelectedClients();
        const productName = elements.inputs.product.value;
        const zone = elements.inputs.zone.value;

        let url = `${API_ENDPOINTS.achat.download_pdf}?years=${years.join(',')}&fournisseur=${fournisseurs.join(',')}&product=${encodeURIComponent(productName)}&clients=${clients.join(',')}&zone=${encodeURIComponent(zone)}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('PDF generation failed');

        const blob = await response.blob();
        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = downloadUrl;
        a.download = 'global_rotation_report.pdf';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(downloadUrl);

        btnText.textContent = 'Download PDF';
    } catch (error) {
        console.error('PDF export error:', error);
        errorElement.textContent = 'Failed to generate PDF. Please try again.';
        errorElement.classList.remove('hidden');
        btnText.textContent = 'Download PDF';
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
        pdfIcon.classList.remove('hidden');
    }
});