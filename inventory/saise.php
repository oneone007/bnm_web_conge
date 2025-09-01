<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * SAISE (CASSE) PAGE
 * 
 * This page shows inventories from casse (casse = 'yes') that are confirmed by admin
 * Users can only mark them as 'done' when they complete the work
 */

session_start();


// Check admin privileges - only allow specific admin roles
if (!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Admin', 'Developer','saisie' ,'gestion stock'])) {
    header("Location: Access_Denied");    
    exit();
}

// Check if the user is logged in and session is valid
if (!isset($_SESSION['username'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Get current user for logging
$current_user = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saise - Casse Inventories</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="api_config_inv.js"></script>
    <script src="theme.js" defer></script>

    <style>
        .status-confirmed { 
            background-color: #dbeafe; 
            color: #1e40af; 
        }
        .status-done { 
            background-color: #d1fae5; 
            color: #065f46; 
        }
        .btn-done { 
            background-color: #10b981; 
            color: white; 
        }
        .btn-done:hover { 
            background-color: #059669; 
        }
        .btn-done:disabled { 
            background-color: #9ca3af; 
            cursor: not-allowed; 
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }
        .card-header {
            background-color: #fef3c7;
            border-bottom: 1px solid #f59e0b;
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
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .casse-indicator {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* DARK MODE STYLES */
        .dark .card {
            background: #1f2937;
            border-color: #374151;
            color: #e5e7eb;
        }
        .dark .card-header {
            background-color: #374151;
            border-bottom: 1px solid #f59e0b;
            color: #fde68a;
        }
        .dark .card-body {
            color: #e5e7eb;
        }
        .dark .badge.status-confirmed {
            background-color: #1e40af;
            color: #dbeafe;
        }
        .dark .badge.status-done {
            background-color: #065f46;
            color: #d1fae5;
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
        .dark .casse-indicator {
            background: linear-gradient(45deg, #92400e, #f59e0b);
            color: #fff7ed;
        }
        .dark body {
            background-color: #111827;
            color: #e5e7eb;
        }
        .dark .bg-white {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
        }
        .dark .bg-amber-50 {
            background-color: #374151 !important;
            color: #fde68a !important;
        }
        .dark .text-amber-900 {
            color: #fde68a !important;
        }
        .dark .text-amber-700 {
            color: #f59e0b !important;
        }
        .dark .border-amber-200 {
            border-color: #f59e0b !important;
        }
        .dark .bg-blue-100 {
            background-color: #1e40af !important;
            color: #dbeafe !important;
        }
        .dark .bg-green-100 {
            background-color: #065f46 !important;
            color: #d1fae5 !important;
        }
        .dark .bg-amber-100 {
            background-color: #92400e !important;
            color: #fde68a !important;
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
        .dark .text-blue-900 {
            color: #dbeafe !important;
        }
        .dark .text-green-900 {
            color: #d1fae5 !important;
        }
        .dark .text-amber-900 {
            color: #fde68a !important;
        }
        .dark .text-blue-700 {
            color: #dbeafe !important;
        }
        .dark .text-green-700 {
            color: #d1fae5 !important;
        }
        .dark .text-orange-700 {
            color: #fde68a !important;
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
        .dark .border {
            border-color: #374151 !important;
        }
        .dark .shadow-sm {
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
        }
        .dark .modalContent {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
        }
    </style>
</head>
<body class="bg-amber-50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-amber-900 mb-2">
                🏪 Saise - Casse Inventories
            </h1>
            <p class="text-amber-700">View and complete confirmed casse inventory tasks</p>
            <div class="mt-4">
                <span class="casse-indicator">
                    🔥 Casse Work Area
                </span>
            </div>
        </div>

        <!-- Info Section -->
        <div class="bg-white rounded-lg shadow-sm border border-amber-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="text-2xl">ℹ️</div>
                <h2 class="text-lg font-semibold text-gray-900">How it works</h2>
            </div>
            <div class="text-gray-700 space-y-2">
                <p>• This page shows <strong>casse inventories</strong> that have been <strong>confirmed by admin</strong></p>
                <p>• When you complete the physical inventory work, click <strong>"Mark as Done"</strong></p>
                <p>• Once marked as done, the inventory cannot be changed anymore</p>
                <p class="text-amber-700 font-medium">👤 Logged in as: <span class="font-bold"><?= htmlspecialchars($current_user) ?></span></p>
            </div>
        </div>

        <!-- Inventories List -->
        <div id="inventoriesContainer" class="space-y-4">
            <!-- Loading state -->
            <div id="loadingState" class="text-center py-12">
                <div class="text-6xl mb-4">⏳</div>
                <h3 class="text-xl font-semibold text-amber-900 mb-2">Loading casse inventories...</h3>
                <p class="text-amber-700">Please wait while we fetch your confirmed tasks.</p>
            </div>
            
            <!-- Error state (hidden by default) -->
            <div id="errorState" class="text-center py-12 hidden">
                <div class="text-6xl mb-4">❌</div>
                <h3 class="text-xl font-semibold text-red-900 mb-2">Error loading inventories</h3>
                <p class="text-red-600" id="errorMessage">Unable to connect to the API.</p>
                <button onclick="loadCasseInventories()" class="mt-4 bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-md">
                    🔄 Retry
                </button>
            </div>
            
            <!-- Empty state (hidden by default) -->
            <div id="emptyState" class="text-center py-12 hidden">
                <div class="text-6xl mb-4">🎉</div>
                <h3 class="text-xl font-semibold text-amber-900 mb-2">No pending casse inventories</h3>
                <p class="text-amber-700">All your casse tasks are complete or there are no confirmed tasks yet.</p>
                <div class="mt-4 text-sm text-gray-600">
                    <p>Waiting for admin to confirm new casse inventories...</p>
                </div>
            </div>
            
            <!-- Inventories will be loaded here via JavaScript -->
        </div>
        
        <!-- Quick Stats -->
        <div id="statsSection" class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
            <div class="bg-blue-100 p-4 rounded-lg">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-900" id="confirmedCount">0</div>
                    <div class="text-blue-700">Confirmed Tasks</div>
                </div>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-900" id="completedCount">0</div>
                    <div class="text-green-700">Completed Today</div>
                </div>
            </div>
            <div class="bg-amber-100 p-4 rounded-lg">
                <div class="text-center">
                    <div class="text-2xl font-bold text-amber-900" id="totalItems">0</div>
                    <div class="text-amber-700">Total Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Inventory Details -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[80vh] overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b bg-amber-50">
                    <h3 class="text-lg font-semibold text-amber-900">🏪 Casse Inventory Details</h3>
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

    <script>
        // Get user info from PHP session
        const currentUser = '<?= htmlspecialchars($current_user) ?>';
        
        console.log('🏪 Saise Page: Loaded successfully');
        console.log('🏪 Current user:', currentUser);
        
        // Load casse inventories from API
        async function loadCasseInventories() {
            console.log('🏪 Loading casse inventories...');
            
            const loadingState = document.getElementById('loadingState');
            const errorState = document.getElementById('errorState');
            const emptyState = document.getElementById('emptyState');
            const container = document.getElementById('inventoriesContainer');
            const statsSection = document.getElementById('statsSection');
            
            // Show loading state
            loadingState.classList.remove('hidden');
            errorState.classList.add('hidden');
            emptyState.classList.add('hidden');
            statsSection.classList.add('hidden');
            
            // Remove existing inventory cards
            const existingCards = container.querySelectorAll('.inventory-card');
            existingCards.forEach(card => card.remove());
            
            try {
                // Call Python API to get all inventories, we'll filter for casse + confirmed
                const response = await fetch(`${API_CONFIGinv.getApiUrl('/inventory/list')}?limit=200&offset=0`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const apiResult = await response.json();
                
                if (!apiResult.success) {
                    throw new Error(apiResult.error || 'API call failed');
                }
                
                let allInventories = apiResult.inventories || [];
                
                // Filter for casse inventories that are confirmed
                const casseInventories = allInventories.filter(inventory => {
                    return inventory.casse === 'yes' && inventory.status === 'confirmed';
                });
                
                // Also get completed ones for stats (done today)
                const completedToday = allInventories.filter(inventory => {
                    const today = new Date().toISOString().split('T')[0];
                    const updatedDate = new Date(inventory.updated_at).toISOString().split('T')[0];
                    return inventory.casse === 'yes' && 
                           inventory.status === 'done' && 
                           updatedDate === today;
                });
                
                // Hide loading state
                loadingState.classList.add('hidden');
                
                if (casseInventories.length === 0) {
                    // Show empty state
                    emptyState.classList.remove('hidden');
                } else {
                    // Render inventories
                    casseInventories.forEach(inventory => {
                        renderCasseInventoryCard(inventory, container);
                    });
                }
                
                // Update statistics
                updateStats(casseInventories, completedToday, allInventories);
                statsSection.classList.remove('hidden');
                
                console.log('✅ Casse inventories loaded:', casseInventories.length);
                
            } catch (error) {
                console.error('❌ Error loading casse inventories:', error);
                
                // Hide loading state and show error
                loadingState.classList.add('hidden');
                errorState.classList.remove('hidden');
                document.getElementById('errorMessage').textContent = error.message;
            }
        }
        
        // Render a single casse inventory card
        function renderCasseInventoryCard(inventory, container) {
            const card = document.createElement('div');
            card.className = 'card inventory-card';
            card.setAttribute('data-id', inventory.id);
            
            const createdDate = new Date(inventory.created_at);
            const updatedDate = new Date(inventory.updated_at);
            
            card.innerHTML = `
                <div class="card-header">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-amber-900">
                                    🏪 #${inventory.id} - ${escapeHtml(inventory.title)}
                                </h3>
                                <span class="badge status-confirmed">
                                    ✅ Confirmed by Admin
                                </span>
                            </div>
                            <div class="text-sm text-amber-700">
                                <span class="mr-4">📦 ${inventory.total_items || 0} items to process</span>
                                <span class="mr-4">👤 Created by: ${escapeHtml(inventory.created_by)}</span>
                                <span>📅 ${createdDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        <div class="flex gap-2">
                            <button onclick="markAsDone(${inventory.id})" 
                                    class="btn btn-done" title="Mark this inventory as completed">
                                ✅ Mark as Done
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    ${inventory.notes ? `
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Notes:</h4>
                            <p class="text-gray-600 text-sm bg-amber-50 p-2 rounded border border-amber-200">
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
                            <span class="font-medium text-gray-700">Confirmed:</span>
                            <span class="text-gray-600">${updatedDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                    </div>
                    
                    <!-- View Details Button -->
                    <div class="mt-4 pt-4 border-t border-amber-200">
                        <button onclick="viewInventoryDetails(${inventory.id})" 
                                class="text-amber-600 hover:text-amber-800 text-sm font-medium">
                            📋 View Items to Process
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(card);
        }
        
        // Mark inventory as done
        async function markAsDone(inventoryId) {
            console.log('🏪 Marking inventory as done:', inventoryId);
            
            const card = document.querySelector(`[data-id="${inventoryId}"]`);
            if (!card) return;
            
            // Confirm with user
            if (!confirm('Are you sure you want to mark this inventory as DONE?\n\nThis action cannot be undone.')) {
                return;
            }
            
            card.classList.add('loading');
            
            try {
                // Call Python API to update status to 'done'
                const response = await fetch(`${API_CONFIGinv.getApiUrl('/inventory/update_status/')}${inventoryId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: 'done',
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
                
                if (result.success) {
                    console.log('✅ Inventory marked as done:', result);
                    
                    // Show success message
                    alert('✅ Inventory marked as DONE!\n\n🎉 Great job! The inventory task has been completed successfully.');
                    
                    // Reload the page to show updated list
                    loadCasseInventories();
                } else {
                    console.error('❌ Failed to mark as done:', result.error);
                    alert('❌ Error: ' + result.error);
                }
                
            } catch (error) {
                console.error('❌ Error marking as done:', error);
                alert('❌ Error marking inventory as done: ' + error.message);
            } finally {
                card.classList.remove('loading');
            }
        }
        
        // View inventory details
        async function viewInventoryDetails(inventoryId) {
            console.log('🏪 Viewing inventory details:', inventoryId);
            
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('modalContent');
            
            content.innerHTML = '<div class="text-center">Loading inventory details...</div>';
            modal.classList.remove('hidden');
            
            try {
                const response = await fetch(`${API_CONFIGinv.getApiUrl('/inventory/details/')}${inventoryId}`);
                const data = await response.json();
                
                if (data.success) {
                    content.innerHTML = generateCasseDetailsHTML(data.inventory, data.items);
                } else {
                    content.innerHTML = '<div class="text-red-600">Error loading details: ' + data.error + '</div>';
                }
            } catch (error) {
                content.innerHTML = '<div class="text-red-600">Error: ' + error.message + '</div>';
            }
        }
        
        // Generate HTML for casse inventory details
        function generateCasseDetailsHTML(inventory, items) {
            let html = `
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="casse-indicator">🏪 Casse Inventory</span>
                        <span class="badge status-confirmed">Confirmed by Admin</span>
                    </div>
                    <h4 class="text-lg font-semibold mb-2">${inventory.title}</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Created by:</strong> ${inventory.created_by}</div>
                        <div><strong>Total Items:</strong> ${items.length}</div>
                        <div><strong>Created:</strong> ${new Date(inventory.created_at).toLocaleString()}</div>
                        <div><strong>Confirmed:</strong> ${new Date(inventory.updated_at).toLocaleString()}</div>
                    </div>
                    ${inventory.notes ? `<div class="mt-3"><strong>Notes:</strong><br><div class="bg-amber-50 p-2 rounded text-sm border border-amber-200">${inventory.notes}</div></div>` : ''}
                </div>
                
                <div class="space-y-6">
            `;
            
            // Group items by type
            const entryItems = items.filter(item => item.type === 'entry');
            const sortieItems = items.filter(item => item.type === 'sortie');
            
            // Entry items
            if (entryItems.length > 0) {
                html += `
                    <div>
                        <h5 class="font-semibold text-green-700 mb-3 flex items-center gap-2">
                            📦 ENTRY - Items to Add (${entryItems.length} items)
                        </h5>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                `;
                
                entryItems.forEach(item => {
                    const isManual = item.is_manual_entry == 1;
                    html += `
                        <div class="bg-green-50 p-3 rounded border border-green-200${isManual ? ' border-l-4 border-l-orange-400' : ''}">
                            <div class="font-medium">${item.product_name}${isManual ? ' <span class="text-orange-600 text-xs">📝 Manual Entry</span>' : ''}</div>
                            <div class="text-sm text-gray-600">
                                <strong>Quantity: ${item.quantity}</strong> | 
                                Lot: ${item.lot || 'N/A'} | 
                                PPA: ${parseFloat(item.ppa).toFixed(2)} DA |
                                Date: ${item.date ? new Date(item.date).toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
            
            // Sortie items
            if (sortieItems.length > 0) {
                html += `
                    <div>
                        <h5 class="font-semibold text-orange-700 mb-3 flex items-center gap-2">
                            📤 SORTIE - Items to Remove (${sortieItems.length} items)
                        </h5>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                `;
                
                sortieItems.forEach(item => {
                    const isManual = item.is_manual_entry == 1;
                    html += `
                        <div class="bg-orange-50 p-3 rounded border border-orange-200${isManual ? ' border-l-4 border-l-orange-600' : ''}">
                            <div class="font-medium">${item.product_name}${isManual ? ' <span class="text-orange-600 text-xs">📝 Manual Entry</span>' : ''}</div>
                            <div class="text-sm text-gray-600">
                                <strong>Quantity: ${item.quantity}</strong> | 
                                Lot: ${item.lot || 'N/A'} | 
                                PPA: ${parseFloat(item.ppa).toFixed(2)} DA |
                                Date: ${item.date ? new Date(item.date).toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
            
            html += `</div>`;
            
            // Instructions
            html += `
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h6 class="font-semibold text-blue-900 mb-2">📝 Instructions:</h6>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>ENTRY</strong>: Add these items to the casse inventory</li>
                        <li>• <strong>SORTIE</strong>: Remove these items from the casse inventory</li>
                        <li>• When all physical work is done, click "Mark as Done"</li>
                        <li>• Once marked as done, this task cannot be changed</li>
                    </ul>
                </div>
            `;
            
            return html;
        }
        
        // Update statistics
        function updateStats(confirmedInventories, completedToday, allInventories) {
            const totalItems = confirmedInventories.reduce((sum, inv) => sum + (inv.total_items || 0), 0);
            
            document.getElementById('confirmedCount').textContent = confirmedInventories.length;
            document.getElementById('completedCount').textContent = completedToday.length;
            document.getElementById('totalItems').textContent = totalItems;
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
        
        // Load inventories when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🏪 Saise page loaded, loading casse inventories...');
            loadCasseInventories();
        });
    </script>
</body>
</html>
