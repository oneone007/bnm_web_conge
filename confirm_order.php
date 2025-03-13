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
    <link rel="stylesheet" href="journal.css">


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 <!-- Include SweetAlert2 Library (Add this to your HTML head if not already included) -->



    <!-- Dark/Light Mode Toggle Button -->
    <div id="themeSwitcher">
        <input type="checkbox" class="checkbox" id="themeToggle">
        <label for="themeToggle" class="checkbox-label">
            <span class="icon sun">☀️</span>
            <span class="icon moon">🌙</span>
        </label>
        <div id="lottieContainer" style="width: 200px; height: 150px; margin-top: 10px;"></div>
    
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


<br>

<div class="search-container">
            <div>
                <label for="BCCB_confirm">Search BCCB:</label>
                <input type="text" id="bccb_confirm" placeholder="Search for BCCB ...">
            </div>

           
        
        </div>
        <br>
        <button id="refresh-btn" class="p-3 bg-white text-blue-500 rounded-full shadow-lg hover:shadow-xl border border-blue-500 transition duration-200 flex items-center justify-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
    </svg>
</button>

        <br>

        <!-- <button id="downloadExcel_journal"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Journal de vente Download</span>
        </button> -->




        <br>
        
        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Ordre a confirmer</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
        <th data-column="NDOCUMENT" onclick="sortorderconfirmedTable('NDOCUMENT')" class="border px-4 py-2">NDocument</th>
        <th data-column="TIER" onclick="sortorderconfirmedTable('TIER')" class="border px-4 py-2">Tiers</th>

            <th data-column="DATECOMMANDE" onclick="sortorderconfirmedTable('DATECOMMANDE')" class="border px-4 py-2">Date Commande</th>
            <th data-column="VENDEUR" onclick="sortorderconfirmedTable('VENDEUR')" class="border px-4 py-2">Vendeur</th>
            <th data-column="MARGE" onclick="sortorderconfirmedTable('MARGE')" class="border px-4 py-2">Marge</th>
            <th data-column="MONTANT" onclick="sortorderconfirmedTable('MONTANT')" class="border px-4 py-2">Montant</th>
            <th data-column="ORGANISATION" onclick="sortorderconfirmedTable('ORGANISATION')" class="border px-4 py-2">Organization</th>

        </tr>
    </thead>
    <tbody id="order-confirmer-table" class="dark:bg-gray-800">
        
    </tbody>
</table>


</div>
<br><br>



<div id="bccb-product-container" class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800" style="display: none;">
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">BCCB Product</h2>

        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
            <thead>
                <tr class="table-header dark:bg-gray-700">
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">PRODUCT</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">QTY</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">REMISE</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">MARGE</th>
                </tr>
            </thead>
            <tbody id="confirmed-bccb-product-table" class="dark:bg-gray-800">
            
            </tbody>
        </table>
    </div>
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



         
// Fetch data when filters are applied for journal vente


// Show loader animation


// Hide loader after fetching data
document.addEventListener("DOMContentLoaded", function () {
    fetchOrderConfirmed();

    const searchInput = document.getElementById("bccb_confirm");

    // Listen for input changes in the search field
    searchInput.addEventListener("input", function () {
        const bccb = searchInput.value.trim();
        if (bccb) {
            fetchBccbProduct(bccb); // Fetch data when input changes
        }
    });
});
document.getElementById("refresh-btn").addEventListener("click", async function () {
    await fetchOrderConfirmed();

    // Restore search input value and re-trigger search event
    const searchInput = document.getElementById("bccb_confirm");
    const currentValue = searchInput.value;

    if (currentValue) {
        searchInput.value = currentValue;
        searchInput.dispatchEvent(new Event("input"));
    }
});



async function fetchOrderConfirmed() {
    try {
        const response = await fetch('http://192.168.1.156:5000/order_confirmed');
        const data = await response.json();
        
        const tableBody = document.getElementById('order-confirmer-table');
        tableBody.innerHTML = '';

        let totalRow = null;

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.classList.add("cursor-pointer", "hover:bg-gray-200", "dark:hover:bg-gray-700");

            if (row.ORGANISATION === 'Total') {
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
                <td class="border px-4 py-2">${row.ORGANISATION || ''}</td>
            `;

            // Make row selectable
            tr.addEventListener("click", function () {
                document.querySelectorAll("tr").forEach(r => r.classList.remove("bg-blue-200", "dark:bg-gray-600")); 
                tr.classList.add("bg-blue-200", "dark:bg-gray-600"); // Highlight selected row
                
                const searchInput = document.getElementById("bccb_confirm");
                searchInput.value = row.NDOCUMENT; // Fill search input
                searchInput.dispatchEvent(new Event("input")); // Trigger search event
            });

            if (row.ORGANISATION === 'Total') {
                totalRow = tr;
            } else {
                tableBody.appendChild(tr);
            }
        });

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

    const url = new URL("http://192.168.1.156:5000/fetchBCCBProduct");
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