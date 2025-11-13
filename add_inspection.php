<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('maintenance_add');

$markerId = $_GET['marker_id'] ?? 0;
$marker = getMarkerById($markerId, $pdo);

if (!$marker) {
    die('Marker nicht gefunden');
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $inspectionType = trim($_POST['inspection_type'] ?? '');
    $lastInspection = $_POST['last_inspection'] ?? null;
    $inspectionIntervalMonths = intval($_POST['inspection_interval_months'] ?? 12);
    $inspectionAuthority = trim($_POST['inspection_authority'] ?? '');
    $certificateNumber = trim($_POST['certificate_number'] ?? '');
    $responsiblePerson = trim($_POST['responsible_person'] ?? '');
    $notificationDaysBefore = intval($_POST['notification_days_before'] ?? 14);
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($inspectionType)) {
        $message = 'Prüfungsart ist erforderlich';
        $messageType = 'danger';
    } elseif ($inspectionIntervalMonths < 1 || $inspectionIntervalMonths > 120) {
        $message = 'Ungültiges Prüfungsintervall (1-120 Monate)';
        $messageType = 'danger';
    } else {
        try {
            // Nächste Prüfung berechnen
            $nextInspection = null;
            if ($lastInspection) {
                $nextInspection = date('Y-m-d', strtotime($lastInspection . " +$inspectionIntervalMonths months"));
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO inspection_schedules 
                (marker_id, inspection_type, inspection_interval_months, last_inspection, next_inspection, 
                 inspection_authority, certificate_number, responsible_person, notification_days_before, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $markerId,
                $inspectionType,
                $inspectionIntervalMonths,
                $lastInspection ?: null,
                $nextInspection,
                $inspectionAuthority ?: null,
                $certificateNumber ?: null,
                $responsiblePerson ?: null,
                $notificationDaysBefore,
                $notes ?: null
            ]);
            
            logActivity('inspection_added', "Prüfung '$inspectionType' für '{$marker['name']}' hinzugefügt", $markerId);
            
            $message = 'Prüfung erfolgreich hinzugefügt!';
            $messageType = 'success';
            
            header("refresh:2;url=view_marker.php?id=$markerId");
            
        } catch (Exception $e) {
            $message = 'Fehler: ' . e($e->getMessage());
            $messageType = 'danger';
        }
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
    <title>Prüfung hinzufügen - <?= e($marker['name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-clipboard-check"></i> Prüfung hinzufügen</h1>
                <h2><?= e($marker['name']) ?></h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="marker-form">
                <?= csrf_field() ?>
                
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Prüfungsinformationen</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="inspection_type">Prüfungsart *</label>
                            <select id="inspection_type" name="inspection_type" required>
                                <option value="">-- Bitte wählen --</option>
                                <option value="TÜV">TÜV</option>
                                <option value="UVV">UVV (Unfallverhütungsvorschrift)</option>
                                <option value="DGUV">DGUV (Deutsche Gesetzliche Unfallversicherung)</option>
                                <option value="Sicherheitsprüfung">Sicherheitsprüfung</option>
                                <option value="Sonstiges">Sonstiges</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="inspection_interval_months">Prüfungsintervall (Monate) *</label>
                            <input type="number" id="inspection_interval_months" name="inspection_interval_months" 
                                   value="12" min="1" max="120" required>
                            <small>Wie oft muss diese Prüfung durchgeführt werden?</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_inspection">Letzte Prüfung</label>
                            <input type="date" id="last_inspection" name="last_inspection" 
                                   value="<?= date('Y-m-d') ?>"
                                   max="<?= date('Y-m-d') ?>">
                            <small>Wann wurde die Prüfung zuletzt durchgeführt?</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Nächste Prüfung</label>
                            <input type="text" id="next_inspection_display" disabled 
                                   placeholder="Wird automatisch berechnet"
                                   style="background: #e9ecef;">
                            <small>Wird automatisch basierend auf letzter Prüfung + Intervall berechnet</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-building"></i> Prüfstelle & Zertifikat</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="inspection_authority">Prüfstelle / Prüforganisation</label>
                            <input type="text" id="inspection_authority" name="inspection_authority"
                                   placeholder="z.B. TÜV Süd, DEKRA, BG BAU">
                        </div>
                        
                        <div class="form-group">
                            <label for="certificate_number">Zertifikatsnummer</label>
                            <input type="text" id="certificate_number" name="certificate_number"
                                   placeholder="z.B. TÜV-12345-2024">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-bell"></i> Benachrichtigungen</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="responsible_person">Verantwortliche Person</label>
                            <input type="text" id="responsible_person" name="responsible_person"
                                   placeholder="z.B. Max Mustermann">
                            <small>Wer ist für diese Prüfung verantwortlich?</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="notification_days_before">Erinnerung (Tage vorher)</label>
                            <input type="number" id="notification_days_before" name="notification_days_before"
                                   value="14" min="1" max="365">
                            <small>Wie viele Tage vorher soll erinnert werden?</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-sticky-note"></i> Notizen</h2>
                    
                    <div class="form-group">
                        <label for="notes">Anmerkungen</label>
                        <textarea id="notes" name="notes" rows="4"
                                  placeholder="Optionale Notizen zur Prüfung..."></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success btn-large">
                        <i class="fas fa-check"></i> Prüfung hinzufügen
                    </button>
                    <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    
    <script>
    // Automatische Berechnung der nächsten Prüfung
    function calculateNextInspection() {
        const lastInspection = document.getElementById('last_inspection').value;
        const intervalMonths = parseInt(document.getElementById('inspection_interval_months').value) || 12;
        const displayField = document.getElementById('next_inspection_display');
        
        if (lastInspection) {
            const lastDate = new Date(lastInspection);
            const nextDate = new Date(lastDate);
            nextDate.setMonth(nextDate.getMonth() + intervalMonths);
            
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            displayField.value = nextDate.toLocaleDateString('de-DE', options);
        } else {
            displayField.value = 'Bitte letzte Prüfung eingeben';
        }
    }
    
    document.getElementById('last_inspection').addEventListener('change', calculateNextInspection);
    document.getElementById('inspection_interval_months').addEventListener('change', calculateNextInspection);
    
    // Initial berechnen
    calculateNextInspection();
    </script>
</body>
</html>