<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



// // Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], [ 'Sup Vente'])) {
//     header("Location: Acess_Denied");    exit();
// }
$page_identifier = 'DETTE_F';


require_once 'check_permission.php';



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECHU&DETTE</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="theme.js"></script>
    <script src="api_config.js"></script>

    <link rel="stylesheet" href="etat_f.css">


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->











        <!-- Filters -->
   
        
        <div id="content" class="content flex-grow p-4">

        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Etat Fournisseur 
            </h1>
        </div>
        <br>



        <!-- <button id="downloadExcel_journal"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Journal de vente Download</span>
        </button> -->

<!-- From Uiverse.io by Rodrypaladin --> 
<!-- <button id="downloadExcel_journal"
 class="button">
  <span class="button__span">Journal de vente Download</span>
  
</button> -->
<div class="container">
  <button id="dette-excel" class="button">
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

<div class="search-container">
  <label for="etat_fournisseur">Search :</label>
  <input type="text" id="etat_fournisseur" placeholder="Search fournisseur..." disabled>
  <div id="suggestions"></div>
</div>



        <br>
        <div id="supplier-table-container">
    <h2>TOTAL FOURNISSEUR</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>TOTAL ECHU</th>
                    <th>TOTAL DETTE</th>
                    <th>TOTAL STOCK</th>
                </tr>
            </thead>
            <tbody id="dette-table">
                <!-- Dynamic Data Will Be Centered -->
            </tbody>
        </table>
    </div>
</div>





    <br>
        
        <!-- Table -->
        <div id="etat-fournisseur-container" class="p-4 bg-white rounded-xl shadow-lg">
  <h2 class="text-2xl font-semibold mb-4">ETAT FOURNISSEUR</h2>

  <!-- Refresh Button -->
  <div class="flex justify-end mb-3">
    <button id="refresh-btn" 
            class="p-2 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 transition duration-200 flex items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" 
              d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5
                 m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
      </svg>
      <span class="ml-2">Refresh</span>
    </button>
  </div>

  <!-- Table -->
  <div class="etat-table-wrapper overflow-x-auto rounded-lg border border-gray-300">
    <table id="etat-fournisseur-table" class="table-auto w-full border-collapse">
      <thead class="bg-gray-100">
        <tr>
          <th data-column="FOURNISSEUR" onclick="sortetatTable('FOURNISSEUR')" class="p-3 cursor-pointer">Fournisseur</th>
          <th data-column="TOTAL ECHU" onclick="sortetatTable('TOTAL ECHU')" class="p-3 cursor-pointer">Total Échu</th>
          <th data-column="TOTAL DETTE" onclick="sortetatTable('TOTAL DETTE')" class="p-3 cursor-pointer">Total Dette</th>
          <th data-column="TOTAL STOCK" onclick="sortetatTable('TOTAL STOCK')" class="p-3 cursor-pointer">Total Stock</th>
        </tr>
      </thead>
      <tbody id="etat-fournisseur-body">
        <!-- Data Loads Here -->
      </tbody>
    </table>
  </div>
</div>












     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
         
    
     <br>


        <br><br><br> <br>
        <script>
            window.onload = () => {
    fetchFournisseurDette();  // If you have this function
    loadFournisseurData();    // Your main data load function
};
async function fetchFournisseurDette() {
  const tableBody = document.getElementById("dette-table");
tableBody.innerHTML = `
<tr>
  <td>
    <div class="window">
      <div class="logobnm">
        <p class="top">BNM</p>
        <p class="mid">BnmWeb</p>
        <p class="bottom">Loading...</p>
        <div class="containerlo">
          <div class="box"></div>
          <div class="box"></div>
          <div class="box"></div>
        </div>
      </div>
    </div>
  </td>
</tr>`;

    try {
    const response = await fetch(API_CONFIG.getApiUrl('/etat_fournisseur'));
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("✅ Data received:", data);

        // Clear loading
        tableBody.innerHTML = "";

        // Check if data contains values
        if (!data || Object.keys(data).length === 0) {
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">Aucune donnée disponible</td></tr>`;
            return;
        }

        // Build the row dynamically
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        // Check if dark mode is active and add appropriate classes
        if (document.body.classList.contains('dark-mode')) {
            tr.classList.add('dark-mode-row');
        }
        tr.innerHTML = `
            <td class="border px-3 py-2 dark:border-gray-600">${data["TOTAL ECHU"].toLocaleString('fr-FR')}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${data["TOTAL DETTE"].toLocaleString('fr-FR')}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${data["TOTAL STOCK"].toLocaleString('fr-FR')}</td>
        `;
        tableBody.appendChild(tr);

    } catch (error) {
        console.error("❌ Error fetching data:", error);
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">Erreur de chargement</td></tr>`;
    }
}




