<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Comptable'])) {
    header("Location: Acess_Denied");    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Distribution Editor</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .operator-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .operator-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .operator-card h2 {
            margin-top: 0;
            color: #3498db;
            font-size: 1.3em;
        }
        
        .client-count {
            font-style: italic;
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        
        .client-list {
            min-height: 200px;
            border: 2px dashed #eee;
            padding: 10px;
            margin-top: 10px;
        }
        
        .client-item {
            padding: 8px;
            margin: 4px 0;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: grab;
            border-left: 4px solid #3498db;
        }
        
        .client-item.dragging {
            opacity: 0.5;
            background: #e9ecef;
        }
        
        .controls {
            text-align: center;
            margin: 30px 0;
        }
        
        #save-changes {
            padding: 12px 24px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        
        #save-changes:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        #save-status {
            margin-top: 15px;
            color: #6c757d;
            font-style: italic;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s, fadeOut 0.5s 2.5s forwards;
        }
        
        .toast.success {
            background-color: #28a745;
        }
        
        .toast.error {
            background-color: #dc3545;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        @media (max-width: 768px) {
            .operator-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Add these to your existing styles */
.client-list {
    min-height: 200px;
    max-height: 400px; /* Limit height for better scrolling */
    overflow-y: auto; /* Enable scrolling for long lists */
    scroll-behavior: smooth; /* Smooth scrolling */
}

/* Visual cue for scrollable lists */
.client-list::-webkit-scrollbar {
    width: 8px;
}

.client-list::-webkit-scrollbar-thumb {
    background: #3498db;
    border-radius: 4px;
}

.sidebar {
    width: 200px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    position: sticky;
    top: 20px;
    height: fit-content;
}

.operator-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.operator-tag {
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    cursor: pointer;
    border-left: 4px solid #3498db;
}

.operator-tag:hover {
    background: #e9ecef;
}

.main-content {
    flex: 1;
}

@keyframes highlight {
    0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
    50% { box-shadow: 0 0 0 8px rgba(52, 152, 219, 0.3); }
    100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
}

/* Visual feedback during drag */
body.dragging-active {
    cursor: grabbing;
}
    </style>
</head>
<body>
        <div class="container">
              <!-- Sidebar -->
    <div class="sidebar">
        <h3>Operators</h3>
        <div class="operator-list" id="operator-sidebar">
            <!-- Dynamically populated -->
        </div>
    </div>
        <h1>Client Wallet Editor</h1>
        
        <div class="operator-grid" id="operators-container"></div>
        
        <div class="controls">
            <button id="undo-btn" class="btn" disabled>Undo</button>
            <button id="redo-btn" class="btn" disabled>Redo</button>
            <button id="save-changes" class="btn">Save All Changes</button>
        </div>
        <div id="save-status"></div>
    </div>
      <script>
        // Global variables
        let operatorsData = {};
        let hasUnsavedChanges = false;
        let scrollInterval;
        const SCROLL_SPEED = 10;
        const SCROLL_ZONE = 50;
        
        // DOM elements
        const saveBtn = document.getElementById('save-changes');
        const undoBtn = document.getElementById('undo-btn');
        const redoBtn = document.getElementById('redo-btn');
        const statusEl = document.getElementById('save-status');
        
        // State management
        let undoStack = [];
        let redoStack = [];
        let currentState = {};
        
        // Initialize the editor
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                showLoading();
                await loadOperatorData();
                renderOperatorSidebar();
                renderOperators();
                setupDragAndDrop();
                setupEventListeners();
                statusEl.textContent = "Ready. Make changes by dragging clients between operators.";
            } catch (error) {
                console.error("Initialization error:", error);
                showToast(`❌ Failed to load data: ${error.message}`, true);
                // Fallback data for debugging
                operatorsData = {
                    "AMINE": ["EURL CONTROL SANTE", "SARL M H B PHARM"],
                    "ANWAR": ["MER SANTE (PARA)"],
                    "BENNIA": ["AC LAB", "SARL HICH RINGE PARAPHARM"]
                };
                renderOperators();
            } finally {
                hideLoading();
            }
        });
        
        function showLoading() {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner"></div>';
            overlay.id = 'loading-overlay';
            document.body.appendChild(overlay);
        }
        
        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
        }
        
        async function loadOperatorData() {
        try {
        const timestamp = new Date().getTime();
        const response = await fetch(`operators_clients.json?t=${timestamp}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const rawData = await response.json();
        console.log("Data loaded:", rawData);
        
        // Process the raw data to match your expected format
        operatorsData = rawData;
        
    } catch (error) {
        console.error("Error loading operator data:", error);
        throw error; // Re-throw if you want calling code to handle it
    }
}
        
        function renderOperators() {
            const container = document.getElementById('operators-container');
            container.innerHTML = '';
            
            if (!operatorsData || Object.keys(operatorsData).length === 0) {
                container.innerHTML = '<div class="error">No operator data available</div>';
                return;
            }
            
            for (const [operator, clients] of Object.entries(operatorsData)) {
                const clientList = Array.isArray(clients) ? clients : [clients];
                
                const card = document.createElement('div');
                card.className = 'operator-card';
                card.innerHTML = `
                    <h2>
                        <span>${operator}</span>
                        <span class="client-count">${clientList.length} clients</span>
                    </h2>
                    <div class="client-list" data-operator="${operator}">
                        ${clientList.map(client => `
                            <div class="client-item" draggable="true" data-client="${client}">
                                ${client.replace(/%/g, ' ').trim()}
                            </div>
                        `).join('')}
                    </div>
                `;
                container.appendChild(card);
            }
        }
        
        function setupEventListeners() {
            saveBtn.addEventListener('click', saveChanges);
            undoBtn.addEventListener('click', undo);
            redoBtn.addEventListener('click', redo);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'z') {
                    e.preventDefault();
                    undo();
                } else if (e.ctrlKey && e.key === 'y') {
                    e.preventDefault();
                    redo();
                }
            });
        }
        
        function saveState() {
            const state = {};
            document.querySelectorAll('.client-list').forEach(list => {
                state[list.dataset.operator] = 
                    Array.from(list.querySelectorAll('.client-item'))
                         .map(item => item.dataset.client);
            });
            
            undoStack.push(JSON.stringify(currentState));
            redoStack = [];
            currentState = state;
            hasUnsavedChanges = true;
            saveBtn.disabled = false;
            updateUndoRedoButtons();
        }
        
        function undo() {
            if (undoStack.length === 0) return;
            
            redoStack.push(JSON.stringify(currentState));
            currentState = JSON.parse(undoStack.pop());
            applyState(currentState);
            updateUndoRedoButtons();
        }
        
        function redo() {
            if (redoStack.length === 0) return;
            
            undoStack.push(JSON.stringify(currentState));
            currentState = JSON.parse(redoStack.pop());
            applyState(currentState);
            updateUndoRedoButtons();
        }
        
        function applyState(state) {
            for (const [operator, clients] of Object.entries(state)) {
                const list = document.querySelector(`.client-list[data-operator="${operator}"]`);
                if (list) {
                    list.innerHTML = clients.map(client => `
                        <div class="client-item" draggable="true" data-client="${client}">
                            ${client.replace(/%/g, ' ').trim()}
                        </div>
                    `).join('');
                }
            }
            setupDragAndDrop();
            updateClientCounts();
        }
        
        function updateUndoRedoButtons() {
            undoBtn.disabled = undoStack.length === 0;
            redoBtn.disabled = redoStack.length === 0;
        }
        
        function updateClientCounts() {
            document.querySelectorAll('.operator-card').forEach(card => {
                const operator = card.querySelector('h2 span').textContent;
                const count = card.querySelectorAll('.client-item').length;
                card.querySelector('.client-count').textContent = `${count} clients`;
            });
        }
        
        function setupDragAndDrop() {
            let draggedItem = null;
            let dragSource = null;
            
            document.querySelectorAll('.client-item').forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    draggedItem = this;
                    dragSource = this.parentElement;
                    setTimeout(() => {
                        this.classList.add('dragging');
                        document.body.classList.add('dragging-active');
                    }, 0);
                    
                    // Set custom drag image
                    const dragImage = this.cloneNode(true);
                    dragImage.style.position = 'absolute';
                    dragImage.style.top = '-9999px';
                    document.body.appendChild(dragImage);
                    e.dataTransfer.setDragImage(dragImage, 0, 0);
                    setTimeout(() => document.body.removeChild(dragImage), 0);
                });
                
                item.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                    document.body.classList.remove('dragging-active');
                    stopAutoScroll();
                    draggedItem = null;
                    dragSource = null;
                });
            });
            
            document.querySelectorAll('.client-list').forEach(list => {
                list.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('drop-target');
                    checkScroll(e.clientY);
                });
                
                list.addEventListener('dragleave', function() {
                    this.classList.remove('drop-target');
                    stopAutoScroll();
                });
                
                list.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drop-target');
                    stopAutoScroll();
                    
                    if (draggedItem && !this.contains(draggedItem)) {
                        saveState();
                        this.appendChild(draggedItem);
                        updateClientCounts();
                        statusEl.textContent = `Moved client to ${this.dataset.operator} (unsaved changes)`;
                    }
                });
            });
            
            function checkScroll(yPos) {
                const viewportHeight = window.innerHeight;
                stopAutoScroll();
                
                if (yPos < SCROLL_ZONE) {
                    scrollInterval = setInterval(() => {
                        window.scrollBy(0, -SCROLL_SPEED);
                    }, 20);
                } else if (yPos > viewportHeight - SCROLL_ZONE) {
                    scrollInterval = setInterval(() => {
                        window.scrollBy(0, SCROLL_SPEED);
                    }, 20);
                }
            }
            
            function stopAutoScroll() {
                if (scrollInterval) {
                    clearInterval(scrollInterval);
                    scrollInterval = null;
                }
            }

             // Make operator tags in sidebar drop targets
    document.querySelectorAll('.operator-tag').forEach(tag => {
        tag.addEventListener('dragover', (e) => {
            e.preventDefault();
            tag.classList.add('drop-target');
        });

        tag.addEventListener('dragleave', () => {
            tag.classList.remove('drop-target');
        });

        tag.addEventListener('drop', (e) => {
            e.preventDefault();
            tag.classList.remove('drop-target');

            if (draggedItem) {
                const operator = tag.dataset.operator;
                const targetList = document.querySelector(`.client-list[data-operator="${operator}"]`);
                
                if (targetList && !targetList.contains(draggedItem)) {
                    saveState();
                    targetList.appendChild(draggedItem);
                    updateClientCounts();
                    statusEl.textContent = `Moved client to ${operator} (unsaved changes)`;
                    
                    // Scroll the target operator card into view
                    targetList.closest('.operator-card').scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }
        });
    });

    document.querySelectorAll('.operator-tag').forEach(tag => {
    tag.addEventListener('click', () => {
        const operator = tag.dataset.operator;
        const card = document.querySelector(`.client-list[data-operator="${operator}"]`)?.closest('.operator-card');
        if (card) {
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Visual feedback
            card.style.animation = 'highlight 1s';
            card.addEventListener('animationend', () => {
                card.style.animation = '';
            });
        }
    });
});
}
        
        
        async function saveChanges() {
    if (!hasUnsavedChanges) {
        showToast("No changes to save", true);
        return;
    }
    
    try {
        saveBtn.disabled = true;
        statusEl.textContent = "Validating changes...";
        
        // Validate no empty operator lists
        const emptyOperators = [];
        document.querySelectorAll('.client-list').forEach(list => {
            if (list.querySelectorAll('.client-item').length === 0) {
                emptyOperators.push(list.dataset.operator);
            }
        });
        
        if (emptyOperators.length > 0) {
            throw new Error(`Operators cannot be empty: ${emptyOperators.join(', ')}`);
        }
        
        statusEl.textContent = "Saving changes...";
        
        // Create object with current data
        const currentData = {};
        document.querySelectorAll('.client-list').forEach(list => {
            const operator = list.dataset.operator;
            currentData[operator] = 
                Array.from(list.querySelectorAll('.client-item'))
                     .map(item => item.dataset.client);
        });
        
        // Define the custom order
        const CUSTOM_ORDER = ["MOHCEN", "KERMICHE", "BOULKROUN", "AMINE", "YACINE", 
                             "KARIM", "BENNIA", "BOUGHABA", "BOUSBIAT", "ANWAR"];
        
        // Create new ordered object
        const orderedData = {};
        CUSTOM_ORDER.forEach(operator => {
            if (currentData.hasOwnProperty(operator)) {
                orderedData[operator] = currentData[operator];
            }
        });
        
        // Add any remaining operators not in CUSTOM_ORDER (just in case)
        Object.keys(currentData).forEach(operator => {
            if (!orderedData.hasOwnProperty(operator)) {
                orderedData[operator] = currentData[operator];
            }
        });
        
        const response = await fetch('http://192.168.1.200:5002/api/save-json', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                data: orderedData,
                filename: 'operators_clients.json'
            })
        });
        
        const result = await response.json();
        if (response.ok) {
            hasUnsavedChanges = false;
            operatorsData = orderedData; // Update with ordered data
            statusEl.textContent = "Changes saved successfully!";
            showToast('✅ Client assignments updated with correct order!');
            saveBtn.disabled = true;
            
            // Optional: Re-render to reflect any ordering changes
            renderOperators();
        } else {
            throw new Error(result.error || 'Failed to save changes');
        }
    } catch (error) {
        console.error("Save error:", error);
        statusEl.textContent = `Error: ${error.message}`;
        showToast(`❌ ${error.message}`, true);
    } finally {
        saveBtn.disabled = false;
    }
}

function renderOperatorSidebar() {
    const sidebar = document.getElementById('operator-sidebar');
    sidebar.innerHTML = '';

    Object.keys(operatorsData).forEach(operator => {
        const tag = document.createElement('div');
        tag.className = 'operator-tag';
        tag.textContent = operator;
        tag.dataset.operator = operator;
        sidebar.appendChild(tag);
    });
}
        
        function showToast(message, isError = false) {
            // Remove existing toasts
            document.querySelectorAll('.toast').forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `toast ${isError ? 'error' : 'success'}`;
            toast.innerHTML = `
                <span>${message}</span>
                <button class="toast-close">&times;</button>
            `;
            
            document.body.appendChild(toast);
            
            // Close button functionality
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.remove();
            });
            
            // Auto-remove after delay
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>