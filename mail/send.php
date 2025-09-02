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
    <title>Send Email - BNM Mail Management</title>
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
        
        textarea {
            min-height: 120px;
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
            background-color: #363636;
            border-bottom: 1px solid #404040;
            color: #e0e0e0;
        }

        body.dark-mode .form-control {
            background-color: #374151;
            border-color: #404040;
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

        body.dark-mode .btn-primary {
            background-color: #6bb6ff;
            border-color: #6bb6ff;
        }

        body.dark-mode .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
        }

        body.dark-mode .btn-outline-secondary {
            color: #95a24b;
            border-color: #95a24b;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #95a24b;
            color: #1a1a1a;
        }

        body.dark-mode .text-muted {
            color: #a0a0a0 !important;
        }

        body.dark-mode .text-primary {
            color: #6bb6ff !important;
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
                        <li class="breadcrumb-item active">Send Email</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-paper-plane text-info"></i> Send Email</h1>
               
                </div>
            </div>
        </div>

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
                        <div class="form-text">
                            Use JSON format to replace variables in the template. For example: {"product_name": "Product A", "quantity": "100"}
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="sendEmail()">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                            <i class="fas fa-times"></i> Clear Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = window.API_CONFIGinv.getApiUrl();

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
                    clearForm();
                } else {
                    alert('Error sending emails: ' + result.error);
                }
            } catch (error) {
                alert('Error sending emails: ' + error.message);
            }
        }

        function clearForm() {
            document.getElementById('send-config-select').value = '';
            document.getElementById('send-subject').value = '';
            document.getElementById('send-body').value = '';
            document.getElementById('send-replacements').value = '';
            document.querySelectorAll('#recipients-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = false);
        }

        // Load send mail data on page load
        window.addEventListener('load', function() {
            loadSendMailData();
            initThemeToggle();
        });

        // Theme toggle functionality
    
    </script>
</body>
</html>
