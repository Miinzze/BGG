<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$message = '';
$messageType = '';

$showForceWarning = false;
$warningNFCChip = '';
$warningMessage = '';

// NFC-Chip löschen
if (isset($_GET['delete']) && isset($_GET['confirm'])) {
    validateCSRF();
    
    $nfcChipId = $_GET['delete'];
    $force = isset($_GET['force']) && $_GET['force'] == '1';
    
    try {
        // NFC-Chip Info abrufen
        $stmt = $pdo->prepare("SELECT * FROM nfc_chip_pool WHERE nfc_chip_id = ?");
        $stmt->execute([$nfcChipId]);
        $nfcInfo = $stmt->fetch();
        
        if (!$nfcInfo) {
            throw new Exception('NFC-Chip nicht gefunden');
        }
        
        // Wenn zugewiesen und kein Force
        if ($nfcInfo['is_assigned'] && !$force) {
            // Marker-Namen holen
            $markerName = 'Unbekannt';
            if ($nfcInfo['assigned_to_marker_id']) {
                $stmt = $pdo->prepare("SELECT name FROM markers WHERE id = ?");
                $stmt->execute([$nfcInfo['assigned_to_marker_id']]);
                $markerName = $stmt->fetchColumn() ?: 'Unbekannt';
            }
            
            // Warnung anzeigen
            $showForceWarning = true;
            $warningNFCChip = $nfcChipId;
            $warningMessage = "Dieser NFC-Chip ist dem Marker \"$markerName\" zugewiesen. Wenn Sie den NFC-Chip löschen, wird auch der Marker gelöscht!";
            
        } else {
            // Löschen (mit oder ohne Force)
            $pdo->beginTransaction();
            
            if ($force && $nfcInfo['is_assigned'] && $nfcInfo['assigned_to_marker_id']) {
                // Marker zuerst in Papierkorb verschieben
                $stmt = $pdo->prepare("
                    UPDATE markers 
                    SET deleted_at = NOW(), deleted_by = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $nfcInfo['assigned_to_marker_id']]);
                
                logActivity('marker_deleted_by_nfc', "Marker automatisch gelöscht durch NFC-Chip Löschung", $nfcInfo['assigned_to_marker_id']);
            }
            
            // NFC-Chip löschen
            $stmt = $pdo->prepare("DELETE FROM nfc_chip_pool WHERE nfc_chip_id = ?");
            $stmt->execute([$nfcChipId]);
            
            $pdo->commit();
            
            logActivity('nfc_chip_deleted', "NFC-Chip '$nfcChipId' gelöscht" . ($force ? ' (inkl. Marker)' : ''));
            
            $message = "NFC-Chip erfolgreich gelöscht!" . ($force ? " Der zugewiesene Marker wurde in den Papierkorb verschoben." : "");
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = 'Fehler: ' . e($e->getMessage());
        $messageType = 'danger';
    }
}

$filter = $_GET['filter'] ?? 'all';
$batch = $_GET['batch'] ?? '';

// Basis-Query
$sql = "SELECT ncp.*, m.name as marker_name, m.id as marker_id
        FROM nfc_chip_pool ncp
        LEFT JOIN markers m ON ncp.assigned_to_marker_id = m.id AND m.deleted_at IS NULL
        WHERE 1=1";

$params = [];

// Filter anwenden
if ($filter === 'available') {
    $sql .= " AND ncp.is_assigned = 0";
} elseif ($filter === 'assigned') {
    $sql .= " AND ncp.is_assigned = 1";
}

// Batch-Filter
if (!empty($batch)) {
    $sql .= " AND ncp.batch_name = ?";
    $params[] = $batch;
}

$sql .= " ORDER BY ncp.nfc_chip_id ASC";

// Paginierung
$page = $_GET['page'] ?? 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Gesamtanzahl ermitteln
$countStmt = $pdo->prepare(str_replace('ncp.*, m.name as marker_name, m.id as marker_id', 'COUNT(*)', $sql));
$countStmt->execute($params);
$totalChips = $countStmt->fetchColumn();
$totalPages = ceil($totalChips / $perPage);

