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
    <link rel="stylesheet" href="confirm_orderv2.css">
    <script src="theme.js"></script>
    <script src="api_config.js"></script>
    <style>


        /* Modern Search Container Styles */
        .search-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .search-group {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            min-width: 250px;
        }

        .search-group:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .dark .search-group {
            background: #374151;
            border-color: #4b5563;
        }

        .dark .search-group:focus-within {
            border-color: #60a5fa;
        }

        .search-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #4b5563;
            margin-right: 0.75rem;
            white-space: nowrap;
        }

        .dark .search-label {
            color: #d1d5db;
        }

        .search-input {
            border: none;
            outline: none;
            background: transparent;
            flex: 1;
            padding: 0.5rem 0;
            font-size: 1rem;
            color: #1f2937;
        }

        .dark .search-input {
            color: #f9fafb;
        }

        .search-input::placeholder {
            color: #9ca3af;
        }

        .dark .search-input::placeholder {
            color: #6b7280;
        }

        .search-btn {
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            padding: 0.5rem;
            margin-left: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn:hover {
            background: #2563eb;
        }

        .search-btn svg {
            width: 1.25rem;
            height: 1.25rem;
            color: white;
        }

        /* Document Type Selector Styles */
        .search-type-select {
            border: none;
            outline: none;
            background: transparent;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #4b5563;
            border-right: 2px solid #e5e7eb;
            margin-right: 0.75rem;
            cursor: pointer;
            min-width: 80px;
        }

        .dark .search-type-select {
            color: #d1d5db;
            border-right-color: #4b5563;
        }

        /* Orders Dropdown Styles */
        .orders-dropdown {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 400px;
            z-index: 1000;
            overflow: hidden;
        }

        .dark .orders-dropdown {
            background: #374151;
            border-color: #4b5563;
        }

        .orders-dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .dark .orders-dropdown-header {
            background: #4b5563;
            border-bottom-color: #6b7280;
        }

        .orders-actions {
            display: flex;
            gap: 0.5rem;
        }

        .select-all-btn, .process-orders-btn {
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            border: none;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .select-all-btn {
            background: #e5e7eb;
            color: #374151;
        }

        .select-all-btn:hover {
            background: #d1d5db;
        }

        .process-orders-btn {
            background: #3b82f6;
            color: white;
        }

        .process-orders-btn:hover {
            background: #2563eb;
        }

        .orders-list {
            max-height: 300px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .order-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-item:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .order-item.selected {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .dark .order-item {
            border-color: #4b5563;
            background: #374151;
        }

        .dark .order-item:hover {
            border-color: #60a5fa;
            background: #4b5563;
        }

        .dark .order-item.selected {
            border-color: #34d399;
            background: #064e3b;
        }

        .order-info {
            flex: 1;
        }

        .order-number {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .order-details {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .dark .order-details {
            color: #9ca3af;
        }

        .order-checkbox {
            margin-left: 1rem;
            transform: scale(1.2);
        }

        /* Document type color indicators */
        .doc-type-bccb {
            border-left: 4px solid #3b82f6 !important;
        }

        .doc-type-facture {
            border-left: 4px solid #10b981 !important;
        }

        .doc-type-orm {
            border-left: 4px solid #f59e0b !important;
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
            .search-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .search-group {
                min-width: 90%;
                width: 90%;
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

        /* Force black text for Type Client badges in dark mode */
        .dark .client-type-badge {
            color: black !important;
        }

        /* Force black text for select-all-btn and process-orders-btn in dark mode */
        .dark .select-all-btn {
            color: black !important;
        }

        .dark .process-orders-btn {
            color: black !important;
        }

        /* Force black text for product details buttons in dark mode */
        .dark #add-product-to-bccb {
            color: black !important;
        }

        .dark #close-product-details {
            color: black !important;
        }

        /* Force black text for unified margin summary in dark mode */
        .dark #unified-margin-summary {
            color: white !important;
            background: linear-gradient(to right, #1e40af, #3730a3) !important;
            border-color: #60a5fa !important;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3) !important;
        }

        .dark #unified-margin-summary .text-gray-600 {
            color: #e5e7eb !important;
        }

        .dark #unified-margin-summary .text-gray-300 {
            color: #f9fafb !important;
        }

        .dark #unified-summary-details {
            color: #d1d5db !important;
        }

        /* Selected row styling for both light and dark modes */
        .selected-row {
            background-color: rgba(59, 130, 246, 0.15) !important;
            border: 2px solid #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.3) !important;
            transform: scale(1.01) !important;
            transition: all 0.2s ease !important;
        }

        .dark .selected-row {
            background-color: rgba(59, 130, 246, 0.25) !important;
            border: 2px solid #60a5fa !important;
            box-shadow: 0 0 15px rgba(96, 165, 250, 0.4) !important;
        }

        /* Selected row text enhancement */
        .selected-row td {
            font-weight: 600 !important;
            color: #1e40af !important;
        }

        .dark .selected-row td {
            color: #93c5fd !important;
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
            top: calc(100% + 5px);
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

        <!-- Modern Search Section -->
        <div class="mb-8">
            <div class="text-center mb-6">
                <p class="text-gray-600 dark:text-gray-400">🔎 Recherchez par numéro de commande ou de facture, ou par produit</p>
            </div>
            
            <div class="search-row">
                <!-- BCCB Search with Dropdown -->
                <div class="search-group">
                    <div class="relative">
                        <select id="document-type-select" class="search-type-select">
                            <option value="bccb" data-color="#3b82f6">BCCB</option>
                            <option value="facture" data-color="#10b981">Facture</option>
                        </select>
                    </div>
                    <input type="text" id="bccb_confirm" class="search-input" placeholder="Numéro de Order" autocomplete="off">
                    <button id="bccb-search-btn" class="search-btn" type="button">
                        <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <!-- Orders dropdown for facture -->
                    <div id="orders-dropdown" class="orders-dropdown" style="display: none;">
                        <div class="orders-dropdown-header">
                            <span>Orders disponibles:</span>
                            <div class="orders-actions">
                                <button id="select-all-orders" class="select-all-btn">Tout sélectionner</button>
                                <button id="process-selected-orders" class="process-orders-btn">Traiter sélectionnés</button>
                            </div>
                        </div>
                        <div id="orders-list" class="orders-list">
                            <!-- Orders will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="search-group">
                    <span class="search-label">Ajouter un produit</span>
                    <input type="text" id="product_search" class="search-input" placeholder="Ajouter un produit..." autocomplete="off">
                    <button class="search-btn" type="button">
                        <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M4 9a5 5 0 1110 0A5 5 0 014 9zm5-7a7 7 0 104.2 12.6.999.999 0 00.093.107l3 3a1 1 0 001.414-1.414l-3-3a.999.999 0 00-.107-.093A7 7 0 009 2z" fill-rule="evenodd" fill="currentColor"></path>
                        </svg>
                    </button>
                    <div id="product-dropdown" class="dropdown"></div>
                </div>
            </div>

            <!-- Client Type Selection -->
            <div class="flex justify-center mb-4">
                <div class="flex space-x-4 flex-wrap justify-center">
                    <label class="flex items-center mb-2">
                        <input type="radio" name="client_type" value="" class="mr-2" checked>
                        <span class="text-gray-700 dark:text-gray-300 text-sm">Tous les clients</span>
                    </label>
                    <label class="flex items-center mb-2">
                        <input type="radio" name="client_type" value="Client Potentiel" class="mr-2">
                        <span class="text-gray-700 dark:text-gray-300 text-sm">Client Potentiel</span>
                    </label>
                    <label class="flex items-center mb-2">
                        <input type="radio" name="client_type" value="Client Para" class="mr-2">
                        <span class="text-gray-700 dark:text-gray-300 text-sm">Client Para</span>
                    </label>
                </div>
            </div>

            <!-- Refresh Button -->
            <div class="flex justify-center">
                <button id="refresh-btn" class="p-3 bg-white dark:bg-gray-700 text-blue-500 dark:text-blue-400 rounded-full shadow-lg hover:shadow-xl border border-blue-500 dark:border-blue-400 transition duration-200 flex items-center justify-center hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
                    </svg>
                </button>
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
                                <th class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-left text-gray-900 dark:text-white font-medium">Type Client</th>
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
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h2a1 1 0 100-2H7z"/>
                        </svg>
                        <span id="bccb-table-title">Détails du Produit BCCB</span>
                        <span id="selected-order-summary" class="ml-4 text-base font-normal text-gray-700 dark:text-gray-300"></span>
                    </h2>
                    <div id="bccb-view-controls" class="flex gap-2" style="display: none;">
                        <button id="show-unified-bccb-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 text-sm font-medium shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            Unifié
                        </button>
                        <button id="show-individual-bccb-btn" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            Individuel
                        </button>
                    </div>
                </div>

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

                <!-- Unified Margin Summary -->
                <div id="unified-margin-summary" class="mt-6 p-4 bg-gradient-to-r from-blue-100 to-indigo-100 dark:from-blue-800 dark:to-indigo-800 rounded-lg border border-blue-300 dark:border-blue-400 shadow-lg dark:shadow-blue-900/50" style="display: none;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Marge Facture Unifiée</h3>
                        </div>
                        <div class="text-right">
                            <div class="grid grid-cols-3 gap-6 text-sm">
                                <div class="text-center">
                                    <p class="text-gray-600 dark:text-gray-300 font-medium">Chiffre d'Affaires Total</p>
                                    <p id="unified-ca-total" class="text-xl font-bold text-blue-600 dark:text-blue-400">0 DA</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-600 dark:text-gray-300 font-medium">Coût Total</p>
                                    <p id="unified-cost-total" class="text-xl font-bold text-red-600 dark:text-red-400">0 DA</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-600 dark:text-gray-300 font-medium">Marge Globale</p>
                                    <p id="unified-margin-total" class="text-2xl font-bold text-green-600 dark:text-green-400">0%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-300 dark:border-gray-500">
                        <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-300">
                            <span id="unified-summary-details">Calcul basé sur 0 commandes, 0 produits</span>
                            <span class="text-xs">Mise à jour en temps réel</span>
                        </div>
                    </div>
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

    // Color mapping for document types
    const docTypeColors = {
        'bccb': 'doc-type-bccb',
        'facture': 'doc-type-facture', 
        'orm': 'doc-type-orm'
    };

    // Update the row count dynamically
    const updateRowCount = (count) => {
        const rowCountElement = document.getElementById('row-count');
        rowCountElement.innerHTML = `Total: ${count} commandes`;
    };

    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.classList.add("cursor-pointer", "hover:bg-gray-200", "dark:hover:bg-gray-700");

        // Add document type color class
        const docType = row.DOC_TYPE || 'bccb';
        tr.classList.add(docTypeColors[docType] || 'doc-type-bccb');

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
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">
                ${row.NDOCUMENT || ''}
                ${row.SOURCE_FACTURE ? `<br><small class="text-gray-500">via ${row.SOURCE_FACTURE}</small>` : ''}
            </td>
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
            document.querySelectorAll("#order-confirmer-table tr").forEach(r => r.classList.remove("selected-row", "bg-blue-100", "dark:bg-blue-900", "ring-2", "ring-blue-500")); 
            tr.classList.add("selected-row"); // Highlight selected row

            // Just fetch BCCB product details without changing the search input or table
            fetchBccbProduct(row.NDOCUMENT);

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
    updateRowCount(data.filter(row => row.ORGANISATION !== 'Total').length);

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
        <span class="font-semibold">N° Document:</span> ${order.NDOCUMENT} &nbsp;
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

    // Search button click: handle different document types
    document.getElementById("bccb-search-btn").addEventListener("click", function () {
        const docType = document.getElementById("document-type-select").value;
        const searchValue = searchInput.value.trim();
        
        if (!searchValue) {
            bccbProductContainer.style.display = "none";
            bccbProductContainer.classList.remove("fade-in");
            hideOrdersDropdown();
            return;
        }

        // Hide BCCB product container when performing search - only show after row click
        bccbProductContainer.style.display = "none";
        bccbProductContainer.classList.remove("fade-in");

        if (docType === 'facture') {
            // Handle facture search - fetch related orders
            fetchOrdersFromFacture(searchValue);
        } else {
            // Handle normal BCCB/ORM search
            hideOrdersDropdown();
            processNormalSearch(searchValue);
        }
    });

    // Document type change handler
    document.getElementById("document-type-select").addEventListener("change", function() {
        const selectedOption = this.options[this.selectedIndex];
        const color = selectedOption.getAttribute('data-color');
        const searchGroup = this.closest('.search-group');
        
        // Update search group border color
        searchGroup.style.borderColor = color;
        
        // Update placeholder based on document type
        const searchInput = document.getElementById("bccb_confirm");
        switch(this.value) {
            case 'bccb':
                searchInput.placeholder = "Numéro de Order ";
                break;
            case 'facture':
                searchInput.placeholder = "Numéro de facture";
                break;
            case 'orm':
                searchInput.placeholder = "Numéro de Order";
                break;
        }
        
        // Hide orders dropdown when changing type
        hideOrdersDropdown();
    });

    // Orders dropdown event handlers
    document.getElementById("select-all-orders").addEventListener("click", function() {
        const checkboxes = document.querySelectorAll("#orders-list input[type='checkbox']");
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            const orderItem = cb.closest('.order-item');
            if (cb.checked) {
                orderItem.classList.add('selected');
            } else {
                orderItem.classList.remove('selected');
            }
        });
        
        this.textContent = allChecked ? "Tout sélectionner" : "Tout désélectionner";
    });

    document.getElementById("process-selected-orders").addEventListener("click", function() {
        const selectedCheckboxes = document.querySelectorAll("#orders-list input[type='checkbox']:checked");
        if (selectedCheckboxes.length === 0) {
            alert("Veuillez sélectionner au moins un order.");
            return;
        }

        const selectedOrders = Array.from(selectedCheckboxes).map(cb => cb.value);
        processSelectedOrders(selectedOrders);
    });

    // Hide dropdown when clicking outside
    document.addEventListener("click", function(event) {
        const ordersDropdown = document.getElementById("orders-dropdown");
        const searchGroup = document.querySelector('.search-group');
        
        if (!searchGroup.contains(event.target)) {
            hideOrdersDropdown();
        }
    });

    // Enter key triggers search
    searchInput.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            const searchBtn = document.getElementById("bccb-search-btn");
            if (searchBtn) {
                searchBtn.click();
            }
        }
    });

    // Product search functionality
    setupProductSearch();

    // Product details event listeners (with null checks)
    const closeProductDetails = document.getElementById("close-product-details");
    if (closeProductDetails) {
        closeProductDetails.addEventListener("click", function() {
            const container = document.getElementById("product-details-container");
            if (container) {
                container.style.display = "none";
            }
        });
    }

    const addProductBtn = document.getElementById("add-product-to-bccb");
    if (addProductBtn) {
        addProductBtn.addEventListener("click", function() {
            addSelectedProductToBCCB();
        });
    }

    // BCCB view controls event listeners
    document.getElementById("show-unified-bccb-btn").addEventListener("click", function() {
        showUnifiedBCCB();
    });

    document.getElementById("show-individual-bccb-btn").addEventListener("click", function() {
        showIndividualBCCB();
    });

    // Show reserved documents button
    document.getElementById("show-reserved-btn").addEventListener("click", function () {
        // Removed fetchOrderConfirmed() - not needed for simulation
    });
});

