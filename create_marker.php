<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('markers_create');

// Mobilen Zugriff blockieren
if (isMobileDevice()) {
    header('Location: scan.php?mobile_blocked=1');
    exit;
}

$message = '';
$messageType = '';
$settings = getSystemSettings();

// Verfügbare QR-Codes und NFC-Chips abrufen
$availableQRCodes = getAvailableQRCodes($pdo, 100);
$availableNFCChips = getAvailableNFCChips($pdo, 100);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    // NEUE FELDER: Marker-Typ
    $markerType = $_POST['marker_type'] ?? 'qr_code';
    $qrCode = trim($_POST['qr_code'] ?? '');
    $nfcChipId = trim($_POST['nfc_chip_id'] ?? '');
    
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $isStorage = isset($_POST['is_storage']);
    $isMultiDevice = isset($_POST['is_multi_device']);
    $isCustomerDevice = isset($_POST['is_customer_device']);
    $isRepairDevice = isset($_POST['is_repair_device']);
    
    // Kundendaten
    $customerName = $isCustomerDevice ? trim($_POST['customer_name'] ?? '') : null;
    $orderNumber = $isCustomerDevice ? trim($_POST['order_number'] ?? '') : null;
    $weclappEntityId = $isCustomerDevice ? trim($_POST['weclapp_entity_id'] ?? '') : null;
    $repairDescription = $isRepairDevice ? trim($_POST['repair_description'] ?? '') : null;

    // GPS ist jetzt OPTIONAL
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    
    // Validierung
    if ($markerType === 'qr_code') {
        if (empty($qrCode)) {
            $message = 'Bitte wählen Sie einen QR-Code aus';
            $messageType = 'danger';
        } elseif (!validateQRCode($qrCode)) {
            $message = 'Ungültiges QR-Code-Format';
            $messageType = 'danger';
        }
    } elseif ($markerType === 'nfc_chip') {
        if (empty($nfcChipId)) {
            $message = 'Bitte wählen Sie einen NFC-Chip aus';
            $messageType = 'danger';
        }
    }
    
    if (empty($message)) {
        if (empty($name) || !validateStringLength($name, 1, 100)) {
            $message = 'Name ist erforderlich und darf maximal 100 Zeichen lang sein';
            $messageType = 'danger';
        } elseif ($isCustomerDevice && empty($customerName)) {
            $message = 'Bei Kundengeräten muss ein Kundenname angegeben werden';
            $messageType = 'danger';
        } elseif ($isRepairDevice && empty($customerName)) {
            $message = 'Bei Reparaturgeräten muss ein Kundenname angegeben werden';
            $messageType = 'danger';
        } elseif (!empty($category) && !validateStringLength($category, 1, 50)) {
            $message = 'Kategorie darf maximal 50 Zeichen lang sein';
            $messageType = 'danger';
        } elseif ($latitude && $longitude && !validateCoordinates($latitude, $longitude)) {
            $message = 'Ungültige GPS-Koordinaten';
            $messageType = 'danger';
        } else {
            // Verfügbarkeit prüfen
            $isAvailable = false;
            
            if ($markerType === 'qr_code') {
                $stmt = $pdo->prepare("SELECT * FROM qr_code_pool WHERE qr_code = ? AND is_assigned = 0");
                $stmt->execute([$qrCode]);
                $isAvailable = (bool)$stmt->fetch();
                
                if (!$isAvailable) {
                    $message = 'Dieser QR-Code ist nicht verfügbar oder bereits zugewiesen';
                    $messageType = 'warning';
                }
            } elseif ($markerType === 'nfc_chip') {
                $stmt = $pdo->prepare("SELECT * FROM nfc_chip_pool WHERE nfc_chip_id = ? AND is_assigned = 0");
                $stmt->execute([$nfcChipId]);
                $isAvailable = (bool)$stmt->fetch();
                
                if (!$isAvailable) {
                    $message = 'Dieser NFC-Chip ist nicht verfügbar oder bereits zugewiesen';
                    $messageType = 'warning';
                }
            }
            
            if ($isAvailable) {
                try {
                    $pdo->beginTransaction();
                    
                    $rentalStatus = ($isStorage || $isCustomerDevice) ? null : 'verfuegbar';
                    
                    // Bei NFC: qr_code ist die NFC-ID (für Kompatibilität)
                    $qrCodeOrNFCId = ($markerType === 'nfc_chip') ? $nfcChipId : $qrCode;
                    
                    // Marker erstellen
                    $stmt = $pdo->prepare("
                        INSERT INTO markers (qr_code, name, category, is_storage, is_multi_device, is_customer_device,
                                        is_repair_device, customer_name, order_number, weclapp_entity_id, repair_description, rental_status,
                                        latitude, longitude, created_by, is_activated, marker_type, nfc_enabled, nfc_chip_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $isActivated = ($latitude && $longitude) ? 1 : 0;
                    $nfcEnabled = ($markerType === 'nfc_chip') ? 1 : 0;
                    $nfcChipIdValue = ($markerType === 'nfc_chip') ? $nfcChipId : null;

                    $stmt->execute([
                        $qrCodeOrNFCId, $name, $category, 
                        $isStorage ? 1 : 0, 
                        $isMultiDevice ? 1 : 0,
                        $isCustomerDevice ? 1 : 0,
                        $isRepairDevice ? 1 : 0,
                        $customerName,
                        $orderNumber,
                        $weclappEntityId,
                        $repairDescription,
                        $rentalStatus,
                        $latitude ? floatval($latitude) : null,
                        $longitude ? floatval($longitude) : null,
                        $_SESSION['user_id'],
                        $isActivated,
                        $markerType,
                        $nfcEnabled,
                        $nfcChipIdValue
                    ]);
                    
                    $markerId = $pdo->lastInsertId();

                    // Public Token generieren
                    $publicToken = bin2hex(random_bytes(32));
                    $stmt = $pdo->prepare("UPDATE markers SET public_token = ? WHERE id = ?");
                    $stmt->execute([$publicToken, $markerId]);
                    
                    // Wartungssatz zuweisen
                    $maintenanceSetId = !empty($_POST['maintenance_set_id']) ? $_POST['maintenance_set_id'] : null;
                    if ($maintenanceSetId) {
                        $stmt = $pdo->prepare("UPDATE markers SET maintenance_set_id = ? WHERE id = ?");
                        $stmt->execute([$maintenanceSetId, $markerId]);
                        
                        // Wartungssatz-Werte speichern
                        if (!empty($_POST['maintenance_set_fields'])) {
                            $stmt = $pdo->prepare("INSERT INTO maintenance_set_values (marker_id, maintenance_set_field_id, field_value) VALUES (?, ?, ?)");
                            foreach ($_POST['maintenance_set_fields'] as $fieldId => $value) {
                                if ($value !== '' && $value !== null) {
                                    $stmt->execute([$markerId, $fieldId, $value]);
                                }
                            }
                        }
                    }
                    
                    // Pool aktualisieren
                    if ($markerType === 'qr_code') {
                        $stmt = $pdo->prepare("
                            UPDATE qr_code_pool 
                            SET is_assigned = 1, is_activated = ?, marker_id = ?, assigned_at = NOW()
                            WHERE qr_code = ?
                        ");
                        $stmt->execute([$isActivated, $markerId, $qrCode]);
                    } elseif ($markerType === 'nfc_chip') {
                        $stmt = $pdo->prepare("
                            UPDATE nfc_chip_pool 
                            SET is_assigned = 1, assigned_to_marker_id = ?, assigned_at = NOW()
                            WHERE nfc_chip_id = ?
                        ");
                        $stmt->execute([$markerId, $nfcChipId]);
                    }
                    
                    // ===== CACHE INVALIDIEREN =====
                    global $cache;
                    $cache->delete("all_markers");
                    $cache->deletePattern("available_qr_codes");
                    $cache->deletePattern("available_nfc_chips");
                    // ==============================

                    // Multi-Device Seriennummern
                    if ($isMultiDevice) {
                        $serial_numbers = $_POST['serial_numbers'] ?? [];
                        foreach ($serial_numbers as $serial) {
                            $serial = trim($serial);
                            if (!empty($serial)) {
                                if (!validateSerialNumber($serial)) {
                                    throw new Exception('Seriennummer enthält ungültige Zeichen oder ist zu lang (max. 100 Zeichen): ' . $serial);
                                }
                                $stmt = $pdo->prepare("INSERT INTO marker_serial_numbers (marker_id, serial_number) VALUES (?, ?)");
                                $stmt->execute([$markerId, $serial]);
                            }
                        }
                    } 
                    // Einzelgerät (kein Multi-Device)
                    else {
                        $serialNumber = trim($_POST['serial_number'] ?? '');
                        
                        // Für normale Geräte (nicht Kundengeräte): Wartungs- und Kraftstoffdaten
                        if (!$isCustomerDevice) {
                            $operatingHours = $_POST['operating_hours'] ?? 0;
                            $fuelLevel = $_POST['fuel_level'] ?? 0;
                            $fuelUnit = $_POST['fuel_unit'] ?? 'percent';
                            $fuelCapacity = ($fuelUnit === 'liter') ? ($_POST['fuel_capacity'] ?? null) : null;
                            $maintenanceInterval = $_POST['maintenance_interval'] ?? 6;
                            $lastMaintenance = $_POST['last_maintenance'] ?? date('Y-m-d');
                            
                            if (!empty($serialNumber) && !validateSerialNumber($serialNumber)) {
                                throw new Exception('Seriennummer enthält ungültige Zeichen oder ist zu lang (max. 100 Zeichen)');
                            }
                            
                            $nextMaintenance = null;
                            if (!$isStorage && $lastMaintenance && $maintenanceInterval > 0) {
                                $nextMaintenance = calculateNextMaintenance($lastMaintenance, $maintenanceInterval);
                            }
                            
                            $stmt = $pdo->prepare("UPDATE markers SET 
                                serial_number = ?,
                                operating_hours = ?,
                                fuel_level = ?,
                                fuel_unit = ?,
                                fuel_capacity = ?,
                                maintenance_interval_months = ?,
                                last_maintenance = ?,
                                next_maintenance = ?
                                WHERE id = ?");
                            $stmt->execute([
                                $serialNumber,
                                floatval($operatingHours),
                                intval($fuelLevel),
                                $fuelUnit,
                                $fuelCapacity ? floatval($fuelCapacity) : null,
                                intval($maintenanceInterval),
                                $lastMaintenance,
                                $nextMaintenance,
                                $markerId
                            ]);
                        } 
                        // Für Kundengeräte: Nur Seriennummer speichern
                        else {
                            if (!empty($serialNumber)) {
                                if (!validateSerialNumber($serialNumber)) {
                                    throw new Exception('Seriennummer enthält ungültige Zeichen oder ist zu lang (max. 100 Zeichen)');
                                }
                                
                                $stmt = $pdo->prepare("UPDATE markers SET serial_number = ? WHERE id = ?");
                                $stmt->execute([$serialNumber, $markerId]);
                            }
                        }
                    }
                    
                    // DGUV/UVV/TÜV Prüfungen
                    if (!empty($_POST['inspections']) && !$isStorage && !$isMultiDevice && !$isCustomerDevice) {
                        foreach ($_POST['inspections'] as $inspection) {
                            $inspectionType = $inspection['type'] ?? '';
                            $intervalMonths = $inspection['interval'] ?? 12;
                            $lastInspection = !empty($inspection['last_date']) ? $inspection['last_date'] : null;
                            $authority = !empty($inspection['authority']) ? $inspection['authority'] : null;
                            
                            if (!empty($inspectionType)) {
                                $nextInspection = null;
                                if ($lastInspection) {
                                    $nextInspection = date('Y-m-d', strtotime($lastInspection . " + $intervalMonths months"));
                                }
                                
                                $stmt = $pdo->prepare("
                                    INSERT INTO inspection_schedules 
                                    (marker_id, inspection_type, inspection_interval_months, last_inspection, next_inspection, inspection_authority)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $markerId,
                                    $inspectionType,
                                    $intervalMonths,
                                    $lastInspection,
                                    $nextInspection,
                                    $authority
                                ]);
                            }
                        }
                    }
                    
                    // Bilder hochladen
                    if (!empty($_FILES['images']['name'][0])) {
                        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                            if (!empty($tmpName)) {
                                $file = [
                                    'name' => $_FILES['images']['name'][$key],
                                    'type' => $_FILES['images']['type'][$key],
                                    'tmp_name' => $tmpName,
                                    'size' => $_FILES['images']['size'][$key]
                                ];
                                
                                $result = uploadImage($file, $markerId);
                                if ($result['success']) {
                                    $stmt = $pdo->prepare("INSERT INTO marker_images (marker_id, image_path) VALUES (?, ?)");
                                    $stmt->execute([$markerId, $result['path']]);
                                }
                            }
                        }
                    }
                    
                    // Custom Fields
                    if (!empty($_POST['custom_fields'])) {
                        $stmt = $pdo->prepare("INSERT INTO marker_custom_values (marker_id, field_id, field_value) VALUES (?, ?, ?)");
                        foreach ($_POST['custom_fields'] as $fieldId => $value) {
                            if (!empty($value)) {
                                $stmt->execute([$markerId, $fieldId, $value]);
                            }
                        }
                    }

                    $pdo->commit();

                    $deviceType = $isCustomerDevice ? 'Kundengerät' : ($isMultiDevice ? 'Multi-Device' : ($isStorage ? 'Lagergerät' : 'Gerät'));
                    $markerTypeLabel = ($markerType === 'nfc_chip') ? "NFC-Chip '$nfcChipId'" : "QR-Code '$qrCode'";
                    logActivity('marker_created', "$deviceType '{$name}' erstellt mit {$markerTypeLabel}", $markerId);
                    
                    if ($isActivated) {
                        $message = 'Marker erfolgreich erstellt und aktiviert!';
                    } else {
                        $message = $markerType === 'nfc_chip' 
                            ? 'Marker erfolgreich erstellt! Der NFC-Chip muss noch vor Ort gescannt werden, um den Marker zu aktivieren.'
                            : 'Marker erfolgreich erstellt! Der QR-Code muss noch vor Ort gescannt werden, um den Marker zu aktivieren.';
                    }
                    $messageType = 'success';
                    
                    header("refresh:3;url=view_marker.php?id=$markerId");
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = 'Fehler: ' . e($e->getMessage());
                    $messageType = 'danger';
                }
            }
        }
    }
}

$customFields = $pdo->query("SELECT * FROM custom_fields ORDER BY display_order, id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGG Geräte Verwaltung - Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .inspection-input-group {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
            position: relative;
        }
        
        .inspection-input-group.removable {
            border-left-color: #6c757d;
        }
        
        .inspection-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .remove-inspection-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-inspection-btn:hover {
            background: #c82333;
        }
        
        .customer-info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .marker-type-selector {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .marker-type-option {
            flex: 1;
            padding: 20px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        
        .marker-type-option:hover {
            border-color: #007bff;
            background: white;
        }
        
        .marker-type-option.selected {
            border-color: #007bff;
            background: #e7f3ff;
        }
        
        .marker-type-option input[type="radio"] {
            display: none;
        }
        
        .marker-type-option i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #6c757d;
        }
        
        .marker-type-option.selected i {
            color: #007bff;
        }
        
        @media (max-width: 768px) {
            .inspection-row {
                grid-template-columns: 1fr;
            }
            .marker-type-selector {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Neuen Marker erstellen</h1>
                <p>Erstellen Sie einen Marker im Büro - GPS-Position kann später vor Ort erfasst werden</p>
            </div>
            
            <?php if (empty($availableQRCodes)): ?>
                <div class="alert alert-warning">
                    <strong><i class="fas fa-exclamation-triangle"></i> Keine Marker verfügbar!</strong><br>
                    Sie müssen zuerst QR-Codes oder NFC-Chips generieren/registrieren.<br>
                    <a href="qr_code_generator.php" class="btn btn-primary" style="margin-top: 10px;">
                        <i class="fas fa-qrcode"></i> QR-Codes generieren
                    </a>
                    <!-- NFC-Chips sind auf Desktop nicht verfügbar -->
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="marker-form">
                <?= csrf_field() ?>
                
                <!-- Marker-Typ Auswahl -->
                <div class="form-section">
                    <h2><i class="fas fa-tag"></i> Marker-Typ auswählen</h2>
                    <input type="hidden" name="marker_type" id="marker_type" value="qr_code">
                    <div class="marker-type-selector">
                        <div class="marker-type-option selected" data-type="qr_code">
                            <i class="fas fa-qrcode" style="font-size: 2em; color: #007bff;"></i>
                            <h3>QR-Code</h3>
                            <p>Klassischer QR-Code zum Scannen</p>
                        </div>
                        <div class="marker-type-option" data-type="nfc_chip">
                            <i class="fas fa-wifi" style="font-size: 2em; color: #28a745;"></i>
                            <h3>NFC-Chip</h3>
                            <p>Kontaktloses NFC-Tag (perfekt für Apple)</p>
                        </div>
                    </div>
                </div>
                
                <!-- QR-Code Sektion -->
                <div class="form-section" id="qr_code_section">
                    <h2><i class="fas fa-qrcode"></i> QR-Code auswählen</h2>
                    <div class="form-group">
                        <label for="qr_code">QR-Code *</label>
                        <select id="qr_code" name="qr_code" <?= empty($availableQRCodes) ? 'disabled' : '' ?>>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($availableQRCodes as $code): ?>
                                <option value="<?= e($code['qr_code']) ?>">
                                    <?= e($code['qr_code']) ?>
                                    <?php if ($code['print_batch']): ?>
                                        (Batch: <?= e($code['print_batch']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Der QR-Code wird dem Gerät zugewiesen und kann später vor Ort aktiviert werden</small>
                    </div>
                </div>
                
                <!-- NFC-Chip Sektion -->
                <div class="form-section" id="nfc_chip_section" style="display: none;">
                    <h2><i class="fas fa-wifi"></i> NFC-Chip auswählen</h2>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>NFC-Chip URL:</strong><br>
                        Für alle NFC-Chips verwendest du die <strong>universelle URL</strong>:<br>
                        <code style="background: white; padding: 5px; display: inline-block; margin-top: 5px;">
                            https://bgg-objekt.de/nfc_redirect.php?id=
                        </code><br>
                        <small>Mit der "NFC Tools" App (kostenlos) auf den Chip schreiben. Aktiviere "Add UID parameter" mit Parameter-Name "id"!</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="nfc_chip_id">NFC-Chip *</label>
                        <select id="nfc_chip_id" name="nfc_chip_id" <?= empty($availableNFCChips) ? 'disabled' : '' ?>>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($availableNFCChips as $chip): ?>
                                <option value="<?= e($chip['nfc_chip_id']) ?>">
                                    <?= e($chip['nfc_chip_id']) ?>
                                    <?php if ($chip['batch_name']): ?>
                                        (Batch: <?= e($chip['batch_name']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($availableNFCChips)): ?>
                            <small class="text-danger">Keine NFC-Chips verfügbar. Bitte fügen Sie welche im <a href="nfc_chip_generator.php">NFC-Chip Generator</a> hinzu.</small>
                        <?php else: ?>
                            <small>Der NFC-Chip wird dem Gerät zugewiesen</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                
                
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Grunddaten</h2>
                    
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required placeholder="z.B. Generator 5kW">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Kategorie</label>
                        <select id="category" name="category">
                            <option value="">-- Keine --</option>
                            <option value="Generator">Generator</option>
                            <option value="Kompressor">Kompressor</option>
                            <option value="Pumpe">Pumpe</option>
                            <option value="Fahrzeug">Fahrzeug</option>
                            <option value="Werkzeug">Werkzeug</option>
                            <option value="Lager">Lager</option>
                            <option value="Sonstiges">Sonstiges</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_customer_device" name="is_customer_device">
                            <strong>Kundengerät</strong> (Gerät gehört einem Kunden)
                        </label>
                    </div>
                    
                    <!-- Reparatur-Gerät Checkbox und Beschreibung -->
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_repair_device" name="is_repair_device" onchange="toggleRepairFields()">
                            <span class="checkbox-text">
                                <i class="fas fa-tools"></i> Reparatur-Gerät
                            </span>
                        </label>
                        <small class="form-text text-muted">Gerät befindet sich in Reparatur</small>
                    </div>

                    <div id="repair-fields" style="display: none;">
                        <div class="form-group">
                            <label for="repair_description">Reparatur-Beschreibung</label>
                            <textarea id="repair_description" name="repair_description" rows="3" 
                                    placeholder="Beschreiben Sie den Reparaturgrund und -umfang..."></textarea>
                            <small>Was muss repariert werden?</small>
                        </div>
                    </div>

                    <!-- Kundengerät Details -->
                    <div id="customer_device_fields" style="display: none;">
                        <div class="customer-info-box">
                            <h3 style="margin: 0 0 15px 0;"><i class="fas fa-user"></i> Kundendaten</h3>
                            
                            <div class="form-group">
                                <label for="customer_name">Kundenname *</label>
                                <input type="text" id="customer_name" name="customer_name" placeholder="Name des Kunden">
                            </div>
                            
                            <div class="form-group">
                                <label for="order_number">Auftragsnummer</label>
                                <input type="text" id="order_number" name="order_number" placeholder="z.B. AUF-2025-001">
                            </div>
                            
                            <div class="form-group">
                                <label for="weclapp_entity_id">
                                    <i class="fas fa-link"></i> Weclapp Entity-ID
                                </label>
                                <input type="text" id="weclapp_entity_id" name="weclapp_entity_id" placeholder="z.B. 12345">
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
                            
                            <p style="color: #1976d2; margin: 10px 0 0 0;">
                                <i class="fas fa-info-circle"></i> 
                                Bei Kundengeräten werden keine Wartungs- oder Kraftstoffdaten erfasst.
                            </p>
                        </div>
                    </div>
                    
                    <div class="form-group" id="multi_device_checkbox">
                        <label>
                            <input type="checkbox" id="is_multi_device" name="is_multi_device">
                            <strong>Mehrere Geräte an diesem Standort</strong>
                        </label>
                    </div>
                    
                    <div class="form-group" id="storage_checkbox">
                        <label>
                            <input type="checkbox" id="is_storage" name="is_storage">
                            <strong>Lagergerät</strong> (keine Wartung/kein Kraftstoff)
                        </label>
                    </div>
                </div>
                
                <div class="form-section" id="device_data">
                    <h2><i class="fas fa-cog"></i> Gerätedaten</h2>
                    
                    <div id="single_device">
                        <div class="form-group">
                            <label for="serial_number">Seriennummer</label>
                            <input type="text" id="serial_number" name="serial_number" 
                                   maxlength="100"
                                   placeholder="z.B. ABC-123, GEN/2024-001">
                            <small>Erlaubt: Buchstaben, Zahlen und Sonderzeichen (- _ . / ( ) : , + * #)</small>
                        </div>
                        
                        <div class="form-group" id="operating_hours_group">
                            <label for="operating_hours">Betriebsstunden</label>
                            <input type="number" id="operating_hours" name="operating_hours" 
                                   value="0" step="0.01" min="0">
                        </div>
                        
                        <div class="form-group" id="fuel_group">
                            <label for="fuel_level">Kraftstofffüllstand</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" id="fuel_level" name="fuel_level" min="0" 
                                    value="0" style="flex: 1;">
                                <select id="fuel_unit" name="fuel_unit" style="width: 120px;" onchange="toggleFuelCapacity()">
                                    <option value="percent">Prozent (%)</option>
                                    <option value="liter">Liter (L)</option>
                                </select>
                            </div>
                            <small>Aktueller Füllstand des Kraftstofftanks</small>
                        </div>

                        <div id="fuel-capacity-field" style="display: none;">
                            <div class="form-group">
                                <label for="fuel_capacity">Tank-Kapazität (Liter)</label>
                                <input type="number" id="fuel_capacity" name="fuel_capacity" min="1" step="0.1" 
                                    placeholder="z.B. 50">
                                <small>Gesamt-Kapazität des Tanks für Prozent-Umrechnung</small>
                            </div>
                        </div>
                    </div>
                    
                    <div id="multi_device" style="display: none;">
                        <div id="serial_numbers_list">
                            <div class="form-group">
                                <input type="text" name="serial_numbers[]" 
                                       placeholder="Seriennummer 1" 
                                       class="form-control">
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="add_serial">
                            <i class="fas fa-plus"></i> Weitere Seriennummer
                        </button>
                    </div>
                </div>
                
                <div class="form-section" id="maintenance_section">
                    <h2><i class="fas fa-wrench"></i> Wartung</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="maintenance_interval">Wartungsintervall (Monate)</label>
                            <input type="number" id="maintenance_interval" name="maintenance_interval" 
                                   value="6" min="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_maintenance">Letzte Wartung</label>
                            <input type="date" id="last_maintenance" name="last_maintenance" 
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- DGUV/UVV/TÜV Prüfungen -->
                <div class="form-section" id="inspection_section">
                    <h2><i class="fas fa-clipboard-check"></i> DGUV / UVV / TÜV Prüfungen (optional)</h2>
                    <p style="color: #6c757d; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> 
                        Legen Sie hier die erforderlichen Prüfungen für dieses Gerät an (optional, kann auch später hinzugefügt werden)
                    </p>
                    
                    <div id="inspections_list">
                        <!-- Inspektionen werden hier dynamisch hinzugefügt -->
                    </div>
                    
                    <button type="button" class="btn btn-secondary" id="add_inspection">
                        <i class="fas fa-plus"></i> Prüfung hinzufügen
                    </button>
                </div>
                
                <!-- Wartungssätze -->
                <?php 
                $stmt = $pdo->query("SELECT * FROM maintenance_sets ORDER BY name ASC");
                $maintenanceSets = $stmt->fetchAll();
                ?>
                <?php if (!empty($maintenanceSets)): ?>
                <div class="form-section" id="maintenance_set_section">
                    <h2><i class="fas fa-tools"></i> Wartungssatz</h2>
                    <div class="form-group">
                        <label for="maintenance_set_id">Wartungssatz zuweisen (optional)</label>
                        <select id="maintenance_set_id" name="maintenance_set_id" onchange="loadMaintenanceSetFields()">
                            <option value="">Kein Wartungssatz</option>
                            <?php foreach ($maintenanceSets as $set): ?>
                                <option value="<?= $set['id'] ?>"><?= htmlspecialchars($set['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($maintenanceSets[0]['description'])): ?>
                            <small><?= htmlspecialchars($maintenanceSets[0]['description']) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div id="maintenance_set_fields_container">
                        <!-- Wartungssatz-Felder werden hier dynamisch geladen -->
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customFields)): ?>
                <div class="form-section">
                    <h2><i class="fas fa-list"></i> Zusätzliche Informationen</h2>
                    <?php foreach ($customFields as $field): ?>
                        <div class="form-group">
                            <label for="custom_<?= $field['id'] ?>">
                                <?= e($field['field_label']) ?>
                                <?php if ($field['required']): ?><span style="color: red;">*</span><?php endif; ?>
                            </label>
                            <?php if ($field['field_type'] === 'textarea'): ?>
                                <textarea id="custom_<?= $field['id'] ?>" 
                                        name="custom_fields[<?= $field['id'] ?>]" 
                                        rows="4" <?= $field['required'] ? 'required' : '' ?>></textarea>
                            <?php elseif ($field['field_type'] === 'number'): ?>
                                <input type="number" id="custom_<?= $field['id'] ?>" 
                                       name="custom_fields[<?= $field['id'] ?>]" 
                                       step="any" <?= $field['required'] ? 'required' : '' ?>>
                            <?php elseif ($field['field_type'] === 'date'): ?>
                                <input type="date" id="custom_<?= $field['id'] ?>" 
                                       name="custom_fields[<?= $field['id'] ?>]" 
                                       <?= $field['required'] ? 'required' : '' ?>>
                            <?php else: ?>
                                <input type="text" id="custom_<?= $field['id'] ?>" 
                                       name="custom_fields[<?= $field['id'] ?>]" 
                                       <?= $field['required'] ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Standorterfassung:</strong> Die GPS-Position wird automatisch beim ersten 
                    Scan (QR-Code oder NFC) vor Ort erfasst.
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-images"></i> Bilder</h2>
                    <div class="form-group">
                        <input type="file" id="images" name="images[]" multiple accept="image/*">
                    </div>
                    <div id="imagePreview"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large" id="submit_btn">
                        <i class="fas fa-save"></i> Marker erstellen
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    const multiDeviceCheckbox = document.getElementById('is_multi_device');
    const storageCheckbox = document.getElementById('is_storage');
    const customerDeviceCheckbox = document.getElementById('is_customer_device');
    
    function toggleMarkerTypeSections(markerType) {
        const qrSection = document.getElementById('qr_code_section');
        const nfcSection = document.getElementById('nfc_chip_section');
        const qrSelect = document.getElementById('qr_code');
        const nfcSelect = document.getElementById('nfc_chip_id');
        
        if (markerType === 'qr_code') {
            qrSection.style.display = 'block';
            nfcSection.style.display = 'none';
            qrSelect.required = true;
            nfcSelect.required = false;
        } else {
            qrSection.style.display = 'none';
            nfcSection.style.display = 'block';
            qrSelect.required = false;
            nfcSelect.required = true;
        }
    }
    
    function updateSubmitButton() {
        const markerType = document.querySelector('input[name="marker_type"]:checked').value;
        const submitBtn = document.getElementById('submit_btn');
        
        if (markerType === 'qr_code') {
            const hasQRCodes = document.getElementById('qr_code').options.length > 1;
            submitBtn.disabled = !hasQRCodes;
        } else {
            const hasNFCChips = document.getElementById('nfc_chip_id').options.length > 1;
            submitBtn.disabled = !hasNFCChips;
        }
    }
    
    // Reparatur-Felder anzeigen/verstecken
    function toggleRepairFields() {
        const isRepair = document.getElementById('is_repair_device').checked;
        document.getElementById('repair-fields').style.display = isRepair ? 'block' : 'none';
        
        if (isRepair) {
            // Automatisch auch is_customer_device aktivieren
            document.getElementById('is_customer_device').checked = true;
            toggleCustomerFields();
        }
    }

    // Kraftstoff-Kapazität anzeigen/verstecken
    function toggleFuelCapacity() {
        const fuelUnit = document.getElementById('fuel_unit').value;
        document.getElementById('fuel-capacity-field').style.display = 
            (fuelUnit === 'liter') ? 'block' : 'none';
    }
    
    function toggleCustomerFields() {
        updateFormFields();
    }

    // Fuel Level anzeigen basierend auf Einheit
    document.addEventListener('DOMContentLoaded', function() {
        const fuelLevelInput = document.getElementById('fuel_level');
        const fuelUnitSelect = document.getElementById('fuel_unit');
        
        fuelUnitSelect.addEventListener('change', function() {
            if (this.value === 'percent') {
                fuelLevelInput.max = 100;
                fuelLevelInput.step = 1;
            } else {
                fuelLevelInput.max = 999;
                fuelLevelInput.step = 0.1;
            }
        });
        
        updateSubmitButton();
    });

    function updateFormFields() {
        const isMulti = multiDeviceCheckbox.checked;
        const isStorage = storageCheckbox.checked;
        const isCustomer = customerDeviceCheckbox.checked;
        
        // Kundengerät-Felder
        document.getElementById('customer_device_fields').style.display = isCustomer ? 'block' : 'none';
        if (isCustomer) {
            document.getElementById('customer_name').required = true;
        } else {
            document.getElementById('customer_name').required = false;
        }
        
        // Multi-Device und Storage ausblenden bei Kundengerät
        document.getElementById('multi_device_checkbox').style.display = isCustomer ? 'none' : 'block';
        document.getElementById('storage_checkbox').style.display = (isMulti || isCustomer) ? 'none' : 'block';
        
        // Bei Kundengerät: Multi und Storage deaktivieren
        if (isCustomer) {
            multiDeviceCheckbox.checked = false;
            storageCheckbox.checked = false;
        }
        
        // Gerätedaten-Bereiche
        document.getElementById('single_device').style.display = isMulti ? 'none' : 'block';
        document.getElementById('multi_device').style.display = isMulti ? 'block' : 'none';
        
        // Wartung und Inspektionen
        const hideMaintenanceAndInspections = (isMulti || isStorage || isCustomer);
        document.getElementById('maintenance_section').style.display = hideMaintenanceAndInspections ? 'none' : 'block';
        document.getElementById('inspection_section').style.display = hideMaintenanceAndInspections ? 'none' : 'block';
        
        // Kraftstoff und Betriebsstunden
        const hideFuelAndHours = (isStorage || isCustomer);
        document.getElementById('fuel_group').style.display = hideFuelAndHours ? 'none' : 'block';
        document.getElementById('operating_hours_group').style.display = hideFuelAndHours ? 'none' : 'block';
    }
    
    multiDeviceCheckbox.addEventListener('change', updateFormFields);
    storageCheckbox.addEventListener('change', updateFormFields);
    customerDeviceCheckbox.addEventListener('change', updateFormFields);
    
    // Seriennummern hinzufügen
    let serialCount = 1;
    document.getElementById('add_serial').addEventListener('click', function() {
        serialCount++;
        const div = document.createElement('div');
        div.className = 'form-group';
        div.innerHTML = `
            <div style="display: flex; gap: 10px;">
                <input type="text" name="serial_numbers[]" placeholder="Seriennummer ${serialCount}" class="form-control" style="flex: 1;">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        document.getElementById('serial_numbers_list').appendChild(div);
    });
    
    // DGUV/UVV/TÜV Prüfungen hinzufügen
    let inspectionCount = 0;
    document.getElementById('add_inspection').addEventListener('click', function() {
        inspectionCount++;
        const div = document.createElement('div');
        div.className = 'inspection-input-group removable';
        div.innerHTML = `
            <button type="button" class="remove-inspection-btn" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
            <div class="inspection-row">
                <div class="form-group">
                    <label>Prüfungsart *</label>
                    <select name="inspections[${inspectionCount}][type]" required>
                                <option value="">Bitte wählen...</option>
                                <option value="TÜV">TÜV</option>
                                <option value="UVV">UVV</option>
                                <option value="DGUV">DGUV</option>
                                <option value="DGUV V3">DGUV V3</option>
                                <option value="Elektrische Wartung">Elektrische Wartung</option>
                                <option value="Sicherheitsprüfung">Sicherheitsprüfung</option>
                                <option value="Sonstiges">Sonstiges</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Intervall (Monate)</label>
                    <input type="number" name="inspections[${inspectionCount}][interval]" 
                           value="12" min="1" max="120">
                </div>
                <div class="form-group">
                    <label>Letzte Prüfung</label>
                    <input type="date" name="inspections[${inspectionCount}][last_date]">
                </div>
                <div class="form-group">
                    <label>Prüfstelle / Behörde</label>
                    <input type="text" name="inspections[${inspectionCount}][authority]" 
                           placeholder="z.B. TÜV Süd">
                </div>
            </div>
        `;
        document.getElementById('inspections_list').appendChild(div);
    });
    
    // Image Preview
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '150px';
                img.style.margin = '10px';
                img.style.borderRadius = '8px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
    
    // Wartungssatz-Felder dynamisch laden
    function loadMaintenanceSetFields() {
        const setId = document.getElementById('maintenance_set_id').value;
        const container = document.getElementById('maintenance_set_fields_container');
        
        if (!setId) {
            container.innerHTML = '';
            return;
        }
        
        fetch(`api_get_maintenance_set_fields.php?set_id=${setId}`)
            .then(response => response.json())
            .then(fields => {
                let html = '<div style="margin-top: 20px;">';
                fields.forEach(field => {
                    html += `<div class="form-group">`;
                    html += `<label for="ms_field_${field.id}">${escapeHtml(field.field_label)}`;
                    if (field.is_required) html += ' <span style="color: red;">*</span>';
                    html += `</label>`;
                    
                    if (field.field_type === 'textarea') {
                        html += `<textarea id="ms_field_${field.id}" name="maintenance_set_fields[${field.id}]" rows="4" ${field.is_required ? 'required' : ''}></textarea>`;
                    } else if (field.field_type === 'number') {
                        html += `<input type="number" id="ms_field_${field.id}" name="maintenance_set_fields[${field.id}]" step="any" ${field.is_required ? 'required' : ''}>`;
                    } else if (field.field_type === 'date') {
                        html += `<input type="date" id="ms_field_${field.id}" name="maintenance_set_fields[${field.id}]" ${field.is_required ? 'required' : ''}>`;
                    } else if (field.field_type === 'checkbox') {
                        html += `<label><input type="checkbox" id="ms_field_${field.id}" name="maintenance_set_fields[${field.id}]" value="1"> Ja</label>`;
                    } else if (field.field_type === 'select' && field.field_options) {
                        html += `<select id="ms_field_${field.id}" name="maintenance_set_fields[${field.id}]" ${field.is_required ? 'required' : ''}>`;
                        html += `<option value="">Bitte wählen...</option>`;
                        const options = field.field_options.split('\n');
                        options.forEach(opt => {
                            if (opt.trim()) {
                                html += `<option value="${escapeHtml(opt.trim())}">${escapeHtml(opt.trim())}</option>`;
                            }
                        });
                        html += `</select>`;
                    } else {
                        html += `<input type="text" id="ms_field_${field.id}" name="maintenance_set_fields[${field.id}]" ${field.is_required ? 'required' : ''}>`;
                    }
                    html += `</div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Fehler beim Laden der Wartungssatz-Felder:', error);
            });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ===== MARKER-TYP UMSCHALTUNG (QR-Code / NFC-Chip) =====
    document.addEventListener('DOMContentLoaded', function() {
        const markerTypeOptions = document.querySelectorAll('.marker-type-option');
        const markerTypeInput = document.getElementById('marker_type');
        const qrSection = document.getElementById('qr_code_section');
        const nfcSection = document.getElementById('nfc_chip_section');
        const qrSelect = document.getElementById('qr_code');
        const nfcSelect = document.getElementById('nfc_chip_id');
        
        markerTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Alle Optionen deselektieren
                markerTypeOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Diese Option selektieren
                this.classList.add('selected');
                
                // Marker-Typ setzen
                const selectedType = this.dataset.type;
                markerTypeInput.value = selectedType;
                
                // Sections umschalten
                if (selectedType === 'qr_code') {
                    qrSection.style.display = 'block';
                    nfcSection.style.display = 'none';
                    qrSelect.required = true;
                    nfcSelect.required = false;
                    // Kundengerät wieder aktivieren bei QR-Code
                    customerDeviceCheckbox.disabled = false;
                    customerDeviceCheckbox.parentElement.style.opacity = '1';
                } else {
                    qrSection.style.display = 'none';
                    nfcSection.style.display = 'block';
                    qrSelect.required = false;
                    nfcSelect.required = true;
                    // Kundengerät deaktivieren bei NFC
                    customerDeviceCheckbox.checked = false;
                    customerDeviceCheckbox.disabled = true;
                    customerDeviceCheckbox.parentElement.style.opacity = '0.5';
                    document.getElementById('customer_device_fields').style.display = 'none';
                    document.getElementById('customer_name').value = '';
                    document.getElementById('order_number').value = '';
                    document.getElementById('weclapp_entity_id').value = '';
                }
                
                updateSubmitButton();
            });
        });
        
        // Initial state
        updateSubmitButton();
    });
    </script>
</body>
</html>