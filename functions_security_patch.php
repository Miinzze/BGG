<?php
/**
 * SICHERHEITS-UPDATE: functions_security_patch.php
 * 
 * Diese Datei enthält aktualisierte Versionen von Funktionen aus functions.php
 * mit erweiterten Sicherheitsmaßnahmen gegen SQL-Injection.
 * 
 * Anwendung: Fügen Sie am Anfang von functions.php folgendes ein:
 * require_once 'security_enhanced.php';
 * 
 * Dann ersetzen Sie die betroffenen Funktionen durch diese Versionen.
 */

/**
 * VERBESSERTE VERSION - Marker-Suche mit erweiterten SQL-Injection Schutz
 * 
 * ÄNDERUNGEN:
 * - Verwendung der SecurityEnhanced::sanitizeOrderBy() Methode
 * - Explizite Whitelist für alle sortierbaren Spalten
 * - Zusätzliche Validierung der Filter-Werte
 */
function searchMarkers($pdo, $filters = []) {
    // Security Enhancement: Verwende SecurityEnhanced für sichere ORDER BY
    require_once __DIR__ . '/security_enhanced.php';
    
    $sql = "SELECT m.*, u.username as created_by_name 
            FROM markers m 
            LEFT JOIN users u ON m.created_by = u.id 
            WHERE 1=1";
    
    $params = [];
    
    // Globale Textsuche - bereits sicher mit Prepared Statements
    if (!empty($filters['search'])) {
        $sql .= " AND (m.name LIKE ? OR m.rfid_chip LIKE ? OR m.serial_number LIKE ? OR m.category LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Kategorie-Filter
    if (!empty($filters['category'])) {
        $sql .= " AND m.category = ?";
        $params[] = $filters['category'];
    }
    
    // Status-Filter
    if (!empty($filters['status'])) {
        // Whitelist für erlaubte Status-Werte
        $allowedStatuses = ['storage', 'multi_device', 'verfügbar', 'vermietet', 'wartung'];
        
        if ($filters['status'] === 'storage') {
            $sql .= " AND m.is_storage = 1";
        } elseif ($filters['status'] === 'multi_device') {
            $sql .= " AND m.is_multi_device = 1";
        } elseif (in_array($filters['status'], $allowedStatuses)) {
            $sql .= " AND m.rental_status = ?";
            $params[] = $filters['status'];
        }
    }
    
    // Wartungsstatus-Filter
    if (!empty($filters['maintenance_status'])) {
        // Whitelist für Wartungsstatus
        $allowedMaintenanceStatus = ['overdue', 'due_soon', 'ok'];
        
        if (!in_array($filters['maintenance_status'], $allowedMaintenanceStatus)) {
            // Ungültiger Status - ignorieren
        } elseif ($filters['maintenance_status'] === 'overdue') {
            $sql .= " AND m.next_maintenance < CURDATE() AND m.is_storage = 0";
        } elseif ($filters['maintenance_status'] === 'due_soon') {
            $sql .= " AND m.next_maintenance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND m.is_storage = 0";
        } elseif ($filters['maintenance_status'] === 'ok') {
            $sql .= " AND (m.next_maintenance > DATE_ADD(CURDATE(), INTERVAL 30 DAY) OR m.is_storage = 1)";
        }
    }
    
    // Datumsbereich - mit Validierung
    if (!empty($filters['date_from'])) {
        // Validiere Datum-Format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_from'])) {
            $sql .= " AND DATE(m.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
    }
    
    if (!empty($filters['date_to'])) {
        // Validiere Datum-Format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_to'])) {
            $sql .= " AND DATE(m.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
    }
    
    // Kraftstoff-Filter - mit Integer-Validierung
    if (!empty($filters['fuel_min'])) {
        $fuelMin = filter_var($filters['fuel_min'], FILTER_VALIDATE_INT);
        if ($fuelMin !== false && $fuelMin >= 0 && $fuelMin <= 100) {
            $sql .= " AND m.fuel_level >= ?";
            $params[] = $fuelMin;
        }
    }
    
    if (!empty($filters['fuel_max'])) {
        $fuelMax = filter_var($filters['fuel_max'], FILTER_VALIDATE_INT);
        if ($fuelMax !== false && $fuelMax >= 0 && $fuelMax <= 100) {
            $sql .= " AND m.fuel_level <= ?";
            $params[] = $fuelMax;
        }
    }
    
    // SICHERHEITS-UPDATE: Verwende SecurityEnhanced für ORDER BY
    $allowedSortColumns = ['name', 'category', 'created_at', 'next_maintenance', 'fuel_level', 'rental_status'];
    $sortOrder = SecurityEnhanced::sanitizeOrderBy(
        $filters['sort_by'] ?? 'created_at',
        $filters['sort_dir'] ?? 'DESC',
        $allowedSortColumns
    );
    
    $sql .= " ORDER BY m.{$sortOrder['column']} {$sortOrder['direction']}";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * NEUE FUNKTION: Render Custom Fields mit XSS-Schutz
 * 
 * Diese Funktion sollte verwendet werden, um Custom Field Werte sicher auszugeben
 * 
 * @param PDO $pdo
 * @param int $markerId
 * @return string HTML Output
 */
function renderMarkerCustomFields($pdo, $markerId) {
    require_once __DIR__ . '/security_enhanced.php';
    
    $stmt = $pdo->prepare("
        SELECT cf.field_label, cf.field_type, mcv.field_value
        FROM marker_custom_values mcv
        JOIN custom_fields cf ON mcv.field_id = cf.id
        WHERE mcv.marker_id = ?
        ORDER BY cf.display_order, cf.id
    ");
    $stmt->execute([$markerId]);
    $customFields = $stmt->fetchAll();
    
    if (empty($customFields)) {
        return '';
    }
    
    $html = '<div class="custom-fields-section">';
    $html .= '<h3>Zusätzliche Felder</h3>';
    
    foreach ($customFields as $field) {
        $html .= SecurityEnhanced::renderCustomFieldValue($field);
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * NEUE FUNKTION: Sichere Kommentar-Ausgabe
 * 
 * Diese Funktion sollte verwendet werden, um Kommentare sicher auszugeben
 * 
 * @param string $comment
 * @param bool $allowFormatting
 * @return string
 */
function renderSafeComment($comment, $allowFormatting = true) {
    require_once __DIR__ . '/security_enhanced.php';
    return SecurityEnhanced::renderComment($comment, $allowFormatting);
}