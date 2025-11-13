<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('custom_fields_manage');

$message = '';
$messageType = '';

// Feld erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_field'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Ungültiges Sicherheitstoken';
        $messageType = 'danger';
    } else {
        $fieldName = trim($_POST['field_name'] ?? '');
        $fieldLabel = trim($_POST['field_label'] ?? '');
        $fieldType = $_POST['field_type'] ?? 'text';
        $required = isset($_POST['required']) ? 1 : 0;
        $fieldOptions = trim($_POST['field_options'] ?? ''); // Für select, radio, checkbox
        
        if (empty($fieldName) || empty($fieldLabel)) {
            $message = 'Name und Label sind erforderlich';
            $messageType = 'danger';
        } elseif (!preg_match('/^[a-z_]+$/', $fieldName)) {
            $message = 'Feldname darf nur Kleinbuchstaben und Unterstriche enthalten';
            $messageType = 'danger';
        } elseif (in_array($fieldType, ['select', 'radio', 'checkbox']) && empty($fieldOptions)) {
            $message = 'Für diesen Feldtyp müssen Auswahloptionen angegeben werden';
            $messageType = 'danger';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO custom_fields (field_name, field_label, field_type, required, field_options, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fieldName, $fieldLabel, $fieldType, $required, $fieldOptions, $_SESSION['user_id']]);
                
                logActivity('custom_field_created', "Feld '{$fieldLabel}' erstellt");
                
                $message = 'Feld erfolgreich erstellt!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Fehler: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// Feld löschen
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM custom_fields WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    
    logActivity('custom_field_deleted', "Custom Field ID {$_GET['delete']} gelöscht");
    
    $message = 'Feld gelöscht';
    $messageType = 'success';
}

