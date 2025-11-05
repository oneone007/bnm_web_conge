<?php
session_start();
// Check for force logout by admin
include_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture Detail</title>
    <script src="theme.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* --- Modern, compact, professional UI/UX and robust dark mode --- */
        body {
            background: #f6f7fb;
            font-family: "Poppins", sans-serif;
            padding: 20px;
            min-height: 100vh;
            color: #333;
        }
        body.dark-mode {
            background: #181a1b !important;
            color: #e0e0e0 !important;
        }
        .facture-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.10);
            padding: 28px;
            max-width: 1200px;
            margin: 0 auto;
            transition: background 0.3s, color 0.3s;
        }
        body.dark-mode .facture-container {
            background: #23272b !important;
            color: #e0e0e0 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .header {
            font-weight: 700;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2c3e50;
            font-size: 1.6rem;
        }
        body.dark-mode .header {
            color: #b3d1ff !important;
        }
        .top-bar {
            background: #f8f9fa;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
        }
        body.dark-mode .top-bar {
            background: #23272b !important;
            border: 1px solid #444 !important;
        }
        .search-input-wrapper, .product-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 400px;
        }
        .search-input, .product-search {
            width: 100%;
            padding: 9px 14px 9px 40px;
            border: 1px solid #ced4da;
            border-radius: 40px;
            font-size: 14px;
            background: white;
            transition: background 0.3s, color 0.3s;
        }
        body.dark-mode .search-input, body.dark-mode .product-search {
            background: #23272b !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }
        .search-icon, .product-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        body.dark-mode .search-icon, body.dark-mode .product-search-icon {
            color: #b3d1ff !important;
        }
        .tabs {
            display: flex;
            gap: 6px;
            background: #eef2ff;
            padding: 3px;
            border-radius: 7px;
        }
        body.dark-mode .tabs {
            background: #232d3b !important;
        }
        .tab-btn {
            padding: 7px 14px;
            border: none;
            background: transparent;
            color: #6c757d;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.2s, color 0.2s;
        }
        .tab-btn.active {
            background: white;
            color: #007bff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        body.dark-mode .tab-btn.active {
            background: #181a1b !important;
            color: #4da3ff !important;
        }
        .form-content {
            padding: 16px 0;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            align-items: center;
            gap: 10px;
            margin-bottom: 13px;
        }
        .form-label {
            text-align: right;
            font-weight: 500;
            color: #333;
            font-size: 0.98rem;
        }
        body.dark-mode .form-label {
            color: #b3d1ff !important;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 9px 13px;
            border: 1px solid #ced4da;
            background: #f8f9fa;
            font-size: 0.98rem;
        }
        body.dark-mode .form-control, body.dark-mode .form-select {
            background: #23272b !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            background: white;
            box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.18);
        }
        .form-textarea {
            padding: 9px 13px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            background: #f8f9fa;
            resize: vertical;
            min-height: 70px;
            font-family: "Poppins", sans-serif;
        }
        body.dark-mode .form-textarea {
            background: #23272b !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }
        .form-textarea:focus {
            background: white;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.18);
        }
        .payment-badge {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border: 1px solid #ced4da;
            background-color: white;
            border-radius: 8px;
            font-size: 14px;
            color: #007bff;
            font-weight: 500;
            transition: background 0.3s, color 0.3s;
        }
        body.dark-mode .payment-badge {
            background: #232d3b !important;
            color: #b3d1ff !important;
            border-color: #4da3ff !important;
        }
        .section-header {
            background: #eef2ff;
            border-left: 5px solid #3b82f6;
            padding: 13px 16px;
            margin: 24px 0 14px;
            font-weight: 600;
            border-radius: 0 8px 8px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.08rem;
        }
        body.dark-mode .section-header {
            background: #232d3b !important;
            color: #b3d1ff !important;
            border-left: 5px solid #4da3ff !important;
        }
        .navigation-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 7px 10px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-top: 12px;
        }
        body.dark-mode .navigation-controls {
            background: #232d3b !important;
            border-radius: 10px;
            border: 1px solid #4da3ff !important;
        }
        .nav-btn {
            background: #76a9dfff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 0.92rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .nav-btn:hover {
            background: #4da3ff;
        }
        .nav-btn:disabled {
            background: #d6d6d6;
            color: #999;
            cursor: not-allowed;
        }
        .nav-indicator {
            font-weight: 500;
            color: #333;
            padding: 3px 8px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 7px;
            min-width: 48px;
            text-align: center;
            font-size: 0.92rem;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
        }
        body.dark-mode .nav-indicator {
            background: #23272b !important;
            color: #b3d1ff !important;
            border-color: #4da3ff !important;
        }
        .print-wrapper {
            margin: 20px 0 10px;
            position: relative;
        }
        .print-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #76a9dfff;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .print-toggle-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        .print-toggle-btn i.bi-chevron-down {
            transition: transform 0.3s;
        }
        .print-toggle-btn.active i.bi-chevron-down {
            transform: rotate(180deg);
        }
        .print-settings {
            display: none;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
            margin-top: 12px;
            padding: 16px;
            background: #eef2ff;
            border-radius: 12px;
            border: 1px solid #d6e4ff;
            animation: slideDown 0.3s ease-out;
        }
        .print-settings.show {
            display: flex;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        body.dark-mode .print-settings {
            background: #232d3b !important;
            border-color: #4da3ff !important;
        }
        .print-settings .print-label {
            display: block;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            font-size: 0.95rem;
        }
        body.dark-mode .print-settings .print-label {
            color: #b3d1ff !important;
        }
        .print-options {
            display: flex;
            gap: 14px;
            align-items: center;
        }
        .print-options label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.95rem;
            color: #374151;
            cursor: pointer;
        }
        body.dark-mode .print-options label {
            color: #e0e0e0 !important;
        }
        .print-select {
            min-width: 220px;
        }
        .print-help {
            flex: 1 1 100%;
            font-size: 0.86rem;
            color: #4b5563;
        }
        body.dark-mode .print-help {
            color: #9dd2ff !important;
        }
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        /* --- End modern/dark mode UI --- */
        /* ...existing code... */
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2c3e50;
        }
        .top-bar {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }
        .search-input-wrapper, .product-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 400px;
        }
        .search-input, .product-search {
            width: 100%;
            padding: 10px 14px 10px 44px;
            border: 1px solid #ced4da;
            border-radius: 50px;
            font-size: 14px;
            background: white;
        }
        .search-icon, .product-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .tabs {
            display: flex;
            gap: 8px;
            background: #eef2ff;
            padding: 4px;
            border-radius: 8px;
        }
        .tab-btn {
            padding: 8px 16px;
            border: none;
            background: transparent;
            color: #6c757d;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
        }
        .tab-btn.active {
            background: white;
            color: #007bff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .form-content {
            padding: 20px 0;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .form-label {
            text-align: right;
            font-weight: 500;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 14px;
            border: 1px solid #ced4da;
            background: #f8f9fa;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            background: white;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .form-textarea {
            padding: 10px 14px;
            border: 1px solid #ced4da;
            border-radius: 10px;
            background: #f8f9fa;
            resize: vertical;
            min-height: 80px;
            font-family: "Poppins", sans-serif;
        }
        .form-textarea:focus {
            background: white;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .payment-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 1px solid #ced4da;
            background-color: white;
            border-radius: 10px;
            font-size: 14px;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        .section-header {
            background: #eef2ff;
            border-left: 5px solid #3b82f6;
            padding: 16px 20px;
            margin: 32px 0 20px;
            font-weight: 600;
            border-radius: 0 10px 10px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
      
        .button-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #ced4da;
            background: white;
            color: #495057;
        }
        .btn:hover {
            background: #f8f9fa;
        }
        .btn-primary {
            background: #76a9dfff;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
            color: white;
            border: none;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
            border: none;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
        }
        .loader {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            font-size: 18px;
            color: #6c757d;
        }
        .loader::before {
            content: "";
            width: 24px;
            height: 24px;
            border: 3px solid #e9ecef;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 12px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .line-item {
            padding: 12px;
            border-bottom: 1px solid #f1f3f4;
            cursor: pointer;
            border-radius: 8px;
            margin-bottom: 4px;
        }
        .line-item:hover {
            background: #f8f9fa;
        }
        .line-item.active {
            background: #eef2ff;
            border-left: 4px solid #007bff;
            font-weight: 500;
        }
        .line-number {
            font-weight: 600;
            color: #007bff;
            margin-bottom: 4px;
        }
    
        .nav-btn {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 8px;
            cursor: pointer;
        }
        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .nav-indicator {
            font-weight: 500;
            color: #495057;
            padding: 6px 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .suggestions-container {
            position: absolute;
            z-index: 9999;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            max-height: 280px;
            overflow: auto;
            width: 100%;
            font-size: 14px;
            margin-top: 8px;
        }
        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f4;
        }
        .suggestion-item:hover {
            background: #f8f9fa;
        }
        body.dark-mode .suggestions-container,
        body.dark-mode .suggestion-item {
            background: #23272b !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }
        body.dark-mode .suggestion-item:hover {
            background: #333 !important;
        }
        .status-message {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .btn-close {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            min-width: 140px;
            justify-content: center;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #495057;
        }
        @media (max-width: 1024px) {
            .form-grid { grid-template-columns: 1fr; }
            .top-bar { flex-direction: column; align-items: stretch; }
        }
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; text-align: left; }
            .form-label { margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <script>
        // Dark mode activation function for theme.js
        window.setDarkMode = function (isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
    </script>
    <div class="facture-container">
        <h2 class="header"><i class="bi bi-receipt-cutoff"></i> Facture Management System</h2>
        <div id="app"></div>
    </div>

    <script>
        const API_CONFIG = {
            getApiUrl: (endpoint) => `http://192.168.1.94:5000${endpoint}`
        };
        let currentInvoice = null;
        let invoiceLines = [];
        let originalInvoiceLines = [];
        let currentLine = null;
        let currentIndex = 0;
        let currentArticleFilter = '';

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('fr-FR');
        }
        function formatNumber(num) {
            if (num === null || num === undefined || num === '' || isNaN(num)) return '0.00';
            const n = Number(num);
            try {
                return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
            } catch (e) {
                const fixed = n.toFixed(2);
                const parts = fixed.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                return parts.join('.');
            }
        }

        async function loadInvoice(documentNo) {
            if (!documentNo.trim()) return;
            try {
                const url = `${API_CONFIG.getApiUrl('/factures')}?documentno=${encodeURIComponent(documentNo)}`;
                const r = await fetch(url);
                if (!r.ok) throw new Error('Network response was not ok');
                const data = await r.json();
                const invoices = Array.isArray(data) ? data : (data.factures || []);
                currentInvoice = invoices[0] || null;
                renderInvoice();
            } catch (err) {
                console.error('Error loading invoice', err);
                alert('Erreur lors du chargement de la facture');
            }
        }

        function renderInvoice() {
            const inv = currentInvoice || {};
            const appEl = document.getElementById('app');
            appEl.innerHTML = `
                <div class="top-bar">
                    <div class="search-section" style="display:flex;align-items:center;gap:12px;flex:1;min-width:300px;">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                type="text"
                                id="documentNoInput"
                                placeholder="N° de facture (ex: 9926/2025)"
                                class="search-input"
                                value="${inv.DOCUMENTNO || ''}"
                            >
                        </div>
                        <button class="btn btn-primary" onclick="searchInvoice()">Charger</button>
                    </div>
                    <div class="tabs">
                        <button id="tab-facture" class="tab-btn active">Facture</button>
                        <button id="tab-lines" class="tab-btn" disabled>Facture lines</button>
                    </div>
                    <div class="product-search-wrapper">
                        <i class="bi bi-box product-search-icon"></i>
                        <input type="text" id="productSearch" placeholder="Rechercher article..." class="product-search" oninput="handleProductInput(event)" value="${currentArticleFilter || ''}">
                        <button id="clearProductSearch" class="clear-search" style="display:none;position:absolute;right:8px;top:8px;padding:6px 8px;border-radius:8px;border:0;background:#eee;cursor:pointer" type="button" onclick="(function(){ document.getElementById('productSearch').value=''; applyArticleFilter(''); hideSuggestions(); })()">✖</button>
                        <div id="suggestions" class="suggestions-container" style="display:none;"></div>
                    </div>
                    <div id="searchStatus"></div>
                </div>
                <div id="mainLayout" style="display:flex;">
                    <div id="leftSidebarWrapper" style="width:320px;min-width:280px;border-right:1px solid #e9ecef;padding:16px;background:#f8f9fa;display:none;">
                        <div id="linesSidebar" style="position:sticky;top:16px;z-index:2;background:#f8f9fa;padding:12px;max-height:calc(100vh - 140px);overflow:auto;">
                            <div id="linesList" style="max-height:400px;overflow:auto;border:1px solid #e9ecef;background:white;padding:12px;border-radius:8px;"></div>
                        </div>
                    </div>
                    <div id="rightMain" style="flex:1;padding:24px;">
                        <div class="form-content">
                            <div class="print-wrapper">
                                <button id="printToggle-main" type="button" class="print-toggle-btn">
                                    <i class="bi bi-printer"></i>
                                    Imprimer PDF
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="print-settings" id="printSettings-main">
                                    <div>
                                        <span class="print-label">Impression PDF</span>
                                        <div class="print-options">
                                            <label>
                                                <input type="radio" name="printMode-main" value="bl">
                                                BL
                                            </label>
                                            <label>
                                                <input type="radio" name="printMode-main" value="facture" checked>
                                                Facture
                                            </label>
                                        </div>
                                    </div>
                                    <div class="print-select" id="printVariantWrapper-main">
                                        <label class="print-label" for="factureVariant-main">Format facture</label>
                                        <select id="factureVariant-main" class="form-select">
                                            <option value="original" selected>Facture originale</option>
                                            <option value="sans-entete">Facture sans entête</option>
                                        </select>
                                    </div>
                                    <div>
                                        <span class="print-label">Options</span>
                                        <div class="print-options">
                                            <label>
                                                <input type="checkbox" id="remise5-main">
                                                Remise 5% max
                                            </label>
                                        </div>
                                    </div>
                                    <button id="printButton-main" type="button" class="btn btn-primary print-btn">
                                        <i class="bi bi-printer"></i>
                                        Imprimer PDF
                                    </button>
                                    <div class="print-help" id="printHelp-main"></div>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div>
                                    <div class="form-row">
                                        <label class="form-label">Société</label>
                                        <input type="text" class="form-control" value="${inv.SOCIÉTÉ || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Ordre de vente</label>
                                        <input type="text" class="form-control" value="${inv.ORDRE_DE_VENTE || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">N° Facture</label>
                                        <input type="text" class="form-control" value="${inv.DOCUMENTNO || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-textarea" readonly>${inv.DESCRIPTION || ''}</textarea>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Code journal</label>
                                        <input type="text" class="form-control" value="${inv.CODE_JOURNAL || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Date facturation</label>
                                        <input type="text" class="form-control" value="${formatDate(inv.DATEINVOICED)}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Client</label>
                                        <input type="text" class="form-control" value="${inv.CLIENT || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Contact</label>
                                        <input type="text" class="form-control" value="${inv.CONTACT || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Tarif</label>
                                        <input type="text" class="form-control" value="${inv.TARIF || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Vendeur</label>
                                        <input type="text" class="form-control" value="${inv.VENDEUR || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Méthode de paiement</label>
                                        <div class="payment-badge">
                                            <i class="bi bi-credit-card"></i>
                                            <span>${inv.PAYMENTRULELABEL || ''}</span>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Sous méthode de paiement</label>
                                        <input type="text" class="form-control" value="${inv.SOUS_METHODE_DE_PAIEMENT || ''}" readonly>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-row">
                                        <label class="form-label">Organisation</label>
                                        <input type="text" class="form-control" value="${inv.ORGANISATION || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Date commande</label>
                                        <input type="text" class="form-control" value="${formatDate(inv.DATEORDERED)}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Référence</label>
                                        <input type="text" class="form-control" value="${inv.POREFERENCE || ''}">
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <input type="checkbox" ${inv.ISSELFSERVICE === 'Y' ? 'checked' : ''} disabled>
                                            <span>Self-Service</span>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Date comptable</label>
                                        <input type="text" class="form-control" value="${formatDate(inv.DATEACCT)}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Adresse du tiers</label>
                                        <input type="text" class="form-control" value="${inv.ADRESSE_DU_TIERS || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Devise</label>
                                        <input type="text" class="form-control" value="${inv.DEVISE || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <input type="checkbox" disabled>
                                            <span>Imprimer remise</span>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Délai de paiement</label>
                                        <input type="text" class="form-control" value="${inv.DELAI_DE_PAIEMENT || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Organisation Trx</label>
                                        <input type="text" class="form-control" value="${inv.ORGANISATION_TRX || ''}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header"><i class="bi bi-bar-chart"></i> Statut de la facture</div>
                            <div class="form-grid">
                                <div>
                                    <div class="form-row">
                                        <label class="form-label">Total lignes</label>
                                        <input type="text" class="form-control" value="${formatNumber(inv.TOTALLINES)}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Statut document</label>
                                        <input type="text" class="form-control" value="${inv.STATUT_DOCUMENT || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <input type="checkbox" disabled>
                                            <span>Échélonnement paiement validé</span>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <i class="bi bi-check-circle text-success"></i>
                                            <span>Non comptabilisé</span>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <input type="checkbox" ${inv.ISPAID === 'Y' ? 'checked' : ''} disabled>
                                            <span>Payé</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-row">
                                        <label class="form-label">Total général</label>
                                        <input type="text" class="form-control" value="${formatNumber(inv.GRANDTOTAL)}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Type document</label>
                                        <input type="text" class="form-control" value="${inv.TYPE_DOCUMENT || ''}" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <input type="checkbox" disabled>
                                            <span>En litige</span>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <button class="btn btn-close" type="button">
                                                <i class="bi bi-gear"></i>
                                                ${inv.ACTION_STATUS || inv.Action_Status || inv.DOCACTION || ''}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label">Collection Status</label>
                                        <input type="text" class="form-control" value="" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="checkbox-row">
                                            <input type="checkbox" disabled>
                                            <span>Archivé</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('documentNoInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') searchInvoice();
            });
            initializePrintControls('main');
            populateArticleSuggestions(invoiceLines);
            const clearBtnMain = document.getElementById('clearProductSearch');
            if (clearBtnMain) clearBtnMain.style.display = currentArticleFilter ? 'inline-block' : 'none';

            const tabFacture = document.getElementById('tab-facture');
            const tabLines = document.getElementById('tab-lines');
            const leftSidebarWrapper = document.getElementById('leftSidebarWrapper');
            const rightMain = document.getElementById('rightMain');

            function showSidebar(show) {
                if (!leftSidebarWrapper || !rightMain) return;
                leftSidebarWrapper.style.display = show ? '' : 'none';
                rightMain.style.paddingLeft = show ? '16px' : '8px';
            }

            // Enable tab-lines only if facture is loaded (inv.DOCUMENTNO exists)
            if (tabLines) {
                if (inv && inv.DOCUMENTNO) {
                    tabLines.disabled = false;
                } else {
                    tabLines.disabled = true;
                }
            }

            if (tabFacture && tabLines) {
                tabFacture.addEventListener('click', () => {
                    showSidebar(false);
                    renderInvoice();
                    tabFacture.classList.add('active');
                    tabLines.classList.remove('active');
                });
                tabLines.addEventListener('click', () => {
                    if (tabLines.disabled) return;
                    const docNo = document.getElementById('documentNoInput').value;
                    showSidebar(true);
                    loadInvoiceLines(docNo, true);
                    tabLines.classList.add('active');
                    tabFacture.classList.remove('active');
                });
                showSidebar(false);
                tabFacture.classList.add('active');
            }
        }

        // --- Rest of your original JavaScript functions remain unchanged ---
        // (loadInvoiceLines, populateArticleSuggestions, applyArticleFilter, etc.)

        async function loadInvoiceLines(documentNo, showOnly = false) {
            if (!documentNo || !documentNo.trim()) return;
            const listEl = document.getElementById('linesList');
            if (!listEl) return;
            listEl.innerHTML = '<div class="loader">Chargement des lignes...</div>';
            try {
                const url = `${API_CONFIG.getApiUrl('/facture-lines')}?documentno=${encodeURIComponent(documentNo)}`;
                let r;
                try { r = await fetch(url); } catch (e) { }
                if (!r || !r.ok) {
                    const fallback = `/facture-lines?documentno=${encodeURIComponent(documentNo)}`;
                    r = await fetch(fallback);
                }
                if (!r.ok) throw new Error('Network response was not ok');
                const data = await r.json();
                const lines = Array.isArray(data) ? data : (data.lines || []);
                originalInvoiceLines = Array.isArray(lines) ? lines.slice() : [];
                invoiceLines = originalInvoiceLines.slice();
                currentIndex = 0;
                currentLine = invoiceLines[0] || null;
                if (lines.length === 0) {
                    listEl.innerHTML = '<div style="padding:12px;color:#666">Aucune ligne</div>';
                    return;
                }
                populateArticleSuggestions(invoiceLines);
                if (currentArticleFilter) { applyArticleFilter(currentArticleFilter); return; }
                if (showOnly) {
                    if (currentArticleFilter) applyArticleFilter(currentArticleFilter);
                    renderLinesOnly(invoiceLines, documentNo);
                    return;
                }
                listEl.innerHTML = '';
                invoiceLines.forEach((ln, idx) => {
                    const div = document.createElement('div');
                    div.className = 'line-item';
                    div.innerHTML = `<div class="line-number">Ligne ${ln.LINE || idx+1}</div><div>${ln.ARTICLE || ln.DESCRIPTION || ''}</div>`;
                    div.addEventListener('click', () => { selectLine(idx); });
                    listEl.appendChild(div);
                });
                populateArticleSuggestions(invoiceLines);
            } catch (err) {
                console.error('Error loading lines', err);
                listEl.innerHTML = '<div style="padding:12px;color:#b91c1c">Erreur en chargeant les lignes</div>';
            }
        }

        function populateArticleSuggestions(lines) {
            try {
                // Only use ARTICLE field for suggestions
                const suggestions = Array.from(new Set((lines || []).map(l => (l.ARTICLE || '').trim()).filter(Boolean)));
                const dl = document.getElementById('articleSuggestions');
                const dlMini = document.getElementById('articleSuggestionsMini');
                if (dl) { dl.innerHTML = ''; suggestions.forEach(s => { const opt = document.createElement('option'); opt.value = s; dl.appendChild(opt); }); }
                if (dlMini) { dlMini.innerHTML = ''; suggestions.forEach(s => { const opt = document.createElement('option'); opt.value = s; dlMini.appendChild(opt); }); }
            } catch (e) { console.warn('populateArticleSuggestions error', e); }
        }

        function applyArticleFilter(article, exact = false) {
            try {
                if (typeof article === 'string') currentArticleFilter = article.trim();
                if (!originalInvoiceLines || !originalInvoiceLines.length) return;
                if (!currentArticleFilter) {
                    invoiceLines = originalInvoiceLines.slice();
                } else {
                    const f = currentArticleFilter.toLowerCase();
                    invoiceLines = originalInvoiceLines.filter(l => ((l.ARTICLE || l.DESCRIPTION || '') + '').toLowerCase().includes(f));
                }
                currentIndex = 0;
                currentLine = invoiceLines[0] || null;
                const listEl = document.getElementById('linesList');
                if (listEl) {
                    listEl.innerHTML = '';
                    if (!invoiceLines.length) {
                        listEl.innerHTML = '<div style="padding:12px;color:#666">Aucune ligne</div>';
                    } else {
                        invoiceLines.forEach((ln, idx) => {
                            const div = document.createElement('div');
                            div.className = 'line-item';
                            div.innerHTML = `<div class="line-number">Ligne ${ln.LINE || idx+1}</div><div>${ln.ARTICLE || ln.DESCRIPTION || ''}</div>`;
                            div.addEventListener('click', () => { selectLine(idx); });
                            listEl.appendChild(div);
                        });
                    }
                }
                if (document.getElementById('linesListContainer')) {
                    const docNo = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
                    renderLinesOnly(invoiceLines, docNo);
                } else {
                    if (invoiceLines.length) selectLine(0);
                }
                const cb = document.getElementById('clearProductSearch'); if (cb) cb.style.display = currentArticleFilter ? 'inline-block' : 'none';
                const cbMini = document.getElementById('clearProductSearchMini'); if (cbMini) cbMini.style.display = currentArticleFilter ? 'inline-block' : 'none';
                updateNavButtons();
            } catch (e) { console.warn('applyArticleFilter error', e); }
        }

        function showTemporaryStatus(msg, mini = false, isError = false) {
            try {
                const el = document.getElementById(mini ? 'searchStatusMini' : 'searchStatus');
                if (!el) return;
                el.textContent = msg;
                el.className = isError ? 'status-message status-error' : 'status-message status-success';
                setTimeout(() => { el.textContent = ''; el.className = ''; }, 3000);
            } catch (e) { console.warn('showTemporaryStatus error', e); }
        }

        function onProductSelected() {
            const v = document.getElementById('productSearch').value;
            if (!v) return;
            const doc = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
            if ((!originalInvoiceLines || !originalInvoiceLines.length) && doc) {
                loadInvoiceLines(doc, false).then(() => {
                    applyArticleFilter(v, true);
                    if (!invoiceLines.length) showTemporaryStatus('Article non trouvé dans les lignes chargées', false, true);
                });
                return;
            }
            applyArticleFilter(v, true);
            if (!invoiceLines.length) showTemporaryStatus('Article non trouvé dans les lignes chargées', false, true);
        }

        async function onProductSelectedMini() {
            const v = document.getElementById('productSearchMini').value;
            if (!v) return;
            const doc = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
            if ((!originalInvoiceLines || !originalInvoiceLines.length) && doc) {
                await loadInvoiceLines(doc, false);
            }
            applyArticleFilter(v, true);
            if (!invoiceLines.length) showTemporaryStatus('Article non trouvé dans les lignes chargées', false, true);
        }

        async function handleProductInput() {
            const input = document.getElementById('productSearch');
            if (!input) return;
            const v = input.value.trim();
            if (!invoiceLines || !invoiceLines.length) {
                const doc = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
                if (doc) await loadInvoiceLines(doc, false);
            }
            populateArticleSuggestions(invoiceLines);
            renderSuggestions(v);
        }

        async function handleProductInputMini() {
            const input = document.getElementById('productSearchMini');
            if (!input) return;
            const v = input.value.trim();
            if (!invoiceLines || !invoiceLines.length) {
                const doc = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
                if (doc) await loadInvoiceLines(doc, false);
            }
            populateArticleSuggestions(invoiceLines);
            renderSuggestions(v, true);
        }

        function renderSuggestions(filter, mini = false) {
            const container = document.getElementById(mini ? 'suggestionsMini' : 'suggestions');
            const input = document.getElementById(mini ? 'productSearchMini' : 'productSearch');
            if (!container || !input) return;
            const f = (filter || '').toLowerCase();
            // Only use ARTICLE field for suggestions
            const items = Array.from(new Set((invoiceLines || []).map(l => (l.ARTICLE || '').trim()).filter(Boolean)));
            const matches = f ? items.filter(s => s.toLowerCase().includes(f)) : items.slice(0, 100);
            if (!matches.length) {
                container.style.display = 'none';
                container.innerHTML = '';
                return;
            }
            container.innerHTML = '';
            matches.forEach((m, idx) => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = m;
                div.addEventListener('click', () => {
                    input.value = m;
                    currentArticleFilter = m;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    hideSuggestions(mini);
                    applyArticleFilter(m, true);
                    if (invoiceLines && invoiceLines.length) selectLine(0);
                    else showTemporaryStatus('Article non trouvé dans les lignes chargées', mini, true);
                });
                container.appendChild(div);
            });
            container.style.display = 'block';
            container.style.width = (input.offsetWidth + 60) + 'px';
        }

        function hideSuggestions(mini = false) {
            const container = document.getElementById(mini ? 'suggestionsMini' : 'suggestions');
            if (container) container.style.display = 'none';
        }

        function searchInvoice() {
            const docNo = document.getElementById('documentNoInput').value;
            loadInvoice(docNo);
        }

        function onVoirLignesClick() {
            const docNo = document.getElementById('documentNoInput').value;
            loadInvoiceLines(docNo, true);
        }

        function selectLine(index) {
            currentIndex = index;
            currentLine = invoiceLines[index];
            renderLinesOnly(invoiceLines, currentLine ? currentLine.FACTURE : '');
        }

        function goFirst() { if (invoiceLines.length) selectLine(0); }
        function goPrev() { if (currentIndex > 0) selectLine(currentIndex - 1); }
        function goNext() { if (currentIndex < invoiceLines.length - 1) selectLine(currentIndex + 1); }
        function goLast() { if (invoiceLines.length) selectLine(invoiceLines.length - 1); }

        function updateNavButtons() {
            const firstBtn = document.getElementById('nav-first');
            const prevBtn = document.getElementById('nav-prev');
            const nextBtn = document.getElementById('nav-next');
            const lastBtn = document.getElementById('nav-last');
            const indicator = document.getElementById('nav-indicator');
            if (!firstBtn) return;
            firstBtn.disabled = currentIndex === 0;
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex >= invoiceLines.length - 1;
            lastBtn.disabled = currentIndex >= invoiceLines.length - 1;
            if (indicator) indicator.textContent = `${currentIndex + 1} / ${invoiceLines.length}`;
        }

        function renderLinesOnly(lines, documentNo) {
            invoiceLines = Array.isArray(lines) ? lines : [];
            currentIndex = currentIndex || 0;
            currentLine = invoiceLines[currentIndex] || null;
            const appEl = document.getElementById('app');
            let linesListHtml = '';
            invoiceLines.forEach((l, index) => {
                const isActive = currentLine && l.C_INVOICELINE_ID === currentLine.C_INVOICELINE_ID;
                linesListHtml += `
                    <div class="line-item ${isActive ? 'active' : ''}" onclick="selectLine(${index})">
                        <div class="line-number">Ligne ${l.LINE || index + 1}</div>
                        <div>${l.ARTICLE || l.DESCRIPTION || 'Sans description'}</div>
                    </div>
                `;
            });
            const line = currentLine || {};
            appEl.innerHTML = `
                <div class="top-bar">
                    <div class="search-section" style="display:flex;align-items:center;gap:12px;flex:1;min-width:300px;">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                type="text"
                                id="documentNoInput"
                                placeholder="N° de facture (ex: 9926/2025)"
                                class="search-input"
                                value="${documentNo || ''}"
                            >
                        </div>
                        <button class="btn btn-primary" onclick="(function(){ const v=document.getElementById('documentNoInput').value; loadInvoiceLines(v,true); })()">Charger</button>
                    </div>
                    <div class="tabs">
                        <button id="tab-facture-mini" class="tab-btn">Facture</button>
                        <button id="tab-lines-mini" class="tab-btn active">Facture lines</button>
                    </div>
                    <div class="product-search-wrapper">
                        <i class="bi bi-box product-search-icon"></i>
                        <input type="text" id="productSearchMini" placeholder="Rechercher article..." class="product-search" onchange="onProductSelectedMini()" oninput="handleProductInputMini(event)" value="${currentArticleFilter || ''}">
                        <button id="clearProductSearchMini" class="clear-search" style="display:none;position:absolute;right:8px;top:8px;padding:6px 8px;border-radius:8px;border:0;background:#eee;cursor:pointer" type="button" onclick="(function(){ document.getElementById('productSearchMini').value=''; applyArticleFilter(''); hideSuggestions(true); })()">✖</button>
                        <div id="suggestionsMini" class="suggestions-container" style="display:none;"></div>
                    </div>
                    <div id="searchStatusMini"></div>
                </div>
                <div class="form-content">
                    ${invoiceLines.length > 1 ? `
                       <style>
.navigation-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 14px;
  background: #f8f9fa;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  margin-top: 16px;
}

.nav-btn {
  background: #76a9dfff;
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.2s ease-in-out;
  display: flex;
  align-items: center;
  gap: 4px;
}

.nav-btn:hover {
  background: #76a9dfff;
  transform: translateY(-1px);
}

.nav-btn:disabled {
  background: #d6d6d6;
  color: #999;
  cursor: not-allowed;
}

.nav-indicator {
  font-weight: 500;
  color: #333;
  padding: 4px 10px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  min-width: 60px;
  text-align: center;
  font-size: 0.9rem;
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
}
</style>

<div class="navigation-controls">
  <button id="nav-first" class="nav-btn" onclick="goFirst()">
    <i class="bi bi-chevron-double-left"></i> Première
  </button>
  <button id="nav-prev" class="nav-btn" onclick="goPrev()">
    <i class="bi bi-chevron-left"></i> Précédente
  </button>
  
  <div id="nav-indicator" class="nav-indicator">1 / 10</div>
  
  <button id="nav-next" class="nav-btn" onclick="goNext()">
    Suivante <i class="bi bi-chevron-right"></i>
  </button>
  <button id="nav-last" class="nav-btn" onclick="goLast()">
    Dernière <i class="bi bi-chevron-double-right"></i>
  </button>
</div>

                    ` : ''}
                    <div class="print-wrapper">
                        <button id="printToggle-mini" type="button" class="print-toggle-btn">
                            <i class="bi bi-printer"></i>
                            Imprimer PDF
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="print-settings" id="printSettings-mini">
                            <div>
                                <span class="print-label">Impression PDF</span>
                                <div class="print-options">
                                    <label>
                                        <input type="radio" name="printMode-mini" value="bl">
                                        BL
                                    </label>
                                    <label>
                                        <input type="radio" name="printMode-mini" value="facture" checked>
                                        Facture
                                    </label>
                                </div>
                            </div>
                            <div class="print-select" id="printVariantWrapper-mini">
                                <label class="print-label" for="factureVariant-mini">Format facture</label>
                                <select id="factureVariant-mini" class="form-select">
                                    <option value="original" selected>Facture originale</option>
                                    <option value="sans-entete">Facture sans entête</option>
                                </select>
                            </div>
                            <div>
                                <span class="print-label">Options</span>
                                <div class="print-options">
                                    <label>
                                        <input type="checkbox" id="remise5-mini">
                                        Remise 5% max
                                    </label>
                                </div>
                            </div>
                            <button id="printButton-mini" type="button" class="btn btn-primary print-btn">
                                <i class="bi bi-printer"></i>
                                Imprimer PDF
                            </button>
                            <div class="print-help" id="printHelp-mini"></div>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div>
                            <div class="form-row">
                                <label class="form-label">Immobilisation</label>
                                <input type="text" class="form-control" value="${line.IMMOBILISATION || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Société</label>
                                <input type="text" class="form-control" value="${line.SOCIÉTÉ || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Facture</label>
                                <input type="text" class="form-control" value="${line.FACTURE || documentNo || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">N° ligne</label>
                                <input type="text" class="form-control" value="${line.LINE || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Ligne commande de vente</label>
                                <input type="text" class="form-control" value="${line.LIGNE_COMMANDE_DE_VENTE || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Article</label>
                                <input type="text" class="form-control" value="${line.ARTICLE || ''}" readonly>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <label class="form-label">Organisation</label>
                                <input type="text" class="form-control" value="${line.ORGANISATION || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Ligne livraison</label>
                                <input type="text" class="form-control" value="${line.LIGNE_LIVRAISON || ''}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Charge</label>
                                <input type="text" class="form-control" value="${line.CHARGE || ''}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" style="grid-template-columns:160px 1fr;margin-top:16px;">
                        <label class="form-label">Lot</label>
                        <input type="text" class="form-control" value="${line.LOT || ''}" readonly>
                    </div>
                    <div class="form-row" style="grid-template-columns:160px 1fr;">
                        <label class="form-label"></label>
                        <input type="text" class="form-control" value="" placeholder="Répartition ressource">
                    </div>
                    <div class="form-row" style="grid-template-columns:160px 1fr;">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea">${line.DESCRIPTION || ''}</textarea>
                    </div>

                    <div class="section-header"><i class="bi bi-box-seam"></i> Quantités</div>
                    <div class="form-grid">
                        <div>
                            <div class="form-row">
                                <label class="form-label">Quantité</label>
                                <input type="text" class="form-control" value="${line.QTYENTERED || '0'}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Quantité facturée</label>
                                <input type="text" class="form-control" value="${line.QTYINVOICED || '0'}" readonly>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <label class="form-label">Unité</label>
                                <input type="text" class="form-control" value="${line.UNITÉ || ''}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="section-header"><i class="bi bi-currency-dollar"></i> Montants</div>
                    <div class="form-grid">
                        <div>
                            <div class="form-row">
                                <label class="form-label">Prix</label>
                                <input type="text" class="form-control" value="${formatNumber(line.PRICEENTERED)}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Prix unitaire</label>
                                <input type="text" class="form-control" value="${formatNumber(line.PRICEACTUAL)}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">TVA</label>
                                <input type="text" class="form-control" value="${line.TVA || ''}" readonly>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <label class="form-label">Prix tarif</label>
                                <input type="text" class="form-control" value="${formatNumber(line.PRICELIST)}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label">Organisation Trx</label>
                                <input type="text" class="form-control" value="${line.ORGANISATION_TRX || ''}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="section-header"><i class="bi bi-graph-up"></i> Statut</div>
                    <div class="form-grid">
                        <div>
                            <div class="form-row">
                                <label class="form-label">Montant net ligne</label>
                                <input type="text" class="form-control" value="${formatNumber(line.LINENETAMT)}" readonly>
                            </div>
                            <div class="form-row">
                                <label class="form-label"></label>
                                <div class="checkbox-row">
                                    <input type="checkbox" disabled ${line.ISDESCRIPTION === 'Y' ? 'checked' : ''}>
                                    <span>Uniquement description</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label"></label>
                                <div class="checkbox-row">
                                    <input type="checkbox" disabled ${line.REPRICING === 'Y' ? 'checked' : ''}>
                                    <span>Repricing</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="form-label">BL Facturé</label>
                                <input type="text" class="form-control" value="${line.XX_BLINVOICELINE_ID || ''}" readonly>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <label class="form-label"></label>
                                <div class="checkbox-row">
                                    <input type="checkbox" disabled ${line.ISPRINTED === 'Y' ? 'checked' : ''}>
                                    <span>Imprimé</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            initializePrintControls('mini');
            updateNavButtons();
            populateArticleSuggestions(invoiceLines);
            const clearBtnMini = document.getElementById('clearProductSearchMini');
            if (clearBtnMini) clearBtnMini.style.display = currentArticleFilter ? 'inline-block' : 'none';
            const miniFacture = document.getElementById('tab-facture-mini');
            const miniLines = document.getElementById('tab-lines-mini');
            if (miniFacture && miniLines) {
                miniFacture.addEventListener('click', () => { renderInvoice(); });
                miniLines.addEventListener('click', () => {
                    miniLines.classList.add('active');
                    miniFacture.classList.remove('active');
                });
            }
        }

        function initializePrintControls(context) {
            const toggleBtn = document.getElementById(`printToggle-${context}`);
            const settingsPanel = document.getElementById(`printSettings-${context}`);
            const radios = Array.from(document.querySelectorAll(`input[name="printMode-${context}"]`));
            const variantWrapper = document.getElementById(`printVariantWrapper-${context}`);
            const variantSelect = document.getElementById(`factureVariant-${context}`);
            const helpEl = document.getElementById(`printHelp-${context}`);
            const printButton = document.getElementById(`printButton-${context}`);
            if (!toggleBtn || !settingsPanel || !radios.length || !variantWrapper || !variantSelect || !helpEl || !printButton) return;

            // Toggle settings panel
            toggleBtn.addEventListener('click', () => {
                const isShown = settingsPanel.classList.toggle('show');
                toggleBtn.classList.toggle('active', isShown);
            });

            const updateHelp = () => {
                const selectedMode = radios.find(r => r.checked)?.value || 'facture';
                // Always show variant dropdown
                variantWrapper.style.display = '';

                let description;
                if (selectedMode === 'facture') {
                    description = variantSelect.value === 'sans-entete'
                        ? 'Facture sans entête (include_societe=0, modepaiment=auto)'
                        : 'Facture originale (include_societe=1, modepaiment=auto)';
                } else {
                    // BL mode
                    description = variantSelect.value === 'sans-entete'
                        ? 'BL sans entête (include_societe=0, modepaiment=static)'
                        : 'BL original (include_societe=1, modepaiment=static)';
                }
                helpEl.textContent = description;
            };

            radios.forEach(radio => radio.addEventListener('change', updateHelp));
            variantSelect.addEventListener('change', updateHelp);
            printButton.addEventListener('click', () => {
                const selectedMode = radios.find(r => r.checked)?.value || 'facture';
                const remise5Checkbox = document.getElementById(`remise5-${context}`);
                executePrint({
                    mode: selectedMode,
                    variant: variantSelect.value,
                    remise5: remise5Checkbox ? remise5Checkbox.checked : false
                });
            });

            updateHelp();
        }

        function getCurrentDocumentNo() {
            const input = document.getElementById('documentNoInput');
            if (input && input.value && input.value.trim()) {
                return input.value.trim();
            }
            if (currentInvoice && currentInvoice.DOCUMENTNO) {
                return String(currentInvoice.DOCUMENTNO).trim();
            }
            if (currentLine && (currentLine.FACTURE || currentLine.DOCUMENTNO)) {
                return String(currentLine.FACTURE || currentLine.DOCUMENTNO).trim();
            }
            return '';
        }

        function resolveBPartnerId(source) {
            if (!source) return null;
            const candidateKeys = [
                'C_BPARTNER_ID',
                'C_BPartner_ID',
                'C_BPARTNERID',
                'BPARTNER_ID',
                'BPartner_ID',
                'c_bpartner_id',
                'bpartner_id'
            ];
            for (const key of candidateKeys) {
                if (source[key] !== undefined && source[key] !== null && String(source[key]).trim() !== '') {
                    return source[key];
                }
            }
            for (const key of Object.keys(source)) {
                if (key && (key.toLowerCase() === 'c_bpartner_id' || key.toLowerCase() === 'bpartner_id')) {
                    const val = source[key];
                    if (val !== undefined && val !== null && String(val).trim() !== '') {
                        return val;
                    }
                }
            }
            return null;
        }

        function getCurrentBPartnerId() {
            const fromInvoice = resolveBPartnerId(currentInvoice);
            if (fromInvoice !== null && fromInvoice !== undefined) return fromInvoice;
            const fromLine = resolveBPartnerId(currentLine);
            if (fromLine !== null && fromLine !== undefined) return fromLine;
            return '';
        }

        function executePrint({ mode = 'facture', variant = 'original', remise5 = false } = {}) {
            const documentNo = getCurrentDocumentNo();
            const bpartnerId = getCurrentBPartnerId();
            if (!documentNo) {
                alert('Veuillez sélectionner ou charger une facture avant impression.');
                return;
            }
            if (!bpartnerId) {
                alert('Identifiant partenaire indisponible pour cette impression.');
                return;
            }

            let baseUrl = '/download-header-pdf';
            if (API_CONFIG && typeof API_CONFIG.getApiUrl === 'function') {
                baseUrl = API_CONFIG.getApiUrl('/download-header-pdf');
            }

            let url;
            try {
                url = new URL(baseUrl);
            } catch (err) {
                url = new URL(baseUrl, window.location.origin);
            }

            const includeClient = 1;
            let includeSociete = 1;
            // Set includeSociete to 0 for both BL and Facture when variant is 'sans-entete'
            if (variant === 'sans-entete') {
                includeSociete = 0;
            }
            const modepaiment = mode === 'bl' ? 'static' : 'auto';

            url.searchParams.set('include_societe', String(includeSociete));
            url.searchParams.set('include_client', String(includeClient));
            url.searchParams.set('bpartner_id', String(bpartnerId).trim());
            url.searchParams.set('remise5', remise5 ? '1' : '0');
            url.searchParams.set('documentno', String(documentNo).trim());
            url.searchParams.set('modepaiment', modepaiment);

            window.open(url.toString(), '_blank', 'noopener');
        }

        currentInvoice = null;
        renderInvoice();
    </script>
</body>
</html>