<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Comptable'])) {
    header("Location: Acess_Denied");    exit();
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="confirm_order.css">
    <script src="theme.js"></script>
    <style>


        /* Search Container Styles */
        .input__container--variant {
            background: linear-gradient(to bottom, #F3FFF9, #F3FFF9);
            border-radius: 30px;
            max-width: 34em;
            padding: 1em;
            box-shadow: 0em 1em 3em #beecdc64;
            display: flex;
            align-items: center;
            position: relative;
            margin: 0 auto 2rem auto;
        }

        .shadow__input--variant {
            filter: blur(25px);
            border-radius: 30px;
            background-color: #F3FFF9;
            opacity: 0.5;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .input__search--variant {
            width: 33em;
            border-radius: 13em;
            outline: none;
            border: none;
            padding: 0.8em;
            font-size: 1em;
            color: #002019;
            background-color: transparent;
            z-index: 1;
        }

        .input__search--variant::placeholder {
            color: #002019;
            opacity: 0.7;
        }

        .input__button__shadow--variant {
            border-radius: 15px;
            background-color: #07372C;
            padding: 10px;
            border: none;
            cursor: pointer;
            z-index: 1;
            transition: background-color 0.3s ease;
        }

        .input__button__shadow--variant:hover {
            background-color: #3C6659;
        }

        .input__button__shadow--variant svg {
            width: 1.5em;
            height: 1.5em;
        }

        /* Table Styles */
        .table-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            max-height: 70vh; /* Limit table height */
        }

        .table-header th {
            cursor: pointer;
            transition: background-color 0.2s ease;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-header th:hover {
            background-color: #e5e7eb;
        }

        .dark .table-header th:hover {
            background-color: #4b5563;
        }

        /* Enhanced table scrolling */
        .overflow-x-auto {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 60vh;
            border-radius: 8px;
        }

        /* Sort indicator styling */
        .table-header th svg.text-blue-500 {
            color: #3b82f6 !important;
        }

        /* Animation for loading states */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .input__container--variant {
                max-width: 90%;
            }
            
            .input__search--variant {
                width: 100%;
            }
            
            h1 {
                font-size: 2.5rem !important;
            }
        }
.dark .remise-badge {
    color: black !important;
}

        /* Force black text for remise badge in both modes */
        .remise-badge {
            color: black !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">


        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold dark:text-white mb-2">
                Simulation
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Gestion et simulation des commandes</p>
            <!-- <button id="show-reserved-btn" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">Afficher les documents réservés</button> -->
        </div>

        <!-- Search Section -->
        <div class="mb-8">
            <div class="input__container--variant">
                <div class="shadow__input--variant"></div>
                <span style="font-weight:600; font-size:1.1em; margin-right:0.5em;">BCCB</span>
                <input type="text" id="bccb_confirm" class="input__search--variant" placeholder="Entrer le numéro..." autocomplete="off" style="width:18em;" value="BCCB">
            <div class="text-center mb-2 text-sm text-gray-700 dark:text-gray-300">
                <span>🔎 Saisissez un numéro BCCB (ex: <b>BCCB***/2025 Or ORM</b>) puis cliquez sur OK pour voir la simulation du produit.</span>
            </div>
                <button id="bccb-search-btn" class="input__button__shadow--variant" type="button">
                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M4 9a5 5 0 1110 0A5 5 0 014 9zm5-7a7 7 0 104.2 12.6.999.999 0 00.093.107l3 3a1 1 0 001.414-1.414l-3-3a.999.999 0 00-.107-.093A7 7 0 009 2z" fill-rule="evenodd" fill="#FFF"></path>
                    </svg>
                </button>
            </div>

            <!-- Refresh Button -->
            <div class="flex justify-center mb-4">
                <button id="refresh-btn" class="p-3 bg-white dark:bg-gray-700 text-blue-500 dark:text-blue-400 rounded-full shadow-lg hover:shadow-xl border border-blue-500 dark:border-blue-400 transition duration-200 flex items-center justify-center hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
                    </svg>
                </button>
            </div>

            <!-- Update Info -->
            <div id="order-update-info" class="text-center text-sm  rounded-lg p-3 shadow-sm border border-gray-200">
                <div class="flex justify-center items-center space-x-4">
                    <span > Dernière mise à jour : <span id="last-update" class="font-semibold text-gray-900">--:--:--</span></span>
                    <span >|</span>
                    <span >Actualisation dans : <span id="countdown" class="font-semibold text-blue-600">10m 0s</span></span>
                </div>
            </div>
        </div>
        <!-- Main Orders Table -->
        <div class="table-container bg-white shadow-lg dark:bg-gray-800 mb-8 fade-in">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Simulation des Commandes</h2>
                    <div id="row-count" class="text-sm font-medium text-gray-600 dark:text-gray-400   px-3 py-1 rounded-full ">
                        Total: 0 commandes
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="table-header bg-gray-50 dark:bg-gray-700">
                                <th data-column="NDOCUMENT" onclick="sortorderconfirmedTable('NDOCUMENT')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    N° Document
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                                <th data-column="TIER" onclick="sortorderconfirmedTable('TIER')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    Tiers
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                                <th data-column="DATECOMMANDE" onclick="sortorderconfirmedTable('DATECOMMANDE')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    Date Commande
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                                <th data-column="VENDEUR" onclick="sortorderconfirmedTable('VENDEUR')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    Vendeur
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                                <th data-column="MARGE" onclick="sortorderconfirmedTable('MARGE')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    Marge
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                                <th data-column="MONTANT" onclick="sortorderconfirmedTable('MONTANT')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    Montant
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                                <th data-column="ORGANISATION" onclick="sortorderconfirmedTable('ORGANISATION')" class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">
                                    Organisation
                                    <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>
                                    </svg>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="order-confirmer-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Data rows will be inserted here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BCCB Product Details Table -->
        <div id="bccb-product-container" class="table-container bg-white shadow-lg dark:bg-gray-800 fade-in" style="display: none;">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h2a1 1 0 100-2H7z"/>
                    </svg>
                    Détails du Produit BCCB
                    <span id="selected-order-summary" class="ml-4 text-base font-normal text-gray-700 dark:text-gray-300"></span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="table-header bg-gray-50 dark:bg-gray-700">
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Produit</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Quantité</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Remise</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="confirmed-bccb-product-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Product data will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer spacing -->
        <div class="h-16"></div>
    </div>
        <script>
            // Enhanced functionality and improved user experience

// Global variables for table sorting
let currentOrderConfirmedSortColumn = null;
let currentOrderConfirmedSortDirection = 'asc';
let orderConfirmedData = [];

// Table sorting function
function sortorderconfirmedTable(column) {
    // Toggle sort direction if same column is clicked
    if (currentOrderConfirmedSortColumn === column) {
        currentOrderConfirmedSortDirection = currentOrderConfirmedSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentOrderConfirmedSortColumn = column;
        currentOrderConfirmedSortDirection = 'asc';
    }

    // Sort the data
    const sortedData = [...orderConfirmedData].sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];

        // Handle null/undefined values
        if (aVal === null || aVal === undefined) aVal = '';
        if (bVal === null || bVal === undefined) bVal = '';

        // Convert to string for comparison if not numbers
        if (typeof aVal !== 'number' && typeof bVal !== 'number') {
            aVal = String(aVal).toLowerCase();
            bVal = String(bVal).toLowerCase();
        }

        // Perform comparison
        if (aVal < bVal) return currentOrderConfirmedSortDirection === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentOrderConfirmedSortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    // Update table with sorted data
    updateOrderConfirmedTable(sortedData);

    // Update sort indicators
    updateSortIndicators(column, currentOrderConfirmedSortDirection);
}

// Update sort indicators in table headers
function updateSortIndicators(column, direction) {
    // Reset all sort indicators
    document.querySelectorAll('.table-header th svg').forEach(svg => {
        svg.innerHTML = '<path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z"/>';
        svg.classList.remove('text-blue-500');
    });

    // Update the clicked column's indicator
    const headerElement = document.querySelector(`th[data-column="${column}"] svg`);
    if (headerElement) {
        headerElement.classList.add('text-blue-500');
        if (direction === 'asc') {
            headerElement.innerHTML = '<path d="M5 12l5-5 5 5H5z"/>';
        } else {
            headerElement.innerHTML = '<path d="M5 8l5 5 5-5H5z"/>';
        }
    }
}

// Update table with data
function updateOrderConfirmedTable(data) {
    const tableBody = document.getElementById('order-confirmer-table');
    tableBody.innerHTML = '';

    let totalRow = null;
    let rowCount = 0;

    // Store the first row for summary display
    window.firstOrderRow = null;

    // Update the row count dynamically
    const updateRowCount = (count) => {
        const rowCountElement = document.getElementById('row-count');
        rowCountElement.innerHTML = `Total: ${count - 1} commandes`; // Subtract 1 for the "Total" row
    };

    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.classList.add("cursor-pointer", "hover:bg-gray-200", "dark:hover:bg-gray-700");

        if (row.ORGANISATION === 'Total') {
            tr.style.fontWeight = 'bold';
            totalRow = tr;
        }

        // Format number with space as thousand separator and comma for decimals
        const formatNumber = (num) => 
            num !== null ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num) : '';

        // Format date as DD/MM/YYYY
        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        };

        // Save the first non-total row for summary
        if (!window.firstOrderRow && row.ORGANISATION !== 'Total') {
            window.firstOrderRow = {
                NDOCUMENT: row.NDOCUMENT || '',
                TIER: row.TIER || '',
                DATECOMMANDE: formatDate(row.DATECOMMANDE),
                VENDEUR: row.VENDEUR || '',
                MARGE: row.MARGE !== null ? formatNumber(row.MARGE) + ' %' : '',
                MONTANT: formatNumber(row.MONTANT)
            };
        }

        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.NDOCUMENT || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.TIER || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${formatDate(row.DATECOMMANDE)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.VENDEUR || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.MARGE !== null ? formatNumber(row.MARGE) + ' %' : ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white font-medium">${formatNumber(row.MONTANT)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.ORGANISATION || ''}</td>
        `;

        // Increment row count
        rowCount++;

        // Make row selectable with improved styling
        tr.addEventListener("click", function () {
            document.querySelectorAll("#order-confirmer-table tr").forEach(r => r.classList.remove("bg-blue-100", "dark:bg-blue-900", "ring-2", "ring-blue-500")); 
            tr.classList.add("bg-blue-100", "dark:bg-blue-900", "ring-2", "ring-blue-500"); // Highlight selected row

            const searchInput = document.getElementById("bccb_confirm");
            searchInput.value = row.NDOCUMENT; // Fill search input
            // Directly trigger search for BCCB
            document.getElementById("bccb-search-btn").click();

            // Show summary for selected row
            showOrderSummary({
                NDOCUMENT: row.NDOCUMENT || '',
                TIER: row.TIER || '',
                DATECOMMANDE: formatDate(row.DATECOMMANDE),
                VENDEUR: row.VENDEUR || '',
                MARGE: row.MARGE !== null ? formatNumber(row.MARGE) + ' %' : '',
                MONTANT: formatNumber(row.MONTANT)
            });
        });

        if (row.ORGANISATION === 'Total') {
            totalRow = tr;
        } else {
            tableBody.appendChild(tr);
        }
    });

    // Update the row count display
    updateRowCount(rowCount);

    // If a totalRow exists, prepend it to the table
    if (totalRow) {
        tableBody.prepend(totalRow);
    }

    // Show summary for first row by default
    if (window.firstOrderRow) {
        showOrderSummary(window.firstOrderRow);
    }
}

// Show order summary next to BCCB product title
function showOrderSummary(order) {
    const summaryEl = document.getElementById('selected-order-summary');
    if (!order) {
        summaryEl.textContent = '';
        return;
    }
    summaryEl.innerHTML = `
        <span class="font-semibold">N° Document:</span> ${order.NDOCUMENT} &nbsp;|
        <span class="font-semibold">Tiers:</span> ${order.TIER} &nbsp;|
        <span class="font-semibold">Date Commande:</span> ${order.DATECOMMANDE} &nbsp;|
        <span class="font-semibold">Vendeur:</span> ${order.VENDEUR} &nbsp;|
        <span class="font-semibold">Marge:</span> ${order.MARGE} &nbsp;|
        <span class="font-semibold">Montant:</span> ${order.MONTANT}
    `;
}

// Enhanced document ready with loading improvements
document.addEventListener("DOMContentLoaded", function () {
    // Add loading state
    const mainTable = document.getElementById('order-confirmer-table');
    mainTable.innerHTML = `
        <tr>
            <td colspan="7" class="text-center p-8">
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mb-2"></div>
                    <span class="text-gray-500 dark:text-gray-400">Chargement des données...</span>
                </div>
            </td>
        </tr>
    `;
    
    fetchOrderConfirmed();

    const searchInput = document.getElementById("bccb_confirm");
    const bccbProductContainer = document.getElementById("bccb-product-container");

    // BCCB input: clear and hide table on click
    // BCCB input: only hide table on click, do not clear value
    searchInput.addEventListener("click", function () {
        bccbProductContainer.style.display = "none";
        bccbProductContainer.classList.remove("fade-in");
    });

    // Search button click: send BCCB to API and show table
    document.getElementById("bccb-search-btn").addEventListener("click", function () {
        const bccb = searchInput.value.trim();
        if (bccb) {
            fetchSimulationSingle(bccb);
            fetchBccbProduct(bccb);
        } else {
            bccbProductContainer.style.display = "none";
            bccbProductContainer.classList.remove("fade-in");
        }
    });

    // Enter key triggers search
    searchInput.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            document.getElementById("bccb-search-btn").click();
        }
    });

    // Show reserved documents button
    document.getElementById("show-reserved-btn").addEventListener("click", function () {
        fetchOrderConfirmed();
    });
// Fetch single simulation by ndocument and show in table
async function fetchSimulationSingle(ndocument) {
    try {
        const response = await fetch(`http://192.168.1.94:5000/simulation_all?ndocument=${encodeURIComponent(ndocument)}`);
        const data = await response.json();
        if (data && !data.error) {
            // Show as single row in table
            orderConfirmedData = [data];
            updateOrderConfirmedTable([data]);
        } else {
            // Show empty table or error
            updateOrderConfirmedTable([]);
        }
    } catch (error) {
        updateOrderConfirmedTable([]);
    }
}
});

