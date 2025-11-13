<?php
/**
 * API zum Abrufen der Scan-History
 */

require_once 'config.php';
require_once 'functions.php';
requireLogin();

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'];
    
    // Letzte 10 Scans des aktuellen Benutzers abrufen
    $stmt = $pdo->prepare("
        SELECT 
            sh.qr_code,
            sh.scan_type,
            sh.scanned_at,
            m.name as marker_name,
            m.id as marker_id,
            CASE 
                WHEN TIMESTAMPDIFF(SECOND, sh.scanned_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(SECOND, sh.scanned_at, NOW()), ' Sekunden')
                WHEN TIMESTAMPDIFF(MINUTE, sh.scanned_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, sh.scanned_at, NOW()), ' Minuten')
                WHEN TIMESTAMPDIFF(HOUR, sh.scanned_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, sh.scanned_at, NOW()), ' Stunden')
                ELSE CONCAT(TIMESTAMPDIFF(DAY, sh.scanned_at, NOW()), ' Tage')
            END as time_ago
        FROM scan_history sh
        LEFT JOIN markers m ON sh.marker_id = m.id AND m.deleted_at IS NULL
        WHERE sh.user_id = ?
        ORDER BY sh.scanned_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $history = $stmt->fetchAll();
    
    // Scan-Type Labels
    $typeLabels = [
        'activation' => 'Aktivierung',
        'view' => 'Anzeige',
        'update' => 'Aktualisierung'
    ];
    
    // Formatieren
    foreach ($history as &$scan) {
        $scan['scan_type_label'] = $typeLabels[$scan['scan_type']] ?? $scan['scan_type'];
    }
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Laden der Scan-History: ' . $e->getMessage()
    ]);
}
