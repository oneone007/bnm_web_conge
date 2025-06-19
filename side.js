function navigateTo(pageId) {
    // Generate unique ID for each tab instance to allow duplicates
    const timestamp = Date.now();
    const tabId = `tab-${pageId}-${timestamp}`;
    const contentId = `content-${pageId}-${timestamp}`;
  
    // Create new tab button
    const tab = document.createElement('button');
    tab.id = tabId;
    tab.className = 'tab-button';
    tab.textContent = pageId;
    tab.onclick = () => showTab(pageId, timestamp);
    tab.draggable = true; // Enable drag and drop
  
    // Add refresh button (left side)
    const refreshBtn = document.createElement('span');
    refreshBtn.innerHTML = '&#x21bb;'; // Refresh symbol
    refreshBtn.className = 'refresh-btn';
        refreshBtn.style.cssText = `
    
        color: #4CAF50;
    `;
    refreshBtn.onclick = (e) => {
        e.stopPropagation();
        refreshTab(pageId, timestamp);
    };
    tab.insertBefore(refreshBtn, tab.firstChild);
  
    // Add close button (right side)
    const closeBtn = document.createElement('span');
    closeBtn.textContent = '×';
    closeBtn.className = 'close-btn';
        closeBtn.style.cssText = `
    
        color:rgb(245, 16, 0);
    `;
    closeBtn.onclick = (e) => {
        e.stopPropagation();
        document.getElementById(tabId).remove();
        document.getElementById(contentId).remove();
        
        // Switch to another tab if available
        const remainingTabs = document.querySelectorAll('.tab-button');
        if (remainingTabs.length > 0) {
            const activeTab = document.querySelector('.tab-button.active');
            if (!activeTab && remainingTabs[0]) {
                const idParts = remainingTabs[0].id.split('-');
                showTab(idParts[1], idParts[2]);
            }
        }
    };
    tab.appendChild(closeBtn);
    
    // Add drag and drop event listeners
    tab.addEventListener('dragstart', handleDragStart);
    tab.addEventListener('dragend', handleDragEnd);
    tab.addEventListener('dragover', handleDragOver);
    tab.addEventListener('drop', handleDrop);
    
    document.getElementById('tabs').appendChild(tab);
  
    // Create content container with iframe
    const content = document.createElement('div');
    content.id = contentId;
    content.className = 'tab-pane';
    content.innerHTML = `<iframe src="${pageId}" width="100%" height="600px" frameborder="0"></iframe>`;
    document.getElementById('tab-contents').appendChild(content);
  
    showTab(pageId, timestamp);
}

function refreshTab(pageId, timestamp) {
    const contentId = `content-${pageId}-${timestamp}`;
    const content = document.getElementById(contentId);
    if (content) {
        const iframe = content.querySelector('iframe');
        if (iframe) {
            // Refresh by reassigning the src attribute
            iframe.src = iframe.src;
        }
    }
}

function showTab(pageId, timestamp) {
    // Deactivate all tabs and contents
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(div => div.style.display = 'none');
  
    // Activate the selected tab
    const tab = document.getElementById(`tab-${pageId}-${timestamp}`);
    const content = document.getElementById(`content-${pageId}-${timestamp}`);
    
    if (tab) tab.classList.add('active');
    if (content) content.style.display = 'block';
}
// Drag and Drop Functionality
let draggedTab = null;

function handleDragStart(e) {
    draggedTab = this;
    this.style.opacity = '0.4';
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.style.opacity = '1';
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    
    if (this !== draggedTab) {
        this.classList.add('drag-over');
    }
    return false;
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (this !== draggedTab) {
        const tabsContainer = document.getElementById('tabs');
        const allTabs = Array.from(tabsContainer.children);
        const fromIndex = allTabs.indexOf(draggedTab);
        const toIndex = allTabs.indexOf(this);
        
        if (fromIndex < toIndex) {
            this.parentNode.insertBefore(draggedTab, this.nextSibling);
        } else {
            this.parentNode.insertBefore(draggedTab, this);
        }
    }
    
    this.classList.remove('drag-over');
    return false;
}



    // Toggle submenu function
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const chevron = submenu.previousElementSibling.querySelector('.chevron');
            
            submenu.classList.toggle('show');
            chevron.classList.toggle('rotate');
            
            // Close other submenus when opening a new one
            if (submenu.classList.contains('show')) {
                document.querySelectorAll('.submenu').forEach(menu => {
                    if (menu.id !== id && menu.classList.contains('show')) {
                        menu.classList.remove('show');
                        menu.previousElementSibling.querySelector('.chevron').classList.remove('rotate');
                    }
                });
            }
        }

