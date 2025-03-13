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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="journal.css">


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 <!-- Include SweetAlert2 Library (Add this to your HTML head if not already included) -->



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


        <!-- Filters -->
   
        

        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->


        <br>
        <!-- Date Inputs -->
        <div class="date-container flex space-x-4 items-center">
    <div class="flex items-center space-x-2">
        <label for="start-date">Begin Date:</label>
        <input type="date" id="start-date" class="border rounded px-2 py-1">
    </div>

    <div class="flex items-center space-x-2">
        <label for="end-date">End Date:</label>
        <input type="date" id="end-date" class="border rounded px-2 py-1">
    </div>

    <!-- Refresh Button with Icon -->
    <button id="refresh-btn" class="p-3 bg-white text-blue-500 rounded-full shadow-lg hover:shadow-xl border border-blue-500 transition duration-200 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
        </svg>
    </button>
</div>

<br>

<div class="search-container">
            <div>
                <label for="etat_fournisseur">Search :</label>
                <input type="text" id="etat_fournisseur" placeholder="Search  ...">
            </div>
   
           
        
        </div>
        <br>

        <!-- <button id="downloadExcel_journal"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Journal de vente Download</span>
        </button> -->

<!-- From Uiverse.io by Rodrypaladin --> 
<button id="downloadExcel_journal"
 class="button">
  <span class="button__span">Journal de vente Download</span>
  
</button>
<STYle>
   .button {
  font-size: 1.4em; /* Slightly smaller text */
  padding: 8px 16px;
  background-color: transparent;
  transition: all 0.2s ease-out;
  border: none;
}

.button__span {
  color: #131313;
  transition: all 0.2s ease-out;
  letter-spacing: 0.1em;
  text-shadow: 1px -1px 0 #767676, 
      -1px 2px 1px #737272, 
      -2px 3px 1px #767474, 
      -3px 4px 1px #787777, 
      -4px 5px 1px #7b7a7a, 
      -5px 6px 1px #7f7d7d, 
      -6px 7px 1px #828181, 
      -7px 8px 1px #868585, 
      -8px 9px 1px #8b8a89, 
      -9px 10px 1px #8f8e8d;
}

.button__span:hover {
  text-shadow: -1px -1px 0 #767676, 
      1px 2px 1px #737272, 
      2px 3px 1px #767474, 
      3px 4px 1px #787777, 
      4px 5px 1px #7b7a7a, 
      5px 6px 1px #7f7d7d, 
      6px 7px 1px #828181, 
      7px 8px 1px #868585, 
      8px 9px 1px #8b8a89, 
      9px 10px 1px #8f8e8d;
}

.button:active .button__span {
  text-shadow: none;
}

</STYle>
        <br>
        <div class="w-1/4">
        <!-- First Table: Smaller -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-4">
            <div class="flex justify-between items-center p-3">
                <h2 class="text-base font-semibold dark:text-black">TOTAL FOURNISSUER</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th class="border px-3 py-2">TOTAL ECHU</th>
                            <th class="border px-3 py-2">TOTAL DETTE</th>
                            <th class="border px-3 py-2">TOTAL STOCK</th>
                        </tr>
                    </thead>
                    <tbody id="dette-table" class="dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
        <br>

        <!-- Second Table: Taller -->
</div>
    <br>
        
        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">ETAT FOURNISSUER</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
        <th data-column="FOURNISSEUR" onclick="sortetatTable('FOURNISSEUR')" class="border px-4 py-2">FOURNISSEUR</th>

        <th data-column="TOTAL ECHU" onclick="sortetatTable('TOTAL ECHU')" class="border px-4 py-2">TOTAL ECHU</th>
        <th data-column="TOTAL DETTE" onclick="sortetatTable('TOTAL DETTE')" class="border px-4 py-2">TOTAL DETTE</th>

            <th data-column="TOTAL STOCK" onclick="sortetatTable('TOTAL STOCK')" class="border px-4 py-2">TOTAL STOCK</th>


        </tr>
    </thead>
    <tbody id="etat-fournisseur-table" class="dark:bg-gray-800">
        <tr id="loading-row">
            <td colspan="10" class="text-center p-4">
                <div id="lottie-container-d" style="width: 290px; height: 200px; margin: auto;"></div>
            </td>
        </tr>
    </tbody>
</table>


