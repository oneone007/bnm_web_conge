<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



$page_identifier = 'simuler';

require_once 'check_permission.php';

// // Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }


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
    <script src="api_config.js"></script>
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

        /* Product Search Dropdown Styles */
        .search-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto 1rem auto;
        }

        .search-container input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            background-color: white;
            transition: border-color 0.3s ease;
        }

        .search-container input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .dark .search-container input {
            background-color: #374151;
            border-color: #4b5563;
            color: white;
        }

        .dark .search-container input:focus {
            border-color: #60a5fa;
        }

        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .dark .dropdown {
            background: #374151;
            border-color: #4b5563;
        }

        .dropdown-item {
            padding: 10px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #f8fafc;
        }

        .dark .dropdown-item {
            border-bottom-color: #4b5563;
        }

        .dark .dropdown-item:hover {
            background-color: #4b5563;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        /* Manual product styling */
        tr[data-manual="true"] {
            border-left: 3px solid #10b981;
            background-color: rgba(16, 185, 129, 0.05);
        }

        .dark tr[data-manual="true"] {
            background-color: rgba(16, 185, 129, 0.1);
        }

        /* Delete button animation */
        .delete-btn {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        tr:hover .delete-btn {
            opacity: 1;
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
        </div>

        <!-- Product Search Section -->
        <div class="mb-8">
            <div class="text-center mb-4">
                <h2 class="text-2xl font-semibold dark:text-white mb-2">Recherche de Produits</h2>
                <p class="text-gray-600 dark:text-gray-400">Recherchez et ajoutez des produits manuellement aux détails BCCB</p>
            </div>

            <div class="search-container">
                <input type="text" id="product_search" placeholder="Rechercher un produit..." autocomplete="off">
                <div id="product-dropdown" class="dropdown"></div>
            </div>
        </div>

        <!-- Product Details Table (Initially Hidden) -->
        <div id="product-details-container" class="table-container bg-white shadow-lg dark:bg-gray-800 mb-8 fade-in" style="display: none;">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="product-details-title" class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        Détails du Produit
                    </h2>
                    <div class="flex space-x-2">
                        <button id="add-product-to-bccb" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                            </svg>
                            Ajouter au BCCB
                        </button>
                        <button id="close-product-details" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            Fermer
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="table-header bg-gray-50 dark:bg-gray-700">
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Fournisseur</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Produit</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Quantité</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Prix Achat</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Prix Revient</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Prix Vente</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Marge</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Lot</th>
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Emplacement</th>
                            </tr>
                        </thead>
                        <tbody id="product-details-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Product details will be inserted here -->
                        </tbody>
                    </table>
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
                                    Marge Simulation %
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
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Bonus</th>
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


    // Define which columns are numeric or date
    const numericColumns = ['MONTANT', 'MARGE', 'QTY'];
    const dateColumns = ['DATECOMMANDE'];

    const sortedData = [...orderConfirmedData].sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];

        // Handle null/undefined values
        if (aVal === null || aVal === undefined) aVal = '';
        if (bVal === null || bVal === undefined) bVal = '';

        // Numeric sort
        if (numericColumns.includes(column)) {
            aVal = parseFloat(aVal) || 0;
            bVal = parseFloat(bVal) || 0;
        } else if (dateColumns.includes(column)) {
            // Date sort
            aVal = aVal ? new Date(aVal) : new Date(0);
            bVal = bVal ? new Date(bVal) : new Date(0);
        } else {
            // String sort
            aVal = String(aVal).toLowerCase();
            bVal = String(bVal).toLowerCase();
        }

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

    // Initialize simulation data storage
    if (!window.simulationData) window.simulationData = [];

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
        } else {
            // Store simulation data for non-total rows
            window.simulationData[rowCount] = {
                montant: row.MONTANT || 0,
                marge: row.MARGE || 0,
                originalMarge: row.MARGE || 0
            };
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
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-center">
                <span class="marge-simulation-display font-semibold text-blue-600" data-row-index="${rowCount}" data-ndocument="${row.NDOCUMENT}">${row.MARGE !== null ? formatNumber(row.MARGE) + ' %' : ''}</span>
            </td>
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
                    <svg class="w-12 h-12 mb-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">Entrez un numéro BCCB pour commencer la simulation</span>
                </div>
            </td>
        </tr>
    `;
    
    // Removed fetchOrderConfirmed() - not needed for simulation

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

    // Product search functionality
    setupProductSearch();

    // Product details event listeners
    document.getElementById("close-product-details").addEventListener("click", function() {
        document.getElementById("product-details-container").style.display = "none";
    });

    document.getElementById("add-product-to-bccb").addEventListener("click", function() {
        addSelectedProductToBCCB();
    });

    // Show reserved documents button
    document.getElementById("show-reserved-btn").addEventListener("click", function () {
        // Removed fetchOrderConfirmed() - not needed for simulation
    });
// Fetch single simulation by ndocument and show in table
async function fetchSimulationSingle(ndocument) {
    try {
        const response = await fetch(API_CONFIG.getApiUrl(`/real_simulation_all?ndocument=${encodeURIComponent(ndocument)}`));
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


async function fetchBccbProduct(bccb) {
    if (!bccb) return;

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL(API_CONFIG.getApiUrl("/simulation_fetchBCCBProduct"));
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
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-8 text-gray-500 dark:text-gray-400">
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

    data.forEach((row, index) => {
        // Convert REMISE to a whole number percentage, default to 0%
        const remiseValue = row.REMISE ? Math.round(row.REMISE * 100) : 0;
        // Convert BONUS_VENTE to a whole number, default to 0
        const bonusValue = row.BONUS_VENTE ? Math.round(row.BONUS_VENTE) : 0;

        const tr = document.createElement("tr");
        tr.classList.add("hover:bg-gray-50", "dark:hover:bg-gray-700", "transition-colors", "duration-150");
        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.PRODUCT || "N/A"}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">${row.QTY || "N/A"}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">
                <input type="number" 
                       class="w-16 px-2 py-1 text-center border rounded remise-input bg-transparent"
                       value="${remiseValue}" 
                       min="0" 
                       max="100"
                       data-row-index="${index}"
                       onchange="updateMargin(${index})"
                       step="1">%
            </td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">
                <input type="number" 
                       class="w-16 px-2 py-1 text-center border rounded bonus-input bg-transparent"
                       value="${bonusValue}" 
                       min="0"
                       data-row-index="${index}"
                       onchange="updateMargin(${index})"
                       step="1">%
            </td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">
                <span class="marge-display font-semibold" data-row-index="${index}">${row.MARGE || "N/A"}%</span>
            </td>
        `;
        fragment.appendChild(tr);
        
        // Store original data for calculations
        if (!window.productData) window.productData = [];
        window.productData[index] = {
            priceentered: row.PRICEENTERED || 0,
            p_revient: row.P_REVIENT || 0,
            pricelist: row.PRICELIST || 0,

            remise_vente: row.REMISE_VENTE || 0,
            bonus_vente: row.BONUS_VENTE || 0,
            originalMarge: row.MARGE || 0
        };
    });

    tableBody.appendChild(fragment);
    
    // Update simulation margins based on loaded BCCB data
    setTimeout(() => {
        updateSimulationMargin();
    }, 100); // Small delay to ensure DOM is updated
}

