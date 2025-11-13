<?php
require_once 'config.php';

try {
    // Prüfe verschiedene URL-Parameter für NFC-Chip-ID
    $nfc_chip_id = null;
    
    if (isset($_GET['nfc'])) {
        $nfc_chip_id = $_GET['nfc'];
    } elseif (isset($_GET['chip'])) {
        $nfc_chip_id = $_GET['chip'];
    } elseif (isset($_GET['nfc_chip'])) {
        $nfc_chip_id = $_GET['nfc_chip'];
    } elseif (isset($_GET['id'])) {
        $nfc_chip_id = $_GET['id'];
    }
    
    if (!$nfc_chip_id || empty($nfc_chip_id)) {
        throw new Exception('Keine NFC-Chip-ID angegeben');
    }
    
    // Marker anhand NFC-Chip-ID suchen
    $stmt = $pdo->prepare("
        SELECT id, qr_code, is_activated, nfc_chip_id
        FROM markers 
        WHERE nfc_chip_id = ? 
        AND deleted_at IS NULL
    ");
    $stmt->execute([$nfc_chip_id]);
    $marker = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$marker) {
        throw new Exception('Marker mit dieser NFC-Chip-ID nicht gefunden');
    }
    
    // === AUTOMATISCHE AKTIVIERUNG beim ersten Scan ===
    if ($marker['is_activated'] == 0) {
        // UPDATE: is_activated auf 1 setzen
        $updateStmt = $pdo->prepare("
            UPDATE markers 
            SET is_activated = 1,
                gps_captured_by = 'NFC',
                gps_captured_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$marker['id']]);
        
        // Activity Log erstellen
        $logStmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, username, action, details, marker_id, ip_address, user_agent, created_at)
            VALUES (NULL, NULL, 'qr_activated', ?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([
            "NFC-Chip '{$nfc_chip_id}' aktiviert beim ersten Scan",
            $marker['id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }
    
    // === PRÜFE OB MARKER ZU AKTIVER MESSE GEHÖRT ===
    $messeCheck = $pdo->prepare("
        SELECT mc.id as messe_id
        FROM messe_markers mm
        JOIN messe_config mc ON mm.messe_id = mc.id
        WHERE mm.marker_id = ? 
        AND mc.is_active = 1
        LIMIT 1
    ");
    $messeCheck->execute([$marker['id']]);
    $activeMesse = $messeCheck->fetch(PDO::FETCH_ASSOC);
    
    // Wenn Marker zu aktiver Messe gehört, zur Messe-Ansicht weiterleiten
    if ($activeMesse) {
        header('Location: messe_view.php?nfc=' . urlencode($nfc_chip_id));
        exit;
    }
    
    // Sonst normale Weiterleitung zur Public View
    header('Location: public_view.php?nfc=' . urlencode($nfc_chip_id));
    exit;
    
} catch (Exception $e) {
    // Fehlerseite anzeigen
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>NFC-Fehler</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .error-card {
                background: white;
                border-radius: 20px;
                padding: 40px;
                text-align: center;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            
            .error-icon {
                font-size: 4em;
                color: #dc3545;
                margin-bottom: 20px;
            }
            
            .error-title {
                font-size: 1.8em;
                font-weight: 700;
                color: #333;
                margin-bottom: 15px;
            }
            
            .error-message {
                color: #666;
                font-size: 1.1em;
                line-height: 1.6;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="error-title">NFC-Scan fehlgeschlagen</div>
            <div class="error-message">
                <?= htmlspecialchars($e->getMessage()) ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}