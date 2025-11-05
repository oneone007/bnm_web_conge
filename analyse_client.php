<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM");
    exit();
}

// Check user permissions and get user info
$userRole = $_SESSION['Role'] ?? '';
$userName = $_SESSION['username'] ?? '';

// Define roles that can see all data
$adminRoles = ['Admin', 'Developer', 'Sup Vente'];
$canSeeAllData = in_array($userRole, $adminRoles);

$page_identifier = 'analyse_client';
require_once 'check_permission.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator-Client-Supplier Analysis - BNM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="api_config.js"></script>
    <script src="theme.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Dark mode compatibility with theme.js */
        body.dark-mode { background-color: #111827; color: #F3F4F6; }
        body.dark-mode .bg-white { background-color: #1f2937 !important; }
        body.dark-mode .bg-gray-50 { background-color: #374151 !important; }
        body.dark-mode .text-gray-900 { color: #ffffff !important; }
        body.dark-mode .text-gray-800 { color: #ffffff !important; }
        body.dark-mode .text-gray-700 { color: #d1d5db !important; }
        body.dark-mode .text-gray-600 { color: #9ca3af !important; }
        body.dark-mode .text-gray-500 { color: #9ca3af !important; }
        body.dark-mode .border-gray-200 { border-color: #4b5563 !important; }
        body.dark-mode .border-gray-300 { border-color: #4b5563 !important; }
        body.dark-mode .bg-gray-100 { background-color: #4b5563 !important; }

        /* Fix for form elements in dark mode */
        body.dark-mode input[type="date"],
        body.dark-mode input[type="text"],
        body.dark-mode select {
            background-color: #374151 !important;
            color: #ffffff !important;
            border-color: #4b5563 !important;
        }

        body.dark-mode input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        body.dark-mode select option {
            background-color: #374151 !important;
            color: #ffffff !important;
        }

        body.dark-mode input::placeholder {
            color: #9ca3af !important;
        }

        /* Clickable table rows */
        #results-table-body tr {
            transition: all 0.2s ease;
        }
        
        #results-table-body tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        body.dark-mode #results-table-body tr:hover {
            background-color: #374151;
            box-shadow: 0 2px 4px rgba(255, 255, 255, 0.1);
        }
        
        #results-table-body tr.selected-product {
            background-color: #dbeafe !important;
        }
        
        .dark #results-table-body tr.selected-product {
            background-color: #1e3a8a !important;
        }
        
        /* Histogram container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Clear search button */
        #clear-search-btn {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        body.dark-mode .table-container { border-color: #4b5563; }

        .loading {
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

        .selectable-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .selectable-row:hover {
            background-color: #f3f4f6;
        }

        .selectable-row.selected {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
        }

        body.dark-mode .selectable-row:hover {
            background-color: #4b5563;
        }

        body.dark-mode .selectable-row.selected {
            background-color: #1e3a8a;
            border-left: 4px solid #60a5fa;
        }

        .four-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            width: 100%;
        }

        @media (max-width: 1400px) {
            .four-column-layout {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .four-column-layout {
                grid-template-columns: 1fr;
            }
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .results-container {
            min-height: 300px;
        }

        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        body.dark-mode .filter-badge {
            background-color: #1e40af;
            color: #dbeafe;
        }

        /* Loading Spinner Styles */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .dark .loading-overlay {
            background-color: rgba(17, 24, 39, 0.8);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .dark .loading-spinner {
            border-color: #374151;
            border-top-color: #60a5fa;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            margin-left: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .dark .loading-text {
            color: #9ca3af;
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

        .btn-primary:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
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

        #monthly-table-body .selectable-row:hover {
            background-color: #4b5563 !important;
        }

        /* Light mode override */
        body:not(.dark-mode):not(.dark) #monthly-table-body .selectable-row:hover {
            background-color: #f3f4f6 !important;
        }

        #monthly-details-table-body .selectable-row:hover {
            background-color: #4b5563 !important;
        }

        /* Light mode override for monthly details */
        body:not(.dark-mode):not(.dark) #monthly-details-table-body .selectable-row:hover {
            background-color: #f3f4f6 !important;
        }

        /* Search input styles */
        input[type="text"]:disabled {
            background-color: #f3f4f6 !important;
            color: #9ca3af !important;
            cursor: not-allowed;
        }

        .dark input[type="text"]:disabled {
            background-color: #374151 !important;
            color: #6b7280 !important;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 dark:text-white">
    <div class="w-full px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">Operator-Client-Supplier Analysis</h1>
            <p class="text-gray-600 dark:text-gray-300">Analyze sales relationships between operators, clients, and suppliers</p>
        </div>

        <!-- Controls -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label for="start-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" id="start-date" class="p-2 border border-gray-300 rounded dark:bg-gray-600 dark:text-white dark:border-gray-500">
                </div>
                <div>
                    <label for="end-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" id="end-date" class="p-2 border border-gray-300 rounded dark:bg-gray-600 dark:text-white dark:border-gray-500">
                </div>
                <div class="flex items-end">
                    <button id="fetch-data-btn" class="btn-primary">
                        <span id="fetch-btn-loading" class="loading hidden"></span>
                        <span id="fetch-btn-text">Load Data</span>
                    </button>
                </div>
                <div class="flex items-end">
                    <button id="clear-filters-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Clear Filters
                    </button>
                </div>
               
                <div class="flex items-end">
                    <button id="download-excel-btn" class="btn-excel">
                        <i class="fas fa-file-excel"></i>
                        Download Excel
                    </button>
                </div>
                
            </div>
        </div>

        <!-- Four Column Layout for Selection -->
        <div class="four-column-layout">
            <!-- Operators Column -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md relative">
                <!-- Loading Overlay -->
                <div id="operators-loading" class="loading-overlay hidden">
                    <div class="loading-spinner"></div>
                    <span class="loading-text">Loading operators...</span>
                </div>
                
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Operators</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400" id="operators-count">Click "Load Data" to start</span>
                </div>
                <div class="table-container">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody id="operators-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Click "Load Data" to load operators
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Clients Column -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md relative">
                <!-- Loading Overlay -->
                <div id="clients-loading" class="loading-overlay hidden">
                    <div class="loading-spinner"></div>
                    <span class="loading-text">Loading clients...</span>
                </div>
                
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Clients</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400" id="clients-count">Select an operator first</span>
                    <div class="mt-2">
                        <input type="text" id="client-search" placeholder="Search clients..." 
                               class="w-full p-2 text-sm border border-gray-300 rounded dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:placeholder-gray-400"
                               disabled>
                    </div>
                </div>
                <div class="table-container">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client Name</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody id="clients-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Select an operator to view clients
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Suppliers Column -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md relative">
                <!-- Loading Overlay -->
                <div id="suppliers-loading" class="loading-overlay hidden">
                    <div class="loading-spinner"></div>
                    <span class="loading-text">Loading suppliers...</span>
                </div>
                
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Suppliers</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400" id="suppliers-count">Select operator and client first</span>
                    <div class="mt-2">
                        <input type="text" id="supplier-search" placeholder="Search suppliers..." 
                               class="w-full p-2 text-sm border border-gray-300 rounded dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:placeholder-gray-400"
                               disabled>
                    </div>
                </div>
                <div class="table-container">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Supplier Name</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody id="suppliers-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Select operator and client to view suppliers
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Results Summary Column -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md relative">
                <!-- Loading Overlay -->
                <div id="summary-loading" class="loading-overlay hidden">
                    <div class="loading-spinner"></div>
                    <span class="loading-text">Loading summary...</span>
                </div>
                
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Selection Summary</h3>
                    <div id="selection-summary" class="space-y-2">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Make your selections to see summary</div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="space-y-2">
                        <button id="view-monthly-btn" class="btn-primary w-full">
                            <i class="fas fa-calendar-alt mr-2"></i>Monthly Trends
                        </button>
                        <button id="view-table-btn" class="btn-primary w-full">
                            <i class="fas fa-table mr-2"></i>View Table
                        </button>
                        <button id="view-chart-btn" class="btn-primary w-full">
                            <i class="fas fa-chart-bar mr-2"></i>View Chart
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md results-container relative">
            <!-- Loading Overlay -->
            <div id="results-loading" class="loading-overlay hidden">
                <div class="loading-spinner"></div>
                <span class="loading-text">Loading analysis results...</span>
            </div>
            
            <div class="p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white" id="results-title">Analysis Results</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="results-subtitle">Select operator, client, and supplier to see detailed results</p>
                
                <!-- Product Search -->
                <div class="mt-3 hidden" id="product-search-container">
                    <div class="relative">
                        <input type="text" 
                               id="product-search" 
                               placeholder="Search products..." 
                               class="w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <button id="clear-search-btn" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Table View -->
            <div id="table-view" class="hidden">
                <div class="flex">
                    <!-- Left side: Table -->
                    <div class="w-1/2 pr-2">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Discount %</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="results-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Right side: Histogram -->
                    <div class="w-1/2 pl-2">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Sales Trend Analysis</h4>
                            <div class="chart-container">
                                <canvas id="product-histogram" height="300"></canvas>
                            </div>
                            <div id="histogram-info" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                Click on a product row to see its sales trend over time
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart View -->
            <div id="chart-view" class="hidden p-6">
                <div class="chart-container">
                    <canvas id="results-chart"></canvas>
                </div>
            </div>

            <!-- Monthly View -->
            <div id="monthly-view" class="hidden relative">
                <!-- Loading Overlay -->
                <div id="monthly-loading" class="loading-overlay hidden">
                    <div class="loading-spinner"></div>
                    <span class="loading-text">Loading monthly trends...</span>
                </div>
                
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Monthly Sales Trends</h4>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">View:</label>
                            <select id="chart-type-select" class="px-3 py-1 border border-gray-300 rounded dark:bg-gray-600 dark:text-white dark:border-gray-500">
                                <option value="bar">Histogram</option>
                                <option value="line">Line Chart</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Monthly Chart -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <canvas id="monthly-chart" width="400" height="300"></canvas>
                        </div>
                        <!-- Monthly Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Month</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Products</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="monthly-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Select operator, client, and supplier to view monthly trends
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Monthly Details Section -->
                    <div id="monthly-details-section" class="mt-6 hidden relative">
                        <!-- Loading Overlay -->
                        <div id="monthly-details-loading" class="loading-overlay hidden">
                            <div class="loading-spinner"></div>
                            <span class="loading-text">Loading month details...</span>
                        </div>
                        
                        <h5 class="text-lg font-semibold text-gray-800 dark:text-white mb-4" id="monthly-details-title">Details for Selected Month</h5>
                        
                        <!-- Monthly Details Product Search -->
                        <div class="mb-4">
                            <div class="relative">
                                <input type="text" 
                                       id="monthly-details-search" 
                                       placeholder="Search products in this month..." 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Discount %</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="monthly-details-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="p-8 text-center text-gray-500 dark:text-gray-400">
                <i class="fas fa-chart-line text-4xl mb-4"></i>
                <p>Complete your selection to view detailed analysis results</p>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentData = {
            operators: [],
            clients: [],
            suppliers: [],
            results: [],
            monthlyData: [],
            monthlyDetails: [],
            originalClients: [],
            originalSuppliers: []
        };
        
        let selectedFilters = {
            operator: null,
            client: null,
            supplier: null
        };
        
        // Track the last combination for which monthly data was fetched
        let lastMonthlyDataCombination = {
            operator: null,
            client: null,
            supplier: null
        };
        
        let selectedMonth = {
            year: null,
            month: null,
            monthName: null
        };
        
        let chart = null;
        let monthlyChart = null;
        
        // User permission data from PHP
        const userRole = '<?php echo htmlspecialchars($userRole); ?>';
        const userName = '<?php echo htmlspecialchars($userName); ?>';
        const canSeeAllData = <?php echo $canSeeAllData ? 'true' : 'false'; ?>;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            initializeDateInputs();
        });

        function initializeDateInputs() {
            // Set default dates - last 30 days
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            
            document.getElementById('end-date').value = endDate.toISOString().split('T')[0];
            document.getElementById('start-date').value = startDate.toISOString().split('T')[0];
        }

        function setupEventListeners() {
            document.getElementById('fetch-data-btn').addEventListener('click', function() {
                // If we have existing selections, refresh with current selections
                // Otherwise, fetch initial data (original behavior)
                if (selectedFilters.operator) {
                    refreshDataWithCurrentSelections();
                } else {
                    fetchInitialData();
                }
            });
            document.getElementById('clear-filters-btn').addEventListener('click', clearAllFilters);
            
            // Only add download button listener if the button exists (admin roles only)
            const downloadBtn = document.getElementById('download-excel-btn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', downloadExcel);
            }
            
            document.getElementById('view-table-btn').addEventListener('click', () => showView('table'));
            document.getElementById('view-chart-btn').addEventListener('click', () => showView('chart'));
            document.getElementById('view-monthly-btn').addEventListener('click', () => showView('monthly'));
            
            // Chart type selection
            document.getElementById('chart-type-select').addEventListener('change', function() {
                if (currentData.monthlyData && currentData.monthlyData.length > 0) {
                    updateMonthlyChart(currentData.monthlyData);
                }
            });
            
            // Search event listeners
            document.getElementById('client-search').addEventListener('input', filterClients);
            document.getElementById('supplier-search').addEventListener('input', filterSuppliers);
            document.getElementById('product-search').addEventListener('input', filterProducts);
            document.getElementById('clear-search-btn').addEventListener('click', clearProductSelection);
            
            // Monthly details search listener is added dynamically when section is shown
            
            // Removed auto-fetch when dates change - data should only load when Load button is clicked
            /*
            document.getElementById('start-date').addEventListener('change', function() {
                refreshDataWithCurrentSelections();
            });
            document.getElementById('end-date').addEventListener('change', function() {
                refreshDataWithCurrentSelections();
            });
            */
        }

        async function fetchInitialData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const fetchBtn = document.getElementById('fetch-data-btn');
            const fetchBtnText = document.getElementById('fetch-btn-text');
            const fetchBtnLoading = document.getElementById('fetch-btn-loading');

            // Validate dates
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date must be before end date');
                return;
            }

            // Show loading state
            fetchBtnText.textContent = 'Loading...';
            fetchBtnLoading.classList.remove('hidden');
            fetchBtn.disabled = true;
            
            // Show operators loading
            showLoading('operators-loading');

            try {
                // Build API URL with role parameters
                let apiUrl = `/operator-client-supplier-analysis?start_date=${startDate}&end_date=${endDate}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }
                
                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                currentData.operators = data;
                updateOperatorsTable(data);
                
                // Clear previous selections
                clearAllFilters();

            } catch (error) {
                console.error('Error fetching data:', error);
                showError('Failed to fetch data: ' + error.message);
            } finally {
                // Reset loading state
                fetchBtnText.textContent = 'Load Data';
                fetchBtnLoading.classList.add('hidden');
                fetchBtn.disabled = false;
                
                // Hide operators loading
                hideLoading('operators-loading');
            }
        }

        function updateOperatorsTable(data) {
            const tableBody = document.getElementById('operators-table-body');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No operators found for the selected date range</td></tr>';
                document.getElementById('operators-count').textContent = '0 operators';
                return;
            }

            // Group data by operator and calculate totals
            const operatorSummary = {};
            data.forEach(row => {
                if (!operatorSummary[row.OPERATEUR]) {
                    operatorSummary[row.OPERATEUR] = {
                        name: row.OPERATEUR,
                        totalSales: 0
                    };
                }
                operatorSummary[row.OPERATEUR].totalSales += parseFloat(row.CHIFFRE_AFFAIRES) || 0;
            });

            let operators = Object.values(operatorSummary);
            
            // Backend now handles role-based filtering, no need for frontend filtering
            
            tableBody.innerHTML = '';
            
            operators.forEach(operator => {
                const tr = document.createElement('tr');
                tr.className = 'selectable-row';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-white">${operator.name}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(operator.totalSales)}</td>
                `;
                tr.addEventListener('click', () => selectOperator(operator.name, tr));
                tableBody.appendChild(tr);
            });

            document.getElementById('operators-count').textContent = `${operators.length} operators`;
            
            // Auto-select operator for Vente role if there's only one operator
            if (userRole === 'Vente' && operators.length === 1) {
                // Use setTimeout to ensure the DOM is fully updated before selection
                setTimeout(() => {
                    const firstRow = tableBody.querySelector('.selectable-row');
                    if (firstRow) {
                        console.log('Auto-selecting operator for Vente role:', operators[0].name);
                        selectOperator(operators[0].name, firstRow);
                        console.log('Auto-selection completed, row classes:', firstRow.className);
                    }
                }, 100);
            }
        }

        async function refreshDataWithCurrentSelections() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            // Validate dates
            if (!startDate || !endDate) {
                return; // Don't refresh if dates are not set
            }

            if (new Date(startDate) > new Date(endDate)) {
                return; // Don't refresh if dates are invalid
            }

            // If no operator is selected, fetch initial data (original behavior)
            if (!selectedFilters.operator) {
                if (currentData.operators.length > 0) {
                    fetchInitialData();
                }
                return;
            }

            // If operator is selected, refresh data while preserving selections
            try {
                // Show loading state
                showLoading('operators-loading');

                // Fetch updated operators data
                let apiUrl = `/operator-client-supplier-analysis?start_date=${startDate}&end_date=${endDate}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }

                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Update operators data
                currentData.operators = data;
                updateOperatorsTable(data);

                // Re-select the previously selected operator (if it still exists)
                const operatorExists = data.some(op => op.OPERATEUR === selectedFilters.operator);
                if (operatorExists) {
                    // Find and select the operator row
                    const operatorRows = document.querySelectorAll('#operators-table-body .selectable-row');
                    for (let row of operatorRows) {
                        const operatorName = row.cells[0].textContent.trim();
                        if (operatorName === selectedFilters.operator) {
                            // Temporarily store current client/supplier selections
                            const currentClient = selectedFilters.client;
                            const currentSupplier = selectedFilters.supplier;
                            
                            // Select the operator (this will clear client/supplier)
                            selectOperator(selectedFilters.operator, row);
                            
                            // If we had a client selected, try to re-select it
                            if (currentClient) {
                                // Wait for clients to load, then try to select the client
                                const checkClientsLoaded = setInterval(() => {
                                    const clientRows = document.querySelectorAll('#clients-table-body .selectable-row');
                                    if (clientRows.length > 0) {
                                        clearInterval(checkClientsLoaded);
                                        for (let clientRow of clientRows) {
                                            const clientName = clientRow.cells[0].textContent.trim();
                                            if (clientName === currentClient) {
                                                // Temporarily store supplier selection before calling selectClient
                                                const storedSupplier = currentSupplier;
                                                
                                                selectClient(currentClient, clientRow);
                                                
                                                // If we had a supplier selected, try to re-select it
                                                if (storedSupplier) {
                                                    console.log('Attempting to re-select supplier:', storedSupplier);
                                                    // Add a small delay to ensure suppliers table is fully rendered
                                                    setTimeout(() => {
                                                        const checkSuppliersLoaded = setInterval(() => {
                                                            const supplierRows = document.querySelectorAll('#suppliers-table-body .selectable-row');
                                                            console.log('Checking for suppliers, found:', supplierRows.length);
                                                            if (supplierRows.length > 0) {
                                                                clearInterval(checkSuppliersLoaded);
                                                                console.log('Suppliers loaded, looking for:', storedSupplier);
                                                                for (let supplierRow of supplierRows) {
                                                                    const supplierName = supplierRow.cells[0].textContent.trim();
                                                                    console.log('Checking supplier:', supplierName);
                                                                    if (supplierName === storedSupplier) {
                                                                        console.log('Found supplier, selecting:', storedSupplier);
                                                                        selectSupplier(storedSupplier, supplierRow);
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }, 100);
                                                        // Timeout after 5 seconds
                                                        setTimeout(() => {
                                                            clearInterval(checkSuppliersLoaded);
                                                            console.log('Timeout: Could not find supplier:', storedSupplier);
                                                        }, 5000);
                                                    }, 200); // Small delay to ensure table is rendered
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }, 100);
                                // Timeout after 5 seconds
                                setTimeout(() => clearInterval(checkClientsLoaded), 5000);
                            }
                            break;
                        }
                    }
                } else {
                    // If operator no longer exists, clear selections
                    clearAllFilters();
                }

            } catch (error) {
                console.error('Error refreshing data:', error);
                showError('Failed to refresh data: ' + error.message);
            } finally {
                hideLoading('operators-loading');
            }
        }

        function selectOperator(operatorName, row) {
            // Clear previous operator selection
            document.querySelectorAll('#operators-table-body .selectable-row').forEach(r => r.classList.remove('selected'));
            
            // Add selection to clicked row
            row.classList.add('selected');
            
            selectedFilters.operator = operatorName;
            selectedFilters.client = null;
            selectedFilters.supplier = null;
            
            // Clear subsequent tables
            clearClientsTable();
            clearSuppliersTable();
            clearResults();
            
            // Update selection summary
            updateSelectionSummary();
            
            // Fetch clients for this operator
            fetchClientsForOperator(operatorName);
        }

        async function fetchClientsForOperator(operatorName) {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            // Show clients loading
            showLoading('clients-loading');
            
            try {
                let apiUrl = `/operator-clients-analysis?operator=${encodeURIComponent(operatorName)}&start_date=${startDate}&end_date=${endDate}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }
                
                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                currentData.clients = data;
                updateClientsTable(data);

            } catch (error) {
                console.error('Error fetching clients:', error);
                showError('Failed to fetch clients: ' + error.message);
            } finally {
                // Hide clients loading
                hideLoading('clients-loading');
            }
        }

        function updateClientsTable(data) {
            const tableBody = document.getElementById('clients-table-body');
            const clientSearch = document.getElementById('client-search');
            
            // Store original data and enable search
            currentData.originalClients = data || [];
            clientSearch.disabled = !data || data.length === 0;
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No clients found for this operator</td></tr>';
                document.getElementById('clients-count').textContent = '0 clients';
                clientSearch.value = '';
                return;
            }

            renderClientsTable(data);
        }

        function renderClientsTable(data) {
            const tableBody = document.getElementById('clients-table-body');
            tableBody.innerHTML = '';
            
            data.forEach(client => {
                const tr = document.createElement('tr');
                tr.className = 'selectable-row';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-white">${client.CLIENT}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(client.CHIFFRE_AFFAIRES)}</td>
                `;
                tr.addEventListener('click', () => selectClient(client.CLIENT, tr));
                tableBody.appendChild(tr);
            });

            document.getElementById('clients-count').textContent = `${data.length} clients`;
        }

        function filterClients() {
            const searchTerm = document.getElementById('client-search').value.toLowerCase();
            const filteredClients = currentData.originalClients.filter(client => 
                client.CLIENT.toLowerCase().includes(searchTerm)
            );
            renderClientsTable(filteredClients);
        }

        function selectClient(clientName, row) {
            // Clear previous client selection
            document.querySelectorAll('#clients-table-body .selectable-row').forEach(r => r.classList.remove('selected'));
            
            // Add selection to clicked row
            row.classList.add('selected');
            
            selectedFilters.client = clientName;
            selectedFilters.supplier = null;
            
            // Clear subsequent tables
            clearSuppliersTable();
            clearResults();
            
            // Update selection summary
            updateSelectionSummary();
            
            // Fetch suppliers for this operator-client combination
            fetchSuppliersForOperatorClient(selectedFilters.operator, clientName);
        }

        async function fetchSuppliersForOperatorClient(operatorName, clientName) {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            // Show suppliers loading
            showLoading('suppliers-loading');
            
            try {
                let apiUrl = `/operator-client-suppliers-analysis?operator=${encodeURIComponent(operatorName)}&client=${encodeURIComponent(clientName)}&start_date=${startDate}&end_date=${endDate}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }
                
                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                currentData.suppliers = data;
                updateSuppliersTable(data);

            } catch (error) {
                console.error('Error fetching suppliers:', error);
                showError('Failed to fetch suppliers: ' + error.message);
            } finally {
                // Hide suppliers loading
                hideLoading('suppliers-loading');
            }
        }

        function updateSuppliersTable(data) {
            const tableBody = document.getElementById('suppliers-table-body');
            const supplierSearch = document.getElementById('supplier-search');
            
            // Store original data and enable search
            currentData.originalSuppliers = data || [];
            supplierSearch.disabled = !data || data.length === 0;
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No suppliers found for this combination</td></tr>';
                document.getElementById('suppliers-count').textContent = '0 suppliers';
                supplierSearch.value = '';
                return;
            }

            renderSuppliersTable(data);
        }

        function renderSuppliersTable(data) {
            const tableBody = document.getElementById('suppliers-table-body');
            tableBody.innerHTML = '';
            
            data.forEach(supplier => {
                const tr = document.createElement('tr');
                tr.className = 'selectable-row';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-white">${supplier.FOURNISSEUR}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(supplier.CHIFFRE_AFFAIRES)}</td>
                `;
                tr.addEventListener('click', () => selectSupplier(supplier.FOURNISSEUR, tr));
                tableBody.appendChild(tr);
            });

            document.getElementById('suppliers-count').textContent = `${data.length} suppliers`;
        }

        function filterSuppliers() {
            const searchTerm = document.getElementById('supplier-search').value.toLowerCase();
            const filteredSuppliers = currentData.originalSuppliers.filter(supplier => 
                supplier.FOURNISSEUR.toLowerCase().includes(searchTerm)
            );
            renderSuppliersTable(filteredSuppliers);
        }

        function filterProducts() {
            const searchInput = document.getElementById('product-search');
            const clearBtn = document.getElementById('clear-search-btn');
            const searchTerm = searchInput.value.toLowerCase();
            
            // Show/hide clear button
            if (searchTerm) {
                clearBtn.style.opacity = '1';
                clearBtn.style.pointerEvents = 'auto';
            } else {
                clearBtn.style.opacity = '0';
                clearBtn.style.pointerEvents = 'none';
            }
            
            // If search is cleared, also clear selection
            if (!searchTerm && selectedProduct) {
                selectedProduct = null;
                // Remove highlighting
                const rows = document.querySelectorAll('#results-table-body tr');
                rows.forEach(row => {
                    row.classList.remove('selected-product');
                    row.classList.add('hover:bg-gray-50', 'dark:hover:bg-gray-700');
                });
            }
            
            updateResultsTable();
        }

        function filterMonthlyDetails() {
            console.log('filterMonthlyDetails called');
            console.log('currentData.monthlyDetails:', currentData.monthlyDetails);
            
            if (currentData.monthlyDetails && currentData.monthlyDetails.length > 0) {
                const titleElement = document.getElementById('monthly-details-title');
                const monthName = titleElement.textContent.replace('Details for ', '');
                console.log('Month name extracted:', monthName);
                updateMonthlyDetailsTable(currentData.monthlyDetails, monthName);
            } else {
                console.log('No monthly details data available');
            }
        }

        function selectSupplier(supplierName, row) {
            // Clear previous supplier selection
            document.querySelectorAll('#suppliers-table-body .selectable-row').forEach(r => r.classList.remove('selected'));
            
            // Add selection to clicked row
            row.classList.add('selected');
            
            selectedFilters.supplier = supplierName;
            
            // Clear product selection and search bars
            selectedProduct = null;
            const productSearchInput = document.getElementById('product-search');
            if (productSearchInput) {
                productSearchInput.value = '';
            }
            const monthlySearchInput = document.getElementById('monthly-details-search');
            if (monthlySearchInput) {
                monthlySearchInput.value = '';
            }
            
            // Note: Don't clear monthly data here - let showView() handle it based on combination changes
            // This allows switching between views without losing data if supplier hasn't changed
            
            // Update selection summary
            updateSelectionSummary();
            
            // Fetch detailed results
            fetchDetailedResults();
        }

        async function fetchDetailedResults() {
            if (!selectedFilters.operator || !selectedFilters.client || !selectedFilters.supplier) {
                return;
            }
            
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            // Show results loading
            showLoading('results-loading');
            
            try {
                let apiUrl = `/operator-client-supplier-detailed-results?operator=${encodeURIComponent(selectedFilters.operator)}&client=${encodeURIComponent(selectedFilters.client)}&supplier=${encodeURIComponent(selectedFilters.supplier)}&start_date=${startDate}&end_date=${endDate}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }
                
                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                currentData.results = data;
                
                // Update results display
                document.getElementById('results-title').textContent = 'Detailed Analysis Results';
                document.getElementById('results-subtitle').textContent = `${data.length} transactions found`;
                
                // Show monthly view by default
                showView('monthly');

            } catch (error) {
                console.error('Error fetching detailed results:', error);
                showError('Failed to fetch detailed results: ' + error.message);
            } finally {
                // Hide results loading
                hideLoading('results-loading');
            }
        }

        function updateSelectionSummary() {
            const summaryDiv = document.getElementById('selection-summary');
            
            if (!selectedFilters.operator) {
                summaryDiv.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400">Select an operator to start</div>';
                return;
            }
            
            const hasSelection = selectedFilters.operator || selectedFilters.client || selectedFilters.supplier;
            
            if (hasSelection) {
                summaryDiv.innerHTML = `
                    <div class="space-y-1">
                        ${selectedFilters.operator ? `
                            <div class="filter-badge">
                                <i class="fas fa-user"></i>
                                <span>Operator: ${selectedFilters.operator}</span>
                            </div>
                        ` : ''}
                        ${selectedFilters.client ? `
                            <div class="filter-badge">
                                <i class="fas fa-building"></i>
                                <span>Client: ${selectedFilters.client}</span>
                            </div>
                        ` : ''}
                        ${selectedFilters.supplier ? `
                            <div class="filter-badge">
                                <i class="fas fa-truck"></i>
                                <span>Supplier: ${selectedFilters.supplier}</span>
                            </div>
                        ` : ''}
                    </div>
                `;
            }
        }

        function showView(viewType) {
            // Hide all views
            document.getElementById('table-view').classList.add('hidden');
            document.getElementById('chart-view').classList.add('hidden');
            document.getElementById('monthly-view').classList.add('hidden');
            document.getElementById('empty-state').classList.add('hidden');
            
            if (viewType === 'monthly') {
                if (!selectedFilters.operator || !selectedFilters.client || !selectedFilters.supplier) {
                    document.getElementById('empty-state').classList.remove('hidden');
                    return;
                }
                
                // Check if the combination has changed since last monthly data fetch
                const combinationChanged = (
                    lastMonthlyDataCombination.operator !== selectedFilters.operator ||
                    lastMonthlyDataCombination.client !== selectedFilters.client ||
                    lastMonthlyDataCombination.supplier !== selectedFilters.supplier
                );
                
                document.getElementById('monthly-view').classList.remove('hidden');
                
                if (combinationChanged || currentData.monthlyData.length === 0) {
                    // Clear existing data and fetch fresh data only if combination changed or no data exists
                    currentData.monthlyData = [];
                    currentData.monthlyDetails = [];
                    
                    // Clear selected month for fresh start
                    selectedMonth = {
                        year: null,
                        month: null,
                        monthName: null
                    };
                    
                    // Hide monthly details section
                    document.getElementById('monthly-details-section').classList.add('hidden');
                    
                    fetchMonthlyData().then(() => {
                        // Update the last combination after successful fetch
                        lastMonthlyDataCombination = {
                            operator: selectedFilters.operator,
                            client: selectedFilters.client,
                            supplier: selectedFilters.supplier
                        };
                    });
                } else {
                    // Use existing data, just update the display
                    updateMonthlyTable(currentData.monthlyData);
                    updateMonthlyChart(currentData.monthlyData);
                    
                    // Restore month selection after a brief delay
                    setTimeout(() => {
                        restoreMonthSelection();
                    }, 100);
                }
                return;
            }
            
            if (!currentData.results || currentData.results.length === 0) {
                document.getElementById('empty-state').classList.remove('hidden');
                return;
            }
            
            if (viewType === 'table') {
                document.getElementById('table-view').classList.remove('hidden');
                updateResultsTable();
            } else if (viewType === 'chart') {
                document.getElementById('chart-view').classList.remove('hidden');
                updateResultsChart();
            }
        }

        let selectedProduct = null;
        let productHistogramChart = null;

        function updateResultsTable() {
            const tableBody = document.getElementById('results-table-body');
            const searchContainer = document.getElementById('product-search-container');
            
            if (!currentData.results || currentData.results.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No results found</td></tr>';
                searchContainer.classList.add('hidden');
                updateProductHistogram([]);
                return;
            }

            // Show search container when there are results
            searchContainer.classList.remove('hidden');
            
            // Apply product search filter
            const searchTerm = document.getElementById('product-search').value.toLowerCase();
            const filteredResults = currentData.results.filter(row => 
                row.PRODUIT.toLowerCase().includes(searchTerm)
            );

            tableBody.innerHTML = '';
            
            if (filteredResults.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No products match your search</td></tr>';
                updateProductHistogram([]);
                return;
            }
            
            // Calculate totals
            let totalQuantity = 0;
            let totalAmount = 0;
            
            filteredResults.forEach(row => {
                totalQuantity += parseFloat(row.QUANTITE || 0);
                totalAmount += parseFloat(row.MONTANT || 0);
                
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200';
                tr.innerHTML = `
                    <td class="px-6 py-4 text-gray-900 dark:text-white">${row.PRODUIT}</td>
                    <td class="px-6 py-4 text-right text-gray-900 dark:text-white">${formatNumber(row.QUANTITE)}</td>
                    <td class="px-6 py-4 text-right text-gray-900 dark:text-white">${formatNumber(row.REMISE || 0)}%</td>
                    <td class="px-6 py-4 text-right text-gray-900 dark:text-white font-semibold">${formatNumber(row.MONTANT)}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-white">${formatDate(row.DATE_MOUVEMENT)}</td>
                `;
                
                // Add click handler for product selection
                tr.addEventListener('click', () => {
                    selectProduct(row.PRODUIT);
                });
                
                tableBody.appendChild(tr);
            });
            
            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'bg-gray-100 dark:bg-gray-600 border-t-2 border-gray-300 dark:border-gray-500';
            totalRow.innerHTML = `
                <td class="px-6 py-4 text-gray-900 dark:text-white font-bold">TOTAL</td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white font-bold">${formatNumber(totalQuantity)}</td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white">-</td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white font-bold">${formatNumber(totalAmount)}</td>
                <td class="px-6 py-4 text-gray-900 dark:text-white">-</td>
            `;
            tableBody.appendChild(totalRow);
            
            // Update histogram with current filtered results
            updateProductHistogram(filteredResults);
        }

        function selectProduct(productName) {
            selectedProduct = productName;
            
            // Update search input
            const searchInput = document.getElementById('product-search');
            searchInput.value = productName;
            
            // Trigger search to filter table
            updateResultsTable();
            
            // Highlight selected product rows
            highlightSelectedProduct(productName);
        }

        function clearProductSelection() {
            selectedProduct = null;
            
            // Clear search input
            const searchInput = document.getElementById('product-search');
            searchInput.value = '';
            
            // Update table and histogram
            updateResultsTable();
            
            // Remove highlighting
            const rows = document.querySelectorAll('#results-table-body tr');
            rows.forEach(row => {
                row.classList.remove('selected-product');
                row.classList.add('hover:bg-gray-50', 'dark:hover:bg-gray-700');
            });
        }

        function highlightSelectedProduct(productName) {
            const rows = document.querySelectorAll('#results-table-body tr');
            rows.forEach(row => {
                const productCell = row.cells[0];
                if (productCell && productCell.textContent.trim() === productName) {
                    row.classList.add('selected-product');
                    row.classList.remove('hover:bg-gray-50', 'dark:hover:bg-gray-700');
                } else {
                    row.classList.remove('selected-product');
                    row.classList.add('hover:bg-gray-50', 'dark:hover:bg-gray-700');
                }
            });
        }

        function updateProductHistogram(data) {
            const ctx = document.getElementById('product-histogram').getContext('2d');
            
            // Destroy existing chart
            if (productHistogramChart) {
                productHistogramChart.destroy();
            }
            
            // If no product is selected, show empty chart
            if (!selectedProduct) {
                document.getElementById('histogram-info').innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-400 dark:text-gray-500 mb-2">
                            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-1">No Product Selected</div>
                        <div class="text-sm text-gray-500 dark:text-gray-500">Click on a product row to view its sales trend</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">Note: 100% discount transactions are excluded • Daily amounts are aggregated</div>
                    </div>
                `;
                return;
            }
            
            if (!data || data.length === 0) {
                document.getElementById('histogram-info').textContent = 'No data to display';
                return;
            }
            
            // Filter data to only show selected product and exclude 100% discounts
            const filteredData = data.filter(row => 
                row.PRODUIT === selectedProduct && 
                parseFloat(row.REMISE || 0) < 100
            );
            
            if (filteredData.length === 0) {
                document.getElementById('histogram-info').innerHTML = `
                    <div class="text-center py-4">
                        <div class="text-gray-500 dark:text-gray-400 mb-2">No data available for selected product</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">Note: 100% discount transactions are excluded • Daily amounts are aggregated</div>
                    </div>
                `;
                return;
            }
            
            // Sort data by date for time series
            const sortedData = filteredData.sort((a, b) => new Date(a.DATE_MOUVEMENT) - new Date(b.DATE_MOUVEMENT));
            
            // Aggregate data by date for the chart (sum amounts for same date)
            const aggregatedData = {};
            
            sortedData.forEach(row => {
                const date = new Date(row.DATE_MOUVEMENT);
                const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD format
                
                if (!aggregatedData[dateKey]) {
                    aggregatedData[dateKey] = {
                        date: date,
                        totalAmount: 0,
                        totalQuantity: 0,
                        transactions: []
                    };
                }
                
                aggregatedData[dateKey].totalAmount += parseFloat(row.MONTANT || 0);
                aggregatedData[dateKey].totalQuantity += parseFloat(row.QUANTITE || 0);
                aggregatedData[dateKey].transactions.push({
                    amount: parseFloat(row.MONTANT || 0),
                    quantity: parseFloat(row.QUANTITE || 0),
                    discount: parseFloat(row.REMISE || 0)
                });
            });
            
            // Create time series data from aggregated data
            const timeSeriesData = [];
            const dateLabels = [];
            
            Object.keys(aggregatedData).sort().forEach(dateKey => {
                const data = aggregatedData[dateKey];
                const formattedDate = data.date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    year: 'numeric'
                });
                
                timeSeriesData.push({
                    x: data.date,
                    y: data.totalAmount,
                    product: selectedProduct,
                    quantity: data.totalQuantity,
                    discount: data.transactions.length > 1 ? 'Multiple' : data.transactions[0].discount,
                    transactionCount: data.transactions.length
                });
                
                dateLabels.push(formattedDate);
            });
            
            // Create line chart showing sales over time
            productHistogramChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Sales Amount (DA)',
                        data: timeSeriesData,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        fill: true,
                        pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'MMM dd, yyyy'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Date'
                            },
                            ticks: {
                                maxTicksLimit: 10
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Amount (DA)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value) + ' DA';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const date = new Date(context[0].parsed.x);
                                    return date.toLocaleDateString('en-US', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    });
                                },
                                label: function(context) {
                                    const data = context.raw;
                                    const tooltipLines = [
                                        `Amount: ${formatNumber(data.y)} DA`,
                                        `Quantity: ${formatNumber(data.quantity)}`
                                    ];
                                    
                                    if (data.transactionCount > 1) {
                                        tooltipLines.push(`Transactions: ${data.transactionCount}`);
                                        tooltipLines.push(`Avg Discount: ${data.discount}`);
                                    } else {
                                        tooltipLines.push(`Discount: ${formatNumber(data.discount)}%`);
                                    }
                                    
                                    tooltipLines.push(`Product: ${data.product}`);
                                    
                                    return tooltipLines;
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
            
            // Calculate trend statistics using aggregated daily data
            const dailyAmounts = Object.values(aggregatedData).map(day => day.totalAmount);
            const totalAmount = dailyAmounts.reduce((sum, amt) => sum + amt, 0);
            const avgAmount = totalAmount / dailyAmounts.length;
            
            // Calculate trend using linear regression
            let trend = 'stable';
            let trendDirection = '';
            let trendStrength = '';
            
            if (dailyAmounts.length > 1) {
                // Simple linear regression to calculate slope
                const n = dailyAmounts.length;
                const indices = Array.from({length: n}, (_, i) => i);
                
                const sumX = indices.reduce((sum, x) => sum + x, 0);
                const sumY = dailyAmounts.reduce((sum, y) => sum + y, 0);
                const sumXY = indices.reduce((sum, x, i) => sum + (x * dailyAmounts[i]), 0);
                const sumXX = indices.reduce((sum, x) => sum + (x * x), 0);
                
                const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
                
                // Calculate trend strength as percentage change
                const firstValue = dailyAmounts[0];
                const lastValue = dailyAmounts[dailyAmounts.length - 1];
                const percentChange = ((lastValue - firstValue) / firstValue) * 100;
                
                if (slope > avgAmount * 0.005) {
                    trend = '↗️ increasing';
                    trendDirection = 'up';
                } else if (slope < -avgAmount * 0.005) {
                    trend = '↘️ decreasing';
                    trendDirection = 'down';
                } else {
                    trend = '➡️ stable';
                    trendDirection = 'stable';
                }
                
                // Add strength indicator
                const absPercentChange = Math.abs(percentChange);
                if (absPercentChange > 50) {
                    trendStrength = ' (strong)';
                } else if (absPercentChange > 20) {
                    trendStrength = ' (moderate)';
                } else if (absPercentChange > 5) {
                    trendStrength = ' (weak)';
                }
                
                trend += trendStrength;
            }
            
            // Calculate additional statistics
            const maxAmount = Math.max(...dailyAmounts);
            const minAmount = Math.min(...dailyAmounts);
            const volatility = dailyAmounts.length > 1 ? 
                (dailyAmounts.reduce((sum, amt, i) => {
                    if (i === 0) return 0;
                    const change = ((amt - dailyAmounts[i-1]) / dailyAmounts[i-1]) * 100;
                    return sum + Math.abs(change);
                }, 0) / (dailyAmounts.length - 1)) : 0;
            
            // Update histogram info
            document.getElementById('histogram-info').innerHTML = `
                <div class="grid grid-cols-2 gap-4 text-center mb-3">
                    <div>
                        <div class="font-semibold text-lg">${formatNumber(totalAmount)} DA</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Sales</div>
                    </div>
                    <div>
                        <div class="font-semibold text-lg">${trend}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Trend</div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-sm">
                    <div>
                        <div class="font-medium">${formatNumber(avgAmount)} DA</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Avg Sale</div>
                    </div>
                    <div>
                        <div class="font-medium">${formatNumber(maxAmount)} DA</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Peak Sale</div>
                    </div>
                    <div>
                        <div class="font-medium">${formatNumber(volatility, 1)}%</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Volatility</div>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                        ${timeSeriesData.length} data points • ${sortedData.length} total transactions
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">
                        ${dateLabels[0] || ''} to ${dateLabels[dateLabels.length - 1] || ''}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        Excluding 100% discount transactions • Daily amounts aggregated
                    </div>
                </div>
            `;
        }

        function updateResultsChart() {
            const ctx = document.getElementById('results-chart').getContext('2d');
            
            // Destroy existing chart
            if (chart) {
                chart.destroy();
            }

            if (!currentData.results || currentData.results.length === 0) {
                return;
            }

            // Group data by product for chart
            const productSummary = {};
            currentData.results.forEach(row => {
                if (!productSummary[row.PRODUIT]) {
                    productSummary[row.PRODUIT] = {
                        total: 0,
                        quantity: 0
                    };
                }
                productSummary[row.PRODUIT].total += parseFloat(row.MONTANT) || 0;
                productSummary[row.PRODUIT].quantity += parseFloat(row.QUANTITE) || 0;
            });

            const products = Object.keys(productSummary);
            const totals = products.map(p => productSummary[p].total);

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: products,
                    datasets: [{
                        label: 'Total Sales Amount',
                        data: totals,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: `Sales: ${selectedFilters.operator} → ${selectedFilters.client} → ${selectedFilters.supplier}`
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value);
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    }
                }
            });
        }

        function clearAllFilters() {
            selectedFilters = {
                operator: null,
                client: null,
                supplier: null
            };
            
            // Clear all table selections
            document.querySelectorAll('.selectable-row').forEach(r => r.classList.remove('selected'));
            
            clearClientsTable();
            clearSuppliersTable();
            clearResults();
            updateSelectionSummary();
        }

        function clearClientsTable() {
            document.getElementById('clients-table-body').innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Select an operator to view clients</td></tr>';
            document.getElementById('clients-count').textContent = 'Select an operator first';
            
            // Clear and disable client search
            const clientSearch = document.getElementById('client-search');
            clientSearch.value = '';
            clientSearch.disabled = true;
            currentData.originalClients = [];
        }

        function clearSuppliersTable() {
            document.getElementById('suppliers-table-body').innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Select operator and client to view suppliers</td></tr>';
            document.getElementById('suppliers-count').textContent = 'Select operator and client first';
            
            // Clear and disable supplier search
            const supplierSearch = document.getElementById('supplier-search');
            supplierSearch.value = '';
            supplierSearch.disabled = true;
            currentData.originalSuppliers = [];
        }

        function clearResults() {
            currentData.results = [];
            currentData.monthlyData = [];
            currentData.monthlyDetails = [];
            
            // Reset the monthly data combination tracking
            lastMonthlyDataCombination = {
                operator: null,
                client: null,
                supplier: null
            };
            
            // Clear selected month
            selectedMonth = {
                year: null,
                month: null,
                monthName: null
            };
            
            document.getElementById('empty-state').classList.remove('hidden');
            document.getElementById('table-view').classList.add('hidden');
            document.getElementById('chart-view').classList.add('hidden');
            document.getElementById('monthly-view').classList.add('hidden');
            document.getElementById('monthly-details-section').classList.add('hidden');
            document.getElementById('product-search-container').classList.add('hidden');
            
            // Clear product searches
            document.getElementById('product-search').value = '';
            document.getElementById('monthly-details-search').value = '';
            
            document.getElementById('results-title').textContent = 'Analysis Results';
            document.getElementById('results-subtitle').textContent = 'Select operator, client, and supplier to see detailed results';
        }

        async function downloadExcel() {
            if (!selectedFilters.operator || !selectedFilters.client || !selectedFilters.supplier) {
                alert('Please make complete selections before downloading');
                return;
            }
            
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            try {
                let url = `/download-operator-client-supplier-excel?operator=${encodeURIComponent(selectedFilters.operator)}&client=${encodeURIComponent(selectedFilters.client)}&supplier=${encodeURIComponent(selectedFilters.supplier)}&start_date=${startDate}&end_date=${endDate}`;
                
                if (userRole) {
                    url += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    url += `&username=${encodeURIComponent(userName)}`;
                }

                const link = document.createElement('a');
                link.href = API_CONFIG.getApiUrl(url);
                
                // Create filename
                const today = new Date().toISOString().split('T')[0];
                const filename = `Operator_Client_Supplier_Analysis_${selectedFilters.operator}_${selectedFilters.client}_${selectedFilters.supplier}_${startDate}_to_${endDate}.xlsx`;
                link.download = filename;
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
            } catch (error) {
                console.error('Error downloading Excel:', error);
                showError('Failed to download Excel file');
            }
        }

        // Monthly Data Functions
        async function fetchMonthlyData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!selectedFilters.operator || !selectedFilters.client || !selectedFilters.supplier) {
                return;
            }
            
            // Show monthly loading
            showLoading('monthly-loading');
            
            try {
                let apiUrl = `/operator-client-supplier-monthly-sales?operator=${encodeURIComponent(selectedFilters.operator)}&client=${encodeURIComponent(selectedFilters.client)}&supplier=${encodeURIComponent(selectedFilters.supplier)}&start_date=${startDate}&end_date=${endDate}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }
                
                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                currentData.monthlyData = data;
                updateMonthlyTable(data);
                updateMonthlyChart(data);

            } catch (error) {
                console.error('Error fetching monthly data:', error);
                showError('Failed to fetch monthly data: ' + error.message);
            } finally {
                // Hide monthly loading
                hideLoading('monthly-loading');
            }
        }

        function updateMonthlyTable(data) {
            const tableBody = document.getElementById('monthly-table-body');
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No monthly data found</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            
            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'selectable-row cursor-pointer';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-white">${row.MONTH_NAME}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(row.NOMBRE_PRODUITS)}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(row.QTY_TOTALE)}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-semibold">${formatNumber(row.CHIFFRE_AFFAIRES)}</td>
                `;
                tr.addEventListener('click', (event) => selectMonth(event, row.YEAR, row.MONTH, row.MONTH_NAME));
                tableBody.appendChild(tr);
            });
            
            // Restore month selection if there was one
            setTimeout(() => {
                restoreMonthSelection();
            }, 50);
        }

        function updateMonthlyChart(data) {
            const ctx = document.getElementById('monthly-chart').getContext('2d');
            
            // Destroy existing chart
            if (monthlyChart) {
                monthlyChart.destroy();
            }

            if (!data || data.length === 0) {
                return;
            }

            const months = data.map(row => row.MONTH_NAME);
            const sales = data.map(row => parseFloat(row.CHIFFRE_AFFAIRES) || 0);
            
            const chartType = document.getElementById('chart-type-select').value;

            monthlyChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Sales',
                        data: sales,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: chartType === 'line' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(59, 130, 246, 0.8)',
                        fill: chartType === 'line',
                        tension: chartType === 'line' ? 0.4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: `Monthly Sales Trend: ${selectedFilters.operator} → ${selectedFilters.client} → ${selectedFilters.supplier}`
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value);
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    }
                }
            });
        }

        async function selectMonth(event, year, month, monthName) {
            // Store selected month information
            selectedMonth = {
                year: year,
                month: month,
                monthName: monthName
            };
            
            // Clear previous month selection
            document.querySelectorAll('#monthly-table-body .selectable-row').forEach(r => r.classList.remove('selected'));
            
            // Add selection to clicked row
            const clickedRow = event.target.closest('tr');
            if (clickedRow) {
                clickedRow.classList.add('selected');
            }
            
            // Show monthly details loading
            showLoading('monthly-details-loading');
            
            try {
                let apiUrl = `/operator-client-supplier-monthly-details?year=${year}&month=${month}&operator=${encodeURIComponent(selectedFilters.operator)}&client=${encodeURIComponent(selectedFilters.client)}&supplier=${encodeURIComponent(selectedFilters.supplier)}`;
                if (userRole) {
                    apiUrl += `&user_role=${encodeURIComponent(userRole)}`;
                }
                if (userName) {
                    apiUrl += `&username=${encodeURIComponent(userName)}`;
                }
                
                const response = await fetch(API_CONFIG.getApiUrl(apiUrl));
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                currentData.monthlyDetails = data;
                
                // Clear the monthly details search
                document.getElementById('monthly-details-search').value = '';
                
                updateMonthlyDetailsTable(data, monthName);
                
                // Show the details section
                document.getElementById('monthly-details-section').classList.remove('hidden');
                
                // Add event listener for search if not already added
                const searchInput = document.getElementById('monthly-details-search');
                if (searchInput && !searchInput.hasAttribute('data-listener-added')) {
                    searchInput.addEventListener('input', filterMonthlyDetails);
                    searchInput.setAttribute('data-listener-added', 'true');
                    console.log('Event listener added to monthly details search');
                }

            } catch (error) {
                console.error('Error fetching monthly details:', error);
                showError('Failed to fetch monthly details: ' + error.message);
            } finally {
                // Hide monthly details loading
                hideLoading('monthly-details-loading');
            }
        }

        function restoreMonthSelection() {
            if (selectedMonth.year && selectedMonth.month && selectedMonth.monthName) {
                // Find the row that matches the selected month
                const rows = document.querySelectorAll('#monthly-table-body .selectable-row');
                rows.forEach(row => {
                    const monthCell = row.cells[0]; // First cell contains month name
                    if (monthCell && monthCell.textContent.trim() === selectedMonth.monthName) {
                        row.classList.add('selected');
                    }
                });
                
                // If monthly details section was visible, keep it visible
                if (currentData.monthlyDetails && currentData.monthlyDetails.length > 0) {
                    document.getElementById('monthly-details-section').classList.remove('hidden');
                }
            }
        }

        function selectMonthlyDetailProduct(productName) {
            // Put the product name in the monthly details search field
            const searchInput = document.getElementById('monthly-details-search');
            if (searchInput) {
                searchInput.value = productName;
                // Trigger input event to update filtering
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        function updateMonthlyDetailsTable(data, monthName) {
            const tableBody = document.getElementById('monthly-details-table-body');
            document.getElementById('monthly-details-title').textContent = `Details for ${monthName}`;
            
            console.log('updateMonthlyDetailsTable called with:', { dataLength: data?.length, monthName });
            
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No details found for this month</td></tr>';
                return;
            }

            // Apply search filter
            const searchInput = document.getElementById('monthly-details-search');
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            console.log('Search term:', searchTerm);
            
            const filteredData = data.filter(row => 
                row.PRODUIT.toLowerCase().includes(searchTerm)
            );
            console.log('Filtered data length:', filteredData.length);

            tableBody.innerHTML = '';
            
            if (filteredData.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No products match your search</td></tr>';
                return;
            }
            
            // Calculate totals
            let totalQuantity = 0;
            let totalAmount = 0;
            
            filteredData.forEach(row => {
                totalQuantity += parseFloat(row.QUANTITE || 0);
                totalAmount += parseFloat(row.MONTANT || 0);
                
                const tr = document.createElement('tr');
                tr.className = 'selectable-row cursor-pointer';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-white">${formatDate(row.DATE_MOUVEMENT)}</td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">${row.PRODUIT}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(row.QUANTITE)}</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${formatNumber(row.REMISE || 0)}%</td>
                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-semibold">${formatNumber(row.MONTANT)}</td>
                `;
                tr.addEventListener('click', () => selectMonthlyDetailProduct(row.PRODUIT));
                tableBody.appendChild(tr);
            });
            
            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'bg-gray-100 dark:bg-gray-600 border-t-2 border-gray-300 dark:border-gray-500';
            totalRow.innerHTML = `
                <td class="px-4 py-3 text-gray-900 dark:text-white">-</td>
                <td class="px-4 py-3 text-gray-900 dark:text-white font-bold">TOTAL</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-bold">${formatNumber(totalQuantity)}</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">-</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-bold">${formatNumber(totalAmount)}</td>
            `;
            tableBody.appendChild(totalRow);
        }

        // Utility Functions
        function showLoading(elementId) {
            const loadingElement = document.getElementById(elementId);
            if (loadingElement) {
                loadingElement.classList.remove('hidden');
            }
        }

        function hideLoading(elementId) {
            const loadingElement = document.getElementById(elementId);
            if (loadingElement) {
                loadingElement.classList.add('hidden');
            }
        }

        function showError(message) {
            alert(message); // Simple error display, can be enhanced with toast notifications
        }

        function formatNumber(num) {
            if (num === null || num === undefined) return '0';
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }
    </script>
</body>
</html>
