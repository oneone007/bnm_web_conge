

<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)
$inactive_time = 3600;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate session lifetime
    $session_lifetime = time() - $_SESSION['last_activity'];

    if ($session_lifetime > $inactive_time) {
        session_unset(); // Unset session variables
        session_destroy(); // Destroy the session
        header("Location: BNM?session_expired=1"); // Redirect to login page with message
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['username']) && in_array($_SESSION['username'], ['vente', 'achat'])) {
    header("Location: Acess_Denied");
    exit();
}

// ------------------------------------------------------------------------

$bna_sold = isset($_POST['bna_sold']) ? floatval($_POST['bna_sold']) : 0;
$bna_remise = isset($_POST['bna_remise']) ? floatval($_POST['bna_remise']) : 0;
$baraka_sold = isset($_POST['baraka_sold']) ? floatval($_POST['baraka_sold']) : 0;
$baraka_remise = isset($_POST['baraka_remise']) ? floatval($_POST['baraka_remise']) : 0;

$banque_total = $bna_sold + $bna_remise + $baraka_sold + $baraka_remise;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Web</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>


    <style>
:root {
--primary-color: #28a745;
  --primary-light: #e6e9ff;
  --secondary-color: #3f37c9;
  --success-color: #4cc9f0;
  --warning-color: #f8961e;
  --danger-color: #f72585;
  --dark-color: #212529;
  --light-color: #f8f9fa;
  --gray-color: #6c757d;
  --border-radius: 12px;
  --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  --transition: all 0.3s ease;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background-color: #f5f7fa;
  color: var(--dark-color);
  line-height: 1.6;
}

.dashboard-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--dark-color);
}

.last-updated {
  font-size: 0.9rem;
  color: var(--gray-color);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.kpi-card {
  background: white;
  border-radius: var(--border-radius);
  padding: 1.1rem;
  box-shadow: var(--box-shadow);
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.kpi-card.primary {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
}

.kpi-card.primary .kpi-header h2,
.kpi-card.primary .kpi-value,
.kpi-card.primary .currency {
  color: white;
}

.kpi-card.wide {
  grid-column: span 2;
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.kpi-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark-color);
}

.kpi-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: var(--primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-color);
}

.kpi-card.primary .kpi-icon {
  background-color: rgba(85, 26, 26, 0.2);
  color: white;
}

.kpi-value {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 1rem 0;
  color: var(--dark-color);
}

.currency {
  font-size: 1rem;
  color: var(--gray-color);
  margin-left: 0.3rem;
}

.kpi-trend {
  font-size: 0.9rem;
  color: black; /* Changed to black */
}


.trend-indicator {
  font-weight: 600;
}

.trend-indicator.positive {
  color: #2ecc71;
}

.trend-indicator.negative {
  color: var(--danger-color);
}

.kpi-loader {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top-color: var(--primary-color);
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.kpi-details-toggle {
  margin-top: 1rem;
}

.details-btn {
  background: none;
  border: none;
  color: var(--primary-color);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.3rem 0;
  transition: var(--transition);
}

.details-btn:hover {
  opacity: 0.8;
}

.details-btn svg {
  transition: var(--transition);
}

.details-btn .rotate {
  transform: rotate(180deg);
}

.kpi-details {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.detail-label {
  color: var(--gray-color);
  margin-right: 0.5rem;
  flex-shrink: 0;
}

.detail-value {
  font-weight: 600;
  color: var(--dark-color);
  white-space: nowrap;
  flex-shrink: 0;
}


/* Treasury section */
.treasury-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin: 1.5rem 0;
}

.treasury-item {
  background: var(--light-color);
  border-radius: 10px;
  padding: 1.2rem;
}

.treasury-item h3 {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--gray-color);
  margin-bottom: 0.5rem;
}

.treasury-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--dark-color);
}

.bank-details-btn {
  background: none;
  border: none;
  color: var(--primary-color);
  font-size: 0.85rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  cursor: pointer;
  margin-top: 0.5rem;
  padding: 0;
}

.bank-details-btn svg {
  transition: var(--transition);
}

.bank-details-btn .rotate {
  transform: rotate(180deg);
}

.bank-details {
  margin-top: 1.5rem;
}

.bank-table {
  width: 100%;
  border-collapse: collapse;
}

