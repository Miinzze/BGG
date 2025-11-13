<?php
/**
 * Erweiterte Funktionen für neue Features
 * Diese Datei sollte in functions.php eingebunden werden: require_once 'extended_functions.php';
 */

/**
 * Prüft, ob ein Marker an einem Ort platziert werden darf (Geo-Fence Validierung)
 * 
 * @param int $markerId Marker ID
 * @param float $latitude Breitengrad
 * @param float $longitude Längengrad
 * @return array ['allowed' => bool, 'message' => string, 'group_name' => string]
 */
function validateMarkerPlacement($markerId, $latitude, $longitude) {
    global $pdo;
    
    // Marker-Daten laden
    $stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
    $stmt->execute([$markerId]);
    $marker = $stmt->fetch();
    
    if (!$marker) {
        return ['allowed' => false, 'message' => 'Marker nicht gefunden'];
    }
    
    // Prüfen, ob Position in einem Geo-Fence liegt
    $stmt = $pdo->query("SELECT g.*, gg.* FROM geofences g 
                        LEFT JOIN geofence_groups gg ON g.group_id = gg.id 
                        WHERE g.is_active = 1");
    $geofences = $stmt->fetchAll();
    
    foreach ($geofences as $fence) {
        if (isPointInGeofence($latitude, $longitude, $fence)) {
            // Punkt liegt in diesem Geo-Fence
            $group = $fence;
            
            // Gerätetyp-Einschränkungen prüfen
            if ($marker['is_customer_device'] && !$group['allow_customer_devices']) {
                return [
                    'allowed' => false, 
                    'message' => "Kundengeräte sind in dieser Zone ('{$group['name']}') nicht erlaubt!",
                    'group_name' => $group['name']
                ];
            }
            
            if ($marker['is_repair_device'] && !$group['allow_repair_devices']) {
                return [
                    'allowed' => false,
                    'message' => "Reparaturgeräte sind in dieser Zone ('{$group['name']}') nicht erlaubt!",
                    'group_name' => $group['name']
                ];
            }
            
            if ($marker['is_storage'] && !$group['allow_storage_devices']) {
                return [
                    'allowed' => false,
                    'message' => "Lagergeräte sind in dieser Zone ('{$group['name']}') nicht erlaubt!",
                    'group_name' => $group['name']
                ];
            }
            
            if (!$marker['is_storage'] && !$marker['is_customer_device'] && !$marker['is_repair_device'] && !$group['allow_rental_devices']) {
                return [
                    'allowed' => false,
                    'message' => "Mietgeräte sind in dieser Zone ('{$group['name']}') nicht erlaubt!",
                    'group_name' => $group['name']
                ];
            }
            
            // NEU: Prüfen, ob nur fertige Geräte erlaubt sind
            if ($group['allow_only_finished'] && !$marker['is_finished']) {
                return [
                    'allowed' => false,
                    'message' => "In dieser Zone ('{$group['name']}') sind nur fertiggestellte Geräte erlaubt! Bitte markieren Sie das Gerät zuerst als 'Fertig'.",
                    'group_name' => $group['name']
                ];
            }
            
            // Alles OK
            return [
                'allowed' => true,
                'message' => "Platzierung in Zone '{$group['name']}' erlaubt",
                'group_name' => $group['name']
            ];
        }
    }
    
    // Nicht in einem Geo-Fence - Standard erlauben
    return ['allowed' => true, 'message' => 'Platzierung erlaubt (keine Geo-Fence-Einschränkung)'];
}

/**
 * Prüft, ob ein Punkt innerhalb eines Geo-Fence liegt
 * 
 * @param float $lat Breitengrad
 * @param float $lng Längengrad
 * @param array $fence Geo-Fence Daten
 * @return bool
 */
function isPointInGeofence($lat, $lng, $fence) {
    if ($fence['fence_type'] === 'circle') {
        // Kreis-Prüfung
        $distance = calculateDistance(
            $lat, $lng,
            $fence['center_lat'], $fence['center_lng']
        );
        return $distance <= $fence['radius'];
    } else {
        // Polygon-Prüfung (Ray Casting Algorithm)
        $coordinates = json_decode($fence['coordinates'], true);
        if (!$coordinates) return false;
        
        $vertices_x = array_column($coordinates, 'lng');
        $vertices_y = array_column($coordinates, 'lat');
        $points_polygon = count($coordinates);
        
        $inside = false;
        for ($i = 0, $j = $points_polygon - 1; $i < $points_polygon; $j = $i++) {
            if ((($vertices_y[$i] > $lat) != ($vertices_y[$j] > $lat)) &&
                ($lng < ($vertices_x[$j] - $vertices_x[$i]) * ($lat - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i])) {
                $inside = !$inside;
            }
        }
        
        return $inside;
    }
}

/**
 * Berechnet die Distanz zwischen zwei GPS-Punkten in Metern
 * 
 * @param float $lat1 Breitengrad 1
 * @param float $lng1 Längengrad 1
 * @param float $lat2 Breitengrad 2
 * @param float $lng2 Längengrad 2
 * @return float Distanz in Metern
 */
function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6371000; // Erdradius in Metern
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLng/2) * sin($dLng/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Loggt eine erweiterte Aktivität mit mehr Details
 * 
 * @param string $action Aktion
 * @param string $details Details
 * @param int|null $markerId Marker ID
 * @param array $additionalData Zusätzliche Daten als Array
 */
function logExtendedActivity($action, $details, $markerId = null, $additionalData = []) {
    global $pdo;
    
    $userId = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'system';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Zusätzliche Daten als JSON speichern
    $detailsJson = !empty($additionalData) ? json_encode($additionalData) : $details;
    
    $stmt = $pdo->prepare("INSERT INTO activity_log 
        (user_id, username, action, details, marker_id, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $username, $action, $detailsJson, $markerId, $ipAddress, $userAgent]);
}

/**
 * Erstellt einen Marker-Historie-Eintrag
 * 
 * @param int $markerId Marker ID
 * @param string $action Aktion (created, updated, deleted, etc.)
 * @param array $changes Array mit Änderungen ['field' => ['old' => val, 'new' => val]]
 */
function createMarkerHistoryEntry($markerId, $action, $changes = []) {
    global $pdo;
    
    $userId = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'system';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $changeJson = !empty($changes) ? json_encode($changes) : null;
    
    $stmt = $pdo->prepare("INSERT INTO marker_history 
        (marker_id, user_id, username, action, change_details, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$markerId, $userId, $username, $action, $changeJson, $ipAddress, $userAgent]);
}

/**
 * Lädt ein Marker-Template
 * 
 * @param int $templateId Template ID
 * @return array|null Template-Daten oder null
 */
function loadMarkerTemplate($templateId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM marker_templates WHERE id = ?");
    $stmt->execute([$templateId]);
    return $stmt->fetch();
}

/**
 * Dupliziert einen Marker
 * 
 * @param int $markerId ID des zu duplizierenden Markers
 * @param string $newQrCode Neuer QR-Code für das Duplikat
 * @return int|false ID des neuen Markers oder false bei Fehler
 */
function duplicateMarker($markerId, $newQrCode) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Original Marker laden
        $stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
        $stmt->execute([$markerId]);
        $original = $stmt->fetch();
        
        if (!$original) {
            throw new Exception("Marker nicht gefunden");
        }
        
        // QR-Code prüfen
        $stmt = $pdo->prepare("SELECT * FROM qr_code_pool WHERE qr_code = ? AND is_assigned = 0");
        $stmt->execute([$newQrCode]);
        if (!$stmt->fetch()) {
            throw new Exception("QR-Code nicht verfügbar");
        }
        
        // Neuen Marker erstellen
        $stmt = $pdo->prepare("INSERT INTO markers 
            (qr_code, name, category, serial_number, is_storage, rental_status, 
            operating_hours, fuel_level, maintenance_interval_months, is_multi_device, 
            nfc_enabled, nfc_chip_id, marker_type, is_customer_device, customer_name, 
            order_number, is_repair_device, repair_description, fuel_unit, fuel_capacity, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $newName = $original['name'] . ' (Kopie)';
        
        $stmt->execute([
            $newQrCode,
            $newName,
            $original['category'],
            $original['serial_number'],
            $original['is_storage'],
            $original['rental_status'],
            $original['operating_hours'],
            $original['fuel_level'],
            $original['maintenance_interval_months'],
            $original['is_multi_device'],
            $original['nfc_enabled'],
            $original['nfc_chip_id'],
            $original['marker_type'],
            $original['is_customer_device'],
            $original['customer_name'],
            $original['order_number'],
            $original['is_repair_device'],
            $original['repair_description'],
            $original['fuel_unit'],
            $original['fuel_capacity'],
            $_SESSION['user_id'] ?? null
        ]);
        
        $newMarkerId = $pdo->lastInsertId();
        
        // QR-Code als zugewiesen markieren
        $stmt = $pdo->prepare("UPDATE qr_code_pool SET is_assigned = 1, marker_id = ?, assigned_at = NOW() WHERE qr_code = ?");
        $stmt->execute([$newMarkerId, $newQrCode]);
        
        // Custom Fields kopieren
        $stmt = $pdo->prepare("SELECT field_id, field_value FROM marker_custom_values WHERE marker_id = ?");
        $stmt->execute([$markerId]);
        $customValues = $stmt->fetchAll();
        
        foreach ($customValues as $value) {
            $stmt = $pdo->prepare("INSERT INTO marker_custom_values (marker_id, field_id, field_value) VALUES (?, ?, ?)");
            $stmt->execute([$newMarkerId, $value['field_id'], $value['field_value']]);
        }
        
        $pdo->commit();
        
        logExtendedActivity('marker_duplicated', "Marker '$newName' dupliziert von ID $markerId", $newMarkerId);
        createMarkerHistoryEntry($newMarkerId, 'created', ['source' => 'duplicate', 'original_id' => $markerId]);
        
        return $newMarkerId;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Fehler beim Duplizieren: " . $e->getMessage());
        return false;
    }
}

/**
 * Holt die Marker-Historie
 * 
 * @param int $markerId Marker ID
 * @param int $limit Anzahl der Einträge
 * @return array Historie-Einträge
 */
function getMarkerHistory($markerId, $limit = 50) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM marker_history 
        WHERE marker_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?");
    $stmt->execute([$markerId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Speichert oder aktualisiert die Benutzer-Signatur
 * 
 * @param int $userId Benutzer ID
 * @param string $signatureData Base64-kodierte Signatur
 * @return bool Erfolg
 */
function saveUserSignature($userId, $signatureData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user_signatures (user_id, signature_data) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE signature_data = ?, updated_at = NOW()");
        $stmt->execute([$userId, $signatureData, $signatureData]);
        
        logExtendedActivity('signature_updated', "Benutzer-Signatur aktualisiert");
        return true;
    } catch (Exception $e) {
        error_log("Fehler beim Speichern der Signatur: " . $e->getMessage());
        return false;
    }
}

/**
 * Lädt die Benutzer-Signatur
 * 
 * @param int $userId Benutzer ID
 * @return string|null Base64-kodierte Signatur oder null
 */
function getUserSignature($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT signature_data FROM user_signatures WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result ? $result['signature_data'] : null;
}

/**
 * Berechnet Dashboard-Statistiken für heute
 * 
 * @return array Statistiken
 */
function calculateDashboardStats() {
    global $pdo;
    
    $stats = [];
    $today = date('Y-m-d');
    
    // Marker-Statistiken
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_activated = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN is_storage = 1 THEN 1 ELSE 0 END) as storage,
        SUM(CASE WHEN is_customer_device = 1 THEN 1 ELSE 0 END) as customer,
        SUM(CASE WHEN is_repair_device = 1 THEN 1 ELSE 0 END) as repair,
        SUM(CASE WHEN is_finished = 1 THEN 1 ELSE 0 END) as finished
        FROM markers WHERE deleted_at IS NULL");
    $stats['markers'] = $stmt->fetch();
    
    // Wartungen
    $stmt = $pdo->query("SELECT 
        COUNT(*) as overdue
        FROM markers 
        WHERE next_maintenance < CURDATE() AND deleted_at IS NULL AND is_storage = 0");
    $stats['maintenance_overdue'] = $stmt->fetch()['overdue'];
    
    // Aktivitäten heute
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_log WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $stats['activities_today'] = $stmt->fetch()['count'];
    
    // Neue Marker heute
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM markers WHERE DATE(created_at) = ? AND deleted_at IS NULL");
    $stmt->execute([$today]);
    $stats['new_markers_today'] = $stmt->fetch()['count'];
    
    return $stats;
}

/**
 * Holt die letzten Aktivitäten für das Dashboard
 * 
 * @param int $limit Anzahl der Einträge
 * @return array Aktivitäten
 */
function getRecentActivities($limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT al.*, m.name as marker_name, m.qr_code 
        FROM activity_log al 
        LEFT JOIN markers m ON al.marker_id = m.id 
        ORDER BY al.created_at DESC 
        LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Exportiert Marker-Historie als CSV
 * 
 * @param int $markerId Marker ID
 * @return string CSV-Inhalt
 */
function exportMarkerHistoryCSV($markerId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT mh.*, m.name, m.qr_code 
        FROM marker_history mh 
        LEFT JOIN markers m ON mh.marker_id = m.id 
        WHERE mh.marker_id = ? 
        ORDER BY mh.created_at DESC");
    $stmt->execute([$markerId]);
    $history = $stmt->fetchAll();
    
    $csv = "Zeitstempel,Benutzer,Aktion,Details\n";
    foreach ($history as $entry) {
        $csv .= sprintf('"%s","%s","%s","%s"' . "\n",
            $entry['created_at'],
            $entry['username'],
            $entry['action'],
            str_replace('"', '""', $entry['change_details'])
        );
    }
    
    return $csv;
}