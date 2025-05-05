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
    <title>Quota</title>
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
            PRODUIT QUOTA 
            </h1>
        </div>
      



        <!-- Filters -->
        <div class="grid grid-cols-4 gap-4 mb-4">
        <div class="input-wrapper">

        <!-- <input type="text" id="search-product" placeholder="Search Produit..." name="text" class="input" oninput="filterDropdown('product')" autocomplete="off">
<div id="product-dropdown" class="dropdown hidden bg-white shadow-md absolute z-10"></div> -->


</div>


            <!-- <input type="text" id="search-product" placeholder="Search Produit..." class="border px-3 py-2 w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" oninput="filterDropdown('product')"> -->
            <!-- <input type="text" id="search-supplier" placeholder="Search Fournisseur..." class="border px-3 py-2 w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white" oninput="filterDropdown('supplier')"> -->
        </div>


        <br>
        
  

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

<br><br>

        <!-- <div class="container">
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
</div> -->



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


    .selected-row {
    background-color: #cce5ff;
}
.input {
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 100%;
}

.dropdown {
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    border-radius: 6px;
}



</style>

<!-- Search input field for product -->
<!-- Add this search input above the PRODUIT QUOTA table -->
<div class="relative mb-4">
    <input type="text" id="search-product" placeholder="Search Produit..." name="text" 
           class="w-full p-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
           oninput="filterDropdown('product')" autocomplete="off">
    <div id="product-dropdown" class="dropdown hidden bg-white dark:bg-gray-700 shadow-md absolute z-10 w-full mt-1 rounded-md overflow-hidden"></div>
</div>
<!-- Table for Product Quota (PRODUIT QUOTA) -->
<!-- Table for Product Quota (PRODUIT QUOTA) -->
<!-- Table for Product Quota (PRODUIT QUOTA) -->

<div id="summary" class="mt-4 text-sm dark:text-white">
    <div id="total-rows">Total Rows: 0</div>
    <div id="total-price">Total Price: 0</div>
</div>

<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">PRODUIT QUOTA</h2>
        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
            <th data-column="NAME" onclick="sortTable('NAME')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Name
            </th>
            <th data-column="PRIX" onclick="sortTable('PRIX')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                Prix
            </th>
            <th data-column="QTY" onclick="sortTable('QTY')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                QTY
            </th>
        </tr>
    </thead>
    <tbody id="data-table" class="dark:bg-gray-800">
        <!-- Example dynamic row -->
    
        <!-- More dynamic rows go here -->
    </tbody>
</table>

    </div>
</div>

<!-- Table for Operator Quota (OPERATOR QUOTA) -->
<div class="mt-4 flex flex-col md:flex-row">
    <!-- Table Container (Left Side) -->
    <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 w-full md:w-1/2">
        <div class="overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">OPERATOR QUOTA</h2>
            <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                <thead>
                    <tr class="table-header dark:bg-gray-700">
                        <th data-column="NAME" onclick="sortTableOp('NAME')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                            Name
                        </th>
                        <th data-column="QTY" onclick="sortTableOp('QTY')" class="border border-gray-300 px-4 py-2 dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600">
                            QTY
                        </th>
                    </tr>
                </thead>
                <tbody id="quotaop-table" class="dark:bg-gray-800">
                    <!-- Dynamic Rows for OPERATOR QUOTA -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Canvas Container (Right Side) -->
    <div class="mt-4 md:mt-0 md:ml-4 w-full md:w-1/2">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">Product Quota Chart</h2>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <canvas id="productQuotaChart"></canvas>
        </div>
        <div class="flex justify-center mt-4">
            <button class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" onclick="changeChartType()">
                Change Chart Type
            </button>
        </div>
    </div>
</div>

<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Initial Data Setup
let originalData = [];
let operatorData = [];
let currentSort = { column: null, direction: 'asc' };
let currentSortOp = { column: null, direction: 'asc' };
let chartInstance = null;
let currentChartType = 'bar'; // Default chart type

// Load product quota data
async function loadQuotaProducts() {
    try {
        const response = await fetch('http://127.0.0.1:5000/quota-product');
        const data = await response.json();
        if (Array.isArray(data)) {
            originalData = data;
            renderProductQuotaTable(data);
        } else {
            console.error("Invalid data format or error:", data);
        }
    } catch (error) {
        console.error("Failed to fetch product quota data:", error);
    }
}

// Render Product Quota Table
// Render Product Quota Table

function formatNumberWithSpaces(value) {
    if (typeof value !== 'number') return value;
    return value.toLocaleString('fr-FR');
}

