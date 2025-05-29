

let currentPage = 1;
const rowsPerPage = 10;
let allData = [];
let filters = {
    product: '',
    supplier: '',
    lot: '',
    margin: '',
    lab: ''
};
let sortColumn = '';
let sortDirection = 'asc';

// Fetch data on page load
window.onload = () => {
    fetchData();
    fetchRemiseData();
    fetchBonusData();
    fetchReservedData();
};

document.getElementById("downloadExcel").addEventListener("click", function () {
    let table = document.getElementById("data-table");
    let wb = XLSX.utils.book_new(); // Create a new workbook

    // Convert the HTML table to a worksheet
    let ws = XLSX.utils.table_to_sheet(table);

    // Rename and set headers (simulating a pivot table header)
    ws["A1"].v = "Supplier (Fournisseur)";
    ws["B1"].v = "Product";
    ws["C1"].v = "Purchase Price (P_ACHAT)";
    ws["D1"].v = "Selling Price (P_VENTE)";
    ws["E1"].v = "Discount Purchase (REM_ACHAT)";
    ws["F1"].v = "Discount Sale (REM_VENTE)";
    ws["G1"].v = "Purchase Bonus (BON_ACHAT)";
    ws["H1"].v = "Sale Bonus (BON_VENTE)";
    ws["I1"].v = "Auto Discount (REMISE_AUTO)";
    ws["J1"].v = "Auto Bonus (BONUS_AUTO)";
    ws["K1"].v = "Cost Price (P_REVIENT)";
    ws["L1"].v = "Margin (MARGE)";
    ws["M1"].v = "Laboratory (LABO)";
    ws["N1"].v = "Batch (LOT)";
    ws["O1"].v = "Quantity (QTY)";

    // Add the worksheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Filtered Data");

    // Save the file
    XLSX.writeFile(wb, "Filtered_Data.xlsx");
});


document.getElementById("downloadExcel_REMISE").addEventListener("click", function () {
    window.open("http://127.0.0.1:5000/download-marge-excel", "_blank"); 
});
document.getElementById("downloadExcel_REMISE").addEventListener("click", function () {
    window.open("http://127.0.0.1:5000/download-remise-excel", "_blank"); 
});
document.getElementById("downloadExcel_BONUS").addEventListener("click", function () {
    window.open("http://127.0.0.1:5000/download-bonus-excel", "_blank"); 
});
document.getElementById("downloadExcel_RESERVE").addEventListener("click", function () {
    window.open("http://127.0.0.1:5000/download-reserved-excel", "_blank"); 
});