.bank-table th, .bank-table td {
  padding: 0.8rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.bank-table th {
  font-weight: 600;
  color: var(--gray-color);
  font-size: 0.85rem;
  text-transform: uppercase;
}

.bank-update-time {
  font-size: 0.8rem;
  color: var(--gray-color);
  margin-top: 0.5rem;
  text-align: right;
}

.treasury-total {
  font-size: 1.2rem;
  font-weight: 600;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.total-amount {
  color: var(--primary-color);
  font-size: 1.4rem;
}

.hidden {
  display: none;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .kpi-card.wide {
    grid-column: span 1;
  }
  
  .treasury-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .dashboard-container {
    padding: 1rem;
  }
  
  .kpi-value {
    font-size: 1.5rem;
  }
}

.trend-indicator {
  font-weight: bold;
}

.chart-container {
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 20px;
  margin: 20px auto; /* Changed from '20px 0' to '20px auto' for horizontal centering */
  position: relative;
  width: 80%;
  max-width: 1200px; /* Optional: prevents the container from getting too wide on large screens */
}

.chart-container canvas {
  height: 400px !important;
  width: 100% !important;
}

.date-range-controls {
  display: flex;
  gap: 15px;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.date-input-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-input-group label {
  font-weight: 500;
  color: #5a5c69;
}

.date-range-controls input[type="datetime-local"] {
  padding: 8px 12px;
  border: 1px solid #d1d3e2;
  border-radius: 4px;
  font-size: 14px;
}

.filter-btn {
  background-color: #4e73df;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.filter-btn:hover {
  background-color: #3a5bc7;
}

@media (max-width: 768px) {
  .date-range-controls {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .date-input-group {
    width: 100%;
  }
  
  .date-input-group input {
    width: 100%;
  }
  
  .filter-btn {
    width: 100%;
  }
}

/* Add to your existing CSS */
.kpi-trend {
  font-size: 0.9rem;
  color: #6c757d;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.trend-indicator {
  font-weight: 600;
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
}

.trend-indicator.positive {
  background-color: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.trend-indicator.negative {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.trend-indicator.neutral {
  background-color: rgba(108, 117, 125, 0.1);
  color: #6c757d;
}

/* Add to your existing CSS */
.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-weight: 500;
  color: #5a5c69;
}

.detail-value {
  font-weight: 600;
}

.detail-trend {
  font-size: 0.8rem;
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  margin-left: 0.5rem;
  min-width: 60px;
  text-align: right;
}

.detail-trend.positive {
  background-color: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.detail-trend.negative {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.detail-trend.neutral {
  background-color: rgba(108, 117, 125, 0.1);
  color: #6c757d;
}
</style>
</head>
<body>
  

<script>
fetch("side")
  .then(response => response.text())
  .then(html => {
    const container = document.getElementById("sidebar-container");
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    container.innerHTML = tempDiv.innerHTML;

    // After DOM injection, dynamically load sidebar script
    const script = document.createElement('script');
    script.src = 'sidebar.js'; // Move all logic into sidebar.js
    document.body.appendChild(script);
  })
  .catch(error => console.error("Error loading sidebar:", error));


</script>


<div id="sidebar-container"></div>



<div class="dashboard-container">
  <div class="dashboard-header">
    <h1>Total Profit</h1>
    <div class="last-updated">
  Last updated: <span id="update-time">Just now</span>
</div>  </div>

  <div class="dashboard-grid">
    <!-- FONDS PROPRE (Main KPI) -->
    <div class="kpi-card wide primary">
      <div class="kpi-header">
        <h2>FONDS PROPRE</h2>
        <div class="kpi-icon">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="10"></circle>
    <path d="M12 4v16M8 10h8"></path>
    <path d="M8 14h8"></path>
  </svg>
</div>

      </div>
      <div class="kpi-value" id="fonds-propre-value">Loading...</div>
<div class="kpi-trend">
  <span class="trend-indicator neutral">N/A</span> VS Last Time
</div>
    </div>

    <!-- DETTES FOURNISSEUR -->
    <div class="kpi-card">
  <div class="kpi-header">
    <h2>DETTES FOURNISSEUR</h2>
    <div class="kpi-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
        <polyline points="14 2 14 8 20 8"></polyline>
        <line x1="16" y1="13" x2="8" y2="13"></line>
        <line x1="16" y1="17" x2="8" y2="17"></line>
        <polyline points="10 9 9 9 8 9"></polyline>
      </svg>
    </div>
  </div>
  <div id="loading-dette-animation" class="kpi-loader">
    <div class="spinner"></div>
  </div>
  <div id="dette-text" class="kpi-value hidden">
    <span id="dette-value"></span> <span class="currency">DZD</span>
  </div>
  <div class="kpi-trend">
    <span class="trend-indicator neutral">N/A</span> VS Last Time
  </div>
  <div id="dette-details" class="kpi-details hidden">
    <div class="detail-item">
      <span class="detail-label">Total Échu:</span>
      <span class="detail-value"><span id="dette-echu"></span> DZD</span>
    </div>
    <div class="detail-item">
      <span class="detail-label">Total Stock:</span>
      <span class="detail-value"><span id="dette-stock"></span> DZD</span>
    </div>
  </div>
</div>


        <!-- CREANCE CLIENT -->
        <div class="kpi-card">
      <div class="kpi-header">
        <h2>CREANCE CLIENT</h2>
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
      </div>
      <div id="loading-credit-animation" class="kpi-loader">
        <div class="spinner"></div>
      </div>
      <div id="credit-text" class="kpi-value hidden">
        <span id="credit-client-value"></span> <span class="currency">DZD</span>
      </div>
    </div>
    <!-- Total Stock -->
<div class="kpi-card">
  <div class="kpi-header">
    <h2>TOTAL STOCK</h2>
    <div class="kpi-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>
    </div>
  </div>
  <div id="loading-stock-animation" class="kpi-loader">
    <div class="spinner"></div>
  </div>
  <div id="stock-text" class="kpi-value hidden">
    <span id="stock-value"></span> <span class="currency">DZD</span>
    <div class="kpi-trend">
      <span class="trend-indicator neutral">N/A</span> VS Last Time
    </div>
  </div>
  <div class="kpi-details-toggle">
    <button id="toggle-details" class="details-btn hidden">
      <span>View Details</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </button>
  </div>
  <div id="stock-details" class="kpi-details hidden">
    <div class="detail-item">
      <span class="detail-label">Stock Principale:</span>
      <span class="detail-value"><span id="stock-principale"></span> DZD</span>
      <span class="detail-trend neutral">N/A</span>
    </div>
    <div class="detail-item">
      <span class="detail-label">Hangar:</span>
      <span class="detail-value"><span id="stock-hangar"></span> DZD</span>
      <span class="detail-trend neutral">N/A</span>
    </div>
    <div class="detail-item">
      <span class="detail-label">Hangar Réserve:</span>
      <span class="detail-value"><span id="stock-hangarre"></span> DZD</span>
      <span class="detail-trend neutral">N/A</span>
    </div>
    <div class="detail-item">
      <span class="detail-label">Dépôt Réserver:</span>
      <span class="detail-value"><span id="stock-depot"></span> DZD</span>
      <span class="detail-trend neutral">N/A</span>
    </div>
  </div>
</div>

    <!-- TRÉSORERIE -->
    <div class="kpi-card wide">
      <div class="kpi-header">
        <h2>TRÉSORERIE</h2>
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
            <path d="M12 12h.01"></path>
            <path d="M17 12h.01"></path>
            <path d="M7 12h.01"></path>
          </svg>
        </div>
      </div>
      
      <div class="treasury-grid">
        <!-- LA CAISSE -->
        <div class="treasury-item">
          <h3>LA CAISSE</h3>
          <div id="la-caisse-value" class="treasury-value" data-caisse="0">Loading...</div>
        </div>
        
        <!-- BANQUE -->
        <div class="treasury-item">
          <h3>BANQUE</h3>
          <div id="banque-value" class="treasury-value" data-banktotal="0">
          <?php
        $grand_total = 0;
        $json_file = 'bank.json';
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $bank_records = json_decode($json_data, true);
            
            if (json_last_error() === JSON_ERROR_NONE && !empty($bank_records)) {
                usort($bank_records, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });

                $last_record = $bank_records[0];

                $bna_sold = isset($last_record['bna_sold']) ? (float)$last_record['bna_sold'] : 0;
                $baraka_sold = isset($last_record['baraka_sold']) ? (float)$last_record['baraka_sold'] : 0;
                $bna_remise = isset($last_record['bna_remise']) ? (float)$last_record['bna_remise'] : 0;
                $baraka_remise = isset($last_record['baraka_remise']) ? (float)$last_record['baraka_remise'] : 0;

                $total_sold = $bna_sold + $baraka_sold;
                $total_remise = $bna_remise + $baraka_remise;
                $grand_total = $total_sold + $total_remise;

                echo "<br>Total: " . number_format($grand_total, 2) . " DZD";
                echo "<script>document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('banque-value').setAttribute('data-banktotal', $grand_total);
                });</script>";
            } else {
                echo "No data available";
            }
        } else {
            echo "No data available";
        }
        ?>
          </div>
          <button id="show-more-bank" class="bank-details-btn">
            <span>Bank Details</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
        </div>
      </div>
      
      <div id="bank-details" class="bank-details hidden">
        <?php if (isset($last_record)): ?>
          <table class="bank-table">
            <thead>
              <tr>
                <th>Bank</th>
                <th>Sold</th>
                <th>Remise</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>BNA</td>
                <td><?php echo number_format($bna_sold, 2); ?> DZD</td>
                <td><?php echo number_format($last_record['bna_remise'], 2); ?> DZD</td>
              </tr>
              <tr>
                <td>EL BARAKA</td>
                <td><?php echo number_format($baraka_sold, 2); ?> DZD</td>
                <td><?php echo number_format($last_record['baraka_remise'], 2); ?> DZD</td>
              </tr>
            </tbody>
          </table>
          <div class="bank-update-time">
            Last updated: <?php echo $last_record['date']; ?>
          </div>
        <?php else: ?>
          <div class="no-data">No detailed data available</div>
        <?php endif; ?>
      </div>
      
      <div id="tresorerie-total" class="treasury-total">
        TOTAL TRÉSORERIE: <span class="total-amount">...</span>
      </div>
    </div>
  </div>
</div>

<script>
// Update timestamp
function updateTimestamp() {
  const now = new Date();
  const options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  };
  document.getElementById('update-time').textContent = now.toLocaleDateString('en-US', options);
}

// Fetch caisse and banque data
async function fetchCaisseAndBanque() {
  const formatDZD = (value) => {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(value) + ' DZD';
  };

  try {
    // Fetch caisse value
    const caisseResponse = await fetch('http://192.168.1.94:5000/caisse');
    const caisseData = await caisseResponse.json();
    const caisseValue = caisseData.caisse !== undefined ? caisseData.caisse : 0;

    // Set the caisse value on the page
    document.getElementById('la-caisse-value').innerHTML = formatDZD(caisseValue);
    document.getElementById('la-caisse-value').setAttribute('data-caisse', caisseValue);

    // Now fetch the banque total
    const banqueTotal = parseFloat(document.getElementById('banque-value').getAttribute('data-banktotal')) || 0;

    // Calculate total trésorerie
    const tresorerieTotal = caisseValue + banqueTotal;

    // Update the trésorerie total display
    document.getElementById('tresorerie-total').textContent = "TOTAL TRÉSORERIE: " + formatDZD(tresorerieTotal);

  } catch (error) {
    console.error('Error fetching caisse:', error);
    document.getElementById('la-caisse-value').innerHTML = '<strong>Erreur</strong>';
  }
}

document.addEventListener('DOMContentLoaded', fetchCaisseAndBanque);

// Toggle bank details
document.getElementById('show-more-bank').addEventListener('click', function() {
  const details = document.getElementById('bank-details');
  const icon = this.querySelector('svg');
  details.classList.toggle('hidden');
  icon.classList.toggle('rotate');
  
  // Update button text
  const span = this.querySelector('span');
  span.textContent = details.classList.contains('hidden') ? 'Bank Details' : 'Hide Details';
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  fetchTotalStock();

  
  // Set interval to refresh data every 5 minutes
  setInterval(fetchTotalStock, 300000);
  
  // Add toggle functionality for other detail sections
  document.getElementById('toggle-details').addEventListener('click', function() {
    const details = document.getElementById('stock-details');
    const icon = this.querySelector('svg');
    details.classList.toggle('hidden');
    icon.classList.toggle('rotate');
    
    // Update button text
    const span = this.querySelector('span');
    span.textContent = details.classList.contains('hidden') ? 'View Details' : 'Hide Details';
  });
  
  document.getElementById('toggle-dette-details').addEventListener('click', function() {
    const details = document.getElementById('dette-details');
    const icon = this.querySelector('svg');
    details.classList.toggle('hidden');
    icon.classList.toggle('rotate');
    
    // Update button text
    const span = this.querySelector('span');
    span.textContent = details.classList.contains('hidden') ? 'View Details' : 'Hide Details';
  });
});
</script>




<div class="chart-container">
  <div id="dateFilter"></div>
  <canvas id="fondsPropreChart"></canvas>
</div>


<script>






  
async function fetchTotalStock() {
  const loadingElement = document.getElementById("loading-stock-animation");
  const stockTextElement = document.getElementById("stock-text");
  const stockValueElement = document.getElementById("stock-value");
  const toggleBtn = document.getElementById("toggle-details");
  const stockDetailsElement = document.getElementById("stock-details");

  try {
    // Show loading state
    loadingElement.classList.remove("hidden");
    stockTextElement.classList.add("hidden");
    toggleBtn.classList.add("hidden");
    stockDetailsElement.classList.add("hidden");

    // Fetch current stock data
    const response = await fetch("http://192.168.1.94:5000/stock-summary");
    if (!response.ok) throw new Error("Network error");

    const data = await response.json();
    const totalStock = parseFloat(data.total_stock) || 0;
    const stockPrincipale = parseFloat(data.STOCK_principale) || 0;
    const hangar = parseFloat(data.hangar) || 0;
    const hangarReserve = parseFloat(data.hangarréserve) || 0;
    const depotReserver = parseFloat(data.depot_reserver) || 0;

    // Format and display values
    stockValueElement.textContent = formatNumber(totalStock);
    document.getElementById("stock-principale").textContent = formatNumber(stockPrincipale);
    document.getElementById("stock-hangar").textContent = formatNumber(hangar);
    document.getElementById("stock-hangarre").textContent = formatNumber(hangarReserve);
    document.getElementById("stock-depot").textContent = formatNumber(depotReserver);

    // Show elements
    loadingElement.classList.add("hidden");
    stockTextElement.classList.remove("hidden");
    toggleBtn.classList.remove("hidden");

    // Process trend for total stock
    await processStockTrend('total', totalStock, 
      stockTextElement.querySelector('.trend-indicator'));

    // Process trends for each detail
    await processStockTrend('principale', stockPrincipale, 
      document.querySelector('#stock-principale').closest('.detail-item').querySelector('.detail-trend'));
    
    await processStockTrend('hangar', hangar, 
      document.querySelector('#stock-hangar').closest('.detail-item').querySelector('.detail-trend'));
    
    await processStockTrend('hangar_reserve', hangarReserve, 
      document.querySelector('#stock-hangarre').closest('.detail-item').querySelector('.detail-trend'));
    
    await processStockTrend('depot', depotReserver, 
      document.querySelector('#stock-depot').closest('.detail-item').querySelector('.detail-trend'));

    // Store all stock values
    await fetch("http://192.168.1.94:5000/store-stock", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        total_stock: totalStock,
        stock_principale: stockPrincipale,
        hangar: hangar,
        hangar_reserve: hangarReserve,
        depot_reserver: depotReserver
      })
    });

  } catch (error) {
    console.error("Error fetching total stock price:", error);
    stockValueElement.textContent = "Erreur lors du chargement";
    stockTextElement.classList.add("error");
    
    loadingElement.classList.add("hidden");
    stockTextElement.classList.remove("hidden");
  }
}

