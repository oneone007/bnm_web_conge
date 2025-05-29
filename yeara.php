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
    <title>
    Annual Recap vente
</title>
<link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.3.4/purify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<!-- Include these scripts in your head section -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>

    <!-- <link rel="stylesheet" href="recap_achat.css"> -->
    <link rel="stylesheet" href="year.css">
    <script src="theme.js"></script>

</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
<div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="text-white text-lg font-semibold">Chargement des données...</div>
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





    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">

        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Annual Recap Achat
            </h1>
        </div>
        <!-- Filters -->

        <div class="dashboard-container ycheffck">
<div class="search-controls bg-white dark:bg-gray-900 p-4 rounded-lg shadow-md mb-6">
  <!-- Year Selection -->
  <div class="mb-4">
  <label class="block text-sm font-medium mb-2 years">Select Years</label>
  <div class="flex flex-wrap gap-4">
      <label class="inline-flex items-center">
        <input type="checkbox" class="year-checkbox" value="2022">
        <span class="ml-2 year-label years">2022</span>
      </label>
      <label class="inline-flex items-center">
        <input type="checkbox" class="year-checkbox" value="2023">
        <span class="ml-2 year-label years">2023</span>
      </label>
      <label class="inline-flex items-center">
        <input type="checkbox" class="year-checkbox" value="2024">
        <span class="ml-2 year-label years">2024</span>
      </label>
      <label class="inline-flex items-center">
        <input type="checkbox" class="year-checkbox" value="2025">
        <span class="ml-2 year-label years">2025</span>
      </label>
    </div>
  </div>
</div>

  
   <!-- Rest of your existing search controls -->
   <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 recap-grid">
  <div>
    <label for="recap_fournisseur" class="block text-sm font-medium recap-label">Fournisseur</label>
    <div class="relative">
      <input type="text" id="recap_fournisseur" placeholder="Search..." 
             class="w-full p-2 border rounded recap-input">
      <div id="fournisseur_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
    </div>
  </div>

  <div>
    <label for="recap_product" class="block text-sm font-medium recap-label">Product</label>
    <div class="relative">
      <input type="text" id="recap_product" placeholder="Search..." 
             class="w-full p-2 border rounded recap-input">
      <div id="product_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
    </div>
  </div>


</div>
  <button id="applyFilters" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
    Apply Filters
  </button>
  <button id="resetFilters" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded transition hidden">
  Reset
</button>

</div>
  <!-- Search Controls -->

  <div >
  <button class="Btn center-btn" id="exportPdf">
    <div class="svgWrapper">
      <img src="assets/pdf.png" alt="Excel Icon" class="excelIcon" />
      <div class="text">&nbsp;Download</div>
      </div>
  </button>
</div>
  <!-- Data Tables -->
<div class="table-wrapper mt-6">
  <!-- Year 2022 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2022">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2022</h2>

    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2022Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2022Table('TOTAL')" class="border px-4 py-2">Total</th>
          </tr>
        </thead>
        <tbody id="table-body-2022" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Year 2023 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2023">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2023</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2023Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2023Table('TOTAL')" class="border px-4 py-2">Total</th>
          </tr>
        </thead>
        <tbody id="table-body-2023" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Year 2024 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2024">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2024</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2024Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2024Table('TOTAL')" class="border px-4 py-2">Total</th>
          </tr>
        </thead>
        <tbody id="table-body-2024" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Year 2025 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2025">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2025</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2025Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2025Table('TOTAL')" class="border px-4 py-2">Total</th>
          </tr>
        </thead>
        <tbody id="table-body-2025" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
 </div>

  <!-- Charts Section -->


  <div class="chart-container mt-8 opacity-0 transition-opacity duration-500" style="display: none;">
  <div class="chart-controls bg-white dark:bg-gray-900 p-4 rounded-lg shadow-md mb-6">
    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
      <div>
        <label for="chart-type" class="block text-sm font-medium dark:text-white">Chart Type</label>
        <select id="chart-type" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600">
        <option value="line">Line Chart</option>
          <option value="bar">Bar Chart</option>
          <option value="pie">Pie Chart</option>
          <option value="doughnut">Doughnut Chart</option>
          <option value="radar">Radar Chart</option>
        </select>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6">
    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg shadow-md h-full">
      <h3 class="text-lg font-semibold mb-4 dark:text-white">Total Revenue</h3>
      <div class="chart-wrapper relative h-64 w-full">
        <canvas id="totalChart"></canvas>
      </div>
    </div>

    <div id="qtyChartContainer" class="bg-white dark:bg-gray-900 p-4 rounded-lg shadow-md h-full hidden">
      <h3 class="text-lg font-semibold mb-4 dark:text-white">Quantity</h3>
      <div class="chart-wrapper relative h-64 w-full">
        <canvas id="qtyChart"></canvas>
      </div>
    </div>
  </div>
