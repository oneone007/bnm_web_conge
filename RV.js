

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
    // Clear all inputs except date fields on page load
    document.getElementById("recap_fournisseur").value = "";
    document.getElementById("recap_product").value = "";
    document.getElementById("recap_zone").value = "";
    document.getElementById("recap_client").value = "";
    document.getElementById("recap_operateur").value = "";
    document.getElementById("recap_bccbclient").value = "";

    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split("T")[0];

    function updateEndDate() {
        if (!endDateInput.value || new Date(endDateInput.value) < new Date(startDateInput.value)) {
            endDateInput.value = today;
        }

        // Trigger events
        endDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("change", { bubbles: true }));
    }

    // Set end date when start date is selected
    startDateInput.addEventListener("change", updateEndDate);

    // Refresh button: clear other fields but keep date fields
    document.getElementById("refresh-btn").addEventListener("click", () => {
        // Clear non-date fields
        document.getElementById("recap_fournisseur").value = "";
        document.getElementById("recap_product").value = "";
        document.getElementById("recap_zone").value = "";
        document.getElementById("recap_client").value = "";
        document.getElementById("recap_operateur").value = "";
        document.getElementById("recap_bccbclient").value = "";

        // Trigger update events for date fields
        startDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        startDateInput.dispatchEvent(new Event("change", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("change", { bubbles: true }));

        // Refresh data based on existing date values
        fetchData(startDateInput.value, endDateInput.value, "", "", "", "", "", ""); 
    });
};



            // Fetch data when both dates are selected
            async function fetchTotalRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;

                if (!startDate || !endDate) return; // Don't fetch until both dates are selected

                try {
                    
                    const response = await fetch(`http://192.168.1.94:5001/fetchTotalrecapData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`);

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
            function formatNumbert(value) {
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
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.CHIFFRE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.QTY)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.MARGE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatPercentage(row.POURCENTAGE)}</td>
</tr>
`;
            }

            // Attach event listeners to date inputs
            document.getElementById("start-date").addEventListener("change", fetchTotalRecap);
            document.getElementById("end-date").addEventListener("change", fetchTotalRecap);



            document.getElementById("downloadExcel_totalrecap").addEventListener("click", function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        alert("Please select both start and end dates before downloading.");
        return;
    }

    const downloadUrl = `http://192.168.1.94:5001/download-totalrecap-excel?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`;
    window.location.href = downloadUrl;  // Triggers file download
});


let currentPage = 1;
const rowsPerPage = 10;
let fullData = [];


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
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5001/fetchFournisseurData");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000"); 
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
            function formatNumberf(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }


            document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("recap-frnsr-table");
    const fournisseurInput = document.getElementById("recap_fournisseur");

    tableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedFournisseurs = [...document.querySelectorAll(".selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        fournisseurInput.value = selectedFournisseurs.join(", ");

        // Manually trigger the input event to simulate user search
        fournisseurInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchFournisseurRecap();
    });
});




   
function updateFournisseurTable(data) {
    const tableBody = document.getElementById("recap-frnsr-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Save data globally for pagination
    fullData = data;

    // Separate total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    currentPage = Math.min(currentPage, totalPages);

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = filteredData.slice(start, end);

    // Append total row first
    if (totalRow) {
        tableBody.innerHTML += `
            <tr class="bg-gray-200 font-bold">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.MARGE)}%</td>
            </tr>
        `;
    }

    // Then add paginated rows
    pageData.forEach(row => {
        tableBody.innerHTML += `
            <tr class="dark:bg-gray-700">
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.MARGE)}%</td>
            </tr>
        `;
    });

    // Update pagination text
    document.getElementById("pageIndicator").textContent = `Page ${currentPage} of ${totalPages}`;
}


document.getElementById("firstPage").addEventListener("click", () => {
    currentPage = 1;
    updateFournisseurTable(fullData);
});

document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) currentPage--;
    updateFournisseurTable(fullData);
});