async function processStockTrend(stockType, currentValue, trendElement) {
  try {
    const response = await fetch(`http://192.168.1.94:5000/previous-stock/${stockType}`);
    const previousData = await response.json();

    if (previousData && previousData.value !== undefined) {
      const previousValue = parseFloat(previousData.value);

      // If values are exactly the same, mark as neutral
      if (currentValue === previousValue) {
        trendElement.className = 'detail-trend neutral';
        trendElement.textContent = '0.00%';
        return;
      }

      const percentageChange = ((currentValue - previousValue) / previousValue) * 100;
      const absChange = Math.abs(percentageChange);

      // Dynamically adjust decimals
      let decimals = 2;
      if (absChange < 0.01) decimals = 6;
      if (absChange < 0.0001) decimals = 8;

      const roundedChange = absChange.toFixed(decimals);
      const trendClass = percentageChange > 0 ? 'positive' : 'negative';
      const trendSymbol = percentageChange > 0 ? '+' : '-';

      trendElement.className = `detail-trend ${trendClass}`;
      trendElement.textContent = `${trendSymbol}${roundedChange}%`;
    } else {
      trendElement.className = 'detail-trend neutral';
      trendElement.textContent = 'N/A';
    }
  } catch (error) {
    console.error(`Error processing trend for ${stockType}:`, error);
    trendElement.className = 'detail-trend neutral';
    trendElement.textContent = 'N/A';
  }
}



