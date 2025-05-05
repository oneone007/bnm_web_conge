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
// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['username']) && in_array($_SESSION['username'], ['yasser'])) {
  header("Location: Acess_Denied");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en" >
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


 
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->


    <!-- Dark/Light Mode Toggle Button -->

  <!-- Dark Mode Toggle (Top Right) -->
<!-- From Uiverse.io by Galahhad --> 
<div class="theme-switch-wrapper">
  <label class="theme-switch">
    <input type="checkbox" class="theme-switch__checkbox" id="themeToggle">
    <div class="theme-switch__container">
      <div class="theme-switch__clouds"></div>
      <div class="theme-switch__stars-container">
        <!-- Stars SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 144 55" fill="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M135.831 3.00688C135.055 3.85027 134.111 4.29946 133 4.35447C134.111 4.40947 135.055 4.85867 135.831 5.71123C136.607 6.55462 136.996 7.56303 136.996 8.72727C136.996 7.95722 137.172 7.25134 137.525 6.59129C137.886 5.93124 138.372 5.39954 138.98 5.00535C139.598 4.60199 140.268 4.39114 141 4.35447C139.88 4.2903 138.936 3.85027 138.16 3.00688C137.384 2.16348 136.996 1.16425 136.996 0C136.996 1.16425 136.607 2.16348 135.831 3.00688ZM31 23.3545C32.1114 23.2995 33.0551 22.8503 33.8313 22.0069C34.6075 21.1635 34.9956 20.1642 34.9956 19C34.9956 20.1642 35.3837 21.1635 36.1599 22.0069C36.9361 22.8503 37.8798 23.2903 39 23.3545C38.2679 23.3911 37.5976 23.602 36.9802 24.0053C36.3716 24.3995 35.8864 24.9312 35.5248 25.5913C35.172 26.2513 34.9956 26.9572 34.9956 27.7273C34.9956 26.563 34.6075 25.5546 33.8313 24.7112C33.0551 23.8587 32.1114 23.4095 31 23.3545Z" fill="currentColor"></path>
        </svg>
      </div>
      <div class="theme-switch__circle-container">
        <div class="theme-switch__sun-moon-container">
          <div class="theme-switch__moon">
            <div class="theme-switch__spot"></div>
            <div class="theme-switch__spot"></div>
            <div class="theme-switch__spot"></div>
          </div>
        </div>
      </div>
    </div>
  </label>
</div>




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


<div id="sidebar-container"></div>

<script>
fetch("side")
  .then(response => response.text())
  .then(html => {
    const container = document.getElementById("sidebar-container");
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    container.innerHTML = tempDiv.innerHTML;

    // After DOM injection, dynamically load sidebar script
    const script = document.createElement('script');
    script.src = 'sid.js'; // Move all logic into sid.js
    document.body.appendChild(script);
  })
  .catch(error => console.error("Error loading sidebar:", error));


</script>


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

  <input type="text"  id="search-product" placeholder="Search Produit..." name="text" class="input"  oninput="filterDropdown('product')" >

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
        <!-- <button id="downloadExcel" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Download Marge Table</span>
        </button> -->
        <!-- <div class="container">

        <button id="downloadExcel" class="buttonn">
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

</div> -->



        <div class="container">
  <button id="downloadExcel" class="button">
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
     
        <!-- Data Table -->
        <style>
/* General table styles */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto; /* Allows columns to resize based on content */
}

/* Styles for table cells */
th, td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
    word-wrap: break-word; /* Allows text to wrap onto multiple lines */
    white-space: normal; /* Allows automatic wrapping for long text */
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
    font-size: 0.875rem;  /* Smaller font for headers */
    font-weight: 600;
    text-align: center;  /* Center align header text */
}

/* Resizing numeric columns for better alignment */
th[data-column="MARGE"],
th[data-column="QTY"],
th[data-column="P_ACHAT"],
th[data-column="REM_ACHAT"],
th[data-column="BON_ACHAT"],
th[data-column="P_REVIENT"],
th[data-column="P_VENTE"],
th[data-column="REM_VENTE"],
th[data-column="BON_VENTE"],
th[data-column="REMISE_AUTO"],
th[data-column="BONUS_AUTO"] {
    width: 80px;  /* Fixed width for number-heavy columns */
    white-space: nowrap;  /* Prevents text wrapping */
}

th[data-column="LOCATION"],
th[data-column="LOT"],
th[data-column="GUARANTEEDATE"] {
    width: 120px;  /* Slightly larger width for these columns */
}

/* Table rows */
td {
    font-size: 0.875rem;  /* Smaller font for table data */
    text-align: center;  /* Center align numeric data */
}

/* Alternate row colors for better readability */


/* Resizer divs for resizable columns */
.resizer {
    cursor: ew-resize;
}