let currentSortColumn = '';
let currentSortOrder = 'desc';
let fournisseurData = [];
async function loadFournisseurData(sortColumn = '', sortOrder = 'desc') {
  try {
    console.log('Fetching data from server...'); // ✅ Check in browser console

    const tableBody = document.getElementById("etat-fournisseur-body");

    // Show loading animation while waiting for data
    tableBody.innerHTML = `
    <tr>
      <td colspan="4" style="text-align: center;">
        <div class="window">
          <div class="logobnm">
            <p class="top">BNM</p>
            <p class="mid">BnmWeb</p>
            <p class="bottom">Loading...</p>
            <div class="containerlo">
              <div class="box"></div>
              <div class="box"></div>
              <div class="box"></div>
            </div>
          </div>
        </div>
      </td>
    </tr>`;

    // Fetch data from server
    const url = API_CONFIG.getApiUrl('/fetchFournisseurDette');
    const response = await fetch(url);
    const data = await response.json();

    if (data.error) {
      console.error(data.error);
      tableBody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: red;">Error loading data</td></tr>`;
      return;
    }

    fournisseurData = data; // ✅ Store fresh data
    console.log('Data fetched successfully:', fournisseurData); // ✅ Data preview

    // Enable search input after data is loaded
    document.getElementById('etat_fournisseur').disabled = false;

    // Render table with fetched data
    renderFournisseurTable(sortColumn, sortOrder);
  } catch (error) {
    console.error('Error fetching data:', error);
    document.getElementById("etat-fournisseur-body").innerHTML =
      `<tr><td colspan="4" style="text-align: center; color: red;">Error fetching data</td></tr>`;
  }
}

function renderFournisseurTable(sortColumn = '', sortOrder = 'desc', filterText = '') {
  let dataToRender = [...fournisseurData];

  // Filter Logic
  if (filterText) {
    dataToRender = dataToRender.filter(f =>
      f["FOURNISSEUR"].toLowerCase().includes(filterText.toLowerCase())
    );
  }

  // Sorting Logic
  if (sortColumn) {
    dataToRender.sort((a, b) => {
      const valA = isNaN(parseFloat(a[sortColumn])) ? (a[sortColumn] || '').toLowerCase() : parseFloat(a[sortColumn]);
      const valB = isNaN(parseFloat(b[sortColumn])) ? (b[sortColumn] || '').toLowerCase() : parseFloat(b[sortColumn]);

      if (valA < valB) return sortOrder === 'asc' ? -1 : 1;
      if (valA > valB) return sortOrder === 'asc' ? 1 : -1;
      return 0;
    });
  }

  // Render table body
  const tableBody = document.getElementById('etat-fournisseur-body');
  tableBody.innerHTML = ''; // Clear previous content

  if (dataToRender.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" style="text-align: center;">No data found</td></tr>`;
    return;
  }

  dataToRender.forEach(row => {
    const tr = document.createElement('tr');
    // Add dark mode classes if dark mode is active
    if (document.body.classList.contains('dark-mode')) {
      tr.classList.add('dark-mode-row');
    }
    tr.innerHTML = `
      <td class="clickable">${row["FOURNISSEUR"].toLocaleString('fr-FR')}</td>
      <td>${row["TOTAL ECHU"].toLocaleString('fr-FR')}</td>
      <td>${row["TOTAL DETTE"].toLocaleString('fr-FR')}</td>
      <td>${row["TOTAL STOCK"].toLocaleString('fr-FR')}</td>
    `;
    tableBody.appendChild(tr);
  });

  // Make Fournisseur column clickable for auto-filling the search
  document.querySelectorAll('.clickable').forEach(td => {
    td.addEventListener('click', () => {
      document.getElementById('etat_fournisseur').value = td.textContent;
      triggerSearch(); // Auto trigger the search
    });
  });
}

function sortetatTable(column) {
  // Toggle sort order if the same column is clicked again
  if (currentSortColumn === column) {
    currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
  } else {
    currentSortColumn = column;
    currentSortOrder = 'desc';
  }

  // Update arrows on headers
  document.querySelectorAll('th').forEach(th => {
    const content = th.innerText.replace(/ ↑| ↓/g, '');
    th.innerText = content;
  });

  const currentHeader = document.querySelector(`th[data-column="${column}"]`);
  if (currentHeader) {
    const arrow = currentSortOrder === 'asc' ? ' ↑' : ' ↓';
    currentHeader.innerText += arrow;
  }

  // Re-render table with current search filter
  const searchValue = document.getElementById('etat_fournisseur').value.trim();
  renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
}


// Autocomplete suggestion dropdown
function showSuggestions(filteredList) {
  const suggestionBox = document.getElementById('suggestions');
  suggestionBox.innerHTML = '';
  filteredList.forEach(f => {
    const div = document.createElement('div');
    div.classList.add('suggestion-item');
    div.textContent = f["FOURNISSEUR"];
    div.addEventListener('click', () => {
      document.getElementById('etat_fournisseur').value = f["FOURNISSEUR"];
      suggestionBox.innerHTML = '';
      triggerSearch(); // Auto trigger search when suggestion clicked
    });
    suggestionBox.appendChild(div);
  });
}

function triggerSearch() {
  const searchValue = document.getElementById('etat_fournisseur').value.trim();
  renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
}