// Helper function to format numbers
function formatNumber(value) {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
}

// Initialize when page loads
document.addEventListener("DOMContentLoaded", fetchTotalStock);

// Helper function to format numbers

// Initialize when page loads
document.addEventistener("DOMContentLoaded", fetchTotalStock);
  function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "0.00";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }




  let lastDettePercentageChange = null;

async function fetchFournisseurDette() {
  const loading = document.getElementById("loading-dette-animation");
  const detteText = document.getElementById("dette-text");
  const detteValue = document.getElementById("dette-value");
  const trendElement = document.querySelector('#dette-text').parentElement.querySelector('.kpi-trend .trend-indicator');

  try {
    loading.classList.remove("hidden");
    detteText.classList.add("hidden");

    const response = await fetch("http://192.168.1.94:5000/fourniseurdettfond");
    if (!response.ok) throw new Error("Échec de la récupération des données");

    const data = await response.json();
    const totalDette = parseFloat(data.value) || 0;

    // Format and display the value
    const formattedDette = new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(totalDette);

    detteValue.textContent = formattedDette;
    loading.classList.add("hidden");
    detteText.classList.remove("hidden");

    // Fetch previous value
    const previousResponse = await fetch("http://192.168.1.94:5000/previous-dette-fournisseur");
    const previousData = await previousResponse.json();

    if (previousData && previousData.value !== undefined) {
      const previousValue = parseFloat(previousData.value);

      // Calculate percentage change if not already calculated
      if (lastDettePercentageChange === null) {
        const percentageChange = ((totalDette - previousValue) / previousValue) * 100;
        const roundedChange = Math.abs(percentageChange).toFixed(5);

        const trendClass = percentageChange > 0 ? 'negative' : 'positive'; // Note: For debts, increase is negative
        const trendSymbol = percentageChange > 0 ? '+' : '-';
        
        trendElement.className = `trend-indicator ${trendClass}`;
        trendElement.textContent = `${trendSymbol}${roundedChange}%`;

        // Store the calculated percentage for later use
        lastDettePercentageChange = roundedChange;
      } else {
        // Display the stored percentage change
        const trendSymbol = lastDettePercentageChange > 0 ? '+' : '-';
        trendElement.className = `trend-indicator ${lastDettePercentageChange > 0 ? 'negative' : 'positive'}`;
        trendElement.textContent = `${trendSymbol}${Math.abs(lastDettePercentageChange)}%`;
      }

      // Store the new dette value if significantly different
      const valueDifference = Math.abs(previousValue - totalDette);
      if (valueDifference > 0.001) {
        await fetch("http://192.168.1.94:5000/store-dette-fournisseur", {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            value: totalDette,
            date: new Date().toISOString()
          })
        });
      }
    } else {
      // No previous data available
      trendElement.textContent = 'N/A';
      trendElement.className = 'trend-indicator neutral';

      // Store the current dette value as the first entry
      await fetch("http://192.168.1.94:5000/store-dette-fournisseur", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          value: totalDette,
          date: new Date().toISOString()
        })
      });
    }
  } catch (error) {
    console.error("Erreur lors du chargement des dettes :", error);
    loading.innerHTML = `<p class="error-message">Erreur de chargement</p>`;
    trendElement.textContent = "N/A";
    trendElement.className = "trend-indicator neutral";
  }
}

