<?php
/**
 * PERFORMANCE MONITORING DASHBOARD
 * 
 * Zeigt Cache-Statistiken, Query-Performance und System-Metriken
 * 
 * Zugriff: performance_monitor.php
 * 
 * WICHTIG: Nur für Admins zugänglich machen!
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Nur für Admins
requireLogin();
requireAdmin();

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_cache'])) {
        clearAllCache();
        $_SESSION['success_message'] = 'Cache erfolgreich geleert!';
        header('Location: performance_monitor.php');
        exit;
    }
    
    if (isset($_POST['cleanup_expired'])) {
        $cleaned = cleanupExpiredCache();
        $_SESSION['success_message'] = "$cleaned abgelaufene Cache-Einträge gelöscht!";
        header('Location: performance_monitor.php');
        exit;
    }
    
    if (isset($_POST['analyze_tables'])) {
        try {
            $tables = ['markers', 'activity_log', 'maintenance_history', 'users', 'marker_serial_numbers'];
            foreach ($tables as $table) {
                // Use query() and fetchAll() instead of exec() to avoid unbuffered query issues
                $stmt = $pdo->query("ANALYZE TABLE `$table`");
                $stmt->fetchAll(); // Fetch results to clear buffer
            }
            $_SESSION['success_message'] = 'Tabellen-Statistiken aktualisiert!';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Fehler beim Analysieren: ' . $e->getMessage();
        }
        header('Location: performance_monitor.php');
        exit;
    }
}

// Cache-Statistiken
$cacheStats = getCacheStats();

// Datenbank-Statistiken
$dbStats = [];

try {
    // Datenbanknamen aus DSN extrahieren
    $dbName = DB_NAME; // Aus config.php

    // Tabellen-Größen
    $stmt = $pdo->prepare("
        SELECT
            table_name AS name,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
            table_rows AS row_count,
            ROUND((index_length / 1024 / 1024), 2) AS index_size_mb
        FROM information_schema.TABLES
        WHERE table_schema = ?
        AND table_name IN ('markers', 'activity_log', 'maintenance_history', 'users', 'marker_serial_numbers')
        ORDER BY (data_length + index_length) DESC
    ");
    $stmt->execute([$dbName]);
    $tables = $stmt->fetchAll();

    if (!empty($tables)) {
        $dbStats['tables'] = $tables;
    }

    // Index-Nutzung (nur für wichtige Tabellen)
    $stmt = $pdo->prepare("
        SELECT
            TABLE_NAME as table_name,
            INDEX_NAME as index_name,
            GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = ?
        AND TABLE_NAME IN ('markers', 'activity_log', 'maintenance_history')
        GROUP BY TABLE_NAME, INDEX_NAME
        ORDER BY TABLE_NAME, INDEX_NAME
    ");
    $stmt->execute([$dbName]);
    $indexes = $stmt->fetchAll();

    if (!empty($indexes)) {
        $dbStats['indexes'] = $indexes;
    }

    // Query-Performance Statistiken
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_queries,
               AVG(LENGTH(action)) as avg_query_complexity
        FROM activity_log
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $dbStats['recent_activity'] = $stmt->fetch();

} catch (PDOException $e) {
    $dbStats['error'] = $e->getMessage();
    error_log("Performance Monitor DB Error: " . $e->getMessage());
}

// System-Performance
$systemStats = [
    'php_version' => PHP_VERSION,
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled'] ?? false,
];

// Teste Query-Performance
$queryBenchmarks = [];

// Test 1: markers.php Query
$start = microtime(true);
$stmt = $pdo->query("SELECT m.* FROM markers m WHERE m.deleted_at IS NULL ORDER BY m.created_at DESC LIMIT 20");
$stmt->fetchAll();
$queryBenchmarks['markers_list'] = round((microtime(true) - $start) * 1000, 2);

// Test 2: activity_log Query
$start = microtime(true);
$stmt = $pdo->query("
    SELECT al.*, m.name as marker_name 
    FROM activity_log al 
    LEFT JOIN markers m ON al.marker_id = m.id 
    ORDER BY al.created_at DESC 
    LIMIT 100
");
$stmt->fetchAll();
$queryBenchmarks['activity_log'] = round((microtime(true) - $start) * 1000, 2);

// Test 3: maintenance_timeline Query
$start = microtime(true);
$stmt = $pdo->query("
    SELECT mh.*, m.name as marker_name, u.username as performed_by_name
    FROM maintenance_history mh
    LEFT JOIN markers m ON mh.marker_id = m.id
    LEFT JOIN users u ON mh.performed_by = u.id
    WHERE mh.maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
    ORDER BY mh.maintenance_date DESC
    LIMIT 50
");
$stmt->fetchAll();
$queryBenchmarks['maintenance_timeline'] = round((microtime(true) - $start) * 1000, 2);

// Test 4: Cache Performance
$start = microtime(true);
$categories = getCachedCategories();
$queryBenchmarks['cached_categories'] = round((microtime(true) - $start) * 1000, 2);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Monitor</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .metric-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .metric-card .icon {
            font-size: 24px;
            color: #007bff;
        }
        
        .metric-value {
            font-size: 36px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }
        
        .metric-label {
            color: #666;
            font-size: 14px;
        }
        
        .status-good { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-bad { color: #dc3545; }
        
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #007bff);
            transition: width 0.3s;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .benchmark-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .benchmark-item:last-child {
            border-bottom: none;
        }
        
        .benchmark-time {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard">
        <h1><i class="fas fa-tachometer-alt"></i> Performance Monitor</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <form method="post" style="display: inline;">
                <button type="submit" name="clear_cache" class="btn btn-warning">
                    <i class="fas fa-trash"></i> Cache leeren
                </button>
            </form>
            
            <form method="post" style="display: inline;">
                <button type="submit" name="cleanup_expired" class="btn btn-primary">
                    <i class="fas fa-broom"></i> Abgelaufene Caches löschen
                </button>
            </form>
            
            <form method="post" style="display: inline;">
                <button type="submit" name="analyze_tables" class="btn btn-success">
                    <i class="fas fa-sync"></i> Tabellen analysieren
                </button>
            </form>
        </div>
        
        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <!-- Cache Stats -->
            <div class="metric-card">
                <h3><i class="fas fa-database icon"></i> Cache Statistiken</h3>
                <div class="metric-value"><?= $cacheStats['total_files'] ?></div>
                <div class="metric-label">Cache Dateien</div>
                <div style="margin-top: 15px;">
                    <div>✓ Gültige Caches: <strong><?= $cacheStats['valid_caches'] ?></strong></div>
                    <div>✗ Abgelaufene: <strong><?= $cacheStats['expired_caches'] ?></strong></div>
                    <div>💾 Größe: <strong><?= $cacheStats['total_size_formatted'] ?></strong></div>
                </div>
                <?php 
                $hitRate = $cacheStats['total_files'] > 0 
                    ? round(($cacheStats['valid_caches'] / $cacheStats['total_files']) * 100) 
                    : 0;
                ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $hitRate ?>%"></div>
                </div>
                <small>Cache Hit Rate: <?= $hitRate ?>%</small>
            </div>
            
            <!-- Query Performance -->
            <div class="metric-card">
                <h3><i class="fas fa-bolt icon"></i> Query Performance</h3>
                <?php 
                $avgTime = array_sum($queryBenchmarks) / count($queryBenchmarks);
                $statusClass = $avgTime < 50 ? 'status-good' : ($avgTime < 150 ? 'status-warning' : 'status-bad');
                ?>
                <div class="metric-value <?= $statusClass ?>"><?= round($avgTime, 1) ?>ms</div>
                <div class="metric-label">Durchschnittliche Query-Zeit</div>
                
                <div style="margin-top: 15px;">
                    <?php foreach ($queryBenchmarks as $name => $time): ?>
                        <div class="benchmark-item">
                            <span><?= ucfirst(str_replace('_', ' ', $name)) ?></span>
                            <span class="benchmark-time <?= $time < 50 ? 'status-good' : ($time < 150 ? 'status-warning' : 'status-bad') ?>">
                                <?= $time ?>ms
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="metric-card">
                <h3><i class="fas fa-server icon"></i> System Info</h3>
                <div style="font-size: 14px;">
                    <div style="margin: 8px 0;">
                        <strong>PHP Version:</strong> <?= $systemStats['php_version'] ?>
                    </div>
                    <div style="margin: 8px 0;">
                        <strong>Memory Limit:</strong> <?= $systemStats['memory_limit'] ?>
                    </div>
                    <div style="margin: 8px 0;">
                        <strong>Max Execution:</strong> <?= $systemStats['max_execution_time'] ?>s
                    </div>
                    <div style="margin: 8px 0;">
                        <strong>OpCache:</strong> 
                        <?php if ($systemStats['opcache_enabled']): ?>
                            <span class="status-good">✓ Aktiviert</span>
                        <?php else: ?>
                            <span class="status-warning">✗ Nicht aktiviert</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Database Tables -->
        <div class="table-container">
            <h3><i class="fas fa-table"></i> Datenbank-Tabellen</h3>
            <?php if (isset($dbStats['tables'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tabelle</th>
                            <th>Größe</th>
                            <th>Index-Größe</th>
                            <th>Zeilen</th>
                            <th>Index-Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dbStats['tables'] as $table): ?>
                            <?php 
                            $indexRatio = $table['size_mb'] > 0 
                                ? round(($table['index_size_mb'] / $table['size_mb']) * 100) 
                                : 0;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($table['name']) ?></strong></td>
                                <td><?= $table['size_mb'] ?> MB</td>
                                <td><?= $table['index_size_mb'] ?> MB</td>
                                <td><?= number_format($table['row_count']) ?></td>
                                <td>
                                    <?= $indexRatio ?>%
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= min($indexRatio, 100) ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (isset($dbStats['error'])): ?>
                <div class="alert alert-danger">
                    <strong>Fehler beim Laden der Datenbank-Statistiken:</strong><br>
                    <?= htmlspecialchars($dbStats['error']) ?>
                </div>
            <?php else: ?>
                <p>Keine Daten verfügbar</p>
            <?php endif; ?>
        </div>

        <!-- Database Indexes -->
        <div class="table-container">
            <h3><i class="fas fa-list"></i> Datenbank-Indizes</h3>
            <?php if (isset($dbStats['indexes'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tabelle</th>
                            <th>Index Name</th>
                            <th>Spalten</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dbStats['indexes'] as $index): ?>
                            <tr>
                                <td><?= htmlspecialchars($index['table_name']) ?></td>
                                <td><code><?= htmlspecialchars($index['index_name']) ?></code></td>
                                <td><?= htmlspecialchars($index['columns']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (isset($dbStats['error'])): ?>
                <div class="alert alert-danger">
                    <strong>Fehler beim Laden der Index-Statistiken:</strong><br>
                    <?= htmlspecialchars($dbStats['error']) ?>
                </div>
            <?php else: ?>
                <p>Keine Daten verfügbar</p>
            <?php endif; ?>
        </div>

        <!-- Performance Recommendations -->
        <div class="table-container">
            <h3><i class="fas fa-lightbulb"></i> Empfehlungen</h3>
            <ul style="line-height: 1.8;">
                <?php if ($avgTime > 150): ?>
                    <li class="status-bad">⚠️ Query-Performance ist langsam. Prüfe Indizes und optimiere Queries.</li>
                <?php elseif ($avgTime > 50): ?>
                    <li class="status-warning">⚡ Query-Performance ist OK, aber kann verbessert werden.</li>
                <?php else: ?>
                    <li class="status-good">✓ Query-Performance ist ausgezeichnet!</li>
                <?php endif; ?>
                
                <?php if (!$systemStats['opcache_enabled']): ?>
                    <li class="status-warning">⚠️ OpCache ist nicht aktiviert. Aktiviere es für bessere Performance.</li>
                <?php else: ?>
                    <li class="status-good">✓ OpCache ist aktiviert.</li>
                <?php endif; ?>
                
                <?php if ($cacheStats['expired_caches'] > 10): ?>
                    <li class="status-warning">⚠️ Viele abgelaufene Caches. Führe Cleanup aus.</li>
                <?php endif; ?>
                
                <?php if ($hitRate < 50): ?>
                    <li class="status-warning">⚠️ Cache Hit Rate ist niedrig. System könnte von mehr Caching profitieren.</li>
                <?php elseif ($hitRate >= 80): ?>
                    <li class="status-good">✓ Exzellente Cache Hit Rate!</li>
                <?php endif; ?>

                <?php if (isset($dbStats['tables']) && is_array($dbStats['tables'])): ?>
                    <?php foreach ($dbStats['tables'] as $table): ?>
                        <?php if ($table['row_count'] > 100000 && $table['index_size_mb'] < ($table['size_mb'] * 0.1)): ?>
                            <li class="status-warning">
                                ⚠️ Tabelle "<?= $table['name'] ?>" hat viele Zeilen aber wenig Indizes.
                                Prüfe ob zusätzliche Indizes sinnvoll sind.
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>