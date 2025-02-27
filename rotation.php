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
    <link rel="stylesheet" href="recap.css">
    <style>
        .dark td {
            color: #000000 !important;
            /* Force black text in dark mode */
            background-color: #d1d5db;
            /* Light gray background for contrast */
        }

        .dark h2 {
            color: #000000 !important;
            /* Force black text in dark mode */
            background-color: #d1d5db;
            /* Light gray background for contrast */
        }


        .dark label {
            color: white !important;
        }

        /* Positioning the Dark Mode Toggle on Top Right */
      
        .download-container {
    display: flex;
    justify-content: flex-end;
    padding: 0 16px 12px 16px;
}
.download-wrapper {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 50px; /* Reduced for responsiveness */
      margin-top: 20px;
      padding: 10px;
  }

  .download-wrapper button {
      display: flex;
      align-items: center;
      gap: 10px;
      background-color: white;
      border: 1px solid #d1d5db;
      color: #374151;
      padding: 12px 24px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease-in-out;
  }

  .download-wrapper button:hover {
      background-color: #f3f4f6;
      transform: scale(1.05);
  }

  .download-wrapper button img {
      width: 24px;
      height: 24px;
  }

  /* Responsive Styles */
  @media (max-width: 768px) {
      .download-wrapper {
          flex-direction: column;
          align-items: center;
          gap: 20px;
      }

      .download-wrapper button {
          width: 90%; /* Full width for smaller screens */
          justify-content: center;
      }
  }

  .search-container {
      display: grid;
      grid-template-columns: repeat(3, minmax(250px, 1fr)); /* 3 columns per row */
      gap: 16px;
      padding: 20px;
      background: #f9fafb;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .search-container label {
      display: block;
      font-weight: 600;
      color: #374151;
      margin-bottom: 6px;
  }

  .search-container input {
      width: 100%;
      padding: 12px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease-in-out;
      background-color: white;
      color: #111827;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .search-container input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
  }

  /* Dark Mode */
  .dark .search-container {
      background: #1f2937;
      box-shadow: none;
  }

  .dark .search-container label {
      color: #e5e7eb;
  }

  .dark .search-container input {
      background-color: #374151;
      color: white;
      border: 1px solid #4b5563;
      box-shadow: none;
  }

  .dark .search-container input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
      .search-container {
          grid-template-columns: repeat(2, minmax(250px, 1fr)); /* 2 per row on tablets */
      }
  }

  @media (max-width: 768px) {
      .search-container {
          grid-template-columns: 1fr; /* 1 per row on mobile */
      }
  }


.date-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 600px; /* Adjust width as needed */
    width: 100%; /* Ensures it doesn't exceed max-width */
    margin: 0 auto; /* Centers the container */
}

@media (max-width: 768px) {
    .date-container {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
        max-width: 90%; /* Allows slight expansion on smaller screens */
    }
}


.date-container label {
    font-weight: 600;
    color: #374151;
}

.date-container input {
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease-in-out;
    background-color: white;
    color: #111827;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.date-container input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
}

/* Dark Mode */
.dark .date-container {
    background: #1f2937;
    box-shadow: none;
}

.dark .date-container label {
    color: #e5e7eb;
}

.dark .date-container input {
    background-color: #374151;
    color: white;
    border: 1px solid #4b5563;
}

.dark .date-container input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
    .date-container {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
}

/* Hide Default Checkbox */
/* Hide Default Checkbox */
.checkbox {
    display: none;
}

/* Toggle Background */
.checkbox-label {
    width: 60px;
    height: 30px;
    background: #f97316; /* Light Mode Orange */
    display: flex;
    align-items: center;
    border-radius: 50px;
    position: relative;
    cursor: pointer;
    padding: 5px;
    transition: background 0.3s ease-in-out;
}

/* Ball */
.ball {
    width: 24px;
    height: 24px;
    background: white;
    position: absolute;
    border-radius: 50%;
    transition: transform 0.3s ease-in-out;
    left: 5px;
}

