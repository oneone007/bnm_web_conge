<?php
session_start();

// Check if the user is not logged in, redirect to login page
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
    <title>Email Logs - BNM Mail Management</title>
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
        
        .log-status-success {
            color: #28a745;
        }
        
        .log-status-failed {
            color: #dc3545;
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

        body.dark-mode .table {
            color: #e0e0e0;
            --bs-table-bg: transparent;
        }

        body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
        }

        body.dark-mode .table-striped > tbody > tr:nth-of-type(even) > td {
            color: #e0e0e0;
        }

        body.dark-mode .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
        }

        body.dark-mode .table tbody tr td {
            color: #e0e0e0;
        }

        body.dark-mode .table th {
            border-color: #374151;
            color: #e0e0e0;
        }

        body.dark-mode .table td {
            border-color: #374151;
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

        body.dark-mode .alert {
            background-color: #1f2937;
            border-color: #374151;
            color: #e0e0e0;
        }

        body.dark-mode .btn-outline-primary {
            color: #6bb6ff;
            border-color: #6bb6ff;
        }

        body.dark-mode .btn-outline-primary:hover {
            background-color: #6bb6ff;
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
            color: #95a24b;
            border-color: #95a24b;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #95a24b;
            color: #111827;
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

        body.dark-mode .text-danger {
            color: #e74c3c !important;
        }

        body.dark-mode .log-status-success {
            color: #5dc460;
        }

        body.dark-mode .log-status-failed {
            color: #e74c3c;
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
                        <li class="breadcrumb-item active">Email Logs</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-history text-warning"></i> Today's Email Logs</h1>
                    <div>
        
                        <button class="btn btn-warning" onclick="cleanupOldLogs()">
                            <i class="fas fa-trash"></i> Cleanup Old Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="logs-search" placeholder="Search today's emails...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="logs-status-filter">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-secondary" onclick="loadLogs()">
                            <i class="fas fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Only today's email logs are shown. Old logs are automatically cleaned up daily.
                </div>
                <div id="logs-table">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody id="logs-tbody">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = window.API_CONFIGinv.getApiUrl();

        async function loadLogs() {
            try {
                console.log('Loading logs...');
                const response = await fetch(`${API_BASE}/mail/logs`);
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Logs data:', data);
                
                let logsHtml = '';
                if (data.success && data.logs.length > 0) {
                    data.logs.forEach(log => {
                        const statusClass = log.status === 'success' ? 'text-success' : 'text-danger';
                        logsHtml += `
                            <tr>
                                <td>${log.to_email}</td>
                                <td>${log.subject}</td>
                                <td><span class="${statusClass}">${log.status}</span></td>
                                <td>${log.sent_at}</td>
                            </tr>
                        `;
                    });
                } else {
                    logsHtml = '<tr><td colspan="4" class="text-center">No email logs found for today</td></tr>';
                }
                
                document.getElementById('logs-tbody').innerHTML = logsHtml;
            } catch (error) {
                console.error('Error loading logs:', error);
                document.getElementById('logs-tbody').innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error loading logs: ${error.message}</td></tr>`;
            }
        }

        async function cleanupOldLogs() {
            try {
                const response = await fetch(`${API_BASE}/mail/cleanup`, { method: 'POST' });
                const data = await response.json();
                
                if (data.success) {
                    alert('Old email logs cleaned up successfully!');
                    loadLogs(); // Refresh logs view
                } else {
                    alert('Error cleaning up logs: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error cleaning up logs:', error);
                alert('Error cleaning up logs: ' + error.message);
            }
        }

        // Load logs on page load
        window.addEventListener('load', function() {
            loadLogs();
            initThemeToggle();
        });


    </script>
</body>
</html>