</div>



</div>


<br>


<!-- Add this after your tables section -->
<!-- Add this after your tables section -->


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>



// Update the appState to include selected years
const appState = {
  selected: {
    fournisseur: null,
    product: null,
    years: []
  },
  isLoading: false,
  cache: new Map(),
  debounceTimer: null
};

// DOM Elements
const elements = {
  applyBtn: document.getElementById('applyFilters'),
  resetBtn: document.getElementById('resetFilters'),
  inputs: {
    fournisseur: document.getElementById('recap_fournisseur'),
    product: document.getElementById('recap_product')
  },
  suggestionBoxes: {
    fournisseur: document.getElementById('fournisseur_suggestions'),
    product: document.getElementById('product_suggestions')
  },
  yearCheckboxes: document.querySelectorAll('.year-checkbox')
};

// Constants
const API_ENDPOINTS = {
  fetchData: 'http://192.168.1.94:5000/fetchFournisseurRecapAchatByYear',
  listFournisseur: 'http://192.168.1.94:5000/listfournisseur',
  listProduct: 'http://192.168.1.94:5000/listproduct'
};
const monthNames = {
  '01': 'Janvier', '02': 'Février', '03': 'Mars', '04': 'Avril',
  '05': 'Mai', '06': 'Juin', '07': 'Juillet', '08': 'Août',
  '09': 'Septembre', '10': 'Octobre', '11': 'Novembre', '12': 'Décembre'
};

// Initialize the application
function init() {
  setupEventListeners();
  loadInitialData();
}

// Set up all event listeners
function setupEventListeners() {
  // Search input handlers with debouncing
  Object.entries(elements.inputs).forEach(([type, input]) => {
    input.addEventListener('input', debounce(() => handleSearchInput(type), 300));
    
    // Prevent hiding suggestions when clicking inside the input
    input.addEventListener('mousedown', (e) => {
      e.stopPropagation();
    });
  });

  // Year checkbox handlers
  elements.yearCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', handleYearSelection);
  });

  // Apply/reset buttons
  elements.applyBtn.addEventListener('click', fetchAndDisplayData);
  elements.resetBtn.addEventListener('click', resetFilters);

  // Enhanced click outside handler
  document.addEventListener('click', (e) => {
    let isSuggestion = false;
    let isInput = false;
    
    Object.values(elements.suggestionBoxes).forEach(box => {
      if (box.contains(e.target)) {
        isSuggestion = true;
      }
    });
    
    Object.values(elements.inputs).forEach(input => {
      if (input === e.target || input.contains(e.target)) {
        isInput = true;
      }
    });
    
    if (!isSuggestion && !isInput) {
      hideAllSuggestions();
    }
  });
}

function handleYearSelection() {
  const selectedYears = [];
  elements.yearCheckboxes.forEach(checkbox => {
    if (checkbox.checked) {
      selectedYears.push(parseInt(checkbox.value));
    }
  });
  
  if (selectedYears.length === 0) {
    elements.yearCheckboxes.forEach(checkbox => {
      checkbox.checked = true;
    });
    appState.selected.years = [2022, 2023, 2024, 2025];
  } else {
    appState.selected.years = selectedYears;
  }
}

function debounce(func, delay) {
  return function() {
    clearTimeout(appState.debounceTimer);
    appState.debounceTimer = setTimeout(() => func.apply(this, arguments), delay);
  };
}

function clearPreviousSearches(currentType) {
  Object.keys(elements.inputs).forEach(type => {
    if (type !== currentType) {
      elements.inputs[type].value = '';
      appState.selected[type] = null;
      hideSuggestions(type);
    }
  });
}

