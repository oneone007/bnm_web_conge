<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../BNM");
    exit();
}

$Role = $_SESSION['Role'] ?? 'Unknown';
$username = $_SESSION['username'] ?? 'Guest';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Settings - BNM Mail Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="theme.js"></script>
        <script src="api_config_inv.js"></script>

    <style>
        /* Light mode styles */
        body {
            background-color: #f8f9fa;
            margin-left: 280px;
            padding: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border: none;
            background-color: #ffffff;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .setting-item {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 20px;
            background: white;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .setting-item h5 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .setting-item p {
            color: #6c757d;
            margin-bottom: 15px;
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
            border-bottom: 1px solid #404040;
            color: #e0e0e0;
        }

        body.dark-mode .setting-item {
            background-color: #1f2937;
            border-color: #404040;
        }

        body.dark-mode .setting-item h5 {
            color: #e0e0e0;
        }

        body.dark-mode .setting-item p {
            color: #a0a0a0;
        }

        body.dark-mode .btn-primary {
            background-color: #6bb6ff;
            border-color: #6bb6ff;
        }

        body.dark-mode .btn-outline-secondary {
            color: #95a24b;
            border-color: #95a24b;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #95a24b;
            color: #1a1a1a;
        }

        body.dark-mode .text-primary {
            color: #6bb6ff !important;
        }

        body.dark-mode .text-secondary {
            color: #95a24b !important;
        }
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="mail_dashboard">Mail Dashboard</a></li>
                        <li class="breadcrumb-item active">Mail Settings</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-cog text-secondary"></i> Mail System Settings</h1>
        
                </div>
            </div>
        </div>

        <!-- User Access Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-check"></i> User Access & Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <h5><i class="fas fa-users text-info"></i> Recipients Management</h5>
                                    <p>Manage email recipient lists and distribution groups.</p>
                                    <button class="btn btn-info" onclick="window.location.href='mail_recipients'">
                                        <i class="fas fa-users"></i> Manage Recipients
                                    </button>
                                    <small class="text-success d-block mt-1">Available to all users</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <h5><i class="fas fa-user-shield text-secondary"></i> Current User Role</h5>
                                    <p>Your current access level and permissions.</p>
                                    <span class="badge bg-<?php echo $Role === 'Developer' ? 'success' : 'primary'; ?> fs-6">
                                        <i class="fas fa-<?php echo $Role === 'Developer' ? 'code' : 'user'; ?>"></i> <?php echo $Role; ?>
                                    </span>
                                    <small class="text-muted d-block mt-1">Role: <?php echo $username; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Actions Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> System Actions 
                            <?php if ($Role !== 'Developer'): ?>
                                <span class="badge bg-warning ms-2">Developer Only</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($Role !== 'Developer'): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Access Restricted:</strong> System configuration actions require Developer role access. 
                                Contact your administrator if you need these permissions.
                            </div>
                        <?php endif; ?>
                        <div class="row">
            <div class="col-md-6">
                <div class="setting-item">
                    <h5><i class="fas fa-database text-primary"></i> Database Setup</h5>
                    <p>Create the necessary database tables for the mail management system.</p>
                    <?php if ($Role === 'Developer'): ?>
                        <button class="btn btn-primary" onclick="setupMailTables()">
                            <i class="fas fa-database"></i> Setup Mail Tables
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary" disabled title="Developer access required">
                            <i class="fas fa-database"></i> Setup Mail Tables
                        </button>
                        <small class="text-muted d-block mt-1">Developer role required</small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="setting-item">
                    <h5><i class="fas fa-download text-info"></i> Initialize Data</h5>
                    <p>Load default email templates and contacts to get started quickly.</p>
                    <?php if ($Role === 'Developer'): ?>
                        <button class="btn btn-info" onclick="initDefaultData()">
                            <i class="fas fa-download"></i> Initialize Default Data
                        </button>
                    <?php else: ?>
                        <button class="btn btn-info" disabled title="Developer access required">
                            <i class="fas fa-download"></i> Initialize Default Data
                        </button>
                        <small class="text-muted d-block mt-1">Developer role required</small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="setting-item">
                    <h5><i class="fas fa-trash text-warning"></i> Log Cleanup</h5>
                    <p>Clean up old email logs to save database space. Only today's logs are kept.</p>
                    <?php if ($Role === 'Developer'): ?>
                        <button class="btn btn-warning" onclick="cleanupOldLogs()">
                            <i class="fas fa-trash"></i> Cleanup Old Logs
                        </button>
                    <?php else: ?>
                        <button class="btn btn-warning" disabled title="Developer access required">
                            <i class="fas fa-trash"></i> Cleanup Old Logs
                        </button>
                        <small class="text-muted d-block mt-1">Developer role required</small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="setting-item">
                    <h5><i class="fas fa-server text-success"></i> Service Status</h5>
                    <p>Check if the mail service is running and accessible.</p>
                    <?php if ($Role === 'Developer'): ?>
                        <button class="btn btn-success" onclick="checkServiceStatus()">
                            <i class="fas fa-heartbeat"></i> Check Service Status
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success" disabled title="Developer access required">
                            <i class="fas fa-heartbeat"></i> Check Service Status
                        </button>
                        <small class="text-muted d-block mt-1">Developer role required</small>
                    <?php endif; ?>
                </div>
            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mail Service API:</strong> http://192.168.1.94:5003</p>
                                <p><strong>SMTP Server:</strong> mail.bnmparapharm.com</p>
                                <p><strong>SMTP Port:</strong> 465 (SSL)</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>From Email:</strong> inventory.system.bnm@bnmparapharm.com</p>
                                <p><strong>Log Retention:</strong> Daily cleanup (current day only)</p>
                                <p><strong>Database:</strong> MySQL</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-question-circle"></i> Help & Documentation</h5>
                    </div>
                    <div class="card-body">
                        <h6>Getting Started:</h6>
                        <ol>
                            <li>First, run "Setup Mail Tables" to create the database structure</li>
                            <li>Then, run "Initialize Default Data" to load sample templates and contacts</li>
                            <li>Use the Templates section to create custom email templates</li>
                            <li>Add contacts in the Contacts section</li>
                            <li>Send emails using the Send Email section</li>
                        </ol>
                        
                        <h6 class="mt-3">Template Variables:</h6>
                        <p>You can use variables in your templates using JSON format in the replacements field:</p>
                        <code>{"product_name": "Sample Product", "quantity": "100", "date": "2024-01-01"}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = window.API_CONFIGinv.getApiUrl();
        async function setupMailTables() {
            try {
                const response = await fetch(`${API_BASE}/mail/setup`, {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Mail tables created successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error setting up tables: ' + error.message);
            }
        }

        async function initDefaultData() {
            try {
                const response = await fetch(`${API_BASE}/mail/init_data`, {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Default data initialized successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error initializing data: ' + error.message);
            }
        }

        async function cleanupOldLogs() {
            try {
                const response = await fetch(`${API_BASE}/mail/cleanup`, { method: 'POST' });
                const data = await response.json();
                
                if (data.success) {
                    alert('Old email logs cleaned up successfully!');
                } else {
                    alert('Error cleaning up logs: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error cleaning up logs:', error);
                alert('Error cleaning up logs: ' + error.message);
            }
        }

        async function checkServiceStatus() {
            try {
                const response = await fetch(`${API_BASE}/mail/configs`);
                if (response.ok) {
                    alert('✅ Mail service is running and accessible!');
                } else {
                    alert('❌ Mail service returned an error: ' + response.status);
                }
            } catch (error) {
                alert('❌ Mail service is not accessible: ' + error.message);
            }
        }


        // Initialize theme toggle on page load
        window.addEventListener('load', function() {
            initThemeToggle();
        });
    </script>
</body>
</html>
