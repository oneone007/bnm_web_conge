<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Recipients Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="theme.js"></script>
        <script src="api_config_inv.js"></script>

    <style>
        /* Light mode styles */
        body {
            background-color: #f8f9fa;
            padding: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
            background-color: #ffffff;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .route-section {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 20px;
            background: white;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .recipient-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background: #f8f9fa;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .recipient-email {
            flex: 1;
            margin-right: 10px;
        }
        
        .route-title {
            color: #495057;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #111827;
            color: #e0e0e0;
        }

        body.dark-mode .card {
            background-color: #1f2937;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            border: 1px solid #404040;
        }

        body.dark-mode .card-header {
            background-color: #374151;
            border-bottom: 1px solid #4b5563;
            color: #e0e0e0;
        }

        body.dark-mode .route-section {
            background-color: #1f2937;
            border-color: #374151;
        }

        body.dark-mode .recipient-item {
            background-color: #374151;
            border-color: #4b5563;
        }

        body.dark-mode .route-title {
            color: #e0e0e0;
            border-bottom-color: #6bb6ff;
        }

        body.dark-mode .btn-outline-primary {
            color: #6bb6ff;
            border-color: #6bb6ff;
        }

        body.dark-mode .btn-outline-primary:hover {
            background-color: #6bb6ff;
            color: #1a1a1a;
        }

        body.dark-mode .btn-outline-success {
            color: #5dc460;
            border-color: #5dc460;
        }

        body.dark-mode .btn-outline-success:hover {
            background-color: #5dc460;
            color: #1a1a1a;
        }

        body.dark-mode .btn-outline-danger {
            color: #e74c3c;
            border-color: #e74c3c;
        }

        body.dark-mode .btn-outline-danger:hover {
            background-color: #e74c3c;
            color: #1a1a1a;
        }

        body.dark-mode .btn-outline-secondary {
            color: #60a5fa;
            border-color: #60a5fa;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #60a5fa;
            color: #111827;
        }

        body.dark-mode .form-control {
            background-color: #374151;
            border-color: #4b5563;
            color: #e0e0e0;
        }

        body.dark-mode .form-control:focus {
            background-color: #374151;
            border-color: #6bb6ff;
            color: #e0e0e0;
            box-shadow: 0 0 0 0.2rem rgba(107, 182, 255, 0.25);
        }

        body.dark-mode .form-select {
            background-color: #374151;
            border-color: #4b5563;
            color: #e0e0e0;
        }

        body.dark-mode .text-muted {
            color: #a0a0a0 !important;
        }
        
        
        .add-recipient-form {
            border: 2px dashed #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="mail_settings">Mail Settings</a></li>
                        <li class="breadcrumb-item active">Recipients Management</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1><i class="fas fa-users text-primary"></i> Mail Recipients Management</h1>
                        <p class="text-muted">Manage email recipients for each mail route. Changes are saved immediately to JSON file.</p>
                    </div>
                    <div>
 
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Saisie Mail Recipients -->
        <div class="route-section">
            <h3 class="route-title"><i class="fas fa-envelope text-info"></i> Send Saisie Mail Recipients</h3>
            <p class="text-muted">Recipients for inventory operations notifications (send_saisie_mail route)</p>
            <div id="saisie-recipients">
                <!-- Recipients will be loaded here -->
            </div>
            <div class="add-recipient-form">
                <div class="input-group">
                    <input type="email" class="form-control" id="saisie-new-email" placeholder="Enter email address">
                    <button class="btn btn-primary" onclick="addRecipient('send_saisie_mail', 'saisie')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
        </div>

        <!-- Send Info Mail Recipients -->
        <div class="route-section">
            <h3 class="route-title"><i class="fas fa-envelope text-success"></i> Send Info Mail Recipients</h3>
            <p class="text-muted">Recipients for information notifications (send_info_mail route)</p>
            <div id="info-recipients">
                <!-- Recipients will be loaded here -->
            </div>
            <div class="add-recipient-form">
                <div class="input-group">
                    <input type="email" class="form-control" id="info-new-email" placeholder="Enter email address">
                    <button class="btn btn-success" onclick="addRecipient('send_info_mail', 'info')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
        </div>

        <!-- Inventory Save Recipients -->
        <div class="route-section">
            <h3 class="route-title"><i class="fas fa-envelope text-warning"></i> Inventory Save Recipients</h3>
            <p class="text-muted">Recipients for inventory creation notifications (inventory/save route)</p>
            <div id="inventory-recipients">
                <!-- Recipients will be loaded here -->
            </div>
            <div class="add-recipient-form">
                <div class="input-group">
                    <input type="email" class="form-control" id="inventory-new-email" placeholder="Enter email address">
                    <button class="btn btn-warning" onclick="addRecipient('inventory_save', 'inventory')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary me-2" onclick="loadAllRecipients()">
                            <i class="fas fa-refresh"></i> Reload Recipients
                        </button>
                        <button class="btn btn-danger me-2" onclick="resetToDefaults()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                        <button class="btn btn-info" onclick="exportConfiguration()">
                            <i class="fas fa-download"></i> Export Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = window.API_CONFIGinv.getApiUrl();
        let recipientsData = {};

        // Load all recipients on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllRecipients();
        });

        async function loadAllRecipients() {
            try {
                const response = await fetch(`${API_BASE}/mail/recipients`);
                const data = await response.json();
                
                if (data.success) {
                    recipientsData = data.recipients;
                    displayRecipients('send_saisie_mail', 'saisie-recipients');
                    displayRecipients('send_info_mail', 'info-recipients');
                    displayRecipients('inventory_save', 'inventory-recipients');
                } else {
                    alert('Error loading recipients: ' + data.error);
                }
            } catch (error) {
                alert('Error loading recipients: ' + error.message);
            }
        }

        function displayRecipients(routeName, containerId) {
            const container = document.getElementById(containerId);
            const recipients = recipientsData[routeName] || [];
            
            container.innerHTML = '';
            
            if (recipients.length === 0) {
                container.innerHTML = '<p class="text-muted">No recipients configured</p>';
                return;
            }
            
            recipients.forEach((email, index) => {
                const recipientDiv = document.createElement('div');
                recipientDiv.className = 'recipient-item';
                recipientDiv.innerHTML = `
                    <div class="recipient-email">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <strong>${email}</strong>
                    </div>
                    <button class="btn btn-sm btn-danger" onclick="removeRecipient('${routeName}', ${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(recipientDiv);
            });
        }

        async function addRecipient(routeName, prefix) {
            const emailInput = document.getElementById(prefix + '-new-email');
            const email = emailInput.value.trim();
            
            if (!email) {
                alert('Please enter an email address');
                return;
            }
            
            if (!email.includes('@')) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Add to local data
            if (!recipientsData[routeName]) {
                recipientsData[routeName] = [];
            }
            
            if (recipientsData[routeName].includes(email)) {
                alert('This email is already in the list');
                return;
            }
            
            recipientsData[routeName].push(email);
            
            // Save to server
            try {
                const response = await fetch(`${API_BASE}/mail/recipients/${routeName}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        recipients: recipientsData[routeName]
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    emailInput.value = '';
                    displayRecipients(routeName, prefix + '-recipients');
                } else {
                    alert('Error saving recipient: ' + data.error);
                    // Remove from local data if save failed
                    recipientsData[routeName].pop();
                }
            } catch (error) {
                alert('Error saving recipient: ' + error.message);
                // Remove from local data if save failed
                recipientsData[routeName].pop();
            }
        }

        async function removeRecipient(routeName, index) {
            if (!confirm('Are you sure you want to remove this recipient?')) {
                return;
            }
            
            // Remove from local data
            recipientsData[routeName].splice(index, 1);
            
            // Save to server
            try {
                const response = await fetch(`${API_BASE}/mail/recipients/${routeName}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        recipients: recipientsData[routeName]
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Refresh display
                    const containerId = routeName === 'send_saisie_mail' ? 'saisie-recipients' :
                                     routeName === 'send_info_mail' ? 'info-recipients' : 'inventory-recipients';
                    displayRecipients(routeName, containerId);
                } else {
                    alert('Error removing recipient: ' + data.error);
                    // Restore the removed item
                    recipientsData[routeName].splice(index, 0, '');
                }
            } catch (error) {
                alert('Error removing recipient: ' + error.message);
                // Restore the removed item
                recipientsData[routeName].splice(index, 0, '');
            }
        }

        async function resetToDefaults() {
            if (!confirm('Are you sure you want to reset all recipients to default values? This cannot be undone.')) {
                return;
            }
            
            const defaultRecipients = {
                "send_saisie_mail": [
                    "guend.hamza@bnmparapharm.com",
                    "seifeddine.nemdili@bnmparapharm.com",
                    "belhanachi.abdenour@bnmparapharm.com"
                ],
                "send_info_mail": [
                    "maamri.yasser@bnmparapharm.com",
                    "mahroug.nazim@bnmparapharm.com",
                    "benmalek.abderrahmane@bnmparapharm.com"
                ],
                "inventory_save": [
                    "benmalek.abderrahmane@bnmparapharm.com",
                    "mahroug.nazim@bnmparapharm.com"
                ]
            };
            
            try {
                const response = await fetch(`${API_BASE}/mail/recipients/all`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(defaultRecipients)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Recipients reset to defaults successfully!');
                    loadAllRecipients();
                } else {
                    alert('Error resetting recipients: ' + data.error);
                }
            } catch (error) {
                alert('Error resetting recipients: ' + error.message);
            }
        }

        function exportConfiguration() {
            const dataStr = JSON.stringify(recipientsData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'mail_recipients_backup.json';
            link.click();
            URL.revokeObjectURL(url);
        }

        // Theme toggle functionality
  

        // Initialize everything on page load
        window.addEventListener('load', function() {
            loadAllRecipients();
            initThemeToggle();
        });
    </script>
</body>
</html>
