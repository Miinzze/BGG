<?php
/**
 * Upload Handler für Messe-Gerätebilder und Badges
 * Wird von messe_admin.php verwendet
 */

require_once 'config.php';
requireAjaxCSRF(); // CSRF-Schutz
require_once 'functions.php'; // WICHTIG: functions.php für requirePermission()

requirePermission('manage_system_settings');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$action = $_POST['action'] ?? '';

// ===== BILD HOCHLADEN =====
if ($action === 'upload_device_image') {
    $messeMarkerId = intval($_POST['messe_marker_id'] ?? 0);
    
    if (!$messeMarkerId) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Marker-ID']);
        exit;
    }
    
    if (!isset($_FILES['device_image']) || $_FILES['device_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Keine Datei hochgeladen oder Upload-Fehler']);
        exit;
    }
    
    $file = $_FILES['device_image'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    // Validierung
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Ungültiger Dateityp. Nur JPG, PNG, WEBP und GIF erlaubt.']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Datei zu groß. Maximum 10MB.']);
        exit;
    }
    
    // Upload-Verzeichnis erstellen
    $uploadDir = __DIR__ . '/uploads/messe_devices/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Dateiname generieren
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'device_' . $messeMarkerId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Alte Datei löschen
    try {
        $stmt = $pdo->prepare("SELECT device_image FROM messe_markers WHERE id = ?");
        $stmt->execute([$messeMarkerId]);
        $oldImage = $stmt->fetchColumn();
        
        if ($oldImage && file_exists(__DIR__ . '/' . $oldImage)) {
            unlink(__DIR__ . '/' . $oldImage);
        }
    } catch (Exception $e) {
        // Alte Datei konnte nicht gelöscht werden, aber das ist nicht kritisch
    }
    
    // Datei verschieben
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $relativePath = 'uploads/messe_devices/' . $filename;
        
        // Datenbank aktualisieren
        try {
            $stmt = $pdo->prepare("UPDATE messe_markers SET device_image = ? WHERE id = ?");
            $stmt->execute([$relativePath, $messeMarkerId]);
            
            // Hole die richtige marker_id für Activity Log
            $stmt = $pdo->prepare("SELECT marker_id FROM messe_markers WHERE id = ?");
            $stmt->execute([$messeMarkerId]);
            $actualMarkerId = $stmt->fetchColumn();
            
            // Aktivität loggen
            logActivity('messe_device_image_upload', "Gerätebild hochgeladen für Messe-Marker ID: $messeMarkerId", $actualMarkerId);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Bild erfolgreich hochgeladen',
                'image_path' => $relativePath,
                'image_url' => $relativePath
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Verschieben der Datei']);
    }
    exit;
}

// ===== BILD LÖSCHEN =====
if ($action === 'delete_device_image') {
    $messeMarkerId = intval($_POST['messe_marker_id'] ?? 0);
    
    if (!$messeMarkerId) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Marker-ID']);
        exit;
    }
    
    try {
        // Bild-Pfad holen
        $stmt = $pdo->prepare("SELECT device_image FROM messe_markers WHERE id = ?");
        $stmt->execute([$messeMarkerId]);
        $imagePath = $stmt->fetchColumn();
        
        // Datei löschen
        if ($imagePath && file_exists(__DIR__ . '/' . $imagePath)) {
            unlink(__DIR__ . '/' . $imagePath);
        }
        
        // Datenbank aktualisieren
        $stmt = $pdo->prepare("UPDATE messe_markers SET device_image = NULL WHERE id = ?");
        $stmt->execute([$messeMarkerId]);
        
        // Hole die richtige marker_id für Activity Log
        $stmt = $pdo->prepare("SELECT marker_id FROM messe_markers WHERE id = ?");
        $stmt->execute([$messeMarkerId]);
        $actualMarkerId = $stmt->fetchColumn();
        
        // Aktivität loggen
        logActivity('messe_device_image_delete', "Gerätebild gelöscht für Messe-Marker ID: $messeMarkerId", $actualMarkerId);
        
        echo json_encode(['success' => true, 'message' => 'Bild erfolgreich gelöscht']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
    }
    exit;
}

// ===== BADGE HINZUFÜGEN =====
if ($action === 'add_badge') {
    $messeMarkerId = intval($_POST['messe_marker_id'] ?? 0);
    $badgeText = trim($_POST['badge_text'] ?? '');
    $badgeIcon = trim($_POST['badge_icon'] ?? '');
    $badgeColor = trim($_POST['badge_color'] ?? '#FFD700');
    $displayOrder = intval($_POST['display_order'] ?? 0);
    
    if (!$messeMarkerId || !$badgeText) {
        echo json_encode(['success' => false, 'message' => 'Marker-ID und Badge-Text erforderlich']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO messe_marker_badges (messe_marker_id, badge_text, badge_icon, badge_color, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$messeMarkerId, $badgeText, $badgeIcon, $badgeColor, $displayOrder]);
        
        // Hole die richtige marker_id für Activity Log
        $stmt = $pdo->prepare("SELECT marker_id FROM messe_markers WHERE id = ?");
        $stmt->execute([$messeMarkerId]);
        $actualMarkerId = $stmt->fetchColumn();
        
        // Aktivität loggen
        logActivity('messe_badge_add', "Badge '$badgeText' hinzugefügt für Messe-Marker ID: $messeMarkerId", $actualMarkerId);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Badge hinzugefügt',
            'badge_id' => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
    }
    exit;
}

// ===== BADGE LÖSCHEN =====
if ($action === 'delete_badge') {
    $badgeId = intval($_POST['badge_id'] ?? 0);
    
    if (!$badgeId) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Badge-ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM messe_marker_badges WHERE id = ?");
        $stmt->execute([$badgeId]);
        
        // Aktivität loggen (NULL als marker_id da Badge keine direkte Marker-Referenz hat)
        logActivity('messe_badge_delete', "Badge gelöscht (ID: $badgeId)", null);
        
        echo json_encode(['success' => true, 'message' => 'Badge gelöscht']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);