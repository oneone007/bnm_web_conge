let allData = [];
let selectedMagasin = null;
let selectedEmplacement = null;

// Debounce function to limit API calls
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
    initializeDropdowns();
    fetchData(); // Initial fetch without any filters
    
    // Set up other event listeners
    document.getElementById("refreshButton").addEventListener("click", () => {
        console.log("Refreshing data...");
        const fournisseur = document.getElementById("recap_fournisseur").value.trim();
        fetchData(fournisseur, selectedMagasin, selectedEmplacement);
    });

    document.getElementById('stock_excel').addEventListener('click', exportToExcel);
    setupFournisseurSearch();
    setupThemeToggle();
});

// Initialize dropdown functionality
function initializeDropdowns() {
    // Load magasins dropdown
    loadMagasinsDropdown();
    
    // Set up dropdown event listeners
    document.getElementById("magasinDropdown").addEventListener("change", function() {
        selectedMagasin = this.value || null;
        updateEmplacementDropdown();
        fetchFilteredData();
    });
    
    document.getElementById("emplacementDropdown").addEventListener("change", function() {
        selectedEmplacement = this.value || null;
        fetchFilteredData();
    });
}

// Load magasins into dropdown
async function loadMagasinsDropdown() {
    const dropdown = document.getElementById("magasinDropdown");
    try {
        const response = await fetch("http://192.168.1.94:5000/fetch-magasins");
        if (!response.ok) throw new Error("Failed to load magasins");
        
        const data = await response.json();
        dropdown.innerHTML = '<option value="">All Magasins</option>';
        
        data.forEach(magasin => {
            const option = document.createElement("option");
            option.value = magasin.MAGASIN;
            option.textContent = magasin.MAGASIN || "Unknown";
            dropdown.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading magasins:", error);
        dropdown.innerHTML = '<option value="">Error loading magasins</option>';
    }
}

// Update emplacement dropdown based on selected magasin
async function updateEmplacementDropdown() {
    const dropdown = document.getElementById("emplacementDropdown");
    
    if (!selectedMagasin) {
        dropdown.innerHTML = '<option value="">Select magasin first</option>';
        dropdown.disabled = true;
        return;
    }
    
    dropdown.innerHTML = '<option value="">Loading emplacements...</option>';
    dropdown.disabled = false;
    
    try {
        const url = new URL("http://192.168.1.94:5000/fetch-emplacements");
        url.searchParams.append("magasin", selectedMagasin);
        
        const response = await fetch(url);
        if (!response.ok) throw new Error("Failed to load emplacements");
        
        const data = await response.json();
        dropdown.innerHTML = '<option value="">All Emplacements</option>';
        
        data.forEach(emplacement => {
            const option = document.createElement("option");
            option.value = emplacement.EMPLACEMENT;
            option.textContent = emplacement.EMPLACEMENT || "Unknown";
            dropdown.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading emplacements:", error);
        dropdown.innerHTML = '<option value="">Error loading emplacements</option>';
    }
}

// Fetch data with current filters
async function fetchData(fournisseur = "", magasin = null, emplacement = null) {
    try {
        const url = new URL("http://192.168.1.94:5000/fetch-stock-data");
        if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
        if (magasin) url.searchParams.append("magasin", magasin);
        if (emplacement) url.searchParams.append("emplacement", emplacement);

        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');

        allData = await response.json();
        console.log("Fetched Data:", allData);
        renderTable();
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

// Alias for fetchData to maintain compatibility
function fetchFilteredData() {
    const fournisseur = document.getElementById("recap_fournisseur").value.trim();
    fetchData(fournisseur, selectedMagasin, selectedEmplacement);
}

// Export to Excel function
function exportToExcel() {
    const fournisseur = document.getElementById('recap_fournisseur').value.trim() || null;
    const magasin = selectedMagasin;
    const emplacement = selectedEmplacement;

    let url = 'http://192.168.1.94:5000/download-stock-excel?';
    if (fournisseur) url += `fournisseur=${fournisseur}&`;
    if (magasin) url += `magasin=${magasin}&`;
    if (emplacement) url += `emplacement=${emplacement}&`;

    if (url.endsWith('&')) url = url.slice(0, -1);
    window.location.href = url;
}

// Fournisseur search functionality
function setupFournisseurSearch() {
    const fournisseurInput = document.getElementById("recap_fournisseur");
    const fournisseurDropdown = document.getElementById("fournisseur-dropdown");

    function clearSearch() {
        fournisseurInput.value = "";
        fournisseurDropdown.style.display = "none";
        fetchData("", selectedMagasin, selectedEmplacement);
    }

    fournisseurInput.addEventListener("input", debounce(function() {
        const searchValue = this.value.trim().toLowerCase();
        if (searchValue) {
            showFournisseurDropdown(searchValue);
        } else {
            clearSearch();
        }
    }, 300));

    fournisseurInput.addEventListener("click", clearSearch);
}

// Show fournisseur dropdown
function showFournisseurDropdown(searchValue) {
    const dropdown = document.getElementById("fournisseur-dropdown");
    dropdown.innerHTML = "";
    dropdown.style.display = "block";

    const uniqueFournisseurs = [...new Set(allData.map(row => row.FOURNISSEUR))]
        .filter(f => f && f.toLowerCase().includes(searchValue));

    if (uniqueFournisseurs.length === 0) {
        dropdown.style.display = "none";
        return;
    }

    uniqueFournisseurs.forEach(fournisseur => {
        const option = document.createElement("div");
        option.classList.add("dropdown-item");
        option.textContent = fournisseur;
        option.addEventListener("click", () => {
            document.getElementById("recap_fournisseur").value = fournisseur;
            dropdown.style.display = "none";
            fetchData(fournisseur, selectedMagasin, selectedEmplacement);
        });
        dropdown.appendChild(option);
    });
}

// Theme toggle functionality
function setupThemeToggle() {
    document.getElementById('themeToggle').addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
    });

    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
    }
}

// Keep your existing renderTable and createTableRow functions exactly as they were
function renderTable() {
    const tableBody = document.getElementById("data-table");
    tableBody.innerHTML = "";

    let totalRow = allData.find(row => row.FOURNISSEUR?.toLowerCase() === "total");
    let filteredData = allData.filter(row => row.FOURNISSEUR?.toLowerCase() !== "total");

    if (totalRow) {
        const tr = createTableRow(totalRow, true);
        tableBody.appendChild(tr);
    }

    filteredData.forEach(row => {
        const tr = createTableRow(row);
        tableBody.appendChild(tr);
    });
}

function createTableRow(row, isTotal = false) {
    const tr = document.createElement("tr");
    tr.classList.add('table-row', 'dark:bg-gray-700');

    if (isTotal) {
        tr.classList.add('font-bold', 'bg-gray-200', 'dark:bg-gray-800');
    }

    const formatNumber = (num) => num ? parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';

    tr.innerHTML = `
        <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${row.NAME || ''}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY_DISPO)}</td>
        <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.PRIX_DISPO)}</td>
    `;
    return tr;
}
document.getElementById('themeToggle').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
});

if (localStorage.getItem('darkMode') === 'true') {
    document.documentElement.classList.add('dark');
}

  