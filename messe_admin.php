<?php
require_once 'config.php';
require_once 'functions.php';

define('MESSE_UPLOAD_DIR', 'uploads/messe/');
if (!is_dir(MESSE_UPLOAD_DIR)) {
    mkdir(MESSE_UPLOAD_DIR, 0755, true);
}

$stmt = $pdo->query("
    SELECT * FROM messe_config 
    ORDER BY 
        CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END,
        is_active DESC,
        created_at DESC
");
$messen = $stmt->fetchAll();

// Hilfsfunktion für Messe-Bild-Uploads
function uploadMesseImage($file, $prefix = 'image') {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'path' => null]; // Kein Fehler, nur kein Upload
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload-Fehler: ' . $file['error']];
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Ungültiger Dateityp. Erlaubt: JPG, PNG, GIF, WEBP'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Datei zu groß (max. 5MB)'];
    }
    
    // Bildvalidierung
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'message' => 'Keine gültige Bilddatei'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
    $filepath = MESSE_UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        chmod($filepath, 0644);
        return ['success' => true, 'path' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Upload fehlgeschlagen'];
}

requireLogin();
requirePermission('manage_system_settings');

$success = '';
$error = '';

// Messe erstellen/bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    validateCSRF();
    
    if ($_POST['action'] === 'create_messe') {
        try {
            // Logo Upload
            $logoPath = null;
            if (isset($_FILES['messe_logo']) && $_FILES['messe_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $logoResult = uploadMesseImage($_FILES['messe_logo'], 'logo');
                if ($logoResult['success']) {
                    $logoPath = $logoResult['path'];
                } else {
                    throw new Exception('Logo Upload: ' . $logoResult['message']);
                }
            }
            
            // Hero Image Upload
            $heroPath = null;
            if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $heroResult = uploadMesseImage($_FILES['hero_image'], 'hero');
                if ($heroResult['success']) {
                    $heroPath = $heroResult['path'];
                } else {
                    throw new Exception('Hero-Bild Upload: ' . $heroResult['message']);
                }
            }
            
            // Background Image Upload
            $bgPath = null;
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $bgResult = uploadMesseImage($_FILES['background_image'], 'background');
                if ($bgResult['success']) {
                    $bgPath = $bgResult['path'];
                } else {
                    throw new Exception('Hintergrund-Bild Upload: ' . $bgResult['message']);
                }
            }
            
            // Social Links als JSON speichern
            $socialLinks = null;
            if (isset($_POST['show_social_links'])) {
                $socialLinks = json_encode([
                    'facebook' => trim($_POST['facebook_url'] ?? ''),
                    'instagram' => trim($_POST['instagram_url'] ?? ''),
                    'linkedin' => trim($_POST['linkedin_url'] ?? ''),
                    'youtube' => trim($_POST['youtube_url'] ?? '')
                ]);
            }
            
            // Prüfe ob alle Spalten existieren
            $stmt = $pdo->query("SHOW COLUMNS FROM messe_config LIKE 'lead_email'");
            if ($stmt->rowCount() == 0) {
                throw new Exception('Datenbank-Schema ist nicht aktuell. Bitte führen Sie die SQL-Migration aus!');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO messe_config (
                    name, start_date, end_date, description, lead_email,
                    background_color, text_color, accent_color, 
                    primary_color, secondary_color, button_color,
                    background_style, background_image_path,
                    logo_path, hero_image_path,
                    font_family, welcome_text, footer_text,
                    social_links,
                    show_3d_models, show_lead_capture, thank_you_message
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['start_date'] ?: null,
                $_POST['end_date'] ?: null,
                $_POST['description'] ?: null,
                $_POST['lead_email'] ?? null,
                $_POST['background_color'] ?? '#ffffff',
                $_POST['text_color'] ?? '#000000',
                $_POST['accent_color'] ?? '#007bff',
                $_POST['primary_color'] ?? '#667eea',
                $_POST['secondary_color'] ?? '#764ba2',
                $_POST['button_color'] ?? '#28a745',
                $_POST['background_style'] ?? 'gradient',
                $bgPath,
                $logoPath,
                $heroPath,
                $_POST['font_family'] ?? "'Segoe UI', sans-serif",
                $_POST['welcome_text'] ?: null,
                $_POST['footer_text'] ?? '© 2025 Ihr Unternehmen',
                $socialLinks,
                isset($_POST['show_3d_models']) ? 1 : 0,
                isset($_POST['show_lead_capture']) ? 1 : 0,
                $_POST['thank_you_message'] ?? 'Vielen Dank für Ihr Interesse!'
            ]);
            
            $messeId = $pdo->lastInsertId();
            
            $success = "Messe erfolgreich erstellt!";
            if ($logoPath) $success .= " Logo hochgeladen.";
            if ($heroPath) $success .= " Hero-Bild hochgeladen.";
            if ($bgPath) $success .= " Hintergrund hochgeladen.";
            
            logActivity('messe_created', "Messe '{$_POST['name']}' erstellt (ID: $messeId)");
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
            error_log("Messe creation error: " . $e->getMessage());
        }
    }
    
    if ($_POST['action'] === 'activate_messe') {
        try {
            // Alle deaktivieren
            $pdo->query("UPDATE messe_config SET is_active = 0");
            // Diese aktivieren
            $stmt = $pdo->prepare("UPDATE messe_config SET is_active = 1 WHERE id = ?");
            $stmt->execute([$_POST['messe_id']]);
            $success = "Messe aktiviert!";
            logActivity('messe_activated', "Messe ID {$_POST['messe_id']} aktiviert");
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'end_messe') {
        try {
            // Messe beenden
            $stmt = $pdo->prepare("UPDATE messe_config SET is_active = 0 WHERE id = ?");
            $stmt->execute([$_POST['messe_id']]);
            
            // Alle Marker wieder auf "verfügbar" setzen
            $stmt = $pdo->prepare("
                UPDATE markers m
                JOIN messe_markers mm ON m.id = mm.marker_id
                SET m.rental_status = 'verfuegbar'
                WHERE mm.messe_id = ?
            ");
            $stmt->execute([$_POST['messe_id']]);
            
            $success = "Messe beendet! Alle Geräte wieder auf 'verfügbar' gesetzt.";
            logActivity('messe_ended', "Messe ID {$_POST['messe_id']} beendet");
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'delete_messe') {
        $messeId = intval($_POST['messe_id']);
        
        try {
            $pdo->beginTransaction();
            
            // Hole Messe-Daten
            $stmt = $pdo->prepare("SELECT * FROM messe_config WHERE id = ?");
            $stmt->execute([$messeId]);
            $messe = $stmt->fetch();
            
            if (!$messe) {
                throw new Exception('Messe nicht gefunden');
            }
            
            // SOFT DELETE: Markiere als gelöscht statt permanent zu löschen
            $stmt = $pdo->prepare("
                UPDATE messe_config 
                SET deleted_at = NOW(),
                    deleted_by = ?,
                    is_active = 0
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $messeId]);
            
            // Optional: Lösche zugehörige Daten (oder behalte sie für Statistiken)
            // Kommentar entfernen, wenn Daten auch gelöscht werden sollen:
            /*
            $pdo->prepare("DELETE FROM messe_markers WHERE messe_id = ?")->execute([$messeId]);
            $pdo->prepare("DELETE FROM messe_scan_stats WHERE messe_id = ?")->execute([$messeId]);
            $pdo->prepare("DELETE FROM messe_leads WHERE messe_id = ?")->execute([$messeId]);
            */
            
            // Activity Log
            logActivity('messe_deleted', "Messe '{$messe['name']}' gelöscht");
            
            $pdo->commit();
            $success = 'Messe erfolgreich gelöscht!';
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Fehler beim Löschen der Messe: ' . $e->getMessage();
            error_log("Messe Delete Error: " . $e->getMessage());
        }
    }
    
    if ($_POST['action'] === 'permanent_delete_messe') {
        $messeId = intval($_POST['messe_id']);
        
        // Nur für Admins erlaubt
        if ($_SESSION['role'] !== 'admin') {
            $error = 'Keine Berechtigung für permanentes Löschen';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Hole Messe-Daten
                $stmt = $pdo->prepare("SELECT * FROM messe_config WHERE id = ?");
                $stmt->execute([$messeId]);
                $messe = $stmt->fetch();
                
                if (!$messe) {
                    throw new Exception('Messe nicht gefunden');
                }
                
                // Lösche Bilder
                $imagesToDelete = [
                    $messe['logo_path'],
                    $messe['hero_image_path'],
                    $messe['background_image_path']
                ];
                
                foreach ($imagesToDelete as $imagePath) {
                    if (!empty($imagePath) && file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                // Lösche zugehörige Daten
                $pdo->prepare("DELETE FROM messe_markers WHERE messe_id = ?")->execute([$messeId]);
                $pdo->prepare("DELETE FROM messe_scan_stats WHERE messe_id = ?")->execute([$messeId]);
                $pdo->prepare("DELETE FROM messe_leads WHERE messe_id = ?")->execute([$messeId]);
                // messe_marker_fields werden automatisch durch CASCADE DELETE gelöscht
                
                // Lösche Messe-Konfiguration
                $pdo->prepare("DELETE FROM messe_config WHERE id = ?")->execute([$messeId]);
                
                // Activity Log
                logActivity('messe_permanent_deleted', "Messe '{$messe['name']}' permanent gelöscht");
                
                $pdo->commit();
                $success = 'Messe permanent gelöscht!';
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Fehler beim permanenten Löschen: ' . $e->getMessage();
                error_log("Messe Permanent Delete Error: " . $e->getMessage());
            }
        }
    }

    if ($_POST['action'] === 'restore_messe') {
        $messeId = intval($_POST['messe_id']);
        
        try {
            $stmt = $pdo->prepare("
                UPDATE messe_config 
                SET deleted_at = NULL,
                    deleted_by = NULL
                WHERE id = ?
            ");
            $stmt->execute([$messeId]);
            
            // Activity Log
            $stmt = $pdo->prepare("SELECT name FROM messe_config WHERE id = ?");
            $stmt->execute([$messeId]);
            $messeName = $stmt->fetchColumn();
            
            logActivity('messe_restored', "Messe '{$messeName}' wiederhergestellt");
            
            $success = 'Messe erfolgreich wiederhergestellt!';
            
        } catch (Exception $e) {
            $error = 'Fehler beim Wiederherstellen: ' . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'add_marker_to_messe') {
        try {
            // Marker zur Messe hinzufügen
            $stmt = $pdo->prepare("INSERT INTO messe_markers (messe_id, marker_id, display_order, is_featured, custom_title, custom_description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['messe_id'],
                $_POST['marker_id'],
                $_POST['display_order'] ?: 0,
                isset($_POST['is_featured']) ? 1 : 0,
                $_POST['custom_title'] ?: null,
                $_POST['custom_description'] ?: null
            ]);
            
            // Marker-Status auf "messe" setzen
            $stmt = $pdo->prepare("UPDATE markers SET rental_status = 'messe' WHERE id = ?");
            $stmt->execute([$_POST['marker_id']]);
            
            $success = "Gerät zur Messe hinzugefügt und Status auf 'messe' gesetzt!";
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'remove_marker_from_messe') {
        try {
            // Marker von Messe entfernen
            $stmt = $pdo->prepare("DELETE FROM messe_markers WHERE id = ? AND messe_id = ?");
            $stmt->execute([$_POST['messe_marker_id'], $_POST['messe_id']]);
            
            // Marker-Status zurück auf "verfügbar"
            $stmt = $pdo->prepare("UPDATE markers SET rental_status = 'verfuegbar' WHERE id = ?");
            $stmt->execute([$_POST['marker_id']]);
            
            $success = "Gerät von Messe entfernt und Status auf 'verfügbar' gesetzt!";
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'edit_messe_marker') {
        try {
            $stmt = $pdo->prepare("UPDATE messe_markers SET custom_title = ?, custom_description = ?, display_order = ?, is_featured = ?, additional_info = ? WHERE id = ?");
            $stmt->execute([
                $_POST['custom_title'] ?: null,
                $_POST['custom_description'] ?: null,
                $_POST['display_order'] ?: 0,
                isset($_POST['is_featured']) ? 1 : 0,
                $_POST['additional_info'] ?: null,
                $_POST['messe_marker_id']
            ]);
            $success = "Messe-Gerät aktualisiert!";
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'add_custom_field') {
        try {
            $stmt = $pdo->prepare("INSERT INTO messe_marker_fields (messe_marker_id, field_name, field_value, field_icon, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['messe_marker_id'],
                $_POST['field_name'],
                $_POST['field_value'],
                $_POST['field_icon'] ?: null,
                $_POST['display_order'] ?: 0
            ]);
            $success = "Custom Field hinzugefügt!";
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
}

// Alle Messen laden
$messen = $pdo->query("SELECT * FROM messe_config ORDER BY created_at DESC")->fetchAll();

// Alle Marker laden
$markers = $pdo->query("SELECT id, name, qr_code, category FROM markers WHERE deleted_at IS NULL ORDER BY name")->fetchAll();

// Aktive Messe
$activeMesse = $pdo->query("SELECT * FROM messe_config WHERE is_active = 1")->fetch();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Messe-Modus Administration</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Einfacher Rich-Text-Editor ohne externe Bibliotheken -->
    <style>
        .simple-editor-toolbar {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            padding: 8px;
            border-radius: 4px 4px 0 0;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .simple-editor-toolbar button {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 3px;
            font-size: 14px;
        }
        .simple-editor-toolbar button:hover {
            background: #e9ecef;
        }
        .simple-editor-toolbar button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .simple-editor-content {
            border: 1px solid #ddd;
            min-height: 300px;
            padding: 12px;
            background: white;
            border-radius: 0 0 4px 4px;
            overflow-y: auto;
        }
        .simple-editor-content:focus {
            outline: none;
            border-color: #80bdff;
        }
    </style>
    <style>
        .messe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .messe-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .messe-card.active {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
        }
        .messe-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: #28a745; color: white; }
        .badge-inactive { background: #6c757d; color: white; }
        .qr-preview {
            width: 100px;
            height: 100px;
            margin: 10px auto;
            display: block;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 5px;
        }
        .color-picker-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 15px 0;
        }
        .color-picker-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .color-picker-item input[type="color"] {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .marker-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin: 10px 0;
        }
        .marker-item {
            padding: 10px;
            background: #f8f9fa;
            margin: 5px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-preview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
        }
        .stat-box .number {
            font-size: 32px;
            font-weight: bold;
            display: block;
        }
        .stat-box .label {
            font-size: 12px;
            opacity: 0.9;
        }

        .btn-sm {
            margin: 2px;
        }

        .table tr[style*="opacity"] {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,0,0,0.05) 10px,
                rgba(255,0,0,0.05) 20px
            );
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container" style="max-width: 1400px; margin: 20px auto; padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-bullhorn"></i> Messe-Modus Administration</h1>
            <button onclick="document.getElementById('createModal').style.display='block'" class="btn btn-primary">
                <i class="fas fa-plus"></i> Neue Messe erstellen
            </button>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($activeMesse): ?>
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Aktive Messe: <?= htmlspecialchars($activeMesse['name']) ?></h3>
            <p><strong>Öffentlicher Link:</strong> <a href="messe_view.php" target="_blank"><?= $_SERVER['HTTP_HOST'] ?>/messe_view.php</a></p>
            <p><strong>QR-Code für Besucher:</strong></p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($_SERVER['HTTP_HOST'] . '/messe_view.php') ?>" class="qr-preview" alt="Messe QR-Code">
            <a href="messe_stats.php?id=<?= $activeMesse['id'] ?>" class="btn btn-info" style="margin-top: 10px;">
                <i class="fas fa-chart-bar"></i> Statistiken anzeigen
            </a>
        </div>
        <?php endif; ?>
        
        <h2 style="margin-top: 40px;">Ihre Messen</h2>
        <div class="messe-grid">
            <?php foreach ($messen as $messe): ?>
                <?php 
                $messeMarkers = $pdo->prepare("SELECT mm.*, m.name as marker_name FROM messe_markers mm JOIN markers m ON mm.marker_id = m.id WHERE mm.messe_id = ?");
                $messeMarkers->execute([$messe['id']]);
                $markers_count = $messeMarkers->rowCount();
                
                $stats = $pdo->prepare("SELECT COUNT(*) as scans, COUNT(DISTINCT ip_address) as visitors FROM messe_scan_stats WHERE messe_id = ?");
                $stats->execute([$messe['id']]);
                $stat = $stats->fetch();
                
                $leads = $pdo->prepare("SELECT COUNT(*) as count FROM messe_leads WHERE messe_id = ?");
                $leads->execute([$messe['id']]);
                $lead_count = $leads->fetchColumn();
                ?>
                <div class="messe-card <?= $messe['is_active'] ? 'active' : '' ?>">
                    <h3>
                        <?= htmlspecialchars($messe['name']) ?>
                        <span class="badge <?= $messe['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $messe['is_active'] ? 'AKTIV' : 'Inaktiv' ?>
                        </span>
                    </h3>
                    
                    <?php if ($messe['start_date']): ?>
                    <p><i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($messe['start_date'])) ?> 
                    <?php if ($messe['end_date']): ?>- <?= date('d.m.Y', strtotime($messe['end_date'])) ?><?php endif; ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($messe['description']): ?>
                    <p style="color: #666; font-size: 14px;"><?= htmlspecialchars($messe['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="stats-preview">
                        <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <span class="number"><?= $markers_count ?></span>
                            <span class="label">Geräte</span>
                        </div>
                        <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <span class="number"><?= $stat['scans'] ?? 0 ?></span>
                            <span class="label">Scans</span>
                        </div>
                        <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <span class="number"><?= $lead_count ?></span>
                            <span class="label">Leads</span>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                        <?php if (!$messe['is_active']): ?>
                        <form method="POST" style="flex: 1;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="activate_messe">
                            <input type="hidden" name="messe_id" value="<?= $messe['id'] ?>">
                            <button type="submit" class="btn btn-success" style="width: 100%;">
                                <i class="fas fa-check"></i> Aktivieren
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" onsubmit="return confirm('Messe wirklich beenden? Alle Geräte werden wieder auf verfügbar gesetzt.')" style="flex: 1;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="end_messe">
                            <input type="hidden" name="messe_id" value="<?= $messe['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                <i class="fas fa-stop"></i> Beenden
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <button onclick="showAddMarkerModal(<?= $messe['id'] ?>)" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-plus"></i> Gerät hinzufügen
                        </button>
                        
                        <button onclick="showMesseDevices(<?= $messe['id'] ?>)" class="btn btn-info" style="flex: 1;">
                            <i class="fas fa-list"></i> Geräte verwalten
                        </button>
                        
                        <a href="messe_stats.php?id=<?= $messe['id'] ?>" class="btn btn-info" style="flex: 1;">
                            <i class="fas fa-chart-line"></i> Statistiken
                        </a>
                        
                        <?php if ($messe['deleted_at']): ?>
                        <!-- Gelöschte Messe - nur permanentes Löschen erlaubt (Admin) -->
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <form method="POST" onsubmit="return confirm('ACHTUNG: Diese Messe wird PERMANENT gelöscht! Alle zugehörigen Daten gehen verloren. Sind Sie sicher?')" style="flex: 1;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="permanent_delete_messe">
                            <input type="hidden" name="messe_id" value="<?= $messe['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                <i class="fas fa-trash-alt"></i> Permanent löschen
                            </button>
                        </form>
                        <?php endif; ?>
                        <?php else: ?>
                        <!-- Aktive Messe - Soft Delete -->
                        <form method="POST" onsubmit="return confirm('Messe wirklich löschen? Sie wird in den Papierkorb verschoben und kann wiederhergestellt werden.')" style="flex: 1;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_messe">
                            <input type="hidden" name="messe_id" value="<?= $messe['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                <i class="fas fa-trash"></i> Löschen
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Modal: Neue Messe erstellen -->
    <div id="createModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <span class="close" onclick="document.getElementById('createModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-plus-circle"></i> Neue Messe erstellen</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_messe">
                
                <div class="form-group">
                    <label>Messe-Name *</label>
                    <input type="text" name="name" required class="form-control" placeholder="z.B. Hannover Messe 2025">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Start-Datum</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>End-Datum</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Beschreibung</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Kurze Beschreibung der Messe..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Lead-Empfänger Email *</label>
                    <input type="email" name="lead_email" required class="form-control" 
                        placeholder="info@firma.de" 
                        value="">
                    <small style="color: #666; margin-top: 5px; display: block;">
                        <i class="fas fa-info-circle"></i> Leads von Besuchern werden an diese Email-Adresse gesendet
                    </small>
                </div>

                <h3 style="margin-top: 25px;">Infoseiten-Baukasten</h3>
                <p style="color: #666; margin-bottom: 15px;">
                    Gestalte die öffentliche Ansicht für Besucher
                </p>

                <div class="form-group">
                    <label>Logo hochladen (optional)</label>
                    <input type="file" name="messe_logo" accept="image/*" class="form-control">
                    <small>Empfohlen: PNG mit transparentem Hintergrund, max. 2MB</small>
                </div>

                <div class="form-group">
                    <label>Hero-Bild hochladen (optional)</label>
                    <input type="file" name="hero_image" accept="image/*" class="form-control">
                    <small>Großes Titelbild für die Startseite, empfohlen: 1920x600px</small>
                </div>

                <div class="form-group">
                    <label>Hintergrund-Stil</label>
                    <select name="background_style" class="form-control">
                        <option value="solid">Einfarbig</option>
                        <option value="gradient" selected>Verlauf</option>
                        <option value="image">Bild</option>
                    </select>
                </div>

                <div class="form-group" id="backgroundImageGroup" style="display:none;">
                    <label>Hintergrund-Bild hochladen</label>
                    <input type="file" name="background_image" accept="image/*" class="form-control">
                    <small>Wird als Hintergrund verwendet, empfohlen: 1920x1080px</small>
                </div>

                <div class="color-picker-group">
                    <div class="color-picker-item">
                        <label>Primärfarbe</label>
                        <input type="color" name="primary_color" value="#667eea">
                    </div>
                    <div class="color-picker-item">
                        <label>Sekundärfarbe</label>
                        <input type="color" name="secondary_color" value="#764ba2">
                    </div>
                    <div class="color-picker-item">
                        <label>Button-Farbe</label>
                        <input type="color" name="button_color" value="#28a745">
                    </div>
                </div>

                <div class="form-group">
                    <label>Schriftart</label>
                    <select name="font_family" class="form-control">
                        <option value="'Segoe UI', sans-serif" selected>Segoe UI (Standard)</option>
                        <option value="'Arial', sans-serif">Arial</option>
                        <option value="'Helvetica', sans-serif">Helvetica</option>
                        <option value="'Georgia', serif">Georgia</option>
                        <option value="'Times New Roman', serif">Times New Roman</option>
                        <option value="'Courier New', monospace">Courier New</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Willkommenstext</label>
                    <textarea name="welcome_text" class="form-control" rows="3" 
                            placeholder="Willkommen auf unserer Messe! Entdecken Sie unsere innovativen Produkte..."></textarea>
                    <small>Wird auf der Startseite angezeigt</small>
                </div>

                <div class="form-group">
                    <label>Footer-Text</label>
                    <input type="text" name="footer_text" class="form-control" 
                        placeholder="© 2025 Ihr Unternehmen - Alle Rechte vorbehalten"
                        value="© 2025 Ihr Unternehmen">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="show_social_links"> Social Media Links anzeigen
                    </label>
                </div>

                <div id="socialLinksGroup" style="display:none; margin-top: 15px;">
                    <div class="form-group">
                        <label>Facebook URL</label>
                        <input type="url" name="facebook_url" class="form-control" placeholder="https://facebook.com/...">
                    </div>
                    <div class="form-group">
                        <label>Instagram URL</label>
                        <input type="url" name="instagram_url" class="form-control" placeholder="https://instagram.com/...">
                    </div>
                    <div class="form-group">
                        <label>LinkedIn URL</label>
                        <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/...">
                    </div>
                    <div class="form-group">
                        <label>YouTube URL</label>
                        <input type="url" name="youtube_url" class="form-control" placeholder="https://youtube.com/...">
                    </div>
                </div>

                <h3 style="margin-top: 25px;">Design-Einstellungen</h3>
                <div class="color-picker-group">
                    <div class="color-picker-item">
                        <label>Hintergrundfarbe</label>
                        <input type="color" name="background_color" value="#ffffff">
                    </div>
                    <div class="color-picker-item">
                        <label>Textfarbe</label>
                        <input type="color" name="text_color" value="#000000">
                    </div>
                    <div class="color-picker-item">
                        <label>Akzentfarbe</label>
                        <input type="color" name="accent_color" value="#007bff">
                    </div>
                </div>
                
                <h3 style="margin-top: 25px;">Funktionen</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="show_3d_models" checked> 3D-Modelle anzeigen
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="show_lead_capture" checked> Lead-Erfassung aktivieren
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Danke-Nachricht nach Lead-Erfassung</label>
                    <textarea name="thank_you_message" class="form-control" rows="2">Vielen Dank für Ihr Interesse! Wir melden uns bei Ihnen.</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Messe erstellen
                </button>
            </form>
        </div>
    </div>
    
    <!-- Modal: Gerät zur Messe hinzufügen -->
    <div id="addMarkerModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="document.getElementById('addMarkerModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-plus"></i> Gerät zur Messe hinzufügen</h2>
            
            <form method="POST" id="addMarkerForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_marker_to_messe">
                <input type="hidden" name="messe_id" id="modal_messe_id">
                
                <div class="form-group">
                    <label>Gerät auswählen *</label>
                    <select name="marker_id" required class="form-control">
                        <option value="">-- Bitte wählen --</option>
                        <?php foreach ($markers as $marker): ?>
                            <option value="<?= $marker['id'] ?>"><?= htmlspecialchars($marker['name']) ?> (<?= $marker['qr_code'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Custom Titel (optional)</label>
                    <input type="text" name="custom_title" class="form-control" placeholder="Überschreibt den Geräte-Namen">
                </div>
                
                <div class="form-group">
                    <label>Custom Beschreibung (optional)</label>
                    <textarea name="custom_description" class="form-control" rows="3" placeholder="Spezielle Beschreibung für die Messe..."></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Anzeigereihenfolge</label>
                        <input type="number" name="display_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured"> Featured
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Hinzufügen
                </button>
            </form>
        </div>
    </div>
    
    <!-- Modal: Geräte verwalten -->
    <div id="manageDevicesModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <span class="close" onclick="document.getElementById('manageDevicesModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-cogs"></i> Geräte verwalten</h2>
            <div id="devicesList"></div>
        </div>
    </div>
    
    <!-- Modal: Gerät bearbeiten -->
    <div id="editMarkerModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="closeEditMarkerModal()">&times;</span>
            <h2><i class="fas fa-edit"></i> Messe-Gerät bearbeiten</h2>
            
            <form method="POST" id="editMarkerForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="edit_messe_marker">
                <input type="hidden" name="messe_marker_id" id="edit_messe_marker_id">
                
                <div class="form-group">
                    <label>Custom Titel (optional)</label>
                    <input type="text" name="custom_title" id="edit_custom_title" class="form-control" placeholder="Überschreibt den Geräte-Namen">
                </div>
                
                <div class="form-group">
                    <label>Custom Beschreibung (optional)</label>
                    <textarea name="custom_description" id="edit_custom_description" class="form-control" rows="3" placeholder="Spezielle Beschreibung für die Messe..."></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Anzeigereihenfolge</label>
                        <input type="number" name="display_order" id="edit_display_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured" id="edit_is_featured"> Featured
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Speichern
                </button>
<!-- ============================================ -->
<!-- ERWEITERUNG für messe_admin.php -->
<!-- Zum Verwalten von Gerätebildern und Badges -->
<!-- ============================================ -->

<!-- Diesen Code in messe_admin.php in das Edit-Modal einfügen -->

<style>
/* Badge-Management Styles */
.badge-management {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.badge-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
}

.badge-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: default;
}

.badge-delete-btn {
    background: rgba(0,0,0,0.1);
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.badge-delete-btn:hover {
    background: rgba(255,0,0,0.7);
    color: white;
}

.image-upload-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.current-image-preview {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 15px 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.upload-btn, .delete-image-btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-right: 10px;
}

.upload-btn {
    background: #28a745;
    color: white;
}

.upload-btn:hover {
    background: #218838;
}

.delete-image-btn {
    background: #dc3545;
    color: white;
}

.delete-image-btn:hover {
    background: #c82333;
}
</style>

<!-- ===== BILD-UPLOAD SECTION ===== -->
<div class="form-section image-upload-section">
    <h3><i class="fas fa-image"></i> Gerätebild</h3>
    <p style="color: #666; margin-bottom: 15px;">
        Das Gerätebild wird prominent im Modal angezeigt (wie auf dem HO-MA Beispiel)
    </p>
    
    <div id="current_image_display"></div>
    
    <div style="margin-top: 15px;">
        <input type="file" id="device_image_input" accept="image/*" style="display: none;">
        <button type="button" class="upload-btn" onclick="document.getElementById('device_image_input').click()">
            <i class="fas fa-upload"></i> Bild hochladen
        </button>
        <button type="button" class="delete-image-btn" id="delete_image_btn" style="display: none;" onclick="deleteDeviceImage()">
            <i class="fas fa-trash"></i> Bild löschen
        </button>
    </div>
    
    <small style="color: #888; display: block; margin-top: 10px;">
        <i class="fas fa-info-circle"></i> Empfohlene Größe: 800x600px oder größer. Max. 10MB.
    </small>
</div>

<!-- ===== BADGE-MANAGEMENT SECTION ===== -->
<div class="form-section badge-management">
    <h3><i class="fas fa-tags"></i> Icon-Badges</h3>
    <p style="color: #666; margin-bottom: 15px;">
        Badges werden als Icon-Kacheln angezeigt (wie: 01, Wassergekühlt, Drehphasig, etc.)
    </p>
    
    <div id="badges_list" class="badge-list">
        <!-- Badges werden hier dynamisch geladen -->
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label>Badge-Text *</label>
            <input type="text" id="new_badge_text" placeholder="z.B. Wassergekühlt, 50 Hz, ...">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label>Icon (optional)</label>
            <input type="text" id="new_badge_icon" placeholder="fas fa-water">
            <small><a href="https://fontawesome.com/icons" target="_blank">Icon-Liste</a></small>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label>Farbe</label>
            <input type="color" id="new_badge_color" value="#FFD700">
        </div>
        
        <button type="button" class="upload-btn" onclick="addBadge()" style="margin-bottom: 5px;">
            <i class="fas fa-plus"></i> Hinzufügen
        </button>
    </div>
    
    <small style="color: #888; display: block; margin-top: 10px;">
        <i class="fas fa-lightbulb"></i> <strong>Tipp:</strong> Verwenden Sie Font Awesome Icons (z.B. "fas fa-water" für Wasser, "fas fa-leaf" für Umwelt)
    </small>
</div>

<!-- ===== ZUSÄTZLICHE INFORMATIONEN SECTION ===== -->
<div class="form-section" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <h3><i class="fas fa-info-circle"></i> Zusätzliche Informationen</h3>
    <p style="color: #666; margin-bottom: 15px;">
        Dieser Text wird unterhalb der technischen Daten angezeigt. Hier können Sie beliebige Informationen wie Aufstellbedingungen, PRP, ESP etc. eingeben.
    </p>
    
    <div class="form-group">
        <label>Zusatzinformationen (optional)</label>
        <textarea name="additional_info" id="edit_additional_info" class="form-control" rows="8" placeholder="z.B.

Aufstellbedingungen:
1.000 mbar, 25°C, 30% relative Luftfeuchtigkeit. Leistung gemäß der Norm ISO 3046.

PRP:
Ständig verfügbare Leistung bei variabler Last für eine unbegrenzte Stundenzahl pro Jahr nach ISO 8528-1.

ESP:
Standby-Leistung verfügbar für eine Notstromwendung (eine Stunde) bei variabler Last nach ISO 8528-1."></textarea>
        <small class="text-muted">
            <i class="fas fa-lightbulb"></i> <strong>Tipp:</strong> Sie können auch HTML-Tags verwenden, z.B. <code>&lt;strong&gt;</code> für Fettdruck oder <code>&lt;br&gt;</code> für Zeilenumbrüche.
        </small>
    </div>
</div>

<script>
// ===== BILD-UPLOAD FUNKTIONEN =====
let currentMesseMarkerId = null;

function initImageUpload(messeMarkerId, currentImage) {
    currentMesseMarkerId = messeMarkerId;
    
    // Aktuelles Bild anzeigen
    const imageDisplay = document.getElementById('current_image_display');
    const deleteBtn = document.getElementById('delete_image_btn');
    
    if (currentImage) {
        imageDisplay.innerHTML = `
            <div>
                <p><strong>Aktuelles Bild:</strong></p>
                <img src="${currentImage}" class="current-image-preview">
            </div>
        `;
        deleteBtn.style.display = 'inline-block';
    } else {
        imageDisplay.innerHTML = '<p style="color: #888;"><i class="fas fa-info-circle"></i> Kein Bild vorhanden</p>';
        deleteBtn.style.display = 'none';
    }
    
    // File Input Event
    document.getElementById('device_image_input').onchange = function(e) {
        if (e.target.files.length > 0) {
            uploadDeviceImage(e.target.files[0]);
        }
    };
}

function uploadDeviceImage(file) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        alert('Sicherheits-Token fehlt. Bitte laden Sie die Seite neu.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'upload_device_image');
    formData.append('messe_marker_id', currentMesseMarkerId);
    formData.append('device_image', file);
    formData.append('csrf_token', csrfToken);
    
    fetch('messe_device_upload.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bild erfolgreich hochgeladen!');
            location.reload(); // Seite neu laden um Bild anzuzeigen
        } else {
            alert('Fehler: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload-Fehler');
    });
}

function deleteDeviceImage() {
    if (!confirm('Bild wirklich löschen?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        alert('Sicherheits-Token fehlt. Bitte laden Sie die Seite neu.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_device_image');
    formData.append('messe_marker_id', currentMesseMarkerId);
    formData.append('csrf_token', csrfToken);
    
    fetch('messe_device_upload.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bild erfolgreich gelöscht!');
            location.reload();
        } else {
            alert('Fehler: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Fehler beim Löschen');
    });
}

// ===== BADGE-MANAGEMENT FUNKTIONEN =====
let currentBadges = [];
let currentMarkerId = null;

function loadBadges(markerId) {
    currentMarkerId = markerId;
    
    console.log('Loading badges for marker_id:', markerId, 'messe_id:', <?= $activeMesse['id'] ?? 0 ?>);
    
    fetch(`messe_get_marker_details.php?marker_id=${markerId}&messe_id=<?= $activeMesse['id'] ?? 0 ?>`)
    .then(response => response.json())
    .then(data => {
        console.log('Badge data received:', data);
        if (data.success) {
            currentBadges = data.marker.badges || [];
            // Speichere die messe_marker_id für spätere Verwendung (z.B. addBadge)
            currentMesseMarkerId = data.marker.messe_marker_id || document.getElementById('edit_messe_marker_id').value;
            console.log('Loaded badges:', currentBadges);
            renderBadges();
        } else {
            console.error('Failed to load badges:', data.message);
        }
    })
    .catch(error => {
        console.error('Error loading badges:', error);
    });
}

function renderBadges() {
    const badgesList = document.getElementById('badges_list');
    
    console.log('Rendering badges. Current badges:', currentBadges);
    
    if (!badgesList) {
        console.error('badges_list element not found!');
        return;
    }
    
    if (!currentBadges || currentBadges.length === 0) {
        badgesList.innerHTML = '<p style="color: #888;"><i class="fas fa-info-circle"></i> Noch keine Badges hinzugefügt</p>';
        console.log('No badges to display');
        return;
    }
    
    badgesList.innerHTML = currentBadges.map(badge => `
        <div class="badge-item" style="background-color: ${badge.badge_color};">
            ${badge.badge_icon ? `<i class="${badge.badge_icon}"></i>` : ''}
            <span>${badge.badge_text}</span>
            <button class="badge-delete-btn" onclick="deleteBadge(${badge.id})" title="Badge löschen">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
    
    console.log('Badges rendered:', currentBadges.length, 'badges');
}

function addBadge() {
    const text = document.getElementById('new_badge_text').value.trim();
    const icon = document.getElementById('new_badge_icon').value.trim();
    const color = document.getElementById('new_badge_color').value;
    
    if (!text) {
        alert('Bitte Badge-Text eingeben');
        return;
    }
    
    // CSRF-Token aus Meta-Tag holen
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        alert('Sicherheits-Token fehlt. Bitte laden Sie die Seite neu.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_badge');
    formData.append('messe_marker_id', currentMesseMarkerId);
    formData.append('badge_text', text);
    formData.append('badge_icon', icon);
    formData.append('badge_color', color);
    formData.append('display_order', currentBadges.length);
    formData.append('csrf_token', csrfToken); // WICHTIG: Als POST-Parameter
    
    fetch('messe_device_upload.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken // Auch als Header (doppelte Sicherheit)
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Eingabefelder leeren
            document.getElementById('new_badge_text').value = '';
            document.getElementById('new_badge_icon').value = '';
            document.getElementById('new_badge_color').value = '#FFD700';
            
            // Badges neu laden mit der richtigen marker_id
            loadBadges(currentMarkerId);
        } else {
            alert('Fehler: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Fehler beim Hinzufügen des Badges');
    });
}

function deleteBadge(badgeId) {
    if (!confirm('Badge wirklich löschen?')) return;
    
    // CSRF-Token aus Meta-Tag holen
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        alert('Sicherheits-Token fehlt. Bitte laden Sie die Seite neu.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_badge');
    formData.append('badge_id', badgeId);
    formData.append('csrf_token', csrfToken); // WICHTIG: Als POST-Parameter
    
    fetch('messe_device_upload.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken // Auch als Header (doppelte Sicherheit)
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadBadges(currentMarkerId);
        } else {
            alert('Fehler: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Fehler beim Löschen des Badges');
    });
}
</script>

<!-- 
INTEGRATION IN MESSE_ADMIN.PHP:

1. Suchen Sie die editMesseMarker() Funktion
2. Fügen Sie am Ende der Funktion folgende Zeilen hinzu:

    initImageUpload(messeMarkerId, currentImage);
    loadBadges(messeMarkerId);

Beispiel:
function editMesseMarker(id) {
    // ... bestehender Code ...
    
    // NEU: Bild und Badges laden
    const messeMarker = messeMarkers.find(mm => mm.id == id);
    initImageUpload(id, messeMarker.device_image);
    loadBadges(id);
    
    
    // NEU: Lade Bild und Badges
    fetch('messe_get_marker_details.php?marker_id=' + id + '&messe_id=<?= $activeMesse["id"] ?? 0 ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                initImageUpload(id, data.marker.device_image);
                loadBadges(id);
            }
        })
        .catch(error => console.error('Error loading marker details:', error));
    
    document.getElementById('editMesseMarkerModal').style.display = 'block';
}
-->

            </form>
        </div>
    </div>


    <script>
        function showAddMarkerModal(messeId) {
            document.getElementById('modal_messe_id').value = messeId;
            document.getElementById('addMarkerModal').style.display = 'block';
        }
        
        function showMesseDevices(messeId) {
            // Lade Geräte per AJAX
            fetch('messe_get_devices.php?messe_id=' + messeId)
                .then(response => response.json())
                .then(data => {
                    let html = '<div style="margin: 20px 0;">';
                    
                    if (data.devices.length === 0) {
                        html += '<p style="text-align: center; color: #999; padding: 40px;">Noch keine Geräte hinzugefügt.</p>';
                    } else {
                        data.devices.forEach(device => {
                            html += `
                                <div class="marker-item" style="padding: 15px; background: #f8f9fa; margin: 10px 0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1;">
                                        <strong>${device.custom_title || device.marker_name}</strong>
                                        ${device.is_featured ? '<span class="badge badge-active" style="margin-left: 10px;">FEATURED</span>' : ''}
                                        <br>
                                        <small style="color: #666;">QR: ${device.qr_code}</small>
                                        ${device.custom_description ? '<br><small style="color: #888;">' + device.custom_description.substring(0, 80) + '...</small>' : ''}
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="messe_custom_fields.php?mm_id=${device.id}" class="btn btn-sm btn-info" title="Custom Fields & 3D">
                                            <i class="fas fa-cube"></i>
                                        </a>
                                        <button onclick="editDevice(${device.id}, ${device.marker_id}, '${device.custom_title || ''}', '${(device.custom_description || '').replace(/'/g, "\\'")}', ${device.display_order}, ${device.is_featured}, '${device.device_image || ''}')" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Wirklich entfernen?')">
                                            ${document.querySelector('input[name=csrf_token]').outerHTML}
                                            <input type="hidden" name="action" value="remove_marker_from_messe">
                                            <input type="hidden" name="messe_id" value="${data.messe_id}">
                                            <input type="hidden" name="messe_marker_id" value="${device.id}">
                                            <input type="hidden" name="marker_id" value="${device.marker_id}">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <a href="messe_print_qr.php?m=${device.marker_id}" target="_blank" class="btn btn-sm btn-success" title="QR-Code drucken">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    html += '</div>';
                    document.getElementById('devicesList').innerHTML = html;
                    document.getElementById('manageDevicesModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Fehler beim Laden der Geräte');
                });
        }
        
        function editDevice(messeMarkerId, markerId, title, description, order, featured, deviceImage = null) {
            document.getElementById('edit_messe_marker_id').value = messeMarkerId;
            document.getElementById('edit_custom_title').value = title;
            document.getElementById('edit_custom_description').value = description;
            document.getElementById('edit_display_order').value = order;
            document.getElementById('edit_is_featured').checked = featured == 1;
            
            // WICHTIG: Bild-Upload und Badges initialisieren
            initImageUpload(messeMarkerId, deviceImage);
            loadBadges(markerId); // Verwende markerId für die Badge-Abfrage
            
            // WICHTIG: Additional Info laden
            fetch('messe_get_marker_details.php?marker_id=' + markerId + '&messe_id=<?= $activeMesse["id"] ?? 0 ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.marker.additional_info) {
                        document.getElementById('edit_additional_info').value = data.marker.additional_info;
                    } else {
                        document.getElementById('edit_additional_info').value = '';
                    }
                })
                .catch(error => console.error('Error loading additional info:', error));
            
            document.getElementById('manageDevicesModal').style.display = 'none';
            document.getElementById('editMarkerModal').style.display = 'block';
            
            // Editor initialisieren (ohne Verzögerung, da keine externe Bibliothek)
            setTimeout(function() {
                initSimpleEditor();
            }, 100);
        }
        
        // Modal schließen bei Klick außerhalb
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                // Wenn es das editMarkerModal ist, verwende die spezielle Funktion
                if (event.target.id === 'editMarkerModal') {
                    closeEditMarkerModal();
                } else {
                    event.target.style.display = 'none';
                }
            }
        }

        // Background Style Toggle
        document.querySelector('[name="background_style"]').addEventListener('change', function() {
            const imageGroup = document.getElementById('backgroundImageGroup');
            if (this.value === 'image') {
                imageGroup.style.display = 'block';
            } else {
                imageGroup.style.display = 'none';
            }
        });

        // Social Links Toggle
        document.querySelector('[name="show_social_links"]').addEventListener('change', function() {
            const socialGroup = document.getElementById('socialLinksGroup');
            socialGroup.style.display = this.checked ? 'block' : 'none';
        });

        // Einfacher Rich-Text-Editor mit ContentEditable
        let editorInitialized = false;
        
        function initSimpleEditor() {
            if (editorInitialized) return;
            
            const textarea = document.getElementById('edit_additional_info');
            if (!textarea) {
                console.error('Textarea nicht gefunden');
                return;
            }
            
            // Verstecke die Textarea
            textarea.style.display = 'none';
            
            // Erstelle Editor Container
            const editorWrapper = document.createElement('div');
            editorWrapper.className = 'simple-editor-wrapper';
            
            // Toolbar erstellen
            const toolbar = document.createElement('div');
            toolbar.className = 'simple-editor-toolbar';
            toolbar.innerHTML = `
                <button type="button" onclick="formatText('bold')" title="Fett"><b>B</b></button>
                <button type="button" onclick="formatText('italic')" title="Kursiv"><i>I</i></button>
                <button type="button" onclick="formatText('underline')" title="Unterstrichen"><u>U</u></button>
                <span style="border-left: 1px solid #ddd; height: 20px; margin: 0 5px;"></span>
                <button type="button" onclick="formatText('insertUnorderedList')" title="Liste">• Liste</button>
                <button type="button" onclick="formatText('insertOrderedList')" title="Nummerierte Liste">1. Liste</button>
                <span style="border-left: 1px solid #ddd; height: 20px; margin: 0 5px;"></span>
                <button type="button" onclick="formatText('justifyLeft')" title="Linksbündig">⬅</button>
                <button type="button" onclick="formatText('justifyCenter')" title="Zentriert">↔</button>
                <button type="button" onclick="formatText('justifyRight')" title="Rechtsbündig">➡</button>
                <span style="border-left: 1px solid #ddd; height: 20px; margin: 0 5px;"></span>
                <button type="button" onclick="insertLink()" title="Link einfügen">🔗 Link</button>
                <button type="button" onclick="formatText('removeFormat')" title="Formatierung entfernen">✖ Format</button>
            `;
            
            // Content Editable Bereich
            const content = document.createElement('div');
            content.className = 'simple-editor-content';
            content.contentEditable = 'true';
            content.id = 'editor-content';
            content.innerHTML = textarea.value || '<p>Zusätzliche Informationen hier eingeben...</p>';
            
            // Zusammensetzen
            editorWrapper.appendChild(toolbar);
            editorWrapper.appendChild(content);
            textarea.parentNode.insertBefore(editorWrapper, textarea);
            
            // Synchronisiere Content mit Textarea
            content.addEventListener('input', function() {
                textarea.value = content.innerHTML;
            });
            
            // Beim ersten Klick Platzhalter entfernen
            content.addEventListener('focus', function() {
                if (content.innerHTML === '<p>Zusätzliche Informationen hier eingeben...</p>') {
                    content.innerHTML = '<p><br></p>';
                }
            });
            
            editorInitialized = true;
            console.log('✅ Editor erfolgreich initialisiert!');
        }
        
        // Globale Formatierungsfunktion
        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            document.getElementById('editor-content').focus();
        }
        
        // Link einfügen
        function insertLink() {
            const url = prompt('URL eingeben:', 'https://');
            if (url && url !== 'https://') {
                formatText('createLink', url);
            }
        }
        
        // Funktion zum Schließen des Edit-Modals
        function closeEditMarkerModal() {
            // Editor zurücksetzen
            const editorWrapper = document.querySelector('.simple-editor-wrapper');
            if (editorWrapper) {
                editorWrapper.remove();
            }
            const textarea = document.getElementById('edit_additional_info');
            if (textarea) {
                textarea.style.display = 'block';
            }
            editorInitialized = false;
            
            document.getElementById('editMarkerModal').style.display = 'none';
        }

    </script>
</body>
</html>