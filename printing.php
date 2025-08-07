<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}
$page_identifier = 'print';


require_once 'check_permission.php';



// // Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
//     header("Location: Acess_Denied");    exit();
// }


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Printing System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/print_modern.css">
    <link rel="stylesheet" href="css/print_fix.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .user-info {
            margin-left: auto;
            display: flex;
            align-items: center;
            color: #fff;
            padding: 0 15px;
        }
        .user-info i {
            margin-right: 8px;
        }
        .logout-btn {
            margin-left: 15px;
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Place Select2 styling */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px;
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        
        /* Recipient autocomplete styling */
        .recipient-actions {
            display: flex;
            margin-top: 5px;
        }
        .recipient-actions button {
            padding: 5px 8px;
            font-size: 12px;
            margin-right: 5px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
        }
        .recipient-actions button:hover {
            background-color: #e0e0e0;
        }
        .recipient-suggestions {
            position: absolute;
            z-index: 100;
            width: 100%;
            max-height: 150px;
            overflow-y: auto;
            background-color: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            display: none;
        }
        .recipient-suggestion {
            padding: 8px 10px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .recipient-suggestion:hover {
            background-color: #f5f5f5;
        }
        .input-with-suggestions {
            position: relative;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <header class="app-header">
        <h1><i class="fas fa-money-check-alt"></i> BNM Check Printing System</h1>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>

    </header>

    <div class="container">
        <div class="main-content">
            <!-- Sidebar with form controls -->
            <aside class="sidebar">
                <div class="form-controls">
                    <div class="form-group">
                        <label for="bankSelect">Select Bank:</label>
                        <select id="bankSelect" class="form-control">
                            <option value="">Select a bank</option>
                            <option value="bna" selected>Banque Nationale d'Algérie</option>
                            <option value="albaraka">Al Baraka</option>
                            <option value="sg">Société Générale Algérie</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountInput">Amount (DA):</label>
                        <input type="number" id="amountInput" class="form-control" step="0.01" placeholder="0.00">
                        <div id="amountTextPreview" class="amount-preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="datePicker">Date:</label>
                        <input type="text" id="datePicker" class="form-control" placeholder="Select a date">
                    </div>

                    <div class="form-group">
                        <label for="placeSelect">Place:</label>
                        <select id="placeSelect" class="form-control select2-places">
                            <option value="">Select a place</option>
                            <option value="Adrar">Adrar</option>
                            <option value="Chlef">Chlef</option>
                            <option value="Laghouat">Laghouat</option>
                            <option value="Oum El Bouaghi">Oum El Bouaghi</option>
                            <option value="Batna">Batna</option>
                            <option value="Béjaïa">Béjaïa</option>
                            <option value="Biskra">Biskra</option>
                            <option value="Béchar">Béchar</option>
                            <option value="Blida">Blida</option>
                            <option value="Bouira">Bouira</option>
                            <option value="Tamanrasset">Tamanrasset</option>
                            <option value="Tébessa">Tébessa</option>
                            <option value="Tlemcen">Tlemcen</option>
                            <option value="Tiaret">Tiaret</option>
                            <option value="Tizi Ouzou">Tizi Ouzou</option>
                            <option value="Algiers">Algiers</option>
                            <option value="Djelfa">Djelfa</option>
                            <option value="Jijel">Jijel</option>
                            <option value="Sétif">Sétif</option>
                            <option value="Saïda">Saïda</option>
                            <option value="Skikda">Skikda</option>
                            <option value="Sidi Bel Abbès">Sidi Bel Abbès</option>
                            <option value="Annaba">Annaba</option>
                            <option value="Guelma">Guelma</option>
                            <option value="Constantine">Constantine</option>
                            <option value="Médéa">Médéa</option>
                            <option value="Mostaganem">Mostaganem</option>
                            <option value="M'Sila">M'Sila</option>
                            <option value="Mascara">Mascara</option>
                            <option value="Ouargla">Ouargla</option>
                            <option value="Oran">Oran</option>
                            <option value="El Bayadh">El Bayadh</option>
                            <option value="Illizi">Illizi</option>
                            <option value="Bordj Bou Arréridj">Bordj Bou Arréridj</option>
                            <option value="Boumerdès">Boumerdès</option>
                            <option value="El Tarf">El Tarf</option>
                            <option value="Tindouf">Tindouf</option>
                            <option value="Tissemsilt">Tissemsilt</option>
                            <option value="El Oued">El Oued</option>
                            <option value="Khenchela">Khenchela</option>
                            <option value="Souk Ahras">Souk Ahras</option>
                            <option value="Tipaza">Tipaza</option>
                            <option value="Mila">Mila</option>
                            <option value="Aïn Defla">Aïn Defla</option>
                            <option value="Naâma">Naâma</option>
                            <option value="Aïn Témouchent">Aïn Témouchent</option>
                            <option value="Ghardaïa">Ghardaïa</option>
                            <option value="Relizane">Relizane</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payToInput">Pay to the Order Of:</label>
                        <div class="input-with-suggestions">
                            <input type="text" id="payToInput" class="form-control" placeholder="Recipient name" autocomplete="off">
                            <div id="recipientSuggestions" class="recipient-suggestions"></div>
                        </div>
                        <div class="recipient-actions">
                            <button id="saveRecipientBtn" type="button" title="Save this recipient for future use">
                                <i class="fas fa-save"></i> Save Recipient
                            </button>
                            <button id="manageRecipientsBtn" type="button" title="Manage saved recipients">
                                <i class="fas fa-list"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main canvas area -->
             
            <div class="canvas-area">
                <div class="canvas-container">
                    <div id="checkCanvas">
                        <img id="checkImage" src="" alt="Bank Check Template">
                        <div id="amountElement" class="editable-element" data-field="amount"></div>
                        <div id="amountTextElement" class="editable-element" data-field="amountText"></div>
                        <div id="amountTextLine2Element" class="editable-element" data-field="amountTextLine2"></div>
                        <div id="dateElement" class="editable-element" data-field="date"></div>
                        <div id="placeElement" class="editable-element" data-field="place"></div>
                        <div id="payToElement" class="editable-element" data-field="payTo"></div>
                    </div>
                </div>

                <div class="controls-panel">
                    <div class="element-info">
                        <div class="selected-element">
                            <span>Selected Element:</span>
                            <span id="selectedElementName" class="element-name">None</span>
                        </div>
                    </div>
                    <div class="position-controls">
                        <button id="moveUp" title="Move Up"><i class="fas fa-arrow-up"></i></button>
                        <button id="moveDown" title="Move Down"><i class="fas fa-arrow-down"></i></button>
                        <button id="moveLeft" title="Move Left"><i class="fas fa-arrow-left"></i></button>
                        <button id="moveRight" title="Move Right"><i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button id="reloadPositionsBtn" class="btn btn-info">
                <i class="fas fa-sync-alt"></i> Refresh Display
            </button>
            <button id="savePositionsBtn" class="btn btn-secondary">
                <i class="fas fa-save"></i> Save Positions
            </button>
            <button id="printBtn" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Check
            </button>
        </div>
    </div>

    <footer class="app-footer">
        <div class="footer-content">
            <img src="log.png" alt="BNM Logo" class="footer-logo">
            <p>&copy; 2025 BNM Check Printing System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/p_number-to-french.js"></script>
    <script src="js/print_script.js"></script>
    <script>
        // Initialize Select2 for places dropdown
        $(document).ready(function() {
            $('.select2-places').select2({
                placeholder: "Search and select a place",
                allowClear: true
            });
            
            // Update the place element when Select2 changes
            $('.select2-places').on('change', function() {
                document.getElementById('placeElement').textContent = this.value;
            });
            
            // Initialize recipient functionality
            initRecipientMemory();
        });
        
        // Recipient memory functionality
        function initRecipientMemory() {
            const payToInput = document.getElementById('payToInput');
            const saveRecipientBtn = document.getElementById('saveRecipientBtn');
            const suggestionsContainer = document.getElementById('recipientSuggestions');
            
            // Keep a cache of recipients for faster access
            let recipientsCache = [];
            
            // Load saved recipients from server and localStorage
            function loadRecipients() {
                // First check localStorage for cached recipients
                let recipients = localStorage.getItem('savedRecipients');
                recipientsCache = recipients ? JSON.parse(recipients) : [];
                
                // Then try to fetch from server to update cache
                fetch('save_recipients.php')
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            recipientsCache = data;
                            localStorage.setItem('savedRecipients', JSON.stringify(data));
                        }
                    })
                    .catch(error => {
                        console.warn('Could not fetch recipients from server:', error);
                    });
                
                return recipientsCache;
            }
            
            // Initial load of recipients
            loadRecipients();
            
            // Save a recipient
            function saveRecipient(name) {
                if (!name.trim()) return;
                
                // Create a status message
                const toast = document.createElement('div');
                toast.classList.add('status-message');
                toast.textContent = 'Saving recipient...';
                document.body.appendChild(toast);
                
                // Save to server
                fetch('save_recipients.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ recipient: name.trim() })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        toast.textContent = 'Recipient saved!';
                        toast.classList.add('success');
                        
                        // Update local cache
                        if (!recipientsCache.includes(name.trim())) {
                            recipientsCache.push(name.trim());
                            localStorage.setItem('savedRecipients', JSON.stringify(recipientsCache));
                        }
                    } else {
                        toast.textContent = data.message || 'Error saving recipient';
                        toast.classList.add('error');
                    }
                    
                    // Remove toast after delay
                    setTimeout(() => {
                        toast.remove();
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    toast.textContent = 'Network error. Please try again.';
                    toast.classList.add('error');
                    
                    // Remove toast after delay
                    setTimeout(() => {
                        toast.remove();
                    }, 2000);
                });
            }
            
            // Show suggestions as user types
            payToInput.addEventListener('input', function() {
                const value = this.value.trim().toLowerCase();
                if (!value) {
                    suggestionsContainer.style.display = 'none';
                    return;
                }
                
                const recipients = loadRecipients();
                const matches = recipients.filter(recipient => 
                    recipient.toLowerCase().includes(value)
                );
                
                if (matches.length > 0) {
                    suggestionsContainer.innerHTML = '';
                    matches.forEach(match => {
                        const suggestion = document.createElement('div');
                        suggestion.classList.add('recipient-suggestion');
                        suggestion.textContent = match;
                        suggestion.addEventListener('click', function() {
                            payToInput.value = match;
                            document.getElementById('payToElement').textContent = match;
                            suggestionsContainer.style.display = 'none';
                        });
                        suggestionsContainer.appendChild(suggestion);
                    });
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            });
            
            // Hide suggestions when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (e.target !== payToInput && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.style.display = 'none';
                }
            });
            
            // Save button click handler
            saveRecipientBtn.addEventListener('click', function() {
                saveRecipient(payToInput.value);
            });
            
            // Manage recipients button
            document.getElementById('manageRecipientsBtn').addEventListener('click', function() {
                // Create modal for managing recipients
                const modal = document.createElement('div');
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.width = '100%';
                modal.style.height = '100%';
                modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.style.zIndex = '1000';
                
                const modalContent = document.createElement('div');
                modalContent.style.width = '400px';
                modalContent.style.maxHeight = '80%';
                modalContent.style.backgroundColor = 'white';
                modalContent.style.borderRadius = '8px';
                modalContent.style.padding = '20px';
                modalContent.style.overflowY = 'auto';
                
                const modalHeader = document.createElement('div');
                modalHeader.style.display = 'flex';
                modalHeader.style.justifyContent = 'space-between';
                modalHeader.style.alignItems = 'center';
                modalHeader.style.marginBottom = '15px';
                
                const modalTitle = document.createElement('h3');
                modalTitle.textContent = 'Manage Recipients';
                modalTitle.style.margin = '0';
                
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '&times;';
                closeBtn.style.background = 'none';
                closeBtn.style.border = 'none';
                closeBtn.style.fontSize = '24px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.addEventListener('click', () => modal.remove());
                
                modalHeader.appendChild(modalTitle);
                modalHeader.appendChild(closeBtn);
                
                // List of recipients
                const recipientsList = document.createElement('div');
                
                // Reload recipients from server to ensure we have the latest
                fetch('save_recipients.php')
                    .then(response => response.json())
                    .then(recipients => {
                        // Update cache
                        recipientsCache = recipients;
                        localStorage.setItem('savedRecipients', JSON.stringify(recipients));
                        
                        // Populate list
                        if (recipients.length === 0) {
                            const emptyMsg = document.createElement('p');
                            emptyMsg.textContent = 'No saved recipients yet.';
                            emptyMsg.style.textAlign = 'center';
                            emptyMsg.style.color = '#777';
                            recipientsList.appendChild(emptyMsg);
                        } else {
                            recipients.forEach((recipient) => {
                                const item = document.createElement('div');
                                item.style.display = 'flex';
                                item.style.justifyContent = 'space-between';
                                item.style.alignItems = 'center';
                                item.style.padding = '8px 0';
                                item.style.borderBottom = '1px solid #eee';
                                
                                const name = document.createElement('span');
                                name.textContent = recipient;
                                
                                const deleteBtn = document.createElement('button');
                                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                                deleteBtn.style.background = 'none';
                                deleteBtn.style.border = 'none';
                                deleteBtn.style.color = '#e53935';
                                deleteBtn.style.cursor = 'pointer';
                                deleteBtn.addEventListener('click', () => {
                                    // Delete from server
                                    fetch('save_recipients.php', {
                                        method: 'DELETE',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({ recipient: recipient })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            // Remove from UI
                                            item.remove();
                                            
                                            // Update local cache
                                            const index = recipientsCache.indexOf(recipient);
                                            if (index !== -1) {
                                                recipientsCache.splice(index, 1);
                                                localStorage.setItem('savedRecipients', JSON.stringify(recipientsCache));
                                            }
                                            
                                            // Show empty message if needed
                                            if (recipientsList.children.length === 0) {
                                                const emptyMsg = document.createElement('p');
                                                emptyMsg.textContent = 'No saved recipients yet.';
                                                emptyMsg.style.textAlign = 'center';
                                                emptyMsg.style.color = '#777';
                                                recipientsList.appendChild(emptyMsg);
                                            }
                                        } else {
                                            alert('Error deleting recipient: ' + (data.message || 'Unknown error'));
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('Network error. Please try again.');
                                    });
                                });
                                
                                item.appendChild(name);
                                item.appendChild(deleteBtn);
                                recipientsList.appendChild(item);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const errorMsg = document.createElement('p');
                        errorMsg.textContent = 'Error loading recipients. Please try again.';
                        errorMsg.style.textAlign = 'center';
                        errorMsg.style.color = '#e53935';
                        recipientsList.appendChild(errorMsg);
                    });
                
                
                modalContent.appendChild(modalHeader);
                modalContent.appendChild(recipientsList);
                modal.appendChild(modalContent);
                document.body.appendChild(modal);
            });
            
            // Show suggestions when focusing on input
            payToInput.addEventListener('focus', function() {
                const value = this.value.trim().toLowerCase();
                const recipients = loadRecipients();
                
                if (recipients.length > 0) {
                    suggestionsContainer.innerHTML = '';
                    recipients.forEach(recipient => {
                        if (!value || recipient.toLowerCase().includes(value)) {
                            const suggestion = document.createElement('div');
                            suggestion.classList.add('recipient-suggestion');
                            suggestion.textContent = recipient;
                            suggestion.addEventListener('click', function() {
                                payToInput.value = recipient;
                                document.getElementById('payToElement').textContent = recipient;
                                suggestionsContainer.style.display = 'none';
                            });
                            suggestionsContainer.appendChild(suggestion);
                        }
                    });
                    
                    if (suggestionsContainer.children.length > 0) {
                        suggestionsContainer.style.display = 'block';
                    } else {
                        suggestionsContainer.style.display = 'none';
                    }
                }
            });
        }
    </script>
</body>
</html>
