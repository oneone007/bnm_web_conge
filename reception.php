<?php
session_start();
// Force page identifier for permissions system
$page_identifier = 'Arrivage';
require_once __DIR__ . '/check_permission.php';

// Check user permissions for admin-only actions in UI
$isAdmin = false;
if (isset($_SESSION['username']) && isset($_SESSION['Role'])) {
    $isAdmin = (
      $_SESSION['username'] === 'hichem' ||
      $_SESSION['Role'] === 'Developer' ||
      $_SESSION['Role'] === 'Sup Achat' ||
      $_SESSION['Role'] === 'Sup Vente'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📦 Arrivages par date</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
  <script src="api_config.js"></script>
  <style>
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --light-bg: #f8f9fa;
      --dark-bg: #111827;
      --dark-card: #1f2937;
      --dark-accent: #59869c;
      --text-light: #f8f9fa;
      --text-dark: #343a40;
      --card-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    body {
      background: #f4f6f9;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      color: var(--text-dark);
    }
    body.dark-mode {
      background: var(--dark-bg);
      color: var(--text-light);
    }

    .container { margin-top: 30px; max-width: 1400px; }

    .page-header {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 1.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--secondary-color);
    }
    body.dark-mode .page-header {
      color: var(--dark-accent);
      border-bottom: 2px solid var(--dark-accent);
    }

    .filter-section {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.5rem;
      border: 1px solid #e9ecef;
    }
    body.dark-mode .filter-section {
      background: var(--dark-card);
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      border: 1px solid #333;
      color: var(--text-light);
    }

    .filter-title { font-weight: 600; margin-bottom: 1rem; font-size: 1.1rem; }

    .card {
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 15px;
      border: 1px solid #e9ecef;
      transition: transform 0.2s;
    }
    body.dark-mode .card {
      background: var(--dark-card);
      color: var(--text-light);
      border: 1px solid #333;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .card:hover { transform: translateY(-3px); }

    .card-header {
      cursor: pointer;
      font-weight: 600;
      font-size: 1.1rem;
      background: linear-gradient(to right, #ffffff, #f8f9fa);
      border-bottom: 1px solid #e9ecef;
      padding: 1rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 12px 12px 0 0;
    }
    body.dark-mode .card-header {
      background: linear-gradient(to right, #2a2a2a, var(--dark-card));
      border-bottom: 1px solid #333;
      color: var(--text-light);
    }

    .card-header .bi { transition: transform 0.3s; }
    .card-header.collapsed .bi-chevron-down { transform: rotate(-90deg); }

    table {
      margin-top: 10px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      border-collapse: collapse;
    }
    body.dark-mode table {
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    table th, table td {
      vertical-align: middle !important;
      text-align: center;
      padding: 0.75rem;
      border: 1px solid #e9ecef;
    }
    body.dark-mode table th,
    body.dark-mode table td {
      border: 1px solid #333;
    }
    table thead {
      background: var(--primary-color);
      color: #fff;
    }
    body.dark-mode table thead {
      background: var(--dark-accent);
      color: #fff;
    }
    table tbody tr:nth-child(even) {
      background: #f8f9fa;
    }
    body.dark-mode table tbody tr:nth-child(even) {
      background: #2a2a2a;
    }
    table tbody tr:hover {
      background: rgba(52, 152, 219, 0.05);
    }
    body.dark-mode table tbody tr:hover {
      background: rgba(89, 134, 156, 0.15);
    }

    /* Override Bootstrap table styles for dark mode */
    body.dark-mode .table tbody tr {
      background-color: var(--dark-card);
      color: var(--text-light);
    }
    body.dark-mode .table-striped tbody tr:nth-child(even) {
      background-color: #2a2a2a;
    }

    /* Dark mode: list group (suppliers pane) */
    body.dark-mode .list-group-item {
      background-color: var(--dark-card);
      color: var(--text-light);
      border-color: #333;
    }
    body.dark-mode .list-group-item.active {
      background-color: var(--dark-accent) !important;
      border-color: var(--dark-accent) !important;
      color: #fff !important;
    }

    .btn-primary {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
      border-radius: 8px;
      font-weight: 500;
      padding: 0.5rem 1.25rem;
      color: white;
    }
    .btn-primary:hover {
      background-color: #2980b9;
      border-color: #2980b9;
      color: white;
    }
    body.dark-mode .btn-primary {
      background-color: var(--dark-accent);
      border-color: var(--dark-accent);
    }
    body.dark-mode .btn-primary:hover {
      background-color: #4a6b7a;
      border-color: #4a6b7a;
    }

    .btn-secondary {
      border-radius: 8px;
      font-weight: 500;
      padding: 0.5rem 1.25rem;
      background-color: #6c757d;
      border-color: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
      border-color: #5a6268;
      color: white;
    }
    body.dark-mode .btn-secondary {
      background-color: #495057;
      border-color: #495057;
    }
    body.dark-mode .btn-secondary:hover {
      background-color: #3d4145;
      border-color: #3d4145;
    }

    .btn-action { display: flex; align-items: center; gap: 0.5rem; }

    .stats-bar {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }
    .stat-card {
      background: white;
      border-radius: 8px;
      padding: 0.75rem 1rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      min-width: 120px;
      border: 1px solid #e9ecef;
    }
    body.dark-mode .stat-card {
      background: var(--dark-card);
      color: var(--text-light);
      border: 1px solid #333;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .stat-value { font-size: 1.25rem; font-weight: 600; color: var(--primary-color); }
    body.dark-mode .stat-value { color: var(--dark-accent); }
    .stat-label { font-size: 0.8rem; color: #6c757d; }
    body.dark-mode .stat-label { color: #bbb; }

    .form-label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--text-dark);
    }
    body.dark-mode .form-label {
      color: var(--text-light);
    }

    .form-control {
      border-radius: 8px;
      border: 1px solid #ced4da;
      padding: 0.5rem 0.75rem;
    }
    .form-control:focus {
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    body.dark-mode .form-control {
      background-color: #2a2a2a;
      border-color: #495057;
      color: var(--text-light);
    }
    body.dark-mode .form-control:focus {
      border-color: var(--dark-accent);
      box-shadow: 0 0 0 0.2rem rgba(89, 134, 156, 0.25);
    }

    /* Fix for text-muted in dark mode */
    .text-muted {
      color: #6c757d !important;
    }
    body.dark-mode .text-muted {
      color: #adb5bd !important;
    }

    /* Fix table background in dark mode */
    .table {
      background-color: transparent;
    }
    body.dark-mode .table {
      background-color: var(--dark-card);
      color: var(--text-light);
    }
    /* Ensure table cells don't stay white in dark mode */
    body.dark-mode .table > :not(caption) > * > * { /* th, td */
      background-color: transparent !important;
      color: var(--text-light);
    }
    /* Ensure wrappers use dark background when expanded */
    body.dark-mode .table-responsive,
    body.dark-mode .card-body,
    body.dark-mode .collapse,
    body.dark-mode .collapse.show {
      background-color: var(--dark-card) !important;
    }
    body.dark-mode .table-striped tbody tr:nth-child(odd) {
      background-color: var(--dark-card);
    }
    body.dark-mode .table-striped tbody tr:nth-child(even) {
      background-color: #2a2a2a;
    }

    /* Suggestions dropdown styles */
    .suggestions-list {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #ced4da;
      border-top: none;
      border-radius: 0 0 8px 8px;
      max-height: 200px;
      overflow-y: auto;
      z-index: 1000;
      display: none;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    body.dark-mode .suggestions-list {
      background: var(--dark-card);
      border-color: #495057;
      color: var(--text-light);
    }
    .suggestion-item {
      padding: 0.5rem 0.75rem;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
    }
    body.dark-mode .suggestion-item {
      border-bottom-color: #333;
    }
    .suggestion-item:hover {
      background: #f8f9fa;
    }
    body.dark-mode .suggestion-item:hover {
      background: #2a2a2a;
    }
    .suggestion-item:last-child {
      border-bottom: none;
    }

    /* Loading spinner */
    .loading-spinner {
      display: none;
      text-align: center;
      margin: 2rem 0;
    }
    .loading-spinner::after {
      content: "";
      display: inline-block;
      width: 40px;
      height: 40px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid var(--secondary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Selectable rows */
    .selectable-row {
      cursor: pointer;
      transition: background-color 0.2s ease;
    }
    .selectable-row:hover {
      background-color: #e3f2fd !important;
    }
    body.dark-mode .selectable-row:hover {
      background-color: #1a237e !important;
    }
    .selectable-row.table-info {
      background-color: #d1ecf1 !important;
    }
    body.dark-mode .selectable-row.table-info {
      background-color: #0d47a1 !important;
    }
    
    /* ==== Professional Theme Overrides (Non-breaking) ==== */
    /* Palette refresh */
    :root {
      --pro-primary: #2563eb; /* blue-600 */
      --pro-primary-700: #1d4ed8; /* blue-700 */
      --pro-slate-600: #475569; /* slate-600 */
      --pro-slate-700: #334155; /* slate-700 */
      --pro-slate-800: #1f2937; /* slate-800 */
      --pro-green: #059669; /* green-600 */
      --pro-amber: #d97706; /* amber-600 */
      --pro-rose: #e11d48; /* rose-600 */
      --pro-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
      --pro-shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }

    /* Container breathing room */
    .container { max-width: 1280px; }

    /* Header refinement */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .75rem;
      padding-bottom: 1rem;
      margin-bottom: 1.75rem;
      border-bottom-width: 3px;
      border-image: linear-gradient(90deg, var(--pro-primary), transparent) 1;
    }
    .page-header .bi { color: var(--pro-primary); }
    body.dark-mode .page-header .bi { color: #93c5fd; }

    /* Filter card polish */
    .filter-section {
      border-radius: 16px;
      box-shadow: var(--pro-shadow);
      border: 1px solid rgba(0,0,0,0.06);
    }
    body.dark-mode .filter-section { border-color: rgba(255,255,255,0.06); }

    /* Tabs: make them look like pills */
    .nav-tabs { border-bottom-color: rgba(0,0,0,0.08); }
    .nav-tabs .nav-link {
      border: 1px solid transparent;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
      color: var(--pro-slate-600);
      background: #fff;
    }
    .nav-tabs .nav-link:hover { color: var(--pro-primary); background: #f1f5f9; }
    .nav-tabs .nav-link.active {
      background: linear-gradient(135deg, var(--pro-primary), var(--pro-primary-700));
      color: #fff;
      box-shadow: 0 6px 18px rgba(37,99,235,0.25);
    }
    body.dark-mode .nav-tabs .nav-link { background: var(--dark-card); color: #cbd5e1; }
    body.dark-mode .nav-tabs .nav-link.active { background: linear-gradient(135deg, #334155, #1f2937); }

    /* Card and header tidy up */
    .card { border-radius: 16px; box-shadow: var(--pro-shadow); }
    .card:hover { box-shadow: var(--pro-shadow-lg); transform: translateY(-2px); }
    .card-header {
      border-radius: 16px 16px 0 0;
      background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(168,85,247,0.08));
    }
    body.dark-mode .card-header { background: linear-gradient(135deg, rgba(51,65,85,0.6), rgba(30,41,59,0.6)); }

    /* Table visual hierarchy */
    table { border-spacing: 0; border-collapse: separate; }
    table thead { background: linear-gradient(135deg, var(--pro-primary), var(--pro-primary-700)); }
    table thead th { font-size: .85rem; letter-spacing: .04em; }
    table tbody tr { transition: background .15s ease, transform .1s ease; }
    table tbody tr:hover { background: rgba(37,99,235,0.06); }
    body.dark-mode table tbody tr:hover { background: rgba(148,163,184,0.12); }

    /* Buttons: consistent look */
    .btn { border-radius: 12px; font-weight: 600; }
    .btn-primary { background: linear-gradient(135deg, var(--pro-primary), var(--pro-primary-700)); border: none; }
    .btn-primary:hover { filter: brightness(1.05); transform: translateY(-1px); }
    .btn-secondary { background: linear-gradient(135deg, #64748b, #475569); border: none; }
    .btn-secondary:hover { filter: brightness(1.05); transform: translateY(-1px); }
    .btn-success { background: linear-gradient(135deg, var(--pro-green), #047857); border: none; }
    .btn-danger { background: linear-gradient(135deg, var(--pro-rose), #be123c); border: none; }

    /* Stats cards elevated */
    .stats-bar { gap: 1.25rem; }
    .stat-card {
      border-radius: 14px;
      box-shadow: var(--pro-shadow);
      border: 1px solid rgba(0,0,0,0.06);
    }
    .stat-value { font-size: 1.75rem; color: var(--pro-primary); }
    body.dark-mode .stat-value { color: #bfdbfe; }
    .stat-label { color: var(--pro-slate-700); }
    body.dark-mode .stat-label { color: #94a3b8; }

    /* Inputs */
    .form-control { border-radius: 12px; border-width: 2px; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(37,99,235,0.12); }
    body.dark-mode .form-control { border-color: #334155; }

    /* Badges */
    .badge { border-radius: 10px; font-weight: 700; padding: .4rem .6rem; }
    .bg-info { background: linear-gradient(135deg, #0ea5e9, #0284c7)!important; }
    .bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706)!important; }
    .bg-primary { background: linear-gradient(135deg, var(--pro-primary), var(--pro-primary-700))!important; }

    /* Alerts */
    .alert { border-radius: 12px; border: none; box-shadow: var(--pro-shadow); }
    .alert-success { background: rgba(5,150,105,0.1); color: #065f46; }
    .alert-danger { background: rgba(225,29,72,0.1); color: #9f1239; }
    .alert-info { background: rgba(14,165,233,0.12); color: #075985; }

    /* PDF Button Loading State */
    .btn-loading {
      pointer-events: none;
      opacity: 0.6;
    }
    .btn-loading .spinner-border {
      width: 1rem;
      height: 1rem;
    }
  </style>
  <script src="simple_tracker.js"></script>
</head>
<body>
<div class="container-fluid">
  <h2 class="page-header text-center">📦 Arrivages par date</h2>

  <!-- Navigation Tabs -->
  <ul class="nav nav-tabs" id="mainTabs" role="tablist">
    <li class="nav-item" role="presentation">
  <button class="nav-link active" id="reception-tab" data-bs-toggle="tab" data-bs-target="#reception" type="button" role="tab" aria-controls="reception" aria-selected="true">Arrivage</button>
    </li>
    <?php if ($isAdmin): ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="false">Admin</button>
    </li>
    <?php endif; ?>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content" id="mainTabContent">
    <!-- Reception Tab -->
    <div class="tab-pane fade show active" id="reception" role="tabpanel" aria-labelledby="reception-tab">
      <!-- Filters -->
      <div class="filter-section">
        <div class="row align-items-end">
          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" id="start_date" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" id="end_date" class="form-control">
          </div>
          <div class="col-md-6 d-flex gap-2 justify-content-md-end">
            <button id="fetchBtn" class="btn btn-primary btn-action"><i class="bi bi-arrow-repeat"></i> Fetch Data</button>
            <button id="downloadPdfBtn" class="btn btn-success btn-action" disabled><i class="bi bi-file-pdf"></i> Download PDF</button>
          </div>
        </div>
      </div>

      <!-- Stats Summary -->
      <div id="statsContainer" class="stats-bar"></div>

      <!-- Loading Spinner -->
      <div id="loadingSpinner" class="loading-spinner my-4"></div>

      <!-- Results: Master-Detail Layout -->
      <div id="results">
        <div class="row g-3">
          <div class="col-lg-4">
            <div class="card h-100">
              <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-people"></i> <span>Suppliers</span>
              </div>
              <div class="card-body p-0">
                <div id="suppliersList" class="list-group list-group-flush"></div>
              </div>
            </div>
          </div>
          <div class="col-lg-8">
            <div id="productsPanel" class="card h-100">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                  <span id="selectedSupplierName" class="fw-semibold">Products</span>
                  <small id="selectedSupplierMeta" class="text-muted"></small>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div id="supplierBadges" class="d-flex gap-2"></div>
                  <?php if ($isAdmin): ?>
                  <button id="toggleAdminViewBtn" class="btn btn-outline-secondary btn-sm" title="Toggle admin view">
                    <i class="bi bi-eye-slash"></i> How others see
                  </button>
                  <?php endif; ?>
                  <button id="showAllBtn" class="btn btn-secondary btn-sm" title="Show all products across suppliers">
                    <i class="bi bi-list-ul"></i> Show All
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div id="productsContainer"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Admin Tab -->
    <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">
      <div class="filter-section">
        <div class="row">
          <div class="col-md-6">
            <label class="form-label">Search Product</label>
            <div style="position: relative;">
              <input type="text" id="productSearch" class="form-control" placeholder="Type to search products..." autocomplete="off">
              <div id="productSuggestions" class="suggestions-list"></div>
            </div>
            <small id="searchStatus" class="text-muted">Loading products...</small>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <button id="searchProductBtn" class="btn btn-primary btn-action"><i class="bi bi-search"></i> Search</button>
            <button id="testApiBtn" class="btn btn-secondary btn-action"><i class="bi bi-gear"></i> Test API</button>
          </div>
        </div>
      </div>

      <!-- Product Details -->
      <div id="productDetailsContainer"></div>

      <!-- Added Products Section -->
      <div id="addedProductsSection" style="display: none;">
        <div class="card mt-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Added Products to Reception</h5>
            <button id="refreshAddedBtn" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
          </div>
          <div class="card-body">
            <div id="addedProductsContainer">
              <div class="loading-spinner"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
$(document).ready(function () {
  // Admin status from PHP
  const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
  // Runtime admin view toggle (starts same as isAdmin). When false, simulates non-admin view.
  let adminView = isAdmin;
  
  // Default today date
  let today = new Date().toISOString().split("T")[0];
  $("#start_date").val(today);
  $("#end_date").val(today);

  // Global variables for admin tab
  let selectedProductId = null;
  let productsList = [];

  // Load products list for autocomplete
  function loadProductsList() {
    console.log('Loading products list...');
    $('#searchStatus').text('Loading products...');
  $.getJSON(window.API_CONFIG.getApiUrl('/listproduct_inv'), function(data) {
      console.log('Products list loaded:', data);
      if (!data.error) {
        productsList = data;
        console.log('Products list set:', productsList.length, 'products');
        $('#searchStatus').text(`Loaded ${productsList.length} products. Start typing to search...`);
      } else {
        console.error('Error in products list:', data.error);
        $('#searchStatus').text('Error loading products. Check console.');
      }
    }).fail(function(jqXHR, textStatus, errorThrown) {
      console.error('Failed to load products list:', textStatus, errorThrown);
      $('#searchStatus').text('Failed to load products. Check API connection.');
    });
  }

  // Initialize products list
  loadProductsList();

  // Load added products on page load
  loadAddedProducts();

  // Refresh added products button
  $('#refreshAddedBtn').on('click', function() {
    loadAddedProducts();
  });

  // Product search functionality
  $('#productSearch').on('input', function() {
    const query = $(this).val().toLowerCase();
    const $suggestions = $('#productSuggestions');

    console.log('Search input triggered, query:', query);
    console.log('Products list length:', productsList.length);

    if (query.length < 2) {
      $suggestions.hide();
      return;
    }

    const filteredProducts = productsList.filter(product =>
      product.name.toLowerCase().includes(query)
    ).slice(0, 10); // Limit to 10 suggestions

    console.log('Filtered products:', filteredProducts.length);

    if (filteredProducts.length > 0) {
      const suggestionsHtml = filteredProducts.map(product =>
        `<div class="suggestion-item" data-id="${product.id}" data-name="${product.name}">${product.name}</div>`
      ).join('');
      $suggestions.html(suggestionsHtml).show();
      console.log('Suggestions shown');
    } else {
      $suggestions.hide();
      console.log('No suggestions to show');
    }
  });

  // Handle suggestion click
  $(document).on('click', '.suggestion-item', function() {
    const productId = $(this).data('id');
    const productName = $(this).data('name');
    $('#productSearch').val(productName);
    selectedProductId = productId;
    $('#productSuggestions').hide();
  });

  // Hide suggestions when clicking outside
  $(document).on('click', function(e) {
    if (!$(e.target).closest('#productSearch, #productSuggestions').length) {
      $('#productSuggestions').hide();
    }
  });

  // Search product button click
  $('#searchProductBtn').click(function() {
    if (!selectedProductId) {
      alert('Please select a product from the suggestions');
      return;
    }

    // Always use "preparation" category
    fetchProductData(selectedProductId, 'preparation');
  });

  // Test API button click
  $('#testApiBtn').click(function() {
    console.log('Testing API connection...');
  $.getJSON(window.API_CONFIG.getApiUrl('/listproduct_inv'), function(data) {
      console.log('API test successful:', data);
      alert('API is working! Check console for details.');
    }).fail(function(jqXHR, textStatus, errorThrown) {
      console.error('API test failed:', textStatus, errorThrown);
      alert('API test failed! Check console for details.');
    });
  });

  // Global variable to store selected row data
  let selectedRowData = null;

  // Fetch product data function
  function fetchProductData(productId, category) {
    $('#productDetailsContainer').html('<div class="loading-spinner"></div>');

  $.getJSON(window.API_CONFIG.getApiUrl(`/details-products?product_id=${productId}&category=preparation`), function(data) {
      $('#productDetailsContainer').empty();

      if (data.error) {
        $('#productDetailsContainer').html(`<div class="alert alert-danger">${data.error}</div>`);
        return;
      }

      if (data.length === 0) {
        $('#productDetailsContainer').html(`<div class="alert alert-info">No inventory data found for this product.</div>`);
        return;
      }

      // Create table with selectable rows
      const tableHtml = `
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Inventory Details</h5>
            <button id="addSelectedBtn" class="btn btn-success btn-sm" disabled>
              <i class="bi bi-plus-circle"></i> Add Selected
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Select</th>
                    <th>Product</th>
                    <th>LOT</th>
                    <th>Description</th>
                    <th>PPA</th>
                    <th>Qty Dispo</th>
                  </tr>
                </thead>
                <tbody>
                  ${data.map((item, index) => `
                    <tr class="selectable-row" data-row-data='${JSON.stringify(item)}'>
                      <td>
                        <input type="radio" name="selectedRow" value="${index}" class="form-check-input">
                      </td>
                      <td>${item.PRODUCT || '-'}</td>
                      <td>${item.LOT || '-'}</td>
                      <td>${item.DESCRIPTION || '-'}</td>
                      <td>${item.PPA || '-'}</td>
                      <td>${item.QTY_DISPO || '-'}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;

      $('#productDetailsContainer').html(tableHtml);

      // Add row selection functionality
      $('.selectable-row').on('click', function() {
        // Uncheck all other radio buttons
        $('input[name="selectedRow"]').prop('checked', false);
        
        // Check this row's radio button
        $(this).find('input[name="selectedRow"]').prop('checked', true);
        
        // Remove previous selection highlighting
        $('.selectable-row').removeClass('table-info');
        
        // Highlight selected row
        $(this).addClass('table-info');
        
        // Store selected row data
        selectedRowData = JSON.parse($(this).attr('data-row-data'));
        
        // Enable the add button
        $('#addSelectedBtn').prop('disabled', false);
        
        console.log('Selected row data:', selectedRowData);
      });

      // Handle radio button clicks
      $('input[name="selectedRow"]').on('click', function(e) {
        e.stopPropagation();
        $(this).closest('.selectable-row').click();
      });

      // Handle add selected button click
      $('#addSelectedBtn').on('click', function() {
        if (selectedRowData) {
          addToFakeReception(selectedRowData);
        }
      });

    }).fail(function() {
      $('#productDetailsContainer').html(`<div class="alert alert-danger">Failed to fetch product data.</div>`);
    });
  }

  // Function to add selected data to fake_reception table
  function addToFakeReception(rowData) {
    const dataToSend = {
      m_attributesetinstance_id: rowData.M_ATTRIBUTESSETINSTANCE_ID,
      product_id: selectedProductId,
      product_name: rowData.PRODUCT
    };

    console.log('Sending data to fake_reception:', dataToSend);

    // Show loading state
    $('#addSelectedBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Adding...');

    $.ajax({
      url: window.API_CONFIG.getApiUrl('/insert_fake_reception'),
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(dataToSend),
      success: function(response) {
        console.log('Success response:', response);
        
        // Show success message
        const successAlert = `
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> Successfully added to reception!
            <strong>ID:</strong> ${response.inserted_id}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        `;
        $('#productDetailsContainer').prepend(successAlert);
        
        // Reset button
        $('#addSelectedBtn').prop('disabled', false).html('<i class="bi bi-plus-circle"></i> Add Selected');
        
        // Clear selection
        selectedRowData = null;
        $('.selectable-row').removeClass('table-info');
        $('input[name="selectedRow"]').prop('checked', false);
        $('#addSelectedBtn').prop('disabled', true);
        
        // Reload added products list
        loadAddedProducts();
        // Also refresh the main reception data since it's now merged
        fetchData();
      },
      error: function(xhr, status, error) {
        console.error('Error response:', xhr.responseJSON);
        
        // Show error message
        const errorAlert = `
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> Failed to add to reception!
            <strong>Error:</strong> ${xhr.responseJSON?.error || 'Unknown error'}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        `;
        $('#productDetailsContainer').prepend(errorAlert);
        
        // Reset button
        $('#addSelectedBtn').prop('disabled', false).html('<i class="bi bi-plus-circle"></i> Add Selected');
      }
    });
  }

  // Function to load added products from fake_reception table
  function loadAddedProducts() {
    $('#addedProductsContainer').html('<div class="loading-spinner"></div>');
    $('#addedProductsSection').show();

  $.getJSON(window.API_CONFIG.getApiUrl('/get_fake_reception'), function(response) {
      $('#addedProductsContainer').empty();

      if (response.error) {
        $('#addedProductsContainer').html(`<div class="alert alert-danger">${response.error}</div>`);
        return;
      }

      if (!response.data || response.data.length === 0) {
        $('#addedProductsContainer').html(`
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No products added to reception yet.
          </div>
        `);
        return;
      }

      // Create table with added products
      const tableHtml = `
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Attribute Instance ID</th>
                <th>Product ID</th>
                <th>Added Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              ${response.data.map(item => `
                <tr id="row-${item.id}">
                  <td>${item.id}</td>
                  <td><strong>${item.product_name}</strong></td>
                  <td>${item.m_attributesetinstance_id || '-'}</td>
                  <td>${item.product_id}</td>
                  <td>${formatDateTime(item.created_at)}</td>
                  <td>
                    <button class="btn btn-danger btn-sm delete-product-btn" data-id="${item.id}" data-name="${item.product_name}">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
          <div class="mt-2">
            <small class="text-muted">
              <i class="bi bi-info-circle"></i> Total: ${response.count} products in reception
            </small>
          </div>
        </div>
      `;

      $('#addedProductsContainer').html(tableHtml);

      // Bind delete button events
      $('.delete-product-btn').on('click', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        deleteProduct(productId, productName);
      });

    }).fail(function() {
      $('#addedProductsContainer').html(`
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> Failed to load added products.
        </div>
      `);
    });
  }

  // Function to delete a product from fake_reception
  function deleteProduct(productId, productName) {
    if (!confirm(`Are you sure you want to delete "${productName}" from reception?`)) {
      return;
    }

    const deleteBtn = $(`.delete-product-btn[data-id="${productId}"]`);
    deleteBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Deleting...');

    $.ajax({
      url: window.API_CONFIG.getApiUrl(`/delete_fake_reception/${productId}`),
      method: 'DELETE',
      success: function(response) {
        console.log('Delete success:', response);
        
        // Remove the row with animation
        $(`#row-${productId}`).fadeOut(300, function() {
          $(this).remove();
          
          // Show success message
          const successAlert = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle"></i> "${productName}" deleted successfully!
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          `;
          $('#addedProductsContainer').prepend(successAlert);
          
          // Auto dismiss after 3 seconds
          setTimeout(function() {
            $('.alert-success').fadeOut();
          }, 3000);
          
          // Reload the list to update count
          setTimeout(function() {
            loadAddedProducts();
            // Also refresh the main reception data since it's now merged
            fetchData();
          }, 1000);
        });
      },
      error: function(xhr, status, error) {
        console.error('Delete error:', xhr.responseJSON);
        
        // Reset button
        deleteBtn.prop('disabled', false).html('<i class="bi bi-trash"></i> Delete');
        
        // Show error message
        const errorAlert = `
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> Failed to delete "${productName}"!
            <strong>Error:</strong> ${xhr.responseJSON?.error || 'Unknown error'}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        `;
        $('#addedProductsContainer').prepend(errorAlert);
      }
    });
  }

  // Helper function to format datetime
  function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  // State for master-detail view
  let mergedDataState = [];
  let selectedSupplierIndex = 0;
  let showAllProducts = false;

  function renderSuppliersList() {
    const $list = $('#suppliersList');
    $list.empty();
    if (!mergedDataState || mergedDataState.length === 0) {
      $list.append('<div class="list-group-item">No suppliers found.</div>');
      $('#selectedSupplierName').text('Products');
      $('#selectedSupplierMeta').text('');
      $('#supplierBadges').empty();
      $('#productsContainer').html('<div class="alert alert-info">No products to display.</div>');
      return;
    }

    mergedDataState.forEach((supplier, idx) => {
      const productCount = supplier.PRODUCTS?.length || 0;
      let realCount = 0, fakeCount = 0;
      supplier.PRODUCTS.forEach(p => p.FAKE_RECEPTION_ID ? fakeCount++ : realCount++);
      const hasMixed = realCount > 0 && fakeCount > 0;
      const onlyFake = fakeCount > 0 && realCount === 0;

      const activeClass = idx === selectedSupplierIndex ? 'active' : '';
      const badge = `<span class="badge bg-secondary ms-2">${productCount}</span>`;
  const adminBadges = adminView
        ? (hasMixed ? '<span class="badge bg-info ms-2">Mixed</span>'
            : onlyFake ? '<span class="badge bg-warning ms-2">Added</span>'
            : '<span class="badge bg-primary ms-2">Real</span>')
        : '';

      const item = $(`
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ${activeClass}" data-index="${idx}">
          <span class="text-truncate" style="max-width: 75%;">${supplier.SUPPLIER_NAME}</span>
          <span class="d-flex align-items-center">${badge}${adminBadges}</span>
        </button>
      `);

      item.on('click', function() {
        selectedSupplierIndex = Number($(this).data('index'));
        // Exit Show All mode when a supplier is chosen
        showAllProducts = false;
        const $btn = $('#showAllBtn');
        if ($btn.length) $btn.html('<i class="bi bi-list-ul"></i> Show All');
        renderSuppliersList();
        renderProductsForSelected();
      });

      $list.append(item);
    });
  }

  function renderProductsForSelected() {
    if (showAllProducts) {
      return renderAllProducts();
    }
    if (!mergedDataState || mergedDataState.length === 0) {
      $('#productsContainer').html('<div class="alert alert-info">No products to display.</div>');
      return;
    }
    const supplier = mergedDataState[selectedSupplierIndex];
    if (!supplier) { $('#productsContainer').html('<div class="alert alert-info">No products to display.</div>'); return; }

    const productCount = supplier.PRODUCTS?.length || 0;
    let realCount = 0, fakeCount = 0;
    supplier.PRODUCTS.forEach(p => p.FAKE_RECEPTION_ID ? fakeCount++ : realCount++);
    const hasMixed = realCount > 0 && fakeCount > 0;
    const onlyFake = fakeCount > 0 && realCount === 0;

    $('#selectedSupplierName').text(supplier.SUPPLIER_NAME);
    $('#selectedSupplierMeta').text(`(${productCount} products)`);
    const $badges = $('#supplierBadges');
    $badges.empty();
  if (adminView) {
      if (hasMixed) $badges.append('<span class="badge bg-info">Mixed Data</span>');
      else if (onlyFake) $badges.append('<span class="badge bg-warning">Added Products</span>');
      else $badges.append('<span class="badge bg-primary">Real Reception</span>');
    }

    if (productCount === 0) {
      $('#productsContainer').html('<div class="alert alert-info">No products for this supplier.</div>');
      return;
    }

    const tableHtml = `
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead>
            <tr>
              <th rowspan="2">Product</th>
              <th rowspan="2">Bonus</th>
              <th rowspan="2">Qty Dispo</th>
              <th colspan="2">Rem Vente</th>
              ${adminView ? '<th rowspan="2">Actions</th>' : ''}
            </tr>
            <tr>
              <th>Rem Pot</th>
              <th>Rem Para</th>
            </tr>
          </thead>
          <tbody>
            ${supplier.PRODUCTS.map(prod => {
              const bonusVal = (prod.BONUS && prod.BONUS.toLowerCase() !== 'normal') ? prod.BONUS : (prod.BON_VENTE ?? '-');
              const remPot = prod.REM_POT ?? (prod.REM_VENTE ?? '-');
              const remPara = prod.REM_PARA ?? (prod.REM_VENTE ?? '-');
              const actions = prod.FAKE_RECEPTION_ID && adminView
                ? `<button class="btn btn-danger btn-sm delete-fake-product" data-id="${prod.FAKE_RECEPTION_ID}" data-name="${prod.PRODUCT_NAME}"><i class="bi bi-trash"></i></button>`
                : (adminView ? '-' : '');
              return `
                <tr>
                  <td>${prod.PRODUCT_NAME ?? '-'}</td>
                  <td>${bonusVal}</td>
                  <td>${prod.QTY_DISPO ?? '-'}</td>
                  <td>${remPot}</td>
                  <td>${remPara}</td>
                  ${isAdmin ? `<td>${actions}</td>` : ''}
                </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;

    $('#productsContainer').html(tableHtml);

    // Bind delete handlers
    $('.delete-fake-product').on('click', function() {
      const productId = $(this).data('id');
      const productName = $(this).data('name');
      if (!confirm(`Are you sure you want to delete "${productName}" from reception?`)) return;
      const prevSupplierName = supplier.SUPPLIER_NAME;
      $.ajax({
        url: window.API_CONFIG.getApiUrl(`/delete_fake_reception/${productId}`),
        method: 'DELETE',
        success: function() {
          // Re-fetch but keep selection by supplier name
          fetchData(prevSupplierName);
        },
        error: function(xhr) {
          alert('Failed to delete product: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
      });
    });
  }

  // Render all products from all suppliers in a single table
  function renderAllProducts() {
    const allRows = [];
    let totalCount = 0;
    (mergedDataState || []).forEach(supplier => {
      (supplier.PRODUCTS || []).forEach(prod => {
        totalCount++;
        allRows.push({ supplier: supplier.SUPPLIER_NAME, prod });
      });
    });

    $('#selectedSupplierName').text('All Products');
    $('#selectedSupplierMeta').text(`(${totalCount} items)`);
    $('#supplierBadges').empty();

    if (totalCount === 0) {
      $('#productsContainer').html('<div class="alert alert-info">No products available.</div>');
      return;
    }

    const rowsHtml = allRows.map(({ supplier, prod }) => {
      const bonusVal = (prod.BONUS && prod.BONUS.toLowerCase() !== 'normal') ? prod.BONUS : (prod.BON_VENTE ?? '-');
      const remPot = prod.REM_POT ?? (prod.REM_VENTE ?? '-');
      const remPara = prod.REM_PARA ?? (prod.REM_VENTE ?? '-');
      const actions = prod.FAKE_RECEPTION_ID && adminView
        ? `<button class="btn btn-danger btn-sm delete-fake-product" data-id="${prod.FAKE_RECEPTION_ID}" data-name="${prod.PRODUCT_NAME}"><i class=\"bi bi-trash\"></i></button>`
        : (adminView ? '-' : '');
      return `
        <tr>
          <td class="text-start">${supplier}</td>
          <td>${prod.PRODUCT_NAME ?? '-'}</td>
          <td>${bonusVal}</td>
          <td>${prod.QTY_DISPO ?? '-'}</td>
          <td>${remPot}</td>
          <td>${remPara}</td>
          ${isAdmin ? `<td>${actions}</td>` : ''}
        </tr>`;
    }).join('');

    const tableHtml = `
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead>
            <tr>
              <th rowspan="2">Supplier</th>
              <th rowspan="2">Product</th>
              <th rowspan="2">Bonus</th>
              <th rowspan="2">Qty Dispo</th>
              <th colspan="2">Rem Vente</th>
              ${adminView ? '<th rowspan="2">Actions</th>' : ''}
            </tr>
            <tr>
              <th>Rem Pot</th>
              <th>Rem Para</th>
            </tr>
          </thead>
          <tbody>${rowsHtml}</tbody>
        </table>
      </div>
    `;

    $('#productsContainer').html(tableHtml);

    // Bind delete handlers (stay in All view and preserve selection)
    $('.delete-fake-product').on('click', function() {
      const productId = $(this).data('id');
      const productName = $(this).data('name');
      if (!confirm(`Are you sure you want to delete "${productName}" from reception?`)) return;
      $.ajax({
        url: window.API_CONFIG.getApiUrl(`/delete_fake_reception/${productId}`),
        method: 'DELETE',
        success: function() {
          // Refresh and keep All view
          const keepAll = showAllProducts;
          fetchData();
          showAllProducts = keepAll;
          // Rendering will happen in fetchData completion
        },
        error: function(xhr) {
          alert('Failed to delete product: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
      });
    });
  }

  function fetchData(preferSupplierName = null) {
    $("#loadingSpinner").css("display", "block");
    $("#statsContainer").html("");
    // Preserve layout; just clear panes
    $('#suppliersList').empty();
    $('#productsContainer').empty();
    let start = $("#start_date").val();
    let end = $("#end_date").val();

    $.getJSON(window.API_CONFIG.getApiUrl(`/recieved_products_bydate?start_date=${start}&end_date=${end}`), function (response) {
      $("#loadingSpinner").css("display", "none");
      $('#suppliersList').empty();
      $('#productsContainer').empty();

      if (response.error) { 
        $('#productsContainer').html(`<div class="alert alert-danger">${response.error}</div>`); 
        $('#downloadPdfBtn').prop('disabled', true);
        return; 
      }

      // Extract data from the new merged response structure
      const mergedData = response.reception_data || [];
      
      if (mergedData.length === 0) { 
        $('#suppliersList').html('<div class="list-group-item">No suppliers found.</div>');
        $('#productsContainer').html(`<div class="alert alert-info">No products found in this date range.</div>`); 
        $('#downloadPdfBtn').prop('disabled', true);
        return; 
      }

      // Stats - count real and fake products within merged data
      let totalSuppliers = mergedData.length;
      let totalProducts = 0;
      let realProducts = 0;
      let fakeProducts = 0;
      
      mergedData.forEach(supplier => {
        supplier.PRODUCTS.forEach(product => {
          totalProducts++;
          if (product.FAKE_RECEPTION_ID) {
            fakeProducts++;
          } else {
            realProducts++;
          }
        });
      });
      
      $("#statsContainer").html(`
        <div class="stat-card"><span class="stat-value">${totalSuppliers}</span><span class="stat-label">Suppliers</span></div>
        <div class="stat-card"><span class="stat-value">${totalProducts}</span><span class="stat-label">Total Products</span></div>
        ${adminView ? `<div class="stat-card"><span class="stat-value">${realProducts}</span><span class="stat-label">Real Reception</span></div>` : ''}
        ${adminView ? `<div class="stat-card"><span class="stat-value">${fakeProducts}</span><span class="stat-label">Added Products</span></div>` : ''}
      `);
      // Save state and render master-detail view
      const prevName = preferSupplierName;
      mergedDataState = mergedData;
      // Choose default selection: previous supplier if present, else first
      if (prevName) {
        const idx = mergedDataState.findIndex(s => s.SUPPLIER_NAME === prevName);
        selectedSupplierIndex = idx >= 0 ? idx : 0;
      } else {
        selectedSupplierIndex = 0;
      }
      renderSuppliersList();
      if (showAllProducts) {
        renderAllProducts();
      } else {
        renderProductsForSelected();
      }
      
      // Enable PDF download button since we have data
      $('#downloadPdfBtn').prop('disabled', false);
    }).fail(function () {
      $("#loadingSpinner").css("display", "none");
      $('#productsContainer').html(`<div class="alert alert-danger">Failed to fetch data.</div>`);
      $('#downloadPdfBtn').prop('disabled', true);
    });
  }

  $("#fetchBtn").click(fetchData);

  // Toggle simulated non-admin view
  $(document).on('click', '#toggleAdminViewBtn', function() {
    if (!isAdmin) return; // safety
    adminView = !adminView;
    // Update button label/icon
    if (adminView) {
      $(this).html('<i class="bi bi-eye-slash"></i> How others see');
      // Show Admin tab again if it exists
      $('#admin-tab').closest('li').show();
    } else {
      $(this).html('<i class="bi bi-eye"></i> Back to admin');
      // Hide Admin tab content/label while simulating
      $('#admin-tab').closest('li').hide();
      if ($('#admin').hasClass('active')) {
        $('#reception-tab').trigger('click');
      }
    }
    // Re-render suppliers/products with new adminView mode without refetching
    renderSuppliersList();
    if (showAllProducts) renderAllProducts(); else renderProductsForSelected();
    // Rebuild stats from current state
    // Quick recompute using last mergedDataState
    let totalSuppliers = mergedDataState.length;
    let totalProducts = 0, realProducts = 0, fakeProducts = 0;
    mergedDataState.forEach(s => (s.PRODUCTS||[]).forEach(p => { totalProducts++; p.FAKE_RECEPTION_ID ? fakeProducts++ : realProducts++; }));
    $("#statsContainer").html(`
      <div class="stat-card"><span class="stat-value">${totalSuppliers}</span><span class="stat-label">Suppliers</span></div>
      <div class="stat-card"><span class="stat-value">${totalProducts}</span><span class="stat-label">Total Products</span></div>
      ${adminView ? `<div class="stat-card"><span class="stat-value">${realProducts}</span><span class="stat-label">Real Reception</span></div>` : ''}
      ${adminView ? `<div class="stat-card"><span class="stat-value">${fakeProducts}</span><span class="stat-label">Added Products</span></div>` : ''}
    `);
  });

  // Toggle show all products
  $(document).on('click', '#showAllBtn', function() {
    showAllProducts = !showAllProducts;
    // Update button text/icon
    if (showAllProducts) {
      $(this).html('<i class="bi bi-list-ul"></i> Showing All');
    } else {
      $(this).html('<i class="bi bi-list-ul"></i> Show All');
    }
    // Render accordingly
    if (showAllProducts) {
      renderAllProducts();
    } else {
      renderProductsForSelected();
    }
  });

  // PDF Download functionality
  function generatePDF() {
    if (!mergedDataState || mergedDataState.length === 0) {
      alert('No data available to download. Please fetch data first.');
      return;
    }

    // Check if jsPDF is loaded
    if (typeof window.jspdf === 'undefined') {
      console.error('jsPDF library not loaded');
      alert('PDF library not loaded. Please refresh the page and try again.');
      return;
    }

    try {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      
      const startDate = $("#start_date").val();
      const endDate = $("#end_date").val();
      
      // Format dates for display
      const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric' 
        });
      };
      
      const formattedStartDate = formatDate(startDate);
      const formattedEndDate = formatDate(endDate);
      
      // Determine title and filename
      let titleDate = '';
      let filenameDate = '';
      
      if (startDate === endDate) {
        titleDate = formattedStartDate;
        filenameDate = startDate;
      } else {
        titleDate = `Du ${formattedStartDate} au ${formattedEndDate}`;
        filenameDate = `${startDate}_au_${endDate}`;
      }

      // Add logo (if it exists, otherwise skip)
      const logoImg = new Image();
      logoImg.onload = function() {
        // Logo loaded successfully, add it to PDF
        doc.addImage(logoImg, 'PNG', 15, 10, 30, 20);
        addContentToPDF();
      };
      logoImg.onerror = function() {
        // Logo failed to load, continue without it
        console.warn('Logo not found, continuing without logo');
        addContentToPDF();
      };
      logoImg.src = 'assets/log.png';

      function addContentToPDF() {
        try {
          // Page setup
          const pageWidth = doc.internal.pageSize.getWidth();
          const pageHeight = doc.internal.pageSize.getHeight();
          const margin = 20;
          
          // Header background rectangle
          doc.setFillColor(44, 62, 80); // Dark blue background
          doc.rect(0, 0, pageWidth, 70, 'F');
          
          // Logo positioning (top-left of header) - better positioning and size
          let logoLoaded = false;
          if (logoImg.complete && logoImg.naturalWidth > 0) {
            doc.addImage(logoImg, 'PNG', margin, 15, 40, 30);
            logoLoaded = true;
          }
          
          // Company name and system title (centered in header)
          doc.setTextColor(255, 255, 255); // White text
          doc.setFontSize(26);
          doc.setFont('helvetica', 'bold');
          doc.text('BNM ANALYSE SYSTEM', pageWidth / 2, 30, { align: 'center' });
          
          doc.setFontSize(14);
          doc.setFont('helvetica', 'normal');
          doc.text('Gestion des Arrivages', pageWidth / 2, 45, { align: 'center' });
          
          // Date info in header (left side, below logo)
          doc.setFontSize(10);
          doc.text(`Généré le: ${new Date().toLocaleDateString('fr-FR')}`, margin, 50, { align: 'left' });
          
          // Main title section - more space from header
          doc.setTextColor(44, 62, 80); // Dark blue text
          doc.setFontSize(20);
          doc.setFont('helvetica', 'bold');
          doc.text('RAPPORT D\'ARRIVAGE', pageWidth / 2, 95, { align: 'center' });
          
          // Date range with decorative elements
          doc.setFontSize(14);
          doc.setFont('helvetica', 'normal');
          doc.setTextColor(52, 152, 219); // Blue text
          
          // Decorative line before date
          doc.setLineWidth(2);
          doc.setDrawColor(52, 152, 219);
          doc.line(pageWidth / 2 - 50, 105, pageWidth / 2 + 50, 105);
          
          doc.text(titleDate, pageWidth / 2, 115, { align: 'center' });
          
          // Summary box
          let currentY = 130;
          doc.setFillColor(248, 249, 250); // Light gray background
          doc.setDrawColor(224, 224, 224); // Gray border
          doc.rect(margin, currentY, pageWidth - (margin * 2), 25, 'FD');
          
          doc.setTextColor(44, 62, 80);
          doc.setFontSize(12);
          doc.setFont('helvetica', 'bold');
          doc.text('RÉSUMÉ:', margin + 10, currentY + 10);
          
          // Collect products for summary
          const allProducts = [];
          const productMap = new Map();
          mergedDataState.forEach(supplier => {
            if (supplier.PRODUCTS && supplier.PRODUCTS.length > 0) {
              supplier.PRODUCTS.forEach(product => {
                if (product.PRODUCT_NAME) {
                  if (!productMap.has(product.PRODUCT_NAME)) {
                    productMap.set(product.PRODUCT_NAME, { hasQuota: false, name: product.PRODUCT_NAME });
                  }
                  if (product.BONUS === "Quota") {
                    productMap.get(product.PRODUCT_NAME).hasQuota = true;
                  }
                }
              });
            }
          });
          productMap.forEach((value, key) => {
            if (!value.hasQuota) {
              allProducts.push(value.name);
            }
          });
          
          const uniqueProducts = [...new Set(allProducts)].sort((a, b) => a.localeCompare(b, 'fr', { sensitivity: 'base' }));
          const totalSuppliers = mergedDataState.length;
          
          doc.setFont('helvetica', 'normal');
          doc.text(`${uniqueProducts.length} Produit(s)`, margin + 80, currentY + 10);
          
          currentY += 40;
          
          console.log('Products to include in PDF:', uniqueProducts.length);
          
          // Products table with enhanced styling
          if (uniqueProducts.length > 0) {
            // Table title
            doc.setTextColor(44, 62, 80);
            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.text('LISTE DES PRODUITS', margin, currentY);
            currentY += 10;
            
            // Create numbered product list
            const tableData = uniqueProducts.map((productName, index) => [
              (index + 1).toString().padStart(3, '0'), // Sequential number
              productName
            ]);
            
            doc.autoTable({
              startY: currentY,
              head: [['N°', 'Nom du Produit']],
              body: tableData,
              theme: 'striped',
              styles: {
                fontSize: 10,
                cellPadding: 6,
                lineColor: [220, 220, 220],
                lineWidth: 0.5,
                overflow: 'linebreak',
                cellWidth: 'wrap'
              },
              headStyles: {
                fillColor: [44, 62, 80],
                textColor: [255, 255, 255],
                fontStyle: 'bold',
                fontSize: 11,
                halign: 'center'
              },
              columnStyles: {
                0: { 
                  halign: 'center', 
                  cellWidth: 25,
                  fillColor: [248, 249, 250],
                  fontStyle: 'bold'
                },
                1: { 
                  halign: 'left', 
                  cellWidth: pageWidth - margin * 2 - 25, // Calculate remaining width
                  fillColor: [240, 248, 255], // Light blue background for product names
                  overflow: 'linebreak'
                }
              },
              alternateRowStyles: {
                fillColor: [252, 252, 252]
              },
              margin: { left: margin, right: margin },
              tableWidth: pageWidth - margin * 2,
              didDrawPage: function(data) {
                // Add page border
                doc.setDrawColor(44, 62, 80);
                doc.setLineWidth(1);
                doc.rect(10, 10, pageWidth - 20, pageHeight - 20);
              }
            });
            
          } else {
            // No products message with styling
            doc.setFillColor(255, 245, 157); // Light yellow background
            doc.setDrawColor(255, 193, 7); // Yellow border
            doc.rect(margin, currentY, pageWidth - (margin * 2), 30, 'FD');
            
            doc.setTextColor(133, 100, 4); // Dark yellow text
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.text('AUCUN PRODUIT TROUVÉ', pageWidth / 2, currentY + 15, { align: 'center' });
            
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.text('Aucun produit n\'a été trouvé pour la période sélectionnée', pageWidth / 2, currentY + 25, { align: 'center' });
          }
          
          // Enhanced footer for all pages
          const pageCount = doc.internal.getNumberOfPages();
          for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            
            // Footer background
            doc.setFillColor(248, 249, 250);
            doc.rect(0, pageHeight - 25, pageWidth, 25, 'F');
            
            // Footer line
            doc.setDrawColor(44, 62, 80);
            doc.setLineWidth(0.5);
            doc.line(margin, pageHeight - 25, pageWidth - margin, pageHeight - 25);
            
            // Footer text
            doc.setTextColor(108, 117, 125);
            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            
            // Left side - Company info
            doc.text('BNM Analyse System', margin, pageHeight - 15);
            doc.text('Rapport d\'Arrivage', margin, pageHeight - 10);
            
            // Center - Page number
            doc.text(`Page ${i} sur ${pageCount}`, pageWidth / 2, pageHeight - 12, { align: 'center' });
            
            // Right side - Generation info
            doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}`, 
                     pageWidth - margin, pageHeight - 15, { align: 'right' });
            doc.text('', pageWidth - margin, pageHeight - 10, { align: 'right' });
          }
          
          // Save the PDF
          const filename = `BNM_Arrivage_${filenameDate}.pdf`;
          console.log('Saving PDF as:', filename);
          doc.save(filename);
          
        } catch (error) {
          console.error('Error in addContentToPDF:', error);
          alert('Error generating PDF content: ' + error.message);
        }
      }
      
    } catch (error) {
      console.error('Error in generatePDF:', error);
      alert('Error generating PDF: ' + error.message);
    }
  }

  // PDF Download button click event
  $('#downloadPdfBtn').click(function() {
    const $btn = $(this);
    const originalText = $btn.html();
    
    // Show loading state
    $btn.addClass('btn-loading').html('<span class="spinner-border spinner-border-sm" role="status"></span> Generating PDF...');
    
    // Small delay to show loading state
    setTimeout(() => {
      try {
        generatePDF();
      } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF. Please try again.');
      } finally {
        // Restore button state
        $btn.removeClass('btn-loading').html(originalText);
      }
    }, 100);
  });

  // Dark mode is now controlled by theme.js via localStorage + events

  fetchData(); // On load
});
</script>

<script src="theme.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
