<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



$page_identifier = 'Journal_Vente';


require_once 'check_permission.php';

?>

<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="theme.js"></script>
    <script src="api_config.js"></script>
    <link rel="stylesheet" href="journal.css">
</head>






<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">

        <!-- Facturation Server Banner -->
        <div class="mb-4 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white p-3 rounded-lg shadow-md text-center">
            <div class="flex items-center justify-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium">Facturation Server</span>
            </div>
        </div>

        <div class="flex justify-center items-center mb-6">
            <h1 class="text-4xl font-bold dark:text-white text-center bg-gradient-to-r from-gray-700 to-gray-900 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
                Journal de Vente Fact 
            </h1>
        </div>

        <!-- Filters -->
   
        

        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->


        <!-- Date Inputs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Date Filter</h3>
            <div class="date-container flex flex-wrap gap-4 items-center">
                <div class="flex items-center space-x-2">
                    <label for="start-date" class="text-sm font-medium text-gray-600 dark:text-gray-300">Begin Date:</label>
                    <input type="date" id="start-date" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex items-center space-x-2">
                    <label for="end-date" class="text-sm font-medium text-gray-600 dark:text-gray-300">End Date:</label>
                    <input type="date" id="end-date" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Refresh Button with Icon -->
                <button id="refresh-btn" class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Search Filters</h3>
            <div class="search-container">
                <div>
                    <label for="client_journal" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Search Client:</label>
                    <input type="text" id="client_journal" placeholder="Search for client..." class="w-full max-w-md border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <div class="flex justify-center mb-6">
            <button class="flex items-center space-x-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition duration-200 font-medium" id="downloadExcel_journal">
                <img src="assets/excel.png" alt="Excel Icon" class="w-5 h-5" />
                <span>Download Excel</span>
            </button>
        </div>
        
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Total journal de vente</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">

            <th data-column="totalTotalHT" onclick="sortJournalVenteTable('TotalHT')" class="border px-4 py-2">Total HT</th>
            <th data-column="totalTotalTVA" onclick="sortJournalVenteTable('TotalTVA')" class="border px-4 py-2">Total TVA</th>
            <th data-column="totalTotalDT" onclick="sortJournalVenteTable('TotalDT')" class="border px-4 py-2">Total DT</th>
            <th data-column="totalTotalTTC" onclick="sortJournalVenteTable('TotalTTC')" class="border px-4 py-2">Total TTC</th>
            <th data-column="totalNETAPAYER" onclick="sortJournalVenteTable('NETAPAYER')" class="border px-4 py-2">Net à Payer</th>


        </tr>
    </thead>
    <tbody id="totaljournal-vente-table" class="dark:bg-gray-800">

  
    </tbody>
</table>


</div>

<br> <br>


        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Journal de vente</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
        <th data-column="DocumentNo" onclick="sortJournalVenteTable('DocumentNo')" class="border px-4 py-2">
    Document No <span id="sort-icon-DocumentNo"></span>
</th>
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


<div class="flex justify-center items-center mt-4 text-sm text-gray-700 dark:text-white">
    <div id="pagination-info" class="mr-4">Page 1</div>
    <div class="space-x-2">
        <button onclick="goToFirstPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">First</button>
        <button onclick="goToPreviousPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Previous</button>
        <button onclick="goToNextPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Next</button>
        <button onclick="goToLastPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Last</button>
    </div>
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
function formatDate(dateString) {
    if (!dateString) return ''; // Return an empty string if no date provided

    const date = new Date(dateString);
    
    // Format the date as 'Wed, 26 Mar 2025'
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options); // 'en-GB' for British date format
}



 // Function to fetch and display total journal data
async function fetchAndDisplayTotalJournal() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    try {
        // Fetch data from the API endpoint
        const url = API_CONFIG.getApiUrl(`/totalJournal?start_date=${startDate}&end_date=${endDate}`);
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        // Format numbers with thousands separators and 2 decimal places
        const formatNumber = (num) => {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };
        // Create the table row with the data
        const tableBody = document.getElementById('totaljournal-vente-table');
        const isDarkMode = document.body.classList.contains('dark-mode');
        tableBody.innerHTML = `
            <tr class="${isDarkMode ? 'dark-mode-row' : ''}">
                <td class="border px-4 py-2">${formatNumber(data.TotalHT)}</td>
                <td class="border px-4 py-2">${formatNumber(data.TotalTVA)}</td>
                <td class="border px-4 py-2">${formatNumber(data.TotalDT)}</td>
                <td class="border px-4 py-2">${formatNumber(data.TotalTTC)}</td>
                <td class="border px-4 py-2">${formatNumber(data.NETAPAYER)}</td>
            </tr>
        `;
    } catch (error) {
        console.error('Error fetching total journal data:', error);
        // Display error message in the table
        document.getElementById('totaljournal-vente-table').innerHTML = `
            <tr>
                <td colspan="5" class="border px-4 py-2 text-red-500">Error loading data: ${error.message}</td>
            </tr>
        `;
    }
}

// Call the function when the page loads or when date inputs change
document.addEventListener('DOMContentLoaded', fetchAndDisplayTotalJournal);

