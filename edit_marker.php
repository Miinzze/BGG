<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('markers_edit');

$id = $_GET['id'] ?? 0;
$marker = getMarkerById($id, $pdo);

if (!$marker) {
    die('Marker nicht gefunden');
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $rentalStatus = $_POST['rental_status'] ?? $marker['rental_status'];
    $serialNumber = trim($_POST['serial_number'] ?? '');
    $operatingHours = $_POST['operating_hours'] ?? 0;
    $fuelLevel = $_POST['fuel_level'] ?? 0;
    $fuelUnit = $_POST['fuel_unit'] ?? 'percent';
    $fuelCapacity = ($fuelUnit === 'liter') ? ($_POST['fuel_capacity'] ?? null) : null;
    $maintenanceInterval = $_POST['maintenance_interval'] ?? 6;
    $lastMaintenance = $_POST['last_maintenance'] ?? null;
    $isStorage = isset($_POST['is_storage']);
    $isMultiDevice = isset($_POST['is_multi_device']);
    $isCustomerDevice = isset($_POST['is_customer_device']);
    $isRepairDevice = isset($_POST['is_repair_device']);
    $repairDescription = $isRepairDevice ? trim($_POST['repair_description'] ?? '') : null;
    $customerName = $isCustomerDevice ? trim($_POST['customer_name'] ?? '') : null;
    $orderNumber = $isCustomerDevice ? trim($_POST['order_number'] ?? '') : null;
    $weclappEntityId = $isCustomerDevice ? trim($_POST['weclapp_entity_id'] ?? '') : null;

    // GPS-Position
    $latitude = $_POST['latitude'] ?? $marker['latitude'];
    $longitude = $_POST['longitude'] ?? $marker['longitude'];
    $updateGPS = isset($_POST['update_gps']) && $_POST['update_gps'] == '1';
    
    if (empty($name)) {
        $message = 'Name ist erforderlich';
        $messageType = 'danger';
    } elseif (!validateSerialNumber($serialNumber)) {
        $message = 'Seriennummer enthält ungültige Zeichen oder ist zu lang (max. 100 Zeichen)';
        $messageType = 'danger';
    } elseif ($updateGPS && !validateCoordinates($latitude, $longitude)) {
        $message = 'Ungültige GPS-Koordinaten';
        $messageType = 'danger';
    } else {
        try {
            $pdo->beginTransaction();
            
            $nextMaintenance = null;
            if (!$isStorage && !$isMultiDevice && $lastMaintenance && $maintenanceInterval > 0) {
                $nextMaintenance = calculateNextMaintenance($lastMaintenance, $maintenanceInterval);
            }
            
            // Wenn GPS aktualisiert wird und Marker noch nicht aktiviert war -> aktivieren
            $isActivated = $marker['is_activated'];
            if ($updateGPS && $latitude && $longitude) {
                $isActivated = 1;
            }
            
            $stmt = $pdo->prepare("
                UPDATE markers SET
                    name = ?,
                    category = ?,
                    rental_status = ?,
                    serial_number = ?,
                    is_storage = ?,
                    is_multi_device = ?,
                    is_customer_device = ?,
                    is_repair_device = ?,
                    customer_name = ?,
                    order_number = ?,
                    weclapp_entity_id = ?,
                    repair_description = ?,
                    operating_hours = ?,
                    fuel_level = ?,
                    fuel_unit = ?,
                    fuel_capacity = ?,
                    maintenance_interval_months = ?,
                    last_maintenance = ?,
                    next_maintenance = ?,
                    latitude = ?,
                    longitude = ?,
                    is_activated = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $name,
                $category,
                $rentalStatus,
                $serialNumber,
                $isStorage ? 1 : 0,
                $isMultiDevice ? 1 : 0,
                $isCustomerDevice ? 1 : 0,
                $isRepairDevice ? 1 : 0,
                $customerName,
                $orderNumber,
                $weclappEntityId,
                $repairDescription,
                floatval($operatingHours),
                intval($fuelLevel),
                $fuelUnit,
                $fuelCapacity ? floatval($fuelCapacity) : null,
                intval($maintenanceInterval),
                $lastMaintenance,
                $nextMaintenance,
                $latitude,
                $longitude,
                $isActivated,
                $id
            ]);
            
            // ===== CACHE INVALIDIEREN =====
            global $cache;
            $cache->delete("marker:{$id}");
            $cache->delete("all_markers");
            // ==============================

            // KRITISCH: QR-Code Pool Eintrag auch aktivieren wenn Marker aktiviert wird
            if ($isActivated && !$marker['is_activated']) {
                $stmt = $pdo->prepare("
                    UPDATE qr_code_pool 
                    SET is_activated = 1
                    WHERE qr_code = ?
                ");
                $stmt->execute([$marker['qr_code']]);
                
                // ===== CACHE INVALIDIEREN =====
                global $cache;
                $cache->deletePattern("available_qr_codes");
                // ==============================

                logActivity('qr_activated', "QR-Code '{$marker['qr_code']}' aktiviert durch GPS-Update", $id);
            }
            
            // Custom Fields
            if (!empty($_POST['custom_fields'])) {
                foreach ($_POST['custom_fields'] as $fieldId => $value) {
                    $stmt = $pdo->prepare("
                        INSERT INTO marker_custom_values (marker_id, field_id, field_value)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE field_value = VALUES(field_value)
                    ");
                    $stmt->execute([$id, $fieldId, $value]);
                }
            }
            
            // Öffentliche Dokumente
            if (hasPermission('documents_upload')) {
                $stmt = $pdo->prepare("UPDATE marker_documents SET is_public = 0 WHERE marker_id = ?");
                $stmt->execute([$id]);
                
                if (!empty($_POST['public_docs'])) {
                    foreach ($_POST['public_docs'] as $docId => $value) {
                        $description = $_POST['public_descriptions'][$docId] ?? null;
                        
                        $stmt = $pdo->prepare("
                            UPDATE marker_documents 
                            SET is_public = 1, public_description = ?
                            WHERE id = ? AND marker_id = ?
                        ");
                        $stmt->execute([$description, $docId, $id]);

                        // ===== CACHE INVALIDIEREN (Dokumente geändert) =====
                        global $cache;
                        $cache->delete("marker:{$id}");
                        // ==================================================
                    }
                }
            }
            
            $pdo->commit();
            
            logActivity('marker_updated', "Marker '{$name}' aktualisiert", $id);
            
            // Statusänderung loggen
            if ($rentalStatus !== $marker['rental_status']) {
                $oldStatusLabel = getRentalStatusLabel($marker['rental_status'])['label'];
                $newStatusLabel = getRentalStatusLabel($rentalStatus)['label'];
                logActivity('status_changed', "Status geändert: {$oldStatusLabel} → {$newStatusLabel}", $id);
            }
            
            if ($updateGPS && !$marker['is_activated'] && $isActivated) {
                $message = 'Marker erfolgreich aktualisiert und aktiviert! QR-Code ist jetzt aktiv.';
            } else {
                $message = 'Marker erfolgreich aktualisiert!';
            }
            $messageType = 'success';
            
            $marker = getMarkerById($id, $pdo);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Fehler: ' . htmlspecialchars($e->getMessage());
            $messageType = 'danger';
        }
    }
}

// DGUV/UVV/TÜV Prüfungen laden
$stmt = $pdo->prepare("
    SELECT * FROM inspection_schedules 
    WHERE marker_id = ? 
    ORDER BY next_inspection ASC
");
$stmt->execute([$id]);
$inspections = $stmt->fetchAll();

$customFields = $pdo->query("SELECT * FROM custom_fields ORDER BY display_order, id")->fetchAll();
$customValues = [];
if (!empty($customFields)) {
    $stmt = $pdo->prepare("SELECT field_id, field_value FROM marker_custom_values WHERE marker_id = ?");
    $stmt->execute([$id]);
    $customValues = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$settings = getSystemSettings();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marker bearbeiten - <?= htmlspecialchars($marker['name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="css/dark-mode.css">
    <link rel="stylesheet" href="css/mobile-features.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .qr-code-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .qr-code-display code {
            font-size: 24px;
            font-weight: bold;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            display: inline-block;
            margin: 10px 0;
        }
        
        .qr-code-display .activation-status {
            margin-top: 10px;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            display: inline-block;
        }
        
        .qr-code-display .activation-status.active {
            background: #28a745;
        }
        
        .qr-code-display .activation-status.inactive {
            background: #ffc107;
            color: #333;
        }
        
        .document-item {
            background: var(--bg-secondary);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .document-item.public {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        body.dark-mode .document-item.public {
            background: #1a4d2e;
        }
        
        .public-toggle {
            margin: 10px 0;
            padding: 10px;
            background: var(--card-bg);
            border-radius: 5px;
        }
        
        .gps-section {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .gps-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .gps-coordinates {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        #editMap {
            height: 400px;
            border-radius: 8px;
            border: 2px solid var(--border-color);
            margin-top: 15px;
        }
        
        /* DGUV/UVV/TÜV Styles */
        .inspection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .inspection-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .inspection-card:hover {
            transform: translateY(-5px);
        }
        
        .inspection-card.overdue {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        
        .inspection-card.due-soon {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        
        .inspection-card.ok {
            border-left-color: #28a745;
        }
        
        body.dark-mode .inspection-card {
            background: #2d2d2d;
        }
        
        body.dark-mode .inspection-card.overdue {
            background: #4d1a1a;
        }
        
        body.dark-mode .inspection-card.due-soon {
            background: #4d3d1a;
        }
        
        .inspection-type {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .inspection-date {
            font-size: 13px;
            color: #6c757d;
            margin: 5px 0;
        }
        
        .inspection-status {
            margin-top: 15px;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }
        
        .inspection-actions {
            margin-top: 15px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-primary {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
        }
        
        /* Animation für GPS-Status Updates */
        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Karten-Cursor Hinweis */
        #editMap {
            position: relative;
        }
        
        #editMap:hover::after {
            content: "Klicke auf die Karte oder ziehe den Marker";
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 12px;
            pointer-events: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-edit"></i> Marker bearbeiten</h1>
                        <p><?= htmlspecialchars($marker['name']) ?></p>
                    </div>
                    <a href="view_marker.php?id=<?= $marker['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück zum Marker
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <div class="qr-code-display">
                <div><i class="fas fa-qrcode" style="font-size: 36px;"></i></div>
                <strong>QR-Code:</strong>
                <code><?= htmlspecialchars($marker['qr_code']) ?></code>
                
                <div class="activation-status <?= $marker['is_activated'] ? 'active' : 'inactive' ?>">
                    <?php if ($marker['is_activated']): ?>
                        <i class="fas fa-check-circle"></i> Aktiviert
                    <?php else: ?>
                        <i class="fas fa-clock"></i> Wartet auf Aktivierung (GPS scannen)
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 15px;">
                    <span style="opacity: 0.9;">
                        <i class="fas fa-info-circle"></i> QR-Codes können nicht geändert werden
                    </span>
                    <a href="print_qr.php?id=<?= $marker['id'] ?>" class="btn btn-light btn-sm" target="_blank" style="margin-left: 15px;">
                        <i class="fas fa-print"></i> QR-Code drucken
                    </a>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="marker-form">
                <?= csrf_field() ?>
                
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Grunddaten</h2>
                    
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($marker['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Kategorie</label>
                        <select id="category" name="category">
                            <option value="">-- Keine --</option>
                            <option value="Generator" <?= $marker['category'] === 'Generator' ? 'selected' : '' ?>>Generator</option>
                            <option value="Kompressor" <?= $marker['category'] === 'Kompressor' ? 'selected' : '' ?>>Kompressor</option>
                            <option value="Pumpe" <?= $marker['category'] === 'Pumpe' ? 'selected' : '' ?>>Pumpe</option>
                            <option value="Fahrzeug" <?= $marker['category'] === 'Fahrzeug' ? 'selected' : '' ?>>Fahrzeug</option>
                            <option value="Werkzeug" <?= $marker['category'] === 'Werkzeug' ? 'selected' : '' ?>>Werkzeug</option>
                            <option value="Lager" <?= $marker['category'] === 'Lager' ? 'selected' : '' ?>>Lager</option>
                            <option value="Sonstiges" <?= $marker['category'] === 'Sonstiges' ? 'selected' : '' ?>>Sonstiges</option>
                        </select>
                    </div>
                </div>

                <?php if (!$marker['is_multi_device'] && !$marker['is_storage']): ?>
                <!-- Status ändern -->
                <div class="form-section">
                    <h2><i class="fas fa-exchange-alt"></i> Status</h2>
                    
                    <?php
                    $statusInfo = getRentalStatusLabel($marker['rental_status']);
                    ?>
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Aktueller Status:</strong> 
                        <span class="badge badge-<?= $statusInfo['class'] ?>" style="font-size: 16px; padding: 8px 15px;">
                            <?= $statusInfo['label'] ?>
                        </span>
                    </div>
                    
                    <div class="form-group">
                        <label for="rental_status">Status ändern</label>
                        <select id="rental_status" name="rental_status" class="form-control">
                            <?php if ($marker['rental_status'] === 'verfuegbar'): ?>
                                <option value="verfuegbar" selected>✅ Verfügbar</option>
                                <option value="vermietet">🔴 Vermietet</option>
                                <option value="wartung">🔧 Wartung</option>
                            <?php elseif ($marker['rental_status'] === 'vermietet'): ?>
                                <option value="verfuegbar">✅ Verfügbar</option>
                                <option value="vermietet" selected>🔴 Vermietet</option>
                                <option value="wartung">🔧 Wartung</option>
                            <?php else: ?>
                                <option value="verfuegbar">✅ Verfügbar</option>
                                <option value="vermietet">🔴 Vermietet</option>
                                <option value="wartung" selected>🔧 Wartung</option>
                            <?php endif; ?>
                        </select>
                        <small>Status des Geräts (Verfügbar ⇄ Vermietet möglich)</small>
                    </div>
                </div>

                <div class="form-section">
                    <h2><i class="fas fa-cog"></i> Gerätedaten</h2>
                    
                    <div class="form-group">
                        <label for="serial_number">Seriennummer</label>
                        <input type="text" id="serial_number" name="serial_number" 
                               value="<?= htmlspecialchars($marker['serial_number']) ?>"
                               maxlength="100"
                               placeholder="z.B. ABC-123, GEN/2024-001">
                        <small>Erlaubt: Buchstaben, Zahlen und Sonderzeichen (- _ . / ( ) : , + * #)</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="operating_hours">Betriebsstunden</label>
                            <input type="number" id="operating_hours" name="operating_hours" 
                                   value="<?= $marker['operating_hours'] ?>" step="0.01" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="fuel_level">Kraftstofffüllstand</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" id="fuel_level" name="fuel_level" min="0" 
                                       value="<?= intval($marker['fuel_level'] ?? 0) ?>" style="flex: 1;">
                                <select id="fuel_unit" name="fuel_unit" style="width: 120px;" onchange="toggleFuelCapacity()">
                                    <option value="percent" <?= ($marker['fuel_unit'] ?? 'percent') === 'percent' ? 'selected' : '' ?>>Prozent (%)</option>
                                    <option value="liter" <?= ($marker['fuel_unit'] ?? 'percent') === 'liter' ? 'selected' : '' ?>>Liter (L)</option>
                                </select>
                            </div>
                            <small>Aktueller Füllstand des Kraftstofftanks</small>
                        </div>
                    </div>
                    
                    <div id="fuel-capacity-field" style="display: <?= ($marker['fuel_unit'] ?? 'percent') === 'liter' ? 'block' : 'none' ?>; margin-top: -10px;">
                        <div class="form-group">
                            <label for="fuel_capacity">Tank-Kapazität (Liter)</label>
                            <input type="number" id="fuel_capacity" name="fuel_capacity" min="1" step="0.1" 
                                   value="<?= htmlspecialchars($marker['fuel_capacity'] ?? '') ?>" placeholder="z.B. 50">
                            <small>Gesamt-Kapazität des Tanks für Prozent-Umrechnung</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                </div>
            
                <!-- GPS-Position Section -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt"></i> GPS-Position</h2>
                    
                    <?php if ($marker['is_activated'] && $marker['latitude'] && $marker['longitude']): ?>
                        <div class="alert alert-info">
                            <strong><i class="fas fa-check-circle"></i> Aktuelle Position:</strong>
                            <div class="gps-coordinates">
                                <i class="fas fa-map-pin"></i> 
                                <?= number_format($marker['latitude'], 6) ?>, <?= number_format($marker['longitude'], 6) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> Marker nicht aktiviert</strong>
                            <p>Dieser Marker hat noch keine GPS-Position. Erfassen Sie die Position vor Ort um den QR-Code zu aktivieren!</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="gps-section">
                        <div class="gps-info">
                            <div>
                                <h3 style="margin: 0 0 10px 0;"><i class="fas fa-crosshairs"></i> Position aktualisieren</h3>
                                <p style="color: var(--text-secondary); margin: 0;">
                                    Ändern Sie die Position, wenn das Gerät umgestellt wurde.
                                    <?php if (!$marker['is_activated']): ?>
                                        <strong>Dies aktiviert auch den QR-Code!</strong>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <button type="button" class="gps-button" onclick="captureGPS()">
                                <i class="fas fa-satellite-dish"></i> GPS erfassen
                            </button>
                        </div>
                        
                        <div class="alert alert-success" style="margin-top: 15px;">
                            <strong><i class="fas fa-hand-pointer"></i> 3 Möglichkeiten die Position zu setzen:</strong><br>
                            <small>
                                1️⃣ <strong>GPS-Button</strong> klicken für automatische Erfassung (±5-20m Genauigkeit)<br>
                                2️⃣ <strong>Marker auf der Karte ziehen</strong> (Drag & Drop) für präzise Positionierung<br>
                                3️⃣ <strong>Auf die Karte klicken</strong> um den Marker zu verschieben<br>
                                4️⃣ <strong>Koordinaten manuell eingeben</strong> in die Felder unten
                            </small>
                        </div>
                        
                        <!-- Karte zur Positionsauswahl -->
                        <div id="editMap" style="height: 400px; border-radius: 8px; border: 2px solid #dee2e6; margin: 15px 0; cursor: crosshair;"></div>
                        
                        <div class="form-row" style="margin-top: 15px;">
                            <div class="form-group">
                                <label for="latitude">
                                    <i class="fas fa-map-marker-alt"></i> Breitengrad
                                    <small style="color: var(--text-secondary); font-weight: normal;">(manuell editierbar)</small>
                                </label>
                                <input type="number" id="latitude" name="latitude" 
                                       value="<?= $marker['latitude'] ?>" 
                                       step="0.000001" 
                                       placeholder="z.B. 49.995567"
                                       onchange="updateMapFromInputs()">
                            </div>
                            
                            <div class="form-group">
                                <label for="longitude">
                                    <i class="fas fa-map-marker-alt"></i> Längengrad
                                    <small style="color: var(--text-secondary); font-weight: normal;">(manuell editierbar)</small>
                                </label>
                                <input type="number" id="longitude" name="longitude" 
                                       value="<?= $marker['longitude'] ?>" 
                                       step="0.000001" 
                                       placeholder="z.B. 9.073127"
                                       onchange="updateMapFromInputs()">
                            </div>
                        </div>
                        
                        <input type="hidden" id="update_gps" name="update_gps" value="0">
                        
                        <div id="gps-status" class="gps-status"></div>
                    </div>
                </div>
                
                <!-- Gerätetyp-Auswahl -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Marker-Information</h2>
                    <div class="alert alert-info">
                        <strong>Marker-Typ:</strong>
                        <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                            <span class="badge badge-info">
                                <i class="fas fa-wifi"></i> NFC-Chip
                            </span>
                            <br>
                            <strong>NFC-Chip-ID:</strong> <code><?= e($marker['nfc_chip_id']) ?></code>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Backup QR-Code: <?= e($marker['qr_code']) ?>
                            </small>
                        <?php else: ?>
                            <span class="badge badge-primary">
                                <i class="fas fa-qrcode"></i> QR-Code
                            </span>
                            <br>
                            <strong>QR-Code:</strong> <code><?= e($marker['qr_code']) ?></code>
                        <?php endif; ?>
                        <br><br>
                        <small class="text-muted">
                            <i class="fas fa-lock"></i> Der Marker-Typ kann nach der Erstellung nicht mehr geändert werden.
                        </small>
                    </div>

                    <h2><i class="fas fa-tag"></i> Gerätetyp</h2>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Hinweis:</strong> Wählen Sie den Typ des Geräts. Ein Gerät kann nur einen Typ haben.
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_storage" name="is_storage" 
                                <?= $marker['is_storage'] ? 'checked' : '' ?> onchange="toggleDeviceTypeFields()">
                            <i class="fas fa-warehouse"></i> <strong>Lagergerät</strong>
                        </label>
                        <small>Gerät ist ein Lagerort für Materialien (hat keinen Status)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_multi_device" name="is_multi_device" 
                                <?= $marker['is_multi_device'] ? 'checked' : '' ?> onchange="toggleDeviceTypeFields()">
                            <i class="fas fa-boxes"></i> <strong>Multi-Gerät</strong>
                        </label>
                        <small>Mehrere identische Geräte unter einem QR-Code (z.B. Kabeltrommel-Set)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_customer_device" name="is_customer_device" 
                                <?= $marker['is_customer_device'] ? 'checked' : '' ?> onchange="toggleDeviceTypeFields()">
                            <i class="fas fa-user"></i> <strong>Kundengerät</strong>
                        </label>
                        <small>Gerät gehört einem Kunden und befindet sich in Bearbeitung/Reparatur</small>
                    </div>
                    
                    <div id="customer-fields" style="display: <?= $marker['is_customer_device'] ? 'block' : 'none' ?>; margin-top: 15px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                        <div class="form-group">
                            <label for="customer_name">Kundenname *</label>
                            <input type="text" id="customer_name" name="customer_name" 
                                value="<?= htmlspecialchars($marker['customer_name'] ?? '') ?>"
                                placeholder="Name des Kunden">
                        </div>
                        
                        <div class="form-group">
                            <label for="order_number">Auftragsnummer</label>
                            <input type="text" id="order_number" name="order_number" 
                                value="<?= htmlspecialchars($marker['order_number'] ?? '') ?>"
                                placeholder="Optional">
                            <small>Interne Auftragsnummer zur Nachverfolgung</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="weclapp_entity_id">
                                <i class="fas fa-link"></i> Weclapp Entity-ID
                            </label>
                            <input type="text" id="weclapp_entity_id" name="weclapp_entity_id" 
                                value="<?= htmlspecialchars($marker['weclapp_entity_id'] ?? '') ?>"
                                placeholder="z.B. 12345">
                            <small>
                                <i class="fas fa-info-circle"></i> 
                                Die Entity-ID aus Weclapp für direkte Verlinkung zum Auftrag. 
                                <br>
                                <strong>Wie finde ich die Entity-ID?</strong> 
                                Öffnen Sie den Auftrag in Weclapp und schauen Sie in die Browser-URL. 
                                Die Zahl am Ende ist die Entity-ID.
                                <br>
                                <em>Beispiel:</em> https://IhrTenant.weclapp.com/webapp/.../salesOrder/id/<strong>12345</strong>
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 15px;">
                        <label>
                            <input type="checkbox" id="is_repair_device" name="is_repair_device" 
                                <?= $marker['is_repair_device'] ? 'checked' : '' ?> onchange="toggleDeviceTypeFields()">
                            <i class="fas fa-tools"></i> <strong>Reparatur-Gerät</strong>
                        </label>
                        <small>Gerät befindet sich in Reparatur</small>
                    </div>
                    
                    <div id="repair-fields" style="display: <?= $marker['is_repair_device'] ? 'block' : 'none' ?>; margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                        <div class="form-group">
                            <label for="repair_description">Reparatur-Beschreibung</label>
                            <textarea id="repair_description" name="repair_description" rows="3"><?= htmlspecialchars($marker['repair_description'] ?? '') ?></textarea>
                            <small>Was wird repariert?</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-wrench"></i> Wartung</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="maintenance_interval">Wartungsintervall (Monate)</label>
                            <input type="number" id="maintenance_interval" name="maintenance_interval" 
                                   value="<?= $marker['maintenance_interval_months'] ?>" min="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_maintenance">Letzte Wartung</label>
                            <input type="date" id="last_maintenance" name="last_maintenance" 
                                   value="<?= $marker['last_maintenance'] ?>">
                        </div>
                    </div>
                </div>

                <h2>
                    <i class="fas fa-clipboard-check"></i> DGUV / UVV / TÜV Prüfungen
                    <a href="add_inspection.php?marker_id=<?= $marker['id'] ?>" class="btn btn-sm btn-success" style="float: right;">
                        <i class="fas fa-plus"></i> Prüfung hinzufügen
                    </a>
                </h2>
                <!-- DGUV/UVV/TÜV Prüfungen -->
                <?php if (!$marker['is_storage'] && !$marker['is_multi_device']): ?>
                <div class="form-section">
                    <?php if (!empty($inspections)): ?>
                        <div class="inspection-grid">
                            <?php foreach ($inspections as $inspection): 
                                $daysUntil = $inspection['next_inspection'] 
                                    ? (strtotime($inspection['next_inspection']) - time()) / (60 * 60 * 24) 
                                    : 999;
                                
                                $statusClass = 'ok';
                                $statusText = 'Aktuell';
                                $statusBadge = 'success';
                                
                                if ($daysUntil < 0) {
                                    $statusClass = 'overdue';
                                    $statusText = 'ÜBERFÄLLIG!';
                                    $statusBadge = 'danger';
                                } elseif ($daysUntil <= 30) {
                                    $statusClass = 'due-soon';
                                    $statusText = 'Bald fällig';
                                    $statusBadge = 'warning';
                                }
                            ?>
                            <div class="inspection-card <?= $statusClass ?>">
                                <div class="inspection-type">
                                    <i class="fas fa-certificate"></i>
                                    <?= htmlspecialchars($inspection['inspection_type']) ?>
                                </div>
                                
                                <?php if ($inspection['last_inspection']): ?>
                                <div class="inspection-date">
                                    <i class="fas fa-check"></i> Letzte Prüfung: 
                                    <strong><?= formatDate($inspection['last_inspection']) ?></strong>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($inspection['next_inspection']): ?>
                                <div class="inspection-date">
                                    <i class="fas fa-calendar"></i> Nächste Prüfung: 
                                    <strong><?= formatDate($inspection['next_inspection']) ?></strong>
                                    <?php if ($daysUntil < 999): ?>
                                        (<?= $daysUntil < 0 ? 'vor ' . abs(round($daysUntil)) : 'in ' . round($daysUntil) ?> Tagen)
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($inspection['inspection_authority']): ?>
                                <div class="inspection-date">
                                    <i class="fas fa-building"></i> Prüfstelle: 
                                    <?= htmlspecialchars($inspection['inspection_authority']) ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($inspection['certificate_number']): ?>
                                <div class="inspection-date">
                                    <i class="fas fa-file-alt"></i> Zertifikat: 
                                    <?= htmlspecialchars($inspection['certificate_number']) ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="inspection-status badge-<?= $statusBadge ?>">
                                    <?= $statusText ?>
                                </div>
                                
                                <div class="inspection-actions">
                                    <a href="complete_inspection.php?id=<?= $inspection['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Durchführen
                                    </a>
                                    <a href="edit_inspection.php?id=<?= $inspection['id'] ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Bearbeiten
                                    </a>
                                    <a href="delete_inspection.php?id=<?= $inspection['id'] ?>&marker_id=<?= $marker['id'] ?>" 
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Prüfung wirklich löschen?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Noch keine Prüfungen hinterlegt. Klicken Sie auf "Prüfung hinzufügen" um die erste Prüfung anzulegen.
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php endif; ?>
                
                <!-- Custom Fields -->
                <?php if (!empty($customFields)): ?>
                <div class="form-section">
                    <h2><i class="fas fa-list"></i> Zusätzliche Informationen</h2>
                    
                    <?php foreach ($customFields as $field): ?>
                        <div class="form-group">
                            <label for="custom_<?= $field['id'] ?>">
                                <?= htmlspecialchars($field['field_label']) ?>
                                <?php if ($field['required']): ?>
                                    <span style="color: red;">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php $value = $customValues[$field['id']] ?? ''; ?>
                            
                            <?php if ($field['field_type'] === 'textarea'): ?>
                                <textarea id="custom_<?= $field['id'] ?>" 
                                        name="custom_fields[<?= $field['id'] ?>]" 
                                        rows="4"
                                        <?= $field['required'] ? 'required' : '' ?>><?= htmlspecialchars($value) ?></textarea>
                            
                            <?php elseif ($field['field_type'] === 'number'): ?>
                                <input type="number" 
                                    id="custom_<?= $field['id'] ?>" 
                                    name="custom_fields[<?= $field['id'] ?>]"
                                    value="<?= htmlspecialchars($value) ?>"
                                    step="any"
                                    <?= $field['required'] ? 'required' : '' ?>>
                            
                            <?php elseif ($field['field_type'] === 'date'): ?>
                                <input type="date" 
                                    id="custom_<?= $field['id'] ?>" 
                                    name="custom_fields[<?= $field['id'] ?>]"
                                    value="<?= htmlspecialchars($value) ?>"
                                    <?= $field['required'] ? 'required' : '' ?>>
                            
                            <?php else: ?>
                                <input type="text" 
                                    id="custom_<?= $field['id'] ?>" 
                                    name="custom_fields[<?= $field['id'] ?>]"
                                    value="<?= htmlspecialchars($value) ?>"
                                    <?= $field['required'] ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- 3D-Modelle -->
                <div class="form-section">
                    <h2><i class="fas fa-cube"></i> 3D-Modelle</h2>
                    
                    <?php
                    // Lade vorhandene 3D-Modelle
                    $stmt = $pdo->prepare("
                        SELECT * FROM marker_3d_models 
                        WHERE marker_id = ? 
                        ORDER BY uploaded_at DESC
                    ");
                    $stmt->execute([$marker['id']]);
                    $models_3d = $stmt->fetchAll();
                    ?>
                    
                    <?php if (!empty($models_3d)): ?>
                        <div style="margin-bottom: 20px;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 15px;">Vorhandene 3D-Modelle</h3>
                            <?php foreach ($models_3d as $model): ?>
                                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?= htmlspecialchars($model['model_name']) ?></strong>
                                        <br>
                                        <small style="color: #666;">
                                            <i class="fas fa-file"></i> <?= strtoupper($model['file_format']) ?> | 
                                            <i class="fas fa-calendar"></i> <?= date('d.m.Y H:i', strtotime($model['uploaded_at'])) ?>
                                            <?php if ($model['is_public']): ?>
                                                | <i class="fas fa-eye" style="color: #28a745;"></i> Öffentlich
                                            <?php else: ?>
                                                | <i class="fas fa-eye-slash" style="color: #999;"></i> Privat
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <button type="button" onclick="toggle3DPublic(<?= $model['id'] ?>, <?= $model['is_public'] ? 'false' : 'true' ?>)" 
                                                class="btn btn-sm btn-secondary" style="margin-right: 5px;">
                                            <i class="fas fa-<?= $model['is_public'] ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                        <button type="button" onclick="delete3DModel(<?= $model['id'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Noch keine 3D-Modelle vorhanden. Erstellen Sie ein 3D-Modell oder laden Sie eines hoch.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Mobile 3D-Erfassung (nur auf Mobilgeräten) -->
                    <div id="mobile-3d-section" style="display: none; margin: 20px 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
                        <h3 style="color: white; margin-bottom: 10px;">
                            <i class="fas fa-mobile-alt"></i> 3D-Modell mit Smartphone erstellen
                        </h3>
                        <p style="margin-bottom: 15px; opacity: 0.9;">
                            Erstellen Sie ein 360°-3D-Modell dieses Geräts direkt mit Ihrem Smartphone.
                        </p>
                        <a href="mobile_3d_capture.php?marker_id=<?= $marker['id'] ?>" 
                           class="btn" style="background: white; color: #667eea; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-camera"></i>
                            Jetzt 3D-Erfassung starten
                        </a>
                    </div>
                    
                    <!-- Desktop: Datei-Upload für fertige 3D-Modelle -->
                    <div id="desktop-3d-section">
                        <h3 style="font-size: 1.1rem; margin: 20px 0 15px 0;">3D-Modell hochladen</h3>
                        <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">
                            <div class="form-group">
                                <label for="model_name">Modell-Name</label>
                                <input type="text" id="model_name" placeholder="z.B. Produktansicht 2024">
                            </div>
                            
                            <div class="form-group">
                                <label for="model_file">3D-Modell-Datei</label>
                                <input type="file" id="model_file" accept=".glb,.gltf,.obj,.fbx,.usdz">
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Unterstützte Formate: GLB, GLTF, OBJ, FBX, USDZ (max. 50MB)
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="model_public" checked>
                                    In Messe-Ansicht öffentlich anzeigen
                                </label>
                            </div>
                            
                            <button type="button" onclick="upload3DModel()" class="btn btn-primary">
                                <i class="fas fa-upload"></i> 3D-Modell hochladen
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-save"></i> Änderungen speichern
                    </button>
                    <a href="view_marker.php?id=<?= $marker['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="js/gps-helper.js"></script>
    
    <script>
        // Karte initialisieren
        const currentLat = <?= $marker['latitude'] ?: $settings['map_default_lat'] ?>;
        const currentLng = <?= $marker['longitude'] ?: $settings['map_default_lng'] ?>;
        
        const editMap = L.map('editMap').setView([currentLat, currentLng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(editMap);
        
        let marker = L.marker([currentLat, currentLng], { 
            draggable: true,
            autoPan: true
        }).addTo(editMap);
        
        // Tooltip für Marker
        marker.bindTooltip("Ziehe mich oder klicke auf die Karte!", { 
            permanent: false, 
            direction: 'top' 
        });
        
        // Marker-Position bei Drag aktualisieren
        marker.on('dragstart', function(e) {
            console.log('🖱️ Marker wird gezogen...');
        });
        
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            console.log('📍 Neue Position:', pos.lat, pos.lng);
            document.getElementById('latitude').value = pos.lat.toFixed(6);
            document.getElementById('longitude').value = pos.lng.toFixed(6);
            document.getElementById('update_gps').value = '1';
            
            // Erfolgsanzeige
            showPositionUpdate('Marker verschoben', pos.lat, pos.lng);
        });
        
        // Bei Klick auf Karte Marker verschieben
        editMap.on('click', function(e) {
            console.log('🗺️ Karte geklickt:', e.latlng.lat, e.latlng.lng);
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
            document.getElementById('update_gps').value = '1';
            
            // Erfolgsanzeige
            showPositionUpdate('Position gesetzt', e.latlng.lat, e.latlng.lng);
        });
        
        // Funktion um Karte bei manueller Eingabe zu aktualisieren
        function updateMapFromInputs() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                console.log('⌨️ Manuelle Eingabe:', lat, lng);
                marker.setLatLng([lat, lng]);
                editMap.setView([lat, lng], 16);
                document.getElementById('update_gps').value = '1';
                
                showPositionUpdate('Koordinaten manuell eingegeben', lat, lng);
            } else {
                alert('Ungültige Koordinaten!\nBreitengrad: -90 bis 90\nLängengrad: -180 bis 180');
            }
        }
        
        // Erfolgsanzeige für Position-Updates
        function showPositionUpdate(method, lat, lng) {
            const statusDiv = document.getElementById('gps-status');
            statusDiv.innerHTML = `
                <div style="padding: 12px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 5px; margin: 10px 0; color: #155724; animation: slideIn 0.3s;">
                    <i class="fas fa-check-circle"></i> <strong>${method}</strong>
                    <br><small>Position: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                    <br><small style="color: #28a745;"><i class="fas fa-save"></i> Klicke "Speichern" um die neue Position zu übernehmen</small>
                </div>
            `;
            
            // Auto-Hide nach 5 Sekunden
            setTimeout(() => {
                statusDiv.innerHTML = '';
            }, 5000);
        }
        
        // GPS-Erfassung
        function captureGPS() {
            const gpsHelper = new GPSHelper();
            const button = document.querySelector('.gps-button');
            const statusDiv = document.getElementById('gps-status');
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Erfasse Position...';
            button.disabled = true;
            
            gpsHelper.getCurrentPosition(
                (position) => {
                    document.getElementById('latitude').value = position.lat.toFixed(6);
                    document.getElementById('longitude').value = position.lng.toFixed(6);
                    document.getElementById('update_gps').value = '1';
                    
                    marker.setLatLng([position.lat, position.lng]);
                    editMap.setView([position.lat, position.lng], 16);
                    
                    const isActivated = <?= $marker['is_activated'] ? 'true' : 'false' ?>;
                    const activationText = isActivated ? '' : '<br><strong>Der QR-Code wird beim Speichern aktiviert!</strong>';
                    
                    statusDiv.innerHTML = `
                        <div style="padding: 10px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 5px; margin: 10px 0; color: #155724;">
                            <i class="fas fa-check-circle"></i> GPS-Position erfasst!
                            <br><small>Genauigkeit: ${position.accuracy.toFixed(0)}m</small>
                            ${activationText}
                        </div>
                    `;
                    
                    button.innerHTML = '<i class="fas fa-check"></i> Position erfasst';
                    button.style.background = '#28a745';
                    
                    setTimeout(() => {
                        button.innerHTML = '<i class="fas fa-satellite-dish"></i> GPS erfassen';
                        button.style.background = '';
                        button.disabled = false;
                    }, 2000);
                },
                (error) => {
                    statusDiv.innerHTML = `
                        <div style="padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 5px; margin: 10px 0; color: #721c24;">
                            <i class="fas fa-exclamation-circle"></i> ${error}
                        </div>
                    `;
                    button.innerHTML = '<i class="fas fa-satellite-dish"></i> GPS erfassen';
                    button.disabled = false;
                }
            );
        }
        
        function togglePublicDescription(docId) {
            const checkbox = document.getElementById('public_' + docId);
            const descDiv = document.getElementById('public_desc_' + docId);
            descDiv.style.display = checkbox.checked ? 'block' : 'none';
        }
        
        function deleteDocument(docId) {
            if (confirm('Dokument wirklich löschen?')) {
                window.location.href = 'delete_document.php?id=' + docId + '&marker_id=<?= $marker['id'] ?>';
            }
        }
        
        // Reparatur-Felder Toggle
        function toggleRepairFields() {
            const isRepair = document.getElementById('is_repair_device');
            const repairFields = document.getElementById('repair-fields');
            
            if (isRepair && repairFields) {
                repairFields.style.display = isRepair.checked ? 'block' : 'none';
            }
        }
        
        // Kraftstoff-Kapazität Toggle
        function toggleFuelCapacity() {
            const fuelUnit = document.getElementById('fuel_unit');
            const capacityField = document.getElementById('fuel-capacity-field');
            
            if (fuelUnit && capacityField) {
                capacityField.style.display = (fuelUnit.value === 'liter') ? 'block' : 'none';
            }
        }
        
        // Fuel Level Max-Wert anpassen
        document.addEventListener('DOMContentLoaded', function() {
            const fuelLevelInput = document.getElementById('fuel_level');
            const fuelUnitSelect = document.getElementById('fuel_unit');
            
            if (fuelLevelInput && fuelUnitSelect) {
                function updateFuelLevelConstraints() {
                    if (fuelUnitSelect.value === 'percent') {
                        fuelLevelInput.max = 100;
                        fuelLevelInput.step = 1;
                    } else {
                        fuelLevelInput.max = 999;
                        fuelLevelInput.step = 0.1;
                    }
                }
                
                fuelUnitSelect.addEventListener('change', updateFuelLevelConstraints);
                updateFuelLevelConstraints(); // Initial call
            }
            
            // Toggles initialisieren
            if (typeof toggleFuelCapacity === 'function') {
                toggleFuelCapacity();
            }
            <?php if ($marker['is_repair_device']): ?>
            if (typeof toggleRepairFields === 'function') {
                toggleRepairFields();
            }
            <?php endif; ?>
        });

        // Toggle-Funktionen für Gerätetypen
        function toggleDeviceTypeFields() {
            const isStorage = document.getElementById('is_storage');
            const isMultiDevice = document.getElementById('is_multi_device');
            const isCustomer = document.getElementById('is_customer_device');
            const isRepair = document.getElementById('is_repair_device');
            const customerFields = document.getElementById('customer-fields');
            const repairFields = document.getElementById('repair-fields');
            
            // Wenn Lagergerät aktiviert wird, alle anderen deaktivieren
            if (isStorage && isStorage.checked) {
                if (isMultiDevice) isMultiDevice.checked = false;
                if (isCustomer) isCustomer.checked = false;
                if (isRepair) isRepair.checked = false;
                if (customerFields) customerFields.style.display = 'none';
                if (repairFields) repairFields.style.display = 'none';
            }
            // Wenn Multi-Gerät aktiviert wird, alle anderen deaktivieren
            else if (isMultiDevice && isMultiDevice.checked) {
                if (isStorage) isStorage.checked = false;
                if (isCustomer) isCustomer.checked = false;
                if (isRepair) isRepair.checked = false;
                if (customerFields) customerFields.style.display = 'none';
                if (repairFields) repairFields.style.display = 'none';
            }
            // Wenn Kundengerät aktiviert wird, Reparaturgerät deaktivieren
            else if (isCustomer && isCustomer.checked) {
                if (isRepair) isRepair.checked = false;
                if (customerFields) customerFields.style.display = 'block';
                if (repairFields) repairFields.style.display = 'none';
            }
            // Wenn Reparaturgerät aktiviert wird, Kundengerät deaktivieren
            else if (isRepair && isRepair.checked) {
                if (isCustomer) isCustomer.checked = false;
                if (repairFields) repairFields.style.display = 'block';
                if (customerFields) customerFields.style.display = 'none';
            }
            // Wenn nichts aktiviert ist, alle Felder ausblenden
            else {
                if (customerFields) customerFields.style.display = 'none';
                if (repairFields) repairFields.style.display = 'none';
            }
        }

        // Erweiterte Toggle-Funktion für Reparatur-Felder (wird vom alten Code noch aufgerufen)
        function toggleRepairFields() {
            toggleDeviceTypeFields();
        }

        // Bei Seitenload initialisieren
        document.addEventListener('DOMContentLoaded', function() {
            toggleDeviceTypeFields();
            check3DMobileDevice();
        });
        
        // ===== 3D-MODELL FUNKTIONEN =====
        
        // Prüfe ob mobiles Gerät
        function check3DMobileDevice() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const mobileSection = document.getElementById('mobile-3d-section');
            const desktopSection = document.getElementById('desktop-3d-section');
            
            if (isMobile) {
                if (mobileSection) mobileSection.style.display = 'block';
                if (desktopSection) desktopSection.style.display = 'none';
            } else {
                if (mobileSection) mobileSection.style.display = 'none';
                if (desktopSection) desktopSection.style.display = 'block';
            }
        }
        
        // 3D-Modell hochladen
        function upload3DModel() {
            const fileInput = document.getElementById('model_file');
            const nameInput = document.getElementById('model_name');
            const publicCheckbox = document.getElementById('model_public');
            
            if (!fileInput.files.length) {
                alert('Bitte wählen Sie eine 3D-Modell-Datei aus.');
                return;
            }
            
            const file = fileInput.files[0];
            const maxSize = 50 * 1024 * 1024; // 50MB
            
            if (file.size > maxSize) {
                alert('Die Datei ist zu groß. Maximal 50MB erlaubt.');
                return;
            }
            
            const formData = new FormData();
            formData.append('marker_id', '<?= $marker['id'] ?>');
            formData.append('model_name', nameInput.value || file.name);
            formData.append('model_file', file);
            formData.append('is_public', publicCheckbox.checked ? '1' : '0');
            
            // CSRF-Token hinzufügen
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            formData.append('csrf_token', csrfToken);
            
            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird hochgeladen...';
            
            fetch('upload_3d_model.php', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('3D-Modell erfolgreich hochgeladen!');
                    location.reload();
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-upload"></i> 3D-Modell hochladen';
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Es ist ein Fehler aufgetreten.');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-upload"></i> 3D-Modell hochladen';
            });
        }
        
        // 3D-Modell löschen
        function delete3DModel(modelId) {
            if (!confirm('3D-Modell wirklich löschen?')) {
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            fetch('delete_3d_model.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    model_id: modelId,
                    csrf_token: csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('3D-Modell wurde gelöscht.');
                    location.reload();
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Es ist ein Fehler aufgetreten.');
            });
        }
        
        // 3D-Modell öffentlich/privat umschalten
        function toggle3DPublic(modelId, makePublic) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            fetch('toggle_3d_public.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    model_id: modelId,
                    is_public: makePublic,
                    csrf_token: csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Es ist ein Fehler aufgetreten.');
            });
        }
    </script>
    
    <script src="js/dark-mode.js"></script>
</body>
</html>