document.getElementById("nextPage").addEventListener("click", () => {
    const totalPages = Math.ceil((fullData.filter(r => r.FOURNISSEUR !== "Total").length) / rowsPerPage);
    if (currentPage < totalPages) currentPage++;
    updateFournisseurTable(fullData);
});

document.getElementById("lastPage").addEventListener("click", () => {
    currentPage = Math.ceil((fullData.filter(r => r.FOURNISSEUR !== "Total").length) / rowsPerPage);
    updateFournisseurTable(fullData);
});


document.getElementById("download-fournisseur").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("r:ecap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5001/download-fournisseur-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

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

            let currentProductPage = 1;
const productRowsPerPage = 10;
let fullProductData = [];

document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-prdct-table");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-prdct-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        document.getElementById("recap_product").value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        document.getElementById("recap_product").dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchProductRecap();
    });
});

// Fetch data for product table
async function fetchProductRecap() {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5001/fetchProductData");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); 
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

// Update product table with pagination
function updateProductTable(data) {
    const tableBody = document.getElementById("recap-prdct-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Save data globally for pagination
    fullProductData = data;

    // Separate the "Total" row
    const totalRow = data.find(row => row.PRODUIT === "Total");
    const filteredData = data.filter(row => row.PRODUIT !== "Total");

    const totalPages = Math.ceil(filteredData.length / productRowsPerPage);
    currentProductPage = Math.min(currentProductPage, totalPages);

    const start = (currentProductPage - 1) * productRowsPerPage;
    const end = start + productRowsPerPage;
    const pageData = filteredData.slice(start, end);

    const fragment = document.createDocumentFragment();

    // Add total row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Add paginated rows
    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination text
    document.getElementById("productPageIndicator").textContent = `Page ${currentProductPage} of ${totalPages}`;
}

// Pagination controls
document.getElementById("firstProductPage").addEventListener("click", () => {
    currentProductPage = 1;
    updateProductTable(fullProductData);
});

document.getElementById("prevProductPage").addEventListener("click", () => {
    if (currentProductPage > 1) currentProductPage--;
    updateProductTable(fullProductData);
});

document.getElementById("nextProductPage").addEventListener("click", () => {
    const totalPages = Math.ceil(fullProductData.filter(r => r.PRODUIT !== "Total").length / productRowsPerPage);
    if (currentProductPage < totalPages) currentProductPage++;
    updateProductTable(fullProductData);
});

document.getElementById("lastProductPage").addEventListener("click", () => {
    currentProductPage = Math.ceil(fullProductData.filter(r => r.PRODUIT !== "Total").length / productRowsPerPage);
    updateProductTable(fullProductData);
});

// Format number for product table with thousand separators & two decimals
function formatNumberp(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}



document.getElementById("download-product-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5001/download-product-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

            // Format numbers with commas (thousands separator)
            function formatNumber(value) {
                return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
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





            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            }

            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-zone-table");
    const productInput = document.getElementById("recap_zone");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-zone-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        updateZoneTable();
    });
});

// Pagination state for zone
let currentZonePage = 1;
let totalZonePages = 1;
const itemsPerZonePage = 10; // Number of items to display per page

// Fetch data when filters are applied for zone recap
async function fetchZoneRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5001/fetchZoneRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000");
    url.searchParams.append("page", page);  // Add the page parameter for pagination
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
        console.log("Received Data:", data); // Debugging log
        updateZoneTable(data);
        return data; // Ensure function returns data
    } catch (error) {
        console.error("Error fetching zone recap data:", error);
    }
}

