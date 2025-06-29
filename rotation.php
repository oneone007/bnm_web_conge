<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}


// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
    header("Location: Acess_Denied");    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotation</title>
    <script src="main.js" defer></script>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="theme.js"></script>
    
    <!-- Leaflet CSS and JS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="rotation.css">
    <style>
    /* Chart container improvements */
    #chartContainer.canvas-container {
        max-width: 1100px;
        margin: 0 auto;
        width: 100%;
        min-width: 320px;
        background: linear-gradient(135deg, #f8fafc 60%, #e0e7ef 100%);
        box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
        border-radius: 1.25rem;
        padding: 2.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: box-shadow 0.2s;
    }
    @media (max-width: 1200px) {
        #chartContainer.canvas-container {
            max-width: 98vw;
            padding: 1.5rem 0.5rem;
        }
    }
    @media (max-width: 700px) {
        #chartContainer.canvas-container {
            min-width: 0;
            padding: 0.5rem 0.1rem;
        }
    }
    #histogramChart {
        max-width: 100% !important;
        height: 520px !important;
    }
    
    /* Map Styles */
    #mapDiv {
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    
    #mapDiv:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 0.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    
    /* Custom map controls */
    .leaflet-control-zoom {
        border: none !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
    }
    
    .leaflet-control-zoom a {
        border-radius: 4px !important;
        transition: all 0.2s ease !important;
    }
    
    .leaflet-control-zoom a:hover {
        transform: scale(1.05) !important;
    }
    
    /* Map tooltip styles */
    #mapTooltip {
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.875rem;
        line-height: 1.4;
        max-width: 250px;
        word-wrap: break-word;
        z-index: 1000;
    }
    
    #mapTooltip::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: rgba(0,0,0,0.9) transparent transparent transparent;
    }
    
    /* Zone list scrollbar */
    #mapZonesList::-webkit-scrollbar {
        width: 6px;
    }
    
    #mapZonesList::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    #mapZonesList::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    #mapZonesList::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Dark mode adjustments for map */
    .dark #mapZonesList::-webkit-scrollbar-track {
        background: #374151;
    }
    
    .dark #mapZonesList::-webkit-scrollbar-thumb {
        background: #6b7280;
    }
    
    .dark #mapZonesList::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Hide Leaflet attribution */
    .leaflet-control-attribution {
        display: none !important;
    }
    
    .leaflet-bottom.leaflet-right {
        display: none !important;
    }
    
    /* Hide any remaining attribution links */
    .leaflet-control-attribution a {
        display: none !important;
    }
    </style>


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">


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


    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Rotation 
            </h1>
        </div>

        <!-- Filters -->


        <br>




      




        <!-- Date Inputs -->
<div class="date-container">
    <div class="flex items-center space-x-2">
        <label for="start-date">Begin Date:</label>
        <input type="date" id="start-date">
    </div>

    <div class="flex items-center space-x-2">
        <label for="end-date">End Date:</label>
        <input type="date" id="end-date">
    </div>

</div>




<div class="product-container">
    <input type="text" id="product-search" placeholder="Search product...">
    



</div>
<div class="products-table-container" id="products-table-container">
        <table class="products-table" id="products-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <!-- Products will be loaded here -->
            </tbody>
        </table>
        <div class="table-pagination" id="table-pagination">
            <button id="prev-page">Previous</button>
            <span id="page-info">Page 1 of 1</span>
            <button id="next-page">Next</button>
        </div>
    </div>



<button id="downloadExcel_rotation" class="loader">
  <div class="loader-bg">
    <span>Download</span>
  </div>
  <div class="drops">
    <div class="drop1"></div>
    <div class="drop2"></div>
    <div class="drop3"></div>
  </div>
</button>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" style="position: absolute; width: 0; height: 0;">
  <defs>
    <filter id="liquid">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur>
      <feColorMatrix
        in="blur"
        mode="matrix"
        values="1 0 0 0 0  
                0 1 0 0 0  
                0 0 1 0 0  
                0 0 0 18 -7"
        result="liquid">
      </feColorMatrix>
    </filter>
  </defs>
</svg>


       


 
<!-- Tables Section - Full Width -->
<div class="w-full mb-8">
    <div class="flex gap-6">
        <!-- First Table: HISTORIQUE -->
        <div class="w-1/2">
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-3">
                    <h2 class="text-lg font-semibold text-black dark:text-white">HISTORIQUE</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th class="border px-3 py-2">QTY DISPO</th>
                                <th class="border px-3 py-2">DERNIER ACHAT</th>
                                <th class="border px-3 py-2">DATE</th>
                            </tr>
                        </thead>
                        <tbody id="historique-table" class="dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <!-- This Week Table -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mt-4">
                <div class="flex justify-between items-center p-3">
                    <h2 class="text-lg font-semibold text-black dark:text-white">THIS WEEK (Sun-Thu)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr id="this-week-header" class="table-header dark:bg-gray-700"></tr>
                        </thead>
                        <tbody id="this-week-table" class="dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <!-- Last Week Table -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mt-4">
                <div class="flex justify-between items-center p-3">
                    <h2 class="text-lg font-semibold text-black dark:text-white">LAST WEEK (Sun-Thu)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr id="last-week-header" class="table-header dark:bg-gray-700"></tr>
                        </thead>
                        <tbody id="last-week-table" class="dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Second Table: ROTATION PAR MOIS -->
        <div class="w-1/2">
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-3">
                    <h2 class="text-lg font-semibold text-black dark:text-white">ROTATION PAR MOIS</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th class="border px-3 py-2">PERIOD</th>
                                <th class="border px-3 py-2">QTY_VENDU</th>
                                <th class="border px-3 py-2">QTY_ACHETE</th>
                            </tr>
                        </thead>
                        <tbody id="rotation-table" class="dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section - Full Width Below Tables -->
