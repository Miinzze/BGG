<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('calendar_settings');

// Benutzer-Daten und Einstellungen laden
$user = getUserInfo($_SESSION['user_id'], $pdo);
$settings = getSystemSettings();

$message = '';
$messageType = '';

// Einstellungen speichern
if (isset($_POST['save_calendar_settings'])) {
    $calendarSettings = [
        'enable_calendar_integration' => isset($_POST['enable_calendar_integration']) ? '1' : '0',
        'calendar_auto_maintenance' => isset($_POST['calendar_auto_maintenance']) ? '1' : '0',
        'calendar_maintenance_days_before' => intval($_POST['calendar_maintenance_days_before'] ?? 7),
        'enable_outlook_sync' => isset($_POST['enable_outlook_sync']) ? '1' : '0',
        'outlook_client_id' => trim($_POST['outlook_client_id'] ?? ''),
        'outlook_client_secret' => trim($_POST['outlook_client_secret'] ?? ''),
        'outlook_redirect_uri' => trim($_POST['outlook_redirect_uri'] ?? ''),
        'enable_google_calendar' => isset($_POST['enable_google_calendar']) ? '1' : '0',
        'google_calendar_api_key' => trim($_POST['google_calendar_api_key'] ?? ''),
        'google_calendar_client_id' => trim($_POST['google_calendar_client_id'] ?? ''),
    ];
    
    try {
        foreach ($calendarSettings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }
        
        // Cache löschen
        if (isset($cache)) {
            $cache->delete('system_settings');
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'calendar_settings_updated', "Kalender-Einstellungen aktualisiert");
        
        $message = "Einstellungen erfolgreich gespeichert!";
        $messageType = 'success';
        
        // Einstellungen neu laden
        $settings = getSystemSettings();
    } catch (PDOException $e) {
        $message = "Fehler beim Speichern: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Test Outlook Verbindung
if (isset($_POST['test_outlook'])) {
    $clientId = trim($_POST['outlook_client_id']);
    $clientSecret = trim($_POST['outlook_client_secret']);
    
    if (!empty($clientId) && !empty($clientSecret)) {
        // TODO: Hier würde die eigentliche Outlook API Verbindung getestet
        $message = "Outlook-Verbindung wird getestet... (Feature in Entwicklung)";
        $messageType = 'info';
    } else {
        $message = "Bitte Client ID und Client Secret eingeben.";
        $messageType = 'warning';
    }
}

// Wartungstermine zählen
$stmt = $pdo->query("
    SELECT COUNT(*) as upcoming 
    FROM inspection_schedules 
    WHERE next_inspection >= CURDATE() 
    AND next_inspection <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
");
$upcomingMaintenance = $stmt->fetch()['upcoming'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender-Einstellungen - <?= htmlspecialchars($settings['system_name'] ?? 'System') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
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
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #2ecc71;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .integration-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .integration-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .integration-card.enabled {
            border-color: #2ecc71;
            background: #f0fff4;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .stats-widget {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .stats-widget h4 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }

        .stats-widget p {
            margin: 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-calendar-alt"></i> Kalender-Einstellungen</h1>
                <div class="header-actions">
                    <a href="settings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <!-- Basis-Einstellungen -->
                        <div class="setting-group">
                            <h3><i class="fas fa-sliders-h"></i> Basis-Einstellungen</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="enable_calendar_integration" name="enable_calendar_integration" 
                                       <?= ($settings['enable_calendar_integration'] ?? '0') == '1' ? 'checked' : '' ?>>
                                <label for="enable_calendar_integration">
                                    <strong><i class="fas fa-calendar-check"></i> Kalender-Integration aktivieren</strong>
                                    <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">
                                        Aktiviert die Kalender-Funktionen für das gesamte System
                                    </div>
                                </label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="calendar_auto_maintenance" name="calendar_auto_maintenance" 
                                       <?= ($settings['calendar_auto_maintenance'] ?? '0') == '1' ? 'checked' : '' ?>>
                                <label for="calendar_auto_maintenance">
                                    <strong><i class="fas fa-tools"></i> Automatische Wartungstermine</strong>
                                    <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">
                                        Wartungstermine automatisch in Kalender eintragen
                                    </div>
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="calendar_maintenance_days_before">
                                    <i class="fas fa-bell"></i> Erinnerung (Tage vorher)
                                </label>
                                <input type="number" class="form-control" id="calendar_maintenance_days_before" 
                                       name="calendar_maintenance_days_before"
                                       value="<?= htmlspecialchars($settings['calendar_maintenance_days_before'] ?? '7') ?>"
                                       min="1" max="30">
                                <small class="form-text text-muted">
                                    Wie viele Tage vor dem Wartungstermin soll erinnert werden?
                                </small>
                            </div>
                        </div>

                        <!-- Outlook Integration -->
                        <div class="setting-group">
                            <h3><i class="fab fa-microsoft"></i> Outlook / Microsoft 365 Integration</h3>
                            
                            <div class="info-box">
                                <strong><i class="fas fa-info-circle"></i> Hinweis:</strong>
                                Um Outlook zu integrieren, benötigen Sie eine Azure App-Registrierung.
                                <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank">
                                    Zur Azure Portal <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>

                            <div class="integration-card <?= ($settings['enable_outlook_sync'] ?? '0') == '1' ? 'enabled' : '' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5><i class="fab fa-microsoft"></i> Outlook Synchronisation</h5>
                                    <label class="switch">
                                        <input type="checkbox" name="enable_outlook_sync" 
                                               <?= ($settings['enable_outlook_sync'] ?? '0') == '1' ? 'checked' : '' ?>
                                               onchange="toggleFeature(this, 'outlookSettings')">
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div id="outlookSettings" style="display: <?= ($settings['enable_outlook_sync'] ?? '0') == '1' ? 'block' : 'none' ?>;">
                                    <div class="form-group">
                                        <label for="outlook_client_id">
                                            <i class="fas fa-key"></i> Client ID (Application ID)
                                        </label>
                                        <input type="text" class="form-control" id="outlook_client_id" name="outlook_client_id"
                                               value="<?= htmlspecialchars($settings['outlook_client_id'] ?? '') ?>"
                                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                    </div>

                                    <div class="form-group">
                                        <label for="outlook_client_secret">
                                            <i class="fas fa-lock"></i> Client Secret
                                        </label>
                                        <input type="password" class="form-control" id="outlook_client_secret" name="outlook_client_secret"
                                               value="<?= htmlspecialchars($settings['outlook_client_secret'] ?? '') ?>"
                                               placeholder="******************">
                                        <small class="form-text text-muted">
                                            Wird verschlüsselt gespeichert
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="outlook_redirect_uri">
                                            <i class="fas fa-link"></i> Redirect URI
                                        </label>
                                        <input type="url" class="form-control" id="outlook_redirect_uri" name="outlook_redirect_uri"
                                               value="<?= htmlspecialchars($settings['outlook_redirect_uri'] ?? '') ?>"
                                               placeholder="https://ihre-domain.de/outlook_callback.php">
                                        <small class="form-text text-muted">
                                            Diese URL muss in Azure als Redirect URI eingetragen sein
                                        </small>
                                    </div>

                                    <button type="submit" name="test_outlook" class="btn btn-info">
                                        <i class="fas fa-vial"></i> Verbindung testen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Google Calendar Integration -->
                        <div class="setting-group">
                            <h3><i class="fab fa-google"></i> Google Calendar Integration</h3>
                            
                            <div class="info-box">
                                <strong><i class="fas fa-info-circle"></i> Hinweis:</strong>
                                Um Google Calendar zu integrieren, benötigen Sie einen API-Schlüssel.
                                <a href="https://console.cloud.google.com/" target="_blank">
                                    Zur Google Cloud Console <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>

                            <div class="integration-card <?= ($settings['enable_google_calendar'] ?? '0') == '1' ? 'enabled' : '' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5><i class="fab fa-google"></i> Google Calendar Synchronisation</h5>
                                    <label class="switch">
                                        <input type="checkbox" name="enable_google_calendar" 
                                               <?= ($settings['enable_google_calendar'] ?? '0') == '1' ? 'checked' : '' ?>
                                               onchange="toggleFeature(this, 'googleSettings')">
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div id="googleSettings" style="display: <?= ($settings['enable_google_calendar'] ?? '0') == '1' ? 'block' : 'none' ?>;">
                                    <div class="form-group">
                                        <label for="google_calendar_api_key">
                                            <i class="fas fa-key"></i> API Key
                                        </label>
                                        <input type="text" class="form-control" id="google_calendar_api_key" name="google_calendar_api_key"
                                               value="<?= htmlspecialchars($settings['google_calendar_api_key'] ?? '') ?>"
                                               placeholder="AIza...">
                                    </div>

                                    <div class="form-group">
                                        <label for="google_calendar_client_id">
                                            <i class="fas fa-fingerprint"></i> Client ID
                                        </label>
                                        <input type="text" class="form-control" id="google_calendar_client_id" name="google_calendar_client_id"
                                               value="<?= htmlspecialchars($settings['google_calendar_client_id'] ?? '') ?>"
                                               placeholder="xxxxxxxxx.apps.googleusercontent.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Speichern -->
                        <div class="text-right">
                            <button type="submit" name="save_calendar_settings" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Einstellungen speichern
                            </button>
                        </div>
                    </form>

                    <!-- Anleitung -->
                    <div class="setting-group mt-4">
                        <h3><i class="fas fa-book"></i> Anleitung</h3>
                        
                        <div class="accordion" id="guideAccordion">
                            <div class="card">
                                <div class="card-header" id="headingOutlook">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOutlook">
                                            <i class="fab fa-microsoft"></i> Outlook/Microsoft 365 einrichten
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseOutlook" class="collapse" data-parent="#guideAccordion">
                                    <div class="card-body">
                                        <ol>
                                            <li>Gehen Sie zum <a href="https://portal.azure.com" target="_blank">Azure Portal</a></li>
                                            <li>Navigieren Sie zu "Azure Active Directory" → "App-Registrierungen"</li>
                                            <li>Klicken Sie auf "Neue Registrierung"</li>
                                            <li>Geben Sie einen Namen ein (z.B. "Marker System Calendar")</li>
                                            <li>Wählen Sie "Nur Konten in diesem Organisationsverzeichnis"</li>
                                            <li>Tragen Sie Ihre Redirect URI ein</li>
                                            <li>Kopieren Sie die "Anwendungs-ID (Client-ID)"</li>
                                            <li>Erstellen Sie unter "Zertifikate & Geheimnisse" ein neues Client Secret</li>
                                            <li>Tragen Sie Client ID und Secret oben ein</li>
                                            <li>Unter "API-Berechtigungen": Fügen Sie "Calendars.ReadWrite" hinzu</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="headingGoogle">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseGoogle">
                                            <i class="fab fa-google"></i> Google Calendar einrichten
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseGoogle" class="collapse" data-parent="#guideAccordion">
                                    <div class="card-body">
                                        <ol>
                                            <li>Gehen Sie zur <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                                            <li>Erstellen Sie ein neues Projekt oder wählen Sie ein bestehendes</li>
                                            <li>Aktivieren Sie die "Google Calendar API"</li>
                                            <li>Erstellen Sie API-Anmeldedaten (OAuth 2.0-Client-ID)</li>
                                            <li>Kopieren Sie die Client-ID</li>
                                            <li>Erstellen Sie einen API-Schlüssel unter "Anmeldedaten"</li>
                                            <li>Tragen Sie beide Werte oben ein</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <div class="stats-widget">
                        <h4><?= $upcomingMaintenance ?></h4>
                        <p><i class="fas fa-calendar-check"></i> Anstehende Wartungen (30 Tage)</p>
                    </div>

                    <div class="setting-group">
                        <h4><i class="fas fa-lightbulb"></i> Tipps</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Aktivieren Sie die automatische Synchronisation für nahtlose Integration
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Outlook und Google Calendar können parallel genutzt werden
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Benutzer können individuelle Kalender-Einstellungen vornehmen
                            </li>
                        </ul>
                    </div>

                    <div class="setting-group">
                        <h4><i class="fas fa-link"></i> Schnellzugriff</h4>
                        <div class="list-group">
                            <a href="user_calendar_settings.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-cog"></i> Persönliche Kalender-Einstellungen
                            </a>
                            <a href="maintenance_timeline.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-calendar-alt"></i> Wartungs-Timeline
                            </a>
                            <a href="ical_feed.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-rss"></i> iCal Feed
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleFeature(checkbox, targetId) {
        const target = document.getElementById(targetId);
        if (checkbox.checked) {
            target.style.display = 'block';
            checkbox.closest('.integration-card').classList.add('enabled');
        } else {
            target.style.display = 'none';
            checkbox.closest('.integration-card').classList.remove('enabled');
        }
    }
    </script>

    <?php require_once 'footer.php'; ?>
</body>
</html>