<?php
session_start();



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



$page_identifier = 'Recap_Achat';

// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }
require_once 'check_permission.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
Reacap Achat
</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="recap_achat.css">
    <script src="theme.js"></script>
            <script src="api_config.js"></script>



  
</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">






<style>
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 2s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .table-wrapper {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .table-container {
        flex: 1;
        min-width: 300px;
    }

    #invoice-section .flex {
        flex-wrap: wrap;
    }

    #invoice-section .flex-1 {
        min-width: 400px;
    }

    @media (max-width: 768px) {
        #invoice-section .flex {
            flex-direction: column;
        }
        
        .table-wrapper {
            flex-direction: column;
        }
    }

    /* Pagination Styles */
    .pagination-container button:hover:not(:disabled) {
        transform: translateY(-1px);
    }
</style>


    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">

        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Reacap Achat 
            </h1>
        </div>
        <!-- Filters -->
   
        

        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->


        <br>
        <!-- Date Inputs -->
        <div class="date-container flex space-x-4 items-center">
    <div class="flex items-center space-x-2">
        <label for="start-date" class="text-gray-900 dark:text-white">Begin Date:</label>
        <input type="date" id="start-date" class="border rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600">
    </div>

    <div class="flex items-center space-x-2">
        <label for="end-date" class="text-gray-900 dark:text-white">End Date:</label>
        <input type="date" id="end-date" class="border rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600">
    </div>

    <!-- Fetch & Refresh Button -->
    <button id="refresh-btn" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl border border-blue-500 dark:border-blue-400 transition-all duration-200 flex items-center justify-center space-x-2" title="Fetch and refresh all data">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
        </svg>
        <span>Fetch & Refresh</span>
    </button>
</div>

<!-- User Instructions -->
<!-- <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-4">
    <div class="flex items-center">
        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <p class="text-blue-800 dark:text-blue-200 text-sm">
            <strong>How to use:</strong> Select both start and end dates, then click "Fetch & Refresh" to load data. Use the search fields to filter results locally or click rows for cross-filtering.
        </p>
    </div>
</div> -->



        <br>

        <!-- <button id="downloadExcel_totalrecap"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Total Recap Download</span>
        </button> -->

        <div class="container">
  <button id="downloadExcel_totalrecap" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
</div>



        <br>
        
        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Total Recap</h2>

    <!-- Loading Animation -->
    <div id="loading-animation" class="flex justify-center items-center">
        <!-- <p class="text-lg font-medium text-gray-800 dark:text-white mb-4">Loading...</p> -->
        <div id="lottie-container" style="width: 250px; height: 250px;"></div>
    </div>

    <!-- Result Text (Initially Hidden) -->
    <p id="recap-text" class="text-lg font-medium text-gray-900 dark:text-white hidden">
        Total Chiffre: <span id="chiffre-value" class="font-bold text-indigo-600 dark:text-indigo-400"></span>
    </p>
