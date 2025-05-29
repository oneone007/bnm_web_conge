<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
    header("Location: Acess_Denied");    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
R Achat Facturation
</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="recap_achat.css">
    <script src="theme.js"></script>

  
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





    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">

        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Reacap Achat Facturation
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

    <!-- Refresh Button with Icon -->
    <button id="refresh-btn" class="p-3 bg-white dark:bg-gray-700 text-blue-500 dark:text-blue-400 rounded-full shadow-lg hover:shadow-xl border border-blue-500 dark:border-blue-400 transition duration-200 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
        </svg>
    </button>
</div>



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
            <button id="download-recap-fournisseur-achat_facturation-excel" class="button">
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
            <div>
                <label for="recap_fournisseur" class="text-gray-900 dark:text-white">Recap Fournisseur:</label>
                <input type="text" id="recap_fournisseur" placeholder="Search..." class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600">
            </div>
   
            <div>
                <label for="recap_product" class="text-gray-900 dark:text-white">Recap Product:</label>
                <input type="text" id="recap_product" placeholder="Search..." class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600">
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

            // Ensure dates clear on refresh
            window.onload = () => {
                document.getElementById("start-date").value = "";
                document.getElementById("end-date").value = "";
                document.getElementById("recap_fournisseur").value = "";
                document.getElementById("recap_product").value = "";
            };

            document.addEventListener("DOMContentLoaded", function () {
        const startDate = document.getElementById("start-date");
        const endDate = document.getElementById("end-date");
        const refreshBtn = document.getElementById("refresh-btn");

        

        // Set default value for end date to today
        const today = new Date().toISOString().split("T")[0];
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
            triggerChangeEvent(endDate); // Make sure refresh triggers the change
        });
    });