// ===== NEW FACTURE ORDERS FUNCTIONALITY =====

// Process normal search (BCCB/ORM)
async function processNormalSearch(searchValue) {
    try {
        const response = await fetch(API_CONFIG.getApiUrl(`/real_simulation_all?ndocument=${encodeURIComponent(searchValue)}`));
        const data = await response.json();
        if (data && !data.error) {
            // Add document type indicator based on search type
            const docType = document.getElementById("document-type-select").value;
            data.DOC_TYPE = docType;
            
            // Show as single row in table with colors
            orderConfirmedData = [data];
            updateOrderConfirmedTable([data]);
            
            // Don't fetch BCCB product data automatically - only when row is clicked
            // await fetchBccbProduct(searchValue);
        } else {
            // Show empty table or error
            updateOrderConfirmedTable([]);
        }
    } catch (error) {
        updateOrderConfirmedTable([]);
    }
}

// Fetch orders related to a facture (invoice)
async function fetchOrdersFromFacture(factureNumber) {
    try {
        const ordersDropdown = document.getElementById("orders-dropdown");
        const ordersList = document.getElementById("orders-list");
        
        // Show loading state
        ordersList.innerHTML = `
            <div class="text-center p-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                <span class="text-gray-500">Chargement des orders...</span>
            </div>
        `;
        ordersDropdown.style.display = "block";

        const response = await fetch(API_CONFIG.getApiUrl(`/orders_from_facture?facture_number=${encodeURIComponent(factureNumber)}`));
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.orders && data.orders.length > 0) {
            displayOrdersDropdown(data.orders);
        } else {
            // No orders found
            ordersList.innerHTML = `
                <div class="text-center p-4 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Aucun order trouvé pour cette facture
                </div>
            `;
        }
    } catch (error) {
        console.error("Error fetching orders from facture:", error);
        const ordersList = document.getElementById("orders-list");
        ordersList.innerHTML = `
            <div class="text-center p-4 text-red-500">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Erreur lors du chargement des orders
            </div>
        `;
    }
}