</div>





     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
        <div class="mt-4 flex justify-center space-x-2" id="pagination"></div>
        
        <div class="download-wrapper">

            <!-- <button id="download-recap-fournisseur-achat-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Fournisseur Download </span> -->
            </button>
            <button id="download-recap-fournisseur-achat-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-recap-product-achat-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
             <!-- <button id="download-recap-product-achat-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Product Download</span>
            </button> -->
        </div>

        <div class="search-container">
            <div class="relative">
                <label for="recap_fournisseur">Recap Fournisseur:</label>
                <div class="relative">
                    <input type="text" id="recap_fournisseur" placeholder="Search... (click to clear & fetch all)">
                    <button type="button" id="clear-fournisseur" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-red-500 text-xl font-bold" title="Clear and fetch all data">×</button>
                </div>
            </div>
   
            <div class="relative">
                <label for="recap_product">Recap Product:</label>
                <div class="relative">
                    <input type="text" id="recap_product" placeholder="Search... (click to clear & fetch all)">
                    <button type="button" id="clear-product" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-red-500 text-xl font-bold" title="Clear and fetch all data">×</button>
                </div>
            </div>
        
        </div>
        
     <br>
     <div class="table-wrapper">
        <!-- First Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <h2 class="text-lg font-semibold p-4 text-gray-900 dark:text-white">RECAP ACHAT FOURNISSEUR</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header bg-gray-100 dark:bg-gray-700">
                            <th data-column="FOURNISSEUR" onclick="sortrecapachatTable('FOURNISSEUR')" class="border px-4 py-2 text-gray-900 dark:text-white">Fournisseur</th>
                            <th data-column="CHIFFRE" onclick="sortrecapachatTable('CHIFFRE')" class="border px-4 py-2 text-gray-900 dark:text-white">CHIFFRE</th>
                        </tr>
                    </thead>
                    <tbody id="recap-frnsr-table-achat" class="dark:bg-gray-800">
                        <tr id="loading-row">
                            <td colspan="2" class="text-center p-4">
                                <div id="lottie-container-d" style="width: 290px; height: 200px; margin: auto;">
                                    <div class="loading-spinner"></div>
                                    <p>Loading...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination for First Table -->
            <div class="mt-4 flex justify-center space-x-2" id="fournisseur-pagination"></div>
        </div>

        <!-- Second Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <h2 class="text-lg font-semibold p-4 text-gray-900 dark:text-white">RECAP ACHAT PRODUIT</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header bg-gray-100 dark:bg-gray-700">
                            <th data-column="PRODUIT" onclick="sortrecpproductTableachat('PRODUIT')" class="border px-4 py-2 text-gray-900 dark:text-white">Product</th>
                            <th data-column="QTY" onclick="sortrecpproductTableachat('QTY')" class="border px-4 py-2 text-gray-900 dark:text-white">QTY</th>
                            <th data-column="CHIFFRE" onclick="sortrecpproductTableachat('CHIFFRE')" class="border px-4 py-2 text-gray-900 dark:text-white">Chiffre</th>
                        </tr>
                    </thead>
                    <tbody id="recap-prdct-table" class="dark:bg-gray-800">
                        <tr id="loading-row">
                            <td colspan="3" class="text-center p-4">
                                <div id="lottie-d" style="width: 290px; height: 200px; margin: auto;">
                                    <div class="loading-spinner"></div>
                                    <p>Loading...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination for Second Table -->
            <div class="mt-4 flex justify-center space-x-2" id="product-pagination"></div>
        </div>
    </div>

    <!-- Invoice Details Section -->
    <div id="invoice-section" class="hidden mt-8">
        <!-- Download buttons for Invoice Tables -->
        <div class="flex gap-4 mb-4 justify-center">
            <button id="download-invoices-excel" class="button">
                <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
                <p class="text">
                    <span style="transition-duration: 100ms">I</span>
                    <span style="transition-duration: 150ms">n</span>
                    <span style="transition-duration: 200ms">v</span>
                    <span style="transition-duration: 250ms">o</span>
                    <span style="transition-duration: 300ms">i</span>
                    <span style="transition-duration: 350ms">c</span>
                    <span style="transition-duration: 400ms">e</span>
                    <span style="transition-duration: 450ms">s</span>
                </p>
            </button>
            
            <button id="download-invoice-lines-excel" class="button">
                <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
                <p class="text">
                    <span style="transition-duration: 100ms">L</span>
                    <span style="transition-duration: 150ms">i</span>
                    <span style="transition-duration: 200ms">n</span>
                    <span style="transition-duration: 250ms">e</span>
                    <span style="transition-duration: 300ms">s</span>
                </p>
            </button>
        </div>
        
        <div class="flex gap-4">
            <!-- Invoice Table -->
            <div class="flex-1 table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">FACTURES - <span id="selected-supplier-name">Supplier</span></h2>
                    <button id="close-invoice-section" class="text-red-500 hover:text-red-700 font-bold text-xl">×</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header bg-gray-100 dark:bg-gray-700">
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Document No</th>
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Total</th>
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Description</th>
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Date</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body" class="dark:bg-gray-800">
                            <!-- Invoice rows will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Invoice Lines Table -->
            <div class="flex-1 table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="p-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">INVOICE LINES - <span id="selected-invoice-number">Select Invoice</span></h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header bg-gray-100 dark:bg-gray-700">
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Product Name</th>
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Quantity</th>
                                <th class="border px-4 py-2 text-gray-900 dark:text-white">Line Amount</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-lines-table-body" class="dark:bg-gray-800">
                            <tr>
                                <td colspan="3" class="text-center p-4 text-gray-500">Click on an invoice to view line details</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
