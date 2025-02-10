
let emplacementPage = 1;
const emplacementRowsPerPage = 10;
let emplacementData = [];
let emplacementSortColumn = '';
let emplacementSortDirection = 'asc';

// Fetch Emplacement data on page load
window.onload = () => {
    fetchEmplacementData();
    fetchData();

};

document.addEventListener("DOMContentLoaded", () => {
    fetchEmplacementData();
});

async function fetchEmplacementData() {
    try {
        const response = await fetch('http://127.0.0.1:5003/fetch-emplacement-data');
        if (!response.ok) throw new Error('Network response was not ok');

        const emplacementData = await response.json();
        updateEmplacementDropdown(emplacementData);
    } catch (error) {
        console.error("Error fetching emplacement data:", error);
        document.getElementById("emplacement-dropdown").innerHTML = `<option value="">Failed to load</option>`;
    }
}

function updateEmplacementDropdown(data) {
    const dropdown = document.getElementById("emplacement-dropdown");
    dropdown.innerHTML = ""; // Clear existing options

    if (!data || data.length === 0) {
        dropdown.innerHTML = `<option value="">No data available</option>`;
        return;
    }

    data.forEach(item => {
        const option = document.createElement("option");
        option.value = item.EMPLACEMENT || "";
        option.textContent = item.EMPLACEMENT || "Unknown";
        dropdown.appendChild(option);
    });
}


let currentPage = 1;
const rowsPerPage = 10;
let allData = [];
let filters = {
    fournisseur: '',
    name: '',
    qty: '',
    prix: '',
    qty_dispo: '',
    prix_dispo: '',
    locatorid: '',
    PRODUCTID: '',
    sort_order: ''
};
let sortColumn = '';
let sortDirection = 'asc';

// Fetch data on page load
async function fetchData() {
    try {
        const response = await fetch('http://127.0.0.1:5003/fetch-data');
        if (!response.ok) throw new Error('Network response was not ok');
        allData = await response.json();
        console.log("Fetched Data:", allData); // Debugging log
        updateTableAndPagination();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

document.getElementById("stock").addEventListener("click", function () {
    window.open("http://127.0.0.1:5003/download-stock-excel", "_blank"); 
});

function filterDropdown(type) {
    const searchValue = document.getElementById(`search-${type}`).value.toLowerCase();
    filters[type] = searchValue;
    currentPage = 1;
    updateTableAndPagination();
}

function filterData(data) {
    const selectedLocatorID = Number(filters.locatorid); // Convert once for efficiency

    return data.filter(row => {
        if (!filters.locatorid) return true; // No filter applied, show all
        return Number(row.LOCATORID) === selectedLocatorID;
    });
}



function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    document.querySelectorAll('th').forEach(th => {
        const content = th.innerText.replace(/ ↑| ↓/g, '');
        th.innerText = content;
    });
    
    const currentHeader = document.querySelector(`th[data-column="${column}"]`);
    if (currentHeader) {
        const arrow = sortDirection === 'asc' ? ' ↑' : ' ↓';
        currentHeader.innerText += arrow;
    }
    
    updateTableAndPagination();
}
document.getElementById("locatorDropdown").addEventListener("change", function () {
    filters.locatorid = this.value; // Store the selected value in filters
    currentPage = 1;
    updateTableAndPagination(); // Refresh the table with the new filter
});

function updateTableAndPagination() {
    renderTablePage(currentPage);
    renderPagination();
}

function renderTablePage(page) {
    let filteredData = filterData(allData);

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
            <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRIX || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY_DISPO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRIX_DISPO || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.LOCATORID || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCTID || ''}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.SORT_ORDER || ''}</td>
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

    paginationContainer.appendChild(createPageButton("First", 1));
    paginationContainer.appendChild(createPageButton("<", currentPage - 1));

    const pageButton = document.createElement("button");
    pageButton.innerText = currentPage;
    pageButton.classList.add("px-4", "py-2", "bg-blue-500", "text-white", "rounded", "dark:bg-blue-600");
    pageButton.disabled = true;
    paginationContainer.appendChild(pageButton);

    paginationContainer.appendChild(createPageButton(">", currentPage + 1));
    paginationContainer.appendChild(createPageButton("Last", totalPages));
}

document.getElementById('themeToggle').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
});

if (localStorage.getItem('darkMode') === 'true') {
    document.documentElement.classList.add('dark');
}
