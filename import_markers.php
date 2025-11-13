<?php
require_once 'config.php';
require_once 'functions.php';
// ✅ GEÄNDERT: Von requireAdmin() zu spezifischer Permission
requirePermission('markers_import');

$message = '';
$messageType = '';
$importResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    validateCSRF();
    
    $file = $_FILES['import_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload-Fehler';
        $messageType = 'danger';
    } elseif ($file['type'] !== 'application/json' && !str_ends_with($file['name'], '.json')) {
        $message = 'Nur JSON-Dateien erlaubt';
        $messageType = 'danger';
    } else {
        try {
            $jsonContent = file_get_contents($file['tmp_name']);
            $importData = json_decode($jsonContent, true);
            
            if (!$importData || !isset($importData['markers'])) {
                throw new Exception('Ungültige JSON-Struktur');
            }
            
            $imported = 0;
            $skipped = 0;
            $errors = [];
            
            $pdo->beginTransaction();
            
            foreach ($importData['markers'] as $markerData) {
                try {
                    // QR-Code aus Import-Daten
                    $qrCode = $markerData['qr_code'] ?? $markerData['rfid_chip'] ?? null; // Fallback für alte Exporte
                    
                    if (!$qrCode) {
                        $errors[] = "Marker '{$markerData['name']}': Kein QR-Code vorhanden";
                        $skipped++;
                        continue;
                    }
                    
                    // ===== WICHTIG: QR-Code-Prüfung =====
                    
                    // 1. Prüfen ob Marker mit diesem QR-Code bereits existiert
                    $stmt = $pdo->prepare("SELECT id FROM markers WHERE qr_code = ? AND deleted_at IS NULL");
                    $stmt->execute([$qrCode]);
                    
                    if ($stmt->fetch()) {
                        $errors[] = "Marker mit QR-Code '{$qrCode}' existiert bereits";
                        $skipped++;
                        continue;
                    }
                    
                    // 2. Prüfen ob QR-Code im Pool existiert
                    $stmt = $pdo->prepare("SELECT id, is_assigned FROM qr_code_pool WHERE qr_code = ?");
                    $stmt->execute([$qrCode]);
                    $poolCode = $stmt->fetch();
                    
                    if (!$poolCode) {
                        // QR-Code existiert nicht im Pool → Erstellen
                        $stmt = $pdo->prepare("
                            INSERT INTO qr_code_pool (qr_code, print_batch) 
                            VALUES (?, 'IMPORT')
                        ");
                        $stmt->execute([$qrCode]);
                    } elseif ($poolCode['is_assigned']) {
                        // QR-Code bereits zugewiesen
                        $errors[] = "QR-Code '{$qrCode}' ist bereits einem anderen Marker zugewiesen";
                        $skipped++;
                        continue;
                    }
                    
                    // Marker erstellen
                    $stmt = $pdo->prepare("
                        INSERT INTO markers (
                            qr_code, name, category, serial_number, is_storage, is_multi_device,
                            rental_status, operating_hours, fuel_level, maintenance_interval_months,
                            last_maintenance, next_maintenance, latitude, longitude, created_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $qrCode,
                        $markerData['name'],
                        $markerData['category'] ?? null,
                        $markerData['serial_number'] ?? null,
                        $markerData['is_storage'] ?? 0,
                        $markerData['is_multi_device'] ?? 0,
                        $markerData['rental_status'] ?? 'verfuegbar',
                        $markerData['operating_hours'] ?? 0,
                        $markerData['fuel_level'] ?? 0,
                        $markerData['maintenance_interval_months'] ?? 6,
                        $markerData['last_maintenance'] ?? null,
                        $markerData['next_maintenance'] ?? null,
                        $markerData['latitude'],
                        $markerData['longitude'],
                        $_SESSION['user_id']
                    ]);
                    
                    $markerId = $pdo->lastInsertId();
                    
                    // QR-Code im Pool als zugewiesen markieren
                    $stmt = $pdo->prepare("
                        UPDATE qr_code_pool 
                        SET is_assigned = 1, marker_id = ?, assigned_at = NOW()
                        WHERE qr_code = ?
                    ");
                    $stmt->execute([$markerId, $qrCode]);
                    
                    // Public Token generieren
                    $publicToken = bin2hex(random_bytes(32));
                    $stmt = $pdo->prepare("UPDATE markers SET public_token = ? WHERE id = ?");
                    $stmt->execute([$publicToken, $markerId]);
                    
                    // Multi-Device Seriennummern
                    if (!empty($markerData['serial_numbers'])) {
                        foreach ($markerData['serial_numbers'] as $sn) {
                            if (!empty($sn)) {
                                $stmt = $pdo->prepare("INSERT INTO marker_serial_numbers (marker_id, serial_number) VALUES (?, ?)");
                                $stmt->execute([$markerId, $sn]);
                            }
                        }
                    }
                    
                    // Custom Fields
                    if (!empty($markerData['custom_fields'])) {
                        foreach ($markerData['custom_fields'] as $label => $value) {
                            $stmt = $pdo->prepare("SELECT id FROM custom_fields WHERE field_label = ?");
                            $stmt->execute([$label]);
                            $field = $stmt->fetch();
                            
                            if ($field && !empty($value)) {
                                $stmt = $pdo->prepare("INSERT INTO marker_custom_values (marker_id, field_id, field_value) VALUES (?, ?, ?)");
                                $stmt->execute([$markerId, $field['id'], $value]);
                            }
                        }
                    }
                    
                    // Bilder
                    if (!empty($markerData['images_base64'])) {
                        foreach ($markerData['images_base64'] as $imageData) {
                            $imageContent = base64_decode($imageData['data']);
                            $filename = uniqid('img_import_', true) . '_' . $markerId . '.' . pathinfo($imageData['filename'], PATHINFO_EXTENSION);
                            $filepath = UPLOAD_DIR . $filename;
                            
                            file_put_contents($filepath, $imageContent);
                            
                            $stmt = $pdo->prepare("INSERT INTO marker_images (marker_id, image_path) VALUES (?, ?)");
                            $stmt->execute([$markerId, $filepath]);
                        }
                    }
                    
                    // Dokumente
                    if (!empty($markerData['documents_base64'])) {
                        $docDir = UPLOAD_DIR . 'documents/';
                        if (!is_dir($docDir)) {
                            mkdir($docDir, 0755, true);
                        }
                        
                        foreach ($markerData['documents_base64'] as $docData) {
                            $docContent = base64_decode($docData['data']);
                            $filename = 'doc_import_' . uniqid() . '_' . $markerId . '.pdf';
                            $filepath = $docDir . $filename;
                            
                            file_put_contents($filepath, $docContent);
                            
                            $isPublic = $docData['is_public'] ?? 0;
                            $publicDesc = $docData['public_description'] ?? null;
                            
                            $stmt = $pdo->prepare("
                                INSERT INTO marker_documents 
                                (marker_id, document_name, document_path, file_size, uploaded_by, is_public, public_description)
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $markerId,
                                $docData['filename'],
                                $filepath,
                                strlen($docContent),
                                $_SESSION['user_id'],
                                $isPublic,
                                $publicDesc
                            ]);
                        }
                    }
                    
                    $imported++;
                    $importResults[] = [
                        'name' => $markerData['name'],
                        'qr_code' => $qrCode,
                        'status' => 'success'
                    ];
                    
                } catch (Exception $e) {
                    $errors[] = "Marker '{$markerData['name']}': " . $e->getMessage();
                    $skipped++;
                    $importResults[] = [
                        'name' => $markerData['name'],
                        'qr_code' => $qrCode ?? 'N/A',
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $pdo->commit();
            
            logActivity('markers_imported', "$imported Marker importiert, $skipped übersprungen");
            
            $message = "Import abgeschlossen: $imported Marker erfolgreich importiert, $skipped übersprungen";
            $messageType = $imported > 0 ? 'success' : 'warning';
            
            if (!empty($errors)) {
                $message .= '<br><br><strong>Fehler:</strong><ul style="margin: 10px 0 0 20px;">';
                foreach (array_slice($errors, 0, 10) as $error) {
                    $message .= '<li>' . e($error) . '</li>';
                }
                if (count($errors) > 10) {
                    $message .= '<li>... und ' . (count($errors) - 10) . ' weitere Fehler</li>';
                }
                $message .= '</ul>';
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Import-Fehler: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marker importieren - Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-file-import"></i> Marker importieren</h1>
                <div class="header-actions">
                    <a href="markers.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <!-- Rest bleibt identisch -->
            
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>