<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

// UTF-8 Header setzen für korrekte Umlaut-Darstellung
header('Content-Type: text/html; charset=UTF-8');

$maintenanceId = $_GET['id'] ?? null;
$markerId = $_GET['marker_id'] ?? null;

// Fall 1: Wenn marker_id übergeben wurde, zeige Checklistenauswahl
if (!$maintenanceId && $markerId) {
    // Marker-Informationen laden
    $stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$markerId]);
    $marker = $stmt->fetch();
    
    if (!$marker) {
        die('Gerät nicht gefunden');
    }
    
    // ALLE verfügbaren Checklisten laden mit zusätzlichen Infos
    $stmt = $pdo->query("
        SELECT mc.*,
               COUNT(mci.id) as item_count,
               ROUND(COUNT(mci.id) * 2 / 60, 0) as estimated_minutes,
               (SELECT COUNT(*) FROM maintenance_history mh 
                WHERE mh.checklist_id = mc.id 
                AND mh.performed_by = {$_SESSION['user_id']} 
                AND mh.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_usage
        FROM maintenance_checklists mc
        LEFT JOIN maintenance_checklist_items mci ON mc.id = mci.checklist_id
        GROUP BY mc.id
        ORDER BY mc.is_template DESC, recent_usage DESC, mc.name
    ");
    $checklists = $stmt->fetchAll();
    
    // Gruppierung nach Kategorie
    $groupedChecklists = [];
    foreach ($checklists as $checklist) {
        $category = $checklist['category'] ?: 'Allgemein';
        $groupedChecklists[$category][] = $checklist;
    }
    
    // Wenn POST: Checkliste wurde ausgewählt, erstelle maintenance_history Eintrag
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checklist_id'])) {
        validateCSRF();
        
        $checklistId = $_POST['checklist_id'];
        
        // Neuen Wartungseintrag erstellen mit Draft-Status
        $stmt = $pdo->prepare("
            INSERT INTO maintenance_history 
            (marker_id, maintenance_date, description, performed_by, status, checklist_id)
            VALUES (?, CURDATE(), 'Wartung in Bearbeitung', ?, 'draft', ?)
        ");
        $stmt->execute([$markerId, $_SESSION['user_id'], $checklistId]);
        $newMaintenanceId = $pdo->lastInsertId();
        
        logActivity('maintenance_started', "Wartung für Marker '" . $marker['name'] . "' gestartet", $markerId);
        
        // Weiterleitung zur Wartungsdurchführung
        header('Location: maintenance_perform.php?id=' . $newMaintenanceId);
        exit;
    }
    
    // Checklistenauswahl anzeigen
    include 'header.php';
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Checkliste auswählen - <?= htmlspecialchars($marker['name']) ?></title>
        <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .setting-group {
                background: white;
                padding: 25px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .device-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px 25px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            /* Suchfeld */
            .search-box {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .search-box input {
                width: 100%;
                padding: 12px 15px 12px 45px;
                border: 2px solid #dee2e6;
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s;
            }
            .search-box input:focus {
                outline: none;
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .search-wrapper {
                position: relative;
            }
            .search-wrapper i {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #6c757d;
                font-size: 18px;
            }
            
            /* Kategorien */
            .category-section {
                margin-bottom: 30px;
            }
            .category-title {
                font-size: 18px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 15px;
                padding-bottom: 8px;
                border-bottom: 2px solid #e9ecef;
            }
            
            .checklist-card {
                background: white;
                border: 2px solid #dee2e6;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 15px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: start;
                gap: 20px;
            }
            .checklist-card:hover {
                border-color: var(--primary-color);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }
            .checklist-card input[type="radio"] {
                margin-top: 5px;
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
            .checklist-info {
                flex: 1;
            }
            .checklist-title {
                font-size: 18px;
                font-weight: 600;
                color: #2c3e50;
                margin: 0 0 8px 0;
            }
            .checklist-meta {
                font-size: 13px;
                color: #6c757d;
                margin-bottom: 8px;
                display: flex;
                gap: 15px;
                flex-wrap: wrap;
            }
            .checklist-meta span {
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            .badge-dguv {
                background: #28a745;
                color: white;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
            }
            .badge-template {
                background: #17a2b8;
                color: white;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
            }
            .badge-category {
                background: #6c757d;
                color: white;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
            }
            .badge-recent {
                background: #ffc107;
                color: #000;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
            }
            .no-results {
                text-align: center;
                padding: 60px;
                background: #f8f9fa;
                border-radius: 8px;
                color: #6c757d;
            }
        </style>
    </head>
    <body>
    
    <div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
        <div class="device-header">
            <h2><i class="fas fa-clipboard-check"></i> Wartungscheckliste auswählen</h2>
            <p><i class="fas fa-box"></i> Gerät: <?= htmlspecialchars($marker['name']) ?></p>
        </div>
        
        <?php if (empty($checklists)): ?>
            <div class="setting-group">
                <div style="text-align: center; padding: 60px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-clipboard" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3 style="color: #6c757d; margin-bottom: 10px;">Keine Checklisten verfügbar</h3>
                    <p style="color: #999; margin-bottom: 25px;">
                        Es sind noch keine Wartungschecklisten definiert.
                    </p>
                    <a href="maintenance_checklists.php" class="btn btn-primary btn-large">
                        <i class="fas fa-plus"></i> Checkliste erstellen
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Suchfeld -->
            <div class="search-box">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="checklistSearch" placeholder="Checkliste suchen..." onkeyup="filterChecklists()">
                </div>
            </div>
            
            <form method="POST">
                <?= csrf_field() ?>
                
                <div id="checklistContainer">
                    <?php foreach ($groupedChecklists as $category => $categoryChecklists): ?>
                        <div class="category-section" data-category="<?= htmlspecialchars($category) ?>">
                            <div class="category-title">
                                <i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?>
                            </div>
                            
                            <?php foreach ($categoryChecklists as $checklist): ?>
                                <label class="checklist-card" data-name="<?= htmlspecialchars(strtolower($checklist['name'])) ?>">
                                    <input type="radio" name="checklist_id" value="<?= $checklist['id'] ?>" required>
                                    
                                    <div class="checklist-info">
                                        <div class="checklist-title">
                                            <?= htmlspecialchars($checklist['name']) ?>
                                            <?php if ($checklist['recent_usage'] > 0): ?>
                                                <span class="badge-recent">
                                                    <i class="fas fa-star"></i> Zuletzt verwendet
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="checklist-meta">
                                            <span>
                                                <i class="fas fa-list"></i> 
                                                <?= $checklist['item_count'] ?> Prüfpunkte
                                            </span>
                                            <span>
                                                <i class="fas fa-clock"></i> 
                                                ~<?= max(5, $checklist['estimated_minutes']) ?> Min
                                            </span>
                                            <?php if (isset($checklist['is_dguv_compliant']) && $checklist['is_dguv_compliant']): ?>
                                                <span class="badge-dguv">DGUV</span>
                                            <?php endif; ?>
                                            <?php if ($checklist['is_template']): ?>
                                                <span class="badge-template">Vorlage</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($checklist['description']): ?>
                                            <p style="margin: 10px 0 0 0; font-size: 14px; color: #6c757d;">
                                                <?= htmlspecialchars($checklist['description']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="no-results" id="noResults" style="display: none;">
                    <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p style="margin: 0;">Keine Checklisten gefunden</p>
                </div>
                
                <div class="form-actions" style="margin-top: 30px;">
                    <button type="submit" class="btn btn-success btn-large">
                        <i class="fas fa-arrow-right"></i> Weiter zur Wartung
                    </button>
                    <a href="view_marker.php?id=<?= $markerId ?>" class="btn btn-secondary btn-large">
                        <i class="fas fa-times"></i> Abbrechen
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
    function filterChecklists() {
        const searchTerm = document.getElementById('checklistSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.checklist-card');
        const categories = document.querySelectorAll('.category-section');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Kategorien ausblenden, wenn keine Checklisten sichtbar
        categories.forEach(category => {
            const visibleCards = category.querySelectorAll('.checklist-card[style="display: flex;"]');
            category.style.display = visibleCards.length > 0 ? 'block' : 'none';
        });
        
        // "Keine Ergebnisse" anzeigen
        document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
        document.getElementById('checklistContainer').style.display = visibleCount === 0 ? 'none' : 'block';
    }
    
    // Auto-select bei Click auf Card
    document.querySelectorAll('.checklist-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'radio') {
                this.querySelector('input[type="radio"]').checked = true;
            }
        });
    });
    </script>
    
    <?php include 'footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// Fall 2: Wartung durchführen (maintenanceId vorhanden)
if (!$maintenanceId) {
    die('Keine Wartungs-ID angegeben');
}

// Wartungsinformationen laden
$stmt = $pdo->prepare("
    SELECT mh.*, m.name as marker_name, m.id as marker_id,
           mc.name as checklist_name, mc.id as checklist_id,
           u.username as performer_name
    FROM maintenance_history mh
    LEFT JOIN markers m ON mh.marker_id = m.id
    LEFT JOIN maintenance_checklists mc ON mh.checklist_id = mc.id
    LEFT JOIN users u ON mh.performed_by = u.id
    WHERE mh.id = ?
");
$stmt->execute([$maintenanceId]);
$maintenance = $stmt->fetch();

if (!$maintenance) {
    die('Wartung nicht gefunden');
}

// Nur bei Draft oder In Progress bearbeitbar
if (!in_array($maintenance['status'], ['draft', 'in_progress'])) {
    header('Location: view_marker.php?id=' . $maintenance['marker_id']);
    exit;
}

// Checklist Items laden
$stmt = $pdo->prepare("
    SELECT * FROM maintenance_checklist_items 
    WHERE checklist_id = ? 
    ORDER BY item_order, id
");
$stmt->execute([$maintenance['checklist_id']]);
$items = $stmt->fetchAll();

// Bereits gespeicherte Antworten laden (bei Draft)
$savedAnswers = [];
if ($maintenance['checklist_data']) {
    $savedAnswers = json_decode($maintenance['checklist_data'], true) ?? [];
}

$success = '';
$error = '';

// POST-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $action = $_POST['action'] ?? '';
    
    // Draft speichern
    if ($action === 'save_draft') {
        try {
            $checklistData = $_POST['checklist_items'] ?? [];
            $maintenanceNotes = $_POST['maintenance_notes'] ?? '';
            $signature = $_POST['signature'] ?? '';
            
            $stmt = $pdo->prepare("
                UPDATE maintenance_history 
                SET checklist_data = ?,
                    notes = ?,
                    signature_data = ?,
                    status = 'draft',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                json_encode($checklistData),
                $maintenanceNotes,
                $signature,
                $maintenanceId
            ]);
            
            $success = 'Entwurf gespeichert!';
            
            // Gespeicherte Antworten neu laden
            $savedAnswers = $checklistData;
            
        } catch (Exception $e) {
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
    
    // Wartung abschließen
    if ($action === 'complete_maintenance') {
        try {
            $checklistData = $_POST['checklist_items'] ?? [];
            $maintenanceNotes = $_POST['maintenance_notes'] ?? '';
            $signature = $_POST['signature'] ?? '';
            
            // Validierung: Pflichtfelder prüfen
            $missingRequired = [];
            foreach ($items as $item) {
                if ($item['is_required']) {
                    $itemData = $checklistData[$item['id']] ?? [];
                    $isEmpty = true;
                    
                    if (isset($itemData['checked']) && $itemData['checked']) {
                        $isEmpty = false;
                    } elseif (isset($itemData['value']) && trim($itemData['value']) !== '') {
                        $isEmpty = false;
                    }
                    
                    if ($isEmpty) {
                        $missingRequired[] = $item['item_text'];
                    }
                }
            }
            
            if (!empty($missingRequired)) {
                throw new Exception('Folgende Pflichtfelder fehlen: ' . implode(', ', array_slice($missingRequired, 0, 3)) . (count($missingRequired) > 3 ? '...' : ''));
            }
            
            // Fotos hochladen
            $uploadedPhotos = [];
            $uploadDir = 'uploads/maintenance/' . $maintenanceId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($_FILES as $key => $file) {
                if (strpos($key, 'item_photo_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
                    $itemId = str_replace('item_photo_', '', $key);
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = 'photo_' . $itemId . '_' . time() . '.' . $ext;
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $uploadedPhotos[$itemId] = $filepath;
                    }
                }
            }
            
            // Fotos zu checklist_data hinzufügen
            foreach ($uploadedPhotos as $itemId => $photoPath) {
                if (!isset($checklistData[$itemId])) {
                    $checklistData[$itemId] = [];
                }
                $checklistData[$itemId]['photo'] = $photoPath;
            }
            
            // Status auf completed setzen
            $stmt = $pdo->prepare("
                UPDATE maintenance_history 
                SET checklist_data = ?,
                    notes = ?,
                    signature_data = ?,
                    status = 'completed',
                    maintenance_date = CURDATE(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                json_encode($checklistData),
                $maintenanceNotes,
                $signature,
                $maintenanceId
            ]);
            
            logActivity('maintenance_completed', "Wartung für Marker '" . $maintenance['marker_name'] . "' abgeschlossen", $maintenance['marker_id']);
            
            // Zur Marker-Ansicht weiterleiten
            header('Location: view_marker.php?id=' . $maintenance['marker_id'] . '&maintenance_success=1');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fortschritt berechnen
$totalItems = count($items);
$requiredItems = 0;
$filledItems = 0;
$filledRequired = 0;

foreach ($items as $item) {
    if ($item['is_required']) {
        $requiredItems++;
    }
    
    $itemData = $savedAnswers[$item['id']] ?? [];
    $isFilled = false;
    
    if (isset($itemData['checked']) && $itemData['checked']) {
        $isFilled = true;
    } elseif (isset($itemData['value']) && trim($itemData['value']) !== '') {
        $isFilled = true;
    }
    
    if ($isFilled) {
        $filledItems++;
        if ($item['is_required']) {
            $filledRequired++;
        }
    }
}

$progressPercent = $totalItems > 0 ? round(($filledItems / $totalItems) * 100) : 0;
$missingRequired = $requiredItems - $filledRequired;

include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wartung durchführen - <?= htmlspecialchars($maintenance['marker_name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setting-group {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .setting-group h3 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Progress Bar */
        .progress-container {
            background: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 70px;
            z-index: 100;
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .progress-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }
        .progress-stats {
            font-size: 14px;
            color: #6c757d;
        }
        .progress-bar-container {
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
        }
        .progress-warning {
            margin-top: 12px;
            padding: 10px 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            font-size: 14px;
            color: #856404;
        }
        .progress-warning.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        /* Checklist Items */
        .checklist-item {
            background: #f8f9fa;
            border-left: 4px solid #dee2e6;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .checklist-item.required {
            border-left-color: #dc3545;
        }
        .checklist-item.filled {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            gap: 20px;
        }
        .item-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            flex: 1;
        }
        .item-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .item-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-required {
            background: #dc3545;
            color: white;
        }
        .badge-photo {
            background: #17a2b8;
            color: white;
        }
        .badge-measurement {
            background: #ffc107;
            color: #000;
        }
        .field-container {
            margin-bottom: 15px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }
        .checkbox-group label {
            font-size: 16px;
            cursor: pointer;
            margin: 0;
        }
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .radio-option:hover {
            border-color: var(--primary-color);
        }
        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .measurement-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .measurement-input input {
            flex: 1;
        }
        .measurement-unit {
            font-weight: 600;
            color: #6c757d;
            padding: 10px 15px;
            background: #e9ecef;
            border-radius: 6px;
        }
        .photo-upload {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border: 2px dashed #dee2e6;
            border-radius: 6px;
        }
        .photo-upload label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        .notes-field {
            margin-top: 15px;
        }
        .notes-field label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Signature Pad */
        .signature-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .signature-pad-container {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            margin-top: 15px;
        }
        #signaturePad {
            width: 100%;
            height: 200px;
            cursor: crosshair;
            display: block;
        }
        .signature-controls {
            padding: 10px;
            background: #f8f9fa;
            border-top: 2px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn-large {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 1000px; margin: 40px auto; padding: 20px;">
    
    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-header">
            <div class="progress-title">
                <i class="fas fa-clipboard-check"></i> 
                <?= htmlspecialchars($maintenance['checklist_name']) ?>
            </div>
            <div class="progress-stats">
                <strong><?= $filledItems ?></strong> / <?= $totalItems ?> ausgefüllt
            </div>
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?= $progressPercent ?>%">
                <?= $progressPercent ?>%
            </div>
        </div>
        <?php if ($missingRequired > 0): ?>
            <div class="progress-warning error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong><?= $missingRequired ?></strong> Pflichtfeld<?= $missingRequired > 1 ? 'er' : '' ?> noch offen
            </div>
        <?php elseif ($filledItems < $totalItems): ?>
            <div class="progress-warning">
                <i class="fas fa-info-circle"></i>
                Noch <strong><?= $totalItems - $filledItems ?></strong> optionale Feld<?= ($totalItems - $filledItems) > 1 ? 'er' : '' ?> offen
            </div>
        <?php else: ?>
            <div class="progress-warning" style="background: #d4edda; border-left-color: #28a745; color: #155724;">
                <i class="fas fa-check-circle"></i>
                Alle Felder ausgefüllt! Bereit zum Abschließen.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="maintenanceForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="complete_maintenance" id="formAction">
        
        <?php if (!empty($items)): ?>
            <div class="setting-group">
                <h3><i class="fas fa-clipboard-check"></i> Checkliste ausfüllen</h3>
                
                <?php foreach ($items as $index => $item): 
                    $itemData = $savedAnswers[$item['id']] ?? [];
                    $isFilled = (isset($itemData['checked']) && $itemData['checked']) || 
                                (isset($itemData['value']) && trim($itemData['value']) !== '');
                ?>
                    <div class="checklist-item <?= $item['is_required'] ? 'required' : '' ?> <?= $isFilled ? 'filled' : '' ?>" 
                         data-item-id="<?= $item['id'] ?>">
                        <div class="item-header">
                            <div class="item-title">
                                <span style="color: #007bff; margin-right: 10px;"><?= $index + 1 ?>.</span>
                                <?= htmlspecialchars($item['item_text']) ?>
                                <?php if ($item['is_required']): ?>
                                    <span style="color: #dc3545; margin-left: 5px;">*</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-badges">
                                <?php if ($item['is_required']): ?>
                                    <span class="item-badge badge-required"><i class="fas fa-asterisk"></i> Pflicht</span>
                                <?php endif; ?>
                                <?php if ($item['requires_photo']): ?>
                                    <span class="item-badge badge-photo"><i class="fas fa-camera"></i> Foto</span>
                                <?php endif; ?>
                                <?php if ($item['field_type'] === 'measurement' || $item['requires_measurement']): ?>
                                    <span class="item-badge badge-measurement"><i class="fas fa-ruler"></i> Messwert</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <input type="hidden" name="checklist_items[<?= $item['id'] ?>][field_type]" value="<?= $item['field_type'] ?>">
                        
                        <div class="field-container">
                            <?php
                            switch ($item['field_type']) {
                                case 'checkbox':
                                    $checked = isset($itemData['checked']) && $itemData['checked'] ? 'checked' : '';
                                    echo '<div class="checkbox-group">';
                                    echo '<input type="checkbox" id="item_' . $item['id'] . '" name="checklist_items[' . $item['id'] . '][checked]" value="1" ' . $checked . ' onchange="updateProgress()">';
                                    echo '<label for="item_' . $item['id'] . '">Geprüft / Erledigt</label>';
                                    echo '</div>';
                                    break;
                                
                                case 'radio':
                                    echo '<div class="radio-group">';
                                    if (!empty($item['options'])) {
                                        $savedValue = $itemData['value'] ?? '';
                                        foreach ($item['options'] as $option) {
                                            $checked = ($savedValue === $option['value']) || (!$savedValue && $option['is_default']);
                                            echo '<label class="radio-option">';
                                            echo '<input type="radio" name="checklist_items[' . $item['id'] . '][value]" value="' . htmlspecialchars($option['value']) . '"' . ($checked ? ' checked' : '') . ' onchange="updateProgress()">';
                                            echo htmlspecialchars($option['label']);
                                            echo '</label>';
                                        }
                                    }
                                    echo '</div>';
                                    break;
                                
                                case 'select':
                                    $savedValue = $itemData['value'] ?? '';
                                    echo '<select name="checklist_items[' . $item['id'] . '][value]" class="form-control" onchange="updateProgress()">';
                                    echo '<option value="">-- Bitte auswählen --</option>';
                                    if (!empty($item['options'])) {
                                        foreach ($item['options'] as $option) {
                                            $selected = ($savedValue === $option['value']) || (!$savedValue && $option['is_default']);
                                            echo '<option value="' . htmlspecialchars($option['value']) . '"' . ($selected ? ' selected' : '') . '>';
                                            echo htmlspecialchars($option['label']);
                                            echo '</option>';
                                        }
                                    }
                                    echo '</select>';
                                    break;
                                
                                case 'text':
                                    $savedValue = $itemData['value'] ?? '';
                                    echo '<input type="text" name="checklist_items[' . $item['id'] . '][value]" class="form-control" placeholder="Antwort eingeben..." value="' . htmlspecialchars($savedValue) . '" oninput="updateProgress()">';
                                    break;
                                
                                case 'textarea':
                                    $savedValue = $itemData['value'] ?? '';
                                    echo '<textarea name="checklist_items[' . $item['id'] . '][value]" class="form-control" rows="3" placeholder="Antwort eingeben..." oninput="updateProgress()">' . htmlspecialchars($savedValue) . '</textarea>';
                                    break;
                                
                                case 'number':
                                    $savedValue = $itemData['value'] ?? '';
                                    echo '<input type="number" step="0.01" name="checklist_items[' . $item['id'] . '][value]" class="form-control" placeholder="Zahl eingeben..." value="' . htmlspecialchars($savedValue) . '" oninput="updateProgress()">';
                                    break;
                                
                                case 'date':
                                    $savedValue = $itemData['value'] ?? '';
                                    echo '<input type="date" name="checklist_items[' . $item['id'] . '][value]" class="form-control" value="' . htmlspecialchars($savedValue) . '" onchange="updateProgress()">';
                                    break;
                                
                                case 'measurement':
                                    $savedValue = $itemData['value'] ?? '';
                                    echo '<div class="measurement-input">';
                                    echo '<input type="number" step="0.01" name="checklist_items[' . $item['id'] . '][value]" class="form-control" placeholder="Messwert..." value="' . htmlspecialchars($savedValue) . '" oninput="updateProgress()">';
                                    if ($item['measurement_unit']) {
                                        echo '<span class="measurement-unit">' . htmlspecialchars($item['measurement_unit']) . '</span>';
                                    }
                                    echo '</div>';
                                    if ($item['measurement_min'] !== null || $item['measurement_max'] !== null) {
                                        echo '<div style="margin-top: 8px; font-size: 13px; color: #6c757d;">';
                                        if ($item['measurement_min'] !== null) {
                                            echo '<i class="fas fa-info-circle"></i> Min: ' . $item['measurement_min'];
                                        }
                                        if ($item['measurement_max'] !== null) {
                                            echo ' | Max: ' . $item['measurement_max'];
                                        }
                                        echo '</div>';
                                    }
                                    if ($item['measurement_min'] !== null) {
                                        echo '<input type="hidden" name="checklist_items[' . $item['id'] . '][min]" value="' . $item['measurement_min'] . '">';
                                    }
                                    if ($item['measurement_max'] !== null) {
                                        echo '<input type="hidden" name="checklist_items[' . $item['id'] . '][max]" value="' . $item['measurement_max'] . '">';
                                    }
                                    break;
                            }
                            ?>
                        </div>
                        
                        <?php if ($item['requires_photo']): ?>
                            <div class="photo-upload">
                                <label><i class="fas fa-camera"></i> Foto hochladen</label>
                                <input type="file" name="item_photo_<?= $item['id'] ?>" accept="image/*" class="form-control">
                                <?php if (isset($itemData['photo']) && file_exists($itemData['photo'])): ?>
                                    <div style="margin-top: 10px;">
                                        <img src="<?= htmlspecialchars($itemData['photo']) ?>" 
                                             style="max-width: 200px; border-radius: 6px; border: 2px solid #dee2e6;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="notes-field">
                            <label>Notizen / Bemerkungen (optional)</label>
                            <?php $savedNotes = $itemData['notes'] ?? ''; ?>
                            <textarea name="checklist_items[<?= $item['id'] ?>][notes]" class="form-control" rows="2" placeholder="Zusätzliche Bemerkungen zu diesem Prüfpunkt..."><?= htmlspecialchars($savedNotes) ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="setting-group">
            <h3><i class="fas fa-comment"></i> Allgemeine Notizen zur Wartung</h3>
            <textarea name="maintenance_notes" class="form-control" rows="4" placeholder="Allgemeine Bemerkungen zur Wartung..."><?= htmlspecialchars($maintenance['notes'] ?? '') ?></textarea>
        </div>
        
        <!-- Digitale Unterschrift -->
        <div class="signature-section">
            <h3 style="margin: 0 0 15px 0; color: var(--secondary-color); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-signature"></i> Digitale Unterschrift
            </h3>
            <p style="color: #6c757d; margin-bottom: 15px;">
                Bitte unterschreiben Sie hier, um die Wartung zu bestätigen.
            </p>
            <div class="signature-pad-container">
                <canvas id="signaturePad"></canvas>
                <div class="signature-controls">
                    <span style="color: #6c757d; font-size: 14px;">
                        <i class="fas fa-pen"></i> Mit Maus oder Finger unterschreiben
                    </span>
                    <button type="button" onclick="clearSignature()" class="btn btn-sm btn-secondary">
                        <i class="fas fa-eraser"></i> Löschen
                    </button>
                </div>
            </div>
            <input type="hidden" name="signature" id="signatureData" value="<?= htmlspecialchars($maintenance['signature_data'] ?? '') ?>">
        </div>
        
        <div class="form-actions">
            <button type="button" onclick="saveDraft()" class="btn btn-primary btn-large">
                <i class="fas fa-save"></i> Entwurf speichern
            </button>
            <button type="submit" class="btn btn-success btn-large">
                <i class="fas fa-check"></i> Wartung abschließen
            </button>
            <a href="view_marker.php?id=<?= $maintenance['marker_id'] ?>" class="btn btn-secondary btn-large" onclick="return confirmLeave()">
                <i class="fas fa-times"></i> Abbrechen
            </a>
        </div>
    </form>
</div>

<script>
// Signature Pad
const canvas = document.getElementById('signaturePad');
const ctx = canvas.getContext('2d');
let isDrawing = false;
let hasSignature = false;

// Canvas Größe setzen
function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    ctx.scale(ratio, ratio);
    
    // Gespeicherte Signatur wiederherstellen
    const savedSignature = document.getElementById('signatureData').value;
    if (savedSignature) {
        const img = new Image();
        img.onload = function() {
            ctx.drawImage(img, 0, 0, canvas.offsetWidth, canvas.offsetHeight);
            hasSignature = true;
        };
        img.src = savedSignature;
    }
}

resizeCanvas();
window.addEventListener('resize', resizeCanvas);

// Zeichnen
function startDrawing(e) {
    isDrawing = true;
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.touches[0].clientX) - rect.left;
    const y = (e.clientY || e.touches[0].clientY) - rect.top;
    ctx.beginPath();
    ctx.moveTo(x, y);
}

function draw(e) {
    if (!isDrawing) return;
    e.preventDefault();
    
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.touches[0].clientX) - rect.left;
    const y = (e.clientY || e.touches[0].clientY) - rect.top;
    
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    ctx.lineTo(x, y);
    ctx.stroke();
    
    hasSignature = true;
}

function stopDrawing() {
    if (isDrawing) {
        isDrawing = false;
        // Signatur speichern
        document.getElementById('signatureData').value = canvas.toDataURL();
    }
}

canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

canvas.addEventListener('touchstart', startDrawing);
canvas.addEventListener('touchmove', draw);
canvas.addEventListener('touchend', stopDrawing);

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('signatureData').value = '';
    hasSignature = false;
}

// Progress Update
function updateProgress() {
    // Aktualisierung erfolgt beim nächsten Laden
}

// Draft speichern
function saveDraft() {
    document.getElementById('formAction').value = 'save_draft';
    document.getElementById('maintenanceForm').submit();
}

// Verlassen bestätigen
let formChanged = false;

document.getElementById('maintenanceForm').addEventListener('change', function() {
    formChanged = true;
});

function confirmLeave() {
    if (formChanged) {
        return confirm('Sie haben nicht gespeicherte Änderungen. Möchten Sie wirklich fortfahren?');
    }
    return true;
}

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Bei Formular-Submit formChanged zurücksetzen
document.getElementById('maintenanceForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>