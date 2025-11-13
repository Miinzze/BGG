<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('settings_manage');

$messeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Messe laden
$stmt = $pdo->prepare("SELECT * FROM messe_config WHERE id = ?");
$stmt->execute([$messeId]);
$messe = $stmt->fetch();

if (!$messe) {
    die("Messe nicht gefunden.");
}

// Statistiken laden
$stmt = $pdo->prepare("
    SELECT 
        m.name as marker_name,
        m.qr_code,
        m.category,
        COUNT(DISTINCT s.ip_address) as unique_visitors,
        SUM(s.scan_count) as total_scans,
        MAX(s.last_scan) as last_scan
    FROM messe_scan_stats s
    JOIN markers m ON s.marker_id = m.id
    WHERE s.messe_id = ?
    GROUP BY s.marker_id
    ORDER BY total_scans DESC
");
$stmt->execute([$messeId]);
$deviceStats = $stmt->fetchAll();

// Lead-Statistiken
$stmt = $pdo->prepare("
    SELECT 
        l.*,
        m.name as marker_name
    FROM messe_leads l
    LEFT JOIN markers m ON l.marker_id = m.id
    WHERE l.messe_id = ?
    ORDER BY l.created_at DESC
");
$stmt->execute([$messeId]);
$leads = $stmt->fetchAll();

// Gesamt-Statistiken
$totalScans = array_sum(array_column($deviceStats, 'total_scans'));
$totalVisitors = array_sum(array_column($deviceStats, 'unique_visitors'));
$totalLeads = count($leads);
$conversionRate = $totalVisitors > 0 ? round(($totalLeads / $totalVisitors) * 100, 2) : 0;

// Scans pro Tag
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(scan_count) as scans
    FROM messe_scan_stats
    WHERE messe_id = ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$messeId]);
