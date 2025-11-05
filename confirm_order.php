<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



// // Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }
$page_identifier = 'CONFIRMED_ORDERS';
  
require_once 'check_permission.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
Order a Confirmer
</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="confirm_orderv2.css">
        <script src="api_config.js"></script>

    <script src="theme.js"></script>

</head>

<body class="transition-colors duration-300">
    <!-- Sidebar Toggle Button -->
 <!-- Include SweetAlert2 Library (Add this to your HTML head if not already included) -->




  <!-- Dark Mode Toggle (Top Right) -->
<style>



.input__container--variant {
  background: linear-gradient(to bottom, #F3FFF9, #F3FFF9);
  border-radius: 30px;
  max-width: 34em;
  padding: 1em;
  box-shadow: 0em 1em 3em #beecdc64;
  display: flex;
  align-items: center;
  position: relative;
}

.shadow__input--variant {
  filter: blur(25px);
  border-radius: 30px;
  background-color: #F3FFF9;
  opacity: 0.5;
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  z-index: 0;
}

.input__search--variant {
  width: 33em;
  border-radius: 13em;
  outline: none;
  border: none;
  padding: 0.8em;
  font-size: 1em;
  color: #002019;
  background-color: transparent;
  z-index: 1;
}

.input__search--variant::placeholder {
  color: #002019;
  opacity: 0.7;
}

.input__button__shadow--variant {
  border-radius: 15px;
  background-color: #07372C;
  padding: 10px;
  border: none;
  cursor: pointer;
  z-index: 1;
}

.input__button__shadow--variant:hover {
  background-color: #3C6659;
}

.input__button__shadow--variant svg {
  width: 1.5em;
  height: 1.5em;
}



// In your CSS (add this to prevent layout shifts)
.content {
  margin-left: 0 !important; /* Force no margin for sidebar */
  transition: none !important; /* Disable animations */
}

.sidebar {
  display: none !important; /* Completely hide sidebar */
}

.sidebar-hidden {
  display: none !important;
}
/* Disable table lift/hover animations on this page */
table tr,
table tbody tr,
.table-container,
.table-container:hover,
.table-container *,
.row-selectable {
    -webkit-transition: none !important;
    -o-transition: none !important;
    transition: none !important;
    -webkit-transform: none !important;
    -moz-transform: none !important;
    -ms-transform: none !important;
    -o-transform: none !important;
    transform: none !important;
    -webkit-box-shadow: none !important;
    box-shadow: none !important;
}

/* Disable table row hover effects */
.table-container tbody tr:hover {
    background: transparent !important;
    transform: none !important;
    box-shadow: none !important;
    border-left: none !important;
}

.table-container tbody tr:hover td {
    color: inherit !important;
    font-weight: normal !important;
}
/* Also disable hover transforms on search and refresh controls */
.search-container,
.search-container *,
.search-btn,
.search-btn *,
.search-btn::before,
.refresh-btn,
.refresh-btn * {
    -webkit-transition: none !important;
    -o-transition: none !important;
    transition: none !important;
    -webkit-transform: none !important;
    -moz-transform: none !important;
    -ms-transform: none !important;
    -o-transform: none !important;
    transform: none !important;
    -webkit-box-shadow: none !important;
    box-shadow: none !important;
}
.search-btn::before { left: 0 !important; }
</style>


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




    <div class="container mx-auto px-4 py-8">
        <div class="page-header">
            <h1 class="page-title">Order a Confirmer</h1>
        </div>
        <!-- Filters -->
   
        

        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->


        <br>


<br>

<!-- 💻 Your new search bar styled like the example -->
<div class="search-container">
  <form class="search-form">
    <input type="text" id="bccb_confirm" class="search-input" placeholder="Search for BCCB ...">
    <button class="search-btn" type="button">
      <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
        <path d="M4 9a5 5 0 1110 0A5 5 0 014 9zm5-7a7 7 0 104.2 12.6.999.999 0 00.093.107l3 3a1 1 0 001.414-1.414l-3-3a.999.999 0 00-.107-.093A7 7 0 009 2z" fill-rule="evenodd" fill="#FFF"></path>
      </svg>
    </button>
  </form>
</div>

<div class="flex justify-center w-full mb-4">
    <button id="refresh-btn" class="refresh-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
        </svg>
    </button>
</div>





        <div id="order-update-info" class="update-info">
            Dernière mise à jour : <span id="last-update">--:--:--</span> |
            Actualisation dans : <span id="countdown">30</span>s
        </div>

        <br>
        
        <!-- Table -->
        <div class="table-container">
            <div class="table-section">
                <h2 class="table-title">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h2a1 1 0 100-2H7z"/>
                    </svg>
                    Ordre a confirmer
                </h2>
            </div>

            <!-- Row count fixed header -->
            <div id="row-count" class="row-count">
                Total Rows: 0
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th data-column="NDOCUMENT" onclick="sortorderconfirmedTable('NDOCUMENT')">NDocument</th>
                            <th data-column="TIER" onclick="sortorderconfirmedTable('TIER')">Tiers</th>
                            <th data-column="DATECOMMANDE" onclick="sortorderconfirmedTable('DATECOMMANDE')">Date Commande</th>
                            <th data-column="VENDEUR" onclick="sortorderconfirmedTable('VENDEUR')">Vendeur</th>
                            <th data-column="MARGE" onclick="sortorderconfirmedTable('MARGE')">Marge</th>
                            <th data-column="MONTANT" onclick="sortorderconfirmedTable('MONTANT')">Montant</th>
                            <th data-column="DESCRIPTION" onclick="sortorderconfirmedTable('DESCRIPTION')">Description</th>
                        </tr>
                    </thead>
                    <tbody id="order-confirmer-table">
                        <!-- Data rows will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

<br><br>

<!-- Add margin top to create space between tables -->
<div id="bccb-product-container" class="table-container" style="display: none;">
    <div class="table-section">
        <h2 class="table-title">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h2a1 1 0 100-2H7z"/>
            </svg>
            BCCB Product
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr>
                    <th>PRODUCT</th>
                    <th>QTY</th>
                    <th>REMISE</th>
                    <th>MARGE</th>
                </tr>
            </thead>
            <tbody id="confirmed-bccb-product-table">
                <!-- Product data will be inserted here -->
            </tbody>
        </table>
    </div>
</div>

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





// Hide loader after fetching data
document.addEventListener("DOMContentLoaded", function () {
    fetchOrderConfirmed();

    const searchInput = document.getElementById("bccb_confirm");
    const bccbProductContainer = document.getElementById("bccb-product-container");

    // On click: Clear and trigger input event
    searchInput.addEventListener("click", function () {
        searchInput.value = '';
        bccbProductContainer.style.display = "none"; // Hide the product container when cleared
        searchInput.dispatchEvent(new Event("input", { bubbles: true }));
    });

    // Listen for input changes
    searchInput.addEventListener("input", function () {
        const bccb = searchInput.value.trim();

        if (bccb) {
            fetchBccbProduct(bccb); // Fetch data when input is not empty
        } else {
            // If search is empty, hide the BCCB product table
            bccbProductContainer.style.display = "none";
        }
    });
});

document.getElementById("refresh-btn").addEventListener("click", async function () {
    await fetchOrderConfirmed();

    const searchInput = document.getElementById("bccb_confirm");
    const currentValue = searchInput.value;

    if (currentValue) {
        searchInput.value = currentValue;
        searchInput.dispatchEvent(new Event("input"));
    }

    console.log("✅ Data refreshed manually via refresh button.");
});


async function fetchOrderConfirmed() {
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/order_confirmed'));
        const data = await response.json();
        
        const tableBody = document.getElementById('order-confirmer-table');
        tableBody.innerHTML = '';

        let totalRow = null;
        let rowCount = 0; // Variable to keep track of the number of rows

        // Update the row count dynamically
        const updateRowCount = (count) => {
            const rowCountElement = document.getElementById('row-count');
            rowCountElement.innerHTML = `Total Rows: ${count - 1}`; // Subtract 1 for the "Total" row
        };

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.classList.add("cursor-pointer");

            if (row.DESCRIPTION === 'Total') {
                tr.style.fontWeight = 'bold';
                totalRow = tr;
            }

            // Format number with space as thousand separator and comma for decimals
            const formatNumber = (num) => 
                num !== null ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num) : '';

            // Format date as DD/MM/YYYY
            const formatDate = (dateString) => {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR');
            };

            tr.innerHTML = `
                <td class="border px-4 py-2">${row.NDOCUMENT || ''}</td>
                <td class="border px-4 py-2">${row.TIER || ''}</td>
                <td class="border px-4 py-2">${formatDate(row.DATECOMMANDE)}</td>
                <td class="border px-4 py-2">${row.VENDEUR || ''}</td>
                <td class="border px-4 py-2">${row.MARGE !== null ? formatNumber(row.MARGE) + ' %' : ''}</td>
                <td class="border px-4 py-2">${formatNumber(row.MONTANT)}</td>
                <td class="border px-4 py-2">${row.DESCRIPTION || ''}</td>
            `;

            // Increment row count
            rowCount++;

            // Make row selectable
            tr.addEventListener("click", function () {
                document.querySelectorAll("tr").forEach(r => r.classList.remove("bg-blue-200", "dark:bg-gray-600")); 
                tr.classList.add("bg-blue-200", "dark:bg-gray-600"); // Highlight selected row
                
                const searchInput = document.getElementById("bccb_confirm");
                searchInput.value = row.NDOCUMENT; // Fill search input
                searchInput.dispatchEvent(new Event("input")); // Trigger search event
            });

            if (row.DESCRIPTION === 'Total') {
                totalRow = tr;
            } else {
                tableBody.appendChild(tr);
            }
        });

        // Update the row count display
        updateRowCount(rowCount);

        // If a totalRow exists, prepend it to the table
        if (totalRow) {
            tableBody.prepend(totalRow);
        }
    } catch (error) {
        console.error('Error fetching order confirmed:', error);
    }
}


