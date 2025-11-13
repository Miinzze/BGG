<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // JSON-Daten empfangen
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['marker_id']) || !isset($input['latitude']) || !isset($input['longitude'])) {
        throw new Exception('Fehlende Parameter');
    }
    
    $marker_id = intval($input['marker_id']);
    $latitude = floatval($input['latitude']);
    $longitude = floatval($input['longitude']);
    $accuracy = isset($input['accuracy']) ? floatval($input['accuracy']) : null;
    $altitude = isset($input['altitude']) ? floatval($input['altitude']) : null;
    $altitude_accuracy = isset($input['altitude_accuracy']) ? floatval($input['altitude_accuracy']) : null;
    $heading = isset($input['heading']) ? floatval($input['heading']) : null;
    $speed = isset($input['speed']) ? floatval($input['speed']) : null;
    $scan_method = isset($input['scan_method']) ? $input['scan_method'] : 'QR';
    
    // GPS-Position in BEIDE Felder speichern für Kompatibilität
    $stmt = $pdo->prepare("
        UPDATE markers 
        SET latitude = ?,
            longitude = ?,
            gps_latitude = ?,
            gps_longitude = ?,
            gps_accuracy = ?,
            gps_captured_at = NOW(),
            gps_captured_by = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $latitude,
        $longitude,
        $latitude,
        $longitude,
        $accuracy,
        $scan_method,
        $marker_id
    ]);
    
    // Log-Eintrag
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, username, action, details, marker_id, ip_address, user_agent, created_at)
            VALUES (NULL, NULL, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            'gps_captured',
            "GPS-Position erfasst: {$latitude}, {$longitude} (Genauigkeit: " . ($accuracy ? round($accuracy, 2) . 'm' : 'unbekannt') . ") via {$scan_method}",
            $marker_id,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (Exception $e) {
        // Wenn Activity Log fehlschlägt, ignorieren wir es
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'GPS-Position erfolgreich gespeichert'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}