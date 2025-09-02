<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Mail Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background: #2c3e50;
            transition: all 0.3s;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            color: #ecf0f1 !important;
            padding: 15px 20px;
            border-radius: 0;
        }
        .nav-link:hover {
            background: #34495e;
        }
        .nav-link.active {
            background: #3498db;
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .log-status-success {
            color: #28a745;
        }
        .log-status-failed {
            color: #dc3545;
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
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-3">
            <h4 class="text-white"><i class="fas fa-envelope"></i> Mail Admin</h4>
        </div>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#" onclick="showSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('configs')">
                    <i class="fas fa-cog"></i> Email Templates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('contacts')">
                    <i class="fas fa-address-book"></i> Contacts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('send-mail')">
                    <i class="fas fa-paper-plane"></i> Send Mail
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('logs')">
                    <i class="fas fa-history"></i> Today's Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('settings')">
                    <i class="fas fa-tools"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Section -->
        <div id="dashboard" class="content-section active">
            <h2><i class="fas fa-tachometer-alt"></i> Mail System Dashboard</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Email Templates</h5>
                            <h3 id="templates-count">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Active Contacts</h5>
                            <h3 id="contacts-count">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Emails Sent Today</h5>
                            <h3 id="emails-today">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Failed Emails Today</h5>
                            <h3 id="failed-emails">-</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Email Activity (Today)</h5>
                        </div>
                        <div class="card-body">
                            <div id="recent-activity">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Templates Section -->
        <div id="configs" class="content-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-cog"></i> Email Templates</h2>
                <button class="btn btn-primary" onclick="showAddConfigModal()">
                    <i class="fas fa-plus"></i> Add Template
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div id="configs-table">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Contacts Section -->
        <div id="contacts" class="content-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-address-book"></i> Email Contacts</h2>
                <button class="btn btn-primary" onclick="showAddContactModal()">
                    <i class="fas fa-plus"></i> Add Contact
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div id="contacts-table">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Send Mail Section -->
        <div id="send-mail" class="content-section">
            <h2><i class="fas fa-paper-plane"></i> Send Email</h2>
            <div class="card">
                <div class="card-body">
                    <form id="send-mail-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Template</label>
                                    <select class="form-select" id="send-config-select" onchange="loadTemplateForSend()">
                                        <option value="">Select a template...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Recipients</label>
                                    <div id="recipients-checkboxes">Loading...</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" id="send-subject" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message Body</label>
                            <textarea class="form-control" id="send-body" rows="8" readonly></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Replacements (JSON format)</label>
                            <textarea class="form-control" id="send-replacements" rows="3" placeholder='{"inventory_details": "Sample inventory details"}'></textarea>
                        </div>
                        
                        <button type="button" class="btn btn-success" onclick="sendEmail()">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Today's Email Logs Section -->
        <div id="logs" class="content-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-history"></i> Today's Email Logs</h2>
                <button class="btn btn-warning" onclick="cleanupOldLogs()">
                    <i class="fas fa-trash"></i> Cleanup Old Logs
                </button>
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
                    <div id="logs-table">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <div id="settings" class="content-section">
            <h2><i class="fas fa-tools"></i> System Settings</h2>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-warning" onclick="setupMailTables()">
                                <i class="fas fa-database"></i> Setup Mail Tables
                            </button>
                            <p class="text-muted mt-2">Create mail management database tables</p>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-info" onclick="initDefaultData()">
                                <i class="fas fa-download"></i> Initialize Default Data
                            </button>
                            <p class="text-muted mt-2">Load default email templates and contacts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
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

    <!-- Add/Edit Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalTitle">Add Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="contact-form">
                        <input type="hidden" id="contact-id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="contact-name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="contact-email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="contact-department">
                                <option value="Management">Management</option>
                                <option value="Operations">Operations</option>
                                <option value="IT">IT</option>
                                <option value="Finance">Finance</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" id="contact-position">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="contact-is-active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveContact()">Save</button>
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
        const API_BASE = 'http://localhost:5003';

        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
            
            // Load data for the section
            switch(sectionId) {
                case 'dashboard':
                    loadDashboard();
                    break;
                case 'configs':
                    loadConfigs();
                    break;
                case 'contacts':
                    loadContacts();
                    break;
                case 'send-mail':
                    loadSendMailData();
                    break;
                case 'logs':
                    loadLogs();
                    break;
            }
        }

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
                    const recentLogs = logsData.logs.slice(0, 5);
                    const activityHtml = recentLogs.map(log => `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>${log.to_email}</strong> - ${log.subject}
                                <br><small class="text-muted">${log.sent_at}</small>
                            </div>
                            <span class="badge ${log.status === 'success' ? 'bg-success' : 'bg-danger'}">${log.status}</span>
                        </div>
                    `).join('');
                    document.getElementById('recent-activity').innerHTML = activityHtml || 'No emails sent today';
                } else {
                    document.getElementById('emails-today').textContent = '0';
                    document.getElementById('failed-emails').textContent = '0';
                    document.getElementById('recent-activity').innerHTML = 'No emails sent today';
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        async function loadConfigs() {
            try {
                const response = await fetch(`${API_BASE}/mail/configs`);
                const data = await response.json();
                
                if (data.success) {
                    const html = `
                        <table class="table table-striped">
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

        async function loadContacts() {
            try {
                const response = await fetch(`${API_BASE}/mail/contacts`);
                const data = await response.json();
                
                if (data.success) {
                    const html = `
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.contacts.map(contact => `
                                    <tr>
                                        <td><strong>${contact.name}</strong></td>
                                        <td>${contact.email}</td>
                                        <td>${contact.department || '-'}</td>
                                        <td>${contact.position || '-'}</td>
                                        <td><span class="badge ${contact.is_active ? 'bg-success' : 'bg-secondary'}">${contact.is_active ? 'Active' : 'Inactive'}</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editContact(${contact.id})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                    document.getElementById('contacts-table').innerHTML = html;
                } else {
                    document.getElementById('contacts-table').innerHTML = '<p class="text-danger">Error loading contacts: ' + data.error + '</p>';
                }
            } catch (error) {
                document.getElementById('contacts-table').innerHTML = '<p class="text-danger">Error loading contacts</p>';
            }
        }

        async function loadSendMailData() {
            // Load templates for dropdown
            try {
                const configResponse = await fetch(`${API_BASE}/mail/configs`);
                const configData = await configResponse.json();
                
                if (configData.success) {
                    const options = configData.configs
                        .filter(c => c.is_active)
                        .map(c => `<option value="${c.config_name}">${c.config_name} - ${c.subject}</option>`)
                        .join('');
                    document.getElementById('send-config-select').innerHTML = '<option value="">Select a template...</option>' + options;
                }

                // Load contacts for checkboxes
                const contactResponse = await fetch(`${API_BASE}/mail/contacts`);
                const contactData = await contactResponse.json();
                
                if (contactData.success) {
                    const checkboxes = contactData.contacts
                        .filter(c => c.is_active)
                        .map(c => `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="${c.email}" id="contact-${c.id}">
                                <label class="form-check-label" for="contact-${c.id}">
                                    ${c.name} (${c.email})
                                </label>
                            </div>
                        `).join('');
                    document.getElementById('recipients-checkboxes').innerHTML = checkboxes;
                }
            } catch (error) {
                console.error('Error loading send mail data:', error);
            }
        }

        async function loadTemplateForSend() {
            const templateName = document.getElementById('send-config-select').value;
            if (!templateName) {
                document.getElementById('send-subject').value = '';
                document.getElementById('send-body').value = '';
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/mail/configs`);
                const data = await response.json();
                
                if (data.success) {
                    const template = data.configs.find(c => c.config_name === templateName);
                    if (template) {
                        document.getElementById('send-subject').value = template.subject;
                        document.getElementById('send-body').value = template.body;
                    }
                }
            } catch (error) {
                console.error('Error loading template:', error);
            }
        }

        async function sendEmail() {
            const templateName = document.getElementById('send-config-select').value;
            const replacementsText = document.getElementById('send-replacements').value;
            
            if (!templateName) {
                alert('Please select a template');
                return;
            }

            // Get selected recipients
            const checkboxes = document.querySelectorAll('#recipients-checkboxes input[type="checkbox"]:checked');
            const recipients = Array.from(checkboxes).map(cb => cb.value);
            
            if (recipients.length === 0) {
                alert('Please select at least one recipient');
                return;
            }

            let replacements = {};
            if (replacementsText.trim()) {
                try {
                    replacements = JSON.parse(replacementsText);
                } catch (error) {
                    alert('Invalid JSON format in replacements');
                    return;
                }
            }

            try {
                const response = await fetch(`${API_BASE}/mail/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        config_name: templateName,
                        to_emails: recipients,
                        replacements: replacements
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Emails sent successfully!');
                    // Clear form
                    document.getElementById('send-config-select').value = '';
                    document.getElementById('send-subject').value = '';
                    document.getElementById('send-body').value = '';
                    document.getElementById('send-replacements').value = '';
                    document.querySelectorAll('#recipients-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = false);
                } else {
                    alert('Error sending emails: ' + result.error);
                }
            } catch (error) {
                alert('Error sending emails: ' + error.message);
            }
        }

        function showAddConfigModal() {
            document.getElementById('configModalTitle').textContent = 'Add Email Template';
            document.getElementById('config-form').reset();
            document.getElementById('config-id').value = '';
            new bootstrap.Modal(document.getElementById('configModal')).show();
        }

        function showAddContactModal() {
            document.getElementById('contactModalTitle').textContent = 'Add Contact';
            document.getElementById('contact-form').reset();
            document.getElementById('contact-id').value = '';
            new bootstrap.Modal(document.getElementById('contactModal')).show();
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

        async function editContact(id) {
            try {
                const response = await fetch(`${API_BASE}/mail/contacts`);
                const data = await response.json();
                
                if (data.success) {
                    const contact = data.contacts.find(c => c.id === id);
                    if (contact) {
                        document.getElementById('contactModalTitle').textContent = 'Edit Contact';
                        document.getElementById('contact-id').value = contact.id;
                        document.getElementById('contact-name').value = contact.name;
                        document.getElementById('contact-email').value = contact.email;
                        document.getElementById('contact-department').value = contact.department || '';
                        document.getElementById('contact-position').value = contact.position || '';
                        document.getElementById('contact-is-active').value = contact.is_active ? '1' : '0';
                        
                        new bootstrap.Modal(document.getElementById('contactModal')).show();
                    }
                }
            } catch (error) {
                alert('Error loading contact for editing');
            }
        }

        async function saveConfig() {
            const form = document.getElementById('config-form');
            const formData = new FormData(form);
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

        async function saveContact() {
            const id = document.getElementById('contact-id').value;
            
            const data = {
                name: document.getElementById('contact-name').value,
                email: document.getElementById('contact-email').value,
                department: document.getElementById('contact-department').value,
                position: document.getElementById('contact-position').value,
                is_active: document.getElementById('contact-is-active').value === '1'
            };

            try {
                const url = `${API_BASE}/mail/contacts`;
                const method = 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
                    loadContacts();
                    alert('Contact saved successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error saving contact: ' + error.message);
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
                    loadDashboard();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error initializing data: ' + error.message);
            }
        }

        async function loadLogs() {
            try {
                const response = await fetch(`${API_BASE}/mail/logs`);
                const data = await response.json();
                
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
                document.getElementById('logs-tbody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading logs</td></tr>';
            }
        }

        async function cleanupOldLogs() {
            try {
                const response = await fetch(`${API_BASE}/mail/cleanup`, { method: 'POST' });
                const data = await response.json();
                
                if (data.success) {
                    alert('Old email logs cleaned up successfully!');
                    loadDashboard(); // Refresh dashboard
                    if (document.getElementById('logs-tbody')) {
                        loadLogs(); // Refresh logs view if visible
                    }
                } else {
                    alert('Error cleaning up logs: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error cleaning up logs:', error);
                alert('Error cleaning up logs: ' + error.message);
            }
        }

        // Load dashboard on page load
        window.addEventListener('load', function() {
            loadDashboard();
        });
    </script>
</body>
</html>