// Display orders in dropdown
function displayOrdersDropdown(orders) {
    const ordersList = document.getElementById("orders-list");
    ordersList.innerHTML = "";

    orders.forEach((order, index) => {
        const orderItem = document.createElement('div');
        orderItem.className = 'order-item';
        orderItem.innerHTML = `
            <div class="order-info">
                <div class="order-number">${order.ORDERNUMBER}</div>
                <div class="order-details">
                    <span>${order.BUSINESSPARTNERNAME}</span> | 
                    <span>${formatCurrency(order.ORDERTOTAL)}</span>
                </div>
            </div>
            <input type="checkbox" class="order-checkbox" value="${order.ORDERNUMBER}" 
                   onchange="toggleOrderSelection(this, '${order.ORDERNUMBER}')">
        `;
        
        // Add click handler for the order item (excluding checkbox)
        orderItem.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = orderItem.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                toggleOrderSelection(checkbox, order.ORDERNUMBER);
            }
        });

        ordersList.appendChild(orderItem);
    });
}

// Toggle order selection
function toggleOrderSelection(checkbox, orderNumber) {
    const orderItem = checkbox.closest('.order-item');
    if (checkbox.checked) {
        orderItem.classList.add('selected');
    } else {
        orderItem.classList.remove('selected');
    }
}

// Process selected orders
async function processSelectedOrders(selectedOrders) {
    hideOrdersDropdown();
    
    // Show processing state
    const mainTable = document.getElementById('order-confirmer-table');
    mainTable.innerHTML = `
        <tr>
            <td colspan="7" class="text-center p-8">
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
                    <span class="text-gray-500 dark:text-gray-400">Traitement de ${selectedOrders.length} order(s)...</span>
                </div>
            </td>
        </tr>
    `;

    try {
        let allOrderData = [];
        
        // Process each selected order
        for (const orderNumber of selectedOrders) {
            const response = await fetch(API_CONFIG.getApiUrl(`/real_simulation_all?ndocument=${encodeURIComponent(orderNumber)}`));
            const data = await response.json();
            
            if (data && !data.error) {
                // Add document type indicator
                data.DOC_TYPE = 'facture';
                data.SOURCE_FACTURE = document.getElementById("bccb_confirm").value;
                allOrderData.push(data);
            }
        }

        // Update table with all order data using the standard function
        if (allOrderData.length > 0) {
            orderConfirmedData = allOrderData;
            updateOrderConfirmedTable(allOrderData);
            
            // Store selected orders for BCCB operations
            window.currentSelectedOrders = selectedOrders;
            
            // Show view controls if multiple orders
            const bccbViewControls = document.getElementById("bccb-view-controls");
            const bccbTableTitle = document.getElementById("bccb-table-title");
            
            if (selectedOrders.length > 1) {
                if (bccbViewControls) {
                    bccbViewControls.style.display = "block";
                }
                if (bccbTableTitle) {
                    bccbTableTitle.textContent = `BCCB Produits (${selectedOrders.length} commandes)`;
                }
            } else {
                if (bccbViewControls) {
                    bccbViewControls.style.display = "none";
                }
                if (bccbTableTitle) {
                    bccbTableTitle.textContent = "Détails du Produit BCCB";
                }
            }
            
            // Process BCCB products for the first order to initialize margin simulation
            if (selectedOrders.length > 0) {
                await fetchBccbProduct(selectedOrders[0]);
            }
        } else {
            updateOrderConfirmedTable([]);
        }
        
    } catch (error) {
        console.error("Error processing selected orders:", error);
        updateOrderConfirmedTable([]);
    }
}

// Hide orders dropdown
function hideOrdersDropdown() {
    const ordersDropdown = document.getElementById("orders-dropdown");
    ordersDropdown.style.display = "none";
}