async function fetchBccbProduct(bccb) {
    if (!bccb) return;

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL(API_CONFIG.getApiUrl('/fetchBCCBProduct'));
    url.searchParams.append("bccb", bccb);
    url.searchParams.append("ad_org_id", "1000000"); 

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received BCCB Product Data:", data); // Debugging log

        updateBccbProductTable(data);

        // Show table only if data exists
        if (data.length > 0) {
            tableContainer.style.display = "block";
        }
    } catch (error) {
        console.error("Error fetching BCCB product data:", error);
    }
}

function updateBccbProductTable(data) {
    const tableBody = document.getElementById("confirmed-bccb-product-table");
    tableBody.innerHTML = ""; // Clear previous content

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No product data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    data.forEach(row => {
        // Convert REMISE to a whole number percentage, default to 0%
        const remiseFormatted = row.REMISE ? Math.round(row.REMISE * 100) + "%" : "0%";

        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${remiseFormatted}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE || "N/A"}</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment);
}





let countdown = 30;
const countdownElement = document.getElementById('countdown');
const lastUpdateElement = document.getElementById('last-update');

// Update the timestamp
function updateTimestamp() {
    const now = new Date();
    lastUpdateElement.textContent = now.toLocaleTimeString('fr-FR');
}

// Main refresh loop
async function refreshOrderConfirmed() {
    await fetchOrderConfirmed();
    updateTimestamp();
    countdown = 30;
}

// Countdown logic
setInterval(() => {
    countdown--;
    countdownElement.textContent = countdown;

    if (countdown === 0) {
        refreshOrderConfirmed();
    }
}, 1000);

// Initial fetch
refreshOrderConfirmed();






        </script>

</body> 

</html>