function makeTableColumnsResizable(table) {
    const cols = table.querySelectorAll("th");
    const tableContainer = table.parentElement;

    cols.forEach((col) => {
        // Create a resizer handle
        const resizer = document.createElement("div");
        resizer.classList.add("resizer");
        col.style.position = "relative";
        resizer.style.width = "5px";
        resizer.style.height = "100%";
        resizer.style.position = "absolute";
        resizer.style.top = "0";
        resizer.style.right = "0";
        resizer.style.cursor = "col-resize";
        resizer.style.userSelect = "none";
        resizer.style.zIndex = "10";

        col.appendChild(resizer);

        let x = 0;
        let w = 0;

        resizer.addEventListener("mousedown", (e) => {
            x = e.clientX;
            w = col.offsetWidth;

            document.addEventListener("mousemove", mouseMoveHandler);
            document.addEventListener("mouseup", mouseUpHandler);
        });

        const mouseMoveHandler = (e) => {
            const dx = e.clientX - x;
            col.style.width = `${w + dx}px`;
        };

        const mouseUpHandler = () => {
            document.removeEventListener("mousemove", mouseMoveHandler);
            document.removeEventListener("mouseup", mouseUpHandler);
        };
    });
}

// Wait for the DOM to load before applying resizable
document.addEventListener("DOMContentLoaded", () => {
    const tables = document.querySelectorAll(".table-container table");
tables.forEach((table) => makeTableColumnsResizable(table));
});

        // Example functions for sorting (you can replace them with your sorting logic)
        function sortrecapachatTable(column) {
            console.log('Sorting recapachat table by ' + column);
            // Add your sorting logic here
        }

        function sortrecpproductTableachat(column) {
            console.log('Sorting recap product table by ' + column);
            // Add your sorting logic here
        }
    </script>

    
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

            // Set dates to today on page load
            window.onload = () => {
                const today = new Date().toISOString().split("T")[0];
                document.getElementById("start-date").value = today;
                document.getElementById("end-date").value = today;
                document.getElementById("recap_fournisseur").value = "";
                document.getElementById("recap_product").value = "";
            };

            document.addEventListener("DOMContentLoaded", function () {
        const startDate = document.getElementById("start-date");
        const endDate = document.getElementById("end-date");
        const refreshBtn = document.getElementById("refresh-btn");

        

        // Set default value for both start and end date to today
        const today = new Date().toISOString().split("T")[0];
        startDate.value = today;
        endDate.value = today;

        function triggerChangeEvent(inputElement) {
            inputElement.focus(); // Simulate user focusing on the field
            inputElement.value = inputElement.value; // Ensure the value is set correctly
            inputElement.dispatchEvent(new Event("input", { bubbles: true })); // Simulate user typing
            inputElement.dispatchEvent(new Event("change", { bubbles: true })); // Simulate user selection
        }

        // When start date is selected, set end date to today if not manually changed
        startDate.addEventListener("change", function () {
            if (!endDate.dataset.changed) {
                endDate.value = today;
                triggerChangeEvent(endDate); // Ensure all listeners detect the change
            }
        });

        // Mark end date as manually changed
        endDate.addEventListener("change", function () {
            endDate.dataset.changed = true;
        });

        // Refresh button action
        refreshBtn.addEventListener("click", function () {
            if (startDate.value && endDate.value) {
                // Clear all selections and filters
                selectedFournisseur = null;
                selectedProduct = null;
                document.getElementById("recap_fournisseur").value = "";
                document.getElementById("recap_product").value = "";
                
                // Reset pagination to first page
                fournisseurCurrentPage = 1;
                productCurrentPage = 1;
                
                // Fetch all data
                fetchTotalRecapAchat();
                fetchFournisseurRecapAchat();
                fetchProductRecapAchat();
            } else {
                alert("Please select both start and end dates before fetching data.");
            }
        });
    });
