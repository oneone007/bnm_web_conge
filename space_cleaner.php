<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

$page_identifier = 'space_cleaner';

require_once 'check_permission.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Space Cleaner - Admin</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="theme.js"></script>
    <script src="api_config.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .table-container {
            height: auto;
            max-height: none;
            overflow-y: visible;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        body.dark-mode .modal-content {
            background-color: #1f2937;
            color: white;
            border-color: #374151;
        }

        body.dark-mode .modal-content button {
            transition: all 0.2s ease;
        }

        body.dark-mode .modal-content #cancelBtn {
            background-color: #4b5563;
            color: #e5e7eb;
        }

        body.dark-mode .modal-content #cancelBtn:hover {
            background-color: #6b7280;
        }

        body.dark-mode .modal-content #confirmBtn {
            background-color: #dc2626;
        }

        body.dark-mode .modal-content #confirmBtn:hover {
            background-color: #b91c1c;
        }

        /* Dark mode styles for body.dark-mode */
        body.dark-mode {
            background-color: #111827;
        }

        body.dark-mode h1,
        body.dark-mode h2 {
            color: #ffffff;
        }

        body.dark-mode p {
            color: #9ca3af;
        }

        body.dark-mode .bg-white {
            background-color: #1f2937;
        }

        body.dark-mode .text-red-600 {
            color: #f87171;
        }

        body.dark-mode .text-blue-600 {
            color: #60a5fa;
        }

        body.dark-mode .text-green-600 {
            color: #34d399;
        }

        body.dark-mode .text-purple-600 {
            color: #a78bfa;
        }

        body.dark-mode .text-indigo-600 {
            color: #818cf8;
        }

        body.dark-mode .text-pink-600 {
            color: #f472b6;
        }

        body.dark-mode .text-gray-500 {
            color: #9ca3af;
        }

        body.dark-mode .bg-red-500:hover,
        body.dark-mode .bg-red-600 {
            background-color: #dc2626;
        }

        body.dark-mode .bg-red-600:hover,
        body.dark-mode .bg-red-700 {
            background-color: #b91c1c;
        }

        body.dark-mode .bg-blue-500:hover,
        body.dark-mode .bg-blue-600 {
            background-color: #2563eb;
        }

        body.dark-mode .bg-blue-600:hover,
        body.dark-mode .bg-blue-700 {
            background-color: #1d4ed8;
        }

        body.dark-mode .bg-green-500:hover,
        body.dark-mode .bg-green-600 {
            background-color: #16a34a;
        }

        body.dark-mode .bg-green-600:hover,
        body.dark-mode .bg-green-700 {
            background-color: #15803d;
        }

        body.dark-mode .bg-purple-500:hover,
        body.dark-mode .bg-purple-600 {
            background-color: #9333ea;
        }

        body.dark-mode .bg-purple-600:hover,
        body.dark-mode .bg-purple-700 {
            background-color: #7c3aed;
        }

        body.dark-mode .bg-indigo-500:hover,
        body.dark-mode .bg-indigo-600 {
            background-color: #6366f1;
        }

        body.dark-mode .bg-indigo-600:hover,
        body.dark-mode .bg-indigo-700 {
            background-color: #4f46e5;
        }

        body.dark-mode .bg-pink-500:hover,
        body.dark-mode .bg-pink-600 {
            background-color: #db2777;
        }

        body.dark-mode .bg-pink-600:hover,
        body.dark-mode .bg-pink-700 {
            background-color: #be185d;
        }

        body.dark-mode .bg-gray-300:hover,
        body.dark-mode .bg-gray-600 {
            background-color: #4b5563;
        }

        body.dark-mode .bg-gray-600:hover,
        body.dark-mode .bg-gray-500 {
            background-color: #6b7280;
        }

        body.dark-mode .text-gray-700 {
            color: #d1d5db;
        }

        body.dark-mode .text-gray-300 {
            color: #d1d5db;
        }

        /* Dynamic content dark mode styles */
        body.dark-mode .bg-red-50 {
            background-color: rgba(127, 29, 29, 0.2);
        }

        body.dark-mode .border-red-200 {
            border-color: #7f1d1d;
        }

        body.dark-mode .text-red-800 {
            color: #fecaca;
        }

        body.dark-mode .text-red-600 {
            color: #fca5a5;
        }

        body.dark-mode .bg-blue-50 {
            background-color: rgba(30, 64, 175, 0.2);
        }

        body.dark-mode .border-blue-200 {
            border-color: #1e40af;
        }

        body.dark-mode .text-blue-800 {
            color: #bfdbfe;
        }

        body.dark-mode .text-blue-600 {
            color: #93c5fd;
        }

        body.dark-mode .bg-green-50 {
            background-color: rgba(20, 83, 45, 0.2);
        }

        body.dark-mode .border-green-200 {
            border-color: #14532d;
        }

        body.dark-mode .text-green-800 {
            color: #bbf7d0;
        }

        body.dark-mode .text-green-600 {
            color: #86efac;
        }

        body.dark-mode .bg-purple-50 {
            background-color: rgba(88, 28, 135, 0.2);
        }

        body.dark-mode .border-purple-200 {
            border-color: #581c87;
        }

        body.dark-mode .text-purple-800 {
            color: #e9d5ff;
        }

        body.dark-mode .text-purple-600 {
            color: #c4b5fd;
        }

        body.dark-mode .bg-indigo-50 {
            background-color: rgba(67, 56, 202, 0.2);
        }

        body.dark-mode .border-indigo-200 {
            border-color: #4338ca;
        }

        body.dark-mode .text-indigo-800 {
            color: #c7d2fe;
        }

        body.dark-mode .text-indigo-600 {
            color: #a5b4fc;
        }

        body.dark-mode .bg-pink-50 {
            background-color: rgba(153, 21, 75, 0.2);
        }

        body.dark-mode .border-pink-200 {
            border-color: #991547;
        }

        body.dark-mode .text-pink-800 {
            color: #fce7f3;
        }

        body.dark-mode .text-pink-600 {
            color: #f9a8d4;
        }

        /* Disabled button styles */
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        body.dark-mode button:disabled {
            opacity: 0.4;
        }

        /* Additional disabled button styling for better visibility */
        button:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        /* CSS-based disabling for buttons with no-data class */
        button.no-data {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        body.dark-mode button.no-data {
            opacity: 0.4;
        }

        button.no-data:hover {
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="container mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-broom mr-2"></i>Space Cleaner
                </h1>
                <p class="text-gray-600">
                    Clean up spacing issues in business partner names and product names
                </p>
            </div>

        <!-- Sections -->
        <div class="space-y-8">

            <!-- Business Partners Row -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Business Partners</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Names starting with spaces -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-red-600">
                                <i class="fas fa-arrow-left mr-2"></i>Leading Spaces
                                <span id="leadingSpacesCount" class="text-sm font-normal text-gray-500">(0)</span>
                            </h3>
                            <button id="cleanLeadingBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center space-x-2 text-sm" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Clean All</span>
                            </button>
                        </div>
                        <div id="leadingSpacesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </div>
                        </div>
                    </div>

                    <!-- Names ending with spaces -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-blue-600">
                                <i class="fas fa-arrow-right mr-2"></i>Trailing Spaces
                                <span id="trailingSpacesCount" class="text-sm font-normal text-gray-500">(0)</span>
                            </h3>
                            <button id="cleanTrailingBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center space-x-2 text-sm" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Clean All</span>
                            </button>
                        </div>
                        <div id="trailingSpacesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </div>
                        </div>
                    </div>

                    <!-- Names with multiple spaces -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-green-600">
                                <i class="fas fa-arrows-alt-h mr-2"></i>Multiple Spaces
                                <span id="multipleSpacesCount" class="text-sm font-normal text-gray-500">(0)</span>
                            </h3>
                            <button id="cleanMultipleBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center space-x-2 text-sm" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Clean All</span>
                            </button>
                        </div>
                        <div id="multipleSpacesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Products Row -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Products</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Product names starting with spaces -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-purple-600">
                                <i class="fas fa-box mr-2"></i>Leading Spaces
                                <span id="productLeadingSpacesCount" class="text-sm font-normal text-gray-500">(0)</span>
                            </h3>
                            <button id="cleanProductLeadingBtn" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center space-x-2 text-sm" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Clean All</span>
                            </button>
                        </div>
                        <div id="productLeadingSpacesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </div>
                        </div>
                    </div>

                    <!-- Product names ending with spaces -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-indigo-600">
                                <i class="fas fa-box mr-2"></i>Trailing Spaces
                                <span id="productTrailingSpacesCount" class="text-sm font-normal text-gray-500">(0)</span>
                            </h3>
                            <button id="cleanProductTrailingBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center space-x-2 text-sm" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Clean All</span>
                            </button>
                        </div>
                        <div id="productTrailingSpacesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </div>
                        </div>
                    </div>

                    <!-- Product names with multiple spaces -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-pink-600">
                                <i class="fas fa-box mr-2"></i>Multiple Spaces
                                <span id="productMultipleSpacesCount" class="text-sm font-normal text-gray-500">(0)</span>
                            </h3>
                            <button id="cleanProductMultipleBtn" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center space-x-2 text-sm" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Clean All</span>
                            </button>
                        </div>
                        <div id="productMultipleSpacesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                Confirm Action
            </h3>
            <p id="modalText" class="mb-6">
                Are you sure you want to clean all space issues in business partner names? This action cannot be undone.
            </p>
            <div class="flex justify-end space-x-3">
                <button id="cancelBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition duration-200">
                    Cancel
                </button>
                <button id="confirmBtn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition duration-200">
                    <i class="fas fa-broom mr-2"></i>Clean All
                </button>
            </div>
        </div>
    </div>

    <script>
        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadLeadingSpaces();
            loadTrailingSpaces();
            loadMultipleSpaces();
            loadProductLeadingSpaces();
            loadProductTrailingSpaces();
            loadProductMultipleSpaces();
        });

        // Modal handling
        const modal = document.getElementById('confirmModal');
        const modalText = document.getElementById('modalText');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmBtn = document.getElementById('confirmBtn');

        let currentCleanFunction = null;

        // Individual button handlers
        document.getElementById('cleanLeadingBtn').addEventListener('click', function() {
            modalText.textContent = 'Are you sure you want to clean all leading spaces from business partner names? This action cannot be undone.';
            currentCleanFunction = cleanLeadingSpaces;
            modal.style.display = 'block';
        });

        document.getElementById('cleanTrailingBtn').addEventListener('click', function() {
            modalText.textContent = 'Are you sure you want to clean all trailing spaces from business partner names? This action cannot be undone.';
            currentCleanFunction = cleanTrailingSpaces;
            modal.style.display = 'block';
        });

        document.getElementById('cleanMultipleBtn').addEventListener('click', function() {
            modalText.textContent = 'Are you sure you want to clean all multiple consecutive spaces from business partner names? This action cannot be undone.';
            currentCleanFunction = cleanMultipleSpaces;
            modal.style.display = 'block';
        });

        document.getElementById('cleanProductLeadingBtn').addEventListener('click', function() {
            modalText.textContent = 'Are you sure you want to clean all leading spaces from product names? This action cannot be undone.';
            currentCleanFunction = cleanProductLeadingSpaces;
            modal.style.display = 'block';
        });

        document.getElementById('cleanProductTrailingBtn').addEventListener('click', function() {
            modalText.textContent = 'Are you sure you want to clean all trailing spaces from product names? This action cannot be undone.';
            currentCleanFunction = cleanProductTrailingSpaces;
            modal.style.display = 'block';
        });

        document.getElementById('cleanProductMultipleBtn').addEventListener('click', function() {
            modalText.textContent = 'Are you sure you want to clean all multiple consecutive spaces from product names? This action cannot be undone.';
            currentCleanFunction = cleanProductMultipleSpaces;
            modal.style.display = 'block';
        });

        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            currentCleanFunction = null;
        });

        confirmBtn.addEventListener('click', async function() {
            modal.style.display = 'none';
            if (currentCleanFunction) {
                await currentCleanFunction();
                currentCleanFunction = null;
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                currentCleanFunction = null;
            }
        });

        async function loadLeadingSpaces() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/fetch-space-start'));
                const data = await response.json();

                const container = document.getElementById('leadingSpacesList');
                const countElement = document.getElementById('leadingSpacesCount');
                const button = document.getElementById('cleanLeadingBtn');

                countElement.textContent = `(${data.length})`;
                button.disabled = data.length === 0;
                if (data.length === 0) {
                    button.classList.add('no-data');
                } else {
                    button.classList.remove('no-data');
                }

                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">No issues found</div>';
                    return;
                }

                container.innerHTML = data.map(item => `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-3">
                        <div class="font-medium text-red-800 dark:text-red-200">${item.NAME}</div>
                        <div class="text-sm text-red-600 dark:text-red-400">ID: ${item.C_BPARTNER_ID}</div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading leading spaces:', error);
                document.getElementById('leadingSpacesList').innerHTML = '<div class="text-center text-red-500 py-4">Error loading data</div>';
            }
        }

        async function loadTrailingSpaces() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/fetch-space-end'));
                const data = await response.json();

                const container = document.getElementById('trailingSpacesList');
                const countElement = document.getElementById('trailingSpacesCount');
                const button = document.getElementById('cleanTrailingBtn');

                countElement.textContent = `(${data.length})`;
                button.disabled = data.length === 0;
                if (data.length === 0) {
                    button.classList.add('no-data');
                } else {
                    button.classList.remove('no-data');
                }

                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">No issues found</div>';
                    return;
                }

                container.innerHTML = data.map(item => `
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-3">
                        <div class="font-medium text-blue-800 dark:text-blue-200">${item.NAME}</div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">ID: ${item.C_BPARTNER_ID}</div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading trailing spaces:', error);
                document.getElementById('trailingSpacesList').innerHTML = '<div class="text-center text-red-500 py-4">Error loading data</div>';
            }
        }

        async function loadMultipleSpaces() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/fetch-space-multiple'));
                const data = await response.json();

                const container = document.getElementById('multipleSpacesList');
                const countElement = document.getElementById('multipleSpacesCount');
                const button = document.getElementById('cleanMultipleBtn');

                countElement.textContent = `(${data.length})`;
                button.disabled = data.length === 0;
                if (data.length === 0) {
                    button.classList.add('no-data');
                } else {
                    button.classList.remove('no-data');
                }

                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">No issues found</div>';
                    return;
                }

                container.innerHTML = data.map(item => `
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded p-3">
                        <div class="font-medium text-green-800 dark:text-green-200">${item.NAME}</div>
                        <div class="text-sm text-green-600 dark:text-green-400">ID: ${item.C_BPARTNER_ID}</div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading multiple spaces:', error);
                document.getElementById('multipleSpacesList').innerHTML = '<div class="text-center text-red-500 py-4">Error loading data</div>';
            }
        }

        async function loadProductLeadingSpaces() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/fetch-product-space-start'));
                const data = await response.json();

                const container = document.getElementById('productLeadingSpacesList');
                const countElement = document.getElementById('productLeadingSpacesCount');
                const button = document.getElementById('cleanProductLeadingBtn');

                countElement.textContent = `(${data.length})`;
                button.disabled = data.length === 0;
                if (data.length === 0) {
                    button.classList.add('no-data');
                } else {
                    button.classList.remove('no-data');
                }

                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">No issues found</div>';
                    return;
                }

                container.innerHTML = data.map(item => `
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded p-3">
                        <div class="font-medium text-purple-800 dark:text-purple-200">${item.NAME}</div>
                        <div class="text-sm text-purple-600 dark:text-purple-400">ID: ${item.M_PRODUCT_ID}</div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading product leading spaces:', error);
                document.getElementById('productLeadingSpacesList').innerHTML = '<div class="text-center text-red-500 py-4">Error loading data</div>';
            }
        }

        async function loadProductTrailingSpaces() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/fetch-product-space-end'));
                const data = await response.json();

                const container = document.getElementById('productTrailingSpacesList');
                const countElement = document.getElementById('productTrailingSpacesCount');
                const button = document.getElementById('cleanProductTrailingBtn');

                countElement.textContent = `(${data.length})`;
                button.disabled = data.length === 0;
                if (data.length === 0) {
                    button.classList.add('no-data');
                } else {
                    button.classList.remove('no-data');
                }

                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">No issues found</div>';
                    return;
                }

                container.innerHTML = data.map(item => `
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded p-3">
                        <div class="font-medium text-indigo-800 dark:text-indigo-200">${item.NAME}</div>
                        <div class="text-sm text-indigo-600 dark:text-indigo-400">ID: ${item.M_PRODUCT_ID}</div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading product trailing spaces:', error);
                document.getElementById('productTrailingSpacesList').innerHTML = '<div class="text-center text-red-500 py-4">Error loading data</div>';
            }
        }

        async function loadProductMultipleSpaces() {
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/fetch-product-space-multiple'));
                const data = await response.json();

                const container = document.getElementById('productMultipleSpacesList');
                const countElement = document.getElementById('productMultipleSpacesCount');
                const button = document.getElementById('cleanProductMultipleBtn');

                countElement.textContent = `(${data.length})`;
                button.disabled = data.length === 0;
                if (data.length === 0) {
                    button.classList.add('no-data');
                } else {
                    button.classList.remove('no-data');
                }

                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">No issues found</div>';
                    return;
                }

                container.innerHTML = data.map(item => `
                    <div class="bg-pink-50 dark:bg-pink-900/20 border border-pink-200 dark:border-pink-800 rounded p-3">
                        <div class="font-medium text-pink-800 dark:text-pink-200">${item.NAME}</div>
                        <div class="text-sm text-pink-600 dark:text-pink-400">ID: ${item.M_PRODUCT_ID}</div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading product multiple spaces:', error);
                document.getElementById('productMultipleSpacesList').innerHTML = '<div class="text-center text-red-500 py-4">Error loading data</div>';
            }
        }

        async function cleanLeadingSpaces() {
            const btn = document.getElementById('cleanLeadingBtn');
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Cleaning...</span>';

                const response = await fetch(API_CONFIG.getApiUrl('/update-space-start'), { method: 'POST' });

                if (response.ok) {
                    await loadLeadingSpaces();
                    alert('Leading spaces cleaned successfully!');
                } else {
                    throw new Error('Failed to clean leading spaces');
                }

            } catch (error) {
                console.error('Error cleaning leading spaces:', error);
                alert('Error occurred while cleaning leading spaces.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><span>Clean All</span>';
            }
        }

        async function cleanTrailingSpaces() {
            const btn = document.getElementById('cleanTrailingBtn');
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Cleaning...</span>';

                const response = await fetch(API_CONFIG.getApiUrl('/update-space-end'), { method: 'POST' });

                if (response.ok) {
                    await loadTrailingSpaces();
                    alert('Trailing spaces cleaned successfully!');
                } else {
                    throw new Error('Failed to clean trailing spaces');
                }

            } catch (error) {
                console.error('Error cleaning trailing spaces:', error);
                alert('Error occurred while cleaning trailing spaces.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><span>Clean All</span>';
            }
        }

        async function cleanMultipleSpaces() {
            const btn = document.getElementById('cleanMultipleBtn');
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Cleaning...</span>';

                const response = await fetch(API_CONFIG.getApiUrl('/update-space-multiple'), { method: 'POST' });

                if (response.ok) {
                    await loadMultipleSpaces();
                    alert('Multiple spaces cleaned successfully!');
                } else {
                    throw new Error('Failed to clean multiple spaces');
                }

            } catch (error) {
                console.error('Error cleaning multiple spaces:', error);
                alert('Error occurred while cleaning multiple spaces.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><span>Clean All</span>';
            }
        }

        async function cleanProductLeadingSpaces() {
            const btn = document.getElementById('cleanProductLeadingBtn');
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Cleaning...</span>';

                const response = await fetch(API_CONFIG.getApiUrl('/update-product-space-start'), { method: 'POST' });

                if (response.ok) {
                    await loadProductLeadingSpaces();
                    alert('Product leading spaces cleaned successfully!');
                } else {
                    throw new Error('Failed to clean product leading spaces');
                }

            } catch (error) {
                console.error('Error cleaning product leading spaces:', error);
                alert('Error occurred while cleaning product leading spaces.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><span>Clean All</span>';
            }
        }

        async function cleanProductTrailingSpaces() {
            const btn = document.getElementById('cleanProductTrailingBtn');
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Cleaning...</span>';

                const response = await fetch(API_CONFIG.getApiUrl('/update-product-space-end'), { method: 'POST' });

                if (response.ok) {
                    await loadProductTrailingSpaces();
                    alert('Product trailing spaces cleaned successfully!');
                } else {
                    throw new Error('Failed to clean product trailing spaces');
                }

            } catch (error) {
                console.error('Error cleaning product trailing spaces:', error);
                alert('Error occurred while cleaning product trailing spaces.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><span>Clean All</span>';
            }
        }

        async function cleanProductMultipleSpaces() {
            const btn = document.getElementById('cleanProductMultipleBtn');
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Cleaning...</span>';

                const response = await fetch(API_CONFIG.getApiUrl('/update-product-space-multiple'), { method: 'POST' });

                if (response.ok) {
                    await loadProductMultipleSpaces();
                    alert('Product multiple spaces cleaned successfully!');
                } else {
                    throw new Error('Failed to clean product multiple spaces');
                }

            } catch (error) {
                console.error('Error cleaning product multiple spaces:', error);
                alert('Error occurred while cleaning product multiple spaces.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><span>Clean All</span>';
            }
        }
    </script>

</body>
</html>
