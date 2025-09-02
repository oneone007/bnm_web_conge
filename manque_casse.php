<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Inventory Analysis - Manque & Casse</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 1400px;
            padding: 30px;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .date-filters {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            border: none;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .manque-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }
        
        .casse-header {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .summary-value {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .summary-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
        }
        
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .manque-row-checkbox {
            transform: scale(1.2);
        }
        
        .cumulate-row {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }
        
        .cumulate-badge {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <h1><i class="fas fa-chart-line me-3"></i>Stock Inventory Analysis</h1>
                <p class="mb-0">Analyse des Manques et Casses de Stock par Période</p>
            </div>

            <!-- Date Filters -->
            <div class="date-filters">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label fw-bold">
                            <i class="fas fa-calendar-alt me-2"></i>Date de Début
                        </label>
                        <input type="date" class="form-control" id="start_date" value="">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label fw-bold">
                            <i class="fas fa-calendar-alt me-2"></i>Date de Fin
                        </label>
                        <input type="date" class="form-control" id="end_date" value="">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-custom w-100" onclick="loadInventoryData()">
                            <i class="fas fa-search me-2"></i>Rechercher
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row" id="summary-section" style="display: none;">
                <div class="col-md-6">
                    <div class="summary-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="summary-label">Total Manque Stock</div>
                                <div class="summary-value" id="total-manque">0.00 DA</div>
                            </div>
                            <div class="fs-1">
                                <i class="fas fa-arrow-down text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="summary-label">Total Casse Stock</div>
                                <div class="summary-value" id="total-casse">0.00 DA</div>
                            </div>
                            <div class="fs-1">
                                <i class="fas fa-broken-glass text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manque Stock Section -->
            <div class="card">
                <div class="card-header manque-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-down me-2"></i>
                            Manque de Stock
                            <span class="badge bg-light text-dark ms-2" id="manque-count">0</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm" onclick="showCumulateModal()" id="cumulate-btn" style="display: none;">
                                <i class="fas fa-plus me-2"></i>Cumuler
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="downloadFinalManqueExcel()" id="download-final-manque-btn" style="display: none;">
                                <i class="fas fa-file-excel me-2"></i>Final Excel
                            </button>
                            <button type="button" class="btn btn-light btn-sm" onclick="downloadManqueExcel()" id="download-manque-btn" style="display: none;">
                                <i class="fas fa-download me-2"></i>Original Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-manque" onchange="toggleAllManqueSelection()">
                                    </th>
                                    <th>Document No</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Nombre de Lignes</th>
                                    <th class="text-end">Montant Différence</th>
                                </tr>
                            </thead>
                            <tbody id="manque-table-body">
                                <tr>
                                    <td colspan="6" class="text-center p-4">
                                        <div class="empty-state">
                                            <i class="fas fa-search"></i>
                                            <p>Sélectionnez une période pour voir les données</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Casse Stock Section -->
            <div class="card">
                <div class="card-header casse-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-broken-glass me-2"></i>
                            Casse de Stock (Business Partner: 1126375)
                            <span class="badge bg-light text-dark ms-2" id="casse-count">0</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm" onclick="showCasseCumulateModal()" id="cumulate-casse-btn" style="display: none;">
                                <i class="fas fa-plus me-2"></i>Cumuler
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="downloadFinalCasseExcel()" id="download-final-casse-btn" style="display: none;">
                                <i class="fas fa-file-excel me-2"></i>Final Excel
                            </button>
                            <button type="button" class="btn btn-light btn-sm" onclick="downloadCasseExcel()" id="download-casse-btn" style="display: none;">
                                <i class="fas fa-download me-2"></i>Original Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-casse" onchange="toggleAllCasseSelection()">
                                    </th>
                                    <th>Document No</th>
                                    <th>Description</th>
                                    <th>Date Facture</th>
                                    <th>Statut</th>
                                    <th>Total Lignes</th>
                                    <th class="text-end">Montant Net</th>
                                    <th class="text-end">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody id="casse-table-body">
                                <tr>
                                    <td colspan="8" class="text-center p-4">
                                        <div class="empty-state">
                                            <i class="fas fa-search"></i>
                                            <p>Sélectionnez une période pour voir les données</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cumulate Modal for Manque -->
    <div class="modal fade" id="cumulateModal" tabindex="-1" aria-labelledby="cumulateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cumulateModalLabel">
                        <i class="fas fa-plus me-2"></i>Cumuler les Lignes Sélectionnées (Manque)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Résumé de la cumulation:</strong>
                    </div>
                    <div id="cumulate-summary">
                        <!-- Summary will be populated by JavaScript -->
                    </div>
                    <div class="mt-3">
                        <label for="cumulate-description" class="form-label fw-bold">Description:</label>
                        <input type="text" class="form-control" id="cumulate-description" placeholder="Entrez la description pour la ligne cumulée">
                        <div class="form-text">Par défaut: description de la première ligne sélectionnée</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="performCumulation()">
                        <i class="fas fa-plus me-2"></i>Cumuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cumulate Modal for Casse -->
    <div class="modal fade" id="cumulateCasseModal" tabindex="-1" aria-labelledby="cumulateCasseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cumulateCasseModalLabel">
                        <i class="fas fa-plus me-2"></i>Cumuler les Lignes Sélectionnées (Casse)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Résumé de la cumulation:</strong>
                    </div>
                    <div id="cumulate-casse-summary">
                        <!-- Summary will be populated by JavaScript -->
                    </div>
                    <div class="mt-3">
                        <label for="cumulate-casse-description" class="form-label fw-bold">Description:</label>
                        <input type="text" class="form-control" id="cumulate-casse-description" placeholder="Entrez la description pour la ligne cumulée">
                        <div class="form-text">Par défaut: description de la première ligne sélectionnée</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="performCasseCumulation()">
                        <i class="fas fa-plus me-2"></i>Cumuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SheetJS for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- API Config -->
    <script src="api_config.js"></script>
    
    <script>
        // Ensure API_CONFIG is loaded
        if (typeof API_CONFIG === 'undefined') {
            console.error('API_CONFIG not loaded');
            window.API_CONFIG = {
                getApiUrl: function(endpoint) {
                    return 'http://192.168.1.94:5000' + endpoint;
                }
            };
        }

        // Store current search parameters for Excel download
        let currentSearchParams = {
            startDate: '',
            endDate: ''
        };

        // Initialize dates to current month
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
            document.getElementById('end_date').value = lastDay.toISOString().split('T')[0];
        });

        function showLoading(tableId) {
            document.getElementById(tableId).innerHTML = `
                <tr>
                    <td colspan="${tableId === 'manque-table-body' ? '6' : '8'}" class="text-center p-4">
                        <div class="loading-spinner"></div>
                        <span class="ms-2">Chargement des données...</span>
                    </td>
                </tr>
            `;
        }

        function showError(tableId, message) {
            document.getElementById(tableId).innerHTML = `
                <tr>
                    <td colspan="${tableId === 'manque-table-body' ? '6' : '8'}" class="text-center p-4">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${message}
                        </div>
                    </td>
                </tr>
            `;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-DZ', {
                style: 'currency',
                currency: 'DZD',
                minimumFractionDigits: 2
            }).format(amount);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR');
        }

        async function loadInventoryData() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Veuillez sélectionner les dates de début et de fin');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('La date de début doit être antérieure à la date de fin');
                return;
            }

            // Store current search parameters for Excel download
            currentSearchParams.startDate = startDate;
            currentSearchParams.endDate = endDate;

            // Show loading for both tables
            showLoading('manque-table-body');
            showLoading('casse-table-body');

            try {
                // Fetch both datasets in parallel
                const [manqueResponse, casseResponse] = await Promise.all([
                    fetch(API_CONFIG.getApiUrl(`/stock-manque?start_date=${startDate}&end_date=${endDate}`)),
                    fetch(API_CONFIG.getApiUrl(`/stock-casse?start_date=${startDate}&end_date=${endDate}`))
                ]);

                const manqueData = await manqueResponse.json();
                const casseData = await casseResponse.json();

                // Load Manque data
                loadManqueData(manqueData);
                
                // Load Casse data
                loadCasseData(casseData);

                // Update summary
                updateSummary(manqueData, casseData);

            } catch (error) {
                console.error('Error loading data:', error);
                showError('manque-table-body', 'Erreur lors du chargement des données de manque');
                showError('casse-table-body', 'Erreur lors du chargement des données de casse');
            }
        }

        function loadManqueData(data) {
            const tableBody = document.getElementById('manque-table-body');
            const countBadge = document.getElementById('manque-count');
            const downloadBtn = document.getElementById('download-manque-btn');
            const downloadFinalBtn = document.getElementById('download-final-manque-btn');
            const cumulateBtn = document.getElementById('cumulate-btn');

            if (data.success && data.data && data.data.length > 0) {
                countBadge.textContent = data.data.length;
                downloadBtn.style.display = 'block';
                downloadFinalBtn.style.display = 'block';
                cumulateBtn.style.display = 'block';
                
                tableBody.innerHTML = data.data.map((item, index) => {
                    const isCumulated = item.DOCUMENTNO && item.DOCUMENTNO.startsWith('CUMUL-');
                    return `
                    <tr data-index="${index}" ${isCumulated ? 'class="cumulate-row"' : ''}>
                        <td>
                            ${isCumulated ? 
                                '<i class="fas fa-plus text-warning" title="Ligne cumulée"></i>' : 
                                `<input type="checkbox" class="manque-row-checkbox" value="${index}" onchange="updateCumulateButton()">`
                            }
                        </td>
                        <td>
                            <strong>${item.DOCUMENTNO}</strong>
                            ${isCumulated ? '<span class="badge cumulate-badge ms-2">CUMULÉ</span>' : ''}
                        </td>
                        <td>${item.INVENTORY_DESCRIPTION || ''}</td>
                        <td>${formatDate(item.MOVEMENTDATE)}</td>
                        <td><span class="badge bg-primary">${item.NUMBER_OF_LINES}</span></td>
                        <td class="text-end">
                            <strong class="text-danger">${formatCurrency(item.TOTAL_DIFFERENCE_AMOUNT)}</strong>
                        </td>
                    </tr>
                `;}).join('');

                // Store data globally for cumulation
                window.manqueData = data.data;
                
                // Reset cumulate button state
                updateCumulateButton();
            } else {
                countBadge.textContent = '0';
                downloadBtn.style.display = 'none';
                downloadFinalBtn.style.display = 'none';
                cumulateBtn.style.display = 'none';
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center p-4">
                            <div class="empty-state">
                                <i class="fas fa-info-circle"></i>
                                <p>Aucune donnée de manque trouvée pour cette période</p>
                            </div>
                        </td>
                    </tr>
                `;
                window.manqueData = [];
            }
        }

        function loadCasseData(data) {
            const tableBody = document.getElementById('casse-table-body');
            const countBadge = document.getElementById('casse-count');
            const downloadBtn = document.getElementById('download-casse-btn');
            const downloadFinalBtn = document.getElementById('download-final-casse-btn');
            const cumulateBtn = document.getElementById('cumulate-casse-btn');

            if (data.success && data.data && data.data.length > 0) {
                countBadge.textContent = data.data.length;
                downloadBtn.style.display = 'block';
                downloadFinalBtn.style.display = 'block';
                cumulateBtn.style.display = 'block';
                
                tableBody.innerHTML = data.data.map((item, index) => {
                    const isCumulated = item.DOCUMENTNO && item.DOCUMENTNO.startsWith('CUMUL-');
                    return `
                    <tr data-index="${index}" ${isCumulated ? 'class="cumulate-row"' : ''}>
                        <td>
                            ${isCumulated ? 
                                '<i class="fas fa-plus text-warning" title="Ligne cumulée"></i>' : 
                                `<input type="checkbox" class="casse-row-checkbox" value="${index}" onchange="updateCasseCumulateButton()">`
                            }
                        </td>
                        <td>
                            <strong>${item.DOCUMENTNO}</strong>
                            ${isCumulated ? '<span class="badge cumulate-badge ms-2">CUMULÉ</span>' : ''}
                        </td>
                        <td>${item.DESCRIPTION || ''}</td>
                        <td>${formatDate(item.DATEINVOICED)}</td>
                        <td><span class="badge bg-success">${item.DOCSTATUS}</span></td>
                        <td><span class="badge bg-info">${item.TOTAL_LINES}</span></td>
                        <td class="text-end">${formatCurrency(item.TOTAL_NET_AMOUNT)}</td>
                        <td class="text-end">
                            <strong class="text-primary">${formatCurrency(item.GRANDTOTAL)}</strong>
                        </td>
                    </tr>
                `;}).join('');

                // Store data globally for cumulation
                window.casseData = data.data;
                
                // Reset cumulate button state
                updateCasseCumulateButton();
            } else {
                countBadge.textContent = '0';
                downloadBtn.style.display = 'none';
                downloadFinalBtn.style.display = 'none';
                cumulateBtn.style.display = 'none';
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center p-4">
                            <div class="empty-state">
                                <i class="fas fa-info-circle"></i>
                                <p>Aucune donnée de casse trouvée pour cette période</p>
                            </div>
                        </td>
                    </tr>
                `;
                window.casseData = [];
            }
        }

        function updateSummary(manqueData, casseData) {
            const summarySection = document.getElementById('summary-section');
            const totalManqueElement = document.getElementById('total-manque');
            const totalCasseElement = document.getElementById('total-casse');

            let totalManque = 0;
            let totalCasse = 0;

            if (manqueData.success && manqueData.data) {
                totalManque = manqueData.data.reduce((sum, item) => sum + parseFloat(item.TOTAL_DIFFERENCE_AMOUNT || 0), 0);
            }

            if (casseData.success && casseData.data) {
                totalCasse = casseData.data.reduce((sum, item) => sum + parseFloat(item.GRANDTOTAL || 0), 0);
            }

            totalManqueElement.textContent = formatCurrency(Math.abs(totalManque));
            totalCasseElement.textContent = formatCurrency(totalCasse);

            summarySection.style.display = 'flex';
        }

        async function downloadManqueExcel() {
            if (!currentSearchParams.startDate || !currentSearchParams.endDate) {
                alert('Veuillez d\'abord effectuer une recherche');
                return;
            }

            try {
                const downloadUrl = API_CONFIG.getApiUrl(`/stock-manque/excel?start_date=${currentSearchParams.startDate}&end_date=${currentSearchParams.endDate}`);
                
                // Create a temporary link to trigger download
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `manque_stock_original_${currentSearchParams.startDate}_${currentSearchParams.endDate}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error('Error downloading manque Excel:', error);
                alert('Erreur lors du téléchargement du fichier Excel');
            }
        }

        async function downloadCasseExcel() {
            if (!currentSearchParams.startDate || !currentSearchParams.endDate) {
                alert('Veuillez d\'abord effectuer une recherche');
                return;
            }

            try {
                const downloadUrl = API_CONFIG.getApiUrl(`/stock-casse/excel?start_date=${currentSearchParams.startDate}&end_date=${currentSearchParams.endDate}`);
                
                // Create a temporary link to trigger download
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `casse_stock_original_${currentSearchParams.startDate}_${currentSearchParams.endDate}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error('Error downloading casse Excel:', error);
                alert('Erreur lors du téléchargement du fichier Excel');
            }
        }

        // Final Excel Download Functions (with cumulated data)
        function downloadFinalManqueExcel() {
            if (!window.manqueData || window.manqueData.length === 0) {
                alert('Aucune donnée à télécharger');
                return;
            }

            try {
                // Prepare data for Excel
                const excelData = window.manqueData.map(item => ({
                    'Document No': item.DOCUMENTNO || '',
                    'Description': item.INVENTORY_DESCRIPTION || '',
                    'Date': formatDate(item.MOVEMENTDATE),
                    'Nombre de Lignes': item.NUMBER_OF_LINES || 0,
                    'Montant Différence': parseFloat(item.TOTAL_DIFFERENCE_AMOUNT || 0).toFixed(2)
                }));

                // Create workbook and worksheet
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.json_to_sheet(excelData);

                // Set column widths
                ws['!cols'] = [
                    { wch: 15 }, // Document No
                    { wch: 40 }, // Description
                    { wch: 12 }, // Date
                    { wch: 15 }, // Nombre de Lignes
                    { wch: 18 }  // Montant Différence
                ];

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, 'Manque Stock Final');

                // Generate filename
                const filename = `manque_stock_final_${currentSearchParams.startDate}_${currentSearchParams.endDate}.xlsx`;

                // Download file
                XLSX.writeFile(wb, filename);

            } catch (error) {
                console.error('Error downloading final manque Excel:', error);
                alert('Erreur lors du téléchargement du fichier Excel');
            }
        }

        function downloadFinalCasseExcel() {
            if (!window.casseData || window.casseData.length === 0) {
                alert('Aucune donnée à télécharger');
                return;
            }

            try {
                // Prepare data for Excel
                const excelData = window.casseData.map(item => ({
                    'Document No': item.DOCUMENTNO || '',
                    'Description': item.DESCRIPTION || '',
                    'Date Facture': formatDate(item.DATEINVOICED),
                    'Statut': item.DOCSTATUS || '',
                    'Total Lignes': item.TOTAL_LINES || 0,
                    'Montant Net': parseFloat(item.TOTAL_NET_AMOUNT || 0).toFixed(2),
                    'Grand Total': parseFloat(item.GRANDTOTAL || 0).toFixed(2)
                }));

                // Create workbook and worksheet
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.json_to_sheet(excelData);

                // Set column widths
                ws['!cols'] = [
                    { wch: 15 }, // Document No
                    { wch: 40 }, // Description
                    { wch: 12 }, // Date Facture
                    { wch: 10 }, // Statut
                    { wch: 12 }, // Total Lignes
                    { wch: 15 }, // Montant Net
                    { wch: 15 }  // Grand Total
                ];

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, 'Casse Stock Final');

                // Generate filename
                const filename = `casse_stock_final_${currentSearchParams.startDate}_${currentSearchParams.endDate}.xlsx`;

                // Download file
                XLSX.writeFile(wb, filename);

            } catch (error) {
                console.error('Error downloading final casse Excel:', error);
                alert('Erreur lors du téléchargement du fichier Excel');
            }
        }

        // Cumulation Functions
        function toggleAllManqueSelection() {
            const selectAll = document.getElementById('select-all-manque');
            const checkboxes = document.querySelectorAll('.manque-row-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateCumulateButton();
        }

        function updateCumulateButton() {
            const selectedCheckboxes = document.querySelectorAll('.manque-row-checkbox:checked');
            const cumulateBtn = document.getElementById('cumulate-btn');
            
            if (selectedCheckboxes.length >= 2) {
                cumulateBtn.disabled = false;
                cumulateBtn.innerHTML = `<i class="fas fa-plus me-2"></i>Cumuler (${selectedCheckboxes.length})`;
            } else {
                cumulateBtn.disabled = true;
                cumulateBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Cumuler';
            }
        }

        function showCumulateModal() {
            const selectedCheckboxes = document.querySelectorAll('.manque-row-checkbox:checked');
            
            if (selectedCheckboxes.length < 2) {
                alert('Veuillez sélectionner au moins 2 lignes à cumuler');
                return;
            }

            const selectedIndices = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
            const selectedData = selectedIndices.map(index => window.manqueData[index]);

            // Calculate summary
            const totalAmount = selectedData.reduce((sum, item) => sum + parseFloat(item.TOTAL_DIFFERENCE_AMOUNT || 0), 0);
            const totalLines = selectedData.reduce((sum, item) => sum + parseInt(item.NUMBER_OF_LINES || 0), 0);
            const documentNos = selectedData.map(item => item.DOCUMENTNO);

            // Update modal content
            document.getElementById('cumulate-summary').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Documents sélectionnés:</strong>
                        <ul class="mt-2">
                            ${documentNos.map(doc => `<li>${doc}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Nombre de lignes total:</strong> 
                                    <span class="badge bg-primary">${totalLines}</span>
                                </div>
                                <div>
                                    <strong>Montant total:</strong> 
                                    <span class="text-danger fw-bold">${formatCurrency(totalAmount)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Set description (first selected item)
            document.getElementById('cumulate-description').value = selectedData[0].INVENTORY_DESCRIPTION || '';

            // Store selected data for cumulation
            window.selectedCumulationData = {
                indices: selectedIndices,
                data: selectedData,
                totalAmount: totalAmount,
                totalLines: totalLines,
                description: selectedData[0].INVENTORY_DESCRIPTION || ''
            };

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('cumulateModal'));
            modal.show();
        }

        function performCumulation() {
            if (!window.selectedCumulationData) {
                alert('Erreur: données de cumulation non trouvées');
                return;
            }

            const cumulationData = window.selectedCumulationData;
            
            // Get the description from the input field
            const customDescription = document.getElementById('cumulate-description').value.trim();
            const finalDescription = customDescription || cumulationData.description;
            
            // Create new cumulated row
            const newRow = {
                DOCUMENTNO: 'CUMUL-' + Date.now(),
                INVENTORY_DESCRIPTION: finalDescription,
                MOVEMENTDATE: new Date().toISOString().split('T')[0],
                NUMBER_OF_LINES: cumulationData.totalLines,
                TOTAL_DIFFERENCE_AMOUNT: cumulationData.totalAmount
            };

            // Remove selected rows from data (sort indices in descending order to avoid index issues)
            const sortedIndices = cumulationData.indices.sort((a, b) => b - a);
            sortedIndices.forEach(index => {
                window.manqueData.splice(index, 1);
            });

            // Add new cumulated row at the beginning
            window.manqueData.unshift(newRow);

            // Refresh table
            loadManqueData({ success: true, data: window.manqueData });

            // Update summary - need to get current casse data
            updateSummary({ success: true, data: window.manqueData }, { success: true, data: window.casseData || [] });

            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('cumulateModal'));
            modal.hide();

            // Reset select all checkbox
            document.getElementById('select-all-manque').checked = false;

            // Show success message
            setTimeout(() => {
                alert(`Cumulation réussie! ${cumulationData.indices.length} lignes ont été cumulées.`);
            }, 500);
        }

        // Casse Cumulation Functions
        function toggleAllCasseSelection() {
            const selectAll = document.getElementById('select-all-casse');
            const checkboxes = document.querySelectorAll('.casse-row-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateCasseCumulateButton();
        }

        function updateCasseCumulateButton() {
            const selectedCheckboxes = document.querySelectorAll('.casse-row-checkbox:checked');
            const cumulateBtn = document.getElementById('cumulate-casse-btn');
            
            if (selectedCheckboxes.length >= 2) {
                cumulateBtn.disabled = false;
                cumulateBtn.innerHTML = `<i class="fas fa-plus me-2"></i>Cumuler (${selectedCheckboxes.length})`;
            } else {
                cumulateBtn.disabled = true;
                cumulateBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Cumuler';
            }
        }

        function showCasseCumulateModal() {
            const selectedCheckboxes = document.querySelectorAll('.casse-row-checkbox:checked');
            
            if (selectedCheckboxes.length < 2) {
                alert('Veuillez sélectionner au moins 2 lignes à cumuler');
                return;
            }

            const selectedIndices = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
            const selectedData = selectedIndices.map(index => window.casseData[index]);

            // Calculate summary
            const totalNetAmount = selectedData.reduce((sum, item) => sum + parseFloat(item.TOTAL_NET_AMOUNT || 0), 0);
            const totalGrandTotal = selectedData.reduce((sum, item) => sum + parseFloat(item.GRANDTOTAL || 0), 0);
            const totalLines = selectedData.reduce((sum, item) => sum + parseInt(item.TOTAL_LINES || 0), 0);
            const documentNos = selectedData.map(item => item.DOCUMENTNO);

            // Update modal content
            document.getElementById('cumulate-casse-summary').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Documents sélectionnés:</strong>
                        <ul class="mt-2">
                            ${documentNos.map(doc => `<li>${doc}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Total lignes:</strong> 
                                    <span class="badge bg-info">${totalLines}</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Montant net total:</strong> 
                                    <span class="text-primary fw-bold">${formatCurrency(totalNetAmount)}</span>
                                </div>
                                <div>
                                    <strong>Grand total:</strong> 
                                    <span class="text-primary fw-bold">${formatCurrency(totalGrandTotal)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Set description (first selected item)
            document.getElementById('cumulate-casse-description').value = selectedData[0].DESCRIPTION || '';

            // Store selected data for cumulation
            window.selectedCasseCumulationData = {
                indices: selectedIndices,
                data: selectedData,
                totalNetAmount: totalNetAmount,
                totalGrandTotal: totalGrandTotal,
                totalLines: totalLines,
                description: selectedData[0].DESCRIPTION || ''
            };

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('cumulateCasseModal'));
            modal.show();
        }

        function performCasseCumulation() {
            if (!window.selectedCasseCumulationData) {
                alert('Erreur: données de cumulation non trouvées');
                return;
            }

            const cumulationData = window.selectedCasseCumulationData;
            
            // Get the description from the input field
            const customDescription = document.getElementById('cumulate-casse-description').value.trim();
            const finalDescription = customDescription || cumulationData.description;
            
            // Create new cumulated row
            const newRow = {
                DOCUMENTNO: 'CUMUL-' + Date.now(),
                DESCRIPTION: finalDescription,
                DATEINVOICED: new Date().toISOString().split('T')[0],
                DOCSTATUS: 'CO',
                TOTAL_LINES: cumulationData.totalLines,
                TOTAL_NET_AMOUNT: cumulationData.totalNetAmount,
                GRANDTOTAL: cumulationData.totalGrandTotal
            };

            // Remove selected rows from data (sort indices in descending order to avoid index issues)
            const sortedIndices = cumulationData.indices.sort((a, b) => b - a);
            sortedIndices.forEach(index => {
                window.casseData.splice(index, 1);
            });

            // Add new cumulated row at the beginning
            window.casseData.unshift(newRow);

            // Refresh table
            loadCasseData({ success: true, data: window.casseData });

            // Update summary - need to get current manque data
            updateSummary({ success: true, data: window.manqueData || [] }, { success: true, data: window.casseData });

            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('cumulateCasseModal'));
            modal.hide();

            // Reset select all checkbox
            document.getElementById('select-all-casse').checked = false;

            // Show success message
            setTimeout(() => {
                alert(`Cumulation réussie! ${cumulationData.indices.length} lignes ont été cumulées.`);
            }, 500);
        }
    </script>
</body>
</html>
