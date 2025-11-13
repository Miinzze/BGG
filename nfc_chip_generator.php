<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('qr_manage'); // Nutzt dieselbe Permission wie QR-Codes

// Mobile Detection
$isMobile = isMobileDevice();

$message = '';
$messageType = '';

// NFC-Chips verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_nfc'])) {
    validateCSRF();
    
    $chipIdsText = trim($_POST['nfc_chip_ids'] ?? '');
    $batchName = trim($_POST['batch_name'] ?? '');
    
    if (empty($chipIdsText)) {
        $message = 'Bitte geben Sie mindestens eine NFC-Chip-ID ein';
        $messageType = 'danger';
    } else {
        // Zeilen aufteilen und bereinigen
        $chipIds = array_filter(array_map('trim', explode("\n", $chipIdsText)));
        
        if (count($chipIds) === 0) {
            $message = 'Keine gültigen NFC-Chip-IDs gefunden';
            $messageType = 'danger';
        } else {
            $result = generateNFCChipsToPool($pdo, $chipIds, $batchName);
            
            if ($result['success']) {
                $message = sprintf(
                    'Erfolgreich %d NFC-Chip(s) hinzugefügt%s',
                    $result['generated'],
                    $result['skipped'] > 0 ? ' (' . $result['skipped'] . ' übersprungen)' : ''
                );
                $messageType = 'success';
                
                if (!empty($result['errors'])) {
                    $message .= '<br><br><strong>Fehler:</strong><br>' . implode('<br>', $result['errors']);
                }
                
                logActivity('nfc_chips_added', "Batch '$batchName': {$result['generated']} NFC-Chips hinzugefügt, {$result['skipped']} übersprungen");
            } else {
                $message = 'Fehler beim Hinzufügen der NFC-Chips: ' . e($result['error']);
                $messageType = 'danger';
            }
        }
    }
}

// Statistiken abrufen
$availableCount = countAvailableNFCChips($pdo);
$stmt = $pdo->query("SELECT COUNT(*) FROM nfc_chip_pool WHERE is_assigned = 1");
$assignedCount = $stmt->fetchColumn();
$totalCount = $availableCount + $assignedCount;