// Update table with fetched zone data
function updateZoneTable(data) {
    const tableBody = document.getElementById("recap-zone-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.ZONE === "Total");
    const filteredData = data.filter(row => row.ZONE !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.ZONE}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append remaining rows (apply pagination by slicing the data)
    const paginatedData = filteredData.slice((currentZonePage - 1) * itemsPerZonePage, currentZonePage * itemsPerZonePage);
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.ZONE || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination
    updatePagination(filteredData.length);
}

// Update pagination controls for zone
function updatePagination(totalItems) {
    totalZonePages = Math.ceil(totalItems / itemsPerZonePage);

    const zonePageIndicator = document.getElementById("zonePageIndicator");
    zonePageIndicator.textContent = `Page ${currentZonePage} of ${totalZonePages}`;

    document.getElementById("firstzonePage").disabled = currentZonePage === 1;
    document.getElementById("prevzonePage").disabled = currentZonePage === 1;
    document.getElementById("nextzonePage").disabled = currentZonePage === totalZonePages;
    document.getElementById("lastzonePage").disabled = currentZonePage === totalZonePages;
}

// Handle pagination button clicks for zone
document.getElementById("firstzonePage").addEventListener("click", () => changeZonePage(1));
document.getElementById("prevzonePage").addEventListener("click", () => changeZonePage(currentZonePage - 1));
document.getElementById("nextzonePage").addEventListener("click", () => changeZonePage(currentZonePage + 1));
document.getElementById("lastzonePage").addEventListener("click", () => changeZonePage(totalZonePages));

// Change page for zone
function changeZonePage(page) {
    if (page < 1 || page > totalZonePages) return;
    currentZonePage = page;
    fetchZoneRecap(currentZonePage); // Fetch data for the new page
}

// Format numbers with commas (thousands separator)
function formatNumber(value) {
    return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
}


            // Attach event listeners to all search fields
            document.getElementById("start-date").addEventListener("change", fetchZoneRecap);
            document.getElementById("end-date").addEventListener("change", fetchZoneRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchZoneRecap, 500));

// Download Zone Recap as Excel
document.getElementById("download-zone-excel").addEventListener("click", function () {
    downloadExcel("download-zone-excel");
});

// Download Client Recap as Excel
document.getElementById("download-client-excel").addEventListener("click", function () {
    downloadExcel("download-client-excel");
});
// Download Operator Recap as Excel
document.getElementById("download-Opérateur-excel").addEventListener("click", function () {
    downloadExcel("download-operator-excel");
});

// Download BCCB Recap as Excel
document.getElementById("download-BCCB-excel").addEventListener("click", function () {
    downloadExcel("download-bccb-excel");
});
document.getElementById("download-bccb-product-excel").addEventListener("click", function () {
    downloadExcel("download-bccb-product-excel");
});
function downloadExcel(endpoint) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL(`http://192.168.1.94:5001/${endpoint}`);
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    window.location.href = url;
}


let currentClientPage = 1;
let totalClientPages = 1;
const itemsPerClientPage = 10; // Number of items to display per page


async function fetchClientRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5001/fetchClientRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000");
    url.searchParams.append("page", page);  // Add the page parameter for pagination
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
        console.log("Received Client Recap Data:", data); // Debugging log
        updateClientTable(data);
        return data; // Ensure function returns data
    } catch (error) {
        console.error("Error fetching client recap data:", error);
    }
}


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-client-table");
    const productInput = document.getElementById("recap_client");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-client-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchClientRecap();
    });
});


