<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Ungültige Anfrage');
    }

    $marker_id = $_POST['marker_id'] ?? null;
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $accuracy = $_POST['accuracy'] ?? null;
    $scan_type = $_POST['scan_type'] ?? 'QR';

    if (!$marker_id || !$latitude || !$longitude) {
        throw new Exception('Fehlende Parameter');
    }

    // Marker existiert prüfen
    $stmt = $pdo->prepare("SELECT id FROM markers WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$marker_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Marker nicht gefunden');
    }

    // GPS-Daten speichern
    $stmt = $pdo->prepare("
        UPDATE markers 
        SET gps_latitude = ?,
            gps_longitude = ?,
            gps_captured_at = NOW(),
            gps_captured_by = ?,
            gps_accuracy = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $latitude,
        $longitude,
        $scan_type,
        $accuracy,
        $marker_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'GPS-Standort erfolgreich gespeichert!'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
