<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

$page_identifier = 'bank';
require_once 'check_permission.php';

// Include database connection
require_once 'db/db_connect.php';

// Get all active banks
function getActiveBanks($conn) {
    $sql = "SELECT id_bank, bank_name, bank_code, logo_filename FROM banks WHERE is_active = TRUE ORDER BY bank_name";
    $result = $conn->query($sql);
    $banks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $banks[] = $row;
        }
    }
    return $banks;
}

// Get last transaction data for each bank
function getLastTransactionData($conn) {
    $sql = "SELECT bt.bank_id, b.bank_name, bt.remise, bt.sold, bt.check_amount 
            FROM bank_transactions bt
            JOIN banks b ON bt.bank_id = b.id_bank
            WHERE bt.creation_time = (
                SELECT MAX(creation_time) FROM bank_transactions bt2 WHERE bt2.bank_id = bt.bank_id
            )
            AND b.is_active = TRUE";
    
    $result = $conn->query($sql);
    $lastData = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lastData[$row['bank_id']] = $row;
        }
    }
    return $lastData;
}

$banks = getActiveBanks($conn);
$lastTransactionData = getLastTransactionData($conn);

// Initialize default form values
$default_values = [];
foreach ($banks as $bank) {
    $bank_id = $bank['id_bank'];
    $default_values[$bank_id] = [
        'remise' => $lastTransactionData[$bank_id]['remise'] ?? '',
        'sold' => $lastTransactionData[$bank_id]['sold'] ?? '',
        'check_amount' => $lastTransactionData[$bank_id]['check_amount'] ?? ''
    ];
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    $transaction_data = [];
    
    // Validate input for each bank
    foreach ($banks as $bank) {
        $bank_id = $bank['id_bank'];
        $remise = filter_input(INPUT_POST, "bank_{$bank_id}_remise", FILTER_VALIDATE_FLOAT);
        $sold = filter_input(INPUT_POST, "bank_{$bank_id}_sold", FILTER_VALIDATE_FLOAT);
        $check_amount = filter_input(INPUT_POST, "bank_{$bank_id}_check", FILTER_VALIDATE_FLOAT);
        
        if ($remise === false || $sold === false || $check_amount === false) {
            $errors[] = "Please enter valid numbers for {$bank['bank_name']} fields.";
        } else {
            $transaction_data[$bank_id] = [
                'remise' => $remise ?: 0,
                'sold' => $sold ?: 0,
                'check_amount' => $check_amount ?: 0
            ];
        }
    }
    
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();
        try {
            $transaction_date = date('Y-m-d');
            
            // Prepare SQL statement for bank transactions (creation_time will be auto-set)
            $sql = "INSERT INTO bank_transactions (bank_id, transaction_date, remise, sold, check_amount, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            
            // Insert transaction for each bank
            foreach ($transaction_data as $bank_id => $data) {
                if (!$stmt->bind_param("isdddi",
                    $bank_id,
                    $transaction_date,
                    $data['remise'],
                    $data['sold'],
                    $data['check_amount'],
                    $_SESSION['user_id']
                )) {
                    throw new Exception("Error binding parameters: " . $stmt->error);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Error executing statement: " . $stmt->error);
                }
            }
            
            $conn->commit();
            $success_message = "Data saved successfully!";
            
            // Update default values with newly submitted values
            foreach ($transaction_data as $bank_id => $data) {
                $default_values[$bank_id] = $data;
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        } finally {
            if (isset($stmt) && $stmt !== false) $stmt->close();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Get summary by date - show only last record or all records based on view mode
function getSummaryByDate($conn, $date = null, $viewMode = 'last') {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    if ($viewMode === 'all') {
        // Show all transactions (remove date filter when "All Records" is selected)
        $sql = "SELECT 
                    SUM(bt.remise) as total_remise,
                    SUM(bt.sold) as total_sold,
                    SUM(bt.check_amount) as total_checks,
                    SUM(bt.remise + bt.sold) as total_bank,
                    COUNT(DISTINCT bt.creation_time) as transaction_sessions,
                    COUNT(*) as bank_records,
                    'all' as view_type
                FROM bank_transactions bt
                JOIN banks b ON bt.bank_id = b.id_bank
                WHERE b.is_active = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } else {
        // Show only last record for the date
        $sql = "SELECT MAX(creation_time) as latest_time
                FROM bank_transactions 
                WHERE DATE(creation_time) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $latestTime = $result->fetch_assoc()['latest_time'];
        
        if (!$latestTime) {
            return null; // No transactions for this date
        }
        
        // Get summary for the latest transaction time only
        $sql = "SELECT 
                    SUM(bt.remise) as total_remise,
                    SUM(bt.sold) as total_sold,
                    SUM(bt.check_amount) as total_checks,
                    SUM(bt.remise + bt.sold) as total_bank,
                    COUNT(*) as bank_records,
                    bt.creation_time,
                    'last' as view_type
                FROM bank_transactions bt
                JOIN banks b ON bt.bank_id = b.id_bank
                WHERE bt.creation_time = ? AND b.is_active = TRUE
                GROUP BY bt.creation_time";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $latestTime);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

// Get detailed transactions by date - grouped by creation_time
function getTransactionsByDate($conn, $date = null, $viewMode = 'last') {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    if ($viewMode === 'all') {
        // Get all transactions regardless of date
        $sql = "SELECT 
                    bt.creation_time,
                    b.bank_name,
                    b.bank_code,
                    bt.remise,
                    bt.sold,
                    bt.check_amount
                FROM bank_transactions bt
                JOIN banks b ON bt.bank_id = b.id_bank
                WHERE b.is_active = TRUE
                ORDER BY bt.creation_time DESC, b.bank_name";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } else {
        // Get transactions for specific date
        $sql = "SELECT 
                    bt.creation_time,
                    b.bank_name,
                    b.bank_code,
                    bt.remise,
                    bt.sold,
                    bt.check_amount
                FROM bank_transactions bt
                JOIN banks b ON bt.bank_id = b.id_bank
                WHERE DATE(bt.creation_time) = ?
                ORDER BY bt.creation_time DESC, b.bank_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
    }
    
    $result = $stmt->get_result();
    
    // Group transactions by creation_time
    $groupedTransactions = [];
    $allBanks = [];
    
    while ($row = $result->fetch_assoc()) {
        $time = $row['creation_time'];
        $bankCode = $row['bank_code'];
        
        if (!isset($groupedTransactions[$time])) {
            $groupedTransactions[$time] = [];
        }
        
        $groupedTransactions[$time][$bankCode] = [
            'bank_name' => $row['bank_name'],
            'remise' => $row['remise'],
            'sold' => $row['sold'],
            'check_amount' => $row['check_amount']
        ];
        
        if (!in_array($bankCode, $allBanks)) {
            $allBanks[] = $bankCode;
        }
    }
    
    return ['transactions' => $groupedTransactions, 'banks' => $allBanks];
}

// Get date range for dropdown
function getAvailableDates($conn) {
    $sql = "SELECT DISTINCT DATE(creation_time) as transaction_date 
            FROM bank_transactions 
            ORDER BY transaction_date DESC 
            LIMIT 30"; // Last 30 dates
    $result = $conn->query($sql);
    $dates = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['transaction_date'];
        }
    }
    return $dates;
}

// Handle date filter and view mode
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$view_mode = isset($_GET['view_mode']) ? $_GET['view_mode'] : 'last';
$summaryData = getSummaryByDate($conn, $filter_date, $view_mode);
$transactionData = getTransactionsByDate($conn, $filter_date, $view_mode);
$transactionDetails = $transactionData['transactions'];
$availableBanks = $transactionData['banks'];
$availableDates = getAvailableDates($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Bank - Improved</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="theme.js"></script>
    

    <style>
    :root {
      --bg-primary: #f8f9fa;
      --bg-secondary: white;
      --bg-tertiary: #f8f9fa;
      --text-primary: #495057;
      --text-secondary: #6c757d;
      --border-color: #ced4da;
      --border-light: #e1e5e9;
      --border-table: #dee2e6;
      --shadow: rgba(0, 0, 0, 0.1);
      --input-bg: white;
      --table-hover: #f8f9fa;
    }

    .dark-mode,
    .dark {
      --bg-primary: #111827;
      --bg-secondary: #1f2937;
      --bg-tertiary: #374151;
      --text-primary: #f9fafb;
      --text-secondary: #d1d5db;
      --border-color: #4b5563;
      --border-light: #374151;
      --border-table: #4b5563;
      --shadow: rgba(0, 0, 0, 0.3);
      --input-bg: #374151;
      --table-hover: #374151;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--bg-primary);
      color: var(--text-primary);
      margin: 0;
      padding: 20px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      background-color: var(--bg-secondary);
      color: var(--text-primary);
      border-radius: 10px;
      box-shadow: 0 4px 6px var(--shadow);
      overflow: hidden;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .header {
      background-color: var(--bg-tertiary);
      color: var(--text-primary);
      padding: 30px;
      text-align: center;
      border-bottom: 2px solid var(--border-light);
    }

    .header h1 {
      margin: 0 0 10px 0;
      font-size: 2em;
      font-weight: 600;
      color: var(--text-primary);
    }

    .header p {
      margin: 0 0 20px 0;
      color: var(--text-secondary);
      font-size: 1.1em;
    }

    .header-btn {
      display: inline-block;
      padding: 12px 24px;
      background-color: var(--bg-secondary);
      color: var(--text-primary);
      text-decoration: none;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px var(--shadow);
    }

    .header-btn:hover {
      background-color: var(--bg-primary);
      border-color: var(--text-primary);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px var(--shadow);
    }

    .form-section {
      padding: 30px;
      background-color: var(--bg-secondary);
    }

    .bank-card {
      border: 2px solid var(--border-light);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      background-color: var(--bg-tertiary);
      transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    .bank-card h3 {
      color: var(--text-primary);
      margin-bottom: 15px;
      font-size: 1.2em;
      font-weight: 600;
    }

    .bank-logo {
      width: 40px;
      height: 40px;
      object-fit: contain;
      border-radius: 6px;
      border: 1px solid var(--border-color);
      padding: 4px;
      background-color: var(--bg-primary);
    }

    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 15px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      font-weight: 500;
      margin-bottom: 5px;
      color: var(--text-primary);
    }

    .form-group input {
      padding: 10px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 14px;
      background-color: var(--input-bg);
      color: var(--text-primary);
      transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    .form-group input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.25);
    }

    /* Unified Button Styling */
    .submit-btn, .filter-btn, .btn-secondary, .header-btn {
      display: inline-block;
      padding: 12px 24px;
      background-color: var(--bg-secondary);
      color: var(--text-primary);
      text-decoration: none;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px var(--shadow);
    }

    .submit-btn:hover, .filter-btn:hover, .btn-secondary:hover, .header-btn:hover {
      background-color: var(--bg-primary);
      border-color: var(--text-primary);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px var(--shadow);
    }

    /* Primary button variant */
    .submit-btn {
      background-color: var(--bg-tertiary);
      border-color: var(--text-primary);
      font-weight: 600;
    }

    .submit-btn:hover {
      background-color: var(--text-primary);
      color: var(--bg-primary);
    }

    .summary-section {
      background-color: #f8f9fa;
      padding: 20px;
      border-top: 1px solid #dee2e6;
    }

    .date-filter-section {
      background-color: var(--bg-secondary);
      padding: 20px;
      border-bottom: 1px solid var(--border-table);
      display: flex;
      align-items: center;
      gap: 15px;
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .filter-group label {
      font-weight: 500;
      color: var(--text-primary);
    }

    .filter-group select,
    .filter-group input {
      padding: 8px 12px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 14px;
      background-color: var(--input-bg);
      color: var(--text-primary);
    }

    .summary-card {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }

    .transactions-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: var(--bg-secondary);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px var(--shadow);
      font-size: 14px;
    }

    .transactions-table th,
    .transactions-table td {
      padding: 8px 6px;
      text-align: center;
      border: 1px solid var(--border-table);
      white-space: nowrap;
      color: var(--text-primary);
    }

    .transactions-table th {
      background-color: var(--bg-tertiary);
      font-weight: 600;
      color: var(--text-primary);
      font-size: 12px;
    }

    .transactions-table th:first-child,
    .transactions-table td:first-child {
      text-align: left;
      font-weight: bold;
      background-color: var(--bg-tertiary);
      position: sticky;
      left: 0;
      z-index: 1;
    }

    .transactions-table tr:hover {
      background-color: var(--table-hover);
    }

    .transactions-table tfoot tr {
      background-color: #667eea !important;
      color: white;
    }

    .transactions-table tfoot td {
      font-weight: bold;
      border-color: #495057;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: var(--text-secondary);
      font-style: italic;
    }

    .summary-item {
      background: var(--bg-secondary);
      padding: 15px;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 2px 4px var(--shadow);
      border: 1px solid var(--border-light);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .summary-item h4 {
      margin: 0 0 10px 0;
      color: var(--text-primary);
      font-size: 0.9em;
    }

    .summary-item .value {
      font-size: 1.2em;
      font-weight: 600;
      color: #667eea;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }

    .alert-success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }

    .alert-error {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }

    .dark-mode .alert-success,
    .dark .alert-success {
      background-color: #1e4d2b;
      border: 1px solid #2d5a37;
      color: #4ade80;
    }

    .dark-mode .alert-error,
    .dark .alert-error {
      background-color: #4d1e1e;
      border: 1px solid #5a2d2d;
      color: #f87171;
    }

    .summary-section {
      background-color: var(--bg-tertiary);
      padding: 20px;
      border-top: 1px solid var(--border-table);
    }

    .total-cell {
      text-align: right;
      font-weight: bold;
      background-color: var(--bg-tertiary) !important;
    }

    .time-cell {
      text-align: left;
      font-weight: bold;
      background-color: var(--bg-tertiary) !important;
    }

    .bank-header {
      text-align: center;
      background-color: #e9ecef;
    }

    .dark-mode .bank-header,
    .dark .bank-header {
      background-color: #667eea !important;
      color: white !important;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daily Transaction Entry</h1>
            <p>Record daily transactions across all financial institutions</p>
            <div style="margin-top: 20px;">
                <a href="bank_management.php" class="header-btn">
                    🏦 Manage Banks
                </a>
            </div>
        </div>

        <div class="form-section">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php foreach ($banks as $bank): ?>
                    <div class="bank-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                            <?php if (!empty($bank['logo_filename'])): ?>
                                <img src="bank_logo/<?php echo htmlspecialchars($bank['logo_filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($bank['bank_name']); ?> Logo" 
                                     class="bank-logo">
                            <?php else: ?>
                                <div class="bank-logo" style="background-color: var(--bg-secondary); border: 2px dashed var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-secondary);">
                                    <?php echo strtoupper(substr($bank['bank_code'], 0, 3)); ?>
                                </div>
                            <?php endif; ?>
                            <h3 style="margin: 0;"><?php echo htmlspecialchars($bank['bank_name']); ?> (<?php echo htmlspecialchars($bank['bank_code']); ?>)</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bank_<?php echo $bank['id_bank']; ?>_sold">Sold (دج)</label>
                                <input type="number" 
                                       step="0.01" 
                                       name="bank_<?php echo $bank['id_bank']; ?>_sold" 
                                       id="bank_<?php echo $bank['id_bank']; ?>_sold"
                                       value="<?php echo htmlspecialchars($default_values[$bank['id_bank']]['sold']); ?>"
                                       onchange="calculateTotals()">
                            </div>
                            <div class="form-group">
                                <label for="bank_<?php echo $bank['id_bank']; ?>_remise">Remise (دج)</label>
                                <input type="number" 
                                       step="0.01" 
                                       name="bank_<?php echo $bank['id_bank']; ?>_remise" 
                                       id="bank_<?php echo $bank['id_bank']; ?>_remise"
                                       value="<?php echo htmlspecialchars($default_values[$bank['id_bank']]['remise']); ?>"
                                       onchange="calculateTotals()">
                            </div>
                            <div class="form-group">
                                <label for="bank_<?php echo $bank['id_bank']; ?>_check">Check (دج)</label>
                                <input type="number" 
                                       step="0.01" 
                                       name="bank_<?php echo $bank['id_bank']; ?>_check" 
                                       id="bank_<?php echo $bank['id_bank']; ?>_check"
                                       value="<?php echo htmlspecialchars($default_values[$bank['id_bank']]['check_amount']); ?>"
                                       onchange="calculateTotals()">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit"  class="submit-btn">Save Bank Data</button>
                    <a href="bank_management.php" class="btn-secondary" style="margin-left: 15px;">
                        🏦 Manage Institutions
                    </a>
                </div>
            </form>
        </div>

        <!-- Date Filter Section -->
        <div class="date-filter-section">
            <div class="filter-group">
                <label for="filter_date">Select Date:</label>
                <select id="filter_date" name="filter_date" onchange="filterByDate()">
                    <option value="<?php echo date('Y-m-d'); ?>" <?php echo $filter_date == date('Y-m-d') ? 'selected' : ''; ?>>Today</option>
                    <?php foreach ($availableDates as $date): ?>
                        <option value="<?php echo $date; ?>" <?php echo $filter_date == $date ? 'selected' : ''; ?>>
                            <?php echo date('Y-m-d (D)', strtotime($date)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="custom_date">Or Custom Date:</label>
                <input type="date" id="custom_date" value="<?php echo $filter_date; ?>" onchange="filterByCustomDate()">
            </div>
            <div class="filter-group">
                <label for="view_mode">Summary View:</label>
                <select id="view_mode" name="view_mode" onchange="handleViewModeChange()">
                    <option value="last" <?php echo $view_mode == 'last' ? 'selected' : ''; ?>>Last Record Only</option>
                    <option value="all" <?php echo $view_mode == 'all' ? 'selected' : ''; ?>>All Records</option>
                </select>
                <?php if ($view_mode === 'all'): ?>
                    <small style="display: block; color: #6c757d; margin-top: 5px;">
                        <em>Date filters are ignored when "All Records" is selected</em>
                    </small>
                <?php endif; ?>
            </div>
            <button type="button" class="filter-btn" onclick="filterByDate('<?php echo date('Y-m-d'); ?>')">Reset to Today</button>
        </div>

                <!-- Summary Section -->
        <?php if ($summaryData && $summaryData['bank_records'] > 0): ?>
        <div class="summary-section">
            <h3>Summary for <?php echo date('F j, Y (D)', strtotime($filter_date)); ?></h3>
            <?php if ($summaryData['view_type'] === 'last'): ?>
                <p style="color: #6c757d; margin-bottom: 20px;">
                    <strong>Last transaction:</strong> <?php echo date('H:i:s', strtotime($summaryData['creation_time'])); ?>
                    (<?php echo $summaryData['bank_records']; ?> bank record(s))
                </p>
            <?php else: ?>
                <p style="color: #6c757d; margin-bottom: 20px;">
                    <strong>All transactions:</strong> <?php echo $summaryData['transaction_sessions']; ?> session(s) 
                    with <?php echo $summaryData['bank_records']; ?> bank record(s)
                </p>
            <?php endif; ?>

            <!-- Detailed Transactions Table -->
            <?php if (!empty($transactionDetails)): ?>
            <?php if ($view_mode === 'all'): ?>
                <h4 style="margin-top: 30px; margin-bottom: 15px;">All Detailed Transactions</h4>
            <?php else: ?>
                <h4 style="margin-top: 30px; margin-bottom: 15px;">Detailed Transactions</h4>
            <?php endif; ?>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <?php foreach ($availableBanks as $bankCode): ?>
                            <th colspan="3" class="bank-header"><?php echo $bankCode; ?></th>
                        <?php endforeach; ?>
                        <th colspan="2" style="text-align: center; background-color: #667eea; color: white;">Totals</th>
                    </tr>
                    <tr>
                        <th style="border-top: none;"></th>
                        <?php foreach ($availableBanks as $bankCode): ?>
                            <th style="font-size: 12px;">Remise</th>
                            <th style="font-size: 12px;">Sold</th>
                            <th style="font-size: 12px;">Check</th>
                        <?php endforeach; ?>
                        <th style="font-size: 12px; background-color: #667eea; color: white;">Total Bank</th>
                        <th style="font-size: 12px; background-color: #667eea; color: white;">Total Check</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotalRemise = 0;
                    $grandTotalSold = 0;
                    $grandTotalCheck = 0;
                    
                    foreach ($transactionDetails as $time => $banks): 
                        $rowTotalRemise = 0;
                        $rowTotalSold = 0;
                        $rowTotalCheck = 0;
                    ?>
                    <tr>
                        <td class="time-cell"><?php echo date('H:i:s', strtotime($time)); ?></td>
                        <?php foreach ($availableBanks as $bankCode): ?>
                            <?php 
                            $remise = isset($banks[$bankCode]) ? $banks[$bankCode]['remise'] : 0;
                            $sold = isset($banks[$bankCode]) ? $banks[$bankCode]['sold'] : 0;
                            $check = isset($banks[$bankCode]) ? $banks[$bankCode]['check_amount'] : 0;
                            
                            $rowTotalRemise += $remise;
                            $rowTotalSold += $sold;
                            $rowTotalCheck += $check;
                            ?>
                            <td style="text-align: right;"><?php echo $remise > 0 ? number_format($remise, 2) : '0.00'; ?></td>
                            <td style="text-align: right;"><?php echo $sold > 0 ? number_format($sold, 2) : '0.00'; ?></td>
                            <td style="text-align: right;"><?php echo $check > 0 ? number_format($check, 2) : '0.00'; ?></td>
                        <?php endforeach; ?>
                        <td class="total-cell">
                            <?php echo number_format($rowTotalRemise + $rowTotalSold, 2); ?>
                        </td>
                        <td class="total-cell">
                            <?php echo number_format($rowTotalCheck, 2); ?>
                        </td>
                    </tr>
                    <?php 
                        $grandTotalRemise += $rowTotalRemise;
                        $grandTotalSold += $rowTotalSold;
                        $grandTotalCheck += $rowTotalCheck;
                    endforeach; 
                    ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #667eea; color: white; font-weight: 600;">
                        <td>TOTAL</td>
                        <?php foreach ($availableBanks as $bankCode): ?>
                            <?php 
                            $bankTotalRemise = 0;
                            $bankTotalSold = 0;
                            $bankTotalCheck = 0;
                            
                            foreach ($transactionDetails as $banks) {
                                if (isset($banks[$bankCode])) {
                                    $bankTotalRemise += $banks[$bankCode]['remise'];
                                    $bankTotalSold += $banks[$bankCode]['sold'];
                                    $bankTotalCheck += $banks[$bankCode]['check_amount'];
                                }
                            }
                            ?>
                            <td style="text-align: right;"><?php echo number_format($bankTotalRemise, 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($bankTotalSold, 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($bankTotalCheck, 2); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($grandTotalRemise + $grandTotalSold, 2); ?>
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($grandTotalCheck, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <div class="no-data">
                <p>No transactions found for <?php echo date('F j, Y', strtotime($filter_date)); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="summary-section">
            <div class="no-data">
                <p>No data available for <?php echo date('F j, Y', strtotime($filter_date)); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // Theme initialization - detects system preference and applies theme
    function initializeTheme() {
        // Check if user has a saved preference
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme) {
            // Use saved preference
            document.documentElement.setAttribute('data-theme', savedTheme);
        } else {
            // Use system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = prefersDark ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }
    }

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        // Only update if user hasn't manually set a preference
        if (!localStorage.getItem('theme-manual')) {
            const theme = e.matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }
    });

    // Initialize theme immediately
    initializeTheme();

    function calculateTotals() {
        // This can be enhanced to show real-time calculations
        // For now, it's just a placeholder for future enhancements
    }

    function filterByDate(date = null) {
        const selectedDate = date || document.getElementById('filter_date').value;
        const viewMode = document.getElementById('view_mode').value;
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('filter_date', selectedDate);
        currentUrl.searchParams.set('view_mode', viewMode);
        window.location.href = currentUrl.toString();
    }

    function handleViewModeChange(applyFilter = true) {
        const viewMode = document.getElementById('view_mode').value;
        const filterDate = document.getElementById('filter_date');
        const customDate = document.getElementById('custom_date');
        
        if (viewMode === 'all') {
            // When "All Records" is selected, disable date filters
            filterDate.disabled = true;
            customDate.disabled = true;
            filterDate.style.opacity = '0.5';
            customDate.style.opacity = '0.5';
        } else {
            // When "Last Record Only" is selected, enable date filters
            filterDate.disabled = false;
            customDate.disabled = false;
            filterDate.style.opacity = '1';
            customDate.style.opacity = '1';
        }
        
        // Apply the filter immediately only if requested
        if (applyFilter) {
            filterByDate();
        }
    }

    function filterByCustomDate() {
        const customDate = document.getElementById('custom_date').value;
        const viewMode = document.getElementById('view_mode').value;
        if (customDate) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('filter_date', customDate);
            currentUrl.searchParams.set('view_mode', viewMode);
            window.location.href = currentUrl.toString();
        }
    }

    // Auto-sync custom date with dropdown selection
    document.getElementById('filter_date').addEventListener('change', function() {
        document.getElementById('custom_date').value = this.value;
    });

    // Initialize the view mode state on page load
    document.addEventListener('DOMContentLoaded', function() {
        handleViewModeChange(false); // Don't apply filter on page load, just set UI state
    });
    </script>
</body>
</html>