<div class="w-full mb-8">
    <div class="flex gap-4 mb-4">
        <button id="toggleChartBtn" class="btn" onclick="toggleChartType()">
            <i class="fas fa-chart-line"></i> Switch to Graph
        </button> 
        <button id="fullscreenBtn" class="btn" onclick="toggleFullscreen()">
            <i class="fas fa-expand"></i> Full Screen
        </button>
    </div>
    <div id="chartContainer" class="canvas-container rounded-lg shadow-md dark:bg-gray-800 h-[600px] w-full flex justify-center items-center bg-white p-4" style="display: none;">
        <canvas id="histogramChart" class="w-full h-full"></canvas>
    </div>
</div>

<!-- Map Section - Full Width Below Chart -->
<div class="w-full">
    <div class="flex gap-4 mb-4">
        <button id="toggleMapBtn" class="btn" onclick="toggleMapVisibility()">
            <i class="fas fa-map-marked-alt"></i> Show Map
        </button>
        <button id="loadMapDataBtn" class="btn" onclick="loadMapData()" style="display: none;">
            <i class="fas fa-search"></i> Load Map Data
        </button>
        <button id="resetMapBtn" class="btn" onclick="resetMap()" style="display: none;">
            <i class="fas fa-home"></i> Reset Map
        </button>
    </div>
    
    <div id="mapContainer" class="rounded-lg shadow-md dark:bg-gray-800 bg-white" style="display: none;">
        <div class="p-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4 text-center">
                <i class="fas fa-map-marked-alt"></i> Algeria Zone Distribution Map
            </h2>
            
            <!-- Map Info Panel -->
            <div class="flex gap-6 mb-4">
                <div class="w-1/4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
                        <i class="fas fa-chart-bar"></i> Map Statistics
                    </h3>
                    <div id="mapZonesList" class="max-h-64 overflow-y-auto">
                        <p class="text-gray-600 dark:text-gray-400 text-center italic">
                            Select a product and load data to see zone distribution
                        </p>
                    </div>
                </div>
                
                <div class="w-3/4 relative">
                    <div id="mapDiv" class="h-96 w-full bg-gray-100 dark:bg-gray-600 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-500">
                        <div id="mapPlaceholder" class="flex items-center justify-center h-full">
                            <p class="text-gray-500 dark:text-gray-400 text-center">
                                <i class="fas fa-map-marked-alt text-4xl mb-2"></i><br>
                                Map will appear here when data is loaded
                            </p>
                        </div>
                    </div>
                    
                    <!-- Map Legend -->
                    <div id="mapLegend" class="absolute top-2 right-2 bg-white dark:bg-gray-800 p-3 rounded-lg shadow-md" style="display: none;">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">
                            <i class="fas fa-info-circle"></i> Legend
                        </h4>
                        <div class="space-y-1 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 bg-green-100 border border-green-300 rounded"></div>
                                <span class="text-gray-700 dark:text-gray-300">Low QTY</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 bg-green-300 border border-green-400 rounded"></div>
                                <span class="text-gray-700 dark:text-gray-300">Medium QTY</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 bg-green-500 border border-green-600 rounded"></div>
                                <span class="text-gray-700 dark:text-gray-300">High QTY</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 bg-green-700 border border-green-800 rounded"></div>
                                <span class="text-gray-700 dark:text-gray-300">Very High QTY</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 bg-orange-400 border border-orange-500 rounded"></div>
                                <span class="text-gray-700 dark:text-gray-300">Selected</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-3 bg-gray-400 border border-gray-500 rounded"></div>
                                <span class="text-gray-700 dark:text-gray-300">No Data</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hover Tooltip -->
                    <div id="mapTooltip" class="absolute pointer-events-none bg-black bg-opacity-90 text-white px-3 py-2 rounded-lg text-sm shadow-lg z-50" style="display: none;">
                        <div id="tooltipContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  <br>
  <br>
  
<script>







document.addEventListener("DOMContentLoaded", () => {
    updateToggleButtonText();

    // Add event listeners for auto-updating the chart when input values change
    document.getElementById("product-search")?.addEventListener("input", fetchHistogramData);
    document.getElementById("start-date")?.addEventListener("change", function() {
        if (document.getElementById("end-date").value) {
            fetchHistogramData();
        }
    });
    document.getElementById("end-date")?.addEventListener("change", function() {
        if (document.getElementById("start-date").value) {
            fetchHistogramData();
        }
    });
});



function fetchHistogramData() {
    const productName = document.getElementById("product-search")?.value.trim();
    const startDate = document.getElementById("start-date")?.value;
    const endDate = document.getElementById("end-date")?.value;
    const chartContainer = document.getElementById("chartContainer");

    // Check if both startDate and endDate are provided
    if (!startDate || !endDate) {
        console.error("❌ Start date and end date are required.");
        chartContainer.style.display = "none"; // Hide the chart if dates are missing
        return;
    }

    const url = `http://192.168.1.94:5000/histogram?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product=${encodeURIComponent(productName)}`;

    fetch(url)
        .then(response => response.ok ? response.json() : Promise.reject(`HTTP error! Status: ${response.status}`))
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                console.error("❌ No valid data received.");
                chartContainer.style.display = "none"; // Hide chart if no data
                return;
            }
            chartContainer.style.display = "flex"; // Show chart when data is available
            updateHistogramChart(data);
        })
        .catch(error => {
            console.error("❌ Error fetching histogram data:", error);
            chartContainer.style.display = "none"; // Hide chart on error
        });
}