// Run the function when page loads
document.addEventListener("DOMContentLoaded", function() {
  fetchFournisseurDette();
});

// Call the function on page load
document.addEventListener("DOMContentLoaded", fetchFournisseurDette);

async function fetchCreditClient() {
  const loading = document.getElementById("loading-credit-animation");
  const creditText = document.getElementById("credit-text");
  const creditValue = document.getElementById("credit-client-value");

  try {
    const response = await fetch("http://192.168.1.94:5000/credit-client");
    if (!response.ok) throw new Error("Fetch failed");

    const data = await response.json();
    loading.classList.add("hidden");

    if (data.credit_client !== undefined) {
      creditValue.textContent = data.credit_client.toLocaleString("fr-FR");
    } else {
      creditValue.textContent = "Donnée non disponible";
    }

    creditText.classList.remove("hidden");
  } catch (error) {
    console.error("Erreur en récupérant la créance client:", error);
    loading.innerHTML = `<p class="text-red-600">Erreur de chargement</p>`;
  }
}

document.addEventListener("DOMContentLoaded", fetchCreditClient);

// async function fetchCaisseAndBanque() {
//   const formatDZD = (value) => {
//     return new Intl.NumberFormat('fr-FR', {
//       minimumFractionDigits: 2,
//       maximumFractionDigits: 2
//     }).format(value) + ' DZD';
//   };