/* Icons */
.icon {
    font-size: 16px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    transition: opacity 0.3s ease-in-out;
}

/* Sun (Left) */
.sun {
    left: 10px;
    color: white;
}

/* Moon (Right) */
.moon {
    right: 10px;
    color: white;
    opacity: 0; /* Hidden in Light Mode */
}

/* Dark Mode */
html.dark .checkbox-label {
    background: #1f2937; /* Dark Mode Gray */
}

html.dark .ball {
    transform: translateX(30px);
}

html.dark .sun {
    opacity: 0; /* Hide Sun */
}

html.dark .moon {
    opacity: 1; /* Show Moon */
}

/* Theme Switcher Position */
#themeSwitcher {
    position: sticky;
    top: 10px;
    right: 10px;
    padding: 10px;
    z-index: 50;
}

.product-container {
    display: flex;
    flex-direction: column;
    width: 320px;
    margin-top: 10px;
}

/* Search Input */
#product-search {
    padding: 10px;
    border: 1px solid #3a506b; /* Dark border */
    border-radius: 5px;
    width: 100%;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease, background-color 0.3s, color 0.3s;
    
    /* Light Mode */
    background-color: white;  
    color: black;
}

#product-search:focus {
    border-color: #007bff;
}

/* Dropdown */
.custom-dropdown {
    position: relative;
    width: 100%;
}

/* Dropdown Select Box */
.custom-dropdown select {
    width: 100%;
    height: 40px;
    padding: 8px;
    border: 1px solid #3a506b; /* Dark border */
    border-radius: 5px;
    background-color: white;
    color: black;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s, color 0.3s;
}

/* Dropdown List */
.custom-dropdown .dropdown-list {
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #3a506b;
    border-radius: 5px;
    background-color: white;
    color: black;
    display: none;
    z-index: 100;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    padding: 5px;
    transition: background-color 0.3s, color 0.3s;
}

/* Dropdown Items */
.custom-dropdown .dropdown-list div {
    padding: 8px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
    white-space: normal;
    border-bottom: 1px solid #ccc;
    transition: background-color 0.3s, color 0.3s;
}

.custom-dropdown .dropdown-list div:last-child {
    border-bottom: none;
}

/* Hover Effect */
.custom-dropdown .dropdown-list div:hover {
    background-color: #007bff;
    color: white;
}



    /* Button */
    .see-all-btn {
        margin-top: 10px;
        padding: 8px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        text-align: center;
    }

    .see-all-btn:hover {
        background-color: #0056b3;
    }

    /* Popup (Modal) */
    .modal {
    display: none; 
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Ensure it is above other elements */
}

/* Modal content */
.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    max-height: 80vh; /* Prevents overflow */
    overflow-y: auto; /* Scroll if too large */
    position: relative; /* Needed for close button positioning */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

/* Close button */
.modal-content .close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #333;
    background: none;
    border: none;
}

