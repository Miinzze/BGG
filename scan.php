<?php
/**
 * QR-Code Druckvorlage für GoDEX DT4x
 * Etikettengröße: 3.94" x 5.51" (100mm x 140mm)
 * Optimiert für direktes Drucken auf Thermodrucker
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'qr_generator_helper.php';
requireLogin();

// Drei Modi: Marker-ID, QR-Code direkt ODER Batch
$markerId = $_GET['id'] ?? 0;
$qrCode = $_GET['code'] ?? $_GET['qr'] ?? '';
$batch = $_GET['batch'] ?? '';
$logoId = $_GET['logo'] ?? null; // Optionales Logo

$marker = null;
$isBlankCode = false;
$codes = []; // Für Batch-Modus

// Logo ermitteln
$logoPath = null;
if ($logoId) {
    // Spezifisches Logo
    $logoPath = getLogoById($pdo, $logoId);
} else {
    // Standard-Logo verwenden
    $logoPath = getDefaultLogo($pdo);
}

// Modus 1: Batch - Mehrere QR-Codes
if ($batch) {
    $stmt = $pdo->prepare("SELECT qr_code FROM qr_code_pool WHERE print_batch = ? ORDER BY qr_code");
    $stmt->execute([$batch]);
    $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($codes)) {
        die('Batch nicht gefunden oder keine Codes vorhanden');
    }
    
    // Ersten Code als Standard nehmen für Einzel-Anzeige
    $qrCode = $codes[0];
    $isBlankCode = true;
}
// Modus 2: Marker-ID
elseif ($markerId) {
    $marker = getMarkerById($markerId, $pdo);
    if (!$marker) {
        die('Marker nicht gefunden');
    }
    $qrCode = $marker['qr_code'];
    $publicUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/public_view.php?token=' . $marker['public_token'];
}
// Modus 3: QR-Code direkt
elseif ($qrCode) {
    // Prüfen ob QR-Code existiert und ob er zugewiesen ist
    $stmt = $pdo->prepare("
        SELECT qcp.*, m.id as marker_id, m.name, m.category, m.serial_number, m.public_token
        FROM qr_code_pool qcp
        LEFT JOIN markers m ON qcp.marker_id = m.id AND m.deleted_at IS NULL
        WHERE qcp.qr_code = ?
    ");
    $stmt->execute([$qrCode]);
    $poolCode = $stmt->fetch();
    
    if (!$poolCode) {
        die('QR-Code nicht gefunden');
    }
    
    if ($poolCode['marker_id']) {
        // QR-Code ist zugewiesen - als Marker behandeln
        $marker = [
            'id' => $poolCode['marker_id'],
            'name' => $poolCode['name'],
            'category' => $poolCode['category'],
            'serial_number' => $poolCode['serial_number'],
            'qr_code' => $qrCode,
            'public_token' => $poolCode['public_token']
        ];
        $publicUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/public_view.php?token=' . $marker['public_token'];
    } else {
        // Blanko QR-Code
        $isBlankCode = true;
        $publicUrl = $qrCode;
    }
} else {
    die('Keine ID, QR-Code oder Batch angegeben');
}

// Branded QR-Code generieren
try {
    $qrApiUrl = generateBrandedQRCode($publicUrl, $logoPath, 400);
} catch (Exception $e) {
    // Fallback auf normale API
    $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=' . urlencode($publicUrl);
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
    <title>QR-Code - <?= $batch ? "Batch: $batch" : ($isBlankCode ? e($qrCode) : e($marker['name'])) ?></title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        
        .print-container {
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
            border-radius: 10px;
            page-break-after: always;
        }
        
        .marker-name {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #e63312;
        }
        
        .qr-code-number {
            font-size: 28px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #1976d2;
            margin: 20px 0;
        }
        
        .marker-info {
            font-size: 18px;
            margin-bottom: 30px;
            color: #666;
        }
        
        .qr-code {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .qr-code img {
            max-width: 400px;
            border: 3px solid #333;
            padding: 15px;
            background: white;
        }
        
        .instructions {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
            line-height: 1.6;
        }
        
        .blank-code-instructions {
            font-size: 16px;
            color: #1976d2;
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #1976d2;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            font-size: 12px;
            color: #999;
        }
        
        .no-print {
            margin-top: 30px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php if ($batch): ?>
        <!-- Batch-Modus: Alle Codes drucken -->
        <?php foreach ($codes as $index => $batchQrCode): 
            $batchPublicUrl = $batchQrCode;
            try {
                $batchQrApiUrl = generateBrandedQRCode($batchPublicUrl, $logoPath, 400);
            } catch (Exception $e) {
                $batchQrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=' . urlencode($batchPublicUrl);
            }
        ?>
        <div class="print-container">
            <div class="qr-code-number"><?= e($batchQrCode) ?></div>
            
            <div class="blank-code-instructions">
                <strong>BLANKO QR-CODE</strong><br>
                Noch nicht zugewiesen
            </div>
            
            <div class="qr-code">
                <img src="<?= $batchQrApiUrl ?>" alt="QR Code">
            </div>
            
            <div class="instructions">
                <strong>So aktivieren Sie diesen QR-Code:</strong><br>
                1. QR-Code am Gerät/Standort anbringen<br>
                2. Mit Smartphone scannen<br>
                3. GPS-Position erfassen<br>
                4. Gerätedaten eingeben<br>
                5. Marker wird automatisch aktiviert
            </div>
            
            <div class="footer">
                Marker System<br>
                Erstellt am: <?= date('d.m.Y H:i') ?> Uhr<br>
                Batch: <?= e($batch) ?> (<?= $index + 1 ?> von <?= count($codes) ?>)
            </div>
        </div>
        <?php endforeach; ?>
        
    <?php else: ?>
        <!-- Einzel-Modus -->
        <div class="print-container">
            <?php if ($isBlankCode): ?>
                <!-- Blanko QR-Code -->
                <div class="qr-code-number"><?= e($qrCode) ?></div>
                
                <div class="blank-code-instructions">
                    <strong>BLANKO QR-CODE</strong><br>
                    Noch nicht zugewiesen
                </div>
                
                <div class="qr-code">
                    <img src="<?= $qrApiUrl ?>" alt="QR Code">
                </div>
                
                <div class="instructions">
                    <strong>So aktivieren Sie diesen QR-Code:</strong><br>
                    1. QR-Code am Gerät/Standort anbringen<br>
                    2. Mit Smartphone scannen<br>
                    3. GPS-Position erfassen<br>
                    4. Gerätedaten eingeben<br>
                    5. Marker wird automatisch aktiviert
                </div>
                
            <?php else: ?>
                <!-- Zugewiesener Marker -->
                <div class="marker-name">
                    <?= e($marker['name']) ?>
                </div>
                
                <div class="marker-info">
                    <?php if ($marker['category']): ?>
                        Kategorie: <?= e($marker['category']) ?><br>
                    <?php endif; ?>
                    
                    <?php if ($marker['serial_number']): ?>
                        Seriennummer: <?= e($marker['serial_number']) ?><br>
                    <?php endif; ?>
                    
                    QR-Code: <code style="font-family: 'Courier New', monospace; font-weight: bold;"><?= e($marker['qr_code']) ?></code>
                </div>
                
                <div class="qr-code">
                    <img src="<?= $qrApiUrl ?>" alt="QR Code">
                </div>
                
                <div class="instructions">
                    <strong>Scannen Sie diesen QR-Code mit Ihrem Smartphone</strong><br>
                    für sofortigen Zugriff auf alle Geräteinformationen,<br>
                    Wartungshistorie und Standortdaten.
                </div>
            <?php endif; ?>
            
            <div class="footer">
                Marker System<br>
                Erstellt am: <?= date('d.m.Y H:i') ?> Uhr<br>
                <?php if (!$isBlankCode): ?>
                    Marker-ID: <?= $marker['id'] ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="no-print">
        <button onclick="window.print()" style="padding: 15px 30px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px; margin: 10px;">
            <i class="fas fa-print"></i> Drucken <?= $batch ? '(' . count($codes) . ' Codes)' : '' ?>
        </button>
        <button onclick="window.close()" style="padding: 15px 30px; font-size: 16px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 5px; margin: 10px;">
            Schließen
        </button>
    </div>
</body>
</html>