// Format currency helper
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount || 0);
}

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
        // Clear all input fields
        const bccbInput = document.getElementById("bccb_confirm");
        const productInput = document.getElementById("product_search");
        
        // Clear search inputs
        if (bccbInput) bccbInput.value = "";
        if (productInput) productInput.value = "";
        
        // Reset client type to default (all clients)
        const defaultClientType = document.querySelector('input[name="client_type"][value=""]');
        if (defaultClientType) defaultClientType.checked = true;
        
        // Reset document type to default (BCCB)
        const documentTypeSelect = document.getElementById("document-type-select");
        if (documentTypeSelect) documentTypeSelect.value = "bccb";
        
        // Hide all containers and clear tables
        const bccbProductContainer = document.getElementById("bccb-product-container");
        const productDetailsContainer = document.getElementById("product-details-container");
        const ordersDropdown = document.getElementById("orders-dropdown");
        
        if (bccbProductContainer) bccbProductContainer.style.display = "none";
        if (productDetailsContainer) productDetailsContainer.style.display = "none";
        if (ordersDropdown) ordersDropdown.style.display = "none";
        
        // Clear main orders table
        const mainTable = document.getElementById('order-confirmer-table');
        if (mainTable) {
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
        }
        
        // Clear BCCB product table
        const bccbTable = document.getElementById("confirmed-bccb-product-table");
        if (bccbTable) bccbTable.innerHTML = "";
        
        // Clear product details table
        const productDetailsTable = document.getElementById("product-details-table");
        if (productDetailsTable) productDetailsTable.innerHTML = "";
        
        // Clear global data
        window.productData = [];
        window.simulationData = [];
        orderConfirmedData = [];
        selectedProductData = null;
        window.firstOrderRow = null;
        window.currentSelectedOrders = [];
        
        // Hide unified margin summary
        const unifiedMarginSummary = document.getElementById("unified-margin-summary");
        if (unifiedMarginSummary) unifiedMarginSummary.style.display = "none";
        
        console.log("✅ All fields and data cleared successfully.");
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

    // Reset simulation baseline when loading new BCCB data
    window.initialSimulationMargin = null;
    console.log(`🔄 Reset simulation baseline for new BCCB: ${bccb}`);

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL(API_CONFIG.getApiUrl("/simulation_fetchBCCBProduct"));
    url.searchParams.append("bccb", bccb);
    url.searchParams.append("ad_org_id", "1000000"); 

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("📥 Received BCCB Product Data:", data); // Debugging log
        
        // Check if Python returns a total margin for the entire BCCB simulation
        console.log("🔍 Looking for overall margin in Python response...");
        if (data.length > 0 && data[0].OVERALL_MARGIN !== undefined) {
            console.log(`🐍 Python calculated overall margin: ${data[0].OVERALL_MARGIN}%`);
        } else {
            console.log("❌ No overall margin found in Python response");
        }

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
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500 dark:text-gray-400">
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
        // Extract remise percentage from REMISE_AUTO (e.g., "FIXE 12%" -> 12)
        let remiseValue = 0;
        if (row.REMISE_AUTO && typeof row.REMISE_AUTO === 'string') {
            const match = row.REMISE_AUTO.match(/(\d+(?:\.\d+)?)/);
            if (match) {
                remiseValue = parseFloat(match[1]);
            }
        }
        // If REMISE_AUTO doesn't contain a percentage, fall back to REMISE field
        if (remiseValue === 0 && row.REMISE) {
            remiseValue = Math.round(row.REMISE * 100);
        }
        
        // Convert BONUS_VENTE to a whole number, default to 0
        const bonusValue = row.BON_VENTE ? Math.round(row.BON_VENTE) : (row.BONUS_VENTE ? Math.round(row.BONUS_VENTE) : 0);
        
        // Debug: Log the bonus value to see what we're getting
        console.log(`Product ${index}: BON_VENTE =`, row.BON_VENTE, `BONUS_VENTE =`, row.BONUS_VENTE, `bonusValue =`, bonusValue);
        console.log(`Product ${index} full data:`, row);

        const tr = document.createElement("tr");
        tr.classList.add("hover:bg-gray-50", "dark:hover:bg-gray-700", "transition-colors", "duration-150");
        
        // Check if this is unified view data (has SOURCE_ORDERS)
        const isUnifiedView = row.SOURCE_ORDERS && row.SOURCE_ORDERS.length > 0;
        const sourceInfo = isUnifiedView ? 
            `<span class="text-xs text-blue-600 dark:text-blue-400">(${row.SOURCE_ORDERS.join(', ')})</span>` : '';
        
        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">
                ${row.PRODUCT || "N/A"}
                ${sourceInfo ? `<br>${sourceInfo}` : ''}
            </td>
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
        
        // Store original data for calculations - PRESERVE EXACT PRECISION from Python
        if (!window.productData) window.productData = [];
        window.productData[index] = {
            priceentered: row.PRICEENTERED || 0,
            p_revient: row.P_REVIENT || 0,
            pricelist: row.PRICELIST || 0,
            remise_vente: row.REMISE_VENTE || 0,
            bonus_vente: row.BON_VENTE || row.BONUS_VENTE || 0,
            ventef: row.VENTEF || 0, // Pre-calculated selling price from Python - EXACT precision
            originalMarge: row.MARGE || 0
        };
        
        console.log(`📦 Stored product ${index} data:`, window.productData[index]);
        
        // Debug: Log the raw Python response for first product to see all available fields
        if (index === 0) {
            console.log(`🐍 Raw Python response for product 0:`, row);
        }
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
    const newRemiseVente = Math.round((parseFloat(remiseInput.value) || 0) * 100) / 100;
    const newBonusVente = Math.round((parseFloat(bonusInput.value) || 0) * 100) / 100;

    // Use the original price entered and cost price for calculation (all rounded to 2 decimals)
    const priceEntered = Math.round(productInfo.priceentered * 100) / 100;
    const pRevient = Math.round(productInfo.p_revient * 100) / 100;
    const pricelist = Math.round(productInfo.pricelist * 100) / 100;

    // Calculate ventef (final selling price) using the same formula as Python
    // ventef = (priceentered - ((priceentered * remise_vente) / 100)) / (1 + (bonus_vente / 100))
    const ventef = Math.round(((pricelist - ((pricelist * newRemiseVente) / 100)) / (1 + (newBonusVente / 100))) * 100) / 100;

    // Calculate margin using SAME FORMULA as original: (TOTALLINE - CONSOMATION) / CONSOMATION
    // For single product: (ventef - p_revient) / p_revient = (TOTALLINE - CONSOMATION) / CONSOMATION
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
    
    // Check if we're in unified view and recalculate unified margin
    const unifiedMarginSummary = document.getElementById("unified-margin-summary");
    if (unifiedMarginSummary && unifiedMarginSummary.style.display !== "none") {
        // We're in unified view - recalculate unified margin
        calculateUnifiedMargin();
    }
    
    // Update simulation margins based on new BCCB values
    updateSimulationMargin();
}