//   try {
//     const caisseResponse = await fetch('http://192.168.1.94:5000/caisse');
//     const caisseData = await caisseResponse.json();
//     const caisseValue = caisseData.caisse !== undefined ? `<strong>${formatDZD(caisseData.caisse)}</strong>` : 'N/A';
//     document.getElementById('la-caisse-value').innerHTML = caisseValue;
//   } catch (error) {
//     console.error('Error fetching caisse:', error);
//     document.getElementById('la-caisse-value').innerHTML = '<strong>Erreur</strong>';
//   }


// }
async function fetchCaisseAndBanque() {
  const formatDZD = (value) => {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(value) + ' DZD';
  };

  try {
    // Fetch caisse value
    const caisseResponse = await fetch('http://192.168.1.94:5000/caisse');
    const caisseData = await caisseResponse.json();
    const caisseValue = caisseData.caisse !== undefined ? caisseData.caisse : 0;

    // Set the caisse value on the page
    document.getElementById('la-caisse-value').innerHTML = formatDZD(caisseValue);
    document.getElementById('la-caisse-value').setAttribute('data-caisse', caisseValue);

    // Now fetch the banque total
    const banqueTotal = parseFloat(document.getElementById('banque-value').getAttribute('data-banktotal')) || 0;

    // Calculate total trésorerie
    const tresorerieTotal = caisseValue + banqueTotal;

    // Update the trésorerie total display
    document.getElementById('tresorerie-total').textContent = "TOTAL TRÉSORERIE: " + formatDZD(tresorerieTotal);

  } catch (error) {
    console.error('Error fetching caisse:', error);
    document.getElementById('la-caisse-value').innerHTML = '<strong>Erreur</strong>';
  }
}


