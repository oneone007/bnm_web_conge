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
    <title>Email Contacts - BNM Mail Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="theme.js"></script>
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

        /* Dark mode styles */
        body.dark-mode {
            background-color: #111827;
            color: #e0e0e0;
        }

        body.dark-mode .card {
            background-color: #1f2937;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            border: 1px solid #4b5563;
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
            border-bottom-color: #4b5563;
        }

        body.dark-mode .modal-footer {
            border-top-color: #4b5563;
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
            border-color: #4b5563;
            color: #e0e0e0;
        }

        body.dark-mode .table td {
            border-color: #4b5563;
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

        body.dark-mode .text-success {
            color: #5dc460 !important;
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
                        <li class="breadcrumb-item active">Email Contacts</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-address-book text-success"></i> Email Contacts</h1>
                    <div>
            
                        <button class="btn btn-primary" onclick="showAddContactModal()">
                            <i class="fas fa-plus"></i> Add Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="contacts-table">Loading...</div>
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
                                <option value="Admin">Admin</option>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'http://192.168.1.94:5003';

        async function loadContacts() {
            try {
                const response = await fetch(`${API_BASE}/mail/contacts`);
                const data = await response.json();
                
                if (data.success) {
                    const html = `
                        <table class="table table-striped table-hover">
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

        function showAddContactModal() {
            document.getElementById('contactModalTitle').textContent = 'Add Contact';
            document.getElementById('contact-form').reset();
            document.getElementById('contact-id').value = '';
            new bootstrap.Modal(document.getElementById('contactModal')).show();
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
                const url = id ? `${API_BASE}/mail/contacts/${id}` : `${API_BASE}/mail/contacts`;
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
                    bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
                    loadContacts();
                    alert(id ? 'Contact updated successfully!' : 'Contact saved successfully!');
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error saving contact: ' + error.message);
            }
        }

        // Load contacts on page load
        window.addEventListener('load', function() {
            loadContacts();
            initThemeToggle();
        });

        // Theme toggle functionality
    
    </script>
</body>
</html>