// Function to calculate and update margin based on user input
function updateMargin(rowIndex) {
    const productInfo = window.productData[rowIndex];
    if (!productInfo) return;

    // Get current input values
    const remiseInput = document.querySelector(`input.remise-input[data-row-index="${rowIndex}"]`);
    const bonusInput = document.querySelector(`input.bonus-input[data-row-index="${rowIndex}"]`);
    const margeDisplay = document.querySelector(`span.marge-display[data-row-index="${rowIndex}"]`);

    if (!remiseInput || !bonusInput || !margeDisplay) return;

    // Get values from inputs (convert percentages to decimals)
    const newRemiseVente = parseFloat(remiseInput.value) || 0;
    const newBonusVente = parseFloat(bonusInput.value) || 0;

    // Use the original price entered and cost price for calculation
    const priceEntered = productInfo.priceentered;
    const pRevient = productInfo.p_revient;
    const pricelist = productInfo.pricelist;

    // Calculate ventef (final selling price) using the same formula as Python
    // ventef = (priceentered - ((priceentered * remise_vente) / 100)) / (1 + (bonus_vente / 100))
    const ventef = (pricelist - ((pricelist * newRemiseVente) / 100)) / (1 + (newBonusVente / 100));

    // Calculate margin: ((ventef - p_revient) / p_revient) * 100
    let newMarge = 0;
    if (pRevient && pRevient > 0) {
        newMarge = ((ventef - pRevient) / pRevient) * 100;
    }

    // Update the display
    margeDisplay.textContent = `${Math.round(newMarge * 100) / 100}%`;
    
    // Add visual feedback for changes
    margeDisplay.style.transition = 'all 0.3s ease';
    if (newMarge > productInfo.originalMarge) {
        margeDisplay.style.color = '#10B981'; // Green for increase
    } else if (newMarge < productInfo.originalMarge) {
        margeDisplay.style.color = '#EF4444'; // Red for decrease
    } else {
        margeDisplay.style.color = ''; // Default color
    }
    
    // Update simulation margins based on new BCCB values
    updateSimulationMargin();
}

