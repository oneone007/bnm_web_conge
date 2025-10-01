<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM");
    exit();
}

// Include database connection
require_once 'db/db_connect.php';

// Function to get logo path for a bank
function getBankLogo($bank_code, $logo_filename = null) {
    if ($logo_filename && file_exists("bank_logo/{$logo_filename}")) {
        return "bank_logo/{$logo_filename}";
    }
    
    // Fallback to bank code based naming
    $extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
    $bank_code_lower = strtolower($bank_code);
    
    foreach ($extensions as $ext) {
        $logo_path = "bank_logo/{$bank_code_lower}.{$ext}";
        if (file_exists($logo_path)) {
            return $logo_path;
        }
    }
    
    return null; // No logo found
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_bank':
                $bank_name = trim($_POST['bank_name']);
                $bank_code = trim($_POST['bank_code']);
                $logo_filename = null;
                
                // Handle logo upload
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                    $file_type = $_FILES['logo']['type'];
                    
                    if (in_array($file_type, $allowed_types)) {
                        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                        $logo_filename = strtolower($bank_code) . '.' . $file_extension;
                        $upload_path = "bank_logo/" . $logo_filename;
                        
                        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                            $error_message = "Failed to upload logo file.";
                            break;
                        }
                    } else {
                        $error_message = "Please upload a valid image file (JPEG, PNG, GIF, SVG).";
                        break;
                    }
                }
                
                if (!empty($bank_name) && !empty($bank_code)) {
                    $sql = "INSERT INTO banks (bank_name, bank_code, logo_filename, is_active) VALUES (?, ?, ?, TRUE)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $bank_name, $bank_code, $logo_filename);
                    
                    if ($stmt->execute()) {
                        $success_message = "Bank '{$bank_name}' added successfully!";
                    } else {
                        $error_message = "Error adding bank: " . $conn->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Please fill in all fields.";
                }
                break;
                
            case 'toggle_bank':
                $bank_id = (int)$_POST['bank_id'];
                $is_active = $_POST['is_active'] == '1' ? 0 : 1;
                
                $sql = "UPDATE banks SET is_active = ? WHERE id_bank = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $is_active, $bank_id);
                
                if ($stmt->execute()) {
                    $action_text = $is_active ? 'activated' : 'deactivated';
                    $success_message = "Bank {$action_text} successfully!";
                } else {
                    $error_message = "Error updating bank status: " . $conn->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Get all banks
$sql = "SELECT * FROM banks ORDER BY bank_name";
$result = $conn->query($sql);
$banks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Institutions Management - BNM Web</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
      max-width: 1000px;
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
      margin: 0;
      color: var(--text-secondary);
      font-size: 1.1em;
    }

    .content {
      padding: 30px;
      background-color: var(--bg-secondary);
    }

    .form-section {
      background-color: var(--bg-tertiary);
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 30px;
      border: 1px solid var(--border-light);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr auto;
      gap: 15px;
      align-items: end;
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

    /* Unified Button Styling */
    .btn, .btn-primary, .btn-success, .btn-warning {
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

    .btn:hover, .btn-primary:hover, .btn-success:hover, .btn-warning:hover {
      background-color: var(--bg-primary);
      border-color: var(--text-primary);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px var(--shadow);
    }

    /* Primary button variant */
    .btn-primary {
      background-color: var(--bg-tertiary);
      border-color: var(--text-primary);
      font-weight: 600;
    }

    .btn-primary:hover {
      background-color: var(--text-primary);
      color: var(--bg-primary);
    }

    /* Success button variant - subtle green accent */
    .btn-success {
      border-color: var(--text-secondary);
    }

    /* Warning button variant - subtle accent */
    .btn-warning {
      border-color: var(--text-secondary);
    }

    .banks-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: var(--bg-secondary);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px var(--shadow);
    }

    .banks-table th,
    .banks-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border-table);
      color: var(--text-primary);
    }

    .banks-table th {
      background-color: var(--bg-tertiary);
      font-weight: 600;
      color: var(--text-primary);
    }

    .banks-table tr:hover {
      background-color: var(--table-hover);
    }

    .status-active {
      color: var(--text-primary);
      font-weight: 600;
    }

    .status-inactive {
      color: var(--text-secondary);
      font-weight: 600;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      border: 1px solid var(--border-light);
      background-color: var(--bg-secondary);
      color: var(--text-primary);
    }

    .alert-success {
      border-left: 4px solid var(--text-primary);
    }

    .alert-error {
      border-left: 4px solid var(--text-secondary);
    }

    .bank-logo {
      width: 40px;
      height: 40px;
      object-fit: contain;
      border-radius: 4px;
      border: 1px solid var(--border-light);
      background: var(--bg-secondary);
      padding: 2px;
    }

    .bank-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .bank-details {
      display: flex;
      flex-direction: column;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Financial Institutions Management</h1>
            <p>Configure and manage banking partners in the transaction system</p>
        </div>

        <div class="content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Add Bank Form -->
            <div class="form-section">
                <h3 style="margin-bottom: 20px;">Add New Bank</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_bank">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" required placeholder="e.g., Société Générale Algérie">
                        </div>
                        <div class="form-group">
                            <label for="bank_code">Bank Code</label>
                            <input type="text" name="bank_code" id="bank_code" required placeholder="e.g., SGA">
                        </div>
                        <div class="form-group">
                            <label for="logo">Bank Logo (Optional)</label>
                            <input type="file" name="logo" id="logo" accept="image/*" placeholder="Upload bank logo">
                            <small style="color: var(--text-secondary); font-size: 12px;">Supported: JPG, PNG, GIF, SVG</small>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Add Bank</button>
                        </div>
                    </div>
                </form>
            </div>            <!-- Banks List -->
            <div>
                <h3>Existing Banks</h3>
                <table class="banks-table">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Bank Name</th>
                            <th>Bank Code</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banks as $bank): ?>
                        <tr>
                            <td>
                                <?php if (!empty($bank['logo_filename'])): ?>
                                    <img src="bank_logo/<?php echo htmlspecialchars($bank['logo_filename']); ?>" 
                                         alt="<?php echo htmlspecialchars($bank['bank_name']); ?> Logo" 
                                         class="bank-logo">
                                <?php else: ?>
                                    <div class="bank-logo" style="background-color: var(--bg-secondary); border: 2px dashed var(--border-table); display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--text-secondary);">
                                        No Logo
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                            <td><?php echo htmlspecialchars($bank['bank_code']); ?></td>
                            <td class="<?php echo $bank['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $bank['is_active'] ? 'Active' : 'Inactive'; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($bank['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_bank">
                                    <input type="hidden" name="bank_id" value="<?php echo $bank['id_bank']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $bank['is_active']; ?>">
                                    <button type="submit" 
                                            class="btn <?php echo $bank['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                            onclick="return confirm('Are you sure you want to <?php echo $bank['is_active'] ? 'deactivate' : 'activate'; ?> this bank?')">
                                        <?php echo $bank['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="bank_improved.php" class="btn btn-primary">Go to Bank Transactions</a>
            </div>
        </div>
    </div>
</body>
</html>
