<?php
require_once 'config.php';
requireLogin();

$pageTitle = 'Wartungssätze verwalten';

// GET Parameter
$action = $_GET['action'] ?? 'list';
$setId = $_GET['id'] ?? null;

// POST Aktionen verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    if (isset($_POST['create_set'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        $stmt = $pdo->prepare("INSERT INTO maintenance_sets (name, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $_SESSION['user_id']]);
        
        $_SESSION['success_message'] = "Wartungssatz wurde erfolgreich erstellt.";
        header('Location: maintenance_sets.php');
        exit;
    }
    
    if (isset($_POST['update_set'])) {
        $id = $_POST['set_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        $stmt = $pdo->prepare("UPDATE maintenance_sets SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        
        $_SESSION['success_message'] = "Wartungssatz wurde erfolgreich aktualisiert.";
        header('Location: maintenance_sets.php');
        exit;
    }
    
    if (isset($_POST['delete_set'])) {
        $id = $_POST['set_id'];
        
        // Prüfen ob Wartungssatz in Verwendung
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM markers WHERE maintenance_set_id = ?");
        $stmt->execute([$id]);
        $inUse = $stmt->fetchColumn();
        
        if ($inUse > 0) {
            $_SESSION['error_message'] = "Wartungssatz kann nicht gelöscht werden, da er noch {$inUse} Marker(n) zugewiesen ist.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM maintenance_sets WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success_message'] = "Wartungssatz wurde erfolgreich gelöscht.";
        }
        
        header('Location: maintenance_sets.php');
        exit;
    }
    
    if (isset($_POST['add_field'])) {
        $setId = $_POST['set_id'];
        $fieldLabel = trim($_POST['field_label']);
        $fieldType = $_POST['field_type'];
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $fieldOptions = $_POST['field_options'] ?? null;
        
        // Sortierung ermitteln
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM maintenance_set_fields WHERE maintenance_set_id = ?");
        $stmt->execute([$setId]);
        $sortOrder = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("INSERT INTO maintenance_set_fields (maintenance_set_id, field_label, field_type, field_options, is_required, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$setId, $fieldLabel, $fieldType, $fieldOptions, $isRequired, $sortOrder]);
        
        $_SESSION['success_message'] = "Feld wurde erfolgreich hinzugefügt.";
        header('Location: maintenance_sets.php?action=edit&id=' . $setId);
        exit;
    }
    
    if (isset($_POST['delete_field'])) {
        $fieldId = $_POST['field_id'];
        $setId = $_POST['set_id'];
        
        $stmt = $pdo->prepare("DELETE FROM maintenance_set_fields WHERE id = ?");
        $stmt->execute([$fieldId]);
        
        $_SESSION['success_message'] = "Feld wurde erfolgreich gelöscht.";
        header('Location: maintenance_sets.php?action=edit&id=' . $setId);
        exit;
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
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .content-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .field-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #007bff;
            margin-bottom: 10px;
        }
        
        .field-item:hover {
            background: #e9ecef;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" style="padding: 15px; background: #d4edda; border-left: 4px solid #28a745; margin-bottom: 20px; border-radius: 5px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" style="padding: 15px; background: #f8d7da; border-left: 4px solid #dc3545; margin-bottom: 20px; border-radius: 5px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if ($action === 'list'): ?>
                <!-- Liste der Wartungssätze -->
                <div class="page-header">
                    <h1><i class="fas fa-tools"></i> Wartungssätze</h1>
                    <div class="header-actions">
                        <a href="?action=create" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Neuer Wartungssatz
                        </a>
                    </div>
                </div>
                
                <?php
                $stmt = $pdo->query("
                    SELECT ms.*, 
                           COUNT(DISTINCT msf.id) as field_count,
                           COUNT(DISTINCT m.id) as marker_count,
                           u.username as created_by_name
                    FROM maintenance_sets ms
                    LEFT JOIN maintenance_set_fields msf ON ms.id = msf.maintenance_set_id
                    LEFT JOIN markers m ON ms.id = m.maintenance_set_id
                    LEFT JOIN users u ON ms.created_by = u.id
                    GROUP BY ms.id
                    ORDER BY ms.name ASC
                ");
                $sets = $stmt->fetchAll();
                
                // Statistiken
                $totalSets = count($sets);
                $totalFields = array_sum(array_column($sets, 'field_count'));
                $totalUsages = array_sum(array_column($sets, 'marker_count'));
                $unusedSets = count(array_filter($sets, function($s) { return $s['marker_count'] == 0; }));
                ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-tools" style="color: #007bff;"></i>
                        <div class="stat-value"><?= $totalSets ?></div>
                        <div class="stat-label">Wartungssätze</div>
                    </div>
                    
                    <div class="stat-card" style="border-left-color: #28a745;">
                        <i class="fas fa-list" style="color: #28a745;"></i>
                        <div class="stat-value"><?= $totalFields ?></div>
                        <div class="stat-label">Felder insgesamt</div>
                    </div>
                    
                    <div class="stat-card" style="border-left-color: #17a2b8;">
                        <i class="fas fa-qrcode" style="color: #17a2b8;"></i>
                        <div class="stat-value"><?= $totalUsages ?></div>
                        <div class="stat-label">Verwendungen</div>
                    </div>
                    
                    <div class="stat-card" style="border-left-color: #ffc107;">
                        <i class="fas fa-inbox" style="color: #ffc107;"></i>
                        <div class="stat-value"><?= $unusedSets ?></div>
                        <div class="stat-label">Ungenutzt</div>
                    </div>
                </div>
                
                <div class="content-card">
                    <?php if (empty($sets)): ?>
                        <p style="text-align: center; color: #6c757d; padding: 40px;">
                            <i class="fas fa-info-circle" style="font-size: 3rem; display: block; margin-bottom: 15px;"></i>
                            Noch keine Wartungssätze vorhanden. Erstelle deinen ersten Wartungssatz!
                        </p>
                    <?php else: ?>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 12px; text-align: left;">Name</th>
                                    <th style="padding: 12px; text-align: left;">Beschreibung</th>
                                    <th style="padding: 12px; text-align: center;">Felder</th>
                                    <th style="padding: 12px; text-align: center;">Verwendungen</th>
                                    <th style="padding: 12px; text-align: left;">Erstellt</th>
                                    <th style="padding: 12px; text-align: center;">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sets as $set): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 12px;">
                                            <strong><?= htmlspecialchars($set['name']) ?></strong>
                                        </td>
                                        <td style="padding: 12px; color: #6c757d;">
                                            <?= htmlspecialchars($set['description'] ?? '-') ?>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <span style="background: #e7f3ff; color: #007bff; padding: 4px 8px; border-radius: 12px; font-size: 13px;">
                                                <?= $set['field_count'] ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <?php if ($set['marker_count'] > 0): ?>
                                                <span style="background: #d4edda; color: #28a745; padding: 4px 8px; border-radius: 12px; font-size: 13px;">
                                                    <?= $set['marker_count'] ?> Marker
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #6c757d;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 12px;">
                                            <?= date('d.m.Y', strtotime($set['created_at'])) ?><br>
                                            <small style="color: #6c757d;">von <?= htmlspecialchars($set['created_by_name'] ?? 'System') ?></small>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <a href="?action=edit&id=<?= $set['id'] ?>" class="btn btn-sm btn-primary" style="margin-right: 5px;">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <?php if ($set['marker_count'] == 0): ?>
                                                <button onclick="if(confirm('Wartungssatz wirklich löschen?')) { document.getElementById('deleteForm<?= $set['id'] ?>').submit(); }" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <form id="deleteForm<?= $set['id'] ?>" method="POST" style="display: none;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="set_id" value="<?= $set['id'] ?>">
                                                    <input type="hidden" name="delete_set" value="1">
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($action === 'create'): ?>
                <!-- Wartungssatz erstellen -->
                <div class="page-header">
                    <h1><i class="fas fa-plus-circle"></i> Neuer Wartungssatz</h1>
                    <div class="header-actions">
                        <a href="maintenance_sets.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Zurück
                        </a>
                    </div>
                </div>
                
                <div class="content-card">
                    <form method="POST">
                        <?= csrf_field() ?>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Name *</label>
                            <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Beschreibung</label>
                            <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;"></textarea>
                        </div>
                        
                        <button type="submit" name="create_set" class="btn btn-primary">
                            <i class="fas fa-save"></i> Wartungssatz erstellen
                        </button>
                    </form>
                </div>
                
            <?php elseif ($action === 'edit' && $setId): ?>
                <!-- Wartungssatz bearbeiten -->
                <?php
                $stmt = $pdo->prepare("SELECT * FROM maintenance_sets WHERE id = ?");
                $stmt->execute([$setId]);
                $set = $stmt->fetch();
                
                if (!$set) {
                    echo '<div class="alert alert-danger">Wartungssatz nicht gefunden.</div>';
                    include 'footer.php';
                    exit;
                }
                
                $stmt = $pdo->prepare("SELECT * FROM maintenance_set_fields WHERE maintenance_set_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$setId]);
                $fields = $stmt->fetchAll();
                ?>
                
                <div class="page-header">
                    <h1><i class="fas fa-edit"></i> Wartungssatz bearbeiten</h1>
                    <div class="header-actions">
                        <a href="maintenance_sets.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Zurück zur Liste
                        </a>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="content-card">
                        <h3 style="margin-top: 0; color: #2c3e50;">
                            <i class="fas fa-info-circle"></i> Grunddaten
                        </h3>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="set_id" value="<?= $set['id'] ?>">
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Name *</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($set['name']) ?>" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Beschreibung</label>
                                <textarea name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;"><?= htmlspecialchars($set['description'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_set" class="btn btn-primary">
                                <i class="fas fa-save"></i> Speichern
                            </button>
                        </form>
                    </div>
                    
                    <div class="content-card">
                        <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
                            <i class="fas fa-list"></i> Felder
                            <a href="?action=add_field&id=<?= $setId ?>" class="btn btn-sm btn-primary" style="float: right;">
                                <i class="fas fa-plus"></i> Feld hinzufügen
                            </a>
                        </h3>
                        
                        <?php if (empty($fields)): ?>
                            <p style="text-align: center; color: #6c757d; padding: 20px;">
                                <i class="fas fa-info-circle"></i> Noch keine Felder vorhanden.
                            </p>
                        <?php else: ?>
                            <?php foreach ($fields as $field): ?>
                                <div class="field-item">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex-grow: 1;">
                                            <strong style="color: #2c3e50;"><?= htmlspecialchars($field['field_label']) ?></strong>
                                            <br>
                                            <small style="color: #6c757d;">
                                                Typ: <?= htmlspecialchars($field['field_type']) ?>
                                                <?= $field['is_required'] ? ' | <span style="color: #dc3545;">Pflichtfeld</span>' : '' ?>
                                            </small>
                                        </div>
                                        <div>
                                            <button onclick="if(confirm('Feld wirklich löschen? Alle gespeicherten Werte gehen verloren.')) { document.getElementById('deleteFieldForm<?= $field['id'] ?>').submit(); }" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="deleteFieldForm<?= $field['id'] ?>" method="POST" style="display: none;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                                <input type="hidden" name="set_id" value="<?= $setId ?>">
                                                <input type="hidden" name="delete_field" value="1">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($action === 'add_field' && $setId): ?>
                <!-- Feld hinzufügen -->
                <?php
                $stmt = $pdo->prepare("SELECT name FROM maintenance_sets WHERE id = ?");
                $stmt->execute([$setId]);
                $setName = $stmt->fetchColumn();
                ?>
                
                <div class="page-header">
                    <h1><i class="fas fa-plus"></i> Feld hinzufügen</h1>
                    <div class="header-actions">
                        <a href="?action=edit&id=<?= $setId ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Zurück
                        </a>
                    </div>
                </div>
                
                <div class="content-card">
                    <h3 style="color: #2c3e50;">Wartungssatz: <?= htmlspecialchars($setName) ?></h3>
                    
                    <form method="POST" style="max-width: 600px; margin-top: 20px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="set_id" value="<?= $setId ?>">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Feldbezeichnung *</label>
                            <input type="text" name="field_label" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Feldtyp *</label>
                            <select name="field_type" id="field_type" onchange="toggleFieldOptions()" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;">
                                <option value="text">Text (einzeilig)</option>
                                <option value="textarea">Text (mehrzeilig)</option>
                                <option value="number">Zahl</option>
                                <option value="date">Datum</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="select">Auswahl (Select)</option>
                            </select>
                        </div>
                        
                        <div id="field_options_div" style="display: none; margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Auswahloptionen (eine pro Zeile)</label>
                            <textarea name="field_options" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;"></textarea>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="is_required" style="margin-right: 8px;">
                                <span style="font-weight: 600;">Pflichtfeld</span>
                            </label>
                        </div>
                        
                        <button type="submit" name="add_field" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Feld hinzufügen
                        </button>
                    </form>
                </div>
                
                <script>
                function toggleFieldOptions() {
                    const fieldType = document.getElementById('field_type').value;
                    const optionsDiv = document.getElementById('field_options_div');
                    optionsDiv.style.display = (fieldType === 'select') ? 'block' : 'none';
                }
                </script>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>