function updateClientTable(data) {
    const tableBody = document.getElementById("recap-client-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.CLIENT === "Total");
    const filteredData = data.filter(row => row.CLIENT !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.CLIENT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append remaining rows (apply pagination by slicing the data)
    const paginatedData = filteredData.slice((currentClientPage - 1) * itemsPerClientPage, currentClientPage * itemsPerClientPage);
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.CLIENT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination
    updateClientPagination(filteredData.length);
}
function updateClientPagination(totalItems) {
    totalClientPages = Math.ceil(totalItems / itemsPerClientPage);

    const clientPageIndicator = document.getElementById("clpageIndicator");
    clientPageIndicator.textContent = `Page ${currentClientPage} of ${totalClientPages}`;

    document.getElementById("clfirstPage").disabled = currentClientPage === 1;
    document.getElementById("clprevPage").disabled = currentClientPage === 1;
    document.getElementById("clnextPage").disabled = currentClientPage === totalClientPages;
    document.getElementById("cllastPage").disabled = currentClientPage === totalClientPages;
}
document.getElementById("clfirstPage").addEventListener("click", () => {
    currentClientPage = 1;
    fetchClientRecap(currentClientPage);
});

document.getElementById("clprevPage").addEventListener("click", () => {
    if (currentClientPage > 1) {
        currentClientPage--;
        fetchClientRecap(currentClientPage);
    }
});

document.getElementById("clnextPage").addEventListener("click", () => {
    if (currentClientPage < totalClientPages) {
        currentClientPage++;
        fetchClientRecap(currentClientPage);
    }
});

document.getElementById("cllastPage").addEventListener("click", () => {
    currentClientPage = totalClientPages;
    fetchClientRecap(currentClientPage);
});


            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchClientRecap);
            document.getElementById("end-date").addEventListener("change", fetchClientRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchClientRecap, 500));

// Button click triggers the fetching and chart creation
// Fetch the operator recap data normally, without waiting for the button click

let currentOperatorPage = 1;
let totalOperatorPages = 1;
const itemsPerOperatorPage = 10; // Number of items per page for operator recap




async function fetchOperatorRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5001/fetchOperatorRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000");
    url.searchParams.append("page", page);  // Add page parameter for pagination
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
        console.log("Received Operator Recap Data:", data); // Debugging log
        updateOperatorTable(data);  // Update table with fetched data
        return data; // Return fetched data
    } catch (error) {
        console.error("Error fetching operator recap data:", error);
    }
}




document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-operator-table");
    const productInput = document.getElementById("recap_operateur");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-operator-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchOperatorRecap();
    });
});
// Button click triggers the chart creation (after data is fetched)