document.addEventListener('DOMContentLoaded', fetchCaisseAndBanque);

let lastPercentageChange = null;  // Store the percentage change after the first calculation

async function calculateFondsPropre() {
  try {
    // Fetch current data
    const stockResponse = await fetch("http://192.168.1.94:5000/stock-summary");
    const stockData = await stockResponse.json();
    const stockValue = stockData.total_stock || 0;

    const creditResponse = await fetch("http://192.168.1.94:5000/credit-client");
    const creditData = await creditResponse.json();
    const creditClientValue = creditData.credit_client || 0;

    const caisseResponse = await fetch("http://192.168.1.94:5000/caisse");
    const caisseData = await caisseResponse.json();
    const caisseValue = caisseData.caisse || 0;

    const banqueTotal = parseFloat(document.getElementById('banque-value').getAttribute('data-banktotal')) || 0;
    const tresorerieTotal = caisseValue + banqueTotal;

    const detteResponse = await fetch("http://192.168.1.94:5000/fourniseurdettfond");
    const detteData = await detteResponse.json();
    const detteValue = detteData.value || 0;

    const fondsPropre = stockValue + creditClientValue + tresorerieTotal - detteValue;

    // Format and display the value
    const formattedFondsPropre = new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(fondsPropre) + " DZD";

    document.getElementById("fonds-propre-value").textContent = formattedFondsPropre;

    // Fetch previous value
    const previousResponse = await fetch("http://192.168.1.94:5000/previous-fonds-propre");
    const previousData = await previousResponse.json();

    const trendElement = document.querySelector('.kpi-trend .trend-indicator');

    if (previousData && previousData.value !== undefined) {
      const previousValue = previousData.value;

      // Calculate percentage change if not already calculated
      if (lastPercentageChange === null) {
        const percentageChange = ((fondsPropre - previousValue) / previousValue) * 100;
        const roundedChange = Math.abs(percentageChange).toFixed(5);  // Show up to 5 decimal places

        const trendClass = percentageChange > 0 ? 'positive' : 'negative';
        const trendSymbol = percentageChange > 0 ? '+' : '-';
        
        trendElement.className = `trend-indicator ${trendClass}`;
        trendElement.textContent = `${trendSymbol}${roundedChange}%`;

        // Store the calculated percentage for later use
        lastPercentageChange = roundedChange;
      } else {
        // Display the stored percentage change
        const trendSymbol = lastPercentageChange > 0 ? '+' : '-';
        trendElement.className = `trend-indicator ${lastPercentageChange > 0 ? 'positive' : 'negative'}`;
        trendElement.textContent = `${trendSymbol}${Math.abs(lastPercentageChange)}%`;
      }

      // Store the new fondsPropre value
      const valueDifference = Math.abs(previousValue - fondsPropre);
      if (valueDifference > 0.001) {
        await fetch("http://192.168.1.94:5000/store-fonds-propre", {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            value: fondsPropre,
            date: new Date().toISOString()
          })
        });

        // Update the previous value after storing
        previousData.value = fondsPropre;
      }
    } else {
      // No previous data available
      trendElement.textContent = 'N/A';
      trendElement.className = 'trend-indicator neutral';

      // Store the current fondsPropre value as the first entry
      await fetch("http://192.168.1.94:5000/store-fonds-propre", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          value: fondsPropre,
          date: new Date().toISOString()
        })
      });
    }

  } catch (error) {
    console.error("Error calculating FONDS PROPRE:", error);
    document.getElementById("fonds-propre-value").textContent = "Erreur de calcul";
    const trendElement = document.querySelector('.kpi-trend .trend-indicator');
    trendElement.textContent = "N/A";
    trendElement.className = "trend-indicator neutral";
  }
}