// Function to calculate and update simulation margin for orders table based on BCCB product values
function updateSimulationMargin() {
    // Get all simulation margin displays
    const margeDisplays = document.querySelectorAll('span.marge-simulation-display');
    
    margeDisplays.forEach(margeDisplay => {
        const ndocument = margeDisplay.getAttribute('data-ndocument');
        if (!ndocument) return;
        
        // Calculate the new margin based on current BCCB product values
        calculateSimulationMargeBasedOnBCCB(ndocument, margeDisplay);
    });
}

// Calculate simulation margin for a specific order based on BCCB product details
function calculateSimulationMargeBasedOnBCCB(ndocument, margeDisplay) {
    // Check if BCCB product data exists for this document
    if (!window.productData || window.productData.length === 0) {
        return; // No BCCB data available
    }
    
    // Get the row index from the display element
    const rowIndex = margeDisplay.getAttribute('data-row-index');
    const orderInfo = window.simulationData[rowIndex];
    if (!orderInfo) return;
    
    console.log(`\n=== CALCULATING MARGIN FOR ${ndocument} ===`);
    console.log(`Original Order Margin: ${orderInfo.originalMarge || orderInfo.marge || 0}%`);
    
    // Group products by name to handle multiple entries of the same product
    let productGroups = new Map();
    let totalQuantity = 0; // Track total quantity for placement
    
    // Get all BCCB table rows to extract quantities
    const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
    const tableRows = bccbTableBody.querySelectorAll('tr');
    
    // First pass: Group products by name and collect their data
    window.productData.forEach((productInfo, index) => {
        if (!productInfo) return;
        
        // Get product name from table row
        let productName = 'Unknown';
        if (tableRows[index]) {
            const productCell = tableRows[index].cells[0]; // First column (product name)
            if (productCell) {
                productName = productCell.textContent.trim();
            }
        }
        
        // Get current remise and bonus values from BCCB table inputs
        const remiseInput = document.querySelector(`input.remise-input[data-row-index="${index}"]`);
        const bonusInput = document.querySelector(`input.bonus-input[data-row-index="${index}"]`);
        
        if (!remiseInput || !bonusInput) return;
        
        const remiseValue = parseFloat(remiseInput.value) || 0;
        const bonusValue = parseFloat(bonusInput.value) || 0;
        
        // Get quantity from the table row - second column
        let quantity = 1;
        if (tableRows[index]) {
            const qtyCell = tableRows[index].cells[1]; // Second column (index 1)
            if (qtyCell) {
                const qtyText = qtyCell.textContent.trim();
                quantity = parseFloat(qtyText) || 1;
            }
        }
        
        // Always add quantity to total placement regardless of remise
        totalQuantity += quantity;
        
        // Group products by name
        if (!productGroups.has(productName)) {
            productGroups.set(productName, []);
        }
        
        productGroups.get(productName).push({
            index: index,
            quantity: quantity,
            remiseValue: remiseValue,
            bonusValue: bonusValue,
            priceList: productInfo.pricelist || 0,
            costPrice: productInfo.p_revient || 0,
            originalMarge: productInfo.originalMarge || 0
        });
        
        console.log(`Product ${index} (${productName}): Qty=${quantity}, Remise=${remiseValue}%, Bonus=${bonusValue}%, PriceList=${productInfo.pricelist}, Cost=${productInfo.p_revient}`);
    });
    
    // Second pass: Calculate margin for each product group
    let totalNewCost = 0;
    let totalNewSelling = 0;
    let hasValidData = false;
    
    productGroups.forEach((productEntries, productName) => {
        console.log(`\n--- Processing Product Group: ${productName} ---`);
        
        let groupCost = 0;
        let groupSelling = 0;
        let groupQuantity = 0;
        let hasFreeProducts = false;
        let hasNormalProducts = false;
        
        productEntries.forEach(entry => {
            const { index, quantity, remiseValue, bonusValue, priceList, costPrice, originalMarge } = entry;
            
            // Skip products with extreme negative margins (like -100%)
            if (originalMarge <= -50) {
                console.log(`  Entry ${index}: Skipping with extreme negative margin: ${originalMarge}%`);
                return;
            }
            
            // Skip products with invalid pricing
            if (costPrice <= 0 || priceList <= 0) {
                console.log(`  Entry ${index}: Skipping with invalid pricing: CostPrice=${costPrice}, PriceList=${priceList}`);
                return;
            }
            
            groupQuantity += quantity;
            
            // Check if this is a free product (100% remise)
            if (remiseValue >= 100 || Math.abs(remiseValue - 100) < 0.01) {
                console.log(`  Entry ${index}: Free product (100% remise) - excluded from margin calculation`);
                hasFreeProducts = true;
                return; // Skip this entry for margin calculation
            }
            
            // Calculate effective selling price for normal products
            const effectiveSellingPrice = (priceList - ((priceList * remiseValue) / 100)) / (1 + (bonusValue / 100));
            
            groupSelling += effectiveSellingPrice * quantity;
            groupCost += costPrice * quantity;
            hasNormalProducts = true;
            
            console.log(`  Entry ${index}: INCLUDED - EffectivePrice=${effectiveSellingPrice.toFixed(2)}, TotalSelling=${(effectiveSellingPrice * quantity).toFixed(2)}, TotalCost=${(costPrice * quantity).toFixed(2)}`);
        });
        
        // Add group totals to overall calculation only if there are normal (non-free) products
        if (hasNormalProducts) {
            totalNewSelling += groupSelling;
            totalNewCost += groupCost;
            hasValidData = true;
            
            const groupMargin = groupCost > 0 ? ((groupSelling - groupCost) / groupCost) * 100 : 0;
            console.log(`  Group Summary: Qty=${groupQuantity}, Selling=${groupSelling.toFixed(2)}, Cost=${groupCost.toFixed(2)}, Margin=${groupMargin.toFixed(2)}%`);
            
            if (hasFreeProducts) {
                console.log(`  Note: This group has both normal and free products - only normal ones counted for margin`);
            }
        } else if (hasFreeProducts) {
            console.log(`  Group Summary: All products are free (100% remise) - excluded from margin calculation`);
        }
    });
    
    if (!hasValidData || totalNewCost <= 0) {
        console.log('No valid data for simulation calculation');
        return; // No valid data to calculate with
    }
    
    // Calculate the new margin percentage
    const newMargin = ((totalNewSelling - totalNewCost) / totalNewCost) * 100;
    
    console.log(`\n=== FINAL SIMULATION CALCULATION FOR ${ndocument} ===`);
    console.log(`Product Groups Processed: ${productGroups.size}`);
    console.log(`Total Placement Quantity: ${totalQuantity} (includes ALL products)`);
    console.log(`Margin Calculation (excludes 100% remise products):`);
    console.log(`  - Total Selling Price: ${totalNewSelling.toFixed(2)}`);
    console.log(`  - Total Cost Price: ${totalNewCost.toFixed(2)}`);
    console.log(`  - Calculated Margin: ${newMargin.toFixed(2)}%`);
    console.log(`  - Original Margin: ${orderInfo.originalMarge || orderInfo.marge || 0}%`);
    console.log(`  - Difference: ${(newMargin - (orderInfo.originalMarge || orderInfo.marge || 0)).toFixed(2)}%`);
    console.log(`================================================`);
    
    // Get the original margin for comparison
    const originalMargin = orderInfo.originalMarge || orderInfo.marge || 0;
    
    // Update the display
    margeDisplay.textContent = `${Math.round(newMargin * 100) / 100}%`;
    
    // Add visual feedback for changes
    margeDisplay.style.transition = 'all 0.3s ease';
    if (newMargin > originalMargin) {
        margeDisplay.style.color = '#10B981'; // Green for increase
    } else if (newMargin < originalMargin) {
        margeDisplay.style.color = '#EF4444'; // Red for decrease
    } else {
        margeDisplay.style.color = '#3B82F6'; // Blue for default
    }
}