// Initialize sidebar
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const content = document.querySelector('.content');

// Toggle button handler
sidebarToggle?.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-hidden');
    content.classList.toggle('content-full');
    
    // Update toggle button position
    requestAnimationFrame(() => {
        sidebarToggle.style.left = sidebar.classList.contains('sidebar-hidden') ? '10px' : '260px';
    });
});
  // Theme toggle functionality
const themeToggle = document.getElementById('themeToggle');
const html = document.documentElement;

// Check for saved theme preference or default to light
if (localStorage.getItem('theme') === 'dark') {
    html.classList.add('dark');
    themeToggle.checked = true;
} else {
    // Default to light theme if no preference is set
    html.classList.remove('dark');
    themeToggle.checked = false;
    localStorage.setItem('theme', 'light');
}

themeToggle.addEventListener('change', function() {
    if (this.checked) {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
});

// Theme switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    
    if (themeToggle) {
        // Set initial state - default to light
        const isDark = localStorage.getItem('theme') === 'dark';
        themeToggle.checked = isDark;
        document.body.classList.toggle('dark-mode', isDark);

        // Listen for changes
        themeToggle.addEventListener('change', () => {
            const isDark = themeToggle.checked;
            document.body.classList.toggle('dark-mode', isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');

            // Broadcast to all frames
            window.dispatchEvent(new CustomEvent('themeChanged', {
                detail: { isDark }
            }));

            // Update all iframes
            document.querySelectorAll('iframe').forEach(iframe => {
                try {
                    iframe.contentWindow.dispatchEvent(new CustomEvent('themeChanged', {
                        detail: { isDark }
                    }));
                } catch (e) {
                    console.log('Could not update iframe theme');
                }
            });
        });

        // Listen for theme changes from other sources
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                const isDark = e.newValue === 'dark';
                themeToggle.checked = isDark;
                document.body.classList.toggle('dark-mode', isDark);
            }
        });
    }
});

    function logout() {
            window.location.href = 'db/logout.php';
        }



        // Sidebar Mode Toggle Logic
        const sidebarModeManual = document.getElementById('sidebarModeManual');
        const sidebarModeAuto = document.getElementById('sidebarModeAuto');

        function setSidebarMode(mode) {
            if (mode === 'auto') {
                sidebar.classList.add('sidebar-auto-hide');
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('open');
                sidebar.style.transform = 'translateX(-100%)';
                document.addEventListener('mousemove', handleSidebarReveal);
                // Add mouseleave event to sidebar to hide it when mouse leaves
                sidebar.addEventListener('mouseleave', handleSidebarAutoHide);
            } else {
                sidebar.classList.remove('sidebar-auto-hide');
                sidebarToggle.style.display = '';
                sidebar.style.transform = '';
                document.removeEventListener('mousemove', handleSidebarReveal);
                sidebar.removeEventListener('mouseleave', handleSidebarAutoHide);
            }
        }

        function handleSidebarReveal(e) {
            if (e.clientX < 30) {
                sidebar.style.transform = 'translateX(0)';
            }
        }

        function handleSidebarAutoHide(e) {
            // Only hide if in auto mode and mouse is not over sidebar
            if (sidebar.classList.contains('sidebar-auto-hide')) {
                sidebar.style.transform = 'translateX(-100%)';
            }
        }

        sidebarModeManual.addEventListener('change', function() {
            if (this.checked) setSidebarMode('manual');
        });
        sidebarModeAuto.addEventListener('change', function() {
            if (this.checked) setSidebarMode('auto');
        });

        // Set initial mode
        if (sidebarModeAuto.checked) {
            setSidebarMode('auto');
        } else {
            setSidebarMode('manual');
        }