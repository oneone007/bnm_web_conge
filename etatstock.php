<?php
session_start();



// Check if the user is logged in and session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Call the function to check session timeout



// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
    header("Location: Acess_Denied");    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etat Stock</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="etatstock.css">
    <script src="theme.js" defer></script>
    <style>
        
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">



 
    

    

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4 pb-16">
        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Etat de Stock 
            </h1>
        </div>
        


<div class="search-container relative">
    <input type="text" id="recap_fournisseur" placeholder="Search Fournisseur">
    <div id="fournisseur-dropdown" class="dropdown"></div>
</div>
<br>
<div class="search-container relative">
    <input type="text" id="recap_product" placeholder="Search Product">
    <div id="product-dropdown" class="dropdown"></div>
</div>






        <br>
        <br>
        <!-- Data Table -->
        <div class="table-wrapper">
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
               
            </div>
        </div>

<!-- second table remise aauto  -->


        </div>

        <!-- Pagination -->

        <div class="tables-wrapper flex space-x-4">
    <!-- Magasins Dropdown -->
    <div class="dropdown-container flex-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
        <label for="magasinDropdown" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Magasin</label>
        <select id="magasinDropdown" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Loading magasins...</option>
        </select>
    </div>

    <!-- Emplacements Dropdown -->
    <div class="dropdown-container flex-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
        <label for="emplacementDropdown" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Emplacement</label>
        <select id="emplacementDropdown" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" disabled>
            <option value="">Select magasin first</option>
        </select>
    </div>
</div>


        <br>
            <button id="refreshButton" class="p-2 bg-gray-200 text-gray-900 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                🔄 Refresh
            </button>
            
            <div >
  <button class="Btn center-btn" id="stock_excel">
    <div class="svgWrapper">
      <img src="assets/excel.png" alt="Excel Icon" class="excelIcon" />
      <div class="text">&nbsp;Download</div>
      </div>
  </button>
</div>





<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">ETAT DE STOCK</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">Click on any row to view detailed product information</p>

        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
            <th data-column="FOURNISSEUR" onclick="sortTable('FOURNISSEUR')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Fournisseur
                <div class="resizer"></div>
            </th>
            <th data-column="NAME" onclick="sortTable('NAME')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                NAME
                <div class="resizer"></div>
            </th>
            <th data-column="QTY" onclick="sortTable('QTY')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                QTY
                <div class="resizer"></div>
            </th>
            <th data-column="PRIX" onclick="sortTable('PRIX')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                PRIX
                <div class="resizer"></div>
            </th>
            <th data-column="QTY_DISPO" onclick="sortTable('QTY_DISPO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                QTY_DISPO
                <div class="resizer"></div>
            </th>
            <th data-column="PRIX_DISPO" onclick="sortTable('PRIX_DISPO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                PRIX_DISPO
                <div class="resizer"></div>
            </th>
            <th data-column="PLACE" onclick="sortTable('PLACE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                PLACE
                <div class="resizer"></div>
            </th>
        </tr>
    </thead>
    <tbody id="data-table" class="dark:bg-gray-800">
        <!-- Dynamic Rows -->
    </tbody>
</table>

    </div>
</div>
<div id="pagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="pageIndicator"></span>
    <button id="nextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>


