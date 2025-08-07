<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}


// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
//     header("Location: Acess_Denied");    exit();
// }
$page_identifier = 'bank';
 
require_once 'check_permission.php';


// Include database connection
require_once 'db/db_connect.php';

// Initialize default form values
$default_values = [
    'bna_sold' => '',
    'bna_remise' => '',
    'bna_check' => '',
    'baraka_sold' => '',
    'baraka_remise' => '',
    'baraka_check' => ''
];

// Try to get last submitted values from database
$sql = "SELECT * FROM bank ORDER BY creation_time DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $last_entry = $result->fetch_assoc();
    
    // Update default values with last entry values
    $default_values = [
        'bna_sold' => $last_entry['bna_sold'] ?? '',
        'bna_remise' => $last_entry['bna_remise'] ?? '',
        'bna_check' => $last_entry['bna_check'] ?? '',
        'baraka_sold' => $last_entry['baraka_sold'] ?? '',
        'baraka_remise' => $last_entry['baraka_remise'] ?? '',
        'baraka_check' => $last_entry['baraka_check'] ?? ''
    ];
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $bna_sold = filter_input(INPUT_POST, 'bna_sold', FILTER_VALIDATE_FLOAT);
    $bna_remise = filter_input(INPUT_POST, 'bna_remise', FILTER_VALIDATE_FLOAT);
    $bna_check = filter_input(INPUT_POST, 'bna_check', FILTER_VALIDATE_FLOAT);
    $baraka_sold = filter_input(INPUT_POST, 'baraka_sold', FILTER_VALIDATE_FLOAT);
    $baraka_remise = filter_input(INPUT_POST, 'baraka_remise', FILTER_VALIDATE_FLOAT);
    $baraka_check = filter_input(INPUT_POST, 'baraka_check', FILTER_VALIDATE_FLOAT);
    
    // Check if all inputs are valid
    if ($bna_sold === false || $bna_remise === false || 
        $bna_check === false || $baraka_sold === false || 
        $baraka_remise === false || $baraka_check === false) {
        $error_message = "Please enter valid numbers for all fields.";
    } else {
                // Begin transaction
        $conn->begin_transaction();
        try {
            // Calculate totals
            $total_bank = $bna_sold + $bna_remise + $baraka_sold + $baraka_remise;
            $total_checks = $bna_check + $baraka_check;
            
            // Get current datetime
            $creation_time = date('Y-m-d H:i:s');
            
            // Prepare SQL statement for bank table
            $sql = "INSERT INTO bank (creation_time, bna_sold, bna_remise, bna_check, 
                                    baraka_sold, baraka_remise, baraka_check, total_bank, total_checks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Error preparing bank statement: " . $conn->error);
            }
            if (!$stmt->bind_param("sdddddddd",
                $creation_time,
                $bna_sold,
                $bna_remise,
                $bna_check,
                $baraka_sold,
                $baraka_remise,
                $baraka_check,
                $total_bank,
                $total_checks
            )) {
                throw new Exception("Error binding bank parameters: " . $stmt->error);
            }

            // Execute the statement
            if ($stmt->execute()) {
                $conn->commit();
                $success_message = "Data saved successfully!";
                
                // Update default values with newly submitted values
                $default_values = [
                    'bna_sold' => $bna_sold,
                    'bna_remise' => $bna_remise,
                    'bna_check' => $bna_check,
                    'baraka_sold' => $baraka_sold,
                    'baraka_remise' => $baraka_remise,
                    'baraka_check' => $baraka_check
                ];
            } else {
                $conn->rollback();
                $error_message = "Error saving data: " . $stmt->error;
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        } finally {
            if (isset($stmt) && $stmt !== false) $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Bank</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="theme.js"></script>

    <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 20px;
    }

    .container {
  width: 100%;
  max-width: 500px;
  padding: 0 15px;
  box-sizing: border-box;
}



    .bank-card {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      padding: 20px 30px;
      margin-bottom: 30px;
    }

    .bank-title {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #333;
      border-bottom: 2px solid #eee;
      padding-bottom: 10px;
    }

    .input-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 15px;
    }

    .input-group label {
      font-weight: 500;
      margin-bottom: 5px;
      color: #555;
    }

    .input-group input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      transition: border-color 0.3s ease;
    }

    .input-group input:focus {
      border-color: #007bff;
      outline: none;
    }

    .sidebar {
    min-width: 200px;
    max-width: 250px;
    background-color: #f9fafb;
    border-right: 1px solid #e5e7eb;
    transition: transform 0.3s ease-in-out;
    position: fixed;
    height: 100vh;
    z-index: 40;
}

.sidebar-hidden {
    transform: translateX(-100%);
}

.content {
  display: flex;
  justify-content: center; /* horizontal center */
  align-items: center;     /* vertical center */
  height: 100vh;           /* full viewport height */
  padding: 20px;
  margin: 0;               /* remove left margin */
  width: 100%;
  box-sizing: border-box;
}