// Product Search Functionality
let allProductsData = [];
let selectedProductData = null;

// Debounce function for search
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Setup product search functionality
function setupProductSearch() {
    const productInput = document.getElementById("product_search");
    const productDropdown = document.getElementById("product-dropdown");

    // Load products list on first focus
    let productsLoaded = false;
    productInput.addEventListener("focus", async function() {
        if (!productsLoaded) {
            await loadAllProducts();
            productsLoaded = true;
        }
    });

    // Search functionality with debounce
    productInput.addEventListener("input", debounce(function() {
        const searchValue = this.value.trim().toLowerCase();
        if (searchValue.length >= 2) {
            showProductDropdown(searchValue);
        } else {
            hideProductDropdown();
        }
    }, 300));

    // Hide dropdown when clicking outside
    document.addEventListener("click", function(e) {
        if (!productInput.contains(e.target) && !productDropdown.contains(e.target)) {
            hideProductDropdown();
        }
    });
}

// Load all products from API
async function loadAllProducts() {
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/simulation_listproduct'));
        if (!response.ok) throw new Error("Failed to load products");
        
        const data = await response.json();
        allProductsData = data;
        console.log("Products loaded:", allProductsData.length);
    } catch (error) {
        console.error("Error loading products:", error);
        allProductsData = [];
    }
}