function updateHistogramChart(data) {
    const labels = data.map(item => item.PERIOD);
    const qtyAchete = data.map(item => item.QTY_ACHETÉ);
    const qtyVendu = data.map(item => item.QTY_VENDU);

    const ctx = document.getElementById("histogramChart").getContext("2d");

    if (chartInstance) {
        chartInstance.destroy(); 
    }

    chartInstance = new Chart(ctx, {
        type: currentChartType, 
        data: {
            labels,
            datasets: [
                {
                    label: "Quantité Achetée",
                    data: qtyAchete,
                    backgroundColor: currentChartType === "bar" ? "rgba(54, 162, 235, 0.6)" : "transparent",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 2,
                    fill: currentChartType === "bar" ? false : true,
                    tension: 0.3, 
                },
                {
                    label: "Quantité Vendue",
                    data: qtyVendu,
                    backgroundColor: currentChartType === "bar" ? "rgba(255, 99, 132, 0.6)" : "transparent",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 2,
                    fill: currentChartType === "bar" ? false : true,
                    tension: 0.3, 
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: "top" },
                // Add data labels plugin configuration
                datalabels: {
                    display: function(context) {
                        return currentChartType === "bar"; // Only show labels for bar chart
                    },
                    anchor: 'end',
                    align: 'top',
                    formatter: function(value, context) {
                        return value > 0 ? value : ''; // Only show non-zero values
                    },
                    color: function(context) {
                        // Use different colors for different datasets
                        return context.datasetIndex === 0 ? '#1f77b4' : '#ff7f0e';
                    },
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    backgroundColor: function(context) {
                        // Semi-transparent background matching dataset colors
                        return context.datasetIndex === 0 ? 'rgba(54, 162, 235, 0.1)' : 'rgba(255, 99, 132, 0.1)';
                    },
                    borderColor: function(context) {
                        return context.datasetIndex === 0 ? 'rgba(54, 162, 235, 0.5)' : 'rgba(255, 99, 132, 0.5)';
                    },
                    borderRadius: 3,
                    borderWidth: 1,
                    padding: {
                        top: 4,
                        bottom: 4,
                        left: 6,
                        right: 6
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: "Period" } },
                y: { title: { display: true, text: "Quantity" }, beginAtZero: true }
            },
            // Add animation to make it more engaging
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        },
        // Register the datalabels plugin
        plugins: [ChartDataLabels]
    });
}

function toggleChartType() {
    currentChartType = currentChartType === "bar" ? "line" : "bar"; 
    updateToggleButtonText();
    fetchHistogramData();
}

function updateToggleButtonText() {
    const btn = document.getElementById("toggleChartBtn");
    btn.innerHTML = currentChartType === "bar"
        ? '<i class="fas fa-chart-line"></i> Switch to Graph'
        : '<i class="fas fa-chart-bar"></i> Switch to Histogram';
}

function toggleFullscreen() {
    const canvasContainer = document.querySelector(".canvas-container");
    if (!document.fullscreenElement) {
        canvasContainer.requestFullscreen().catch(err => console.error(`❌ Fullscreen error: ${err.message}`));
    } else {
        document.exitFullscreen();
    }
}



let chartInstance = null; 
let currentChartType = Math.random() < 0.5 ? "bar" : "line";
let allProducts = [];
let filteredProducts = [];
let currentPage = 1;
const rowsPerPage = 10;
let lastFetchTime = 0;
const CACHE_DURATION = 5 * 60 * 1000;

document.addEventListener("DOMContentLoaded", async function() {
    updateToggleButtonText();
    await fetchProducts();
    setupProductSearch();
    setupDateInputs();
    
    // Event listeners for chart updates
    document.getElementById("product-search")?.addEventListener("input", fetchHistogramData);
    document.getElementById("start-date")?.addEventListener("change", function() {
        if (document.getElementById("end-date").value) {
            fetchHistogramData();
            // Auto-update map if product is selected and map is visible
            autoUpdateMapIfNeeded();
        }
    });
    document.getElementById("end-date")?.addEventListener("change", function() {
        if (document.getElementById("start-date").value) {
            fetchHistogramData();
            // Auto-update map if product is selected and map is visible
            autoUpdateMapIfNeeded();
        }
    });
});

function setupDateInputs() {
    const productSearch = document.getElementById("product-search");
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    
    // Set end date to today initially
    const today = new Date().toISOString().split("T")[0];
    if (!endDate.value) {
        endDate.value = today;
    }
    
    // Enable dates only when product is selected, but preserve their values
    productSearch.addEventListener("input", function() {
        if (this.value.trim()) {
            startDate.disabled = false;
            endDate.disabled = false;
        } else {
            startDate.disabled = true;
            endDate.disabled = true;
            // Do NOT reset the date values when product is cleared
        }
    });

    // When start date changes, set end date to today if not already set
    startDate.addEventListener("change", function() {
        if (!endDate.value) {
            endDate.value = today;
        }
        // Auto-show map if product is selected and both dates are available
        const productName = document.getElementById("product-search").value.trim();
        if (productName && this.value && endDate.value) {
            autoShowMapWithData();
        }
    });
    
    // When end date changes, auto-show map if product and start date are available
    endDate.addEventListener("change", function() {
        const productName = document.getElementById("product-search").value.trim();
        if (productName && startDate.value && this.value) {
            autoShowMapWithData();
        }
    });
}
async function fetchProducts(forceRefresh = false) {
    const currentTime = Date.now();
    if (!forceRefresh && allProducts.length && (currentTime - lastFetchTime) < CACHE_DURATION) {
        console.log("✅ Using cached product data");
        return;
    }

    try {
        const response = await fetch("http://192.168.1.94:5000/fetch-rotation-product-data");
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        allProducts = await response.json();
        lastFetchTime = currentTime;
        filteredProducts = [...allProducts];
        renderTable();
    } catch (error) {
        console.error("❌ Error fetching products:", error);
    }
}

