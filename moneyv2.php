<?php
session_start();
$page_identifier = 'mony';

// Restrict access for 'vente' and 'achat'
require_once 'check_permission.php';


// Database connection
require_once 'db/db_connect.php';

// Get latest stock timestamp
$query = "SELECT time FROM stock ORDER BY time DESC LIMIT 1";
$result = $conn->query($query);

$lastSavedTime = '';
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $time = new DateTime($row['time']);
    $lastSavedTime = 'since ' . $time->format('H:i');
}

// Fetch last values from each table
$latestData = array();

// Get analyse data
$query = "SELECT total_profit FROM analyse ORDER BY time DESC LIMIT 1";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $latestData['analyse'] = $result->fetch_assoc();
}

// Get bank data from normalized bank_transactions table
$query = "SELECT 
    bt.bank_id,
    b.bank_code,
    b.bank_name,
    bt.sold,
    bt.remise,
    bt.check_amount,
    bt.creation_time
FROM bank_transactions bt 
JOIN banks b ON bt.bank_id = b.id_bank 
WHERE bt.creation_time = (SELECT MAX(creation_time) FROM bank_transactions)
AND b.is_active = TRUE
ORDER BY b.bank_name";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $bankData = array();
    $totalBank = 0;
    $totalChecks = 0;
    $latestCreationTime = null;
    
    while ($row = $result->fetch_assoc()) {
        $bankCode = $row['bank_code'];
        $bankData['banks'][$bankCode] = array(
            'bank_name' => $row['bank_name'],
            'sold' => floatval($row['sold']),
            'remise' => floatval($row['remise']),
            'check_amount' => floatval($row['check_amount'])
        );
        $totalBank += (floatval($row['sold']) + floatval($row['remise']));
        $totalChecks += floatval($row['check_amount']);
        $latestCreationTime = $row['creation_time'];
    }
    
    $bankData['total_bank'] = $totalBank;
    $bankData['total_checks'] = $totalChecks;
    $bankData['creation_time'] = $latestCreationTime;
    
    $latestData['bank'] = $bankData;
}

// Get tresorie data
$query = "SELECT total_tresorie, caisse, paiement_net, total_bank, time 
          FROM tresorie ORDER BY time DESC LIMIT 1";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $latestData['tresorie'] = $result->fetch_assoc();
}

// Get dette data
$query = "SELECT total_dette, dette_fournisseur, total_checks, time 
          FROM dette ORDER BY time DESC LIMIT 1";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $latestData['dette'] = $result->fetch_assoc();
}

// Get creance data
$query = "SELECT creance, time FROM creance_client ORDER BY time DESC LIMIT 1";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $latestData['creance'] = $result->fetch_assoc();
}

// Get stock data
$query = "SELECT total_stock, principal, hangar, depot_reserver, hangar_reserver, time 
          FROM stock ORDER BY time DESC LIMIT 1";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $latestData['stock'] = $result->fetch_assoc();
}

// Convert latestData to JSON for JavaScript use
$latestDataJson = json_encode($latestData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="theme.js"></script>
        <script src="api_config_money.js"></script>


    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        mono: ['IBM Plex Mono', 'monospace'],
                    },
                    colors: {
                        lab: {
                            dark: '#121212',
                            panel: '#1e1e1e',
                            border: '#333333',
                            accent: '#00ff88',
                            highlight: '#0088ff',
                            warning: '#ff6600',
                            danger: '#ff0033',
                            teal: '#00f5d4',
                            purple: '#9b5de5'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-color: #f8f9fa;
            --text-color: #1f2937;
            --panel-bg: #f8f9fa;
            --header-bg: #f8f9fa;
            --border-color: #e5e7eb;
        }

        [data-theme="dark"] {
            --bg-color: #111827;
            --text-color: #e0e0e0;
            --panel-bg: #111827;
            --header-bg: #111827;
            --border-color: #333333;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'IBM Plex Mono', monospace;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .data-panel {
            border: 1px solid var(--border-color);
            background-color: var(--panel-bg);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .data-header {
            border-bottom: 1px solid var(--border-color);
            background-color: var(--header-bg);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .data-table {
            font-size: 0.875rem;
        }
        .data-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .data-table td, .data-table th {
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: border-color 0.3s ease;
        }
        .data-value {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 600;
        }
        .positive-trend {
            color: #00ff88;
        }
        .negative-trend {
            color: #ff0033;
        }
        .neutral-trend {
            color: #0088ff;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .trend-icon {
            margin-right: 4px;
        }
        .section-divider {
            border: none;
            border-top: 1px solid var(--border-color);
            margin: 1.5rem 0;
            transition: border-color 0.3s ease;
        }
        .metric-card {
            background-color: var(--panel-bg);
            border-left: 4px solid;
            padding: 0.75rem 1rem;
            transition: background-color 0.3s ease;
        }
        .metric-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.7;
        }
        .metric-value {
            font-size: 1.5rem;
            line-height: 1;
            margin: 0.25rem 0 0.5rem;
        }
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        .progress-bar {
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
            transition: background-color 0.3s ease;
        }
        .progress-value {
            height: 100%;
        }
        .grid-point {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .kpi-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
        }
        .kpi-loader {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100px;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 136, 255, 0.2);
            border-radius: 50%;
            border-top-color: #0088ff;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .error-message {
            color: #ff0033;
            text-align: center;
        }
        .hidden {
            display: none !important;
        }
        .chart-filter {
            background-color: rgba(var(--border-color-rgb), 0.3);
            transition: all 0.2s ease;
            border: 1px solid transparent;
            opacity: 0.5;
        }
        .chart-filter:hover {
            background-color: rgba(var(--border-color-rgb), 0.5);
            opacity: 0.8;
        }
        .chart-filter.active {
            border-color: currentColor;
            background-color: rgba(var(--border-color-rgb), 0.7);
            opacity: 1;
        }
        
        .theme-toggle {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }
        
        .theme-toggle:hover {
            opacity: 0.8;
        }
        
        [data-theme="light"] .theme-toggle {
            background: var(--panel-bg);
            border-color: var(--border-color);
        }
        
        /* Update input styles for theme support */
        input[type="date"], select {
            background-color: var(--bg-color) !important;
            color: var(--text-color) !important;
            border-color: var(--border-color) !important;
            transition: all 0.3s ease;
        }
        
        input[type="date"]:focus, select:focus {
            border-color: #3b82f6 !important;
        }
    </style>
</head>
<body class="font-mono transition-colors duration-300" style="background-color: var(--bg-color); color: var(--text-color);">
    <header class="border-b sticky top-0 z-10" style="border-color: var(--border-color); background-color: var(--bg-color);")>
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-lab-accent font-bold text-xl">BNM</span>
                    <span class="text-lab-highlight font-bold text-xl">ANALYSE</span>
                </div>
                <div class="flex items-center space-x-4 text-xs text-gray-400">
                    <button id="theme-toggle" class="theme-toggle">
                        <span id="theme-icon">🌙</span>
                        <span id="theme-text">Dark</span>
                    </button>
                    <span>Update in: <span id="refresh-time" style="color:red" class="text-gray-100">5min 00sec</span></span>
                    <button id="manual-refresh-btn" class="px-2 py-1 bg-lab-accent text-black rounded hover:bg-opacity-80">⟳ Refresh Now</button>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6">
        <!-- Executive Analysis -->
        <section class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Market Growth Chart -->
                <div class="data-panel rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">STATISTIQUE</h3>
                        <!-- <span class="text-xs bg-lab-panel px-2 py-1 rounded">real-time data</span> -->
                    </div>
                    <!-- Date range selector -->
                    <div class="flex flex-wrap gap-4 mb-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm">From:</label>
                            <input type="date" id="kpi-start-date" class="border rounded px-2 py-1 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="background-color: var(--bg-color); color: var(--text-color); border-color: var(--border-color);">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm">To:</label>
                            <input type="date" id="kpi-end-date" class="border rounded px-2 py-1 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="background-color: var(--bg-color); color: var(--text-color); border-color: var(--border-color);">
                        </div>
                        <button id="update-kpi-chart" class="bg-lab-accent text-black px-3 py-1 rounded text-sm hover:bg-opacity-80">Update</button>
                    </div>



                    <!-- Metric selector -->                    <select 
    id="metric-selector" 
    class="border rounded px-2 py-1 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
    style="background-color: var(--bg-color); color: var(--text-color); border-color: var(--border-color);"