// Format number with thousand separators & two decimals


            function hideLoader() {
                const loaderRow = document.getElementById('loading-row');
                if (loaderRow) {
                    loaderRow.remove();
                }
            }

      

            
            // Attach event listeners to date inputs
            document.getElementById("start-date").addEventListener("change", fetchTotalRecapAchat);
            document.getElementById("end-date").addEventListener("change", fetchTotalRecapAchat);


            async function fetchTotalRecapAchat() {
  const startDate = document.getElementById("start-date").value;
  const endDate = document.getElementById("end-date").value;

  if (!startDate || !endDate) return; // Don't fetch until both dates are selected

  // Show loading animation, hide result text
  document.getElementById("loading-animation").classList.remove("hidden");
  document.getElementById("recap-text").classList.add("hidden");

  try {
    const response = await fetch(`http://192.168.1.94:5000/fetchTotalRecapAchat_fact?start_date=${startDate}&end_date=${endDate}`);

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

// Attach event listeners to date inputs and trigger fetch on change
document.getElementById("start-date").addEventListener("change", fetchTotalRecapAchat);
document.getElementById("end-date").addEventListener("change", fetchTotalRecapAchat);




// Also trigger fetch when any other field changes




// Attach event listeners to relevant filter inputs


// Fetch data when filters are applied for recap achat
document.getElementById("recap_fournisseur").addEventListener("input", fetchFournisseurRecapAchat);
document.getElementById("recap_product").addEventListener("input", fetchFournisseurRecapAchat);
document.getElementById("start-date").addEventListener("input", fetchFournisseurRecapAchat);
document.getElementById("end-date").addEventListener("input", fetchFournisseurRecapAchat);

// Fetch data when filters are applied for recap achat

async function fetchFournisseurRecapAchat() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = `http://192.168.1.94:5000/fetchfourisseurRecapAchat_fact?start_date=${startDate}&end_date=${endDate}&fournisseur=${fournisseur}&product=${product}`;

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
// Update table with fetched data for recap achat
// function updateFournisseurRecapAchatTable(data) {
//     const tableBody = document.getElementById("recap-frnsr-table-achat");
//     tableBody.innerHTML = "";

//     if (!data || data.length === 0) {
//         tableBody.innerHTML = `<tr><td colspan="2" class="text-center p-4">No data available</td></tr>`;
//         return;
//     }

//     // Find and separate the total row
//     const totalRow = data.find(row => row.FOURNISSEUR === "Total");
//     const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

//     // Add the "Total" row with sticky style
//     if (totalRow) {
//         tableBody.innerHTML += `
//             <tr class="bg-gray-200 font-bold sticky top-0 z-10">
//                 <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
//                 <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
//             </tr>
//         `;
//     }

//     // Add the filtered data rows
//     filteredData.forEach(row => {
//         const tr = document.createElement("tr");
//         tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
//         tr.innerHTML = `
//             <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
//         `;

//         // Add click event to fill in the search input
//         tr.addEventListener("click", () => {
//             const searchInput = document.getElementById("recap_fournisseur");
//             if (row.FOURNISSEUR) {
//                 searchInput.value = row.FOURNISSEUR;
//                 searchInput.dispatchEvent(new Event("input")); // Trigger input event
//             }
//         });

//         tableBody.appendChild(tr);
//     });
// }

// Update table with fetched data for recap achat
function updateFournisseurRecapAchatTable(data) {
    const tableBody = document.getElementById("recap-frnsr-table-achat");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="2" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    // 🔽 Sort by CHIFFRE descending
    filteredData.sort((a, b) => b.CHIFFRE - a.CHIFFRE);

    // Add the "Total" row with sticky style
    if (totalRow) {
        tableBody.innerHTML += `
            <tr class="bg-gray-200 font-bold sticky top-0 z-10">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
            </tr>
        `;
    }

    // Add the filtered data rows
    filteredData.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
        `;

        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("recap_fournisseur");
            if (row.FOURNISSEUR) {
                searchInput.value = row.FOURNISSEUR;
                searchInput.dispatchEvent(new Event("input"));
            }
        });

        tableBody.appendChild(tr);
    });
}


// Event listeners to fetch data when inputs change


// Fetch data when filters are applied for product recap achat
document.getElementById("recap_fournisseur").addEventListener("input", fetchProductRecapAchat);
document.getElementById("recap_product").addEventListener("input", fetchProductRecapAchat);
document.getElementById("start-date").addEventListener("input", fetchProductRecapAchat);
document.getElementById("end-date").addEventListener("input", fetchProductRecapAchat);

// Fetch data when filters are applied for product recap achat
async function fetchProductRecapAchat() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = `http://192.168.1.94:5000/fetchProductRecapAchat_fact?start_date=${startDate}&end_date=${endDate}&fournisseur=${fournisseur}&product=${product}`;

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
    const tableBody = document.getElementById("recap-prdct-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.PRODUIT === "Total");
    const filteredData = data.filter(row => row.PRODUIT !== "Total");

    // 🔽 Sort by CHIFFRE descending
    filteredData.sort((a, b) => b.CHIFFRE - a.CHIFFRE);

    // Add the "Total" row with sticky style
    if (totalRow) {
        const totalRowElement = document.createElement("tr");
        totalRowElement.className = "bg-gray-200 font-bold sticky top-0 z-10";
        totalRowElement.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
        `;
        tableBody.appendChild(totalRowElement);
    }

    // Add the filtered data rows
    filteredData.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
        `;

        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("recap_product");
            if (row.PRODUIT) {
                searchInput.value = row.PRODUIT;
                searchInput.dispatchEvent(new Event("input"));
            }
        });

        tableBody.appendChild(tr);
    });
}

// Event listeners to fetch data when inputs change
document.getElementById("recap_fournisseur").addEventListener("input", fetchProductRecapAchat);
document.getElementById("recap_product").addEventListener("input", fetchProductRecapAchat);
document.getElementById("start-date").addEventListener("input", fetchProductRecapAchat);
document.getElementById("end-date").addEventListener("input", fetchProductRecapAchat);



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
    const url = new URL("http://192.168.1.94:5000/download-recap-product-achat_facturation-excel");
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
document.getElementById("download-recap-fournisseur-achat_facturation-excel").addEventListener("click", async function () {
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
    const url = new URL("http://192.168.1.94:5000/download-recap-fournisseur-achat_facturation-excel");
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

 // Function to handle the click event
    function handleInputClick(event) {
        // Clear the input value
        event.target.value = '';
        
        // Trigger the input event (or whatever event your filter listens to)
        // This will simulate the behavior of manually deleting the text
        event.target.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Get the input elements
    const fournisseurInput = document.getElementById('recap_fournisseur');
    const productInput = document.getElementById('recap_product');

    // Add click event listeners
    fournisseurInput.addEventListener('click', handleInputClick);
    productInput.addEventListener('click', handleInputClick);
    
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