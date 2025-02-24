<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)
$inactive_time = 3600;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate session lifetime
    $session_lifetime = time() - $_SESSION['last_activity'];

    if ($session_lifetime > $inactive_time) {
        session_unset(); // Unset session variables
        session_destroy(); // Destroy the session
        header("Location: BNM?session_expired=1"); // Redirect to login page with message
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en" >
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM</title>
    <link rel="icon" href="tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
     
        .table-container {
    max-height: 400px;
    overflow-y: auto; /* Enables vertical scrolling if needed */
    overflow-x: auto; /* Enables horizontal scrolling if needed */
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    width: 100%;
    display: flex;
    flex-direction: column;
}

.table-container table {
    width: 100%;
    table-layout: auto; /* Allows columns to resize dynamically */
    min-width: 100%; /* Ensures the table doesn't shrink too much */
    max-width: 100%; /* Prevents overflow beyond the container */
    border-collapse: collapse;
}

thead {
    position: sticky;
    top: 0;
    background-color: #f3f4f6;
    z-index: 10;
}

th, td {
    white-space: nowrap; /* Prevents text wrapping */
    text-align: left;
    padding: 10px;
    border: 1px solid #ddd;
}
.table-container.placement-table {
    flex: 0.5; /* Makes this table take less space */
    width: 10px; /* Smaller width for the Emplacement table */
}
.dark .table-container {
    border-color: #374151;
}

.dark .table-header {
    background-color: #374151;
    color: #f9fafb; /* White text in dark mode */
}

.dark .table-row:nth-child(odd) {
    background-color: #1f2937;
    color: #f9fafb; /* White text on dark background */
}

.dark .table-row:nth-child(even) {
    background-color: #474d53;
    color: #ececec;
}




.paginatio-wrapper {
    display: flex;
    justify-content: center; 
    /* Ensures tables are spaced apart */
    gap: 250px; /* Adds spacing between tables */
}
.download-wrapper {
    display: flex;
    
    /* Ensures tables are spaced apart */
    gap: 550px; /* Adds spacing between tables */
}
.title-wrapper{
    display: flex;
    
    /* Ensures tables are spaced apart */
    gap: 730px; /* Adds spacing between tables */
}



.table-wrapper {
    display: flex;
    gap: 20px; /* Space between tables */
    align-items: flex-start; /* Align tables at the top */
    justify-content: space-between; /* Ensures tables are spaced apart */

}

.table-container {
    flex: 1; /* Default full size */
    min-width: 300px; /* Prevents tables from becoming too small */
}


        .sidebar {
            min-width: 200px;
            max-width: 250px;
            background-color: #f9fafb;
            border-right: 1px solid #e5e7eb;
            transition: transform 0.3s ease-in-out;
            position: fixed;
            height: 100vh;
            z-index: 40;
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }

        .content {
            margin-left: 250px; /* Adjust this value based on the sidebar width */
            transition: margin-left 0.3s ease-in-out;
            width: calc(100% - 250px); /* Adjust this value based on the sidebar width */
        }

        .content-full {
            margin-left: 0;
            width: 100%;
        }

        .table-header {
            background-color: #f3f4f6;
            text-align: left;
            color: #000; /* Default text color */
            position: sticky; top: 0;
        }

        .table-row {
            color: #000; /* Default black text */
        }

        .table-row:nth-child(odd) {
            background-color: #f9fafb;
        }

        /* Dark mode styles */
        .dark .sidebar {
            background-color: #1f2937;
            border-right-color: #374151;
        }

   

        .dark body {
            background-color: #111827;
            color: #010911;
        }

    
  
/* Dark Mode */
html.dark .sidebar {
    background-color: #1f2937;
    border-right-color: #374151;
}

html.dark body {
    background-color: #111827;
    color: white;
}

/* Dark Mode Toggle - Styled Checkbox */
.checkbox {
    display: none;
}

/* Toggle Background */
.checkbox-label {
    width: 60px;
    height: 30px;
    background: #ddd;
    display: flex;
    border-radius: 50px;
    align-items: center;
    position: relative;
    cursor: pointer;
    padding: 5px;
    transition: background 0.3s ease-in-out;
}

/* Ball as Sun (Default) */
.ball {
    width: 22px;
    height: 22px;
    background: #facc15; /* Sun color (yellow) */
    position: absolute;
    border-radius: 50%;
    transition: transform 0.3s ease-in-out, background 0.3s ease-in-out;
    left: 5px;
    box-shadow: 0 0 5px 2px #facc15; /* Sun glow effect */
}

/* Add Sun Rays */
.ball::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background: inherit;
    border-radius: 50%;
    transform: scale(1.4);
    opacity: 0.5;
}