/* Sticky header for long tables */
thead {
    position: sticky;
    top: 0;
    z-index: 1;
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
    th[data-column="P_ACHAT"],
    th[data-column="REM_ACHAT"],
    th[data-column="BON_ACHAT"],
    th[data-column="P_REVIENT"],
    th[data-column="P_VENTE"],
    th[data-column="REM_VENTE"],
    th[data-column="BON_VENTE"],
    th[data-column="REMISE_AUTO"],
    th[data-column="BONUS_AUTO"] {
        width: 60px;  /* Smaller width on mobile for numeric columns */
    }

    th[data-column="LOCATION"],
    th[data-column="LOT"],
    th[data-column="GUARANTEEDATE"] {
        width: 100px;  /* Adjusted column widths for mobile */
    }
}

</style>

        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">MARGE Table</h2>

        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
            <th data-column="FOURNISSEUR" onclick="sortTable('FOURNISSEUR')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Fournisseur <div class="resizer"></div>
            </th>
            <th data-column="PRODUCT" onclick="sortTable('PRODUCT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Produit <div class="resizer"></div>
            </th>
            <th data-column="MARGE" onclick="sortTable('MARGE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                MARGE <div class="resizer"></div>
            </th>
            <th data-column="QTY" onclick="sortTable('QTY')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                QTY <div class="resizer"></div>
            </th>
            <th data-column="P_ACHAT" onclick="sortTable('P_ACHAT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                P_Achat <div class="resizer"></div>
            </th>
            <th data-column="REM_ACHAT" onclick="sortTable('REM_ACHAT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                REM_ACHAT <div class="resizer"></div>
            </th>
            <th data-column="BON_ACHAT" onclick="sortTable('BON_ACHAT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                BON_ACHAT <div class="resizer"></div>
            </th>
            <th data-column="P_REVIENT" onclick="sortTable('P_REVIENT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                P_REVIENT <div class="resizer"></div>
            </th>
            <th data-column="P_VENTE" onclick="sortTable('P_VENTE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                P_Vente <div class="resizer"></div>
            </th>
            <th data-column="REM_VENTE" onclick="sortTable('REM_VENTE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                REM_VENTE <div class="resizer"></div>
            </th>
            <th data-column="BON_VENTE" onclick="sortTable('BON_VENTE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                BON_VENTE <div class="resizer"></div>
            </th>
            <th data-column="REMISE_AUTO" onclick="sortTable('REMISE_AUTO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                REMISE_AUTO <div class="resizer"></div>
            </th>
            <th data-column="BONUS_AUTO" onclick="sortTable('BONUS_AUTO')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                BONUS_AUTO <div class="resizer"></div>
            </th>
            <th data-column="LOCATION" onclick="sortTable('LOCATION')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                LOCATION <div class="resizer"></div>
            </th>
            <th data-column="LOT" onclick="sortTable('LOT')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                LOT <div class="resizer"></div>
            </th>
            <!-- New Guaranteedate Column -->
            <th data-column="GUARANTEEDATE" onclick="sortTable('GUARANTEEDATE')" class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Date EXP <div class="resizer"></div>
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
    document.querySelectorAll("th.resizable").forEach(function (th) {
        const resizer = document.createElement("div");
        resizer.classList.add("resizer");
        th.appendChild(resizer);

        resizer.addEventListener("mousedown", function initResize(e) {
            e.preventDefault();
            window.addEventListener("mousemove", resizeColumn);
            window.addEventListener("mouseup", stopResize);

            function resizeColumn(e) {
                const newWidth = e.clientX - th.getBoundingClientRect().left;
                th.style.width = newWidth + "px";
            }

            function stopResize() {
                window.removeEventListener("mousemove", resizeColumn);
                window.removeEventListener("mouseup", stopResize);
            }
        });
    });
</script>