// Enhanced refresh functionality
document.getElementById("refresh-btn").addEventListener("click", async function () {
    const button = this;
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = `
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-current"></div>
    `;
    button.disabled = true;
    
    try {
        await fetchOrderConfirmed();

        const searchInput = document.getElementById("bccb_confirm");
        const currentValue = searchInput.value;

        if (currentValue) {
            searchInput.value = currentValue;
            searchInput.dispatchEvent(new Event("input"));
        }

        console.log("✅ Data refreshed manually via refresh button.");
    } finally {
        // Restore button state
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
        }, 500);
    }
});


async function fetchOrderConfirmed() {
    try {
        const response = await fetch('http://192.168.1.94:5000/simulation');
        const data = await response.json();
        
        // Store data globally for sorting
        orderConfirmedData = data;
        
        // Update table with the fetched data
        updateOrderConfirmedTable(data);
        
    } catch (error) {
        console.error('Error fetching order confirmed:', error);
        const tableBody = document.getElementById('order-confirmer-table');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center p-8 text-red-500 dark:text-red-400">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Erreur lors du chargement des données
                    </div>
                </td>
            </tr>
        `;
    }
}


async function fetchBccbProduct(bccb) {
    if (!bccb) return;

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL("http://192.168.1.94:5000/fetchBCCBProduct");
    url.searchParams.append("bccb", bccb);
    url.searchParams.append("ad_org_id", "1000000"); 

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received BCCB Product Data:", data); // Debugging log

        updateBccbProductTable(data);

        // Show table with animation if data exists
        if (data.length > 0) {
            tableContainer.style.display = "block";
            tableContainer.classList.add("fade-in");
        }
    } catch (error) {
        console.error("Error fetching BCCB product data:", error);
    }
}

function updateBccbProductTable(data) {
    const tableBody = document.getElementById("confirmed-bccb-product-table");
    tableBody.innerHTML = ""; // Clear previous content

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500 dark:text-gray-400">
            <div class="flex flex-col items-center">
                <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
                </svg>
                Aucune donnée de produit disponible
            </div>
        </td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    data.forEach(row => {
        // Convert REMISE to a whole number percentage, default to 0%
        const remiseFormatted = row.REMISE ? Math.round(row.REMISE * 100) + "%" : "0%";

        const tr = document.createElement("tr");
        tr.classList.add("hover:bg-gray-50", "dark:hover:bg-gray-700", "transition-colors", "duration-150");
        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.PRODUCT || "N/A"}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">${row.QTY || "N/A"}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 remise-badge">
                    ${remiseFormatted}
                </span>
            </td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.MARGE || "N/A"}</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment);
}





let countdown = 600; // 10 minutes = 600 seconds
const countdownElement = document.getElementById('countdown');
const lastUpdateElement = document.getElementById('last-update');

// Update the timestamp
function updateTimestamp() {
    const now = new Date();
    lastUpdateElement.textContent = now.toLocaleTimeString('fr-FR');
}

// Main refresh loop
async function refreshOrderConfirmed() {
    await fetchOrderConfirmed();
    updateTimestamp();
    countdown = 600; // Reset to 10 minutes
}

// Countdown logic
setInterval(() => {
    countdown--;
    
    // Convert seconds to minutes and seconds for display
    const minutes = Math.floor(countdown / 60);
    const seconds = countdown % 60;
    const displayTime = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
    
    countdownElement.textContent = displayTime;

    if (countdown === 0) {
        refreshOrderConfirmed();
    }
}, 1000);

// Initial fetch
refreshOrderConfirmed();






        </script>

</body> 

</html>