function renderProductQuotaTable(data) {
    const tbody = document.getElementById("data-table");
    tbody.innerHTML = "";

    let totalPrice = 0;

    data.forEach(item => {
        totalPrice += item.PRIX || 0;

        const row = document.createElement("tr");
        row.classList.add("cursor-pointer", "hover:bg-gray-100", "dark:hover:bg-gray-700");
        row.innerHTML = `
            <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${item.NAME}</td>
            <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${formatNumberWithSpaces(item.PRIX)}</td>
            <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${item.QTY}</td>
        `;
        row.addEventListener("click", () => {
            loadOperatorQuotaData(item.NAME);
        });
        tbody.appendChild(row);
    });

    // Update summary info
    document.getElementById("total-rows").textContent = `Total Rows: ${data.length}`;
    document.getElementById("total-price").textContent = `Total Price: ${formatNumberWithSpaces(totalPrice)}`;
}



// Sort Product Quota Table
function sortTable(column) {
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = 'asc';
    }
    
    const sortedData = [...originalData];
    sortedData.sort((a, b) => {
        const aVal = a[column];
        const bVal = b[column];
        if (typeof aVal === "number" && typeof bVal === "number") {
            return currentSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
        } else {
            return currentSort.direction === 'asc' 
                ? String(aVal).localeCompare(String(bVal)) 
                : String(bVal).localeCompare(String(aVal));
        }
    });
    renderProductQuotaTable(sortedData);
}

// Load Operator Quota Data
async function loadOperatorQuotaData(productName) {
    try {
        const response = await fetch(`http://127.0.0.1:5000/quota-operator?produit=${encodeURIComponent(productName)}`);
        const data = await response.json();
        if (Array.isArray(data)) {
            operatorData = data;
            renderOperatorQuotaTable(data);
            renderOperatorQuotaChart(data);
        } else {
            console.error("Invalid data format for operator quota:", data);
        }
    } catch (error) {
        console.error("Failed to fetch operator quota data:", error);
    }
}

// Render Operator Quota Table
function renderOperatorQuotaTable(data) {
    const tbody = document.getElementById("quotaop-table");
    tbody.innerHTML = ""; // Clear previous rows
    
    data.forEach(item => {
        const row = document.createElement("tr");
        row.classList.add("hover:bg-gray-100", "dark:hover:bg-gray-700");
        row.innerHTML = `
            <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${item.NAME}</td>
            <td class="border border-gray-300 px-4 py-2 dark:border-gray-600">${item.QTY}</td>
        `;
        tbody.appendChild(row);
    });
}

// Sort Operator Quota Table
function sortTableOp(column) {
    if (currentSortOp.column === column) {
        currentSortOp.direction = currentSortOp.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortOp.column = column;
        currentSortOp.direction = 'asc';
    }
    
    const sortedData = [...operatorData];
    sortedData.sort((a, b) => {
        const aVal = a[column];
        const bVal = b[column];
        if (typeof aVal === "number" && typeof bVal === "number") {
            return currentSortOp.direction === 'asc' ? aVal - bVal : bVal - aVal;
        } else {
            return currentSortOp.direction === 'asc' 
                ? String(aVal).localeCompare(String(bVal)) 
                : String(bVal).localeCompare(String(aVal));
        }
    });
    renderOperatorQuotaTable(sortedData);
    renderOperatorQuotaChart(sortedData);
}

