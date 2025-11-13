<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

// AJAX-Handler für Daten laden
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] === 'get_checklist' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM maintenance_checklists WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }
    
    if ($_GET['ajax'] === 'get_items' && isset($_GET['checklist_id'])) {
        $stmt = $pdo->prepare("
            SELECT i.*, 
                   (SELECT JSON_ARRAYAGG(
                       JSON_OBJECT(
                           'id', o.id,
                           'value', o.option_value,
                           'label', o.option_label,
                           'order', o.option_order,
                           'is_default', o.is_default
                       )
                   )
                   FROM maintenance_checklist_item_options o
                   WHERE o.checklist_item_id = i.id
                   ORDER BY o.option_order) as options_json
            FROM maintenance_checklist_items i
            WHERE i.checklist_id = ? 
            ORDER BY i.item_order
        ");
        $stmt->execute([$_GET['checklist_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
}

$success = '';
$error = '';

// Checkliste erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_checklist') {
    validateCSRF();
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO maintenance_checklists 
            (name, description, category, is_template, is_dguv_compliant, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['description'] ?? null,
            $_POST['category'] ?? null,
            isset($_POST['is_template']) ? 1 : 0,
            isset($_POST['is_dguv_compliant']) ? 1 : 0,
            $_SESSION['user_id']
        ]);
        
        $checklistId = $pdo->lastInsertId();
        
        // Items hinzufügen
        if (!empty($_POST['items'])) {
            $itemStmt = $pdo->prepare("
                INSERT INTO maintenance_checklist_items 
                (checklist_id, item_text, field_type, field_options, default_value, item_order, 
                 is_required, requires_photo, requires_measurement, measurement_unit, 
                 measurement_min, measurement_max)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $optionStmt = $pdo->prepare("
                INSERT INTO maintenance_checklist_item_options
                (checklist_item_id, option_value, option_label, option_order, is_default)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($_POST['items'] as $index => $item) {
                if (!empty($item['text'])) {
                    $fieldType = $item['field_type'] ?? 'checkbox';
                    $fieldOptions = null;
                    
                    // Field Options als JSON speichern (falls vorhanden)
                    if (!empty($item['options']) && in_array($fieldType, ['radio', 'select', 'checkbox'])) {
                        $fieldOptions = json_encode($item['options']);
                    }
                    
                    $itemStmt->execute([
                        $checklistId,
                        $item['text'],
                        $fieldType,
                        $fieldOptions,
                        $item['default_value'] ?? null,
                        $index + 1,
                        isset($item['required']) ? 1 : 0,
                        isset($item['requires_photo']) ? 1 : 0,
                        isset($item['requires_measurement']) ? 1 : 0,
                        $item['measurement_unit'] ?? null,
                        !empty($item['measurement_min']) ? $item['measurement_min'] : null,
                        !empty($item['measurement_max']) ? $item['measurement_max'] : null
                    ]);
                    
                    $itemId = $pdo->lastInsertId();
                    
                    // Optionen in separate Tabelle einfügen
                    if (!empty($item['options']) && is_array($item['options'])) {
                        foreach ($item['options'] as $optIdx => $option) {
                            if (!empty($option['label'])) {
                                $optionStmt->execute([
                                    $itemId,
                                    $option['value'] ?? $option['label'],
                                    $option['label'],
                                    $optIdx + 1,
                                    isset($option['is_default']) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        $pdo->commit();
        logActivity('checklist_created', "Checkliste '{$_POST['name']}' erstellt");
        $success = "Checkliste erfolgreich erstellt!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Fehler: " . $e->getMessage();
    }
}

// Checkliste bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_checklist') {
    validateCSRF();
    
    try {
        $stmt = $pdo->prepare("
            UPDATE maintenance_checklists 
            SET name = ?, description = ?, category = ?, is_template = ?, is_dguv_compliant = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['description'] ?? null,
            $_POST['category'] ?? null,
            isset($_POST['is_template']) ? 1 : 0,
            isset($_POST['is_dguv_compliant']) ? 1 : 0,
            $_POST['checklist_id']
        ]);
        
        logActivity('checklist_updated', "Checkliste '{$_POST['name']}' aktualisiert");
        $success = "Checkliste erfolgreich aktualisiert!";
        
    } catch (Exception $e) {
        $error = "Fehler: " . $e->getMessage();
    }
}

// Checkpunkt hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    validateCSRF();
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            SELECT MAX(item_order) as max_order FROM maintenance_checklist_items WHERE checklist_id = ?
        ");
        $stmt->execute([$_POST['checklist_id']]);
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        
        $fieldType = $_POST['field_type'] ?? 'checkbox';
        $fieldOptions = null;
        
        // Field Options verarbeiten
        if (!empty($_POST['options']) && in_array($fieldType, ['radio', 'select', 'checkbox'])) {
            $fieldOptions = json_encode($_POST['options']);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO maintenance_checklist_items 
            (checklist_id, item_text, field_type, field_options, default_value, item_order, 
             is_required, requires_photo, requires_measurement, measurement_unit, 
             measurement_min, measurement_max)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['checklist_id'],
            $_POST['item_text'],
            $fieldType,
            $fieldOptions,
            $_POST['default_value'] ?? null,
            $maxOrder + 1,
            isset($_POST['is_required']) ? 1 : 0,
            isset($_POST['requires_photo']) ? 1 : 0,
            isset($_POST['requires_measurement']) ? 1 : 0,
            $_POST['measurement_unit'] ?? null,
            !empty($_POST['measurement_min']) ? $_POST['measurement_min'] : null,
            !empty($_POST['measurement_max']) ? $_POST['measurement_max'] : null
        ]);
        
        $itemId = $pdo->lastInsertId();
        
        // Optionen in separate Tabelle einfügen
        if (!empty($_POST['options']) && is_array($_POST['options'])) {
            $optionStmt = $pdo->prepare("
                INSERT INTO maintenance_checklist_item_options
                (checklist_item_id, option_value, option_label, option_order, is_default)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($_POST['options'] as $idx => $option) {
                if (!empty($option['label'])) {
                    $optionStmt->execute([
                        $itemId,
                        $option['value'] ?? $option['label'],
                        $option['label'],
                        $idx + 1,
                        isset($option['is_default']) ? 1 : 0
                    ]);
                }
            }
        }
        
        $pdo->commit();
        $success = "Checkpunkt erfolgreich hinzugefügt!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Fehler: " . $e->getMessage();
    }
}

// Checkpunkt löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_item') {
    validateCSRF();
    try {
        $stmt = $pdo->prepare("DELETE FROM maintenance_checklist_items WHERE id = ?");
        $stmt->execute([$_POST['item_id']]);
        $success = "Checkpunkt erfolgreich gelöscht!";
    } catch (Exception $e) {
        $error = "Fehler: " . $e->getMessage();
    }
}

// Checkliste löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_checklist') {
    validateCSRF();
    try {
        $stmt = $pdo->prepare("DELETE FROM maintenance_checklists WHERE id = ?");
        $stmt->execute([$_POST['checklist_id']]);
        $success = "Checkliste erfolgreich gelöscht!";
    } catch (Exception $e) {
        $error = "Fehler: " . $e->getMessage();
    }
}

// Alle Checklisten laden
$stmt = $pdo->query("
    SELECT c.*, 
           u.username as creator,
           (SELECT COUNT(*) FROM maintenance_checklist_items WHERE checklist_id = c.id) as item_count
    FROM maintenance_checklists c
    LEFT JOIN users u ON c.created_by = u.id
    ORDER BY c.is_template DESC, c.created_at DESC
");
$checklists = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wartungs-Checklisten</title>
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
        .checklist-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .checklist-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        .checklist-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .checklist-title {
            font-size: 20px;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .checklist-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .badge-template {
            background: #ffc107;
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-dguv {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-items {
            background: #6c757d;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
        }
        .checklist-actions {
            display: flex;
            gap: 8px;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            overflow-y: auto;
        }
        .modal-dialog {
            max-width: 900px;
            margin: 50px auto;
            animation: modalFadeIn 0.3s;
        }
        .modal-lg {
            max-width: 1000px;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }
        .close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 28px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }
        .close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        .item-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }
        .item-badge.required {
            background: #dc3545;
            color: white;
        }
        .item-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .item-text {
            flex: 1;
            font-size: 15px;
            color: #2c3e50;
        }
        .item-badges {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .item-container {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .item-container:hover {
            border-color: #007bff;
        }
        .item-remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .item-remove-btn:hover {
            background: #c82333;
            transform: rotate(90deg);
        }
        .field-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .field-type-option {
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .field-type-option:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }
        .field-type-option.active {
            border-color: #007bff;
            background: #007bff;
            color: white;
        }
        .field-type-option input[type="radio"] {
            display: none;
        }
        .options-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            margin-top: 15px;
        }
        .option-item {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .option-item input[type="text"] {
            flex: 1;
        }
        .option-remove {
            background: #dc3545;
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 14px;
        }
        .add-option-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .add-option-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-clipboard-list"></i> Wartungs-Checklisten</h1>
            <p class="page-description">Verwalten Sie Ihre Wartungs- und Prüfchecklisten mit erweiterten Feldtypen</p>
        </div>
        <button onclick="openCreateModal()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Neue Checkliste
        </button>
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

    <div class="setting-group">
        <h3><i class="fas fa-list"></i> Alle Checklisten</h3>
        
        <?php if (empty($checklists)): ?>
            <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 8px;">
                <i class="fas fa-clipboard-list" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                <h3 style="color: #6c757d; margin-bottom: 10px;">Keine Checklisten vorhanden</h3>
                <p style="color: #999; margin-bottom: 25px;">Erstellen Sie Ihre erste Wartungscheckliste</p>
                <button onclick="openCreateModal()" class="btn btn-primary btn-large">
                    <i class="fas fa-plus"></i> Erste Checkliste erstellen
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($checklists as $checklist): ?>
                <div class="checklist-card">
                    <div class="checklist-header">
                        <div style="flex: 1;">
                            <h3 class="checklist-title">
                                <i class="fas fa-clipboard-list"></i> 
                                <?= htmlspecialchars($checklist['name']) ?>
                            </h3>
                            
                            <div class="checklist-meta">
                                <?php if ($checklist['is_template']): ?>
                                    <span class="badge-template">
                                        <i class="fas fa-star"></i> Vorlage
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($checklist['is_dguv_compliant']): ?>
                                    <span class="badge-dguv">
                                        <i class="fas fa-shield-alt"></i> DGUV 3
                                    </span>
                                <?php endif; ?>
                                
                                <span class="badge-items">
                                    <i class="fas fa-tasks"></i> <?= $checklist['item_count'] ?> Prüfpunkte
                                </span>
                            </div>
                            
                            <?php if ($checklist['description']): ?>
                                <p style="margin: 10px 0 0 0; color: #6c757d; font-size: 14px;">
                                    <?= htmlspecialchars($checklist['description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="margin-top: 10px; font-size: 13px; color: #999;">
                                <?php if ($checklist['category']): ?>
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($checklist['category']) ?> | 
                                <?php endif; ?>
                                <i class="fas fa-user"></i> <?= htmlspecialchars($checklist['creator'] ?? 'Unbekannt') ?>
                            </div>
                        </div>
                        
                        <div class="checklist-actions">
                            <button onclick="openItemsModal(<?= $checklist['id'] ?>, '<?= htmlspecialchars(addslashes($checklist['name'])) ?>')" 
                                    class="btn btn-sm btn-info" title="Prüfpunkte verwalten">
                                <i class="fas fa-tasks"></i>
                            </button>
                            <button onclick="openEditModal(<?= $checklist['id'] ?>)" 
                                    class="btn btn-sm btn-primary" title="Bearbeiten">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Checkliste wirklich löschen?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_checklist">
                                <input type="hidden" name="checklist_id" value="<?= $checklist['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Löschen">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Neue Checkliste -->
<div id="createModal" class="modal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0;"><i class="fas fa-plus-circle"></i> Neue Checkliste erstellen</h2>
                <button type="button" class="close" onclick="closeCreateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="create_checklist">
                    
                    <div class="setting-group">
                        <h3><i class="fas fa-info-circle"></i> Grundinformationen</h3>
                        <div class="form-group">
                            <label for="name">Name der Checkliste *</label>
                            <input type="text" id="name" name="name" required class="form-control" 
                                   placeholder="z.B. DGUV 3 Prüfung Elektrogeräte">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Beschreibung</label>
                            <textarea id="description" name="description" class="form-control" rows="2" 
                                      placeholder="Optionale Beschreibung der Checkliste..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Kategorie</label>
                            <input type="text" id="category" name="category" class="form-control" 
                                   placeholder="z.B. Elektronik, Maschinen, Fahrzeuge...">
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_template" name="is_template" checked>
                            <label for="is_template">Als Vorlage markieren</label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_dguv_compliant" name="is_dguv_compliant">
                            <label for="is_dguv_compliant">
                                <i class="fas fa-shield-alt"></i> DGUV 3 konform
                            </label>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <h3><i class="fas fa-tasks"></i> Prüfpunkte <small style="color: #6c757d; font-size: 14px;">(Können auch später hinzugefügt werden)</small></h3>
                        <div id="items"></div>
                        <button type="button" onclick="addItem()" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Weiteren Prüfpunkt hinzufügen
                        </button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-save"></i> Checkliste erstellen
                        </button>
                        <button type="button" onclick="closeCreateModal()" class="btn btn-secondary btn-large">
                            <i class="fas fa-times"></i> Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Checkliste bearbeiten -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0;"><i class="fas fa-edit"></i> Checkliste bearbeiten</h2>
                <button type="button" class="close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="edit_checklist">
                    <input type="hidden" id="edit_checklist_id" name="checklist_id">
                    
                    <div class="form-group">
                        <label for="edit_name">Name der Checkliste *</label>
                        <input type="text" id="edit_name" name="name" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Beschreibung</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_category">Kategorie</label>
                        <input type="text" id="edit_category" name="category" class="form-control">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="edit_is_template" name="is_template">
                        <label for="edit_is_template">Als Vorlage markieren</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="edit_is_dguv" name="is_dguv_compliant">
                        <label for="edit_is_dguv">
                            <i class="fas fa-shield-alt"></i> DGUV 3 konform
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-save"></i> Änderungen speichern
                        </button>
                        <button type="button" onclick="closeEditModal()" class="btn btn-secondary btn-large">
                            <i class="fas fa-times"></i> Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Prüfpunkte verwalten -->
<div id="itemsModal" class="modal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="items_modal_title" style="margin: 0;"><i class="fas fa-tasks"></i> Prüfpunkte</h2>
                <button type="button" class="close" onclick="closeItemsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="items_list" style="margin-bottom: 30px;"></div>
                
                <div class="setting-group" style="background: #f0f8ff;">
                    <h3><i class="fas fa-plus-circle"></i> Neuen Prüfpunkt hinzufügen</h3>
                    <form method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_item">
                        <input type="hidden" id="items_checklist_id" name="checklist_id">
                        
                        <div class="form-group">
                            <label for="add_item_text">Prüfpunkt Text *</label>
                            <input type="text" id="add_item_text" name="item_text" required class="form-control" 
                                   placeholder="z.B. Schutzleiterprüfung durchgeführt">
                        </div>
                        
                        <div class="form-group">
                            <label>Feldtyp auswählen *</label>
                            <div class="field-type-selector" id="add_field_type_selector">
                                <label class="field-type-option active">
                                    <input type="radio" name="field_type" value="checkbox" checked>
                                    <div><i class="fas fa-check-square" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Checkbox</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="radio">
                                    <div><i class="fas fa-dot-circle" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Radio</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="select">
                                    <div><i class="fas fa-list" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Dropdown</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="text">
                                    <div><i class="fas fa-font" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Text</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="textarea">
                                    <div><i class="fas fa-align-left" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Textfeld</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="number">
                                    <div><i class="fas fa-hashtag" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Zahl</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="date">
                                    <div><i class="fas fa-calendar" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Datum</div>
                                </label>
                                <label class="field-type-option">
                                    <input type="radio" name="field_type" value="measurement">
                                    <div><i class="fas fa-ruler" style="font-size: 20px;"></i></div>
                                    <div style="font-size: 12px; margin-top: 5px;">Messwert</div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Optionen für Radio/Select/Checkbox -->
                        <div id="add_options_container" class="options-container" style="display: none;">
                            <label><i class="fas fa-list-ul"></i> Auswahlmöglichkeiten</label>
                            <div id="add_options_list"></div>
                            <button type="button" onclick="addOption('add')" class="add-option-btn">
                                <i class="fas fa-plus"></i> Option hinzufügen
                            </button>
                        </div>
                        
                        <div class="form-row">
                            <div class="checkbox-group" style="margin: 0;">
                                <input type="checkbox" id="add_is_required" name="is_required" checked>
                                <label for="add_is_required">Pflichtfeld</label>
                            </div>
                            <div class="checkbox-group" style="margin: 0;">
                                <input type="checkbox" id="add_requires_photo" name="requires_photo">
                                <label for="add_requires_photo">Foto erforderlich</label>
                            </div>
                        </div>
                        
                        <div id="add_measurement_fields" style="display: none; margin-top: 15px;">
                            <div class="form-group">
                                <label for="add_measurement_unit">Messeinheit</label>
                                <input type="text" id="add_measurement_unit" name="measurement_unit" class="form-control" 
                                       placeholder="z.B. Ohm, Volt, Bar, mm">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="add_measurement_min">Min. Wert</label>
                                    <input type="number" step="0.01" id="add_measurement_min" name="measurement_min" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="add_measurement_max">Max. Wert</label>
                                    <input type="number" step="0.01" id="add_measurement_max" name="measurement_max" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-large" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> Prüfpunkt hinzufügen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let counter = 0;
let optionCounters = {add: 0};

// Feldtyp-Auswahl Handler
document.addEventListener('DOMContentLoaded', function() {
    // Event Listener für Feldtyp-Auswahl im Add-Item Modal
    const fieldTypeSelector = document.getElementById('add_field_type_selector');
    if (fieldTypeSelector) {
        fieldTypeSelector.addEventListener('click', function(e) {
            const label = e.target.closest('.field-type-option');
            if (label) {
                // Alle aktiv entfernen
                fieldTypeSelector.querySelectorAll('.field-type-option').forEach(opt => opt.classList.remove('active'));
                // Aktuelle auswählen
                label.classList.add('active');
                const input = label.querySelector('input[type="radio"]');
                if (input) {
                    input.checked = true;
                    handleFieldTypeChange('add', input.value);
                }
            }
        });
    }
});

function handleFieldTypeChange(prefix, fieldType) {
    const optionsContainer = document.getElementById(prefix + '_options_container');
    const measurementFields = document.getElementById(prefix + '_measurement_fields');
    
    // Optionen anzeigen für radio, select, checkbox
    if (['radio', 'select', 'checkbox'].includes(fieldType)) {
        optionsContainer.style.display = 'block';
        // Mindestens 2 Optionen hinzufügen wenn leer
        const optionsList = document.getElementById(prefix + '_options_list');
        if (optionsList && optionsList.children.length === 0) {
            addOption(prefix);
            addOption(prefix);
        }
    } else {
        optionsContainer.style.display = 'none';
    }
    
    // Messfelder anzeigen für measurement
    if (measurementFields) {
        measurementFields.style.display = fieldType === 'measurement' ? 'block' : 'none';
    }
}

function addOption(prefix) {
    if (!optionCounters[prefix]) optionCounters[prefix] = 0;
    const optionId = optionCounters[prefix]++;
    
    const optionsList = document.getElementById(prefix + '_options_list');
    const div = document.createElement('div');
    div.className = 'option-item';
    div.setAttribute('data-option-id', optionId);
    div.innerHTML = `
        <input type="text" name="options[${optionId}][label]" class="form-control" 
               placeholder="Option ${optionId + 1}" required>
        <input type="hidden" name="options[${optionId}][value]" value="">
        <button type="button" class="option-remove" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    optionsList.appendChild(div);
}

function removeOption(btn) {
    btn.closest('.option-item').remove();
}

function openCreateModal() {
    document.getElementById('createModal').style.display = 'block';
    if (counter === 0) addItem(); // Ersten Prüfpunkt automatisch hinzufügen
}

function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
    // Formular zurücksetzen
    document.getElementById('items').innerHTML = '';
    counter = 0;
}

function addItem() {
    const itemId = counter++;
    optionCounters[itemId] = 0;
    
    const div = document.createElement('div');
    div.className = 'item-container';
    div.setAttribute('data-item-id', itemId);
    div.innerHTML = `
        <button type="button" class="item-remove-btn" onclick="removeItem(this)" title="Prüfpunkt entfernen">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="form-group">
            <label>Prüfpunkt Text *</label>
            <input type="text" name="items[${itemId}][text]" class="form-control" 
                   placeholder="z.B. Schutzleiterprüfung durchgeführt" required>
        </div>
        
        <div class="form-group">
            <label>Feldtyp *</label>
            <div class="field-type-selector" id="field_type_selector_${itemId}">
                <label class="field-type-option active">
                    <input type="radio" name="items[${itemId}][field_type]" value="checkbox" checked>
                    <div><i class="fas fa-check-square"></i></div>
                    <div style="font-size: 11px;">Checkbox</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="radio">
                    <div><i class="fas fa-dot-circle"></i></div>
                    <div style="font-size: 11px;">Radio</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="select">
                    <div><i class="fas fa-list"></i></div>
                    <div style="font-size: 11px;">Dropdown</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="text">
                    <div><i class="fas fa-font"></i></div>
                    <div style="font-size: 11px;">Text</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="textarea">
                    <div><i class="fas fa-align-left"></i></div>
                    <div style="font-size: 11px;">Textfeld</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="number">
                    <div><i class="fas fa-hashtag"></i></div>
                    <div style="font-size: 11px;">Zahl</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="date">
                    <div><i class="fas fa-calendar"></i></div>
                    <div style="font-size: 11px;">Datum</div>
                </label>
                <label class="field-type-option">
                    <input type="radio" name="items[${itemId}][field_type]" value="measurement">
                    <div><i class="fas fa-ruler"></i></div>
                    <div style="font-size: 11px;">Messwert</div>
                </label>
            </div>
        </div>
        
        <div id="options_${itemId}" class="options-container" style="display: none;">
            <label><i class="fas fa-list-ul"></i> Auswahlmöglichkeiten</label>
            <div id="options_list_${itemId}"></div>
            <button type="button" onclick="addItemOption(${itemId})" class="add-option-btn">
                <i class="fas fa-plus"></i> Option hinzufügen
            </button>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 15px;">
            <div class="checkbox-group" style="margin: 0;">
                <input type="checkbox" id="item_${itemId}_required" name="items[${itemId}][required]" checked>
                <label for="item_${itemId}_required">Pflichtfeld</label>
            </div>
            <div class="checkbox-group" style="margin: 0;">
                <input type="checkbox" id="item_${itemId}_photo" name="items[${itemId}][requires_photo]">
                <label for="item_${itemId}_photo">Foto</label>
            </div>
        </div>
        
        <div id="measurement_${itemId}" style="display: none; margin-top: 10px;">
            <div class="form-group" style="margin-bottom: 10px;">
                <label>Messeinheit</label>
                <input type="text" name="items[${itemId}][measurement_unit]" class="form-control" 
                       placeholder="z.B. Ohm, Volt, Bar">
            </div>
        </div>
    `;
    
    document.getElementById('items').appendChild(div);
    
    // Event Listener für Feldtyp-Auswahl hinzufügen
    const selector = document.getElementById('field_type_selector_' + itemId);
    selector.addEventListener('click', function(e) {
        const label = e.target.closest('.field-type-option');
        if (label) {
            selector.querySelectorAll('.field-type-option').forEach(opt => opt.classList.remove('active'));
            label.classList.add('active');
            const input = label.querySelector('input[type="radio"]');
            if (input) {
                input.checked = true;
                handleItemFieldTypeChange(itemId, input.value);
            }
        }
    });
}

function handleItemFieldTypeChange(itemId, fieldType) {
    const optionsContainer = document.getElementById('options_' + itemId);
    const measurementFields = document.getElementById('measurement_' + itemId);
    
    if (['radio', 'select', 'checkbox'].includes(fieldType)) {
        optionsContainer.style.display = 'block';
        const optionsList = document.getElementById('options_list_' + itemId);
        if (optionsList.children.length === 0) {
            addItemOption(itemId);
            addItemOption(itemId);
        }
    } else {
        optionsContainer.style.display = 'none';
    }
    
    if (measurementFields) {
        measurementFields.style.display = fieldType === 'measurement' ? 'block' : 'none';
    }
}

function addItemOption(itemId) {
    if (!optionCounters[itemId]) optionCounters[itemId] = 0;
    const optionId = optionCounters[itemId]++;
    
    const optionsList = document.getElementById('options_list_' + itemId);
    const div = document.createElement('div');
    div.className = 'option-item';
    div.innerHTML = `
        <input type="text" name="items[${itemId}][options][${optionId}][label]" class="form-control" 
               placeholder="Option ${optionId + 1}" required>
        <input type="hidden" name="items[${itemId}][options][${optionId}][value]" value="">
        <button type="button" class="option-remove" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    optionsList.appendChild(div);
}

function removeItem(btn) {
    const itemContainer = btn.closest('.item-container');
    if (confirm('Möchten Sie diesen Prüfpunkt wirklich entfernen?')) {
        itemContainer.remove();
    }
}

function openEditModal(checklistId) {
    fetch(`?ajax=get_checklist&id=${checklistId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_checklist_id').value = data.id;
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_category').value = data.category || '';
            document.getElementById('edit_is_template').checked = data.is_template == 1;
            document.getElementById('edit_is_dguv').checked = data.is_dguv_compliant == 1;
            document.getElementById('editModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Fehler beim Laden:', error);
            alert('Fehler beim Laden der Checkliste');
        });
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openItemsModal(checklistId, checklistName) {
    document.getElementById('items_checklist_id').value = checklistId;
    document.getElementById('items_modal_title').innerHTML = `<i class="fas fa-tasks"></i> ${checklistName} - Prüfpunkte`;
    
    fetch(`?ajax=get_items&checklist_id=${checklistId}`)
        .then(response => response.json())
        .then(items => {
            let html = '';
            if (items.length === 0) {
                html = '<div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;"><i class="fas fa-info-circle" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i><p style="color: #999; margin: 0;">Noch keine Prüfpunkte vorhanden</p></div>';
            } else {
                items.forEach((item, index) => {
                    let badges = '';
                    if (item.is_required == 1) badges += '<span class="item-badge required"><i class="fas fa-asterisk"></i> Pflicht</span>';
                    if (item.requires_photo == 1) badges += '<span class="item-badge"><i class="fas fa-camera"></i> Foto</span>';
                    
                    // Feldtyp-Badge
                    const fieldTypeLabels = {
                        'checkbox': 'Checkbox',
                        'radio': 'Radio',
                        'select': 'Dropdown',
                        'text': 'Text',
                        'textarea': 'Textfeld',
                        'number': 'Zahl',
                        'date': 'Datum',
                        'measurement': 'Messwert'
                    };
                    const fieldTypeLabel = fieldTypeLabels[item.field_type] || item.field_type;
                    badges += `<span class="item-badge" style="background: #007bff; color: white;"><i class="fas fa-tag"></i> ${fieldTypeLabel}</span>`;
                    
                    if (item.requires_measurement == 1 || item.field_type === 'measurement') {
                        badges += '<span class="item-badge"><i class="fas fa-ruler"></i> ' + (item.measurement_unit || 'Messwert') + '</span>';
                    }
                    
                    html += `
                        <div class="item-row">
                            <div class="item-text">
                                <strong style="color: #007bff; margin-right: 8px;">${index + 1}.</strong> ${item.item_text}
                            </div>
                            <div class="item-badges">${badges}</div>
                            <div>
                                <form method="POST" onsubmit="return confirm('Prüfpunkt wirklich löschen?')" style="display: inline;">
                                    ${document.querySelector('[name="csrf_token"]').outerHTML}
                                    <input type="hidden" name="action" value="delete_item">
                                    <input type="hidden" name="item_id" value="${item.id}">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Löschen">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('items_list').innerHTML = html;
            document.getElementById('itemsModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Fehler beim Laden:', error);
            alert('Fehler beim Laden der Prüfpunkte');
        });
}

function closeItemsModal() {
    document.getElementById('itemsModal').style.display = 'none';
}

// Modal schließen bei Klick außerhalb
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>