// Format number with thousand separators & two decimals


            function hideLoader() {
                const loaderRow = document.getElementById('loading-row');
                if (loaderRow) {
                    loaderRow.remove();
                }
            }

      

            
            // Note: Auto-fetch removed - data will only be fetched when refresh button is clicked


            async function fetchTotalRecapAchat() {
  const startDate = document.getElementById("start-date").value;
  const endDate = document.getElementById("end-date").value;

  if (!startDate || !endDate) {
    console.log("Both start and end dates are required to fetch data");
    return; // Don't fetch until both dates are selected
  }

  // Show loading animation, hide result text
  document.getElementById("loading-animation").classList.remove("hidden");
  document.getElementById("recap-text").classList.add("hidden");

  try {
    const response = await fetch(API_CONFIG.getApiUrl(`/fetchTotalRecapAchat?start_date=${startDate}&end_date=${endDate}`));
    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();

    // If the server response contains 'chiffre', display the result
    if (data.chiffre) {
      const chiffre = formatNumber(data.chiffre);
      document.getElementById("chiffre-value").textContent = `${chiffre} DZD`;
    } else {
      throw new Error("Data structure is missing 'chiffre' field");
    }

    // Hide loading animation, show result text
    document.getElementById("loading-animation").classList.add("hidden");
    document.getElementById("recap-text").classList.remove("hidden");

  } catch (error) {
    console.error("Error fetching total recap achat data:", error);
    document.getElementById("recap-text").textContent = "Échec du chargement des données";
    document.getElementById("recap-text").classList.add("text-red-500");

    // Hide animation in case of error
    document.getElementById("loading-animation").classList.add("hidden");
    document.getElementById("recap-text").classList.remove("hidden");
  }
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "0.00";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Note: Auto-fetch event listeners removed - data will only be fetched when refresh button is clicked

// Fetch data when filters are applied for recap achat

async function fetchFournisseurRecapAchat() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = API_CONFIG.getApiUrl(`/fetchfourisseurRecapAchat?start_date=${startDate}&end_date=${endDate}&fournisseur=${fournisseur}&product=${product}`);

    try {
        showLoader(); // Show loading animation
        const response = await fetch(url); // Fetch data
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json(); // Parse the response as JSON
        console.log("Fetched Data:", data); // Debugging line to check the response
        updateFournisseurRecapAchatTable(data); // Update table with the fetched data
        hideLoader(); // Hide loading animation
    } catch (error) {
        console.error("Error fetching fournisseur recap achat data:", error);
        document.getElementById('recap-frnsr-table-achat').innerHTML =
            `<tr><td colspan="2" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideLoader(); // Hide loading animation if error occurs
    }
}

// Show loader animation
function showLoader() {
    document.getElementById("recap-frnsr-table-achat").innerHTML = `
        <tr id="loading-row">
            <td colspan="2" class="text-center p-4">Loading...</td>
        </tr>
    `;
}

// Hide loader after fetching data
function hideLoader() {
    const loaderRow = document.getElementById("loading-row");
    if (loaderRow) loaderRow.remove();
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}




// Update table with fetched data for recap achat
function updateFournisseurRecapAchatTable(data) {
    if (!data || data.length === 0) {
        const tableBody = document.getElementById("recap-frnsr-table-achat");
        tableBody.innerHTML = `<tr><td colspan="2" class="text-center p-4">No data available</td></tr>`;
        document.getElementById("fournisseur-pagination").innerHTML = '';
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    // 🔽 Sort by CHIFFRE descending
    filteredData.sort((a, b) => b.CHIFFRE - a.CHIFFRE);

    // Store data globally for pagination
    fournisseurData = filteredData;
    fournisseurCurrentPage = 1; // Reset to first page
    
    // Update display
    updateFournisseurDisplay();
}

function renderFournisseurPage(page, totalRow, dataToUse = null) {
    const tableBody = document.getElementById("recap-frnsr-table-achat");
    tableBody.innerHTML = "";

    // Add the "Total" row with sticky style
    if (totalRow) {
        tableBody.innerHTML += `
            <tr class="bg-gray-200 dark:bg-gray-600 font-bold sticky top-0 z-10">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
            </tr>
        `;
    }

    // Get paginated data
    const dataSource = dataToUse || fournisseurData;
    const paginatedData = paginateData(dataSource, page);

    // Add the paginated data rows
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        // Highlight selected row
        const isSelected = selectedFournisseur === row.FOURNISSEUR;
        tr.className = isSelected 
            ? "bg-blue-100 dark:bg-blue-800 cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-700 border-l-4 border-blue-500"
            : "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
        `;

        tr.addEventListener("click", () => {
            const fournisseurInput = document.getElementById("recap_fournisseur");
            
            if (row.FOURNISSEUR && row.FOURNISSEUR !== "Total") {
                // Toggle functionality - if clicking the same fournisseur, clear the filter
                if (selectedFournisseur === row.FOURNISSEUR) {
                    // Clear only the fournisseur filter
                    selectedFournisseur = null;
                    fournisseurInput.value = '';
                } else {
                    // Set new fournisseur filter - don't touch product search
                    selectedFournisseur = row.FOURNISSEUR;
                    fournisseurInput.value = row.FOURNISSEUR;
                }
                
                // Reset both tables to first page
                fournisseurCurrentPage = 1;
                productCurrentPage = 1;
                
                // Fetch fresh data with the new filter (or clear filter)
                fetchFournisseurRecapAchat();
                fetchProductRecapAchat();
                
                // Fetch invoices for this supplier
                if (selectedFournisseur) {
                    fetchSupplierInvoices(selectedFournisseur);
                }
            }
        });

        tableBody.appendChild(tr);
    });
}