.modal-content .close-btn:hover {
    color: red;
}

       /* Styles specific to the popup table */
       .modal-content table.popup-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .modal-content table.popup-table th,
    .modal-content table.popup-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .modal-content table.popup-table th {
        background-color: #007bff;
        color: white;
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

<script>
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




        <br>

        <button id="downloadExcel_totalrecap"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Total Recap Download</span>
        </button>

 
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
        <div class="canvas-container rounded-lg bg-white shadow-md dark:bg-gray-800 h-80"></div>
        <div class="canvas-container rounded-lg bg-white shadow-md dark:bg-gray-800 h-80"></div>
    </div>
</div>



<script>
    window.onload = () => {
    document.getElementById("start-date").value = "";
    document.getElementById("end-date").value = "";
    document.getElementById("product-select").value = "";
};

let allProducts = []; // ✅ Ensure products are available globally

async function fetchProducts() {
    try {
        const response = await fetch("http://127.0.0.1:5003/fetch-rotation-product-data");
        allProducts = await response.json();
    } catch (error) {
        console.error("Error fetching products:", error);
        document.getElementById("product-select").innerHTML = "<option value=''>Failed to load products</option>";
    }
}

// ✅ Move filterProducts outside DOMContentLoaded
async function filterProducts(searchValue) {
    await fetchProducts(); // Ensure data is available before filtering
    const dropdownList = document.getElementById("dropdown-list");
    dropdownList.innerHTML = ""; // Clear previous options

    if (!searchValue.trim()) {
        dropdownList.style.display = "none";
        return;
    }

    const filteredProducts = allProducts.filter(product =>
        product.NAME.toLowerCase().includes(searchValue.toLowerCase())
    );

    if (filteredProducts.length === 0) {
        dropdownList.style.display = "none";
        return;
    }

    filteredProducts.forEach(product => {
    const div = document.createElement("div");
    div.textContent = product.NAME;
    div.dataset.value = product.NAME.trim(); // ✅ Ensure NAME is stored

    div.addEventListener("click", function () {
        const productInput = document.getElementById("product-search");
        const productSelect = document.getElementById("product-select");

        productInput.value = product.NAME; // ✅ Show selected product in input
        productSelect.value = product.NAME; // ✅ Ensure product-select gets the value
        dropdownList.style.display = "none"; 

        console.log("✅ Selected Product:", productSelect.value); // ✅ Debugging
        fetchHistoriqueRotation(); // ✅ Fetch rotation data
    });

    dropdownList.appendChild(div);
});



    dropdownList.style.display = "block";
}

document.addEventListener("DOMContentLoaded", function () {
    const productSearch = document.getElementById("product-search");
    const dropdownList = document.getElementById("dropdown-list");
    const seeAllBtn = document.getElementById("see-all-btn");
    const modal = document.getElementById("product-modal");
    const closeModal = document.getElementById("close-modal");
    const productTableBody = document.getElementById("product-table-body");

    async function populateTable() {
        await fetchProducts();
        productTableBody.innerHTML = "";

        allProducts.forEach((product, index) => {
            const row = document.createElement("tr");

            const numberCell = document.createElement("td");
            numberCell.textContent = index + 1;

            const nameCell = document.createElement("td");
            nameCell.textContent = product.NAME;

            row.appendChild(numberCell);
            row.appendChild(nameCell);
            productTableBody.appendChild(row);
        });

        modal.style.display = "flex";
    }

    productSearch.addEventListener("input", function () {
        filterProducts(this.value);
    });

    document.addEventListener("click", function (event) {
        if (!document.querySelector(".custom-dropdown").contains(event.target)) {
            dropdownList.style.display = "none";
        }
    });

    productSearch.addEventListener("focus", function () {
        if (productSearch.value.trim()) {
            dropdownList.style.display = "block";
        }
    });

    seeAllBtn.addEventListener("click", populateTable);
    closeModal.addEventListener("click", () => (modal.style.display = "none"));
});

// ✅ Ensure dropdown selection triggers data fetch
document.getElementById("product-select").addEventListener("change", fetchHistoriqueRotation);

async function fetchHistoriqueRotation() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const productName = document.getElementById("product-select").value.trim();

    console.log("Start Date:", startDate);
    console.log("End Date:", endDate);
    console.log("Product Name:", `"${productName}"`); // ✅ Check if it's empty

    if (!startDate || !endDate || !productName) {
        console.error("❌ Missing parameters, not sending request.");
        return; 
    }

    try {
        const url = `http://127.0.0.1:5003/fetchHistoriqueRotation?start_date=${startDate}&end_date=${endDate}&product=${encodeURIComponent(productName)}`;
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
    tableBody.innerHTML = `
        <tr class="dark:bg-gray-700">
            <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_DISPO ?? 0}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${row.DERNIER_ACHAT ?? 0}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${new Date(row.DATE).toLocaleDateString()}</td>
        </tr>
    `;
}

// ✅ Attach event listeners
["start-date", "end-date", "product-select"].forEach(id => {
    document.getElementById(id).addEventListener("change", fetchHistoriqueRotation);
});

</script>


     

    
   
     
      
      

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     





</body>

</html>
