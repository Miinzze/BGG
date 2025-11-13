<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('settings_manage');

header('Content-Type: application/json');

$messeId = isset($_GET['messe_id']) ? intval($_GET['messe_id']) : 0;

try {
    $stmt = $pdo->prepare("
        SELECT mm.*, m.name as marker_name, m.qr_code 
        FROM messe_markers mm
        JOIN markers m ON mm.marker_id = m.id
        WHERE mm.messe_id = ?
        ORDER BY mm.is_featured DESC, mm.display_order ASC
    ");
    $stmt->execute([$messeId]);
    $devices = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'messe_id' => $messeId,
        'devices' => $devices
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}