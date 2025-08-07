<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}


// // Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }

$page_identifier = 'Product';

require_once 'check_permission.php';



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="product.css">
    <link rel="stylesheet" href="prdct.css">
        <script src="api_config.js"></script>

    <script>
        // Check and apply theme on page load
        const isDarkMode = localStorage.getItem('theme') === 'dark';
        if (isDarkMode) {
            document.documentElement.classList.add('dark');
        }

        // Listen for theme changes from sidebar
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme') {
                document.documentElement.classList.toggle('dark', e.newValue === 'dark');
            }
        });
    </script>

    <!-- Sidebar Toggle Button -->



    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script>
        // Second animation
    var rocketAnimation = lottie.loadAnimation({
        container: document.getElementById('lottieContainer'),  // ID of the second container
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'json_files/r.json'  // Path to your second JSON file
    });
    </script>
    

    <!-- Sidebar -->
<!-- Sidebar -->





    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
    <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center  ">
                 Products 
            </h1>
        </div>
      



        <!-- Filters -->
        <div class="grid grid-cols-4 gap-4 mb-4">
        <div class="input-wrapper">
        <!-- <input type="text" id="search-product" placeholder="Search Produit..." class="border px-3 py-2 w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" oninput="filterDropdown('product')"> -->

  <input type="text" id="search-product" placeholder="Search Produit..." name="text" class="input dark:bg-gray-800 dark:text-white dark:placeholder-gray-900" oninput="filterDropdown('product')" >

</div>
<div class="input-wrapper">
        <!-- <input type="text" id="search-product" placeholder="Search Produit..." class="border px-3 py-2 w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" oninput="filterDropdown('product')"> -->

  <input type="text" id="search-supplier" placeholder="Search Fournisseur..." class="input"  oninput="filterDropdown('supplier')">

</div>

            <!-- <input type="text" id="search-product" placeholder="Search Produit..." class="border px-3 py-2 w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" oninput="filterDropdown('product')"> -->
            <!-- <input type="text" id="search-supplier" placeholder="Search Fournisseur..." class="border px-3 py-2 w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" oninput="filterDropdown('supplier')"> -->
        </div>
        <div class="w-96 mx-auto flex items-center gap-3 p-4 bg-white rounded-lg shadow-md">
    <button id="margeConditionBtn" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow-md hover:bg-blue-700 transition-all">
         Condition
    </button>
    <input type="number" id="margeInput" class="w-32 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="MARGE" />
    <input type="color" id="margeColorPicker" class="w-12 h-10 p-1 border border-gray-300 rounded-lg shadow-sm cursor-pointer" />
</div>

        <br>
        
  
        <button id="refresh-btn" class="px-4 py-2 bg-gray-500 text-white rounded-lg shadow-md hover:bg-gray-600 transition duration-200 flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 2a8 8 0 00-8 8H0l3 3 3-3H4a6 6 0 111.757 4.243l1.414 1.414A8 8 0 0010 2z" clip-rule="evenodd"/>
    </svg>
    Refresh
</button>
<br>



        <div class="container">
  <button id="downloadExcel" class="button dark:text-gray-900">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text dark:text-gray-900">
      <span style="transition-duration: 100ms" class="dark:text-gray-900">D</span>
      <span style="transition-duration: 150ms" class="dark:text-gray-900">o</span>
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
     
        <!-- Data Table -->
        <style>
/* General table styles */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto; /* Allow columns to resize dynamically */
}

/* Styles for table cells */
th, td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    /* Removed max-width for resizer to work */
    height: 40px;
}

/* Header styles */
th {
    background-color: #f2f2f2;
    font-weight: bold;
}

/* To avoid horizontal scrolling */
.container {
    overflow-x: auto; /* Adds horizontal scroll if content is wider than container */
}

/* Optional: If you want to allow resizing columns */
th {
    cursor: ew-resize; /* Changes the cursor to indicate resizable columns */
}

/* Zebra striping for alternating rows */
tr:nth-child(even) {
    background-color: #f9f9f9;
}
/* new update */








/* Specific styles for the table with many columns */
.table-container {
    overflow-x: auto;
    margin: 20px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;  /* Makes the table layout more predictable */
}

th, td {
    padding: 8px 12px;  /* Reduces the padding for better fitting */
    text-align: left;
    word-wrap: break-word;
}

/* Smaller header font and making column headers more compact */
th {
    font-size: 0.75rem;  /* Even smaller font for headers */
    font-weight: 600;
    text-align: center;  /* Center align header text */
    padding: 4px 8px;    /* Reduced padding */
}

/* Resizing numeric columns for better alignment */
th[data-column="MARGE"],
th[data-column="QTY"],
th[data-column="QTY_DISPO"],

th[data-column="P_ACHAT"],
th[data-column="REM_ACHAT"],
th[data-column="BON_ACHAT"],
th[data-column="P_REVIENT"],
th[data-column="P_VENTE"],
th[data-column="REM_VENTE"],
th[data-column="BON_VENTE"],
th[data-column="REMISE_AUTO"],
th[data-column="BONUS_AUTO"],
th[data-column="PPA"] {
    width: 60px;  /* Smaller fixed width for number-heavy columns */
    white-space: nowrap;  /* Prevents text wrapping */
}

th[data-column="LOCATION"],
th[data-column="LOT"],
th[data-column="GUARANTEEDATE"] {
    width: 80px;  /* Reduced width for these columns */
}