// Note: Auto-fetch event listeners removed - data will only be fetched when refresh button is clicked or rows are clicked
async function fetchProductRecapAchat() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = API_CONFIG.getApiUrl(`/fetchProductRecapAchat?start_date=${startDate}&end_date=${endDate}&fournisseur=${fournisseur}&product=${product}`);

    try {
        showLoader(); // Show loading animation
        const response = await fetch(url); // Fetch data
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json(); // Parse the response as JSON
        console.log("Fetched Data:", data); // Debugging line to check the response
        updateProductRecapAchatTable(data); // Update table with the fetched data
        hideLoader(); // Hide loading animation
    } catch (error) {
        console.error("Error fetching product recap achat data:", error);
        document.getElementById('recap-prdct-table').innerHTML =
            `<tr><td colspan="3" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideLoader(); // Hide loading animation if error occurs
    }
}

// Show loader animation
function showLoader() {
    document.getElementById("recap-prdct-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="3" class="text-center p-4">Loading...</td>
        </tr>
    `;
}

// Hide loader after fetching data
function hideLoader() {
    const loaderRow = document.getElementById("loading-row");
    if (loaderRow) loaderRow.remove();
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Update table with fetched data for product recap achat
// function updateProductRecapAchatTable(data) {
//     const tableBody = document.getElementById("recap-prdct-table");
//     tableBody.innerHTML = "";

//     if (!data || data.length === 0) {
//         tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
//         return;
//     }

//     // Find and separate the total row
//     const totalRow = data.find(row => row.PRODUIT === "Total");
//     const filteredData = data.filter(row => row.PRODUIT !== "Total");

//     // Add the "Total" row with sticky style
//     if (totalRow) {
//         const totalRowElement = document.createElement("tr");
//         totalRowElement.className = "bg-gray-200 font-bold sticky top-0 z-10";
//         totalRowElement.innerHTML = `
//             <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
//         `;
//         tableBody.appendChild(totalRowElement);
//     }

//     // Add the filtered data rows
//     filteredData.forEach(row => {
//         const tr = document.createElement("tr");
//         tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
//         tr.innerHTML = `
//             <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
//         `;

//         // Add click event to fill in the search input
//         tr.addEventListener("click", () => {
//             const searchInput = document.getElementById("recap_product");
//             if (row.PRODUIT) {
//                 searchInput.value = row.PRODUIT;
//                 searchInput.dispatchEvent(new Event("input")); // Trigger input event
//             }
//         });

//         tableBody.appendChild(tr);
//     });
// }
function updateProductRecapAchatTable(data) {
    if (!data || data.length === 0) {
        const tableBody = document.getElementById("recap-prdct-table");
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        document.getElementById("product-pagination").innerHTML = '';
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.PRODUIT === "Total");
    const filteredData = data.filter(row => row.PRODUIT !== "Total");

    // 🔽 Sort by CHIFFRE descending
    filteredData.sort((a, b) => b.CHIFFRE - a.CHIFFRE);

    // Store data globally for pagination
    productData = filteredData;
    productCurrentPage = 1; // Reset to first page
    
    // Update display
    updateProductDisplay();
}

function renderProductPage(page, totalRow, dataToUse = null) {
    const tableBody = document.getElementById("recap-prdct-table");
    tableBody.innerHTML = "";

    // Add the "Total" row with sticky style
    if (totalRow) {
        const totalRowElement = document.createElement("tr");
        totalRowElement.className = "bg-gray-200 dark:bg-gray-600 font-bold sticky top-0 z-10";
        totalRowElement.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
        `;
        tableBody.appendChild(totalRowElement);
    }

    // Get paginated data
    const dataSource = dataToUse || productData;
    const paginatedData = paginateData(dataSource, page);

    // Add the paginated data rows
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        // Highlight selected row
        const isSelected = selectedProduct === row.PRODUIT;
        tr.className = isSelected 
            ? "bg-blue-100 dark:bg-blue-800 cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-700 border-l-4 border-blue-500"
            : "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
        `;

        tr.addEventListener("click", () => {
            const productInput = document.getElementById("recap_product");
            
            if (row.PRODUIT && row.PRODUIT !== "Total") {
                // Toggle functionality - if clicking the same product, clear the filter
                if (selectedProduct === row.PRODUIT) {
                    // Clear only the product filter
                    selectedProduct = null;
                    productInput.value = '';
                } else {
                    // Set new product filter - don't touch fournisseur search
                    selectedProduct = row.PRODUIT;
                    productInput.value = row.PRODUIT;
                }
                
                // Reset both tables to first page
                fournisseurCurrentPage = 1;
                productCurrentPage = 1;
                
                // Fetch fresh data with the new filter (or clear filter)
                fetchFournisseurRecapAchat();
                fetchProductRecapAchat();
            }
        });

        tableBody.appendChild(tr);
    });
}

