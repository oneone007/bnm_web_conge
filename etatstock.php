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
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
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

.tables-wrapper {
    display: flex;
    gap: 20px;
    align-items: flex-start; /* Align tables at the top */
    justify-content: flex-start; /* Arrange tables in a row */
    flex-wrap: wrap; /* Prevent overflow */
}

/* Small Tables (Magasins & Emplacements) */
.small {
    width: 15%; /* Small width for Magasins & Emplacements */
    min-width: 180px; /* Prevent too small */
}

/* Large Table (Etat de Stock) */
.large {
    width: 65%; /* Takes the remaining space */
}

/* Table Box Styling */
.table-box {
    background: white;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .tables-wrapper {
        flex-direction: column;
    }
    .small, .large {
        width: 100%;
    }
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
.scrollable-table {
    max-height: 250px; /* Adjust height based on your design */
    overflow-y: auto;
    display: block;
}
.selected {
    font-weight: bold; /* Make text bold */
    background-color: #e0f7fa; /* Light blue background */
    border-left: 4px solid #0097a7; /* Add a left border */
}
.scrollable-table table {
    width: 100%;
    border-collapse: collapse;
}

.scrollable-table th,
.scrollable-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}


  .table-box.small {
    width: 250px; /* Adjust as needed */
    max-height: 350px; /* Ensure space for 10 rows */
    overflow-y: auto;
    border: 1px solid #ccc;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tbody tr {
    height: 35px; /* Adjust row height to fit 10 rows */
}


/* Search Container */
.search-container {
    position: relative; /* Ensure dropdown is positioned correctly */
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    max-width: 400px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Search Input */
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

/* Dropdown Container */
.dropdown {
    position: absolute;
    top: 100%; /* Place right below the input */
    left: 0;
    width: 100%;
    max-height: 250px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1050;
    display: none;
    font-size: 14px;
    padding: 5px 0;
}

/* Dropdown Items */
.dropdown-item {
    padding: 10px 15px;
    cursor: pointer;
    transition: background 0.2s ease-in-out;
    color: #333;
    font-weight: 500;
}

/* Hover Effect */
.dropdown-item:hover {
    background: #f0f0f0;
}

/* Dark Mode */
.dark .search-container {
    background: #1f2937;
}

.dark .search-container input {
    background-color: #374151;
    color: white;
    border: 1px solid #4b5563;
}

.dark .search-container input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-container {
        width: 100%;
        max-width: none;
    }
}
/* Wrapper Styling */
.tables-wrapper {
    display: flex;
    gap: 16px; /* Spacing between tables */
    flex-wrap: wrap; /* Wrap to the next line on smaller screens */
    justify-content: flex-start;
}

/* Small Table Box */
.table-box {
    background: #ffffff;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    min-width: 250px; /* Ensures proper size */
}

/* Table Styling */
.table-box table {
    width: 100%;
    border-collapse: collapse;
}

/* Table Head */
.table-box thead tr {
    background-color: #f1f5f9; /* Light gray background */
    color: #374151; /* Dark text */
    font-weight: bold;
}

/* Table Head & Cells */
.table-box th,
.table-box td {
    padding: 8px;
    border: 1px solid #ccc;
    text-align: left;
}

/* Alternating Rows */
.table-box tbody tr:nth-child(odd) {
    background-color: #f9fafb;
}

.table-box tbody tr:nth-child(even) {
    background-color: #e5e7eb;
}

/* ====== DARK MODE ====== */
.dark .table-box {
    background: #1f2937;
    border-color: #374151;
    color: #f9fafb;
}

.dark .table-box thead tr {
    background-color: #374151;
    color: #f9fafb;
}

.dark .table-box tbody tr:nth-child(odd) {
    background-color: #1f2937;
}

.dark .table-box tbody tr:nth-child(even) {
    background-color: #474d53;
}

