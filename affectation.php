<?php

session_start();

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat','Sup Vente', 'Comptable'])) {
    header("Location: Acess_Denied");    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Documents Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="theme.js"></script>
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-created {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        
        .document-row:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
        }
        
        .document-row {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        
        .document-row:hover {
            background-color: #f8fafc;
        }
        
        .document-row.bg-blue-50 {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #1f2937;
            color: #f3f4f6;
        }

        .dark-mode .bg-white {
            background-color: #374151;
        }

        .dark-mode .shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .dark-mode .text-gray-900 {
            color: #f3f4f6;
        }

        .dark-mode .text-gray-500 {
            color: #9ca3af;
        }

        .dark-mode .text-gray-700 {
            color: #d1d5db;
        }

        .dark-mode .bg-gray-50 {
            background-color: #1f2937;
        }

        .dark-mode .empty-state {
            background-color: #374151;
            border-color: #4b5563;
        }

        .dark-mode .document-row:hover {
            background-color: #2d3748;
        }

        .dark-mode .document-row.bg-blue-50 {
            background-color: rgba(59, 130, 246, 0.1);
            border-left-color: #3b82f6;
        }

        .dark-mode thead {
            background-color: #374151;
        }

        .dark-mode th {
            color: #9ca3af;
        }

        .dark-mode td {
            color: #f3f4f6;
        }

        .dark-mode .divide-y > * {
            border-color: #4b5563;
        }

        .dark-mode input[type="datetime-local"] {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        .dark-mode .bg-blue-100 {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .dark-mode .text-blue-600 {
            color: #60a5fa;
        }

        .dark-mode .hover\:bg-gray-100:hover {
            background-color: #374151;
        }

        /* Region dropdown dark mode styles */
        .dark-mode #regionDropdown {
            background-color: #1f2937;
            border-color: #4b5563;
        }

        .dark-mode #regionSearchFilter {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        .dark-mode #regionSearchFilter::placeholder {
            color: #9ca3af;
        }

        .dark-mode #regionsList div {
            color: #f3f4f6;
        }

        .dark-mode #regionsList div:hover {
            background-color: #374151;
        }

        .dark-mode #regionFilterBtn {
            color: #f3f4f6;
        }

        .dark-mode #regionFilterBtn:hover {
            color: #60a5fa;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Document Management System</h1>
                    <p class="text-sm text-gray-500">Track and manage your pending documents</p>
                </div>

            </div>
        </header>

        <!-- Main content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-medium text-gray-900">Pending Documents</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Last updated: <span id="lastUpdated">Just now</span></span>
                    <button id="refreshBtn" class="p-1 rounded-full hover:bg-gray-100 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

      <div class="flex justify-between items-start mb-6">
  <!-- Total Documents Card - Left Side -->
  <div class="bg-white shadow rounded-lg">
    <div class="px-4 py-3">
      <div class="flex items-center">
        <div class="flex-shrink-0 bg-blue-500 rounded-md p-2">
          <i class="fas fa-file-alt text-white text-sm"></i>
        </div>
        <div class="ml-3">
          <dt class="text-xs font-medium text-gray-500 truncate">Total</dt>
          <dd class="flex items-baseline">
            <div class="text-lg font-semibold text-gray-900" id="totalDocuments">0</div>
          </dd>
        </div>
      </div>
    </div>
  </div>

  <!-- Region Filter - Right Side -->
  <div class="relative w-64">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-3">
        <button id="regionFilterBtn" class="flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
          <span id="selectedRegionText">All Regions</span>
          <i class="fas fa-chevron-down ml-2"></i>
        </button>
        <div id="regionDropdown" class="hidden absolute right-0 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg w-96 z-50">
          <div class="sticky top-0 bg-white border-b border-gray-200 p-2">
            <input 
              type="text" 
              id="regionSearchFilter" 
              placeholder="Type to filter regions..." 
              class="w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          <div id="regionsList" class="max-h-60 overflow-y-auto p-1">
            <div class="p-2 hover:bg-gray-100 cursor-pointer text-gray-600" data-value="">All Regions</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



            <!-- Documents table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document No.</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAME</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">REGION</th>



                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="documentsTableBody">
                            <tr class="document-row transition-all duration-150 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">BEC26215/2025</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge status-created">Created</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mon, 26 May 2025 09:00:17 GMT</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state (hidden in this case) -->
                <div id="emptyState" class="empty-state hidden p-12 text-center rounded-lg">
                    <i class="fas fa-file-alt text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900">No pending documents</h3>
                </div>
            </div>

      
            <!-- Pagination -->
            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#" class="relative inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-blue-600 bg-blue-100 hover:bg-blue-200">
                            Previous
                        </a>
                        <a href="#" class="relative inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-blue-600 bg-blue-100 hover:bg-blue-200">
                            Next
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium">1</span> to <span class="font-medium" id="documentCount">1</span> of <span class="font-medium" id="totalCount">1</span> documents
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <!-- Page numbers will be injected here by JavaScript -->
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                    <span class="sr-only">Next</span>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
      <!-- Details Table -->
            <div id="detailsTable" class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg hidden">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Document Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
            
 
        </main>
    </div>

    <script>
