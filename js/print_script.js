document.addEventListener('DOMContentLoaded', function() {
    // Element references
    const bankSelect = document.getElementById('bankSelect');
    const amountInput = document.getElementById('amountInput');
    const amountTextPreview = document.getElementById('amountTextPreview');
    const datePicker = document.getElementById('datePicker');
    const placeSelect = document.getElementById('placeSelect');
    const payToInput = document.getElementById('payToInput');
    const checkImage = document.getElementById('checkImage');
    const checkCanvas = document.getElementById('checkCanvas');
    
    // Canvas elements
    const amountElement = document.getElementById('amountElement');
    const amountTextElement = document.getElementById('amountTextElement');
    const amountTextLine2Element = document.getElementById('amountTextLine2Element');
    const dateElement = document.getElementById('dateElement');
    const placeElement = document.getElementById('placeElement');
    const payToElement = document.getElementById('payToElement');
    
    // Control elements
    const moveUp = document.getElementById('moveUp');
    const moveDown = document.getElementById('moveDown');
    const moveLeft = document.getElementById('moveLeft');
    const moveRight = document.getElementById('moveRight');
    const selectedElementName = document.getElementById('selectedElementName');
    const reloadPositionsBtn = document.getElementById('reloadPositionsBtn');
    const savePositionsBtn = document.getElementById('savePositionsBtn');
    const printBtn = document.getElementById('printBtn');

    // Initialize date picker
    flatpickr(datePicker, {
        dateFormat: "d/m/Y",
        defaultDate: new Date()
    });

    // Store element positions for each bank - always load fresh from canva.json on page load
    // Clear any cached data first to ensure we get fresh canva coordinates
    sessionStorage.removeItem('checkElementPositions');
    localStorage.removeItem('checkElementPositions');
    let elementPositions = loadPositions(true) || {
        bna: {
            amount: { top: 100, left: 600 },
            amountText: { top: 140, left: 200 },
            amountTextLine2: { top: 160, left: 200 },
            date: { top: 170, left: 550 },
            place: { top: 170, left: 450 },
            payTo: { top: 140, left: 350 }
        },
        albaraka: {
            amount: { top: 100, left: 600 },
            amountText: { top: 140, left: 200 },
            amountTextLine2: { top: 160, left: 200 },
            date: { top: 170, left: 550 },
            place: { top: 170, left: 450 },
            payTo: { top: 140, left: 350 }
        },
        sg: {
            amount: { top: 100, left: 600 },
            amountText: { top: 140, left: 200 },
            amountTextLine2: { top: 160, left: 200 },
            date: { top: 170, left: 550 },
            place: { top: 170, left: 450 },
            payTo: { top: 140, left: 350 }
        },
        agb: {
            amount: { top: 100, left: 600 },
            amountText: { top: 140, left: 200 },
            amountTextLine2: { top: 160, left: 200 },
            date: { top: 170, left: 550 },
            place: { top: 170, left: 450 },
            payTo: { top: 140, left: 350 }
        }
    };

    // Track selected element for positioning
    let selectedElement = null;
    let currentBank = '';

    // Initialize the canvas elements
    function initializeCanvasElements() {
        const elements = checkCanvas.querySelectorAll('.editable-element');
        elements.forEach(el => {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                selectElement(el);
            });
        });

        // Deselect when clicking on the canvas
        checkCanvas.addEventListener('click', function() {
            deselectAllElements();
        });
    }

    // Select an element
    function selectElement(element) {
        deselectAllElements();
        element.classList.add('selected');
        selectedElement = element;
        selectedElementName.textContent = element.dataset.field;
    }

    // Deselect all elements
    function deselectAllElements() {
        const elements = document.querySelectorAll('.editable-element');
        elements.forEach(el => el.classList.remove('selected'));
        selectedElement = null;
        selectedElementName.textContent = 'None';
    }

    // Update element positions on the canvas
    function updateElementPositions() {
        if (!currentBank) return;
        
        const positions = elementPositions[currentBank];
        console.log(`Updating display positions for ${currentBank}:`, positions);
        
        for (const field in positions) {
            const element = document.getElementById(`${field}Element`);
            if (element) {
                element.style.top = `${positions[field].top}px`;
                element.style.left = `${positions[field].left}px`;
            }
        }
    }

    // Move the selected element
    function moveElement(direction, amount = 1) {
        if (!selectedElement || !currentBank) return;
        
        const field = selectedElement.dataset.field;
        const positions = elementPositions[currentBank];
        
        switch (direction) {
            case 'up':
                positions[field].top -= amount;
                break;
            case 'down':
                positions[field].top += amount;
                break;
            case 'left':
                positions[field].left -= amount;
                break;
            case 'right':
                positions[field].left += amount;
                break;
        }
        
        updateElementPositions();
    }

    // Load positions from server JSON file
    function loadPositions(forceReload = false) {
        // First try to load from sessionStorage as a quick cache (unless forced to reload)
        if (!forceReload) {
            const cachedPositions = sessionStorage.getItem('checkElementPositions');
            if (cachedPositions) {
                return JSON.parse(cachedPositions);
            }
        }
        
        // Set up a default positions object
        let defaultPositions = {
            bna: {
                amount: { top: 100, left: 600 },
                amountText: { top: 140, left: 200 },
                amountTextLine2: { top: 160, left: 200 },
                date: { top: 170, left: 550 },
                place: { top: 170, left: 450 },
                payTo: { top: 140, left: 350 }
            },
            albaraka: {
                amount: { top: 100, left: 600 },
                amountText: { top: 140, left: 200 },
                amountTextLine2: { top: 160, left: 200 },
                date: { top: 170, left: 550 },
                place: { top: 170, left: 450 },
                payTo: { top: 140, left: 350 }
            },
            sg: {
                amount: { top: 100, left: 600 },
                amountText: { top: 140, left: 200 },
                amountTextLine2: { top: 160, left: 200 },
                date: { top: 170, left: 550 },
                place: { top: 170, left: 450 },
                payTo: { top: 140, left: 350 }
            },
            agb: {
                amount: { top: 100, left: 600 },
                amountText: { top: 140, left: 200 },
                amountTextLine2: { top: 160, left: 200 },
                date: { top: 170, left: 550 },
                place: { top: 170, left: 450 },
                payTo: { top: 140, left: 350 }
            }
        };
        
        // Try to fetch from server (using synchronous XHR for simplicity in initialization)
        // Load from canva.json for display coordinates
        let serverPositions = null;
        
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'save_coordinates_dual.php?type=canva', false); // Synchronous request
            xhr.send();
            
            if (xhr.status === 200) {
                console.log('Loading display coordinates from canva.json');
                serverPositions = JSON.parse(xhr.responseText);
                
                // Ensure amountTextLine2 exists in all banks
                for (const bank in serverPositions) {
                    if (!serverPositions[bank].amountTextLine2) {
                        // If amountTextLine2 doesn't exist, create it based on amountText position
                        serverPositions[bank].amountTextLine2 = {
                            top: serverPositions[bank].amountText.top + 20,
                            left: serverPositions[bank].amountText.left
                        };
                    }
                }
                
                // Cache in sessionStorage
                sessionStorage.setItem('checkElementPositions', JSON.stringify(serverPositions));
                return serverPositions;
            } else {
                console.log('Could not load coordinates from server, using defaults');
                // Use defaults, but don't return yet
            }
        } catch (error) {
            console.error('Error loading positions from server:', error);
            // Use defaults, but don't return yet
        }
        
        // As a fallback, try localStorage
        const localPositions = localStorage.getItem('checkElementPositions');
        if (localPositions) {
            const parsedPositions = JSON.parse(localPositions);
            
            // Ensure amountTextLine2 exists in all banks
            for (const bank in parsedPositions) {
                if (!parsedPositions[bank].amountTextLine2) {
                    // If amountTextLine2 doesn't exist, create it based on amountText position
                    parsedPositions[bank].amountTextLine2 = {
                        top: parsedPositions[bank].amountText.top + 20,
                        left: parsedPositions[bank].amountText.left
                    };
                }
            }
            
            return parsedPositions;
        }
        
        return defaultPositions;
    }

    // Save positions to server JSON file and local storage
    function savePositions() {
        // First save to sessionStorage for immediate access
        sessionStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
        localStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
        
        // Create a status message element
        const statusMsg = document.createElement('div');
        statusMsg.className = 'status-message';
        statusMsg.textContent = 'Saving coordinates to both files...';
        document.body.appendChild(statusMsg);
        
        // Send to server (saves to both canva.json and coordinates.json)
        fetch('save_coordinates_dual.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(elementPositions)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                statusMsg.textContent = 'Coordinates saved to both canva.json and coordinates.json!';
                statusMsg.classList.add('success');
            } else {
                statusMsg.textContent = 'Error: ' + data.message;
                statusMsg.classList.add('error');
            }
            
            // Remove status message after a delay
            setTimeout(() => {
                statusMsg.remove();
            }, 3000);
        })
        .catch(error => {
            statusMsg.textContent = 'Network error. Coordinates saved locally only.';
            statusMsg.classList.add('warning');
            console.error('Error saving positions to server:', error);
            
            // Remove status message after a delay
            setTimeout(() => {
                statusMsg.remove();
            }, 3000);
        });
    }
    
    // Reload positions from server (canva.json)
    function reloadPositions() {
        // Clear cache
        sessionStorage.removeItem('checkElementPositions');
        localStorage.removeItem('checkElementPositions');
        
        // Create a status message element
        const statusMsg = document.createElement('div');
        statusMsg.className = 'status-message';
        statusMsg.textContent = 'Refreshing display coordinates from canva.json...';
        document.body.appendChild(statusMsg);
        
        // Force reload from server
        elementPositions = loadPositions(true);
        
        if (elementPositions) {
            statusMsg.textContent = 'Display coordinates refreshed from canva.json!';
            statusMsg.classList.add('success');
            
            // Update current positions if a bank is selected
            if (currentBank) {
                updateElementPositions();
            }
        } else {
            statusMsg.textContent = 'Error reloading coordinates, using defaults.';
            statusMsg.classList.add('error');
        }
        
        // Remove status message after a delay
        setTimeout(() => {
            statusMsg.remove();
        }, 3000);
    }
    
    // This function ensures positions are loaded correctly
    function ensurePositionsConsistency() {
        // We'll focus on loading from the server in the loadPositions function
        // For consistency, sync localStorage and sessionStorage
        const sessionPositions = sessionStorage.getItem('checkElementPositions');
        const localPositions = localStorage.getItem('checkElementPositions');
        
        if (sessionPositions && !localPositions) {
            localStorage.setItem('checkElementPositions', sessionPositions);
        }
    }

    // Event Listeners
    bankSelect.addEventListener('change', function() {
        currentBank = this.value;
        if (currentBank) {
            checkImage.src = `${currentBank}.jpeg`;
            
            // Try to load fresh display coordinates from canva.json
            fetch('save_coordinates_dual.php?type=canva')
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Failed to load coordinates from server');
                }
            })
            .then(data => {
                // Update our positions with the server data
                if (data && data[currentBank]) {
                    elementPositions[currentBank] = data[currentBank];
                    
                    // Ensure amountTextLine2 exists
                    if (!elementPositions[currentBank].amountTextLine2) {
                        elementPositions[currentBank].amountTextLine2 = {
                            top: elementPositions[currentBank].amountText.top + 20,
                            left: elementPositions[currentBank].amountText.left
                        };
                    }
                    
                    // Update the cache
                    sessionStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
                    localStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
                }
                updateElementPositions();
            })
            .catch(error => {
                console.warn('Using cached positions:', error.message);
                
                // Fall back to session/localStorage if server fails
                const sessionPositions = sessionStorage.getItem('checkElementPositions');
                if (sessionPositions) {
                    const parsedPositions = JSON.parse(sessionPositions);
                    if (parsedPositions[currentBank]) {
                        elementPositions[currentBank] = parsedPositions[currentBank];
                        
                        // Ensure amountTextLine2 exists
                        if (!elementPositions[currentBank].amountTextLine2) {
                            elementPositions[currentBank].amountTextLine2 = {
                                top: elementPositions[currentBank].amountText.top + 20,
                                left: elementPositions[currentBank].amountText.left
                            };
                        }
                    }
                }
                updateElementPositions();
            });
        } else {
            checkImage.src = '';
        }
    });

    amountInput.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        
        // Update amount text and preview
        const amountText = NumberToFrench.convert(amount);
        amountTextPreview.textContent = amountText;
        
        // Update canvas elements - removed 'DA' as requested
        amountElement.textContent = amount.toFixed(2);
        
        // Split the amount text into two lines if necessary
        const splitText = NumberToFrench.splitAmountText(amountText);
        
        // Update the text elements
        amountTextElement.textContent = splitText.line1;
        
        // Handle line 2 if it exists
        if (splitText.line2) {
            amountTextLine2Element.textContent = splitText.line2;
            amountTextLine2Element.style.opacity = '1'; // Make it visible
            
            // Position the second line below the first line by default
            if (currentBank && elementPositions[currentBank]) {
                // Make the second line appear below the first line
                if (!elementPositions[currentBank].amountTextLine2) {
                    elementPositions[currentBank].amountTextLine2 = {
                        top: elementPositions[currentBank].amountText.top + 20,
                        left: elementPositions[currentBank].amountText.left
                    };
                    updateElementPositions();
                }
            }
        } else {
            amountTextLine2Element.textContent = '';
            amountTextLine2Element.style.opacity = '0'; // Hide it
        }
    });

    datePicker.addEventListener('change', function() {
        dateElement.textContent = this.value;
    });

    placeSelect.addEventListener('change', function() {
        placeElement.textContent = this.value;
    });

    payToInput.addEventListener('input', function() {
        payToElement.textContent = this.value;
    });

    // Movement controls
    moveUp.addEventListener('click', function() {
        moveElement('up');
    });
    
    moveDown.addEventListener('click', function() {
        moveElement('down');
    });
    
    moveLeft.addEventListener('click', function() {
        moveElement('left');
    });
    
    moveRight.addEventListener('click', function() {
        moveElement('right');
    });

    // Add keyboard support for moving elements
    document.addEventListener('keydown', function(e) {
        if (!selectedElement) return;
        
        switch (e.key) {
            case 'ArrowUp':
                e.preventDefault();
                moveElement('up');
                break;
            case 'ArrowDown':
                e.preventDefault();
                moveElement('down');
                break;
            case 'ArrowLeft':
                e.preventDefault();
                moveElement('left');
                break;
            case 'ArrowRight':
                e.preventDefault();
                moveElement('right');
                break;
        }
    });

    // Reload button
    reloadPositionsBtn.addEventListener('click', reloadPositions);

    // Save button
    savePositionsBtn.addEventListener('click', savePositions);

    // Print button
    printBtn.addEventListener('click', function() {
        if (!currentBank) {
            alert("Please select a bank before printing");
            return;
        }
        
        // Save current positions to session storage before printing
        sessionStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
        
        // Get current values
        const amount = parseFloat(amountInput.value) || 0;
        
        // Get the amount text directly from the elements - this ensures we use what's visible
        const amountText = amountTextElement.textContent || '';
        const amountTextLine2 = amountTextLine2Element.textContent || '';
        const date = dateElement.textContent || '';
        const place = placeElement.textContent || '';
        const payTo = payToElement.textContent || '';
        
        // Construct URL for print.php
        const printUrl = `print.php?bank=${currentBank}&amount=${amount}&amountText=${encodeURIComponent(amountText)}&amountTextLine2=${encodeURIComponent(amountTextLine2)}&date=${encodeURIComponent(date)}&place=${encodeURIComponent(place)}&payTo=${encodeURIComponent(payTo)}`;
        
        // Open in new window
        window.open(printUrl, '_blank', 'width=850,height=500');
    });

    // Initialize
    ensurePositionsConsistency();
    initializeCanvasElements();
    
    // Set default date
    const today = new Date();
    const formattedDate = today.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).replace(/\//g, '/');
    
    datePicker.value = formattedDate;
    dateElement.textContent = formattedDate;
    
    // Automatically select BNA bank if it's selected in the dropdown
    if (bankSelect.value === 'bna') {
        currentBank = 'bna';
        checkImage.src = 'bna.jpeg';
        
        // Load display coordinates from canva.json and update positions
        fetch('save_coordinates_dual.php?type=canva')
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Failed to load coordinates from server');
            }
        })
        .then(data => {
            // Update our positions with the server data
            if (data && data[currentBank]) {
                elementPositions[currentBank] = data[currentBank];
                
                // Ensure amountTextLine2 exists
                if (!elementPositions[currentBank].amountTextLine2) {
                    elementPositions[currentBank].amountTextLine2 = {
                        top: elementPositions[currentBank].amountText.top + 20,
                        left: elementPositions[currentBank].amountText.left
                    };
                }
                
                // Update the cache
                sessionStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
                localStorage.setItem('checkElementPositions', JSON.stringify(elementPositions));
            }
            updateElementPositions();
        })
        .catch(error => {
            console.warn('Using cached positions:', error.message);
            updateElementPositions();
        });
    }
});
