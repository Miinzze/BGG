<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// Statistiken laden - NUR AKTIVIERTE MARKER
try {
    // Lagergeräte (aktiviert)
    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE is_storage = 1 AND is_activated = 1 AND deleted_at IS NULL");
    $storageCount = $stmt->fetchColumn();
    
    // Verfügbare Mietgeräte (aktiviert)
    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE is_storage = 0 AND rental_status = 'verfuegbar' AND is_activated = 1 AND deleted_at IS NULL");
    $availableCount = $stmt->fetchColumn();
    
    // Vermietete Geräte (aktiviert)
    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE is_storage = 0 AND rental_status = 'vermietet' AND is_activated = 1 AND deleted_at IS NULL");
    $rentedCount = $stmt->fetchColumn();
    
    // Fällige Wartungen (aktiviert)
    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE next_maintenance <= CURDATE() AND next_maintenance IS NOT NULL AND is_activated = 1 AND deleted_at IS NULL");
    $maintenanceDueCount = $stmt->fetchColumn();
    
    // Wartungen diese Woche (aktiviert)
    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE next_maintenance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND is_activated = 1 AND deleted_at IS NULL");
    $maintenanceThisWeek = $stmt->fetchColumn();
    
    // Statistiken für Kundengeräte und Reparaturen
    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE is_customer_device = 1 AND is_activated = 1 AND deleted_at IS NULL");
    $customerDeviceCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE is_repair_device = 1 AND is_activated = 1 AND deleted_at IS NULL AND is_finished = 0");
    $repairDeviceCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE (is_customer_device = 1 OR is_repair_device = 1) AND is_finished = 1 AND deleted_at IS NULL");
    $finishedDeviceCount = $stmt->fetchColumn();

    $totalRentalDevices = $availableCount + $rentedCount;
    $utilizationRate = $totalRentalDevices > 0 ? round(($rentedCount / $totalRentalDevices) * 100) : 0;
    
} catch (Exception $e) {
    $storageCount = 0;
    $availableCount = 0;
    $rentedCount = 0;
    $maintenanceDueCount = 0;
    $maintenanceThisWeek = 0;
    $totalRentalDevices = 0;
    $utilizationRate = 0;
}

// NUR AKTIVIERTE UND NICHT GELÖSCHTE MARKER LADEN
$stmt = $pdo->query("SELECT * FROM markers WHERE is_activated = 1 AND deleted_at IS NULL ORDER BY created_at DESC");
$markers = $stmt->fetchAll();

// Nicht-aktivierte Marker zÀhlen (fßr Info)
$stmt = $pdo->query("SELECT COUNT(*) FROM markers WHERE is_activated = 0 AND deleted_at IS NULL");
$inactiveCount = $stmt->fetchColumn();