let allDocuments = [];
let currentPage = 1;
const pageSize = 10;
let selectedRegion = '';
let selectedDocumentId = null; // Track selected document ID

// Update fetchDocuments to store all data and reset page
function fetchDocuments(region = '') {
    let url = 'http://192.168.1.94:5000/pending_documents';
    if (region) {
        url += '?sales_region=' + encodeURIComponent(region);
    }
    fetch(url)
        .then(response => response.json())
        .then(data => {
            allDocuments = data.documents || [];
            currentPage = 1;
            updateUIWithPagination();
        })
        .catch(() => {
            allDocuments = [];
            currentPage = 1;
            updateUIWithPagination();
        });
}

// Render only the current page
function updateUIWithPagination() {
    const data = {
        count: allDocuments.length,
        documents: allDocuments.slice((currentPage - 1) * pageSize, currentPage * pageSize),
        lastUpdated: new Date().toISOString()
    };
    updateUI(data);
    updatePagination();
}

// Update pagination controls
function updatePagination() {
    const totalCount = allDocuments.length;
    const totalPages = Math.ceil(totalCount / pageSize);
    document.getElementById('documentCount').textContent = Math.min(currentPage * pageSize, totalCount);
    document.getElementById('totalCount').textContent = totalCount;

    // Inject page numbers
    const nav = document.querySelector('nav[aria-label="Pagination"]');
    if (!nav) return;
    // Remove old page numbers
    nav.querySelectorAll('.page-number').forEach(el => el.remove());

    for (let i = 1; i <= totalPages; i++) {
        const a = document.createElement('a');
        a.href = '#';
        a.textContent = i;
        a.className = 'page-number relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50' + (i === currentPage ? ' font-bold bg-blue-100' : '');
        a.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage = i;
            updateUIWithPagination();
        });
        nav.insertBefore(a, nav.children[nav.children.length - 1]);
    }

    // Previous/Next
    nav.children[0].onclick = (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            updateUIWithPagination();
        }
    };
    nav.children[nav.children.length - 1].onclick = (e) => {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            updateUIWithPagination();
        }
    };
}