th[data-column="FOURNISSEUR"],
th[data-column="PRODUCT"] {
    width: 120px;  /* Controlled width for text columns */
}

/* Table rows */
td {
    font-size: 0.75rem;  /* Smaller font for table data */
    text-align: center;  /* Center align numeric data */
    padding: 4px 8px;    /* Reduced padding */
}

/* Alternate row colors for better readability */


/* Resizer divs for resizable columns */
.resizer {
    cursor: ew-resize;
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    background: transparent;
    user-select: none;
    z-index: 1;
}

.resizer:hover {
    background: rgba(0, 0, 0, 0.1);
}

.resizable {
    position: relative;
}

/* Sticky header for long tables */
thead {
    position: sticky;
    top: 0;
    z-index: 2;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    table {
        width: 100%;
        font-size: 0.75rem;  /* Smaller font on smaller screens */
    }

    th, td {
        padding: 6px 8px;  /* Smaller padding */
    }

    th[data-column="MARGE"],
    th[data-column="QTY"],
    th[data-column="QTY_DISPO"],

    th[data-column="P_ACHAT"],
    th[data-column="REM_ACHAT"],
    th[data-column="BON_ACHAT"],
    th[data-column="P_REVIENT"],
    th[data-column="P_VENTE"],
    th[data-column="REM_VENTE"],
    th[data-column="BON_VENTE"],
    th[data-column="REMISE_AUTO"],
    th[data-column="BONUS_AUTO"],
    th[data-column="PPA"] {
        width: 45px;  /* Even smaller width on mobile for numeric columns */
    }

    th[data-column="LOCATION"],
    th[data-column="LOT"],
    th[data-column="GUARANTEEDATE"] {
        width: 60px;  /* Adjusted column widths for mobile */
    }

    th[data-column="FOURNISSEUR"],
    th[data-column="PRODUCT"] {
        width: 80px;  /* Smaller width for text columns on mobile */
    }
}

</style>

        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center font-bold">MARGE Table</h2>

        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
            <th data-column="FOURNISSEUR" onclick="sortTable('FOURNISSEUR')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Fournisseur <div class="resizer"></div>
            </th>
            <th data-column="PRODUCT" onclick="sortTable('PRODUCT')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Produit <div class="resizer"></div>
            </th>
            <th data-column="MARGE" onclick="sortTable('MARGE')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Marge <div class="resizer"></div>
            </th>
            <th data-column="QTY" onclick="sortTable('QTY')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Qty <div class="resizer"></div>
            </th>
            <th data-column="QTY_DISPO" onclick="sortTable('QTY_DISPO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                QTY_DISPO
                <div class="resizer"></div>
            </th>
            <th data-column="P_ACHAT" onclick="sortTable('P_ACHAT')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                P_A <div class="resizer"></div>
            </th>
            <th data-column="REM_ACHAT" onclick="sortTable('REM_ACHAT')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                R_A <div class="resizer"></div>
            </th>
            <th data-column="BON_ACHAT" onclick="sortTable('BON_ACHAT')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                B_A <div class="resizer"></div>
            </th>
            <th data-column="P_REVIENT" onclick="sortTable('P_REVIENT')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                P_R <div class="resizer"></div>
            </th>
            <th data-column="P_VENTE" onclick="sortTable('P_VENTE')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                P_V <div class="resizer"></div>
            </th>
            <th data-column="REM_VENTE" onclick="sortTable('REM_VENTE')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                R_V <div class="resizer"></div>
            </th>
            <th data-column="BON_VENTE" onclick="sortTable('BON_VENTE')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                B_V <div class="resizer"></div>
            </th>
            <th data-column="REMISE_AUTO" onclick="sortTable('REMISE_AUTO')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Rem_A <div class="resizer"></div>
            </th>
            <th data-column="BONUS_AUTO" onclick="sortTable('BONUS_AUTO')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Bo_A <div class="resizer"></div>
            </th>
            <th data-column="PPA" onclick="sortTable('PPA')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                PPA <div class="resizer"></div>
            </th>
            <th data-column="LOCATION" onclick="sortTable('LOCATION')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Location <div class="resizer"></div>
            </th>
            <th data-column="LOT" onclick="sortTable('LOT')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Lot <div class="resizer"></div>
            </th>
            <th data-column="GUARANTEEDATE" onclick="sortTable('GUARANTEEDATE')" class="resizable border border-gray-300 px-2 py-1 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">
                Exp_Date <div class="resizer"></div>
            </th>
        </tr>
    </thead>
    <tbody id="data-table" class="dark:bg-gray-800">
        <!-- Dynamic Rows -->
    </tbody>
</table>

    </div>
</div>
<div class="mt-4 flex justify-center space-x-2" id="pagination"></div>