$settings = getSystemSettings();
$showLegend = !empty($settings['show_map_legend']);
$showMessages = !empty($settings['show_system_messages']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGG Geräte Verwaltung - Übersicht</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="css/ar-navigation.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/ar-navigation.js"></script>
    
    <style>
        /* Leaflet Layer Control Styling */
        .leaflet-control-layers {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            border: none;
        }
        
        .leaflet-control-layers-toggle {
            background-image: none;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .leaflet-control-layers-toggle:before {
            content: "🗺️";
            font-size: 20px;
        }
        
        .leaflet-control-layers-expanded {
            padding: 10px;
            min-width: 250px;
        }
        
        .leaflet-control-layers label {
            display: flex;
            align-items: center;
            padding: 8px 5px;
            margin: 3px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.2s;
            font-size: 14px;
        }
        
        .leaflet-control-layers label:hover {
            background: #f0f0f0;
        }
        
        .leaflet-control-layers-base label {
            font-weight: 500;
        }
        
        .leaflet-control-layers input {
            margin-right: 8px;
        }
        
        .leaflet-control-layers-separator {
            margin: 8px 0;
            border-top: 1px solid #ddd;
        }
        
        /* Mobile Optimierung */
        @media (max-width: 768px) {
            .leaflet-control-layers-expanded {
                min-width: 200px;
                font-size: 13px;
            }
            
            .leaflet-control-layers label {
                padding: 6px 3px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="map-dashboard-container">
        <?php if ($showMessages): ?>
        <div class="system-message" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1001; background: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); max-width: 500px;">
            <strong>System bereit</strong> - <?= count($markers) ?> aktive Marker auf Karte
            <?php if ($inactiveCount > 0): ?>
                <br><small style="color: #666;"><?= $inactiveCount ?> Marker warten auf Aktivierung (QR-Code scannen)</small>
            <?php endif; ?>
            <button onclick="this.parentElement.style.display='none'" style="float: right; border: none; background: none; cursor: pointer; font-size: 18px;">Ă—</button>
        </div>
        <?php endif; ?>
        
        <div class="search-bar">
            <div class="search-input-group">
                <input type="text" id="searchInput" placeholder="Suche: Name, Seriennummer oder Status...">
                <button onclick="searchMarkers()"><i class="fas fa-search"></i></button>
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>
        
        <div class="map-container" id="mapContainer">
            <div id="map"></div>
            
        <?php if ($showLegend): ?>
        <!-- Legende - nur auf Desktop -->
        <div class="map-legend" id="mapLegend">
            <h4>Legende</h4>
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_available'] ?? '#3388ff') ?>;"></div>
                <span>Verfügbar</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_rented'] ?? '#ffc107') ?>;"></div>
                <span>Vermietet</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_maintenance'] ?? '#dc3545') ?>;"></div>
                <span>Wartung</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_storage'] ?? '#28a745') ?>;"></div>
                <span>Lager</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: <?= htmlspecialchars($settings['marker_color_multidevice'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') ?>;"></div>
                <span>Mehrgerät</span>
            </div>
            <!-- NEU: Kundengeräte -->
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_customer'] ?? '#17a2b8') ?>;"></div>
                <span>Kundengeräte</span>
            </div>
            <!-- NEU: Reparaturen -->
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_repair'] ?? '#fd7e14') ?>;"></div>
                <span>Reparaturen</span>
            </div>
            <!-- NEU: Fertige Geräte -->
            <div class="legend-item">
                <div class="legend-color" style="background-color: <?= htmlspecialchars($settings['marker_color_finished'] ?? '#6c757d') ?>;"></div>
                <span>Fertig / Abholbereit</span>
            </div>
        </div>
        <?php endif; ?>
            
            <!-- Dashboard Toggle Button -->
            <button class="dashboard-toggle-map" onclick="toggleDashboard()" style="position: absolute; right: 20px; top: 20px; z-index: 1001; width: 50px; height: 50px; background: white; border: 2px solid #007bff; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #007bff; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                <i class="fas fa-chart-bar"></i>
            </button>
        </div>
        
        <div class="dashboard-sidebar collapsed" id="dashboard">
            <button class="dashboard-toggle" onclick="toggleDashboard()">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="dashboard-header">
                <h2>Statistik-Dashboard</h2>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-section">
                    <h3>Übersicht</h3>
                    
                    <div class="stat-card success">
                        <div class="stat-label">
                            <i class="fas fa-warehouse"></i>
                            Lagergeräte Gesamt
                        </div>
                        <div class="stat-value"><?= $storageCount ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-label">
                            <i class="fas fa-check-circle"></i>
                            Verfügbare Mietgeräte
                        </div>
                        <div class="stat-value"><?= $availableCount ?></div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-label">
                            <i class="fas fa-handshake"></i>
                            Vermietete Geräte
                        </div>
                        <div class="stat-value"><?= $rentedCount ?></div>
                    </div>
                    
                    <div class="stat-card" style="background: #f8f9fa; border-left: 4px solid #17a2b8;">
                        <div class="stat-label">
                            <i class="fas fa-user-tie"></i>
                            Kundengeräte
                        </div>
                        <div class="stat-value"><?= $customerDeviceCount ?></div>
                    </div>
                    
                    <div class="stat-card" style="background: #f8f9fa; border-left: 4px solid #fd7e14;">
                        <div class="stat-label">
                            <i class="fas fa-tools"></i>
                            In Reparatur
                        </div>
                        <div class="stat-value"><?= $repairDeviceCount ?></div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-label">
                            <i class="fas fa-check-circle"></i>
                            Fertig
                        </div>
                        <div class="stat-value"><?= $finishedDeviceCount ?></div>
                        <small style="color: #666; font-size: 0.8em;">Abholbereit</small>
                    </div>

                    <div class="stat-card <?= $maintenanceDueCount > 0 ? 'danger' : '' ?>">
                        <div class="stat-label">
                            <i class="fas fa-wrench"></i>
                            Fällige Wartungen
                        </div>
                        <div class="stat-value"><?= $maintenanceDueCount ?></div>
                    </div>
                    
                    <?php if ($inactiveCount > 0): ?>
                    <div class="stat-card" style="background: #f8f9fa; border-left: 4px solid #6c757d;">
                        <div class="stat-label">
                            <i class="fas fa-qrcode"></i>
                            Warten auf Aktivierung
                        </div>
                        <div class="stat-value"><?= $inactiveCount ?></div>
                        <small style="color: #666; font-size: 0.8em;">QR-Codes noch nicht gescannt</small>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <h3>Auslastung</h3>
                    <div class="chart-container">
                        <h3>Auslastungsrate: <?= $utilizationRate ?>%</h3>
                        <canvas id="utilizationChart" height="200"></canvas>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Wartungen</h3>
                    <div class="stat-card <?= $maintenanceThisWeek > 0 ? 'warning' : 'success' ?>">
                        <div class="stat-label">
                            <i class="fas fa-calendar-week"></i>
                            Wartungen diese Woche
                        </div>
                        <div class="stat-value"><?= $maintenanceThisWeek ?></div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="markers.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Alle Marker anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
    const allMarkers = <?= json_encode($markers) ?>;
    let highlightedMarker = null;
    let markerObjects = {};
    
    function searchMarkers() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const resultsDiv = document.getElementById('searchResults');
        
        if (searchTerm.length < 2) {
            resultsDiv.classList.remove('active');
            return;
        }
        
        // NUR IN AKTIVIERTEN MARKERN SUCHEN
        const results = allMarkers.filter(marker => {
            const name = (marker.name || '').toLowerCase();
            const serial = (marker.serial_number || '').toLowerCase();
            const status = (marker.rental_status || '').toLowerCase();
            const category = (marker.category || '').toLowerCase();
            
            return name.includes(searchTerm) || 
                   serial.includes(searchTerm) || 
                   status.includes(searchTerm) ||
                   category.includes(searchTerm);
        });
        
        displaySearchResults(results);
    }
    
    function displaySearchResults(results) {
        const resultsDiv = document.getElementById('searchResults');
        
        if (results.length === 0) {
            resultsDiv.innerHTML = '<div class="no-results">Keine aktiven Marker gefunden</div>';
            resultsDiv.classList.add('active');
            return;
        }
        
        let html = '';
        results.forEach(marker => {
            const statusText = {
                'verfuegbar': 'Verfügbar',
                'vermietet': 'Vermietet',
                'wartung': 'In Wartung',
                'reparatur': 'In Reparatur'
            }[marker.rental_status] || marker.rental_status;
            
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            
            html += `
                <div class="search-result-item">
                    <h4>${marker.name}</h4>
                    <p>
                        ${marker.serial_number ? 'SN: ' + marker.serial_number + ' | ' : ''}
                        ${marker.category || 'Keine Kategorie'} | 
                        ${marker.is_storage ? 'Lager' : statusText}
                    </p>
                    <div class="search-result-actions">
                        <button class="btn-show-map" onclick="showOnMap(${marker.id})">
                            <i class="fas fa-map-marker-alt"></i> Auf Karte
                        </button>
                        <button class="btn-show-details" onclick="window.location.href='view_marker.php?id=${marker.id}'">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                        ${isMobile ? 
                            `<button class="btn-edit" onclick="scanToEdit(${marker.id})">
                                <i class="fas fa-qrcode"></i> Scannen
                            </button>` :
                            `<button class="btn-edit" onclick="window.location.href='edit_marker.php?id=${marker.id}'">
                                <i class="fas fa-edit"></i> Bearbeiten
                            </button>`
                        }
                    </div>
                </div>
            `;
        });
        
        resultsDiv.innerHTML = html;
        resultsDiv.classList.add('active');
    }
    
    function showOnMap(markerId) {
        const marker = allMarkers.find(m => m.id === markerId);
        if (!marker) return;
        
        map.setView([marker.latitude, marker.longitude], 18);
        
        if (highlightedMarker) {
            map.removeLayer(highlightedMarker);
        }
        
        highlightedMarker = L.circle([marker.latitude, marker.longitude], {
            radius: 20,
            color: '#ff0000',
            fillColor: '#ff0000',
            fillOpacity: 0.3,
            weight: 3,
            className: 'highlight-pulse'
        }).addTo(map);
        
        if (markerObjects[markerId]) {
            markerObjects[markerId].openPopup();
        }
        
        setTimeout(() => {
            if (highlightedMarker) {
                map.removeLayer(highlightedMarker);
                highlightedMarker = null;
            }
        }, 10000);
        
        document.getElementById('searchResults').classList.remove('active');
    }
    
    function scanToEdit(markerId) {
        alert('Bitte scannen Sie den QR-Code des Markers, um ihn zu bearbeiten.');
        window.location.href = 'scan.php?edit=' + markerId;
    }
    
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchMarkers();
        } else {
            searchMarkers();
        }
    });
    
    const map = L.map('map').setView([<?= $settings['map_default_lat'] ?>, <?= $settings['map_default_lng'] ?>], <?= $settings['map_default_zoom'] ?>);
    
    // Verschiedene Karten-Layer definieren
    const osmStandard = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    });
    
    const osmDE = L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
    });
    
    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 19
    });
    
    const satelliteLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Labels © Esri',
        maxZoom: 19
    });
    
    const terrain = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data: © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: © <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)',
        maxZoom: 17
    });
    
    const humanitarian = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a> hosted by <a href="https://openstreetmap.fr/" target="_blank">OpenStreetMap France</a>',
        maxZoom: 19
    });
    
    const cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
        maxZoom: 19
    });
    
    const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
        maxZoom: 19
    });
    
    // Satelliten-Hybrid (Satellit + Labels)
    const satelliteHybrid = L.layerGroup([satellite, satelliteLabels]);
    
    // Base Maps für Layer Control
    const baseMaps = {
        "🗺️ OpenStreetMap": osmStandard,
        "🛰️ Satellit": satellite,
        "🛰️ Satellit + Beschriftung": satelliteHybrid,
        "🚑 Humanitarian (HOT)": humanitarian,
        "☀️ Hell (Carto Light)": cartoLight,
        "🌙 Dunkel (Carto Dark)": cartoDark
    };
    
    // Standard-Layer hinzufügen (OpenStreetMap)
    osmStandard.addTo(map);
    
    // Layer Control hinzufügen (unten links)
    L.control.layers(baseMaps, null, {
        position: 'bottomleft',
        collapsed: true
    }).addTo(map);
    
    const markers = <?= json_encode($markers) ?>;
    
    // Funktion zum Erstellen von farbigen Marker-Icons
    function getColoredIcon(color) {
        return new L.Icon({
            iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${color}.png`,
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
    }
    
    // Standard Leaflet Icons in verschiedenen Farben
    const blueIcon = getColoredIcon('blue');      // Verfügbar
    const goldIcon = getColoredIcon('gold');      // Vermietet
    const redIcon = getColoredIcon('red');        // Wartung
    const orangeIcon = getColoredIcon('orange');  // Reparatur
    const greenIcon = getColoredIcon('green');    // Lager
    const violetIcon = getColoredIcon('violet');  // Multi-Device
    const greyIcon = getColoredIcon('grey');      // Finished (Standard)
    
    // Finished-Icon aus Settings (kann überschrieben werden)
    const finishedIconColor = '<?= $settings['marker_icon_finished'] ?? 'grey' ?>'.replace('-check', '');
    const finishedIcon = getColoredIcon(finishedIconColor);
    
    // Route zu Marker anzeigen - NEUE AR-NAVIGATION
    function showRouteTo(lat, lng, name) {
        if (!lat || !lng) {
            alert('Keine GPS-Koordinaten für diesen Marker verfügbar');
            return;
        }
        
        // Starte AR-Navigation mit 3D-Pfeil
        arNav.startNavigation(lat, lng, name);
    }
    
    // NUR AKTIVIERTE MARKER AUF KARTE ANZEIGEN
    markers.forEach(marker => {
        // GPS-Daten prüfen - Marker ohne GPS nicht anzeigen
        if (!marker.latitude || !marker.longitude) {
            return;
        }
        
        let icon = blueIcon; // Default
        
        // Prüfen ob Gerät fertig ist (is_finished)
        if (marker.is_finished == 1) {
            icon = finishedIcon;
        } else if (marker.is_multi_device) {
            icon = violetIcon;
        } else if (marker.is_storage) {
            icon = greenIcon;
        } else if (marker.rental_status === 'vermietet') {
            icon = goldIcon;
        } else if (marker.rental_status === 'wartung') {
            icon = redIcon;
        } else if (marker.rental_status === 'reparatur') {
            icon = orangeIcon;
        }
        
        const mapMarker = L.marker([marker.latitude, marker.longitude], { icon: icon })
            .addTo(map)
            .bindPopup(`
                <div class="marker-popup">
                    <h3>${marker.name}</h3>
                    <p><strong>Kategorie:</strong> ${marker.category || 'Keine'}</p>
                    <p><strong>Status:</strong> ${getStatusText(marker)}</p>
                    ${marker.serial_number ? `<p><strong>Seriennummer:</strong> ${marker.serial_number}</p>` : ''}
                    ${marker.is_finished == 1 ? '<p style="color: #28a745; font-weight: bold;"><i class="fas fa-check-circle"></i> Fertig / Abholbereit</p>' : ''}
                    <div style="display: flex; gap: 5px; margin-top: 10px;">
                        <a href="view_marker.php?id=${marker.id}" class="btn btn-sm btn-primary" style="flex: 1; text-align: center;">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                        <?php if (!empty($settings['enable_routing'])): ?>
                        <button onclick="showRouteTo(${marker.latitude}, ${marker.longitude}, '${marker.name.replace(/'/g, "\\'")}')" 
                                class="btn btn-sm btn-success" style="flex: 1;">
                            <i class="fas fa-route"></i> Route
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            `);
        
        markerObjects[marker.id] = mapMarker;
    });
    
    function getStatusText(marker) {
        if (marker.is_finished == 1) return 'Fertig / Abholbereit';
        if (marker.is_multi_device) return 'Mehrgerät-Standort';
        if (marker.is_storage) return 'Lagergerät';
        if (marker.is_customer_device) return 'Kundengerät';
        if (marker.is_repair_device) return 'In Reparatur';
        
        switch(marker.rental_status) {
            case 'verfuegbar': return 'Verfügbar';
            case 'vermietet': return 'Vermietet';
            case 'wartung': return 'In Wartung';
            case 'reparatur': return 'In Reparatur';
            default: return marker.rental_status;
        }
    }
    
    function toggleDashboard() {
        const dashboard = document.getElementById('dashboard');
        const mapContainer = document.getElementById('mapContainer');
        const toggleButton = document.querySelector('.dashboard-toggle-map i');
        
        dashboard.classList.toggle('collapsed');
        mapContainer.classList.toggle('dashboard-open');
        
        if (dashboard.classList.contains('collapsed')) {
            toggleButton.className = 'fas fa-chart-bar';
        } else {
            toggleButton.className = 'fas fa-times';
        }
        
        setTimeout(() => {
            map.invalidateSize();
        }, 350);
    }
    
    const ctx = document.getElementById('utilizationChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Verfügbar', 'Vermietet'],
            datasets: [{
                data: [<?= $availableCount ?>, <?= $rentedCount ?>],
                backgroundColor: ['#007bff', '#ffc107'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
    
    setInterval(() => {
        location.reload();
    }, 5 * 60 * 1000);
    </script>

</body>
</html>