// Show product dropdown with filtered results
function showProductDropdown(searchValue) {
    const dropdown = document.getElementById("product-dropdown");
    dropdown.innerHTML = "";
    
    if (allProductsData.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item text-gray-500">Aucun produit trouvé</div>';
        dropdown.style.display = "block";
        return;
    }

    // Filter products based on search value
    const filteredProducts = allProductsData.filter(product => {
        const productName = (product.NAME || "").toLowerCase();
        const productCode = (product.CODE || "").toLowerCase();
        return productName.includes(searchValue) || productCode.includes(searchValue);
    }).slice(0, 10); // Limit to 10 results

    if (filteredProducts.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item text-gray-500">Aucun produit correspondant</div>';
    } else {
        filteredProducts.forEach(product => {
            const option = document.createElement("div");
            option.className = "dropdown-item";
            option.innerHTML = `
                <div class="font-medium">${product.NAME || 'Produit sans nom'}</div>
                <div class="text-sm text-gray-500">${product.CODE || ''}</div>
            `;
            option.addEventListener("click", function() {
                selectProduct(product);
            });
            dropdown.appendChild(option);
        });
    }
    
    dropdown.style.display = "block";
}

// Hide product dropdown
function hideProductDropdown() {
    const dropdown = document.getElementById("product-dropdown");
    dropdown.style.display = "none";
}

// Select a product and fetch its details
async function selectProduct(product) {
    const productInput = document.getElementById("product_search");
    productInput.value = product.NAME || '';
    hideProductDropdown();
    
    // Show loading state
    showProductDetailsLoading();
    
    // Fetch detailed product information
    await fetchProductDetails(product.NAME);
}