// Run the function
calculateFondsPropre();

document.addEventListener("DOMContentLoaded", calculateFondsPropre);

async function renderFondsPropreChart() {
  try {
    // Fetch chart data
    const response = await fetch("http://192.168.1.94:5000/fonds-propre-chart-data");
    const chartData = await response.json();
    
    if (chartData.error) {
      console.error("Error fetching chart data:", chartData.error);
      return;
    }
    
    // Get canvas element
    const ctx = document.getElementById('fondsPropreChart').getContext('2d');
    
    // Create chart
    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartData.labels,
        datasets: [{
          label: 'Fonds Propre (DZD)',
          data: chartData.values,
          borderColor: '#4e73df',
          backgroundColor: 'rgba(78, 115, 223, 0.05)',
          pointBackgroundColor: '#4e73df',
          pointBorderColor: '#fff',
          pointHoverRadius: 5,
          pointHoverBackgroundColor: '#4e73df',
          pointHoverBorderColor: '#fff',
          pointHitRadius: 10,
          pointBorderWidth: 2,
          borderWidth: 2,
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: 'Evolution des Fonds Propres',
            font: {
              size: 16
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return new Intl.NumberFormat('fr-FR', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).format(context.raw) + ' DZD';
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            title: {
              display: true,
              text: 'Date'
            }
          },
          y: {
            grid: {
              color: "rgba(0, 0, 0, 0.05)"
            },
            title: {
              display: true,
              text: 'Montant (DZD)'
            },
            ticks: {
              callback: function(value) {
                return new Intl.NumberFormat('fr-FR', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).format(value);
              }
            }
          }
        }
      }
    });
    
    // Add date range filter
    addDateFilter(chart, chartData.min_date, chartData.max_date);
    
  } catch (error) {
    console.error("Error rendering chart:", error);
  }
}

function addDateFilter(chart, minDate, maxDate) {
  const dateFilterDiv = document.getElementById('dateFilter');
  
  // Create date inputs
  dateFilterDiv.innerHTML = `
    <div class="date-range-controls">
      <div class="date-input-group">
        <label for="startDate">De:</label>
        <input type="datetime-local" id="startDate" min="${minDate}" max="${maxDate}" value="${minDate}">
      </div>
      <div class="date-input-group">
        <label for="endDate">À:</label>
        <input type="datetime-local" id="endDate" min="${minDate}" max="${maxDate}" value="${maxDate}">
      </div>
      <button id="applyFilter" class="filter-btn">Filtrer</button>
    </div>
  `;
  
  // Add filter event
  document.getElementById('applyFilter').addEventListener('click', function() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    
    // Filter the chart data
    chart.data.labels = chart.config.data.labels.filter((label, index) => {
      const date = new Date(label);
      return date >= startDate && date <= endDate;
    });
    
    chart.data.datasets[0].data = chart.config.data.datasets[0].data.filter((value, index) => {
      const label = chart.config.data.labels[index];
      const date = new Date(label);
      return date >= startDate && date <= endDate;
    });
    
    chart.update();
  });
}

// Call the function when the page loads
document.addEventListener('DOMContentLoaded', function() {
  renderFondsPropreChart();
});


  // Toggle detail visibility


  const updateSpan = document.getElementById('update-time');
  const startTime = new Date();

  function getTimeAgo(seconds) {
    if (seconds < 30) return 'Just now';
    if (seconds < 60) return '30 seconds ago';
    const mins = Math.floor(seconds / 60);
    if (mins === 1) return '1 minute ago';
    return `${mins} minutes ago`;
  }

  function updateTimer() {
    const now = new Date();
    const secondsPassed = Math.floor((now - startTime) / 1000);
    updateSpan.textContent = getTimeAgo(secondsPassed);
  }

  // Initial state
  updateTimer();

  // Update every 30 seconds
  setInterval(updateTimer, 30000);

</script>



</body>
</html>
