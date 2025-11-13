<?php
require_once 'config.php';
require_once 'functions.php';
requirePermission('markers_view');

$id = $_GET['id'] ?? 0;
$marker = getMarkerById($id, $pdo);

if (!$marker) {
    die('Marker nicht gefunden');
}

$message = '';
$messageType = '';

// Fertig-Markierung verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_finished'])) {
    validateCSRF();
    
    if ($marker['is_customer_device'] || $marker['is_repair_device']) {
        try {
            $stmt = $pdo->prepare("
                UPDATE markers 
                SET is_finished = 1,
                    finished_at = NOW(),
                    finished_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $id]);
            
            logActivity(
                'marker_finished', 
                "Gerät '{$marker['name']}' als fertig markiert",
                $id
            );
            
            $message = 'Gerät wurde erfolgreich als fertig markiert und ist abholbereit!';
            $messageType = 'success';
            
            // Marker neu laden
            $marker = getMarkerById($id, $pdo);
            
        } catch (Exception $e) {
            $message = 'Fehler beim Markieren: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = 'Nur Kunden- oder Reparaturgeräte können als fertig markiert werden!';
        $messageType = 'warning';
    }
}

// Fertig-Markierung zurücksetzen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unmark_finished'])) {
    validateCSRF();
    
    try {
        $stmt = $pdo->prepare("
            UPDATE markers 
            SET is_finished = 0,
                finished_at = NULL,
                finished_by = NULL
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        logActivity(
            'marker_unfinished', 
            "Fertig-Status von Gerät '{$marker['name']}' zurückgesetzt",
            $id
        );
        
        $message = 'Fertig-Status wurde zurückgesetzt!';
        $messageType = 'success';
        
        // Marker neu laden
        $marker = getMarkerById($id, $pdo);
        
    } catch (Exception $e) {
        $message = 'Fehler beim Zurücksetzen: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Wartungshistorie
$stmt = $pdo->prepare("
    SELECT mh.*, u.username as performed_by_name
    FROM maintenance_history mh
    LEFT JOIN users u ON mh.performed_by = u.id
    WHERE mh.marker_id = ?
    ORDER BY mh.maintenance_date DESC
    LIMIT 10
");
$stmt->execute([$id]);
$maintenanceHistory = $stmt->fetchAll();

// DGUV/UVV/TÜV Inspektionen laden
$stmt = $pdo->prepare("
    SELECT * FROM inspection_schedules 
    WHERE marker_id = ? 
    ORDER BY next_inspection ASC
");
$stmt->execute([$id]);
$inspections = $stmt->fetchAll();

// Checklisten-Completions laden (mit Fehlerbehandlung)
$checklistCompletions = [];
$availableTemplates = [];

try {
    $stmt = $pdo->prepare("
        SELECT cc.*, ct.name as template_name, ct.category, u.username as completed_by_name
        FROM checklist_completions cc
        LEFT JOIN checklist_templates ct ON cc.template_id = ct.id
        LEFT JOIN users u ON cc.completed_by = u.id
        WHERE cc.marker_id = ?
        ORDER BY cc.completed_at DESC
    ");
    $stmt->execute([$id]);
    $checklistCompletions = $stmt->fetchAll();
    
    // Verfügbare Checklisten-Templates laden
    $availableTemplates = $pdo->query("SELECT * FROM checklist_templates ORDER BY category, name")->fetchAll();
} catch (Exception $e) {
    // Tabellen existieren nicht oder andere Fehler - ignorieren und fortfahren
    // Die Checklisten-Sektion wird dann einfach nicht angezeigt
}


// Status
$rentalStatus = getRentalStatusLabel($marker['rental_status']);
$maintenanceStatus = getMaintenanceStatus($marker['next_maintenance']);

// Seriennummern bei Multi-Device
$serialNumbers = [];
if ($marker['is_multi_device']) {
    $serialNumbers = getMarkerSerialNumbers($id, $pdo);
}

// Custom Fields
$stmt = $pdo->prepare("
    SELECT cf.field_label, cf.field_type, mcv.field_value
    FROM marker_custom_values mcv
    JOIN custom_fields cf ON mcv.field_id = cf.id
    WHERE mcv.marker_id = ?
    ORDER BY cf.display_order, cf.id
");
$stmt->execute([$id]);
$customFieldValues = $stmt->fetchAll();

// Gerätetyp bestimmen
$deviceType = 'Lagergerät';
$deviceTypeIcon = 'fa-warehouse';
$deviceTypeBadge = 'info';
if ($marker['is_customer_device']) {
    $deviceType = 'Kundengerät';
    $deviceTypeIcon = 'fa-user';
    $deviceTypeBadge = 'primary';
} elseif ($marker['is_repair_device']) {
    $deviceType = 'Reparaturgerät';
    $deviceTypeIcon = 'fa-tools';
    $deviceTypeBadge = 'warning';
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
    <title><?= e($marker['name']) ?> - Marker Details</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/ar-navigation.js"></script>
    <style>
        .inspection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .inspection-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .inspection-card:hover {
            transform: translateY(-5px);
        }
        
        .inspection-card.overdue {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        
        .inspection-card.due-soon {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        
        .inspection-card.ok {
            border-left-color: #28a745;
        }
        
        .inspection-type {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .inspection-date {
            font-size: 14px;
            color: #6c757d;
            margin: 5px 0;
        }
        
        .inspection-status {
            margin-top: 15px;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }
        
        .inspection-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        /* Checklisten Dropdown */
        .dropdown {
            position: relative;
        }
        
        .dropdown-toggle::after {
            content: '';
            display: inline-block;
            margin-left: 8px;
            vertical-align: middle;
            border-top: 5px solid white;
            border-right: 5px solid transparent;
            border-left: 5px solid transparent;
        }
        
        .dropdown-menu {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .dropdown-header {
            background: #f8f9fa;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .checklist-completion-item {
            transition: box-shadow 0.3s;
        }
        
        .checklist-completion-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        #viewMap {
            height: 400px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            margin-top: 15px;
        }
        
        .gps-coordinates {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: 600;
            color: #495057;
        }
        
        .device-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        
        .info-highlight {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .info-highlight h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 18px;
        }
        
        .fuel-display {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .fuel-bar {
            flex: 1;
            height: 25px;
            background: #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .fuel-bar-fill {
            height: 100%;
            background: linear-gradient(to right, #28a745, #20c997);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        
        .fuel-bar-fill.low {
            background: linear-gradient(to right, #ffc107, #fd7e14);
        }
        
        .fuel-bar-fill.empty {
            background: linear-gradient(to right, #dc3545, #c82333);
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }

        .badge-primary {
            background: #007bff;
            color: white;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-info-circle"></i> <?= e($marker['name']) ?></h1>
                <div class="header-actions">
                    <?php if (hasPermission('markers_edit')): ?>
                        <a href="edit_marker.php?id=<?= $marker['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Bearbeiten
                        </a>
                    <?php endif; ?>
                    <?php if (hasPermission('markers_duplicate')): ?>
                        <a href="duplicate_marker.php?id=<?= $marker['id'] ?>" class="btn btn-info">
                            <i class="fas fa-copy"></i> Marker duplizieren
                        </a>
                    <?php endif; ?>
                    <?php if (hasPermission('markers_history_view')): ?>
                        <a href="marker_history.php?id=<?= $marker['id'] ?>" class="btn btn-info">
                            <i class="fas fa-history"></i> Historie anzeigen
                        </a>
                    <?php endif; ?>

                    <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                        <a href="print_qr.php?id=<?= $marker['id'] ?>" 
                        class="btn btn-secondary" target="_blank">
                            <i class="fas fa-wifi"></i> NFC-Info drucken
                        </a>
                        <a href="print_qr.php?id=<?= $marker['id'] ?>&backup_qr=1" 
                        class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-qrcode"></i> Backup QR-Code
                        </a>
                    <?php else: ?>
                        <a href="print_qr.php?id=<?= $marker['id'] ?>" 
                        class="btn btn-secondary" target="_blank">
                            <i class="fas fa-qrcode"></i> QR-Code drucken
                        </a>
                    <?php endif; ?>
                    
                    <!-- Neue Features -->
                    <a href="maintenance_perform.php?marker_id=<?= $marker['id'] ?>" class="btn btn-success">
                        <i class="fas fa-tools"></i> Checkliste ausfüllen
                    </a>
                    <a href="marker_documents.php?marker_id=<?= $marker['id'] ?>" class="btn btn-info">
                        <i class="fas fa-folder-open"></i> Dokumente
                    </a>
                    
                    <?php if (hasPermission('markers_delete')): ?>
                        <a href="delete_marker.php?id=<?= $marker['id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Marker wirklich löschen?')">
                            <i class="fas fa-trash"></i> Löschen
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($marker['is_finished']): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <i class="fas fa-check-circle" style="font-size: 24px; margin-right: 10px;"></i>
                    <strong>Gerät ist fertig und abholbereit!</strong>
                    <br>
                    <small>
                        Fertiggestellt am: <?= date('d.m.Y H:i', strtotime($marker['finished_at'])) ?> Uhr
                        <?php 
                        if ($marker['finished_by']) {
                            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                            $stmt->execute([$marker['finished_by']]);
                            $finishedByUser = $stmt->fetch();
                            if ($finishedByUser) {
                                echo " von " . htmlspecialchars($finishedByUser['username']);
                            }
                        }
                        ?>
                    </small>
                </div>
                <?php if (hasPermission('markers_edit')): ?>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" name="unmark_finished" class="btn btn-sm btn-warning"
                            onclick="return confirm('Fertig-Status wirklich zurücksetzen?')">
                        <i class="fas fa-undo"></i> Status zurücksetzen
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Gerätetyp-Anzeige -->
            <div class="info-highlight">
                <h3><i class="fas fa-tag"></i> Gerätetyp</h3>
                <span class="device-type-badge badge-<?= $deviceTypeBadge ?>">
                    <i class="fas <?= $deviceTypeIcon ?>"></i>
                    <?= $deviceType ?>
                </span>
            </div>
            
            <!-- Grundinformationen -->
            <div class="info-card">
                <h2><i class="fas fa-info-circle"></i> Grundinformationen</h2>
                
                <?php if (($marker['is_customer_device'] || $marker['is_repair_device']) && !$marker['is_finished'] && hasPermission('markers_edit')): ?>
                <div class="marker-actions" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" name="mark_finished" class="btn btn-success btn-block"
                                onclick="return confirm('Gerät als fertig markieren? Es wird dann als abholbereit angezeigt.')">
                            <i class="fas fa-check-circle"></i> Als Fertig markieren
                        </button>
                    </form>
                    <small class="text-muted" style="display: block; margin-top: 8px; text-align: center;">
                        <i class="fas fa-info-circle"></i> Nur für Kunden- und Reparaturgeräte
                    </small>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['is_customer_device']): ?>
                    <div class="info-card" style="background: #e3f2fd; border-left: 4px solid #2196f3;">
                        <h2><i class="fas fa-user"></i> Kundengerät</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Kundenname</span>
                                <span class="value"><?= e($marker['customer_name']) ?></span>
                            </div>
                            <?php if ($marker['order_number']): ?>
                            <div class="info-item">
                                <span class="label">Auftragsnummer</span>
                                <span class="value">
                                    <code><?= e($marker['order_number']) ?></code>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if ($marker['weclapp_entity_id']): ?>
                            <div class="info-item">
                                <span class="label">
                                    <i class="fas fa-link"></i> Weclapp Auftrag
                                </span>
                                <span class="value">
                                    <?php 
                                    // Weclapp Link mit Entity-ID - verwendet WECLAPP_TENANT_URL aus config.php
                                    // Format: https://IhrTenant.weclapp.com/webapp/view/main/sales/salesOrder/id/12345
                                    $weclappTenantUrl = defined('WECLAPP_TENANT_URL') ? WECLAPP_TENANT_URL : 'https://weclapp.com';
                                    // Stelle sicher, dass die URL keinen trailing slash hat
                                    $weclappTenantUrl = rtrim($weclappTenantUrl, '/');
                                    $weclappUrl = $weclappTenantUrl . '/app/project-order/' . urlencode($marker['weclapp_entity_id']);
                                    ?>
                                    <a href="<?= htmlspecialchars($weclappUrl) ?>" target="_blank" 
                                       style="color: #007bff; text-decoration: none; font-weight: 500;"
                                       title="Auftrag in Weclapp öffnen">
                                        <i class="fas fa-external-link-alt"></i>
                                        Auftrag öffnen
                                        <small style="color: #666; margin-left: 5px;">(ID: <?= e($marker['weclapp_entity_id']) ?>)</small>
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">
                            <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                                <i class="fas fa-wifi"></i> Marker-Typ
                            <?php else: ?>
                                <i class="fas fa-qrcode"></i> Marker-Typ
                            <?php endif; ?>
                        </span>
                        <span class="value">
                            <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                                <span class="badge badge-info">
                                    <i class="fas fa-wifi"></i> NFC-Chip
                                </span>
                            <?php else: ?>
                                <span class="badge badge-primary">
                                    <i class="fas fa-qrcode"></i> QR-Code
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="label">
                            <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                                NFC-Chip-ID
                            <?php else: ?>
                                QR-Code
                            <?php endif; ?>
                        </span>
                        <span class="value">
                            <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                                <code><?= e($marker['nfc_chip_id']) ?></code>
                                <br><small class="text-muted">Backup QR-Code: <?= e($marker['qr_code']) ?></small>
                            <?php else: ?>
                                <code><?= e($marker['qr_code']) ?></code>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <?php if ($marker['category']): ?>
                    <div class="info-item">
                        <span class="label">Kategorie</span>
                        <span class="value"><?= e($marker['category']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($marker['serial_number']): ?>
                    <div class="info-item">
                        <span class="label">Seriennummer</span>
                        <span class="value"><code><?= e($marker['serial_number']) ?></code></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!$marker['is_storage'] && !$marker['is_multi_device']): ?>
                    <div class="info-item">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="badge badge-<?= $rentalStatus['class'] ?>">
                                <?= $rentalStatus['label'] ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="label">Betriebsstunden</span>
                        <span class="value">
                            <strong><?= number_format($marker['operating_hours'], 2) ?> h</strong>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Kraftstofffüllstand -->
                <?php if (!$marker['is_storage'] && !$marker['is_multi_device']): ?>
                <div style="margin-top: 20px;">
                    <h3 style="margin-bottom: 10px;"><i class="fas fa-gas-pump"></i> Kraftstofffüllstand</h3>
                    <div class="fuel-display">
                        <div class="fuel-bar">
                            <?php 
                            $fuelPercent = intval($marker['fuel_level'] ?? 0);
                            if ($marker['fuel_unit'] === 'liter' && $marker['fuel_capacity']) {
                                $fuelPercent = min(100, round(($marker['fuel_level'] / $marker['fuel_capacity']) * 100));
                            }
                            $fuelClass = $fuelPercent > 50 ? '' : ($fuelPercent > 20 ? 'low' : 'empty');
                            ?>
                            <div class="fuel-bar-fill <?= $fuelClass ?>" style="width: <?= $fuelPercent ?>%">
                                <?php if ($fuelPercent > 15): ?>
                                    <?= $fuelPercent ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="min-width: 100px; text-align: right;">
                            <?php if ($marker['fuel_unit'] === 'liter'): ?>
                                <strong><?= number_format($marker['fuel_level'], 1) ?> L</strong>
                                <?php if ($marker['fuel_capacity']): ?>
                                    <br><small>von <?= number_format($marker['fuel_capacity'], 1) ?> L</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <strong><?= $fuelPercent ?>%</strong>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- GPS-Position -->
            <?php if ($marker['latitude'] && $marker['longitude']): ?>
            <div class="info-card">
                <h2><i class="fas fa-map-marker-alt"></i> GPS-Position</h2>
                <div class="gps-coordinates" style="margin: 10px 0;">
                    <i class="fas fa-map-pin"></i> 
                    <?= number_format($marker['latitude'], 6) ?>, <?= number_format($marker['longitude'], 6) ?>
                </div>
                <div style="margin: 15px 0;">
                    <button onclick="arNav.startNavigation(<?= $marker['latitude'] ?>, <?= $marker['longitude'] ?>, '<?= addslashes($marker['name']) ?>')" 
                            class="btn btn-primary" 
                            style="background: linear-gradient(135deg, #e63312 0%, #ff6b6b 100%); 
                                   display: inline-flex; 
                                   align-items: center; 
                                   gap: 10px; 
                                   font-weight: bold;
                                   box-shadow: 0 4px 15px rgba(230, 51, 18, 0.3);">
                        <i class="fas fa-route"></i>
                        AR-Navigation starten
                    </button>
                </div>
                <div id="viewMap"></div>
                <small style="color: #6c757d; margin-top: 10px; display: block;">
                    <i class="fas fa-info-circle"></i> Die Karte zeigt den Standort des Geräts. Die Position kann in der Bearbeitung aktualisiert werden.
                </small>
            </div>
            <?php endif; ?>
            
            <!-- DGUV / UVV / TÜV Prüfungen -->
            <?php if (!$marker['is_storage'] && !empty($inspections)): ?>
            <div class="info-card">
                <h2>
                    <i class="fas fa-clipboard-check"></i> Prüfungen (DGUV / UVV / TÜV)
                    <?php if (hasPermission('maintenance_add')): ?>
                        <a href="add_inspection.php?marker_id=<?= $marker['id'] ?>" class="btn btn-sm btn-success" style="float: right;">
                            <i class="fas fa-plus"></i> Prüfung hinzufügen
                        </a>
                    <?php endif; ?>
                </h2>
                
                <div class="inspection-grid">
                    <?php foreach ($inspections as $inspection): 
                        $daysUntil = $inspection['next_inspection'] 
                            ? (strtotime($inspection['next_inspection']) - time()) / (60 * 60 * 24) 
                            : 999;
                        
                        $statusClass = 'ok';
                        $statusText = 'Aktuell';
                        $statusBadge = 'success';
                        
                        if ($daysUntil < 0) {
                            $statusClass = 'overdue';
                            $statusText = 'ÜBERFÄLLIG!';
                            $statusBadge = 'danger';
                        } elseif ($daysUntil <= 30) {
                            $statusClass = 'due-soon';
                            $statusText = 'Bald fällig';
                            $statusBadge = 'warning';
                        }
                    ?>
                    <div class="inspection-card <?= $statusClass ?>">
                        <div class="inspection-type">
                            <i class="fas fa-certificate"></i>
                            <?= e($inspection['inspection_type']) ?>
                        </div>
                        
                        <?php if ($inspection['last_inspection']): ?>
                        <div class="inspection-date">
                            <i class="fas fa-check"></i> Letzte Prüfung: 
                            <strong><?= formatDate($inspection['last_inspection']) ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($inspection['next_inspection']): ?>
                        <div class="inspection-date">
                            <i class="fas fa-calendar"></i> Nächste Prüfung: 
                            <strong><?= formatDate($inspection['next_inspection']) ?></strong>
                            <?php if ($daysUntil < 999): ?>
                                (<?= $daysUntil < 0 ? 'vor ' . abs(round($daysUntil)) : 'in ' . round($daysUntil) ?> Tagen)
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($inspection['inspection_authority']): ?>
                        <div class="inspection-date">
                            <i class="fas fa-building"></i> Prüfstelle: 
                            <?= e($inspection['inspection_authority']) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($inspection['certificate_number']): ?>
                        <div class="inspection-date">
                            <i class="fas fa-file-alt"></i> Zertifikat: 
                            <?= e($inspection['certificate_number']) ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="inspection-status badge-<?= $statusBadge ?>">
                            <?= $statusText ?>
                        </div>
                        
                        <div class="inspection-actions">
                            <?php if (hasPermission('maintenance_add')): ?>
                            <a href="complete_inspection.php?id=<?= $inspection['id'] ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> Prüfung durchführen
                            </a>
                            <a href="edit_inspection.php?id=<?= $inspection['id'] ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif (!$marker['is_storage'] && hasPermission('maintenance_add')): ?>
            <div class="info-card">
                <h2><i class="fas fa-clipboard-check"></i> Prüfungen (DGUV / UVV / TÜV)</h2>
                <div style="text-align: center; padding: 30px;">
                    <p style="color: #6c757d; margin-bottom: 15px;">
                        Noch keine Prüfungen hinterlegt
                    </p>
                    <a href="add_inspection.php?marker_id=<?= $marker['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Erste Prüfung hinzufügen
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Wartung (bestehend) -->
            <?php if (!$marker['is_storage'] && !$marker['is_multi_device']): ?>
            <div class="info-card">
                <h2><i class="fas fa-wrench"></i> Wartung</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Wartungsintervall</span>
                        <span class="value"><?= $marker['maintenance_interval_months'] ?> Monate</span>
                    </div>
                    
                    <?php if ($marker['last_maintenance']): ?>
                    <div class="info-item">
                        <span class="label">Letzte Wartung</span>
                        <span class="value"><?= formatDate($marker['last_maintenance']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($marker['next_maintenance']): ?>
                    <div class="info-item">
                        <span class="label">Nächste Wartung</span>
                        <span class="value">
                            <?= formatDate($marker['next_maintenance']) ?>
                            <span class="badge badge-<?= $maintenanceStatus['class'] ?>">
                                <?= $maintenanceStatus['label'] ?>
                            </span>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (hasPermission('maintenance_add')): ?>
                <div class="maintenance-action">
                    <a href="add_maintenance.php?id=<?= $marker['id'] ?>" class="btn btn-success">
                        <i class="fas fa-wrench"></i> Checkliste ausfüllen
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Wartungshistorie -->
            <?php if (!empty($maintenanceHistory)): ?>
            <div class="info-card">
                <h2><i class="fas fa-history"></i> Wartungshistorie</h2>
                <div class="maintenance-history">
                    <?php foreach ($maintenanceHistory as $mh): ?>
                    <div class="history-item">
                        <div class="history-date">
                            <?= formatDate($mh['maintenance_date']) ?>
                        </div>
                        <div class="history-content">
                            <p><?= nl2br(e($mh['description'])) ?></p>
                            <small>
                                Durchgeführt von: <?= e($mh['performed_by_name'] ?? 'Unbekannt') ?> 
                                am <?= formatDateTime($mh['created_at']) ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Checklisten -->
            <?php if ((hasPermission('checklists_complete') && !empty($availableTemplates)) || (hasPermission('checklists_view') && !empty($checklistCompletions))): ?>
            <div class="info-card">
                <h2>
                    <i class="fas fa-tasks"></i> Checklisten
                    <?php if (hasPermission('checklists_complete') && !empty($availableTemplates)): ?>
                    <div class="btn-group" style="float: right;">
                        <div class="dropdown" style="display: inline-block; position: relative;">
                            <button class="btn btn-sm btn-success dropdown-toggle" type="button" onclick="toggleChecklistDropdown()">
                                <i class="fas fa-plus"></i> Checkliste ausfüllen
                            </button>
                            <div id="checklistDropdown" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 5px; background: white; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); min-width: 250px; z-index: 1000;">
                                <?php 
                                $currentCategory = '';
                                foreach ($availableTemplates as $template): 
                                    if ($currentCategory !== $template['category']):
                                        if ($currentCategory !== '') echo '</div>';
                                        $currentCategory = $template['category'];
                                ?>
                                    <div class="dropdown-header" style="padding: 8px 15px; font-weight: bold; color: #666; border-bottom: 1px solid #eee;">
                                        <?= e($currentCategory ?: 'Allgemein') ?>
                                    </div>
                                    <div>
                                <?php endif; ?>
                                    <a href="complete_checklist.php?marker=<?= $marker['id'] ?>&template=<?= $template['id'] ?>" 
                                       class="dropdown-item" 
                                       style="display: block; padding: 10px 15px; color: #333; text-decoration: none; transition: background 0.2s;">
                                        <i class="fas fa-clipboard-list"></i> <?= e($template['name']) ?>
                                    </a>
                                <?php 
                                endforeach; 
                                if ($currentCategory !== '') echo '</div>';
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </h2>
                
                <?php if (!empty($checklistCompletions)): ?>
                <div class="checklist-history" style="margin-top: 20px;">
                    <?php foreach ($checklistCompletions as $completion): 
                        $items = json_decode($completion['results'], true);
                        
                        // Template-Items laden (mit Fehlerbehandlung)
                        $templateItems = [];
                        if ($completion['template_id']) {
                            try {
                                $templateStmt = $pdo->prepare("SELECT items FROM checklist_templates WHERE id = ?");
                                $templateStmt->execute([$completion['template_id']]);
                                $templateData = $templateStmt->fetchColumn();
                                if ($templateData) {
                                    $templateItems = json_decode($templateData, true) ?? [];
                                }
                            } catch (Exception $e) {
                                // Fehler beim Laden - ignorieren
                                $templateItems = [];
                            }
                        }
                        
                        $totalItems = count($templateItems);
                        $completedItems = 0;
                        
                        // Zähle erledigte Items (mit Fehlerbehandlung)
                        if (is_array($items)) {
                            foreach ($items as $item) {
                                if (is_array($item) && isset($item['checked']) && $item['checked']) {
                                    $completedItems++;
                                } elseif (is_array($item) && isset($item['value']) && !empty($item['value'])) {
                                    $completedItems++;
                                } elseif ($item == '1' || $item === true) {
                                    $completedItems++;
                                }
                            }
                        }
                        
                        $completionPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                    ?>
                    <div class="checklist-completion-item" style="background: white; padding: 20px; border-radius: 8px; border: 2px solid #dee2e6; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <div>
                                <h3 style="margin: 0 0 5px 0; font-size: 18px;">
                                    <i class="fas fa-clipboard-check"></i> <?= e($completion['template_name']) ?>
                                </h3>
                                <?php if ($completion['category']): ?>
                                <span class="badge badge-secondary"><?= e($completion['category']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <div class="completion-percent" style="font-size: 24px; font-weight: bold; color: <?= $completionPercent >= 80 ? '#28a745' : ($completionPercent >= 50 ? '#ffc107' : '#dc3545') ?>;">
                                    <?= $completionPercent ?>%
                                </div>
                                <small style="color: #6c757d;"><?= $completedItems ?> / <?= $totalItems ?> Punkte</small>
                            </div>
                        </div>
                        
                        <div style="color: #6c757d; font-size: 14px; margin-bottom: 10px;">
                            <i class="fas fa-user"></i> <?= e($completion['completed_by_name'] ?? 'Unbekannt') ?>
                            <i class="fas fa-clock" style="margin-left: 15px;"></i> <?= formatDateTime($completion['completed_at']) ?>
                        </div>
                        
                        <?php if ($completion['notes']): ?>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 5px; margin-top: 10px;">
                            <strong><i class="fas fa-comment"></i> Notizen:</strong><br>
                            <?= nl2br(e($completion['notes'])) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($completion['pdf_path'] && file_exists($completion['pdf_path'])): ?>
                        <div style="margin-top: 10px;">
                            <a href="<?= e($completion['pdf_path']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-pdf"></i> PDF anzeigen
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 30px; color: #6c757d;">
                    <i class="fas fa-clipboard-list" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Noch keine Checklisten ausgefüllt</p>
                    <?php if (hasPermission('checklists_complete') && !empty($availableTemplates)): ?>
                    <p style="margin-top: 10px;">
                        <button class="btn btn-primary" onclick="toggleChecklistDropdown()">
                            <i class="fas fa-plus"></i> Erste Checkliste ausfüllen
                        </button>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            
        </div>
    </div>
    
    <script>
    <?php if ($marker['latitude'] && $marker['longitude']): ?>
    // Karte für Ansicht
    document.addEventListener('DOMContentLoaded', function() {
        var lat = <?= $marker['latitude'] ?>;
        var lng = <?= $marker['longitude'] ?>;
        
        var viewMap = L.map('viewMap').setView([lat, lng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(viewMap);
        
        L.marker([lat, lng]).addTo(viewMap)
            .bindPopup('<b><?= e($marker['name']) ?></b><br>Standort des Geräts')
            .openPopup();
    });
    <?php endif; ?>
    
    // Checklisten-Dropdown Toggle
    function toggleChecklistDropdown() {
        const dropdown = document.getElementById('checklistDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }
    
    // Dropdown schließen beim Klick außerhalb
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('checklistDropdown');
        const button = event.target.closest('.dropdown-toggle');
        
        if (!button && dropdown && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    });
    
    // Dropdown-Item Hover-Effekt
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.background = '#f8f9fa';
            });
            item.addEventListener('mouseleave', function() {
                this.style.background = 'white';
            });
        });
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>