<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// Eigene Bug-Tickets laden
$stmt = $pdo->prepare("
    SELECT * FROM bug_reports 
    WHERE email = (SELECT email FROM users WHERE id = ?)
    AND archived_at IS NULL
    ORDER BY 
        CASE status 
            WHEN 'offen' THEN 1 
            WHEN 'in_bearbeitung' THEN 2 
            WHEN 'erledigt' THEN 3 
        END,
        CASE priority
            WHEN 'kritisch' THEN 1
            WHEN 'hoch' THEN 2
            WHEN 'mittel' THEN 3
            WHEN 'niedrig' THEN 4
        END,
        created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll();

// Statistiken
$stats = [
    'total' => count($tickets),
    'offen' => count(array_filter($tickets, fn($t) => $t['status'] === 'offen')),
    'in_bearbeitung' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_bearbeitung')),
    'erledigt' => count(array_filter($tickets, fn($t) => $t['status'] === 'erledigt'))
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Bug-Tickets - Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ticket-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .ticket-card:hover {
            transform: translateX(5px);
        }
        
        .ticket-card.status-offen {
            border-left-color: #dc3545;
        }
        
        .ticket-card.status-in_bearbeitung {
            border-left-color: #ffc107;
        }
        
        .ticket-card.status-erledigt {
            border-left-color: #28a745;
            opacity: 0.7;
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .ticket-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            flex: 1;
        }
        
        .ticket-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
        
        .ticket-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #6c757d;
            margin-top: 10px;
        }
        
        .ticket-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid #007bff;
        }
        
        .stat-box h3 {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-box .number {
            font-size: 48px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .ticket-header {
                flex-direction: column;
            }
            
            .ticket-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-bug"></i> Meine Bug-Tickets</h1>
                <div class="header-actions">
                    <a href="#" onclick="openBugReport(); return false;" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Neues Ticket erstellen
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <!-- Statistiken -->
            <div class="stat-grid">
                <div class="stat-box" style="border-top-color: #007bff;">
                    <h3>Gesamt</h3>
                    <div class="number"><?= $stats['total'] ?></div>
                </div>
                
                <div class="stat-box" style="border-top-color: #dc3545;">
                    <h3>Offen</h3>
                    <div class="number"><?= $stats['offen'] ?></div>
                </div>
                
                <div class="stat-box" style="border-top-color: #ffc107;">
                    <h3>In Bearbeitung</h3>
                    <div class="number"><?= $stats['in_bearbeitung'] ?></div>
                </div>
                
                <div class="stat-box" style="border-top-color: #28a745;">
                    <h3>Erledigt</h3>
                    <div class="number"><?= $stats['erledigt'] ?></div>
                </div>
            </div>
            
            <!-- Tickets -->
            <?php if (empty($tickets)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Super!</strong> Du hast keine offenen Bug-Tickets.
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): 
                    $statusColors = [
                        'offen' => 'danger',
                        'in_bearbeitung' => 'warning',
                        'erledigt' => 'success'
                    ];
                    
                    $priorityColors = [
                        'niedrig' => 'secondary',
                        'mittel' => 'info',
                        'hoch' => 'warning',
                        'kritisch' => 'danger'
                    ];
                    
                    $statusColor = $statusColors[$ticket['status']] ?? 'secondary';
                    $priorityColor = $priorityColors[$ticket['priority']] ?? 'secondary';
                    
                    $statusLabels = [
                        'offen' => 'Offen',
                        'in_bearbeitung' => 'In Bearbeitung',
                        'erledigt' => 'Erledigt'
                    ];
                    $statusLabel = $statusLabels[$ticket['status']] ?? $ticket['status'];
                ?>
                <div class="ticket-card status-<?= $ticket['status'] ?>">
                    <div class="ticket-header">
                        <div style="flex: 1;">
                            <div class="ticket-title">
                                <?= e($ticket['title']) ?>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                <span class="badge badge-<?= $statusColor ?>">
                                    <i class="fas fa-circle"></i> <?= $statusLabel ?>
                                </span>
                                <span class="badge badge-<?= $priorityColor ?>">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <?= ucfirst($ticket['priority']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="ticket-actions">
                            <small style="color: #6c757d; display: block; margin-bottom: 8px;">
                                ID: #<?= $ticket['id'] ?>
                            </small>
                            <button onclick="deleteTicket(<?= $ticket['id'] ?>)" 
                                    class="btn btn-sm btn-danger" 
                                    title="Ticket löschen">
                                <i class="fas fa-trash"></i> Löschen
                            </button>
                        </div>
                    </div>
                    
                    <div class="ticket-description">
                        <?= nl2br(e($ticket['description'])) ?>
                    </div>
                    
                    <div class="ticket-meta">
                        <span>
                            <i class="fas fa-calendar"></i> Erstellt: 
                            <strong><?= formatDateTime($ticket['created_at']) ?></strong>
                        </span>
                        <span>
                            <i class="fas fa-clock"></i> Aktualisiert: 
                            <strong><?= formatDateTime($ticket['updated_at']) ?></strong>
                        </span>
                        <?php if ($ticket['page_url']): ?>
                        <span>
                            <i class="fas fa-link"></i> Seite: 
                            <code style="font-size: 11px;"><?= e(basename($ticket['page_url'])) ?></code>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($ticket['notes']): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                        <strong><i class="fas fa-comment"></i> Admin-Notizen:</strong>
                        <p style="margin: 10px 0 0 0; color: #6c757d;">
                            <?= nl2br(e($ticket['notes'])) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Info Box -->
            <div class="alert alert-info" style="margin-top: 30px;">
                <h3><i class="fas fa-info-circle"></i> Hinweise</h3>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li><strong>Offen:</strong> Ticket wurde erstellt und wartet auf Bearbeitung</li>
                    <li><strong>In Bearbeitung:</strong> Ein Administrator arbeitet aktiv an der Lösung</li>
                    <li><strong>Erledigt:</strong> Problem wurde behoben - Sie können das Ticket nun löschen</li>
                    <li><strong>Löschen:</strong> Sie können nur Ihre eigenen Tickets löschen. Tickets in Bearbeitung sollten nicht gelöscht werden.</li>
                    <li>Bei Fragen zu einem Ticket kontaktieren Sie bitte einen Administrator</li>
                </ul>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        function deleteTicket(ticketId) {
            if (confirm('Ticket wirklich löschen?\n\nDieser Vorgang kann nicht rückgängig gemacht werden!')) {
                window.location.href = 'delete_bug.php?id=' + ticketId + '&csrf_token=<?= $_SESSION['csrf_token'] ?>';
            }
        }

        // Bug Report Modal
        function openBugReport() {
            const modal = document.getElementById('bugReportModal');
            if (modal) {
                // Automatisch aktuelle Seite und Browser-Info erfassen
                document.getElementById('bug_page_url').value = window.location.href;
                document.getElementById('bug_browser_info').value = navigator.userAgent;
                
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeBugReport() {
            const modal = document.getElementById('bugReportModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        // Modal schließen bei Klick außerhalb
        window.onclick = function(event) {
            const modal = document.getElementById('bugReportModal');
            if (event.target == modal) {
                closeBugReport();
            }
        }

        document.getElementById('bugReportForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird gesendet...';
            
            fetch('submit_bug.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bug erfolgreich gemeldet! Vielen Dank für Ihr Feedback.');
                    closeBugReport();
                    document.getElementById('bugReportForm').reset();
                    // Email-Feld mit Benutzer-Email wiederherstellen
                    document.getElementById('bug_email').value = '<?= e($userEmail) ?>';
                } else {
                    alert('Fehler: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
</body>
</html>