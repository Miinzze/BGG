<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('activity_log_view');

// Filter
$filterUser = $_GET['user'] ?? '';
$filterAction = $_GET['action'] ?? '';
$filterDate = $_GET['date'] ?? '';
$filterMarker = $_GET['marker'] ?? '';
$filterIP = $_GET['ip'] ?? '';
$timeRange = $_GET['range'] ?? 'all';
$limit = $_GET['limit'] ?? 100;

// Zeitraum berechnen
$dateCondition = "1=1";
$dateParams = [];

if ($timeRange !== 'all') {
    switch ($timeRange) {
        case 'today':
            $dateCondition = "DATE(al.created_at) = CURDATE()";
            break;
        case 'yesterday':
            $dateCondition = "DATE(al.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $dateCondition = "al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $dateCondition = "al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

// Query aufbauen
$sql = "SELECT al.*, m.name as marker_name, m.qr_code
        FROM activity_log al 
        LEFT JOIN markers m ON al.marker_id = m.id 
        WHERE $dateCondition";
$params = $dateParams;

if ($filterUser) {
    $sql .= " AND al.username LIKE ?";
    $params[] = "%$filterUser%";
}

if ($filterAction) {
    $sql .= " AND al.action LIKE ?";
    $params[] = "%$filterAction%";
}

if ($filterDate) {
    $sql .= " AND DATE(al.created_at) = ?";
    $params[] = $filterDate;
}

if ($filterMarker) {
    $sql .= " AND (m.name LIKE ? OR m.qr_code LIKE ?)";
    $params[] = "%$filterMarker%";
    $params[] = "%$filterMarker%";
}

if ($filterIP) {
    $sql .= " AND al.ip_address LIKE ?";
    $params[] = "%$filterIP%";
}

$sql .= " ORDER BY al.created_at DESC LIMIT ?";
$params[] = intval($limit);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Statistiken berechnen
$statsSQL = "SELECT 
    COUNT(*) as total,
    COUNT(DISTINCT al.user_id) as unique_users,
    COUNT(DISTINCT al.marker_id) as unique_markers,
    COUNT(DISTINCT DATE(al.created_at)) as active_days,
    COUNT(DISTINCT al.ip_address) as unique_ips
    FROM activity_log al
    WHERE $dateCondition";

if ($filterUser) {
    $statsSQL .= " AND al.username LIKE ?";
}
if ($filterAction) {
    $statsSQL .= " AND al.action LIKE ?";
}

$statsStmt = $pdo->prepare($statsSQL);
$statsStmt->execute($dateParams);
$stats = $statsStmt->fetch();

// Top Aktionen
$topActionsSQL = "SELECT 
    al.action,
    COUNT(*) as count
    FROM activity_log al
    WHERE $dateCondition
    GROUP BY al.action
    ORDER BY count DESC
    LIMIT 5";
$topActionsStmt = $pdo->prepare($topActionsSQL);
$topActionsStmt->execute($dateParams);
$topActions = $topActionsStmt->fetchAll();

// Top Benutzer
$topUsersSQL = "SELECT 
    al.username,
    COUNT(*) as activity_count
    FROM activity_log al
    WHERE $dateCondition
    GROUP BY al.username
    ORDER BY activity_count DESC
    LIMIT 5";
$topUsersStmt = $pdo->prepare($topUsersSQL);
$topUsersStmt->execute($dateParams);
$topUsers = $topUsersStmt->fetchAll();

// Unique Actions und Users für Filter
$actionsStmt = $pdo->query("SELECT DISTINCT action FROM activity_log ORDER BY action");
$actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

$usersStmt = $pdo->query("SELECT DISTINCT username FROM activity_log ORDER BY username");
$users = $usersStmt->fetchAll(PDO::FETCH_COLUMN);

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="activity_log_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Zeitstempel', 'Benutzer', 'Aktion', 'Details', 'Marker', 'QR-Code', 'IP-Adresse'], ';');
    
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['created_at'],
            $log['username'],
            $log['action'],
            $log['details'],
            $log['marker_name'] ?? '',
            $log['qr_code'] ?? '',
            $log['ip_address'] ?? ''
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
    <title>Aktivitätsprotokoll</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .quick-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .quick-filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #e9ecef;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #495057;
        }
        
        .quick-filter-btn:hover {
            background: #f8f9fa;
        }
        
        .quick-filter-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .top-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .top-list h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .top-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            margin-bottom: 5px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .top-item:hover {
            background: #e9ecef;
        }
        
        .log-entry {
            padding: 15px;
            background: white;
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .log-entry.marker-action {
            border-left-color: #28a745;
        }
        
        .log-entry.user-action {
            border-left-color: #17a2b8;
        }
        
        .log-entry.system-action {
            border-left-color: #ffc107;
        }
        
        .log-entry.error-action {
            border-left-color: #dc3545;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .log-user {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .log-time {
            color: #6c757d;
            font-size: 13px;
        }
        
        .log-action {
            display: inline-block;
            padding: 3px 8px;
            background: #e7f3ff;
            color: #007bff;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }
        
        .log-details {
            color: #495057;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .log-meta {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .log-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-history"></i> Aktivitätsprotokoll</h1>
                <div class="header-actions">
                    <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-success">
                        <i class="fas fa-file-csv"></i> CSV Export
                    </a>
                    <a href="settings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <!-- Statistiken -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-list" style="color: #007bff;"></i>
                    <div class="stat-value"><?= number_format($stats['total']) ?></div>
                    <div class="stat-label">Aktivitäten gesamt</div>
                </div>
                
                <div class="stat-card" style="border-left-color: #28a745;">
                    <i class="fas fa-users" style="color: #28a745;"></i>
                    <div class="stat-value"><?= $stats['unique_users'] ?></div>
                    <div class="stat-label">Aktive Benutzer</div>
                </div>
                
                <div class="stat-card" style="border-left-color: #17a2b8;">
                    <i class="fas fa-qrcode" style="color: #17a2b8;"></i>
                    <div class="stat-value"><?= $stats['unique_markers'] ?></div>
                    <div class="stat-label">Betroffene Marker</div>
                </div>
                
                <div class="stat-card" style="border-left-color: #ffc107;">
                    <i class="fas fa-calendar-day" style="color: #ffc107;"></i>
                    <div class="stat-value"><?= $stats['active_days'] ?></div>
                    <div class="stat-label">Aktive Tage</div>
                </div>
                
                <div class="stat-card" style="border-left-color: #6f42c1;">
                    <i class="fas fa-network-wired" style="color: #6f42c1;"></i>
                    <div class="stat-value"><?= $stats['unique_ips'] ?></div>
                    <div class="stat-label">Verschiedene IPs</div>
                </div>
            </div>
            
            <!-- Top Listen -->
            <div class="charts-container">
                <div class="top-list">
                    <h3><i class="fas fa-chart-bar"></i> Top 5 Aktionen</h3>
                    <?php foreach ($topActions as $action): ?>
                        <div class="top-item">
                            <span><?= e($action['action']) ?></span>
                            <strong style="color: #007bff;"><?= number_format($action['count']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="top-list">
                    <h3><i class="fas fa-users"></i> Top 5 Benutzer</h3>
                    <?php foreach ($topUsers as $user): ?>
                        <div class="top-item">
                            <span><?= e($user['username']) ?></span>
                            <strong style="color: #28a745;"><?= number_format($user['activity_count']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Schnellfilter -->
            <div class="quick-filters">
                <a href="?range=today" class="quick-filter-btn <?= $timeRange === 'today' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-day"></i> Heute
                </a>
                <a href="?range=yesterday" class="quick-filter-btn <?= $timeRange === 'yesterday' ? 'active' : '' ?>">
                    <i class="fas fa-calendar"></i> Gestern
                </a>
                <a href="?range=week" class="quick-filter-btn <?= $timeRange === 'week' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-week"></i> 7 Tage
                </a>
                <a href="?range=month" class="quick-filter-btn <?= $timeRange === 'month' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> 30 Tage
                </a>
                <a href="?range=all" class="quick-filter-btn <?= $timeRange === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-infinity"></i> Alle
                </a>
            </div>
            
            <!-- Detaillierte Filter -->
            <div class="filter-bar" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                    <input type="hidden" name="range" value="<?= e($timeRange) ?>">
                    
                    <div>
                        <label style="font-size: 12px; color: #6c757d;">Benutzer</label>
                        <select name="user" style="width: 100%;">
                            <option value="">Alle Benutzer</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e($user) ?>" <?= $filterUser === $user ? 'selected' : '' ?>>
                                    <?= e($user) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label style="font-size: 12px; color: #6c757d;">Aktion</label>
                        <select name="action" style="width: 100%;">
                            <option value="">Alle Aktionen</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?= e($action) ?>" <?= $filterAction === $action ? 'selected' : '' ?>>
                                    <?= e($action) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label style="font-size: 12px; color: #6c757d;">Marker</label>
                        <input type="text" 
                               name="marker" 
                               placeholder="Name oder QR-Code..." 
                               value="<?= e($filterMarker) ?>" 
                               style="width: 100%;">
                    </div>
                    
                    <div>
                        <label style="font-size: 12px; color: #6c757d;">Datum</label>
                        <input type="date" 
                               name="date" 
                               value="<?= e($filterDate) ?>" 
                               style="width: 100%;">
                    </div>
                    
                    <div>
                        <label style="font-size: 12px; color: #6c757d;">IP-Adresse</label>
                        <input type="text" 
                               name="ip" 
                               placeholder="z.B. 192.168..." 
                               value="<?= e($filterIP) ?>" 
                               style="width: 100%;">
                    </div>
                    
                    <div>
                        <label style="font-size: 12px; color: #6c757d;">Anzahl</label>
                        <select name="limit" style="width: 100%;">
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 Einträge</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 Einträge</option>
                            <option value="500" <?= $limit == 500 ? 'selected' : '' ?>>500 Einträge</option>
                            <option value="1000" <?= $limit == 1000 ? 'selected' : '' ?>>1000 Einträge</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 5px; align-items: end;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-filter"></i> Filtern
                        </button>
                        <a href="activity_log.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Log Einträge -->
            <div class="admin-section">
                <h2>Aktivitäten (<?= count($logs) ?> Einträge)</h2>
                
                <?php if (empty($logs)): ?>
                    <p style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                        Keine Aktivitäten gefunden
                    </p>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        // Farbcodierung basierend auf Aktion
                        $entryClass = 'log-entry';
                        if (strpos($log['action'], 'marker') !== false) {
                            $entryClass .= ' marker-action';
                        } elseif (strpos($log['action'], 'user') !== false || strpos($log['action'], 'login') !== false) {
                            $entryClass .= ' user-action';
                        } elseif (strpos($log['action'], 'system') !== false) {
                            $entryClass .= ' system-action';
                        } elseif (strpos($log['action'], 'error') !== false || strpos($log['action'], 'failed') !== false) {
                            $entryClass .= ' error-action';
                        }
                    ?>
                        <div class="<?= $entryClass ?>">
                            <div class="log-header">
                                <div>
                                    <span class="log-user">
                                        <i class="fas fa-user"></i> <?= e($log['username']) ?>
                                    </span>
                                    <span class="log-action"><?= e($log['action']) ?></span>
                                </div>
                                <span class="log-time">
                                    <i class="fas fa-clock"></i> <?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?>
                                </span>
                            </div>
                            
                            <?php if ($log['details']): ?>
                                <div class="log-details">
                                    <?php
                                    $details = $log['details'];
                                    // Versuche JSON zu dekodieren für bessere Darstellung
                                    $jsonData = json_decode($details, true);
                                    if (is_array($jsonData)) {
                                        // Zeige JSON formatiert
                                        foreach ($jsonData as $key => $value) {
                                            if (is_array($value)) {
                                                echo "<strong>" . e($key) . ":</strong> " . e(json_encode($value)) . "<br>";
                                            } else {
                                                echo "<strong>" . e($key) . ":</strong> " . e($value) . "<br>";
                                            }
                                        }
                                    } else {
                                        echo e($details);
                                    }
                                    ?>
                                    
                                    <?php if ($log['marker_name']): ?>
                                        <div style="margin-top: 8px;">
                                            <a href="view_marker.php?id=<?= $log['marker_id'] ?>" style="color: #007bff;">
                                                <i class="fas fa-qrcode"></i> <?= e($log['marker_name']) ?> (<?= e($log['qr_code']) ?>)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="log-meta">
                                <?php if ($log['ip_address']): ?>
                                    <div class="log-meta-item">
                                        <i class="fas fa-network-wired"></i>
                                        <span><?= e($log['ip_address']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($log['user_agent']): ?>
                                    <div class="log-meta-item" title="<?= e($log['user_agent']) ?>">
                                        <i class="fas fa-laptop"></i>
                                        <span><?= e(substr($log['user_agent'], 0, 50)) ?>...</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>