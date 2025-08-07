<?php
session_start();

// Check if the user is logged in (optional - remove if you want public access)
// if (!isset($_SESSION['user_id'])) {
//     header("Location: BNM");
//     exit();
// }

// Get map data statistics
$jsonFile = 'dz.json';
$mapData = null;
$totalWilayas = 0;
$mapStats = [];

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $mapData = json_decode($jsonContent, true);
    
    if ($mapData && isset($mapData['features'])) {
        $totalWilayas = count($mapData['features']);
        
        // Extract wilaya names for server-side processing
        foreach ($mapData['features'] as $feature) {
            if (isset($feature['properties']['name'])) {
                $mapStats[] = [
                    'id' => $feature['properties']['id'] ?? '',
                    'name' => $feature['properties']['name'],
                    'source' => $feature['properties']['source'] ?? ''
                ];
            }
        }
    }
}

// Sort wilayas alphabetically
usort($mapStats, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algeria Interactive Map - BNM System</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme Script -->
    <script src="theme.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        body.dark {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .dark .header {
            background: rgba(52, 73, 94, 0.95);
            color: white;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .dark .header h1 {
            color: white;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .dark .header p {
            color: #bdc3c7;
        }

        .theme-toggle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .theme-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .controls {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            margin: 2rem 0;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            transition: all 0.3s ease;
        }

        .dark .controls {
            background: rgba(52, 73, 94, 0.95);
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .date-inputs {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .date-inputs input {
            padding: 0.7rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .date-inputs input:focus {
            outline: none;
            border-color: #667eea;
        }

        .dark .date-inputs input {
            background: #34495e;
            border-color: #5d6d7e;
            color: white;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            padding: 0.7rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: #667eea;
        }

        .dark .search-box {
            background: #34495e;
            border-color: #5d6d7e;
            color: white;
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            background: white;
            margin-bottom: 2rem;
            position: relative;
        }

        #map {
            height: 70vh;
            min-height: 500px;
            width: 100%;
            background: #f8f9fa !important;
        }

        .info-panels {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .dark .info-panel {
            background: rgba(52, 73, 94, 0.95);
        }

        .info-panel h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .dark .info-panel h3 {
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h4 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            opacity: 0.9;
        }

        .wilayas-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .wilaya-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem;
            margin-bottom: 0.5rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .wilaya-item:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateX(5px);
        }

        .dark .wilaya-item {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .dark .wilaya-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .wilaya-id {
            background: #667eea;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .legend {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-width: 200px;
            transition: all 0.3s ease;
        }

        .dark .legend {
            background: rgba(52, 73, 94, 0.95);
            color: white;
        }

        .legend h4 {
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .dark .legend h4 {
            color: white;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.3rem;
            font-size: 0.8rem;
        }

        .legend-color {
            width: 20px;
            height: 15px;
            border-radius: 3px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
        }

        .dark .back-link {
            color: #74b9ff;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: unset;
            }
            
            .info-panels {
                grid-template-columns: 1fr;
            }
            
            .legend {
                position: relative;
                top: unset;
                right: unset;
                margin: 1rem 0;
            }

            .main-container {
                padding: 0 1rem;
            }
        }

        /* Custom popup styles */
        .leaflet-popup-content-wrapper {
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .popup-content {
            text-align: center;
            padding: 0.5rem;
        }

        .popup-content h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .popup-content p {
            color: #7f8c8d;
            margin: 0.2rem 0;
        }

        .qty-bar-container {
            margin: 0.5rem 0;
            text-align: left;
        }

        .qty-bar {
            background: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            position: relative;
        }

        .qty-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
            position: relative;
        }

        .qty-bar-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        /* QTY Labels on map */
        .qty-label {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Wilaya Name Labels */
        .wilaya-name-label {
            background: rgba(44, 62, 80, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Custom scrollbar */
        .wilayas-list::-webkit-scrollbar {
            width: 6px;
        }

        .wilayas-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .wilayas-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .wilayas-list::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }

        /* Zone Statistics Styles */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            background: rgba(102, 126, 234, 0.1);
            padding: 0.8rem;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .summary-item:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .dark .summary-item {
            background: rgba(255, 255, 255, 0.1);
        }

        .dark .summary-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .summary-label {
            display: block;
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 0.3rem;
        }

        .dark .summary-label {
            color: #bdc3c7;
        }

        .summary-value {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .dark .summary-value {
            color: white;
        }

        .zones-data h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.2);
            padding-bottom: 0.5rem;
        }

        .dark .zones-data h4 {
            color: white;
            border-bottom-color: rgba(255, 255, 255, 0.2);
        }

        .zones-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .zone-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 8px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .zone-item:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .dark .zone-item {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-left-color: #74b9ff;
        }

        .dark .zone-item:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            box-shadow: 0 2px 8px rgba(116, 185, 255, 0.3);
        }

        .zone-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .dark .zone-name {
            color: white;
        }

        .zone-qty {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            min-width: 60px;
            text-align: center;
        }

        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 2rem;
            background: rgba(127, 140, 141, 0.1);
            border-radius: 8px;
            border: 2px dashed rgba(127, 140, 141, 0.3);
        }

        .dark .no-data {
            color: #bdc3c7;
            background: rgba(189, 195, 199, 0.1);
            border-color: rgba(189, 195, 199, 0.3);
        }

        /* Custom scrollbar for zones list */
        .zones-list::-webkit-scrollbar {
            width: 6px;
        }

        .zones-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .zones-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .zones-list::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1><i class="fas fa-map-marked-alt"></i> Algeria Interactive Map</h1>
                <p>Explore the <?php echo $totalWilayas; ?> Wilayas (Provinces) of Algeria</p>
            </div>
            <button class="theme-toggle" onclick="toggleTheme()">
                <i class="fas fa-moon"></i> Dark Mode
            </button>
        </div>
    </div>

    <div class="main-container">
        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to System
        </a>

        <div class="controls">
            <input type="text" class="search-box" id="productSearch" placeholder="Search for a product..." />
            <div class="date-inputs">
                <input type="date" id="startDate" />
                <input type="date" id="endDate" />
            </div>
            <button class="btn" onclick="fetchZoneData()">
                <i class="fas fa-search"></i> Load Data
            </button>
            <button class="btn" onclick="resetMap()">
                <i class="fas fa-home"></i> Reset View
            </button>
            <button class="btn" onclick="downloadMapData()">
                <i class="fas fa-download"></i> Download Data
            </button>
            <button class="btn" onclick="fullscreenMap()">
                <i class="fas fa-expand"></i> Fullscreen
            </button>
        </div>

        <div class="map-container">
            <div id="map"></div>
            <div class="legend">
                <h4><i class="fas fa-info-circle"></i> Legend</h4>
                <div class="legend-item">
                    <div class="legend-color" style="background: #e8f5e8;"></div>
                    <span>Low QTY</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #90EE90;"></div>
                    <span>Medium QTY</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #32CD32;"></div>
                    <span>High QTY</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #228B22;"></div>
                    <span>Very High QTY</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f39c12;"></div>
                    <span>Selected</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #95a5a6;"></div>
                    <span>No Data</span>
                </div>
            </div>
        </div>

        <div class="info-panels">
            <div class="info-panel">
                <h3><i class="fas fa-chart-bar"></i> Map Statistics</h3>
                <div class="stats-summary">
                    <div class="summary-item">
                        <span class="summary-label">Total QTY:</span>
                        <span class="summary-value" id="totalQty">0</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Zones with Data:</span>
                        <span class="summary-value" id="zonesWithData">0</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Selected:</span>
                        <span class="summary-value" id="selectedWilaya">None</span>
                    </div>
                </div>
                <div class="zones-data" id="zonesDataList">
                    <h4><i class="fas fa-database"></i> Zone Data</h4>
                    <div class="zones-list" id="zonesList">
                        <p class="no-data">No data loaded. Click "Load Data" to fetch zone information.</p>
                    </div>
                </div>
            </div>

            <div class="info-panel">
                <h3><i class="fas fa-list"></i> All Wilayas</h3>
                <div class="wilayas-list">
                    <?php foreach ($mapStats as $wilaya): ?>
                    <div class="wilaya-item" onclick="selectWilaya('<?php echo htmlspecialchars($wilaya['name']); ?>')">
                        <span><?php echo htmlspecialchars($wilaya['name']); ?></span>
                        <span class="wilaya-id"><?php echo htmlspecialchars($wilaya['id']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize the map
        let map = L.map('map').setView([28.0339, 1.6596], 6);
        let algeriaLayer;
        let currentStyle = 'default';
        let selectedWilaya = null;
        let wilayaLayers = new Map();
        let zoneData = new Map(); // Store QTY data by zone
        let maxQty = 0; // For color scaling
        let qtyLabels = new Map(); // Store QTY labels for each wilaya
        let wilayaNameLabels = new Map(); // Store wilaya name labels

        // Zone name mapping for special cases
        const zoneNameMapping = {
            'CNE': 'Constantine',
            'JIJEL': 'Jijel',
            'MILA': 'Mila'
        };

        // Combined zones that should have the same color
        const combinedZones = [
            ['Jijel', 'Mila'] // These will be colored the same
        ];

        // No base map tiles - show only administrative boundaries
        // Set a light background for the map
        map.getPane('mapPane').style.background = '#f8f9fa';

        // Function to get color based on QTY
        function getColorByQty(qty) {
            if (!qty || qty === 0) return '#95a5a6'; // Gray for no data
            
            const ratio = qty / maxQty;
            
            if (ratio <= 0.25) return '#e8f5e8'; // Light green
            if (ratio <= 0.5) return '#90EE90';  // Light green
            if (ratio <= 0.75) return '#32CD32'; // Lime green
            return '#228B22'; // Forest green
        }

        // Function to normalize zone names
        function normalizeZoneName(zoneName) {
            // Handle special mappings
            for (const [key, value] of Object.entries(zoneNameMapping)) {
                if (zoneName.toUpperCase().includes(key)) {
                    return value;
                }
            }
            return zoneName;
        }

        // Function to get QTY for a wilaya
        function getWilayaQty(wilayaName) {
            const normalizedName = normalizeZoneName(wilayaName);
            let qty = zoneData.get(normalizedName) || 0;
            
            // Check for combined zones
            for (const group of combinedZones) {
                if (group.includes(normalizedName)) {
                    // Sum QTY from all zones in the group
                    qty = group.reduce((total, zoneName) => {
                        return total + (zoneData.get(zoneName) || 0);
                    }, 0);
                    break;
                }
            }
            
            return qty;
        }

        // Styling functions
        function getStyleByQty(feature) {
            const wilayaName = feature.properties.name;
            const qty = getWilayaQty(wilayaName);
            
            return {
                fillColor: getColorByQty(qty),
                weight: 2,
                opacity: 1,
                color: '#2c3e50',  // Darker border for better visibility
                dashArray: '',
                fillOpacity: 0.8
            };
        }

        function getHighlightStyle(feature) {
            return {
                fillColor: '#e74c3c',
                weight: 3,
                opacity: 1,
                color: '#2c3e50',
                dashArray: '',
                fillOpacity: 0.9
            };
        }

        function getSelectedStyle(feature) {
            return {
                fillColor: '#f39c12',
                weight: 4,
                opacity: 1,
                color: '#2c3e50',
                dashArray: '',
                fillOpacity: 0.9
            };
        }

        // Event handlers for each feature
        function onEachFeature(feature, layer) {
            // Store layer reference for quick access
            wilayaLayers.set(feature.properties.name, layer);

            // Don't create permanent wilaya name labels - only show on click

            // Update popup content with QTY
            function updatePopup() {
                const qty = getWilayaQty(feature.properties.name);
                const percentage = maxQty > 0 ? Math.round((qty / maxQty) * 100) : 0;
                
                const popupContent = `
                    <div class="popup-content">
                        <h3>${feature.properties.name}</h3>
                        <p><strong>QTY:</strong> ${qty.toLocaleString()}</p>
                        <div class="qty-bar-container">
                            <div class="qty-bar">
                                <div class="qty-bar-fill" style="width: ${percentage}%">
                                    <span class="qty-bar-text">${percentage}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                layer.bindPopup(popupContent);
            }

            updatePopup();

            // Create QTY label
            function createQtyLabel() {
                const qty = getWilayaQty(feature.properties.name);
                if (qty > 0) {
                    // Get the center of the polygon
                    const bounds = layer.getBounds();
                    const center = bounds.getCenter();
                    
                    // Create label
                    const qtyLabel = L.marker(center, {
                        icon: L.divIcon({
                            className: 'qty-label',
                            html: qty.toLocaleString(),
                            iconSize: [null, null],
                            iconAnchor: [null, null]
                        }),
                        interactive: false
                    });
                    
                    return qtyLabel;
                }
                return null;
            }

            // Mouse events
            layer.on({
                mouseover: function(e) {
                    if (selectedWilaya !== layer) {
                        layer.setStyle(getHighlightStyle(feature));
                    }
                    layer.bringToFront();
                },
                mouseout: function(e) {
                    if (selectedWilaya !== layer) {
                        layer.setStyle(getStyleByQty(feature));
                    }
                },
                click: function(e) {
                    selectWilayaLayer(layer, feature);
                }
            });
        }

        function selectWilayaLayer(layer, feature) {
            // Reset previously selected
            if (selectedWilaya && selectedWilaya !== layer) {
                selectedWilaya.setStyle(getStyleByQty(selectedWilaya.feature));
                // Remove previous wilaya name label
                const prevName = selectedWilaya.feature.properties.name;
                if (wilayaNameLabels.has(prevName)) {
                    map.removeLayer(wilayaNameLabels.get(prevName));
                    wilayaNameLabels.delete(prevName);
                }
            }
            
            // Set new selection
            selectedWilaya = layer;
            layer.setStyle(getSelectedStyle(feature));
            
            // Update stats
            const qty = getWilayaQty(feature.properties.name);
            document.getElementById('selectedWilaya').textContent = feature.properties.name;
            
            // Show wilaya name label for selected wilaya
            const bounds = layer.getBounds();
            const center = bounds.getCenter();
            
            const nameLabel = L.marker(center, {
                icon: L.divIcon({
                    className: 'wilaya-name-label',
                    html: feature.properties.name,
                    iconSize: [null, null],
                    iconAnchor: [null, null]
                }),
                interactive: false
            });
            
            nameLabel.addTo(map);
            wilayaNameLabels.set(feature.properties.name, nameLabel);
            
            // Update popup content for selected wilaya (no percentage bar)
            const popupContent = `
                <div class="popup-content">
                    <h3>${feature.properties.name}</h3>
                    <p><strong>QTY:</strong> ${qty.toLocaleString()}</p>
                </div>
            `;
            layer.bindPopup(popupContent);
            
            // Zoom to feature
            map.fitBounds(layer.getBounds(), {padding: [20, 20]});
            
            // Open popup
            layer.openPopup();
        }

        // Function to select wilaya by name (called from PHP list)
        function selectWilaya(wilayaName) {
            const layer = wilayaLayers.get(wilayaName);
            if (layer) {
                selectWilayaLayer(layer, layer.feature);
            }
        }

        // Load the Algeria GeoJSON data
        fetch('dz.json')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                algeriaLayer = L.geoJSON(data, {
                    style: getStyleByQty,
                    onEachFeature: onEachFeature
                }).addTo(map);

                // Fit map to Algeria bounds
                map.fitBounds(algeriaLayer.getBounds());
                
                // Setup search functionality
                setupSearch(data.features);

                console.log('Algeria map loaded successfully with', data.features.length, 'wilayas');
                
                // Set default dates
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
                document.getElementById('endDate').value = today.toISOString().split('T')[0];
            })
            .catch(error => {
                console.error('Error loading Algeria map data:', error);
                alert('Error loading map data. Please check if dz.json file exists and is accessible.');
            });

        // Fetch zone data from API
        async function fetchZoneData() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const product = document.getElementById('productSearch').value.trim();

            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            try {
                let url = `http://192.168.1.94:5000/fetchZoneRecap?start_date=${startDate}&end_date=${endDate}`;
                if (product) {
                    url += `&product=${encodeURIComponent(product)}`;
                }

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                // Clear previous data
                zoneData.clear();
                maxQty = 0;

                // Process the data
                data.forEach(item => {
                    if (item.ZONE && item.QTY) {
                        const normalizedZone = normalizeZoneName(item.ZONE);
                        zoneData.set(normalizedZone, item.QTY);
                        maxQty = Math.max(maxQty, item.QTY);
                    }
                });

                // Update map colors
                updateMapColors();
                
                console.log('Zone data loaded:', data.length, 'zones');
                
                // Update stats
                let totalQty = 0;
                let zonesWithData = 0;
                
                zoneData.forEach(qty => {
                    totalQty += qty;
                    if (qty > 0) zonesWithData++;
                });
                
                document.getElementById('totalQty').textContent = totalQty.toLocaleString();
                document.getElementById('zonesWithData').textContent = zonesWithData;
                
                // Update zones list
                updateZonesList();

            } catch (error) {
                console.error('Error fetching zone data:', error);
                alert('Error fetching zone data. Please check your connection and try again.');
            }
        }

        // Function to update map colors based on fetched data
        function updateMapColors() {
            if (algeriaLayer) {
                // Clear existing labels
                qtyLabels.forEach(label => {
                    if (label) {
                        map.removeLayer(label);
                    }
                });
                qtyLabels.clear();

                algeriaLayer.eachLayer(layer => {
                    // Update style
                    layer.setStyle(getStyleByQty(layer.feature));
                    
                    // Update popup (with percentage bar for non-selected wilayas)
                    const qty = getWilayaQty(layer.feature.properties.name);
                    const percentage = maxQty > 0 ? Math.round((qty / maxQty) * 100) : 0;
                    
                    const popupContent = `
                        <div class="popup-content">
                            <h3>${layer.feature.properties.name}</h3>
                            <p><strong>QTY:</strong> ${qty.toLocaleString()}</p>
                            <div class="qty-bar-container">
                                <div class="qty-bar">
                                    <div class="qty-bar-fill" style="width: ${percentage}%">
                                        <span class="qty-bar-text">${percentage}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    layer.bindPopup(popupContent);

                    // Add QTY label if qty > 0
                    if (qty > 0) {
                        const bounds = layer.getBounds();
                        const center = bounds.getCenter();
                        
                        const qtyLabel = L.marker(center, {
                            icon: L.divIcon({
                                className: 'qty-label',
                                html: qty.toLocaleString(),
                                iconSize: [null, null],
                                iconAnchor: [null, null]
                            }),
                            interactive: false
                        });
                        
                        qtyLabel.addTo(map);
                        qtyLabels.set(layer.feature.properties.name, qtyLabel);
                    }
                });
                
                // If there's a selected wilaya, update its popup to not show percentage bar
                if (selectedWilaya) {
                    const qty = getWilayaQty(selectedWilaya.feature.properties.name);
                    const popupContent = `
                        <div class="popup-content">
                            <h3>${selectedWilaya.feature.properties.name}</h3>
                            <p><strong>QTY:</strong> ${qty.toLocaleString()}</p>
                        </div>
                    `;
                    selectedWilaya.bindPopup(popupContent);
                    
                    // Ensure the selected wilaya's name label is still visible
                    const selectedName = selectedWilaya.feature.properties.name;
                    if (!wilayaNameLabels.has(selectedName)) {
                        const bounds = selectedWilaya.getBounds();
                        const center = bounds.getCenter();
                        
                        const nameLabel = L.marker(center, {
                            icon: L.divIcon({
                                className: 'wilaya-name-label',
                                html: selectedName,
                                iconSize: [null, null],
                                iconAnchor: [null, null]
                            }),
                            interactive: false
                        });
                        
                        nameLabel.addTo(map);
                        wilayaNameLabels.set(selectedName, nameLabel);
                    }
                }
            }
        }

        // Function to update the zones list display
        function updateZonesList() {
            const zonesList = document.getElementById('zonesList');
            
            if (zoneData.size === 0) {
                zonesList.innerHTML = '<p class="no-data">No data loaded. Click "Load Data" to fetch zone information.</p>';
                return;
            }
            
            // Convert zoneData to array and sort by QTY (descending)
            const sortedZones = Array.from(zoneData.entries())
                .filter(([zone, qty]) => qty > 0)
                .sort((a, b) => b[1] - a[1]);
            
            if (sortedZones.length === 0) {
                zonesList.innerHTML = '<p class="no-data">No zones with data found for the selected criteria.</p>';
                return;
            }
            
            const zonesHTML = sortedZones.map(([zoneName, qty]) => `
                <div class="zone-item" onclick="selectWilaya('${zoneName.replace(/'/g, "\\'")}')">
                    <span class="zone-name">${zoneName}</span>
                    <span class="zone-qty">${qty.toLocaleString()}</span>
                </div>
            `).join('');
            
            zonesList.innerHTML = zonesHTML;
        }

        // Search functionality
        function setupSearch(features) {
            const searchBox = document.getElementById('productSearch');
            
            // Remove the old search functionality since we're now using product search
            // The search will be handled by the fetchZoneData function
        }

        // Map event listeners
        map.on('zoomend', function() {
            document.getElementById('mapZoom').textContent = map.getZoom();
        });

        // Control functions
        function resetMap() {
            if (algeriaLayer) {
                map.fitBounds(algeriaLayer.getBounds());
                
                // Reset selection
                selectedWilaya = null;
                document.getElementById('selectedWilaya').textContent = 'None';
                
                // Clear search
                document.getElementById('productSearch').value = '';
                
                // Clear QTY labels
                qtyLabels.forEach(label => {
                    if (label) {
                        map.removeLayer(label);
                    }
                });
                qtyLabels.clear();
                
                // Clear wilaya name labels
                wilayaNameLabels.forEach(label => {
                    if (label) {
                        map.removeLayer(label);
                    }
                });
                wilayaNameLabels.clear();
                
                // Clear zone data
                zoneData.clear();
                maxQty = 0;
                
                // Reset colors to default
                algeriaLayer.eachLayer(layer => {
                    layer.setStyle(getStyleByQty(layer.feature));
                    
                    // Reset popup to show only wilaya name
                    const popupContent = `
                        <div class="popup-content">
                            <h3>${layer.feature.properties.name}</h3>
                            <p><strong>QTY:</strong> 0</p>
                            <div class="qty-bar-container">
                                <div class="qty-bar">
                                    <div class="qty-bar-fill" style="width: 0%">
                                        <span class="qty-bar-text">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    layer.bindPopup(popupContent);
                });
                
                // Reset statistics
                document.getElementById('totalQty').textContent = '0';
                document.getElementById('zonesWithData').textContent = '0';
                
                // Reset zones list
                document.getElementById('zonesList').innerHTML = '<p class="no-data">No data loaded. Click "Load Data" to fetch zone information.</p>';
            }
        }

        function toggleLayer() {
            // This function can be used to toggle between different visual styles
            console.log('Toggle layer functionality can be implemented here');
        }

        function downloadMapData() {
            // Create downloadable JSON data including QTY information
            const exportData = {
                message: "Algeria Map Data Export with QTY",
                timestamp: new Date().toISOString(),
                total_wilayas: <?php echo $totalWilayas; ?>,
                selected_wilaya: selectedWilaya ? selectedWilaya.feature.properties.name : 'None',
                current_zoom: map.getZoom(),
                map_center: map.getCenter(),
                zone_data: Object.fromEntries(zoneData),
                max_qty: maxQty,
                product_filter: document.getElementById('productSearch').value,
                date_range: {
                    start: document.getElementById('startDate').value,
                    end: document.getElementById('endDate').value
                },
                wilayas: <?php echo json_encode($mapStats); ?>
            };
            
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportData, null, 2));
            
            const downloadAnchorNode = document.createElement('a');
            downloadAnchorNode.setAttribute("href", dataStr);
            downloadAnchorNode.setAttribute("download", "algeria_map_data_with_qty_" + new Date().toISOString().split('T')[0] + ".json");
            document.body.appendChild(downloadAnchorNode);
            downloadAnchorNode.click();
            downloadAnchorNode.remove();
        }

        function fullscreenMap() {
            const mapContainer = document.querySelector('.map-container');
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen().then(() => {
                    setTimeout(() => map.invalidateSize(), 100);
                }).catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // Theme toggle functionality
        function toggleTheme() {
            document.body.classList.toggle('dark');
            const themeBtn = document.querySelector('.theme-toggle');
            const isDark = document.body.classList.contains('dark');
            
            themeBtn.innerHTML = isDark 
                ? '<i class="fas fa-sun"></i> Light Mode'
                : '<i class="fas fa-moon"></i> Dark Mode';
            
            // Save theme preference
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark');
                document.querySelector('.theme-toggle').innerHTML = '<i class="fas fa-sun"></i> Light Mode';
            }
        });

        // Add scale control
        L.control.scale().addTo(map);

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                fetchZoneData();
            }
        });

        console.log('Algeria Interactive Map (PHP Version) with QTY data loaded successfully!');
    </script>
</body>
</html>
