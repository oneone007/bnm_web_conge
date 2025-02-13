

// Initialize Lottie animation
var loadingAnimation = lottie.loadAnimation({
    container: document.getElementById('lottie-container'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'date.json' // Path to your JSON file
});

// Ensure dates clear on refresh
window.onload = () => {
    document.getElementById("start-date").value = "";
    document.getElementById("end-date").value = "";
};

// Fetch data when both dates are selected
async function fetchTotalRecap() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) return; // Don't fetch until both dates are selected

    try {
        const response = await fetch(`http://127.0.0.1:5003/fetchTotalrecapData?start_date=${startDate}&end_date=${endDate}`);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        updateTotalRecapTable(data, startDate, endDate);
        hideLoader();
    } catch (error) {
        console.error("Error fetching total recap data:", error);
        document.getElementById('loading-row').innerHTML = "<td colspan='5' class='text-center text-red-500'>Failed to load data</td>";
        hideLoader();
    }
}

function hideLoader() {
    const loaderRow = document.getElementById('loading-row');
    if (loaderRow) {
        loaderRow.remove();
    }
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Format percentage
function formatPercentage(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return (parseFloat(value) * 100).toFixed(2) + "%";
}

// Update table with fetched data
function updateTotalRecapTable(data, startDate, endDate) {
    const tableBody = document.getElementById("recap-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const row = data[0]; // Since it's only one row
    tableBody.innerHTML = `
<tr class="dark:bg-gray-700">
    <td class="border px-4 py-2 dark:border-gray-600">From ${startDate} to ${endDate}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.MARGE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatPercentage(row.POURCENTAGE)}</td>
</tr>
`;
}

// Attach event listeners to date inputs
document.getElementById("start-date").addEventListener("change", fetchTotalRecap);
document.getElementById("end-date").addEventListener("change", fetchTotalRecap);


// Debounce function to limit requests
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
}

// Fetch data when filters are applied
async function fetchFournisseurRecap() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
    const client = document.getElementById("recap_client").value.trim().toUpperCase();
    const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
    const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
    const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

    if (!startDate || !endDate) return;

    const url = new URL("http://127.0.0.1:5003/fetchFournisseurData");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        showLoader();
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Fetched Data:", data);  // Debugging line to check if response contains data
        updateFournisseurTable(data);
        hideLoader();
    } catch (error) {
        console.error("Error fetching fournisseur data:", error);
        document.getElementById('recap-frnsr-table').innerHTML =
            `<tr><td colspan="5" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideLoader();
    }
}


// Show loader animation
function showLoader() {
    document.getElementById("recap-frnsr-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="5" class="text-center p-4">Loading...</td>
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

// Update table with fetched data
function updateFournisseurTable(data) {
    const tableBody = document.getElementById("recap-frnsr-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    if (totalRow) {
        tableBody.innerHTML += `
            <tr class="bg-gray-200 font-bold">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.MARGE)}%</td>
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.SORT_ORDER}</td>
            </tr>
        `;
    }

    filteredData.forEach(row => {
        tableBody.innerHTML += `
            <tr class="dark:bg-gray-700">
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.MARGE)}%</td>
                <td class="border px-4 py-2 dark:border-gray-600">${row.SORT_ORDER}</td>
            </tr>
        `;
    });
}

// Attach event listeners
// Attach event listeners for all filters
document.getElementById("start-date").addEventListener("change", fetchFournisseurRecap);
document.getElementById("end-date").addEventListener("change", fetchFournisseurRecap);
document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchFournisseurRecap, 500));
document.getElementById("recap_product").addEventListener("input", debounce(fetchFournisseurRecap, 500));
document.getElementById("recap_client").addEventListener("input", debounce(fetchFournisseurRecap, 500));
document.getElementById("recap_operateur").addEventListener("input", debounce(fetchFournisseurRecap, 500));
document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchFournisseurRecap, 500));
document.getElementById("recap_zone").addEventListener("input", debounce(fetchFournisseurRecap, 500));

// Debounce function to limit requests
function debounce(func, delay) {
let timeout;
return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), delay);
};
}

// Fetch data when filters are applied
async function fetchProductRecap() {
const startDate = document.getElementById("start-date").value;
const endDate = document.getElementById("end-date").value;
const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
const product = document.getElementById("recap_product").value.trim().toUpperCase();
const client = document.getElementById("recap_client").value.trim().toUpperCase();
const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

if (!startDate || !endDate) return;

const url = new URL("http://127.0.0.1:5003/fetchProductData");
url.searchParams.append("start_date", startDate);
url.searchParams.append("end_date", endDate);
if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
if (product) url.searchParams.append("product", product);
if (client) url.searchParams.append("client", client);
if (operateur) url.searchParams.append("operateur", operateur);
if (bccb) url.searchParams.append("bccb", bccb);
if (zone) url.searchParams.append("zone", zone);

try {
    const response = await fetch(url);
    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();
    console.log("Received Data:", data);  // 🚀 Debugging line
    updateProductTable(data);
} catch (error) {
    console.error("Error fetching product data:", error);
}
}

// Update table with fetched data
function updateProductTable(data) {
const tableBody = document.getElementById("recap-prdct-table");
tableBody.innerHTML = ""; // Clear existing rows

if (!data || data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
    return;
}

console.log("Updating table with:", data);  // Debugging

// Use DocumentFragment to improve performance
const fragment = document.createDocumentFragment();

data.forEach(row => {
    console.log("Row Data:", row);

    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td class="border px-4 py-2">${row.PRODUIT || "N/A"}</td>
        <td class="border px-4 py-2">${row.total}</td>
        <td class="border px-4 py-2">${row.qty}</td>
        <td class="border px-4 py-2">${row.marge ? row.marge.toFixed(2) + "%" : "0%"}</td>
    `;
    fragment.appendChild(tr);
});

tableBody.appendChild(fragment); // Append all rows at once
}

// Attach event listeners to all search fields
document.getElementById("start-date").addEventListener("change", fetchProductRecap);
document.getElementById("end-date").addEventListener("change", fetchProductRecap);
document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchProductRecap, 500));
document.getElementById("recap_product").addEventListener("input", debounce(fetchProductRecap, 500));
document.getElementById("recap_client").addEventListener("input", debounce(fetchProductRecap, 500));
document.getElementById("recap_operateur").addEventListener("input", debounce(fetchProductRecap, 500));
document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchProductRecap, 500));
document.getElementById("recap_zone").addEventListener("input", debounce(fetchProductRecap, 500));