function setupProductSearch() {
    const productSearch = document.getElementById("product-search");
    const productsTableContainer = document.getElementById("products-table-container");
    
    productSearch.addEventListener("focus", function() {
        if (filteredProducts.length > 0) {
            productsTableContainer.style.display = "block";
        }
    });
    
    productSearch.addEventListener("input", debounce(function(e) {
        const searchValue = e.target.value.toLowerCase();
        
        if (!searchValue.trim()) {
            filteredProducts = [...allProducts];
        } else {
            filteredProducts = allProducts.filter(product => 
                product.NAME.toLowerCase().includes(searchValue)
            );
        }
        
        currentPage = 1;
        renderTable();
        productsTableContainer.style.display = filteredProducts.length > 0 ? "block" : "none";
    }, 300));
    
    // Close table when clicking outside
    document.addEventListener("click", function(e) {
        if (!productSearch.contains(e.target) && !productsTableContainer.contains(e.target)) {
            productsTableContainer.style.display = "none";
        }
    });
    
    // Pagination controls
    document.getElementById("prev-page").addEventListener("click", function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById("next-page").addEventListener("click", function() {
        const totalPages = Math.ceil(filteredProducts.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });
}

function renderTable() {
    const tableBody = document.getElementById("products-table-body");
    const paginationInfo = document.getElementById("page-info");
    const prevBtn = document.getElementById("prev-page");
    const nextBtn = document.getElementById("next-page");
    
    tableBody.innerHTML = "";
    
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, filteredProducts.length);
    const paginatedProducts = filteredProducts.slice(startIndex, endIndex);
    
    paginatedProducts.forEach((product, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>${product.NAME}</td>
        `;
        row.addEventListener("click", function() {
            document.getElementById("product-search").value = product.NAME;
            document.getElementById("products-table-container").style.display = "none";
            // Enable date inputs and preserve their existing values
            const startDateInput = document.getElementById("start-date");
            const endDateInput = document.getElementById("end-date");
            startDateInput.disabled = false;
            endDateInput.disabled = false;
            // Only set end date to today if it's empty
            if (!endDateInput.value) {
                const today = new Date().toISOString().split("T")[0];
                endDateInput.value = today;
            }
            fetchHistoriqueRotation();
            fetchRotationData();
            fetchHistogramData();
            refreshWeeklyTables(); // Only fetch weekly tables after product is selected
            
            // Auto-show map and load data if both dates are available
            if (startDateInput.value && endDateInput.value) {
                autoShowMapWithData();
            }
        });
        tableBody.appendChild(row);
    });
    
    const totalPages = Math.ceil(filteredProducts.length / rowsPerPage);
    paginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
}



function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}


async function fetchHistoriqueRotation() {
    const productName = document.getElementById("product-search").value.trim();

    console.log("Product Name:", `"${productName}"`); // ✅ Check if it's empty

    if (!productName) {
        console.error("❌ Missing product name, not sending request.");
        return; 
    }

    try {
        const url = `http://192.168.1.94:5000/fetchHistoriqueRotation?product=${encodeURIComponent(productName)}`;
        console.log("Requesting URL:", url); // ✅ Debugging

        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Response Data:", data); // ✅ Confirm data

        updateHistoriqueTable(data);
    } catch (error) {
        console.error("Error fetching data:", error);
        document.getElementById('historique-table').innerHTML = "<tr><td colspan='3' class='text-center text-red-500'>Failed to load data</td></tr>";
    }
}



// Helper to format numbers with spaces as thousands separators
function formatNumberWithSpace(n) {
    if (typeof n !== 'number' && isNaN(Number(n))) return n;
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function updateHistoriqueTable(data) {
    const tableBody = document.getElementById("historique-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const row = data[0];  

    // Format the date properly in French
    const formattedDate = row.DATE 
        ? new Date(row.DATE).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) 
        : 'N/A';

    tableBody.innerHTML = `
        <tr class="dark:bg-gray-700">
            <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_DISPO ?? 0)}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.DERNIER_ACHAT ?? 0)}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${formattedDate}</td>
        </tr>
    `;
}




// ✅ Attach event listeners to trigger fetching when filters change
["start-date", "end-date", "product-search"].forEach(id => {
    document.getElementById(id).addEventListener("change", fetchRotationData);
});

// Clear search input but preserve date fields
document.getElementById("product-search").addEventListener("click", function () {
    this.value = ""; // Clear search input only
    // Keep the date values as they are - do not reset them
});


async function fetchRotationData() {
    const productInput = document.getElementById("product-search");
    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    const productName = productInput.value.trim();
    
    if (!productName) {
        console.warn("⚠️ Please select a product first.");
        return;
    }

    // Ensure startDate and endDate are selected after the product
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;

    if (!startDate || !endDate) {
        console.warn("⚠️ Please select both start and end dates.");
        return;
    }

    const url = `http://192.168.1.94:5000/rotationParMois?product=${encodeURIComponent(productName)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    console.log("🔗 Request URL:", url); // ✅ Debugging

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        const data = await response.json();
        console.log("📥 Response Data:", data); // ✅ Debugging

        updateRotationTable(data);
    } catch (error) {
        console.error("❌ Error fetching rotation data:", error);
        document.getElementById('rotation-table').innerHTML = "<tr><td colspan='3' class='text-center text-red-500'>Failed to load data</td></tr>";
    }
}

function updateRotationTable(data) {
    const tableBody = document.getElementById("rotation-table");
    tableBody.innerHTML = ""; // Clear previous data

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    let specialRows = "";
    let normalRows = "";

    data.forEach(row => {
        const rowHTML = `
            <tr class="dark:bg-gray-700 ${row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE" ? "font-bold" : ""}">
                <td class="border px-3 py-2 dark:border-gray-600">${row.PERIOD ?? 'N/A'}</td>
                <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_VENDU ?? 0)}</td>
                <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_ACHETÉ ?? 0)}</td>
            </tr>
        `;

        // Move "TOTAL" and "MOYENNE" rows to the top
        if (row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE") {
            specialRows += rowHTML;
        } else {
            normalRows += rowHTML;
        }
    });

    // Append special rows first, followed by normal rows
    tableBody.innerHTML = specialRows + normalRows;

    console.log("✅ Table updated successfully.");
}

// Set up event listeners for product and date inputs
document.addEventListener("DOMContentLoaded", () => {
    // Remove the duplicate product-select code since we're using product-search
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");

    // Initially disable date inputs
    startDate.disabled = true;
    endDate.disabled = true;

    // Set end date to today initially only if it's empty
    const today = new Date().toISOString().split("T")[0];
    if (!endDate.value) {
        endDate.value = today;
    }
});

document.getElementById("downloadExcel_rotation").addEventListener("click", async () => {
    const productName = document.getElementById("product-search").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!productName || !startDate || !endDate) {
        console.error("❌ Missing required fields. Not downloading file.");
        return;
    }

    const url = `http://192.168.1.94:5000/download-rotation-par-mois-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product=${encodeURIComponent(productName)}`;
    console.log("🔗 Download URL:", url); // ✅ Debugging

    // Create a hidden link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", ""); // Allow browser to determine filename
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