<!-- Product Details Table (Initially Hidden) -->
<div id="productDetailsContainer" class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mt-6 mb-16 pb-16" style="display: none;">
    <div class="overflow-x-auto">
        <div class="flex justify-between items-center mb-4 p-4">
            <h2 id="productDetailsTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Product Details</h2>
            <div class="flex gap-2">
                <button id="downloadProductDetailsExcel" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600" style="display: none;">
                    Download Excel
                </button>
                <button id="closeProductDetails" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600" onclick="closeProductDetails()">
                    Close
                </button>
            </div>
        </div>
        
        <style>
        /* Product Details Table Only */
        .product-details-table th {
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            padding: 4px 8px;
        }
        .product-details-table th[data-column="MARGE"],
        .product-details-table th[data-column="QTY"],
        .product-details-table th[data-column="QTY_DISPO"],
        .product-details-table th[data-column="P_ACHAT"],
        .product-details-table th[data-column="REM_ACHAT"],
        .product-details-table th[data-column="BON_ACHAT"],
        .product-details-table th[data-column="P_REVIENT"],
        .product-details-table th[data-column="P_VENTE"],
        .product-details-table th[data-column="REM_VENTE"],
        .product-details-table th[data-column="BON_VENTE"],
        .product-details-table th[data-column="REMISE_AUTO"],
        .product-details-table th[data-column="BONUS_AUTO"],
        .product-details-table th[data-column="PPA"] {
            width: 60px;
            white-space: nowrap;
        }
        .product-details-table th[data-column="LOCATION"],
        .product-details-table th[data-column="LOT"],
        .product-details-table th[data-column="GUARANTEEDATE"] {
            width: 80px;
        }
        .product-details-table th[data-column="FOURNISSEUR"],
        .product-details-table th[data-column="PRODUCT"] {
            width: 120px;
        }
        </style>
        <table class="product-details-table min-w-full border-collapse text-sm text-left dark:text-white">
            <thead>
                <tr class="table-header dark:bg-gray-700">
                    <th data-column="FOURNISSEUR" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Fournisseur
                        <div class="resizer"></div>
                    </th>
                    <th data-column="PRODUCT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Product
                        <div class="resizer"></div>
                    </th>
                    <th data-column="MARGE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Marge
                        <div class="resizer"></div>
                    </th>
                    <th data-column="QTY" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Qty
                        <div class="resizer"></div>
                    </th>
                    <th data-column="QTY_DISPO" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Qty_Dispo
                        <div class="resizer"></div>
                    </th>
                    <th data-column="P_ACHAT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        P_Achat
                        <div class="resizer"></div>
                    </th>
                    <th data-column="REM_ACHAT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Rem_Achat
                        <div class="resizer"></div>
                    </th>
                    <th data-column="BON_ACHAT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Bon_Achat
                        <div class="resizer"></div>
                    </th>
                    <th data-column="P_REVIENT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        P_Revient
                        <div class="resizer"></div>
                    </th>
                    <th data-column="P_VENTE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        P_Vente
                        <div class="resizer"></div>
                    </th>
                    <th data-column="REM_VENTE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Rem_Vente
                        <div class="resizer"></div>
                    </th>
                    <th data-column="BON_VENTE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Bon_Vente
                        <div class="resizer"></div>
                    </th>
                    <th data-column="REMISE_AUTO" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Remise_Auto
                        <div class="resizer"></div>
                    </th>
                    <th data-column="BONUS_AUTO" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Bonus_Auto
                        <div class="resizer"></div>
                    </th>
                    <th data-column="PPA" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        PPA
                        <div class="resizer"></div>
                    </th>
                    <th data-column="LOCATION" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Location
                        <div class="resizer"></div>
                    </th>
                    <th data-column="LOT" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Lot
                        <div class="resizer"></div>
                    </th>
                    <th data-column="GUARANTEEDATE" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                        Guarantee Date
                        <div class="resizer"></div>
                    </th>
                </tr>
            </thead>
            <tbody id="product-details-table" class="dark:bg-gray-800">
                <!-- Dynamic Product Details Rows -->
            </tbody>
        </table>
    </div>
</div>


<script>
    // Initialize resizer functionality for all resizable table headers
    function initializeResizers() {
        document.querySelectorAll("th.resizable").forEach(function (th) {
            const resizer = th.querySelector(".resizer");
            
            if (resizer && !resizer.hasListener) {
                resizer.hasListener = true; // Prevent duplicate listeners
                resizer.addEventListener("mousedown", function initResize(e) {
                    e.preventDefault();
                    window.addEventListener("mousemove", resizeColumn);
                    window.addEventListener("mouseup", stopResize);

                    function resizeColumn(e) {
                        const newWidth = e.clientX - th.getBoundingClientRect().left;
                        th.style.width = newWidth + "px";
                    }

                    function stopResize() {
                        window.removeEventListener("mousemove", resizeColumn);
                        window.removeEventListener("mouseup", stopResize);
                    }
                });
            }
        });
    }
    
    // Initialize resizers when page loads
    document.addEventListener("DOMContentLoaded", initializeResizers);
