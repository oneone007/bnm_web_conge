<?php
session_start();
$page_identifier = 'Articles';
require_once 'check_permission.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="etatstock.css">
    <script src="theme.js" defer></script>
    <script src="api_config.js"></script>
    <style>
        .autocomplete-suggestions { position: absolute; z-index: 100; background: #fff; border: 1px solid #ccc; border-radius: 0.5rem; max-height: 200px; overflow-y: auto; width: 100%; }
        /* suggestions must overlay above modal */
        .autocomplete-suggestions { z-index: 9999; }
        .autocomplete-suggestion { padding: 0.5rem 1rem; cursor: pointer; }
        .autocomplete-suggestion:hover, .autocomplete-suggestion.active { background: #f3f4f6; }
        .dark .autocomplete-suggestions { background: #1f2937; color: #fff; border-color: #444; }
        .dark .autocomplete-suggestion:hover, .dark .autocomplete-suggestion.active { background: #374151; }
    </style>
    <style>
        .modal-bg { background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 90vw; max-height: 90vh; overflow: auto; padding: 2rem; position: relative; }
        .dark .modal-content { background: #1f2937; color: #fff; }
        .modal-close { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; cursor: pointer; }
    </style>
    <style>
        /* Ensure search filter container looks correct in dark mode */
        .dark .search-filter-container {
            background-color: #1f2937 !important; /* Tailwind gray-800 */
            border-color: #374151 !important; /* Tailwind gray-700 */
            color: #e5e7eb !important; /* Tailwind gray-200 */
        }
        .dark .search-filter-container label {
            color: #d1d5db !important; /* lighter label */
        }
        .dark .search-filter-container input,
        .dark .search-filter-container select,
        .dark .search-filter-container button {
            background-color: #111827 !important; /* darker input bg */
            color: #f9fafb !important; /* input text */
            border-color: #374151 !important;
        }
        .dark .search-filter-container .autocomplete-suggestions {
            background: #111827 !important;
            color: #f9fafb !important;
            border-color: #374151 !important;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
<div id="content" class="content flex-grow p-4 pb-16">
    <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center">Articles</h1>
    </div>
    <div id="searchWrapper">
    <!-- hide inputs on page; they will be shown inside the modal when opened -->
    <div class="search-filter-container bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6" style="display:none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <label for="productInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produit</label>
                <input id="productInput" type="text" autocomplete="off" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white" placeholder="Entrer le nom du produit...">
                <div id="productSuggestions" class="autocomplete-suggestions" style="display:none;"></div>
            </div>
            <div class="relative">
                <label for="magasinSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin</label>
                <select id="magasinSelect" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white">
                    <option value="">Choisir un magasin</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <!-- kept inside for keyboard focus when modal shown -->
            <button id="showEtatStockBtn_internal" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" style="display:none;">Articles</button>
        </div>
    </div>
    </div>
    <!-- Always-visible control to open the Articles popup -->
    <div class="mb-6">
        <button id="showEtatStockBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Articles</button>
    </div>
</div>
<!-- Modal Popup -->
<div id="modalBg" class="modal-bg" style="display:none;">
    <div class="modal-content relative">
        <span class="modal-close" id="modalCloseBtn">&times;</span>
        <div id="modalContentArea"></div>
    </div>
</div>
<script>

// Fetch magasins from API (like etatstock)
async function fetchMagasins() {
    const magasinSelect = document.getElementById('magasinSelect');
    magasinSelect.disabled = true;
    magasinSelect.innerHTML = '<option value="">Chargement...</option>';
    try {
        const resp = await fetch(API_CONFIG.getApiUrl('/fetch-magasins'));
        if (!resp.ok) throw new Error('Erreur API');
        let data = await resp.json();

        // If API returned an object like { MAGASIN: 'Temporarie' } or { MAGASIN: [...] }
        if (data && !Array.isArray(data) && typeof data === 'object') {
            // If object has string values, collect them
            const stringValues = [];
            const arrayValues = [];
            for (const key in data) {
                const v = data[key];
                if (typeof v === 'string') stringValues.push(v);
                else if (Array.isArray(v)) arrayValues.push(...v);
            }
            if (arrayValues.length > 0) data = arrayValues;
            else if (stringValues.length > 0) data = stringValues;
            else {
                // try to fallback to any nested array
                for (const key in data) {
                    if (Array.isArray(data[key])) { data = data[key]; break; }
                }
            }
        }

        console.log('Magasin API response parsed:', data);
        magasinSelect.innerHTML = '<option value="">Choisir un magasin</option>';

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(m => {
                const opt = document.createElement('option');
                if (typeof m === 'string') {
                    opt.value = m;
                    opt.textContent = m;
                } else if (typeof m === 'object' && m !== null) {
                    // If object has a direct MAGASIN-like property, use it
                    const keys = Object.keys(m);
                    let label = '';
                    let value = '';
                    // common field names to try
                    const tryKeys = ['MAGASIN','magasin','LABEL','label','NOM','nom','CODE','code','ID','id','name','NAME'];
                    for (const k of tryKeys) {
                        if (k in m && (typeof m[k] === 'string' || typeof m[k] === 'number')) {
                            label = String(m[k]);
                            value = String(m[k]);
                            break;
                        }
                    }
                    // fallback: find first string-valued property
                    if (!label) {
                        for (const k of keys) {
                            if (typeof m[k] === 'string' || typeof m[k] === 'number') { label = String(m[k]); value = String(m[k]); break; }
                        }
                    }
                    // final fallback: stringify
                    if (!label) label = JSON.stringify(m);
                    if (!value) value = label;
                    opt.value = value;
                    opt.textContent = label;
                }
                magasinSelect.appendChild(opt);
            });
            // After adding options, try to set default '1-Dépôt Principal'
            try {
                const opts = Array.from(magasinSelect.options).map(o => ({ value: o.value, text: o.textContent }));
                let found = opts.find(o => {
                    if (!o || !o.value && !o.text) return false;
                    const v = (o.value || o.text || '').toString();
                    const nv = v.normalize ? v.normalize('NFD').replace(/\p{Diacritic}/gu, '') : v;
                    const lower = nv.toLowerCase();
                    if (v === '1-Dépôt Principal' || v === '1-Depot Principal' || v === '1-Depôt Principal') return true;
                    if (lower.includes('depot') && lower.includes('principal')) return true;
                    return false;
                });
                if (found) {
                    magasinSelect.value = found.value;
                } else {
                    const def = document.createElement('option');
                    def.value = '1-Dépôt Principal';
                    def.textContent = '1-Dépôt Principal';
                    if (magasinSelect.children.length > 0) magasinSelect.insertBefore(def, magasinSelect.children[1]);
                    else magasinSelect.appendChild(def);
                    magasinSelect.value = def.value;
                }
            } catch (e) {
                console.warn('Could not set default magasin', e);
            }
            magasinSelect.disabled = false;
        } else {
            magasinSelect.innerHTML = '<option value="">Aucun magasin trouvé</option>';
            magasinSelect.disabled = true;
        }
    } catch (e) {
        console.error('Erreur chargement magasins:', e);
        magasinSelect.innerHTML = '<option value="">Erreur chargement</option>';
        magasinSelect.disabled = true;
    }
}
fetchMagasins();

// --- Product autocomplete (like etatstock) ---
const productInput = document.getElementById('productInput');
const productSuggestions = document.getElementById('productSuggestions');
let productList = [];
let suggestionPage = 1;
const SUGGESTIONS_PER_PAGE = 6;
let latestMatches = [];
let latestTotalPages = 1;

// Portal suggestions container to body so it overlays modal and is clickable
try {
    if (productSuggestions && productSuggestions.parentNode !== document.body) {
        document.body.appendChild(productSuggestions);
        productSuggestions.style.position = 'absolute';
        productSuggestions.style.zIndex = '9999';
        productSuggestions.style.display = 'none';
    }
} catch (e) { console.warn('portal suggestions failed', e); }

function positionSuggestions() {
    if (!productInput || !productSuggestions || productSuggestions.style.display === 'none') return;
    const r = productInput.getBoundingClientRect();
    const left = r.left + window.scrollX;
    // place suggestions slightly above the input if there's space, else below
    const belowTop = r.bottom + window.scrollY;
    productSuggestions.style.left = left + 'px';
    productSuggestions.style.top = belowTop + 'px';
    productSuggestions.style.width = Math.max(220, r.width) + 'px';
}
window.addEventListener('resize', positionSuggestions);
window.addEventListener('scroll', positionSuggestions, true);

async function fetchProductList() {
    try {
        const resp = await fetch(API_CONFIG.getApiUrl('/fetch-rotation-product-data'));
        const data = await resp.json();
        // Try to normalize to array of objects with NAME or name
        if (Array.isArray(data)) {
            productList = data;
        } else if (data && Array.isArray(data.products)) {
            productList = data.products;
        } else {
            productList = [];
        }
    } catch (e) { productList = []; }
}
fetchProductList();

productInput.addEventListener('input', function() {
    const val = this.value.trim().toLowerCase();
    if (!val || !productList.length) { productSuggestions.style.display = 'none'; return; }
    // Try to match on NAME, name, LABEL, label, PRODUCT, product
    const matches = productList.filter(p => {
        let name = p.NAME || p.name || p.LABEL || p.label || p.PRODUCT || p.product || '';
        return name.toLowerCase().includes(val);
    });
    // reset pagination on new input
    suggestionPage = 1;
    const totalPages = Math.max(1, Math.ceil(matches.length / SUGGESTIONS_PER_PAGE));
    // store for delegated handlers
    latestMatches = matches;
    latestTotalPages = totalPages;
    const start = (suggestionPage - 1) * SUGGESTIONS_PER_PAGE;
    const pageItems = matches.slice(start, start + SUGGESTIONS_PER_PAGE);
    const makeSuggestionsHtml = (items) => {
        let html = items.map((p, i) => {
            let name = p.NAME || p.name || p.LABEL || p.label || p.PRODUCT || p.product || '';
            return `<div class="autocomplete-suggestion${i===0?' active':''}" data-name="${name.replace(/"/g,'&quot;')}">${name}</div>`;
        }).join('');
        // pagination controls
        if (totalPages > 1) {
            html += `<div class="flex justify-between items-center p-2 border-t mt-2 bg-gray-50"><button id="suggPrev" class="px-2 py-1 text-sm">Prev</button><span class="text-sm">${suggestionPage}/${totalPages}</span><button id="suggNext" class="px-2 py-1 text-sm">Next</button></div>`;
        }
        return html;
    };
    if (!matches.length) { productSuggestions.style.display = 'none'; return; }
    productSuggestions.innerHTML = makeSuggestionsHtml(pageItems);
    productSuggestions.style.display = 'block';
    // wire pagination handlers if present
    setTimeout(() => {
        const prev = document.getElementById('suggPrev');
        const next = document.getElementById('suggNext');
        if (prev) prev.addEventListener('mousedown', (ev) => {
            ev.preventDefault(); // prevent focus changes
            if (suggestionPage > 1) {
                suggestionPage--;
                const start2 = (suggestionPage - 1) * SUGGESTIONS_PER_PAGE;
                const pageItems2 = matches.slice(start2, start2 + SUGGESTIONS_PER_PAGE);
                productSuggestions.innerHTML = makeSuggestionsHtml(pageItems2);
                positionSuggestions();
            }
        });
        if (next) next.addEventListener('mousedown', (ev) => {
            ev.preventDefault();
            if (suggestionPage < totalPages) {
                suggestionPage++;
                const start2 = (suggestionPage - 1) * SUGGESTIONS_PER_PAGE;
                const pageItems2 = matches.slice(start2, start2 + SUGGESTIONS_PER_PAGE);
                productSuggestions.innerHTML = makeSuggestionsHtml(pageItems2);
                positionSuggestions();
            }
        });
        positionSuggestions();
    }, 0);
});

// Delegated handler for suggestion selection and pagination controls
productSuggestions.addEventListener('mousedown', function(e) {
    const t = e.target;
    // Prev
    if (t && t.id === 'suggPrev') {
        e.preventDefault();
        if (suggestionPage > 1) {
            suggestionPage--;
            const start2 = (suggestionPage - 1) * SUGGESTIONS_PER_PAGE;
            const pageItems2 = latestMatches.slice(start2, start2 + SUGGESTIONS_PER_PAGE);
            // rebuild html for this page
            const html = pageItems2.map((p,i)=>{
                let name = p.NAME || p.name || p.LABEL || p.label || p.PRODUCT || p.product || '';
                return `<div class="autocomplete-suggestion${i===0?' active':''}" data-name="${name.replace(/"/g,'&quot;')}">${name}</div>`;
            }).join('') + `<div class="flex justify-between items-center p-2 border-t mt-2 bg-gray-50"><button id="suggPrev" class="px-2 py-1 text-sm">Prev</button><span class="text-sm">${suggestionPage}/${latestTotalPages}</span><button id="suggNext" class="px-2 py-1 text-sm">Next</button></div>`;
            productSuggestions.innerHTML = html;
            positionSuggestions();
        }
        return;
    }
    // Next
    if (t && t.id === 'suggNext') {
        e.preventDefault();
        if (suggestionPage < latestTotalPages) {
            suggestionPage++;
            const start2 = (suggestionPage - 1) * SUGGESTIONS_PER_PAGE;
            const pageItems2 = latestMatches.slice(start2, start2 + SUGGESTIONS_PER_PAGE);
            const html = pageItems2.map((p,i)=>{
                let name = p.NAME || p.name || p.LABEL || p.label || p.PRODUCT || p.product || '';
                return `<div class="autocomplete-suggestion${i===0?' active':''}" data-name="${name.replace(/"/g,'&quot;')}">${name}</div>`;
            }).join('') + `<div class="flex justify-between items-center p-2 border-t mt-2 bg-gray-50"><button id="suggPrev" class="px-2 py-1 text-sm">Prev</button><span class="text-sm">${suggestionPage}/${latestTotalPages}</span><button id="suggNext" class="px-2 py-1 text-sm">Next</button></div>`;
            productSuggestions.innerHTML = html;
            positionSuggestions();
        }
        return;
    }
    // suggestion clicked
    const sugg = t.closest && t.closest('.autocomplete-suggestion') ? (t.closest('.autocomplete-suggestion')) : (t.classList && t.classList.contains('autocomplete-suggestion') ? t : null);
    if (sugg) {
        productInput.value = sugg.dataset.name;
        productSuggestions.style.display = 'none';
        productInput.focus();
    }
});

productInput.addEventListener('blur', function(e) {
    // if focus moved into the suggestions box or its controls, don't hide
    const related = e.relatedTarget || document.activeElement;
    if (related && productSuggestions.contains(related)) return;
    setTimeout(() => { productSuggestions.style.display = 'none'; }, 150);
});

productInput.addEventListener('keydown', function(e) {
    const items = Array.from(productSuggestions.children);
    let idx = items.findIndex(x => x.classList.contains('active'));
    if (e.key === 'ArrowDown') {
        if (idx < items.length - 1) { if (idx >= 0) items[idx].classList.remove('active'); items[++idx].classList.add('active'); }
        e.preventDefault();
    } else if (e.key === 'ArrowUp') {
        if (idx > 0) { items[idx].classList.remove('active'); items[--idx].classList.add('active'); }
        e.preventDefault();
    } else if (e.key === 'Enter') {
        if (idx >= 0) { productInput.value = items[idx].dataset.name; productSuggestions.style.display = 'none'; e.preventDefault(); }
    }
});
const modalBg = document.getElementById('modalBg');
const modalContentArea = document.getElementById('modalContentArea');
document.getElementById('modalCloseBtn').onclick = () => { modalBg.style.display = 'none'; };
modalBg.onclick = e => { if (e.target === modalBg) modalBg.style.display = 'none'; };

// Make the entire search area show in the modal when requested
const searchWrapper = document.getElementById('searchWrapper');
let searchWrapperParent = null;
let searchShownInModal = false;

function openSearchPopup() {
    if (!searchWrapper) return;
    if (!searchShownInModal) {
        // store original parent and insert into modal content
        searchWrapperParent = searchWrapper.parentNode;
        modalContentArea.innerHTML = ''; // clear any previous content
    // show the search UI when moved into the modal
    const sf = searchWrapper.querySelector('.search-filter-container');
    if (sf) sf.style.display = 'block';
    // show internal submit button inside modal
    const internalBtn = searchWrapper.querySelector('#showEtatStockBtn_internal');
    if (internalBtn) internalBtn.style.display = '';
        modalContentArea.appendChild(searchWrapper);
        modalBg.style.display = 'flex';
        searchShownInModal = true;
    } else {
        // already moved; just show modal
        modalBg.style.display = 'flex';
    }
}

function closeSearchPopup() {
    if (!searchWrapper) return;
    if (searchShownInModal && searchWrapperParent) {
        // move it back
    // hide the search UI when returning to the page
    const sf = searchWrapper.querySelector('.search-filter-container');
    if (sf) sf.style.display = 'none';
    // hide internal submit button
    const internalBtn = searchWrapper.querySelector('#showEtatStockBtn_internal');
    if (internalBtn) internalBtn.style.display = 'none';
        // clear inputs and suggestions
        try {
            const prod = document.getElementById('productInput');
            const sugg = document.getElementById('productSuggestions');
            const mag = document.getElementById('magasinSelect');
            if (prod) prod.value = '';
            if (sugg) { sugg.innerHTML = ''; sugg.style.display = 'none'; }
            if (mag) mag.selectedIndex = 0;
            suggestionPage = 1;
        } catch (e) { console.warn('Error clearing search fields', e); }
        searchWrapperParent.insertBefore(searchWrapper, searchWrapperParent.firstChild);
        searchShownInModal = false;
    }
    modalBg.style.display = 'none';
}

// override modal close handlers to restore search UI
document.getElementById('modalCloseBtn').onclick = () => { closeSearchPopup(); };
modalBg.onclick = e => { if (e.target === modalBg) closeSearchPopup(); };

// Listen for parent window messages (used when embedded in iframe)
window.addEventListener('message', (ev) => {
    try {
        const data = ev.data;
        if (!data || typeof data !== 'object') return;
        if (data.type === 'open_articles_popup') {
            // open the modal search UI
            openSearchPopup();
            // show internal submit button if present
            const internalBtn = document.getElementById('showEtatStockBtn_internal');
            if (internalBtn) internalBtn.style.display = '';
            // reply to parent that we're ready
            ev.source.postMessage({ type: 'articles_ready' }, ev.origin || '*');
        }
    } catch (e) { /* ignore */ }
});

// Show Etat de Stock
// Fetch stock data from API using /fetch-stock-data with optional filters
async function fetchStockData(name = null, magasin = null) {
    try {
        const url = new URL(API_CONFIG.getApiUrl('/fetch-stock-data'));
        if (name) url.searchParams.append('name', name);
        if (magasin) url.searchParams.append('magasin', magasin);
        const resp = await fetch(url);
        if (!resp.ok) throw new Error('Erreur fetching stock');
        const data = await resp.json();
        // If response is an object with a list property, try to extract array
        if (data && !Array.isArray(data) && typeof data === 'object') {
            for (const k in data) {
                if (Array.isArray(data[k])) return data[k];
            }
            // if object contains string values only, return them as simple rows
            return [data];
        }
        return Array.isArray(data) ? data : [];
    } catch (e) {
        console.error('fetchStockData error', e);
        return [];
    }
}

// Show Etat de Stock (loads real data from /fetch-stock-data)
async function showEtatStock(product, magasin) {
    modalContentArea.innerHTML = `<h2 class="text-xl font-bold mb-2">ETAT DE STOCK</h2><div id="etatStockTableWrapper">Loading...</div><div id="detailsArea"></div>`;
    modalBg.style.display = 'flex';

    const rowsRaw = await fetchStockData(product || null, magasin || null);
    // Exclude any summary/total rows where FOURNISSEUR equals 'Total'
    const rows = Array.isArray(rowsRaw) ? rowsRaw.filter(r => {
        try {
            return !(r && r.FOURNISSEUR && String(r.FOURNISSEUR).toLowerCase() === 'total');
        } catch (e) { return true; }
    }) : [];
    const wrapper = document.getElementById('etatStockTableWrapper');
    if (!rows || rows.length === 0) {
        wrapper.innerHTML = '<p class="text-sm text-gray-600">No stock data found for given filters.</p>';
        return;
    }

    // Build table
    const table = document.createElement('table');
    table.className = 'min-w-full text-sm text-left mb-4';
    const thead = document.createElement('thead');
    thead.innerHTML = '<tr><th>Produit</th><th>Magasin</th><th>Quantité disponible</th><th>Quantité en stock</th><th>Quantité réservée</th><th>Actions</th></tr>';
    table.appendChild(thead);
    const tbody = document.createElement('tbody');

    rows.forEach(r => {
        const prod = r.NAME || r.name || r.PRODUCT || r.product || r.MAGASIN || r.MAGASIN_NAME || r.PRODUCT_NAME || '';
        const mag = r.MAGASIN || r.magasin || r.CODE_MAGASIN || r.mag || r.store || '';
        const qtyDispo = r.QTY_DISPO ?? r.QTY_AVAILABLE ?? r.qty_dispo ?? r.qty_available ?? r.QTY ?? r.qty ?? '';
        const qty = r.QTY ?? r.qty ?? r.TOTAL ?? r.total ?? '';
        const qtyReserved = r.QTY_RESERVED ?? r.qty_reserved ?? r.RESERVED ?? r.reserved ?? 0;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="border px-4 py-2">${prod}</td>
            <td class="border px-4 py-2">${mag}</td>
            <td class="border px-4 py-2">${qtyDispo}</td>
            <td class="border px-4 py-2">${qty}</td>
            <td class="border px-4 py-2">${qtyReserved}</td>
            <td class="border px-4 py-2">
                <button class="px-2 py-1 bg-green-600 text-white rounded showLotsBtn">Show Lots</button>
                <button class="px-2 py-1 bg-yellow-600 text-white rounded showReservedBtn">Show Reserved</button>
                <button class="px-2 py-1 bg-blue-600 text-white rounded showHistoryBtn">Show History</button>
            </td>
        `;
        // attach actions
        tr.querySelector('.showLotsBtn').addEventListener('click', () => showLots(prod, mag));
        tr.querySelector('.showReservedBtn').addEventListener('click', () => showReserved(r));
        tr.querySelector('.showHistoryBtn').addEventListener('click', () => showHistory(prod));
        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    wrapper.innerHTML = '';
    wrapper.appendChild(table);
}

async function showLots(product, magasin) {
    const container = document.getElementById('detailsArea');
    container.innerHTML = '<p>Loading product details...</p>';
    try {
        const url = new URL(API_CONFIG.getApiUrl('/fetch-product-details'));
        url.searchParams.append('product_name', product);
        if (magasin) url.searchParams.append('magasin', magasin);

        const resp = await fetch(url);
        if (!resp.ok) throw new Error('Failed to fetch product details');
        const data = await resp.json();

        // Normalize to array
        const rows = Array.isArray(data) ? data : (data && Array.isArray(data.rows) ? data.rows : (data ? [data] : []));

        if (!rows || rows.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-600">No product details found.</p>';
            return;
        }

        const formatNumber = (n) => {
            if (n === null || n === undefined || n === '') return '';
            const num = Number(n);
            if (isNaN(num)) return String(n);
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        const formatInteger = (n) => {
            if (n === null || n === undefined || n === '') return '';
            const num = Number(n);
            if (isNaN(num)) return String(n);
            return Math.round(num).toLocaleString('en-US', { maximumFractionDigits: 0 });
        };
        const formatDate = (s) => {
            if (!s) return '';
            try { return new Date(s).toLocaleDateString('fr-FR'); } catch(e) { return s; }
        };

    // Preferred order of columns similar to etatstock product details
    const preferred = ['PRODUCT_NAME','PRODUCT_LABEL','LOT','LOCATION','QTY','QTY_DISPO','QTY_RESERVED','P_ACHAT','P_REVIENT','P_VENTE','GUARANTEEDATE'];
    const srcCols = Object.keys(rows[0] || {});
    // Columns user requested to hide (case-insensitive)
    const hideCols = new Set(['LOT_ID','LABO','FOURNISSEUR','PRODUCT'].map(c => c.toUpperCase()));
    // Build final column list: prefer 'preferred' order but skip hidden columns, then append remaining srcCols (also skipping hidden)
    const cols = [];
    preferred.forEach(k => { if (srcCols.includes(k) && !hideCols.has(k.toUpperCase())) cols.push(k); });
    srcCols.forEach(k => { if (!cols.includes(k) && !hideCols.has(String(k).toUpperCase())) cols.push(k); });

        let html = '<h3 class="text-lg font-semibold mb-2">Product Details</h3>';
        html += '<div class="overflow-x-auto"><table class="min-w-full text-sm text-left mb-4">';
        html += '<thead><tr>';
        // mapping of column keys to short/friendly labels per user request
        const displayNames = {
            'QTY_DISPO': 'qty dis',
            'P_ACHAT': 'P_A',
            'BON_VENTE': 'Bo  n_V',
            'REMISE_AUTO': 'Remise_Aut',
            'REM_ACHAT': 'Remise_A',
            // additional mappings requested
            'QTY_RESERVED': 'qty res',
            'P_REVIENT': 'P_R',
            'P_VENTE': 'P_V',
            'GUARANTEEDATE': 'Guarantee Date',
            'BONUS_AUTO': 'Bonus_Aut',
            'BON_ACHAT': 'Bon_A',
            'REM_VENTE': 'Remise_V'
        };
        const prettyLabel = (k) => {
            const key = String(k).toUpperCase();
            if (displayNames[key]) return displayNames[key];
            // default: replace underscores and capitalize words
            return String(k).replace(/_/g, ' ').replace(/\b\w/g, ch => ch.toUpperCase());
        };
        cols.forEach(c => html += `<th class="border px-2 py-1">${prettyLabel(c)}</th>`);
        html += '</tr></thead><tbody>';

        rows.forEach(r => {
            html += '<tr>';
            cols.forEach(c => {
                let v = r[c];
                // friendly formatting for numeric/date
                if (typeof v === 'number' || (!isNaN(Number(v)) && v !== null && v !== '')) {
                    // choose numeric formatting for common numeric columns
                    if (['QTY','QTY_DISPO','QTY_RESERVED'].includes(c)) v = formatInteger(v);
                    else if (['P_ACHAT','P_REVIENT','P_VENTE'].includes(c)) v = formatNumber(v);
                }
                if (c === 'GUARANTEEDATE' || c.toLowerCase().includes('date')) v = formatDate(v);
                html += `<td class="border px-2 py-1">${v !== null && v !== undefined ? v : ''}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    } catch (e) {
        console.error('Error fetching product details:', e);
        container.innerHTML = '<p class="text-sm text-red-600">Error loading product details.</p>';
    }
}

// Show history (calls /history_articles endpoint)
async function showHistory(product) {
    const container = document.getElementById('detailsArea');
    container.innerHTML = '<p>Loading history...</p>';
    try {
        const url = new URL(API_CONFIG.getApiUrl('/history_articles'));
        url.searchParams.append('product_name', product);
        const resp = await fetch(url);
        if (!resp.ok) throw new Error('Failed to fetch history');
        const data = await resp.json();
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-600">No history found for this product.</p>';
            return;
        }

        // Build simple table from returned objects
        // Build columns in requested order and apply formatting
        // Desired order: TIERS, DOCUMENT, DOCUMENT_TYPE, DATE_INVOICE, DISCOUNT_PCT (Remise), PRIX_UNITAIRE, QTY_FACTURE, Lot
        const srcCols = Object.keys(data[0]);
        const pick = (names) => names.find(n => srcCols.includes(n) || srcCols.map(s=>s.toUpperCase()).includes(n.toUpperCase()));

        const colOrder = [];
        const mapFind = (candidates) => {
            for (const cand of candidates) {
                // try exact match first
                if (srcCols.includes(cand)) return cand;
                // try case-insensitive
                const found = srcCols.find(s => s.toUpperCase() === cand.toUpperCase());
                if (found) return found;
            }
            return null;
        };

        colOrder.push(mapFind(['TIERS','tiers','CLIENT','client','BPARTNER','bpartner']));
        colOrder.push(mapFind(['DOCUMENT','document','DOCUMENTNO','documentno','DOCNO']));
        colOrder.push(mapFind(['DOCUMENT_TYPE','document_type','DOCTYPE','doctype','DOCUMENTTYPE']));
        colOrder.push(mapFind(['DATE_INVOICE','date_invoice','DATEINVOCE','DATE','dateinvoce','DATEINVOICE','DATEINVOICES']));
        // DISCOUNT_PCT will be shown as Remise
        const discountCol = mapFind(['DISCOUNT_PCT','discount_pct','DISCOUNT','discount','REMise','REM']);
        colOrder.push(discountCol);
        colOrder.push(mapFind(['PRIX_UNITAIRE','prix_unitaire','PRICEENTERED','priceentered','PRIX']));
        colOrder.push(mapFind(['QTY_FACTURE','qty_facture','QTYINVOICED','qtyinvoiced','QTY_FACT','QTY']));
        colOrder.push(mapFind(['Lot','LOT','lot','ATTRIBUTESETINSTANCE_ID','M_ATTRIBUTESETINSTANCE_ID','lot_no']));

        // append any other columns that weren't included yet
        srcCols.forEach(c => { if (!colOrder.includes(c)) colOrder.push(c); });

        // helper to format dates as '21 Oct 2025'
        const formatSimpleDate = (s) => {
            if (!s) return '';
            try {
                const d = new Date(s);
                if (isNaN(d.getTime())) return s;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return `${String(d.getDate()).padStart(2,'0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
            } catch (e) { return s; }
        };

        const formatNumberSimple = (v) => {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (isNaN(n)) return v;
            return n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        };

        // Pagination: 20 rows per page
        const pageSize = 15;
        let currentPage = 1;
        const totalRows = data.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

        const prettyHeader = (c) => {
            if (!c) return '';
            if (discountCol && c === discountCol) return 'Remise';
            return String(c).replace(/_/g,' ').replace(/\b\w/g, ch => ch.toUpperCase());
        };

        const renderPage = (page) => {
            const start = (page - 1) * pageSize;
            const end = Math.min(start + pageSize, totalRows);
            let html = '<h3 class="text-lg font-semibold mb-2">History</h3>';
            html += `<div class="mb-2 text-sm text-gray-600">Showing ${start+1} - ${end} of ${totalRows}</div>`;
            html += '<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr>';
            colOrder.forEach(c => { if (c) html += `<th class="border px-2 py-1">${prettyHeader(c)}</th>`; });
            html += '</tr></thead><tbody>';

            for (let i = start; i < end; i++) {
                const row = data[i];
                html += '<tr>';
                colOrder.forEach(c => {
                    if (!c) return;
                    let v = row[c];
                    const upper = String(c).toUpperCase();
                    if (upper.includes('DATE') || (typeof v === 'string' && /\d{4}-\d{2}-\d{2}/.test(v))) v = formatSimpleDate(v);
                    if (discountCol && c === discountCol) {
                        const num = Number(v);
                        if (!isNaN(num)) v = `${num}%`; else if (v === null || v === undefined || v === '') v = '';
                    }
                    if (upper === 'PRIX_UNITAIRE' || upper === 'PRICEENTERED' || upper === 'PRIX' || upper.includes('PRIX')) v = formatNumberSimple(v);
                    if (upper === 'QTY_FACTURE' || upper === 'QTYINVOICED' || upper === 'QTY_FACT' || upper === 'QTY') v = formatNumberSimple(v);
                    html += `<td class="border px-2 py-1">${v !== null && v !== undefined ? v : ''}</td>`;
                });
                html += '</tr>';
            }
            html += '</tbody></table></div>';

            // pagination controls (First, Prev, page numbers, Next, Last)
            html += '<div class="mt-3 flex items-center gap-2">';
            html += `<button id="histFirst" class="px-2 py-1 border rounded ${page<=1? 'opacity-50 cursor-not-allowed' : ''}">First</button>`;
            html += `<button id="histPrev" class="px-2 py-1 border rounded ${page<=1? 'opacity-50 cursor-not-allowed' : ''}">Prev</button>`;
            // page numbers (compact)
            const pageBlockStart = Math.max(1, page - 3);
            const pageBlockEnd = Math.min(totalPages, page + 3);
            for (let p = pageBlockStart; p <= pageBlockEnd; p++) {
                html += `<button data-p="${p}" class="pageBtn px-2 py-1 border rounded ${p===page? 'bg-blue-600 text-white' : ''}">${p}</button>`;
            }
            html += `<button id="histNext" class="px-2 py-1 border rounded ${page>=totalPages? 'opacity-50 cursor-not-allowed' : ''}">Next</button>`;
            html += `<button id="histLast" class="px-2 py-1 border rounded ${page>=totalPages? 'opacity-50 cursor-not-allowed' : ''}">Last</button>`;
            html += '</div>';

            container.innerHTML = html;

            // wire events
            const prev = document.getElementById('histPrev');
            const next = document.getElementById('histNext');
            const firstBtn = document.getElementById('histFirst');
            const lastBtn = document.getElementById('histLast');
            if (prev) prev.onclick = () => { if (currentPage > 1) { currentPage--; renderPage(currentPage); } };
            if (next) next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderPage(currentPage); } };
            if (firstBtn) firstBtn.onclick = () => { if (currentPage !== 1) { currentPage = 1; renderPage(1); } };
            if (lastBtn) lastBtn.onclick = () => { if (currentPage !== totalPages) { currentPage = totalPages; renderPage(totalPages); } };
            Array.from(container.querySelectorAll('.pageBtn')).forEach(b => {
                b.onclick = (e) => { const p = Number(b.getAttribute('data-p')); if (!isNaN(p)) { currentPage = p; renderPage(currentPage); } };
            });
        };

        // initial render
        renderPage(currentPage);
    } catch (e) {
        console.error('Error fetching history:', e);
        container.innerHTML = '<p class="text-sm text-red-600">Error loading history.</p>';
    }
}

async function showReserved(row) {
    const container = document.getElementById('detailsArea');
    container.innerHTML = '<p>Loading reserved orders...</p>';
    try {
        // Try to find an m_product_id in the row
        let m_product_id = null;
        const candidates = ['M_PRODUCT_ID','m_product_id','M_PRODUCTID','m_productid','M_PRODUCT','productid','product_id'];
        for (const k of Object.keys(row)) {
            if (!m_product_id && candidates.includes(k) && row[k]) m_product_id = row[k];
        }

        const url = new URL(API_CONFIG.getApiUrl('/reserved_reserved_fromorder'));
        if (m_product_id) url.searchParams.append('m_product_id', m_product_id);
        else {
            // fallback to product name if available
            const prodName = row.NAME || row.name || row.PRODUCT || row.product || '';
            if (!prodName) {
                container.innerHTML = '<p class="text-sm text-gray-600">No product identifier to fetch reserved orders.</p>';
                return;
            }
            url.searchParams.append('product_name', prodName);
        }

        const resp = await fetch(url);
        if (!resp.ok) {
            const err = await resp.json().catch(() => ({}));
            container.innerHTML = `<p class="text-sm text-red-600">Failed to load reserved orders: ${err.error || resp.statusText}</p>`;
            return;
        }

        const data = await resp.json();
        // Reuse renderReservedResults-like behavior but render into detailsArea with pagination (15 per page)
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-600">No reserved orders found for this product.</p>';
            return;
        }

        const srcCols = Object.keys(data[0]);
        const displayCols = srcCols.filter(c => c.toUpperCase() !== 'DOCACTION' && c.toUpperCase() !== 'DOCSTATUS');
        displayCols.push('DocumentStatus');
        const clientIdx = displayCols.findIndex(c => String(c).toLowerCase() === 'client_name' || String(c).toLowerCase() === 'client');
        if (clientIdx > -1) { const clientCol = displayCols.splice(clientIdx,1)[0]; displayCols.unshift(clientCol); }

        function formatReservedDate(dateString) {
            if (!dateString) return '';
            try {
                const d = new Date(dateString);
                if (isNaN(d.getTime())) return dateString;
                const weekdaysFr = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
                const monthsShort = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
                const wd = (weekdaysFr[d.getDay()] || '').substring(0,4);
                const day = String(d.getDate()).padStart(2, '0');
                const mon = monthsShort[d.getMonth()];
                const year = d.getFullYear();
                return `${wd} ${day} ${mon} ${year}`;
            } catch (e) { return dateString; }
        }

        // Pagination variables
        const pageSize = 15;
        let currentPage = 1;
        const totalRows = data.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

        const renderReservedPage = (page) => {
            const start = (page - 1) * pageSize;
            const end = Math.min(start + pageSize, totalRows);
            let html = '<h3 class="text-md font-semibold mb-2">Reserved Orders</h3>';
            html += `<div class="mb-2 text-sm text-gray-600">Showing ${start+1} - ${end} of ${totalRows}</div>`;
            html += '<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr>';
            displayCols.forEach(c => {
                const label = (String(c).toLowerCase() === 'client_name' || String(c).toLowerCase() === 'client') ? 'Client' : c;
                html += `<th class="border px-2 py-1">${label}</th>`;
            });
            html += '</tr></thead><tbody>';

            for (let i = start; i < end; i++) {
                const r = data[i];
                const docAction = (r.DOCACTION || r.docaction || '').toString();
                const docStatus = (r.DOCSTATUS || r.docstatus || '').toString();
                let documentStatus = 'unknown';
                const checkVal = (s) => s && ['PR', 'IP'].includes(s.toString().toUpperCase());
                if (checkVal(docAction) || checkVal(docStatus)) documentStatus = 'reserved';
                else if ((docAction && ['CO','CL'].includes(docAction.toString().toUpperCase())) || (docStatus && ['CO','CL'].includes(docStatus.toString().toUpperCase()))) documentStatus = 'Achevé';

                html += '<tr class="hover:bg-gray-100 dark:hover:bg-gray-600">';
                for (const c of displayCols) {
                    if (c === 'DocumentStatus') { html += `<td class="border px-2 py-1">${documentStatus}</td>`; continue; }
                    let v = r[c];
                    if (v === null || v === undefined) v = '';
                    const colNameUpper = String(c).toUpperCase();
                    const isDateCol = colNameUpper.includes('DATE') || colNameUpper.includes('DATEORDER') || colNameUpper.includes('DATE_ORDER') || colNameUpper.includes('DATEORDERED');
                    const looksLikeDate = typeof v === 'string' && /\b\d{1,2}\s+[A-Za-z]{3,}\s+\d{4}\b/.test(v) || typeof v === 'string' && v.includes('GMT');
                    if (isDateCol || looksLikeDate) v = formatReservedDate(v);
                    html += `<td class="border px-2 py-1">${v}</td>`;
                }
                html += '</tr>';
            }
            html += '</tbody></table></div>';

            // pagination controls
            html += '<div class="mt-3 flex items-center gap-2">';
            html += `<button id="resFirst" class="px-2 py-1 border rounded ${page<=1? 'opacity-50 cursor-not-allowed' : ''}">First</button>`;
            html += `<button id="resPrev" class="px-2 py-1 border rounded ${page<=1? 'opacity-50 cursor-not-allowed' : ''}">Prev</button>`;
            const pageBlockStart = Math.max(1, page - 3);
            const pageBlockEnd = Math.min(totalPages, page + 3);
            for (let p = pageBlockStart; p <= pageBlockEnd; p++) {
                html += `<button data-p="${p}" class="resPageBtn px-2 py-1 border rounded ${p===page? 'bg-blue-600 text-white' : ''}">${p}</button>`;
            }
            html += `<button id="resNext" class="px-2 py-1 border rounded ${page>=totalPages? 'opacity-50 cursor-not-allowed' : ''}">Next</button>`;
            html += `<button id="resLast" class="px-2 py-1 border rounded ${page>=totalPages? 'opacity-50 cursor-not-allowed' : ''}">Last</button>`;
            html += '</div>';

            container.innerHTML = html;

            // wire events
            const prev = document.getElementById('resPrev');
            const next = document.getElementById('resNext');
            const firstBtn = document.getElementById('resFirst');
            const lastBtn = document.getElementById('resLast');
            if (prev) prev.onclick = () => { if (currentPage > 1) { currentPage--; renderReservedPage(currentPage); } };
            if (next) next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderReservedPage(currentPage); } };
            if (firstBtn) firstBtn.onclick = () => { if (currentPage !== 1) { currentPage = 1; renderReservedPage(1); } };
            if (lastBtn) lastBtn.onclick = () => { if (currentPage !== totalPages) { currentPage = totalPages; renderReservedPage(totalPages); } };
            Array.from(container.querySelectorAll('.resPageBtn')).forEach(b => {
                b.onclick = (e) => { const p = Number(b.getAttribute('data-p')); if (!isNaN(p)) { currentPage = p; renderReservedPage(currentPage); } };
            });
        };

        renderReservedPage(currentPage);
    } catch (e) {
        console.error('Error fetching reserved orders:', e);
        const container = document.getElementById('detailsArea');
        container.innerHTML = '<p class="text-sm text-red-600">Error loading reserved orders.</p>';
    }
}

// Shared handler used by both the page button and the internal modal button
async function handleShowEtatClick(e) {
    // If search UI is not in modal yet, open it there for a focused popup experience
    if (!searchShownInModal) {
        openSearchPopup();
        return;
    }
    // otherwise act as before: validate inputs and show results
    const product = document.getElementById('productInput').value.trim();
    const magasin = document.getElementById('magasinSelect').value;
    if (!product || !magasin) { alert('Entrer un produit et choisir un magasin.'); return; }
    await showEtatStock(product, magasin);
}

// wire both the page button and internal modal button to the shared handler
const pageBtn = document.getElementById('showEtatStockBtn');
if (pageBtn) pageBtn.onclick = handleShowEtatClick;
const internalBtn = document.getElementById('showEtatStockBtn_internal');
if (internalBtn) internalBtn.onclick = handleShowEtatClick;
</script>
</body>
</html>
