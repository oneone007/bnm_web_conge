<?php
// Specify the path to the JSON file
$json_file = 'bank.json';

// Check if the JSON file exists
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $bank_records = json_decode($json_data, true);
    
    // If file is empty or invalid, initialize as empty array
    if (json_last_error() !== JSON_ERROR_NONE) {
        $bank_records = [];
    }
} else {
    $bank_records = [];
}

// Sort records by date (newest first)
usort($bank_records, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Web</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="journal.css">

    <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 20px;
  }
  
  .container {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  h1 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
  }
  
  .back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #007bff;
    text-decoration: none;
  }
  
  .back-link:hover {
    text-decoration: underline;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
  }
  
  .data-table th, .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
  }
  
  .data-table th {
    font-weight: 500;
  }
  
  .data-table .bna-header {
    background-color: #4cab47;
    color: white;
    border: 2px solid #4cab47;
  }

  .data-table .baraka-header {
    background-color: #fc6108;
    color: white;
    border: 2px solid #fc6108;
  }
  
  .data-table tr:nth-child(even) {
    background-color: #f8f9fa;
  }
  
  .data-table tr:hover {
    background-color: #f1f1f1;
  }
  
  .bna-total, .baraka-total {
    font-weight: bold;
  }
  
  .total-row {
    background-color: #e9ecef !important;
    font-weight: bold;
  }
  
  .no-data {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 18px;
  }
  
  .bank-header {
    background-color: #f8f9fa;
    font-weight: bold;
  }
  
  @media (max-width: 768px) {
    .data-table {
      display: block;
      overflow-x: auto;
    }
  }
</style>

</head>
<body>
<div id="sidebar-container"></div>

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
<div class="container">
  <a href="bank" class="back-link">← Back to Form</a>
  <h1>Bank Data History</h1>
  
  <?php if (empty($bank_records)): ?>
    <div class="no-data">No bank data available yet. Please submit data using the form.</div>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Date & Time</th>
          <th colspan="3" class="bna-header">BNA</th>
          <th colspan="3" class="baraka-header">EL BARAKA</th>
          <th>Total</th>
          <th>Total Chèques</th>
        </tr>
        <tr>
          <th></th>
          <th>Sold</th>
          <th>Remise</th>
          <th>Chèque</th>
          <th>Sold</th>
          <th>Remise</th>
          <th>Chèque</th>
          <th></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
<?php foreach ($bank_records as $record): 
  $bna_sold = $record['bna_sold'] ?? 0;
  $bna_remise = $record['bna_remise'] ?? 0;
  $bna_check = $record['bna_check'] ?? 0;

  $baraka_sold = $record['baraka_sold'] ?? 0;
  $baraka_remise = $record['baraka_remise'] ?? 0;
  $baraka_check = $record['baraka_check'] ?? 0;

  $row_total = $bna_sold + $bna_remise + $baraka_sold + $baraka_remise;
  $check_total = $bna_check + $baraka_check;
?>
  <tr>
    <td><?php echo htmlspecialchars($record['date']); ?></td>
    <td><?php echo number_format($bna_sold, 2); ?></td>
    <td><?php echo number_format($bna_remise, 2); ?></td>
    <td><?php echo number_format($bna_check, 2); ?></td>
    <td><?php echo number_format($baraka_sold, 2); ?></td>
    <td><?php echo number_format($baraka_remise, 2); ?></td>
    <td><?php echo number_format($baraka_check, 2); ?></td>
    <td><strong><?php echo number_format($row_total, 2); ?></strong></td>
    <td><strong><?php echo number_format($check_total, 2); ?></strong></td>
  </tr>
<?php endforeach; ?>

      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>