/* Moon Shape */
html.dark .ball {
    transform: translateX(30px);
    background: #1e40af; /* Moon color (blue) */
    box-shadow: none; /* Remove glow */
}

/* Crescent Moon Effect */
html.dark .ball::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background: white;
    border-radius: 50%;
    left: 5px; /* Shift left to create crescent effect */
}

/* Dark Mode Background */
html.dark .checkbox-label {
    background: #333;
}

/* Positioning the Dark Mode Toggle on Top Right */
#themeSwitcher {
    position: fixed;
    top: 10px;
    right: 20px;
    z-index: 50;
}
/* Hide Default Checkbox */
.checkbox {
    display: none;
}

/* Toggle Background */
.checkbox-label {
    width: 60px;
    height: 30px;
    background: #f97316; /* Light Mode Orange */
    display: flex;
    align-items: center;
    border-radius: 50px;
    position: relative;
    cursor: pointer;
    padding: 5px;
    transition: background 0.3s ease-in-out;
}

/* Ball */
.ball {
    width: 24px;
    height: 24px;
    background: white;
    position: absolute;
    border-radius: 50%;
    transition: transform 0.3s ease-in-out;
    left: 5px;
}

/* Icons */
.icon {
    font-size: 16px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    transition: opacity 0.3s ease-in-out;
}

/* Sun (Left) */
.sun {
    left: 10px;
    color: white;
}

/* Moon (Right) */
.moon {
    right: 10px;
    color: white;
    opacity: 0; /* Hidden in Light Mode */
}

/* Dark Mode */
html.dark .checkbox-label {
    background: #1f2937; /* Dark Mode Gray */
}

html.dark .ball {
    transform: translateX(30px);
}

html.dark .sun {
    opacity: 0; /* Hide Sun */
}

html.dark .moon {
    opacity: 1; /* Show Moon */
}

/* Theme Switcher Position */
#themeSwitcher {
    position: sticky;
    top: 10px;
    right: 10px;
    padding: 10px;
    z-index: 50;
}

.search-container {
      display: grid;
      grid-template-columns: repeat(1, minmax(250px, 1fr)); /* 3 columns per row */
      gap: 16px;
      padding: 20px;
      width: 50%;

      background: #f9fafb;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .search-container label {
      display: block;
      font-weight: 600;
      color: #374151;
      margin-bottom: 6px;
  }

  .search-container input {
      width: 100%;
      padding: 12px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease-in-out;
      background-color: white;
      color: #111827;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .search-container input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
  }

  /* Dark Mode */
  .dark .search-container {
      background: #1f2937;
      box-shadow: none;
  }

  .dark .search-container label {
      color: #e5e7eb;
  }

  .dark .search-container input {
      background-color: #374151;
      color: white;
      border: 1px solid #4b5563;
      box-shadow: none;
  }

  .dark .search-container input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
      .search-container {
          grid-template-columns: repeat(2, minmax(250px, 1fr)); /* 2 per row on tablets */
      }
  }

  @media (max-width: 768px) {
      .search-container {
          grid-template-columns: 1fr; /* 1 per row on mobile */
      }
  }


    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->


    <!-- Dark/Light Mode Toggle Button -->
    <div id="themeSwitcher">
        <input type="checkbox" class="checkbox" id="themeToggle">
        <label for="themeToggle" class="checkbox-label">
            <span class="icon sun">☀️</span>
            <span class="icon moon">🌙</span>
        </label>
        <div id="lottieContainer" style="width: 250px; height: 200px; margin-top: 10px;"></div>
    
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script>
        lottie.loadAnimation({
            container: document.getElementById("lottieContainer"),
            renderer: "svg",
            loop: true,
            autoplay: true,
            path: "r.json" // Replace with actual path to your .rjson file
        });
    </script>
    
    

