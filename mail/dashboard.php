<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../BNM");
    exit();
}

$Role = $_SESSION['Role'] ?? 'Unknown';
$username = $_SESSION['username'] ?? 'Guest';

// include_once "../sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Management Dashboard - BNM</title>
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
            margin-bottom: 20px;
            background-color: #ffffff;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .quick-action-card {
            transition: transform 0.2s ease, background-color 0.3s ease;
            cursor: pointer;
        }
        
        .quick-action-card:hover {
            transform: translateY(-5px);
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #111827;
            color: #e0e0e0;
        }

        body.dark-mode .card {
            background-color: #1f2937;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            border: 1px solid #374151;
        }

        body.dark-mode .card-header {
            background-color: #374151;
            border-bottom: 1px solid #4b5563;
            color: #e0e0e0;
        }

        body.dark-mode .text-muted {
            color: #a0a0a0 !important;
        }

        body.dark-mode .text-primary {
            color: #6bb6ff !important;
        }

        body.dark-mode .text-success {
            color: #5dc460 !important;
        }

        body.dark-mode .text-info {
            color: #46c5dc !important;
        }

        body.dark-mode .text-warning {
            color: #f39c12 !important;
        }

        body.dark-mode .text-danger {
            color: #e74c3c !important;
        }

        body.dark-mode .quick-action-card:hover {
            background-color: #374151;
        }

        body.dark-mode .border-bottom {
            border-color: #374151 !important;
        }

        body.dark-mode .btn-outline-primary {
            color: #6bb6ff;
            border-color: #6bb6ff;
        }

        body.dark-mode .btn-outline-primary:hover {
            background-color: #6bb6ff;
            color: #111827;
        }

        body.dark-mode .btn-outline-info {
            color: #46c5dc;
            border-color: #46c5dc;
        }

        body.dark-mode .btn-outline-info:hover {
            background-color: #46c5dc;
            color: #111827;
        }

        body.dark-mode .btn-outline-warning {
            color: #f39c12;
            border-color: #f39c12;
        }

        body.dark-mode .btn-outline-warning:hover {
            background-color: #f39c12;
            color: #111827;
        }

        body.dark-mode .btn-outline-secondary {
            color: #95a24b;
            border-color: #95a24b;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #95a24b;
            color: #111827;
        }

        /* Dark mode scrollbar */
        body.dark-mode .recent-activity::-webkit-scrollbar {
            width: 8px;
        }

        body.dark-mode .recent-activity::-webkit-scrollbar-track {
            background: #1f2937;
        }

        body.dark-mode .recent-activity::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }

        body.dark-mode .recent-activity::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }

        /* Disabled button styles */
        body.dark-mode .btn:disabled {
            background-color: #374151;
            border-color: #4b5563;
            color: #6b7280;
            opacity: 0.6;
        }

        body.dark-mode .alert-info {
            background-color: #1f2937;
            border-color: #374151;
            color: #9ca3af;
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
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-envelope text-primary"></i> Mail Management Dashboard</h1>
                    <p class="text-muted">Manage email templates, contacts, and monitor email activity</p>
                </div>
                <div>
    
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <h3 id="templates-count">-</h3>
                        <p class="mb-0">Email Templates</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card success">
                    <div class="card-body text-center">
                        <i class="fas fa-address-book fa-2x mb-2"></i>
                        <h3 id="contacts-count">-</h3>
                        <p class="mb-0">Active Contacts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card info">
                    <div class="card-body text-center">
                        <i class="fas fa-paper-plane fa-2x mb-2"></i>
                        <h3 id="emails-today">-</h3>
                        <p class="mb-0">Emails Sent Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card warning">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h3 id="failed-emails">-</h3>
                        <p class="mb-0">Failed Emails Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h3>Quick Actions</h3>
            </div>
            <div class="col-md-2">
                <div class="card quick-action-card" onclick="navigateTo('mail_templates')">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h5>Templates</h5>
                        <p class="text-muted">Manage email templates</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card quick-action-card" onclick="navigateTo('mail_contacts')">
                    <div class="card-body text-center">
                        <i class="fas fa-address-book fa-3x text-success mb-3"></i>
                        <h5>Contacts</h5>
                        <p class="text-muted">Manage email contacts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card quick-action-card" onclick="navigateTo('mail_recipients')">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h5>Recipients</h5>
                        <p class="text-muted">Manage recipient lists</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card quick-action-card" onclick="navigateTo('send_mail')">
                    <div class="card-body text-center">
                        <i class="fas fa-paper-plane fa-3x text-warning mb-3"></i>
                        <h5>Send Email</h5>
                        <p class="text-muted">Send emails using templates</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card quick-action-card" onclick="navigateTo('mail_logs')">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-3x text-secondary mb-3"></i>
                        <h5>Logs</h5>
                        <p class="text-muted">Monitor email activity</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card quick-action-card" onclick="navigateTo('mail_settings')">
                    <div class="card-body text-center">
                        <i class="fas fa-cog fa-3x text-danger mb-3"></i>
                        <h5>Settings</h5>
                        <p class="text-muted">Configure mail settings</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Recent Email Activity</h5>
                    </div>
                    <div class="card-body recent-activity">
                        <div id="recent-activity">Loading...</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> System Actions</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($Role === 'Developer'): ?>
                            <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="setupMailTables()">
                                <i class="fas fa-database"></i> Setup Mail Tables
                            </button>
                            <button class="btn btn-outline-info btn-sm w-100 mb-2" onclick="initDefaultData()">
                                <i class="fas fa-download"></i> Initialize Default Data
                            </button>
                            <button class="btn btn-outline-warning btn-sm w-100 mb-2" onclick="cleanupOldLogs()">
                                <i class="fas fa-trash"></i> Cleanup Old Logs
                            </button>
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="refreshDashboard()">
                                <i class="fas fa-refresh"></i> Refresh Dashboard
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-primary btn-sm w-100 mb-2" disabled title="Developer access required">
                                <i class="fas fa-database"></i> Setup Mail Tables
                            </button>
                            <button class="btn btn-outline-info btn-sm w-100 mb-2" disabled title="Developer access required">
                                <i class="fas fa-download"></i> Initialize Default Data
                            </button>
                            <button class="btn btn-outline-warning btn-sm w-100 mb-2" disabled title="Developer access required">
                                <i class="fas fa-trash"></i> Cleanup Old Logs
                            </button>
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="refreshDashboard()">
                                <i class="fas fa-refresh"></i> Refresh Dashboard
                            </button>
                            <div class="alert alert-info mt-2 p-2">
                                <small><i class="fas fa-info-circle"></i> Developer role required for system actions</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const API_BASE = window.API_CONFIGinv.getApiUrl();

        async function loadDashboard() {
            try {
                // Load configs count
                const configsResponse = await fetch(`${API_BASE}/mail/configs`);
                const configsData = await configsResponse.json();
                document.getElementById('templates-count').textContent = configsData.success ? configsData.configs.length : '0';

                // Load contacts count
                const contactsResponse = await fetch(`${API_BASE}/mail/contacts`);
                const contactsData = await contactsResponse.json();
                document.getElementById('contacts-count').textContent = contactsData.success ? contactsData.contacts.length : '0';

                // Load today's email logs
                const logsResponse = await fetch(`${API_BASE}/mail/logs`);
                const logsData = await logsResponse.json();
                
                if (logsData.success) {
                    const totalEmails = logsData.logs.length;
                    const failedEmails = logsData.logs.filter(log => log.status === 'failed').length;
                    
                    document.getElementById('emails-today').textContent = totalEmails;
                    document.getElementById('failed-emails').textContent = failedEmails;

                    // Show recent activity (today's emails)
                    const recentLogs = logsData.logs.slice(0, 10);
                    const activityHtml = recentLogs.map(log => `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>${log.to_email}</strong> - ${log.subject}
                                <br><small class="text-muted">${log.sent_at}</small>
                            </div>
                            <span class="badge ${log.status === 'success' ? 'bg-success' : 'bg-danger'}">${log.status}</span>
                        </div>
                    `).join('');
                    document.getElementById('recent-activity').innerHTML = activityHtml || '<p class="text-muted">No emails sent today</p>';
                } else {
                    document.getElementById('emails-today').textContent = '0';
                    document.getElementById('failed-emails').textContent = '0';
                    document.getElementById('recent-activity').innerHTML = '<p class="text-muted">No emails sent today</p>';
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
                document.getElementById('recent-activity').innerHTML = '<p class="text-danger">Error loading email activity</p>';
            }
        }

        async function setupMailTables() {
            try {
                const response = await fetch(`${API_BASE}/mail/setup`, {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Mail tables created successfully!');
                    loadDashboard();
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
                    loadDashboard();
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
                    loadDashboard();
                } else {
                    alert('Error cleaning up logs: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error cleaning up logs:', error);
                alert('Error cleaning up logs: ' + error.message);
            }
        }

        function refreshDashboard() {
            loadDashboard();
        }

        function navigateTo(page) {
            // Map page names to actual file names
            const pageMap = {
                'mail_templates': 'mail_templates',
                'mail_contacts': 'mail_contacts',
                'mail_recipients': 'mail_recipients',
                'send_mail': 'send_mail',
                'mail_logs': 'mail_logs',
                'mail_settings': 'mail_settings'
            };
            
            const targetPage = pageMap[page] || page;
            window.location.href = targetPage;
        }

        // Load dashboard on page load
        window.addEventListener('load', function() {
            loadDashboard();
            initThemeToggle();
        });


    </script>
</body>
</html>