</script>




 <script>
  let allData = [];
let selectedMagasin = null;
let selectedEmplacement = null;



// Debounce function to limit API calls
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
    initializeDropdowns();
    fetchData(); // Initial fetch without any filters
    
    // Set up other event listeners
    document.getElementById("refreshButton").addEventListener("click", () => {
        console.log("Refreshing data...");
        const fournisseur = document.getElementById("recap_fournisseur").value.trim();
        fetchData(fournisseur, selectedMagasin, selectedEmplacement);
    });

    document.getElementById('stock_excel').addEventListener('click', exportToExcel);
    setupFournisseurSearch();
    setupProductSearch();
    setupThemeToggle();
    
    // Add close button event listener for product details
    const closeBtn = document.getElementById("closeProductDetails");
    if (closeBtn) {
        closeBtn.addEventListener("click", closeProductDetails);
    }
    
    // Add Excel download button event listener for product details
    const downloadBtn = document.getElementById("downloadProductDetailsExcel");
    if (downloadBtn) {
        downloadBtn.addEventListener("click", downloadProductDetailsExcel);
    }
});

// Initialize dropdown functionality
function initializeDropdowns() {
    // Load magasins dropdown
    loadMagasinsDropdown();
    
    // Set up dropdown event listeners
    document.getElementById("magasinDropdown").addEventListener("change", function() {
        selectedMagasin = this.value || null;
        updateEmplacementDropdown();
        fetchFilteredData();
    });
    
    document.getElementById("emplacementDropdown").addEventListener("change", function() {
        selectedEmplacement = this.value || null;
        fetchFilteredData();
    });
}

