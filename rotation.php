<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}


// Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }

$page_identifier = 'Rotation';


require_once 'check_permission.php';


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
    <script src="api_config.js"></script>

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
        transition: opacity 0.2s ease-in-out;
        opacity: 0;
    }
    
    #mapTooltip.show {
        opacity: 1;
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




<div class="product-search-wrapper">
    <div class="product-container">
        <input type="text" id="product-search" placeholder="Search product...">
        <button id="clear-search" class="clear-btn" style="display: none;">Clear</button>
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
                    <h2 id="rotation-table-title" class="text-lg font-semibold text-black dark:text-white">ROTATION PAR MOIS</h2>
                    <?php if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Admin', 'Developer'])): ?>
                    <button id="v2ToggleButton" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 font-medium transition-colors duration-200" style="display: none;">
                        ✅ V2 Mode (VO included)
                    </button>
                    <?php endif; ?>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th class="border px-3 py-2">PERIOD</th>
                                <th class="border px-3 py-2">QTY_VENDU</th>
                                <th class="border px-3 py-2">QTY_ACHETE</th>
                                <th class="border px-3 py-2">QTY_INITIAL</th>
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
    const productElement = document.getElementById("product-search");
    const productName = productElement?.value.trim();
    const productId = productElement?.dataset.productId;
    const startDate = document.getElementById("start-date")?.value;
    const endDate = document.getElementById("end-date")?.value;
    const chartContainer = document.getElementById("chartContainer");

    // Check if both startDate and endDate are provided
    if (!startDate || !endDate) {
        console.error("❌ Start date and end date are required.");
        chartContainer.style.display = "none"; // Hide the chart if dates are missing
        return;
    }

    const url = API_CONFIG.getApiUrl(`/histogram?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product_id=${productId}`);

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
    setupV2Toggle();
    
    // Set initial button text and style for V2 toggle
    updateV2ButtonText();
    
    // Initialize empty tables
    initializeEmptyTables();
    
    // Event listeners for chart updates
    document.getElementById("product-search")?.addEventListener("input", fetchHistogramData);
    document.getElementById("start-date")?.addEventListener("change", function() {
        if (document.getElementById("end-date").value) {
            fetchHistogramData();
            // Use debounced function instead of direct call
            debounceRotationData();
        }
    });
    document.getElementById("end-date")?.addEventListener("change", function() {
        if (document.getElementById("start-date").value) {
            fetchHistogramData();
            // Use debounced function instead of direct call
            debounceRotationData();
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
        const productInput = document.getElementById("product-search");
        const productName = productInput.dataset.selectedProductName || productInput.value.trim();
        if (productName && this.value && endDate.value) {
            // Use debounced function instead of direct call
            debounceRotationData();
            autoShowMapWithData();
        }
    });
    
    // When end date changes, auto-show map if product and start date are available
    endDate.addEventListener("change", function() {
        const productInput = document.getElementById("product-search");
        const productName = productInput.dataset.selectedProductName || productInput.value.trim();
        if (productName && startDate.value && this.value) {
            // Use debounced function instead of direct call
            debounceRotationData();
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
        const response = await fetch(API_CONFIG.getApiUrl("/fetch-rotation-product-data"));
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        allProducts = await response.json();
        lastFetchTime = currentTime;
        filteredProducts = [...allProducts];
        
        // Log basic info and sample structure for debugging
        if (allProducts.length > 0) {
            console.log("✅ Loaded", allProducts.length, "products");
            // Only log structure if there are issues
            if (!allProducts[0].NAME) {
                console.warn("⚠️ Product structure may be incorrect:", allProducts[0]);
            }
        }
        
        renderTable();
    } catch (error) {
        console.error("❌ Error fetching products:", error);
    }
}

function setupProductSearch() {
    const productSearch = document.getElementById("product-search");
    const productsTableContainer = document.getElementById("products-table-container");
    const clearBtn = document.getElementById("clear-search");
    
    // Clear button functionality
    clearBtn.addEventListener("click", function() {
        productSearch.value = "";
        productSearch.dataset.productId = "";
        productSearch.dataset.selectedProductName = "";
        clearBtn.style.display = "none";
        productsTableContainer.style.display = "none";
        
        // Reset table title
        document.getElementById("rotation-table-title").textContent = "ROTATION PAR MOIS";
        
        // Hide V2 toggle button when clearing product
        hideV2Toggle();
        
        // Disable date inputs when clearing product
        const startDateInput = document.getElementById("start-date");
        const endDateInput = document.getElementById("end-date");
        startDateInput.disabled = true;
        endDateInput.disabled = true;
        
        // Clear all tables and charts
        clearAllData();
    });
    
    productSearch.addEventListener("focus", function() {
        const searchValue = this.value.trim();
        if (searchValue) {
            // Re-filter products based on current input value
            filteredProducts = allProducts.filter(product => 
                product.NAME.toLowerCase().includes(searchValue.toLowerCase())
            );
            
            productsTableContainer.style.display = "block";
            renderTable();
        }
    });
    
    productSearch.addEventListener("input", debounce(function(e) {
        const searchValue = e.target.value.toLowerCase().trim();
        
        // Show/hide clear button
        clearBtn.style.display = searchValue ? "block" : "none";
        
        if (!searchValue) {
            filteredProducts = [...allProducts];
            productsTableContainer.style.display = "none";
        } else {
            filteredProducts = allProducts.filter(product => 
                product.NAME.toLowerCase().includes(searchValue)
            );
            
            productsTableContainer.style.display = "block";
        }
        
        currentPage = 1;
        renderTable();
    }, 300));
    
    // Hide table when clicking outside the wrapper
    document.addEventListener("click", function(e) {
        const wrapper = document.querySelector('.product-search-wrapper');
        if (!wrapper.contains(e.target)) {
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



// Function to select a product
async function selectProduct(product) {
    const productSearch = document.getElementById("product-search");
    const clearBtn = document.getElementById("clear-search");
    const productsTableContainer = document.getElementById("products-table-container");
    
    // Try to find the product ID field - check multiple possible field names
    let productId = product.M_PRODUCT_ID || product.ID || product.id || product.PRODUCT_ID || product.product_id;
    
    // If we still don't have an ID, try to use the first available field that looks like an ID
    if (!productId) {
        const idFields = Object.keys(product).filter(key => 
            key.toLowerCase().includes('id') || 
            key.toLowerCase().includes('product')
        );
        if (idFields.length > 0) {
            productId = product[idFields[0]];
        }
    }
    
    // If we still don't have an ID, use the NAME as fallback
    if (!productId) {
        productId = product.NAME;
        console.warn("⚠️ Using product NAME as ID fallback:", productId);
    }
    
    // DO NOT change the input field value - keep the search term
    // Only set the product ID for data fetching
    productSearch.dataset.productId = productId;
    productSearch.dataset.selectedProductName = product.NAME;
    
    // Show clear button
    clearBtn.style.display = "block";
    
    // Hide the dropdown table
    productsTableContainer.style.display = "none";
    
    // Update the ROTATION PAR MOIS table title
    document.getElementById("rotation-table-title").textContent = `ROTATION PAR MOIS - ${product.NAME}`;
    
    // Check reversed/voided stock movements to auto-set V2 mode
    try {
        const url = API_CONFIG.getApiUrl(`/fetch-reversed-voided-stock-movements?product_id=${productId}`);
        console.log("🔍 Checking reversed/voided stock movements:", url);
        
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        
        const data = await response.json();
        console.log("📊 Reversed/voided stock movements data:", data);
        
        // Use debug helper
        debugApiResponse(data, "Reversed/Voided Stock Movements");
        
        // Properly handle the difference value based on the actual API response structure
        let hasDifference = false;
        
        // Check if data has movements array
        if (data && data.movements && Array.isArray(data.movements)) {
            // If there are any movements, we should set V2 mode to false (VO included)
            // This is the opposite of the previous logic
            hasDifference = data.movements.length === 0;
            console.log(`🔢 Found ${data.movements.length} reversed/voided movements, hasDifference: ${hasDifference}`);
            
            // Alternatively, if summary contains a difference property, use that
            if (data.summary && 'difference' in data.summary) {
                const numericDifference = Number(data.summary.difference);
                // Reversed logic: If there's a difference, set V2 mode to false
                hasDifference = isNaN(numericDifference) || numericDifference === 0;
                console.log(`🔢 Summary difference: ${numericDifference}, hasDifference: ${hasDifference}`);
            }
        }
        
        // Set V2 mode based on the result
        isV2Mode = hasDifference;
        
        // Update the V2 toggle button text and show it
        showV2Toggle();
        updateV2ButtonText();
        
        console.log(`🔄 Auto-setting V2 Mode to: ${isV2Mode} based on reversed/voided movements (reversed logic)`);
    } catch (error) {
        console.error("❌ Error checking reversed/voided stock movements:", error);
        // Default to normal mode in case of error
        isV2Mode = false;
        showV2Toggle();
        updateV2ButtonText();
    }
    
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
    
    // Fetch product data in proper order - historique first, then rotation (for QTY_INITIAL calculation)
    try {
        await fetchHistoriqueRotation();
        await fetchRotationData();
        fetchHistogramData();
        refreshWeeklyTables();
    } catch (error) {
        console.error("❌ Error fetching product data:", error);
    }
    
    // Auto-show map and load data if both dates are available
    if (startDateInput.value && endDateInput.value) {
        autoShowMapWithData();
    }
}

// Function to clear all data
function clearAllData() {
    // Clear tables
    document.getElementById("historique-table").innerHTML = "<tr><td colspan='3' class='text-center text-gray-500'>Select a product</td></tr>";
    document.getElementById("rotation-table").innerHTML = "<tr><td colspan='4' class='text-center text-gray-500'>Select a product</td></tr>";
    document.getElementById("this-week-table").innerHTML = "<tr><td colspan='4' class='text-center text-gray-500'>Select a product</td></tr>";
    document.getElementById("last-week-table").innerHTML = "<tr><td colspan='4' class='text-center text-gray-500'>Select a product</td></tr>";
    
    // Hide chart
    const chartContainer = document.getElementById("chartContainer");
    if (chartContainer) {
        chartContainer.style.display = "none";
    }
    
    // Clear chart instance
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
    
    // Hide map data
    const mapContainer = document.getElementById('mapContainer');
    if (mapContainer && mapContainer.style.display !== 'none') {
        resetMap();
    }
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
            selectProduct(product);
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
    const productElement = document.getElementById("product-search");
    const productName = productElement.dataset.selectedProductName || productElement.value.trim();
    const productId = productElement.dataset.productId;

    console.log("Product Name:", `"${productName}"`, "Product ID:", productId); // ✅ Check if it's empty

    if (!productName || !productId) {
        console.error("❌ Missing product name or ID, not sending request.");
        return; 
    }

    try {
        const url = API_CONFIG.getApiUrl(`/fetchHistoriqueRotation?product_id=${productId}`);
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




// ✅ Add debounced function to prevent multiple calls
let rotationDataTimeout = null;

function debounceRotationData() {
    if (rotationDataTimeout) {
        clearTimeout(rotationDataTimeout);
    }
    
    rotationDataTimeout = setTimeout(async () => {
        const productInput = document.getElementById("product-search");
        const productName = productInput.dataset.selectedProductName || productInput.value.trim();
        
        if (productName) {
            try {
                await fetchHistoriqueRotation();
                await fetchRotationData();
            } catch (error) {
                console.error("❌ Error fetching data on date change:", error);
            }
        }
    }, 300); // 300ms delay
}

// ✅ Attach event listeners to trigger fetching when filters change
["start-date", "end-date"].forEach(id => {
    document.getElementById(id).addEventListener("change", debounceRotationData);
});


async function fetchRotationData() {
    // Prevent duplicate calls
    if (fetchRotationData.isRunning) {
        console.log("⚠️ fetchRotationData already running, skipping...");
        return;
    }
    
    fetchRotationData.isRunning = true;
    
    try {
        const productInput = document.getElementById("product-search");
        const startDateInput = document.getElementById("start-date");
        const endDateInput = document.getElementById("end-date");

        const productName = productInput.dataset.selectedProductName || productInput.value.trim();
        const productId = productInput.dataset.productId;
        
        if (!productName || !productId) {
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

        const url = API_CONFIG.getApiUrl(`/rotationParMois?product_id=${productId}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&v2_mode=${isV2Mode}`);
        console.log("🔗 Request URL:", url); // ✅ Debugging

        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        const data = await response.json();
        console.log("📥 Response Data:", data); // ✅ Debugging

        // Check if the response contains an error
        if (data && data.error) {
            throw new Error(`API Error: ${data.error}`);
        }

        // Check if data is an array
        if (!Array.isArray(data)) {
            throw new Error(`Invalid data format: expected array, got ${typeof data}`);
        }

        await updateRotationTable(data);
    } catch (error) {
        console.error("❌ Error fetching rotation data:", error);
        document.getElementById('rotation-table').innerHTML = "<tr><td colspan='4' class='text-center text-red-500'>Failed to load data</td></tr>";
    } finally {
        fetchRotationData.isRunning = false;
    }
}

// Function to calculate QTY_INITIAL for each period using API calls
let isCalculatingInitial = false; // Flag to prevent duplicate calculations

async function calculateQtyInitial(data) {
    // Prevent duplicate calculations
    if (isCalculatingInitial) {
        console.log("⚠️ QTY_INITIAL calculation already in progress, skipping...");
        return data;
    }
    
    isCalculatingInitial = true;
    
    try {
        // Validate data format
        if (!Array.isArray(data)) {
            console.error("❌ calculateQtyInitial: data is not an array:", typeof data);
            return data; // Return original data
        }
        
        // Get product ID
        const productElement = document.getElementById("product-search");
        const productId = productElement.dataset.productId;
        
        if (!productId) {
            console.error("❌ No product ID available for QTY_INITIAL calculation");
            return data; // Return original data without QTY_INITIAL
        }
        
        // Filter out TOTAL and MOYENNE rows for calculation, keep them for display
        const regularPeriods = data.filter(row => row.PERIOD !== "TOTAL" && row.PERIOD !== "MOYENNE");
        const specialRows = data.filter(row => row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE");
        
        // Sort regular periods chronologically
        regularPeriods.sort((a, b) => {
            const periodA = a.PERIOD || '';
            const periodB = b.PERIOD || '';
            return periodA.localeCompare(periodB);
        });
        
        console.log(`📊 Starting QTY_INITIAL calculation for ${regularPeriods.length} periods`);
        
        // Calculate QTY_INITIAL for each period by calling the API
        const periodsWithInitial = [];
        
        for (const period of regularPeriods) {
            try {
                // Convert period to date format (assuming period is like "2024-01" or "2024-01-01")
                let targetDate;
                if (period.PERIOD.includes('-')) {
                    // If period is already in date format, use it
                    if (period.PERIOD.split('-').length === 2) {
                        // Format: "2024-01" -> "2024-01-01"
                        targetDate = period.PERIOD + '-01';
                    } else {
                        // Format: "2024-01-15" -> use as is
                        targetDate = period.PERIOD;
                    }
                } else {
                    // Handle other formats if needed
                    targetDate = period.PERIOD + '-01-01';
                }
                
                // Call API to get initial stock for this date
                const url = API_CONFIG.getApiUrl(`/getInitialStock?date=${encodeURIComponent(targetDate)}&product_id=${productId}&v2_mode=${isV2Mode}`);
                console.log(`📤 Fetching initial stock for ${period.PERIOD} (${targetDate}) - V2 Mode: ${isV2Mode}`);
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.error) {
                    console.error(`❌ Error getting initial stock for ${period.PERIOD}:`, result.error);
                    period.QTY_INITIAL = 0;
                } else {
                    period.QTY_INITIAL = result.initial_stock || 0;
                    console.log(`✅ Got initial stock for ${period.PERIOD}: ${period.QTY_INITIAL}`);
                }
                
            } catch (error) {
                console.error(`❌ Error fetching initial stock for period ${period.PERIOD}:`, error);
                period.QTY_INITIAL = 0;
            }
            
            periodsWithInitial.push(period);
        }
        
        // For special rows (TOTAL, MOYENNE), calculate appropriate values
        specialRows.forEach(row => {
            if (row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE") {
                // Do not calculate or set QTY_INITIAL for TOTAL or MOYENNE rows
                row.QTY_INITIAL = null;
            }
        });
        
        console.log(`✅ QTY_INITIAL calculation completed for ${regularPeriods.length} periods`);
        
        // Return combined data with special rows at the end (they'll be moved to top in updateRotationTable)
        return [...periodsWithInitial, ...specialRows];
        
    } finally {
        isCalculatingInitial = false;
    }
}

async function updateRotationTable(data) {
    const tableBody = document.getElementById("rotation-table");
    tableBody.innerHTML = ""; // Clear previous data

    // Add validation for data format
    if (!data) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data received</td></tr>`;
        return;
    }

    if (!Array.isArray(data)) {
        console.error("❌ Invalid data format for rotation table:", data);
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4 text-red-500">Invalid data format received</td></tr>`;
        return;
    }

    if (data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    try {
        // Calculate QTY_INITIAL for each period (now async)
        const dataWithInitial = await calculateQtyInitial(data);

        let specialRows = "";
        let normalRows = "";

        dataWithInitial.forEach(row => {
            const rowHTML = `
                <tr class="dark:bg-gray-700 ${row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE" ? "font-bold" : ""}">
                    <td class="border px-3 py-2 dark:border-gray-600">${row.PERIOD ?? 'N/A'}</td>
                    <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_VENDU ?? 0)}</td>
                    <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_ACHETÉ ?? 0)}</td>
                    <td class="border px-3 py-2 dark:border-gray-600">${(row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE") ? '' : formatNumberWithSpace(row.QTY_INITIAL ?? 0)}</td>
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

        console.log("✅ Table updated successfully with QTY_INITIAL calculations from API.");
    } catch (error) {
        console.error("❌ Error updating rotation table with QTY_INITIAL:", error);
        
        // Fallback: display table without QTY_INITIAL
        let specialRows = "";
        let normalRows = "";

        data.forEach(row => {
            const rowHTML = `
                <tr class="dark:bg-gray-700 ${row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE" ? "font-bold" : ""}">
                    <td class="border px-3 py-2 dark:border-gray-600">${row.PERIOD ?? 'N/A'}</td>
                    <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_VENDU ?? 0)}</td>
                    <td class="border px-3 py-2 dark:border-gray-600">${formatNumberWithSpace(row.QTY_ACHETÉ ?? 0)}</td>
                    <td class="border px-3 py-2 dark:border-gray-600">Error</td>
                </tr>
            `;

            if (row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE") {
                specialRows += rowHTML;
            } else {
                normalRows += rowHTML;
            }
        });

        tableBody.innerHTML = specialRows + normalRows;
    }
}

// Function to initialize empty tables
function initializeEmptyTables() {
    document.getElementById("historique-table").innerHTML = "<tr><td colspan='3' class='text-center text-gray-500'>Select a product to view data</td></tr>";
    document.getElementById("rotation-table").innerHTML = "<tr><td colspan='4' class='text-center text-gray-500'>Select a product to view data</td></tr>";
    document.getElementById("this-week-table").innerHTML = "<tr><td colspan='4' class='text-center text-gray-500'>Select a product to view data</td></tr>";
    document.getElementById("last-week-table").innerHTML = "<tr><td colspan='4' class='text-center text-gray-500'>Select a product to view data</td></tr>";
    
    // Initialize headers for weekly tables
    document.getElementById("this-week-header").innerHTML = "<th class='border px-3 py-2'></th><th class='border px-3 py-2'>Select Product</th>";
    document.getElementById("last-week-header").innerHTML = "<th class='border px-3 py-2'></th><th class='border px-3 py-2'>Select Product</th>";
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
    const productInput = document.getElementById("product-search");
    const productName = productInput.dataset.selectedProductName || productInput.value.trim();
    const productId = productInput.dataset.productId;
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!productName || !productId || !startDate || !endDate) {
        console.error("❌ Missing required fields. Not downloading file.");
        return;
    }

    const url = API_CONFIG.getApiUrl(`/download-rotation-par-mois-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product_id=${productId}`);
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
    const productInput = document.getElementById("product-search");
    const productName = productInput.dataset.selectedProductName || productInput.value.trim();
    const productId = productInput.dataset.productId;
    
    if (!productName || !productId) {
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
    const url = API_CONFIG.getApiUrl(`/rotationParMois?product_id=${productId}&start_date=${range.start}&end_date=${range.end}`);
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

// Function to refresh both weekly tables
async function refreshWeeklyTables() {
    try {
        // Fetch data for this week and last week
        await fetchWeekRotationData('this-week');
        await fetchWeekRotationData('last-week');
        console.log("✅ Weekly tables refreshed successfully");
    } catch (error) {
        console.error("❌ Error refreshing weekly tables:", error);
    }
}

// Debug helper function
function debugApiResponse(response, title) {
    console.log(`🔍 DEBUG ${title || 'API Response'}: `, response);
    
    if (response === null) {
        console.log(`   - Value is null`);
        return;
    }
    
    if (response === undefined) {
        console.log(`   - Value is undefined`);
        return;
    }
    
    if (typeof response === 'object') {
        console.log(`   - Type: ${Array.isArray(response) ? 'Array' : 'Object'}`);
        console.log(`   - Keys: ${Object.keys(response).join(', ')}`);
        
        if ('difference' in response) {
            console.log(`   - difference value: ${response.difference}`);
            console.log(`   - difference type: ${typeof response.difference}`);
            console.log(`   - difference == 0: ${response.difference == 0}`);
            console.log(`   - difference === 0: ${response.difference === 0}`);
            console.log(`   - Number(difference) === 0: ${Number(response.difference) === 0}`);
        }
    } else {
        console.log(`   - Type: ${typeof response}`);
        console.log(`   - Value: ${response}`);
    }
}

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

function getHighlightStyle(feature) {
    const wilayaName = feature.properties.name;
    const qty = getWilayaQty(wilayaName);
    
    return {
        fillColor: getColorByQty(qty),
        weight: 4,
        opacity: 1,
        color: '#1f2937', // Darker border on hover
        dashArray: '',
        fillOpacity: 1.0  // Full opacity on hover
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
        mousemove: function(e) {
            updateTooltipPosition(e.originalEvent);
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
    mapTooltip.classList.add('show');
    updateTooltipPosition(e.originalEvent);
}

// Hide tooltip
function hideMapTooltip() {
    if (mapTooltip) {
        mapTooltip.classList.remove('show');
        setTimeout(() => {
            if (!mapTooltip.classList.contains('show')) {
                mapTooltip.style.display = 'none';
            }
        }, 200); // Match the CSS transition duration
    }
}

// Update tooltip position
function updateTooltipPosition(e) {
    if (mapTooltip && mapTooltip.classList.contains('show')) {
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
    const productInput = document.getElementById("product-search");
    const productName = productInput.value.trim();
    const productId = productInput.dataset.productId;
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!productName || !productId) {

        alert('Please select a product first');
        return;
    }

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    try {
        let url = API_CONFIG.getApiUrl(`/fetchZonerotation?start_date=${startDate}&end_date=${endDate}`);
        if (productId) {
            url += `&product_id=${productId}`;
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
    const productInput = document.getElementById("product-search");
    const productName = productInput.value.trim();
    const productId = productInput.dataset.productId;
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    
    // Only proceed if we have all required data
    if (productName && productId && startDate && endDate) {
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
    const productInput = document.getElementById("product-search");
    const productName = productInput.value.trim();
    const productId = productInput.dataset.productId;
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const mapContainer = document.getElementById('mapContainer');
    
    // Only update if product is selected, dates are available, and map is visible
    if (productName && productId && startDate && endDate && mapContainer.style.display !== 'none') {
        loadMapData();
    }
}

// V2 Mode state management
let isV2Mode = false;
console.log("🔄 Initializing V2 Mode to:", isV2Mode);

function setupV2Toggle() {
    const v2ToggleButton = document.getElementById("v2ToggleButton");
    
    if (v2ToggleButton) {
        console.log("🔄 Setting up V2 toggle button event listener");
        
        v2ToggleButton.addEventListener("click", function() {
            // Toggle mode
            isV2Mode = !isV2Mode;
            console.log(`🔄 V2 Mode toggled to: ${isV2Mode}`);
            
            // Update button appearance
            updateV2ButtonText();
            
            // Refresh rotation data with new mode
            debounceRotationData();
        });
    } else {
        console.error("❌ V2 toggle button not found during setup");
    }
}

function showV2Toggle() {
    const v2ToggleButton = document.getElementById("v2ToggleButton");
    if (v2ToggleButton) {
        console.log("🔄 Showing V2 toggle button");
        v2ToggleButton.style.display = "inline-block";
        // Update button text and styling when showing
        updateV2ButtonText();
    } else {
        console.error("❌ V2 toggle button not found when trying to show it");
    }
}

function hideV2Toggle() {
    const v2ToggleButton = document.getElementById("v2ToggleButton");
    if (v2ToggleButton) {
        console.log("🔄 Hiding V2 toggle button");
        v2ToggleButton.style.display = "none";
    } else {
        console.error("❌ V2 toggle button not found when trying to hide it");
    }
}

// Update V2 button text based on current mode
function updateV2ButtonText() {
    const v2ToggleButton = document.getElementById("v2ToggleButton");
    if (v2ToggleButton) {
        console.log(`📣 Updating V2 button text. Current isV2Mode: ${isV2Mode}`);
        
        // Update text based on current mode - with reversed logic
        if (isV2Mode) {
            v2ToggleButton.textContent = "❌ V2 Mode (VO excluded)";
            console.log("📣 Setting button text to: ❌ V2 Mode (VO excluded)");
        } else {
            v2ToggleButton.textContent = "✅ V2 Mode (VO included)";
            console.log("📣 Setting button text to: ✅ V2 Mode (VO included)");
        }
        
        // Update button styling based on current mode
        if (isV2Mode) {
            v2ToggleButton.classList.remove("bg-green-600", "hover:bg-green-700", "dark:bg-green-500", "dark:hover:bg-green-600");
            v2ToggleButton.classList.add("bg-red-600", "hover:bg-red-700", "dark:bg-red-500", "dark:hover:bg-red-600");
        } else {
            v2ToggleButton.classList.remove("bg-red-600", "hover:bg-red-700", "dark:bg-red-500", "dark:hover:bg-red-600");
            v2ToggleButton.classList.add("bg-green-600", "hover:bg-green-700", "dark:bg-green-500", "dark:hover:bg-green-600");
        }
    } else {
        console.error("❌ V2 toggle button not found in the DOM");
    }
}
</script>


     

    
   
     
      
      

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     





</body>

</html>
