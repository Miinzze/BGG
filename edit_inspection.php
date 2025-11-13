<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('maintenance_add');

$inspectionId = $_GET['id'] ?? 0;

// Prüfung laden
$stmt = $pdo->prepare("
    SELECT is.*, m.id as marker_id, m.name as marker_name
    FROM inspection_schedules is
    JOIN markers m ON is.marker_id = m.id
    WHERE is.id = ?
");
$stmt->execute([$inspectionId]);
$inspection = $stmt->fetch();

if (!$inspection) {
    die('Prüfung nicht gefunden');
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
                UPDATE inspection_schedules 
                SET inspection_type = ?,
                    inspection_interval_months = ?,
                    last_inspection = ?,
                    next_inspection = ?,
                    inspection_authority = ?,
                    certificate_number = ?,
                    responsible_person = ?,
                    notification_days_before = ?,
                    notes = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $inspectionType,
                $inspectionIntervalMonths,
                $lastInspection ?: null,
                $nextInspection,
                $inspectionAuthority ?: null,
                $certificateNumber ?: null,
                $responsiblePerson ?: null,
                $notificationDaysBefore,
                $notes ?: null,
                $inspectionId
            ]);
            
            logActivity('inspection_updated', "Prüfung '$inspectionType' für '{$inspection['marker_name']}' aktualisiert", $inspection['marker_id']);
            
            $message = 'Prüfung erfolgreich aktualisiert!';
            $messageType = 'success';
            
            // Daten neu laden
            $stmt = $pdo->prepare("
                SELECT is.*, m.id as marker_id, m.name as marker_name
                FROM inspection_schedules is
                JOIN markers m ON is.marker_id = m.id
                WHERE is.id = ?
            ");
            $stmt->execute([$inspectionId]);
            $inspection = $stmt->fetch();
            
            header("refresh:2;url=view_marker.php?id={$inspection['marker_id']}");
            
        } catch (Exception $e) {
            $message = 'Fehler: ' . htmlspecialchars($e->getMessage());
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
    <title>Prüfung bearbeiten - <?= htmlspecialchars($inspection['marker_name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Prüfung bearbeiten</h1>
                <h2><?= htmlspecialchars($inspection['marker_name']) ?></h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
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
                                <option value="TÜV" <?= $inspection['inspection_type'] === 'TÜV' ? 'selected' : '' ?>>TÜV</option>
                                <option value="UVV" <?= $inspection['inspection_type'] === 'UVV' ? 'selected' : '' ?>>UVV (Unfallverhütungsvorschrift)</option>
                                <option value="DGUV" <?= $inspection['inspection_type'] === 'DGUV' ? 'selected' : '' ?>>DGUV (Deutsche Gesetzliche Unfallversicherung)</option>
                                <option value="DGUV V3" <?= $inspection['inspection_type'] === 'DGUV V3' ? 'selected' : '' ?>>DGUV V3 (Elektrische Geräte)</option>
                                <option value="Leitern/Tritte" <?= $inspection['inspection_type'] === 'Leitern/Tritte' ? 'selected' : '' ?>>Leitern und Tritte</option>
                                <option value="Feuerlöscher" <?= $inspection['inspection_type'] === 'Feuerlöscher' ? 'selected' : '' ?>>Feuerlöscher</option>
                                <option value="Hubgerät" <?= $inspection['inspection_type'] === 'Hubgerät' ? 'selected' : '' ?>>Hubgerät</option>
                                <option value="Sicherheitsprüfung" <?= $inspection['inspection_type'] === 'Sicherheitsprüfung' ? 'selected' : '' ?>>Sicherheitsprüfung</option>
                                <option value="Sonstiges" <?= $inspection['inspection_type'] === 'Sonstiges' ? 'selected' : '' ?>>Sonstiges</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="inspection_interval_months">Prüfungsintervall (Monate) *</label>
                            <input type="number" id="inspection_interval_months" name="inspection_interval_months" 
                                   value="<?= $inspection['inspection_interval_months'] ?>" 
                                   min="1" max="120" required>
                            <small>Wie oft muss diese Prüfung durchgeführt werden?</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_inspection">Letzte Prüfung</label>
                            <input type="date" id="last_inspection" name="last_inspection" 
                                   value="<?= $inspection['last_inspection'] ?>"
                                   max="<?= date('Y-m-d') ?>">
                            <small>Wann wurde die Prüfung zuletzt durchgeführt?</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Nächste Prüfung</label>
                            <input type="text" id="next_inspection_display" disabled 
                                   value="<?= $inspection['next_inspection'] ? formatDate($inspection['next_inspection']) : 'Wird berechnet' ?>"
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
                                   value="<?= htmlspecialchars($inspection['inspection_authority'] ?? '') ?>"
                                   placeholder="z.B. TÜV Süd, DEKRA, BG BAU">
                        </div>
                        
                        <div class="form-group">
                            <label for="certificate_number">Zertifikatsnummer</label>
                            <input type="text" id="certificate_number" name="certificate_number"
                                   value="<?= htmlspecialchars($inspection['certificate_number'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($inspection['responsible_person'] ?? '') ?>"
                                   placeholder="z.B. Max Mustermann">
                            <small>Wer ist für diese Prüfung verantwortlich?</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="notification_days_before">Erinnerung (Tage vorher)</label>
                            <input type="number" id="notification_days_before" name="notification_days_before"
                                   value="<?= $inspection['notification_days_before'] ?? 14 ?>" 
                                   min="1" max="365">
                            <small>Wie viele Tage vorher soll erinnert werden?</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-sticky-note"></i> Notizen</h2>
                    
                    <div class="form-group">
                        <label for="notes">Anmerkungen</label>
                        <textarea id="notes" name="notes" rows="4"
                                  placeholder="Optionale Notizen zur Prüfung..."><?= htmlspecialchars($inspection['notes'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-save"></i> Änderungen speichern
                    </button>
                    <a href="view_marker.php?id=<?= $inspection['marker_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                    <a href="delete_inspection.php?id=<?= $inspectionId ?>&marker_id=<?= $inspection['marker_id'] ?>" 
                       class="btn btn-danger"
                       style="margin-left: auto;">
                        <i class="fas fa-trash"></i> Prüfung löschen
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