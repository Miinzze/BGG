<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('maintenance_delete');

$inspectionId = $_GET['id'] ?? 0;
$markerId = $_GET['marker_id'] ?? 0;

// Prüfung und Marker laden
$stmt = $pdo->prepare("
    SELECT isc.*, m.name as marker_name
    FROM inspection_schedules isc
    JOIN markers m ON isc.marker_id = m.id
    WHERE isc.id = ? AND isc.marker_id = ?
");
$stmt->execute([$inspectionId, $markerId]);
$inspection = $stmt->fetch();

if (!$inspection) {
    $_SESSION['error_message'] = 'Prüfung nicht gefunden';
    header("Location: index.php");
    exit;
}

// Bestätigter Löschvorgang
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    try {
        $stmt = $pdo->prepare("DELETE FROM inspection_schedules WHERE id = ? AND marker_id = ?");
        $stmt->execute([$inspectionId, $markerId]);
        
        logActivity(
            'inspection_deleted', 
            "Prüfung '{$inspection['inspection_type']}' für '{$inspection['marker_name']}' gelöscht", 
            $markerId
        );
        
        $_SESSION['success_message'] = 'Prüfung erfolgreich gelöscht';
        header("Location: view_marker.php?id=$markerId");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Fehler beim Löschen: ' . $e->getMessage();
        header("Location: view_marker.php?id=$markerId");
        exit;
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
    <title>Prüfung löschen - <?= htmlspecialchars($inspection['marker_name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .delete-confirmation {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .warning-icon {
            text-align: center;
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .inspection-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .detail-value {
            color: #212529;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-buttons .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="delete-confirmation">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h1 style="text-align: center; color: #dc3545; margin-bottom: 10px;">
                Prüfung wirklich löschen?
            </h1>
            
            <p style="text-align: center; color: #6c757d; margin-bottom: 30px;">
                Diese Aktion kann nicht rückgängig gemacht werden!
            </p>
            
            <div class="inspection-details">
                <h3 style="margin-top: 0;">
                    <i class="fas fa-clipboard-check"></i> Zu löschende Prüfung
                </h3>
                
                <div class="detail-row">
                    <span class="detail-label">Marker:</span>
                    <span class="detail-value"><?= htmlspecialchars($inspection['marker_name']) ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Prüfungsart:</span>
                    <span class="detail-value"><?= htmlspecialchars($inspection['inspection_type']) ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Intervall:</span>
                    <span class="detail-value">Alle <?= $inspection['inspection_interval_months'] ?> Monate</span>
                </div>
                
                <?php if ($inspection['last_inspection']): ?>
                <div class="detail-row">
                    <span class="detail-label">Letzte Prüfung:</span>
                    <span class="detail-value"><?= formatDate($inspection['last_inspection']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($inspection['next_inspection']): ?>
                <div class="detail-row">
                    <span class="detail-label">Nächste Prüfung:</span>
                    <span class="detail-value"><?= formatDate($inspection['next_inspection']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($inspection['inspection_authority']): ?>
                <div class="detail-row">
                    <span class="detail-label">Prüfstelle:</span>
                    <span class="detail-value"><?= htmlspecialchars($inspection['inspection_authority']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($inspection['certificate_number']): ?>
                <div class="detail-row">
                    <span class="detail-label">Zertifikat:</span>
                    <span class="detail-value"><?= htmlspecialchars($inspection['certificate_number']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="alert alert-danger">
                <strong><i class="fas fa-info-circle"></i> Wichtig:</strong><br>
                Alle Daten dieser Prüfung werden unwiderruflich gelöscht. Die Prüfhistorie geht verloren.
            </div>
            
            <div class="action-buttons">
                <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary btn-large">
                    <i class="fas fa-times"></i> Abbrechen
                </a>
                <a href="delete_inspection.php?id=<?= $inspectionId ?>&marker_id=<?= $markerId ?>&confirm=yes" 
                   class="btn btn-danger btn-large">
                    <i class="fas fa-trash"></i> Ja, endgültig löschen
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>