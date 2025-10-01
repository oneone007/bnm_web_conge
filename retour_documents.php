<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Comptable', 'Sup Vente','gestion stock','stock', 'saisie'])) {
//     header("Location: Acess_Denied");    exit();
// }

$page_identifier = 'retour';
require_once 'check_permission.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retour Documents</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="retour_documents.css">
    <script src="theme.js"></script>
    <script src="api_config.js"></script>
</head>
<body class="min-h-screen transition-colors duration-300">
    <div class="container mx-auto px-4 py-8">
        <div class="page-header">
            <h1 class="page-title">Retour Documents</h1>
            <p class="page-subtitle">Recherche des documents ORM et simulation des produits</p>
        </div>

        <!-- Search Section -->
        <div class="search-container">
            <form class="search-form">
                <span class="search-label">N° Document</span>
                <input type="text" id="orm_search" class="search-input" placeholder="Entrer le numéro du document..." autocomplete="off">
                <span class="search-label">Du</span>
                <input type="date" id="start_date" class="date-input">
                <span class="search-label">Au</span>
                <input type="date" id="end_date" class="date-input">
                <button id="orm-search-btn" class="search-btn" type="button">
                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M4 9a5 5 0 1110 0A5 5 0 014 9zm5-7a7 7 0 104.2 12.6.999.999 0 00.093.107l3 3a1 1 0 001.414-1.414l-3-3a.999.999 0 00-.107-.093A7 7 0 009 2z" fill-rule="evenodd" fill="#FFF"></path>
                    </svg>
                </button>
            </form>
            <div class="search-hint">
                <span>🔎 Saisissez un numéro ORM et/ou sélectionnez les dates pour voir les documents.</span>
            </div>
        </div>
        <!-- Main Table -->
        <div class="table-container fade-in">
            <div class="table-section">
                <h2 class="table-title">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h2a1 1 0 100-2H7z"/>
                    </svg>
                    Documents ORM
                </h2>
            </div>
            <div class="overflow-x-auto" style="max-height:55vh;overflow-y:auto;">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>N° Document</th>
                            <th>Tiers</th>
                            <th>Date Commande</th>
                            <th>Vendeur</th>
                            <th>Marge</th>
                            <th>Montant</th>
                            <th>Organisation</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="orm-documents-table">
                        <!-- Data rows will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- ORM Product Details Table -->
        <div id="orm-product-container" class="table-container fade-in" style="display: none;">
            <div class="table-section">
                <h2 class="table-title">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h2a1 1 0 100-2H7z"/>
                    </svg>
                    Détails du Produit ORM
                    <span id="selected-orm-summary" class="table-subtitle"></span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Remise</th>
                            <th>Marge</th>
                        </tr>
                    </thead>
                    <tbody id="orm-product-table">
                        <!-- Product data will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script>