// Daten abrufen
$sql .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$chips = $stmt->fetchAll();

// Statistiken für alle Chips
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_assigned = 0 THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN is_assigned = 1 THEN 1 ELSE 0 END) as assigned
    FROM nfc_chip_pool
")->fetch();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFC-Chip Verwaltung - Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="css/dark-mode.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px var(--shadow);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .stat-card .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .stat-card.available {
            border-left-color: #28a745;
        }
        
        .stat-card.available .stat-value {
            color: #28a745;
        }
        
        .stat-card.assigned {
            border-left-color: #ffc107;
        }
        
        .stat-card.assigned .stat-value {
            color: #ffc107;
        }
        
        .nfc-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .nfc-status.available {
            background: #d4edda;
            color: #155724;
        }
        
        .nfc-status.assigned {
            background: #fff3cd;
            color: #856404;
        }
        
        .section {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px var(--shadow);
            margin-bottom: 25px;
        }
        
        .section h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--text-color);
        }
        
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .warning-box h3 {
            color: #856404;
            margin-top: 0;
        }
        
        .warning-box p {
            color: #856404;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-microchip"></i> NFC-Chip Verwaltung</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="nfc_chip_generator.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> NFC-Chips hinzufügen
                    </a>
                    <a href="settings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <?php if ($showForceWarning): ?>
            <div class="warning-box">
                <h3><i class="fas fa-exclamation-triangle"></i> Warnung: NFC-Chip ist zugewiesen</h3>
                <p><?= e($warningMessage) ?></p>
                <div style="display: flex; gap: 10px;">
                    <a href="?delete=<?= urlencode($warningNFCChip) ?>&confirm=1&force=1&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('ACHTUNG: Möchten Sie den NFC-Chip UND den zugewiesenen Marker wirklich löschen?')">
                        <i class="fas fa-trash"></i> Trotzdem löschen (inkl. Marker)
                    </a>
                    <a href="nfc_chip_list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Statistiken -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-microchip"></i> Gesamt</h3>
                    <div class="stat-value"><?= $stats['total'] ?></div>
                    <p style="color: var(--text-secondary); margin: 0;">NFC-Chips insgesamt</p>
                </div>
                
                <div class="stat-card available">
                    <h3><i class="fas fa-circle-check"></i> Verfügbar</h3>
                    <div class="stat-value"><?= $stats['available'] ?></div>
                    <p style="color: var(--text-secondary); margin: 0;">Nicht zugewiesen</p>
                </div>
                
                <div class="stat-card assigned">
                    <h3><i class="fas fa-link"></i> Zugewiesen</h3>
                    <div class="stat-value"><?= $stats['assigned'] ?></div>
                    <p style="color: var(--text-secondary); margin: 0;">Aktiven Markern zugewiesen</p>
                </div>
            </div>
            
            <!-- Filter-Buttons -->
            <div style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
                <a href="nfc_chip_list.php" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="fas fa-list"></i> Alle (<?= $stats['total'] ?>)
                </a>
                <a href="nfc_chip_list.php?filter=available" class="btn <?= $filter === 'available' ? 'btn-success' : 'btn-secondary' ?>">
                    <i class="fas fa-circle"></i> Verfügbar (<?= $stats['available'] ?>)
                </a>
                <a href="nfc_chip_list.php?filter=assigned" class="btn <?= $filter === 'assigned' ? 'btn-warning' : 'btn-secondary' ?>">
                    <i class="fas fa-link"></i> Zugewiesen (<?= $stats['assigned'] ?>)
                </a>
            </div>
            
            <?php if (!empty($batch)): ?>
                <div class="alert alert-info">
                    <strong><i class="fas fa-filter"></i> Batch-Filter aktiv:</strong> <?= e($batch) ?>
                    <a href="nfc_chip_list.php" style="margin-left: 10px;" class="btn btn-sm btn-secondary">
                        <i class="fas fa-times"></i> Filter entfernen
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Tabelle -->
            <div class="section">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Icon</th>
                                <th>NFC-Chip-ID</th>
                                <th>Status</th>
                                <th>Marker</th>
                                <th>Batch</th>
                                <th>Erstellt</th>
                                <th>Zugewiesen am</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($chips)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i><br>
                                    Keine NFC-Chips gefunden
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($chips as $chip): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <i class="fas fa-microchip" style="font-size: 32px; color: #667eea;"></i>
                                </td>
                                <td>
                                    <code style="font-size: 14px; font-weight: 600; font-family: 'Courier New', monospace;">
                                        <?= e($chip['nfc_chip_id']) ?>
                                    </code>
                                </td>
                                <td>
                                    <?php if ($chip['is_assigned']): ?>
                                        <span class="nfc-status assigned">
                                            <i class="fas fa-link"></i> Zugewiesen
                                        </span>
                                    <?php else: ?>
                                        <span class="nfc-status available">
                                            <i class="fas fa-circle"></i> Verfügbar
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($chip['marker_id']): ?>
                                        <a href="view_marker.php?id=<?= $chip['marker_id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> <?= e($chip['marker_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($chip['batch_name']): ?>
                                        <a href="nfc_chip_list.php?batch=<?= urlencode($chip['batch_name']) ?>" 
                                           style="color: var(--text-color); text-decoration: underline;">
                                            <?= e($chip['batch_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= formatDateTime($chip['created_at']) ?></small>
                                </td>
                                <td>
                                    <?php if ($chip['assigned_at']): ?>
                                        <small><?= formatDateTime($chip['assigned_at']) ?></small>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <?php if ($chip['marker_id']): ?>
                                            <a href="view_marker.php?id=<?= $chip['marker_id'] ?>" 
                                               class="btn btn-sm btn-info" title="Marker anzeigen">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <button onclick="deleteNFCChip('<?= e($chip['nfc_chip_id']) ?>')" 
                                                    class="btn btn-sm btn-danger" title="NFC-Chip löschen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Paginierung -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top: 20px; text-align: center; display: flex; justify-content: center; align-items: center; gap: 15px;">
                <?php if ($page > 1): ?>
                    <a href="?filter=<?= $filter ?>&batch=<?= urlencode($batch) ?>&page=<?= $page - 1 ?>" class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Zurück
                    </a>
                <?php endif; ?>
                
                <span style="color: var(--text-secondary); font-weight: 600;">
                    Seite <?= $page ?> von <?= $totalPages ?>
                </span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?filter=<?= $filter ?>&batch=<?= urlencode($batch) ?>&page=<?= $page + 1 ?>" class="btn btn-secondary">
                        Weiter <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Info Box -->
            <div class="alert alert-info" style="margin-top: 30px;">
                <h3><i class="fas fa-info-circle"></i> Hinweise zur NFC-Chip Verwaltung</h3>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li><strong>Verfügbar:</strong> NFC-Chip wurde zum Pool hinzugefügt, aber noch keinem Marker zugewiesen</li>
                    <li><strong>Zugewiesen:</strong> NFC-Chip wurde einem aktiven Marker zugewiesen</li>
                    <li><strong>Löschen:</strong> Nur nicht zugewiesene NFC-Chips können gelöscht werden</li>
                    <li><strong>Batch:</strong> Chips können in Batches organisiert werden für bessere Übersicht</li>
                </ul>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        function deleteNFCChip(chipId) {
            if (confirm('NFC-Chip "' + chipId + '" wirklich löschen?\n\nDieser Vorgang kann nicht rückgängig gemacht werden!')) {
                window.location.href = 'nfc_chip_list.php?delete=' + encodeURIComponent(chipId) + 
                                      '&confirm=1&csrf_token=<?= $_SESSION['csrf_token'] ?>';
            }
        }
    </script>
    
    <script src="js/dark-mode.js"></script>
</body>
</html>