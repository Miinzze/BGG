<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('markers_bulk_edit');

$message = '';
$messageType = '';

// Marker laden für Auswahl
$markers = [];
if (isset($_POST['load_markers'])) {
    $filter = $_POST['filter'] ?? 'all';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? '';
    
    $sql = "SELECT id, name, qr_code, category, rental_status, is_storage, is_customer_device, is_repair_device, is_finished, latitude, longitude 
            FROM markers WHERE deleted_at IS NULL";
    $params = [];
    
    if ($filter === 'storage') {
        $sql .= " AND is_storage = 1";
    } elseif ($filter === 'rental') {
        $sql .= " AND is_storage = 0 AND is_customer_device = 0";
    } elseif ($filter === 'customer') {
        $sql .= " AND is_customer_device = 1";
    } elseif ($filter === 'repair') {
        $sql .= " AND is_repair_device = 1";
    } elseif ($filter === 'unfinished') {
        $sql .= " AND (is_customer_device = 1 OR is_repair_device = 1) AND is_finished = 0";
    }
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($status) {
        $sql .= " AND rental_status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $markers = $stmt->fetchAll();
}

// Massenbearbeitung durchführen
if (isset($_POST['bulk_update']) && !empty($_POST['selected_markers'])) {
    validateCSRF();
    
    $selectedMarkers = $_POST['selected_markers'];
    $updateAction = $_POST['update_action'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        $updated = 0;
        foreach ($selectedMarkers as $markerId) {
            $markerId = intval($markerId);
            
            switch ($updateAction) {
                case 'change_status':
                    $newStatus = $_POST['new_status'] ?? '';
                    if (in_array($newStatus, ['verfuegbar', 'vermietet', 'wartung'])) {
                        $stmt = $pdo->prepare("UPDATE markers SET rental_status = ? WHERE id = ?");
                        $stmt->execute([$newStatus, $markerId]);
                        logActivity('marker_status_changed', "Status auf '$newStatus' geändert (Massenbearbeitung)", $markerId);
                        $updated++;
                    }
                    break;
                    
                case 'change_category':
                    $newCategory = $_POST['new_category'] ?? '';
                    if ($newCategory) {
                        $stmt = $pdo->prepare("UPDATE markers SET category = ? WHERE id = ?");
                        $stmt->execute([$newCategory, $markerId]);
                        logActivity('marker_category_changed', "Kategorie auf '$newCategory' geändert (Massenbearbeitung)", $markerId);
                        $updated++;
                    }
                    break;
                    
                case 'mark_finished':
                    $stmt = $pdo->prepare("UPDATE markers SET is_finished = 1, finished_at = NOW(), finished_by = ? WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id'], $markerId]);
                    logActivity('marker_finished', "Als fertig markiert (Massenbearbeitung)", $markerId);
                    $updated++;
                    break;
                    
                case 'mark_unfinished':
                    $stmt = $pdo->prepare("UPDATE markers SET is_finished = 0, finished_at = NULL, finished_by = NULL WHERE id = ?");
                    $stmt->execute([$markerId]);
                    logActivity('marker_unfinished', "Als nicht fertig markiert (Massenbearbeitung)", $markerId);
                    $updated++;
                    break;
                    
                case 'set_maintenance_interval':
                    $interval = intval($_POST['maintenance_interval'] ?? 6);
                    $stmt = $pdo->prepare("UPDATE markers SET maintenance_interval_months = ? WHERE id = ?");
                    $stmt->execute([$interval, $markerId]);
                    logActivity('marker_maintenance_interval_changed', "Wartungsintervall auf $interval Monate gesetzt (Massenbearbeitung)", $markerId);
                    $updated++;
                    break;
                    
                case 'reset_position':
                    $stmt = $pdo->prepare("UPDATE markers SET latitude = NULL, longitude = NULL WHERE id = ?");
                    $stmt->execute([$markerId]);
                    logActivity('marker_position_reset', "GPS-Position zurückgesetzt (Massenbearbeitung)", $markerId);
                    $updated++;
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("UPDATE markers SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id'], $markerId]);
                    logActivity('marker_deleted_soft', "In Papierkorb verschoben (Massenbearbeitung)", $markerId);
                    $updated++;
                    break;
            }
        }
        
        $pdo->commit();
        $message = "$updated Marker erfolgreich aktualisiert!";
        $messageType = 'success';
        
        // Marker neu laden
        $markers = [];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Fehler bei der Massenbearbeitung: ' . e($e->getMessage());
        $messageType = 'danger';
    }
}

// Kategorien für Dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM markers WHERE category IS NOT NULL AND deleted_at IS NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marker Massenbearbeitung</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bulk-edit-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .step-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .step-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        
        .step-title {
            font-size: 24px;
            font-weight: bold;
            color: #212529;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .markers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .markers-table thead {
            background: #f8f9fa;
        }
        
        .markers-table th,
        .markers-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .markers-table th {
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        
        .markers-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .marker-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-storage {
            background: #e7f3ff;
            color: #004085;
        }
        
        .badge-rental {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-customer {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-repair {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-verfuegbar {
            background: #d4edda;
            color: #155724;
        }
        
        .status-vermietet {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-wartung {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .selection-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .selection-info i {
            color: #0c5460;
            font-size: 24px;
        }
        
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        
        .select-all-container {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #e7f3ff;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .danger-zone {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="bulk-edit-container">
        <h1><i class="fas fa-tasks"></i> Marker Massenbearbeitung</h1>
        <p>Bearbeiten Sie mehrere Marker gleichzeitig mit wenigen Klicks.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Schritt 1: Marker auswählen -->
        <div class="step-container">
            <div class="step-header">
                <div class="step-number">1</div>
                <div class="step-title">Marker auswählen</div>
            </div>
            
            <form method="POST">
                <div class="filter-grid">
                    <div class="form-group">
                        <label>Filter</label>
                        <select name="filter" class="form-control">
                            <option value="all">Alle Marker</option>
                            <option value="storage">Nur Lagergeräte</option>
                            <option value="rental">Nur Mietgeräte</option>
                            <option value="customer">Nur Kundengeräte</option>
                            <option value="repair">Nur Reparaturgeräte</option>
                            <option value="unfinished">Nur unfertige Geräte</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kategorie</label>
                        <select name="category" class="form-control">
                            <option value="">Alle Kategorien</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Alle Status</option>
                            <option value="verfuegbar">Verfügbar</option>
                            <option value="vermietet">Vermietet</option>
                            <option value="wartung">Wartung</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="load_markers" class="btn btn-primary">
                    <i class="fas fa-search"></i> Marker laden
                </button>
            </form>
        </div>
        
        <?php if (!empty($markers)): ?>
        <!-- Schritt 2: Marker aus Liste auswählen -->
        <div class="step-container">
            <div class="step-header">
                <div class="step-number">2</div>
                <div class="step-title">Gewünschte Marker auswählen</div>
            </div>
            
            <form method="POST" id="bulkEditForm">
                <?= csrf_field() ?>
                
                <div class="select-all-container">
                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                    <label for="selectAll"><strong>Alle auswählen / abwählen</strong></label>
                    <span id="selectionCount" style="margin-left: auto; color: #007bff; font-weight: bold;">0 ausgewählt</span>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="markers-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell">
                                    <input type="checkbox" id="selectAllHeader" onclick="toggleAll(this)">
                                </th>
                                <th>Name</th>
                                <th>QR-Code</th>
                                <th>Typ</th>
                                <th>Kategorie</th>
                                <th>Status</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($markers as $marker): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" name="selected_markers[]" value="<?= $marker['id'] ?>" class="marker-checkbox" onchange="updateCount()">
                                </td>
                                <td><strong><?= e($marker['name']) ?></strong></td>
                                <td><code><?= e($marker['qr_code']) ?></code></td>
                                <td>
                                    <?php if ($marker['is_storage']): ?>
                                        <span class="marker-badge badge-storage"><i class="fas fa-warehouse"></i> Lager</span>
                                    <?php elseif ($marker['is_customer_device']): ?>
                                        <span class="marker-badge badge-customer"><i class="fas fa-user-tie"></i> Kunde</span>
                                    <?php elseif ($marker['is_repair_device']): ?>
                                        <span class="marker-badge badge-repair"><i class="fas fa-tools"></i> Reparatur</span>
                                    <?php else: ?>
                                        <span class="marker-badge badge-rental"><i class="fas fa-handshake"></i> Miete</span>
                                    <?php endif; ?>
                                    <?php if ($marker['is_finished']): ?>
                                        <span class="marker-badge" style="background: #d4edda; color: #155724;">
                                            <i class="fas fa-check"></i> Fertig
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($marker['category'] ?? '-') ?></td>
                                <td>
                                    <span class="status-badge status-<?= e($marker['rental_status']) ?>">
                                        <?= e(ucfirst($marker['rental_status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($marker['latitude'] && $marker['longitude']): ?>
                                        <i class="fas fa-map-marker-alt" style="color: #28a745;"></i> Vorhanden
                                    <?php else: ?>
                                        <i class="fas fa-times" style="color: #dc3545;"></i> Keine
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Schritt 3: Aktion auswählen -->
                <div class="step-container" style="margin-top: 30px;">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <div class="step-title">Aktion wählen und ausführen</div>
                    </div>
                    
                    <div id="noSelectionWarning" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> Bitte wählen Sie mindestens einen Marker aus!
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Aktion</strong></label>
                        <select name="update_action" id="updateAction" class="form-control" onchange="showActionOptions()">
                            <option value="">-- Bitte wählen --</option>
                            <option value="change_status">Status ändern</option>
                            <option value="change_category">Kategorie ändern</option>
                            <option value="mark_finished">Als "Fertig" markieren</option>
                            <option value="mark_unfinished">Als "Nicht fertig" markieren</option>
                            <option value="set_maintenance_interval">Wartungsintervall setzen</option>
                            <option value="reset_position">GPS-Position zurücksetzen</option>
                            <option value="delete" style="color: #dc3545;">In Papierkorb verschieben</option>
                        </select>
                    </div>
                    
                    <!-- Optionen für verschiedene Aktionen -->
                    <div id="statusOptions" class="action-section" style="display: none;">
                        <label><strong>Neuer Status</strong></label>
                        <select name="new_status" class="form-control">
                            <option value="verfuegbar">Verfügbar</option>
                            <option value="vermietet">Vermietet</option>
                            <option value="wartung">Wartung</option>
                        </select>
                    </div>
                    
                    <div id="categoryOptions" class="action-section" style="display: none;">
                        <label><strong>Neue Kategorie</strong></label>
                        <select name="new_category" class="form-control">
                            <option value="">-- Bitte wählen --</option>
                            <option value="Generator">Generator</option>
                            <option value="Kompressor">Kompressor</option>
                            <option value="Pumpe">Pumpe</option>
                            <option value="Fahrzeug">Fahrzeug</option>
                            <option value="Werkzeug">Werkzeug</option>
                            <option value="Lager">Lager</option>
                            <option value="Sonstiges">Sonstiges</option>
                        </select>
                    </div>
                    
                    <div id="maintenanceOptions" class="action-section" style="display: none;">
                        <label><strong>Wartungsintervall (Monate)</strong></label>
                        <input type="number" name="maintenance_interval" class="form-control" value="6" min="1" max="120">
                    </div>
                    
                    <div id="deleteWarning" class="danger-zone" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Achtung!</strong>
                        <p>Die ausgewählten Marker werden in den Papierkorb verschoben. Sie können später wiederhergestellt werden.</p>
                    </div>
                    
                    <div style="margin-top: 20px; display: flex; gap: 15px;">
                        <button type="submit" name="bulk_update" class="btn btn-success" onclick="return confirmBulkUpdate()">
                            <i class="fas fa-check"></i> Änderungen auf ausgewählte Marker anwenden
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                            <i class="fas fa-redo"></i> Abbrechen
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.marker-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        document.getElementById('selectAll').checked = checkbox.checked;
        document.getElementById('selectAllHeader').checked = checkbox.checked;
        updateCount();
    }
    
    function updateCount() {
        const count = document.querySelectorAll('.marker-checkbox:checked').length;
        document.getElementById('selectionCount').textContent = count + ' ausgewählt';
        document.getElementById('noSelectionWarning').style.display = count === 0 ? 'block' : 'none';
    }
    
    function showActionOptions() {
        const action = document.getElementById('updateAction').value;
        
        // Alle Optionen verstecken
        document.getElementById('statusOptions').style.display = 'none';
        document.getElementById('categoryOptions').style.display = 'none';
        document.getElementById('maintenanceOptions').style.display = 'none';
        document.getElementById('deleteWarning').style.display = 'none';
        
        // Relevante Optionen anzeigen
        if (action === 'change_status') {
            document.getElementById('statusOptions').style.display = 'block';
        } else if (action === 'change_category') {
            document.getElementById('categoryOptions').style.display = 'block';
        } else if (action === 'set_maintenance_interval') {
            document.getElementById('maintenanceOptions').style.display = 'block';
        } else if (action === 'delete') {
            document.getElementById('deleteWarning').style.display = 'block';
        }
    }
    
    function confirmBulkUpdate() {
        const count = document.querySelectorAll('.marker-checkbox:checked').length;
        if (count === 0) {
            alert('Bitte wählen Sie mindestens einen Marker aus!');
            return false;
        }
        
        const action = document.getElementById('updateAction').value;
        if (!action) {
            alert('Bitte wählen Sie eine Aktion aus!');
            return false;
        }
        
        let actionText = '';
        switch (action) {
            case 'change_status': actionText = 'den Status ändern'; break;
            case 'change_category': actionText = 'die Kategorie ändern'; break;
            case 'mark_finished': actionText = 'als fertig markieren'; break;
            case 'mark_unfinished': actionText = 'als nicht fertig markieren'; break;
            case 'set_maintenance_interval': actionText = 'das Wartungsintervall setzen'; break;
            case 'reset_position': actionText = 'die GPS-Position zurücksetzen'; break;
            case 'delete': actionText = 'in den Papierkorb verschieben'; break;
        }
        
        return confirm(`Möchten Sie wirklich ${count} Marker ${actionText}?\n\nDieser Vorgang kann nicht rückgängig gemacht werden.`);
    }
    
    // Initial count
    updateCount();
    </script>
</body>
</html>