// --- WEEKLY TABLES LOGIC ---
function getWeekRange(offset = 0) {
    // offset: 0 = this week, -1 = last week
    const today = new Date();
    // Get current day (0=Sunday, 1=Monday, ..., 6=Saturday)
    const day = today.getDay();
    // Find this week's Sunday
    const sunday = new Date(today);
    sunday.setDate(today.getDate() - day + (offset * 7));
    // Thursday is 4 days after Sunday
    const thursday = new Date(sunday);
    thursday.setDate(sunday.getDate() + 4);
    // Format as yyyy-mm-dd
    const format = d => d.toISOString().split('T')[0];
    return { start: format(sunday), end: format(thursday) };
}

async function fetchWeekRotationData(weekType) {
    const productName = document.getElementById("product-search").value.trim();
    if (!productName) {
        document.getElementById(weekType + '-table').innerHTML = "<tr><td colspan='3' class='text-center text-gray-500'>Select a product</td></tr>";
        return;
    }
    let range;
    if (weekType === 'this-week') {
        range = getWeekRange(0);
    } else if (weekType === 'last-week') {
        range = getWeekRange(-1);
    } else {
        return;
    }
    const url = `http://192.168.1.94:5000/rotationParMois?product=${encodeURIComponent(productName)}&start_date=${range.start}&end_date=${range.end}`;
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        const data = await response.json();
        updateWeekTable(weekType, data);
    } catch (error) {
        document.getElementById(weekType + '-table').innerHTML = "<tr><td colspan='3' class='text-center text-red-500'>Failed to load data</td></tr>";
    }
}

function updateWeekTable(weekType, data) {
    const tableBody = document.getElementById(weekType + '-table');
    const tableHeader = document.getElementById(weekType + '-header');
    tableBody.innerHTML = "";
    tableHeader.innerHTML = "";
    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }
    // Helper to format numbers with space as thousands separator
    function formatNumber(n) {
        if (typeof n !== 'number' && isNaN(Number(n))) return n;
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    // Build header: first cell empty, then PERIODs
    let periods = data.map(row => row.PERIOD ?? 'N/A');
    tableHeader.innerHTML = `<th class='border px-3 py-2'></th>` + periods.map(p => {
        const isSpecial = p === 'TOTAL' || p === 'MOYENNE';
        return `<th class='border px-3 py-2${isSpecial ? ' bg-yellow-200 text-yellow-900 dark:bg-yellow-600 dark:text-white' : ''}'>${p}</th>`;
    }).join("");
    // Build rows: QTY_VENDU, QTY_ACHETE
    let qtyVenduRow = `<tr><td class='border px-3 py-2 font-bold'>QTY_VENDU</td>` + data.map(row => {
        const isSpecial = row.PERIOD === 'TOTAL' || row.PERIOD === 'MOYENNE';
        return `<td class='border px-3 py-2${isSpecial ? ' bg-yellow-100 text-yellow-900 dark:bg-yellow-700 dark:text-white font-bold' : ''}'>${formatNumber(row.QTY_VENDU ?? 0)}</td>`;
    }).join("") + `</tr>`;
    let qtyAcheteRow = `<tr><td class='border px-3 py-2 font-bold'>QTY_ACHETE</td>` + data.map(row => {
        const isSpecial = row.PERIOD === 'TOTAL' || row.PERIOD === 'MOYENNE';
        return `<td class='border px-3 py-2${isSpecial ? ' bg-yellow-100 text-yellow-900 dark:bg-yellow-700 dark:text-white font-bold' : ''}'>${formatNumber(row.QTY_ACHETÉ ?? 0)}</td>`;
    }).join("") + `</tr>`;
    tableBody.innerHTML = qtyVenduRow + qtyAcheteRow;
}

// Call these when product changes
function refreshWeeklyTables() {
    fetchWeekRotationData('this-week');
    fetchWeekRotationData('last-week');
}

// Attach to product search and on page load
const productSearchInput = document.getElementById("product-search");
if (productSearchInput) {
    // Only fetch weekly tables after product is selected from dropdown (handled in renderTable)
    // Remove these lines:
    // productSearchInput.addEventListener("change", ...);
    // productSearchInput.addEventListener("input", ...);
}
document.addEventListener("DOMContentLoaded", function() {
    // Only refresh weekly tables if a product is already selected
    const productInput = document.getElementById("product-search");
    if (productInput && productInput.value.trim()) {
        refreshWeeklyTables();
    }
});

// --- MAP FUNCTIONALITY ---
let map = null;
let algeriaLayer = null;
let selectedWilaya = null;
let wilayaLayers = new Map();
let zoneData = new Map();
let maxQty = 0;
let mapTooltip = null;