async function handleSearchInput(type) {
  const input = elements.inputs[type];
  const value = input.value.trim().toLowerCase();
  const suggestionBox = elements.suggestionBoxes[type];

  if (value && !appState.selected[type]) {
    clearPreviousSearches(type);
  }

  if (!value) {
    hideSuggestions(type);
    appState.selected[type] = null;
    return;
  }

  try {
    const endpoint = API_ENDPOINTS[`list${type.charAt(0).toUpperCase() + type.slice(1)}`];
    const response = await fetch(endpoint);
    const data = await response.json();
    
    const filtered = data.filter(item => 
      item.toLowerCase().includes(value)
    );

    showSuggestions(type, filtered);
  } catch (error) {
    console.error(`Error fetching ${type} suggestions:`, error);
  }
}

function showSuggestions(type, items) {
  const box = elements.suggestionBoxes[type];
  box.innerHTML = '';

  if (items.length === 0) {
    box.innerHTML = '<div class="autocomplete-suggestion">No results found</div>';
  } else {
    items.forEach(item => {
      const div = document.createElement('div');
      div.className = 'autocomplete-suggestion';
      div.textContent = item;
      
      div.addEventListener('mousedown', (e) => {
        e.preventDefault();
        selectSuggestion(type, item);
      });
      
      box.appendChild(div);
    });
  }

  box.classList.remove('hidden');
}

function hideSuggestions(type) {
  elements.suggestionBoxes[type].classList.add('hidden');
}

function hideAllSuggestions() {
  Object.values(elements.suggestionBoxes).forEach(box => {
    box.classList.add('hidden');
  });
}

function selectSuggestion(type, value) {
  elements.inputs[type].value = value;
  appState.selected[type] = value;
  hideSuggestions(type);
  elements.inputs[type].focus();
}

function resetFilters() {
  Object.values(elements.inputs).forEach(input => {
    input.value = '';
  });
  
  elements.yearCheckboxes.forEach(checkbox => {
    checkbox.checked = true;
  });

  appState.selected = {
    fournisseur: null,
    product: null,
    years: [2022, 2023, 2024, 2025]
  };

  hideAllSuggestions();
  appState.cache.clear();
  fetchAndDisplayData();
}

async function fetchAndDisplayData() {
  if (appState.isLoading) return;

  appState.isLoading = true;
  document.querySelectorAll('.table-container').forEach(container => {
    container.style.display = 'none';
  });
  showLoadingOverlay();

  try {
    const cacheKey = JSON.stringify(appState.selected);

    if (appState.cache.has(cacheKey)) {
      updateUI(appState.cache.get(cacheKey));
      return;
    }

    const params = new URLSearchParams();
    if (appState.selected.fournisseur) params.append('fournisseur', appState.selected.fournisseur);
    if (appState.selected.product) params.append('product', appState.selected.product);
    appState.selected.years.forEach(year => params.append('years', year));

    const response = await fetch(`${API_ENDPOINTS.fetchData}?${params}`);
    const data = await response.json();

    appState.cache.set(cacheKey, data);
    updateUI(data);
  } catch (error) {
    console.error("Error fetching data:", error);
    showAllErrors();
  } finally {
    appState.isLoading = false;
    hideLoadingOverlay();
  }
}

function updateUI(data) {
  document.querySelectorAll('.table-container').forEach(container => {
    container.style.display = 'none';
  });

  Object.keys(data).forEach(year => {
    const tableBody = document.getElementById(`table-body-${year}`);
    const tableContainer = document.querySelector(`.table-container[data-year="${year}"]`);
    
    if (tableBody && tableContainer) {
      tableContainer.style.display = 'block';
      updateYearTable(tableBody, data[year]);
    }
  });

  createChartsFromTables();
}