// Function to calculate and update simulation margin for orders table based on BCCB product values
function updateSimulationMargin() {
    // Find the currently selected row (highlighted with blue background)
    const selectedRow = document.querySelector("#order-confirmer-table tr.selected-row");
    
    if (!selectedRow) {
        console.log("No row selected - skipping margin update");
        return; // No row is selected
    }
    
    // Find the margin display element in the selected row
    const margeDisplay = selectedRow.querySelector('span.marge-simulation-display');
    
    if (!margeDisplay) {
        console.log("No margin display found in selected row");
        return;
    }
    
    const ndocument = margeDisplay.getAttribute('data-ndocument');
    if (!ndocument) {
        console.log("No document number found in selected row");
        return;
    }
    
    console.log(`Updating margin simulation for selected row: ${ndocument}`);
    
    // Calculate the new margin based on current BCCB product values
    calculateSimulationMargeBasedOnBCCB(ndocument, margeDisplay);
}

// Calculate simulation margin for a specific order based on BCCB product details
function calculateSimulationMargeBasedOnBCCB(ndocument, margeDisplay) {
    console.log(`🧮 calculateSimulationMargeBasedOnBCCB called for ${ndocument}`);
    
    // Check if BCCB product data exists for this document
    if (!window.productData || window.productData.length === 0) {
        console.log("❌ No BCCB product data available");
        return; // No BCCB data available
    }
    
    console.log("✅ ProductData available:", window.productData);
    
    // Get the row index from the display element
    const rowIndex = margeDisplay.getAttribute('data-row-index');
    const orderInfo = window.simulationData[rowIndex];
    if (!orderInfo) return;
    
    console.log(`\n=== CALCULATING MARGIN FOR ${ndocument} ===`);
    console.log(`Original Order Margin: ${orderInfo.originalMarge || orderInfo.marge || 0}% (from xx_ca_fournisseur)`);
    console.log(`Using SAME FORMULA as original: (TOTALLINE - CONSOMATION) / CONSOMATION`);
    
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
        
        const remiseValue = Math.round((parseFloat(remiseInput.value) || 0) * 100) / 100;
        const bonusValue = Math.round((parseFloat(bonusInput.value) || 0) * 100) / 100;
        
        // Get quantity - check if it's a manual product first
        let quantity = 1;
        if (tableRows[index]) {
            const row = tableRows[index];
            const isManualProduct = row.getAttribute('data-manual') === 'true';
            
            if (isManualProduct) {
                // For manual products, get quantity from the input field
                const quantityInput = row.querySelector('input.quantity-input');
                if (quantityInput) {
                    const inputValue = parseFloat(quantityInput.value);
                    quantity = isNaN(inputValue) ? 1 : inputValue; // Allow 0, but default to 1 if invalid
                }
            } else {
                // For BCCB products, get quantity from cell text content
                const qtyCell = row.cells[1]; // Second column (index 1)
                if (qtyCell) {
                    const qtyText = qtyCell.textContent.trim();
                    quantity = Math.round((parseFloat(qtyText) || 1) * 100) / 100;
                }
            }
        }
        
        // Skip products with zero quantity (treat like they don't exist)
        if (quantity <= 0) {
            console.log(`Product ${index} (${productName}): SKIPPED - Zero quantity`);
            return;
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
            priceList: productInfo.pricelist || 0,  // EXACT precision
            costPrice: productInfo.p_revient || 0,  // EXACT precision
            originalMarge: productInfo.originalMarge || 0
        });
        
        console.log(`Product ${index} (${productName}): Manual=${tableRows[index]?.getAttribute('data-manual') === 'true'}, Qty=${quantity}, Remise=${remiseValue}%, Bonus=${bonusValue}%, PriceList=${Math.round((productInfo.pricelist || 0) * 100) / 100}, Cost=${Math.round((productInfo.p_revient || 0) * 100) / 100}`);
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
                console.log(`  🎁 Entry ${index}: FREE PRODUCT (${remiseValue}% remise) - EXCLUDED from margin calculation but quantity (${quantity}) counted`);
                hasFreeProducts = true;
                return; // Skip this entry for margin calculation
            }
            
            // Calculate effective selling price for normal products - USE EXACT PRECISION
            // Check if we should use pre-calculated ventef from Python
            const productInfo = window.productData[index];
            let effectiveSellingPrice;
            
            if (productInfo.ventef && 
                Math.abs(remiseValue - (productInfo.remise_vente || 0)) < 0.01 && 
                Math.abs(bonusValue - (productInfo.bonus_vente || 0)) < 0.01) {
                // Use pre-calculated ventef from Python for exact consistency - NO ROUNDING
                effectiveSellingPrice = productInfo.ventef;
                console.log(`  💎 Entry ${index}: Using Python ventef=${effectiveSellingPrice} (unchanged remise/bonus)`);
            } else {
                // Recalculate if values have changed - USE FULL PRECISION, NO ROUNDING
                effectiveSellingPrice = (priceList - ((priceList * remiseValue) / 100)) / (1 + (bonusValue / 100));
                console.log(`  🔄 Entry ${index}: EXACT ventef=${effectiveSellingPrice} (remise: ${productInfo.remise_vente || 0}→${remiseValue}, bonus: ${productInfo.bonus_vente || 0}→${bonusValue})`);
            }
            
            const lineSelling = effectiveSellingPrice * quantity;
            const lineCost = costPrice * quantity;
            
            groupSelling += lineSelling;
            groupCost += lineCost;
            hasNormalProducts = true;
            
            console.log(`  Entry ${index}: INCLUDED - ExactPrice=${effectiveSellingPrice}, LineSelling=${lineSelling}, LineCost=${lineCost}`);
        });
        
        // Add group totals to overall calculation only if there are normal (non-free) products
        if (hasNormalProducts) {
            totalNewSelling += groupSelling;
            totalNewCost += groupCost;
            hasValidData = true;
            
            const groupMargin = groupCost > 0 ? ((groupSelling - groupCost) / groupCost) * 100 : 0;
            console.log(`  Group Summary: Qty=${groupQuantity}, ExactSelling=${groupSelling}, ExactCost=${groupCost}, Margin=${groupMargin.toFixed(4)}%`);
            
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
    
    // Calculate the new margin percentage using the SAME FORMULA as original Python calculation
    // Original formula: (TOTALLINE - CONSOMATION) / CONSOMATION
    // Where: TOTALLINE = sum of selling prices, CONSOMATION = sum of cost prices
    const newMargin = totalNewCost > 0 ? ((totalNewSelling - totalNewCost) / totalNewCost) * 100 : 0;
    
    // Calculate what Python's weighted margin should be using original ventef values
    let pythonTotalSelling = 0;
    let pythonTotalCost = 0;
    
    window.productData.forEach((productInfo, index) => {
        if (!productInfo || productInfo.originalMarge <= -50) return; // Skip invalid or extreme negative margins
        
        // Get quantity from table
        const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
        const tableRows = bccbTableBody.querySelectorAll('tr');
        let quantity = 1;
        if (tableRows[index]) {
            const qtyCell = tableRows[index].cells[1];
            if (qtyCell) {
                quantity = parseFloat(qtyCell.textContent.trim()) || 1;
            }
        }
        
        // Skip zero quantity products
        if (quantity <= 0) return;
        
        // Check if it's a free product (100% remise - check if ventef is 0 or very small)
        if (productInfo.ventef <= 0.01) {
            console.log(`  🎁 Python product ${index}: FREE (ventef=${productInfo.ventef}) - excluded from Python margin`);
            return;
        }
        
        // Use Python's original ventef and cost
        pythonTotalSelling += productInfo.ventef * quantity;
        pythonTotalCost += productInfo.p_revient * quantity;
        
        console.log(`  📊 Python product ${index}: ventef=${productInfo.ventef}, qty=${quantity}, lineSelling=${productInfo.ventef * quantity}, lineCost=${productInfo.p_revient * quantity}`);
    });
    
    const pythonWeightedMargin = pythonTotalCost > 0 ? ((pythonTotalSelling - pythonTotalCost) / pythonTotalCost) * 100 : 0;
    
    console.log(`\n=== FINAL SIMULATION CALCULATION FOR ${ndocument} ===`);
    console.log(`Product Groups Processed: ${productGroups.size}`);
    console.log(`Total Placement Quantity: ${totalQuantity} (includes ALL products)`);
    console.log(`\n📊 USING SAME FORMULA AS ORIGINAL: (TOTALLINE - CONSOMATION) / CONSOMATION`);
    console.log(`  - TOTALLINE (Total Selling): ${totalNewSelling}`);
    console.log(`  - CONSOMATION (Total Cost): ${totalNewCost}`);
    console.log(`  - SIMULATION MARGIN: ${newMargin}%`);
    console.log(`\n🔍 COMPARISON WITH PYTHON (ventef method):`);
    console.log(`  - Python Total Selling: ${pythonTotalSelling}`);
    console.log(`  - Python Total Cost: ${pythonTotalCost}`);
    console.log(`  - Python Weighted Margin: ${pythonWeightedMargin}%`);
    console.log(`\n� RESULT COMPARISON:`);
    console.log(`  - Original Order Margin: ${orderInfo.originalMarge || orderInfo.marge || 0}%`);
    console.log(`  - New Simulation Margin: ${newMargin}%`);
    console.log(`  - Difference: ${(newMargin - (orderInfo.originalMarge || orderInfo.marge || 0)).toFixed(4)}%`);
    console.log(`  - Note: Small differences expected due to different data sources:`);
    console.log(`    * Original: xx_ca_fournisseur (aggregated sales data)`);
    console.log(`    * Simulation: C_OrderLine (individual product data)`);
    console.log(`    * Both use same formula: (TOTALLINE - CONSOMATION) / CONSOMATION`);
    console.log(`================================================`);
    
    // Get the original margin for comparison
    const originalMargin = orderInfo.originalMarge || orderInfo.marge || 0;
    
    // Store initial simulation margin as baseline if not already set
    if (!window.initialSimulationMargin) {
        window.initialSimulationMargin = newMargin;
        console.log(`📌 Setting initial simulation baseline: ${newMargin.toFixed(4)}%`);
    }
    
    // Update the display
    margeDisplay.textContent = `${Math.round(newMargin * 100) / 100}%`;
    
    // Add visual feedback for changes compared to SIMULATION BASELINE (not historical data)
    margeDisplay.style.transition = 'all 0.3s ease';
    const baselineMargin = window.initialSimulationMargin || newMargin;
    
    if (Math.abs(newMargin - baselineMargin) < 0.01) {
        margeDisplay.style.color = '#3B82F6'; // Blue for unchanged
        console.log(`🔵 Margin unchanged from simulation baseline: ${baselineMargin.toFixed(4)}%`);
    } else if (newMargin > baselineMargin) {
        margeDisplay.style.color = '#10B981'; // Green for increase
        console.log(`🟢 Margin increased from baseline: ${baselineMargin.toFixed(4)}% → ${newMargin.toFixed(4)}%`);
    } else {
        margeDisplay.style.color = '#EF4444'; // Red for decrease
        console.log(`🔴 Margin decreased from baseline: ${baselineMargin.toFixed(4)}% → ${newMargin.toFixed(4)}%`);
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
            <td colspan="10" class="text-center p-8">
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
        // Get selected client type
        const clientTypeRadio = document.querySelector('input[name="client_type"]:checked');
        const clientType = clientTypeRadio ? clientTypeRadio.value : '';
        
        // Build URL with client type parameter
        let url = `/simulation_fetch-product-details?product_name=${encodeURIComponent(productName)}`;
        if (clientType) {
            url += `&client_type=${encodeURIComponent(clientType)}`;
        }
        
        const response = await fetch(API_CONFIG.getApiUrl(url));
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
                <td colspan="10" class="text-center p-8 text-gray-500 dark:text-gray-400">
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
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">
                <span class="client-type-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${row.CLIENT_TYPE === 'Client Potentiel' ? 'bg-blue-100 text-blue-800 dark:bg-blue-200' : row.CLIENT_TYPE === 'Client Para' ? 'bg-green-100 text-green-800 dark:bg-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-300'}">
                    ${row.CLIENT_TYPE || 'N/A'}
                </span>
            </td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${row.LOT || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">${row.LOCATION || ''}</td>
        `;
        
        // Add click event to select this specific product variant
        tr.addEventListener("click", function() {
            // Highlight selected row using the same styling as main table
            tableBody.querySelectorAll("tr").forEach(r => r.classList.remove("selected-row", "bg-blue-100", "dark:bg-blue-900"));
            tr.classList.add("selected-row");
            
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
            <td colspan="10" class="text-center p-8 text-red-500">
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
    
    // Calculate default values - extract remise from REMISE_AUTO
    let remiseValue = 0;
    if (productToAdd.REMISE_AUTO && typeof productToAdd.REMISE_AUTO === 'string') {
        const match = productToAdd.REMISE_AUTO.match(/(\d+(?:\.\d+)?)/);
        if (match) {
            remiseValue = Math.round(parseFloat(match[1]) * 100) / 100;
        }
    }
    const bonusValue = productToAdd.BON_VENTE ? Math.round(productToAdd.BON_VENTE) : 0;  // Use actual bonus from product data (BON_VENTE field)
    
    // Debug: Log the bonus value to see what we're getting
    console.log('Manual Product - productToAdd.BON_VENTE:', productToAdd.BON_VENTE, 'bonusValue:', bonusValue);
    console.log('Manual Product - Full productToAdd data:', productToAdd);
    
    // Use the correct pricing from product details
    const priceEntered = Math.round((productToAdd.P_VENTE || 0) * 100) / 100;  // Use P_VENTE as price entered
    const pRevient = Math.round((productToAdd.P_REVIENT || 0) * 100) / 100;    // Use P_REVIENT as cost price
    const pricelist = Math.round((productToAdd.P_VENTE || 0) * 100) / 100;     // Use P_VENTE as pricelist
    
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
            <input type="number" value="1" min="0" step="1" 
                   class="quantity-input w-20 p-1 border rounded bg-transparent dark:border-gray-600 dark:text-white"
                   data-row-index="${bccbTableBody.children.length}"
                   onchange="handleQuantityChange(${bccbTableBody.children.length})">
        </td>
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">
            <span class="remise-badge bg-transparent text-black px-2 py-1 rounded-full text-sm font-medium">
                <input type="number" value="${remiseValue}" min="0" max="100" step="1" 
                       class="remise-input w-12 bg-transparent border-none text-center font-bold" 
                       data-row-index="${bccbTableBody.children.length}" 
                       onchange="updateMargin(${bccbTableBody.children.length})">%
            </span>
        </td>
        <td class="border border-gray-200 dark:border-gray-600 px-4 py-3">
            <input type="number" value="${bonusValue}" min="0" step="1" 
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
        originalMarge: initialMarge,   // MARGE from database
        actualRemise: remiseValue      // Actual remise from REMISE_AUTO
    };
    
    // Check if we're in unified view and recalculate unified margin
    const unifiedMarginSummary = document.getElementById("unified-margin-summary");
    if (unifiedMarginSummary && unifiedMarginSummary.style.display !== "none") {
        // We're in unified view - recalculate unified margin after adding manual product
        setTimeout(() => {
            calculateUnifiedMargin();
        }, 100); // Small delay to ensure DOM is updated
    } else {
        // We're in individual view - update simulation margin for the current order
        setTimeout(() => {
            updateSimulationMargin();
        }, 100); // Small delay to ensure DOM is updated
    }
    
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
    
    // Get the row to delete and its index
    const row = button.closest('tr');
    const productName = row.querySelector('td:first-child span:last-child').textContent;
    const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
    const rows = Array.from(bccbTableBody.querySelectorAll('tr'));
    const rowIndex = rows.indexOf(row);
    
    // Remove the corresponding product data
    if (window.productData && rowIndex >= 0) {
        window.productData.splice(rowIndex, 1);
    }
    
    // Remove the row from the table
    row.remove();
    
    // Update row indices for remaining products
    updateRowIndicesAfterDeletion();
    
    // Check if we're in unified view and recalculate unified margin
    const unifiedMarginSummary = document.getElementById("unified-margin-summary");
    if (unifiedMarginSummary && unifiedMarginSummary.style.display !== "none") {
        // We're in unified view - recalculate unified margin after deleting manual product
        setTimeout(() => {
            calculateUnifiedMargin();
        }, 100); // Small delay to ensure DOM is updated
    } else {
        // We're in individual view - update simulation margin for the current order
        setTimeout(() => {
            updateSimulationMargin();
        }, 100); // Small delay to ensure DOM is updated
    }
    
    // Show success message
    showNotification(`${productName} supprimé avec succès!`, 'success');
}

// Handle quantity change for manual products
function handleQuantityChange(rowIndex) {
    console.log(`Quantity changed for row ${rowIndex}`);
    
    // Update individual margin if needed
    updateMargin(rowIndex);
    
    // Check if we're in unified view and recalculate unified margin
    const unifiedMarginSummary = document.getElementById("unified-margin-summary");
    if (unifiedMarginSummary && unifiedMarginSummary.style.display !== "none") {
        // We're in unified view - recalculate unified margin
        calculateUnifiedMargin();
    } else {
        // We're in individual view - update simulation margin for the current order
        updateSimulationMargin();
    }
}

// Update row indices after deletion (preserves productData)
function updateRowIndicesAfterDeletion() {
    const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
    const rows = bccbTableBody.querySelectorAll('tr');
    
    // Don't reset productData array - it's already been updated by splice
    
    rows.forEach((row, index) => {
        // Update data-row-index attributes
        const remiseInput = row.querySelector('.remise-input');
        const bonusInput = row.querySelector('.bonus-input');
        const quantityInput = row.querySelector('.quantity-input');
        const margeDisplay = row.querySelector('.marge-display');
        
        if (remiseInput) {
            remiseInput.setAttribute('data-row-index', index);
            remiseInput.setAttribute('onchange', `updateMargin(${index})`);
        }
        
        if (bonusInput) {
            bonusInput.setAttribute('data-row-index', index);
            bonusInput.setAttribute('onchange', `updateMargin(${index})`);
        }
        
        if (quantityInput) {
            quantityInput.setAttribute('data-row-index', index);
            quantityInput.setAttribute('onchange', `handleQuantityChange(${index})`);
        }
        
        if (margeDisplay) {
            margeDisplay.setAttribute('data-row-index', index);
        }
    });
}

// Update row indices after deletion (original function - resets productData)
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
        const quantityInput = row.querySelector('.quantity-input');
        const margeDisplay = row.querySelector('.marge-display');
        
        if (remiseInput) {
            remiseInput.setAttribute('data-row-index', index);
            remiseInput.setAttribute('onchange', `updateMargin(${index})`);
        }
        
        if (bonusInput) {
            bonusInput.setAttribute('data-row-index', index);
            bonusInput.setAttribute('onchange', `updateMargin(${index})`);
        }
        
        if (quantityInput) {
            quantityInput.setAttribute('data-row-index', index);
            quantityInput.setAttribute('onchange', `handleQuantityChange(${index})`);
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

// Show Unified BCCB - combines all BCCB data from multiple orders into one unified view
async function showUnifiedBCCB() {
    if (!window.currentSelectedOrders || window.currentSelectedOrders.length === 0) {
        console.log("No selected orders available");
        return;
    }

    console.log(`Showing unified BCCB for ${window.currentSelectedOrders.length} orders`);
    
    // Update title and button states
    document.getElementById("bccb-table-title").textContent = `BCCB Unifié (${window.currentSelectedOrders.length} commandes)`;
    document.getElementById("show-unified-bccb-btn").classList.add("bg-blue-800");
    document.getElementById("show-individual-bccb-btn").classList.remove("bg-gray-800");
    
    // Show unified margin summary
    document.getElementById("unified-margin-summary").style.display = "block";
    
    try {
        let allBccbData = [];
        
        // Fetch BCCB data for all orders
        for (const orderNumber of window.currentSelectedOrders) {
            const url = new URL(API_CONFIG.getApiUrl("/simulation_fetchBCCBProduct"));
            url.searchParams.append("bccb", orderNumber);
            url.searchParams.append("ad_org_id", "1000000");
            
            const response = await fetch(url);
            if (response.ok) {
                const data = await response.json();
                if (data && data.length > 0) {
                    // Add order identifier to each product for reference
                    data.forEach(product => {
                        product.SOURCE_ORDER = orderNumber;
                    });
                    allBccbData = allBccbData.concat(data);
                }
            }
        }
        
        // Group similar products and merge quantities
        const unifiedProducts = mergeProductsByName(allBccbData);
        
        // Store unified data globally for margin calculations
        window.unifiedBccbData = unifiedProducts;
        window.originalBccbData = allBccbData;
        
        // Update table with unified data
        updateBccbProductTable(unifiedProducts);
        
        // Calculate and display unified margin
        calculateUnifiedMargin();
        
        // Update order summary
        const summaryEl = document.getElementById('selected-order-summary');
        summaryEl.innerHTML = `<span class="font-semibold">Vue:</span> BCCB Unifié (${window.currentSelectedOrders.length} commandes, ${unifiedProducts.length} produits uniques)`;
        
    } catch (error) {
        console.error("Error fetching unified BCCB data:", error);
    }
}

// Merge products with same name from different orders
function mergeProductsByName(allProducts) {
    const productMap = new Map();
    
    allProducts.forEach(product => {
        const productName = product.PRODUCT || 'Produit Inconnu';
        
        if (productMap.has(productName)) {
            // Merge with existing product
            const existing = productMap.get(productName);
            existing.QTY = (existing.QTY || 0) + (product.QTY || 0);
            existing.MONTANT = (existing.MONTANT || 0) + ((product.QTY || 0) * (product.PRICELIST || 0));
            
            // Keep track of source orders
            if (!existing.SOURCE_ORDERS) {
                existing.SOURCE_ORDERS = [existing.SOURCE_ORDER];
            }
            if (!existing.SOURCE_ORDERS.includes(product.SOURCE_ORDER)) {
                existing.SOURCE_ORDERS.push(product.SOURCE_ORDER);
            }
        } else {
            // Add new product
            const newProduct = { ...product };
            newProduct.MONTANT = (product.QTY || 0) * (product.PRICELIST || 0);
            newProduct.SOURCE_ORDERS = [product.SOURCE_ORDER];
            productMap.set(productName, newProduct);
        }
    });
    
    return Array.from(productMap.values());
}

// Show Individual BCCB - shows BCCB for currently selected row only
function showIndividualBCCB() {
    // Update title and button states  
    document.getElementById("bccb-table-title").textContent = "BCCB Individuel";
    document.getElementById("show-unified-bccb-btn").classList.remove("bg-blue-800");
    document.getElementById("show-individual-bccb-btn").classList.add("bg-gray-800");
    
    // Hide unified margin summary
    document.getElementById("unified-margin-summary").style.display = "none";
    
    // Find currently selected row
    const selectedRow = document.querySelector("#order-confirmer-table tr.bg-blue-100, #order-confirmer-table tr.ring-blue-500");
    
    if (selectedRow) {
        // Get the NDOCUMENT from the selected row
        const firstCell = selectedRow.querySelector('td:first-child');
        if (firstCell) {
            const ndocumentText = firstCell.textContent.trim();
            const ndocument = ndocumentText.split('\n')[0].trim(); // Get first line before any <br> content
            
            // Fetch BCCB for this specific order
            fetchBccbProduct(ndocument);
        }
    } else if (window.currentSelectedOrders && window.currentSelectedOrders.length > 0) {
        // If no row is selected, show first order
        fetchBccbProduct(window.currentSelectedOrders[0]);
    }
}

// Calculate unified margin for all combined BCCBs as one document
function calculateUnifiedMargin() {
    const bccbTableBody = document.getElementById("confirmed-bccb-product-table");
    if (!bccbTableBody || bccbTableBody.children.length === 0) {
        console.log("No BCCB products available for unified margin calculation");
        return;
    }

    let totalRevenue = 0;    // Total selling price (Chiffre d'Affaires)
    let totalCost = 0;       // Total cost price
    let totalProducts = 0;
    
    console.log("Starting unified margin calculation with", bccbTableBody.children.length, "products");
    
    // Calculate totals from ALL products currently in the table (both unified BCCB and manual)
    Array.from(bccbTableBody.children).forEach((row, index) => {
        const productInfo = window.productData[index];
        if (!productInfo) {
            console.log(`No product info for index ${index}`);
            return;
        }

        // Get current remise and bonus values from inputs
        const remiseInput = row.querySelector(`input.remise-input[data-row-index="${index}"]`) || 
                           row.querySelector(`input.remise-input`);
        const bonusInput = row.querySelector(`input.bonus-input[data-row-index="${index}"]`) || 
                          row.querySelector(`input.bonus-input`);
        
        const currentRemise = remiseInput ? parseFloat(remiseInput.value) || 0 : 0;
        const currentBonus = bonusInput ? parseFloat(bonusInput.value) || 0 : 0;
        
        // Determine if this is a manual product and get quantity
        const isManualProduct = row.getAttribute('data-manual') === 'true';
        let quantity = 1; // Default for manual products
        
        if (isManualProduct) {
            // For manual products, get quantity from the quantity input (specifically the quantity-input class)
            const quantityInput = row.querySelector('input.quantity-input');
            if (quantityInput) {
                const inputValue = parseFloat(quantityInput.value);
                quantity = isNaN(inputValue) ? 1 : inputValue; // Allow 0, but default to 1 if invalid
            }
        } else if (window.unifiedBccbData && window.unifiedBccbData[index]) {
            // For unified BCCB products, use stored quantity
            quantity = window.unifiedBccbData[index].QTY || 0;
        }
        
        // Skip products with zero quantity (treat like they don't exist)
        if (quantity <= 0) {
            console.log(`Product ${index}: SKIPPED - Zero quantity`);
            return;
        }
        
        // Use the same calculation logic as individual margin calculation
        const priceList = Math.round(productInfo.pricelist * 100) / 100;
        const costPrice = Math.round(productInfo.p_revient * 100) / 100;
        
        // Calculate final selling price with remise and bonus
        // ventef = (pricelist - ((pricelist * remise) / 100)) / (1 + (bonus / 100))
        const finalSellingPrice = Math.round(((priceList - ((priceList * currentRemise) / 100)) / (1 + (currentBonus / 100))) * 100) / 100;
        
        console.log(`Product ${index}: Manual=${isManualProduct}, Qty=${quantity}, Price=${priceList}, Cost=${costPrice}, Remise=${currentRemise}%, Bonus=${currentBonus}%, FinalPrice=${finalSellingPrice}`);
        
        // Add to totals
        totalRevenue += finalSellingPrice * quantity;
        totalCost += costPrice * quantity;
        totalProducts += quantity;
    });

    // Calculate global margin: ((totalRevenue - totalCost) / totalCost) * 100
    let globalMargin = 0;
    if (totalCost > 0) {
        globalMargin = Math.round(((totalRevenue - totalCost) / totalCost) * 10000) / 100; // Round to 2 decimals
    }

    console.log(`Unified calculation results: Revenue=${totalRevenue}, Cost=${totalCost}, Margin=${globalMargin}%`);

    // Update unified margin display
    document.getElementById("unified-ca-total").textContent = `${Math.round(totalRevenue * 100) / 100} DA`;
    document.getElementById("unified-cost-total").textContent = `${Math.round(totalCost * 100) / 100} DA`;
    document.getElementById("unified-margin-total").textContent = `${globalMargin}%`;
    
    // Update summary details
    const orderCount = window.currentSelectedOrders ? window.currentSelectedOrders.length : 0;
    const tableProductCount = bccbTableBody.children.length;
    const manualProductCount = bccbTableBody.querySelectorAll('tr[data-manual="true"]').length;
    const bccbProductCount = tableProductCount - manualProductCount;
    
    document.getElementById("unified-summary-details").textContent = 
        `Calcul basé sur ${orderCount} commandes, ${tableProductCount} produits (${bccbProductCount} BCCB + ${manualProductCount} manuels, ${totalProducts} unités totales)`;

    console.log("Unified Margin Calculation Complete:", {
        totalRevenue: totalRevenue,
        totalCost: totalCost,
        globalMargin: globalMargin,
        orderCount: orderCount,
        tableProductCount: tableProductCount,
        manualProductCount: manualProductCount,
        totalUnits: totalProducts
    });
}






        </script>

</body> 

</html>