><option value="all">All Metrics</option>
                        <option value="profit">Profit</option>
                        <option value="tresorerie">Trésorerie</option>
                        <option value="dette">Dette</option>
                        <option value="stock">Stock</option>
                        <option value="creance">Créance</option>
                    </select>

                    <!-- Chart container -->
                    <div class="h-64">
                        <canvas id="marketGrowthChart"></canvas>
                    </div>
                </div>
                
                <!-- Demographic Breakdown -->
                <div class="data-panel rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">FONDS PROPRE</h3>
                        <!-- <span class="text-xs bg-lab-panel px-2 py-1 rounded">real-time</span> -->
                    </div>
                    <div class="chart-container">
                        <canvas id="profitBreakdownChart"></canvas>
                    </div>
                    <div class="grid grid-cols-4 gap-2 mt-4 text-xs text-center">
                        <div>
                            <div class="font-bold text-[#00f5d4]">Total Stock</div>
                            <div id="stock-breakdown">-</div>
                        </div>
                        <div>
                            <div class="font-bold text-[#0088ff]">Credit Client</div>
                            <div id="credit-breakdown">-</div>
                        </div>
                        <div>
                            <div class="font-bold text-[#9b5de5]">Trésorerie</div>
                            <div id="tresorerie-breakdown">-</div>
                        </div>
                        <div>
                            <div class="font-bold text-[#ff0033]">Dette</div>
                            <div id="dette-breakdown">-</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- DETTES FOURNISSEUR -->
                <div class="data-panel rounded-lg p-3">
                    <div class="text-xs mb-2 uppercase">DETTES FOURNISSEUR</div>
                    <div id="loading-dette-animation" class="kpi-loader">
                        <div class="spinner"></div>
                    </div>
                    <div id="dette-text" class="hidden">
                        <div class="flex justify-between items-end mb-1">
                            <span id="dette-value" class="text-xl data-value">Loading...</span>
                        </div>
                    </div>
                   
                </div>
                
                <!-- CREANCE CLIENT -->
                <div class="data-panel rounded-lg p-3">
                    <div class="text-xs mb-2 uppercase">CREANCE CLIENT</div>
                    <div id="loading-credit-animation" class="kpi-loader">
                        <div class="spinner"></div>
                    </div>
                    <div id="credit-text" class="hidden">
                        <div class="flex justify-between items-end mb-1">
                            <span id="credit-client-value" class="text-xl data-value">Loading...</span>
                        </div>
                    </div>
                </div>
                
                <!-- TOTAL STOCK -->
                <div class="data-panel rounded-lg p-3">
                    <div class="text-xs mb-2 uppercase">TOTAL STOCK</div>
                    <div id="loading-stock-animation" class="kpi-loader">
                        <div class="spinner"></div>
                    </div>
                    <div id="stock-text" class="hidden">
                        <div class="flex justify-between items-end mb-1">
                            <span id="stock-value" class="text-xl data-value">Loading...</span>
                        </div>
                    </div>
                    <button id="toggle-details" class="text-xs text-lab-accent mt-2"></button>
                    <div id="stock-details" class="hidden mt-2 text-xs"></div>
                </div>
                
                <!-- TOTAL TRÉSORERIE -->
                <div class="data-panel rounded-lg p-3">
                    <div class="text-xs mb-2 uppercase">TOTAL TRÉSORERIE</div>
                    <div class="flex justify-between items-end mb-1">
                        <div id="tresorerie-total" class="text-xl data-value">
                            <span id='banque-value'>Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- TOTAL PROFIT -->
                <div class="data-panel rounded-lg p-3 col-span-4">
                    <div class="text-xs mb-4 uppercase text-center">TOTAL PROFIT</div>
                    <div id="loading-profit-animation" class="kpi-loader flex justify-center items-center">
                        <div class="spinner"></div>
                    </div>
                    <div id="profit-text" class="hidden">
                        <div class="flex justify-center items-center">
                            <span id="profit-value" class="text-3xl data-value font-bold">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>



              <!-- analyse  PERFORMANCE -->
        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">ANALYSE PERFORMANCE</h2>
            
            <div class="data-panel rounded-lg p-4">
                <div class="overflow-x-auto">
                    <table class="w-full data-table">
                        <thead>
                            <tr>
                                <th class="text-left py-2 px-4">DATA</th>
                                <th class="text-right py-2 px-4">VALUE</th>
                                <th class="text-right py-2 px-4">LAST UPDATE</th>
                                <th class="text-right py-2 px-4">TODAY %</th>
                            </tr>
                        </thead>
                        <tbody id="performance-matrix">
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        

    </main>



    <script>
        // Theme functionality
        function initializeTheme() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const themeText = document.getElementById('theme-text');
            
            // Check current theme from localStorage (same as sidebar)
            const currentTheme = localStorage.getItem('theme') || 'light';
            const isDark = currentTheme === 'dark';
            
            // Apply theme to page
            applyTheme(isDark);
            updateThemeButton(isDark);
            
            // Theme toggle click handler
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = localStorage.getItem('theme') || 'light';
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    const isDark = newTheme === 'dark';
                    
                    // Save to localStorage (this will sync with sidebar)
                    localStorage.setItem('theme', newTheme);
                    
                    // Apply theme
                    applyTheme(isDark);
                    updateThemeButton(isDark);
                    
                    // Dispatch event for other components
                    window.dispatchEvent(new CustomEvent('themeChanged', {
                        detail: { theme: newTheme, isDark: isDark }
                    }));
                });
            }
            
            // Listen for theme changes from other components (like sidebar)
            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') {
                    const isDark = e.newValue === 'dark';
                    applyTheme(isDark);
                    updateThemeButton(isDark);
                }
            });
            
            // Listen for same-page theme changes
            window.addEventListener('themeChanged', function(e) {
                const isDark = e.detail.isDark;
                applyTheme(isDark);
                updateThemeButton(isDark);
            });
        }
        
        function applyTheme(isDark) {
            // Apply theme using data-theme attribute for CSS custom properties
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            
            // Also add/remove classes for compatibility
            document.documentElement.classList.toggle('dark', isDark);
            document.body.classList.toggle('dark', isDark);
        }
        
        function updateThemeButton(isDark) {
            const themeIcon = document.getElementById('theme-icon');
            const themeText = document.getElementById('theme-text');
            
            if (themeIcon && themeText) {
                if (isDark) {
                    themeIcon.textContent = '☀️';
                    themeText.textContent = 'Light';
                } else {
                    themeIcon.textContent = '🌙';
                    themeText.textContent = 'Dark';
                }
            }
        }

        // Refresh configuration
        const REFRESH_INTERVAL = 300; // 5 minutes in seconds
        let countdown = REFRESH_INTERVAL;
        let refreshIntervalId = null;
        let isRefreshing = false;

        // Start countdown timer
        function startCountdown() {
            if (refreshIntervalId) {
                clearInterval(refreshIntervalId);
            }
            countdown = REFRESH_INTERVAL;
            updateTimerDisplay();
            
            refreshIntervalId = setInterval(() => {
                countdown--;
                updateTimerDisplay();
                if (countdown <= 0) {
                    refreshAll();
                }
            }, 1000);
        }

        // Format time display
        function formatTime(seconds) {
            const minutes = Math.floor(Math.abs(seconds) / 60);
            const secs = Math.abs(seconds) % 60;
            const sign = seconds < 0 ? '-' : '';
            return `${sign}${minutes}min ${secs.toString().padStart(2, '0')}sec`;
        }

        // Update timer display
        function updateTimerDisplay() {
            const timerElement = document.getElementById('refresh-time');
            if (timerElement) {
                timerElement.textContent = formatTime(countdown);
                if (countdown < 0) {
                    timerElement.classList.add('text-lab-danger');
                } else {
                    timerElement.classList.remove('text-lab-danger');
                }
            }
        }

        function startCountdown() {
            // Clear any existing countdown
            if (refreshIntervalId) {
                clearInterval(refreshIntervalId);
            }
            
            // Reset countdown and update display
            countdown = REFRESH_INTERVAL;
            updateTimerDisplay();
            
            // Start new countdown
            refreshIntervalId = setInterval(() => {
                countdown--;
                updateTimerDisplay();
                
                if (countdown === 0) {
                    refreshAll(); // This will show/hide loaders automatically
                }
            }, 1000);
        }

        // Helper function to format numbers
        function formatNumber(value) {
            return new Intl.NumberFormat('fr-FR', {
                maximumFractionDigits: 2
            }).format(value);
        }

  
        // Performance Matrix Update Function
        async function updatePerformanceMatrix() {
            const tbody = document.getElementById('performance-matrix');
            if (!tbody) return;

            try {
                // Fetch all data in parallel
                const [stockResponse, creditResponse, traisorieResponse, 
                       bankResponse, detteResponse] = await Promise.all([
                    fetch(API_CONFIG.getApiUrl("/stock-summary")),
                    fetch(API_CONFIG.getApiUrl("/credit-client")),
                    fetch(API_CONFIG.getApiUrl("/total-tresorie")),
                    fetch(API_CONFIG.getApiUrl("/total-bank")),
                    fetch(API_CONFIG.getApiUrl("/total-dette")),
                ]);

                const [stockData, creditData, traisorieData, bankData, detteData] = await Promise.all([
                    stockResponse.json(),
                    creditResponse.json(),
                    traisorieResponse.json(),
                    bankResponse.json(),
                    detteResponse.json()
                ]);


                // Get last update time from bank data response
                const lastBankUpdate = new Date().toLocaleString('fr-FR');

                // Helper function to calculate percentage change
                function calculatePercentChange(current, previous) {
                    if (!previous || previous === 0) return { value: 0, text: '0%', color: '#ffffff' };
                    const change = ((current - previous) / Math.abs(previous)) * 100;
                    const text = change === 0 ? '0%' : (change > 0 ? `+${change.toFixed(2)}%` : `${change.toFixed(2)}%`);
                    const color = change === 0 ? '#ffffff' : (change > 0 ? '#00ff88' : '#ff0033');
                    return { value: change, text, color };
                }

                // Helper function to create a row
                function createRow(label, value, lastUpdate = '-', previousValue = null, weekly = '-', monthly = '-', isSubRow = false, color = '') {
                    const baseStyle = color ? `color: ${color}` : '';
                    let todayChange = { text: '-', color: '#ffffff' };
                    
                    if (previousValue !== null) {
                        todayChange = calculatePercentChange(value, previousValue);
                    }

                    return `
                        <tr class="border-t border-lab-border">
                            <td class="py-2 px-4 ${isSubRow ? 'pl-8' : 'font-semibold'}" style="${baseStyle}">${label}</td>
                            <td class="text-right py-2 px-4" style="${baseStyle}">${formatNumber(value)} DZD</td>
                            <td class="text-right py-2 px-4" style="${baseStyle}">${lastUpdate}</td>
                            <td class="text-right py-2 px-4" style="color: ${todayChange.color}">${todayChange.text}</td>
                        </tr>
                    `;
                }

let lastSavedData = null;
try {
    // Use the PHP data that was already embedded in the page
    lastSavedData = <?php echo $latestDataJson; ?>;
    
    // Transform the data structure to match what your code expects
    lastSavedData = {
        stock: {
            total: lastSavedData.stock?.total_stock,
            principale: lastSavedData.stock?.principal,
            depot_reserver: lastSavedData.stock?.depot_reserver,
            hangar: lastSavedData.stock?.hangar,
            hangar_reserve: lastSavedData.stock?.hangar_reserver
        },
        credit_client: lastSavedData.creance?.creance,
        tresorerie: {
            total: lastSavedData.tresorie?.total_tresorie,
            caisse: lastSavedData.tresorie?.caisse,
            paiement_net: lastSavedData.tresorie?.paiement_net,
            bank: {
                total: lastSavedData.bank?.total_bank,
                banks: lastSavedData.bank?.banks || {}
            }
        },
        dette: {
            total: lastSavedData.dette?.total_dette,
            fournisseur: lastSavedData.dette?.dette_fournisseur,
            checks: {
                total: lastSavedData.bank?.total_checks,
                banks: lastSavedData.bank?.banks || {}
            }
        }
    };
} catch (error) {
    console.error('Error processing last saved data:', error);
}

                // Build the table HTML
                let html = '';

                // Stock Section
                html += createRow('STOCK TOTAL', stockData.total_stock, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.stock?.total, '-', '-', false, '#00f5d4');
                html += createRow('Stock Principale', stockData.STOCK_principale, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.stock?.principale, '-', '-', true, 'rgba(0, 245, 212, 0.7)');
                html += createRow('Depot Reserver', stockData.depot_reserver, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.stock?.depot_reserver, '-', '-', true, 'rgba(0, 245, 212, 0.7)');
                html += createRow('Hangar', stockData.hangar, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.stock?.hangar, '-', '-', true, 'rgba(0, 245, 212, 0.7)');
                html += createRow('Hangar Reserve', stockData.hangarréserve, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.stock?.hangar_reserve, '-', '-', true, 'rgba(0, 245, 212, 0.7)');

                // Credit Client
                html += createRow('CREANCE CLIENT', creditData.credit_client, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.credit_client, '-', '-', false, '#0088ff');


                // Tresorerie Section
                html += createRow('TOTAL TRÉSORERIE', traisorieData.total_tresorie, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.tresorerie?.total, '-', '-', false, '#9b5de5');

                // Caisse Section
                html += createRow('CAISSE', traisorieData.details.caisse, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.tresorerie?.caisse, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                // Paiement Net
                html += createRow('PAIEMENT NET', traisorieData.details.paiement_net, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.tresorerie?.paiement_net, '-', '-', true, 'rgba(155, 93, 229, 0.7)');

                // Bank Section
                html += createRow('BANK TOTAL', bankData.total_bank, bankData.creation_time , 
                    lastSavedData?.tresorerie?.bank?.total, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                
                // Dynamic bank details
                if (bankData.details && Object.keys(bankData.details).length > 0) {
                    Object.keys(bankData.details).forEach(bankCode => {
                        const bank = bankData.details[bankCode];
                        const lastBankData = lastSavedData?.tresorerie?.bank?.banks?.[bankCode];
                        
                        html += createRow(`${bank.bank_name || bankCode} Sold`, bank.sold, bankData.creation_time, 
                            lastBankData?.sold, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                        html += createRow(`${bank.bank_name || bankCode} Remise`, bank.remise, bankData.creation_time, 
                            lastBankData?.remise, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                    });
                }


                // Dette Section
                html += createRow('DETTE TOTAL', detteData.total_dette, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.dette?.total, '-', '-', false, '#ff0033');

                // Checks Section
                html += createRow('Dette Fournisseur', detteData.details.dette_fournisseur, '<?php echo $lastSavedTime; ?>', 
                    lastSavedData?.dette?.fournisseur, '-', '-', true, 'rgba(255, 0, 51, 0.7)');

                html += createRow('TOTAL CHECKS', detteData.details.total_checks, bankData.creation_time, 
                    lastSavedData?.dette?.checks?.total, '-', '-', true, 'rgba(255, 0, 51, 0.7)');
                
                // Dynamic bank checks
                if (detteData.details.checks_details && Object.keys(detteData.details.checks_details).length > 0) {
                    Object.keys(detteData.details.checks_details).forEach(bankCode => {
                        const bankChecks = detteData.details.checks_details[bankCode];
                        const lastBankChecks = lastSavedData?.dette?.checks?.banks?.[bankCode];
                        
                        html += createRow(`${bankCode} Checks`, bankChecks.checks, bankData.creation_time, 
                            lastBankChecks?.check_amount, '-', '-', true, 'rgba(255, 0, 51, 0.7)');
                    });
                }

                tbody.innerHTML = html;

            } catch (error) {
                console.error('Error updating performance matrix:', error);
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-lab-danger">Error loading data</td></tr>';
            }
        }

        // Main refresh function
        async function refreshAll() {
            if (isRefreshing) {
                console.log('Refresh already in progress, skipping...');
                return;
            }
            
            // Disable refresh button and show loading state
            const refreshBtn = document.getElementById('manual-refresh-btn');
            if (refreshBtn) {
                refreshBtn.disabled = true;
                refreshBtn.classList.add('opacity-50');
            }
            
            console.log('Starting refresh...');
            isRefreshing = true;
            showAllLoaders(); // Show all loading indicators
            
            try {
                // Use a single fetchAllFinancialData call which now handles both panels and donut chart
                await fetchAllFinancialData();
                
                // Update other charts and performance matrix
                await Promise.all([
                    updateKPITrendsChart(),
                    updatePerformanceMatrix(),
                    updateProfitBreakdownChart()
                ]);
            } catch (error) {
                console.error("Error during refresh:", error);
            } finally {
                hideAllLoaders(); // Hide all loading indicators
                isRefreshing = false;
                console.log('Refresh completed');
                
                // Re-enable refresh button
                const refreshBtn = document.getElementById('manual-refresh-btn');
                if (refreshBtn) {
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('opacity-50');
                }

                // Reset countdown after refresh completes
                countdown = REFRESH_INTERVAL;
                updateTimerDisplay();
            }
        }

        async function savePerformanceData(stockData, creditData, caisseData, paiementData, bankData, detteData, checksData) {
            const performanceData = {
                timestamp: new Date().toISOString(),
                stock: {
                    total: stockData.total_stock,
                    principale: stockData.STOCK_principale,
                    depot_reserver: stockData.depot_reserver,
                    hangar: stockData.hangar,
                    hangar_reserve: stockData.hangarréserve
                },
                credit_client: creditData.credit_client,
                tresorerie: {
                    total: caisseData.caisse + paiementData.Total_Paiment + bankData.total_bank,
                    caisse: caisseData.caisse,
                    paiement_net: paiementData.Total_Paiment,
                    bank: {
                        total: bankData.total_bank,
                        bna: bankData.details.BNA,
                        baraka: bankData.details.Baraka
                    }
                },
                dette: {
                    total: detteData.value + checksData.total_checks,
                    fournisseur: detteData.value,
                    checks: {
                        total: checksData.total_checks,
                        bna: checksData.details.BNA.checks,
                        baraka: checksData.details.Baraka.checks
                    }
                }
            };

           
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize theme
            initializeTheme();
            
            // Initial data load and start countdown
            refreshAll().then(() => {
                startCountdown();
            });

            // Set up manual refresh button
            document.getElementById('manual-refresh-btn')?.addEventListener('click', async () => {
                // Clear existing countdown
                if (refreshIntervalId) {
                    clearInterval(refreshIntervalId);
                }
                
                // Reset the countdown display immediately
                countdown = REFRESH_INTERVAL;
                updateTimerDisplay();
                
                // Perform refresh
                await refreshAll();
                
                // Start new countdown after refresh completes
                startCountdown();
            });

            // Set up theme toggle button

            // Add toggle functionality for bank details
            document.getElementById('show-more-bank')?.addEventListener('click', function() {
                const details = document.getElementById('bank-details');
                const icon = this.querySelector('svg');
                details.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
                const span = this.querySelector('span');
                span.textContent = details.classList.contains('hidden') ? 'Bank Details' : 'Hide Details';
            });

            // Add event listener for metric selector
            document.getElementById('metric-selector')?.addEventListener('change', (e) => {
                currentMetric = e.target.value;
                updateKPITrendsChart();
            });

            // Add event listener for KPI chart update button
            document.getElementById('update-kpi-chart')?.addEventListener('click', () => {
                updateKPITrendsChart();
            });
        });


        function updateChartsForTheme(theme) {
            const gridColor = theme === 'light' ? 'rgba(229, 231, 235, 0.5)' : 'rgba(51, 51, 51, 0.5)';
            const textColor = theme === 'light' ? '#1f2937' : '#e0e0e0';
            
            // Update market growth chart
            if (marketGrowthChart) {
                marketGrowthChart.options.scales.y.grid.color = gridColor;
                marketGrowthChart.options.scales.y.ticks.color = textColor;
                marketGrowthChart.options.scales.x.ticks.color = textColor;
                marketGrowthChart.options.plugins.legend.labels.color = textColor;
                marketGrowthChart.update();
            }
            
            // Update profit breakdown chart colors don't need updating as they use specific brand colors
        }

        // Market Growth Chart
        const marketGrowthCtx = document.getElementById('marketGrowthChart').getContext('2d');
        let marketGrowthChart = null;
        let currentMetric = 'profit'; // Default metric to show

        async function updateKPITrendsChart() {
            try {
                // Get the container and check if it exists
                const chartContainer = document.getElementById('marketGrowthChart');
                if (!chartContainer) {
                    throw new Error('Chart container not found');
                }

                const metrics = ['profit', 'tresorerie', 'dette', 'stock', 'creance'];
                const datasets = [];
                
                // Get date range inputs
                const startDateInput = document.getElementById('kpi-start-date');
                const endDateInput = document.getElementById('kpi-end-date');
                
                // Set default date range if not selected
                if (!startDateInput.value || !endDateInput.value) {
                    const end = new Date();
                    const start = new Date();
                    start.setDate(start.getDate() - 30); // Default to last 7 days
                    
                    endDateInput.value = end.toISOString().split('T')[0];
                    startDateInput.value = start.toISOString().split('T')[0];
                }

                // Get current metric selection
                const metricSelector = document.getElementById('metric-selector');
                const selectedMetric = metricSelector ? metricSelector.value : 'profit'; // Default to profit if selector not found

                // Determine which metrics to fetch based on selection
                const metricsToFetch = selectedMetric === 'all' ? metrics : [selectedMetric];

                // Build URL with parameters
                // Fetch data for selected metrics in parallel
                const responses = await Promise.all(metricsToFetch.map(metric => {
                    const url = new URL(API_CONFIG.getApiUrl("/kpi-trends-data"));
                    url.searchParams.append('start_date', startDateInput.value);
                    url.searchParams.append('end_date', endDateInput.value);
                    url.searchParams.append('metric', metric);
                    return fetch(url);
                }));

                // Process all responses
                const allData = await Promise.all(responses.map(response => response.json()));

                // Check for errors
                const errorData = allData.find(data => data.error);
                if (errorData) {
                    throw new Error(errorData.error);
                }

                // Destroy existing chart if it exists
                if (marketGrowthChart) {
                    marketGrowthChart.destroy();
                }

                // Prepare datasets from selected metrics
                metricsToFetch.forEach((metric, index) => {
                    if (allData[index] && allData[index].datasets && allData[index].datasets[0]) {
                        datasets.push({
                            label: metric.charAt(0).toUpperCase() + metric.slice(1),
                            data: allData[index].datasets[0].data,
                            borderColor: getMetricColor(metric),
                            backgroundColor: getMetricColor(metric, selectedMetric === 'all' ? 0.1 : 0.2),
                            borderWidth: 2,
                            fill: selectedMetric !== 'all',  // Fill area only when showing single metric
                            tension: 0.4
                        });
                    }
                });

                // Format dates based on whether all metrics are shown or just one
                const formattedLabels = allData[0].labels.map(dateStr => {
                    if (selectedMetric === 'all') {
                        // For all metrics, show only day/month
                        const [datePart] = dateStr.split(' ');
                        const [day, month] = datePart.split('/');
                        return `${day}/${month}`;
                    } else {
                        // For single metric, include hours
                        const [datePart, timePart] = dateStr.split(' ');
                        const [day, month] = datePart.split('/');
                        const [hours, minutes] = timePart.split(':');
                        return `${day}/${month} ${hours}:${minutes}`;
                    }
                });

                // Create new chart
                marketGrowthChart = new Chart(chartContainer.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: formattedLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: selectedMetric === 'all', // Only show legend when showing all metrics
                                position: 'top',
                                labels: {
                                    color: getThemeAwareColor('text'),
                                    font: {
                                        family: "'IBM Plex Mono', monospace"
                                    },
                                    padding: 15
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${formatNumber(context.parsed.y)} DZD`;
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: getThemeAwareColor('grid')
                                },
                                ticks: {
                                    color: getThemeAwareColor('text'),
                                    font: {
                                        family: "'IBM Plex Mono', monospace"
                                },
                                    callback: function(value) {
                                        return formatNumber(value) + ' DZD';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                    color: getThemeAwareColor('text'),
                                    font: {
                                        family: "'IBM Plex Mono', monospace"
                                }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error updating KPI trends chart:', error);
                // Show user-friendly error state in chart container
                const chartContainer = document.getElementById('marketGrowthChart').parentElement;
                chartContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="text-lab-danger mb-2">Failed to load chart data</div>
                        <div class="text-xs text-gray-400">${error.message}</div>
                        <button onclick="updateKPITrendsChart()" class="mt-3 px-3 py-1 bg-lab-accent text-black rounded text-sm hover:bg-opacity-80">
                            Try Again
                        </button>
                    </div>
                `;
            }
        }

        // Helper function to get color for each metric
        function getMetricColor(metric, alpha = 1) {
            const colors = {
                profit: `rgba(0, 255, 136, ${alpha})`,
                dette: `rgba(255, 0, 51, ${alpha})`,
                stock: `rgba(0, 245, 212, ${alpha})`,
                tresorerie: `rgba(155, 93, 229, ${alpha})`,
                creance: `rgba(0, 136, 255, ${alpha})`
            };
            return colors[metric] || `rgba(255, 255, 255, ${alpha})`;
        }

        // Helper function to get theme-aware colors
        function getThemeAwareColor(type) {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            
            if (currentTheme === 'light') {
                switch(type) {
                    case 'text': return '#1f2937';
                    case 'grid': return 'rgba(229, 231, 235, 0.5)';
                    case 'border': return '#e5e7eb';
                    default: return '#1f2937';
                }
            } else {
                switch(type) {
                    case 'text': return '#e0e0e0';
                    case 'grid': return 'rgba(51, 51, 51, 0.5)';
                    case 'border': return '#333333';
                    default: return '#e0e0e0';
                }
            }
        }

        // Profit Breakdown Chart
        const profitBreakdownCtx = document.getElementById('profitBreakdownChart').getContext('2d');
        let profitBreakdownChart = null;

        async function updateProfitBreakdownChart(stockData, creditData, caisseData, paiementData, bankData, detteData, checksData) {
            // Extract values, default to 0 if undefined
            const totalStock = parseFloat(stockData.total_stock) || 0;
            const creditClient = creditData.credit_client || 0;
            const caisse = caisseData.caisse || 0;
            const paiementNet = paiementData.Total_Paiment || 0;
            const totalBank = bankData.total_bank || 0;
            const dette = detteData.value || 0;
            const totalChecks = checksData.total_checks || 0;

            // Calculate Trésorerie total
            const tresorerie = caisse + paiementNet + totalBank;
            // Calculate Dette total
            const totalDette = dette + totalChecks;

            // Calculate total assets (for percentage calculation)
            const totalAssets = totalStock + creditClient + tresorerie + totalDette;

            // Calculate percentages
            const stockPercentage = (totalStock / totalAssets * 100).toFixed(1);
            const creditPercentage = (creditClient / totalAssets * 100).toFixed(1);
            const tresoreriePercentage = (tresorerie / totalAssets * 100).toFixed(1);
            const dettePercentage = (totalDette / totalAssets * 100).toFixed(1);

            // Update the breakdown text values
            document.getElementById('stock-breakdown').textContent = `${formatNumber(totalStock)} DZD (${stockPercentage}%)`;
            document.getElementById('credit-breakdown').textContent = `${formatNumber(creditClient)} DZD (${creditPercentage}%)`;
            document.getElementById('tresorerie-breakdown').textContent = `${formatNumber(tresorerie)} DZD (${tresoreriePercentage}%)`;
            document.getElementById('dette-breakdown').textContent = `${formatNumber(totalDette)} DZD (${dettePercentage}%)`;

            // Destroy existing chart if it exists
            if (profitBreakdownChart) {
                profitBreakdownChart.destroy();
            }

            // Create new chart
            profitBreakdownChart = new Chart(profitBreakdownCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Stock', 'Credit Client', 'Trésorerie', 'Dette'],
                    datasets: [{
                        data: [totalStock, creditClient, tresorerie, totalDette],
                        backgroundColor: [
                            '#00f5d4',
                            '#0088ff',
                            '#9b5de5',
                            '#ff0033'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = (value / totalAssets * 100).toFixed(1);
                                    return `${context.label}: ${formatNumber(value)} DZD (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Age by Genre Chart
        const ageGenreCtx = document.getElementById('ageGenreChart').getContext('2d');
        const ageGenreChart = new Chart(ageGenreCtx, {
            type: 'bar',
            data: {
                labels: ['13-17', '18-20', '21-24', '25-30', '31-40', '41+'],
                datasets: [
                    {
                        label: 'Action',
                        data: [18, 25, 32, 15, 7, 3],
                        backgroundColor: 'rgba(0, 136, 255, 0.7)'
                    },
                    {
                        label: 'Fantasy',
                        data: [15, 22, 35, 18, 8, 2],
                        backgroundColor: 'rgba(0, 245, 212, 0.7)'
                    },
                    {
                        label: 'Romance',
                        data: [12, 18, 25, 30, 12, 3],
                        backgroundColor: 'rgba(155, 93, 229, 0.7)'
                    },
                    {
                        label: 'Horror',
                        data: [8, 15, 28, 35, 12, 2],
                        backgroundColor: 'rgba(255, 102, 0, 0.7)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: false,
                        grid: {
                            color: 'rgba(51, 51, 51, 0.5)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Gender Preference Chart
        const genderPreferenceCtx = document.getElementById('genderPreferenceChart').getContext('2d');
        const genderPreferenceChart = new Chart(genderPreferenceCtx, {
            type: 'radar',
            data: {
                labels: ['Action', 'Fantasy', 'Romance', 'Horror', 'Sci-Fi', 'Sports', 'Comedy', 'Drama'],
                datasets: [
                    {
                        label: 'Male Readers',
                        data: [72, 14, 5, 8, 12, 15, 18, 6],
                        backgroundColor: 'rgba(0, 136, 255, 0.2)',
                        borderColor: '#0088ff',
                        borderWidth: 2
                    },
                    {
                        label: 'Female Readers',
                        data: [18, 22, 58, 12, 8, 3, 15, 12],
                        backgroundColor: 'rgba(155, 93, 229, 0.2)',
                        borderColor: '#9b5de5',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            color: 'rgba(51, 51, 51, 0.5)'
                        },
                        grid: {
                            color: 'rgba(51, 51, 51, 0.5)'
                        },
                        suggestedMin: 0,
                        suggestedMax: 80
                    }
                }
            }
        });

        // Trend Forecast Chart
        const trendForecastCtx = document.getElementById('trendForecastChart').getContext('2d');
        const trendForecastChart = new Chart(trendForecastCtx, {
            type: 'line',
            data: {
                labels: ['Current', '+1M', '+2M', '+3M', '+4M', '+5M', '+6M'],
                datasets: [
                    {
                        label: 'Isekai',
                        data: [100, 108, 116, 124, 131, 139, 147],
                        borderColor: '#00ff88',
                        backgroundColor: 'rgba(0, 255, 136, 0.1)',
                        tension: 0.4,
                        borderWidth: 2
                    },
                    {
                        label: 'Sports',
                        data: [100, 102, 103, 104, 103, 102, 101],
                        borderColor: '#0088ff',
                        backgroundColor: 'rgba(0, 136, 255, 0.1)',
                        tension: 0.4,
                        borderWidth: 2
                    },
                    {
                        label: 'Traditional Fantasy',
                        data: [100, 97, 94, 91, 87, 84, 81],
                        borderColor: '#ff6600',
                        backgroundColor: 'rgba(255, 102, 0, 0.1)',
                        tension: 0.4,
                        borderWidth: 2
                    },
                    {
                        label: 'Slice-of-Life',
                        data: [100, 103, 107, 110, 113, 117, 120],
                        borderColor: '#9b5de5',
                        backgroundColor: 'rgba(155, 93, 229, 0.1)',
                        tension: 0.4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + (context.raw - 100) + '% change';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        grid: {
                            color: 'rgba(51, 51, 51, 0.5)'
                        },
                        ticks: {
                            callback: function(value) {
                                return (value - 100) + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Revenue Forecast Chart
        const revenueForecastCtx = document.getElementById('revenueForecastChart').getContext('2d');
        const revenueForecastChart = new Chart(revenueForecastCtx, {
            type: 'bar',
            data: {
                labels: ['Q4 2023', 'Q1 2024', 'Q2 2024', 'Q3 2024', 'Q4 2024'],
                datasets: [{
                    label: 'Revenue',
                    data: [2200, 2400, 2700, 2900, 3200],
                    backgroundColor: [
                        'rgba(0, 136, 255, 0.8)',
                        'rgba(0, 136, 255, 0.8)',
                        'rgba(0, 245, 212, 0.8)',
                        'rgba(0, 245, 212, 0.8)',
                        'rgba(0, 255, 136, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw + 'K';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(51, 51, 51, 0.5)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value + 'K';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

     


async function fetchAllFinancialData() {
    // Get all loading elements
    const loadingElements = {
        profit: document.getElementById("loading-profit-animation"),
        stock: document.getElementById("loading-stock-animation"),
        dette: document.getElementById("loading-dette-animation"),
        credit: document.getElementById("loading-credit-animation")
    };

    // Initialize the donut chart context
    const profitBreakdownCtx = document.getElementById('profitBreakdownChart')?.getContext('2d');

    // Get all text elements to hide during loading
    const textElements = {
        profit: document.getElementById("profit-text"),
        stock: document.getElementById("stock-text"),
        dette: document.getElementById("dette-text"),
        credit: document.getElementById("credit-text")
    };

    // Get all value elements to update
    const valueElements = {
        profit: document.getElementById("profit-value"),
        stock: document.getElementById("stock-value"),
        dette: document.getElementById("dette-value"),
        credit: document.getElementById("credit-client-value"),
        tresorerie: document.getElementById("tresorerie-total")
    };

    // Stock-specific elements
    const toggleBtn = document.getElementById("toggle-details");
    const stockDetailsElement = document.getElementById("stock-details");

    try {
        // Show loading states for all elements
        Object.values(loadingElements).forEach(el => el?.classList.remove("hidden"));
        Object.values(textElements).forEach(el => el?.classList.add("hidden"));
        if (toggleBtn) toggleBtn.classList.add("hidden");
        if (stockDetailsElement) stockDetailsElement.classList.add("hidden");

        // Make single API call
        const response = await fetch(API_CONFIG.getApiUrl("/total-profit-page"));
        const data = await response.json();
        
        if ('error' in data) {
            throw new Error(data.error);
        }

        // Update profit
        const totalProfit = data.total_profit || 0;
        if (valueElements.profit) {
            valueElements.profit.textContent = formatNumber(totalProfit) + " DZD";
            valueElements.profit.style.color = totalProfit >= 0 ? '#00ff88' : '#ff0033';
        }

        // Update stock
        const totalStock = parseFloat(data.details?.total_stock) || 0;
        if (valueElements.stock) {
            valueElements.stock.textContent = formatNumber(totalStock) + " DZD";
        }

        // Update dette fournisseur
        const totalDette = data.details?.total_dette || 0;
        if (valueElements.dette) {
            valueElements.dette.textContent = formatNumber(totalDette) + " DZD";
        }

        // Update credit client
        const creditClientValue = data.details?.credit_client || 0;
        if (valueElements.credit) {
            valueElements.credit.textContent = formatNumber(creditClientValue) + " DZD";
        }

        // Update tresorerie
        const totalTresorerie = data.details?.total_tresorie || 0;
        if (valueElements.tresorerie) {
            valueElements.tresorerie.textContent = formatNumber(totalTresorerie) + ' DZD';
        }

        // Update the Profit Breakdown chart
        if (profitBreakdownCtx) {
            // Calculate total assets for percentage calculation
            const totalAssets = totalStock + creditClientValue + totalTresorerie + totalDette;
            
            // Calculate percentages
            const stockPercentage = (totalStock / totalAssets * 100).toFixed(1);
            const creditPercentage = (creditClientValue / totalAssets * 100).toFixed(1);
            const tresoreriePercentage = (totalTresorerie / totalAssets * 100).toFixed(1);
            const dettePercentage = (totalDette / totalAssets * 100).toFixed(1);
            
            // Update the breakdown text values
            document.getElementById('stock-breakdown').textContent = `${formatNumber(totalStock)} DZD (${stockPercentage}%)`;
            document.getElementById('credit-breakdown').textContent = `${formatNumber(creditClientValue)} DZD (${creditPercentage}%)`;
            document.getElementById('tresorerie-breakdown').textContent = `${formatNumber(totalTresorerie)} DZD (${tresoreriePercentage}%)`;
            document.getElementById('dette-breakdown').textContent = `${formatNumber(totalDette)} DZD (${dettePercentage}%)`;
            
            // Destroy existing chart if it exists
            if (profitBreakdownChart) {
                profitBreakdownChart.destroy();
            }
            
            // Create new chart
            profitBreakdownChart = new Chart(profitBreakdownCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Stock', 'Credit Client', 'Trésorerie', 'Dette'],
                    datasets: [{
                        data: [totalStock, creditClientValue, totalTresorerie, totalDette],
                        backgroundColor: [
                            '#00f5d4',
                            '#0088ff',
                            '#9b5de5',
                            '#ff0033'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = ((value / totalAssets) * 100).toFixed(1);
                                    return `${context.label}: ${formatNumber(value)} DZD (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

    } catch (error) {
        console.error("Error fetching financial data:", error);
        
        // Set error states
        Object.values(valueElements).forEach(el => {
            if (el) el.textContent = "Error";
        });
    } finally {
        // Hide loading states and show text
        Object.values(loadingElements).forEach(el => el?.classList.add("hidden"));
        Object.values(textElements).forEach(el => el?.classList.remove("hidden"));
        if (toggleBtn) toggleBtn.classList.remove("hidden");
    }
}

// You can now call this single function instead of all the separate ones
// fetchAllFinancialData();




        // Helper functions for loading states
        function showAllLoaders() {
            const loaderElements = document.querySelectorAll('.kpi-loader');
            const textElements = document.querySelectorAll('[id$="-text"]');
            loaderElements.forEach(el => el?.classList.remove('hidden'));
            textElements.forEach(el => el?.classList.add('hidden'));
        }

        function hideAllLoaders() {
            const loaderElements = document.querySelectorAll('.kpi-loader');
            const textElements = document.querySelectorAll('[id$="-text"]');
            loaderElements.forEach(el => el?.classList.add('hidden'));
            textElements.forEach(el => el?.classList.remove('hidden'));
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', async () => {
            // Initial data load and start countdown
            await refreshAll();
            startCountdown();

            // Set up manual refresh button
            document.getElementById('manual-refresh-btn')?.addEventListener('click', async () => {
                // Clear existing countdown
                if (refreshIntervalId) {
                    clearInterval(refreshIntervalId);
                }
                
                // Perform refresh (this will show/hide loaders)
                await refreshAll();
                
                // Start new countdown after refresh completes
                startCountdown();
            });

            // Start countdown timer initially
            startCountdown();

            // Add toggle functionality for bank details
            document.getElementById('show-more-bank')?.addEventListener('click', function() {
                const details = document.getElementById('bank-details');
                const icon = this.querySelector('svg');
                details.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
                const span = this.querySelector('span');
                span.textContent = details.classList.contains('hidden') ? 'Bank Details' : 'Hide Details';
            });

            // Add event listener for metric selector
            document.getElementById('metric-selector')?.addEventListener('change', (e) => {
                currentMetric = e.target.value;
                updateKPITrendsChart();
            });
        });
    </script>
</body>
</html>