// Letzte Batches
$stmt = $pdo->query("
    SELECT batch_name, COUNT(*) as count, MIN(created_at) as created
    FROM nfc_chip_pool 
    WHERE batch_name IS NOT NULL
    GROUP BY batch_name
    ORDER BY created DESC
    LIMIT 10
");
$recentBatches = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFC-Chip Verwaltung</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .stat-card.available i {
            color: #28a745;
        }
        
        .stat-card.assigned i {
            color: #17a2b8;
        }
        
        .stat-card.total i {
            color: #6c757d;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .nfc-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .form-group textarea {
            font-family: monospace;
            min-height: 200px;
            resize: vertical;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .batches-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .batches-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .batches-table th,
        .batches-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .batches-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .info-box {
            background: #D4585A;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #090909;
            margin-right: 8px;
        }
        
        /* Mobile NFC-Scanner Styles */
        .mobile-nfc-scanner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .mobile-nfc-scanner h3 {
            margin-top: 0;
            color: white;
        }
        
        .mobile-nfc-scanner p {
            margin-bottom: 20px;
            opacity: 0.95;
        }
        
        #nfc-scan-area {
            background: rgba(255, 255, 255, 0.15);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            border: 2px dashed rgba(255, 255, 255, 0.5);
        }
        
        #nfc-scan-area.scanning {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.2);
            animation: pulse-nfc 1.5s infinite;
        }
        
        @keyframes pulse-nfc {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .nfc-icon-large {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        #nfc-status {
            font-size: 1.1em;
            font-weight: 600;
            margin: 15px 0;
            min-height: 25px;
        }
        
        .btn-nfc-scan {
            background: white;
            color: #667eea;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .btn-nfc-scan:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .btn-nfc-scan:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        #scanned-chips-preview {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        #scanned-chips-preview h4 {
            margin-top: 0;
            color: white;
        }
        
        #scanned-chips-list {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 15px;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.95em;
        }
        
        .scanned-chip-item {
            padding: 8px 12px;
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .scanned-chip-item .chip-id {
            font-weight: 600;
        }
        
        .scanned-chip-item .chip-time {
            font-size: 0.85em;
            color: #666;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-wifi"></i> NFC-Chip Verwaltung</h1>
                <p>NFC-Chips zum System hinzufügen und verwalten</p>
            </div>
            
            <!-- Statistiken -->
            <div class="stats-grid">
                <div class="stat-card available">
                    <i class="fas fa-wifi"></i>
                    <div class="stat-number"><?= $availableCount ?></div>
                    <div class="stat-label">Verfügbar</div>
                </div>
                <div class="stat-card assigned">
                    <i class="fas fa-link"></i>
                    <div class="stat-number"><?= $assignedCount ?></div>
                    <div class="stat-label">Zugewiesen</div>
                </div>
                <div class="stat-card total">
                    <i class="fas fa-layer-group"></i>
                    <div class="stat-number"><?= $totalCount ?></div>
                    <div class="stat-label">Gesamt</div>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <!-- NFC-Chips hinzufügen -->
            <div class="nfc-form">
                <h2><i class="fas fa-plus-circle"></i> NFC-Chips hinzufügen</h2>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Hinweis:</strong> 
                    <?php if ($isMobile): ?>
                        Sie können NFC-Chips durch Anhalten Ihres Smartphones scannen oder die IDs manuell eingeben.
                    <?php else: ?>
                        Geben Sie die NFC-Chip-IDs ein, die Sie Ihren physischen NFC-Tags entnommen haben.
                    <?php endif; ?>
                    Jede Chip-ID sollte in einer separaten Zeile stehen.
                </div>
                
                <?php if ($isMobile): ?>
                <!-- NFC-Scanner für mobile Geräte -->
                <div class="mobile-nfc-scanner">
                    <h3><i class="fas fa-wifi"></i> NFC-Chips scannen</h3>
                    <p>Halten Sie Ihr Smartphone an einen NFC-Chip, um ihn automatisch zur Liste hinzuzufügen.</p>
                    
                    <div id="nfc-scan-area">
                        <div class="nfc-icon-large">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <p id="nfc-status">Bereit zum Scannen</p>
                        <button type="button" id="start-nfc-scan" class="btn-nfc-scan">
                            <i class="fas fa-play"></i> NFC-Scan starten
                        </button>
                    </div>
                    
                    <div id="scanned-chips-preview" style="display: none;">
                        <h4><i class="fas fa-check-circle"></i> Gescannte Chips:</h4>
                        <div id="scanned-chips-list"></div>
                        <button type="button" id="clear-scanned" class="btn btn-secondary" style="margin-top: 10px;">
                            <i class="fas fa-trash"></i> Liste leeren
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label for="batch_name">
                            <i class="fas fa-tag"></i> Batch-Name (optional)
                        </label>
                        <input type="text" 
                               id="batch_name" 
                               name="batch_name" 
                               placeholder="z.B. NFC-Batch-2025-01"
                               value="NFC-BATCH-<?= date('Y-m-d') ?>">
                        <small>Ein Name zur Organisation der Chips (z.B. Bestellnummer oder Datum)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="nfc_chip_ids">
                            <i class="fas fa-list"></i> NFC-Chip-IDs *
                        </label>
                        <textarea id="nfc_chip_ids" 
                                  name="nfc_chip_ids" 
                                  required
                                  placeholder="Eine Chip-ID pro Zeile, z.B.:
04:A1:B2:C3:D4:E5:80
04:F6:E7:D8:C9:BA:80
AA:BB:CC:DD:EE:FF:00"></textarea>
                        <small>
                            Fügen Sie die Chip-IDs ein - jede ID in einer neuen Zeile.<br>
                            Erlaubte Zeichen: A-Z, a-z, 0-9, Bindestrich (-), Doppelpunkt (:)<br>
                            Beispiel-Formate: 04:A1:B2:C3:D4:E5:80 oder 04A1B2C3D4E580
                        </small>
                    </div>
                    
                    <button type="submit" name="generate_nfc" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Chips hinzufügen
                    </button>
                </form>
            </div>
            
            <!-- Letzte Batches -->
            <?php if (!empty($recentBatches)): ?>
            <div class="batches-table">
                <h2><i class="fas fa-history"></i> Letzte Batches</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Batch-Name</th>
                            <th>Anzahl Chips</th>
                            <th>Erstellt am</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBatches as $batch): ?>
                        <tr>
                            <td><?= e($batch['batch_name']) ?></td>
                            <td><?= $batch['count'] ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($batch['created'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px;">
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zurück
                </a>
                <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">
                    <i class="fas fa-qrcode"></i> Zur karte
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <?php if ($isMobile): ?>
    <script>
        // NFC-Scanner für mobile Geräte
        let nfcReader = null;
        let isScanning = false;
        let scannedChips = new Set(); // Verhindert Duplikate
        
        const startButton = document.getElementById('start-nfc-scan');
        const nfcStatus = document.getElementById('nfc-status');
        const nfcScanArea = document.getElementById('nfc-scan-area');
        const scannedChipsPreview = document.getElementById('scanned-chips-preview');
        const scannedChipsList = document.getElementById('scanned-chips-list');
        const chipIdsTextarea = document.getElementById('nfc_chip_ids');
        const clearButton = document.getElementById('clear-scanned');
        
        // NFC-Unterstützung prüfen
        if (!('NDEFReader' in window)) {
            nfcStatus.textContent = 'NFC wird von diesem Browser nicht unterstützt';
            nfcStatus.style.color = '#ffc107';
            startButton.disabled = true;
            startButton.innerHTML = '<i class="fas fa-times-circle"></i> NFC nicht verfügbar';
        }
        
        // NFC-Scan starten
        startButton.addEventListener('click', async function() {
            if (isScanning) {
                stopNFCScan();
                return;
            }
            
            try {
                nfcReader = new NDEFReader();
                await nfcReader.scan();
                
                isScanning = true;
                startButton.innerHTML = '<i class="fas fa-stop"></i> Scan beenden';
                startButton.style.background = '#dc3545';
                startButton.style.color = 'white';
                nfcScanArea.classList.add('scanning');
                nfcStatus.textContent = 'Warte auf NFC-Chip...';
                nfcStatus.style.color = '#28a745';
                
                // NFC-Ereignis-Listener
                nfcReader.addEventListener('reading', ({ serialNumber }) => {
                    handleNFCRead(serialNumber);
                });
                
                nfcReader.addEventListener('readingerror', () => {
                    showStatus('Lesefehler - versuchen Sie es erneut', '#dc3545');
                });
                
            } catch (error) {
                console.error('NFC-Fehler:', error);
                showStatus('Fehler beim Starten: ' + error.message, '#dc3545');
                stopNFCScan();
            }
        });
        
        // NFC-Chip gelesen
        function handleNFCRead(serialNumber) {
            if (!serialNumber) {
                showStatus('Keine Chip-ID erkannt', '#ffc107');
                return;
            }
            
            // Chip-ID formatieren (mit Doppelpunkten)
            const chipId = serialNumber;
            
            // Prüfen ob bereits gescannt
            if (scannedChips.has(chipId)) {
                showStatus('Chip bereits gescannt: ' + chipId, '#ffc107');
                // Vibrieren wenn verfügbar
                if ('vibrate' in navigator) {
                    navigator.vibrate([100, 50, 100]);
                }
                return;
            }
            
            // Chip zur Liste hinzufügen
            scannedChips.add(chipId);
            addChipToPreview(chipId);
            updateTextarea();
            
            // Erfolgs-Feedback
            showStatus('✓ Chip erfasst: ' + chipId, '#28a745');
            
            // Vibrieren wenn verfügbar
            if ('vibrate' in navigator) {
                navigator.vibrate(200);
            }
            
            // Status nach 2 Sekunden zurücksetzen
            setTimeout(() => {
                if (isScanning) {
                    showStatus('Bereit für nächsten Chip...', '#28a745');
                }
            }, 2000);
        }
        
        // Chip zur Vorschau hinzufügen
        function addChipToPreview(chipId) {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            const chipItem = document.createElement('div');
            chipItem.className = 'scanned-chip-item';
            chipItem.innerHTML = `
                <span class="chip-id">${escapeHtml(chipId)}</span>
                <span class="chip-time">${timeStr}</span>
            `;
            
            scannedChipsList.appendChild(chipItem);
            scannedChipsPreview.style.display = 'block';
            
            // Automatisch nach unten scrollen
            scannedChipsList.scrollTop = scannedChipsList.scrollHeight;
        }
        
        // Textarea aktualisieren
        function updateTextarea() {
            chipIdsTextarea.value = Array.from(scannedChips).join('\n');
        }
        
        // Status anzeigen
        function showStatus(message, color) {
            nfcStatus.textContent = message;
            nfcStatus.style.color = color;
        }
        
        // NFC-Scan beenden
        function stopNFCScan() {
            isScanning = false;
            startButton.innerHTML = '<i class="fas fa-play"></i> NFC-Scan starten';
            startButton.style.background = 'white';
            startButton.style.color = '#667eea';
            nfcScanArea.classList.remove('scanning');
            nfcStatus.textContent = 'Scan beendet';
            nfcStatus.style.color = 'white';
        }
        
        // Liste leeren
        clearButton.addEventListener('click', function() {
            if (confirm('Möchten Sie alle gescannten Chips aus der Liste entfernen?')) {
                scannedChips.clear();
                scannedChipsList.innerHTML = '';
                scannedChipsPreview.style.display = 'none';
                chipIdsTextarea.value = '';
                showStatus('Liste geleert', '#ffc107');
            }
        });
        
        // HTML escapen
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Beim Verlassen der Seite warnen wenn Chips gescannt wurden
        window.addEventListener('beforeunload', function(e) {
            if (scannedChips.size > 0 && chipIdsTextarea.value) {
                e.preventDefault();
                e.returnValue = 'Sie haben gescannte Chips, die noch nicht gespeichert wurden. Möchten Sie die Seite wirklich verlassen?';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>