@media (max-width: 768px) {
  .content {
    margin-left: 0;
    width: 100%;
    padding: 15px;
  }
}


.content-full {
    margin-left: 0;
    width: 100%;
}

.table-header {
    background-color: #f3f4f6;
    text-align: left;
    color: #000;
    /* Default text color */
    position: sticky;
    top: 0;
}

.table-row {
    color: #000;
    /* Default black text */
}

.table-row:nth-child(odd) {
    background-color: #f9fafb;
}

/* Dark mode styles */
body.dark-mode {
        background-color: #1f2937;
        color: #f3f4f6;
    }

    .dark-mode .bank-card {
        background-color: #374151;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .bank-title {
        color: #f3f4f6;
        border-bottom-color: #4b5563;
    }

    .dark-mode .input-group label {
        color: #9ca3af;
    }

    .dark-mode .input-group input {
        background-color: #1f2937;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    .dark-mode .input-group input:focus {
        border-color: #3b82f6;
    }

    .dark-mode .submit-btn {
        background-color: #3b82f6;
    }

    .dark-mode .submit-btn:hover {
        background-color: #2563eb;
    }

    .dark-mode .history-link {
        color: #60a5fa;
    }

    .dark-mode .success-message {
        background-color: #065f46;
        color: #d1fae5;
        border-color: #047857;
    }

    .dark-mode .error-message {
        background-color: #991b1b;
        color: #fee2e2;
        border-color: #dc2626;
    }

/* Dark Mode */
html.dark .sidebar {
    background-color: #1f2937;
    border-right-color: #374151;
}

html.dark body {
    background-color: #111827;
    color: white;
}

/* Dark Mode Toggle - Styled Checkbox */
/* Hide Default Checkbox */
/* Hide Default Checkbox */


/* Sidebar Hidden by Default */
.sidebar-hidden {
    transform: translateX(-100%);
}

/* Sidebar Appears Smoothly */
.sidebar {
    transition: transform 0.3s ease-in-out;
}

/* Sidebar Stays Open Until Mouse Leaves */
.sidebar:hover {
    transform: translateX(0);
}



    .submit-btn {
      display: block;
      width: 100%;
      padding: 12px;
      font-size: 16px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 20px;
      transition: background-color 0.3s ease;
    }

    .submit-btn:hover {
      background-color: #0056b3;
    }

    .success-message {
      margin-top: 20px;
      padding: 10px;
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      border-radius: 4px;
      text-align: center;
    }

    .error-message {
      margin-top: 20px;
      padding: 10px;
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      border-radius: 4px;
      text-align: center;
    }
    
    .history-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: #007bff;
      text-decoration: none;
    }
    
    .history-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>


<div class="content">
  <div class="container">
    <form method="POST">
      <!-- BNA -->
      <div class="bank-card">
        <div class="bank-title">BNA - Banque Nationale d'Algérie</div>
        <div class="input-group">
          <label for="bna_sold">SOLD BNA</label>
          <input type="number" step="0.01" name="bna_sold" id="bna_sold" 
                 value="<?php echo htmlspecialchars($default_values['bna_sold']); ?>" required>
        </div>
        <div class="input-group">
          <label for="bna_remise">REMISE NON ENCAISSÉE (BNA)</label>
          <input type="number" step="0.01" name="bna_remise" id="bna_remise" 
                 value="<?php echo htmlspecialchars($default_values['bna_remise']); ?>" required>
        </div>
        <div class="input-group">
          <label for="bna_check">CHÈQUE (BNA)</label>
          <input type="number" step="0.01" name="bna_check" id="bna_check"
                 value="<?php echo htmlspecialchars($default_values['bna_check']); ?>">
        </div>
      </div>

      <!-- EL BARAKA -->
      <div class="bank-card">
        <div class="bank-title">EL BARAKA BANK</div>
        <div class="input-group">
          <label for="baraka_sold">SOLD EL BARAKA</label>
          <input type="number" step="0.01" name="baraka_sold" id="baraka_sold" 
                 value="<?php echo htmlspecialchars($default_values['baraka_sold']); ?>" required>
        </div>
        <div class="input-group">
          <label for="baraka_remise">REMISE NON ENCAISSÉE (EL BARAKA)</label>
          <input type="number" step="0.01" name="baraka_remise" id="baraka_remise" 
                 value="<?php echo htmlspecialchars($default_values['baraka_remise']); ?>" required>
        </div>
        <div class="input-group">
          <label for="baraka_check">CHÈQUE (EL BARAKA)</label>
          <input type="number" step="0.01" name="baraka_check" id="baraka_check"
                 value="<?php echo htmlspecialchars($default_values['baraka_check']); ?>">
        </div>
      </div>

      <button type="submit" class="submit-btn">Envoyer</button>
    </form>

    <!-- Success/Error Message -->
    <?php if (isset($success_message)): ?>
      <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
    <?php elseif (isset($error_message)): ?>
      <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <a href="view_data" class="history-link">View Historical Data</a>
  </div> 
</div>
</body>
</html>