// Event listener for live search/autocomplete
document.getElementById('etat_fournisseur').addEventListener('input', function () {
  const searchValue = this.value.trim().toLowerCase();
  if (searchValue === '') {
    document.getElementById('suggestions').innerHTML = '';
    renderFournisseurTable(currentSortColumn, currentSortOrder);
    return;
  }

  // Suggestion logic
  const filtered = fournisseurData.filter(f =>
    f["FOURNISSEUR"].toLowerCase().includes(searchValue)
  );

  showSuggestions(filtered);
  renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
});

// Initial Load
loadFournisseurData();

// Initial load
document.getElementById('refresh-btn').addEventListener('click', () => {
    const refreshBtn = document.getElementById('refresh-btn');
    refreshBtn.innerHTML = '⏳ Loading...';

    // Disable search during refresh
    document.getElementById('etat_fournisseur').disabled = true;

    const searchValue = document.getElementById('etat_fournisseur').value.trim().toLowerCase();
    loadFournisseurData().then(() => {
        // Re-apply current filter and sorting after refresh
        renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);

        refreshBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
            </svg>
        `;
    });
});




// Initial load on page load



  // Auto load on page load


// Initial load
document.getElementById("dette-excel").addEventListener("click", function () {
    exportToExcel();
});

function exportToExcel() {
    // Ensure the SheetJS library is loaded
    if (typeof XLSX === "undefined") {
        console.error("SheetJS library (XLSX) is required.");
        return;
    }

    // Select table data
    const table = document.getElementById("etat-fournisseur-body");
    if (!table || table.rows.length === 0) {
        alert("Aucune donnée à exporter.");
        return;
    }

    let data = [];
    let headers = ["FOURNISSEUR", "TOTAL ECHU", "TOTAL DETTE", "TOTAL STOCK"];

    // Extract table rows data
    for (let row of table.rows) {
        let rowData = [];
        for (let cell of row.cells) {
            rowData.push(cell.innerText);
        }
        data.push(rowData);
    }

    // Create a new Excel worksheet
    let ws = XLSX.utils.aoa_to_sheet([headers, ...data]);

    // Apply styling to headers (Background and Font)
    let range = XLSX.utils.decode_range(ws["!ref"]);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        let headerCell = XLSX.utils.encode_cell({ r: 0, c: C });
        ws[headerCell].s = {
            fill: { fgColor: { rgb: "4F81BD" } }, // Blue header
            font: { bold: true, color: { rgb: "FFFFFF" } }, // White text
        };
    }

    // Apply alternate row coloring
    for (let R = 1; R <= data.length; R++) {
        for (let C = 0; C < headers.length; C++) {
            let cellRef = XLSX.utils.encode_cell({ r: R, c: C });
            ws[cellRef].s = {
                fill: { fgColor: { rgb: R % 2 === 0 ? "EAEAEA" : "FFFFFF" } }, // Alternating row colors
            };
        }
    }

    // Create the Excel workbook
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Fournisseur Dette");

    // Generate filename
    let today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD
    let filename = `Fournisseur_Dette_${today}.xlsx`;

    // Trigger download
    XLSX.writeFile(wb, filename);
}






// // Format number with thousand separators & two decimals
// function formatNumber(value) {
//     if (value === null || value === undefined || isNaN(value)) return "0.00";
//     return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
// }





            // Dark Mode Toggle Functionality - Compatible with sidebar.php
            document.addEventListener('DOMContentLoaded', function() {
                // Function to apply theme to existing table rows
                function updateTableRowTheme(isDark) {
                    const allRows = document.querySelectorAll('#dette-table tr, #etat-fournisseur-body tr');
                    allRows.forEach(row => {
                        if (isDark) {
                            row.classList.add('dark-mode-row');
                        } else {
                            row.classList.remove('dark-mode-row');
                        }
                    });
                }

                // Function to apply theme
                function applyTheme(isDark) {
                    document.body.classList.toggle('dark-mode', isDark);
                    document.documentElement.classList.toggle('dark', isDark);
                    updateTableRowTheme(isDark);
                }

                // Listen for theme changes from localStorage
                window.addEventListener('storage', (e) => {
                    if (e.key === 'theme') {
                        const isDark = e.newValue === 'dark';
                        applyTheme(isDark);
                    }
                });

                // Listen for custom theme change events
                window.addEventListener('themeChanged', (e) => {
                    const isDark = e.detail.isDark;
                    applyTheme(isDark);
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });

                // Apply initial theme
                const isDark = localStorage.getItem('theme') === 'dark';
                applyTheme(isDark);

                // Check for theme changes every second (as fallback)
                setInterval(() => {
                    const currentTheme = localStorage.getItem('theme');
                    const shouldBeDark = currentTheme === 'dark';
                    const isDark = document.body.classList.contains('dark-mode');
                    
                    if (shouldBeDark !== isDark) {
                        applyTheme(shouldBeDark);
                    }
                }, 1000);
            });

        </script>

</body>

</html>