<!-- second table remise aauto  -->
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
                                <th data-column="FOURNISSEUR" onclick="sortRemiseTable('FOURNISSEUR')" class="border px-4 py-2">Fournisseur</th>
                                <th data-column="LABORATORY_NAME" onclick="sortRemiseTable('LABORATORY_NAME')" class="border px-4 py-2">Laboratory Name</th>
                                <th data-column="PRODUCT" onclick="sortRemiseTable('PRODUCT')" class="border px-4 py-2">Produit</th>
                                <th data-column="REWARD" onclick="sortRemiseTable('REWARD')" class="border px-4 py-2">Reward</th>
                                <th data-column="TYPE_CLIENT" onclick="sortRemiseTable('TYPE_CLIENT')" class="border px-4 py-2">Type Client</th>
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
            const response = await fetch('http://192.168.1.94:5000/fetch-remise-data');
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
                                <th data-column="PRODUCT" onclick="sortBonusTable('PRODUCT')" class="border px-4 py-2">Product</th>
                                <th data-column="BONUS" onclick="sortBonusTable('BONUS')" class="border px-4 py-2">Bonus</th>
                                <th data-column="LABORATORY_NAME" onclick="sortBonusTable('LABORATORY_NAME')" class="border px-4 py-2">Laboratory Name</th>
                                <th data-column="FOURNISSEUR" onclick="sortBonusTable('FOURNISSEUR')" class="border px-4 py-2">Fournisseur</th>
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
                    <th data-column="OPERATEUR" onclick="sortReservedTable('OPERATEUR')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">OPERATEUR</th>
                    <th data-column="NDOCUMENT" onclick="sortReservedTable('NDOCUMENT')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">NDOCUMENT</th>
                    <th data-column="PRODUCT" onclick="sortReservedTable('PRODUCT')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">PRODUCT</th>
                    <th data-column="DATECOMMANDE" onclick="sortReservedTable('DATECOMMANDE')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">DATE COMMANDE</th>
                    <th data-column="TOTALRESERVE" onclick="sortReservedTable('TOTALRESERVE')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">TOTAL RESERVE</th>
                    <th data-column="QTYRESERVE" onclick="sortReservedTable('QTYRESERVE')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">QTY RESERVE</th>
                    <th data-column="NAME" onclick="sortReservedTable('NAME')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">NAME</th>
                    <th data-column="STATUS" onclick="sortReservedTable('STATUS')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">STATUS</th>
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

    let url = `http://192.168.1.94:5000/download-marge-excel?fournisseur=${encodeURIComponent(fournisseur)}&product=${encodeURIComponent(product)}&marge=${encodeURIComponent(marge)}`;
    window.open(url, "_blank");
});

document.getElementById("downloadExcel_REMISE").addEventListener("click", function () {
    window.open("http://192.168.1.94:5000/download-remise-excel", "_blank"); 
});
document.getElementById("downloadExcel_BONUS").addEventListener("click", function () {
    window.open("http://192.168.1.94:5000/download-bonus-excel", "_blank"); 
});
document.getElementById("downloadExcel_RESERVE").addEventListener("click", function () {
    window.open("http://192.168.1.94:5000/download-reserved-excel", "_blank"); 
});


let dataChart = null;
const availableMetrics = [
    { id: 'P_VENTE', name: 'Prix Vente', color: 'rgba(54, 162, 235, 0.7)' },
    { id: 'P_ACHAT', name: 'Prix Achat', color: 'rgba(255, 99, 132, 0.7)' },
    { id: 'MARGE', name: 'Marge', color: 'rgba(75, 192, 192, 0.7)' },
    { id: 'P_REVIENT', name: 'Prix Revient', color: 'rgba(255, 159, 64, 0.7)' },
    { id: 'REM_ACHAT', name: 'Remise Achat', color: 'rgba(153, 102, 255, 0.7)' },
    { id: 'REM_VENTE', name: 'Remise Vente', color: 'rgba(255, 205, 86, 0.7)' }
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
        const response = await fetch('http://192.168.1.94:5000/fetch-data');
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

        // Format the GUARANTEEDATE value to DD/MM/YYYY
        let guaranteedDate = row.GUARANTEEDATE ? new Date(row.GUARANTEEDATE) : null;
        let formattedDate = guaranteedDate ? guaranteedDate.toLocaleDateString('en-GB') : '';  // Format as DD/MM/YYYY

        tr.innerHTML = `
<td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.MARGE || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.QTY || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.P_ACHAT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.REM_ACHAT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.BON_ACHAT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.P_REVIENT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.P_VENTE || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.REM_VENTE || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.BON_VENTE || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.REMISE_AUTO || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.BONUS_AUTO || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.LOCATION || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${row.LOT || ''}</td>
<td class="border px-4 py-2 dark:border-gray-600">${formattedDate || ''}</td> <!-- New column data formatted -->
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




// Dark/Light Mode Toggle Functionality
const themeToggle = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

themeToggle.addEventListener('click', () => {
    htmlElement.classList.toggle('dark');
    // Save the theme preference in localStorage
    const isDarkMode = htmlElement.classList.contains('dark');
    localStorage.setItem('darkMode', isDarkMode);
});

// Check for saved theme preference
const savedDarkMode = localStorage.getItem('darkMode');
if (savedDarkMode === 'true') {
    htmlElement.classList.add('dark');
} else {
    htmlElement.classList.remove('dark');
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
        const response = await fetch('http://192.168.1.94:5000/fetch-remise-data');
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
        const response = await fetch('http://192.168.1.94:5000/fetch-bonus-data');
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
        const response = await fetch('http://192.168.1.94:5000/fetch-reserved-data');
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