<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'performance_cache.php'; // OPTIMIERT: Cache-System laden
requireLogin();
requirePermission('maintenance_timeline_view');

// Filter-Parameter
$filterMarker = isset($_GET['marker_id']) ? intval($_GET['marker_id']) : null;
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterDays = isset($_GET['days']) ? intval($_GET['days']) : 90;

// Wartungen laden
$sql = "SELECT 
    mh.*,
    m.name as marker_name,
    m.qr_code,
    m.category,
    u.username as performed_by_name
FROM maintenance_history mh
LEFT JOIN markers m ON mh.marker_id = m.id
LEFT JOIN users u ON mh.performed_by = u.id
WHERE mh.maintenance_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";

$params = [$filterDays];

if ($filterMarker) {
    $sql .= " AND mh.marker_id = ?";
    $params[] = $filterMarker;
}

if ($filterType !== 'all') {
    $sql .= " AND mh.maintenance_type = ?";
    $params[] = $filterType;
}

$sql .= " ORDER BY mh.maintenance_date DESC, mh.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$maintenances = $stmt->fetchAll();

// OPTIMIERT: Marker für Dropdown aus Cache laden
$markers = getCachedMarkersList();

// OPTIMIERT: Wartungstypen aus Cache laden
$maintenanceTypes = getCachedMaintenanceTypes();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wartungsübersicht - Zeitleiste</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .timeline-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #007bff 0%, #28a745 100%);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateX(5px);
        }
        
        .timeline-marker {
            position: absolute;
            left: -33px;
            top: 25px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px;
            z-index: 1;
        }
        
        .timeline-date {
            position: absolute;
            left: -250px;
            top: 20px;
            width: 200px;
            text-align: right;
            font-weight: bold;
            color: #495057;
            font-size: 14px;
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .timeline-title {
            font-size: 18px;
            font-weight: bold;
            color: #212529;
        }
        
        .timeline-type {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px;
            border-radius: 20px;
            color: white;
            font-size: 14px;
            font-weight: 500;
        }
        
        .timeline-content {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .timeline-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .detail-item i {
            color: #007bff;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            font-size: 13px;
        }
        
        .detail-value {
            color: #6c757d;
            font-size: 13px;
        }
        
        .signature-preview {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border: 2px dashed #dee2e6;
        }
        
        .signature-preview img {
            max-width: 300px;
            max-height: 150px;
            margin-top: 10px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .month-divider {
            text-align: center;
            margin: 40px 0 30px 0;
            padding: 10px;
            background: linear-gradient(90deg, transparent, #f0f0f0, transparent);
            border-radius: 20px;
            color: #495057;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .timeline-date {
                position: static;
                text-align: left;
                width: auto;
                margin-bottom: 10px;
            }
            
            .timeline {
                padding-left: 30px;
            }
            
            .timeline-marker {
                left: -23px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="timeline-container">
        <h1><i class="fas fa-history"></i> Wartungsübersicht - Zeitleiste</h1>
        
        <div class="filter-bar">
            <form method="GET">
                <div class="filter-group">
                    <div class="form-group">
                        <label>Zeitraum</label>
                        <select name="days" class="form-control">
                            <option value="30" <?= $filterDays == 30 ? 'selected' : '' ?>>Letzte 30 Tage</option>
                            <option value="60" <?= $filterDays == 60 ? 'selected' : '' ?>>Letzte 60 Tage</option>
                            <option value="90" <?= $filterDays == 90 ? 'selected' : '' ?>>Letzte 90 Tage</option>
                            <option value="180" <?= $filterDays == 180 ? 'selected' : '' ?>>Letzte 6 Monate</option>
                            <option value="365" <?= $filterDays == 365 ? 'selected' : '' ?>>Letztes Jahr</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gerät</label>
                        <select name="marker_id" class="form-control">
                            <option value="">Alle Geräte</option>
                            <?php foreach ($markers as $marker): ?>
                                <option value="<?= $marker['id'] ?>" <?= $filterMarker == $marker['id'] ? 'selected' : '' ?>>
                                    <?= e($marker['name']) ?> (<?= e($marker['qr_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Wartungstyp</label>
                        <select name="type" class="form-control">
                            <option value="all">Alle Typen</option>
                            <?php foreach ($maintenanceTypes as $key => $type): ?>
                                <option value="<?= $key ?>" <?= $filterType == $key ? 'selected' : '' ?>>
                                    <?= e($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter anwenden
                </button>
                <a href="maintenance_timeline.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Zurücksetzen
                </a>
            </form>
        </div>
        
        <?php
        // Statistiken berechnen
        $totalMaintenances = count($maintenances);
        $typeCount = [];
        $totalCost = 0;
        foreach ($maintenances as $m) {
            $typeCount[$m['maintenance_type']] = ($typeCount[$m['maintenance_type']] ?? 0) + 1;
            $totalCost += floatval($m['cost'] ?? 0);
        }
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Gesamt</div>
                <div class="stat-value"><?= $totalMaintenances ?></div>
                <small>Wartungen</small>
            </div>
            <?php
            $mostCommonType = !empty($typeCount) ? array_keys($typeCount, max($typeCount))[0] : null;
            if ($mostCommonType): ?>
            <div class="stat-card">
                <div class="stat-label">Häufigster Typ</div>
                <div class="stat-value">
                    <i class="fas <?= $maintenanceTypes[$mostCommonType]['icon'] ?>" 
                       style="color: <?= $maintenanceTypes[$mostCommonType]['color'] ?>"></i>
                </div>
                <small><?= e($maintenanceTypes[$mostCommonType]['name']) ?></small>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($maintenances)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h2>Keine Wartungen gefunden</h2>
                <p>Im ausgewählten Zeitraum wurden keine Wartungen durchgeführt.</p>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($maintenances as $maintenance): 
                    $type = $maintenanceTypes[$maintenance['maintenance_type']] ?? $maintenanceTypes['other'];
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker" style="box-shadow: 0 0 0 3px <?= $type['color'] ?>; background: <?= $type['color'] ?>"></div>
                    <div class="timeline-date">
                        <?= date('d.m.Y', strtotime($maintenance['maintenance_date'])) ?>
                        <br><small><?= date('H:i', strtotime($maintenance['created_at'])) ?> Uhr</small>
                    </div>
                    
                    <div class="timeline-header">
                        <div class="timeline-title">
                            <i class="fas fa-qrcode"></i> <?= e($maintenance['marker_name']) ?>
                            <small style="color: #999; font-weight: normal;">(<?= e($maintenance['qr_code']) ?>)</small>
                        </div>
                        <span class="timeline-type" style="background: <?= $type['color'] ?>">
                            <i class="fas <?= $type['icon'] ?>"></i>
                            <?= e($type['name']) ?>
                        </span>
                    </div>
                    
                    <?php if ($maintenance['description']): ?>
                    <div class="timeline-content">
                        <i class="fas fa-comment"></i> <?= nl2br(e($maintenance['description'])) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="timeline-details">
                        <?php if ($maintenance['performed_by_name']): ?>
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <div class="detail-label">Durchgeführt von</div>
                                <div class="detail-value"><?= e($maintenance['performed_by_name']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($maintenance['cost']): ?>
                        <div class="detail-item">
                            <i class="fas fa-euro-sign"></i>
                            <div>
                                <div class="detail-label">Kosten</div>
                                <div class="detail-value"><?= number_format($maintenance['cost'], 2, ',', '.') ?> €</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($maintenance['next_maintenance_date']): ?>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <div class="detail-label">Nächste Wartung</div>
                                <div class="detail-value"><?= date('d.m.Y', strtotime($maintenance['next_maintenance_date'])) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($maintenance['category']): ?>
                        <div class="detail-item">
                            <i class="fas fa-tag"></i>
                            <div>
                                <div class="detail-label">Kategorie</div>
                                <div class="detail-value"><?= e($maintenance['category']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($maintenance['signature_data']): ?>
                    <div class="signature-preview">
                        <i class="fas fa-signature"></i> <strong>Signatur</strong>
                        <img src="<?= e($maintenance['signature_data']) ?>" alt="Signatur">
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>