// If you have date inputs that should trigger a refresh when changed:
document.getElementById("start-date")?.addEventListener('change', fetchAndDisplayTotalJournal);
document.getElementById("end-date")?.addEventListener('change', fetchAndDisplayTotalJournal);



// Optional: Auto-fetch on page load or hook it to a filter button




let journalData = [];
let currentPage = 1;
const rowsPerPage = 8;
let sortColumn = null;
let sortDirection = 'asc'; // or 'desc'
// Sorting function (as referenced in your table headers)
function sortJournalVenteTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    journalData.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (column === 'DateInvoiced') {
            valA = new Date(valA);
            valB = new Date(valB);
        }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortDirection === 'asc' ? valA - valB : valB - valA;
        }

        valA = (valA || '').toString().toUpperCase();
        valB = (valB || '').toString().toUpperCase();

        if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    updateSortIcons(); // Call to update icons
    currentPage = 1;
    renderPage();
}


// Fetch data when filters are applied for journal vente
async function fetchJournalVente() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const client = document.getElementById("client_journal").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = API_CONFIG.getApiUrl(`/journalVente?start_date=${startDate}&end_date=${endDate}&client=${client}`);

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


function updateJournalVenteTable(data) {
    journalData = data || [];
    currentPage = 1;
    renderPage();
}

function renderPage() {
    const tableBody = document.getElementById("journal-vente-table");
    tableBody.innerHTML = "";

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = journalData.slice(start, end);

    if (pageData.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-4">No data available</td></tr>`;
    }

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        
        // Add dark mode class if dark mode is active
        if (document.body.classList.contains('dark-mode')) {
            tr.classList.add('dark-mode-row');
        }
        
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DocumentNo}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.DateInvoiced)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Client}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalHT)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalTVA)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalDT)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalTTC)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.NETAPAYER)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Region ? row.Region.replace(/</g, "&lt;").replace(/>/g, "&gt;") : "Aucune"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Entreprise || "N/A"}</td>
        `;
        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("client_journal");
            if (row.Client) {
                searchInput.value = row.Client;
                searchInput.dispatchEvent(new Event("input"));
            }
        });
        tableBody.appendChild(tr);
    });

    updatePaginationInfo();
}
function updateSortIcons() {
    const headers = document.querySelectorAll("th[data-column]");
    headers.forEach(th => {
        const col = th.getAttribute("data-column");
        const icon = th.querySelector("span");
        if (!icon) return;
        if (col === sortColumn) {
            icon.innerHTML = sortDirection === 'asc' ? '▲' : '▼';
        } else {
            icon.innerHTML = '';
        }
    });
}

function updatePaginationInfo() {
    const totalPages = Math.ceil(journalData.length / rowsPerPage) || 1;
    document.getElementById("pagination-info").textContent = `Page ${currentPage} of ${totalPages}`;
}

function goToFirstPage() {
    currentPage = 1;
    renderPage();
}

function goToPreviousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderPage();
    }
}

function goToNextPage() {
    const totalPages = Math.ceil(journalData.length / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderPage();
    }
}

function goToLastPage() {
    currentPage = Math.ceil(journalData.length / rowsPerPage);
    renderPage();
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

    const url = API_CONFIG.getApiUrl(`/download-journal-vente-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&client=${encodeURIComponent(clientName || "")}`);
    console.log("🔗 Download URL:", url); // ✅ Debugging

    // Create a hidden link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "journal_vente.xlsx"); // Ensure filename is set
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}




            // Dark Mode Toggle Functionality - Compatible with sidebar.php
            document.addEventListener('DOMContentLoaded', function() {
                // Function to apply theme to existing table rows
                function updateTableRowTheme(isDark) {
                    const allRows = document.querySelectorAll('#journal-vente-table tr, #totaljournal-vente-table tr');
                    allRows.forEach(row => {
                        if (isDark) {
                            row.classList.add('dark-mode-row');
                        } else {
                            row.classList.remove('dark-mode-row');
                        }
                    });
                }

                // Function to apply theme
                function applyTheme(isDark) {
                    document.body.classList.toggle('dark-mode', isDark);
                    document.documentElement.classList.toggle('dark', isDark);
                    updateTableRowTheme(isDark);
                }

                // Listen for theme changes from localStorage
                window.addEventListener('storage', (e) => {
                    if (e.key === 'theme') {
                        const isDark = e.newValue === 'dark';
                        applyTheme(isDark);
                    }
                });

                // Listen for custom theme change events
                window.addEventListener('themeChanged', (e) => {
                    const isDark = e.detail.isDark;
                    applyTheme(isDark);
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });

                // Apply initial theme
                const isDark = localStorage.getItem('theme') === 'dark';
                applyTheme(isDark);

                // Check for theme changes every second (as fallback)
                setInterval(() => {
                    const currentTheme = localStorage.getItem('theme');
                    const shouldBeDark = currentTheme === 'dark';
                    const isDark = document.body.classList.contains('dark-mode');
                    
                    if (shouldBeDark !== isDark) {
                        applyTheme(shouldBeDark);
                    }
                }, 1000);
            });

        </script>

</body>

</html>