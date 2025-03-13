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
    <link rel="stylesheet" href="etatstock.css">

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
        <div id="lottieContainer" style="width: 200px; height: 150px; margin-top: 10px;"></div>
    
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

        // Insert sidebar content into the page
        container.innerHTML = tempDiv.innerHTML;

        // Reattach event listeners for submenu toggles (Products, Recaps)
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

        // Auto-hide sidebar when not hovered
        document.addEventListener('mousemove', (event) => {
            if (event.clientX < 50) {  // Mouse near the left edge (50px)
                sidebar.classList.remove('sidebar-hidden');
                content.classList.remove('content-full');
            }
        });

        // Hide sidebar when the mouse leaves it
        sidebar.addEventListener('mouseleave', () => {
            sidebar.classList.add('sidebar-hidden');
            content.classList.add('content-full');
        });

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