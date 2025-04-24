
<div class="layout">
  <div class="content">

    <div class="dashboard">

      <!-- Total Profit -->


      <div class="card">
  <div class="card-title">FONDS PROPRE (Total Profit)</div>
  <p id="fonds-propre-value" class="text-lg font-medium text-gray-900 dark:text-gray-100">
    Loading...
  </p>
</div>


      <div class="row">

      <!-- Dettes & Créances -->
      <div class="card half">
        <div class="card-title">DETTES FOURNISSEUR</div>
      
        <!-- Spinner while loading -->
        <div id="loading-dette-animation" class="flex justify-center items-center my-4">
          <div class="spinner border-4 border-gray-500 border-t-transparent rounded-full w-12 h-12 animate-spin"></div>
        </div>
      
        <!-- Result Text -->
        <p id="dette-text" class="text-lg font-medium text-gray-900 dark:text-gray-100 hidden">
          <span id="dette-value" class="font-semibold"></span>
          <span class="text-green-600 dark:text-green-400">DZD</span>
        </p>
      
        <!-- Show more button -->
        <button id="toggle-dette-details" class="mt-4 text-blue-600 hover:underline hidden">
          Show more
        </button>
      
        <!-- Dette details section -->
        <div id="dette-details" class="mt-4 hidden text-sm text-gray-800 dark:text-gray-200 space-y-3">
          <div>
            <strong>Total Échu:</strong>
            <div><span id="dette-echu"></span> DZD</div>
          </div>
          <div>
            <strong>Total Stock:</strong>
            <div><span id="dette-stock"></span> DZD</div>
          </div>
        </div>
      </div>
      




        <div class="card half">
          <div class="card-title"  >Total Stock</div>
          
          <!-- Spinner while loading -->
          <div id="loading-stock-animation" class="flex justify-center items-center my-4">
            <div class="spinner border-4 border-gray-500 border-t-transparent rounded-full w-12 h-12 animate-spin"></div>
          </div>
          
          <!-- Result Text -->
          <p id="stock-text" class="text-lg font-medium text-gray-900 dark:text-gray-100 hidden">
            <span id="stock-value" class="font-semibold"></span>
            <span class="text-green-600 dark:text-green-400">DZD</span>
          </p>
        
          <!-- Show more button -->
          <button id="toggle-details" class="mt-4 text-blue-600 hover:underline hidden">
            Show more
          </button>
        
          <!-- Stock details section -->
          <div id="stock-details" class="mt-4 hidden text-sm text-gray-800 dark:text-gray-200 space-y-3">
            <div>
              <strong>Stock Principale:</strong>
              <div><span id="stock-principale"></span> DZD</div>
            </div>
            <div>
              <strong>Hangar:</strong>
              <div><span id="stock-hangar"></span> DZD</div>
            </div>
            <div>
              <strong>Hangar Réserve:</strong>
              <div><span id="stock-hangarre"></span> DZD</div>
            </div>
            <div>
              <strong>Dépôt Réserver:</strong>
              <div><span id="stock-depot"></span> DZD</div>
            </div>
          </div>
     
        </div>
        
<!-- CREANCE CLIENT (Top) -->
<div class="card">
  <div class="card-title">CREANCE CLIENT</div>

  <!-- Spinner while loading -->
  <div id="loading-credit-animation" class="flex justify-center items-center my-4">
    <div class="spinner border-4 border-gray-500 border-t-transparent rounded-full w-12 h-12 animate-spin"></div>
  </div>

  <!-- Result Text -->
  <p id="credit-text" class="text-lg font-medium text-gray-900 dark:text-gray-100 hidden">
    <span id="credit-client-value" class="font-semibold"></span>
    <span class="text-green-600 dark:text-green-400">DZD</span>
  </p>
</div>

<!-- TRÉSORERIE (Bottom) -->
<div class="card">
  <div class="card-title">TRÉSORERIE</div>
  <div class="nested-row">
    <!-- LA CAISSE (JS filled) -->
    <div class="card nested-card">
      <div class="card-title">LA CAISSE</div>
      <div id="la-caisse-value" class="card-value" data-caisse="0">Chargement...</div>
    </div>

    <!-- BANQUE (PHP) -->
    <div class="card nested-card">
      <div class="card-title">BANQUE</div>
      <div id="banque-value" class="card-value" data-banktotal="0">
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
    </div>
  </div>

  <!-- Display TRÉSORERIE total here -->
  <div id="tresorerie-total" style="margin-top: 15px; font-weight: bold; font-size: 1.1em; color: #333;">
    TOTAL TRÉSORERIE: ...
  </div>


  <script>
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
</script>



      <button id="show-more-bank" class="show-more-btn">Show Details</button>
      
      <!-- Hidden details div -->
      <div id="bank-details" style="display: none; margin-top: 10px;">
        <?php if (isset($last_record)): ?>
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <th style="text-align: left; padding: 5px; border-bottom: 1px solid #ddd;">Bank</th>
              <th style="text-align: left; padding: 5px; border-bottom: 1px solid #ddd;">Sold</th>
              <th style="text-align: left; padding: 5px; border-bottom: 1px solid #ddd;">Remise</th>
            </tr>
            <tr>
              <td style="padding: 5px; border-bottom: 1px solid #eee;">BNA</td>
              <td style="padding: 5px; border-bottom: 1px solid #eee;"><?php echo number_format($bna_sold, 2); ?></td>
              <td style="padding: 5px; border-bottom: 1px solid #eee;"><?php echo number_format($last_record['bna_remise'], 2); ?></td>
            </tr>
            <tr>
              <td style="padding: 5px;">EL BARAKA</td>
              <td style="padding: 5px;"><?php echo number_format($baraka_sold, 2); ?></td>
              <td style="padding: 5px;"><?php echo number_format($last_record['baraka_remise'], 2); ?></td>
            </tr>
          </table>
          <div style="margin-top: 5px; font-size: 0.8em; color: #666;">
            Last updated: <?php echo $last_record['date']; ?>
          </div>
        <?php else: ?>
          <div>No detailed data available</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>