function updateYearTable(tableBody, yearData) {
    tableBody.innerHTML = '';
    const showQty = appState.selected.product !== null;

    if (!yearData || yearData.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="${showQty ? 3 : 2}" class="text-center py-4 text-gray-500">
                    No data available
                </td>
            </tr>
        `;
        return;
    }

    // Create a map of month data for easy lookup
    const monthDataMap = {};
    yearData.forEach(item => {
        if (item.MONTH) {
            monthDataMap[item.MONTH] = {
                TOTAL: item.CHIFFRE || 0,
                QTY: item.QTY || 0
            };
        }
    });

    // Add TOTAL ANNUEL row at the top (always first)
    const totalRow = yearData.find(item => item.MONTH === null);
    if (totalRow) {
        const row = document.createElement('tr');
        row.className = 'border-b dark:border-gray-700';

        row.innerHTML = `
            <td colspan="${showQty ? 3 : 2}" class="text-center font-bold bg-gray-100 dark:bg-gray-700 py-2">
                TOTAL ANNUEL: ${formatNumber(totalRow.CHIFFRE)}
                ${showQty ? ` | QTY: ${formatNumber(totalRow.QTY)}` : ''}
            </td>
        `;
        row.classList.add('annual-total-row');
        tableBody.appendChild(row);
    }

    // Get all months that have data and sort them chronologically
    const monthsWithData = yearData
        .filter(item => item.MONTH !== null)
        .map(item => item.MONTH)
        .sort((a, b) => parseInt(a) - parseInt(b));
    
    // Add only months that have data
    monthsWithData.forEach(monthNum => {
        const monthName = monthNames[monthNum];
        const row = document.createElement('tr');
        row.className = 'border-b dark:border-gray-700';

        const monthData = monthDataMap[monthNum] || { TOTAL: 0, QTY: 0 };

        row.innerHTML = `
            <td class="border px-4 py-2">${monthName}</td>
            <td class="border px-4 py-2">${formatNumber(monthData.TOTAL)}</td>
            ${showQty ? `<td class="border px-4 py-2">${formatNumber(monthData.QTY)}</td>` : ''}
        `;
        tableBody.appendChild(row);
    });

    // Update table headers to match column count
    const table = tableBody.closest('table');
    if (table) {
        const headerRow = table.querySelector('thead tr');
        if (headerRow) {
            headerRow.innerHTML = `
                <th class="px-4 py-2 cursor-pointer" onclick="sort${tableBody.id.split('-')[2]}Table('MONTH')">Mois</th>
                <th class="px-4 py-2 cursor-pointer" onclick="sort${tableBody.id.split('-')[2]}Table('TOTAL')">Total</th>
                ${showQty ? '<th class="px-4 py-2">Quantité</th>' : ''}
            `;
        }
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
}

function createSortFunction(year) {
    return function(column) {
        const tableBody = document.getElementById(`table-body-${year}`);
        const rows = Array.from(tableBody.querySelectorAll('tr:not(.annual-total-row)'));
        const showQty = appState.selected.product !== null;
        
        rows.sort((a, b) => {
            let columnIndex;
            if (column === 'MONTH') columnIndex = 0;
            else if (column === 'TOTAL') columnIndex = 1;
            else if (column === 'QTY' && showQty) columnIndex = 2;
            
            const aValue = a.cells[columnIndex].textContent;
            const bValue = b.cells[columnIndex].textContent;
            
            if (column === 'MONTH') {
                const monthNumbers = Object.entries(monthNames).reduce((acc, [num, name]) => {
                    acc[name] = num;
                    return acc;
                }, {});
                return parseInt(monthNumbers[aValue]) - parseInt(monthNumbers[bValue]);
            } else {
                const aNum = parseFloat(aValue.replace(/[^\d,-]/g, '').replace(',', '.'));
                const bNum = parseFloat(bValue.replace(/[^\d,-]/g, '').replace(',', '.'));
                return aNum - bNum;
            }
        });
        
        const annualTotalRow = tableBody.querySelector('.annual-total-row');
        tableBody.innerHTML = '';
        rows.forEach(row => tableBody.appendChild(row));
        if (annualTotalRow) tableBody.appendChild(annualTotalRow);
    };
}

const sort2022Table = createSortFunction('2022');
const sort2023Table = createSortFunction('2023');
const sort2024Table = createSortFunction('2024');
const sort2025Table = createSortFunction('2025');

// Initial load
function showLoadingOverlay() {
  document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoadingOverlay() {
  document.getElementById('loadingOverlay').classList.add('hidden');
}

// Initialize the app
document.addEventListener('DOMContentLoaded', init);

// Add this to your setupEventListeners function
// Add this to your setupEventListeners function
document.getElementById('exportPdf').addEventListener('click', exportTablesToPDF);

function exportTablesToPDF() {
  try {
    let hasData = false;
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l'); // Landscape orientation
    
    // Smaller font sizes
    const fontSize = 6;
    const headerFontSize = 7;
    
    // Layout configuration
    const tablesPerRow = 3; // Three tables side by side
    const tableWidth = 85; // Reduced width for each table
    const horizontalMargin = 5; // Space between tables
    const verticalMargin = 15; // Space between rows
    let xPos = 10;
    let yPos = 20;
    let currentRowTables = 0;

    // Helper function to clean numbers
    const cleanNumberFormat = (text) => {
      return text.replace(/ /g, ' ')
                 .replace(/\s+/g, ' ')
                 .trim();
    };

    appState.selected.years.forEach((year, index) => {
      const tableContainer = document.querySelector(`.table-container[data-year="${year}"]`);
      if (!tableContainer || tableContainer.style.display === 'none') return;
      
      const table = tableContainer.querySelector('table');
      if (!table) return;

      const tableClone = table.cloneNode(true);
      tableClone.querySelectorAll('[onclick]').forEach(el => el.removeAttribute('onclick'));

      // Convert table data
      const rows = Array.from(tableClone.querySelectorAll('tr')).map(tr => {
        return Array.from(tr.querySelectorAll('td, th')).map(cell => {
          return cleanNumberFormat(cell.innerText);
        });
      });

      // Add title (smaller and centered above table)
      doc.setFontSize(headerFontSize);
      doc.text(`Recap ${year}`, xPos + tableWidth/2, yPos - 5, { align: 'center' });

      // Add table with smaller font
      doc.autoTable({
        startY: yPos,
        startX: xPos,
        head: [rows[0]],
        body: rows.slice(1),
        tableWidth: tableWidth,
        margin: { left: xPos },
        styles: { 
          fontSize: fontSize,
          halign: 'right',
          cellPadding: 1, // Reduced padding
          overflow: 'linebreak',
          lineWidth: 0.1 // Thinner borders
        },
        columnStyles: {
          0: { halign: 'left', cellWidth: 'auto' } // First column left-aligned
        },
        headStyles: { 
          fillColor: [22, 160, 133],
          halign: 'center',
          fontSize: headerFontSize,
          textColor: [255, 255, 255]
        },
        bodyStyles: {
          lineWidth: 0.1
        }
      });

      hasData = true;
      
      // Update position for next table
      xPos += tableWidth + horizontalMargin;
      currentRowTables++;
      
      // Move to next row if we've placed enough tables in this row
      if (currentRowTables >= tablesPerRow) {
        xPos = 10;
        yPos = doc.lastAutoTable.finalY + verticalMargin;
        currentRowTables = 0;
        
        // Add new page if we're running out of vertical space
        if (yPos > doc.internal.pageSize.height - 20) {
          doc.addPage('l');
          yPos = 20;
        }
      }
    });

    if (!hasData) {
      alert('No visible data to export');
      return;
    }

    // Generate filename
    const today = new Date().toISOString().split('T')[0].replace(/-/g, '');
    let filename = 'Annual_Recap_achat';
    if (appState.selected.fournisseur) filename += `_${appState.selected.fournisseur}`;
    if (appState.selected.product) filename += `_${appState.selected.product}`;
    if (appState.selected.zone) filename += `_${appState.selected.zone}`;
    if (appState.selected.client) filename += `_${appState.selected.client}`;
    filename += `_${today}.pdf`;

    doc.save(filename);

  } catch (error) {
    console.error('PDF export error:', error);
    alert('Error during PDF export');
  }
}

// CHART PART
let totalChart = null;
let qtyChart = null;

function createChartsFromTables() {
  const chartContainer = document.querySelector('.chart-container');
  const chartType = document.getElementById('chart-type').value;
  const showQty = appState.selected.product !== null;

  // Show chart container with fade-in effect
  chartContainer.style.display = 'block';
  setTimeout(() => {
    chartContainer.classList.remove('opacity-0');
    chartContainer.classList.add('opacity-100');
  }, 50);

  const allData = [];
  for (let year = 2022; year <= 2025; year++) {
    const tableBody = document.getElementById(`table-body-${year}`);
    if (tableBody) {
      const rows = tableBody.querySelectorAll('tr:not(.annual-total-row)');
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        // Handle both cases (2 or 3 columns - without marge)
        if (cells.length >= 2) {
          const monthIndex = 0;
          const totalIndex = 1;
          const qtyIndex = 2;
          
          const dataItem = {
            year: year.toString(),
            month: cells[monthIndex].textContent.trim(),
            total: parseFloat(cells[totalIndex].textContent.replace(/[^\d,.-]/g, '').replace(',', '.'))
          };
          
          if (showQty && cells.length > 2) {
            dataItem.qty = parseFloat(cells[qtyIndex].textContent.replace(/[^\d,.-]/g, '').replace(',', '.'));
          }
          
          allData.push(dataItem);
        }
      });
    }
  }

  updateTotalChart(allData, chartType);
  
  // Only show QTY chart if product is selected
  if (showQty) {
    updateQtyChart(allData, chartType);
    document.getElementById('qtyChartContainer').classList.remove('hidden');
  } else {
    if (qtyChart) qtyChart.destroy();
    document.getElementById('qtyChartContainer').classList.add('hidden');
  }
}

function updateTotalChart(data, chartType) {
  const ctx = document.getElementById('totalChart');
  if (totalChart) totalChart.destroy();

  const labels = data.map(item => `${item.month} ${item.year}`);
  const totals = data.map(item => item.total);
  const backgroundColors = data.map(item => getChartColor(item.year, 0.7));
  const borderColors = data.map(item => getChartColor(item.year, 1));

  const dataset = {
    label: 'Total Revenue',
    data: totals,
    backgroundColor: backgroundColors,
    borderColor: borderColors,
    borderWidth: 1
  };

  totalChart = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: chartType === 'pie' || chartType === 'doughnut' ? [dataset] : [dataset]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) label += ': ';
              return label + formatNumber(context.raw);
            }
          }
        }
      },
      scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return formatNumber(value);
            }
          }
        }
      }
    }
  });
}

function updateQtyChart(data, chartType) {
  const ctx = document.getElementById('qtyChart');
  if (qtyChart) qtyChart.destroy();

  const labels = data.map(item => `${item.month} ${item.year}`);
  const quantities = data.map(item => item.qty);
  const backgroundColors = data.map(item => getChartColor(item.year, 0.7));
  const borderColors = data.map(item => getChartColor(item.year, 1));

  const dataset = {
    label: 'Quantity',
    data: quantities,
    backgroundColor: backgroundColors,
    borderColor: borderColors,
    borderWidth: 1
  };

  qtyChart = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: [dataset]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `Quantity: ${formatNumber(context.raw)}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return formatNumber(value);
            }
          }
        }
      }
    }
  });
}

function getChartColor(year, opacity) {
  const colors = {
    '2022': `rgba(54, 162, 235, ${opacity})`,
    '2023': `rgba(255, 99, 132, ${opacity})`,
    '2024': `rgba(75, 192, 192, ${opacity})`,
    '2025': `rgba(153, 102, 255, ${opacity})`
  };
  return colors[year] || `rgba(201, 203, 207, ${opacity})`;
}

// Event listener for chart type change
document.getElementById('chart-type').addEventListener('change', createChartsFromTables);

function formatNumber(value) {
  if (typeof value === 'string') {
    value = parseFloat(value.replace(/[^\d,.-]/g, '').replace(',', '.'));
  }
  return value.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Dark Mode Toggle
const themeToggle = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

const savedDarkMode = localStorage.getItem('darkMode');
if (savedDarkMode === 'true') {
    htmlElement.classList.add('dark');
    themeToggle.checked = true;
}

themeToggle.addEventListener('change', () => {
    htmlElement.classList.toggle('dark');
    const isDarkMode = htmlElement.classList.contains('dark');
    localStorage.setItem('darkMode', isDarkMode);
    
    // Refresh charts to update their theme
    createChartsFromTables();
});

</script>

</body>

</html>