.dark .table-box th,
.dark .table-box td {
    border-color: #4b5563;
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
            path: "json_files/r.json" // Replace with actual path to your .rjson file
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
                    path: 'json_files/ram.json',
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

<div class="search-container relative">
    <input type="text" id="recap_fournisseur" placeholder="Search Fournisseur">
    <div id="fournisseur-dropdown" class="dropdown"></div>
</div>





        <br><br>
     
        <button id="stock_excel" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Download Stock Table</span>
        </button>

        <br>
        <br>
        <!-- Data Table -->
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">MARGE</h2>
        <div class="table-wrapper">
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
               
            </div>
        </div>

<!-- second table remise aauto  -->


        </div>

        <!-- Pagination -->

        <div class="tables-wrapper">
    <!-- Magasins Table -->
    <div class="table-box small">
        <table>
            <thead>
                <tr><th>Magasin</th></tr>
            </thead>
            <tbody id="magasin-table-body">
                <tr><td>Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Emplacements Table -->
    <div class="table-box small">
        <table>
            <thead>
                <tr><th>Emplacement</th></tr>
            </thead>
            <tbody id="emplacement-table-body">
                <tr><td>Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Large Table (Etat de Stock) -->

</div>


        <br>
            <button id="refreshButton" class="p-2 bg-gray-200 text-gray-900 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                🔄 Refresh
            </button>
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> ETAT DE STOCK</h2>
                

                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th data-column="FOURNISSEUR" onclick="sortTable('FOURNISSEUR')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">Fournisseur</th>
                            <th data-column="NAME" onclick="sortTable('NAME')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">NAME</th>
                            <th data-column="QTY" onclick="sortTable('QTY')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY</th>
                            <th data-column="PRIX" onclick="sortTable('PRIX')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PRIX</th>
                            <th data-column="QTY_DISPO" onclick="sortTable('QTY_DISPO')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY_DISPO</th>
                            <th data-column="PRIX_DISPO" onclick="sortTable('PRIX_DISPO')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PRIX_DISPO</th>

                    </thead>
                    <tbody id="data-table" class="dark:bg-gray-800">
                        <!-- Dynamic Rows -->
                    </tbody>
                </table>
            </div>
        </div>

        <script >
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



// Add event listener for the Refresh button
document.getElementById("refreshButton").addEventListener("click", () => {
    console.log("Refreshing data...");
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement);
});



document.getElementById('stock_excel').addEventListener('click', function() {
    // Get parameters (if available)
    const fournisseur = document.getElementById('recap_fournisseur').value.trim() || null;
    const magasin = selectedMagasin; // Use the selectedMagasin variable
    const emplacement = selectedEmplacement; // Use the selectedEmplacement variable

    // Build the URL with parameters
    let url = 'http://192.168.1.156:5000/download-stock-excel?';
    if (fournisseur) url += `fournisseur=${fournisseur}&`;
    if (magasin) url += `magasin=${magasin}&`;
    if (emplacement) url += `emplacement=${emplacement}&`;

    // Remove the trailing '&' if no parameters are provided
    if (url.endsWith('&')) url = url.slice(0, -1);

    // Trigger the download
    window.location.href = url;
});



// Fetch data on page load
document.addEventListener("DOMContentLoaded", () => {
    fetchData(); // Initial fetch without any filters
    fetchAndDisplayMagasins(); // Fetch magasins
});

const fournisseurInput = document.getElementById("recap_fournisseur");
const fournisseurDropdown = document.getElementById("fournisseur-dropdown");

function clearSearch() {
    fournisseurInput.value = ""; // Clear input field
    fournisseurDropdown.style.display = "none"; // Hide dropdown
    fetchData("", selectedMagasin, selectedEmplacement); // Refresh data
}

// Search input event listener with debounce
fournisseurInput.addEventListener("input", debounce(function () {
    const searchValue = this.value.trim().toLowerCase();

    if (searchValue) {
        showFournisseurDropdown(searchValue);
    } else {
        clearSearch();
    }
}, 300));

// Add event listener for click to clear search and refresh
fournisseurInput.addEventListener("click", clearSearch);


