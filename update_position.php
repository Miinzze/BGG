<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'extended_functions.php';


// Prüfen ob Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Benutzer-Daten laden
$user = getUserById($pdo, $_SESSION['user_id']);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// System-Einstellungen laden
$settings = getSystemSettings();

$message = '';
$messageType = '';
$marker = null;

// Marker ID aus URL holen
if (isset($_GET['id'])) {
    $markerId = (int)$_GET['id'];
    
    // Marker-Daten laden
    $stmt = $pdo->prepare("
        SELECT m.*, 
               COALESCE(m.device_status, 'Lager') as device_status,
               m.latitude, 
               m.longitude
        FROM markers m
        WHERE m.id = ?
    ");
    $stmt->execute([$markerId]);
    $marker = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$marker) {
        $message = 'Marker nicht gefunden';
        $messageType = 'danger';
    }
} else {
    $message = 'Keine Marker-ID angegeben';
    $messageType = 'danger';
}

function validateGeofencePosition($pdo, $latitude, $longitude, $markerId) {
    try {
        // Lade Geo-Fence für diesen Marker
        $stmt = $pdo->prepare("
            SELECT g.* 
            FROM geofence_areas g
            JOIN marker_geofence mg ON g.id = mg.geofence_id
            WHERE mg.marker_id = ? AND g.is_active = 1
        ");
        $stmt->execute([$markerId]);
        $geofence = $stmt->fetch();
        
        if (!$geofence) {
            // Kein Geo-Fence = immer erlaubt
            return ['allowed' => true, 'message' => null];
        }
        
        // Berechne Distanz zum Geo-Fence Zentrum
        $fenceLat = floatval($geofence['latitude']);
        $fenceLng = floatval($geofence['longitude']);
        $radius = floatval($geofence['radius_meters']);
        
        $distance = calculateDistance($latitude, $longitude, $fenceLat, $fenceLng);
        
        if ($distance <= $radius) {
            return [
                'allowed' => true, 
                'message' => 'Position innerhalb des Geo-Fence (' . round($distance) . 'm vom Zentrum)',
                'distance' => $distance
            ];
        } else {
            return [
                'allowed' => false, 
                'message' => 'Position außerhalb des Geo-Fence! Distanz: ' . round($distance) . 'm (erlaubt: ' . $radius . 'm)',
                'distance' => $distance
            ];
        }
        
    } catch (Exception $e) {
        error_log("Geo-Fence Validierung fehlgeschlagen: " . $e->getMessage());
        return ['allowed' => true, 'message' => 'Geo-Fence Prüfung übersprungen (Fehler)'];
    }
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Erdradius in Metern
    
    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);
    $deltaLat = deg2rad($lat2 - $lat1);
    $deltaLon = deg2rad($lon2 - $lon1);
    
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1Rad) * cos($lat2Rad) *
         sin($deltaLon / 2) * sin($deltaLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c; // Distanz in Metern
}

