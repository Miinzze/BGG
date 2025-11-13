<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('settings_manage');

$messeMarkerId = isset($_GET['mm_id']) ? intval($_GET['mm_id']) : 0;

// Messe-Marker laden
$stmt = $pdo->prepare("
    SELECT mm.*, m.name as marker_name, mc.name as messe_name 
    FROM messe_markers mm
    JOIN markers m ON mm.marker_id = m.id
    JOIN messe_config mc ON mm.messe_id = mc.id
    WHERE mm.id = ?
");
$stmt->execute([$messeMarkerId]);
$messeMarker = $stmt->fetch();

if (!$messeMarker) {
    die("Messe-Marker nicht gefunden.");
}

$success = '';
$error = '';

// Custom Field hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    validateCSRF();
    
    if ($_POST['action'] === 'add_field') {
        try {
            $stmt = $pdo->prepare("INSERT INTO messe_marker_fields (messe_marker_id, field_name, field_value, field_icon, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $messeMarkerId,
                $_POST['field_name'],
                $_POST['field_value'],
                $_POST['field_icon'] ?: null,
                $_POST['display_order'] ?: 0
            ]);
            $success = "Custom Field erfolgreich hinzugefügt!";
            logActivity($_SESSION['user_id'], 'custom_field_added', "Field '{$_POST['field_name']}' hinzugefügt");
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'delete_field') {
        try {
            $stmt = $pdo->prepare("DELETE FROM messe_marker_fields WHERE id = ? AND messe_marker_id = ?");
            $stmt->execute([$_POST['field_id'], $messeMarkerId]);
            $success = "Custom Field gelöscht!";
            logActivity($_SESSION['user_id'], 'custom_field_deleted', "Field ID {$_POST['field_id']} gelöscht");
        } catch (Exception $e) {
            $error = "Fehler: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'upload_3d_model') {
        if (isset($_FILES['model_file']) && $_FILES['model_file']['error'] === 0) {
            $allowed = ['glb', 'gltf', 'obj', 'stl'];
            $filename = $_FILES['model_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFilename = 'model_' . $messeMarkerId . '_' . time() . '.' . $ext;
                $uploadPath = 'uploads/3d_models/' . $newFilename;
                
                if (!is_dir('uploads/3d_models')) {
                    mkdir('uploads/3d_models', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['model_file']['tmp_name'], $uploadPath)) {
                    $stmt = $pdo->prepare("UPDATE messe_markers SET model_3d_path = ? WHERE id = ?");
                    $stmt->execute([$uploadPath, $messeMarkerId]);
                    $success = "3D-Modell erfolgreich hochgeladen!";
                    logActivity($_SESSION['user_id'], '3d_model_uploaded', "3D-Modell für Marker ID $messeMarkerId hochgeladen");
                    // Reload
                    header("Location: " . $_SERVER['PHP_SELF'] . "?mm_id=" . $messeMarkerId);
                    exit;
                } else {
                    $error = "Fehler beim Hochladen der Datei.";
                }
            } else {
                $error = "Nur .glb, .gltf, .obj und .stl Dateien erlaubt!";
            }
        }
    }
}

// Custom Fields laden
$stmt = $pdo->prepare("SELECT * FROM messe_marker_fields WHERE messe_marker_id = ? ORDER BY display_order ASC");
$stmt->execute([$messeMarkerId]);
$customFields = $stmt->fetchAll();

// Font Awesome Icons Liste
$icons = ['bolt', 'weight', 'ruler', 'tachometer-alt', 'temperature-high', 'battery-full', 'cog', 'tools', 'wrench', 'screwdriver', 'power-off', 'plug', 'microchip', 'signal', 'wifi', 'ethernet', 'database', 'server', 'hard-drive', 'memory', 'speedometer', 'gauge'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Fields & 3D-Modell - <?= htmlspecialchars($messeMarker['marker_name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .panel {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .panel h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .fields-list {
            margin: 20px 0;
        }
        .field-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .field-item .icon {
            font-size: 24px;
            color: #667eea;
            width: 40px;
        }
        .field-item .content {
            flex: 1;
            margin: 0 15px;
        }
        .field-item .name {
            font-weight: bold;
            color: #333;
        }
        .field-item .value {
            color: #666;
            font-size: 14px;
        }
        .icon-picker {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 10px;
            margin: 15px 0;
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .icon-picker-item {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .icon-picker-item:hover {
            background: #f8f9fa;
            border-color: #667eea;
        }
        .icon-picker-item.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .upload-area {
            border: 3px dashed #667eea;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin: 20px 0;
        }
        .upload-area:hover {
            background: #f8f9fa;
            border-color: #764ba2;
        }
        .upload-area .icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .model-preview {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin: 20px 0;
        }
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container" style="max-width: 1400px; margin: 20px auto; padding: 0 20px;">
        <div class="header-info">
            <h1><i class="fas fa-edit"></i> Custom Fields & 3D-Modell</h1>
            <p style="margin-top: 10px; font-size: 18px;">
                <strong>Messe:</strong> <?= htmlspecialchars($messeMarker['messe_name']) ?><br>
                <strong>Gerät:</strong> <?= htmlspecialchars($messeMarker['marker_name']) ?>
            </p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="content-grid">
            <!-- Custom Fields -->
            <div class="panel">
                <h2><i class="fas fa-list"></i> Custom Fields</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    Erstelle eigene Felder mit benutzerdefinierten Namen, um Messebesuchern detaillierte Infos zu geben.
                </p>
                
                <button onclick="document.getElementById('addFieldModal').style.display='block'" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Neues Field hinzufügen
                </button>
                
                <div class="fields-list">
                    <?php if (empty($customFields)): ?>
                        <p style="text-align: center; color: #999; padding: 40px 0;">
                            <i class="fas fa-info-circle"></i><br>
                            Noch keine Custom Fields vorhanden.<br>
                            Füge jetzt welche hinzu!
                        </p>
                    <?php else: ?>
                        <?php foreach ($customFields as $field): ?>
                            <div class="field-item">
                                <div class="icon">
                                    <?php if ($field['field_icon']): ?>
                                        <i class="fas fa-<?= htmlspecialchars($field['field_icon']) ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-tag"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="content">
                                    <div class="name"><?= htmlspecialchars($field['field_name']) ?></div>
                                    <div class="value"><?= htmlspecialchars($field['field_value']) ?></div>
                                </div>
                                <form method="POST" onsubmit="return confirm('Wirklich löschen?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_field">
                                    <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 3D-Modell -->
            <div class="panel">
                <h2><i class="fas fa-cube"></i> 3D-Modell</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    Lade ein 3D-Modell des Geräts hoch, damit Besucher es interaktiv betrachten können.
                </p>
                
                <?php if ($messeMarker['model_3d_path']): ?>
                    <div class="model-preview">
                        <div style="text-align: center;">
                            <i class="fas fa-check-circle" style="font-size: 60px;"></i>
                            <p>3D-Modell vorhanden</p>
                            <small><?= basename($messeMarker['model_3d_path']) ?></small>
                        </div>
                    </div>
                    <form method="POST" onsubmit="return confirm('Altes Modell wird überschrieben!')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_3d_model">
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Modell löschen
                        </button>
                    </form>
                    <br>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="upload_3d_model">
                    
                    <div class="upload-area" onclick="document.getElementById('modelFile').click()">
                        <div class="icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3>3D-Modell hochladen</h3>
                        <p style="color: #666; margin-top: 10px;">
                            Klicken oder Datei hierher ziehen<br>
                            <small>Unterstützt: .glb, .gltf, .obj, .stl</small>
                        </p>
                        <input type="file" id="modelFile" name="model_file" accept=".glb,.gltf,.obj,.stl" style="display: none;" onchange="document.getElementById('uploadForm').submit()">
                    </div>
                </form>
                
                <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <h4><i class="fas fa-info-circle"></i> Tipps für 3D-Modelle:</h4>
                    <ul style="margin: 10px 0 0 20px; color: #666;">
                        <li>.glb oder .gltf bevorzugt (beste Kompatibilität)</li>
                        <li>Dateigröße unter 10 MB für schnelles Laden</li>
                        <li>Optimierte/vereinfachte Modelle für Web</li>
                        <li>Mit Texturen für realistisches Aussehen</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal: Field hinzufügen -->
    <div id="addFieldModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="document.getElementById('addFieldModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-plus-circle"></i> Neues Custom Field</h2>
            
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_field">
                
                <div class="form-group">
                    <label>Field-Name * (z.B. "Leistung", "Gewicht", "Geschwindigkeit")</label>
                    <input type="text" name="field_name" required class="form-control" placeholder="z.B. Leistung">
                </div>
                
                <div class="form-group">
                    <label>Wert * (z.B. "500 kW", "2.5 Tonnen")</label>
                    <input type="text" name="field_value" required class="form-control" placeholder="z.B. 500 kW">
                </div>
                
                <div class="form-group">
                    <label>Icon auswählen (optional)</label>
                    <input type="hidden" name="field_icon" id="selectedIcon">
                    <div class="icon-picker" id="iconPicker">
                        <?php foreach ($icons as $icon): ?>
                            <div class="icon-picker-item" data-icon="<?= $icon ?>" onclick="selectIcon('<?= $icon ?>')">
                                <i class="fas fa-<?= $icon ?>"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Anzeigereihenfolge</label>
                    <input type="number" name="display_order" class="form-control" value="0" min="0">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Field hinzufügen
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function selectIcon(icon) {
            document.getElementById('selectedIcon').value = icon;
            
            document.querySelectorAll('.icon-picker-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            event.target.closest('.icon-picker-item').classList.add('selected');
        }
        
        // Modal schließen
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Drag & Drop für 3D Upload
        const uploadArea = document.querySelector('.upload-area');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.background = '#f8f9fa';
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.background = '';
            }, false);
        });
        
        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            document.getElementById('modelFile').files = files;
            document.getElementById('uploadForm').submit();
        }, false);
    </script>
</body>
</html>