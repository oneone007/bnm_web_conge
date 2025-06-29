
<?php
session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for certain roles
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Vente'])) {
    header("Location: Acess_Denied");    
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État Fournisseur - BNM</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="etatstock.css">
    <script src="theme.js" defer></script>
    
    <style>
        .search-container {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-container input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: white;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .dark .search-container input {
            background-color: #374151;
            border-color: #4b5563;
            color: white;
        }
        
        .search-container input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .dark .dropdown {
            background-color: #374151;
            border-color: #4b5563;
        }
        
        .dropdown-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .dark .dropdown-item {
            border-bottom-color: #4b5563;
        }
        
        .dropdown-item:hover {
            background-color: #f3f4f6;
        }
        
        .dark .dropdown-item:hover {
            background-color: #4b5563;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .dark .stat-card {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        .dark .stat-card:hover {
            background-color: #374151;
        }
        
        .Btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            background-color: #217346;
        }
        
        .svgWrapper {
            width: 100%;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .excelIcon {
            width: 24px;
            height: 24px;
        }
        
        .text {
            position: absolute;
            right: 0;
            width: 0;
            opacity: 0;
            color: white;
            font-size: 1.1em;
            font-weight: 600;
            transition: 0.3s;
            white-space: nowrap;
        }
        
        .Btn:hover {
            width: 140px;
            border-radius: 40px;
        }
        
        .Btn:hover .svgWrapper {
            width: 30%;
            padding-left: 20px;
        }
        
        .Btn:hover .text {
            opacity: 1;
            width: 70%;
            padding-right: 10px;
        }
        
        .Btn:active {
            transform: translate(2px, 2px);
        }
        
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .dark .table-container {
            border-color: #374151;
        }
        
        .table-container table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }
        
        thead {
            position: sticky;
            top: 0;
            background-color: #f3f4f6;
            z-index: 10;
        }
        
        .dark thead {
            background-color: #374151;
        }
        
        th, td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .dark th, .dark td {
            border-color: #4b5563;
        }
            border-radius: 10px;
            height: 38px;
            line-height: 34px;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #667eea;
        }
        
        #resultsTable {
            border-radius: 10px;
            overflow: hidden;
        }
        
        #resultsTable thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }
        
        #resultsTable tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        #resultsTable tbody tr:hover {
            background-color: #e3f2fd;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner-border {
            color: #667eea;
        }
        
        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .export-buttons {
            margin-bottom: 1rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stats-label {
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        /* Custom CSS for date inputs in dark mode */
        .dark input[type="date"] {
            color-scheme: dark;
            background-color: rgb(55 65 81); /* gray-700 */
            border-color: rgb(75 85 99); /* gray-600 */
            color: white;
        }
        
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-text {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-month-field {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-day-field {
            color: white;
        }
        
        .dark input[type="date"]::-webkit-datetime-edit-year-field {
            color: white;
        }

        /* Dark mode for select elements */
        .dark select {
            color-scheme: dark;
            background-color: rgb(55 65 81); /* gray-700 */
            border-color: rgb(75 85 99); /* gray-600 */
            color: white;
        }

        /* Dark mode for statistics cards */
        .dark .stats-card {
            background-color: #1f2937;
            border-color: #374151;
        }

        .dark .stats-card:hover {
            background-color: #374151;
        }

        /* Dark mode colors for statistics numbers */
        .dark .stat-card .text-blue-600 {
            color: rgb(147 197 253) !important; /* blue-300 */
        }

        .dark .stat-card .text-green-600 {
            color: rgb(134 239 172) !important; /* green-300 */
        }

        .dark .stat-card .text-red-600 {
            color: rgb(252 165 165) !important; /* red-300 */
        }

        /* Dark mode for loading spinner */
        .dark .spinner-border {
            color: #3b82f6;
        }

        /* Dark mode for alert messages */
        .dark .bg-green-100 {
            background-color: rgba(20, 83, 45, 0.3) !important;
        }

        .dark .bg-red-100 {
            background-color: rgba(127, 29, 29, 0.3) !important;
        }

        .dark .bg-yellow-100 {
            background-color: rgba(146, 124, 47, 0.3) !important;
        }

        .dark .bg-blue-100 {
            background-color: rgba(30, 58, 138, 0.3) !important;
        }

        .dark .text-green-700 {
            color: rgb(187 247 208) !important;
        }

        .dark .text-red-700 {
            color: rgb(254 202 202) !important;
        }

        .dark .text-yellow-700 {
            color: rgb(253 230 138) !important;
        }

        .dark .text-blue-700 {
            color: rgb(196 181 253) !important;
        }

        .dark .border-green-500 {
            border-color: rgb(34 197 94) !important;
        }

        .dark .border-red-500 {
            border-color: rgb(239 68 68) !important;
        }

        .dark .border-yellow-500 {
            border-color: rgb(234 179 8) !important;
        }

        .dark .border-blue-500 {
            border-color: rgb(59 130 246) !important;
        }

        /* Force dark mode for filter section */
        .dark .filter-section {
            background-color: #1f2937 !important;
        }

        .dark .filter-section h2 {
            color: white !important;
        }

        /* Ensure form elements are properly styled in dark mode */
        .dark .filter-section input,
        .dark .filter-section select {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: white !important;
        }

        .dark .filter-section label {
            color: white !important;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4 pb-16">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">
                État Fournisseur
            </h1>
        </div>

        <!-- Filter Section -->
        <div class="bg-white dark:bg-gray-800 filter-section p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Filtres de recherche
            </h2>
            <form id="filterForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="startDate" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                            Date début *
                        </label>
                        <input type="date" id="startDate" name="startDate" required
                               class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="endDate" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                            Date fin *
                        </label>
                        <input type="date" id="endDate" name="endDate" required
                               class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="fournisseur" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            <i class="fas fa-truck text-purple-600 mr-1"></i>
                            Fournisseur
                        </label>
                        <div class="search-container relative">
                            <input type="text" id="fournisseurSearch" placeholder="Rechercher un fournisseur..." 
                                   class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <div id="fournisseur-dropdown" class="dropdown"></div>
                        </div>
                        <input type="hidden" id="fournisseur" name="fournisseur" value="">
                    </div>
                    <div>
                        <label for="isPaid" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            <i class="fas fa-credit-card text-green-600 mr-1"></i>
                            État paiement
                        </label>
                        <select id="isPaid" name="isPaid"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Tous</option>
                            <option value="Y">Payé</option>
                            <option value="N">Non payé</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-center space-x-4 mt-6">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Rechercher
                    </button>
                    <button type="button" id="resetBtn" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors flex items-center">
                        <i class="fas fa-undo mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div id="statsContainer" class="stats-grid" style="display: none;">
            <!-- Stats will be populated here -->
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="flex flex-col items-center justify-center py-8" style="display: none;">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Chargement des données...</p>
        </div>

        <!-- Export Buttons -->
        <div id="exportContainer" class="flex justify-center mb-4" style="display: none;">
            <button class="Btn" id="exportExcel">
                <div class="svgWrapper">
                    <img src="assets/excel.png" alt="Excel Icon" class="excelIcon" />
                    <div class="text">&nbsp;Excel</div>
                </div>
            </button>
            <button class="Btn ml-4" id="exportPDF" style="background-color: #dc3545;">
                <div class="svgWrapper">
                    <i class="fas fa-file-pdf text-white"></i>
                    <div class="text">&nbsp;PDF</div>
                </div>
            </button>
        </div>

        <!-- Results Table -->
        <div id="resultsContainer" class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800" style="display: none;">
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center p-4">ÉTAT FOURNISSEUR</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">
                    Résultats de la recherche: <span id="recordCount" class="font-semibold">0</span> enregistrements
                </p>

                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                                Date
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-file-invoice text-purple-600 mr-1"></i>
                                N° Facture
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-receipt text-orange-600 mr-1"></i>
                                N° BL
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-calendar-check text-blue-600 mr-1"></i>
                                Date Versement
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                Montant
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-hand-holding-usd text-green-600 mr-1"></i>
                                Total Versé
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-coins text-green-600 mr-1"></i>
                                Mt Versement
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-1"></i>
                                Reste
                                <div class="resizer"></div>
                            </th>
                            <th class="resizable border border-gray-300 px-4 py-2 dark:border-gray-600">
                                <i class="fas fa-money-check text-indigo-600 mr-1"></i>
                                N° Chèque
                                <div class="resizer"></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody" class="dark:bg-gray-800">
                        <!-- Results will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer" class="mt-4"></div>
    </div>

    <!-- Scripts -->
    <!-- jsPDF and jsPDF-AutoTable for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <!-- SheetJS for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <script>
        // Initialize resizer functionality for all resizable table headers
        function initializeResizers() {
            document.querySelectorAll("th.resizable").forEach(function (th) {
                const resizer = th.querySelector(".resizer");
                
                if (resizer && !resizer.hasListener) {
                    resizer.hasListener = true;
                    resizer.addEventListener("mousedown", function initResize(e) {
                        e.preventDefault();
                        window.addEventListener("mousemove", resizeColumn);
                        window.addEventListener("mouseup", stopResize);

                        function resizeColumn(e) {
                            const newWidth = e.clientX - th.getBoundingClientRect().left;
                            th.style.width = newWidth + "px";
                        }

                        function stopResize() {
                            window.removeEventListener("mousemove", resizeColumn);
                            window.removeEventListener("mouseup", stopResize);
                        }
                    });
                }
            });
        }
        
        // Initialize resizers when page loads
        document.addEventListener("DOMContentLoaded", initializeResizers);
    </script>

    <script>
        let currentData = [];
        const API_BASE_URL = 'http://localhost:5000';

        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates (current month)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
            document.getElementById('endDate').value = lastDay.toISOString().split('T')[0];

            // Load fournisseurs
            loadFournisseurs();
            
            // Setup fournisseur search
            setupFournisseurSearch();

            // Form submission
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                searchData();
            });

            // Reset button
            document.getElementById('resetBtn').addEventListener('click', function() {
                document.getElementById('filterForm').reset();
                document.getElementById('fournisseur').value = '';
                document.getElementById('fournisseurSearch').value = '';
                document.getElementById('fournisseur-dropdown').style.display = 'none';
                document.getElementById('resultsContainer').style.display = 'none';
                document.getElementById('statsContainer').style.display = 'none';
                document.getElementById('exportContainer').style.display = 'none';
                showAlert('info', 'Formulaire réinitialisé');
            });

            // Export buttons
            document.getElementById('exportExcel').addEventListener('click', function() {
                exportToExcel();
            });

            document.getElementById('exportPDF').addEventListener('click', function() {
                exportToPDF();
            });
        });

        let allFournisseurs = [];

        function loadFournisseurs() {
            fetch(API_BASE_URL + '/listfournisseur_etat')
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        allFournisseurs = data;
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erreur lors du chargement des fournisseurs');
                });
        }

        function setupFournisseurSearch() {
            const searchInput = document.getElementById('fournisseurSearch');
            const dropdown = document.getElementById('fournisseur-dropdown');
            const hiddenInput = document.getElementById('fournisseur');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                if (searchTerm === '') {
                    dropdown.style.display = 'none';
                    hiddenInput.value = '';
                    return;
                }

                const filteredFournisseurs = allFournisseurs.filter(fournisseur => 
                    fournisseur.name.toLowerCase().includes(searchTerm)
                );

                if (filteredFournisseurs.length > 0) {
                    dropdown.innerHTML = '';
                    
                    // Add "Tous les fournisseurs" option
                    const allOption = document.createElement('div');
                    allOption.className = 'dropdown-item';
                    allOption.textContent = 'Tous les fournisseurs';
                    allOption.addEventListener('click', function() {
                        searchInput.value = 'Tous les fournisseurs';
                        hiddenInput.value = '';
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(allOption);

                    filteredFournisseurs.forEach(fournisseur => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        item.textContent = fournisseur.name;
                        item.addEventListener('click', function() {
                            searchInput.value = fournisseur.name;
                            hiddenInput.value = fournisseur.id;
                            dropdown.style.display = 'none';
                        });
                        dropdown.appendChild(item);
                    });
                    
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            });

            searchInput.addEventListener('focus', function() {
                if (this.value === '') {
                    // Show all fournisseurs when focused with empty input
                    dropdown.innerHTML = '';
                    
                    // Add "Tous les fournisseurs" option
                    const allOption = document.createElement('div');
                    allOption.className = 'dropdown-item';
                    allOption.textContent = 'Tous les fournisseurs';
                    allOption.addEventListener('click', function() {
                        searchInput.value = 'Tous les fournisseurs';
                        hiddenInput.value = '';
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(allOption);

                    allFournisseurs.forEach(fournisseur => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        item.textContent = fournisseur.name;
                        item.addEventListener('click', function() {
                            searchInput.value = fournisseur.name;
                            hiddenInput.value = fournisseur.id;
                            dropdown.style.display = 'none';
                        });
                        dropdown.appendChild(item);
                    });
                    
                    dropdown.style.display = 'block';
                }
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }

        function searchData() {
            const formData = {
                date1: document.getElementById('startDate').value.split('-').reverse().join('/'),
                date2: document.getElementById('endDate').value.split('-').reverse().join('/'),
                c_bpartner_id: document.getElementById('fournisseur').value || null,
                ispaid: document.getElementById('isPaid').value || null
            };

            // Show loading
            document.getElementById('loadingSpinner').style.display = 'flex';
            document.getElementById('resultsContainer').style.display = 'none';
            document.getElementById('statsContainer').style.display = 'none';
            document.getElementById('exportContainer').style.display = 'none';

            const params = new URLSearchParams();
            Object.keys(formData).forEach(key => {
                if (formData[key] !== null) {
                    params.append(key, formData[key]);
                }
            });

            fetch(API_BASE_URL + '/etat_f?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loadingSpinner').style.display = 'none';
                    
                    if (data.error) {
                        showAlert('danger', 'Erreur: ' + data.error);
                        return;
                    }

                    if (!Array.isArray(data) || data.length === 0) {
                        showAlert('warning', 'Aucun résultat trouvé pour les critères sélectionnés');
                        return;
                    }

                    currentData = data;
                    populateTable(data);
                    showStatistics(data);
                    document.getElementById('resultsContainer').style.display = 'block';
                    document.getElementById('statsContainer').style.display = 'grid';
                    document.getElementById('exportContainer').style.display = 'flex';
                    showAlert('success', `${data.length} enregistrement(s) trouvé(s)`);
                })
                .catch(error => {
                    document.getElementById('loadingSpinner').style.display = 'none';
                    showAlert('danger', 'Erreur lors de la récupération des données: ' + error.message);
                });
        }

        function populateTable(data) {
            const tbody = document.getElementById('resultsBody');
            tbody.innerHTML = '';

            data.forEach(function(row) {
                const montant = parseFloat(row.GRANDTOTAL) || 0;
                const totalVerse = parseFloat(row.VERSE_FACT) || 0;
                const reste = montant - totalVerse;
                
                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
                tr.innerHTML = `
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600">${formatDate(row.DATEINVOICED)}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600">${row.DOCUMENTNO || ''}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600">${row.NBL || ''}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600">${formatDate(row.DATEVERSEMENT)}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">${formatCurrency(row.GRANDTOTAL)}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">${formatCurrency(row.VERSE_FACT)}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">${formatCurrency(row.VERSE_CHEQUE)}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right ${reste > 0 ? 'text-red-600 font-semibold' : 'text-green-600'}">${formatCurrency(reste)}</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-600">${row.CHEQUE || ''}</td>
                `;
                tbody.appendChild(tr);
            });

            // Update record count
            document.getElementById('recordCount').textContent = data.length;
        }

        function showStatistics(data) {
            const totalRecords = data.length;
            
            // Get unique BP totals (since they're the same for each business partner)
            const uniqueBpChiffre = data.length > 0 ? (parseFloat(data[0].BP_CHIFFRE) || 0) : 0;
            const uniqueVerseTot = data.length > 0 ? (parseFloat(data[0].VERSE_TOT) || 0) : 0;
            
            // Calculate total reste from all records
            const totalReste = data.reduce((sum, row) => {
                const montant = parseFloat(row.GRANDTOTAL) || 0;
                const totalVerse = parseFloat(row.VERSE_FACT) || 0;
                const reste = montant - totalVerse;
                return sum + reste;
            }, 0);

            const statsHtml = `
                <div class="stat-card">
                    <div class="flex items-center justify-center mb-2">
                        <i class="fas fa-file-invoice text-blue-600 dark:text-blue-400 text-xl mr-2"></i>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">${totalRecords}</div>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Factures</div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-center mb-2">
                        <i class="fas fa-money-bill-wave text-green-600 dark:text-green-400 text-xl mr-2"></i>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">${formatCurrency(uniqueBpChiffre)}</div>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Montant</div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-center mb-2">
                        <i class="fas fa-hand-holding-usd text-green-600 dark:text-green-400 text-xl mr-2"></i>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">${formatCurrency(uniqueVerseTot)}</div>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Versé</div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-center mb-2">
                        <i class="fas fa-exclamation-triangle ${totalReste > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'} text-xl mr-2"></i>
                        <div class="text-2xl font-bold ${totalReste > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'}">${formatCurrency(totalReste)}</div>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Reste</div>
                </div>
            `;

            document.getElementById('statsContainer').innerHTML = statsHtml;
        }

        function exportToExcel() {
            if (currentData.length === 0) {
                showAlert('warning', 'Aucune donnée à exporter');
                return;
            }

            // Prepare data for export
            const exportData = currentData.map(row => {
                const montant = parseFloat(row.GRANDTOTAL) || 0;
                const totalVerse = parseFloat(row.VERSE_FACT) || 0;
                const reste = montant - totalVerse;
                
                return {
                    'Date': formatDate(row.DATEINVOICED),
                    'N° Facture': row.DOCUMENTNO || '',
                    'N° BL': row.NBL || '',
                    'Date Versement': formatDate(row.DATEVERSEMENT),
                    'Montant': row.GRANDTOTAL || 0,
                    'Total Versé': row.VERSE_FACT || 0,
                    'Mt Versement': row.VERSE_CHEQUE || 0,
                    'Reste': reste,
                    'N° Chèque': row.CHEQUE || ''
                };
            });

            // Create workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(exportData);

            // Auto-width columns
            const colWidths = [];
            Object.keys(exportData[0]).forEach(key => {
                colWidths.push({wch: Math.max(key.length, 15)});
            });
            ws['!cols'] = colWidths;

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, "État Fournisseur");

            // Generate filename
            const date = new Date();
            const filename = `etat_fournisseur_${date.getFullYear()}_${(date.getMonth()+1).toString().padStart(2,'0')}_${date.getDate().toString().padStart(2,'0')}.xlsx`;

            // Save file
            XLSX.writeFile(wb, filename);
            showAlert('success', 'Export Excel terminé avec succès');
        }

        // Helper function to format currency for PDF (avoiding locale issues)
        function formatCurrencyForPDF(amount) {
            if (!amount || isNaN(amount)) return '0,00 DA';
            
            // Format number with French-style formatting (spaces for thousands, comma for decimals)
            const formattedNumber = Math.abs(amount).toFixed(2).replace('.', ',');
            const parts = formattedNumber.split(',');
            
            // Add spaces for thousands separator
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            
            const result = parts.join(',');
            return (amount < 0 ? '-' : '') + result + ' DA';
        }

        function exportToPDF() {
            if (currentData.length === 0) {
                showAlert('warning', 'Aucune donnée à exporter');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape'); // Use landscape orientation for better table layout

            // Add title
            doc.setFontSize(24);
            doc.setTextColor(102, 126, 234);
            doc.text('ÉTAT FOURNISSEUR', doc.internal.pageSize.getWidth() / 2, 20, { align: 'center' });

            // Add date range
            doc.setFontSize(14);
            doc.setTextColor(60, 60, 60);
            const dateRange = `Période: ${document.getElementById('startDate').value} au ${document.getElementById('endDate').value}`;
            doc.text(dateRange, doc.internal.pageSize.getWidth() / 2, 30, { align: 'center' });

            // Add generation date
            const generatedDate = `Généré le: ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}`;
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(generatedDate, doc.internal.pageSize.getWidth() / 2, 37, { align: 'center' });

            // Calculate statistics
            const totalRecords = currentData.length;
            const uniqueBpChiffre = currentData.length > 0 ? (parseFloat(currentData[0].BP_CHIFFRE) || 0) : 0;
            const uniqueVerseTot = currentData.length > 0 ? (parseFloat(currentData[0].VERSE_TOT) || 0) : 0;
            const totalReste = currentData.reduce((sum, row) => {
                const montant = parseFloat(row.GRANDTOTAL) || 0;
                const totalVerse = parseFloat(row.VERSE_FACT) || 0;
                return sum + (montant - totalVerse);
            }, 0);

            // Add Statistics Cards section
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('STATISTIQUES', 14, 50);

            // Create statistics table
            const statsData = [
                ['Total Factures', totalRecords.toString()],
                ['Total Montant', formatCurrencyForPDF(uniqueBpChiffre)],
                ['Total Versé', formatCurrencyForPDF(uniqueVerseTot)],
                ['Total Reste', formatCurrencyForPDF(totalReste)]
            ];

            doc.autoTable({
                body: statsData,
                startY: 55,
                theme: 'grid',
                styles: {
                    fontSize: 11,
                    cellPadding: 3,
                    textColor: [0, 0, 0]
                },
                columnStyles: {
                    0: { fontStyle: 'bold', fillColor: [240, 248, 255] },
                    1: { halign: 'right', fontStyle: 'bold' }
                },
                margin: { left: 14, right: 14 },
                tableWidth: 'auto'
            });

            // Get the Y position after the statistics table
            let finalY = doc.lastAutoTable.finalY + 15;

            // Add main data table title
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('DÉTAIL DES FACTURES', 14, finalY);

            // Prepare table data
            const tableData = currentData.map(row => {
                const montant = parseFloat(row.GRANDTOTAL) || 0;
                const totalVerse = parseFloat(row.VERSE_FACT) || 0;
                const reste = montant - totalVerse;
                
                return [
                    formatDate(row.DATEINVOICED),
                    row.DOCUMENTNO || '',
                    row.NBL || '',
                    formatDate(row.DATEVERSEMENT),
                    formatCurrencyForPDF(row.GRANDTOTAL),
                    formatCurrencyForPDF(row.VERSE_FACT),
                    formatCurrencyForPDF(row.VERSE_CHEQUE),
                    formatCurrencyForPDF(reste),
                    row.CHEQUE || ''
                ];
            });

            // Add main data table
            doc.autoTable({
                head: [['Date', 'N° Facture', 'N° BL', 'Date Vers.', 'Montant', 'Total Versé', 'Mt Versement', 'Reste', 'N° Chèque']],
                body: tableData,
                startY: finalY + 5,
                theme: 'striped',
                styles: {
                    fontSize: 8,
                    cellPadding: 2,
                    overflow: 'linebreak'
                },
                headStyles: {
                    fillColor: [102, 126, 234],
                    textColor: 255,
                    fontStyle: 'bold',
                    fontSize: 9
                },
                alternateRowStyles: {
                    fillColor: [248, 249, 250]
                },
                columnStyles: {
                    0: { cellWidth: 25 }, // Date
                    1: { cellWidth: 30 }, // N° Facture
                    2: { cellWidth: 25 }, // N° BL
                    3: { cellWidth: 25 }, // Date Versement
                    4: { cellWidth: 30, halign: 'right' }, // Montant
                    5: { cellWidth: 30, halign: 'right' }, // Total Versé
                    6: { cellWidth: 30, halign: 'right' }, // Mt Versement
                    7: { cellWidth: 30, halign: 'right' }, // Reste
                    8: { cellWidth: 25 } // N° Chèque
                },
                margin: { left: 14, right: 14 }
            });

            // Add footer with page numbers
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(100, 100, 100);
                doc.text(
                    `Page ${i} sur ${pageCount}`,
                    doc.internal.pageSize.getWidth() / 2,
                    doc.internal.pageSize.getHeight() - 10,
                    { align: 'center' }
                );
                
                // Add company name or system name in footer
                doc.text(
                    'BNM - Système de Gestion',
                    14,
                    doc.internal.pageSize.getHeight() - 10
                );
                
                // Add generation timestamp
                doc.text(
                    generatedDate,
                    doc.internal.pageSize.getWidth() - 14,
                    doc.internal.pageSize.getHeight() - 10,
                    { align: 'right' }
                );
            }

            // Generate filename
            const date = new Date();
            const filename = `etat_fournisseur_${date.getFullYear()}_${(date.getMonth()+1).toString().padStart(2,'0')}_${date.getDate().toString().padStart(2,'0')}.pdf`;

            // Save PDF
            doc.save(filename);
            showAlert('success', 'Export PDF terminé avec succès');
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString('fr-FR');
        }

        function formatCurrency(amount) {
            if (!amount || isNaN(amount)) return '0,00 DA';
            return parseFloat(amount).toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' DA';
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'bg-green-100 border-green-500 text-green-700' :
                              type === 'danger' ? 'bg-red-100 border-red-500 text-red-700' :
                              type === 'warning' ? 'bg-yellow-100 border-yellow-500 text-yellow-700' :
                              'bg-blue-100 border-blue-500 text-blue-700';
            
            const iconClass = type === 'success' ? 'fa-check-circle' :
                             type === 'danger' ? 'fa-exclamation-triangle' :
                             type === 'warning' ? 'fa-exclamation-circle' :
                             'fa-info-circle';
            
            const alertHtml = `
                <div class="border-l-4 p-4 ${alertClass} rounded-md mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">${message}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            alertContainer.innerHTML = alertHtml;
            
            // Auto-hide success and info alerts
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 5000);
            }
        }
    </script>
</body>
</html>
