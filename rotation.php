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
    <script src="main.js" defer></script>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="rotation.css">
    <style>
        /* Responsive Chart Container */
@media (max-width: 1024px) {
    #chartContainer {
        height: 400px; /* Reduce height on medium screens */
    }
}

@media (max-width: 768px) {
    #chartContainer {
        height: 300px; /* Smaller height for tablets */
    }
}

@media (max-width: 480px) {
    #chartContainer {
        height: 250px; /* Compact height for mobile screens */
    }
}




/* Sidebar Hidden by Default */
.sidebar-hidden {
    transform: translateX(-100%);
}

/* Sidebar Appears Smoothly */
.sidebar {
    transition: transform 0.3s ease-in-out;
}

/* Sidebar Stays Open Until Mouse Leaves */
.sidebar:hover {
    transform: translateX(0);
}

    </style>


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 

    <!-- Dark/Light Mode Toggle Button -->
<!-- Dark/Light Mode Toggle Button -->
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

<!-- <script>
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
</script> -->
<script>
    // Fetch sidebar content dynamically
 fetch("side")
    .then(response => response.text())
    .then(html => {
        let container = document.getElementById("sidebar-container");
        let tempDiv = document.createElement("div");
        tempDiv.innerHTML = html;

        // Insert sidebar content into the page
        container.innerHTML = tempDiv.innerHTML;

        // Reattach event listeners for submenu toggles (Products, Recaps)
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

        // Auto-hide sidebar when not hovered
        document.addEventListener('mousemove', (event) => {
            if (event.clientX < 50) {  // Mouse near the left edge (50px)
                sidebar.classList.remove('sidebar-hidden');
                content.classList.remove('content-full');
            }
        });

        // Hide sidebar when the mouse leaves it
        sidebar.addEventListener('mouseleave', () => {
            sidebar.classList.add('sidebar-hidden');
            content.classList.add('content-full');
        });

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



      
<!-- Popup (Modal) -->
<div class="modal" id="product-modal">
    <div class="modal-content">
        <span class="close-btn" id="close-modal">&times;</span>
        <h3>All Products</h3>
        <table class="popup-table">
    <thead>
        <tr>
            <th>#</th> <!-- Numbering Column -->
            <th>Product Name</th>
        </tr>
    </thead>
    <tbody id="product-table-body">
        <!-- Rows will be added dynamically -->
    </tbody>
</table>


    </div>
</div>



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
    
    <div class="custom-dropdown">
        <select id="product-select">
            <option value="">Select a product</option>
        </select>
        <div class="dropdown-list" id="dropdown-list"></div>
    </div>

    <button class="see-all-btn" id="see-all-btn">See All Products</button>
</div>

<!-- Modal for showing all products -->
<div id="product-modal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close">&times;</span>
        <table class="product-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                </tr>
            </thead>
            <tbody id="product-table-body"></tbody>
        </table>
    </div>
</div>




        <br>

        <button id="downloadExcel_rotation"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span> ROTATION PAR MOIS Download</span>
        </button>
<br>
 
<div class="flex gap-6">
    <!-- Left Side: Tables -->
    <div class="w-1/4">
        <!-- First Table: Smaller -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-4">
            <div class="flex justify-between items-center p-3">
                <h2 class="text-base font-semibold dark:text-black">HISTORIQUE</h2>
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
                <h2 class="text-base font-semibold dark:text-black">ROTATION PAR MOIS</h2>
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
    <div id="chartContainer" class="canvas-container rounded-lg bg-white shadow-md dark:bg-gray-800 h-[500px] w-full flex justify-center items-center" style="display: none;">
    <canvas id="histogramChart" class="w-full h-full"></canvas>
</div>
<br><br>

<!-- Button Styles -->
<style>
    .btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn:hover {
        background-color: #45a049;
        transform: scale(1.05);
    }

    .btn i {
        font-size: 18px;
    }
</style>

</div>

<br><br>

<script>





document.addEventListener("DOMContentLoaded", () => {
    updateToggleButtonText();

    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    const productSearch = document.getElementById("product-search");

    // Initially disable date inputs
    startDate.disabled = true;
    endDate.disabled = true;

    // Enable dates only if a product is selected
    productSearch.addEventListener("input", function () {
        if (this.value.trim()) {
            startDate.disabled = false;
            endDate.disabled = false;
        } else {
            startDate.disabled = true;
            endDate.disabled = true;
            startDate.value = "";
            endDate.value = "";
        }
    });

    // Prevent date change if no product is selected
    startDate.addEventListener("click", function (event) {
        if (startDate.disabled) {
            event.preventDefault();
            alert("Please select a product first.");
        }
    });

    endDate.addEventListener("click", function (event) {
        if (endDate.disabled) {
            event.preventDefault();
            alert("Please select a product first.");
        }
    });
});




let chartInstance = null; 
let currentChartType = Math.random() < 0.5 ? "bar" : "line"; // Random chart on first load

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

    const url = `http://192.168.1.156:5000/histogram?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product=${encodeURIComponent(productName)}`;

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



let allProducts = [];
let lastFetchTime = 0;
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

// ✅ Fetch products from API (Only if data is outdated)
async function fetchProducts(forceRefresh = false) {
    const currentTime = Date.now();
    if (!forceRefresh && allProducts.length && (currentTime - lastFetchTime) < CACHE_DURATION) {
        console.log("✅ Using cached product data");
        return;
    }

    try {
        const response = await fetch("http://192.168.1.156:5000/fetch-rotation-product-data");
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        allProducts = await response.json();
        lastFetchTime = currentTime;
    } catch (error) {
        console.error("❌ Error fetching products:", error);
        document.getElementById("product-select").innerHTML = "<option value=''>Failed to load products</option>";
    }
}

// ✅ Debounced input handling for search field
function debounce(func, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

// ✅ Filter products based on search input
async function filterProducts(searchValue) {
    if (!allProducts.length) await fetchProducts(); // Ensure data is available

    const dropdownList = document.getElementById("dropdown-list");
    dropdownList.innerHTML = ""; // Clear previous options

    if (!searchValue.trim()) {
        dropdownList.style.display = "none";
        return;
    }

    const filteredProducts = allProducts.filter(product =>
        product.NAME.toLowerCase().includes(searchValue.toLowerCase())
    );

    if (!filteredProducts.length) {
        dropdownList.style.display = "none";
        return;
    }

    const fragment = document.createDocumentFragment();

    filteredProducts.forEach(product => {
        const div = document.createElement("div");
        div.textContent = product.NAME;
        div.classList.add("dropdown-item");
        div.dataset.value = product.NAME.trim();

        div.addEventListener("click", function () {
            document.getElementById("product-search").value = product.NAME;
            document.getElementById("product-select").value = product.NAME;
            dropdownList.style.display = "none";
            console.log("✅ Selected Product:", product.NAME);
            fetchHistoriqueRotation();
        });

        fragment.appendChild(div);
    });

    dropdownList.appendChild(fragment);
    dropdownList.style.display = "block";
}

// ✅ Populate table when "See All Products" is clicked
async function populateTable() {
    await fetchProducts();
    const productTableBody = document.getElementById("product-table-body");
    productTableBody.innerHTML = "";

    const fragment = document.createDocumentFragment();
    
    allProducts.forEach((product, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `<td>${index + 1}</td><td>${product.NAME}</td>`;

        row.addEventListener("click", function () {
            document.getElementById("product-search").value = product.NAME;
            document.getElementById("product-select").value = product.NAME;
            document.getElementById("product-modal").style.display = "none";
            fetchHistoriqueRotation();
        });

        fragment.appendChild(row);
    });

    productTableBody.appendChild(fragment);
    document.getElementById("product-modal").style.display = "flex";
}

document.addEventListener("DOMContentLoaded", async function () {
    await fetchProducts(); // Prefetch products

    const productSearch = document.getElementById("product-search");
    const dropdownList = document.getElementById("dropdown-list");
    const seeAllBtn = document.getElementById("see-all-btn");
    const closeModal = document.getElementById("close-modal");
    const productSelect = document.getElementById("product-select");

    // ✅ Attach event listeners
    productSearch.addEventListener("input", debounce(function () {
        filterProducts(this.value);
    }, 300));

    productSearch.addEventListener("focus", function () {
        if (productSearch.value.trim()) dropdownList.style.display = "block";
    });

    document.addEventListener("click", function (event) {
        if (!document.querySelector(".custom-dropdown").contains(event.target)) {
            dropdownList.style.display = "none";
        }
    });

    seeAllBtn.addEventListener("click", populateTable);
    closeModal.addEventListener("click", () => (document.getElementById("product-modal").style.display = "none"));
    productSelect.addEventListener("change", fetchHistoriqueRotation);
});


async function fetchHistoriqueRotation() {
    const productName = document.getElementById("product-search").value.trim();

    console.log("Product Name:", `"${productName}"`); // ✅ Check if it's empty

    if (!productName) {
        console.error("❌ Missing product name, not sending request.");
        return; 
    }

    try {
        const url = `http://192.168.1.156:5000/fetchHistoriqueRotation?product=${encodeURIComponent(productName)}`;
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

    const url = `http://192.168.1.156:5000/rotationParMois?product=${encodeURIComponent(productName)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
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

    const url = `http://192.168.1.156:5000/download-rotation-par-mois-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product=${encodeURIComponent(productName)}`;
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


     

    
   
     
      
      

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     





</body>

</html>