<script>
    // Apply resizing functionality to all tables with resizable columns
    function initializeColumnResizing() {
        document.querySelectorAll("th.resizable").forEach(function (th, thIndex) {
            // Remove existing resizer if any
            const existingResizer = th.querySelector('.resizer');
            if (existingResizer) {
                existingResizer.remove();
            }

            const resizer = document.createElement("div");
            resizer.classList.add("resizer");
            resizer.style.cssText = `
                position: absolute;
                top: 0;
                right: 0;
                width: 5px;
                height: 100%;
                cursor: ew-resize;
                user-select: none;
                z-index: 1;
            `;
            th.style.position = "relative";
            th.appendChild(resizer);

            resizer.addEventListener("mousedown", function initResize(e) {
                e.preventDefault();
                e.stopPropagation();
                const startX = e.clientX;
                const startWidth = th.offsetWidth;
                function resizeColumn(e) {
                    const newWidth = startWidth + e.clientX - startX;
                    if (newWidth > 50) {
                        th.style.width = newWidth + "px";
                        // Set width for all td in this column
                        const table = th.closest('table');
                        if (table) {
                            Array.from(table.rows).forEach(row => {
                                if (row.cells[th.cellIndex]) {
                                    row.cells[th.cellIndex].style.width = newWidth + "px";
                                }
                            });
                        }
                    }
                }
                function stopResize() {
                    window.removeEventListener("mousemove", resizeColumn);
                    window.removeEventListener("mouseup", stopResize);
                }
                window.addEventListener("mousemove", resizeColumn);
                window.addEventListener("mouseup", stopResize);
            });
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', initializeColumnResizing);
    
    // Re-initialize after data loads (since tables are dynamically populated)
    const originalFetchRemiseData = fetchRemiseData;
    const originalFetchBonusData = fetchBonusData;
    const originalFetchReservedData = fetchReservedData;
    
    fetchRemiseData = async function() {
        await originalFetchRemiseData();
        setTimeout(initializeColumnResizing, 100);
    };
    
    fetchBonusData = async function() {
        await originalFetchBonusData();
        setTimeout(initializeColumnResizing, 100);
    };
    
    fetchReservedData = async function() {
        await originalFetchReservedData();
        setTimeout(initializeColumnResizing, 100);
    };
</script>



<!-- second table remise aauto  -->


        <!-- Pagination -->
        <!--       
        <button id="downloadExcel_REMISE" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Download remise Auto Table</span>
        </button>  -->
        <br>
        <div class="download-wrapper">

    
  <button id="downloadExcel_REMISE" class="buttonn">
        <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />

  <p>Download</p>
  <div class="liquid">
    <span style="--i:0"><span></span></span>
    <span style="--i:1"><span></span></span>
    <span style="--i:2"><span></span></span>
    <span style="--i:3"><span></span></span>
    <span style="--i:4"><span></span></span>
    <span style="--i:5"><span></span></span>
    <span style="--i:6"><span></span></span>
    <span class="bg"><span></span></span>
  </div>
  <svg>
    <filter id="gooey">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10"></feGaussianBlur>
      <feColorMatrix
        values="1 0 0 0 0
          0 1 0 0 0 
          0 0 1 0 0
          0 0 0 20 -10"
      ></feColorMatrix>
    </filter>
  </svg>
</button>
<button id="downloadExcel_BONUS" class="buttonn">
        <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />

  <p>Download</p>
  <div class="liquid">
    <span style="--i:0"><span></span></span>
    <span style="--i:1"><span></span></span>
    <span style="--i:2"><span></span></span>
    <span style="--i:3"><span></span></span>
    <span style="--i:4"><span></span></span>
    <span style="--i:5"><span></span></span>
    <span style="--i:6"><span></span></span>
    <span class="bg"><span></span></span>
  </div>
  <svg>
    <filter id="gooey">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10"></feGaussianBlur>
      <feColorMatrix
        values="1 0 0 0 0
          0 1 0 0 0 
          0 0 1 0 0
          0 0 0 20 -10"
      ></feColorMatrix>
    </filter>
  </svg>
</button>

        </div>


        <!-- <button id="downloadExcel_BONUS" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Download bonus Auto Table</span>
        </button> -->
        <br>
        <div class="title-wrapper">

        </div>
        <div class="table-wrapper">
            <!-- First Table -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">

                <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">REMISE AUTO </h2>


                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="FOURNISSEUR" onclick="sortRemiseTable('FOURNISSEUR')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Fournisseur <div class="resizer"></div>
                                </th>
                                <th data-column="LABORATORY_NAME" onclick="sortRemiseTable('LABORATORY_NAME')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Laboratory Name <div class="resizer"></div>
                                </th>
                                <th data-column="PRODUCT" onclick="sortRemiseTable('PRODUCT')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Produit <div class="resizer"></div>
                                </th>
                                <th data-column="REWARD" onclick="sortRemiseTable('REWARD')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Reward <div class="resizer"></div>
                                </th>
                                <th data-column="TYPE_CLIENT" onclick="sortRemiseTable('TYPE_CLIENT')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Type Client <div class="resizer"></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="remise-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="5" class="text-center p-4">
                                    <div id="lottie-container-a" style="width: 290px; height: 310px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                                            </table>
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
<script>
    // Initialize Lottie animation
// First animation
var loadingAnimation = lottie.loadAnimation({
        container: document.getElementById('lottie-container-a'),  // ID of the first container
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'json_files/load.json'  // Path to your first JSON file
    });

    async function fetchRemiseData() {
        try {
            const response = await fetch(API_CONFIG.getApiUrl('/fetch-remise-data'));
            if (!response.ok) throw new Error('Network response was not ok');

            remiseData = await response.json();

            // Hide loader once data is fetched
            hideLoader();
            updateRemiseTableAndPagination();
        } catch (error) {
            console.error("Error fetching remise data:", error);
        }
    }

    function hideLoader() {
        const loaderRow = document.getElementById('loading-row');
        if (loaderRow) {
            loaderRow.remove();
        }
    }

    // Call the function to fetch data when the page loads
    fetchRemiseData();
</script>



                </div>
                <!-- Pagination for First Table -->
            </div>
   
            <!-- Second Table -->

            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            
                <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">BONUS AUTO </h2>

                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="PRODUCT" onclick="sortBonusTable('PRODUCT')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Product <div class="resizer"></div>
                                </th>
                                <th data-column="BONUS" onclick="sortBonusTable('BONUS')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Bonus <div class="resizer"></div>
                                </th>
                                <th data-column="LABORATORY_NAME" onclick="sortBonusTable('LABORATORY_NAME')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Laboratory Name <div class="resizer"></div>
                                </th>
                                <th data-column="FOURNISSEUR" onclick="sortBonusTable('FOURNISSEUR')" class="resizable border px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                                    Fournisseur <div class="resizer"></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="bonus-table" class="dark:bg-gray-800"></tbody>
                    </table>
                </div>
                <!-- Pagination for Second Table -->
            </div>
        </div>
        <div class="paginatio-wrapper">

        <div class="mt-4 flex justify-center space-x-2" id="pagination-remise"></div>
        <div class="mt-4 flex justify-center space-x-2" id="pagination-bonus"></div>
</div>


<!-- 
<button id="downloadExcel_RESERVE" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
    <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
    <span>Download RESERVED PRODUCTS Table</span>
</button> -->
<br>
<div class="container">

  <button id="downloadExcel_RESERVE" class="buttonn">
        <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />

  <p>Download</p>
  <div class="liquid">
    <span style="--i:0"><span></span></span>
    <span style="--i:1"><span></span></span>
    <span style="--i:2"><span></span></span>
    <span style="--i:3"><span></span></span>
    <span style="--i:4"><span></span></span>
    <span style="--i:5"><span></span></span>
    <span style="--i:6"><span></span></span>
    <span class="bg"><span></span></span>
  </div>
  <svg>
    <filter id="gooey">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10"></feGaussianBlur>
      <feColorMatrix
        values="1 0 0 0 0
          0 1 0 0 0 
          0 0 1 0 0
          0 0 0 20 -10"
      ></feColorMatrix>
    </filter>
  </svg>
</button>
</div>
<br>
<!-- Reserver products  Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
    <div class="overflow-x-auto">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">Produit Réservé </h2>

        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
            <thead>
                <tr class="table-header dark:bg-gray-700">
                    <th data-column="OPERATEUR" onclick="sortReservedTable('OPERATEUR')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        OPERATEUR <div class="resizer"></div>
                    </th>
                    <th data-column="NDOCUMENT" onclick="sortReservedTable('NDOCUMENT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        NDOCUMENT <div class="resizer"></div>
                    </th>
                    <th data-column="PRODUCT" onclick="sortReservedTable('PRODUCT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        PRODUCT <div class="resizer"></div>
                    </th>
                    <th data-column="DATECOMMANDE" onclick="sortReservedTable('DATECOMMANDE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        DATE COMMANDE <div class="resizer"></div>
                    </th>
                    <th data-column="TOTALRESERVE" onclick="sortReservedTable('TOTALRESERVE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        TOTAL RESERVE <div class="resizer"></div>
                    </th>
                    <th data-column="QTYRESERVE" onclick="sortReservedTable('QTYRESERVE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        QTY RESERVE <div class="resizer"></div>
                    </th>
                    <th data-column="NAME" onclick="sortReservedTable('NAME')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        NAME <div class="resizer"></div>
                    </th>
                    <th data-column="STATUS" onclick="sortReservedTable('STATUS')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                        STATUS <div class="resizer"></div>
                    </th>
                </tr>
            </thead>
            <tbody id="reserved-table" class="dark:bg-gray-800">
                <!-- Dynamic Rows -->
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-4 flex justify-center space-x-2" id="pagination-reserved"></div>


<br>
<div class="chart-controls mb-4 p-4 bg-gray-100 rounded-lg dark:bg-gray-700">
    <h2 class="text-lg font-semibold mb-4 dark:text-white">Chart Visualization</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <!-- Filter Selection -->
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Filter By:</label>
            <div class="relative">
                <select id="chartFilterType" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    <option value="FOURNISSEUR">Fournisseur</option>
                    <option value="LABO">Labo</option>
                    <option value="PRODUCT">Product</option>
                </select>
            </div>
        </div>
        
        <!-- Value Selection with Search - Improved Dropdown -->
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Select Value:</label>
            <div class="relative">
                <input type="text" id="filterValueSearch" placeholder="Search..." 
                       class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                       autocomplete="off">
                <div id="filterValueDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg dark:bg-gray-800 dark:border-gray-600 hidden max-h-60 overflow-y-auto">
                    <!-- Options will be populated dynamically -->
                </div>
            </div>
        </div>
        
        <!-- Metric Toggle -->
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Toggle Metrics:</label>
          
            <div id="metricToggles" class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-1">
                <!-- Metric toggles will be added here dynamically -->
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <canvas id="dataChart" class="w-full h-96"></canvas>
    </div>
</div>
<script >

let currentPage = 1;
const rowsPerPage = 10;
let allData = [];
let filters = {
    product: '',
    supplier: '',
    lot: '',
    margin: '',
    lab: ''
};
let sortColumn = '';
let sortDirection = 'asc';

// Fetch data on page load
window.onload = () => {
    fetchData();
    fetchRemiseData();
    fetchBonusData();
    fetchReservedData();
};
document.getElementById("refresh-btn").addEventListener("click", function() {
        // Store current input values
        const productSearchValue = document.getElementById("search-product").value;
        const supplierSearchValue = document.getElementById("search-supplier").value;

        // Fetch new data
        fetchData();
        fetchRemiseData();
        fetchBonusData();
        fetchReservedData();

        // Restore input values after data refresh
        setTimeout(() => {
            document.getElementById("search-product").value = productSearchValue;
            document.getElementById("search-supplier").value = supplierSearchValue;

            // Re-trigger filtering to apply search after refresh
            filterDropdown('product');
            filterDropdown('supplier');
        }, 100);
    });



document.getElementById("downloadExcel").addEventListener("click", function () {
    let fournisseur = document.getElementById("search-supplier").value;
    let product = document.getElementById("search-product").value;
    let marge = document.getElementById("margeInput").value;

    let url = API_CONFIG.getApiUrl(`/download-marge-excel?fournisseur=${encodeURIComponent(fournisseur)}&product=${encodeURIComponent(product)}&marge=${encodeURIComponent(marge)}`);
    window.open(url, "_blank");
});

document.getElementById("downloadExcel_REMISE").addEventListener("click", function () {
    window.open(API_CONFIG.getApiUrl('/download-remise-excel'), "_blank"); 
});
document.getElementById("downloadExcel_BONUS").addEventListener("click", function () {
    window.open(API_CONFIG.getApiUrl('/download-bonus-excel'), "_blank"); 
});
document.getElementById("downloadExcel_RESERVE").addEventListener("click", function () {
    window.open(API_CONFIG.getApiUrl('/download-reserved-excel'), "_blank"); 
});


let dataChart = null;
const availableMetrics = [
    { id: 'P_VENTE', name: 'Prix Vente', color: 'rgba(54, 162, 235, 0.7)' },
    { id: 'P_ACHAT', name: 'Prix Achat', color: 'rgba(255, 99, 132, 0.7)' },
    { id: 'MARGE', name: 'Marge', color: 'rgba(75, 192, 192, 0.7)' },
    { id: 'P_REVIENT', name: 'Prix Revient', color: 'rgba(255, 159, 64, 0.7)' },
    { id: 'REM_ACHAT', name: 'Remise Achat', color: 'rgba(153, 102, 255, 0.7)' },
    { id: 'REM_VENTE', name: 'Remise Vente', color: 'rgba(255, 205, 86, 0.7)' },
    { id: 'PPA', name: 'PPA', color: 'rgba(255, 102, 0, 0.7)' }
];
const activeMetrics = new Set(availableMetrics.map(m => m.id));
let currentFilterValue = '';

// Initialize chart controls
function initChartControls() {
    const filterTypeSelect = document.getElementById('chartFilterType');
    const filterValueSearch = document.getElementById('filterValueSearch');
    const filterValueDropdown = document.getElementById('filterValueDropdown');
    const metricSearch = document.getElementById('metricSearch');
    
    // Create metric toggle buttons
    setupMetricToggles();
    
    // Set up search functionality for filter values
    filterValueSearch.addEventListener('focus', () => {
        filterValueDropdown.classList.remove('hidden');
        updateFilterValueOptions(filterTypeSelect.value);
    });
    
    filterValueSearch.addEventListener('blur', () => {
        // Small timeout to allow click events to register
        setTimeout(() => {
            filterValueDropdown.classList.add('hidden');
        }, 200);
    });
    
    filterValueSearch.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const options = filterValueDropdown.querySelectorAll('.dropdown-option');
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Set up search functionality for metrics
    metricSearch.addEventListener('input', () => {
        const searchTerm = metricSearch.value.toLowerCase();
        const metricButtons = document.querySelectorAll('#metricToggles button');
        
        metricButtons.forEach(button => {
            const metricName = button.textContent.toLowerCase();
            button.style.display = metricName.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Function to handle all changes that should trigger chart update
    const handleChange = () => {
        updateFilterValueOptions(filterTypeSelect.value);
        generateChart();
    };
    
    // Set up event listeners
    filterTypeSelect.addEventListener('change', handleChange);
    
    // Initial population and chart generation
    updateFilterValueOptions(filterTypeSelect.value);
    generateChart();
}

// Set up metric toggle buttons
function setupMetricToggles() {
    const container = document.getElementById('metricToggles');
    container.innerHTML = '';
    
    availableMetrics.forEach(metric => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `px-3 py-1 rounded-full text-xs font-medium ${activeMetrics.has(metric.id) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-white'}`;
        button.textContent = metric.name;
        button.style.borderColor = metric.color.replace('0.7', '1');
        button.style.borderWidth = '2px';
        button.dataset.metric = metric.id;
        
        button.addEventListener('click', () => {
            if (activeMetrics.has(metric.id)) {
                activeMetrics.delete(metric.id);
                button.classList.remove('bg-blue-500', 'text-white');
                button.classList.add('bg-gray-200', 'text-gray-800', 'dark:bg-gray-600', 'dark:text-white');
            } else {
                activeMetrics.add(metric.id);
                button.classList.add('bg-blue-500', 'text-white');
                button.classList.remove('bg-gray-200', 'text-gray-800', 'dark:bg-gray-600', 'dark:text-white');
            }
            generateChart();
        });
        
        container.appendChild(button);
    });
}

// Update the filter value dropdown
function updateFilterValueOptions(filterType) {
    const filterValueDropdown = document.getElementById('filterValueDropdown');
    const filterValueSearch = document.getElementById('filterValueSearch');
    
    filterValueDropdown.innerHTML = '';
    
    const uniqueValues = [...new Set(allData.map(item => item[filterType]))];
    
    uniqueValues.forEach(value => {
        if (value) {
            const option = document.createElement('div');
            option.className = 'dropdown-option p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer';
            option.textContent = value;
            
            option.addEventListener('click', () => {
                currentFilterValue = value;
                filterValueSearch.value = value;
                filterValueDropdown.classList.add('hidden');
                generateChart();
            });
            
            filterValueDropdown.appendChild(option);
        }
    });
}

// Generate the chart with selected metrics
function generateChart() {
    if (!allData || allData.length === 0 || activeMetrics.size === 0) {
        if (dataChart) {
            dataChart.destroy();
            dataChart = null;
        }
        return;
    }
    
    const filterType = document.getElementById('chartFilterType').value;
    const filterValue = currentFilterValue;
    
    if (!filterValue) return;
    
    const filteredData = allData.filter(item => item[filterType] === filterValue);
    if (filteredData.length === 0) {
        if (dataChart) {
            dataChart.destroy();
            dataChart = null;
        }
        return;
    }
    
    const labels = filteredData.map(item => item.PRODUCT);
    const ctx = document.getElementById('dataChart').getContext('2d');
    
    if (dataChart) dataChart.destroy();
    
    // Create datasets for each active metric
    const datasets = availableMetrics
        .filter(metric => activeMetrics.has(metric.id))
        .map(metric => ({
            label: `${metric.name} for ${filterValue}`,
            data: filteredData.map(item => parseFloat(item[metric.id]) || 0),
            backgroundColor: metric.color,
            borderColor: metric.color.replace('0.7', '1'),
            borderWidth: 1
        }));
    
    dataChart = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: context => `${context.dataset.label}: ${context.raw.toFixed(2)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => value.toFixed(2) }
                }
            }
        }
    });
}

// Fetch data function
async function fetchData() {
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/fetch-data'));
        if (!response.ok) throw new Error('Network response was not ok');

        allData = await response.json();
        updateTableAndPagination();
        initChartControls();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}



function filterDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filters[type] = searchValue;
    currentPage = 1;
    updateTableAndPagination();
}

function filterData(data) {
    return data.filter(row => {
        return (!filters.product || row.PRODUCT.toLowerCase().includes(filters.product)) &&
               (!filters.supplier || row.FOURNISSEUR.toLowerCase().includes(filters.supplier)); 

    });
}

function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const textNode = th.querySelector('span');
        if (textNode) {
            textNode.textContent = textNode.textContent.replace(/ ↑| ↓/g, '');
        }
    });

    // Add arrow to current sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        let textNode = currentHeader.querySelector('span');
        if (textNode) {
            const arrow = sortDirection === 'asc' ? ' ↑' : ' ↓';
            textNode.textContent += arrow;
        }
    }

    updateTableAndPagination();
}


function updateTableAndPagination() {
    renderTablePage(currentPage);
    renderPagination();
}

let margeValue = '';  // Default value
let margeColor = '#ffffff'; // Default color

// Add event listener for Marge Condition button and color picker
document.getElementById('margeConditionBtn').addEventListener('click', () => {
    margeValue = parseFloat(document.getElementById('margeInput').value); // Get the entered MARGE value as a number
    margeColor = document.getElementById('margeColorPicker').value; // Get the selected color
    updateTableAndPagination(); // Re-render the table with the new MARGE value and color
});

// Update the table rendering logic
document.getElementById("downloadExcel").addEventListener("click", function () {
    let table = document.getElementById("data-table");
    let wb = XLSX.utils.book_new(); // Create a new workbook

    // Convert the HTML table to a worksheet
    let ws = XLSX.utils.table_to_sheet(table);

    // Rename and set headers (simulating a pivot table header)
    ws["A1"].v = "Supplier (Fournisseur)";
    ws["B1"].v = "Product";
    ws["C1"].v = "Purchase Price (P_ACHAT)";
    ws["D1"].v = "Selling Price (P_VENTE)";
    ws["E1"].v = "Discount Purchase (REM_ACHAT)";
    ws["F1"].v = "Discount Sale (REM_VENTE)";
    ws["G1"].v = "Purchase Bonus (BON_ACHAT)";
    ws["H1"].v = "Sale Bonus (BON_VENTE)";
    ws["I1"].v = "Auto Discount (REMISE_AUTO)";
    ws["J1"].v = "Auto Bonus (BONUS_AUTO)";
    ws["K1"].v = "Cost Price (P_REVIENT)";
    ws["L1"].v = "Margin (MARGE)";
    ws["M2"].v = "Laboratory (LABO)";
    ws["N1"].v = "Batch (LOT)";
    ws["O1"].v = "Quantity (QTY)";
    ws["P1"].v = "Guaranteed Date (GUARANTEEDATE)";  // New column header

    // Add the worksheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Filtered Data");

    // Save the file
    XLSX.writeFile(wb, "Filtered_Data.xlsx");
});

function renderTablePage(page) {
    let filteredData = filterData(allData);

    // Sort data
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

        // Check if the "MARGE" value is less than the entered value
        const marge = parseFloat(row.MARGE); // Ensure we're comparing numbers
        if (margeValue && !isNaN(marge) && marge < margeValue) {
            tr.style.backgroundColor = margeColor;  // Apply the color to the entire row
        }
        const formatNumber = (num, isInt = false) => {
            if (num === null || num === undefined || num === "") return 0;
            if (isInt) return parseInt(num, 10).toLocaleString('en-US');
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        // Format the GUARANTEEDATE value to DD/MM/YYYY
        let guaranteedDate = row.GUARANTEEDATE ? new Date(row.GUARANTEEDATE) : null;
        let formattedDate = guaranteedDate ? guaranteedDate.toLocaleDateString('en-GB') : '';  // Format as DD/MM/YYYY

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
                <td class="border px-4 py-2 dark:border-gray-600">${formattedDate || ''}</td>
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

    // First and Previous buttons
    const firstPageButton = createPageButton("First", 1);
    const prevPageButton = createPageButton("<", currentPage - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPage;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createPageButton(">", currentPage + 1);
    const lastPageButton = createPageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}




let currentPageRemise = 1;
const rowsPerPageRemise = 10;
let remiseData = [];
let filtersRemise = {
    fournisseur: '',
    laboratoryName: '',
    product: '',
    reward: '',
    typeClient: ''
};
let sortColumnRemise = '';
let sortDirectionRemise = 'asc';

// Fetch data for the second table (remise)

async function fetchRemiseData() {
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/fetch-remise-data'));
        if (!response.ok) throw new Error('Network response was not ok');

        remiseData = await response.json();
        updateRemiseTableAndPagination();
    } catch (error) {
        console.error("Error fetching remise data:", error);
    }
}

function filterRemiseDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filtersRemise[type] = searchValue;
    currentPageRemise = 1;
    updateRemiseTableAndPagination();
}

function filterRemiseData(data) {
    return data.filter(row => {
        return (!filtersRemise.fournisseur || row.FOURNISSEUR.toLowerCase().includes(filtersRemise.fournisseur)) &&
               (!filtersRemise.laboratoryName || row.LABORATORY_NAME.toLowerCase().includes(filtersRemise.laboratoryName)) &&
               (!filtersRemise.product || row.PRODUCT.toLowerCase().includes(filtersRemise.product)) &&
               (!filtersRemise.reward || row.REWARD.toLowerCase().includes(filtersRemise.reward)) &&
               (!filtersRemise.typeClient || row.TYPE_CLIENT.toLowerCase().includes(filtersRemise.typeClient));
    });
}

function sortRemiseTable(column) {
    if (sortColumnRemise === column) {
        sortDirectionRemise = sortDirectionRemise === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnRemise = column;
        sortDirectionRemise = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });

    // Add arrow to current sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirectionRemise === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }

    updateRemiseTableAndPagination();
}

function updateRemiseTableAndPagination() {
    renderRemiseTablePage(currentPageRemise);
    renderRemisePagination();
}

function renderRemiseTablePage(page) {
    let filteredData = filterRemiseData(remiseData);

    // Sort data
    if (sortColumnRemise) {
        filteredData.sort((a, b) => {
            if (a[sortColumnRemise] < b[sortColumnRemise]) return sortDirectionRemise === 'asc' ? -1 : 1;
            if (a[sortColumnRemise] > b[sortColumnRemise]) return sortDirectionRemise === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPageRemise;
    const end = start + rowsPerPageRemise;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("remise-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LABORATORY_NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.REWARD || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.TYPE_CLIENT || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function createRemisePageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterRemiseData(remiseData).length / rowsPerPageRemise);
    button.addEventListener("click", () => {
        currentPageRemise = pageNumber;
        updateRemiseTableAndPagination();
    });
    return button;
}

function renderRemisePagination() {
    const filteredData = filterRemiseData(remiseData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPageRemise);
    const paginationContainer = document.getElementById("pagination-remise");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createRemisePageButton("First", 1);
    const prevPageButton = createRemisePageButton("<", currentPageRemise - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPageRemise;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createRemisePageButton(">", currentPageRemise + 1);
    const lastPageButton = createRemisePageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

let currentPageBonus = 1;
const rowsPerPageBonus = 10;
let bonusData = [];
let filtersBonus = {
    product: '',
    bonus: '',
    laboratoryName: '',
    fournisseur: ''
};
let sortColumnBonus = '';
let sortDirectionBonus = 'asc';

// Fetch data for the bonus table
async function fetchBonusData() {
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/fetch-bonus-data'));
        if (!response.ok) throw new Error('Network response was not ok');

        bonusData = await response.json();
        updateBonusTableAndPagination();
    } catch (error) {
        console.error("Error fetching bonus data:", error);
    }
}

function filterBonusDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filtersBonus[type] = searchValue;
    currentPageBonus = 1;
    updateBonusTableAndPagination();
}

function filterBonusData(data) {
    return data.filter(row => {
        return (!filtersBonus.product || row.PRODUCT.toLowerCase().includes(filtersBonus.product)) &&
               (!filtersBonus.bonus || row.BONUS.toLowerCase().includes(filtersBonus.bonus)) &&
               (!filtersBonus.laboratoryName || row.LABORATORY_NAME.toLowerCase().includes(filtersBonus.laboratoryName)) &&
               (!filtersBonus.fournisseur || row.FOURNISSEUR.toLowerCase().includes(filtersBonus.fournisseur));
    });
}

function sortBonusTable(column) {
    if (sortColumnBonus === column) {
        sortDirectionBonus = sortDirectionBonus === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnBonus = column;
        sortDirectionBonus = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });

    // Add arrow to current sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirectionBonus === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }

    updateBonusTableAndPagination();
}

function updateBonusTableAndPagination() {
    renderBonusTablePage(currentPageBonus);
    renderBonusPagination();
}

function renderBonusTablePage(page) {
    let filteredData = filterBonusData(bonusData);

    // Sort data
    if (sortColumnBonus) {
        filteredData.sort((a, b) => {
            if (a[sortColumnBonus] < b[sortColumnBonus]) return sortDirectionBonus === 'asc' ? -1 : 1;
            if (a[sortColumnBonus] > b[sortColumnBonus]) return sortDirectionBonus === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPageBonus;
    const end = start + rowsPerPageBonus;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("bonus-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.BONUS || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LABORATORY_NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function createBonusPageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterBonusData(bonusData).length / rowsPerPageBonus);
    button.addEventListener("click", () => {
        currentPageBonus = pageNumber;
        updateBonusTableAndPagination();
    });
    return button;
}

function renderBonusPagination() {
    const filteredData = filterBonusData(bonusData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPageBonus);
    const paginationContainer = document.getElementById("pagination-bonus");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createBonusPageButton("First", 1);
    const prevPageButton = createBonusPageButton("<", currentPageBonus - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPageBonus;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createBonusPageButton(">", currentPageBonus + 1);
    const lastPageButton = createBonusPageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

// Reserved Products Table Script

let reservedData = [];
let filtersReserved = {};
let sortColumnReserved = null;
let sortDirectionReserved = 'asc';
let currentPageReserved = 1;
const rowsPerPageReserved = 10;

// Fetch data for the reserved table
async function fetchReservedData() {
    try {
        const response = await fetch(API_CONFIG.getApiUrl('/fetch-reserved-data'));
        if (!response.ok) throw new Error('Network response was not ok');

        reservedData = await response.json();
        updateReservedTableAndPagination();
    } catch (error) {
        console.error("Error fetching reserved data:", error);
    }
}

// Filter function for the reserved table
function filterReservedData(data) {
    return data.filter(row => {
        return (!filtersReserved.operateur || row.OPERATEUR.toLowerCase().includes(filtersReserved.operateur)) &&
               (!filtersReserved.ndocument || row.NDOCUMENT.toLowerCase().includes(filtersReserved.ndocument)) &&
               (!filtersReserved.product || row.PRODUCT.toLowerCase().includes(filtersReserved.product)) &&
               (!filtersReserved.datecommande || row.DATECOMMANDE.toLowerCase().includes(filtersReserved.datecommande)) &&
               (!filtersReserved.totalreserve || row.TOTALRESERVE.toString().includes(filtersReserved.totalreserve)) &&
               (!filtersReserved.qtyreserve || row.QTYRESERVE.toString().includes(filtersReserved.qtyreserve)) &&
               (!filtersReserved.name || row.NAME.toLowerCase().includes(filtersReserved.name)) &&
               (!filtersReserved.status || row.STATUS.toLowerCase().includes(filtersReserved.status));
    });
}

// Sorting function
function sortReservedTable(column) {
    if (sortColumnReserved === column) {
        sortDirectionReserved = sortDirectionReserved === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnReserved = column;
        sortDirectionReserved = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });

    // Add arrow to sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirectionReserved === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }

    updateReservedTableAndPagination();
}

// Update the table and pagination
function updateReservedTableAndPagination() {
    renderReservedTablePage(currentPageReserved);
    renderReservedPagination();
}

// Render a page of reserved data
function renderReservedTablePage(page) {
    let filteredData = filterReservedData(reservedData);

    // Sorting logic
    if (sortColumnReserved) {
        filteredData.sort((a, b) => {
            if (a[sortColumnReserved] < b[sortColumnReserved]) return sortDirectionReserved === 'asc' ? -1 : 1;
            if (a[sortColumnReserved] > b[sortColumnReserved]) return sortDirectionReserved === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPageReserved;
    const end = start + rowsPerPageReserved;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("reserved-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.OPERATEUR || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.NDOCUMENT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">
    ${formatDate(row.DATECOMMANDE)}
</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.TOTALRESERVE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTYRESERVE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.STATUS || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}
function formatDate(dateString) {
    if (!dateString) return ''; // Return an empty string if no date provided

    const date = new Date(dateString);
    
    // Format the date as 'Wed, 26 Mar 2025'
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options); // 'en-GB' for British date format
}

// Create pagination buttons
function createReservedPageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterReservedData(reservedData).length / rowsPerPageReserved);
    button.addEventListener("click", () => {
        currentPageReserved = pageNumber;
        updateReservedTableAndPagination();
    });
    return button;
}

// Render pagination for the reserved table
function renderReservedPagination() {
    const filteredData = filterReservedData(reservedData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPageReserved);
    const paginationContainer = document.getElementById("pagination-reserved");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createReservedPageButton("First", 1);
    const prevPageButton = createReservedPageButton("<", currentPageReserved - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPageReserved;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createReservedPageButton(">", currentPageReserved + 1);
    const lastPageButton = createReservedPageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

// Call fetch function when the page loads
document.addEventListener("DOMContentLoaded", fetchReservedData);



</script>


</body>
</html>