<!-- Sidebar -->
<!-- Sidebar -->
<div id="sidebar-container"></div>

<script>
    // Fetch sidebar content dynamically
    fetch("side")
        .then(response => response.text())
        .then(html => {
            let container = document.getElementById("sidebar-container");
            let tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;

            // Insert the sidebar content into the page
            container.innerHTML = tempDiv.innerHTML;

            // Reattach event listeners for the submenu toggles (Products, Recaps)
            const productsToggle = document.getElementById("products-toggle");
            if (productsToggle) {
                productsToggle.addEventListener("click", function () {
                    let submenu = document.getElementById("products-submenu");
                    submenu.classList.toggle("hidden");
                });
            }

            const recapsToggle = document.getElementById("recaps-toggle");
            if (recapsToggle) {
                recapsToggle.addEventListener("click", function () {
                    let submenu = document.getElementById("recaps-submenu");
                    submenu.classList.toggle("hidden");
                });
            }

            // Initialize Lottie animation after sidebar is inserted
            const ramAnimation = document.getElementById('ram-animation');
            if (ramAnimation) {
                lottie.loadAnimation({
                    container: ramAnimation,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: 'ram.json',
                    rendererSettings: {
                        clearCanvas: true,
                        preserveAspectRatio: 'xMidYMid meet',
                        progressiveLoad: true,
                        hideOnTransparent: true
                    }
                });
            }

            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content');

            // Ensure sidebarToggle is initialized after sidebar is loaded
            if (sidebarToggle && sidebar && content) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('sidebar-hidden');
                    content.classList.toggle('content-full');

                    // Adjust button position when sidebar is hidden or shown
                    if (sidebar.classList.contains('sidebar-hidden')) {
                        sidebarToggle.style.left = '10px';  // Sidebar hidden
                    } else {
                        sidebarToggle.style.left = '260px'; // Sidebar visible
                    }
                });
            } else {
                console.error("Sidebar or Toggle Button not found!");
            }

        })
        .catch(error => console.error("Error loading sidebar:", error));
</script>

    
    

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center text-blue-600 ">
                 Etat de Stock 
            </h1>
        </div>
        
        <!-- Filters -->

        <br>
 <!-- <div class="placement-dropdown bg-white shadow-md dark:bg-gray-800 rounded-lg p-4 w-80">
    <label for="emplacement-dropdown" class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
        Select Emplacement:
    </label>
    <select id="emplacement-dropdown" class="w-full border border-gray-300 px-4 py-2 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        <option value="">Loading...</option> 
    </select>
</div> 
-->

<div class="search-container">
    <div>
        <label for="recap_fournisseur"> Fournisseur:</label>
        <input type="text" id="recap_fournisseur" placeholder="Search...">
    </div>
<div>
    <label class="text-blue-600 font-semibold mb-2 block" for="locatorDropdown">Select Location:</label>
