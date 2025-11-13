<?php
require_once 'config.php';

// Helper-Funktion ZUERST definieren
function adjustBrightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) != 6) {
        return '#000000';
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// Hole aktive Messe
$stmt = $pdo->query("SELECT * FROM messe_config WHERE is_active = 1 LIMIT 1");
$messe = $stmt->fetch();

// Fallback: Zeige Fehlermeldung wenn keine Messe aktiv
if (!$messe) {
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <meta charset="UTF-8">
        <?= csrfMetaTag() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Keine aktive Messe</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: <?= htmlspecialchars($hero_text_color) ?>;
                text-align: center;
                padding: 20px;
            }
            .error-container {
                max-width: 600px;
                background: rgba(255,255,255,0.1);
                backdrop-filter: blur(10px);
                padding: 60px 40px;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            .error-icon {
                font-size: 80px;
                margin-bottom: 30px;
                opacity: 0.8;
            }
            h1 { font-size: 2.5rem; margin-bottom: 20px; }
            p { font-size: 1.2rem; line-height: 1.6; opacity: 0.9; margin-bottom: 30px; }
            .btn {
                display: inline-block;
                padding: 15px 40px;
                background: white;
                color: #667eea;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 600;
                transition: all 0.3s;
            }
            .btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            }
    
        
        /* ============================================ */
        /* NEUES DEVICE MODAL DESIGN (wie HO-MA Bild) */
        /* ============================================ */
        
        .modal-content { max-width: 1200px !important; width: 95%; }
        .device-detail-layout { display: grid; grid-template-columns: 400px 1fr; gap: 30px; margin-bottom: 30px; }
        .device-image-section { position: relative; }
        .device-image-container { 
            position: relative; 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e9ecef; max-width: 400px; 
        }
        .device-main-image { width: 100%; height: auto; display: block; object-fit: contain; padding: 20px; max-height: 400px; }
        .image-watermark { 
            position: absolute; 
            bottom: 10px; 
            left: 50%; 
            transform: translateX(-50%); 
            background: rgba(0,0,0,0.7); 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-size: 11px; 
            color: white; 
            font-weight: 500; 
        }
        .device-image-placeholder { 
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); 
            border-radius: 15px; 
            min-height: 300px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            color: #adb5bd; 
        }
        .device-image-placeholder i { font-size: 60px; margin-bottom: 15px; }
        .device-info-section { display: flex; flex-direction: column; gap: 25px; }
        .product-header { padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
        .product-name { 
            font-size: 2.5rem; 
            font-weight: 700; 
            color: #0066cc; 
            margin: 0 0 10px 0; 
            line-height: 1.2; 
        }
        .product-description { font-size: 1.1rem; color: #495057; line-height: 1.6; margin: 0; }
        .badge-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; padding: 20px 0; }
        .icon-badge { 
            background-color: #FFD700; 
            padding: 20px; 
            border-radius: 10px; 
            text-align: center; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            transition: all 0.3s; 
        }
        .icon-badge:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }
        .badge-icon { font-size: 32px; margin-bottom: 8px; color: #000; }
        .badge-text { 
            font-size: 13px; 
            font-weight: 700; 
            color: #000; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .tech-data-section { 
            background: #f8f9fa; 
            padding: 25px; 
            border-radius: 12px; 
            border: 2px solid #e9ecef; 
        }
        .tech-header { 
            font-size: 1.3rem; 
            font-weight: 700; 
            color: #0066cc; 
            margin: 0 0 20px 0; 
        }
        .tech-table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
        }
        .tech-table thead { background: white; color: white; }
        .tech-table th { padding: 12px 15px; text-align: left; font-weight: 600; font-size: 14px; }
        .tech-table td { padding: 12px 15px; border-bottom: 1px solid #e9ecef; }
        .tech-table tbody tr:hover { background: #f8f9fa; }
        .tech-table .field-name { font-weight: 600; color: #495057; }
        .additional-info { background: #f8f9fa; padding: 30px; border-radius: 12px; margin-top: 30px; }
        .info-box { margin-bottom: 20px; }
        .info-box h4 { color: #0066cc; font-size: 1.1rem; margin: 0 0 8px 0; font-weight: 600; }
        .info-box p { color: #495057; line-height: 1.6; margin: 0; font-size: 0.95rem; }
        
        @media (max-width: 968px) {
            .device-detail-layout { grid-template-columns: 1fr; }
            .badge-grid { grid-template-columns: repeat(2, 1fr); }
            .product-name { font-size: 2rem; }
        }
        
        @media (max-width: 576px) {
            .badge-grid { grid-template-columns: 1fr; }
            .product-name { font-size: 1.5rem; }
        }

    </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h1>Keine aktive Messe</h1>
            <p>Aktuell ist keine Messe aktiv. Bitte erstellen und aktivieren Sie zuerst eine Messe in der Administration.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="messe_admin.php" class="btn">
                    <i class="fas fa-cog"></i> Zur Messe-Verwaltung
                </a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Ab hier: $messe ist garantiert gesetzt

// Marker-ID aus URL (unterstützt QR-Scan, NFC-Scan und direkte ID)
$marker_id = null;
$scan_method = null;

// Prüfe QR-Code Parameter
if (isset($_GET['qr']) || isset($_GET['code']) || isset($_GET['qr_code'])) {
    $qr_code = $_GET['qr'] ?? $_GET['code'] ?? $_GET['qr_code'];
    $scan_method = 'qr';
    
    // Hole Marker-ID anhand QR-Code
    $stmt = $pdo->prepare("SELECT id FROM markers WHERE qr_code = ? AND deleted_at IS NULL");
    $stmt->execute([$qr_code]);
    $result = $stmt->fetch();
    if ($result) {
        $marker_id = intval($result['id']);
    }
}

// Prüfe NFC-Chip Parameter
if (!$marker_id && (isset($_GET['nfc']) || isset($_GET['nfc_chip']) || isset($_GET['chip']))) {
    $nfc_chip = $_GET['nfc'] ?? $_GET['nfc_chip'] ?? $_GET['chip'];
    $scan_method = 'nfc';
    
    // Hole Marker-ID anhand NFC-Chip-ID
    $stmt = $pdo->prepare("SELECT id FROM markers WHERE nfc_chip_id = ? AND deleted_at IS NULL");
    $stmt->execute([$nfc_chip]);
    $result = $stmt->fetch();
    if ($result) {
        $marker_id = intval($result['id']);
    }
}

// Fallback: Direkte Marker-ID
if (!$marker_id && isset($_GET['m'])) {
    $marker_id = intval($_GET['m']);
    $scan_method = 'direct';
}

// Alle Geräte der Messe laden
$stmt = $pdo->prepare("
    SELECT mm.*, m.name as marker_name, m.category, m.qr_code, m.serial_number
    FROM messe_markers mm
    JOIN markers m ON mm.marker_id = m.id
    WHERE mm.messe_id = ?
    ORDER BY mm.is_featured DESC, mm.display_order ASC
");
$stmt->execute([$messe['id']]);
$markers = $stmt->fetchAll();

// Top 3 meist gescannte Geräte
$topDevices = [];
try {
    $stmt = $pdo->prepare("
        SELECT m.name, SUM(s.scan_count) as total_scans
        FROM messe_scan_stats s
        JOIN markers m ON s.marker_id = m.id
        WHERE s.messe_id = ?
        GROUP BY s.marker_id
        ORDER BY total_scans DESC
        LIMIT 3
    ");
    $stmt->execute([$messe['id']]);
    $topDevices = $stmt->fetchAll();
} catch (Exception $e) {
    // Ignoriere Fehler wenn Tabelle nicht existiert
}

// Design-Variablen mit Fallbacks
$primary_color = $messe['primary_color'] ?? '#667eea';
$secondary_color = $messe['secondary_color'] ?? '#764ba2';
$accent_color = $messe['accent_color'] ?? '#667eea';
$button_color = $messe['button_color'] ?? '#28a745';
$background_color = $messe['background_color'] ?? '#ffffff';
$text_color = $messe['text_color'] ?? '#000000';
$font_family = $messe['font_family'] ?? "'Segoe UI', sans-serif";
$background_style = $messe['background_style'] ?? 'gradient';

// Background CSS generieren
if ($background_style === 'solid') {
    $bg_css = htmlspecialchars($background_color);
} elseif ($background_style === 'gradient') {
    $bg_css = "linear-gradient(135deg, {$primary_color} 0%, {$secondary_color} 100%)";
} elseif ($background_style === 'image' && !empty($messe['background_image_path'])) {
    $bg_css = "url('{$messe['background_image_path']}') center/cover fixed";
} else {
    $bg_css = "linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
}

// Hero Background
if (!empty($messe['hero_image_path']) && file_exists($messe['hero_image_path'])) {
    $hero_bg = "linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('{$messe['hero_image_path']}') center/cover";
} else {
    // Kein Hero-Bild hochgeladen -> transparenter Hintergrund
    $hero_bg = "transparent";
}

// Hero-Textfarbe: weiß bei Bild, dunkle Farbe bei transparentem Hintergrund
if (!empty($messe['hero_image_path']) && file_exists($messe['hero_image_path'])) {
    $hero_text_color = "white";
} else {
    $hero_text_color = $text_color;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <?= csrfMetaTag() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($messe['name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/3d-features.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: <?= $font_family ?>;
            background: <?= $bg_css ?>;
            color: <?= htmlspecialchars($text_color) ?>;
            min-height: 100vh;
        }
        
        .hero {
            position: relative;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
            background: <?= $hero_bg ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
        }
        
        .hero-logo {
            max-width: 300px;
            max-height: 120px;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        }
        
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }
        
        .hero-description {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            max-width: 700px;
            line-height: 1.6;
            opacity: 0.95;
        }
        
        .container {
            max-width: 1200px;
            margin: -50px auto 0;
            padding: 0 20px 40px;
            position: relative;
            z-index: 10;
        }
        
        .top-devices {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .top-devices h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .top-device-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 12px 0;
            border-radius: 12px;
            border-left: 4px solid <?= htmlspecialchars($accent_color) ?>;
        }
        
        .device-rank {
            font-size: 28px;
            font-weight: bold;
            color: <?= htmlspecialchars($accent_color) ?>;
            width: 50px;
        }
        
        .device-name {
            flex: 1;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .device-scans {
            background: <?= htmlspecialchars($accent_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .device-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            transition: transform 0.3s;
            cursor: pointer;
            position: relative; /* Für absolute positionierten featured-badge */
        }
        
        .device-card:hover {
            transform: translateY(-10px);
        }
        
        .device-card.featured {
            border: 3px solid <?= htmlspecialchars($accent_color) ?>;
        }
        
        .device-image {
            display: none; /* Lila Bildbereich ausgeblendet */
        }
        
        .device-image i {
            display: none;
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: <?= htmlspecialchars($accent_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .device-content {
            padding: 30px 25px 25px; /* Mehr Padding oben, da kein Bild */
        }
        
        .device-content h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: #2c3e50;
        }
        
        .device-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .device-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #888;
        }
        
        .btn-view {
            width: 100%;
            padding: 15px;
            background: <?= htmlspecialchars($button_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-view:hover {
            background: <?= adjustBrightness($button_color, -20) ?>;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.4s;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            background: linear-gradient(135deg, <?= htmlspecialchars($primary_color) ?> 0%, <?= htmlspecialchars($secondary_color) ?> 100%);
            color: <?= htmlspecialchars($hero_text_color) ?>;
            padding: 40px 30px;
            border-radius: 20px 20px 0 0;
            position: relative;
        }
        
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 40px 30px;
        }
        
        .info-section {
            margin-bottom: 40px;
        }
        
        .info-section h3 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .custom-fields-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .field-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid <?= htmlspecialchars($accent_color) ?>;
        }
        
        .field-icon {
            font-size: 28px;
            color: <?= htmlspecialchars($accent_color) ?>;
            margin-bottom: 10px;
        }
        
        .field-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .field-value {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .interest-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 40px;
        }
        
        .btn-interest {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 40px;
            background: <?= htmlspecialchars($button_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-interest:hover {
            background: <?= adjustBrightness($button_color, -20) ?>;
            transform: translateY(-3px);
        }
        
        .lead-form-section {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: <?= htmlspecialchars($accent_color) ?>;
        }
        
        .btn-submit {
            width: 100%;
            padding: 18px;
            background: <?= htmlspecialchars($button_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-submit:hover {
            background: <?= adjustBrightness($button_color, -20) ?>;
        }
        
        .success-message {
            text-align: center;
            padding: 60px 30px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: <?= htmlspecialchars($button_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 25px;
            animation: scaleIn 0.5s;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        
        .footer {
            text-align: center;
            padding: 40px 20px;
            background: rgba(0,0,0,0.05);
            margin-top: 60px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .social-links a {
            width: 50px;
            height: 50px;
            background: <?= htmlspecialchars($accent_color) ?>;
            color: <?= htmlspecialchars($hero_text_color) ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .social-links a:hover {
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .hero {
                min-height: 50vh;
                padding: 40px 20px;
            }
            .device-grid {
                grid-template-columns: 1fr;
            }
            .custom-fields-grid {
                grid-template-columns: 1fr;
            }
        }

        
        /* ============================================ */
        /* NEUES DEVICE MODAL DESIGN (wie HO-MA Bild) */
        /* ============================================ */
        
        .modal-content { max-width: 1200px !important; width: 95%; }
        .device-detail-layout { display: grid; grid-template-columns: 400px 1fr; gap: 30px; margin-bottom: 30px; }
        .device-image-section { position: relative; }
        .device-image-container { 
            position: relative; 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e9ecef; max-width: 400px; 
        }
        .device-main-image { width: 100%; height: auto; display: block; object-fit: contain; padding: 20px; max-height: 400px; }
        .image-watermark { 
            position: absolute; 
            bottom: 10px; 
            left: 50%; 
            transform: translateX(-50%); 
            background: rgba(0,0,0,0.7); 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-size: 11px; 
            color: white; 
            font-weight: 500; 
        }
        .device-image-placeholder { 
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); 
            border-radius: 15px; 
            min-height: 300px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            color: #adb5bd; 
        }
        .device-image-placeholder i { font-size: 60px; margin-bottom: 15px; }
        .device-info-section { display: flex; flex-direction: column; gap: 25px; }
        .product-header { padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
        .product-name { 
            font-size: 2.5rem; 
            font-weight: 700; 
            color: #0066cc; 
            margin: 0 0 10px 0; 
            line-height: 1.2; 
        }
        .product-description { font-size: 1.1rem; color: #495057; line-height: 1.6; margin: 0; }
        .badge-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; padding: 20px 0; }
        .icon-badge { 
            background-color: #FFD700; 
            padding: 20px; 
            border-radius: 10px; 
            text-align: center; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            transition: all 0.3s; 
        }
        .icon-badge:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }
        .badge-icon { font-size: 32px; margin-bottom: 8px; color: #000; }
        .badge-text { 
            font-size: 13px; 
            font-weight: 700; 
            color: #000; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .tech-data-section { 
            background: #f8f9fa; 
            padding: 25px; 
            border-radius: 12px; 
            border: 2px solid #e9ecef; 
        }
        .tech-header { 
            font-size: 1.3rem; 
            font-weight: 700; 
            color: #0066cc; 
            margin: 0 0 20px 0; 
        }
        .tech-table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
        }
        .tech-table thead { background: white; color: white; }
        .tech-table th { padding: 12px 15px; text-align: left; font-weight: 600; font-size: 14px; }
        .tech-table td { padding: 12px 15px; border-bottom: 1px solid #e9ecef; }
        .tech-table tbody tr:hover { background: #f8f9fa; }
        .tech-table .field-name { font-weight: 600; color: #495057; }
        .additional-info { background: #f8f9fa; padding: 30px; border-radius: 12px; margin-top: 30px; }
        .info-box { margin-bottom: 20px; }
        .info-box h4 { color: #0066cc; font-size: 1.1rem; margin: 0 0 8px 0; font-weight: 600; }
        .info-box p { color: #495057; line-height: 1.6; margin: 0; font-size: 0.95rem; }
        
        @media (max-width: 968px) {
            .device-detail-layout { grid-template-columns: 1fr; }
            .badge-grid { grid-template-columns: repeat(2, 1fr); }
            .product-name { font-size: 2rem; }
        }
        
        @media (max-width: 576px) {
            .badge-grid { grid-template-columns: 1fr; }
            .product-name { font-size: 1.5rem; }
        }

    </style>
</head>
<body>
    <div class="hero">
        <?php if (!empty($messe['logo_path']) && file_exists($messe['logo_path'])): ?>
            <img src="<?= htmlspecialchars($messe['logo_path']) ?>" alt="Logo" class="hero-logo">
        <?php endif; ?>
        
        <h1><?= htmlspecialchars($messe['name']) ?></h1>
        
        <?php if (!empty($messe['welcome_text'])): ?>
            <p class="hero-description"><?= nl2br(htmlspecialchars($messe['welcome_text'])) ?></p>
        <?php elseif (!empty($messe['description'])): ?>
            <p class="hero-description"><?= nl2br(htmlspecialchars($messe['description'])) ?></p>
        <?php endif; ?>
        
        <?php if (!empty($messe['start_date'])): ?>
            <p style="margin-top: 20px; font-size: 1.1rem;">
                <i class="fas fa-calendar-alt"></i> 
                <?= date('d.m.Y', strtotime($messe['start_date'])) ?>
                <?php if (!empty($messe['end_date'])): ?>
                    - <?= date('d.m.Y', strtotime($messe['end_date'])) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <!-- <?php if (!empty($topDevices)): ?>
        <div class="top-devices">
            <h2><i class="fas fa-fire"></i> Meist angeschaute Produkte</h2>
            <?php foreach ($topDevices as $index => $device): ?>
                <div class="top-device-item">
                    <div class="device-rank">#<?= $index + 1 ?></div>
                    <div class="device-name"><?= htmlspecialchars($device['name']) ?></div>
                    <div class="device-scans"><?= $device['total_scans'] ?> Aufrufe</div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?> -->
        
        <h2 style="text-align: center; font-size: 2.5rem; margin: 40px 0 30px; color: <?= htmlspecialchars($hero_text_color) ?>;">
            Unsere Produkte
        </h2>
        
        <?php if (empty($markers)): ?>
            <div style="text-align: center; padding: 60px 20px; background: rgba(255,255,255,0.1); border-radius: 20px; color: <?= htmlspecialchars($hero_text_color) ?>;">
                <i class="fas fa-box-open" style="font-size: 60px; margin-bottom: 20px; opacity: 0.5;"></i>
                <h3>Noch keine Geräte hinzugefügt</h3>
                <p style="opacity: 0.8; margin-top: 10px;">Fügen Sie Geräte zur Messe hinzu, um sie hier anzuzeigen.</p>
            </div>
        <?php else: ?>
            <div class="device-grid">
                <?php foreach ($markers as $marker): ?>
                    <div class="device-card <?= $marker['is_featured'] ? 'featured' : '' ?>" 
                         onclick="showDeviceModal(<?= $marker['marker_id'] ?>)">
                        
                        <?php if ($marker['is_featured']): ?>
                            <div class="featured-badge">
                                <i class="fas fa-star"></i> FEATURED
                            </div>
                        <?php endif; ?>
                        
                        <div class="device-image">
                            <i class="fas fa-cog"></i>
                        </div>
                        
                        <div class="device-content">
                            <h3><?= htmlspecialchars($marker['custom_title'] ?: $marker['marker_name']) ?></h3>
                            
                            <p>
                                <?php 
                                // Zeige zuerst custom_description, dann additional_info, dann "Keine Beschreibung verfügbar"
                                if (!empty($marker['custom_description'])) {
                                    $desc = $marker['custom_description'];
                                } elseif (!empty($marker['additional_info'])) {
                                    $desc = strip_tags($marker['additional_info']); // HTML-Tags entfernen
                                } else {
                                    $desc = 'Keine Beschreibung verfügbar';
                                }
                                echo nl2br(htmlspecialchars(substr($desc, 0, 120)));
                                echo strlen($desc) > 120 ? '...' : '';
                                ?>
                            </p>
                            
                            <div class="device-meta">
                                <?php if ($marker['category']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span><?= htmlspecialchars($marker['category']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($marker['qr_code']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-qrcode"></i>
                                        <span><?= htmlspecialchars($marker['qr_code']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <button class="btn-view" onclick="showDeviceModal(<?= $marker['marker_id'] ?>); event.stopPropagation();">
                                <i class="fas fa-info-circle"></i>
                                Mehr erfahren
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <?php 
        $socialLinks = !empty($messe['social_links']) ? json_decode($messe['social_links'], true) : null;
        if ($socialLinks && is_array($socialLinks) && array_filter($socialLinks)): 
        ?>
        <div class="social-links">
            <?php if (!empty($socialLinks['facebook'])): ?>
                <a href="<?= htmlspecialchars($socialLinks['facebook']) ?>" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($socialLinks['instagram'])): ?>
                <a href="<?= htmlspecialchars($socialLinks['instagram']) ?>" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($socialLinks['linkedin'])): ?>
                <a href="<?= htmlspecialchars($socialLinks['linkedin']) ?>" target="_blank">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($socialLinks['youtube'])): ?>
                <a href="<?= htmlspecialchars($socialLinks['youtube']) ?>" target="_blank">
                    <i class="fab fa-youtube"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <p style="color: <?= htmlspecialchars($hero_text_color) ?>; opacity: 0.9;">
            <?= htmlspecialchars($messe['footer_text'] ?? '© 2025 Alle Rechte vorbehalten') ?>
        </p>
    </div>
    
    <div id="deviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close-btn" onclick="closeModal()">×</button>
                <h2 id="modalTitle"></h2>
                <p id="modalCategory"></p>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
    
    <script>
        let currentMarker = null;
        
        function showDeviceModal(markerId) {
            fetch('messe_get_marker_details.php?marker_id=' + markerId + '&messe_id=<?= $messe['id'] ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentMarker = data.marker;
                        renderDeviceInfo();
                        document.getElementById('deviceModal').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                    }
                })
                .catch(error => {
                    console.error('Fehler:', error);
                    alert('Fehler beim Laden der Details');
                });
        }
        
        function renderDeviceInfo() {
            const marker = currentMarker;
            
            console.log('Rendering device info for marker:', marker);
            console.log('Badges:', marker.badges);
            
            document.getElementById('modalTitle').textContent = marker.custom_title || marker.marker_name;
            document.getElementById('modalCategory').innerHTML = marker.category ? 
                `<i class="fas fa-tag"></i> ${marker.category}` : '';
            
            let html = '';
            
            // ===== HAUPTLAYOUT: BILD LINKS, INFO RECHTS =====
            html += `<div class="device-detail-layout">`;
            
            // LINKE SEITE: Gerätebild
            html += `<div class="device-image-section">`;
            if (marker.device_image) {
                html += `
                    <div class="device-image-container">
                        <img src="${marker.device_image}" alt="${marker.marker_name}" class="device-main-image">
                        <div class="image-watermark">Abbildung ähnlich</div>
                    </div>
                `;
            } else {
                html += `
                    <div class="device-image-placeholder">
                        <i class="fas fa-image"></i>
                        <p>Kein Bild verfügbar</p>
                    </div>
                `;
            }
            html += `</div>`;
            
            // RECHTE SEITE: Produktinfo
            html += `<div class="device-info-section">`;
            
            // Produktname und Beschreibung
            html += `
                <div class="product-header">
                    <h1 class="product-name">${marker.custom_title || marker.marker_name}</h1>
                    ${marker.custom_description || marker.additional_info ? `<p class="product-description">${marker.custom_description || marker.additional_info}</p>` : ''}
                </div>
            `;
            
            // ICON-BADGES
            console.log('Checking badges condition:', marker.badges, Array.isArray(marker.badges), marker.badges?.length);
            if (marker.badges && Array.isArray(marker.badges) && marker.badges.length > 0) {
                console.log('Rendering badges...');
                html += `<div class="badge-grid">`;
                marker.badges.forEach(badge => {
                    console.log('Badge:', badge);
                    html += `
                        <div class="icon-badge" style="background-color: ${badge.badge_color || '#FFD700'}">
                            ${badge.badge_icon ? `<div class="badge-icon"><i class="${badge.badge_icon}"></i></div>` : ''}
                            <div class="badge-text">${badge.badge_text}</div>
                        </div>
                    `;
                });
                html += `</div>`;
            } else {
                console.log('No badges to display');
            }
            
            // TECHNISCHE DATEN TABELLE
            if (marker.custom_fields && marker.custom_fields.length > 0) {
                html += `
                    <div class="tech-data-section">
                        <h3 class="tech-header">Technische Daten</h3>
                        <table class="tech-table">
                            <thead>
                                <tr>
                                    <th>Bezeichnung</th>
                                    <th>PRP</th>
                                    <th>ESP</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                marker.custom_fields.forEach(field => {
                    html += `
                        <tr>
                            <td class="field-name">${field.field_name}</td>
                            <td>${field.field_value}</td>
                            <td>${field.field_value}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            html += `</div>`; // Ende device-info-section
            html += `</div>`; // Ende device-detail-layout
            
            // ===== 3D-VIEWER & AR-FEATURES =====
            // Prüfe ob 3D-Modell vorhanden
            if (marker.model_3d_url) {
                html += `
                    <div style="margin-top: 30px;">
                        <h3 style="color: #2c3e50; margin-bottom: 20px; font-size: 1.5rem;">
                            <i class="fas fa-cube"></i> 360° Produkt-Ansicht
                        </h3>
                        <div class="viewer-3d-container" id="viewer3d-${marker.marker_id}">
                            <div class="badge-360">
                                <i class="fas fa-sync-alt"></i>
                                360° Ansicht
                            </div>
                            <div class="loading-3d">
                                <div class="spinner"></div>
                                <p>Lade 3D-Modell...</p>
                            </div>
                        </div>
                        <div class="viewer-controls">
                            <button onclick="toggle3DAutoRotate()" title="Auto-Rotation">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button onclick="reset3DCamera()" title="Ansicht zurücksetzen">
                                <i class="fas fa-home"></i>
                            </button>
                            <button onclick="launch3DAR()" title="AR-Ansicht starten" class="btn-ar">
                                <i class="fas fa-cube"></i>
                                AR ansehen
                            </button>
                        </div>
                    </div>
                `;
                
                // AR-Konfigurator
                html += `
                    <div class="ar-configurator">
                        <h3><i class="fas fa-palette"></i> Produkt-Konfigurator</h3>
                        
                        <div class="config-section">
                            <label>Farbe auswählen</label>
                            <div class="color-picker">
                                <div class="color-option" style="background: #ff4444;" onclick="change3DColor('#ff4444')"></div>
                                <div class="color-option" style="background: #4444ff;" onclick="change3DColor('#4444ff')"></div>
                                <div class="color-option" style="background: #44ff44;" onclick="change3DColor('#44ff44')"></div>
                                <div class="color-option" style="background: #ffff44;" onclick="change3DColor('#ffff44')"></div>
                                <div class="color-option" style="background: #ff44ff;" onclick="change3DColor('#ff44ff')"></div>
                                <div class="color-option" style="background: #ffffff; border: 1px solid #ddd;" onclick="change3DColor('#ffffff')"></div>
                                <div class="color-option" style="background: #000000;" onclick="change3DColor('#000000')"></div>
                                <div class="color-option" style="background: #ff8800;" onclick="change3DColor('#ff8800')"></div>
                            </div>
                        </div>
                        
                        <div class="config-section">
                            <label>Größe</label>
                            <div class="config-controls">
                                <div class="slider-control">
                                    <input type="range" min="0.5" max="2" step="0.1" value="1" 
                                           onchange="change3DScale(this.value)" id="scaleSlider">
                                    <span class="slider-value" id="scaleValue">1.0x</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-section">
                            <label>Rotation (Grad)</label>
                            <div class="config-controls">
                                <div class="slider-control">
                                    <input type="range" min="0" max="360" step="15" value="0" 
                                           onchange="change3DRotation(this.value)" id="rotationSlider">
                                    <span class="slider-value" id="rotationValue">0°</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // ZUSATZINFORMATIONEN (aus Datenbank)
            if (marker.additional_info) {
                html += `
                    <div class="additional-info">
                        <div class="info-box">
                            ${marker.additional_info.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                `;
            }
            
            // KONTAKT-FORMULAR
            <?php if (!empty($messe['show_lead_capture'])): ?>
            html += `
                <div class="interest-section">
                    <h3>💼 Interesse geweckt?</h3>
                    <p>Hinterlassen Sie uns Ihre Kontaktdaten und wir melden uns zeitnah bei Ihnen mit weiteren Informationen zu diesem Produkt.</p>
                    <button class="btn-interest" onclick="showLeadForm()">
                        <i class="fas fa-envelope"></i>
                        Jetzt Kontakt aufnehmen
                    </button>
                </div>
            `;
            <?php endif; ?>
            
            document.getElementById('modalBody').innerHTML = html;
        }
        
        function showLeadForm() {
            const marker = currentMarker;
            
            const html = `
                <div class="lead-form-section">
                    <h2 style="text-align: center; color: #2c3e50; margin-bottom: 25px;">
                        <i class="fas fa-paper-plane"></i> Kontaktformular
                    </h2>
                    
                    <p style="text-align: center; color: #666; margin-bottom: 30px;">
                        Interesse an: <strong>${marker.custom_title || marker.marker_name}</strong>
                    </p>
                    
                    <form id="leadForm" onsubmit="submitLead(event)">
                        <input type="hidden" name="marker_id" value="${marker.marker_id}">
                        <input type="hidden" name="interested_in" value="${marker.marker_name}">
                        
                        <div class="form-group">
                            <label>E-Mail Adresse *</label>
                            <input type="email" name="email" required placeholder="ihre@email.de">
                        </div>
                        
                        <div class="form-group">
                            <label>Ihr Name</label>
                            <input type="text" name="name" placeholder="Max Mustermann">
                        </div>
                        
                        <div class="form-group">
                            <label>Firma</label>
                            <input type="text" name="company" placeholder="Ihre Firma GmbH">
                        </div>
                        
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="tel" name="phone" placeholder="+49 ...">
                        </div>
                        
                        <div class="form-group">
                            <label>Ihre Nachricht</label>
                            <textarea name="message" rows="5" placeholder="Ich interessiere mich für..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            Anfrage absenden
                        </button>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 25px; display: flex; align-items: center; gap: 12px; font-size: 13px; color: #666;">
                            <i class="fas fa-shield-alt" style="font-size: 20px;"></i>
                            <span>Ihre Daten werden vertraulich behandelt und nur für die Kontaktaufnahme verwendet.</span>
                        </div>
                    </form>
                </div>
            `;
            
            document.getElementById('modalBody').innerHTML = html;
        }
        
        function submitLead(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('messe_id', '<?= $messe['id'] ?>');
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird gesendet...';
            
            // CSRF-Token hinzufügen
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            formData.append('csrf_token', csrfToken);
            
            fetch('messe_submit_lead.php', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage();
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
        
        function showSuccessMessage() {
            const html = `
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2><?= htmlspecialchars($messe['thank_you_message'] ?? 'Vielen Dank für Ihr Interesse!') ?></h2>
                    <p style="color: #666; margin: 20px 0;">
                        Wir haben Ihre Anfrage erhalten und werden uns schnellstmöglich bei Ihnen melden.
                    </p>
                    <button class="btn-view" onclick="closeModal()" style="max-width: 300px; margin: 20px auto;">
                        <i class="fas fa-arrow-left"></i>
                        Zurück zur Übersicht
                    </button>
                </div>
            `;
            
            document.getElementById('modalBody').innerHTML = html;
        }
        
        function closeModal() {
            document.getElementById('deviceModal').classList.remove('active');
            document.body.style.overflow = '';
            currentMarker = null;
        }
        
        document.getElementById('deviceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('deviceModal').classList.contains('active')) {
                closeModal();
            }
        });
        
        // ===== 3D VIEWER & AR FUNCTIONS =====
        let viewer3D = null;
        
        // Initialize 3D Viewer when modal content is loaded
        function init3DViewer() {
            if (!currentMarker || !currentMarker.model_3d_url) return;
            
            const containerId = 'viewer3d-' + currentMarker.marker_id;
            const container = document.getElementById(containerId);
            
            if (!container) return;
            
            // Import 3D viewer class
            const script = document.createElement('script');
            script.src = 'js/3d-viewer.js';
            script.onload = function() {
                viewer3D = new Model3DViewer(containerId);
                viewer3D.init(currentMarker.model_3d_url);
            };
            document.head.appendChild(script);
        }
        
        function toggle3DAutoRotate() {
            if (viewer3D) {
                const isRotating = viewer3D.toggleAutoRotate();
                console.log('Auto-rotate:', isRotating);
            }
        }
        
        function reset3DCamera() {
            if (viewer3D) {
                viewer3D.resetCamera();
            }
        }
        
        function launch3DAR() {
            if (!currentMarker || !currentMarker.model_3d_url) {
                alert('Kein 3D-Modell verfügbar');
                return;
            }
            
            // Check for AR support
            if ('xr' in navigator) {
                navigator.xr.isSessionSupported('immersive-ar').then(supported => {
                    if (supported) {
                        // Launch WebXR AR
                        alert('AR wird gestartet...');
                        // Implement WebXR AR session here
                    } else {
                        fallbackAR();
                    }
                });
            } else {
                fallbackAR();
            }
        }
        
        function fallbackAR() {
            // Fallback for devices without WebXR
            const modelUrl = currentMarker.model_3d_url;
            
            // Try iOS Quick Look
            if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
                const anchor = document.createElement('a');
                anchor.rel = 'ar';
                anchor.href = modelUrl;
                anchor.click();
            } else {
                alert('AR wird auf diesem Gerät nicht unterstützt. Bitte verwenden Sie ein kompatibles Smartphone.');
            }
        }
        
        function change3DColor(color) {
            if (viewer3D) {
                viewer3D.changeMaterial(color);
                
                // Update active color indicator
                document.querySelectorAll('.color-option').forEach(el => {
                    el.classList.remove('active');
                });
                event.target.classList.add('active');
            }
        }
        
        function change3DScale(value) {
            if (viewer3D) {
                viewer3D.updateARConfig({
                    scale: parseFloat(value)
                });
                document.getElementById('scaleValue').textContent = value + 'x';
            }
        }
        
        function change3DRotation(value) {
            if (viewer3D) {
                viewer3D.updateARConfig({
                    rotation: { y: parseFloat(value) }
                });
                document.getElementById('rotationValue').textContent = value + '°';
            }
        }
        
        // Override renderDeviceInfo to initialize 3D viewer after rendering
        const originalRenderDeviceInfo = renderDeviceInfo;
        renderDeviceInfo = function() {
            originalRenderDeviceInfo();
            
            // Initialize 3D viewer after a short delay to ensure DOM is ready
            setTimeout(() => {
                init3DViewer();
            }, 500);
        };
        
        <?php if ($marker_id): ?>
        window.addEventListener('DOMContentLoaded', function() {
            showDeviceModal(<?= $marker_id ?>);
        });
        <?php endif; ?>
    </script>
</body>
</html>