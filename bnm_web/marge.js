
let currentPage = 1;
const rowsPerPage = 20;
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
window.onload = fetchData;

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