$scansByDay = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messe-Statistiken - <?= htmlspecialchars($messe['name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card .icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .stat-card .number {
            font-size: 42px;
            font-weight: bold;
            display: block;
            margin: 10px 0;
        }
        .stat-card .label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin: 30px 0;
        }
        .device-stats-table {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin: 30px 0;
        }
        .device-stats-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .device-stats-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .device-stats-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .device-stats-table tr:hover {
            background: #f8f9fa;
        }
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 0.3s;
        }
        .leads-list {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin: 30px 0;
        }
        .lead-item {
            padding: 20px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            margin: 15px 0;
            border-radius: 8px;
        }
        .lead-item .lead-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .lead-item .email {
            font-weight: bold;
            color: #667eea;
            font-size: 18px;
        }
        .lead-item .timestamp {
            color: #888;
            font-size: 14px;
        }
        .lead-item .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .btn-export {
            padding: 12px 24px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-export:hover {
            background: #218838;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container" style="max-width: 1400px; margin: 20px auto; padding: 0 20px;">
        <div class="stats-header">
            <h1><i class="fas fa-chart-bar"></i> <?= htmlspecialchars($messe['name']) ?></h1>
            <p style="font-size: 18px; margin-top: 10px;">Messe-Statistiken & Analytics</p>
            <?php if ($messe['start_date']): ?>
                <p><i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($messe['start_date'])) ?>
                <?php if ($messe['end_date']): ?>- <?= date('d.m.Y', strtotime($messe['end_date'])) ?><?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: right; margin-bottom: 20px;">
            <button onclick="exportToCSV()" class="btn-export">
                <i class="fas fa-download"></i> Leads exportieren (CSV)
            </button>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card" style="border-top: 4px solid #667eea;">
                <div class="icon" style="color: #667eea;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <span class="number" style="color: #667eea;"><?= $totalScans ?></span>
                <span class="label">Gesamt-Scans</span>
            </div>
            
            <div class="stat-card" style="border-top: 4px solid #f093fb;">
                <div class="icon" style="color: #f093fb;">
                    <i class="fas fa-users"></i>
                </div>
                <span class="number" style="color: #f093fb;"><?= $totalVisitors ?></span>
                <span class="label">Eindeutige Besucher</span>
            </div>
            
            <div class="stat-card" style="border-top: 4px solid #4facfe;">
                <div class="icon" style="color: #4facfe;">
                    <i class="fas fa-envelope"></i>
                </div>
                <span class="number" style="color: #4facfe;"><?= $totalLeads ?></span>
                <span class="label">Leads generiert</span>
            </div>
            
            <div class="stat-card" style="border-top: 4px solid #43e97b;">
                <div class="icon" style="color: #43e97b;">
                    <i class="fas fa-percentage"></i>
                </div>
                <span class="number" style="color: #43e97b;"><?= $conversionRate ?>%</span>
                <span class="label">Conversion Rate</span>
            </div>
        </div>
        
        <?php if (!empty($scansByDay)): ?>
        <div class="chart-container">
            <h2><i class="fas fa-chart-line"></i> Scans pro Tag</h2>
            <canvas id="scansChart" height="80"></canvas>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($deviceStats)): ?>
        <div class="device-stats-table">
            <h2><i class="fas fa-trophy"></i> Geräte-Performance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Gerät</th>
                        <th>Kategorie</th>
                        <th>QR-Code</th>
                        <th>Scans</th>
                        <th>Besucher</th>
                        <th>Letzter Scan</th>
                        <th>Popularität</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $maxScans = max(array_column($deviceStats, 'total_scans'));
                    foreach ($deviceStats as $index => $device): 
                        $percentage = $maxScans > 0 ? ($device['total_scans'] / $maxScans) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <strong style="font-size: 20px; color: #667eea;">#<?= $index + 1 ?></strong>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($device['marker_name']) ?></strong>
                        </td>
                        <td>
                            <span style="background: #e9ecef; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                                <?= htmlspecialchars($device['category'] ?: 'Keine') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($device['qr_code']) ?></td>
                        <td>
                            <strong style="font-size: 18px; color: #667eea;"><?= $device['total_scans'] ?></strong>
                        </td>
                        <td><?= $device['unique_visitors'] ?></td>
                        <td>
                            <?php if ($device['last_scan']): ?>
                                <?= date('d.m.Y H:i', strtotime($device['last_scan'])) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($leads)): ?>
        <div class="leads-list">
            <h2><i class="fas fa-address-book"></i> Generierte Leads (<?= count($leads) ?>)</h2>
            <?php foreach ($leads as $lead): ?>
                <div class="lead-item">
                    <div class="lead-header">
                        <div class="email">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($lead['email']) ?>
                        </div>
                        <div class="timestamp">
                            <?= date('d.m.Y H:i', strtotime($lead['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="details">
                        <?php if ($lead['name']): ?>
                            <div><strong>Name:</strong> <?= htmlspecialchars($lead['name']) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($lead['company']): ?>
                            <div><strong>Firma:</strong> <?= htmlspecialchars($lead['company']) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($lead['phone']): ?>
                            <div><strong>Telefon:</strong> <?= htmlspecialchars($lead['phone']) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($lead['interested_in']): ?>
                            <div><strong>Interesse:</strong> <?= htmlspecialchars($lead['interested_in']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($lead['message']): ?>
                        <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 6px;">
                            <strong>Nachricht:</strong><br>
                            <?= nl2br(htmlspecialchars($lead['message'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Chart.js - Scans pro Tag
        <?php if (!empty($scansByDay)): ?>
        const ctx = document.getElementById('scansChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php foreach ($scansByDay as $day): ?>
                        '<?= date('d.m', strtotime($day['date'])) ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Scans',
                    data: [
                        <?php foreach ($scansByDay as $day): ?>
                            <?= $day['scans'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // CSV Export
        function exportToCSV() {
            const leads = <?= json_encode($leads) ?>;
            
            let csv = 'Email,Name,Firma,Telefon,Interesse,Nachricht,Datum\n';
            
            leads.forEach(lead => {
                csv += `"${lead.email}","${lead.name || ''}","${lead.company || ''}","${lead.phone || ''}","${lead.interested_in || ''}","${(lead.message || '').replace(/"/g, '""')}","${lead.created_at}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'messe_leads_<?= $messe['name'] ?>_<?= date('Y-m-d') ?>.csv';
            link.click();
        }
    </script>
</body>
</html>