// Search and fetch ORM documents
async function fetchOrmDocuments() {
    const ndocument = document.getElementById('orm_search').value.trim();
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    let url = new URL(API_CONFIG.getApiUrl('/retour_documents'));
    // If document number is provided, use only that param
    if (ndocument) {
        url.searchParams.append('ndocument', ndocument);
    } else {
        if (startDate) url.searchParams.append('start_date', startDate);
        if (endDate) url.searchParams.append('end_date', endDate);
    }
    try {
        const response = await fetch(url);
        const data = await response.json();
        updateOrmDocumentsTable(data);
    } catch (error) {
        updateOrmDocumentsTable([]);
    }
}
// Update ORM documents table
function updateOrmDocumentsTable(data) {
    const tableBody = document.getElementById('orm-documents-table');
    tableBody.innerHTML = '';
    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-8 text-gray-500 dark:text-gray-400">Aucun document trouvé</td></tr>`;
        return;
    }
    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.classList.add('cursor-pointer', 'hover:bg-gray-200', 'dark:hover:bg-gray-700');
        // Format date as DD/MM/YYYY
        let dateCommande = '';
        if (row.DATECOMMANDE) {
            const dateObj = new Date(row.DATECOMMANDE);
            dateCommande = dateObj.toLocaleDateString('fr-FR');
        }
        // Format number with space as thousand separator and comma for decimals
        const formatNumber = (num) => num !== null && num !== undefined ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num) : '';
        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.NDOCUMENT || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.TIER || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${dateCommande}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.VENDEUR || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.MARGE !== null && row.MARGE !== undefined ? formatNumber(row.MARGE) + ' %' : ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white font-medium">${formatNumber(row.MONTANT)}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.ORGANISATION || ''}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.DESCRIPTION || ''}</td>
        `;
        tr.addEventListener('click', function() {
            // Remove highlight from all rows
            document.querySelectorAll('#orm-documents-table tr').forEach(r => r.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'ring-2', 'ring-blue-500'));
            // Highlight selected row
            tr.classList.add('bg-blue-100', 'dark:bg-blue-900', 'ring-2', 'ring-blue-500');
            fetchOrmProduct(row.NDOCUMENT);
            showOrmSummary(row);
        });
        tableBody.appendChild(tr);
    });
}
// Show summary for selected ORM document
function showOrmSummary(row) {
    const summaryEl = document.getElementById('selected-orm-summary');
    // Compose the summary with the requested fields
    summaryEl.innerHTML = `
        <span class="font-semibold">N° Document:</span> ${row.NDOCUMENT || ''} &nbsp;|
        <span class="font-semibold">Tiers:</span> ${row.TIER || ''} &nbsp;|
        <span class="font-semibold">Date Commande:</span> ${row.DATECOMMANDE ? new Date(row.DATECOMMANDE).toLocaleDateString('fr-FR') : ''} &nbsp;|
        <span class="font-semibold">Vendeur:</span> ${row.VENDEUR || ''} &nbsp;|
        <span class="font-semibold">Marge:</span> ${row.MARGE !== null && row.MARGE !== undefined ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(row.MARGE) + ' %' : ''} &nbsp;|
        <span class="font-semibold">Montant:</span> ${row.MONTANT !== null && row.MONTANT !== undefined ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(row.MONTANT) : ''}
    `;
}
// Fetch product details for selected document
async function fetchOrmProduct(documentNo) {
    if (!documentNo) return;
    const tableContainer = document.getElementById('orm-product-container');
    tableContainer.style.display = 'none';
    const url = new URL(API_CONFIG.getApiUrl('/fetchBCCBProduct'));
    url.searchParams.append('bccb', documentNo);
    url.searchParams.append('ad_org_id', '1000000');
    try {
        const response = await fetch(url);
        const data = await response.json();
        updateOrmProductTable(data);
        if (data.length > 0) {
            tableContainer.style.display = 'block';
            tableContainer.classList.add('fade-in');
        }
    } catch (error) {
        updateOrmProductTable([]);
    }
}
// Update ORM product table
function updateOrmProductTable(data) {
    const tableBody = document.getElementById('orm-product-table');
    tableBody.innerHTML = '';
    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500 dark:text-gray-400">Aucune donnée de produit disponible</td></tr>`;
        return;
    }
    data.forEach(row => {
        const remiseFormatted = row.REMISE ? Math.round(row.REMISE * 100) + '%' : '0%';
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-gray-50', 'dark:hover:bg-gray-700', 'transition-colors', 'duration-150');
        tr.innerHTML = `
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.PRODUCT || 'N/A'}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center">${row.QTY || 'N/A'}</td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 remise-badge">${remiseFormatted}</span></td>
            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white">${row.MARGE || 'N/A'}</td>
        `;
        tableBody.appendChild(tr);
    });
}
// Event listeners
// Search button
 document.getElementById('orm-search-btn').addEventListener('click', fetchOrmDocuments);
// Enter key triggers search
 document.getElementById('orm_search').addEventListener('keydown', function(e) { if (e.key === 'Enter') fetchOrmDocuments(); });
// Date pickers trigger search
 document.getElementById('start_date').addEventListener('change', fetchOrmDocuments);
 document.getElementById('end_date').addEventListener('change', fetchOrmDocuments);
</script>
</body>
</html>
