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
    <script src="theme.js"></script>

    <link rel="stylesheet" href="rotation.css">
    <style>




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


       


 
<div class="flex gap-6">
    <!-- Left Side: Tables -->
    <div class="w-1/4">
        <!-- First Table: Smaller -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-4">
            <div class="flex justify-between items-center p-3">
                <h2 class="text-base font-semibold text-black dark:text-white">HISTORIQUE</h2>
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
        <br>

        <!-- Second Table: Taller -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="flex justify-between items-center p-3">
                <h2 class="text-base font-semibold text-black dark:text-white">ROTATION PAR MOIS</h2>
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

    <!-- Right Side: Larger Canvas -->
 
    <div class="w-3/4 flex flex-col gap-6">
    <div class="flex gap-4">
        <button id="toggleChartBtn" class="btn" onclick="toggleChartType()">
            <i class="fas fa-chart-line"></i> Switch to Graph
        </button> 
        <button id="fullscreenBtn" class="btn" onclick="toggleFullscreen()">
            <i class="fas fa-expand"></i> Full Screen
        </button>
    </div>
    <div id="chartContainer" class="canvas-container rounded-lg  shadow-md dark:bg-gray-800 h-[500px] w-full flex justify-center items-center" style="display: none;">
    <canvas id="histogramChart" class="w-full h-full"></canvas>
</div>
<br><br>

<!-- Button Styles -->

</div> 

<br><br>
  
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
            plugins: { legend: { position: "top" } },
            scales: {
                x: { title: { display: true, text: "Period" } },
                y: { title: { display: true, text: "Quantity" }, beginAtZero: true }
            }
        }
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
    document.getElementById("start-date")?.addEventListener("change", fetchHistogramData);
    document.getElementById("end-date")?.addEventListener("change", fetchHistogramData);
});

function setupDateInputs() {
    const productSearch = document.getElementById("product-search");
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    
    // Set end date to today initially
    const today = new Date().toISOString().split("T")[0];
    endDate.value = today;
    
    // Enable dates only when product is selected, but do NOT reset their values
    productSearch.addEventListener("input", function() {
        if (this.value.trim()) {
            startDate.disabled = false;
            endDate.disabled = false;
        } else {
            startDate.disabled = true;
            endDate.disabled = true;
            startDate.value = "";
            endDate.value = today;
        }
    });

    // When start date changes, set end date to today if not already set
    startDate.addEventListener("change", function() {
        if (!endDate.value) {
            endDate.value = today;
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
            // Enable date inputs, but do NOT reset their values
            document.getElementById("start-date").disabled = false;
            document.getElementById("end-date").disabled = false;
            // Do NOT reset start or end date here
            fetchHistoriqueRotation();
            fetchRotationData();
            fetchHistogramData();
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
            <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_DISPO ?? 0}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${row.DERNIER_ACHAT ?? 0}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${formattedDate}</td>
        </tr>
    `;
}






// ✅ Attach event listeners to trigger fetching when filters change
["start-date", "end-date", "product-search"].forEach(id => {
    document.getElementById(id).addEventListener("change", fetchRotationData);
});

// Clear search input and date fields, then trigger function on click
document.getElementById("product-search").addEventListener("click", function () {
    this.value = ""; // Clear search input
    document.getElementById("start-date").value = ""; // Clear start date
    document.getElementById("end-date").value = ""; // Clear end date

    // Trigger change event for all to refresh results
    ["start-date", "end-date", "product-search"].forEach(id => {
        document.getElementById(id).dispatchEvent(new Event("change"));
    });
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
                <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_VENDU ?? 0}</td>
                <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_ACHETÉ ?? 0}</td>
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
    const productSelect = document.getElementById("product-select");
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");

    // Initially disable date inputs
    startDate.disabled = true;
    endDate.disabled = true;

    // Set end date to today initially
    const today = new Date().toISOString().split("T")[0];
    endDate.value = today;

    function enableDateInputs() {
        if (productSelect.value) {
            startDate.disabled = false;
            endDate.disabled = false;
        } else {
            startDate.disabled = true;
            endDate.disabled = true;
            startDate.value = "";
            endDate.value = today;
        }
    }

    productSelect.addEventListener("change", () => {
        enableDateInputs();
        fetchRotationData(); // Fetch new data when product changes
    });

    startDate.addEventListener("change", () => {
        if (!endDate.value) {
            endDate.value = today;
        }
        fetchRotationData(); // Fetch new data when start date changes
    });

    endDate.addEventListener("change", () => {
        fetchRotationData(); // Fetch new data when end date changes
    });

    // Initial fetch if product is already selected
    if (productSelect.value) {
        enableDateInputs();
        fetchRotationData();
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




// ✅ Attach event listeners to trigger fetching when filters change
["start-date", "end-date", "product-search"].forEach(id => {
    document.getElementById(id).addEventListener("change", fetchRotationData);
});

</script>


     

    
   
     
      
      

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     





</body>

</html>
