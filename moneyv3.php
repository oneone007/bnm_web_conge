<?php
session_start();

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
    header("Location: Acess_Denied");
    exit();
}

// Database connection
require_once 'db/db_connect.php';

// Get latest bank data from database
$query = "SELECT * FROM bank_data ORDER BY date DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $last_record = $result->fetch_assoc();
    $bna_sold = isset($last_record['bna_sold']) ? (float)$last_record['bna_sold'] : 0;
    $baraka_sold = isset($last_record['baraka_sold']) ? (float)$last_record['baraka_sold'] : 0;
    $bna_remise = isset($last_record['bna_remise']) ? (float)$last_record['bna_remise'] : 0;
    $baraka_remise = isset($last_record['baraka_remise']) ? (float)$last_record['baraka_remise'] : 0;
    
    $total_sold = $bna_sold + $baraka_sold;
    $total_remise = $bna_remise + $baraka_remise;
    $grand_total = $total_sold + $total_remise;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'IBM Plex Mono', monospace;
        }
        .data-panel {
            border: 1px solid #333;
            background-color: #1e1e1e;
        }
        .data-header {
            border-bottom: 1px solid #333;
            background-color: #1a1a1a;
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
            border-bottom: 1px solid #333333;
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
            border-top: 1px solid #333;
            margin: 1.5rem 0;
        }
        .metric-card {
            background-color: #252525;
            border-left: 4px solid;
            padding: 0.75rem 1rem;
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
            background-color: #333;
            border-radius: 2px;
            overflow: hidden;
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
            background-color: rgba(51, 51, 51, 0.3);
            transition: all 0.2s ease;
            border: 1px solid transparent;
            opacity: 0.5;
        }
        .chart-filter:hover {
            background-color: rgba(51, 51, 51, 0.5);
            opacity: 0.8;
        }
        .chart-filter.active {
            border-color: currentColor;
            background-color: rgba(51, 51, 51, 0.7);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-lab-dark text-gray-300 font-mono">
    <header class="border-b border-lab-border bg-lab-dark sticky top-0 z-10">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-lab-accent font-bold text-xl">BNM</span>
                    <span class="text-lab-highlight font-bold text-xl">ANALYSE</span>
                    <span class="text-xs px-2 py-1 bg-lab-panel rounded">v3.1.0</span>
                </div>
                <div class="text-xs text-gray-400">
                    <div class="flex items-center space-x-4">
                        <span>Update in: <span id="refresh-time" class="text-gray-100">5min 00sec</span></span>
                        <button id="manual-refresh-btn" class="px-2 py-1 bg-lab-accent text-black rounded hover:bg-opacity-80">⟳ Refresh Now</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6">
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
                const [stockResponse, creditResponse, caisseResponse, paiementResponse, 
                       bankResponse, detteResponse, checksResponse] = await Promise.all([
                    fetch("http://192.168.1.94:5000/stock-summary"),
                    fetch("http://192.168.1.94:5000/credit-client"),
                    fetch("http://192.168.1.94:5000/caisse"),
                    fetch("http://192.168.1.94:5000/paiement-net"),
                    fetch("http://192.168.1.94:5000/total-bank"),
                    fetch("http://192.168.1.94:5000/fourniseurdettfond"),
                    fetch("http://192.168.1.94:5000/total-checks")
                ]);

                const [stockData, creditData, caisseData, paiementData, 
                       bankData, detteData, checksData] = await Promise.all([
                    stockResponse.json(),
                    creditResponse.json(),
                    caisseResponse.json(),
                    paiementResponse.json(),
                    bankResponse.json(),
                    detteResponse.json(),
                    checksResponse.json()
                ]);


                // Get last update time from bank data response
                const lastBankUpdate = new Date().toLocaleString('fr-FR');

                // Helper function to calculate percentage change
                function calculatePercentChange(current, previous) {
                    // Handle null, undefined, or invalid values
                    if (previous === null || previous === undefined || isNaN(previous) || isNaN(current)) {
                        return { value: 0, text: '-', color: '#ffffff' };
                    }
                    
                    // Convert to numbers
                    const currentNum = parseFloat(current) || 0;
                    const previousNum = parseFloat(previous) || 0;
                    
                    // Handle case where previous is zero
                    if (previousNum === 0) {
                        if (currentNum === 0) {
                            return { value: 0, text: '0%', color: '#ffffff' };
                        } else {
                            // When previous is 0 but current is not, show "NEW" instead of infinity
                            return { value: 0, text: 'NEW', color: '#00ff88' };
                        }
                    }
                    
                    const change = ((currentNum - previousNum) / Math.abs(previousNum)) * 100;
                    
                    // Handle very large changes (over 1000%)
                    if (Math.abs(change) > 1000) {
                        const sign = change > 0 ? '+' : '';
                        return { value: change, text: `${sign}${change > 0 ? '↑' : '↓'}1000%+`, color: change > 0 ? '#00ff88' : '#ff0033' };
                    }
                    
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

                // Get last saved data for comparison from database
                let lastSavedData = null;
                try {
                    const response = await fetch('http://192.168.1.94:5000/previous-performance-data');
                    const historyData = await response.json();
                    console.log('History data received:', historyData); // Debug log
                    
                    if (historyData.success && historyData.data) {
                        // Transform database structure to match the old JSON structure
                        const dbData = historyData.data;
                        console.log('DB Data:', dbData); // Debug log
                        
                        lastSavedData = {
                            stock: {
                                total: dbData.stock?.previous?.total_stock,
                                principale: dbData.stock?.previous?.principal,
                                depot_reserver: dbData.stock?.previous?.depot_reserver,
                                hangar: dbData.stock?.previous?.hangar,
                                hangar_reserve: dbData.stock?.previous?.hangar_reserver
                            },
                            credit_client: dbData.creance?.previous?.creance,
                            tresorerie: {
                                total: dbData.tresorie?.previous?.total_tresorie,
                                caisse: dbData.tresorie?.previous?.caisse,
                                paiement_net: dbData.tresorie?.previous?.paiement_net,
                                bank: {
                                    total: dbData.bank?.previous?.total_bank,
                                    bna: {
                                        sold: dbData.bank?.previous?.bna_sold,
                                        remise: dbData.bank?.previous?.bna_remise
                                    },
                                    baraka: {
                                        sold: dbData.bank?.previous?.baraka_sold,
                                        remise: dbData.bank?.previous?.baraka_remise
                                    }
                                }
                            },
                            dette: {
                                total: dbData.dette?.previous?.total_dette,
                                fournisseur: dbData.dette?.previous?.dette_fournisseur,
                                checks: {
                                    total: dbData.dette?.previous?.total_checks,
                                    bna: dbData.bank?.previous?.bna_check,
                                    baraka: dbData.bank?.previous?.baraka_check
                                }
                            }
                        };
                        console.log('Transformed lastSavedData:', lastSavedData); // Debug log
                    }
                } catch (error) {
                    console.error('Error loading last saved data from database:', error);
                }

                // Build the table HTML
                let html = '';

                // Stock Section
                html += createRow('STOCK TOTAL', stockData.total_stock, '-', 
                    lastSavedData?.stock?.total, '-', '-', false, '#00f5d4');
                html += createRow('Stock Principale', stockData.STOCK_principale, '-', 
                    lastSavedData?.stock?.principale, '-', '-', true, 'rgba(0, 245, 212, 0.7)');
                html += createRow('Depot Reserver', stockData.depot_reserver, '-', 
                    lastSavedData?.stock?.depot_reserver, '-', '-', true, 'rgba(0, 245, 212, 0.7)');
                html += createRow('Hangar', stockData.hangar, '-', 
                    lastSavedData?.stock?.hangar, '-', '-', true, 'rgba(0, 245, 212, 0.7)');
                html += createRow('Hangar Reserve', stockData.hangarréserve, '-', 
                    lastSavedData?.stock?.hangar_reserve, '-', '-', true, 'rgba(0, 245, 212, 0.7)');

                // Credit Client
                html += createRow('CREANCE CLIENT', creditData.credit_client, '-', 
                    lastSavedData?.credit_client, '-', '-', false, '#0088ff');

                const currentTresorerieTotal = caisseData.caisse + paiementData.Total_Paiment + bankData.total_bank;

                // Tresorerie Section
                html += createRow('TOTAL TRÉSORERIE', currentTresorerieTotal, '-', 
                    lastSavedData?.tresorerie?.total, '-', '-', false, '#9b5de5');

                // Caisse Section
                html += createRow('CAISSE', caisseData.caisse, '-', 
                    lastSavedData?.tresorerie?.caisse, '-', '-', true, 'rgba(155, 93, 229, 0.7)');

                // Paiement Net
                html += createRow('PAIEMENT NET', paiementData.Total_Paiment, '-', 
                    lastSavedData?.tresorerie?.paiement_net, '-', '-', true, 'rgba(155, 93, 229, 0.7)');

                // Bank Section
                html += createRow('BANK TOTAL', bankData.total_bank, lastBankUpdate, 
                    lastSavedData?.tresorerie?.bank?.total, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                html += createRow('BNA Sold', bankData.details.BNA.sold, lastBankUpdate, 
                    lastSavedData?.tresorerie?.bank?.bna?.sold, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                html += createRow('BNA Remise', bankData.details.BNA.remise, lastBankUpdate, 
                    lastSavedData?.tresorerie?.bank?.bna?.remise, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
               
                html += createRow('Baraka Sold', bankData.details.Baraka.sold, lastBankUpdate, 
                    lastSavedData?.tresorerie?.bank?.baraka?.sold, '-', '-', true, 'rgba(155, 93, 229, 0.7)');
                html += createRow('Baraka Remise', bankData.details.Baraka.remise, lastBankUpdate, 
                    lastSavedData?.tresorerie?.bank?.baraka?.remise, '-', '-', true, 'rgba(155, 93, 229, 0.7)');

                const currentDetteTotal = detteData.value + checksData.total_checks;

                // Dette Section
                html += createRow('DETTE TOTAL', currentDetteTotal, '-', 
                    lastSavedData?.dette?.total, '-', '-', false, '#ff0033');

                // Checks Section
                html += createRow('Dette Fournisseur', detteData.value, '-', 
                    lastSavedData?.dette?.fournisseur, '-', '-', true, 'rgba(255, 0, 51, 0.7)');

                html += createRow('TOTAL CHECKS', checksData.total_checks, lastBankUpdate, 
                    lastSavedData?.dette?.checks?.total, '-', '-', true, 'rgba(255, 0, 51, 0.7)');
                html += createRow('BNA Checks', checksData.details.BNA.checks, lastBankUpdate, 
                    lastSavedData?.dette?.checks?.bna, '-', '-', true, 'rgba(255, 0, 51, 0.7)');
                html += createRow('Baraka Checks', checksData.details.Baraka.checks, lastBankUpdate, 
                    lastSavedData?.dette?.checks?.baraka, '-', '-', true, 'rgba(255, 0, 51, 0.7)');

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
                // Update UI with remaining functions
                await Promise.all([
                    updateKPITrendsChart(),
                    updatePerformanceMatrix()
                ]);
            } catch (error) {
                console.error("Error during refresh:", error);
            } finally {
                hideAllLoaders(); // Hide all loading indicators
                isRefreshing = false;
                console.log('Refresh completed');
                
                // Re-enable refresh button
                if (refreshBtn) {
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('opacity-50');
                }

                // Reset countdown after refresh completes
                countdown = REFRESH_INTERVAL;
                updateTimerDisplay();
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
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

            // Add toggle functionality for bank details
            document.getElementById('show-more-bank')?.addEventListener('click', function() {
                const details = document.getElementById('bank-details');
                const icon = this.querySelector('svg');
                details.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
                const span = this.querySelector('span');
                span.textContent = details.classList.contains('hidden') ? 'Bank Details' : 'Hide Details';
            });
        });

        // Market Growth Chart
        const marketGrowthCtx = document.getElementById('marketGrowthChart').getContext('2d');
        let marketGrowthChart = null;

        // Store visibility state for each series
        const seriesVisibility = {
            dette: true,
            stock: true,
            tresorerie: true,
            creance: true
        };

        // Set up filter button click handlers
        document.querySelectorAll('.chart-filter').forEach(button => {
            button.addEventListener('click', function() {
                const series = this.dataset.series;
                this.classList.toggle('active');
                seriesVisibility[series] = !seriesVisibility[series];
                updateKPITrendsChart();
            });
        });

        async function updateKPITrendsChart() {
            try {
                // Fetch data from consolidated endpoint
                const response = await fetch('http://192.168.1.94:5000/kpi-trends-data');
                const chartData = await response.json();

                if (chartData.error) {
                    console.error('Error fetching KPI trends data:', chartData.error);
                    return;
                }

                // Destroy existing chart if it exists
                if (marketGrowthChart) {
                    marketGrowthChart.destroy();
                }

                // Filter datasets based on visibility
                const visibleDatasets = chartData.datasets.filter(dataset => {
                    if (dataset.label === 'Dette Fournisseur' && !seriesVisibility.dette) return false;
                    if (dataset.label === 'Stock Total' && !seriesVisibility.stock) return false;
                    if (dataset.label === 'Trésorerie' && !seriesVisibility.tresorerie) return false;
                    if (dataset.label === 'Crédit Client' && !seriesVisibility.creance) return false;
                    return true;
                });

                // Add fill property to datasets
                visibleDatasets.forEach(dataset => {
                    dataset.fill = true;
                });

                // Create new chart with improved configuration
                marketGrowthChart = new Chart(marketGrowthCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: visibleDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR', {
                                            style: 'currency',
                                            currency: 'DZD',
                                            maximumFractionDigits: 2
                                        }).format(value);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: 'rgba(51, 51, 51, 0.5)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('fr-FR', {
                                            style: 'currency',
                                            currency: 'DZD',
                                            notation: 'compact',
                                            maximumFractionDigits: 2
                                        }).format(value);
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
                                    callback: function(value, index, ticks) {
                                        const date = new Date(this.getLabelForValue(value));
                                        return date.toLocaleTimeString('fr-FR', {
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        });
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error updating KPI trends chart:', error);
                // Show error state in chart container
                const chartContainer = document.getElementById('marketGrowthChart').parentElement;
                chartContainer.innerHTML = '<div class="error-message">Failed to load chart data</div>';
            }
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

        // Initialize everything when DOM is loaded
        // Show all loading animations
        function showAllLoaders() {
            document.querySelectorAll('.kpi-loader').forEach(loader => {
                loader.classList.remove('hidden');
            });
            document.querySelectorAll('[id$="-text"]').forEach(text => {
                text.classList.add('hidden');
            });
        }

        // Hide all loading animations
        function hideAllLoaders() {
            document.querySelectorAll('.kpi-loader').forEach(loader => {
                loader.classList.add('hidden');
            });
            document.querySelectorAll('[id$="-text"]').forEach(text => {
                text.classList.remove('hidden');
            });
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
        });
    </script>
</body>
</html>