// Update table with fetched data
function updateOperatorTable(data) {
    const tableBody = document.getElementById("recap-operator-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.OPERATEUR === "Total");
    const filteredData = data.filter(row => row.OPERATEUR !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.OPERATEUR}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Apply pagination by slicing the data
    const paginatedData = filteredData.slice((currentOperatorPage - 1) * itemsPerOperatorPage, currentOperatorPage * itemsPerOperatorPage);
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.OPERATEUR || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination
    updatePaginationop(filteredData.length);
}
function updatePaginationop(totalItems) {
    totalOperatorPages = Math.ceil(totalItems / itemsPerOperatorPage);

    const pageIndicator = document.getElementById("oppageIndicator");
    pageIndicator.textContent = `Page ${currentOperatorPage} of ${totalOperatorPages}`;

    document.getElementById("opfirstPage").disabled = currentOperatorPage === 1;
    document.getElementById("opprevPage").disabled = currentOperatorPage === 1;
    document.getElementById("opnextPage").disabled = currentOperatorPage === totalOperatorPages;
    document.getElementById("oplastPage").disabled = currentOperatorPage === totalOperatorPages;
}

document.getElementById("opfirstPage").addEventListener("click", () => changeOperatorPage(1));
document.getElementById("opprevPage").addEventListener("click", () => changeOperatorPage(currentOperatorPage - 1));
document.getElementById("opnextPage").addEventListener("click", () => changeOperatorPage(currentOperatorPage + 1));
document.getElementById("oplastPage").addEventListener("click", () => changeOperatorPage(totalOperatorPages));

function changeOperatorPage(page) {
    if (page < 1 || page > totalOperatorPages) return;
    currentOperatorPage = page;
    fetchOperatorRecap(currentOperatorPage); // Fetch data for the new page
}


// Update chart with fetched data (only when clicking the button)
let allcharts = null; // Global chart variable

// Function to fetch and update the Operator chart
document.getElementById("generate-chart-operateur").addEventListener("click", async function () {
    const data = await fetchOperatorRecap();
    console.log("Fetched operateur Data chart without total:", data); // Debugging log

    if (data && data.length > 0) {
        // Filter out the "Total" row before passing the data to the chart
        const dataForChart = data.filter(row => row.OPERATEUR !== "Total");
        
        // Update the chart with the filtered data
        updateChart(dataForChart, "OPERATEUR");
    } else {
        console.warn("No data received for ZONE.");
    }
});

// Function to fetch and update the Zone chart
document.getElementById("generate-chart-zone").addEventListener("click", async function () {
    const data = await fetchZoneRecap();
    console.log("Fetched Zone Data chart without total:", data); // Debugging log

    if (data && data.length > 0) {
        // Filter out the "Total" row before passing the data to the chart
        const dataForChart = data.filter(row => row.ZONE !== "Total");
        
        // Update the chart with the filtered data
        updateChart(dataForChart, "ZONE");
    } else {
        console.warn("No data received for ZONE.");
    }
});



// Generic function to update the chart based on dataset type (Operator or Zone)
function updateChart(data, type) {
    if (!data || data.length === 0) {
        console.warn(`No data available for the ${type} chart.`);
        return;
    }

    // Extract "Total" row if available
    const totalRow = data.find(row => row[type] === "Total");
    const filteredData = data.filter(row => row[type] !== "Total");

    // Prepare labels and values
    const labels = filteredData.map(row => row[type]);
    const totalValues = filteredData.map(row => row.TOTAL);
    const qtyValues = filteredData.map(row => row.QTY);
    const margeValues = filteredData.map(row => row.MARGE * 100);

    // Include the "Total" row in the chart
    if (totalRow) {
        labels.unshift(totalRow[type]);
        totalValues.unshift(totalRow.TOTAL);
        qtyValues.unshift(totalRow.QTY);
        margeValues.unshift(totalRow.MARGE * 100);
    }

    console.log(`Chart Labels for ${type}:`, labels);
    console.log("Total Values:", totalValues);
    console.log("Qty Values:", qtyValues);
    console.log("Marge Values:", margeValues);

    const canvas = document.getElementById("allcharts");
    if (!canvas) {
        console.error("Canvas element not found!");
        return;
    }

    const ctx = canvas.getContext("2d");

    // Destroy previous chart before creating a new one
    if (allcharts instanceof Chart) {
        console.log("Destroying old chart...");
        allcharts.destroy();
    }

    // Render the new chart
    setTimeout(() => {
        allcharts = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Total",
                        data: totalValues,
                        backgroundColor: "rgba(54, 162, 235, 0.6)",
                    },
                    {
                        label: "QTy",
                        data: qtyValues,
                        backgroundColor: "rgba(255, 99, 132, 0.6)",
                    },
                    {
                        label: "Marge (%)",
                        data: margeValues,
                        backgroundColor: "rgba(75, 192, 192, 0.6)",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });

        console.log(`${type} chart successfully created!`);
    }, 100);
}

// Helper function to format numbers
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchOperatorRecap);
            document.getElementById("end-date").addEventListener("change", fetchOperatorRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchOperatorRecap, 500));



