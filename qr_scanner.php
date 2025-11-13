<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// AJAX-Anfrage zur QR-Code-Prüfung
if (isset($_POST['check_qr'])) {
    header('Content-Type: application/json');
    
    $scannedData = trim($_POST['qr_code'] ?? '');
    
    if (empty($scannedData)) {
        echo json_encode(['success' => false, 'message' => 'Gescannte Daten sind leer']);
        exit;
    }
    
    // Fall 1: URL wurde gescannt (z.B. https://example.com/public_view.php?token=xyz)
    if (preg_match('/public_view\.php\?token=([a-zA-Z0-9]+)/', $scannedData, $matches)) {
        $token = $matches[1];
        
        $stmt = $pdo->prepare("
            SELECT m.id, m.name, m.qr_code, m.is_activated
            FROM markers m
            WHERE m.public_token = ? AND m.deleted_at IS NULL
        ");
        $stmt->execute([$token]);
        $marker = $stmt->fetch();
        
        if ($marker) {
            echo json_encode([
                'success' => true,
                'type' => 'marker',
                'redirect' => 'view_marker.php?id=' . $marker['id'],
                'message' => 'Marker gefunden: ' . $marker['name']
            ]);
            exit;
        }
    }
    
    // Fall 2: Direkter QR-Code wurde gescannt (z.B. "MRK-001")
    $stmt = $pdo->prepare("
        SELECT m.id, m.name, m.qr_code, m.is_activated
        FROM markers m
        WHERE m.qr_code = ? AND m.deleted_at IS NULL
    ");
    $stmt->execute([$scannedData]);
    $marker = $stmt->fetch();
    
    if ($marker) {
        echo json_encode([
            'success' => true,
            'type' => 'marker',
            'redirect' => 'view_marker.php?id=' . $marker['id'],
            'message' => 'Marker gefunden: ' . $marker['name']
        ]);
        exit;
    }
    
    // Fall 3: QR-Code nicht zugewiesen - prüfen ob er im Pool existiert
    $stmt = $pdo->prepare("SELECT qr_code FROM qr_code_pool WHERE qr_code = ?");
    $stmt->execute([$scannedData]);
    $poolCode = $stmt->fetch();
    
    if ($poolCode) {
        echo json_encode([
            'success' => true,
            'type' => 'new',
            'redirect' => 'create_marker.php?qr_code=' . urlencode($scannedData),
            'message' => 'Neuer QR-Code - Marker erstellen'
        ]);
        exit;
    }
    
    // QR-Code nicht gefunden
    echo json_encode([
        'success' => false,
        'message' => 'QR-Code nicht im System gefunden: ' . htmlspecialchars($scannedData)
    ]);
    exit;
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
    <title>QR-Code Scanner</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .scanner-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .scanner-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .scanner-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .scanner-header h2 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.8em;
        }
        
        .scanner-header p {
            color: #666;
            margin: 0;
        }
        
        #qr-reader {
            border: 3px solid #667eea;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .btn-scan {
            width: 100%;
            padding: 18px;
            font-size: 1.2em;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-scan.btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-scan.btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-scan:active {
            transform: scale(0.98);
        }
        
        .btn-scan:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .scanner-result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 12px;
            font-weight: 500;
            display: none;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .scanner-result.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        
        .scanner-result.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196f3;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .info-box h4 {
            margin: 0 0 15px 0;
            color: #0c5460;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-box ul {
            margin: 0 0 0 20px;
            line-height: 2;
            color: #0c5460;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }
        
        @media (max-width: 480px) {
            .scanner-container {
                padding: 10px;
            }
            
            .scanner-card {
                padding: 20px;
            }
            
            .scanner-header h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="scanner-container">
                <a href="index.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Zurück zur Karte
                </a>
                
                <div class="scanner-card">
                    <div class="scanner-header">
                        <h2><i class="fas fa-qrcode"></i> QR-Code Scanner</h2>
                        <p>Scannen Sie einen QR-Code um den Marker anzuzeigen</p>
                    </div>
                    
                    <div id="qr-reader" style="display: none;"></div>
                    
                    <button type="button" onclick="toggleScanner()" class="btn-scan btn-primary" id="scan-button">
                        <i class="fas fa-camera"></i>
                        <span id="button-text">Kamera starten</span>
                    </button>
                    
                    <div id="scan-result" class="scanner-result"></div>
                    
                    <div class="info-box">
                        <h4>
                            <i class="fas fa-info-circle"></i>
                            So funktioniert's:
                        </h4>
                        <ul>
                            <li>Klicken Sie auf "Kamera starten"</li>
                            <li>Erlauben Sie den Kamera-Zugriff</li>
                            <li>Halten Sie den QR-Code vor die Kamera</li>
                            <li>Der QR-Code wird automatisch gescannt</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        let html5QrCode = null;
        let isScanning = false;
        
        function toggleScanner() {
            if (isScanning) {
                stopScanner();
            } else {
                startScanner();
            }
        }
        
        function startScanner() {
            console.log('📷 Starte QR-Scanner...');
            
            const readerDiv = document.getElementById('qr-reader');
            const button = document.getElementById('scan-button');
            const buttonText = document.getElementById('button-text');
            
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }
            
            button.disabled = true;
            buttonText.textContent = 'Starte Kamera...';
            readerDiv.style.display = 'block';
            
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                console.log('✅ Scanner gestartet');
                isScanning = true;
                button.disabled = false;
                button.className = 'btn-scan btn-danger';
                buttonText.textContent = 'Scanner stoppen';
            }).catch(err => {
                console.error('❌ Fehler:', err);
                showResult('Fehler beim Starten der Kamera: ' + err, 'error');
                button.disabled = false;
                buttonText.textContent = 'Kamera starten';
                readerDiv.style.display = 'none';
            });
        }
        
        function stopScanner() {
            console.log('🛑 Stoppe Scanner...');
            
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    console.log('✅ Scanner gestoppt');
                }).catch(err => {
                    console.error('Fehler beim Stoppen:', err);
                });
            }
            
            isScanning = false;
            document.getElementById('qr-reader').style.display = 'none';
            
            const button = document.getElementById('scan-button');
            const buttonText = document.getElementById('button-text');
            button.className = 'btn-scan btn-primary';
            buttonText.textContent = 'Kamera starten';
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            console.log('✅ QR-Code gescannt:', decodedText);
            
            // Scanner stoppen
            stopScanner();
            
            // Vibrieren wenn möglich
            if ('vibrate' in navigator) {
                navigator.vibrate(200);
            }
            
            // Verarbeiten
            processQRCode(decodedText);
        }
        
        function onScanFailure(error) {
            // Ignoriere Scan-Fehler (zu häufig)
        }
        
        function processQRCode(qrCode) {
            showResult('<i class="fas fa-spinner fa-spin"></i> Verarbeite QR-Code...', 'success');
            
            fetch('qr_scanner.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'check_qr=1&qr_code=' + encodeURIComponent(qrCode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('<i class="fas fa-check-circle"></i> ' + data.message, 'success');
                    
                    // Nach 1 Sekunde weiterleiten
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showResult('<i class="fas fa-times-circle"></i> ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                showResult('<i class="fas fa-exclamation-triangle"></i> Fehler bei der Verarbeitung', 'error');
            });
        }
        
        function showResult(message, type) {
            const resultDiv = document.getElementById('scan-result');
            resultDiv.innerHTML = message;
            resultDiv.className = 'scanner-result ' + type;
            resultDiv.style.display = 'block';
            
            // Scroll zur Nachricht
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function hideResult() {
            document.getElementById('scan-result').style.display = 'none';
        }
        
        // Beim Verlassen aufräumen
        window.addEventListener('beforeunload', function() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop();
            }
        });
    </script>
</body>
</html>