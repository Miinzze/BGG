<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// Benutzer-Daten laden
$user = getUserInfo($_SESSION['user_id'], $pdo);

// WICHTIG: Prüfen ob Wartungs-/Prüfungserinnerungen aktiviert sind
// Nur Admin kann diese Einstellung ändern
$hasNotificationsEnabled = ($user['receive_maintenance_emails'] == 1 || $user['maintenance_notification'] == 1);

if (!$hasNotificationsEnabled) {
    $_SESSION['error_message'] = 'Zugriff verweigert: Wartungs- und Prüfungserinnerungen müssen vom Administrator aktiviert werden.';
    header('Location: index.php');
    exit;
}

// System-Einstellungen laden
$settings = getSystemSettings();

// Benutzer-Kalender-Einstellungen laden
$stmt = $pdo->prepare("SELECT * FROM user_calendar_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userCalendar = $stmt->fetch();

// Wenn keine Einstellungen vorhanden, Standardwerte erstellen
if (!$userCalendar) {
    $stmt = $pdo->prepare("
        INSERT INTO user_calendar_settings (user_id, google_calendar_enabled, outlook_enabled, ical_enabled, auto_create_events, notification_days_before) 
        VALUES (?, 0, 0, 1, 0, 3)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("SELECT * FROM user_calendar_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userCalendar = $stmt->fetch();
}

$message = '';
$messageType = '';

// Kalender-Einstellungen speichern
if (isset($_POST['save_calendar_settings'])) {
    validateCSRF();
    
    $notificationDaysBefore = intval($_POST['notification_days_before'] ?? 3);
    $autoCreateEvents = isset($_POST['auto_create_events']) ? 1 : 0;
    $icalEnabled = isset($_POST['ical_enabled']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE user_calendar_settings 
            SET notification_days_before = ?,
                auto_create_events = ?,
                ical_enabled = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$notificationDaysBefore, $autoCreateEvents, $icalEnabled, $_SESSION['user_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'calendar_settings_updated', 'Kalender-Einstellungen aktualisiert');
        
        $message = "Einstellungen erfolgreich gespeichert!";
        $messageType = 'success';
        
        // Neu laden
        $stmt = $pdo->prepare("SELECT * FROM user_calendar_settings WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userCalendar = $stmt->fetch();
        
    } catch (PDOException $e) {
        $message = "Fehler beim Speichern: " . $e->getMessage();
        $messageType = 'danger';
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
    <title>Meine Kalender-Einstellungen - <?= htmlspecialchars($settings['system_name'] ?? 'System') ?></title>
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

        .integration-card.connected {
            border-color: #2ecc71;
            background: #f0fff4;
        }

        .integration-card.disabled {
            opacity: 0.6;
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-badge.connected {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.disconnected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.disabled {
            background: #e2e3e5;
            color: #6c757d;
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

        .error-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
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

        input:disabled + .slider {
            background-color: #dee2e6;
            cursor: not-allowed;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .feature-list li {
            padding: 8px 0;
            padding-left: 30px;
            position: relative;
        }

        .feature-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2ecc71;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-calendar-alt"></i> Meine Kalender-Einstellungen</h1>
                <div class="header-actions">
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Zurück
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Hinweis dass Wartungserinnerungen aktiviert sind -->
            <div class="success-box">
                <strong><i class="fas fa-check-circle"></i> Aktiviert:</strong>
                Sie erhalten Benachrichtigungen über fällige Wartungen und Prüfungen. Diese Einstellung kann nur vom Administrator geändert werden.
            </div>

            <!-- Outlook Integration -->
            <?php if (($settings['enable_outlook_sync'] ?? '0') == '1'): ?>
            <div class="setting-group">
                <h3><i class="fab fa-microsoft"></i> Outlook / Microsoft 365</h3>
                
                <div class="integration-card <?= ($userCalendar['outlook_enabled'] == 1 ? 'connected' : '') ?>">
                    <div style="display: flex; justify-content-space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <h4 style="margin: 0;">
                                <i class="fab fa-microsoft"></i> Outlook Kalender
                            </h4>
                        </div>
                        <div>
                            <?php if ($userCalendar['outlook_enabled'] == 1): ?>
                                <span class="status-badge connected">
                                    <i class="fas fa-check-circle"></i> Verbunden
                                </span>
                            <?php else: ?>
                                <span class="status-badge disconnected">
                                    <i class="fas fa-times-circle"></i> Nicht verbunden
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p style="color: #6c757d; margin-bottom: 15px;">
                        Verbinden Sie Ihr Outlook-Konto, um automatisch Termine für fällige Wartungen und Prüfungen zu erstellen.
                    </p>
                    
                    <ul class="feature-list">
                        <li>Automatische Kalenderereignisse für Wartungen</li>
                        <li>Erinnerungen für fällige Prüfungen</li>
                        <li>Synchronisation mit Ihrem Outlook-Kalender</li>
                        <li>Mobile Benachrichtigungen über Outlook-App</li>
                    </ul>
                    
                    <?php if ($userCalendar['outlook_enabled'] == 1): ?>
                        <div class="info-box">
                            <strong>Verbunden seit:</strong> 
                            <?= date('d.m.Y H:i', strtotime($userCalendar['updated_at'])) ?> Uhr
                        </div>
                        
                        <a href="outlook_disconnect.php" class="btn btn-danger" 
                           onclick="return confirm('Möchten Sie die Outlook-Verbindung wirklich trennen?')">
                            <i class="fas fa-unlink"></i> Verbindung trennen
                        </a>
                    <?php else: ?>
                        <a href="outlook_callback.php?action=connect" class="btn btn-primary">
                            <i class="fab fa-microsoft"></i> Mit Outlook verbinden
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Google Calendar (falls aktiviert) -->
            <?php if (($settings['enable_google_calendar'] ?? '0') == '1'): ?>
            <div class="setting-group">
                <h3><i class="fab fa-google"></i> Google Calendar</h3>
                
                <div class="integration-card">
                    <div style="display: flex; justify-content-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <h4 style="margin: 0;">
                                <i class="fab fa-google"></i> Google Kalender
                            </h4>
                        </div>
                        <div>
                            <span class="status-badge disconnected">
                                <i class="fas fa-times-circle"></i> Nicht verbunden
                            </span>
                        </div>
                    </div>
                    
                    <p style="color: #6c757d;">
                        Die Google Calendar Integration ist in Entwicklung.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- iCal Feed -->
            <div class="setting-group">
                <h3><i class="fas fa-rss"></i> iCal Feed</h3>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="integration-card <?= ($userCalendar['ical_enabled'] == 1 ? 'connected' : '') ?>">
                        <div style="display: flex; justify-content-between; align-items-center; margin-bottom: 15px;">
                            <div>
                                <h4 style="margin: 0;">
                                    <i class="fas fa-rss"></i> iCal / WebCal Feed
                                </h4>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="ical_enabled" 
                                       <?= $userCalendar['ical_enabled'] == 1 ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <?php if ($userCalendar['ical_enabled'] == 1): ?>
                            <p style="color: #6c757d; margin-bottom: 15px;">
                                Abonnieren Sie diesen Feed in Ihrer Kalender-App (Apple Kalender, Google Calendar, Outlook, etc.)
                            </p>
                            
                            <?php 
                            // Token generieren falls nicht vorhanden
                            if (empty($userCalendar['calendar_token'])) {
                                $token = bin2hex(random_bytes(32));
                                $stmt = $pdo->prepare("UPDATE user_calendar_settings SET calendar_token = ? WHERE user_id = ?");
                                $stmt->execute([$token, $_SESSION['user_id']]);
                                $userCalendar['calendar_token'] = $token;
                            }
                            
                            $icalUrl = 'webcal://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/ical_feed.php?token=' . $userCalendar['calendar_token'];
                            $httpUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/ical_feed.php?token=' . $userCalendar['calendar_token'];
                            ?>
                            
                            <div class="form-group">
                                <label>Feed-URL</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($icalUrl) ?>" 
                                           id="icalUrl" readonly>
                                    <button type="button" class="btn btn-secondary" onclick="copyToClipboard('icalUrl')">
                                        <i class="fas fa-copy"></i> Kopieren
                                    </button>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <strong><i class="fas fa-info-circle"></i> Hinweis:</strong>
                                Kopieren Sie diese URL und fügen Sie sie in Ihrer Kalender-App als neuen Kalender hinzu.
                            </div>
                        <?php else: ?>
                            <p style="color: #6c757d;">
                                Aktivieren Sie den iCal-Feed, um einen Link zu erhalten, den Sie in Ihrer Kalender-App abonnieren können.
                            </p>
                        <?php endif; ?>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <label>Erinnerung vor Termin</label>
                            <select name="notification_days_before" class="form-control">
                                <option value="1" <?= $userCalendar['notification_days_before'] == 1 ? 'selected' : '' ?>>1 Tag vorher</option>
                                <option value="3" <?= $userCalendar['notification_days_before'] == 3 ? 'selected' : '' ?>>3 Tage vorher</option>
                                <option value="7" <?= $userCalendar['notification_days_before'] == 7 ? 'selected' : '' ?>>7 Tage vorher</option>
                                <option value="14" <?= $userCalendar['notification_days_before'] == 14 ? 'selected' : '' ?>>14 Tage vorher</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="auto_create_events" 
                                       <?= $userCalendar['auto_create_events'] == 1 ? 'checked' : '' ?>
                                       style="margin-right: 10px;">
                                <span>Automatisch Ereignisse für neue Wartungen erstellen</span>
                            </label>
                        </div>
                        
                        <button type="submit" name="save_calendar_settings" class="btn btn-primary">
                            <i class="fas fa-save"></i> Einstellungen speichern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        element.select();
        element.setSelectionRange(0, 99999);
        
        navigator.clipboard.writeText(element.value).then(function() {
            alert('Feed-URL wurde in die Zwischenablage kopiert!');
        }).catch(function() {
            alert('Fehler beim Kopieren');
        });
    }
    </script>

    <?php require_once 'footer.php'; ?>
</body>
</html>