// Zone name mapping for Algeria map
const zoneNameMapping = {
    // Constantine zones (CNE 1, 2, 3) - all map to Constantine
    'CNE 1': 'Constantine',
    'CNE 2': 'Constantine', 
    'CNE 3': 'Constantine',
    'SMK/DJBEL WAHECHE/BOUSSOUF': 'Constantine',
    'NOUVELLE/KHROUB': 'Constantine',
    'AIN SMARA/ZOUAGHI': 'Constantine',
    
    // JIJEL/MILA zone maps to both wilayas
    'JIJEL/ MILA': ['Jijel', 'Mila'],
    
    // Direct mappings - zones that match wilaya names
    'TIZI OUZOU': 'Tizi Ouzou',
    'BATNA': 'Batna',
    'BEJAIA': 'Bejaia',
    'EL OUED': 'El Oued',
    'SKIKDA': 'Skikda',
    'SETIF': 'Setif',
    'GUELMA': 'Guelma',
    'ANNABA': 'Annaba',
    'OUARGLA': 'Ouargla',
    'BISKRA': 'Biskra',
    'CHLEF': 'Chlef',
    'LAGHOUAT': 'Laghouat',
    'OUM EL BOUAGHI': 'Oum El Bouaghi',
    'BOUIRA': 'Bouira',
    'TAMANRASSET': 'Tamanrasset',
    'TLEMCEN': 'Tlemcen',
    'TIARET': 'Tiaret',
    'SAIDA': 'Saida',
    'MASCARA': 'Mascara',
    'ORAN': 'Oran',
    'MEDEA': 'Medea',
    'AIN DEFLA': 'Ain-Defla',
    'NAAMA': 'Naama',
    'AIN TEMOUCHENT': 'Ain-Temouchent',
    'GHARDAIA': 'Ghardaia',
    'RELIZANE': 'Relizane',
    'MOSTAGANEM': 'Mostaganem',  
    'M SILA': 'M\'Sila',
    'MILA': 'Mila',
    'AIN MLILA': 'Oum El Bouaghi',
    'EL TARF': 'El-Tarf',
    'JIJEL': 'Jijel',
    'SOUK AHRAS': 'Souk-Ahras',
    'TIPAZA': 'Tipaza',
    'BOUMERDES': 'Boumerdes',
    'EL BAYADH': 'El Bayadh',
    'ILLIZI': 'Illizi',
    'BORDJ BOU ARRERIDJ': 'Bordj Bou Arrer',
    'TINDOUF': 'Tindouf',
    'TISSEMSILT': 'Tissemsilt',
    'KHENCHELA': 'Khenchela',
    'EL MGHAIER': 'El M\'Ghair',
    'MENIAA': 'El Menia',
    'OULED DJELLAL': 'Ouled Djellal',
    'BORDJ BADJI MOKHTAR': 'Bordj Baji Mokhtar',
    'BENI ABBES': 'Béni Abbès',
    'TIMIMOUN': 'Timimoun',
    'TOUGGOURT': 'Touggourt',
    'DJANET': 'Djanet',
    'IN SALAH': 'In Salah',
    'IN GUEZZAM': 'In Guezzam',
    'ADRAR': 'Adrar',
    'BECHAR': 'Bechar',
    'DJELFA': 'Djelfa',
    'SIDI BEL ABBES': 'Sidi Bel Abbes',
    'TEBESSA': 'Tebessa',
    'EL BORDJ /MSILA': ['Bordj Bou Arrer', 'M\'Sila'],
    'TEBESSA / KHENCHELA': ['Tebessa', 'Khenchela'],
    'CHELGHOUM': 'Mila',
    'EL KALA': 'El-Tarf',
    'ALGER': 'Alger',
    'BLIDA': 'Blida'
};

const anonymousZones = ['<Aucune>'];

// Toggle map visibility
function toggleMapVisibility() {
    const mapContainer = document.getElementById('mapContainer');
    const toggleBtn = document.getElementById('toggleMapBtn');
    const loadDataBtn = document.getElementById('loadMapDataBtn');
    const resetMapBtn = document.getElementById('resetMapBtn');
    
    if (mapContainer.style.display === 'none') {
        mapContainer.style.display = 'block';
        toggleBtn.innerHTML = '<i class="fas fa-map-marked-alt"></i> Hide Map';
        loadDataBtn.style.display = 'inline-block';
        resetMapBtn.style.display = 'inline-block';
        
        // Initialize map if not already done
        if (!map) {
            initializeMap();
        }
    } else {
        mapContainer.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fas fa-map-marked-alt"></i> Show Map';
        loadDataBtn.style.display = 'none';
        resetMapBtn.style.display = 'none';
    }
}

// Initialize the map
function initializeMap() {
    // Create map
    map = L.map('mapDiv', {
        zoomControl: true,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        touchZoom: true,
        boxZoom: true,
        keyboard: true,
        dragging: true,
        maxZoom: 12,
        minZoom: 4,
        attributionControl: false
    }).setView([28.0339, 1.6596], 7);

    // No base map tiles - show only administrative boundaries
    map.getPane('mapPane').style.background = '#f8f9fa';

    // Hide the placeholder when map is initialized
    const mapPlaceholder = document.getElementById('mapPlaceholder');
    if (mapPlaceholder) {
        mapPlaceholder.style.display = 'none';
    }

    // Load Algeria GeoJSON data
    fetch('dz.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            algeriaLayer = L.geoJSON(data, {
                style: getStyleByQty,
                onEachFeature: onEachFeature
            }).addTo(map);

            map.fitBounds(algeriaLayer.getBounds());
            console.log('Algeria map loaded successfully with', data.features.length, 'wilayas');
        })
        .catch(error => {
            console.error('Error loading Algeria map data:', error);
            document.getElementById('mapDiv').innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading map data</div>';
        });

    // Initialize tooltip
    mapTooltip = document.getElementById('mapTooltip');
}

