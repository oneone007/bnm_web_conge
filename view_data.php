<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}


// Allow access only for 'Admin', 'Developer', and 'Comptable'
if (!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Admin', 'Developer', 'Comptable'])) {
  header("Location: Acess_Denied");
  exit();
}


// Include database connection
require_once 'db/db_connect.php';

// Get records from database ordered by creation_time desc
$sql = "SELECT * FROM bank ORDER BY creation_time DESC";
$result = $conn->query($sql);

$bank_records = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $bank_records[] = $row;
    }
}
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
    <script src="theme.js"></script>
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

  /* Dark mode styles */
  body.dark-mode {
      background-color: #1f2937;
      color: #f3f4f6;
  }

  .dark-mode h1 {
      color: #f3f4f6;
  }

  .dark-mode .back-link {
      color: #60a5fa;
  }

  .dark-mode .data-table {
      background-color: #374151;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }

  .dark-mode .data-table th,
  .dark-mode .data-table td {
      border-bottom-color: #4b5563;
      color: #f3f4f6;
  }

  .dark-mode .data-table .bna-header {
      background-color: #3b8237;
      border-color: #3b8237;
  }

  .dark-mode .data-table .baraka-header {
      background-color: #cc4e06;
      border-color: #cc4e06;
  }

  .dark-mode .data-table tr:nth-child(even) {
      background-color: #2d3748;
  }

  .dark-mode .data-table tr:hover {
      background-color: #4b5563;
  }

  .dark-mode .total-row {
      background-color: #2d3748 !important;
  }

  .dark-mode .no-data {
      color: #9ca3af;
  }

  .dark-mode .bank-header {
      background-color: #2d3748;
  }

  /* Ensure text remains visible in dark mode */
  .dark-mode .data-table td,
  .dark-mode .data-table th {
      color: #f3f4f6;
  }

  .dark-mode .bna-total,
  .dark-mode .baraka-total,
  .dark-mode .total-row {
      color: #f3f4f6;
  }
</style>

</head>
<body>

<style>
    
    .content {
      margin-left: 0 !important; /* Force no margin for sidebar */
      transition: none !important; /* Disable animations */
    }
    
    .sidebar {
      display: none !important; /* Completely hide sidebar */
    }
    
    .sidebar-hidden {
      display: none !important;
    }
    </style>
<div class="container">
  <a href="bank" class="back-link">← Back to Form</a>
  <h1>Bank Data History</h1>
  
  <?php if (empty($bank_records)): ?>
    <div class="no-data">No bank data available yet. Please submit data using the form.</div>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th style="color :black">Date & Time</th>
          <th colspan="3" class="bna-header">BNA</th>
          <th colspan="3" class="baraka-header">EL BARAKA</th>
          <th style="color :black">Total</th>
          <th style="color :black">Total Chèques</th>
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
<?php foreach ($bank_records as $record): ?>
  <tr>
    <td><?php echo htmlspecialchars($record['creation_time']); ?></td>
    <td><?php echo number_format($record['bna_sold'], 2); ?></td>
    <td><?php echo number_format($record['bna_remise'], 2); ?></td>
    <td><?php echo number_format($record['bna_check'], 2); ?></td>
    <td><?php echo number_format($record['baraka_sold'], 2); ?></td>
    <td><?php echo number_format($record['baraka_remise'], 2); ?></td>
    <td><?php echo number_format($record['baraka_check'], 2); ?></td>
    <td><strong><?php echo number_format($record['total_bank'], 2); ?></strong></td>
    <td><strong><?php echo number_format($record['total_checks'], 2); ?></strong></td>
  </tr>
<?php endforeach; ?>

      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>