// Pagination variables
let fournisseurData = [];
let productData = [];
let fournisseurCurrentPage = 1;
let productCurrentPage = 1;
let fournisseurFilteredData = [];
let productFilteredData = [];
const itemsPerPage = 10;

// Track selected rows for toggle functionality
let selectedFournisseur = null;
let selectedProduct = null;

// Pagination functions
function createPagination(totalItems, currentPage, paginationId, tableType) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById(paginationId);
    paginationContainer.innerHTML = '';
    paginationContainer.className = 'flex items-center justify-center space-x-4 mt-4';

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = `px-4 py-2 rounded-lg font-medium transition-all duration-200 ${currentPage === 1 ? 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed' : 'bg-blue-500 text-white hover:bg-blue-600 hover:shadow-lg'}`;
    prevBtn.textContent = 'Previous';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
        if (currentPage > 1) {
            if (tableType === 'fournisseur') {
                fournisseurCurrentPage = currentPage - 1;
                updateFournisseurDisplay();
            } else if (tableType === 'product') {
                productCurrentPage = currentPage - 1;
                updateProductDisplay();
            }
        }
    };
    paginationContainer.appendChild(prevBtn);

    // Page info (1/22 format) with total items count
    const pageInfo = document.createElement('div');
    pageInfo.className = 'flex items-center space-x-3';
    
    const pageDisplay = document.createElement('span');
    pageDisplay.className = 'px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg font-semibold min-w-[80px] text-center border border-gray-300 dark:border-gray-600';
    pageDisplay.textContent = `${currentPage}/${totalPages}`;
    
    const itemsInfo = document.createElement('span');
    itemsInfo.className = 'text-sm text-gray-600 dark:text-gray-400';
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    itemsInfo.textContent = `(${startItem}-${endItem} of ${totalItems})`;
    
    pageInfo.appendChild(pageDisplay);
    pageInfo.appendChild(itemsInfo);
    paginationContainer.appendChild(pageInfo);

    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = `px-4 py-2 rounded-lg font-medium transition-all duration-200 ${currentPage === totalPages ? 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed' : 'bg-blue-500 text-white hover:bg-blue-600 hover:shadow-lg'}`;
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => {
        if (currentPage < totalPages) {
            if (tableType === 'fournisseur') {
                fournisseurCurrentPage = currentPage + 1;
                updateFournisseurDisplay();
            } else if (tableType === 'product') {
                productCurrentPage = currentPage + 1;
                updateProductDisplay();
            }
        }
    };
    paginationContainer.appendChild(nextBtn);
}

// Update display functions
function updateFournisseurDisplay() {
    // Only filter by fournisseur search field for local filtering
    const fournisseurSearch = document.getElementById('recap_fournisseur').value.trim().toUpperCase();
    let dataToUse = fournisseurData;
    
    if (fournisseurSearch) {
        dataToUse = fournisseurData.filter(row => 
            row.FOURNISSEUR && row.FOURNISSEUR.toUpperCase().includes(fournisseurSearch)
        );
    }
    
    fournisseurFilteredData = dataToUse;
    
    // Find total row for display
    const allData = [...fournisseurData];
    const totalRow = allData.find(row => row.FOURNISSEUR === "Total");
    
    renderFournisseurPage(fournisseurCurrentPage, totalRow, dataToUse);
    createPagination(dataToUse.length, fournisseurCurrentPage, 'fournisseur-pagination', 'fournisseur');
}

function updateProductDisplay() {
    // Only filter by product search field for local filtering
    const productSearch = document.getElementById('recap_product').value.trim().toUpperCase();
    let dataToUse = productData;
    
    if (productSearch) {
        dataToUse = productData.filter(row => 
            row.PRODUIT && row.PRODUIT.toUpperCase().includes(productSearch)
        );
    }
    
    productFilteredData = dataToUse;
    
    // Find total row for display
    const allData = [...productData];
    const totalRow = allData.find(row => row.PRODUIT === "Total");
    
    renderProductPage(productCurrentPage, totalRow, dataToUse);
    createPagination(dataToUse.length, productCurrentPage, 'product-pagination', 'product');
}

function paginateData(data, page) {
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return data.slice(startIndex, endIndex);
}