let currentBccbPage = 1;
let totalBccbPages = 1;
const itemsPerBccbPage = 10; // Adjust this to the number of items per page

        
  // Fetch data for Recap by BCCB
  async function fetchBccbRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;
   
    const url = new URL("http://192.168.1.94:5001/fetchBCCBRecap"); 
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("page", page);  // Add page parameter for pagination
    
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
        console.log("Received BCCB Recap Data:", data); // Debugging log
        updateBccbTable(data);
        return data; // Return data for pagination
    } catch (error) {
        console.error("Error fetching BCCB recap data:", error);
    }
}
function formatNumberb(value) {
    return parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateString) {
    if (!dateString) return ''; // Return an empty string if no date provided

    const date = new Date(dateString);
    
    // Format the date as 'Wed, 26 Mar 2025'
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options); // 'en-GB' for British date format
}
// Debounce function to limit API calls on input change
function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}


 // Update table with fetched data
 function updateBccbTable(data) {
    const tableBody = document.getElementById("recap-bccb-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();
    const totalRow = data.find(row => row.DOCUMENTNO === "Total");
    const filteredData = data.filter(row => row.DOCUMENTNO !== "Total");

    const paginatedData = filteredData.slice((currentBccbPage - 1) * itemsPerBccbPage, currentBccbPage * itemsPerBccbPage);

    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DOCUMENTNO || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.DATEORDERED)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberb(row.GRANDTOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE !== null ? row.MARGE.toFixed(2) + '%' : "N/A"}</td>
        `;
        fragment.appendChild(tr);
    });

    // Append total row at the bottom
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600 text-right" colspan="2">Total:</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.GRANDTOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.MARGE !== null ? totalRow.MARGE.toFixed(2) + '%' : "N/A"}</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append everything to the table
    tableBody.appendChild(fragment);

    // Update pagination
    updatePaginationbccb(filteredData.length);
}
function updatePaginationbccb(totalItems) {
    totalBccbPages = Math.ceil(totalItems / itemsPerBccbPage);

    const pageIndicator = document.getElementById("bpageIndicator");
    pageIndicator.textContent = `Page ${currentBccbPage} of ${totalBccbPages}`;

    document.getElementById("bfirstPage").disabled = currentBccbPage === 1;
    document.getElementById("bprevPage").disabled = currentBccbPage === 1;
    document.getElementById("bnextPage").disabled = currentBccbPage === totalBccbPages;
    document.getElementById("blastPage").disabled = currentBccbPage === totalBccbPages;
}


document.getElementById("bfirstPage").addEventListener("click", () => changeBccbPage(1));
document.getElementById("bprevPage").addEventListener("click", () => changeBccbPage(currentBccbPage - 1));
document.getElementById("bnextPage").addEventListener("click", () => changeBccbPage(currentBccbPage + 1));
document.getElementById("blastPage").addEventListener("click", () => changeBccbPage(totalBccbPages));

function changeBccbPage(page) {
    if (page < 1 || page > totalBccbPages) return;
    currentBccbPage = page;
    fetchBccbRecap(currentBccbPage); // Fetch data for the new page
}



            

            document.getElementById("recap_bccbclient").addEventListener("input", debounce(() => {
    const bccbInput = document.getElementById("recap_bccbclient").value.trim();
    fetchBccbRecap();
    
    if (!bccbInput) {
        // Hide the product table if BCCB is cleared
        document.getElementById("bccb-product-container").style.display = "none";
    }
}, 500));


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-bccb-table");
    const productInput = document.getElementById("recap_bccbclient");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get selected BCCB (assuming only one should be selected)
        let selectedBccb = row.cells[0].textContent.trim();

        // Update input field
        productInput.value = selectedBccb;

        // Manually trigger input event
        productInput.dispatchEvent(new Event("input"));

        // Fetch BCCB Recap
        fetchBccbRecap();

        // Fetch BCCB Product (Fix: Use selectedBccb)
        fetchBccbProduct(selectedBccb);
    });
});

          
            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchBccbRecap);
            document.getElementById("end-date").addEventListener("change", fetchBccbRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchBccbRecap, 500));

 async function fetchBccbProduct(bccb) {
    if (!bccb) return;

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL("http://192.168.1.94:5001/fetchBCCBProduct");
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
    const tableBody = document.getElementById("recap-bccb-product-table");
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






// List all the input IDs you want to apply this to
const recapInputs = [
    'recap_fournisseur',
    'recap_product',
    'recap_zone',
    'recap_client',
    'recap_operateur',
    'recap_bccbclient'
];

// Add event listener for each one
recapInputs.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener('focus', function() {
            this.value = ''; // Clear
            const event = new Event('input', { bubbles: true }); // Trigger 'input' event
            this.dispatchEvent(event);
        });
    }
});