<select id="locatorDropdown" class="w-60 border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
    <option value="">-- Select Location --</option>
    <option value="1000000">Z-ETAGE</option>
    <option value="1001130">3-etage</option>
    <option value="1000614">Préparation</option>
    <option value="1000514">Depot Cos</option>
    <option value="1001020">Dépôt Vente</option>
    <option value="1000212">Dépôt Compensation</option>
    <option value="1001127">Standard</option>
    <option value="1001128">Dépot réserve</option>
    <option value="1001136">Dépot Hangar réserve</option>
    <option value="1001132">Dépôt Compensation Périmé</option>
    <option value="1001135">HANGAR</option>
    <option value="1001129">ARCHIVE PERIME</option>
    <option value="1000817">PRODUIT NON VENDABLE</option>
    <option value="1000414">Revignettage Client</option>
    <option value="1000214">RETOUR FOURNISSEUR</option>
    <option value="1000213">VIDE</option>
    <option value="1000209">SV</option>
    <option value="1000109">CASSE</option>
    <option value="1000211">MANQUE</option>
    <option value="1000314">MOYEN GENERAUX</option>
    <option value="1000210">PERIME</option>
</select>
</div>

</div>

        <br><br>
     
        <button id="stock" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Download Stock Table</span>
        </button>

        <br>
        <br>
        <!-- Data Table -->
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">MARGE</h2>
        <div class="table-wrapper">
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th data-column="FOURNISSEUR" onclick="sortTable('FOURNISSEUR')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Fournisseur</th>
                            <th data-column="NAME" onclick="sortTable('NAME')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">NAME</th>
                            <th data-column="QTY" onclick="sortTable('QTY')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY</th>
                            <th data-column="PRIX" onclick="sortTable('PRIX')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PRIX</th>
                            <th data-column="QTY_DISPO" onclick="sortTable('QTY_DISPO')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY_DISPO</th>
                            <th data-column="PRIX_DISPO" onclick="sortTable('PRIX_DISPO')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PRIX_DISPO</th>
                            <th data-column="LOCATORID" onclick="sortTable('LOCATORID')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">LOCATORID</th>
                            <th data-column="PRODUCTID" onclick="sortTable('PRODUCTID')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PRODUCTID</th>
                            <th data-column="SORT_ORDER" onclick="sortTable('SORT_ORDER')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">SORT_ORDER</th>
                    </thead>
                    <tbody id="data-table" class="dark:bg-gray-800">
                        <!-- Dynamic Rows -->
                    </tbody>
                </table>
            </div>
        </div>

<!-- second table remise aauto  -->


        </div>
        <div class="mt-4 flex justify-center space-x-2" id="pagination"></div>

        <!-- Pagination -->

        
        <br>
    
        <br>
     
  
        <script >
window.onload = () => {
    document.getElementById("recap_fournisseur").value = ""; // Clear search input
    document.getElementById("locatorDropdown").value = ""; // Reset select dropdown to empty value
};



let emplacementPage = 1;
const emplacementRowsPerPage = 10;
let emplacementData = [];
let emplacementSortColumn = '';
let emplacementSortDirection = 'asc';

// Fetch Emplacement data on page load
window.onload = () => {
    fetchEmplacementData();
    fetchData();

};

document.addEventListener("DOMContentLoaded", () => {
    fetchEmplacementData();
});

async function fetchEmplacementData() {
    try {
        const response = await fetch('http://192.168.1.156:5000/fetch-emplacement-data');
        if (!response.ok) throw new Error('Network response was not ok');

        const emplacementData = await response.json();
        updateEmplacementDropdown(emplacementData);
    } catch (error) {
        console.error("Error fetching emplacement data:", error);
        document.getElementById("emplacement-dropdown").innerHTML = `<option value="">Failed to load</option>`;
    }
}

function updateEmplacementDropdown(data) {
    const dropdown = document.getElementById("emplacement-dropdown");
    dropdown.innerHTML = ""; // Clear existing options

    if (!data || data.length === 0) {
        dropdown.innerHTML = `<option value="">No data available</option>`;
        return;
    }

    data.forEach(item => {
        const option = document.createElement("option");
        option.value = item.EMPLACEMENT || "";
        option.textContent = item.EMPLACEMENT || "Unknown";
        dropdown.appendChild(option);
    });
}


