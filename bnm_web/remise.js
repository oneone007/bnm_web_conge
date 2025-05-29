let currentPageRemise = 1;
const rowsPerPageRemise = 20;
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
window.onload = fetchRemiseData;

async function fetchRemiseData() {
    try {
        const response = await fetch('http://127.0.0.1:5001/fetch-remise-data');
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
