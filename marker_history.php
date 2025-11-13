<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('markers_view');

$markerId = $_GET['id'] ?? 0;

// Marker-Daten laden
$stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
$stmt->execute([$markerId]);
$marker = $stmt->fetch();

if (!$marker) {
    die('Marker nicht gefunden');
}

// Filter
$filterAction = $_GET['action'] ?? '';
$filterUser = $_GET['user'] ?? '';
$filterDate = $_GET['date'] ?? '';
$limit = $_GET['limit'] ?? 100;

// Historie laden mit Filtern
$sql = "SELECT mh.*, u.email as user_email 
        FROM marker_history mh 
        LEFT JOIN users u ON mh.user_id = u.id 
        WHERE mh.marker_id = ?";
$params = [$markerId];

if ($filterAction) {
    $sql .= " AND mh.action LIKE ?";
    $params[] = "%$filterAction%";
}

if ($filterUser) {
    $sql .= " AND mh.username LIKE ?";
    $params[] = "%$filterUser%";
}

if ($filterDate) {
    $sql .= " AND DATE(mh.created_at) = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY mh.created_at DESC LIMIT ?";
$params[] = intval($limit);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$history = $stmt->fetchAll();

// Unique Actions und Users für Filter
$actionsStmt = $pdo->prepare("SELECT DISTINCT action FROM marker_history WHERE marker_id = ? ORDER BY action");
$actionsStmt->execute([$markerId]);
$actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

$usersStmt = $pdo->prepare("SELECT DISTINCT username FROM marker_history WHERE marker_id = ? ORDER BY username");
$usersStmt->execute([$markerId]);
$users = $usersStmt->fetchAll(PDO::FETCH_COLUMN);

// Export als CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="marker_history_' . $marker['qr_code'] . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    fputcsv($output, ['Zeitstempel', 'Benutzer', 'E-Mail', 'Aktion', 'Änderungen', 'IP-Adresse', 'User Agent'], ';');
    
    foreach ($history as $entry) {
        $changes = '';
        if ($entry['change_details']) {
            $changeData = json_decode($entry['change_details'], true);
            if (is_array($changeData)) {
                $changeParts = [];
                foreach ($changeData as $field => $change) {
                    if (is_array($change) && isset($change['old'], $change['new'])) {
                        $changeParts[] = "$field: '{$change['old']}' → '{$change['new']}'";
                    }
                }
                $changes = implode('; ', $changeParts);
            } else {
                $changes = $entry['change_details'];
            }
        }
        
        fputcsv($output, [
            $entry['created_at'],
            $entry['username'],
            $entry['user_email'] ?? '',
            $entry['action'],
            $changes,
            $entry['ip_address'] ?? '',
            $entry['user_agent'] ?? ''
        ], ';');
    }
    
    fclose($output);
    exit;
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
    <title>Marker-Historie - <?= e($marker['name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .history-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .marker-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .marker-info h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .marker-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .marker-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            align-items: end;
        }
        
        .history-timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .history-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        
        .history-entry {
            position: relative;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .history-entry:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        
        .history-entry::before {
            content: '';
            position: absolute;
            left: -34px;
            top: 25px;
            width: 16px;
            height: 16px;
            background: white;
            border: 3px solid #667eea;
            border-radius: 50%;
            box-shadow: 0 0 0 4px white;
        }
        
        .history-entry.created::before {
            background: #28a745;
            border-color: #28a745;
        }
        
        .history-entry.updated::before {
            background: #007bff;
            border-color: #007bff;
        }
        
        .history-entry.deleted::before {
            background: #dc3545;
            border-color: #dc3545;
        }
        
        .history-entry.duplicated::before {
            background: #17a2b8;
            border-color: #17a2b8;
        }
        
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .entry-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .action-badge.created {
            background: #d4edda;
            color: #155724;
        }
        
        .action-badge.updated {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .action-badge.deleted {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-badge.duplicated {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .action-badge.default {
            background: #e7f3ff;
            color: #004085;
        }
        
        .entry-meta {
            display: flex;
            gap: 20px;
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 10px;
        }
        
        .entry-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .entry-timestamp {
            color: #6c757d;
            font-size: 13px;
        }
        
        .entry-changes {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .change-item {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .change-item:last-child {
            border-bottom: none;
        }
        
        .change-field {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .change-values {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .old-value {
            color: #dc3545;
            background: #f8d7da;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .new-value {
            color: #28a745;
            background: #d4edda;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .arrow-icon {
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-history"></i> Marker-Historie</h1>
                <div class="header-actions">
                    <a href="?id=<?= $markerId ?>&export=csv" class="btn btn-success">
                        <i class="fas fa-file-csv"></i> CSV Export
                    </a>
                    <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück zum Marker
                    </a>
                </div>
            </div>
            
            <!-- Marker Info -->
            <div class="marker-info">
                <h2><i class="fas fa-qrcode"></i> <?= e($marker['name']) ?></h2>
                <div class="marker-info-grid">
                    <div class="marker-info-item">
                        <i class="fas fa-barcode"></i>
                        <span><strong>QR-Code:</strong> <?= e($marker['qr_code']) ?></span>
                    </div>
                    <?php if ($marker['serial_number']): ?>
                    <div class="marker-info-item">
                        <i class="fas fa-hashtag"></i>
                        <span><strong>Seriennummer:</strong> <?= e($marker['serial_number']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="marker-info-item">
                        <i class="fas fa-tag"></i>
                        <span><strong>Kategorie:</strong> <?= e($marker['category']) ?></span>
                    </div>
                    <div class="marker-info-item">
                        <i class="fas fa-calendar"></i>
                        <span><strong>Erstellt:</strong> <?= date('d.m.Y', strtotime($marker['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Statistiken -->
            <?php
            $statsStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_entries,
                    COUNT(DISTINCT username) as unique_users,
                    COUNT(DISTINCT DATE(created_at)) as active_days,
                    MIN(created_at) as first_entry,
                    MAX(created_at) as last_entry
                FROM marker_history 
                WHERE marker_id = ?
            ");
            $statsStmt->execute([$markerId]);
            $stats = $statsStmt->fetch();
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_entries'] ?></div>
                    <div class="stat-label">Einträge gesamt</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['unique_users'] ?></div>
                    <div class="stat-label">Beteiligte Benutzer</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['active_days'] ?></div>
                    <div class="stat-label">Aktive Tage</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['last_entry'] ? floor((time() - strtotime($stats['last_entry'])) / 86400) : 0 ?></div>
                    <div class="stat-label">Tage seit letzter Änderung</div>
                </div>
            </div>
            
            <!-- Filter -->
            <div class="filter-bar">
                <form method="GET" class="filter-form">
                    <input type="hidden" name="id" value="<?= $markerId ?>">
                    
                    <div>
                        <label>Aktion</label>
                        <select name="action" class="form-control">
                            <option value="">Alle Aktionen</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?= e($action) ?>" <?= $filterAction === $action ? 'selected' : '' ?>>
                                    <?= e($action) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label>Benutzer</label>
                        <select name="user" class="form-control">
                            <option value="">Alle Benutzer</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e($user) ?>" <?= $filterUser === $user ? 'selected' : '' ?>>
                                    <?= e($user) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label>Datum</label>
                        <input type="date" name="date" value="<?= e($filterDate) ?>" class="form-control">
                    </div>
                    
                    <div>
                        <label>Anzahl</label>
                        <select name="limit" class="form-control">
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                            <option value="500" <?= $limit == 500 ? 'selected' : '' ?>>500</option>
                            <option value="1000" <?= $limit == 1000 ? 'selected' : '' ?>>1000</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtern
                    </button>
                    
                    <a href="?id=<?= $markerId ?>" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Zurücksetzen
                    </a>
                </form>
            </div>
            
            <!-- Historie Timeline -->
            <div class="history-container">
                <div class="history-header">
                    <h2>Änderungsverlauf (<?= count($history) ?> Einträge)</h2>
                </div>
                
                <?php if (empty($history)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>Keine Historie-Einträge vorhanden</h3>
                        <p>Für diesen Marker wurden noch keine Änderungen protokolliert.</p>
                    </div>
                <?php else: ?>
                    <div class="history-timeline">
                        <?php foreach ($history as $entry): ?>
                            <?php
                            $actionClass = 'default';
                            if (strpos($entry['action'], 'created') !== false) $actionClass = 'created';
                            elseif (strpos($entry['action'], 'updated') !== false) $actionClass = 'updated';
                            elseif (strpos($entry['action'], 'deleted') !== false) $actionClass = 'deleted';
                            elseif (strpos($entry['action'], 'duplicated') !== false) $actionClass = 'duplicated';
                            
                            $changeData = json_decode($entry['change_details'], true);
                            ?>
                            
                            <div class="history-entry <?= $actionClass ?>">
                                <div class="entry-header">
                                    <div class="entry-title">
                                        <span class="action-badge <?= $actionClass ?>">
                                            <?= e($entry['action']) ?>
                                        </span>
                                    </div>
                                    <span class="entry-timestamp">
                                        <i class="fas fa-clock"></i>
                                        <?= date('d.m.Y H:i:s', strtotime($entry['created_at'])) ?>
                                    </span>
                                </div>
                                
                                <div class="entry-meta">
                                    <div class="entry-meta-item">
                                        <i class="fas fa-user"></i>
                                        <strong><?= e($entry['username']) ?></strong>
                                        <?php if ($entry['user_email']): ?>
                                            (<?= e($entry['user_email']) ?>)
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($entry['ip_address']): ?>
                                    <div class="entry-meta-item">
                                        <i class="fas fa-network-wired"></i>
                                        <?= e($entry['ip_address']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (is_array($changeData) && !empty($changeData)): ?>
                                    <div class="entry-changes">
                                        <strong><i class="fas fa-edit"></i> Änderungen:</strong>
                                        <?php foreach ($changeData as $field => $change): ?>
                                            <div class="change-item">
                                                <div class="change-field"><?= e(ucfirst(str_replace('_', ' ', $field))) ?>:</div>
                                                <?php if (is_array($change) && isset($change['old'], $change['new'])): ?>
                                                    <div class="change-values">
                                                        <span class="old-value">
                                                            <?= e($change['old'] ?: '(leer)') ?>
                                                        </span>
                                                        <span class="arrow-icon">
                                                            <i class="fas fa-arrow-right"></i>
                                                        </span>
                                                        <span class="new-value">
                                                            <?= e($change['new'] ?: '(leer)') ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <div><?= e(is_array($change) ? json_encode($change) : $change) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ($entry['change_details']): ?>
                                    <div class="entry-changes">
                                        <strong><i class="fas fa-info-circle"></i> Details:</strong>
                                        <p><?= e($entry['change_details']) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($entry['user_agent']): ?>
                                    <div style="margin-top: 10px; font-size: 11px; color: #adb5bd;">
                                        <i class="fas fa-laptop"></i> <?= e(substr($entry['user_agent'], 0, 100)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>