// Fetch data with optional filters
async function fetchData(fournisseur = "", magasin = null, emplacement = null) {
    try {
        const url = new URL("http://192.168.1.156:5000/fetch-stock-data");
        if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
        if (magasin) url.searchParams.append("magasin", magasin);
        if (emplacement) url.searchParams.append("emplacement", emplacement);

        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');

        allData = await response.json();
        console.log("Fetched Data:", allData); // Debugging
        renderTable();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

// Show fournisseur dropdown
function showFournisseurDropdown(searchValue) {
    fournisseurDropdown.innerHTML = "";
    fournisseurDropdown.style.display = "block";

    // Get unique fournisseurs matching search
    const uniqueFournisseurs = [...new Set(allData.map(row => row.FOURNISSEUR))]
        .filter(f => f && f.toLowerCase().includes(searchValue));

    if (uniqueFournisseurs.length === 0) {
        fournisseurDropdown.style.display = "none";
        return;
    }

    // Populate dropdown
    uniqueFournisseurs.forEach(fournisseur => {
        const option = document.createElement("div");
        option.classList.add("dropdown-item");
        option.textContent = fournisseur;
        option.addEventListener("click", () => {
            fournisseurInput.value = fournisseur;
            fournisseurDropdown.style.display = "none";
            fetchData(fournisseur, selectedMagasin, selectedEmplacement);
        });
        fournisseurDropdown.appendChild(option);
    });
}

// Render table function
function renderTable() {
    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    let totalRow = allData.find(row => row.FOURNISSEUR?.toLowerCase() === "total");
    let filteredData = allData.filter(row => row.FOURNISSEUR?.toLowerCase() !== "total");

    if (totalRow) {
        const tr = createTableRow(totalRow, true);
        tableBody.appendChild(tr);
    }

    filteredData.forEach(row => {
        const tr = createTableRow(row);
        tableBody.appendChild(tr);
    });
}

// Helper function to create a table row
function createTableRow(row, isTotal = false) {
    const tr = document.createElement("tr");
    tr.classList.add('table-row', 'dark:bg-gray-700');

    if (isTotal) {
        tr.classList.add('font-bold', 'bg-gray-200', 'dark:bg-gray-800'); // Highlight total row
    }

    const formatNumber = (num) => num ? parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';

    tr.innerHTML = `
        <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_DISPO)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX_DISPO)}</td>

    `;
    return tr;
}







// Fetch and display magasins
async function fetchAndDisplayMagasins() {
    try {
        const response = await fetch("http://192.168.1.156:5000/fetch-magasins");
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Magasins Data:", data);
        updateMagasinTable(data);
    } catch (error) {
        console.error("Error fetching magasins:", error);
    }
}

// Update magasin table
function updateMagasinTable(data) {
    const tableBody = document.getElementById("magasin-table-body");
    tableBody.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="1">No data available</td></tr>`;
        return;
    }

    // Add cancel selection row
    const cancelRow = document.createElement("tr");
    cancelRow.innerHTML = `<td class="cancel-selection">❌ Cancel Selection</td>`;
    cancelRow.addEventListener("click", () => resetSelection("magasin"));
    tableBody.appendChild(cancelRow);

    data.forEach(item => {
        const row = document.createElement("tr");
        row.innerHTML = `<td>${item.MAGASIN || "Unknown"}</td>`;
        row.dataset.magasin = item.MAGASIN;
        row.addEventListener("click", () => selectMagasin(row));
        tableBody.appendChild(row);
    });
}

// Magasin selection logic
function selectMagasin(selectedRow) {
    // Remove 'selected' class from all magasin rows
    document.querySelectorAll("#magasin-table-body tr").forEach(row => {
        row.classList.remove("selected");
    });

    // Add 'selected' class to the clicked row
    selectedRow.classList.add("selected");

    selectedMagasin = selectedRow.dataset.magasin;
    console.log("Selected Magasin:", selectedMagasin);

    // Fetch and display emplacements for the selected magasin
    fetchAndDisplayEmplacements(selectedMagasin);

    // Fetch stock data with the selected magasin
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement);
}

// Fetch and display emplacements
async function fetchAndDisplayEmplacements(magasin) {
    if (!magasin) {
        document.getElementById("emplacement-table-body").innerHTML = "";
        return;
    }

    try {
        const url = new URL("http://192.168.1.156:5000/fetch-emplacements");
        url.searchParams.append("magasin", magasin);

        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Emplacements Data:", data);
        updateEmplacementTable(data);
    } catch (error) {
        console.error("Error fetching emplacements:", error);
    }
}

// Update emplacement table
function updateEmplacementTable(data) {
    const tableBody = document.getElementById("emplacement-table-body");
    tableBody.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="1">No data available</td></tr>`;
        return;
    }

    // Add cancel selection row
    const cancelRow = document.createElement("tr");
    cancelRow.innerHTML = `<td class="cancel-selection">❌ Cancel Selection</td>`;
    cancelRow.addEventListener("click", () => resetSelection("emplacement"));
    tableBody.appendChild(cancelRow);

    data.forEach(item => {
        const row = document.createElement("tr");
        row.innerHTML = `<td>${item.EMPLACEMENT || "Unknown"}</td>`;
        row.dataset.emplacement = item.EMPLACEMENT;
        row.addEventListener("click", () => selectEmplacement(row));
        tableBody.appendChild(row);
    });
}

// Emplacement selection logic
function selectEmplacement(selectedRow) {
    // Remove 'selected' class from all emplacement rows
    document.querySelectorAll("#emplacement-table-body tr").forEach(row => {
        row.classList.remove("selected");
    });

    // Add 'selected' class to the clicked row
    selectedRow.classList.add("selected");

    selectedEmplacement = selectedRow.dataset.emplacement;
    console.log("Selected Emplacement:", selectedEmplacement);

    // Fetch stock data with the selected emplacement
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement);
}

// Reset selection logic
function resetSelection(type) {
    console.log(`Resetting ${type} selection...`);

    if (type === "magasin") {
        document.querySelectorAll("#magasin-table-body tr").forEach(row => row.classList.remove("selected"));
        selectedMagasin = null;
        // Clear emplacement table when magasin selection is reset
        document.getElementById("emplacement-table-body").innerHTML = "";
        selectedEmplacement = null;
    } else if (type === "emplacement") {
        document.querySelectorAll("#emplacement-table-body tr").forEach(row => row.classList.remove("selected"));
        selectedEmplacement = null;
    }

    // Fetch stock data without the reset filter
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement);
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