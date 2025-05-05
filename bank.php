
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

// Initialize default form values
$default_values = [
    'bna_sold' => '',
    'bna_remise' => '',
    'bna_check' => '',
    'baraka_sold' => '',
    'baraka_remise' => '',
    'baraka_check' => ''
];

// Try to get last submitted values from bank.json
$json_file = 'bank.json';
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $existing_data = json_decode($json_data, true);
    
    if (json_last_error() === JSON_ERROR_NONE && !empty($existing_data)) {
        // Get the last entry
        $last_entry = end($existing_data);
        
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
        // Create an array with the form data and timestamp
        $bank_data = array(
            "date" => date('Y-m-d H:i:s'),
            "bna_sold" => $bna_sold,
            "bna_remise" => $bna_remise,
            "bna_check" => $bna_check,
            "baraka_sold" => $baraka_sold,
            "baraka_remise" => $baraka_remise,
            "baraka_check" => $baraka_check
        );

        // Create directory if it doesn't exist
        if (!file_exists(dirname($json_file))) {
            mkdir(dirname($json_file), 0755, true);
        }

        // Check if the JSON file exists, then read and decode the current data
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $existing_data = json_decode($json_data, true);
            
            // If file is empty or invalid, initialize as empty array
            if (json_last_error() !== JSON_ERROR_NONE) {
                $existing_data = [];
            }
        } else {
            $existing_data = [];
        }

        // Add new data to the existing data
        $existing_data[] = $bank_data;

        // Encode the data into a JSON string
        $json_data = json_encode($existing_data, JSON_PRETTY_PRINT);

        // Save the data to the JSON file with error handling
        if (file_put_contents($json_file, $json_data, LOCK_EX) !== false) {
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
            $error_message = "Error saving data. Please check file permissions.";
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
.dark .sidebar {
    background-color: #1f2937;
    border-right-color: #374151;
}



.dark body {
    background-color: #111827;
    color: #010911;
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