// Zone name normalization function
function normalizeZoneName(zoneName) {
    const upperZoneName = zoneName.toUpperCase();
    
    // Handle exact matches first (case-insensitive)
    for (const [key, value] of Object.entries(zoneNameMapping)) {
        if (key.toUpperCase() === upperZoneName) {
            return value;
        }
    }
    
    // Handle partial matches
    if (upperZoneName.includes('CNE 1') || upperZoneName.includes('SMK/DJBEL WAHECHE/BOUSSOUF')) {
        return 'Constantine';
    }
    if (upperZoneName.includes('CNE 2') || upperZoneName.includes('NOUVELLE/KHROUB')) {
        return 'Constantine';
    }
    if (upperZoneName.includes('CNE 3') || upperZoneName.includes('AIN SMARA/ZOUAGHI')) {
        return 'Constantine';
    }
    if (upperZoneName.includes('JIJEL/ MILA') || upperZoneName.includes('JIJEL/MILA')) {
        return ['Jijel', 'Mila'];
    }
    if (upperZoneName.includes('TEBESSA / KHENCHELA') || upperZoneName.includes('TEBESSA/KHENCHELA')) {
        return ['Tebessa', 'Khenchela'];
    }
    if (upperZoneName.includes('EL BORDJ /MSILA') || upperZoneName.includes('EL BORDJ/MSILA')) {
        return ['Bordj Bou Arrer', 'M\'Sila'];
    }
    
    // Handle common variations
    if (upperZoneName.includes('GUELMA')) return 'Guelma';
    if (upperZoneName.includes('SIDI BEL ABBES')) return 'Sidi Bel Abbes';
    if (upperZoneName.includes('DJELFA')) return 'Djelfa';
    if (upperZoneName.includes('SETIF')) return 'Setif';
    if (upperZoneName.includes('MSILA') || upperZoneName.includes('M SILA')) return 'M\'Sila';
    
    return zoneName;
}

// Get color based on QTY
function getColorByQty(qty) {
    if (!qty || qty === 0) return '#9ca3af'; // Gray for no data
    
    const ratio = qty / maxQty;
    
    if (ratio <= 0.25) return '#dcfce7'; // Light green
    if (ratio <= 0.5) return '#86efac';  // Light green
    if (ratio <= 0.75) return '#22c55e'; // Green
    return '#15803d'; // Dark green
}

// Get QTY for a wilaya
function getWilayaQty(wilayaName) {
    let primaryQty = 0;
    
    zoneData.forEach((qty, zoneName) => {
        if (anonymousZones.includes(zoneName)) {
            return;
        }
        
        const normalizedZone = normalizeZoneName(zoneName);
        
        if (Array.isArray(normalizedZone)) {
            if (normalizedZone.includes(wilayaName)) {
                primaryQty = Math.max(primaryQty, qty);
            }
        } else if (normalizedZone === wilayaName) {
            if (primaryQty === 0) {
                primaryQty += qty;
            }
        }
    });
    
    return primaryQty;
}

// Get zone details for a wilaya
function getWilayaZoneDetails(wilayaName) {
    let zones = [];
    
    zoneData.forEach((qty, zoneName) => {
        const normalizedZone = normalizeZoneName(zoneName);
        
        if (Array.isArray(normalizedZone)) {
            if (normalizedZone.includes(wilayaName)) {
                zones.push({
                    name: zoneName,
                    qty: qty,
                    type: 'shared',
                    mappedTo: normalizedZone
                });
            }
        } else if (normalizedZone === wilayaName) {
            zones.push({
                name: zoneName,
                qty: qty,
                type: 'individual',
                mappedTo: [normalizedZone]
            });
        }
    });
    
    return zones;
}

// Style functions
function getStyleByQty(feature) {
    const wilayaName = feature.properties.name;
    const qty = getWilayaQty(wilayaName);
    
    return {
        fillColor: getColorByQty(qty),
        weight: 2,
        opacity: 1,
        color: '#374151',
        dashArray: '',
        fillOpacity: 0.7
    };
}

function getHighlightStyle(feature) {
    return {
        fillColor: '#fbbf24',
        weight: 3,
        opacity: 1,
        color: '#374151',
        dashArray: '',
        fillOpacity: 0.9
    };
}

function getSelectedStyle(feature) {
    return {
        fillColor: '#f59e0b',
        weight: 4,
        opacity: 1,
        color: '#374151',
        dashArray: '',
        fillOpacity: 0.9
    };
}

// Event handlers for each feature
function onEachFeature(feature, layer) {
    wilayaLayers.set(feature.properties.name, layer);

    layer.on({
        mouseover: function(e) {
            if (selectedWilaya !== layer) {
                layer.setStyle(getHighlightStyle(feature));
            }
            layer.bringToFront();
            showMapTooltip(e, feature);
        },
        mouseout: function(e) {
            if (selectedWilaya !== layer) {
                layer.setStyle(getStyleByQty(feature));
            }
            hideMapTooltip();
        },
        click: function(e) {
            selectWilayaLayer(layer, feature);
        }
    });
}

// Show tooltip on hover
function showMapTooltip(e, feature) {
    const wilayaName = feature.properties.name;
    const qty = getWilayaQty(wilayaName);
    const zoneDetails = getWilayaZoneDetails(wilayaName);
    
    let tooltipContent = `<h4 class="font-bold">${wilayaName}</h4>`;
    tooltipContent += `<p class="text-yellow-300 font-semibold">Total QTY: ${qty.toLocaleString()}</p>`;
    
    if (zoneDetails.length > 0) {
        tooltipContent += '<div class="mt-2 text-xs">';
        const maxZonesToShow = 3;
        zoneDetails.slice(0, maxZonesToShow).forEach(zone => {
            if (zone.type === 'shared') {
                tooltipContent += `<p>• ${zone.name}: ${zone.qty.toLocaleString()} (shared)</p>`;
            } else {
                tooltipContent += `<p>• ${zone.name}: ${zone.qty.toLocaleString()}</p>`;
            }
        });
        
        if (zoneDetails.length > maxZonesToShow) {
            tooltipContent += `<p class="italic opacity-75">+${zoneDetails.length - maxZonesToShow} more zones...</p>`;
        }
        tooltipContent += '</div>';
    } else if (qty === 0) {
        tooltipContent += '<p class="opacity-75">No data available</p>';
    }
    
    document.getElementById('tooltipContent').innerHTML = tooltipContent;
    mapTooltip.style.display = 'block';
    updateTooltipPosition(e.originalEvent);
}

