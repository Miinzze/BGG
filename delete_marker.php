<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('markers_delete');

// CSRF-Token validieren
validateCSRF();

$markerId = intval($_GET['id'] ?? 0);

if ($markerId <= 0) {
    $_SESSION['message'] = 'Ungültige Marker-ID';
    $_SESSION['messageType'] = 'danger';
    header('Location: index.php');
    exit;
}

try {
    // Marker laden - WICHTIG: Auch gelöschte Marker laden!
    $stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
    $stmt->execute([$markerId]);
    $marker = $stmt->fetch();
    
    if (!$marker) {
        $_SESSION['message'] = 'Marker nicht gefunden';
        $_SESSION['messageType'] = 'danger';
        header('Location: index.php');
        exit;
    }
    
    // Prüfen ob bereits gelöscht
    if ($marker['deleted_at'] !== null) {
        $_SESSION['message'] = 'Marker wurde bereits in den Papierkorb verschoben';
        $_SESSION['messageType'] = 'info';
        header('Location: trash.php');
        exit;
    }
    
    // Bestätigung
    if (!isset($_GET['confirm'])) {
        ?>
        <!DOCTYPE html>
        <html lang="de">
        <head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Marker löschen - Bestätigung</title>
            <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>
        <body>
            <?php include 'header.php'; ?>
            
            <div class="main-container">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h1><i class="fas fa-trash"></i> Marker löschen</h1>
                    </div>
                    
                    <div class="alert alert-warning" style="padding: 30px;">
                        <h2 style="margin-top: 0;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Marker wirklich in den Papierkorb verschieben?
                        </h2>
                        
                        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <h3><?= e($marker['name']) ?></h3>
                            <p style="color: #666; margin: 10px 0;">
                                <strong>QR-Code:</strong> <code><?= e($marker['qr_code']) ?></code><br>
                                <?php if ($marker['category']): ?>
                                    <strong>Kategorie:</strong> <?= e($marker['category']) ?><br>
                                <?php endif; ?>
                                <?php if ($marker['serial_number']): ?>
                                    <strong>Seriennummer:</strong> <?= e($marker['serial_number']) ?><br>
                                <?php endif; ?>
                                <strong>Erstellt:</strong> <?= formatDateTime($marker['created_at']) ?>
                            </p>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.6;">
                            <strong>Was passiert beim Löschen?</strong>
                        </p>
                        <ul style="margin: 15px 0 20px 20px; line-height: 1.8;">
                            <li>Der Marker wird in den <strong>Papierkorb</strong> verschoben</li>
                            <li>Der QR-Code wird <strong>freigegeben</strong> und kann neu zugewiesen werden</li>
                            <li>Alle Bilder, Dokumente und Wartungsdaten bleiben erhalten</li>
                            <li>Der Marker kann aus dem Papierkorb wiederhergestellt werden</li>
                        </ul>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <a href="delete_marker.php?id=<?= $markerId ?>&confirm=1&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                               class="btn btn-danger btn-large">
                                <i class="fas fa-trash"></i> Ja, in Papierkorb verschieben
                            </a>
                            <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary btn-large">
                                <i class="fas fa-arrow-left"></i> Abbrechen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'footer.php'; ?>
        </body>
        </html>
        <?php
        exit;
    }
    
    // ===== LÖSCHEN BESTÄTIGT - SOFT DELETE DURCHFÜHREN =====
    
    $pdo->beginTransaction();
    
    try {
        // 1. Marker in Papierkorb verschieben
        $stmt = $pdo->prepare("
            UPDATE markers 
            SET deleted_at = NOW(), 
                deleted_by = ? 
            WHERE id = ? 
            AND deleted_at IS NULL
        ");
        $result = $stmt->execute([$_SESSION['user_id'], $markerId]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Marker konnte nicht gelöscht werden - möglicherweise bereits gelöscht');
        }
        
        // 2. QR-Code freigeben (Der Trigger macht das auch, aber zur Sicherheit)
        $stmt = $pdo->prepare("
            UPDATE qr_code_pool 
            SET is_assigned = 0, 
                is_activated = 0, 
                marker_id = NULL,
                assigned_at = NULL
            WHERE qr_code = ?
        ");
        $stmt->execute([$marker['qr_code']]);
        
        // ===== CACHE INVALIDIEREN =====
        global $cache;
        $cache->delete("marker:{$markerId}");
        $cache->delete("all_markers");
        $cache->deletePattern("available_qr_codes");
        // ==============================

        // Commit MUSS vor dem Logging erfolgen!
        $pdo->commit();
        
        // Logging NACH erfolgreichem Commit
        $deviceType = 'Marker';
        if (isset($marker['is_customer_device']) && $marker['is_customer_device']) {
            $deviceType = 'Kundengerät';
        } elseif ($marker['is_multi_device']) {
            $deviceType = 'Multi-Device Marker';
        } elseif ($marker['is_storage']) {
            $deviceType = 'Lagergerät';
        }
        
        logActivity('marker_deleted_soft', "$deviceType '{$marker['name']}' in Papierkorb verschoben", $markerId);
        
        $_SESSION['message'] = "Marker erfolgreich in den Papierkorb verschoben. Der QR-Code '{$marker['qr_code']}' ist jetzt wieder verfügbar.";
        $_SESSION['messageType'] = 'success';
        
        header('Location: trash.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e; // Weiterwerfen zum äußeren catch-Block
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Delete Marker Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    $_SESSION['message'] = 'Fehler beim Löschen: ' . $e->getMessage();
    $_SESSION['messageType'] = 'danger';
    
    header('Location: view_marker.php?id=' . $markerId);
    exit;
}