// New functionality for supplier invoices
async function fetchSupplierInvoices(supplierName) {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        alert("Please select start and end dates first");
        return;
    }

    try {
        // Show the invoice section
        const invoiceSection = document.getElementById("invoice-section");
        const supplierNameSpan = document.getElementById("selected-supplier-name");
        
        invoiceSection.classList.remove("hidden");
        supplierNameSpan.textContent = supplierName;

        // Show loading in invoice table
        const invoiceTableBody = document.getElementById("invoice-table-body");
        invoiceTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center p-4">
                    <div class="loading-spinner"></div>
                    <p>Loading invoices...</p>
                </td>
            </tr>
        `;

        // Clear invoice lines table
        const invoiceLinesTableBody = document.getElementById("invoice-lines-table-body");
        invoiceLinesTableBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center p-4 text-gray-500">Click on an invoice to view line details</td>
            </tr>
        `;

        // Fetch invoices
        const url = API_CONFIG.getApiUrl(`/fetchFactureRecapAchat?start_date=${startDate}&end_date=${endDate}&partner_name=${encodeURIComponent(supplierName)}`);
        const response = await fetch(url);
        
        if (!response.ok) throw new Error("Network response was not ok");
        
        const data = await response.json();
        updateInvoiceTable(data);

    } catch (error) {
        console.error("Error fetching supplier invoices:", error);
        const invoiceTableBody = document.getElementById("invoice-table-body");
        invoiceTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-red-500 p-4">Failed to load invoices</td>
            </tr>
        `;
    }
}

function updateInvoiceTable(data) {
    const tableBody = document.getElementById("invoice-table-body");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No invoices found</td></tr>`;
        return;
    }

    data.forEach(invoice => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.setAttribute('data-invoice-id', invoice.c_invoice_id); // Store invoice ID for download
        
        // Format date
        const dateFormatted = invoice.dateinvoiced ? new Date(invoice.dateinvoiced).toLocaleDateString() : 'N/A';
        
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${invoice.documentno || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(invoice.totallines)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${invoice.description || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${dateFormatted}</td>
        `;

        // Add click event to fetch invoice lines
        tr.addEventListener("click", () => {
            if (invoice.c_invoice_id) {
                fetchInvoiceLines(invoice.c_invoice_id, invoice.documentno);
            }
        });

        tableBody.appendChild(tr);
    });
}

async function fetchInvoiceLines(invoiceId, documentNo) {
    try {
        // Update the invoice number in the header
        const invoiceNumberSpan = document.getElementById("selected-invoice-number");
        invoiceNumberSpan.textContent = documentNo;

        // Show loading in invoice lines table
        const invoiceLinesTableBody = document.getElementById("invoice-lines-table-body");
        invoiceLinesTableBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center p-4">
                    <div class="loading-spinner"></div>
                    <p>Loading invoice lines...</p>
                </td>
            </tr>
        `;

        // Fetch invoice lines
        const url = API_CONFIG.getApiUrl(`/fetchBCFProduct?invoice_id=${invoiceId}`);
        const response = await fetch(url);
        
        if (!response.ok) throw new Error("Network response was not ok");
        
        const data = await response.json();
        updateInvoiceLinesTable(data);

    } catch (error) {
        console.error("Error fetching invoice lines:", error);
        const invoiceLinesTableBody = document.getElementById("invoice-lines-table-body");
        invoiceLinesTableBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-red-500 p-4">Failed to load invoice lines</td>
            </tr>
        `;
    }
}

function updateInvoiceLinesTable(data) {
    const tableBody = document.getElementById("invoice-lines-table-body");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No invoice lines found</td></tr>`;
        return;
    }

    data.forEach(line => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600";
        
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${line.product_name || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(line.qtyentered)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(line.linenetamt)}</td>
        `;

        tableBody.appendChild(tr);
    });
}

// Close invoice section
document.getElementById("close-invoice-section").addEventListener("click", () => {
    const invoiceSection = document.getElementById("invoice-section");
    invoiceSection.classList.add("hidden");
    
    // Reset invoice lines table
    const invoiceLinesTableBody = document.getElementById("invoice-lines-table-body");
    const invoiceNumberSpan = document.getElementById("selected-invoice-number");
    
    invoiceNumberSpan.textContent = "Select Invoice";
    invoiceLinesTableBody.innerHTML = `
        <tr>
            <td colspan="3" class="text-center p-4 text-gray-500">Click on an invoice to view line details</td>
        </tr>
    `;
});



document.getElementById("download-recap-product-achat-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
 

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    // Construct the URL with query parameters
    const url = API_CONFIG.getApiUrl('/download-recap-product-achat-excel');
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
 

    try {
        // Trigger the download by navigating to the URL
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

// Fetch data when filters are applied
document.getElementById("download-recap-fournisseur-achat-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
 

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    // Construct the URL with query parameters
    const url = API_CONFIG.getApiUrl('/download-recap-fournisseur-achat-excel');
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
 

    try {
        // Trigger the download by navigating to the URL
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

// Download Invoices Excel
document.getElementById("download-invoices-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const supplierName = document.getElementById("selected-supplier-name").textContent;

    // Check if we have the required data
    if (!startDate || !endDate) {
        alert("Please select start and end dates first.");
        return;
    }

    if (!supplierName || supplierName === "Supplier") {
        alert("Please select a supplier first by clicking on a supplier in the table.");
        return;
    }

    // Create URL for downloading invoices
    const url = API_CONFIG.getApiUrl('/download-invoices-excel');
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("partner_name", supplierName);

    try {
        // Trigger the download by navigating to the URL
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading invoices:", error);
        alert("Failed to download invoices.");
    }
});

// Download Invoice Lines Excel
document.getElementById("download-invoice-lines-excel").addEventListener("click", async function () {
    const invoiceNumber = document.getElementById("selected-invoice-number").textContent;
    const invoiceTableBody = document.getElementById("invoice-table-body");
    
    if (!invoiceNumber || invoiceNumber === "Select Invoice") {
        alert("Please select an invoice first by clicking on an invoice in the invoices table.");
        return;
    }

    // Find the selected invoice ID from the invoice table
    let selectedInvoiceId = null;
    const invoiceRows = invoiceTableBody.querySelectorAll("tr");
    
    for (let row of invoiceRows) {
        const documentNoCell = row.querySelector("td:first-child");
        if (documentNoCell && documentNoCell.textContent.trim() === invoiceNumber) {
            // Get the invoice ID from the row's click event data
            // We need to store this when creating the rows
            selectedInvoiceId = row.getAttribute('data-invoice-id');
            break;
        }
    }

    if (!selectedInvoiceId) {
        alert("Could not find invoice ID. Please select the invoice again.");
        return;
    }

    // Create URL for downloading invoice lines
    const url = API_CONFIG.getApiUrl('/download-invoice-lines-excel');
    url.searchParams.append("invoice_id", selectedInvoiceId);

    try {
        // Trigger the download by navigating to the URL
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading invoice lines:", error);
        alert("Failed to download invoice lines.");
    }
});

    // Function to handle the click event
    function handleInputClick(event) {
        // Clear the input value
        event.target.value = '';
        
        // Clear selection states
        selectedFournisseur = null;
        selectedProduct = null;
        
        // Reset both tables to first page
        fournisseurCurrentPage = 1;
        productCurrentPage = 1;
        
        // Fetch fresh data with no filters (empty parameters)
        fetchFournisseurRecapAchat();
        fetchProductRecapAchat();
    }

    // Local filtering functions for search
    function filterFournisseurTable() {
        // Reset to first page when searching
        fournisseurCurrentPage = 1;
        // Update display with current search
        updateFournisseurDisplay();
    }

    function filterProductTable() {
        // Reset to first page when searching
        productCurrentPage = 1;
        // Update display with current search
        updateProductDisplay();
    }

    // Removed old render functions - now using updateFournisseurDisplay() and updateProductDisplay()

    // Get the input elements
    const fournisseurInput = document.getElementById('recap_fournisseur');
    const productInput = document.getElementById('recap_product');

    // Add click event listeners for clearing search fields
    fournisseurInput.addEventListener('click', handleInputClick);
    productInput.addEventListener('click', handleInputClick);
    
    // Add input event listeners for local filtering
    fournisseurInput.addEventListener('input', filterFournisseurTable);
    productInput.addEventListener('input', filterProductTable);
    
    // Clear button event listeners
    document.getElementById('clear-fournisseur').addEventListener('click', function() {
        document.getElementById('recap_fournisseur').value = '';
        selectedFournisseur = null;
        fournisseurCurrentPage = 1;
        productCurrentPage = 1;
        // Only fetch data if dates are selected
        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;
        if (startDate && endDate) {
            fetchFournisseurRecapAchat();
            fetchProductRecapAchat();
        }
    });
    
    document.getElementById('clear-product').addEventListener('click', function() {
        document.getElementById('recap_product').value = '';
        selectedProduct = null;
        fournisseurCurrentPage = 1;
        productCurrentPage = 1;
        // Only fetch data if dates are selected
        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;
        if (startDate && endDate) {
            fetchFournisseurRecapAchat();
            fetchProductRecapAchat();
        }
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