// Hide tooltip
function hideMapTooltip() {
    if (mapTooltip) {
        mapTooltip.style.display = 'none';
    }
}

// Update tooltip position
function updateTooltipPosition(e) {
    if (mapTooltip && mapTooltip.style.display === 'block') {
        const mapDiv = document.getElementById('mapDiv');
        const mapRect = mapDiv.getBoundingClientRect();
        const tooltipRect = mapTooltip.getBoundingClientRect();
        
        let x = e.clientX - mapRect.left;
        let y = e.clientY - mapRect.top - tooltipRect.height - 10;
        
        // Adjust if tooltip goes outside bounds
        if (x + tooltipRect.width > mapRect.width) {
            x = mapRect.width - tooltipRect.width - 10;
        }
        if (x < 10) x = 10;
        if (y < 10) y = e.clientY - mapRect.top + 10;
        
        mapTooltip.style.left = x + 'px';
        mapTooltip.style.top = y + 'px';
    }
}

// Select wilaya
function selectWilayaLayer(layer, feature) {
    if (selectedWilaya && selectedWilaya !== layer) {
        selectedWilaya.setStyle(getStyleByQty(selectedWilaya.feature));
    }
    
    selectedWilaya = layer;
    layer.setStyle(getSelectedStyle(feature));
    
    map.fitBounds(layer.getBounds(), {padding: [20, 20]});
}

// Load map data
async function loadMapData() {
    const productName = document.getElementById("product-search").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!productName) {
        alert('Please select a product first');
        return;
    }

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    try {
        let url = `http://192.168.1.94:5000/fetchZonerotation?start_date=${startDate}&end_date=${endDate}`;
        if (productName) {
            url += `&product=${encodeURIComponent(productName)}`;
        }

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        // Clear previous data
        zoneData.clear();
        maxQty = 0;

        // Process the data
        data.forEach(item => {
            if (item.ZONE && item.QTY) {
                zoneData.set(item.ZONE, item.QTY);
                maxQty = Math.max(maxQty, item.QTY);
            }
        });

        // Update map colors
        updateMapColors();
        
        // Update zones list
        updateMapZonesList();
        
        // Show legend
        document.getElementById('mapLegend').style.display = 'block';

        console.log('Map data loaded:', data.length, 'zones');

    } catch (error) {
        console.error('Error fetching map data:', error);
        alert('Error fetching map data. Please check your connection and try again.');
    }
}

// Update map colors
function updateMapColors() {
    if (algeriaLayer) {
        algeriaLayer.eachLayer(layer => {
            layer.setStyle(getStyleByQty(layer.feature));
        });
    }
}

// Update zones list
function updateMapZonesList() {
    const zonesList = document.getElementById('mapZonesList');
    
    if (zoneData.size === 0) {
        zonesList.innerHTML = '<p class="text-gray-600 dark:text-gray-400 text-center italic">No data loaded</p>';
        return;
    }
    
    const sortedZones = Array.from(zoneData.entries())
        .filter(([zone, qty]) => qty > 0)
        .sort((a, b) => b[1] - a[1]);
    
    if (sortedZones.length === 0) {
        zonesList.innerHTML = '<p class="text-gray-600 dark:text-gray-400 text-center italic">No zones with data found</p>';
        return;
    }
    
    const zonesHTML = sortedZones.map(([zoneName, qty]) => `
        <div class="flex justify-between items-center p-2 mb-2 bg-white dark:bg-gray-600 rounded border-l-4 border-blue-500">
            <span class="font-medium text-gray-800 dark:text-white text-sm">${zoneName}</span>
            <span class="bg-blue-500 text-white px-2 py-1 rounded text-xs font-bold">${qty.toLocaleString()}</span>
        </div>
    `).join('');
    
    zonesList.innerHTML = `<div class="space-y-1">${zonesHTML}</div>`;
}

// Reset map
function resetMap() {
    if (algeriaLayer) {
        map.fitBounds(algeriaLayer.getBounds());
        
        selectedWilaya = null;
        zoneData.clear();
        maxQty = 0;
        
        algeriaLayer.eachLayer(layer => {
            layer.setStyle(getStyleByQty(layer.feature));
        });
        
        document.getElementById('mapZonesList').innerHTML = '<p class="text-gray-600 dark:text-gray-400 text-center italic">Select a product and load data to see zone distribution</p>';
        document.getElementById('mapLegend').style.display = 'none';
    }
}

// Track mouse movement for tooltip
document.addEventListener('mousemove', function(e) {
    if (mapTooltip && mapTooltip.style.display === 'block') {
        updateTooltipPosition(e);
    }
});

// Auto-show map and load data when product and dates are available
function autoShowMapWithData() {
    const productName = document.getElementById("product-search").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    
    // Only proceed if we have all required data
    if (productName && startDate && endDate) {
        // Show map if it's hidden
        const mapContainer = document.getElementById('mapContainer');
        if (mapContainer.style.display === 'none') {
            toggleMapVisibility();
        }
        
        // Load map data automatically
        setTimeout(() => {
            loadMapData();
        }, 500); // Small delay to ensure map is initialized
    }
}

// Auto-update map if needed when dates change
function autoUpdateMapIfNeeded() {
    const productName = document.getElementById("product-search").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const mapContainer = document.getElementById('mapContainer');
    
    // Only update if product is selected, dates are available, and map is visible
    if (productName && startDate && endDate && mapContainer.style.display !== 'none') {
        loadMapData();
    }
}
</script>


     

    
   
     
      
      

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     





</body>

</html>
