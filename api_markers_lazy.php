<?php
/**
 * MARKER LAZY LOADING API
 * 
 * AJAX-Endpoint für dynamisches Nachladen von Markern
 * Lädt nur Marker im sichtbaren Kartenbereich
 * 
 * FEATURES:
 * - Bounding Box Filter (nur sichtbare Marker)
 * - Clustering auf Server-Seite für >100 Marker
 * - Pagination
 * - Cache-Support
 * 
 * VERWENDUNG:
 * GET /api_markers_lazy.php?bounds=lat1,lng1,lat2,lng2&zoom=10
 */

require_once 'config.php';
require_once 'functions.php';

// Nur für eingeloggte User
requireLogin();

// CORS Headers (falls von anderem Port/Domain aufgerufen)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Parameter
$bounds = isset($_GET['bounds']) ? $_GET['bounds'] : null;
$zoom = isset($_GET['zoom']) ? intval($_GET['zoom']) : 10;
$limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 1000) : 500;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Bounds parsen: "lat1,lng1,lat2,lng2"
$boundsParts = $bounds ? explode(',', $bounds) : null;

if (!$boundsParts || count($boundsParts) !== 4) {
    // Keine Bounds = alle Marker (für Fallback)
    $sql = "SELECT id, name, qr_code, latitude, longitude, category, rental_status, 
            is_storage, is_multi_device, marker_type, nfc_chip_id
            FROM markers 
            WHERE is_activated = 1 
            AND deleted_at IS NULL 
            AND latitude IS NOT NULL 
            AND longitude IS NOT NULL";
    $params = [];
} else {
    // Mit Bounds = nur sichtbare Marker
    list($lat1, $lng1, $lat2, $lng2) = array_map('floatval', $boundsParts);
    
    // Min/Max für Bounding Box
    $minLat = min($lat1, $lat2);
    $maxLat = max($lat1, $lat2);
    $minLng = min($lng1, $lng2);
    $maxLng = max($lng1, $lng2);
    
    $sql = "SELECT id, name, qr_code, latitude, longitude, category, rental_status, 
            is_storage, is_multi_device, marker_type, nfc_chip_id
            FROM markers 
            WHERE is_activated = 1 
            AND deleted_at IS NULL 
            AND latitude IS NOT NULL 
            AND longitude IS NOT NULL
            AND latitude BETWEEN ? AND ?
            AND longitude BETWEEN ? AND ?";
    
    $params = [$minLat, $maxLat, $minLng, $maxLng];
}

// Zähle Gesamt-Marker (für Pagination)
$countSql = str_replace('SELECT id, name, qr_code, latitude, longitude, category, rental_status, 
            is_storage, is_multi_device, marker_type, nfc_chip_id', 'SELECT COUNT(*)', $sql);
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalMarkers = $countStmt->fetchColumn();

// Clustering bei vielen Markern und niedriger Zoom-Stufe
$useCluster = ($totalMarkers > 100 && $zoom < 12);

if ($useCluster) {
    // Server-seitiges Clustering
    // Gruppiere Marker nach gerundeten Koordinaten
    $precision = getPrecisionForZoom($zoom);
    
    $clusterSql = "SELECT 
        ROUND(latitude, $precision) as cluster_lat,
        ROUND(longitude, $precision) as cluster_lng,
        COUNT(*) as marker_count,
        GROUP_CONCAT(id) as marker_ids,
        GROUP_CONCAT(name SEPARATOR '|') as marker_names
        FROM markers 
        WHERE is_activated = 1 
        AND deleted_at IS NULL 
        AND latitude IS NOT NULL 
        AND longitude IS NOT NULL";
    
    if ($boundsParts) {
        $clusterSql .= " AND latitude BETWEEN ? AND ?
                        AND longitude BETWEEN ? AND ?";
    }
    
    $clusterSql .= " GROUP BY cluster_lat, cluster_lng
                     LIMIT ? OFFSET ?";
    
    $clusterParams = $params;
    $clusterParams[] = $limit;
    $clusterParams[] = $offset;
    
    $stmt = $pdo->prepare($clusterSql);
    $stmt->execute($clusterParams);
    $clusters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatiere Cluster für Frontend
    $markers = [];
    foreach ($clusters as $cluster) {
        if ($cluster['marker_count'] == 1) {
            // Einzelner Marker
            $markerId = $cluster['marker_ids'];
            $markerStmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
            $markerStmt->execute([$markerId]);
            $marker = $markerStmt->fetch(PDO::FETCH_ASSOC);
            
            $markers[] = formatMarkerForMap($marker);
        } else {
            // Cluster
            $markers[] = [
                'type' => 'cluster',
                'count' => intval($cluster['marker_count']),
                'latitude' => floatval($cluster['cluster_lat']),
                'longitude' => floatval($cluster['cluster_lng']),
                'marker_ids' => explode(',', $cluster['marker_ids']),
                'names' => explode('|', $cluster['marker_names'])
            ];
        }
    }
    
    $result = [
        'success' => true,
        'markers' => $markers,
        'total' => $totalMarkers,
        'clustered' => true,
        'zoom' => $zoom
    ];
    
} else {
    // Normale Marker-Liste (bei hoher Zoom-Stufe oder wenigen Markern)
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rawMarkers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatiere für Frontend
    $markers = array_map('formatMarkerForMap', $rawMarkers);
    
    $result = [
        'success' => true,
        'markers' => $markers,
        'total' => $totalMarkers,
        'clustered' => false,
        'zoom' => $zoom,
        'limit' => $limit,
        'offset' => $offset
    ];
}

// Response
echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;

/**
 * Formatiere Marker für Karte
 */
function formatMarkerForMap($marker) {
    return [
        'type' => 'marker',
        'id' => intval($marker['id']),
        'name' => $marker['name'],
        'qr_code' => $marker['qr_code'],
        'latitude' => floatval($marker['latitude']),
        'longitude' => floatval($marker['longitude']),
        'category' => $marker['category'],
        'rental_status' => $marker['rental_status'],
        'is_storage' => (bool)$marker['is_storage'],
        'is_multi_device' => (bool)$marker['is_multi_device'],
        'marker_type' => $marker['marker_type'],
        'nfc_chip_id' => $marker['nfc_chip_id']
    ];
}

/**
 * Berechne Clustering-Präzision basierend auf Zoom
 */
function getPrecisionForZoom($zoom) {
    if ($zoom <= 5) return 0; // Sehr grobe Cluster
    if ($zoom <= 8) return 1;
    if ($zoom <= 10) return 2;
    if ($zoom <= 12) return 3;
    return 4; // Feine Cluster
}