<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('settings_manage');

$message = '';
$messageType = '';
$settings = getSystemSettings();

// Logo hochladen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_logo'])) {
    if (!empty($_FILES['logo']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $maxSize = 2 * 1024 * 1024;
        
        if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
            $message = 'Ungültiger Dateityp';
            $messageType = 'danger';
        } elseif ($_FILES['logo']['size'] > $maxSize) {
            $message = 'Datei zu groß';
            $messageType = 'danger';
        } else {
            $uploadDir = 'uploads/system/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            $oldLogo = $settings['system_logo'] ?? '';
            if ($oldLogo && file_exists($oldLogo)) {
                unlink($oldLogo);
            }
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
                saveSetting('system_logo', $filepath);
                $message = 'Logo hochgeladen!';
                $messageType = 'success';
                $settings = getSystemSettings();
            }
        }
    }
}

// Logo löschen
if (isset($_GET['delete_logo'])) {
    $oldLogo = $settings['system_logo'] ?? '';
    if ($oldLogo && file_exists($oldLogo)) {
        unlink($oldLogo);
    }
    saveSetting('system_logo', '');
    $message = 'Logo gelöscht';
    $messageType = 'success';
    $settings = getSystemSettings();
}

// ALLE Einstellungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_settings'])) {
    $systemName = trim($_POST['system_name'] ?? 'RFID Marker System');
    $mapLat = $_POST['map_default_lat'] ?? 49.995567;
    $mapLng = $_POST['map_default_lng'] ?? 9.0731267;
    $mapZoom = $_POST['map_default_zoom'] ?? 15;
    $maintenanceDays = $_POST['maintenance_check_days_before'] ?? 7;
    $inspectionDays = $_POST['inspection_check_days_before'] ?? 30;
    $emailFrom = trim($_POST['email_from'] ?? '');
    $emailFromName = trim($_POST['email_from_name'] ?? 'RFID System');
    
    // Marker-Farben
    $markerColorAvailable = trim($_POST['marker_color_available'] ?? '#3388ff');
    $markerColorRented = trim($_POST['marker_color_rented'] ?? '#ffc107');
    $markerColorMaintenance = trim($_POST['marker_color_maintenance'] ?? '#dc3545');
    $markerColorStorage = trim($_POST['marker_color_storage'] ?? '#28a745');
    $markerColorMultidevice = trim($_POST['marker_color_multidevice'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
    $markerColorCustomer = trim($_POST['marker_color_customer'] ?? '#17a2b8');
    $markerColorRepair = trim($_POST['marker_color_repair'] ?? '#fd7e14');
    $markerColorMesse = trim($_POST['marker_color_messe'] ?? '#9c27b0');

    // ===== INPUT VALIDIERUNG =====
    
    if (!validateStringLength($systemName, 1, 100)) {
        $message = 'Systemname ungültig';
        $messageType = 'danger';
    } elseif (!validateCoordinates($mapLat, $mapLng)) {
        $message = 'Ungültige Karten-Koordinaten';
        $messageType = 'danger';
    } elseif (!validateZoomLevel($mapZoom)) {
        $message = 'Zoom-Level muss zwischen 1 und 19 liegen';
        $messageType = 'danger';
    } elseif (!validateInteger($maintenanceDays, 1, 30)) {
        $message = 'Wartungserinnerung muss zwischen 1 und 30 Tagen liegen';
        $messageType = 'danger';
    } elseif (!validateInteger($inspectionDays, 1, 90)) {
        $message = 'Prüfungserinnerung muss zwischen 1 und 90 Tagen liegen';
        $messageType = 'danger';
    } elseif (!empty($emailFrom) && !validateEmail($emailFrom)) {
        $message = 'Ungültige E-Mail-Adresse';
        $messageType = 'danger';
    } elseif (!validateStringLength($emailFromName, 1, 100)) {
        $message = 'E-Mail-Absendername ungültig';
        $messageType = 'danger';
    } else {
        saveSetting('system_name', $systemName);
        saveSetting('show_map_legend', isset($_POST['show_map_legend']) ? '1' : '0');
        saveSetting('show_system_messages', isset($_POST['show_system_messages']) ? '1' : '0');
        saveSetting('maintenance_check_days_before', intval($maintenanceDays));
        saveSetting('inspection_check_days_before', intval($inspectionDays));
        saveSetting('marker_size', $_POST['marker_size'] ?? 'medium');
        saveSetting('marker_pulse', isset($_POST['marker_pulse']) ? '1' : '0');
        saveSetting('marker_hover_scale', isset($_POST['marker_hover_scale']) ? '1' : '0');
        saveSetting('map_default_lat', floatval($mapLat));
        saveSetting('map_default_lng', floatval($mapLng));
        saveSetting('map_default_zoom', intval($mapZoom));
        saveSetting('email_enabled', isset($_POST['email_enabled']) ? '1' : '0');
        saveSetting('email_from', $emailFrom);
        saveSetting('email_from_name', $emailFromName);
        
        // Marker-Farben speichern
        saveSetting('marker_color_available', $markerColorAvailable);
        saveSetting('marker_color_rented', $markerColorRented);
        saveSetting('marker_color_maintenance', $markerColorMaintenance);
        saveSetting('marker_color_storage', $markerColorStorage);
        saveSetting('marker_color_multidevice', $markerColorMultidevice);
        saveSetting('marker_color_customer', $markerColorCustomer);
        saveSetting('marker_color_repair', $markerColorRepair);
        saveSetting('marker_color_messe', $markerColorMesse);

        // Neue Einstellungen für Geo-Fencing und Routing
        saveSetting('marker_color_finished', trim($_POST['marker_color_finished'] ?? '#6c757d'));
        saveSetting('marker_icon_finished', trim($_POST['marker_icon_finished'] ?? 'grey'));
        saveSetting('show_geofences_on_map', isset($_POST['show_geofences_on_map']) ? '1' : '0');
        saveSetting('enable_routing', isset($_POST['enable_routing']) ? '1' : '0');
        saveSetting('enforce_geofence_rules', isset($_POST['enforce_geofence_rules']) ? '1' : '0');
        
        $settings = getSystemSettings();
        $message = 'Alle Einstellungen gespeichert!';
        $messageType = 'success';
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
    <title>Einstellungen</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            margin-top: 2px;
        }
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            flex: 1;
        }
        .logo-preview-box {
            display: inline-flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        .logo-preview-box img {
            max-height: 60px;
            max-width: 200px;
            object-fit: contain;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .color-picker-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 12px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .color-picker-row input[type="color"] {
            width: 50px;
            height: 40px;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
        }
        .color-picker-row input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .color-picker-row label {
            min-width: 180px;
            font-weight: 500;
        }

        /* User Dropdown Container */
        .setting-dropdown-container {
            position: relative;
        }

        .setting-dropdown-btn {
            background: #2c3e50;
            border: 2px solid #2c3e50;
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .setting-dropdown-btn:hover {
            background: #2c3e50;
            border-color: #2c3e50;
        }

        .setting-dropdown-btn .username-text {
            font-weight: 500;
        }

        .setting-dropdown-btn .badge {
            font-size: 11px;
            padding: 3px 8px;
        }

        .setting-dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #ffffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 250px;
            display: none;
            z-index: 1000;
        }

        .setting-dropdown-menu.show {
            display: block;
            animation: dropdownFade 0.2s ease;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-cog"></i> Einstellungen</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <!-- LOGO UPLOAD - GANZ OBEN -->
            <div class="setting-group">
                <h3><i class="fas fa-image"></i> System-Logo</h3>
                
                <?php if (!empty($settings['system_logo']) && file_exists($settings['system_logo'])): ?>
                    <div class="logo-preview-box">
                        <img src="<?= htmlspecialchars($settings['system_logo']) ?>?v=<?= time() ?>" alt="Logo">
                        <a href="?delete_logo=1" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Logo löschen?')">
                            <i class="fas fa-trash"></i> Löschen
                        </a>
                    </div>
                <?php else: ?>
                    <p style="color: #999; margin: 15px 0;">Kein Logo vorhanden</p>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="form-group">
                        <label for="logo"><i class="fas fa-upload"></i> Logo hochladen (200x60px empfohlen)</label>
                        <input type="file" id="logo" name="logo" accept="image/*" required>
                        <small>JPG, PNG, GIF, SVG (max. 2MB)</small>
                    </div>
                    <button type="submit" name="upload_logo" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Hochladen
                    </button>
                </form>
            </div>
            
            <!-- HAUPTFORMULAR -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="setting-group">
                    <h3><i class="fas fa-info-circle"></i> System</h3>
                    <div class="form-group">
                        <label for="system_name">Systemname</label>
                        <input type="text" id="system_name" name="system_name" 
                               value="<?= htmlspecialchars($settings['system_name'] ?? 'RFID Marker System') ?>">
                    </div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-eye"></i> Anzeige</h3>
                    <div class="checkbox-group">
                        <input type="checkbox" id="show_map_legend" name="show_map_legend" 
                               <?= ($settings['show_map_legend'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label for="show_map_legend"><strong>Kartenlegende anzeigen</strong></label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="show_system_messages" name="show_system_messages" 
                               <?= ($settings['show_system_messages'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label for="show_system_messages"><strong>Systemnachrichten anzeigen</strong></label>
                    </div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-wrench"></i> Wartung & Prüfungen</h3>
                    <div class="form-group">
                        <label for="maintenance_check_days_before">Wartungs-Erinnerung (Tage vorher)</label>
                        <input type="number" id="maintenance_check_days_before" name="maintenance_check_days_before" 
                               value="<?= htmlspecialchars($settings['maintenance_check_days_before'] ?? '7') ?>" 
                               min="1" max="30">
                        <small>Anzahl der Tage vor fälliger Wartung für E-Mail-Benachrichtigungen</small>
                    </div>
                    <div class="form-group">
                        <label for="inspection_check_days_before">Prüfungs-Erinnerung (Tage vorher)</label>
                        <input type="number" id="inspection_check_days_before" name="inspection_check_days_before" 
                               value="<?= htmlspecialchars($settings['inspection_check_days_before'] ?? '30') ?>" 
                               min="1" max="90">
                        <small>Anzahl der Tage vor fälliger DGUV/UVV/TÜV Prüfung für E-Mail-Benachrichtigungen</small>
                    </div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-palette"></i> Marker-Farben (Kartenlegende)</h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> Passen Sie die Farben der Marker auf der Karte an.
                        Für Verlaufsfarben verwenden Sie CSS-Gradient-Syntax.
                    </p>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_available">Verfügbar</label>
                        <input type="color" id="marker_color_available_picker" value="<?= htmlspecialchars(substr($settings['marker_color_available'] ?? '#3388ff', 0, 7)) ?>">
                        <input type="text" id="marker_color_available" name="marker_color_available" 
                               value="<?= htmlspecialchars($settings['marker_color_available'] ?? '#3388ff') ?>">
                    </div>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_rented">Vermietet</label>
                        <input type="color" id="marker_color_rented_picker" value="<?= htmlspecialchars(substr($settings['marker_color_rented'] ?? '#ffc107', 0, 7)) ?>">
                        <input type="text" id="marker_color_rented" name="marker_color_rented" 
                               value="<?= htmlspecialchars($settings['marker_color_rented'] ?? '#ffc107') ?>">
                    </div>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_maintenance">Wartung</label>
                        <input type="color" id="marker_color_maintenance_picker" value="<?= htmlspecialchars(substr($settings['marker_color_maintenance'] ?? '#dc3545', 0, 7)) ?>">
                        <input type="text" id="marker_color_maintenance" name="marker_color_maintenance" 
                               value="<?= htmlspecialchars($settings['marker_color_maintenance'] ?? '#dc3545') ?>">
                    </div>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_storage">Lager</label>
                        <input type="color" id="marker_color_storage_picker" value="<?= htmlspecialchars(substr($settings['marker_color_storage'] ?? '#28a745', 0, 7)) ?>">
                        <input type="text" id="marker_color_storage" name="marker_color_storage" 
                               value="<?= htmlspecialchars($settings['marker_color_storage'] ?? '#28a745') ?>">
                    </div>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_multidevice">Mehrgerät</label>
                        <input type="color" id="marker_color_multidevice_picker" value="#667eea" disabled>
                        <input type="text" id="marker_color_multidevice" name="marker_color_multidevice" 
                               value="<?= htmlspecialchars($settings['marker_color_multidevice'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') ?>"
                               placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
                    </div>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_customer">Kundengeräte</label>
                        <input type="color" id="marker_color_customer_picker" value="<?= htmlspecialchars(substr($settings['marker_color_customer'] ?? '#17a2b8', 0, 7)) ?>">
                        <input type="text" id="marker_color_customer" name="marker_color_customer" 
                               value="<?= htmlspecialchars($settings['marker_color_customer'] ?? '#17a2b8') ?>">
                    </div>
                    
                    <div class="color-picker-row">
                        <label for="marker_color_repair">Reparaturen</label>
                        <input type="color" id="marker_color_repair_picker" value="<?= htmlspecialchars(substr($settings['marker_color_repair'] ?? '#fd7e14', 0, 7)) ?>">
                        <input type="text" id="marker_color_repair" name="marker_color_repair"
                               value="<?= htmlspecialchars($settings['marker_color_repair'] ?? '#fd7e14') ?>">
                    </div>

                    <div class="color-picker-row">
                        <label for="marker_color_messe">Auf Messe</label>
                        <input type="color" id="marker_color_messe_picker" value="<?= htmlspecialchars(substr($settings['marker_color_messe'] ?? '#9c27b0', 0, 7)) ?>">
                        <input type="text" id="marker_color_messe" name="marker_color_messe"
                               value="<?= htmlspecialchars($settings['marker_color_messe'] ?? '#9c27b0') ?>">
                    </div>

                    <div class="color-picker-row">
                        <label for="marker_color_finished">Fertig / Abholbereit</label>
                        <input type="color" id="marker_color_finished_picker" value="<?= htmlspecialchars(substr($settings['marker_color_finished'] ?? '#6c757d', 0, 7)) ?>">
                        <input type="text" id="marker_color_finished" name="marker_color_finished" 
                               value="<?= htmlspecialchars($settings['marker_color_finished'] ?? '#6c757d') ?>">
                    </div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-map-marker-alt"></i> Marker</h3>
                    <div class="form-group">
                        <label>Größe</label>
                        <div style="display: flex; gap: 15px;">
                            <label><input type="radio" name="marker_size" value="small" <?= ($settings['marker_size'] ?? 'medium') === 'small' ? 'checked' : '' ?>> Klein</label>
                            <label><input type="radio" name="marker_size" value="medium" <?= ($settings['marker_size'] ?? 'medium') === 'medium' ? 'checked' : '' ?>> Mittel</label>
                            <label><input type="radio" name="marker_size" value="large" <?= ($settings['marker_size'] ?? 'medium') === 'large' ? 'checked' : '' ?>> Groß</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="marker_icon_finished">Icon für fertige Geräte</label>
                        <select id="marker_icon_finished" name="marker_icon_finished">
                            <option value="green" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'green' ? 'selected' : '' ?>>Grün</option>
                            <option value="grey" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'grey' ? 'selected' : '' ?>>Grau</option>
                            <option value="blue" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'blue' ? 'selected' : '' ?>>Blau</option>
                            <option value="red" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'red' ? 'selected' : '' ?>>Rot</option>
                            <option value="orange" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'orange' ? 'selected' : '' ?>>Orange</option>
                            <option value="yellow" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'yellow' ? 'selected' : '' ?>>Gelb</option>
                            <option value="violet" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'violet' ? 'selected' : '' ?>>Violett</option>
                            <option value="black" <?= ($settings['marker_icon_finished'] ?? 'grey') === 'black' ? 'selected' : '' ?>>Schwarz</option>
                        </select>
                        <small style="color: #666; display: block; margin-top: 5px;">Icon für Geräte die als "Fertig/Abholbereit" markiert sind</small>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="marker_pulse" name="marker_pulse" 
                               <?= ($settings['marker_pulse'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label for="marker_pulse">Pulseffekt</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="marker_hover_scale" name="marker_hover_scale" 
                               <?= ($settings['marker_hover_scale'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label for="marker_hover_scale">Hover-Skalierung</label>
                    </div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-draw-polygon"></i> Navigation</h3>

                    <div class="checkbox-group">
                        <input type="checkbox" id="enable_routing" name="enable_routing" 
                               <?= ($settings['enable_routing'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label for="enable_routing">Route zu Markern anzeigen</label>
                    </div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-map"></i> Karte</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="map_default_lat">Breitengrad</label>
                            <input type="number" id="map_default_lat" name="map_default_lat" 
                                   step="0.000001" value="<?= $settings['map_default_lat'] ?? 49.995567 ?>">
                        </div>
                        <div class="form-group">
                            <label for="map_default_lng">Längengrad</label>
                            <input type="number" id="map_default_lng" name="map_default_lng" 
                                   step="0.000001" value="<?= $settings['map_default_lng'] ?? 9.0731267 ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="map_default_zoom">Zoom</label>
                        <input type="range" id="map_default_zoom" name="map_default_zoom" 
                               min="1" max="19" value="<?= $settings['map_default_zoom'] ?? 15 ?>" 
                               oninput="document.getElementById('zoomValue').textContent = this.value">
                        <div><span id="zoomValue"><?= $settings['map_default_zoom'] ?? 15 ?></span></div>
                    </div>
                    <div id="map" style="height: 300px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                </div>
                
                <div class="setting-group">
                    <h3><i class="fas fa-envelope"></i> E-Mail</h3>
                    <div class="checkbox-group">
                        <input type="checkbox" id="email_enabled" name="email_enabled" 
                               <?= ($settings['email_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label for="email_enabled">Benachrichtigungen aktivieren</label>
                    </div>
                    <div class="form-group">
                        <label for="email_from">Absender E-Mail</label>
                        <input type="email" id="email_from" name="email_from" 
                               value="<?= htmlspecialchars($settings['email_from'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email_from_name">Absender Name</label>
                        <input type="text" id="email_from_name" name="email_from_name" 
                               value="<?= htmlspecialchars($settings['email_from_name'] ?? 'RFID System') ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_all_settings" class="btn btn-primary btn-large">
                        <i class="fas fa-save"></i> Alle Einstellungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
    let map = L.map('map').setView([<?= $settings['map_default_lat'] ?? 49.995567 ?>, <?= $settings['map_default_lng'] ?? 9.0731267 ?>], <?= $settings['map_default_zoom'] ?? 15 ?>);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([<?= $settings['map_default_lat'] ?? 49.995567 ?>, <?= $settings['map_default_lng'] ?? 9.0731267 ?>]).addTo(map);
    
    // Color Picker Synchronisation
    document.querySelectorAll('.color-picker-row').forEach(row => {
        const picker = row.querySelector('input[type="color"]');
        const text = row.querySelector('input[type="text"]');
        
        if (picker && text && !picker.disabled) {
            picker.addEventListener('input', function() {
                text.value = this.value;
            });
            
            text.addEventListener('input', function() {
                if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                    picker.value = this.value;
                }
            });
        }
    });

    // User Dropdown Toggle
    function toggleUserDropdown() {
        const menu = document.getElementById('settingDropdownMenu');
        menu.classList.toggle('show');
    }

    // Schließen beim Klick außerhalb
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.setting-dropdown-container')) {
            const menu = document.getElementById('settingDropdownMenu');
            if (menu) {
                menu.classList.remove('show');
            }
        }
    });
    </script>
</body>
</html>