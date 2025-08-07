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

    <style>
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-confirmed { background-color: #dbeafe; color: #1e40af; }
        .status-canceled { background-color: #fecaca; color: #991b1b; }
        .status-done { background-color: #d1fae5; color: #065f46; }
        .btn-confirm { background-color: #3b82f6; color: white; }
        .btn-confirm:hover { background-color: #2563eb; }
        .btn-cancel { background-color: #ef4444; color: white; }
        .btn-cancel:hover { background-color: #dc2626; }
        .btn-done { background-color: #10b981; color: white; }
        .btn-done:hover { background-color: #059669; }
        .btn-done:disabled { background-color: #9ca3af; cursor: not-allowed; }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }
        .card-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 8px 8px 0 0;
            padding: 1rem;
        }
        .card-body {
            padding: 1rem;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            margin: 0.25rem;
        }
        .filter-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* DARK MODE STYLES */
        .dark .card {
            background: #1f2937;
            border-color: #374151;
            color: #e5e7eb;
        }
        .dark .card-header {
            background-color: #374151;
            border-bottom: 1px solid #e5e7eb;
            color: #fde68a;
        }
        .dark .card-body {
            color: #e5e7eb;
        }
        .dark .badge.status-pending {
            background-color: #92400e;
            color: #fde68a;
        }
        .dark .badge.status-confirmed {
            background-color: #1e40af;
            color: #dbeafe;
        }
        .dark .badge.status-canceled {
            background-color: #991b1b;
            color: #fecaca;
        }
        .dark .badge.status-done {
            background-color: #065f46;
            color: #d1fae5;
        }
        .dark .btn-confirm {
            background-color: #2563eb;
            color: #e5e7eb;
        }
        .dark .btn-confirm:hover {
            background-color: #1e40af;
        }
        .dark .btn-cancel {
            background-color: #dc2626;
            color: #e5e7eb;
        }
        .dark .btn-cancel:hover {
            background-color: #991b1b;
        }
        .dark .btn-done {
            background-color: #059669;
            color: #e5e7eb;
        }
        .dark .btn-done:hover {
            background-color: #065f46;
        }
        .dark .btn-done:disabled {
            background-color: #374151;
            color: #9ca3af;
        }
        .dark .filter-section {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #374151;
        }
        .dark body {
            background-color: #111827;
            color: #e5e7eb;
        }
        .dark .bg-white {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
        }
        .dark .bg-gray-100 {
            background-color: #111827 !important;
            color: #e5e7eb !important;
        }
        .dark .bg-gray-50 {
            background-color: #374151 !important;
            color: #e5e7eb !important;
        }
        .dark .text-gray-900 {
            color: #e5e7eb !important;
        }
        .dark .text-gray-700 {
            color: #d1d5db !important;
        }
        .dark .text-gray-600 {
            color: #9ca3af !important;
        }
        .dark .text-blue-600 {
            color: #dbeafe !important;
        }
        .dark .text-blue-800 {
            color: #1e40af !important;
        }
        .dark .bg-blue-600 {
            background-color: #1e40af !important;
            color: #dbeafe !important;
        }
        .dark .bg-blue-700 {
            background-color: #2563eb !important;
            color: #dbeafe !important;
        }
        .dark .bg-green-50 {
            background-color: #065f46 !important;
            color: #d1fae5 !important;
        }
        .dark .bg-orange-50 {
            background-color: #92400e !important;
            color: #fde68a !important;
        }
        .dark .border-green-200 {
            border-color: #065f46 !important;
        }
        .dark .border-orange-200 {
            border-color: #92400e !important;
        }
        .dark .border-l-orange-400 {
            border-left-color: #f59e0b !important;
        }
        .dark .border-l-orange-600 {
            border-left-color: #92400e !important;
        }
        .dark .border-blue-200 {
            border-color: #1e40af !important;
        }
        .dark .modalContent {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
        }
    </style>
        <script src="api_config_inv.js"></script>

</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                📋 Inventory Administration
            </h1>
            <p class="text-gray-600">Manage and track all inventory records</p>
        </div>

        <!-- Filters Section -->
        <div class="filter-section">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters & Search</h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Title, notes, or user..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
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
                    Showing <?= count($inventories) ?> record(s)
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
        </div>
    </div> -->

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
    <div class="fixed bottom-6 left-6 z-50">
        <button id="popuBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full shadow-lg font-medium">
            ➕ Popup
        </button>
    </div>

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
        
        // Get filter parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {
            status: urlParams.get('status') || '',
            date_from: urlParams.get('date_from') || '',
            date_to: urlParams.get('date_to') || '',
            search: urlParams.get('search') || ''
        };
        
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
                queryParams.push('limit=100'); // Get more records for admin view
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
                
                // Apply additional filters (date and search) since Python API doesn't support them yet
                if (filters.date_from || filters.date_to || filters.search) {
                    inventories = inventories.filter(inventory => {
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
                }
                
                // Hide loading state
                loadingState.classList.add('hidden');
                
                // Update results count
                const resultsCount = document.querySelector('.text-gray-500.text-sm.ml-4');
                if (resultsCount) {
                    resultsCount.textContent = `Showing ${inventories.length} record(s)`;
                }
                
                if (inventories.length === 0) {
                    // Show empty state
                    emptyState.classList.remove('hidden');
                } else {
                    // Render inventories
                    inventories.forEach(inventory => {
                        renderInventoryCard(inventory, container);
                    });
                }
                
                console.log('✅ Inventories loaded successfully:', inventories.length);
                
            } catch (error) {
                console.error('❌ Error loading inventories:', error);
                
                // Hide loading state and show error
                loadingState.classList.add('hidden');
                errorState.classList.remove('hidden');
                document.getElementById('errorMessage').textContent = error.message;
            }
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
                                    #${inventory.id} - ${escapeHtml(inventory.title)}
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
                            <button onclick="handleInsert(${inventory.id})" 
                                class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                Insert
                            </button>   
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


    // Modify the handleInsert function
async function handleInsert(inventoryId) {
    try {
        // 1. Get inventory details
        const response = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/details/${inventoryId}`);
        const data = await response.json();
        
        if (!data.success || !data.items || data.items.length === 0) {
            throw new Error('No items found in inventory');
        }

        // 2. Show modal for each item that needs attribute instance creation
        for (const item of data.items) {
            let itemData = {
                inventory_id: inventoryId,
                product_name: item.product_name,
                quantity: item.quantity,
                qty_dispo: item.qty_dispo,
                m_attributesetinstance_id: item.m_attributesetinstance_id
            };
            if (!item.m_attributesetinstance_id) {
                // Show modal to collect attribute information
                const attributeData = await showAttributeModal(item);
                
                // Merge the collected data with our item data
                itemData = {
                    ...itemData,
                    lot: attributeData.lot,
                    date_expiration: attributeData.date_expiration,
                    attributes: attributeData.attributes  // This is the crucial change
                };
            }
                console.log('Collected attribute data:', itemData);

            // Proceed with insertion
            const insertionResponse = await fetch(`${API_CONFIGinv.getApiUrl()}/inventory/insert_inventory`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(itemData)  // Send the complete item data
            });

            if (!insertionResponse.ok) {
                const errorData = await insertionResponse.json();
                console.error(`Failed to insert item: ${item.product_name}`, errorData);
                throw new Error(`Failed to insert ${item.product_name}: ${errorData.error || 'Unknown error'}`);
            }
        }
        
        alert('All items inserted successfully!');
        
    } catch (error) {
        console.error('Error during insertion:', error);
        alert(`Insertion failed: ${error.message}`);
    }
}

// New function to show attribute collection modal
// Update the showAttributeModal function
function showAttributeModal(item) {
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
                                <input type="text" name="lot" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiration Date</label>
                                <input type="date" name="date_expiration" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attribute Values -->
                    <div class="md:col-span-2">
                        <h4 class="font-medium text-lg mb-2 dark:text-gray-200">Attribute Values</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Achat</label>
                                <input type="number" step="0.01" name="Prix Achat" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Colisage</label>
                                <input type="number" step="0.01" name="Colisage" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PPA</label>
                                <input type="number" step="0.01" name="PPA" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Vente</label>
                                <input type="number" step="0.01" name="Prix Vente" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Revient</label>
                                <input type="number" step="0.01" name="Prix Revient" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                                <input type="text" name="Fournisseur" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bonus</label>
                                <input type="number" step="0.01" name="Bonus" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bonus Vente</label>
                                <input type="number" step="0.01" name="Bonus Vente" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remise Supp</label>
                                <input type="number" step="0.01" name="Remise Supp" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remise Vente</label>
                                <input type="number" step="0.01" name="Remise Vente" class="mt-1 block w-full border border-gray-300 rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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

        modal.querySelector('#attributeForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
    
            // Filter out empty values and prepare for backend
            const attributes = {};
            for (const [key, value] of Object.entries(data)) {
                if (value && key !== 'lot' && key !== 'date_expiration') {
                    attributes[key] = value;
                }
            }

            let date = data.date_expiration;
            if (date) {
                const [year, month, day] = date.split("-");
                const formattedDate = `${day}/${month}/${year.slice(-2)}`; // dd/mm/yy
                data.date_expiration = formattedDate;
            }

            resolve({
                lot: data.lot,
                date_expiration: data.date_expiration,
                attributes: attributes
            });

            modal.remove();
});


        document.body.appendChild(modal);
    });
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
                    <div class="bg-green-50 p-3 rounded border border-green-200${isManual ? ' border-l-4 border-l-orange-400' : ''}">
                        <div class="font-medium">${item.product_name}${isManual ? ' <span class="text-orange-600 text-xs">📝 Manual Entry</span>' : ''}</div>
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
                    <div class="bg-orange-50 p-3 rounded border border-orange-200${isManual ? ' border-l-4 border-l-orange-600' : ''}">
                        <div class="font-medium">${item.product_name}${isManual ? ' <span class="text-orange-600 text-xs">📝 Manual Entry</span>' : ''}</div>
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
