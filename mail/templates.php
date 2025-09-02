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
    <title>Email Templates - BNM Mail Management</title>
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
        
        .btn-sm {
            margin: 2px;
        }
        
        .modal-lg {
            max-width: 800px;
        }
        
        textarea {
            min-height: 120px;
        }
        
        .email-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            transition: background-color 0.3s ease, border-color 0.3s ease;
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

        body.dark-mode .modal-content {
            background-color: #1f2937;
            color: #e0e0e0;
        }

        body.dark-mode .modal-header {
            border-bottom-color: #374151;
        }

        body.dark-mode .modal-footer {
            border-top-color: #374151;
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

        body.dark-mode .email-preview {
            background-color: #374151;
            border-color: #4b5563;
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

        body.dark-mode .text-muted {
            color: #a0a0a0 !important;
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
                        <li class="breadcrumb-item active">Email Templates</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-file-alt text-primary"></i> Email Templates</h1>
                    <div>
                   
                        <button class="btn btn-primary" onclick="showAddConfigModal()">
                            <i class="fas fa-plus"></i> Add Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="configs-table">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Config Modal -->
    <div class="modal fade" id="configModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configModalTitle">Add Email Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="config-form">
                        <input type="hidden" id="config-id">
                        <div class="mb-3">
                            <label class="form-label">Template Name</label>
                            <input type="text" class="form-control" id="config-name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" id="config-subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Body</label>
                            <textarea class="form-control" id="config-body" rows="6" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">From Email</label>
                                    <input type="email" class="form-control" id="config-from-email" value="inventory.system.bnm@bnmparapharm.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">SMTP Server</label>
                                    <input type="text" class="form-control" id="config-smtp-server" value="mail.bnmparapharm.com">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="config-smtp-port" value="465">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="config-is-active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveConfig()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Preview Modal -->
    <div class="modal fade" id="emailPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Subject:</strong>
                        <div id="preview-subject" class="email-preview mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <strong>Body:</strong>
                        <div id="preview-body" class="email-preview mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = window.API_CONFIGinv.getApiUrl();

        async function loadConfigs() {
            try {
                const response = await fetch(`${API_BASE}/mail/configs`);
                const data = await response.json();
                
                if (data.success) {
                    const html = `
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.configs.map(config => `
                                    <tr>
                                        <td><strong>${config.config_name}</strong></td>
                                        <td>${config.subject}</td>
                                        <td><span class="badge ${config.is_active ? 'bg-success' : 'bg-secondary'}">${config.is_active ? 'Active' : 'Inactive'}</span></td>
                                        <td>${config.created_at}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editConfig(${config.id})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="previewEmail('${config.config_name}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                    document.getElementById('configs-table').innerHTML = html;
                } else {
                    document.getElementById('configs-table').innerHTML = '<p class="text-danger">Error loading templates: ' + data.error + '</p>';
                }
            } catch (error) {
                document.getElementById('configs-table').innerHTML = '<p class="text-danger">Error loading templates</p>';
            }
        }

        function showAddConfigModal() {
            document.getElementById('configModalTitle').textContent = 'Add Email Template';
            document.getElementById('config-form').reset();
            document.getElementById('config-id').value = '';
            new bootstrap.Modal(document.getElementById('configModal')).show();
        }

        async function editConfig(id) {
            try {
                const response = await fetch(`${API_BASE}/mail/configs`);
                const data = await response.json();
                
                if (data.success) {
                    const config = data.configs.find(c => c.id === id);
                    if (config) {
                        document.getElementById('configModalTitle').textContent = 'Edit Email Template';
                        document.getElementById('config-id').value = config.id;
                        document.getElementById('config-name').value = config.config_name;
                        document.getElementById('config-subject').value = config.subject;
                        document.getElementById('config-body').value = config.body;
                        document.getElementById('config-from-email').value = config.from_email;
                        document.getElementById('config-smtp-server').value = config.smtp_server;
                        document.getElementById('config-smtp-port').value = config.smtp_port;
                        document.getElementById('config-is-active').value = config.is_active ? '1' : '0';
                        
                        new bootstrap.Modal(document.getElementById('configModal')).show();
                    }
                }
            } catch (error) {
                alert('Error loading template for editing');
            }
        }

        async function saveConfig() {
            const form = document.getElementById('config-form');
            const id = document.getElementById('config-id').value;
            
            const data = {
                config_name: document.getElementById('config-name').value,
                subject: document.getElementById('config-subject').value,
                body: document.getElementById('config-body').value,
                from_email: document.getElementById('config-from-email').value,
                smtp_server: document.getElementById('config-smtp-server').value,
                smtp_port: parseInt(document.getElementById('config-smtp-port').value),
                is_active: document.getElementById('config-is-active').value === '1'
            };

            try {
                const url = id ? `${API_BASE}/mail/configs/${id}` : `${API_BASE}/mail/configs`;
                const method = id ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
                    loadConfigs();
                    alert(id ? 'Template updated successfully!' : 'Template created successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error saving template: ' + error.message);
            }
        }

        async function previewEmail(configName) {
            try {
                const response = await fetch(`${API_BASE}/mail/configs`);
                const data = await response.json();
                
                if (data.success) {
                    const config = data.configs.find(c => c.config_name === configName);
                    if (config) {
                        document.getElementById('preview-subject').textContent = config.subject;
                        document.getElementById('preview-body').textContent = config.body;
                        
                        new bootstrap.Modal(document.getElementById('emailPreviewModal')).show();
                    }
                }
            } catch (error) {
                alert('Error previewing email');
            }
        }

        // Load templates on page load
        window.addEventListener('load', function() {
            loadConfigs();
            initThemeToggle();
        });


    </script>
</body>
</html>
