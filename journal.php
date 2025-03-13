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
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: "You are in Facturation Server!",
        html: '<div id="lte-alert-icon" style="width:150px; height:150px; margin:0 auto;"></div>',
        
        confirmButtonText: "OK",
        allowOutsideClick: false,
        didOpen: () => {
            // Load Lottie Animation
            lottie.loadAnimation({
                container: document.getElementById("lte-alert-icon"),
                renderer: "svg",
                loop: true,
                autoplay: true,
                path: "json_files/alrt.json" // Make sure this file is accessible
            });
        }
    });
});
</script>


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
                <label for="client_journal">Search Client:</label>
                <input type="text" id="client_journal" placeholder="Search for client ...">
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
        
        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Journal de vente</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
        <th data-column="DocumentNo" onclick="sortJournalVenteTable('DocumentNo')" class="border px-4 py-2">Document No</th>
        <th data-column="DateInvoiced" onclick="sortJournalVenteTable('DateInvoiced')" class="border px-4 py-2">Date Invoiced</th>

            <th data-column="Client" onclick="sortJournalVenteTable('Client')" class="border px-4 py-2">Client</th>
            <th data-column="TotalHT" onclick="sortJournalVenteTable('TotalHT')" class="border px-4 py-2">Total HT</th>
            <th data-column="TotalTVA" onclick="sortJournalVenteTable('TotalTVA')" class="border px-4 py-2">Total TVA</th>
            <th data-column="TotalDT" onclick="sortJournalVenteTable('TotalDT')" class="border px-4 py-2">Total DT</th>
            <th data-column="TotalTTC" onclick="sortJournalVenteTable('TotalTTC')" class="border px-4 py-2">Total TTC</th>
            <th data-column="NETAPAYER" onclick="sortJournalVenteTable('NETAPAYER')" class="border px-4 py-2">Net à Payer</th>

            <th data-column="Region" onclick="sortJournalVenteTable('Region')" class="border px-4 py-2">Region</th>
            <th data-column="Entreprise" onclick="sortJournalVenteTable('Entreprise')" class="border px-4 py-2">Entreprise</th>

        </tr>
    </thead>
    <tbody id="journal-vente-table" class="dark:bg-gray-800">
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

            // Ensure dates clear on refresh
            document.addEventListener("DOMContentLoaded", function () {
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    const clientInput = document.getElementById("client_journal");
    const refreshBtn = document.getElementById("refresh-btn");

    // Set default value for end date to today
    const today = new Date().toISOString().split("T")[0];
    endDate.value = today;

    function triggerChangeEvent(inputElement) {
        inputElement.focus();
        inputElement.value = inputElement.value; // Ensure the value remains the same
        inputElement.dispatchEvent(new Event("input", { bubbles: true })); // Simulate typing
        inputElement.dispatchEvent(new Event("change", { bubbles: true })); // Simulate selection
    }

    // Ensure start date selection triggers end date update
    startDate.addEventListener("change", function () {
        if (!endDate.dataset.changed) {
            endDate.value = today;
            triggerChangeEvent(endDate);
        }
    });

    // Mark end date as manually changed
    endDate.addEventListener("change", function () {
        endDate.dataset.changed = true;
    });

    // Refresh button action
    refreshBtn.addEventListener("click", function () {
        triggerChangeEvent(startDate);
        triggerChangeEvent(endDate);
        triggerChangeEvent(clientInput);
    });
});

         
// Fetch data when filters are applied for journal vente
async function fetchJournalVente() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const client = document.getElementById("client_journal").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = `http://192.168.1.156:5000/journalVente?start_date=${startDate}&end_date=${endDate}&client=${client}`;

    try {
        showJournalVenteLoader(); // Show loading animation
        const response = await fetch(url); // Fetch data
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json(); // Parse the response as JSON
        console.log("Fetched Data:", data); // Debugging line to check the response
        updateJournalVenteTable(data); // Update table with the fetched data
        hideJournalVenteLoader(); // Hide loading animation
    } catch (error) {
        console.error("Error fetching journal vente data:", error);
        document.getElementById('journal-vente-table').innerHTML =
            `<tr><td colspan="10" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideJournalVenteLoader(); // Hide loading animation if error occurs
    }
}

// Show loader animation
function showJournalVenteLoader() {
    document.getElementById("journal-vente-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="10" class="text-center p-4">Loading...</td>
        </tr>
    `;
}

// Hide loader after fetching data
function hideJournalVenteLoader() {
    const loaderRow = document.getElementById("loading-row");
    if (loaderRow) loaderRow.remove();
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Update table with fetched data for journal vente
function updateJournalVenteTable(data) {
    const tableBody = document.getElementById("journal-vente-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Loop through the data and add rows
    data.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DocumentNo}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.DateInvoiced}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Client}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalHT)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalTVA)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalDT)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalTTC)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.NETAPAYER)}</td>
<td class="border px-4 py-2 dark:border-gray-600">
    ${row.Region ? row.Region.replace(/</g, "&lt;").replace(/>/g, "&gt;") : "Aucune"}
</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Entreprise || "N/A"}</td>
        `;

        // Add click event to fill in the search input
        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("client_journal");
            if (row.Client) {
                searchInput.value = row.Client;
                searchInput.dispatchEvent(new Event("input")); // Trigger input event
            }
        });

        tableBody.appendChild(tr);
    });
}


// Attach event listeners
document.getElementById("start-date").addEventListener("change", fetchJournalVente);
document.getElementById("end-date").addEventListener("change", fetchJournalVente);
document.getElementById("client_journal").addEventListener("input", fetchJournalVente);

// Clear client_journal and trigger function on click
document.getElementById("client_journal").addEventListener("click", function () {
    this.value = ""; // Clear the input
    this.dispatchEvent(new Event("input")); // Trigger input event
});


document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("downloadExcel_journal").addEventListener("click", downloadJournalExcel);
});

function downloadJournalExcel() {
    const clientName = document.getElementById("client_journal").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        console.error("❌ Missing required fields. Not downloading file.");
        return;
    }

    const url = `http://192.168.1.156:5000/download-journal-vente-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&client=${encodeURIComponent(clientName || "")}`;
    console.log("🔗 Download URL:", url); // ✅ Debugging

    // Create a hidden link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "journal_vente.xlsx"); // Ensure filename is set
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}




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