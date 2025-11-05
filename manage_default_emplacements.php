    <?php
    session_start();
    // Session validation can be added here if needed
    $page_identifier = 'manage_default_emplacements';

// Include permission system - this will handle both login check and role permissions
require_once 'check_permission.php';
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Emplacements par Défaut</title>
    <script src="api_config.js"></script>
    <script src="theme.js"></script>
    <style>
        :root {
            --bg-primary: #F8FAFC;
            --bg-secondary: #FFFFFF;
            --bg-tertiary: #F1F5F9;
            --bg-admin: #1E293B;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --border-admin: #334155;
            --accent-primary: #2563EB;
            --accent-secondary: #1D4ED8;
            --success: #059669;
            --error: #DC2626;
            --warning: #D97706;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body.dark-mode {
            --bg-primary: #0F172A;
            --bg-secondary: #1E293B;
            --bg-tertiary: #334155;
            --bg-admin: #020617;
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted: #94A3B8;
            --border-color: #334155;
            --border-admin: #475569;
            --accent-primary: #3B82F6;
            --accent-secondary: #2563EB;
            --success: #10B981;
            --error: #EF4444;
            --warning: #F59E0B;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 16px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--bg-secondary);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .admin-header {
            background: var(--bg-admin);
            color: white;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-admin);
        }

        .admin-header h1 {
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-header .icon {
            font-size: 1.2em;
        }

        .admin-header p {
            font-size: 0.875em;
            opacity: 0.8;
            font-weight: 400;
        }

        .admin-content {
            padding: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-tertiary);
            padding: 16px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            background: var(--bg-secondary);
            box-shadow: var(--shadow-sm);
        }

        .stat-number {
            font-size: 1.75em;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .config-panel {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
        }

        .panel-header {
            background: var(--bg-tertiary);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .panel-header h3 {
            color: var(--text-primary);
            font-size: 1em;
            font-weight: 600;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-header .panel-icon {
            font-size: 1em;
        }

        .panel-header p {
            color: var(--text-muted);
            font-size: 0.75em;
            margin: 0;
        }

        .search-container {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-tertiary);
        }

        .search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.875em;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .items-container {
            max-height: 500px;
            overflow-y: auto;
        }

        .store-group {
            margin-bottom: 20px;
        }

        .store-header {
            background: var(--accent-primary);
            color: white;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.875em;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .store-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7em;
            font-weight: 600;
        }

        .item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .item-row:hover {
            background: var(--bg-tertiary);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.875em;
            margin-bottom: 2px;
        }

        .item-id {
            color: var(--text-muted);
            font-size: 0.75em;
            font-family: 'Monaco', 'Courier New', monospace;
        }

        .item-status {
            color: var(--success);
            font-size: 0.75em;
            font-weight: 600;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75em;
            font-weight: 600;
            transition: all 0.2s ease;
            letter-spacing: 0.025em;
        }

        .btn-add {
            background: var(--success);
            color: white;
        }

        .btn-add:hover:not(:disabled) {
            background: #047857;
            transform: translateY(-1px);
        }

        .btn-remove {
            background: var(--error);
            color: white;
        }

        .btn-remove:hover {
            background: #B91C1C;
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-weight: 500;
            font-size: 0.875em;
        }

        .alert-success {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.1);
            color: var(--error);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.875em;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-secondary);
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-content {
            background: var(--bg-secondary);
            padding: 32px 48px;
            border-radius: 8px;
            text-align: center;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--bg-tertiary);
            border-top: 3px solid var(--accent-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        .loading-text {
            font-size: 1em;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .loading-subtext {
            color: var(--text-secondary);
            font-size: 0.8em;
        }

        .success-icon, .error-icon {
            font-size: 3em;
            margin-bottom: 16px;
        }

        .success-icon {
            color: var(--success);
        }

        .error-icon {
            color: var(--error);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .config-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-content {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .admin-header {
                padding: 16px 20px;
            }

            .admin-header h1 {
                font-size: 1.25em;
            }
        }
    </style>
</head>
<body>


    <div class="admin-container">
        <div class="admin-header">
            <h1><span class="icon">⚙️</span> Configuration des Emplacements</h1>
            <p>Paramètres système - Gestion des emplacements par défaut</p>
        </div>

        <!-- Alert container for messages -->
        <div id="alertContainer" style="padding: 16px 24px 0; display: none;">
            <div id="alertMessage" class="alert"></div>
        </div>

        <div class="admin-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="statTotal">0</div>
                    <div class="stat-label">Total Emplacements</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="statDefaults">0</div>
                    <div class="stat-label">Emplacements Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="statAvailable">0</div>
                    <div class="stat-label">Disponibles</div>
                </div>
            </div>

            <div class="config-grid">
                <!-- Left Panel: All Available Locators -->
                <div class="config-panel">
                    <div class="panel-header">
                        <h3><span class="panel-icon">📍</span> Catalogue Complet</h3>
                        <p>Emplacements disponibles dans Oracle</p>
                        <div class="search-container">
                            <input type="text" id="searchAll" class="search-input" placeholder="Rechercher un emplacement ou magasin...">
                        </div>
                    </div>
                    <div class="items-container" id="allLocatorsList">
                        <div class="empty-state">
                            <div class="empty-state-icon">⏳</div>
                            <p>Chargement du catalogue...</p>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Default Locators -->
                <div class="config-panel">
                    <div class="panel-header">
                        <h3><span class="panel-icon">⭐</span> Configuration Active</h3>
                        <p>Emplacements utilisés dans les requêtes SQL</p>
                        <div class="search-container">
                            <input type="text" id="searchDefault" class="search-input" placeholder="Rechercher dans la configuration...">
                        </div>
                    </div>
                    <div class="items-container" id="defaultLocatorsList">
                        <div class="empty-state">
                            <div class="empty-state-icon">⏳</div>
                            <p>Chargement de la configuration...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div id="loadingSpinner" class="loading-spinner"></div>
            <div id="loadingIcon" style="display: none;"></div>
            <div id="loadingText" class="loading-text">Chargement...</div>
            <div id="loadingSubtext" class="loading-subtext">Veuillez patienter</div>
        </div>
    </div>

    <script>
        // Get API URL based on hostname
        function getApiUrl(endpoint) {
            const hostname = window.location.hostname;
            let baseUrl;
            if (hostname.includes('ddns.net')) {
                baseUrl = `http://${hostname}:5000`;
            } else if (hostname === 'localhost' || hostname.startsWith('192.168.')) {
                baseUrl = 'http://192.168.1.94:5000';
            } else {
                baseUrl = 'http://localhost:5000';
            }
            return baseUrl + endpoint;
        }

        // Show loading overlay
        function showLoading(text = 'Traitement en cours...', subtext = 'Veuillez patienter') {
            const overlay = document.getElementById('loadingOverlay');
            const spinner = document.getElementById('loadingSpinner');
            const icon = document.getElementById('loadingIcon');
            const textEl = document.getElementById('loadingText');
            const subtextEl = document.getElementById('loadingSubtext');
            
            spinner.style.display = 'block';
            icon.style.display = 'none';
            textEl.textContent = text;
            subtextEl.textContent = subtext;
            overlay.classList.add('active');
        }

        // Show success
        function showSuccess(text, subtext, callback) {
            const spinner = document.getElementById('loadingSpinner');
            const icon = document.getElementById('loadingIcon');
            const textEl = document.getElementById('loadingText');
            const subtextEl = document.getElementById('loadingSubtext');
            
            spinner.style.display = 'none';
            icon.style.display = 'block';
            icon.className = 'success-icon';
            icon.textContent = '✅';
            textEl.textContent = text;
            subtextEl.textContent = subtext;
            
            setTimeout(() => {
                hideLoading();
                if (callback) callback();
            }, 2000);
        }

        // Show error
        function showError(text, subtext, callback) {
            const spinner = document.getElementById('loadingSpinner');
            const icon = document.getElementById('loadingIcon');
            const textEl = document.getElementById('loadingText');
            const subtextEl = document.getElementById('loadingSubtext');
            
            spinner.style.display = 'none';
            icon.style.display = 'block';
            icon.className = 'error-icon';
            icon.textContent = '❌';
            textEl.textContent = text;
            subtextEl.textContent = subtext;
            
            setTimeout(() => {
                hideLoading();
                if (callback) callback();
            }, 3000);
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        // Global data storage
        let allLocatorsData = [];
        let defaultEmplacementsData = [];

        // Helper to get API base URL
        function getApiBaseUrl() {
            const hostname = window.location.hostname;
            if (hostname === 'localhost' || hostname === '127.0.0.1' || hostname.startsWith('192.168.')) {
                return 'http://192.168.1.94:5000';
            }
            if (hostname.includes('ddns.net')) {
                return `http://${hostname}:5000`;
            }
            return 'http://bnm.ddns.net:5000';
        }

        // HTML escape helper
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // Update statistics
        function updateStats(allLocators, defaults) {
            let totalEmplacements = 0;
            allLocators.forEach(group => {
                totalEmplacements += group.emplacements.length;
            });
            
            document.getElementById('statTotal').textContent = totalEmplacements;
            document.getElementById('statDefaults').textContent = defaults.length;
            document.getElementById('statAvailable').textContent = totalEmplacements - defaults.length;
        }

        // Fetch all available locators from Flask API
        async function fetchAllLocators() {
            try {
                const apiUrl = getApiBaseUrl();
                const response = await fetch(`${apiUrl}/all_locators`);
                if (!response.ok) throw new Error('Failed to fetch locators');
                const data = await response.json();
                allLocatorsData = data;
                return data;
            } catch (error) {
                console.error('Error fetching locators:', error);
                return [];
            }
        }

        // Fetch default emplacements from Flask API
        async function fetchDefaultEmplacements() {
            try {
                const apiUrl = getApiBaseUrl();
                const response = await fetch(`${apiUrl}/default_emplacements`);
                if (!response.ok) throw new Error('Failed to fetch defaults');
                const data = await response.json();
                defaultEmplacementsData = data;
                return data;
            } catch (error) {
                console.error('Error fetching defaults:', error);
                return [];
            }
        }

        // Render all available locators
        function renderAllLocators(locators, defaults) {
            const container = document.getElementById('allLocatorsList');
            
            if (locators.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <p>Aucun emplacement disponible</p>
                    </div>
                `;
                return;
            }

            // Get default IDs for checking
            const defaultIds = new Set(defaults.map(d => d.emplacement_id));

            let html = '';
            locators.forEach(group => {
                html += `
                    <div class="store-group" data-magasin="${escapeHtml(group.magasin)}">
                        <div class="store-header">
                            <span>🏪 ${escapeHtml(group.magasin)}</span>
                            <span class="store-badge">${group.emplacements.length}</span>
                        </div>
                `;
                
                group.emplacements.forEach(loc => {
                    const isDefault = defaultIds.has(loc.EMPLACEMENT_ID);
                    html += `
                        <div class="item-row" data-name="${escapeHtml(loc.EMPLACEMENT)}">
                            <div class="item-info">
                                <div class="item-name">
                                    ${escapeHtml(loc.EMPLACEMENT)}
                                    ${isDefault ? '<span class="item-status">✓ Actif</span>' : ''}
                                </div>
                                <div class="item-id">ID: ${escapeHtml(loc.EMPLACEMENT_ID)}</div>
                            </div>
                            <button 
                                class="btn btn-add" 
                                ${isDefault ? 'disabled' : ''}
                                onclick="addEmplacement('${escapeHtml(loc.EMPLACEMENT)}', '${escapeHtml(loc.EMPLACEMENT_ID)}', '${escapeHtml(group.magasin)}', '${escapeHtml(group.magasin_id)}')"
                            >
                                ${isDefault ? '✓ Activé' : '+ Activer'}
                            </button>
                        </div>
                    `;
                });
                
                html += '</div>';
            });
            
            container.innerHTML = html;
        }

        // Render default emplacements
        function renderDefaultEmplacements(defaults) {
            const container = document.getElementById('defaultLocatorsList');
            
            if (defaults.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">⚙️</div>
                        <p>Aucune configuration active</p>
                    </div>
                `;
                return;
            }

            // Group by magasin
            const grouped = {};
            defaults.forEach(emp => {
                if (!grouped[emp.magasin]) {
                    grouped[emp.magasin] = [];
                }
                grouped[emp.magasin].push(emp);
            });

            let html = '';
            Object.keys(grouped).sort().forEach(magasin => {
                const emplacements = grouped[magasin];
                html += `
                    <div class="store-group" data-magasin="${escapeHtml(magasin)}">
                        <div class="store-header">
                            <span>🏪 ${escapeHtml(magasin)}</span>
                            <span class="store-badge">${emplacements.length} actifs</span>
                        </div>
                `;
                
                emplacements.forEach(emp => {
                    html += `
                        <div class="item-row" data-name="${escapeHtml(emp.emplacement)}">
                            <div class="item-info">
                                <div class="item-name">${escapeHtml(emp.emplacement)}</div>
                                <div class="item-id">ID: ${escapeHtml(emp.emplacement_id)}</div>
                            </div>
                            <button 
                                class="btn btn-remove" 
                                onclick="removeEmplacement(${emp.default_locators_id})"
                            >
                                ✕ Désactiver
                            </button>
                        </div>
                    `;
                });
                
                html += '</div>';
            });
            
            container.innerHTML = html;
        }

        // Add emplacement
        async function addEmplacement(emplacement, emplacementId, magasin, magasinId) {
            showLoading('Activation en cours...', 'Configuration du système');
            
            try {
                const apiUrl = getApiBaseUrl();
                const response = await fetch(`${apiUrl}/add_emplacement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        emplacement: emplacement,
                        emplacement_id: emplacementId,
                        magasin: magasin,
                        magasin_id: magasinId
                    })
                });

                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Reload configuration
                    showLoading('Rechargement de la configuration...', 'Application des paramètres');
                    const reloadData = await reloadFlaskConfig();
                    
                    // Refresh data
                    await loadAllData();
                    
                    if (reloadData && reloadData.success) {
                        showSuccess(
                            '✓ Configuration mise à jour',
                            `Emplacement activé! ${reloadData.emplacements_loaded} emplacements, ${reloadData.magasins_loaded} magasins`
                        );
                    } else {
                        showSuccess('✓ Configuration mise à jour', 'Emplacement activé avec succès');
                    }
                } else {
                    showError('Erreur de configuration', result.message || 'Erreur lors de l\'activation');
                }
            } catch (error) {
                console.error('Error adding emplacement:', error);
                showError('Erreur système', 'Erreur lors de l\'activation de l\'emplacement');
            }
        }

        // Remove emplacement
        async function removeEmplacement(id) {
            if (!confirm('Confirmer la désactivation de cet emplacement?')) {
                return;
            }
            
            showLoading('Désactivation en cours...', 'Mise à jour de la configuration');
            
            try {
                const apiUrl = getApiBaseUrl();
                const response = await fetch(`${apiUrl}/remove_emplacement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                });

                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Reload configuration
                    showLoading('Rechargement de la configuration...', 'Application des paramètres');
                    const reloadData = await reloadFlaskConfig();
                    
                    // Refresh data
                    await loadAllData();
                    
                    if (reloadData && reloadData.success) {
                        showSuccess(
                            '✓ Configuration mise à jour',
                            `Emplacement désactivé! ${reloadData.emplacements_loaded} emplacements, ${reloadData.magasins_loaded} magasins`
                        );
                    } else {
                        showSuccess('✓ Configuration mise à jour', 'Emplacement désactivé avec succès');
                    }
                } else {
                    showError('Erreur de configuration', result.message || 'Erreur lors de la désactivation');
                }
            } catch (error) {
                console.error('Error removing emplacement:', error);
                showError('Erreur système', 'Erreur lors de la désactivation de l\'emplacement');
            }
        }

        // Reload Flask configuration
        async function reloadFlaskConfig() {
            try {
                const apiUrl = getApiBaseUrl();
                const response = await fetch(`${apiUrl}/reload_config`, {
                    method: 'POST'
                });
                
                if (!response.ok) {
                    console.warn('Failed to reload Flask config');
                    return null;
                }
                
                return await response.json();
            } catch (error) {
                console.error('Error reloading config:', error);
                return null;
            }
        }

        // Load all data and render
        async function loadAllData() {
            const [allLocators, defaults] = await Promise.all([
                fetchAllLocators(),
                fetchDefaultEmplacements()
            ]);
            
            renderAllLocators(allLocators, defaults);
            renderDefaultEmplacements(defaults);
            updateStats(allLocators, defaults);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllData();
        });

        // Search functionality for all locators
        document.getElementById('searchAll').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const groups = document.querySelectorAll('#allLocatorsList .store-group');
            
            groups.forEach(group => {
                const magasin = group.getAttribute('data-magasin').toLowerCase();
                const items = group.querySelectorAll('.item-row');
                let hasVisibleItems = false;
                
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(searchTerm) || magasin.includes(searchTerm)) {
                        item.style.display = 'flex';
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Show/hide the entire group based on whether it has visible items
                group.style.display = hasVisibleItems ? 'block' : 'none';
            });
        });

        // Search functionality for default locators
        document.getElementById('searchDefault').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const groups = document.querySelectorAll('#defaultLocatorsList .store-group');
            
            groups.forEach(group => {
                const magasin = group.getAttribute('data-magasin').toLowerCase();
                const items = group.querySelectorAll('.item-row');
                let hasVisibleItems = false;
                
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(searchTerm) || magasin.includes(searchTerm)) {
                        item.style.display = 'flex';
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                group.style.display = hasVisibleItems ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