// Show loading state for product details
function showProductDetailsLoading() {
    const container = document.getElementById("product-details-container");
    const title = document.getElementById("product-details-title");
    const tableBody = document.getElementById("product-details-table");
    
    title.innerHTML = `
        <svg class="w-5 h-5 mr-2 text-blue-500 animate-spin" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
        </svg>
        Chargement des détails...
    `;
    
    tableBody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center p-8">
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mb-2"></div>
                    <span class="text-gray-500 dark:text-gray-400">Chargement des détails du produit...</span>
                </div>
            </td>
        </tr>
    `;
    
    container.style.display = "block";
}

// Fetch product details
async function fetchProductDetails(productName) {
    try {
        const response = await fetch(API_CONFIG.getApiUrl(`/simulation_fetch-product-details?product_name=${encodeURIComponent(productName)}`));
        if (!response.ok) throw new Error("Failed to fetch product details");
        
        const data = await response.json();
        displayProductDetails(productName, data);
    } catch (error) {
        console.error("Error fetching product details:", error);
        showProductDetailsError();
    }
}

// Display product details in table
function displayProductDetails(productName, data) {
    const container = document.getElementById("product-details-container");
    const title = document.getElementById("product-details-title");
    const tableBody = document.getElementById("product-details-table");
    
    title.innerHTML = `
        <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
        </svg>
        Détails du Produit - ${productName}
    `;
    
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center p-8 text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
                        </svg>
                        Aucun détail disponible pour ce produit
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    // Store the data for potential addition to BCCB
    selectedProductData = data;
    
    const formatNumber = (num) => num ? parseFloat(num).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
    
    tableBody.innerHTML = "";
    data.forEach((row, index) => {
        const tr = document.createElement("tr");
        tr.className = "hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer";
        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${row.FOURNISSEUR || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${row.PRODUCT || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${formatNumber(row.QTY)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${formatNumber(row.P_ACHAT)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${formatNumber(row.P_REVIENT)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${formatNumber(row.P_VENTE)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${formatNumber(row.MARGE)}%</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${row.LOT || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${row.LOCATION || ''}</td>
        `;
        
        // Add click event to select this specific product variant
        tr.addEventListener("click", function() {
            // Highlight selected row
            tableBody.querySelectorAll("tr").forEach(r => r.classList.remove("bg-blue-100", "dark:bg-blue-900"));
            tr.classList.add("bg-blue-100", "dark:bg-blue-900");
            
            // Store the selected row data
            selectedProductData = [row];
        });
        
        tableBody.appendChild(tr);
    });
    
    container.style.display = "block";
}

