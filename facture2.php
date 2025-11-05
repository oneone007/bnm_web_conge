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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root{
            --bg-color: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            --page-bg-flat: #f5f7fa;
            --text-color: #333;
            --container-bg: #ffffff;
            --panel-bg: #f8f9fa;
            --border-color: #e9ecef;
            --muted: #6c757d;
            --accent: #3498db;
            --accent-strong: #2c3e50;
            --success: #27ae60;
            --danger: #c0392b;
        }

    /* Dark theme will be toggled by theme.js which may set data-theme="dark" or add classes on html/body */
    html.dark, body.dark-mode, [data-theme="dark"] {
            --bg-color: #0b0f13; /* fallback flat */
            --page-bg-flat: #0b1020;
            --text-color: #e6eef5;
            --container-bg: #0f1720;
            --panel-bg: #0f1726;
            --border-color: #23303b;
            --muted: #9aa6b2;
            --accent: #2aa7a0;
            --accent-strong: #0f5250;
            --success: #00c27a;
            --danger: #ff6b6b;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Theme-aware overrides for common elements */
    html.dark .container, body.dark-mode .container, [data-theme="dark"] .container { background: var(--container-bg); box-shadow: 0 6px 20px rgba(0,0,0,0.6); }
    html.dark .header, body.dark-mode .header, [data-theme="dark"] .header { background: linear-gradient(135deg,var(--accent-strong) 0%, var(--accent) 100%); }
    html.dark .top-bar, body.dark-mode .top-bar, [data-theme="dark"] .top-bar { background: var(--panel-bg); border-bottom-color: var(--border-color); }
    html.dark .form-input, body.dark-mode .form-input, html.dark .product-search, body.dark-mode .product-search, html.dark .search-input, body.dark-mode .search-input, [data-theme="dark"] .form-input, [data-theme="dark"] .product-search, [data-theme="dark"] .search-input { background: #0b1220 !important; border-color: var(--border-color) !important; color: var(--text-color) !important; }
    html.dark .line-item, body.dark-mode .line-item, [data-theme="dark"] .line-item { background: #0b1220; border-color: var(--border-color); }
    html.dark .line-item.active, body.dark-mode .line-item.active, [data-theme="dark"] .line-item.active { background: linear-gradient(135deg,#12313a22,#0f394020); border-left-color: var(--accent); }
    html.dark .suggestions-container, body.dark-mode .suggestions-container, [data-theme="dark"] .suggestions-container { background: #0b1220; color: var(--text-color); border-color: var(--border-color); }
    html.dark .btn, body.dark-mode .btn, [data-theme="dark"] .btn { background: #15222a; color: var(--text-color); border-color: var(--border-color); }
    html.dark .btn-primary, body.dark-mode .btn-primary, [data-theme="dark"] .btn-primary { background: var(--accent); color: #fff; }

    /* Make section headers, labels and small UI elements dark-theme friendly so
       the emoji headers like 📦 Quantités / 💰 Montants / 📊 Statut are visible */
    html.dark .section-header, body.dark-mode .section-header, [data-theme="dark"] .section-header {
        background: linear-gradient(135deg, rgba(20,26,33,0.6) 0%, rgba(10,14,18,0.6) 100%);
        color: var(--text-color);
        border-left-color: var(--accent);
        box-shadow: none;
    }

    html.dark .form-label, body.dark-mode .form-label, [data-theme="dark"] .form-label {
        color: var(--text-color);
    }

    html.dark .payment-badge, body.dark-mode .payment-badge, [data-theme="dark"] .payment-badge {
        background: #0f1720;
        border-color: rgba(255,255,255,0.04);
        color: var(--text-color);
    }

    html.dark .checkbox-row span, body.dark-mode .checkbox-row span, [data-theme="dark"] .checkbox-row span {
        color: var(--text-color);
    }

    /* Make description textarea dark in dark mode */
    html.dark .form-textarea, body.dark-mode .form-textarea, [data-theme="dark"] .form-textarea {
        background: var(--panel-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    html.dark .form-textarea:focus, body.dark-mode .form-textarea:focus, [data-theme="dark"] .form-textarea:focus {
        background: var(--panel-bg) !important;
        border-color: var(--accent) !important;
        box-shadow: none !important;
        color: var(--text-color) !important;
    }

        /* keep light-mode defaults for non-dark theme */
        .container {
            max-width: calc(100% - 40px);
            width: calc(100% - 40px);
            margin: 0 auto;
            background: var(--container-bg);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
        }

        .container {
            max-width: calc(100% - 40px);
            width: calc(100% - 40px);
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 16px 24px;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header::after {
            content: "📋 Facture Management System";
            font-size: 14px;
            opacity: 0.9;
        }

        .top-bar {
            background: #f8f9fa;
            padding: 16px 24px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-section {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 300px;
        }

        .search-input-wrapper {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid #e1e8ed;
            border-radius: 50px;
            font-size: 14px;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
        }

        .product-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 350px;
        }

        .product-search {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid #e1e8ed;
            border-radius: 50px;
            font-size: 14px;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .product-search:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.2);
            transform: translateY(-2px);
        }

        .product-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
        }

        .tabs {
            display: flex;
            gap: 8px;
            background: #e9ecef;
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
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: white;
            color: #3498db;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-content {
            padding: 32px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
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
            font-size: 14px;
            color: #495057;
            font-weight: 500;
        }

        .form-input {
            padding: 10px 14px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #3498db;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-input.editable {
            background-color: white;
            border-color: #dee2e6;
        }

        .form-textarea {
            padding: 10px 14px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            background-color: #f8f9fa;
            resize: vertical;
            min-height: 80px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #3498db;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .payment-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 2px solid #e1e8ed;
            background-color: white;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .section-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 14px 20px;
            margin: 32px 0 20px 0;
            font-size: 16px;
            font-weight: 600;
            border-left: 5px solid #3498db;
            border-radius: 0 8px 8px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .button-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 32px;
        }

        .button-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        :root {
            /* Neutral/professional button palette (overrides earlier blue defaults) */
            --btn-bg: #ffffff;
            --btn-bg-hover: #f8fafc;
            --btn-text: #0f172a;
            --btn-border: 1px solid #e6e9ee;
            --btn-box-shadow: none;
            --btn-box-shadow-hover: none;
        }

        .btn, .btn-primary, .btn-success, .btn-warning, .btn-danger {
            padding: 12px 20px;
            border: var(--btn-border, none);
            background: var(--btn-bg);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.12s ease;
            border-radius: 8px;
            color: var(--btn-text);
            box-shadow: var(--btn-box-shadow);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover, .btn-primary:hover, .btn-success:hover, .btn-warning:hover, .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: var(--btn-box-shadow-hover);
            background: var(--btn-bg-hover);
        }

        /* Local override: ensure .button-grid buttons are styled from PHP so changes persist
           even if external CSS files are reverted. Placed here inside the page template. */
        .button-grid .btn, .button-grid button, .button-column .btn {
            background: linear-gradient(180deg,#fbfdff 0%,#f3f6f9 100%) !important;
            color: #0f172a !important;
            border: 1px solid #d1d5db !important;
            box-shadow: 0 2px 6px rgba(16,24,40,0.06) !important;
            width: 100% !important;
            padding: 12px 18px !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .button-grid .btn:hover, .button-grid button:hover, .button-column .btn:hover {
            background: linear-gradient(180deg,#f3f6fb 0%,#e9eef6 100%) !important;
            transform: translateY(-2px) !important;
        }

        /* Close button: allow icon via data-icon attribute */
        .btn-close::before {
            content: attr(data-icon);
            display: inline-block;
            margin-right: 8px;
            font-size: 16px;
            line-height: 1;
            vertical-align: middle;
            opacity: 0.95;
        }
        .btn-close .icon { display: none; }

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
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .icon {
            color: #3498db;
        }

        .status-message {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
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

        /* Enhanced suggestion dropdown */
        .suggestions-container {
            position: absolute;
            z-index: 9999;
            background: white;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            max-height: 280px;
            overflow: auto;
            width: 100%;
            font-size: 14px;
            margin-top: 8px;
            animation: dropIn 0.3s ease-out;
        }

        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover, .suggestion-item.active {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding-left: 20px;
        }

        .suggestion-item::before {
            content: "📦";
            font-size: 16px;
        }

        .line-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f3f4;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .line-item:hover {
            background: #f8f9fa;
            transform: translateX(4px);
        }

        .line-item.active {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #3498db;
            font-weight: 500;
        }

        .line-number {
            font-weight: 600;
            color: #3498db;
            margin-bottom: 4px;
        }

        .navigation-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 16px;
        }

        .nav-btn {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .nav-btn:hover:not(:disabled) {
            background: #e9ecef;
            transform: translateY(-1px);
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
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .top-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .search-section, .product-search-wrapper {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .form-label {
                text-align: left;
                margin-bottom: 4px;
            }
            
            .button-grid {
                grid-template-columns: 1fr;
            }
            
            .form-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div id="app"></div>

    <script>
        const API_CONFIG = {
            getApiUrl: (endpoint) => `http://192.168.1.94:5000${endpoint}`
        };

        let currentInvoice = null;
    let invoiceLines = [];
    // keep an unmodified copy of fetched lines to restore after clearing filters
    let originalInvoiceLines = [];
    let currentLine = null;
    let currentIndex = 0;
    // active article filter string (when set, invoiceLines is the filtered set)
    let currentArticleFilter = '';

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('fr-FR');
        }

        function formatNumber(num) {
            // Handle empty/null/undefined
            if (num === null || num === undefined || num === '' || isNaN(num)) return '0.00';

            const n = Number(num);
            // Use Intl.NumberFormat to format with space as thousands separator and dot as decimal separator.
            try {
                return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
            } catch (e) {
                // Fallback: manual formatting
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
                <div class="container">
                    <div class="header"></div>

                    <!-- Top params bar: search + tabs -->
                    <div class="top-bar">
                        <div class="search-section">
                            <div class="search-input-wrapper">
                                <span class="search-icon">🔍</span>
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
                            <button id="tab-lines" class="tab-btn">Facture lines</button>
                        </div>
                        <div class="product-search-wrapper">
                            <span class="product-search-icon">📦</span>
                            <input type="text" id="productSearch" placeholder="Rechercher article..." class="product-search" onchange="onProductSelected()" oninput="handleProductInput(event)" value="${currentArticleFilter || ''}">
                            <button id="clearProductSearch" class="clear-search" style="display:none;position:absolute;right:8px;top:8px;padding:6px 8px;border-radius:8px;border:0;background:#eee;cursor:pointer" type="button" onclick="(function(){ document.getElementById('productSearch').value=''; applyArticleFilter(''); hideSuggestions(); })()">✖</button>
                            <div id="suggestions" class="suggestions-container" style="display:none;"></div>
                        </div>
                        <div id="searchStatus"></div>
                    </div>

                    <div id="mainLayout" style="display:flex;">
                        <!-- Left Sidebar (lines list only) -->
                        <div id="leftSidebarWrapper" style="width:320px;min-width:280px;border-right:1px solid #e9ecef;padding:16px;background:#f8f9fa;">
                            <div id="linesSidebar" style="position:sticky; top:16px; z-index:2; background:#f8f9fa; padding:12px; max-height:calc(100vh - 140px); overflow:auto;">
                                <div id="linesList" style="max-height:400px;overflow:auto;border:1px solid #e9ecef;background:white;padding:12px;border-radius:8px;"></div>
                            </div>
                        </div>

                        <!-- Right Main -->
                        <div id="rightMain" style="flex:1;padding:24px;">
                            <div class="form-content">
                                <div class="form-grid">
                                    <!-- Left Column (invoice header fields) -->
                                    <div>
                                        <div class="form-row">
                                            <label class="form-label">Société</label>
                                            <input type="text" class="form-input" value="${inv.SOCIÉTÉ || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Ordre de vente</label>
                                            <input type="text" class="form-input" value="${inv.ORDRE_DE_VENTE || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">N° Facture</label>
                                            <input type="text" class="form-input" value="${inv.DOCUMENTNO || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-textarea" readonly>${inv.DESCRIPTION || ''}</textarea>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Code journal</label>
                                            <input type="text" class="form-input" value="${inv.CODE_JOURNAL || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Date facturation</label>
                                            <input type="text" class="form-input" value="${formatDate(inv.DATEINVOICED)}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Client</label>
                                            <input type="text" class="form-input" value="${inv.CLIENT || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Contact</label>
                                            <input type="text" class="form-input" value="${inv.CONTACT || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Tarif</label>
                                            <input type="text" class="form-input" value="${inv.TARIF || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Vendeur</label>
                                            <input type="text" class="form-input" value="${inv.VENDEUR || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Méthode de paiement</label>
                                            <div class="payment-badge">
                                                <span class="icon">💳</span>
                                                <span>${inv.PAYMENTRULELABEL || ''}</span>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Sous méthode de paiement</label>
                                            <input type="text" class="form-input" value="${inv.SOUS_METHODE_DE_PAIEMENT || ''}" readonly>
                                        </div>
                                    </div>

                                    <!-- Right Column (other header fields + small line detail) -->
                                    <div>
                                        <div class="form-row">
                                            <label class="form-label">Organisation</label>
                                            <input type="text" class="form-input" value="${inv.ORGANISATION || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Date commande</label>
                                            <input type="text" class="form-input" value="${formatDate(inv.DATEORDERED)}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Référence</label>
                                            <input type="text" class="form-input editable" value="${inv.POREFERENCE || ''}">
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
                                            <input type="text" class="form-input" value="${formatDate(inv.DATEACCT)}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Adresse du tiers</label>
                                            <input type="text" class="form-input" value="SETIF_-_${inv.CLIENT || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Devise</label>
                                            <input type="text" class="form-input" value="${inv.DEVISE || ''}" readonly>
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
                                            <input type="text" class="form-input" value="${inv.DELAI_DE_PAIEMENT || ''}" readonly>
                                        </div>

                                        <div class="form-row">
                                            <label class="form-label">Organisation Trx</label>
                                            <input type="text" class="form-input" value="${inv.ORGANISATION_TRX || ''}" readonly>
                                        </div>

                                        <!-- Line detail placeholder removed (not needed) -->
                                    </div>
                                </div>

                                <!-- Status Section -->
                                <div class="section-header">📊 Statut de la facture</div>

                                <div class="form-grid">
                                    <div>
                                        <div class="form-row">
                                            <label class="form-label">Total lignes</label>
                                            <input type="text" class="form-input" value="${formatNumber(inv.TOTALLINES)}" readonly>
                                        </div>

                                <div class="form-row">
                                    <label class="form-label">Statut document</label>
                                    <input type="text" class="form-input" value="${inv.STATUT_DOCUMENT || ''}" readonly>
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
                                        <span class="icon">✓</span>
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
                                    <input type="text" class="form-input" value="${formatNumber(inv.GRANDTOTAL)}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Type document</label>
                                    <input type="text" class="form-input" value="${inv.TYPE_DOCUMENT || ''}" readonly>
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
                                        <!-- Preserve original close-button placement and style, but show ACTION_STATUS as label -->
                                        <button class="btn btn-close" data-icon="⚙" type="button" style="padding:8px 12px;min-width:140px;justify-content:center;">
                                            ${inv.ACTION_STATUS || inv.Action_Status || inv.DOCACTION || ''}
                                        </button>
                                        <!-- Gear remains next to the status so it's visually in the same area as before -->
                                    </div>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Collection Status</label>
                                    <input type="text" class="form-input editable" value="">
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

                        <!-- Action Buttons -->
                        <div class="button-grid">
                            <div class="button-column">
                                <button class="btn btn-primary">📋 Copier lignes</button>
                                <button class="btn btn-success">💰 Appliquer Remise Globale</button>
                                <button class="btn btn-warning">📄 Generer Avoir Financier</button>
                                <button class="btn">🔄 Restorer Facture</button>
                                <button class="btn btn-warning">✏️ Corriger facture</button>
                                <button class="btn btn-success">📝 Generate avoir</button>
                            </div>
                            <div class="button-column">
                                <button class="btn btn-danger">🔧 Correction totale générale</button>
                                <button class="btn btn-warning">🧾 XX_CorrectTVA</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add Enter key listener for search input
            document.getElementById('documentNoInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    searchInvoice();
                }
            });

            // populate datalists for product search in this newly rendered DOM
            populateArticleSuggestions(invoiceLines);
            // show clear button if filter active
            const clearBtnMain = document.getElementById('clearProductSearch');
            if (clearBtnMain) clearBtnMain.style.display = currentArticleFilter ? 'inline-block' : 'none';

            // Wire top tabs
            const tabFacture = document.getElementById('tab-facture');
            const tabLines = document.getElementById('tab-lines');
            const leftSidebarWrapper = document.getElementById('leftSidebarWrapper');
            const rightMain = document.getElementById('rightMain');

            function showSidebar(show) {
                if (!leftSidebarWrapper || !rightMain) return;
                if (show) {
                    leftSidebarWrapper.style.display = '';
                    rightMain.style.paddingLeft = '16px';
                } else {
                    leftSidebarWrapper.style.display = 'none';
                    rightMain.style.paddingLeft = '8px';
                }
            }

            if (tabFacture && tabLines) {
                tabFacture.addEventListener('click', () => {
                    // Invoice view: hide sidebar to give full width to invoice
                    showSidebar(false);
                    renderInvoice();
                    tabFacture.classList.add('active');
                    tabLines.classList.remove('active');
                });
                tabLines.addEventListener('click', () => {
                    const docNo = document.getElementById('documentNoInput').value;
                    // Lines view: show sidebar
                    showSidebar(true);
                    loadInvoiceLines(docNo, true);
                    tabLines.classList.add('active');
                    tabFacture.classList.remove('active');
                });

                // Initial state: Facture active (hide sidebar)
                showSidebar(false);
                tabFacture.classList.add('active');
            }
        }

        async function loadInvoiceLines(documentNo, showOnly = false) {
            if (!documentNo || !documentNo.trim()) return;
            const listEl = document.getElementById('linesList');
            if (!listEl) return;
            listEl.innerHTML = '<div class="loader">Chargement des lignes...</div>';

            try {
                const url = `${API_CONFIG.getApiUrl('/facture-lines')}?documentno=${encodeURIComponent(documentNo)}`;
                console.info('fetching invoice lines from', url);
                let r;
                try {
                    r = await fetch(url);
                } catch (e) {
                    console.warn('Primary API fetch failed, will try relative /facture-lines fallback', e);
                }

                if (!r || !r.ok) {
                    // Try fallback to same-origin endpoint
                    const fallback = `/facture-lines?documentno=${encodeURIComponent(documentNo)}`;
                    console.info('fetching invoice lines from fallback', fallback);
                    r = await fetch(fallback);
                }

                if (!r.ok) throw new Error('Network response was not ok');
                const data = await r.json();
                const lines = Array.isArray(data) ? data : (data.lines || []);
                // set global state and keep an original copy for restoring after filter clear
                originalInvoiceLines = Array.isArray(lines) ? lines.slice() : [];
                invoiceLines = originalInvoiceLines.slice();
                currentIndex = 0;
                currentLine = invoiceLines[0] || null;
                if (lines.length === 0) {
                    listEl.innerHTML = '<div style="padding:12px;color:#666">Aucune ligne</div>';
                    return;
                }

                // populate article suggestions for product search (do it early so lines-only view can use it)
                populateArticleSuggestions(invoiceLines);
                // if a filter is active, apply it so the sidebar reflects filtered items
                if (currentArticleFilter) { applyArticleFilter(currentArticleFilter); return; }

                if (lines.length === 0) {
                    listEl.innerHTML = '<div style="padding:12px;color:#666">Aucune ligne</div>';
                    return;
                }

                if (showOnly) {
                    // if a filter is active, apply it before rendering
                    if (currentArticleFilter) applyArticleFilter(currentArticleFilter);
                    renderLinesOnly(invoiceLines, documentNo);
                    return;
                }

                // Render list in the sidebar (left column)
                listEl.innerHTML = '';
                invoiceLines.forEach((ln, idx) => {
                    const div = document.createElement('div');
                    div.className = 'line-item';
                    div.style.padding = '8px';
                    div.style.borderBottom = '1px solid #f0f0f0';
                    div.style.cursor = 'pointer';
                    div.innerHTML = `<div style="font-weight:bold;color:#4a7c59">Ligne ${ln.LINE || idx+1}</div><div style="font-size:13px">${ln.ARTICLE || ln.DESCRIPTION || ''}</div>`;
                    div.addEventListener('click', () => { selectLine(idx); });
                    listEl.appendChild(div);
                });
                // populate article suggestions for product search
                populateArticleSuggestions(invoiceLines);

            } catch (err) {
                console.error('Error loading lines', err);
                listEl.innerHTML = '<div style="padding:12px;color:#b91c1c">Erreur en chargeant les lignes</div>';
            }
        }

        // renderSelectedLineDetail removed — not used when line detail placeholder was removed

        // Populate datalist with unique article suggestions from invoice lines
        function populateArticleSuggestions(lines) {
            try {
                const suggestions = Array.from(new Set((lines || []).map(l => (l.ARTICLE || l.DESCRIPTION || '').trim()).filter(Boolean)));
                const dl = document.getElementById('articleSuggestions');
                const dlMini = document.getElementById('articleSuggestionsMini');
                if (dl) {
                    dl.innerHTML = '';
                    suggestions.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s;
                        dl.appendChild(opt);
                    });
                }
                if (dlMini) {
                    dlMini.innerHTML = '';
                    suggestions.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s;
                        dlMini.appendChild(opt);
                    });
                }
            } catch (e) {
                console.warn('populateArticleSuggestions error', e);
            }
        }

        // Apply an article filter. When `article` is provided it becomes the active filter.
        // If empty string is passed the filter is cleared and the full list restored.
        function applyArticleFilter(article, exact = false) {
            try {
                if (typeof article === 'string') currentArticleFilter = article.trim();
                if (!originalInvoiceLines || !originalInvoiceLines.length) return;

                if (!currentArticleFilter) {
                    invoiceLines = originalInvoiceLines.slice();
                } else {
                    const f = currentArticleFilter.toLowerCase();
                    if (exact) {
                        invoiceLines = originalInvoiceLines.filter(l => ((l.ARTICLE || l.DESCRIPTION || '') + '').toLowerCase() === f);
                    } else {
                        invoiceLines = originalInvoiceLines.filter(l => ((l.ARTICLE || l.DESCRIPTION || '') + '').toLowerCase().includes(f));
                    }
                }

                currentIndex = 0;
                currentLine = invoiceLines[0] || null;

                // update sidebar list if present
                const listEl = document.getElementById('linesList');
                if (listEl) {
                    listEl.innerHTML = '';
                    if (!invoiceLines.length) {
                        listEl.innerHTML = '<div style="padding:12px;color:#666">Aucune ligne</div>';
                    } else {
                        invoiceLines.forEach((ln, idx) => {
                            const div = document.createElement('div');
                            div.className = 'line-item';
                            div.style.padding = '8px';
                            div.style.borderBottom = '1px solid #f0f0f0';
                            div.style.cursor = 'pointer';
                            div.innerHTML = `<div style="font-weight:bold;color:#4a7c59">Ligne ${ln.LINE || idx+1}</div><div style="font-size:13px">${ln.ARTICLE || ln.DESCRIPTION || ''}</div>`;
                            div.addEventListener('click', () => { selectLine(idx); });
                            listEl.appendChild(div);
                        });
                    }
                }

                // if lines-only view is active, re-render it
                if (document.getElementById('linesListContainer')) {
                    const docNo = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
                    renderLinesOnly(invoiceLines, docNo);
                } else {
                    // otherwise just select first filtered line to show details
                    if (invoiceLines.length) selectLine(0);
                }

                // show/hide clear buttons
                const cb = document.getElementById('clearProductSearch'); if (cb) cb.style.display = currentArticleFilter ? 'inline-block' : 'none';
                const cbMini = document.getElementById('clearProductSearchMini'); if (cbMini) cbMini.style.display = currentArticleFilter ? 'inline-block' : 'none';
                updateNavButtons();
            } catch (e) {
                console.warn('applyArticleFilter error', e);
            }
        }

        function showTemporaryStatus(msg, mini = false, isError = false) {
            try {
                const el = document.getElementById(mini ? 'searchStatusMini' : 'searchStatus');
                if (!el) return;
                el.textContent = msg;
                el.className = isError ? 'status-message status-error' : 'status-message status-success';
                setTimeout(() => {
                    el.textContent = '';
                    el.className = '';
                }, 3000);
            } catch (e) {
                console.warn('showTemporaryStatus error', e);
            }
        }

        // Open a small settings dialog for the invoice action status.
        // This currently updates the client-side invoice object only (no server persistence).
        function openInvoiceSettings() {
            try {
                const inv = currentInvoice || {};
                const current = inv.ACTION_STATUS || inv.Action_Status || inv.DOCACTION || '';
                const newVal = prompt("Modifier l'Action (ex: Annuler, Corriger, Réserver, Traiter, Clôturer). Valeur actuelle: " + current, current);
                if (newVal === null) return; // user cancelled
                // apply locally and re-render. Persisting to server can be added later if needed.
                if (currentInvoice) {
                    currentInvoice.ACTION_STATUS = newVal;
                }
                showTemporaryStatus('Statut mis à jour localement: ' + newVal, false, false);
                renderInvoice();
            } catch (e) {
                console.warn('openInvoiceSettings error', e);
                showTemporaryStatus('Erreur en ouvrant les paramètres', false, true);
            }
        }

        function onProductSelected() {
            const v = document.getElementById('productSearch').value;
            if (!v) return;
            const doc = document.getElementById('documentNoInput') ? document.getElementById('documentNoInput').value : '';
            // ensure lines are loaded, then apply the filter which will show only matching lines
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

        // When user types, if we don't have suggestions loaded, try to fetch lines first
        async function handleProductInput() {
            const input = document.getElementById('productSearch');
            if (!input) return;
            const v = input.value.trim();
            // populate lines if missing
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

        // Renders custom suggestion dropdown based on current invoiceLines. If mini=true use suggestionsMini
        function renderSuggestions(filter, mini = false) {
            const container = document.getElementById(mini ? 'suggestionsMini' : 'suggestions');
            const input = document.getElementById(mini ? 'productSearchMini' : 'productSearch');
            if (!container || !input) return;
            const f = (filter || '').toLowerCase();
            // build list from invoiceLines ARTICLE/DESCRIPTION
            const items = Array.from(new Set((invoiceLines || []).map(l => (l.ARTICLE || l.DESCRIPTION || '').trim()).filter(Boolean)));
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
                    hideSuggestions(mini);
                    // apply as a persistent exact filter and select first matching line
                    applyArticleFilter(m, true);
                    if (invoiceLines && invoiceLines.length) selectLine(0);
                    else showTemporaryStatus('Article non trouvé dans les lignes chargées', mini, true);
                });
                container.appendChild(div);
            });
            // position and show
            container.style.display = 'block';
            // try to set width to input width
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
            // showOnly = true -> open a minimal view that contains only the lines
            loadInvoiceLines(docNo, true);
        }

        function selectLine(index) {
            currentIndex = index;
            currentLine = invoiceLines[index];
            renderLinesOnly(invoiceLines, currentLine ? currentLine.FACTURE : '');
        }

        function goFirst() {
            if (!invoiceLines.length) return;
            selectLine(0);
        }

        function goPrev() {
            if (!invoiceLines.length) return;
            if (currentIndex > 0) selectLine(currentIndex - 1);
        }

        function goNext() {
            if (!invoiceLines.length) return;
            if (currentIndex < invoiceLines.length - 1) selectLine(currentIndex + 1);
        }

        function goLast() {
            if (!invoiceLines.length) return;
            selectLine(invoiceLines.length - 1);
        }

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
            // Show the full detailed line layout (similar to facture2.php)
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
                <div class="container">
                    <div class="header"></div>

                    <!-- Top params bar: search + tabs (same as invoice view) -->
                    <div class="top-bar">
                        <div class="search-section">
                            <div class="search-input-wrapper">
                                <span class="search-icon">🔍</span>
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
                            <span class="product-search-icon">📦</span>
                            <input type="text" id="productSearchMini" placeholder="Rechercher article..." class="product-search" onchange="onProductSelectedMini()" oninput="handleProductInputMini(event)" value="${currentArticleFilter || ''}">
                            <button id="clearProductSearchMini" class="clear-search" style="display:none;position:absolute;right:8px;top:8px;padding:6px 8px;border-radius:8px;border:0;background:#eee;cursor:pointer" type="button" onclick="(function(){ document.getElementById('productSearchMini').value=''; applyArticleFilter(''); hideSuggestions(true); })()">✖</button>
                            <div id="suggestionsMini" class="suggestions-container" style="display:none;"></div>
                        </div>
                        <div id="searchStatusMini"></div>
                    </div>

                    <div class="form-content">
                        ${invoiceLines.length > 1 ? `
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;">
                                <div>
                                    <button id="nav-first" class="btn" onclick="goFirst()">|&lt; First</button>
                                    <button id="nav-prev" class="btn" onclick="goPrev()">&lt; Prev</button>
                                    <button id="nav-next" class="btn" onclick="goNext()">Next &gt;</button>
                                    <button id="nav-last" class="btn" onclick="goLast()">Last &gt;|</button>
                                </div>
                                <div id="nav-indicator" style="font-size:13px;color:#333">${currentIndex + 1} / ${invoiceLines.length}</div>
                            </div>
                        ` : ''}
                        <div class="form-grid">
                            <!-- Left Column -->
                            <div>
                                <div class="form-row">
                                    <label class="form-label">Immobilisation</label>
                                    <input type="text" class="form-input" value="${line.IMMOBILISATION || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Société</label>
                                    <input type="text" class="form-input" value="${line.SOCIÉTÉ || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Facture</label>
                                    <input type="text" class="form-input" value="${line.FACTURE || documentNo || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">N° ligne</label>
                                    <input type="text" class="form-input" value="${line.LINE || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Ligne commande de vente</label>
                                    <input type="text" class="form-input" value="${line.LIGNE_COMMANDE_DE_VENTE || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Article</label>
                                    <input type="text" class="form-input" value="${line.ARTICLE || ''}" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <div class="form-row">
                                    <label class="form-label">Organisation</label>
                                    <input type="text" class="form-input" value="${line.ORGANISATION || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Ligne livraison</label>
                                    <input type="text" class="form-input" value="${line.LIGNE_LIVRAISON || ''}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Charge</label>
                                    <input type="text" class="form-input" value="${line.CHARGE || ''}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="form-row full-width-row">
                            <label class="form-label">Lot</label>
                            <input type="text" class="form-input" value="${line.LOT || ''}" readonly>
                        </div>

                        <div class="form-row full-width-row">
                            <label class="form-label"></label>
                            <input type="text" class="form-input editable" value="" placeholder="Répartition ressource">
                        </div>

                        <div class="form-row full-width-row">
                            <label class="form-label">Description</label>
                            <textarea class="form-textarea">${line.DESCRIPTION || ''}</textarea>
                        </div>

                        <!-- Quantités Section -->
                        <div class="section-header">📦 Quantités</div>

                        <div class="form-grid">
                            <div>
                                <div class="form-row">
                                    <label class="form-label">Quantité</label>
                                    <input type="text" class="form-input" value="${line.QTYENTERED || '0'}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Quantité facturée</label>
                                    <input type="text" class="form-input" value="${line.QTYINVOICED || '0'}" readonly>
                                </div>
                            </div>

                            <div>
                                <div class="form-row">
                                    <label class="form-label">Unité</label>
                                    <input type="text" class="form-input" value="${line.UNITÉ || ''}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Montants Section -->
                        <div class="section-header">💰 Montants</div>

                        <div class="form-grid">
                            <div>
                                <div class="form-row">
                                    <label class="form-label">Prix</label>
                                    <input type="text" class="form-input" value="${formatNumber(line.PRICEENTERED)}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Prix unitaire</label>
                                    <input type="text" class="form-input" value="${formatNumber(line.PRICEACTUAL)}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">TVA</label>
                                    <input type="text" class="form-input" value="${line.TVA || ''}" readonly>
                                </div>
                            </div>

                            <div>
                                <div class="form-row">
                                    <label class="form-label">Prix tarif</label>
                                    <input type="text" class="form-input" value="${formatNumber(line.PRICELIST)}" readonly>
                                </div>

                                <div class="form-row">
                                    <label class="form-label">Organisation Trx</label>
                                    <input type="text" class="form-input" value="${line.ORGANISATION_TRX || ''}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Statut Section -->
                        <div class="section-header">📊 Statut</div>

                        <div class="form-grid">
                            <div>
                                <div class="form-row">
                                    <label class="form-label">Montant net ligne</label>
                                    <input type="text" class="form-input" value="${formatNumber(line.LINENETAMT)}" readonly>
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
                                    <input type="text" class="form-input" value="${line.XX_BLINVOICELINE_ID || ''}" readonly>
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
                </div>
            `;

            // After rendering, update nav buttons state
            updateNavButtons();
            // populate datalists for product search in the lines-only DOM
            populateArticleSuggestions(invoiceLines);
            const clearBtnMini = document.getElementById('clearProductSearchMini');
            if (clearBtnMini) clearBtnMini.style.display = currentArticleFilter ? 'inline-block' : 'none';
            // Wire mini tabs
            const miniFacture = document.getElementById('tab-facture-mini');
            const miniLines = document.getElementById('tab-lines-mini');
            if (miniFacture && miniLines) {
                miniFacture.addEventListener('click', () => {
                    renderInvoice();
                });
                miniLines.addEventListener('click', () => {
                    // already on lines view; keep classes
                    miniLines.classList.add('active');
                    miniFacture.classList.remove('active');
                });
            }
        }

        // Initialize
        currentInvoice = null;
        renderInvoice();
    </script>
</body>
</html>