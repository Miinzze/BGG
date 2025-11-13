<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('settings_manage');

$message = '';
$messageType = '';

// Gruppen laden
$stmt = $pdo->query("SELECT * FROM geofence_groups ORDER BY name");
$groups = $stmt->fetchAll();

// Geo-Fence erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_geofence'])) {
    validateCSRF();
    
    $name = trim($_POST['name'] ?? '');
    $groupId = intval($_POST['group_id'] ?? 0);
    $coordinates = $_POST['coordinates'] ?? '';
    $centerLat = floatval($_POST['center_lat'] ?? 0);
    $centerLng = floatval($_POST['center_lng'] ?? 0);
    $fenceType = $_POST['fence_type'] ?? 'polygon';
    $radius = $fenceType === 'circle' ? intval($_POST['radius'] ?? 50) : null;
    
    if (empty($name)) {
        $message = 'Bitte geben Sie einen Namen ein';
        $messageType = 'danger';
    } elseif (empty($coordinates)) {
        $message = 'Bitte erfassen Sie mindestens 3 Punkte';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO geofences (name, group_id, coordinates, center_lat, center_lng, 
                                      radius, fence_type, created_by, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $name, 
                $groupId > 0 ? $groupId : null, 
                $coordinates, 
                $centerLat, 
                $centerLng,
                $radius,
                $fenceType,
                $_SESSION['user_id']
            ]);
            
            $message = 'Geo-Fence erfolgreich erstellt';
            $messageType = 'success';
            
            logActivity($_SESSION['user_id'], 'geofence_created', "Geo-Fence '$name' erstellt");
            
            header('Location: geofence_manager.php');
            exit;
        } catch (PDOException $e) {
            $message = 'Fehler beim Erstellen: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Geo-Fence erstellen</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="css/mobile-features.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        .mobile-header {
            background: #007bff;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .mobile-header h1 {
            margin: 0;
            font-size: 18px;
            flex: 1;
            text-align: center;
        }
        
        .header-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }
        
        #map {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }
        
        .action-buttons {
            position: fixed;
            top: 80px;
            right: 15px;
            z-index: 1002;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .action-button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 2px solid #007bff;
            color: #007bff;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .action-button:active {
            transform: scale(0.95);
        }
        
        .action-button.active {
            background: #007bff;
            color: white;
        }
        
        .control-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
            z-index: 1001;
            max-height: 70%;
            overflow-y: auto;
            transform: translateY(calc(100% - 60px));
            transition: transform 0.3s ease;
        }
        
        .control-panel.expanded {
            transform: translateY(0);
        }
        
        .panel-handle {
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            user-select: none;
        }
        
        .panel-handle::before {
            content: '';
            display: block;
            width: 40px;
            height: 4px;
            background: #dee2e6;
            margin: 0 auto 10px;
            border-radius: 2px;
        }
        
        .panel-content {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .mode-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .mode-button {
            padding: 15px;
            border: 2px solid #007bff;
            background: white;
            color: #007bff;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .mode-button:active {
            transform: scale(0.98);
        }
        
        .mode-button.active {
            background: #007bff;
            color: white;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .point-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .point-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            background: #f8f9fa;
            margin-bottom: 5px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .point-item button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .radius-slider {
            width: 100%;
            margin: 10px 0;
        }
        
        .radius-display {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        
        .btn-block {
            width: 100%;
            padding: 15px;
            font-size: 16px;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <button class="header-btn" onclick="window.history.back()" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1><i class="fas fa-draw-polygon"></i> Geo-Fence erstellen</h1>
        <div style="width: 34px;"></div>
    </div>
    
    <div id="map"></div>
    
    <div class="action-buttons">
        <button class="action-button" id="locateBtn" title="Meine Position" type="button">
            <i class="fas fa-crosshairs"></i>
        </button>
        <button class="action-button" id="clearBtn" title="Zurücksetzen" type="button">
            <i class="fas fa-trash"></i>
        </button>
        <button class="action-button" id="undoBtn" title="Rückgängig" type="button">
            <i class="fas fa-undo"></i>
        </button>
    </div>
    
    <div class="control-panel" id="controlPanel">
        <div class="panel-handle" id="panelHandle">
            <strong id="panelTitle">Einstellungen</strong>
        </div>
        
        <div class="panel-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="geofenceForm">
                <?= csrf_field() ?>
                
                <div class="mode-buttons">
                    <button type="button" class="mode-button active" id="polygonBtn">
                        <i class="fas fa-draw-polygon"></i><br>Polygon
                    </button>
                    <button type="button" class="mode-button" id="circleBtn">
                        <i class="fas fa-circle"></i><br>Kreis
                    </button>
                </div>
                
                <input type="hidden" name="fence_type" id="fenceType" value="polygon">
                <input type="hidden" name="coordinates" id="coordinates">
                <input type="hidden" name="center_lat" id="centerLat">
                <input type="hidden" name="center_lng" id="centerLng">
                
                <div id="polygonInfo" class="info-box">
                    <i class="fas fa-info-circle"></i> <strong>Polygon-Modus:</strong><br>
                    Tippen Sie auf die Karte, um Punkte zu setzen. Mindestens 3 Punkte erforderlich.
                </div>
                
                <div id="circleInfo" class="info-box" style="display: none;">
                    <i class="fas fa-info-circle"></i> <strong>Kreis-Modus:</strong><br>
                    Tippen Sie auf die Karte für den Mittelpunkt und passen Sie den Radius an.
                </div>
                
                <div id="radiusControl" style="display: none;">
                    <label>Radius: <span id="radiusValue">50</span> Meter</label>
                    <input type="range" class="radius-slider" name="radius" id="radiusSlider" 
                           min="10" max="500" value="50" step="10">
                </div>
                
                <div id="pointsList" class="point-list" style="display: none;">
                    <strong>Erfasste Punkte: <span id="pointCount">0</span></strong>
                    <div id="pointsContainer"></div>
                </div>
                
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" id="nameInput" required 
                           placeholder="z.B. Lagerbereich Nord">
                </div>
                
                <div class="form-group">
                    <label>Gruppe</label>
                    <select name="group_id" id="groupSelect">
                        <option value="0">Keine Gruppe</option>
                        <?php foreach ($groups as $group): ?>
                        <option value="<?= $group['id'] ?>" 
                                style="color: <?= htmlspecialchars($group['color']) ?>;">
                            <?= htmlspecialchars($group['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="create_geofence" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Geo-Fence speichern
                </button>
            </form>
        </div>
    </div>
    
    <script>
    // Globale Variablen
    var map, currentMode = 'polygon';
    var points = [];
    var markers = [];
    var polygon = null;
    var circle = null;
    var centerMarker = null;
    
    // Karte initialisieren
    function initMap() {
        map = L.map('map').setView([50.0, 9.0], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(map);
        
        // Auf Klicks auf die Karte reagieren
        map.on('click', function(e) {
            addPoint(e.latlng);
        });
        
        // Initial GPS-Position laden
        setTimeout(function() { locateMe(); }, 500);
    }
    
    function setMode(mode) {
        currentMode = mode;
        document.getElementById('fenceType').value = mode;
        
        // Buttons aktualisieren
        document.getElementById('polygonBtn').classList.toggle('active', mode === 'polygon');
        document.getElementById('circleBtn').classList.toggle('active', mode === 'circle');
        
        // Info-Boxen umschalten
        document.getElementById('polygonInfo').style.display = mode === 'polygon' ? 'block' : 'none';
        document.getElementById('circleInfo').style.display = mode === 'circle' ? 'block' : 'none';
        document.getElementById('radiusControl').style.display = mode === 'circle' ? 'block' : 'none';
        document.getElementById('pointsList').style.display = mode === 'polygon' ? 'block' : 'none';
        
        clearPoints();
    }
    
    function addPoint(latlng) {
        if (currentMode === 'polygon') {
            points.push(latlng);
            
            // Marker hinzufügen
            var marker = L.circleMarker(latlng, {
                radius: 8,
                fillColor: '#007bff',
                color: 'white',
                weight: 2,
                fillOpacity: 1
            }).addTo(map);
            markers.push(marker);
            
            // Polygon aktualisieren
            if (polygon) {
                map.removeLayer(polygon);
            }
            
            if (points.length >= 3) {
                polygon = L.polygon(points, {
                    color: '#007bff',
                    fillColor: '#007bff',
                    fillOpacity: 0.3
                }).addTo(map);
            } else if (points.length === 2) {
                polygon = L.polyline(points, {
                    color: '#007bff',
                    weight: 3
                }).addTo(map);
            }
            
            updatePointsList();
        } else {
            // Kreis-Modus
            if (centerMarker) {
                map.removeLayer(centerMarker);
            }
            
            centerMarker = L.marker(latlng, {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41]
                })
            }).addTo(map);
            
            points = [latlng];
            updateCircle();
        }
    }
    
    function updateCircle() {
        if (circle) {
            map.removeLayer(circle);
        }
        
        if (points.length > 0) {
            var radius = parseInt(document.getElementById('radiusSlider').value);
            circle = L.circle(points[0], {
                radius: radius,
                color: '#007bff',
                fillColor: '#007bff',
                fillOpacity: 0.3
            }).addTo(map);
        }
    }
    
    function updateRadius(value) {
        document.getElementById('radiusValue').textContent = value;
        updateCircle();
    }
    
    function updatePointsList() {
        document.getElementById('pointCount').textContent = points.length;
        var container = document.getElementById('pointsContainer');
        container.innerHTML = '';
        
        if (points.length > 0) {
            document.getElementById('pointsList').style.display = 'block';
        }
        
        for (var i = 0; i < points.length; i++) {
            var div = document.createElement('div');
            div.className = 'point-item';
            div.innerHTML = '<span>Punkt ' + (i + 1) + ': ' + points[i].lat.toFixed(6) + ', ' + points[i].lng.toFixed(6) + '</span>' +
                '<button type="button" onclick="removePoint(' + i + ')"><i class="fas fa-times"></i></button>';
            container.appendChild(div);
        }
    }
    
    function removePoint(index) {
        points.splice(index, 1);
        
        if (markers[index]) {
            map.removeLayer(markers[index]);
            markers.splice(index, 1);
        }
        
        if (polygon) {
            map.removeLayer(polygon);
            polygon = null;
        }
        
        if (points.length >= 3) {
            polygon = L.polygon(points, {
                color: '#007bff',
                fillColor: '#007bff',
                fillOpacity: 0.3
            }).addTo(map);
        } else if (points.length === 2) {
            polygon = L.polyline(points, {
                color: '#007bff',
                weight: 3
            }).addTo(map);
        }
        
        updatePointsList();
    }
    
    function undoLastPoint() {
        if (points.length > 0) {
            removePoint(points.length - 1);
        }
    }
    
    function clearPoints() {
        points = [];
        
        for (var i = 0; i < markers.length; i++) {
            map.removeLayer(markers[i]);
        }
        markers = [];
        
        if (polygon) {
            map.removeLayer(polygon);
            polygon = null;
        }
        
        if (circle) {
            map.removeLayer(circle);
            circle = null;
        }
        
        if (centerMarker) {
            map.removeLayer(centerMarker);
            centerMarker = null;
        }
        
        updatePointsList();
        document.getElementById('pointsList').style.display = 'none';
    }
    
    function locateMe() {
        if (navigator.geolocation) {
            document.getElementById('locateBtn').classList.add('active');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var latlng = L.latLng(position.coords.latitude, position.coords.longitude);
                    map.setView(latlng, 18);
                    
                    L.circleMarker(latlng, {
                        radius: 10,
                        fillColor: '#00ff00',
                        color: 'white',
                        weight: 3,
                        fillOpacity: 0.8
                    }).addTo(map).bindPopup('Ihre Position').openPopup();
                    
                    document.getElementById('locateBtn').classList.remove('active');
                },
                function(error) {
                    alert('GPS-Position konnte nicht ermittelt werden: ' + error.message);
                    document.getElementById('locateBtn').classList.remove('active');
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            alert('GPS wird von Ihrem Gerät nicht unterstützt');
        }
    }
    
    function togglePanel() {
        document.getElementById('controlPanel').classList.toggle('expanded');
    }
    
    // Event Listener
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        
        // Button Events
        document.getElementById('locateBtn').addEventListener('click', locateMe);
        document.getElementById('clearBtn').addEventListener('click', clearPoints);
        document.getElementById('undoBtn').addEventListener('click', undoLastPoint);
        document.getElementById('panelHandle').addEventListener('click', togglePanel);
        document.getElementById('polygonBtn').addEventListener('click', function() { setMode('polygon'); });
        document.getElementById('circleBtn').addEventListener('click', function() { setMode('circle'); });
        document.getElementById('radiusSlider').addEventListener('input', function() { updateRadius(this.value); });
        
        // Form Validierung
        document.getElementById('geofenceForm').addEventListener('submit', function(e) {
            if (currentMode === 'polygon' && points.length < 3) {
                e.preventDefault();
                alert('Bitte erfassen Sie mindestens 3 Punkte für ein Polygon');
                return;
            }
            
            if (currentMode === 'circle' && points.length === 0) {
                e.preventDefault();
                alert('Bitte setzen Sie einen Mittelpunkt für den Kreis');
                return;
            }
            
            // Koordinaten als JSON speichern
            var coords = [];
            for (var i = 0; i < points.length; i++) {
                coords.push({ lat: points[i].lat, lng: points[i].lng });
            }
            document.getElementById('coordinates').value = JSON.stringify(coords);
            
            // Zentrum berechnen
            if (points.length > 0) {
                var centerLat = 0, centerLng = 0;
                for (var i = 0; i < points.length; i++) {
                    centerLat += points[i].lat;
                    centerLng += points[i].lng;
                }
                centerLat /= points.length;
                centerLng /= points.length;
                document.getElementById('centerLat').value = centerLat;
                document.getElementById('centerLng').value = centerLng;
            }
        });
    });
    </script>
</body>
</html>