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
    <link rel="stylesheet" href="recap.css">
    <style>
        .dark td {
            color: #000000 !important;
            /* Force black text in dark mode */
            background-color: #d1d5db;
            /* Light gray background for contrast */
        }

        .dark h2 {
            color: #000000 !important;
            /* Force black text in dark mode */
            background-color: #d1d5db;
            /* Light gray background for contrast */
        }


        .dark label {
            color: white !important;
        }

        /* Positioning the Dark Mode Toggle on Top Right */
        #themeSwitcher {
            position: sticky;
            top: 0;
            right: 0;
            padding: 10px;
            z-index: 50;
        }
        .download-container {
    display: flex;
    justify-content: flex-end;
    padding: 0 16px 12px 16px;
}
.download-wrapper {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 50px; /* Reduced for responsiveness */
      margin-top: 20px;
      padding: 10px;
  }

  .download-wrapper button {
      display: flex;
      align-items: center;
      gap: 10px;
      background-color: white;
      border: 1px solid #d1d5db;
      color: #374151;
      padding: 12px 24px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease-in-out;
  }

  .download-wrapper button:hover {
      background-color: #f3f4f6;
      transform: scale(1.05);
  }

  .download-wrapper button img {
      width: 24px;
      height: 24px;
  }

  /* Responsive Styles */
  @media (max-width: 768px) {
      .download-wrapper {
          flex-direction: column;
          align-items: center;
          gap: 20px;
      }

      .download-wrapper button {
          width: 90%; /* Full width for smaller screens */
          justify-content: center;
      }
  }

  .search-container {
    width: 40%;

      display: grid;
      grid-template-columns: repeat(1, minmax(150px, 1fr)); /* 3 columns per row */
      gap: 16px;
      padding: 20px;
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

  .date-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 600px; /* Adjust width as needed */
    width: 100%; /* Ensures it doesn't exceed max-width */
    margin: 0 auto; /* Centers the container */
}

@media (max-width: 768px) {
    .date-container {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
        max-width: 90%; /* Allows slight expansion on smaller screens */
    }
}

.date-container label {
    font-weight: 600;
    color: #374151;
}

.date-container input {
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease-in-out;
    background-color: white;
    color: #111827;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.date-container input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
}

/* Dark Mode */
.dark .date-container {
    background: #1f2937;
    box-shadow: none;
}

.dark .date-container label {
    color: #e5e7eb;
}

.dark .date-container input {
    background-color: #374151;
    color: white;
    border: 1px solid #4b5563;
}

.dark .date-container input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
    .date-container {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
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


    </style>

</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 <!-- Include SweetAlert2 Library (Add this to your HTML head if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: "⚠️ Warning",
        text: "You are in Facturation Server!",
        icon: "warning",
        confirmButtonText: "OK",
        allowOutsideClick: false // Prevent closing by clicking outside
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
                <label for="client_journal">Search Client:</label>
                <input type="text" id="client_journal" placeholder="Search for client ...">
            </div>
   
           
        
        </div>
        <br>

        <button id="downloadExcel_journal"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Journal de vente Download</span>
        </button>

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