// Alle Custom Fields laden
$stmt = $pdo->query("SELECT * FROM custom_fields ORDER BY display_order, id");
$fields = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <title>Custom Fields</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .field-type-description {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 10px;
            border-radius: 4px;
            display: none;
            font-size: 14px;
        }
        
        .field-type-description.active {
            display: block;
        }
        
        .field-options-group {
            display: none;
            margin-top: 10px;
        }
        
        .field-options-group.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-list"></i> Custom Fields verwalten</h1>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zurück
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <p><i class="fas fa-info-circle"></i> Custom Fields erscheinen bei der Marker-Erstellung als zusätzliche Eingabefelder.</p>
            </div>
            
            <div class="admin-grid">
                <div class="admin-section">
                    <h2>Neues Feld erstellen</h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label for="field_name">Feldname (technisch) *</label>
                            <input type="text" id="field_name" name="field_name" required
                                   pattern="[a-z_]+" placeholder="z.B. projekt_nr">
                            <small>Nur Kleinbuchstaben und Unterstriche</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="field_label">Beschriftung *</label>
                            <input type="text" id="field_label" name="field_label" required
                                   placeholder="z.B. Projekt-Nummer">
                        </div>
                        
                        <div class="form-group">
                            <label for="field_type">Feldtyp *</label>
                            <select id="field_type" name="field_type" required onchange="updateFieldTypeInfo()">
                                <optgroup label="Text-Felder">
                                    <option value="text">Text (einzeilig)</option>
                                    <option value="textarea">Text (mehrzeilig)</option>
                                    <option value="email">E-Mail</option>
                                    <option value="url">URL/Webseite</option>
                                    <option value="tel">Telefonnummer</option>
                                </optgroup>
                                <optgroup label="Zahlen & Datum">
                                    <option value="number">Zahl</option>
                                    <option value="date">Datum</option>
                                    <option value="time">Uhrzeit</option>
                                    <option value="datetime">Datum & Uhrzeit</option>
                                </optgroup>
                                <optgroup label="Auswahl-Felder">
                                    <option value="select">Dropdown (Select)</option>
                                    <option value="radio">Radio Buttons</option>
                                    <option value="checkbox">Checkboxen</option>
                                </optgroup>
                                <optgroup label="Sonstiges">
                                    <option value="file">Datei-Upload</option>
                                    <option value="color">Farbe</option>
                                </optgroup>
                            </select>
                        </div>
                        
                        <!-- Feldtyp-Beschreibungen -->
                        <div id="desc-text" class="field-type-description">
                            <strong>Text (einzeilig):</strong> Einfaches Textfeld für kurze Eingaben wie Namen, IDs oder Nummern.
                        </div>
                        <div id="desc-textarea" class="field-type-description">
                            <strong>Text (mehrzeilig):</strong> Größeres Textfeld für längere Beschreibungen oder Notizen.
                        </div>
                        <div id="desc-email" class="field-type-description">
                            <strong>E-Mail:</strong> Textfeld mit automatischer E-Mail-Validierung.
                        </div>
                        <div id="desc-url" class="field-type-description">
                            <strong>URL/Webseite:</strong> Textfeld für Webseiten-Links mit URL-Validierung.
                        </div>
                        <div id="desc-tel" class="field-type-description">
                            <strong>Telefonnummer:</strong> Textfeld für Telefonnummern (optimiert für mobile Geräte).
                        </div>
                        <div id="desc-number" class="field-type-description">
                            <strong>Zahl:</strong> Zahlenfeld für numerische Werte.
                        </div>
                        <div id="desc-date" class="field-type-description">
                            <strong>Datum:</strong> Datumsauswahl mit Kalender.
                        </div>
                        <div id="desc-time" class="field-type-description">
                            <strong>Uhrzeit:</strong> Zeitauswahl für Uhrzeitangaben.
                        </div>
                        <div id="desc-datetime" class="field-type-description">
                            <strong>Datum & Uhrzeit:</strong> Kombinierte Datums- und Zeitauswahl.
                        </div>
                        <div id="desc-select" class="field-type-description">
                            <strong>Dropdown (Select):</strong> Auswahlmenü mit einer wählbaren Option. Geben Sie unten die Optionen ein.
                        </div>
                        <div id="desc-radio" class="field-type-description">
                            <strong>Radio Buttons:</strong> Gruppe von Optionen, von denen genau eine gewählt werden kann.
                        </div>
                        <div id="desc-checkbox" class="field-type-description">
                            <strong>Checkboxen:</strong> Mehrere Auswahlmöglichkeiten, von denen beliebig viele gewählt werden können.
                        </div>
                        <div id="desc-file" class="field-type-description">
                            <strong>Datei-Upload:</strong> Ermöglicht das Hochladen von Dateien (Bilder, PDFs, etc.).
                        </div>
                        <div id="desc-color" class="field-type-description">
                            <strong>Farbe:</strong> Farbauswahl mit Color-Picker.
                        </div>
                        
                        <!-- Optionen für select, radio, checkbox -->
                        <div id="field-options-group" class="field-options-group form-group">
                            <label for="field_options">Auswahloptionen *</label>
                            <textarea id="field_options" name="field_options" rows="4" 
                                      placeholder="Eine Option pro Zeile, z.B.:&#10;Option 1&#10;Option 2&#10;Option 3"></textarea>
                            <small>Geben Sie jede Option in einer neuen Zeile ein</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="required">
                                <span>Pflichtfeld</span>
                            </label>
                        </div>
                        
                        <button type="submit" name="create_field" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Feld erstellen
                        </button>
                    </form>
                </div>
                
                <div class="admin-section">
                    <h2>Vorhandene Felder (<?= count($fields) ?>)</h2>
                    <?php if (empty($fields)): ?>
                        <p style="color: #6c757d;">Noch keine Custom Fields vorhanden</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Feldname</th>
                                    <th>Typ</th>
                                    <th>Pflicht</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $field): ?>
                                <tr>
                                    <td><strong><?= e($field['field_label']) ?></strong></td>
                                    <td><code><?= e($field['field_name']) ?></code></td>
                                    <td>
                                        <?php
                                        $typeIcons = [
                                            'text' => 'fa-text-width',
                                            'textarea' => 'fa-align-left',
                                            'email' => 'fa-envelope',
                                            'url' => 'fa-link',
                                            'tel' => 'fa-phone',
                                            'number' => 'fa-hashtag',
                                            'date' => 'fa-calendar',
                                            'time' => 'fa-clock',
                                            'datetime' => 'fa-calendar-clock',
                                            'select' => 'fa-list',
                                            'radio' => 'fa-circle-dot',
                                            'checkbox' => 'fa-check-square',
                                            'file' => 'fa-file-upload',
                                            'color' => 'fa-palette'
                                        ];
                                        $icon = $typeIcons[$field['field_type']] ?? 'fa-question';
                                        ?>
                                        <i class="fas <?= $icon ?>"></i> <?= e($field['field_type']) ?>
                                    </td>
                                    <td>
                                        <?php if ($field['required']): ?>
                                            <span class="badge badge-warning">Ja</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Nein</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?delete=<?= $field['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Feld wirklich löschen?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    
    <script>
        function updateFieldTypeInfo() {
            const fieldType = document.getElementById('field_type').value;
            const optionsGroup = document.getElementById('field-options-group');
            const optionsTextarea = document.getElementById('field_options');
            
            // Alle Beschreibungen ausblenden
            document.querySelectorAll('.field-type-description').forEach(el => {
                el.classList.remove('active');
            });
            
            // Aktuelle Beschreibung anzeigen
            const activeDesc = document.getElementById('desc-' + fieldType);
            if (activeDesc) {
                activeDesc.classList.add('active');
            }
            
            // Optionsfeld für select, radio, checkbox anzeigen
            if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                optionsGroup.classList.add('active');
                optionsTextarea.required = true;
            } else {
                optionsGroup.classList.remove('active');
                optionsTextarea.required = false;
                optionsTextarea.value = '';
            }
        }
        
        // Initial anzeigen
        updateFieldTypeInfo();
    </script>
</body>
</html>