// Position aktualisieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_position']) && $marker) {
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $accuracy = floatval($_POST['accuracy'] ?? 0); // GPS-Genauigkeit in Metern
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($latitude) || empty($longitude)) {
        $message = 'Bitte geben Sie gültige Koordinaten ein';
        $messageType = 'danger';
    } elseif ($accuracy > 50) {
        // Warnung bei schlechter GPS-Genauigkeit
        $message = 'WARNUNG: GPS-Genauigkeit ist schlecht (' . round($accuracy) . 'm). Bitte versuchen Sie es an einem Ort mit besserem GPS-Empfang erneut.';
        $messageType = 'warning';
    } else {
        // GEO-FENCE VALIDIERUNG
        $geofenceCheck = validateGeofencePosition($pdo, $latitude, $longitude, $markerId);
        
        if (!$geofenceCheck['allowed']) {
            $message = $geofenceCheck['message'];
            $messageType = 'danger';
            // LOGGING: Position-Update wurde abgelehnt (außerhalb Geo-Fence)
            $activityDescription = sprintf(
                'Position-Update ABGELEHNT (außerhalb Geo-Fence): Versuch bei [%s, %s] - Distanz: %sm',
                number_format($latitude, 6),
                number_format($longitude, 6),
                round($geofenceCheck['distance'] ?? 0)
            );
            if (!empty($notes)) {
                $activityDescription .= ' | Notiz: ' . $notes;
            }
            logActivity('position_update_rejected', $activityDescription, $markerId);
        } else {
            try {
                // Alte Position für Protokoll speichern
                $oldLat = $marker['latitude'];
                $oldLng = $marker['longitude'];
                
                // Position aktualisieren
                $stmt = $pdo->prepare("
                    UPDATE markers 
                    SET latitude = ?, 
                        longitude = ?, 
                        gps_accuracy = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$latitude, $longitude, $accuracy, $markerId]);
                
                // Aktivitätsprotokoll eintragen
                $activityDescription = sprintf(
                    'Position aktualisiert von [%s, %s] zu [%s, %s] (Genauigkeit: %dm)',
                    number_format($oldLat, 6),
                    number_format($oldLng, 6),
                    number_format($latitude, 6),
                    number_format($longitude, 6),
                    round($accuracy)
                );
                
                if (!empty($notes)) {
                    $activityDescription .= ' | Notiz: ' . $notes;
                }
                
                if (isset($geofenceCheck['distance'])) {
                    $activityDescription .= ' | Distanz zum Geo-Fence Zentrum: ' . round($geofenceCheck['distance']) . 'm';
                }
                
                logActivity( 'position_update', $activityDescription, $markerId);
                
                $message = 'Position erfolgreich aktualisiert! ' . ($geofenceCheck['message'] ?? '');
                $messageType = 'success';
                
                // Marker neu laden
                $stmt = $pdo->prepare("
                    SELECT m.*, 
                           COALESCE(m.device_status, 'Lager') as device_status,
                           m.latitude, 
                           m.longitude
                    FROM markers m
                    WHERE m.id = ?
                ");
                $stmt->execute([$markerId]);
                $marker = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                $message = 'Fehler beim Aktualisieren: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// GPS-Position per POST empfangen (für mobile Nutzung)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['use_current_position']) && $marker) {
    if (isset($_POST['current_lat']) && isset($_POST['current_lng'])) {
        $currentLat = floatval($_POST['current_lat']);
        $currentLng = floatval($_POST['current_lng']);
        $accuracy = floatval($_POST['current_accuracy'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        if ($currentLat != 0 && $currentLng != 0) {
            if ($accuracy > 50) {
                $message = 'WARNUNG: GPS-Genauigkeit ist schlecht (' . round($accuracy) . 'm). Bitte versuchen Sie es an einem Ort mit besserem GPS-Empfang erneut.';
                $messageType = 'warning';
            } else {
                // GEO-FENCE VALIDIERUNG
                $geofenceCheck = validateGeofencePosition($pdo, $currentLat, $currentLng, $markerId);
                
                if (!$geofenceCheck['allowed']) {
                    $message = $geofenceCheck['message'];
                    $messageType = 'danger';
                    // LOGGING: Position-Update wurde abgelehnt (außerhalb Geo-Fence)
                    $activityDescription = sprintf(
                        'Position-Update ABGELEHNT (außerhalb Geo-Fence): Versuch bei [%s, %s] - Distanz: %sm (Max: %sm)',
                        number_format($currentLat, 6),
                        number_format($currentLng, 6),
                        round($geofenceCheck['distance'] ?? 0),
                        round($radius ?? 0)
                    );
                    if (!empty($notes)) {
                        $activityDescription .= ' | Notiz: ' . $notes;
                    }
                    logActivity('position_update_rejected', $activityDescription, $markerId);
                } else {
                    try {
                        // Alte Position für Protokoll speichern
                        $oldLat = $marker['latitude'];
                        $oldLng = $marker['longitude'];
                        
                        // Position aktualisieren
                        $stmt = $pdo->prepare("
                            UPDATE markers 
                            SET latitude = ?, 
                                longitude = ?, 
                                gps_accuracy = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$currentLat, $currentLng, $accuracy, $markerId]);
                        
                        // Aktivitätsprotokoll eintragen
                        $activityDescription = sprintf(
                            'Position per GPS aktualisiert von [%s, %s] zu [%s, %s] (Genauigkeit: %dm)',
                            number_format($oldLat, 6),
                            number_format($oldLng, 6),
                            number_format($currentLat, 6),
                            number_format($currentLng, 6),
                            round($accuracy)
                        );
                        
                        if (!empty($notes)) {
                            $activityDescription .= ' | Notiz: ' . $notes;
                        }
                        
                        if (isset($geofenceCheck['distance'])) {
                            $activityDescription .= ' | Distanz zum Geo-Fence Zentrum: ' . round($geofenceCheck['distance']) . 'm';
                        }
                        
                        logActivity( 'gps_position_update', $activityDescription, $markerId);
                        
                        // QR-Code aktivieren falls inaktiv
                        if ($marker['is_activated'] == 0) {
                            $stmt = $pdo->prepare("UPDATE markers SET is_activated = 1, activated_at = NOW() WHERE id = ?");
                            $stmt->execute([$markerId]);
                            
                            $stmt = $pdo->prepare("UPDATE qr_code_pool SET is_activated = 1 WHERE marker_id = ?");
                            $stmt->execute([$markerId]);
                            
                            logActivity( 'qr_activated', "QR-Code '{$marker['qr_code']}' aktiviert durch GPS-Update", $markerId);
                        }
                        
                        $message = 'GPS-Position erfolgreich aktualisiert! ' . ($geofenceCheck['message'] ?? '');
                        $messageType = 'success';
                        
                        // Marker neu laden
                        $stmt = $pdo->prepare("
                            SELECT m.*, 
                                   COALESCE(m.device_status, 'Lager') as device_status,
                                   m.latitude, 
                                   m.longitude
                            FROM markers m
                            WHERE m.id = ?
                        ");
                        $stmt->execute([$markerId]);
                        $marker = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                    } catch (PDOException $e) {
                        $message = 'Fehler beim Aktualisieren: ' . $e->getMessage();
                        $messageType = 'danger';
                    }
                }
            }
        } else {
            $message = 'GPS-Position konnte nicht ermittelt werden';
            $messageType = 'danger';
        }
    }
}


$pageTitle = 'Position aktualisieren';
require_once 'header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-map-marker-alt"></i> Position aktualisieren</h1>
        <a href="marker_details.php?id=<?= $marker ? $marker['id'] : '' ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
        <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <?php if ($marker): ?>
    <div class="row">
        <!-- Linke Spalte: Formular -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Marker-Informationen</h3>
                </div>
                <div class="card-body">
                    <div class="marker-info">
                        <div class="info-row">
                            <strong>Name:</strong>
                            <span><?= htmlspecialchars($marker['name']) ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Status:</strong>
                            <span class="badge badge-<?= getStatusColor($marker['device_status']) ?>">
                                <?= htmlspecialchars($marker['device_status']) ?>
                            </span>
                        </div>
                        <?php if (!empty($marker['description'])): ?>
                        <div class="info-row">
                            <strong>Beschreibung:</strong>
                            <span><?= htmlspecialchars($marker['description']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <strong>Aktuelle Position:</strong>
                            <span>
                                Lat: <?= number_format($marker['latitude'], 6) ?>, 
                                Lng: <?= number_format($marker['longitude'], 6) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3>Neue Position eingeben</h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="updatePositionForm">
                        <div class="form-group">
                            <label for="latitude">Breitengrad (Latitude)</label>
                            <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude" 
                                   value="<?= htmlspecialchars($marker['latitude']) ?>" required>
                            <small class="form-text text-muted">Beispiel: 50.110924</small>
                        </div>

                        <div class="form-group">
                            <label for="longitude">Längengrad (Longitude)</label>
                            <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude" 
                                   value="<?= htmlspecialchars($marker['longitude']) ?>" required>
                            <small class="form-text text-muted">Beispiel: 8.682127</small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notiz (optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Grund für die Positionsänderung..."></textarea>
                        </div>

                        <div class="btn-group-vertical w-100">
                            <button type="submit" name="update_position" class="btn btn-primary">
                                <i class="fas fa-save"></i> Position manuell aktualisieren
                            </button>
                            
                            <button type="button" class="btn btn-success" id="useGpsBtn" onclick="useCurrentPosition()">
                                <i class="fas fa-crosshairs"></i> <span id="gpsButtonText">Aktuelle GPS-Position verwenden</span>
                            </button>
                            
                            <button type="button" class="btn btn-info" onclick="pickFromMap()">
                                <i class="fas fa-map-marked-alt"></i> Position auf Karte wählen
                            </button>
                        </div>

                        <!-- Versteckte Felder für GPS -->
                        <input type="hidden" id="current_lat" name="current_lat" value="0">
                        <input type="hidden" id="current_lng" name="current_lng" value="0">
                    </form>

                    <div id="gpsStatus" class="alert alert-info mt-3" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> <span id="gpsStatusText">GPS-Position wird ermittelt...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechte Spalte: Karte -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Kartenansicht</h3>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Klicken Sie auf die Karte, um eine neue Position zu wählen
                    </small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .marker-info {
        margin-bottom: 15px;
    }

    .marker-info .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .marker-info .info-row:last-child {
        border-bottom: none;
    }

    .marker-info .info-row strong {
        color: #333;
    }

    .btn-group-vertical .btn {
        margin-bottom: 10px;
    }

    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }

    #map {
        border-radius: 8px;
    }

    .leaflet-container {
        border-radius: 8px;
    }

    @media (max-width: 768px) {
        .row {
            flex-direction: column-reverse;
        }
        
        #map {
            height: 400px !important;
            margin-bottom: 20px;
        }
    }
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let currentMarker;

    // Karte initialisieren
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($marker): ?>
        // Karte mit aktueller Position initialisieren
        map = L.map('map').setView([<?= $marker['latitude'] ?>, <?= $marker['longitude'] ?>], 15);
        
        // OpenStreetMap Tiles hinzufügen
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Aktuellen Marker hinzufügen
        currentMarker = L.marker([<?= $marker['latitude'] ?>, <?= $marker['longitude'] ?>], {
            draggable: true,
            title: '<?= htmlspecialchars($marker['name']) ?>'
        }).addTo(map);
        
        currentMarker.bindPopup('<b><?= htmlspecialchars($marker['name']) ?></b><br>Aktuelle Position').openPopup();
        
        // Marker-Drag-Event
        currentMarker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateFormCoordinates(position.lat, position.lng);
        });
        
        // Karten-Click-Event
        map.on('click', function(e) {
            currentMarker.setLatLng(e.latlng);
            updateFormCoordinates(e.latlng.lat, e.latlng.lng);
            map.panTo(e.latlng);
        });
        <?php endif; ?>
    });

    // Formular-Koordinaten aktualisieren
    function updateFormCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
    }

    // Position auf Karte wählen
    function pickFromMap() {
        document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Info anzeigen
        const gpsStatus = document.getElementById('gpsStatus');
        const gpsStatusText = document.getElementById('gpsStatusText');
        gpsStatus.className = 'alert alert-info mt-3';
        gpsStatusText.textContent = 'Klicken Sie auf die Karte oder ziehen Sie den Marker';
        gpsStatus.style.display = 'block';
        
        setTimeout(() => {
            gpsStatus.style.display = 'none';
        }, 3000);
    }

    // Aktuelle GPS-Position verwenden
    function useCurrentPosition() {
        if (!navigator.geolocation) {
            alert('Geolocation wird von Ihrem Browser nicht unterstützt');
            return;
        }
        
        const gpsBtn = document.getElementById('useGpsBtn');
        const gpsStatus = document.getElementById('gpsStatus');
        const gpsStatusText = document.getElementById('gpsStatusText');
        const gpsButtonText = document.getElementById('gpsButtonText');
        
        // Button deaktivieren
        gpsBtn.disabled = true;
        gpsButtonText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GPS wird ermittelt...';
        
        // Status anzeigen
        gpsStatus.className = 'alert alert-info mt-3';
        gpsStatusText.innerHTML = '<i class="fas fa-crosshairs"></i> Warte auf präzise GPS-Position... (kann bis zu 30 Sekunden dauern)';
        gpsStatus.style.display = 'block';
        
        let bestAccuracy = Infinity;
        let bestPosition = null;
        let watchId = null;
        let attempts = 0;
        const maxAttempts = 10; // 10 Messungen
        
        // Hochpräzise GPS-Optionen
        const options = {
            enableHighAccuracy: true, // Höchste Genauigkeit
            timeout: 30000, // 30 Sekunden Timeout
            maximumAge: 0 // Keine gecachte Position verwenden
        };
        
        // Kontinuierliche Positionsüberwachung für beste Genauigkeit
        watchId = navigator.geolocation.watchPosition(
            function(position) {
                attempts++;
                const accuracy = position.coords.accuracy;
                
                gpsStatusText.innerHTML = `<i class="fas fa-satellite-dish"></i> Messung ${attempts}/${maxAttempts} - Genauigkeit: ±${Math.round(accuracy)}m`;
                
                // Speichere die genaueste Position
                if (accuracy < bestAccuracy) {
                    bestAccuracy = accuracy;
                    bestPosition = position;
                    
                    // Zeige Live-Update auf Karte
                    if (currentMarker && map) {
                        currentMarker.setLatLng([position.coords.latitude, position.coords.longitude]);
                        map.panTo([position.coords.latitude, position.coords.longitude]);
                    }
                }
                
                // Wenn sehr gute Genauigkeit (<10m) oder max. Versuche erreicht
                if (accuracy < 10 || attempts >= maxAttempts) {
                    navigator.geolocation.clearWatch(watchId);
                    applyBestPosition();
                }
            },
            function(error) {
                navigator.geolocation.clearWatch(watchId);
                
                let errorMsg = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Zugriff auf GPS wurde verweigert. Bitte erlauben Sie den Standortzugriff.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Standortinformationen sind nicht verfügbar. Gehen Sie ggf. nach draußen.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Zeitüberschreitung bei der Standortermittlung. Versuchen Sie es erneut.';
                        break;
                    default:
                        errorMsg = 'Ein unbekannter Fehler ist aufgetreten.';
                }
                
                gpsStatus.className = 'alert alert-danger mt-3';
                gpsStatusText.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errorMsg;
                
                gpsBtn.disabled = false;
                gpsButtonText.textContent = 'Aktuelle GPS-Position verwenden';
                
                setTimeout(() => {
                    gpsStatus.style.display = 'none';
                }, 8000);
            },
            options
        );
        
        function applyBestPosition() {
            if (!bestPosition) {
                gpsStatus.className = 'alert alert-danger mt-3';
                gpsStatusText.innerHTML = '<i class="fas fa-exclamation-circle"></i> Keine GPS-Position gefunden';
                gpsBtn.disabled = false;
                gpsButtonText.textContent = 'Aktuelle GPS-Position verwenden';
                return;
            }
            
            const lat = bestPosition.coords.latitude;
            const lng = bestPosition.coords.longitude;
            
            // Formular aktualisieren mit hoher Präzision (8 Dezimalstellen)
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            
            // Versteckte Felder setzen
            document.getElementById('current_lat').value = lat.toFixed(8);
            document.getElementById('current_lng').value = lng.toFixed(8);
            
            // Marker auf Karte aktualisieren
            if (currentMarker && map) {
                currentMarker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            }
            
            // Erfolg anzeigen mit Qualitätsindikator
            let qualityIcon = '✓';
            let qualityText = 'Gut';
            let qualityColor = 'success';
            
            if (bestAccuracy < 5) {
                qualityIcon = '✓✓✓';
                qualityText = 'Exzellent';
            } else if (bestAccuracy < 15) {
                qualityIcon = '✓✓';
                qualityText = 'Sehr gut';
            } else if (bestAccuracy > 50) {
                qualityIcon = '~';
                qualityText = 'Akzeptabel';
                qualityColor = 'warning';
            }
            
            gpsStatus.className = `alert alert-${qualityColor} mt-3`;
            gpsStatusText.innerHTML = `
                <i class="fas fa-check-circle"></i> 
                <strong>${qualityIcon} GPS-Position erfolgreich ermittelt!</strong><br>
                <small>
                    Genauigkeit: ±${Math.round(bestAccuracy)}m (${qualityText})<br>
                    Position: ${lat.toFixed(8)}, ${lng.toFixed(8)}<br>
                    Messungen: ${attempts}
                </small>
            `;
            
            gpsBtn.disabled = false;
            gpsButtonText.textContent = 'Aktuelle GPS-Position verwenden';
            
            setTimeout(() => {
                gpsStatus.style.display = 'none';
            }, 5000);
        }
    }

    // Hilfsfunktion für Status-Farben
    function getStatusColor(status) {
        const colors = {
            'Lager': 'secondary',
            'Im Einsatz': 'primary',
            'Wartung': 'warning',
            'Defekt': 'danger',
            'Fertig': 'success'
        };
        return colors[status] || 'secondary';
    }

    function getCurrentPosition() {
        if (!navigator.geolocation) {
            alert('Geolocation wird von Ihrem Browser nicht unterstützt');
            return;
        }
        
        const button = document.querySelector('.btn-gps');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GPS wird erfasst...';
        
        // GEÄNDERT: Höhere Genauigkeit und längerer Timeout
        const options = {
            enableHighAccuracy: true,  // Höchste Genauigkeit
            timeout: 30000,            // 30 Sekunden Timeout (statt 5)
            maximumAge: 0              // Keine gecachten Positionen verwenden
        };
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                document.getElementById('current_lat').value = lat;
                document.getElementById('current_lng').value = lng;
                document.getElementById('current_accuracy').value = accuracy;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById('accuracy').value = accuracy;
                
                // Zeige Genauigkeit an
                let accuracyInfo = `GPS erfasst! Genauigkeit: ±${Math.round(accuracy)}m`;
                let accuracyClass = 'success';
                
                if (accuracy > 50) {
                    accuracyInfo += ' (SCHLECHT - bitte an einem Ort mit besserem GPS-Empfang versuchen)';
                    accuracyClass = 'warning';
                } else if (accuracy > 20) {
                    accuracyInfo += ' (MITTEL)';
                    accuracyClass = 'info';
                } else {
                    accuracyInfo += ' (GUT)';
                }
                
                button.innerHTML = '<i class="fas fa-check-circle"></i> ' + accuracyInfo;
                button.classList.add('btn-' + accuracyClass);
                
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-crosshairs"></i> GPS-Position erneut erfassen';
                    button.classList.remove('btn-' + accuracyClass);
                }, 3000);
            },
            function(error) {
                let errorMsg = 'GPS-Fehler: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Standortzugriff verweigert';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Position nicht verfügbar';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Timeout (versuchen Sie es draußen mit bessererem GPS-Empfang)';
                        break;
                    default:
                        errorMsg += 'Unbekannter Fehler';
                }
                
                alert(errorMsg);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-crosshairs"></i> GPS-Position erfassen';
            },
            options
        );
    }
</script>

<?php
// Hilfsfunktion für Badge-Farben
function getStatusColor($status) {
    $colors = [
        'Lager' => 'secondary',
        'Im Einsatz' => 'primary',
        'Wartung' => 'warning',
        'Defekt' => 'danger',
        'Fertig' => 'success',
        'Abholbereit' => 'info'
    ];
    return $colors[$status] ?? 'secondary';
}

require_once 'footer.php';
?>