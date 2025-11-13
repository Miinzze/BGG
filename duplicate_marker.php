<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('markers_create');

$message = '';
$messageType = '';
$markerId = $_GET['id'] ?? 0;

// Marker-Daten laden
$stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
$stmt->execute([$markerId]);
$marker = $stmt->fetch();

if (!$marker) {
    die('Marker nicht gefunden');
}

// Verfügbare QR-Codes abrufen
$availableQRCodes = getAvailableQRCodes($pdo, 100);

// Verfügbare NFC-Chips (falls NFC aktiviert)
$availableNFCChips = [];
if ($marker['nfc_enabled']) {
    $stmt = $pdo->prepare("SELECT * FROM nfc_chip_pool WHERE is_assigned = 0 ORDER BY chip_id ASC LIMIT 100");
    $stmt->execute();
    $availableNFCChips = $stmt->fetchAll();
}

// POST: Marker duplizieren
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    try {
        $newQrCode = trim($_POST['new_qr_code'] ?? '');
        $newSerialNumber = trim($_POST['new_serial_number'] ?? '');
        $newName = trim($_POST['new_name'] ?? $marker['name'] . ' (Kopie)');
        $copyImages = isset($_POST['copy_images']);
        $copyCustomFields = isset($_POST['copy_custom_fields']);
        $copyMaintenanceHistory = isset($_POST['copy_maintenance_history']);
        $newNfcChip = trim($_POST['new_nfc_chip'] ?? '');
        
        // Validierung
        if (empty($newQrCode)) {
            throw new Exception("QR-Code ist erforderlich");
        }
        
        if (!validateQRCode($newQrCode)) {
            throw new Exception("Ungültiger QR-Code");
        }
        
        // Prüfen ob QR-Code verfügbar ist
        $stmt = $pdo->prepare("SELECT * FROM qr_code_pool WHERE qr_code = ? AND is_assigned = 0");
        $stmt->execute([$newQrCode]);
        if (!$stmt->fetch()) {
            throw new Exception("QR-Code ist nicht verfügbar");
        }
        
        // NFC-Chip prüfen falls angegeben
        if ($marker['nfc_enabled'] && !empty($newNfcChip)) {
            $stmt = $pdo->prepare("SELECT * FROM nfc_chip_pool WHERE chip_id = ? AND is_assigned = 0");
            $stmt->execute([$newNfcChip]);
            if (!$stmt->fetch()) {
                throw new Exception("NFC-Chip ist nicht verfügbar");
            }
        }
        
        $pdo->beginTransaction();
        
        // Neuen Marker erstellen
        $stmt = $pdo->prepare("INSERT INTO markers 
            (qr_code, name, category, serial_number, is_storage, rental_status, 
            operating_hours, fuel_level, maintenance_interval_months, is_multi_device, 
            nfc_enabled, nfc_chip_id, marker_type, is_customer_device, customer_name, 
            order_number, is_repair_device, repair_description, fuel_unit, fuel_capacity, 
            is_finished, is_activated, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $newQrCode,
            $newName,
            $marker['category'],
            $newSerialNumber,
            $marker['is_storage'],
            $marker['rental_status'],
            0, // operating_hours auf 0 zurücksetzen
            $marker['fuel_level'],
            $marker['maintenance_interval_months'],
            $marker['is_multi_device'],
            $marker['nfc_enabled'],
            $marker['nfc_enabled'] && !empty($newNfcChip) ? $newNfcChip : null,
            $marker['marker_type'],
            $marker['is_customer_device'],
            $marker['customer_name'],
            $marker['order_number'],
            $marker['is_repair_device'],
            $marker['repair_description'],
            $marker['fuel_unit'],
            $marker['fuel_capacity'],
            $marker['is_finished'],
            0, // is_activated auf 0 setzen
            $marker['notes'],
            $_SESSION['user_id'] ?? null
        ]);
        
        $newMarkerId = $pdo->lastInsertId();
        
        // QR-Code als zugewiesen markieren
        $stmt = $pdo->prepare("UPDATE qr_code_pool SET is_assigned = 1, marker_id = ?, assigned_at = NOW() WHERE qr_code = ?");
        $stmt->execute([$newMarkerId, $newQrCode]);
        
        // NFC-Chip als zugewiesen markieren falls vorhanden
        if ($marker['nfc_enabled'] && !empty($newNfcChip)) {
            $stmt = $pdo->prepare("UPDATE nfc_chip_pool SET is_assigned = 1, marker_id = ?, assigned_at = NOW() WHERE chip_id = ?");
            $stmt->execute([$newMarkerId, $newNfcChip]);
        }
        
        // Custom Fields kopieren
        if ($copyCustomFields) {
            $stmt = $pdo->prepare("SELECT field_id, field_value FROM marker_custom_values WHERE marker_id = ?");
            $stmt->execute([$markerId]);
            $customValues = $stmt->fetchAll();
            
            foreach ($customValues as $value) {
                $stmt = $pdo->prepare("INSERT INTO marker_custom_values (marker_id, field_id, field_value) VALUES (?, ?, ?)");
                $stmt->execute([$newMarkerId, $value['field_id'], $value['field_value']]);
            }
        }
        
        // Seriennummern kopieren (bei Multi-Device)
        if ($marker['is_multi_device']) {
            $stmt = $pdo->prepare("SELECT serial_number FROM marker_serial_numbers WHERE marker_id = ?");
            $stmt->execute([$markerId]);
            $serialNumbers = $stmt->fetchAll();
            
            foreach ($serialNumbers as $sn) {
                $stmt = $pdo->prepare("INSERT INTO marker_serial_numbers (marker_id, serial_number) VALUES (?, ?)");
                $stmt->execute([$newMarkerId, $sn['serial_number']]);
            }
        }
        
        // Bilder kopieren
        if ($copyImages) {
            $stmt = $pdo->prepare("SELECT * FROM marker_images WHERE marker_id = ?");
            $stmt->execute([$markerId]);
            $images = $stmt->fetchAll();
            
            foreach ($images as $image) {
                $oldFilePath = UPLOAD_DIR . $image['image_path'];
                
                if (file_exists($oldFilePath)) {
                    $extension = pathinfo($image['image_path'], PATHINFO_EXTENSION);
                    $newFileName = uniqid('img_', true) . '_' . $newMarkerId . '.' . $extension;
                    $newFilePath = UPLOAD_DIR . $newFileName;
                    
                    if (copy($oldFilePath, $newFilePath)) {
                        $stmt = $pdo->prepare("INSERT INTO marker_images (marker_id, image_path) VALUES (?, ?)");
                        $stmt->execute([$newMarkerId, $newFileName]);
                    }
                }
            }
        }
        
        // Wartungshistorie kopieren (optional)
        if ($copyMaintenanceHistory) {
            $stmt = $pdo->prepare("SELECT * FROM maintenance WHERE marker_id = ?");
            $stmt->execute([$markerId]);
            $maintenanceRecords = $stmt->fetchAll();
            
            foreach ($maintenanceRecords as $maintenance) {
                $stmt = $pdo->prepare("INSERT INTO maintenance 
                    (marker_id, maintenance_date, maintenance_type, description, performed_by, operating_hours, cost, next_maintenance_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $newMarkerId,
                    $maintenance['maintenance_date'],
                    $maintenance['maintenance_type'],
                    $maintenance['description'],
                    $maintenance['performed_by'],
                    $maintenance['operating_hours'],
                    $maintenance['cost'],
                    $maintenance['next_maintenance_date']
                ]);
            }
        }
        
        // Activity Log
        logActivity("Marker dupliziert: $newName (von '{$marker['name']}')", $newMarkerId);
        
        // Marker History
        createMarkerHistoryEntry($newMarkerId, 'created', [
            'source' => 'duplicate',
            'original_id' => $markerId,
            'original_qr_code' => $marker['qr_code'],
            'copied_images' => $copyImages,
            'copied_custom_fields' => $copyCustomFields,
            'copied_maintenance' => $copyMaintenanceHistory
        ]);
        
        // Cache invalidieren
        global $cache;
        $cache->delete("available_qr_codes:100");
        if ($marker['nfc_enabled']) {
            $cache->deletePattern("available_nfc_chips");
        }
        
        $pdo->commit();
        
        $message = "Marker erfolgreich dupliziert! <a href='view_marker.php?id=$newMarkerId' class='alert-link'>Zum neuen Marker →</a>";
        $messageType = 'success';
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Fehler beim Duplizieren: " . $e->getMessage();
        $messageType = 'danger';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marker duplizieren - <?= e($marker['name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .duplicate-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .source-marker-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .source-marker-info h2 {
            margin: 0 0 15px 0;
            font-size: 24px;
        }
        
        .source-marker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .source-marker-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box i {
            margin-right: 8px;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .checkbox-item input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-label {
            flex: 1;
            cursor: pointer;
        }
        
        .checkbox-label strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .checkbox-label small {
            color: #6c757d;
        }
        
        .qr-code-selector {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: start;
        }
        
        .available-codes {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .code-item {
            padding: 8px;
            margin: 4px 0;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .code-item:hover {
            background: #e7f3ff;
            transform: translateX(5px);
        }
        
        .preview-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }
        
        .preview-box h4 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .preview-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            text-align: left;
        }
        
        .preview-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .preview-item strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="duplicate-container">
                <div class="page-header">
                    <h1><i class="fas fa-copy"></i> Marker duplizieren</h1>
                    <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <!-- Original Marker Info -->
                <div class="source-marker-info">
                    <h2><i class="fas fa-qrcode"></i> Original-Marker</h2>
                    <div class="source-marker-grid">
                        <div class="source-marker-item">
                            <i class="fas fa-tag"></i>
                            <span><strong>Name:</strong> <?= e($marker['name']) ?></span>
                        </div>
                        <div class="source-marker-item">
                            <i class="fas fa-barcode"></i>
                            <span><strong>QR-Code:</strong> <?= e($marker['qr_code']) ?></span>
                        </div>
                        <?php if ($marker['serial_number']): ?>
                        <div class="source-marker-item">
                            <i class="fas fa-hashtag"></i>
                            <span><strong>Seriennummer:</strong> <?= e($marker['serial_number']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="source-marker-item">
                            <i class="fas fa-folder"></i>
                            <span><strong>Kategorie:</strong> <?= e($marker['category']) ?></span>
                        </div>
                        <?php if ($marker['nfc_enabled']): ?>
                        <div class="source-marker-item">
                            <i class="fas fa-wifi"></i>
                            <span><strong>NFC:</strong> <?= e($marker['nfc_chip_id'] ?? 'Nicht zugewiesen') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Hinweis:</strong> Beim Duplizieren wird eine exakte Kopie des Markers mit einem neuen QR-Code erstellt.
                    Betriebsstunden und Aktivierungsstatus werden zurückgesetzt.
                </div>
                
                <form method="POST" id="duplicateForm">
                    <?= csrf_field() ?>
                    
                    <!-- Basis-Informationen -->
                    <div class="form-section">
                        <h3><i class="fas fa-edit"></i> Neue Marker-Informationen</h3>
                        
                        <div class="form-group">
                            <label for="new_name">Name des neuen Markers *</label>
                            <input type="text" 
                                   id="new_name" 
                                   name="new_name" 
                                   class="form-control" 
                                   value="<?= e($marker['name']) ?> (Kopie)" 
                                   required>
                            <small class="form-text">Der Name kann später geändert werden.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_qr_code">Neuer QR-Code *</label>
                            <div class="qr-code-selector">
                                <div>
                                    <input type="text" 
                                           id="new_qr_code" 
                                           name="new_qr_code" 
                                           class="form-control" 
                                           placeholder="z.B. QR-0001" 
                                           required
                                           pattern="[A-Za-z0-9\-]{3,100}">
                                    <small class="form-text">Wählen Sie einen verfügbaren QR-Code oder geben Sie einen ein.</small>
                                </div>
                                <button type="button" class="btn btn-secondary" onclick="toggleAvailableCodes()">
                                    <i class="fas fa-list"></i> Verfügbare anzeigen
                                </button>
                            </div>
                            
                            <div id="availableCodes" class="available-codes" style="display: none; margin-top: 10px;">
                                <strong>Verfügbare QR-Codes (<?= count($availableQRCodes) ?>):</strong>
                                <?php if (empty($availableQRCodes)): ?>
                                    <p style="color: #dc3545; margin-top: 10px;">
                                        <i class="fas fa-exclamation-triangle"></i> Keine QR-Codes verfügbar!
                                        Bitte generieren Sie zuerst neue QR-Codes.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($availableQRCodes as $qrCode): ?>
                                        <div class="code-item" onclick="selectQRCode('<?= e($qrCode['qr_code']) ?>')">
                                            <i class="fas fa-qrcode"></i> <?= e($qrCode['qr_code']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_serial_number">Seriennummer (optional)</label>
                            <input type="text" 
                                   id="new_serial_number" 
                                   name="new_serial_number" 
                                   class="form-control" 
                                   placeholder="Neue Seriennummer">
                            <small class="form-text">Leer lassen, um die gleiche Seriennummer zu verwenden.</small>
                        </div>
                        
                        <?php if ($marker['nfc_enabled'] && !empty($availableNFCChips)): ?>
                        <div class="form-group">
                            <label for="new_nfc_chip">NFC-Chip (optional)</label>
                            <select id="new_nfc_chip" name="new_nfc_chip" class="form-control">
                                <option value="">Kein NFC-Chip zuweisen</option>
                                <?php foreach ($availableNFCChips as $chip): ?>
                                    <option value="<?= e($chip['chip_id']) ?>">
                                        <?= e($chip['chip_id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Kopier-Optionen -->
                    <div class="form-section">
                        <h3><i class="fas fa-tasks"></i> Was soll kopiert werden?</h3>
                        
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="copy_custom_fields" name="copy_custom_fields" checked>
                                <label for="copy_custom_fields" class="checkbox-label">
                                    <strong>Custom Fields / Benutzerdefinierte Felder</strong>
                                    <small>Alle benutzerdefinierten Feldwerte werden übernommen</small>
                                </label>
                            </div>
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="copy_images" name="copy_images">
                                <label for="copy_images" class="checkbox-label">
                                    <strong>Bilder</strong>
                                    <small>Alle hochgeladenen Bilder werden dupliziert</small>
                                </label>
                            </div>
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="copy_maintenance_history" name="copy_maintenance_history">
                                <label for="copy_maintenance_history" class="checkbox-label">
                                    <strong>Wartungshistorie</strong>
                                    <small>Vergangene Wartungen werden in den neuen Marker übernommen</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="warning-box">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Wichtig:</strong> Die folgenden Eigenschaften werden NICHT kopiert oder zurückgesetzt:
                        <ul style="margin: 10px 0 0 25px;">
                            <li>Betriebsstunden (werden auf 0 gesetzt)</li>
                            <li>Aktivierungsstatus (wird deaktiviert)</li>
                            <li>GPS-Position (wird nicht kopiert)</li>
                            <li>Scan-Historie</li>
                            <li>Gerätevertrauensliste (Trusted Devices)</li>
                        </ul>
                    </div>
                    
                    <!-- Vorschau -->
                    <div class="preview-box">
                        <h4><i class="fas fa-eye"></i> Vorschau der Kopie</h4>
                        <div class="preview-content">
                            <div class="preview-item">
                                <strong>Name:</strong>
                                <span id="preview_name"><?= e($marker['name']) ?> (Kopie)</span>
                            </div>
                            <div class="preview-item">
                                <strong>QR-Code:</strong>
                                <span id="preview_qr">Noch nicht ausgewählt</span>
                            </div>
                            <div class="preview-item">
                                <strong>Kategorie:</strong>
                                <span><?= e($marker['category']) ?></span>
                            </div>
                            <div class="preview-item">
                                <strong>Betriebsstunden:</strong>
                                <span>0 (zurückgesetzt)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aktionen -->
                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary btn-lg" <?= empty($availableQRCodes) ? 'disabled' : '' ?>>
                            <i class="fas fa-copy"></i> Marker jetzt duplizieren
                        </button>
                        <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Abbrechen
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    function toggleAvailableCodes() {
        const codesDiv = document.getElementById('availableCodes');
        codesDiv.style.display = codesDiv.style.display === 'none' ? 'block' : 'none';
    }
    
    function selectQRCode(qrCode) {
        document.getElementById('new_qr_code').value = qrCode;
        document.getElementById('preview_qr').textContent = qrCode;
        toggleAvailableCodes();
    }
    
    // Live-Vorschau für Name
    document.getElementById('new_name').addEventListener('input', function() {
        document.getElementById('preview_name').textContent = this.value || '<?= e($marker['name']) ?> (Kopie)';
    });
    
    // Live-Vorschau für QR-Code
    document.getElementById('new_qr_code').addEventListener('input', function() {
        document.getElementById('preview_qr').textContent = this.value || 'Noch nicht ausgewählt';
    });
    
    // Bestätigung vor dem Absenden
    document.getElementById('duplicateForm').addEventListener('submit', function(e) {
        const qrCode = document.getElementById('new_qr_code').value;
        const name = document.getElementById('new_name').value;
        
        if (!confirm(`Marker wirklich duplizieren?\n\nNeuer Name: ${name}\nNeuer QR-Code: ${qrCode}`)) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>