function updateUI(data) {
    // Update last updated time
    const updatedTime = new Date(data.lastUpdated || new Date());
    document.getElementById('lastUpdated').textContent = updatedTime.toLocaleTimeString();
    
    // Update counters
    document.getElementById('totalDocuments').textContent = data.count;
    document.querySelectorAll('.pending-count').forEach(el => {
        el.textContent = data.count;
    });
    
    // Get table body and empty state elements
    const tableBody = document.getElementById('documentsTableBody');
    const emptyState = document.getElementById('emptyState');
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Show empty state if no documents
    if (data.count === 0) {
        tableBody.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    // Add documents to table
    data.documents.forEach(doc => {
        const row = document.createElement('tr');
        row.className = 'document-row transition-all duration-150 ease-in-out cursor-pointer hover:bg-gray-50';
        row.setAttribute('data-inoutid', doc.inoutid);
        
        // Check if this row should be selected
        if (doc.inoutid === selectedDocumentId) {
            row.classList.add('bg-blue-50');
        }
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${doc.documentNo}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${doc.createdDate}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${doc.description}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${doc.businessPartner}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${doc.salesRegion}</td>
        `;

        // Add click event listener to the row
        row.addEventListener('click', function() {
            // Store selected document ID
            selectedDocumentId = doc.inoutid;
            
            // Update all rows' selected state
            document.querySelectorAll('.document-row').forEach(r => {
                r.classList.remove('bg-blue-50');
                const rowInoutId = r.getAttribute('data-inoutid');
                if (rowInoutId === selectedDocumentId) {
                    r.classList.add('bg-blue-50');
                }
            });
            
            // Add selected class to clicked row
            row.classList.add('bg-blue-50');
            
            // Update details title with document number
            document.querySelector('#detailsTable h3').textContent = `Document Details - ${doc.documentNo}`;
            
            // Fetch and show details
            fetch(`http://192.168.1.94:5000/inout_lines?m_inout_id=${doc.inoutid}`)
                .then(response => response.json())
                .then(data => {
                    const detailsTable = document.getElementById('detailsTable');
                    const detailsBody = document.getElementById('detailsTableBody');
                    
                    // Clear previous details
                    detailsBody.innerHTML = '';
                    
                    if (data.lines && data.lines.length > 0) {
                        // Add new rows
                        data.lines.forEach(line => {
                            const detailRow = document.createElement('tr');
                            detailRow.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${line.product_name}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${line.quantity}</td>
                            `;
                            detailsBody.appendChild(detailRow);
                        });
                        
                        // Show the details table
                        detailsTable.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                });
        });
        
        tableBody.appendChild(row);
    });
    
    // Show table
    tableBody.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    // Add animation
    tableBody.classList.remove('opacity-0');
    void tableBody.offsetWidth; // Trigger reflow
    tableBody.classList.add('opacity-0');
    setTimeout(() => {
        tableBody.classList.remove('opacity-0');
    }, 50);
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    let allRegions = []; // Store all regions for filtering
    const searchInput = document.getElementById('regionSearchInput');
    const dropdown = document.getElementById('regionDropdown');
    const filterInput = document.getElementById('regionSearchFilter');
    const regionsList = document.getElementById('regionsList');

    // Fetch regions first
    fetch('http://192.168.1.94:5000/regions')
        .then(response => response.json())
        .then(data => {
            allRegions = data.regions || [];
            populateRegionsList(allRegions);
        })
        .catch(error => console.error('Error fetching regions:', error));

    // Function to populate regions list
    function populateRegionsList(regions) {
        const regionsList = document.getElementById('regionsList');
        regionsList.innerHTML = '<div class="p-2 hover:bg-gray-100 cursor-pointer text-gray-600" data-value="">All Regions</div>';
        
        regions.forEach(region => {
            const div = document.createElement('div');
            div.className = 'p-2 hover:bg-gray-100 cursor-pointer text-gray-600';
            div.textContent = region.name;
            div.dataset.value = region.name;
            regionsList.appendChild(div);
        });
    }

    // Show/hide dropdown
    const regionFilterBtn = document.getElementById('regionFilterBtn');
    const selectedRegionText = document.getElementById('selectedRegionText');

    regionFilterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            filterInput.focus();
        }
    });

    // Handle clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== regionFilterBtn) {
            dropdown.classList.add('hidden');
        }
    });

    // Filter regions as user types
    filterInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filteredRegions = allRegions.filter(region => 
            region.name.toLowerCase().includes(searchTerm)
        );
        populateRegionsList(filteredRegions);
    });

    // Handle region selection
    regionsList.addEventListener('click', function(e) {
        const clickedItem = e.target.closest('div[data-value]');
        if (clickedItem) {
            selectedRegion = clickedItem.dataset.value;
            selectedRegionText.textContent = selectedRegion || 'All Regions';
            dropdown.classList.add('hidden');
            fetchDocuments(selectedRegion);
        }
    });

    // Initial documents fetch
    fetchDocuments();

    // Set up event listeners
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.classList.add('animate-spin');
        fetchDocuments(selectedRegion);
        setTimeout(() => this.classList.remove('animate-spin'), 1000);
    });

    document.getElementById('regionSelect').addEventListener('change', function() {
        selectedRegion = this.value;
        fetchDocuments(selectedRegion);
    });
});
    </script>






</body>
</html>