<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * INVENTORY ADMINISTRATION PAGE
 * 
 * Status Workflow:
 * 1. pending (initial state) → can be: confirmed OR canceled
 * 2. confirmed (waiting to be done) → can be: done OR canceled  
 * 3. canceled → can be: pending (reopened)
 * 4. done (final state) → cannot be changed
 * 
 * All status changes are validated by the Python API and saved to database
 */

session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['username'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Check admin privileges - only allow specific admin roles
if (!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Admin', 'Developer', 'DRH'])) {
    header("Location: Access_Denied");    
    exit();
}

// Get filter parameters for URL state
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d'); // Default to today
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); // Default to today
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$product_search = isset($_GET['product_search']) ? trim($_GET['product_search']) : '';

// Initialize empty array - data will be loaded via JavaScript
$inventories = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Administration</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="theme.js" defer></script>
    <script src="api_config_inv.js" defer></script>


    <style>
        /* Professional Color Palette */
        :root {
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-200: #bfdbfe;
            --primary-300: #93c5fd;
            --primary-400: #60a5fa;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --primary-800: #1e40af;
            --primary-900: #1e3a8a;

            --success-50: #f0fdf4;
            --success-100: #dcfce7;
            --success-500: #22c55e;
            --success-600: #16a34a;
            --success-700: #15803d;

            --warning-50: #fffbeb;
            --warning-100: #fef3c7;
            --warning-500: #f59e0b;
            --warning-600: #d97706;
            --warning-700: #b45309;

            --danger-50: #fef2f2;
            --danger-100: #fee2e2;
            --danger-500: #ef4444;
            --danger-600: #dc2626;
            --danger-700: #b91c1c;

            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        /* Modern Status Badges */
        .status-pending {
            background: linear-gradient(135deg, var(--warning-100) 0%, var(--warning-50) 100%);
            color: var(--warning-700);
            border: 1px solid var(--warning-200);
            box-shadow: 0 1px 2px 0 rgba(245, 158, 11, 0.05);
        }
        .status-confirmed {
            background: linear-gradient(135deg, var(--primary-100) 0%, var(--primary-50) 100%);
            color: var(--primary-700);
            border: 1px solid var(--primary-200);
            box-shadow: 0 1px 2px 0 rgba(59, 130, 246, 0.05);
        }
        .status-canceled {
            background: linear-gradient(135deg, var(--danger-100) 0%, var(--danger-50) 100%);
            color: var(--danger-700);
            border: 1px solid var(--danger-200);
            box-shadow: 0 1px 2px 0 rgba(239, 68, 68, 0.05);
        }
        .status-done {
            background: linear-gradient(135deg, var(--success-100) 0%, var(--success-50) 100%);
            color: var(--success-700);
            border: 1px solid var(--success-200);
            box-shadow: 0 1px 2px 0 rgba(34, 197, 94, 0.05);
        }

        /* Enhanced Card Design */
        .card {
            background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }

        .card-header {
            background: linear-gradient(135deg, var(--gray-50) 0%, #ffffff 100%);
            border-bottom: 1px solid var(--gray-200);
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
            position: relative;
        }
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-200), transparent);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Professional Badge Design */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        .badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Modern Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0.25rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .btn:hover::before {
            left: 100%;
        }

        .btn-confirm {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }
        .btn-confirm:hover {
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: linear-gradient(135deg, var(--danger-500) 0%, var(--danger-600) 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }
        .btn-cancel:hover {
            background: linear-gradient(135deg, var(--danger-600) 0%, var(--danger-700) 100%);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }

        .btn-done {
            background: linear-gradient(135deg, var(--success-500) 0%, var(--success-600) 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);
        }
        .btn-done:hover {
            background: linear-gradient(135deg, var(--success-600) 0%, var(--success-700) 100%);
            box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
            transform: translateY(-1px);
        }

        /* Pagination Styles */
        #paginationControls {
            background: linear-gradient(135deg, var(--gray-50) 0%, #ffffff 100%);
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            margin-top: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        #paginationControls button {
            transition: all 0.2s ease;
            font-weight: 500;
        }

        #paginationControls button:not(:disabled):hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #paginationControls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #paginationControls .text-sm {
            font-weight: 600;
            color: var(--gray-700);
        }
        .btn-done:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* Enhanced Filter Section */
        .filter-section {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }
        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600), var(--primary-700));
        }

        /* Form Input Enhancements */
        .form-input {
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
        }
        .form-input:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        /* Loading States */
        .loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid var(--gray-300);
            border-top: 2px solid var(--primary-500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Typography */
        .page-title {
            font-size: 2.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gray-900), var(--gray-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-600);
        }
        .stats-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Responsive Design Improvements */
        @media (max-width: 768px) {
            .filter-section {
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
            .card-header, .card-body {
                padding: 1rem;
            }
            .page-title {
                font-size: 1.875rem;
            }
        }

        /* DARK MODE STYLES - Enhanced */
        .dark .card {
            background: linear-gradient(135deg, var(--gray-800) 0%, var(--gray-900) 100%);
            border-color: var(--gray-700);
            color: var(--gray-100);
        }
        .dark .card::before {
            background: linear-gradient(90deg, var(--primary-400), var(--primary-500));
        }
        .dark .card-header {
            background: linear-gradient(135deg, var(--gray-700) 0%, var(--gray-800) 100%);
            border-bottom: 1px solid var(--gray-600);
            color: var(--gray-100);
        }
        .dark .card-body {
            color: var(--gray-100);
        }
        .dark .filter-section {
            background: linear-gradient(135deg, var(--gray-800) 0%, var(--gray-900) 100%);
            border-color: var(--gray-700);
            color: var(--gray-100);
        }
        .dark .filter-section::before {
            background: linear-gradient(90deg, var(--primary-400), var(--primary-500), var(--primary-600));
        }
        .dark .form-input {
            background: var(--gray-800);
            border-color: var(--gray-600);
            color: var(--gray-100);
        }
        .dark .form-input:focus {
            border-color: var(--primary-400);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }
        .dark .stats-card {
            background: linear-gradient(135deg, var(--gray-800) 0%, var(--gray-900) 100%);
            border-color: var(--gray-700);
        }
        .dark .page-title {
            background: linear-gradient(135deg, var(--gray-100), var(--gray-400));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .page-subtitle {
            color: var(--gray-400);
        }
        .dark body {
            background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-800) 100%);
            color: var(--gray-100);
        }
        .dark .bg-white {
            background: linear-gradient(135deg, var(--gray-800) 0%, var(--gray-900) 100%) !important;
            color: var(--gray-100) !important;
        }
        .dark .bg-gray-100 {
            background: var(--gray-800) !important;
            color: var(--gray-100) !important;
        }
        .dark .bg-gray-50 {
            background: var(--gray-700) !important;
            color: var(--gray-100) !important;
        }
        .dark .text-gray-900 {
            color: var(--gray-100) !important;
        }
        .dark .text-gray-700 {
            color: var(--gray-300) !important;
        }
        .dark .text-gray-600 {
            color: var(--gray-400) !important;
        }
        .dark .text-blue-600 {
            color: var(--primary-400) !important;
        }
        .dark .text-blue-800 {
            color: var(--primary-300) !important;
        }
        .dark .bg-blue-600 {
            background-color: var(--primary-600) !important;
            color: white !important;
        }
        .dark .bg-blue-700 {
            background-color: var(--primary-700) !important;
            color: white !important;
        }
        .dark .bg-green-50 {
            background-color: var(--success-900) !important;
            color: var(--success-100) !important;
        }
        .dark .bg-orange-50 {
            background-color: var(--warning-900) !important;
            color: var(--warning-100) !important;
        }
        .dark .border-green-200 {
            border-color: var(--success-700) !important;
        }
        .dark .border-orange-200 {
            border-color: var(--warning-700) !important;
        }
        .dark .border-l-orange-400 {
            border-left-color: var(--warning-500) !important;
        }
        .dark .border-l-orange-600 {
            border-left-color: var(--warning-700) !important;
        }
        .dark .border-blue-200 {
            border-color: var(--primary-700) !important;
        }
        .dark .modalContent {
            background-color: var(--gray-800) !important;
            color: var(--gray-100) !important;
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1.5em;
            padding-right: 2.5rem;
        }

        /* Dark mode adjustments */
        .dark select {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        }

        /* Tom Select Dark Mode Overrides */
        .dark .ts-dropdown {
            background-color: var(--gray-800);
            border-color: var(--gray-700);
            color: var(--gray-100);
        }
        .dark .ts-dropdown .active {
            background-color: var(--gray-700);
        }
        .dark .ts-control {
            background-color: var(--gray-800);
            border-color: var(--gray-700);
        }
        .dark .ts-control input {
            color: var(--gray-100) !important;
        }
        .dark .ts-dropdown .selected {
            background-color: var(--primary-700);
        }
    </style>
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="page-title">
                📋 Inventory Administration
            </h1>
            <p class="page-subtitle">Manage and track all inventory records</p>
        </div>

        <!-- Filters Section -->
        <div class="filter-section">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters & Search</h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-input form-select w-full">
                        <option value="all" <?= $status_filter === 'all' || $status_filter === '' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="canceled" <?= $status_filter === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                        <option value="done" <?= $status_filter === 'done' ? 'selected' : '' ?>>Done</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" 
                           class="form-input w-full">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" 
                           class="form-input w-full">
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Title, notes, or user..."
                           class="form-input w-full">
                </div>

                <!-- Product Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Search</label>
                    <input type="text" name="product_search" value="<?= htmlspecialchars(isset($_GET['product_search']) ? $_GET['product_search'] : '') ?>" 
                           placeholder="Search by product name..."
                           class="form-input w-full">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-confirm">
                        🔍 Filter
                    </button>
                </div>
            </form>
            
            <!-- Clear Filters -->
            <div class="mt-4">
                <a href="inv_admin" class="text-blue-600 hover:text-blue-800 text-sm">
                    🔄 Clear all filters
                </a>
                <span class="text-gray-500 text-sm ml-4">
                    Loading inventories...
                </span>
            </div>
        </div>

        <!-- Inventories List -->
        <div id="inventoriesContainer" class="space-y-4">
            <!-- Loading state -->
            <div id="loadingState" class="text-center py-12">
                <div class="text-6xl mb-4">⏳</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Loading inventories...</h3>
                <p class="text-gray-600">Please wait while we fetch the data from the API.</p>
            </div>
            
            <!-- Error state (hidden by default) -->
            <div id="errorState" class="text-center py-12 hidden">
                <div class="text-6xl mb-4">❌</div>
                <h3 class="text-xl font-semibold text-red-900 mb-2">Error loading inventories</h3>
                <p class="text-red-600" id="errorMessage">Unable to connect to the API.</p>
                <button onclick="loadInventories()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    🔄 Retry
                </button>
            </div>
            
            <!-- Empty state (hidden by default) -->
            <div id="emptyState" class="text-center py-12 hidden">
                <div class="text-6xl mb-4">📝</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No inventories found</h3>
                <p class="text-gray-600">Try adjusting your filters or create a new inventory.</p>
                <a href="inv" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    Create New Inventory
                </a>
            </div>
            
            <!-- Inventories will be loaded here via JavaScript -->
        </div>
        
        <!-- Quick Actions -->
        <!-- <div class="fixed bottom-6 right-6">
            <a href="inv.php" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full shadow-lg font-medium">
                ➕ New Inventory
            </a>
        </div> -->
    </div>

    <!-- Modal for Inventory Details -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[80vh] overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-semibold">Inventory Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent" class="p-6 overflow-y-auto max-h-[60vh]">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Popu Button -->
    <!-- <div class="fixed bottom-6 left-6 z-50">
        <button id="popuBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full shadow-lg font-medium">
            ➕ Popup
        </button>
    </div> -->

    <!-- Popu Modal -->
    <div id="popuModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg w-full max-w-lg p-6 relative border-2 border-green-200">
            <button id="closePopuModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-600 text-2xl font-bold">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-green-700">Lot</h2>
            <form id="popuForm" class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-2 text-sm text-gray-700 font-semibold">TVA : MARCH EXO 0%</div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" id="editEnreg" class="mr-2">
                        <label for="editEnreg" class="text-sm">Editer enregistrement</label>
                        <button type="button" class="ml-2 px-2 py-1 bg-gray-200 rounded text-xs">Sélectionner un enregistrement existant</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <label class="text-sm">Prix Achat</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="prix_achat">
                    <label class="text-sm">Remise Supp</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="remise_supp">
                    <label class="text-sm">Prix Revient</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="prix_revient">
                    <label class="text-sm">Prix Vente</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="prix_vente">
                    <label class="text-sm">PPA</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="ppa">
                    <label class="text-sm">Bonus Vente</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="bonus_vente">
                    <label class="text-sm">Remise Vente</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="remise_vente">
                    <label class="text-sm">Colisage</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="colisage">
                    <label class="text-sm">Fournisseur</label>
                    <input type="text" class="border rounded px-2 py-1" name="fournisseur">
                    <label class="text-sm">Lot</label>
                    <input type="text" class="border rounded px-2 py-1" name="lot">
                    <label class="text-sm">Date d'expiration</label>
                    <input type="date" class="border rounded px-2 py-1" name="date_expiration">
                    <label class="text-sm">Bonus</label>
                    <input type="number" step="0.01" class="border rounded px-2 py-1" name="bonus">
                </div>
                <div class="mb-4">
                    <label for="attributeType" class="block text-sm font-medium text-gray-700 mb-2">Type d'attribut</label>
                    <select id="attributeType" name="attributeType" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">-- Sélectionner --</option>
                        <option value="Prix Achat">Prix Achat</option>
                        <option value="Colisage">Colisage</option>
                        <option value="PPA">PPA</option>
                        <option value="Prix Vente">Prix Vente</option>
                        <option value="Prix Revient">Prix Revient</option>
                        <option value="Fournisseur">Fournisseur</option>
                        <option value="Bonus">Bonus</option>
                        <option value="Bonus Vente">Bonus Vente</option>
                        <option value="Remise Supp">Remise Supp</option>
                        <option value="Remise Vente">Remise Vente</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="attributeValue" class="block text-sm font-medium text-gray-700 mb-2">Valeur</label>
                    <input type="number" step="any" id="attributeValue" name="attributeValue" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Entrer la valeur">
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">Valider</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Get user info from PHP session (username is always from session)
        const currentUser = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : '' ?>';
        

        let fournisseurTomSelect = null; // Global reference

        // Get filter parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {
            status: urlParams.get('status') || '',
            date_from: urlParams.get('date_from') || '',
            date_to: urlParams.get('date_to') || '',
            search: urlParams.get('search') || '',
            product_search: urlParams.get('product_search') || ''
        };

        // Pagination state
        let currentPage = 1;
        const pageSize = 20; // Show 20 records per page
        let allInventories = []; // Store all records for pagination

        // Sudo mode flag
        window.sudoMode = false;
        // Intercept search form submit for sudo911
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('form[method="GET"]');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const searchInput = searchForm.querySelector('input[name="search"]');
                    if (searchInput && searchInput.value.trim() === 'sudo911' && currentUser === 'admin') {
                        e.preventDefault();
                        window.sudoMode = true;
                        filters.search = '';
                        // Remove search param from URL and reload inventories
                        urlParams.delete('search');
                        history.replaceState(null, '', window.location.pathname + '?' + urlParams.toString());
                        loadInventories();
                    } else {
                        window.sudoMode = false;
                    }
                });
            }
        });
        
        // Add console debugging
        console.log('🔍 Debug: Page loaded successfully');
        console.log('🔍 Debug: Current user:', currentUser);
        console.log('🔍 Debug: Filters:', filters);
        console.log('🔍 Debug: Page info:', {
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        });
        
        // Load inventories from API
        async function loadInventories() {
            console.log('🔍 Debug: loadInventories called');
            
            const loadingState = document.getElementById('loadingState');
            const errorState = document.getElementById('errorState');
            const emptyState = document.getElementById('emptyState');
            const container = document.getElementById('inventoriesContainer');
            
            // Show loading state
            loadingState.classList.remove('hidden');
            errorState.classList.add('hidden');
            emptyState.classList.add('hidden');
            
            // Remove existing inventory cards
            const existingCards = container.querySelectorAll('.inventory-card');
            existingCards.forEach(card => card.remove());
            
            try {
                // Build query parameters for Python API
                const queryParams = [];
                if (filters.status && filters.status !== 'all') {
                    queryParams.push('status=' + encodeURIComponent(filters.status));
                }
                // Set a very high limit to get ALL records for pagination
                queryParams.push('limit=10000'); // Get up to 10,000 records for admin view
                queryParams.push('offset=0');
                
                const queryString = queryParams.length > 0 ? '?' + queryParams.join('&') : '';
                
                // Call Python API to get inventory list
                const response = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/list${queryString}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const apiResult = await response.json();
                
                if (!apiResult.success) {
                    throw new Error(apiResult.error || 'API call failed');
                }
                
                let inventories = apiResult.inventories || [];
                
                // Store all inventories for pagination
                allInventories = inventories;
                
                // Apply product search filter first if specified
                if (filters.product_search) {
                    try {
                        const productResponse = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory_product_search?product_name=${encodeURIComponent(filters.product_search)}`);
                        if (productResponse.ok) {
                            const productResult = await productResponse.json();
                            if (productResult.success && productResult.inventories.length > 0) {
                                // Get inventory IDs that contain the product
                                const matchingInventoryIds = productResult.inventories.map(inv => inv.id);
                                // Filter inventories to only include those with matching products
                                inventories = inventories.filter(inv => matchingInventoryIds.includes(inv.id));
                                allInventories = inventories; // Update allInventories with filtered results
                            } else {
                                // No products found, show empty results
                                inventories = [];
                                allInventories = [];
                            }
                        }
                    } catch (error) {
                        console.error('❌ Error searching products:', error);
                        // Continue with normal filtering if product search fails
                    }
                }
                
                // Apply additional filters (date and search) since Python API doesn't support them yet
                if (filters.date_from || filters.date_to || filters.search) {
                    inventories = allInventories.filter(inventory => {
                        // Date filter
                        if (filters.date_from || filters.date_to) {
                            const createdDate = new Date(inventory.created_at).toISOString().split('T')[0];
                            if (filters.date_from && createdDate < filters.date_from) return false;
                            if (filters.date_to && createdDate > filters.date_to) return false;
                        }
                        
                        // Search filter
                        if (filters.search) {
                            const searchLower = filters.search.toLowerCase();
                            const titleMatch = inventory.title.toLowerCase().includes(searchLower);
                            const notesMatch = (inventory.notes || '').toLowerCase().includes(searchLower);
                            const userMatch = inventory.created_by.toLowerCase().includes(searchLower);
                            
                            if (!titleMatch && !notesMatch && !userMatch) return false;
                        }
                        
                        return true;
                    });
                    allInventories = inventories; // Update allInventories with filtered results
                }
                
                // Reset to first page when filters change
                currentPage = 1;
                
                // Display current page
                displayCurrentPage();
                
                console.log('✅ Inventories loaded successfully:', inventories.length);
                
            } catch (error) {
                console.error('❌ Error loading inventories:', error);
                
                // Hide loading state and show error
                loadingState.classList.add('hidden');
                errorState.classList.remove('hidden');
                document.getElementById('errorMessage').textContent = error.message;
            }
        }
        
        // Display current page of inventories
        function displayCurrentPage() {
            const loadingState = document.getElementById('loadingState');
            const errorState = document.getElementById('errorState');
            const emptyState = document.getElementById('emptyState');
            const container = document.getElementById('inventoriesContainer');
            
            // Hide loading state
            loadingState.classList.add('hidden');
            errorState.classList.add('hidden');
            emptyState.classList.add('hidden');
            
            // Remove existing inventory cards
            const existingCards = container.querySelectorAll('.inventory-card');
            existingCards.forEach(card => card.remove());
            
            // Calculate pagination
            const totalRecords = allInventories.length;
            const totalPages = Math.ceil(totalRecords / pageSize);
            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = Math.min(startIndex + pageSize, totalRecords);
            const currentPageInventories = allInventories.slice(startIndex, endIndex);
            
            // Update results count with pagination info
            const resultsCount = document.querySelector('.text-gray-500.text-sm.ml-4');
            if (resultsCount) {
                if (totalRecords === 0) {
                    resultsCount.textContent = 'No records found';
                } else {
                    resultsCount.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalRecords} record(s) (Page ${currentPage} of ${totalPages})`;
                }
            }
            
            if (currentPageInventories.length === 0) {
                // Show empty state
                emptyState.classList.remove('hidden');
            } else {
                // Render current page inventories
                currentPageInventories.forEach(inventory => {
                    renderInventoryCard(inventory, container);
                });
                
                // Add pagination controls
                addPaginationControls(totalPages);
            }
        }
        
        // Add pagination controls
        function addPaginationControls(totalPages) {
            // Remove existing pagination
            const existingPagination = document.getElementById('paginationControls');
            if (existingPagination) {
                existingPagination.remove();
            }
            
            if (totalPages <= 1) return; // No need for pagination if only one page
            
            const container = document.getElementById('inventoriesContainer');
            const paginationDiv = document.createElement('div');
            paginationDiv.id = 'paginationControls';
            paginationDiv.className = 'flex items-center justify-between bg-white px-4 py-3 sm:px-6 mt-6 border-t border-gray-200';
            
            // Previous button
            const prevButton = document.createElement('button');
            prevButton.className = `relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md ${
                currentPage === 1 
                    ? 'text-gray-300 cursor-not-allowed' 
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
            }`;
            prevButton.innerHTML = '← Previous';
            prevButton.disabled = currentPage === 1;
            prevButton.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayCurrentPage();
                }
            };
            
            // Page info
            const pageInfo = document.createElement('div');
            pageInfo.className = 'text-sm text-gray-700';
            pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            
            // Next button
            const nextButton = document.createElement('button');
            nextButton.className = `relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md ${
                currentPage === totalPages 
                    ? 'text-gray-300 cursor-not-allowed' 
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
            }`;
            nextButton.innerHTML = 'Next →';
            nextButton.disabled = currentPage === totalPages;
            nextButton.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayCurrentPage();
                }
            };
            
            // Page number buttons
            const pageNumbers = document.createElement('div');
            pageNumbers.className = 'flex items-center space-x-1';
            
            // Calculate which page numbers to show
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);
            
            // Adjust if we're near the beginning or end
            if (endPage - startPage < 4) {
                if (startPage === 1) {
                    endPage = Math.min(totalPages, startPage + 4);
                } else if (endPage === totalPages) {
                    startPage = Math.max(1, endPage - 4);
                }
            }
            
            // Add page number buttons
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `relative inline-flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                    i === currentPage 
                        ? 'text-blue-600 bg-blue-50 border border-blue-300' 
                        : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                }`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => {
                    currentPage = i;
                    displayCurrentPage();
                };
                pageNumbers.appendChild(pageBtn);
            }
            
            paginationDiv.appendChild(prevButton);
            paginationDiv.appendChild(pageNumbers);
            paginationDiv.appendChild(pageInfo);
            paginationDiv.appendChild(nextButton);
            
            container.appendChild(paginationDiv);
        }
        
        // Render a single inventory card
        function renderInventoryCard(inventory, container) {
            const card = document.createElement('div');
            card.className = 'card inventory-card';
            card.setAttribute('data-id', inventory.id);
            const createdDate = new Date(inventory.created_at);
            const updatedDate = new Date(inventory.updated_at);

            // Vérifier s'il y a une ligne manuelle dans l'inventaire
            const hasManualItem = (inventory.manual_entries_count && inventory.manual_entries_count > 0) || (inventory.items && inventory.items.some && inventory.items.some(item => item.is_manual_entry == 1));

            card.innerHTML = `
                <div class="card-header">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    inv${inventory.id}/${new Date().getFullYear()} - ${escapeHtml(inventory.title)}
                                    ${inventory.casse === 'yes' ? '<span class="ml-2 px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">🏪 from casse</span>' : ''}
                                </h3>
                                <span class="badge status-${inventory.status}">
                                    ${inventory.status.charAt(0).toUpperCase() + inventory.status.slice(1)}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="mr-4">📦 ${inventory.total_items || 0} items</span>
                                <span class="mr-4">👤 Created by: ${escapeHtml(inventory.created_by)}</span>
                                <span>📅 ${createdDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            ${generateActionButtons(inventory)}
                            ${(currentUser === 'admin' && window.sudoMode) ? `
                            <button onclick="handleInsert(${inventory.id})" 
                                class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                Insert
                            </button>` : ''}
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    ${inventory.notes ? `
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Notes:</h4>
                            <p class="text-gray-600 text-sm bg-gray-50 p-2 rounded">
                                ${escapeHtml(inventory.notes).replace(/\\n/g, '<br>')}
                            </p>
                        </div>
                    ` : ''}
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Created:</span>
                            <span class="text-gray-600">${createdDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                        
                        <div>
                            <span class="font-medium text-gray-700">Last Updated:</span>
                            <span class="text-gray-600">${updatedDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                    </div>
                    
                    <!-- View Details Button -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <button onclick="viewInventoryDetails(${inventory.id})" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            📋 View Inventory Items
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(card);
        }

function formatDateForInput(dateString) {
    if (!dateString) return '';
    console.log('🔍 Debug: formatDateForInput called with:', dateString);
    // Handle both formats: "dd/mm/yy" and "yyyy-mm-dd"
    let date;
    if (dateString.includes('/')) {
        const [day, month, year] = dateString.split('/');
        date = new Date(`20${year}`,month - 1,day);
        console.log('🔍 Debug: Parsed date from dd/mm/yy:', date);
    } else {
        date = new Date(dateString);
    }
    
    // Format as YYYY-MM-DD (HTML date input format)
    const isoDate = date.toISOString().split('T')[0];
    console.log('🔍 Debug: Formatted date for input:', isoDate);
    return isoDate;
}
    // Modify the handleInsert function
async function handleInsert(inventoryId) {
    try {
        // 1. Get inventory details
        const response = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/details/${inventoryId}`);
        const data = await response.json();
        console.log('🔍 Debug: Inventory details fetched:', data);
        if (!data.success || !data.items || data.items.length === 0) {
            throw new Error('No items found in inventory');
        }

        // 2. Collect all items for batch processing
        const itemsToInsert = [];

        for (const item of data.items) {
            let itemData = {
                inventory_id: inventoryId,
                product_name: item.product_name,
                quantity: item.quantity,
                qty_dispo: item.qty_dispo || 0,
                m_attributesetinstance_id: item.m_attributesetinstance_id || null,
                attributes: {}
            };
            if (!item.m_attributesetinstance_id) {
                // Show modal to collect attribute information
                const attributeData = await showAttributeModal(item);
                
                // Merge the collected data with our item data
                Object.assign(itemData, {
                    ...itemData,
                    lot: attributeData.lot || '-',
                    date_expiration: attributeData.date_expiration || null,
                    attributes: attributeData.attributes || {}  // This is the crucial change
                });
            }

            itemsToInsert.push(itemData);
            console.log('Collected attribute data:', itemData);
        }

            // 3. Send ALL items in a single API call
            const insertionResponse = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/insert_inventory`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    items: itemsToInsert,
                    title: data.inventory.title,
                    notes: data.inventory.notes,
                    casse: data.inventory.casse
                })
            });

            const result = await insertionResponse.json().catch(e => {
                console.error("Failed to parse response:", e);
                return { success: false, error: "Invalid server response" };
            });

            if (!insertionResponse.ok || !result.success) {
                throw new Error(result.error || 'Insertion failed');
            }
      
        
        alert('All items inserted successfully!');
        
    } catch (error) {
        console.error('Error during insertion:', error);
        alert(`Insertion failed: ${error.message}`);
    }
        // Helper function to format date for HTML input (YYYY-MM-DD)
}
// New function to show attribute collection modal
// Update the showAttributeModal function
async function showAttributeModal(item) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl dark:bg-gray-800">
                <h3 class="text-xl font-bold mb-4 dark:text-white">Attribute Information for ${item.product_name}</h3>
                <form id="attributeForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Information -->
                    <div class="md:col-span-2">
                        <h4 class="font-medium text-lg mb-2 dark:text-gray-200">Basic Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lot Number</label>
                                <input type="text" name="lot" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900 shadow-sm" required value="${item.lot}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiration Date</label>
                                <input type="date" name="date_expiration" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900 shadow-sm" required value="${item.date ? formatDateForInput(item.date) : ''}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attribute Values -->
                    <div class="md:col-span-2">
                        <h4 class="font-medium text-lg mb-2 dark:text-gray-200">Attribute Values</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Achat</label>
                                <input type="number" step="0.01" name="Prix Achat" id="prix-achat" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Colisage</label>
                                <input type="number" step="0.01" name="Colisage" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PPA</label>
                                <input type="number" step="0.01" name="PPA" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="${item.ppa}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Vente</label>
                                <input type="number" step="0.01" name="Prix Vente" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Revient</label>
                                <input type="number" step="0.01" name="Prix Revient" id="prix-revient" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                                <select id="fournisseur-select" name="Fournisseur" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" required >
                                    <option value="">-- Sélectionner un fournisseur --</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bonus</label>
                                <input type="number" step="0.01" name="Bonus" id="bonus" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bonus Vente</label>
                                <input type="number" step="0.01" name="Bonus Vente" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remise Supp</label>
                                <input type="number" step="0.01" name="Remise Supp" id="remise-supp" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remise Vente</label>
                                <input type="number" step="0.01" name="Remise Vente" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-white text-gray-900" value="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="md:col-span-2 flex justify-end space-x-3 mt-4">
                        <button type="button" onclick="this.closest('div[class*=\\'fixed\\']').remove();" class="px-4 py-2 bg-gray-300 rounded-md dark:bg-gray-600 dark:text-white">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Submit</button>
                    </div>
                </form>
            </div>
        `;

        // Ajout du calcul automatique du prix de revient
        setTimeout(() => {
            const prixAchatInput = modal.querySelector('#prix-achat');
            const remiseSuppInput = modal.querySelector('#remise-supp');
            const bonusInput = modal.querySelector('#bonus');
            const prixRevientInput = modal.querySelector('#prix-revient');

            function calculePrixRevient() {
                let prixAchat = parseFloat(prixAchatInput.value) || 0;
                let remiseSupp = parseFloat(remiseSuppInput.value) || 0;
                let bonus = parseFloat(bonusInput.value) || 0;
                // Remise Supp est un pourcentage du prix achat
                let prixRevient = 0;
                if (prixAchat > 0) {
                    prixRevient = (prixAchat - (prixAchat * (remiseSupp / 100))) / (1 + (bonus / 100));
                }
                prixRevientInput.value = prixRevient.toFixed(2);
            }
            prixAchatInput.addEventListener('input', calculePrixRevient);
            remiseSuppInput.addEventListener('input', calculePrixRevient);
            bonusInput.addEventListener('input', calculePrixRevient);
            // Initialiser au chargement
            calculePrixRevient();
        }, 100);

        modal.querySelector('#attributeForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
    
            // Filter out empty values and prepare for backend
            const attributes = {};
            for (const [key, value] of Object.entries(data)) {
                if (value && !['lot', 'date_expiration'].includes(key)) {
                    attributes[key] = value;
                }
            }
            console.log('🔍 Debug: Collected attributes:', attributes);

            let date = data.date_expiration;
            let formattedDate = null;
            console.log('🔍 Debug: Date before formatting:', data.date_expiration);
            if (date) {
                const [year, month, day] = date.split("-");
                formattedDate = `${day}/${month}/${year.slice(-2)}`; // dd/mm/yy
                console.log('🔍 Debug: Formatted date:', formattedDate);
            }
           

            resolve({
                lot: data.lot,
                date_expiration: formattedDate,
                attributes: attributes
            });

            modal.remove();
        });

   
        document.body.appendChild(modal);
        // After adding modal to DOM:
         loadFournisseurs(item);

        // Cleanup when modal closes
        modal.addEventListener('close', () => {
            if (fournisseurTomSelect) {
                fournisseurTomSelect.destroy();
                fournisseurTomSelect = null;
            }
        });
    });
}
        // Fetch fournisseurs when modal opens