// Render Operator Quota Chart
function renderOperatorQuotaChart(data) {
    const ctx = document.getElementById('productQuotaChart').getContext('2d');
    
    // Destroy previous chart instance if it exists
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    const labels = data.map(item => item.NAME);
    const values = data.map(item => item.QTY);
    
    // Calculate total for percentage calculation
    const total = values.reduce((sum, value) => sum + value, 0);
    
    chartInstance = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: labels,
            datasets: [{
                label: 'Operator Quota',
                data: values,
           
                backgroundColor: [
    'rgba(255, 99, 132, 0.7)',    // Red
    'rgba(54, 162, 235, 0.7)',     // Blue
    'rgba(255, 159, 64, 0.7)',     // Orange
    'rgba(75, 192, 192, 0.7)',     // Teal
    'rgba(153, 102, 255, 0.7)',    // Purple
    'rgba(255, 205, 86, 0.7)',     // Yellow
    'rgba(201, 203, 207, 0.7)',    // Gray
    'rgba(0, 204, 102, 0.7)',      // Green
    'rgba(255, 102, 178, 0.7)',    // Pink
    'rgba(102, 178, 255, 0.7)',    // Light Blue
    'rgba(178, 102, 255, 0.7)'     // Lavender
],
borderColor: [
    'rgba(255, 99, 132, 1)',
    'rgba(54, 162, 235, 1)',
    'rgba(255, 159, 64, 1)',
    'rgba(75, 192, 192, 1)',
    'rgba(153, 102, 255, 1)',
    'rgba(255, 205, 86, 1)',
    'rgba(201, 203, 207, 1)',
    'rgba(0, 204, 102, 1)',
    'rgba(255, 102, 178, 1)',
    'rgba(102, 178, 255, 1)',
    'rgba(178, 102, 255, 1)'
],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                            return [
                                `${context.label}: ${value}`,
                                `Percentage: ${percentage}`
                            ];
                        }
                    }
                },
                // For pie/doughnut charts only
                datalabels: {
                    formatter: (value) => {
                        return total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold'
                    }
                }
            },
            scales: currentChartType === 'bar' || currentChartType === 'line' ? {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            return total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                        }
                    }
                },
                x: {
                    ticks: {
                        callback: function(value) {
                            // For horizontal bar charts
                            if (currentChartType === 'bar' && this.chart.config.indexAxis === 'y') {
                                const rawValue = this.chart.data.datasets[0].data[value];
                                return total > 0 ? ((rawValue / total) * 100).toFixed(1) + '%' : '0%';
                            }
                            return this.getLabelForValue(value);
                        }
                    }
                }
            } : {}
        },
        plugins: currentChartType === 'pie' || currentChartType === 'doughnut' ? [{
            id: 'percentageText',
            afterDatasetsDraw(chart, args, options) {
                const {ctx, chartArea: {top, bottom, left, right, width, height}, scales} = chart;
                
                if (total > 0) {
                    const centerX = (left + right) / 2;
                    const centerY = (top + bottom) / 2;
                    
                    ctx.save();
                    ctx.font = 'bold 16px Arial';
                    ctx.fillStyle = '#333';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('Total: ' + total, centerX, centerY - 20);
                    
                    const percentage = values.reduce((sum, value) => sum + value, 0) / total * 100;
                    ctx.font = 'bold 20px Arial';
                    ctx.fillText(percentage.toFixed(1) + '%', centerX, centerY + 10);
                    ctx.restore();
                }
            }
        }] : []
    });
}

// Change Chart Type
function changeChartType() {
    const chartTypes = ['bar', 'line', 'pie', 'doughnut'];
    const currentIndex = chartTypes.indexOf(currentChartType);
    const nextIndex = (currentIndex + 1) % chartTypes.length;
    currentChartType = chartTypes[nextIndex];
    
    if (operatorData.length > 0) {
        renderOperatorQuotaChart(operatorData);
    }
}

// Initialize the page
// document.addEventListener('DOMContentLoaded', () => {
//     loadQuotaProducts();
    
//     // Make sure the chart canvas has a defined height
//     const canvas = document.getElementById('productQuotaChart');
//     canvas.style.height = '300px';
//     canvas.style.width = '100%';
// });


document.getElementById('search-product').addEventListener('focus', function() {
    // Clear the value
    this.value = '';

    // Trigger the 'input' event to re-run the search
    const event = new Event('input', { bubbles: true });
    this.dispatchEvent(event);
});

// Search and Dropdown Functions
function filterDropdown(type) {
    const input = document.getElementById("search-product");
    const query = input.value.trim().toLowerCase();
    const dropdown = document.getElementById("product-dropdown");

    if (query.length === 0) {
        dropdown.classList.add("hidden");
        renderProductQuotaTable(originalData);
        return;
    }

    const filteredData = originalData.filter(item => 
        item.NAME.toLowerCase().includes(query)
    );

    if (filteredData.length > 0) {
        dropdown.innerHTML = "";
        filteredData.slice(0, 5).forEach(item => { // Limit to 5 results
            const option = document.createElement("div");
            option.textContent = item.NAME;
            option.className = "px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer";
            option.addEventListener("click", () => {
                input.value = item.NAME;
                dropdown.classList.add("hidden");
                // Filter the table to show only the selected product
                renderProductQuotaTable(originalData.filter(i => i.NAME === item.NAME));
                // Load the operator quota data for this product
                loadOperatorQuotaData(item.NAME);
            });
            dropdown.appendChild(option);
        });
        dropdown.classList.remove("hidden");
    } else {
        dropdown.classList.add("hidden");
    }

    // Also filter the main table as you type
    renderProductQuotaTable(filteredData);
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById("product-dropdown");
    const searchInput = document.getElementById("search-product");
    
    if (!searchInput.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add("hidden");
    }
});

// Load data when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadQuotaProducts();
    
    // Make sure the chart canvas has a defined height
    const canvas = document.getElementById('productQuotaChart');
    canvas.style.height = '300px';
    canvas.style.width = '100%';
    
    // Add event listener to search input for Enter key
    const searchInput = document.getElementById("search-product");
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const query = searchInput.value.trim();
            if (query) {
                const matchingItem = originalData.find(item => 
                    item.NAME.toLowerCase() === query.toLowerCase()
                );
                if (matchingItem) {
                    loadOperatorQuotaData(matchingItem.NAME);
                }
            }
        }
    });
});

</script>


</body>
</html>