// Load magasins into dropdown
async function loadMagasinsDropdown() {
    const dropdown = document.getElementById("magasinDropdown");
    try {
        const response = await fetch("http://192.168.1.94:5000/fetch-magasins");
        if (!response.ok) throw new Error("Failed to load magasins");
        
        const data = await response.json();
        dropdown.innerHTML = '<option value="">Default Magasins</option>';
        
        data.forEach(magasin => {
            const option = document.createElement("option");
            option.value = magasin.MAGASIN;
            option.textContent = magasin.MAGASIN || "Unknown";
            dropdown.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading magasins:", error);
        dropdown.innerHTML = '<option value="">Error loading magasins</option>';
    }
}

// Update emplacement dropdown based on selected magasin
async function updateEmplacementDropdown() {
    const dropdown = document.getElementById("emplacementDropdown");
    
    if (!selectedMagasin) {
        dropdown.innerHTML = '<option value="">Select magasin first</option>';
        dropdown.disabled = true;
        return;
    }
    
    dropdown.innerHTML = '<option value="">Loading emplacements...</option>';
    dropdown.disabled = false;
    
    try {
        const url = new URL("http://192.168.1.94:5000/fetch-emplacements");
        url.searchParams.append("magasin", selectedMagasin);
        
        const response = await fetch(url);
        if (!response.ok) throw new Error("Failed to load emplacements");
        
        const data = await response.json();
        dropdown.innerHTML = '<option value="">All Emplacements</option>';
        
        data.forEach(emplacement => {
            const option = document.createElement("option");
            option.value = emplacement.EMPLACEMENT;
            option.textContent = emplacement.EMPLACEMENT || "Unknown";
            dropdown.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading emplacements:", error);
        dropdown.innerHTML = '<option value="">Error loading emplacements</option>';
    }
}

// Fetch data with current filters
async function fetchData(fournisseur = "", magasin = null, emplacement = null, name = null) {
    try {
        const url = new URL("http://192.168.1.94:5000/fetch-stock-data");
        if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
        if (magasin) url.searchParams.append("magasin", magasin);
        if (emplacement) url.searchParams.append("emplacement", emplacement);
        if (name) url.searchParams.append("name", name); // add product name as filter

        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');

        allData = await response.json();
        currentPage = 1; // Reset to first page
        renderTable();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}



let currentPage = 1;
const rowsPerPage = 10;

function renderTable() {
    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    let totalRow = allData.find(row => row.FOURNISSEUR?.toLowerCase() === "total");
    let filteredData = allData.filter(row => row.FOURNISSEUR?.toLowerCase() !== "total");

    const startIndex = (currentPage - 1) * rowsPerPage;
    const paginatedData = filteredData.slice(startIndex, startIndex + rowsPerPage);

    if (totalRow) {
        const tr = createTableRow(totalRow, true);
        tableBody.appendChild(tr);
    }

    paginatedData.forEach(row => {
        const tr = createTableRow(row);
        tableBody.appendChild(tr);
    });

    updatePagination(filteredData.length);
}

function updatePagination(totalItems) {
    const pageIndicator = document.getElementById("pageIndicator");
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const firstBtn = document.getElementById("firstPage");
    const lastBtn = document.getElementById("lastPage");

    const totalPages = Math.ceil(totalItems / rowsPerPage);
    pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
    firstBtn.disabled = currentPage === 1;
    lastBtn.disabled = currentPage === totalPages;

    prevBtn.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    };

    nextBtn.onclick = () => {
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    };

    firstBtn.onclick = () => {
        currentPage = 1;
        renderTable();
    };

    lastBtn.onclick = () => {
        currentPage = totalPages;
        renderTable();
    };
}



// Alias for fetchData to maintain compatibility

// Export to Excel function
function exportToExcel() {
    const fournisseur = document.getElementById('recap_fournisseur').value.trim() || null;
    const magasin = selectedMagasin;
    const emplacement = selectedEmplacement;

    let url = 'http://192.168.1.94:5000/download-stock-excel?';
    if (fournisseur) url += `fournisseur=${fournisseur}&`;
    if (magasin) url += `magasin=${magasin}&`;
    if (emplacement) url += `emplacement=${emplacement}&`;

    if (url.endsWith('&')) url = url.slice(0, -1);
    window.location.href = url;
}

// Fournisseur search functionality
function setupFournisseurSearch() {
    const fournisseurInput = document.getElementById("recap_fournisseur");
    const fournisseurDropdown = document.getElementById("fournisseur-dropdown");

    function clearSearch() {
        fournisseurInput.value = "";
        fournisseurDropdown.style.display = "none";
        fetchData("", selectedMagasin, selectedEmplacement);
    }

    fournisseurInput.addEventListener("input", debounce(function() {
        const searchValue = this.value.trim().toLowerCase();
        if (searchValue) {
            showFournisseurDropdown(searchValue);
        } else {
            clearSearch();
        }
    }, 300));

    fournisseurInput.addEventListener("click", clearSearch);
}

// Show fournisseur dropdown
function showFournisseurDropdown(searchValue) {
    const dropdown = document.getElementById("fournisseur-dropdown");
    dropdown.innerHTML = "";
    dropdown.style.display = "block";

    const uniqueFournisseurs = [...new Set(allData.map(row => row.FOURNISSEUR))]
        .filter(f => f && f.toLowerCase().includes(searchValue));

    if (uniqueFournisseurs.length === 0) {
        dropdown.style.display = "none";
        return;
    }

    uniqueFournisseurs.forEach(fournisseur => {
        const option = document.createElement("div");
        option.classList.add("dropdown-item");
        option.textContent = fournisseur;
        option.addEventListener("click", () => {
            document.getElementById("recap_fournisseur").value = fournisseur;
            dropdown.style.display = "none";
            fetchData(fournisseur, selectedMagasin, selectedEmplacement);
        });
        dropdown.appendChild(option);
    });
}


// Keep your existing renderTable and createTableRow functions exactly as they were


function createTableRow(row, isTotal = false) {
    const tr = document.createElement("tr");
    tr.classList.add('table-row', 'dark:bg-gray-700');

    if (isTotal) {
        tr.classList.add('font-bold', 'bg-gray-200', 'dark:bg-gray-800');
    } else {
        // Make non-total rows clickable
        tr.classList.add('cursor-pointer', 'hover:bg-gray-100', 'dark:hover:bg-gray-600');
        tr.addEventListener('click', () => {
            if (row.NAME && row.NAME.trim() !== '') {
                fetchProductDetails(row.NAME);
            }
        });
    }

    const formatNumber = (num) => num ? parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';

    tr.innerHTML = `
        <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_DISPO)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX_DISPO)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${row.PLACE || ''}</td>  <!-- New place column -->
    `;
    return tr;
}


function setupProductSearch() {
    const productInput = document.getElementById("recap_product");
    const productDropdown = document.getElementById("product-dropdown");

    function clearProductSearch() {
        productInput.value = "";
        productDropdown.style.display = "none";
        fetchFilteredData(); // Re-fetch data without product filter
    }

    productInput.addEventListener("input", debounce(function () {
        const searchValue = this.value.trim().toLowerCase();
        if (searchValue) {
            showProductDropdown(searchValue);
        } else {
            clearProductSearch();
        }
    }, 300));

    productInput.addEventListener("click", clearProductSearch);

    function showProductDropdown(searchValue) {
        productDropdown.innerHTML = "";
        productDropdown.style.display = "block";

        const uniqueProducts = [...new Set(allData.map(row => row.NAME))]
            .filter(p => p && p.toLowerCase().includes(searchValue));

        if (uniqueProducts.length === 0) {
            productDropdown.style.display = "none";
            return;
        }

        uniqueProducts.forEach(product => {
            const option = document.createElement("div");
            option.classList.add("dropdown-item");
            option.textContent = product;
            option.addEventListener("click", () => {
                productInput.value = product;
                productDropdown.style.display = "none";
                fetchFilteredData(); // ✅ Fetch with backend param
            });
            productDropdown.appendChild(option);
        });
    }
}

function showProductDropdown(searchValue) {
    const dropdown = document.getElementById("product-dropdown");
    dropdown.innerHTML = "";
    dropdown.style.display = "block";

    const uniqueProducts = [...new Set(allData.map(row => row.NAME))]
        .filter(p => p && p.toLowerCase().includes(searchValue));

    if (uniqueProducts.length === 0) {
        dropdown.style.display = "none";
        return;
    }

    uniqueProducts.forEach(product => {
        const option = document.createElement("div");
        option.classList.add("dropdown-item");
        option.textContent = product;
        option.addEventListener("click", () => {
            document.getElementById("recap_product").value = product;
            dropdown.style.display = "none";
            filterByProduct(product);
        });
        dropdown.appendChild(option);
    });
}


function fetchFilteredData() {
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    const name = document.getElementById("recap_product").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement, name || null);
}

function filterByProduct(product) {
    let filtered = allData.filter(row =>
        row.FOURNISSEUR?.toLowerCase() !== "total" &&
        row.NAME?.toLowerCase().includes(product.toLowerCase())
    );

    let totalRow = allData.find(row => row.FOURNISSEUR?.toLowerCase() === "total");

    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    const startIndex = (currentPage - 1) * rowsPerPage;
    const paginated = filtered.slice(startIndex, startIndex + rowsPerPage);

    if (totalRow) {
        const tr = createTableRow(totalRow, true);
        tableBody.appendChild(tr);
    }

    paginated.forEach(row => {
        const tr = createTableRow(row);
        tableBody.appendChild(tr);
    });

    updatePagination(filtered.length);
}

// Fetch and display product details
async function fetchProductDetails(productName) {
    try {
        const url = `http://192.168.1.94:5000/fetch-product-details?product_name=${encodeURIComponent(productName)}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.error) {
            console.error("API Error:", data.error);
            alert("Error fetching product details: " + data.error);
            return;
        }
        
        displayProductDetails(productName, data);
    } catch (error) {
        console.error("Error fetching product details:", error);
        alert("Failed to fetch product details. Please try again.");
    }
}

// Display product details in the table
function displayProductDetails(productName, data) {
    const container = document.getElementById("productDetailsContainer");
    const title = document.getElementById("productDetailsTitle");
    const tableBody = document.getElementById("product-details-table");
    const downloadButton = document.getElementById("downloadProductDetailsExcel");
    
    // Update title
    title.textContent = `Product Details - ${productName}`;
    
    // Clear previous data
    tableBody.innerHTML = "";
    
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="18" class="border px-4 py-2 text-center dark:border-gray-600">
                    No details found for this product
                </td>
            </tr>
        `;
        downloadButton.style.display = "none";
    } else {
        const formatNumber = (num, isInt = false) => {
            if (num === null || num === undefined || num === "") return 0;
            if (isInt) return parseInt(num, 10).toLocaleString('en-US');
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        const formatDate = (dateString) => {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR');
            } catch (e) {
                return dateString;
            }
        };
        
        data.forEach(row => {
            const tr = document.createElement("tr");
            tr.classList.add('table-row', 'dark:bg-gray-700', 'hover:bg-gray-100', 'dark:hover:bg-gray-600');
            
            tr.innerHTML = `
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.MARGE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY, true)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_DISPO, true)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.P_ACHAT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.REM_ACHAT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.BON_ACHAT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.P_REVIENT)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.P_VENTE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.REM_VENTE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.BON_VENTE)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.REMISE_AUTO || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.BONUS_AUTO || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PPA)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOCATION || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.LOT || ''}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.GUARANTEEDATE)}</td>
            `;
            
            tableBody.appendChild(tr);
        });
        
        downloadButton.style.display = "inline-flex"; // Show the button
        downloadButton.onclick = () => downloadProductDetailsExcel(data);
    }
    
    // Show the container
    container.style.display = "block";
    
    // Show the download button
    const downloadBtn = document.getElementById("downloadProductDetailsExcel");
    if (downloadBtn) {
        downloadBtn.style.display = "inline-block";
    }
    
    // Re-initialize resizers for the product details table
    setTimeout(() => {
        initializeResizers();
    }, 100);
    
    // Scroll to the product details table
    container.scrollIntoView({ behavior: 'smooth' });
}

// Close product details
function closeProductDetails() {
    console.log("Close button clicked");
    const container = document.getElementById("productDetailsContainer");
    if (container) {
        container.style.display = "none";
        console.log("Product details container hidden");
    } else {
        console.error("Product details container not found");
    }
}

// Download product details as Excel
function downloadProductDetailsExcel() {
    const productName = document.getElementById("productDetailsTitle").textContent.replace("Product Details - ", "");
    if (productName && productName !== "Product Details") {
        const url = `http://192.168.1.94:5000/download-product-details-excel?product_name=${encodeURIComponent(productName)}`;
        window.location.href = url;
    }
}

// Setup theme toggle functionality
function setupThemeToggle() {
    // Dark/Light Mode Toggle Functionality
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement;

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            // Save the theme preference in localStorage
            const isDarkMode = htmlElement.classList.contains('dark');
            localStorage.setItem('darkMode', isDarkMode);
        });
    }

    // Check for saved theme preference
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        htmlElement.classList.add('dark');
    } else {
        htmlElement.classList.remove('dark');
    }
}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply initial theme
            const isDark = localStorage.getItem('theme') === 'dark';
            if (isDark) {
                document.documentElement.classList.add('dark');
            }

            // Listen for theme changes
            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') {
                    if (e.newValue === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        });
    </script>
</body>
</html>