let currentPage = 1;
const rowsPerPage = 10;
let allData = [];
let filters = {
    fournisseur: '',
    name: '',
    qty: '',
    prix: '',
    qty_dispo: '',
    prix_dispo: '',
    locatorid: '',
    PRODUCTID: '',
    sort_order: ''
};
let sortColumn = '';
let sortDirection = 'asc';
document.getElementById("recap_fournisseur").addEventListener("input", function () {
    filters.fournisseur = this.value.toLowerCase(); // Store search input in filters
    currentPage = 1;
    updateTableAndPagination();
});

// Fetch data on page load
async function fetchData() {
    try {
        const response = await fetch('http://192.168.1.156:5000/fetch-data-stock');
        if (!response.ok) throw new Error('Network response was not ok');
        allData = await response.json();
        console.log("Fetched Data:", allData); // Debugging log
        updateTableAndPagination();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

document.getElementById("stock").addEventListener("click", function () {
    let params = new URLSearchParams();

    // Get selected location
    let locatorDropdown = document.getElementById("locatorDropdown");
    let selectedLocatorID = locatorDropdown.value;
    let selectedLocatorName = locatorDropdown.options[locatorDropdown.selectedIndex].text;

    if (selectedLocatorID) {
        params.append("locatorid", selectedLocatorID);
        params.append("locatorname", selectedLocatorName);
    }

    // Get fournisseur search input
    let searchFournisseur = document.getElementById("recap_fournisseur").value.trim();
    if (searchFournisseur) {
        params.append("fournisseur", searchFournisseur);
    }

    // Open the filtered download link
    window.open(`http://192.168.1.156:5000/download-stock-excel?${params.toString()}`, "_blank");
});



function filterDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filters[type] = searchValue;
    currentPage = 1;
    updateTableAndPagination();
}

function filterData(data) {
    const selectedLocatorID = Number(filters.locatorid); // Convert once for efficiency
    const searchQuery = filters.fournisseur; // Get the search query

    return data.filter(row => {
        let matchesLocator = !filters.locatorid || Number(row.LOCATORID) === selectedLocatorID;
        let matchesSearch = !searchQuery || row.FOURNISSEUR.toLowerCase().includes(searchQuery);

        return matchesLocator && matchesSearch;
    });
}




function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });
    
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirection === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }
    
    updateTableAndPagination();
}
document.getElementById("locatorDropdown").addEventListener("change", function () {
    filters.locatorid = this.value; // Store the selected value in filters
    currentPage = 1;
    updateTableAndPagination(); // Refresh the table with the new filter
});

function updateTableAndPagination() {
    renderTablePage(currentPage);
    renderPagination();
}

function renderTablePage(page) {
    let filteredData = filterData(allData);

    if (sortColumn) {
        filteredData.sort((a, b) => {
            if (a[sortColumn] < b[sortColumn]) return sortDirection === 'asc' ? -1 : 1;
            if (a[sortColumn] > b[sortColumn]) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');

        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRIX || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY_DISPO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRIX_DISPO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LOCATORID || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCTID || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.SORT_ORDER || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function createPageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterData(allData).length / rowsPerPage);
    button.addEventListener("click", () => {
        currentPage = pageNumber;
        updateTableAndPagination();
    });
    return button;
}

function renderPagination() {
    const filteredData = filterData(allData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    const paginationContainer = document.getElementById("pagination");
    paginationContainer.innerHTML = "";

    paginationContainer.appendChild(createPageButton("First", 1));
    paginationContainer.appendChild(createPageButton("<", currentPage - 1));

    const pageButton = document.createElement("button");
    pageButton.innerText = currentPage;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    paginationContainer.appendChild(createPageButton(">", currentPage + 1));
    paginationContainer.appendChild(createPageButton("Last", totalPages));
}

document.getElementById('themeToggle').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
});

if (localStorage.getItem('darkMode') === 'true') {
    document.documentElement.classList.add('dark');
}

  

        </script>




<br><br><br> <br>


</body>
</html>