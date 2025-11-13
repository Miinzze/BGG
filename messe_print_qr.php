<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$markerId = isset($_GET['m']) ? intval($_GET['m']) : 0;

// Aktive Messe laden
$stmt = $pdo->query("SELECT * FROM messe_config WHERE is_active = 1 LIMIT 1");
$messe = $stmt->fetch();

if (!$messe) {
    die("Aktuell ist keine Messe aktiv.");
}

// Marker laden
$stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
$stmt->execute([$markerId]);
$marker = $stmt->fetch();

if (!$marker) {
    die("Gerät nicht gefunden.");
}

// URL für QR-Code (zeigt direkt auf das Gerät in der Messe)
$url = 'https://' . $_SERVER['HTTP_HOST'] . '/messe_view.php?m=' . $markerId;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=' . urlencode($url);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Code Drucken - <?= htmlspecialchars($marker['name']) ?></title>
    <style>
        @media print {
            .no-print { display: none; }
            @page { margin: 0; }
            body { margin: 1cm; }
        }
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .qr-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            border: 2px dashed #ccc;
            border-radius: 12px;
            background: white;
        }
        .qr-code {
            margin: 30px 0;
        }
        .qr-code img {
            max-width: 100%;
            height: auto;
            border: 10px solid white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .info {
            color: #666;
            margin: 20px 0;
            font-size: 16px;
        }
        .btn-print {
            background: #007bff;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin: 20px 10px;
        }
        .btn-print:hover {
            background: #0056b3;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .messe-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h1><?= htmlspecialchars($marker['name']) ?></h1>
        
        <div class="messe-info">
            <strong><?= htmlspecialchars($messe['name']) ?></strong><br>
            QR-Code: <?= htmlspecialchars($marker['qr_code']) ?>
        </div>
        
        <div class="qr-code">
            <img src="<?= $qrUrl ?>" alt="QR-Code">
        </div>
        
        <div class="info">
            Scannen Sie diesen QR-Code, um Details zu diesem Gerät zu sehen
        </div>
        
        <div class="no-print">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Drucken
            </button>
            <a href="messe_admin.php" class="btn-back">
                Zurück
            </a>
        </div>
    </div>
</body>
</html>