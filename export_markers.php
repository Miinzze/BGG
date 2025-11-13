<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
// ✅ GEÄNDERT: Von requirePermission zu spezifischerer Permission
requirePermission('markers_export');

$message = '';
$messageType = '';

// Export durchführen
if (isset($_POST['export'])) {
    $exportType = $_POST['export_type'] ?? 'all';
    $includeImages = isset($_POST['include_images']);
    $includeDocuments = isset($_POST['include_documents']);
    $selectedIds = $_POST['selected_markers'] ?? [];
    
    try {
        $markers = [];
        
        if ($exportType === 'selected' && !empty($selectedIds)) {
            $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
            $stmt = $pdo->prepare("SELECT * FROM markers WHERE id IN ($placeholders) AND deleted_at IS NULL");
            $stmt->execute($selectedIds);
            $markers = $stmt->fetchAll();
        } else {
            $stmt = $pdo->query("SELECT * FROM markers WHERE deleted_at IS NULL ORDER BY created_at DESC");
            $markers = $stmt->fetchAll();
        }
        
        $exportData = [
            'export_date' => date('Y-m-d H:i:s'),
            'exported_by' => $_SESSION['username'],
            'version' => '2.0', // Version erhöht für QR-System
            'marker_count' => count($markers),
            'markers' => []
        ];
        
        foreach ($markers as $marker) {
            $markerData = $marker;
            
            // Bilder hinzufügen
            if ($includeImages) {
                $stmt = $pdo->prepare("SELECT image_path FROM marker_images WHERE marker_id = ?");
                $stmt->execute([$marker['id']]);
                $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $markerData['images_base64'] = [];
                foreach ($images as $imagePath) {
                    if (file_exists($imagePath)) {
                        $imageData = file_get_contents($imagePath);
                        $markerData['images_base64'][] = [
                            'filename' => basename($imagePath),
                            'data' => base64_encode($imageData),
                            'mime' => mime_content_type($imagePath)
                        ];
                    }
                }
            }
            
            // Dokumente hinzufügen
            if ($includeDocuments) {
                $stmt = $pdo->prepare("SELECT document_path, document_name, is_public, public_description FROM marker_documents WHERE marker_id = ?");
                $stmt->execute([$marker['id']]);
                $documents = $stmt->fetchAll();
                
                $markerData['documents_base64'] = [];
                foreach ($documents as $doc) {
                    if (file_exists($doc['document_path'])) {
                        $docData = file_get_contents($doc['document_path']);
                        $markerData['documents_base64'][] = [
                            'filename' => $doc['document_name'],
                            'data' => base64_encode($docData),
                            'mime' => 'application/pdf',
                            'is_public' => $doc['is_public'],
                            'public_description' => $doc['public_description']
                        ];
                    }
                }
            }
            
            // Seriennummern bei Multi-Device
            if ($marker['is_multi_device']) {
                $stmt = $pdo->prepare("SELECT serial_number FROM marker_serial_numbers WHERE marker_id = ?");
                $stmt->execute([$marker['id']]);
                $markerData['serial_numbers'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Custom Fields
            $stmt = $pdo->prepare("
                SELECT cf.field_label, mcv.field_value
                FROM marker_custom_values mcv
                JOIN custom_fields cf ON mcv.field_id = cf.id
                WHERE mcv.marker_id = ?
            ");
            $stmt->execute([$marker['id']]);
            $markerData['custom_fields'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $exportData['markers'][] = $markerData;
        }
        
        logActivity('markers_exported', count($markers) . ' Marker exportiert');
        
        // JSON Download
        $filename = 'marker_export_' . date('Y-m-d_His') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        $message = 'Export-Fehler: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Alle Marker für Auswahl laden
$stmt = $pdo->query("SELECT id, name, category, qr_code FROM markers WHERE deleted_at IS NULL ORDER BY name ASC");
$allMarkers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marker exportieren - Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-file-export"></i> Marker exportieren</h1>
                <div class="header-actions">
                    <a href="markers.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <!-- Rest des HTML-Codes bleibt identisch -->
            
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>