</div>





     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
         
    
     <br>


        <br><br><br> <br>
        <script>



            // Define an array of element IDs and their corresponding JSON file paths
            const lottieElements = [
                { id: "lottie-container", path: "json_files/date.json" },
                { id: "lottie-container-d", path: "json_files/l.json" },
                { id: "lottie-d", path: "json_files/l.json" },
                { id: "bccb", path: "json_files/l.json" },
                { id: "operator", path: "json_files/l.json" },
                { id: "zone", path: "json_files/l.json" },
                { id: "client", path: "json_files/l.json" }
            ];

            // Loop through each element and initialize Lottie animation
            lottieElements.forEach(({ id, path }) => {
                const container = document.getElementById(id);
                if (container) {
                    lottie.loadAnimation({
                        container: container,
                        renderer: "svg",
                        loop: true,
                        autoplay: true,
                        path: path
                    });
                }
            });

     

            function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function fetchEtatFournisseur() {
    const fournisseurInput = document.getElementById("etat_fournisseur");
    const fournisseur = fournisseurInput ? fournisseurInput.value.trim() : "";

    // Show loading animation if applicable
    document.getElementById("loading-animation")?.classList.remove("hidden");

    try {
        // Build API URL (include fournisseur only if provided)
        let apiUrl = `http://127.0.0.1:5000/fetchEtatFournisseur`;
        if (fournisseur) {
            apiUrl += `?fournisseur=${encodeURIComponent(fournisseur)}`;
        }

        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Fetched data:", data); // Debugging

        // Validate response format
        if (!data || (!Array.isArray(data) && typeof data !== "object")) {
            throw new Error("Invalid API response format.");
        }

        // Convert object to array if necessary
        const results = Array.isArray(data) ? data : [data];

        // Get table body and clear old data
        const tableBody = document.getElementById("dette-table");
        tableBody.innerHTML = "";

        if (results.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center border px-3 py-2">No data available</td></tr>`;
            return;
        }

        // Append rows to the table
        results.forEach(item => {
            const totalEchu = formatNumber(item["TOTAL ECHU"] ?? 0);
            const totalDette = formatNumber(item["TOTAL DETTE"] ?? 0);
            const totalStock = formatNumber(item["TOTAL STOCK"] ?? 0);

            const row = document.createElement("tr");
            row.innerHTML = `
                <td class="border px-3 py-2">${totalEchu}</td>
                <td class="border px-3 py-2">${totalDette}</td>
                <td class="border px-3 py-2">${totalStock}</td>
            `;
            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error("Error fetching etat fournisseur:", error);
        alert("Failed to load data. Check console for details.");
    } finally {
        document.getElementById("loading-animation")?.classList.add("hidden");
    }
}

// Run function on page load to show all results
document.addEventListener("DOMContentLoaded", fetchEtatFournisseur);

// Run function when search input changes
document.getElementById("etat_fournisseur").addEventListener("input", fetchEtatFournisseur);


// // Format number with thousand separators & two decimals
// function formatNumber(value) {
//     if (value === null || value === undefined || isNaN(value)) return "0.00";
//     return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
// }



document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("etat-fournisseur-table");
    const searchInput = document.getElementById("etat_fournisseur");

    // Fetch and display data
    function fetchAndDisplayFournisseurs(searchValue = "") {
        tableBody.innerHTML = `
            <tr id="loading-row">
                <td colspan="4" class="text-center p-4">Loading...</td>
            </tr>
        `;

        fetch(`http://192.168.1.156:5000/fetchFournisseurDette?fournisseur=${encodeURIComponent(searchValue)}`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = ""; // Clear previous content

                if (!Array.isArray(data) || data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center p-4 text-red-500">No results found</td>
                        </tr>
                    `;
                    return;
                }

                data.forEach((row, index) => {
                    const fournisseur = row["FOURNISSEUR"] || "Unknown";
                    const totalEchu = row["TOTAL ECHU"] ? parseFloat(row["TOTAL ECHU"]).toFixed(2) : "0.00";
                    const totalDette = row["TOTAL DETTE"] ? parseFloat(row["TOTAL DETTE"]).toFixed(2) : "0.00";
                    const totalStock = row["TOTAL STOCK"] ? parseFloat(row["TOTAL STOCK"]).toFixed(2) : "0.00";

                    const tr = document.createElement("tr");
                    tr.classList.add("cursor-pointer", "hover:bg-gray-200", "dark:hover:bg-gray-600");
                    tr.innerHTML = `
                        <td class="border px-4 py-2">${fournisseur}</td>
                        <td class="border px-4 py-2">${totalEchu}</td>
                        <td class="border px-4 py-2">${totalDette}</td>
                        <td class="border px-4 py-2">${totalStock}</td>
                    `;

                    // When row is clicked, fill search box and filter data
                    tr.addEventListener("click", () => {
                        searchInput.value = fournisseur;
                        fetchAndDisplayFournisseurs(fournisseur);
                    });

                    tableBody.appendChild(tr);
                });
            })
            .catch(error => {
                console.error("Error fetching data:", error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center p-4 text-red-500">Error loading data</td>
                    </tr>
                `;
            });
    }

    // Sorting function
    function sortetatTable(column) {
        const rows = Array.from(tableBody.querySelectorAll("tr"));
        const columnIndex = { "FOURNISSEUR": 0, "TOTAL ECHU": 1, "TOTAL DETTE": 2, "TOTAL STOCK": 3 }[column];

        rows.sort((a, b) => {
            const valA = a.cells[columnIndex].innerText.trim();
            const valB = b.cells[columnIndex].innerText.trim();

            if (!isNaN(valA) && !isNaN(valB)) {
                return parseFloat(valA) - parseFloat(valB);
            }
            return valA.localeCompare(valB);
        });

        // Reverse if already sorted in ascending order
        if (tableBody.dataset.sortedColumn === column) {
            rows.reverse();
            tableBody.dataset.sortedColumn = "";
        } else {
            tableBody.dataset.sortedColumn = column;
        }

        tableBody.innerHTML = "";
        rows.forEach(row => tableBody.appendChild(row));
    }

    // Listen for search input changes
    searchInput.addEventListener("input", () => {
        fetchAndDisplayFournisseurs(searchInput.value);
    });

    // Fetch initial data on page load
    fetchAndDisplayFournisseurs();
});



            // Dark Mode Toggle Functionality
            const themeToggle = document.getElementById('themeToggle');
            const htmlElement = document.documentElement;

            // Load Dark Mode Preference from Local Storage
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                htmlElement.classList.add('dark');
                themeToggle.checked = true;
            }

            // Toggle Dark Mode on Click
            themeToggle.addEventListener('change', () => {
                htmlElement.classList.toggle('dark');
                const isDarkMode = htmlElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDarkMode);
            });

        </script>

</body>

</html>