// Show error when product details fetch fails
function showProductDetailsError() {
    const title = document.getElementById("product-details-title");
    const tableBody = document.getElementById("product-details-table");
    
    title.innerHTML = `
        <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
        </svg>
        Erreur lors du chargement
    `;
    
    tableBody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center p-8 text-red-500">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 mb-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    Erreur lors du chargement des détails du produit
                </div>
            </td>
        </tr>
    `;
}

// Add selected product to BCCB table
function addSelectedProductToBCCB() {
    if (!selectedProductData || selectedProductData.length === 0) {
        alert("Veuillez sélectionner un produit d'abord.");
        return;
    }

    const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
    const bccbContainer = document.getElementById("bccb-product-container");
    
    // Show BCCB container if hidden
    bccbContainer.style.display = "block";
    bccbContainer.classList.add("fade-in");
    
    // Get the first (or selected) product from the details
    const productToAdd = selectedProductData[0];
    
    // Create new row for BCCB table
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150";
    tr.setAttribute("data-manual", "true"); // Mark as manually added
    
    // Calculate default values
    const remiseValue = 0; // Default remise
    const bonusValue = 0;  // Default bonus
    
    // Use the correct pricing from product details
    const priceEntered = productToAdd.P_VENTE || 0;  // Use P_VENTE as price entered
    const pRevient = productToAdd.P_REVIENT || 0;    // Use P_REVIENT as cost price
    const pricelist = productToAdd.P_VENTE || 0;     // Use P_VENTE as pricelist
    
    // Use the actual margin from the database instead of calculating
    const initialMarge = productToAdd.MARGE || 0;

    tr.innerHTML = `
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">
            <div class="flex items-center space-x-2">
                <span class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-2 py-1 rounded-full text-xs font-medium">
                    MANUEL
                </span>
                <span>${productToAdd.PRODUCT || ''}</span>
            </div>
        </td>
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">
            <input type="number" value="1" min="0" step="0.01" class="w-20 p-1 border rounded bg-transparent dark:border-gray-600 dark:text-white">
        </td>
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">
            <span class="remise-badge bg-transparent text-black px-2 py-1 rounded-full text-sm font-medium">
                <input type="number" value="${remiseValue}" min="0" max="100" step="0.1" 
                       class="remise-input w-12 bg-transparent border-none text-center font-bold" 
                       data-row-index="${bccbTableBody.children.length}" 
                       onchange="updateMargin(${bccbTableBody.children.length})">%
            </span>
        </td>
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">
            <input type="number" value="${bonusValue}" min="0" step="0.1" 
                   class="bonus-input w-16 p-1 border rounded bg-transparent dark:border-gray-600 dark:text-white" 
                   data-row-index="${bccbTableBody.children.length}" 
                   onchange="updateMargin(${bccbTableBody.children.length})">
        </td>
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">
            <div class="flex items-center justify-between">
                <span class="marge-display font-semibold" data-row-index="${bccbTableBody.children.length}">
                    ${Math.round(initialMarge * 100) / 100}%
                </span>
                <button onclick="deleteManualProduct(this)" 
                        class="delete-btn ml-2 p-1 text-red-500 hover:text-red-700 hover:bg-red-100 dark:hover:bg-red-900 rounded transition-colors duration-200"
                        title="Supprimer ce produit">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </td>
    `;
    
    bccbTableBody.appendChild(tr);
    
    // Store product data for calculations using the correct pricing structure
    if (!window.productData) window.productData = [];
    window.productData[bccbTableBody.children.length - 1] = {
        priceentered: priceEntered,    // P_VENTE
        p_revient: pRevient,           // P_REVIENT
        pricelist: pricelist,          // P_VENTE
        originalMarge: initialMarge    // MARGE from database
    };
    
    // Show success message
    const productName = productToAdd.PRODUCT || 'Produit';
    showNotification(`${productName} ajouté avec succès au BCCB!`, 'success');
    
    // Close product details
    document.getElementById("product-details-container").style.display = "none";
}

// Delete manually added product
function deleteManualProduct(button) {
    // Show confirmation dialog
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit?')) {
        return;
    }
    
    // Get the row to delete
    const row = button.closest('tr');
    const productName = row.querySelector('td:first-child span:last-child').textContent;
    
    // Remove the row from the table
    row.remove();
    
    // Update row indices for remaining manual products
    updateRowIndices();
    
    // Show success message
    showNotification(`${productName} supprimé avec succès!`, 'success');
}

// Update row indices after deletion
function updateRowIndices() {
    const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
    const rows = bccbTableBody.querySelectorAll('tr');
    
    // Reset productData array
    if (window.productData) {
        window.productData = [];
    }
    
    rows.forEach((row, index) => {
        // Update data-row-index attributes
        const remiseInput = row.querySelector('.remise-input');
        const bonusInput = row.querySelector('.bonus-input');
        const margeDisplay = row.querySelector('.marge-display');
        
        if (remiseInput) {
            remiseInput.setAttribute('data-row-index', index);
            remiseInput.setAttribute('onchange', `updateMargin(${index})`);
        }
        
        if (bonusInput) {
            bonusInput.setAttribute('data-row-index', index);
            bonusInput.setAttribute('onchange', `updateMargin(${index})`);
        }
        
        if (margeDisplay) {
            margeDisplay.setAttribute('data-row-index', index);
        }
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    if (type === 'success') {
        notification.classList.add('bg-green-500', 'text-white');
    } else if (type === 'error') {
        notification.classList.add('bg-red-500', 'text-white');
    } else {
        notification.classList.add('bg-blue-500', 'text-white');
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}


// Countdown variables removed - not needed for simulation

// Update timestamp function removed - not needed for simulation

// Refresh system removed - not needed for simulation

// Initial state - waiting for BCCB input






        </script>

</body> 

</html>

