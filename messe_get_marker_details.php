<?php
require_once 'config.php';
requireAjaxCSRF(); // CSRF-Schutz

header('Content-Type: application/json');

$markerId = isset($_GET['marker_id']) ? intval($_GET['marker_id']) : 0;
$messeId = isset($_GET['messe_id']) ? intval($_GET['messe_id']) : 0;

if (!$markerId || !$messeId) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Parameter']);
    exit;
}

try {
    // Messe-Marker-Details laden
    $stmt = $pdo->prepare("
        SELECT 
            mm.*,
            m.name as marker_name,
            m.category,
            m.qr_code,
            m.serial_number
        FROM messe_markers mm
        JOIN markers m ON mm.marker_id = m.id
        WHERE mm.marker_id = ? AND mm.messe_id = ?
    ");
    $stmt->execute([$markerId, $messeId]);
    $marker = $stmt->fetch();
    
    if (!$marker) {
        echo json_encode(['success' => false, 'message' => 'Marker nicht gefunden']);
        exit;
    }
    
    // Custom Fields laden
    $stmt = $pdo->prepare("
        SELECT * FROM messe_marker_fields 
        WHERE messe_marker_id = ? 
        ORDER BY display_order ASC
    ");
    $stmt->execute([$marker['id']]);
    $customFields = $stmt->fetchAll();
    
    // Badges laden (NEU!)
    $stmt = $pdo->prepare("
        SELECT * FROM messe_marker_badges 
        WHERE messe_marker_id = ? 
        ORDER BY display_order ASC
    ");
    $stmt->execute([$marker['id']]);
    $badges = $stmt->fetchAll();
    
    // 3D-Modelle laden
    $model3d_url = null;
    $stmt_3d = $pdo->prepare("
        SELECT file_path FROM marker_3d_models 
        WHERE marker_id = ? AND is_public = 1
        ORDER BY uploaded_at DESC LIMIT 1
    ");
    $stmt_3d->execute([$markerId]);
    $model3d = $stmt_3d->fetch();
    if ($model3d) {
        $model3d_url = $model3d["file_path"];
    }

    // Scan-Statistik aktualisieren
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $pdo->prepare("
        SELECT id FROM messe_scan_stats 
        WHERE messe_id = ? AND marker_id = ? AND ip_address = ?
    ");
    $stmt->execute([$messeId, $markerId, $ip]);
    
    if ($stmt->fetch()) {
        // Update
        $pdo->prepare("
            UPDATE messe_scan_stats 
            SET scan_count = scan_count + 1, last_scan = NOW() 
            WHERE messe_id = ? AND marker_id = ? AND ip_address = ?
        ")->execute([$messeId, $markerId, $ip]);
    } else {
        // Insert
        $pdo->prepare("
            INSERT INTO messe_scan_stats 
            (messe_id, marker_id, scan_count, unique_visitors, ip_address) 
            VALUES (?, ?, 1, 1, ?)
        ")->execute([$messeId, $markerId, $ip]);
    }
    
    // Response
    echo json_encode([
        'success' => true,
        'marker' => [
            'messe_marker_id' => $marker['id'], // ID aus messe_markers Tabelle
            'marker_id' => $marker['marker_id'],
            'marker_name' => $marker['marker_name'],
            'custom_title' => $marker['custom_title'],
            'custom_description' => $marker['custom_description'],
            'category' => $marker['category'],
            'qr_code' => $marker['qr_code'],
            'serial_number' => $marker['serial_number'],
            'model_3d_path' => $marker['model_3d_path'],
            'device_image' => $marker['device_image'] ?? null, // NEU!
            'model_3d_url' => $model3d_url ?? null,
            'is_featured' => $marker['is_featured'],
            'additional_info' => $marker['additional_info'] ?? null,
            'custom_fields' => $customFields,
            'badges' => $badges // NEU!
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}