async function fetchData() {
    try {
        const response = await fetch('http://127.0.0.1:5000/fetch-data');
        if (!response.ok) throw new Error('Network response was not ok');

        allData = await response.json();
        updateTableAndPagination();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

function filterDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filters[type] = searchValue;
    currentPage = 1;
    updateTableAndPagination();
}

function filterData(data) {
    return data.filter(row => {
        return (!filters.product || row.PRODUCT.toLowerCase().includes(filters.product)) &&
               (!filters.supplier || row.FOURNISSEUR.toLowerCase().includes(filters.supplier)); 

    });
}

function sortTable(column) {
if (sortColumn === column) {
sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
} else {
sortColumn = column;
sortDirection = 'asc';
}

// Remove arrows from all headers
document.querySelectorAll('th').forEach(th => {
const content = th.innerText.replace(/ ↑| ↓/g, '');
th.innerText = content;
});

// Add arrow to current sorted column
const currentHeader = document.querySelector(`th[data-column="${column}"]`);
if (currentHeader) {
const arrow = sortDirection === 'asc' ? ' ↑' : ' ↓';
currentHeader.innerText += arrow;
}

updateTableAndPagination();
}

function updateTableAndPagination() {
    renderTablePage(currentPage);
    renderPagination();
}

let margeValue = '';  // Default value
let margeColor = '#ffffff'; // Default color

// Add event listener for Marge Condition button and color picker
document.getElementById('margeConditionBtn').addEventListener('click', () => {
    margeValue = parseFloat(document.getElementById('margeInput').value); // Get the entered MARGE value as a number
    margeColor = document.getElementById('margeColorPicker').value; // Get the selected color
    updateTableAndPagination(); // Re-render the table with the new MARGE value and color
});

// Update the table rendering logic
function renderTablePage(page) {
    let filteredData = filterData(allData);

    // Sort data
    if (sortColumn) {
        filteredData.sort((a, b) => {
            if (a[sortColumn] < b[sortColumn]) return sortDirection === 'asc' ? -1 : 1;
            if (a[sortColumn] > b[sortColumn]) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');

        // Check if the "MARGE" value is less than the entered value
        const marge = parseFloat(row.MARGE); // Ensure we're comparing numbers
        if (margeValue && !isNaN(marge) && marge < margeValue) {
            tr.style.backgroundColor = margeColor;  // Apply the color to the entire row
        }

        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.P_ACHAT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.P_VENTE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.REM_ACHAT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.REM_VENTE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.BON_ACHAT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.BON_VENTE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.REMISE_AUTO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.BONUS_AUTO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.P_REVIENT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LABO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LOT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}


function createPageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterData(allData).length / rowsPerPage);
    button.addEventListener("click", () => {
        currentPage = pageNumber;
        updateTableAndPagination();
    });
    return button;
}

function renderPagination() {
    const filteredData = filterData(allData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    const paginationContainer = document.getElementById("pagination");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createPageButton("First", 1);
    const prevPageButton = createPageButton("<", currentPage - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPage;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createPageButton(">", currentPage + 1);
    const lastPageButton = createPageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

// Sidebar Toggle Functionality
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-hidden');
    content.classList.toggle('content-full');
});

// Dark/Light Mode Toggle Functionality
const themeToggle = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

themeToggle.addEventListener('click', () => {
    htmlElement.classList.toggle('dark');
    // Save the theme preference in localStorage
    const isDarkMode = htmlElement.classList.contains('dark');
    localStorage.setItem('darkMode', isDarkMode);
});

// Check for saved theme preference
const savedDarkMode = localStorage.getItem('darkMode');
if (savedDarkMode === 'true') {
    htmlElement.classList.add('dark');
} else {
    htmlElement.classList.remove('dark');
}

let currentPageRemise = 1;
const rowsPerPageRemise = 10;
let remiseData = [];
let filtersRemise = {
    fournisseur: '',
    laboratoryName: '',
    product: '',
    reward: '',
    typeClient: ''
};
let sortColumnRemise = '';
let sortDirectionRemise = 'asc';

// Fetch data for the second table (remise)

async function fetchRemiseData() {
    try {
        const response = await fetch('http://127.0.0.1:5000/fetch-remise-data');
        if (!response.ok) throw new Error('Network response was not ok');

        remiseData = await response.json();
        updateRemiseTableAndPagination();
    } catch (error) {
        console.error("Error fetching remise data:", error);
    }
}

function filterRemiseDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filtersRemise[type] = searchValue;
    currentPageRemise = 1;
    updateRemiseTableAndPagination();
}

function filterRemiseData(data) {
    return data.filter(row => {
        return (!filtersRemise.fournisseur || row.FOURNISSEUR.toLowerCase().includes(filtersRemise.fournisseur)) &&
               (!filtersRemise.laboratoryName || row.LABORATORY_NAME.toLowerCase().includes(filtersRemise.laboratoryName)) &&
               (!filtersRemise.product || row.PRODUCT.toLowerCase().includes(filtersRemise.product)) &&
               (!filtersRemise.reward || row.REWARD.toLowerCase().includes(filtersRemise.reward)) &&
               (!filtersRemise.typeClient || row.TYPE_CLIENT.toLowerCase().includes(filtersRemise.typeClient));
    });
}

function sortRemiseTable(column) {
    if (sortColumnRemise === column) {
        sortDirectionRemise = sortDirectionRemise === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnRemise = column;
        sortDirectionRemise = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });

    // Add arrow to current sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirectionRemise === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }

    updateRemiseTableAndPagination();
}

function updateRemiseTableAndPagination() {
    renderRemiseTablePage(currentPageRemise);
    renderRemisePagination();
}

function renderRemiseTablePage(page) {
    let filteredData = filterRemiseData(remiseData);

    // Sort data
    if (sortColumnRemise) {
        filteredData.sort((a, b) => {
            if (a[sortColumnRemise] < b[sortColumnRemise]) return sortDirectionRemise === 'asc' ? -1 : 1;
            if (a[sortColumnRemise] > b[sortColumnRemise]) return sortDirectionRemise === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPageRemise;
    const end = start + rowsPerPageRemise;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("remise-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LABORATORY_NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.REWARD || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.TYPE_CLIENT || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function createRemisePageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterRemiseData(remiseData).length / rowsPerPageRemise);
    button.addEventListener("click", () => {
        currentPageRemise = pageNumber;
        updateRemiseTableAndPagination();
    });
    return button;
}

function renderRemisePagination() {
    const filteredData = filterRemiseData(remiseData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPageRemise);
    const paginationContainer = document.getElementById("pagination-remise");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createRemisePageButton("First", 1);
    const prevPageButton = createRemisePageButton("<", currentPageRemise - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPageRemise;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createRemisePageButton(">", currentPageRemise + 1);
    const lastPageButton = createRemisePageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

let currentPageBonus = 1;
const rowsPerPageBonus = 10;
let bonusData = [];
let filtersBonus = {
    product: '',
    bonus: '',
    laboratoryName: '',
    fournisseur: ''
};
let sortColumnBonus = '';
let sortDirectionBonus = 'asc';

// Fetch data for the bonus table
async function fetchBonusData() {
    try {
        const response = await fetch('http://127.0.0.1:5000/fetch-bonus-data');
        if (!response.ok) throw new Error('Network response was not ok');

        bonusData = await response.json();
        updateBonusTableAndPagination();
    } catch (error) {
        console.error("Error fetching bonus data:", error);
    }
}

function filterBonusDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filtersBonus[type] = searchValue;
    currentPageBonus = 1;
    updateBonusTableAndPagination();
}

function filterBonusData(data) {
    return data.filter(row => {
        return (!filtersBonus.product || row.PRODUCT.toLowerCase().includes(filtersBonus.product)) &&
               (!filtersBonus.bonus || row.BONUS.toLowerCase().includes(filtersBonus.bonus)) &&
               (!filtersBonus.laboratoryName || row.LABORATORY_NAME.toLowerCase().includes(filtersBonus.laboratoryName)) &&
               (!filtersBonus.fournisseur || row.FOURNISSEUR.toLowerCase().includes(filtersBonus.fournisseur));
    });
}

function sortBonusTable(column) {
    if (sortColumnBonus === column) {
        sortDirectionBonus = sortDirectionBonus === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnBonus = column;
        sortDirectionBonus = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });

    // Add arrow to current sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirectionBonus === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }

    updateBonusTableAndPagination();
}

function updateBonusTableAndPagination() {
    renderBonusTablePage(currentPageBonus);
    renderBonusPagination();
}

function renderBonusTablePage(page) {
    let filteredData = filterBonusData(bonusData);

    // Sort data
    if (sortColumnBonus) {
        filteredData.sort((a, b) => {
            if (a[sortColumnBonus] < b[sortColumnBonus]) return sortDirectionBonus === 'asc' ? -1 : 1;
            if (a[sortColumnBonus] > b[sortColumnBonus]) return sortDirectionBonus === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPageBonus;
    const end = start + rowsPerPageBonus;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("bonus-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.BONUS || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LABORATORY_NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function createBonusPageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterBonusData(bonusData).length / rowsPerPageBonus);
    button.addEventListener("click", () => {
        currentPageBonus = pageNumber;
        updateBonusTableAndPagination();
    });
    return button;
}

function renderBonusPagination() {
    const filteredData = filterBonusData(bonusData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPageBonus);
    const paginationContainer = document.getElementById("pagination-bonus");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createBonusPageButton("First", 1);
    const prevPageButton = createBonusPageButton("<", currentPageBonus - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPageBonus;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createBonusPageButton(">", currentPageBonus + 1);
    const lastPageButton = createBonusPageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

// Reserved Products Table Script

let reservedData = [];
let filtersReserved = {};
let sortColumnReserved = null;
let sortDirectionReserved = 'asc';
let currentPageReserved = 1;
const rowsPerPageReserved = 10;

// Fetch data for the reserved table
async function fetchReservedData() {
    try {
        const response = await fetch('http://127.0.0.1:5000/fetch-reserved-data');
        if (!response.ok) throw new Error('Network response was not ok');

        reservedData = await response.json();
        updateReservedTableAndPagination();
    } catch (error) {
        console.error("Error fetching reserved data:", error);
    }
}

// Filter function for the reserved table
function filterReservedData(data) {
    return data.filter(row => {
        return (!filtersReserved.operateur || row.OPERATEUR.toLowerCase().includes(filtersReserved.operateur)) &&
               (!filtersReserved.ndocument || row.NDOCUMENT.toLowerCase().includes(filtersReserved.ndocument)) &&
               (!filtersReserved.product || row.PRODUCT.toLowerCase().includes(filtersReserved.product)) &&
               (!filtersReserved.datecommande || row.DATECOMMANDE.toLowerCase().includes(filtersReserved.datecommande)) &&
               (!filtersReserved.totalreserve || row.TOTALRESERVE.toString().includes(filtersReserved.totalreserve)) &&
               (!filtersReserved.qtyreserve || row.QTYRESERVE.toString().includes(filtersReserved.qtyreserve)) &&
               (!filtersReserved.name || row.NAME.toLowerCase().includes(filtersReserved.name)) &&
               (!filtersReserved.status || row.STATUS.toLowerCase().includes(filtersReserved.status));
    });
}

// Sorting function
function sortReservedTable(column) {
    if (sortColumnReserved === column) {
        sortDirectionReserved = sortDirectionReserved === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnReserved = column;
        sortDirectionReserved = 'asc';
    }

    // Remove arrows from all headers
    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });

    // Add arrow to sorted column
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirectionReserved === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }

    updateReservedTableAndPagination();
}

// Update the table and pagination
function updateReservedTableAndPagination() {
    renderReservedTablePage(currentPageReserved);
    renderReservedPagination();
}

// Render a page of reserved data
function renderReservedTablePage(page) {
    let filteredData = filterReservedData(reservedData);

    // Sorting logic
    if (sortColumnReserved) {
        filteredData.sort((a, b) => {
            if (a[sortColumnReserved] < b[sortColumnReserved]) return sortDirectionReserved === 'asc' ? -1 : 1;
            if (a[sortColumnReserved] > b[sortColumnReserved]) return sortDirectionReserved === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const start = (page - 1) * rowsPerPageReserved;
    const end = start + rowsPerPageReserved;
    const pageData = filteredData.slice(start, end);

    const tableBody = document.getElementById("reserved-table");
    tableBody.innerHTML = "";

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add('table-row', 'dark:bg-gray-700');
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.OPERATEUR || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.NDOCUMENT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.DATECOMMANDE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.TOTALRESERVE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTYRESERVE || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.STATUS || ''}</td>
        `;
        tableBody.appendChild(tr);
    });
}

// Create pagination buttons
function createReservedPageButton(text, pageNumber) {
    const button = document.createElement("button");
    button.innerText = text;
    button.classList.add("px-4", "py-2", "bg-gray-300", "rounded", "hover:bg-gray-400", "dark:bg-gray-700", "dark:text-white");
    button.disabled = pageNumber < 1 || pageNumber > Math.ceil(filterReservedData(reservedData).length / rowsPerPageReserved);
    button.addEventListener("click", () => {
        currentPageReserved = pageNumber;
        updateReservedTableAndPagination();
    });
    return button;
}

// Render pagination for the reserved table
function renderReservedPagination() {
    const filteredData = filterReservedData(reservedData);
    const totalPages = Math.ceil(filteredData.length / rowsPerPageReserved);
    const paginationContainer = document.getElementById("pagination-reserved");
    paginationContainer.innerHTML = "";

    // First and Previous buttons
    const firstPageButton = createReservedPageButton("First", 1);
    const prevPageButton = createReservedPageButton("<", currentPageReserved - 1);

    paginationContainer.appendChild(firstPageButton);
    paginationContainer.appendChild(prevPageButton);

    // Current Page Button
    const pageButton = document.createElement("button");
    pageButton.innerText = currentPageReserved;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    // Next and Last buttons
    const nextPageButton = createReservedPageButton(">", currentPageReserved + 1);
    const lastPageButton = createReservedPageButton("Last", totalPages);

    paginationContainer.appendChild(nextPageButton);
    paginationContainer.appendChild(lastPageButton);
}

// Call fetch function when the page loads
document.addEventListener("DOMContentLoaded", fetchReservedData);

   