function showConfirmation(message) {
    return new Promise((resolve) => {
        const dialog = document.createElement('div');
        dialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        dialog.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full dark:bg-gray-800">
                <h3 class="text-lg font-medium mb-4 dark:text-white">Confirmation</h3>
                <p class="mb-6 dark:text-gray-300">${message}</p>
                <div class="flex justify-end space-x-3">
                    <button id="confirmCancel" class="px-4 py-2 bg-gray-300 rounded-md dark:bg-gray-600 dark:text-white">
                        Cancel
                    </button>
                    <button id="confirmOK" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Confirm
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(dialog);
        
        dialog.querySelector('#confirmOK').addEventListener('click', () => {
            document.body.removeChild(dialog);
            resolve(true);
        });
        
        dialog.querySelector('#confirmCancel').addEventListener('click', () => {
            document.body.removeChild(dialog);
            resolve(false);
        });
    });
}

async function loadFournisseurs(item) {
    try {
        const response = await fetch(`${API_CONFIGinv.getApiUrl()}/api/fournisseurs`);
        const data = await response.json();
        
        if (data.success) {
            // Initialize Tom Select if not already done
            if (!fournisseurTomSelect) {
                fournisseurTomSelect = new TomSelect('#fournisseur-select', {
                    valueField: 'value',
                    labelField: 'text',
                    searchField: 'text',
                    options: data.fournisseurs.map(f => ({ value: f, text: f })),
                    create: false,
                    placeholder: 'Rechercher un fournisseur...',
                    render: {
                        option: function(item, escape) {
                            return `<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600">${escape(item.text)}</div>`;
                        }
                    }
                });
            } else {
                // Just update options if already initialized
                fournisseurTomSelect.clearOptions();
                fournisseurTomSelect.addOptions(data.fournisseurs.map(f => ({ value: f, text: f })));
            }
            
            // Set default value if item has a fournisseur
            if (item.fournisseur) {
                fournisseurTomSelect.setValue(item.fournisseur);
            }
        }
    } catch (error) {
        console.error('Error loading fournisseurs:', error);
    }
}


        // Generate action buttons based on inventory status and workflow
        function generateActionButtons(inventory) {
            // Confirm/Cancel/Done/Reopen buttons visible for 'hichem', 'admin', or 'mohamed', others see nothing
            switch (inventory.status) {
                case 'pending':
                    if (currentUser === 'hichem' || currentUser === 'admin' || currentUser === 'mohamed') {
                        return `
                            <button onclick="updateStatus(${inventory.id}, 'confirmed')" class="btn btn-confirm" title="Confirm this inventory">✅ Confirm</button>
                            <button onclick="updateStatus(${inventory.id}, 'canceled')" class="btn btn-cancel" title="Cancel this inventory">❌ Cancel</button>
                        `;
                    } else {
                        return '';
                    }
                case 'confirmed':
                    if (currentUser === 'hichem' || currentUser === 'admin' || currentUser === 'mohamed') {
                        return `
                            <button onclick="updateStatus(${inventory.id}, 'done')" class="btn btn-done" title="Mark as done - final state">✅ Mark as Done</button>
                            <button onclick="updateStatus(${inventory.id}, 'canceled')" class="btn btn-cancel" title="Cancel this inventory">❌ Cancel</button>
                        `;
                    } else {
                        return '';
                    }
                case 'canceled':
                    if (currentUser === 'hichem' || currentUser === 'admin' || currentUser === 'mohamed') {
                        return `
                            <button onclick="updateStatus(${inventory.id}, 'pending')" class="btn btn-confirm" title="Reopen this inventory">🔄 Reopen</button>
                            <span class="text-red-600 font-medium">❌ Canceled</span>
                        `;
                    } else {
                        return `<span class="text-red-600 font-medium">❌ Canceled</span>`;
                    }
                case 'done':
                    return `<span class="text-green-600 font-medium">✅ Completed</span> <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Final State</span>`;
                default:
                    return `<span class="text-gray-500">Unknown Status</span>`;
            }
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // Update inventory status with proper workflow validation
        async function updateStatus(inventoryId, newStatus) {
            console.log('🔍 Debug: updateStatus called', {inventoryId, newStatus});
            
            const card = document.querySelector(`[data-id="${inventoryId}"]`);
            if (!card) return;
            
            card.classList.add('loading');
            
            try {
                // Call Python API to update status - all statuses are now supported
                const response = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/update_status/${inventoryId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        updated_by: currentUser
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    try {
                        const errorData = JSON.parse(errorText);
                        throw new Error(errorData.error || `HTTP ${response.status}`);
                    } catch {
                        throw new Error(`HTTP ${response.status}: ${errorText}`);
                    }
                }

                const result = await response.json();

                // If inventory is just confirmed, send appropriate mail
                if (result.success && newStatus === 'confirmed') {
                    const card = document.querySelector(`[data-id="${inventoryId}"]`);
                    if (card) {
                        if (card.innerHTML.includes('🏪 from casse')) {
                            // Call the Flask endpoint to send saisie mail
                            fetch(`${API_CONFIGinv.getApiUrl()}/send_saisie_mail`, { method: 'GET' })
                                .then(r => r.json())
                                .then(data => {
                                    if (data && data.results) {
                                        console.log('Saisie mail sent:', data);
                                    }
                                })
                                .catch(err => {
                                    console.error('Error sending saisie mail:', err);
                                });
                        } else {
                            // Not from casse: Call the Flask endpoint to send info mail
                            fetch(`${API_CONFIGinv.getApiUrl()}/send_info_mail`, { method: 'GET' })
                                .then (r => r.json())
                                .then(data => {
                                    if (data && data.results) {
                                        console.log('Info mail sent:', data);
                                    }
                                })
                                .catch(err => {
                                    console.error('Error sending info mail:', err);
                                });
                        }
                    }
                }

                if (result.success) {
                    console.log('✅ Status update successful:', result);
                    // Show success message
                    let message = `✅ Status updated to "${newStatus}"`;
                    if (result.previous_status) {
                        message = `✅ Status changed from "${result.previous_status}" to "${newStatus}"`;
                    }
                    // Special messages for specific transitions
                    if (newStatus === 'done') {
                        message += '\n🎉 This inventory is now complete and cannot be modified.';
                    } else if (newStatus === 'pending' && result.previous_status === 'canceled') {
                        message += '\n🔄 Inventory has been reopened for editing.';
                    } else if (newStatus === 'canceled') {
                        message += '\n❌ Inventory has been canceled.';
                    } else if (newStatus === 'confirmed') {
                        message += '\n✅ Inventory is now confirmed and ready for completion.';
                    }
                    
                    alert(message);
                    
                    // Reload the inventories to show updated status
                    loadInventories();
                } else {
                    console.error('❌ Status update failed:', result.error);
                    alert('❌ Error: ' + result.error);
                }
                
            } catch (error) {
                console.error('❌ Status update error:', error);
                alert('❌ Error updating status: ' + error.message);
            } finally {
                card.classList.remove('loading');
            }
        }
        
        // View inventory details
        async function viewInventoryDetails(inventoryId) {
            console.log('🔍 Debug: viewInventoryDetails called', {inventoryId});
            
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('modalContent');
            
            content.innerHTML = '<div class="text-center">Loading...</div>';
            modal.classList.remove('hidden');
            
            try {
                const response = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/details/${inventoryId}`);
                const data = await response.json();
                
                if (data.success) {
                    content.innerHTML = generateDetailsHTML(data.inventory, data.items);
                } else {
                    content.innerHTML = '<div class="text-red-600">Error loading details: ' + data.error + '</div>';
                }
            } catch (error) {
                content.innerHTML = '<div class="text-red-600">Error: ' + error.message + '</div>';
            }
        }
        
        // Generate HTML for inventory details
        function generateDetailsHTML(inventory, items) {
            let html = `
                <div class="mb-6">
                    <h4 class="text-lg font-semibold mb-2">${inventory.title}</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Status:</strong> <span class="badge status-${inventory.status}">${inventory.status}</span></div>
                        <div><strong>Created by:</strong> ${inventory.created_by}</div>
                        <div><strong>Created:</strong> ${new Date(inventory.created_at).toLocaleString()}</div>
                        <div><strong>Items:</strong> ${items.length}</div>
                    </div>
                    ${inventory.notes ? `<div class="mt-3"><strong>Notes:</strong><br><div class="bg-gray-50 p-2 rounded text-sm">${inventory.notes}</div></div>` : ''}
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            `;
            
            // Group items by type
            const entryItems = items.filter(item => item.type === 'entry');
            const sortieItems = items.filter(item => item.type === 'sortie');
            
            // Entry items
            html += `
                <div>
                    <h5 class="font-semibold text-green-700 mb-3">📦 ENTRY (${entryItems.length} items)</h5>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
            `;
            
            entryItems.forEach(item => {
                const isManual = item.is_manual_entry == 1;
                html += `
                    <div class="bg-green-50 p-3 rounded border border-green-200${isManual ? ' border-l-4 border-l-orange-400 bg-orange-50 border-orange-300' : ''}">
                        <div class="font-medium">${item.product_name}</div>
                        ${isManual ? '<div class="mt-1"><span class="bg-orange-200 text-orange-800 px-2 py-1 rounded-full text-xs font-bold">⚠️ 📝 MANUAL ENTRY</span></div>' : ''}
                        ${item.description ? `<div class="text-sm text-gray-700 italic mb-1">${item.description}</div>` : ''}
                        <div class="text-sm text-gray-600">
                            Qty: <strong>${item.quantity}</strong> | 
                            Lot: ${item.lot || 'N/A'} | 
                            PPA: ${parseFloat(item.ppa).toFixed(2)} |
                            Date: ${item.date ? new Date(item.date).toLocaleDateString() : 'N/A'}
                        </div>
                    </div>
                `;
            });
            
            html += `</div></div>`;
            
            // Sortie items
            html += `
                <div>
                    <h5 class="font-semibold text-orange-700 mb-3">📤 SORTIE (${sortieItems.length} items)</h5>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
            `;
            
            sortieItems.forEach(item => {
                const isManual = item.is_manual_entry == 1;

                html += `
                    <div class="bg-orange-50 p-3 rounded border border-orange-200${isManual ? ' border-l-4 border-l-red-500 bg-red-50 border-red-300' : ''}">
                        <div class="font-medium">${item.product_name}</div>
                        ${isManual ? '<div class="mt-1"><span class="bg-red-200 text-red-800 px-2 py-1 rounded-full text-xs font-bold">⚠️ 📝 MANUAL ENTRY</span></div>' : ''}
                        ${item.description ? `<div class="text-sm text-gray-700 italic mb-1">${item.description}</div>` : ''}
                        <div class="text-sm text-gray-600">
                            Qty: <strong>${item.quantity}</strong> | 
                            Lot: ${item.lot || 'N/A'} | 
                            PPA: ${parseFloat(item.ppa).toFixed(2)} |
                            Date: ${item.date ? new Date(item.date).toLocaleDateString() : 'N/A'}
                        </div>
                    </div>
                `;
            });
            
            html += `</div></div></div>`;
            
            return html;
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // Load inventories when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔍 Debug: DOM loaded, loading inventories...');
            loadInventories();
        });
        
        // Close modal
        function closeModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Popu modal logic (fix: ensure DOM is loaded before attaching event listeners)
        document.addEventListener('DOMContentLoaded', function() {
            var popuBtn = document.getElementById('popuBtn');
            var popuModal = document.getElementById('popuModal');
            var closePopuModal = document.getElementById('closePopuModal');
            var closePopuModal2 = document.getElementById('closePopuModal2');
            var popuForm = document.getElementById('popuForm');
            if (popuBtn && popuModal && closePopuModal && closePopuModal2 && popuForm) {
                popuBtn.addEventListener('click', function() {
                    popuModal.classList.remove('hidden');
                });
                closePopuModal.addEventListener('click', function() {
                    popuModal.classList.add('hidden');
                });
                closePopuModal2.addEventListener('click', function() {
                    popuModal.classList.add('hidden');
                });
                popuForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const attributeType = document.getElementById('attributeType').value;
                    const attributeValue = document.getElementById('attributeValue').value;
                    const inventoryId = 123; // À remplacer dynamiquement si besoin
                    const payload = {
                        inventory_id: inventoryId,
                        attribute_type: attributeType,
                        attribute_value: attributeValue
                    };
                    fetch(`${API_CONFIGinv.getApiUrl()}/api/inventory/insert`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('Insertion réussie !');
                            popuModal.classList.add('hidden');
                        } else {
                            alert('Erreur : ' + (result.error || 'Insertion échouée'));
                        }
                    })
                    .catch(error => {
                        alert('Erreur réseau : ' + error);
                    });
                });
            }
        });
    </script>
</body>
</html>
