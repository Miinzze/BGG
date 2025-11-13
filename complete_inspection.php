<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('maintenance_add');

$inspectionId = $_GET['id'] ?? 0;

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
    
    $inspectionDate = $_POST['inspection_date'] ?? date('Y-m-d');
    $certificateNumber = trim($_POST['certificate_number'] ?? '');
    $inspectionAuthority = trim($_POST['inspection_authority'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $result = $_POST['result'] ?? 'bestanden';
    
    if (!validateDate($inspectionDate)) {
        $message = 'Ungültiges Datum';
        $messageType = 'danger';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Nächste Prüfung berechnen
            $nextInspection = date('Y-m-d', strtotime($inspectionDate . " +{$inspection['inspection_interval_months']} months"));
            
            // Inspection Schedule aktualisieren
            $stmt = $pdo->prepare("
                UPDATE inspection_schedules 
                SET last_inspection = ?,
                    next_inspection = ?,
                    certificate_number = ?,
                    inspection_authority = ?,
                    notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $inspectionDate,
                $nextInspection,
                $certificateNumber ?: $inspection['certificate_number'],
                $inspectionAuthority ?: $inspection['inspection_authority'],
                $notes,
                $inspectionId
            ]);
            
            // In Activity Log eintragen
            logActivity(
                'inspection_completed',
                "Prüfung '{$inspection['inspection_type']}' für '{$inspection['marker_name']}' durchgeführt - Ergebnis: $result",
                $inspection['marker_id']
            );
            
            $pdo->commit();
            
            $message = 'Prüfung erfolgreich eingetragen!';
            $messageType = 'success';
            
            header("refresh:2;url=view_marker.php?id={$inspection['marker_id']}");
            
        } catch (Exception $e) {
            $pdo->rollBack();
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
    <title>Prüfung durchführen - <?= e($inspection['inspection_type']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-clipboard-check"></i> Prüfung durchführen</h1>
                <h2><?= e($inspection['marker_name']) ?> - <?= e($inspection['inspection_type']) ?></h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <!-- Info zur aktuellen Prüfung -->
            <div class="alert alert-info">
                <h3><i class="fas fa-info-circle"></i> Prüfungsdetails</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                    <div>
                        <strong>Prüfungsart:</strong><br>
                        <?= e($inspection['inspection_type']) ?>
                    </div>
                    <div>
                        <strong>Intervall:</strong><br>
                        Alle <?= $inspection['inspection_interval_months'] ?> Monate
                    </div>
                    <?php if ($inspection['last_inspection']): ?>
                    <div>
                        <strong>Letzte Prüfung:</strong><br>
                        <?= formatDate($inspection['last_inspection']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($inspection['next_inspection']): ?>
                    <div>
                        <strong>Fällig am:</strong><br>
                        <?= formatDate($inspection['next_inspection']) ?>
                        <?php
                        $daysUntil = (strtotime($inspection['next_inspection']) - time()) / (60 * 60 * 24);
                        if ($daysUntil < 0): ?>
                            <span class="badge badge-danger">ÜBERFÄLLIG</span>
                        <?php elseif ($daysUntil <= 30): ?>
                            <span class="badge badge-warning">BALD FÄLLIG</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <form method="POST" class="marker-form">
                <?= csrf_field() ?>
                
                <div class="form-section">
                    <h2><i class="fas fa-calendar"></i> Prüfungsdurchführung</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="inspection_date">Prüfungsdatum *</label>
                            <input type="date" id="inspection_date" name="inspection_date" 
                                   value="<?= date('Y-m-d') ?>" 
                                   max="<?= date('Y-m-d') ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="result">Prüfungsergebnis *</label>
                            <select id="result" name="result" required>
                                <option value="bestanden">✓ Bestanden</option>
                                <option value="maengel">⚠ Bestanden mit Mängeln</option>
                                <option value="nicht_bestanden">✗ Nicht bestanden</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="inspection_authority">Prüfstelle</label>
                            <input type="text" id="inspection_authority" name="inspection_authority"
                                   value="<?= e($inspection['inspection_authority']) ?>"
                                   placeholder="z.B. TÜV Süd, DEKRA">
                        </div>
                        
                        <div class="form-group">
                            <label for="certificate_number">Zertifikatsnummer</label>
                            <input type="text" id="certificate_number" name="certificate_number"
                                   value="<?= e($inspection['certificate_number']) ?>"
                                   placeholder="z.B. TÜV-12345-2025">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-sticky-note"></i> Prüfprotokoll</h2>
                    
                    <div class="form-group">
                        <label for="notes">Feststellungen / Anmerkungen *</label>
                        <textarea id="notes" name="notes" rows="8" required
                                  placeholder="Beschreiben Sie die durchgeführte Prüfung und eventuelle Feststellungen...

Beispiel:
- Sichtprüfung durchgeführt
- Funktionsprüfung aller Sicherheitseinrichtungen
- Alle Prüfpunkte in Ordnung
- Nächste Prüfung in 12 Monaten"></textarea>
                    </div>
                </div>
                
                <!-- Vorschau nächste Prüfung -->
                <div class="alert alert-success">
                    <strong><i class="fas fa-calendar-plus"></i> Nächste Prüfung wird automatisch berechnet:</strong><br>
                    <span id="next_inspection_preview" style="font-size: 18px; font-weight: bold;">
                        <!-- Wird per JavaScript berechnet -->
                    </span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success btn-large">
                        <i class="fas fa-check"></i> Prüfung eintragen
                    </button>
                    <a href="view_marker.php?id=<?= $inspection['marker_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    
    <script>
    // Nächste Prüfung berechnen und anzeigen
    function updateNextInspection() {
        const inspectionDate = document.getElementById('inspection_date').value;
        const intervalMonths = <?= $inspection['inspection_interval_months'] ?>;
        const preview = document.getElementById('next_inspection_preview');
        
        if (inspectionDate) {
            const date = new Date(inspectionDate);
            date.setMonth(date.getMonth() + intervalMonths);
            
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            const formattedDate = date.toLocaleDateString('de-DE', options);
            
            preview.textContent = formattedDate;
        }
    }
    
    document.getElementById('inspection_date').addEventListener('change', updateNextInspection);
    
    // Initial berechnen
    updateNextInspection();
    </script>
</body>
</html>