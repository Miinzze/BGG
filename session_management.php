<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$message = '';
$messageType = '';

// Session beenden
if (isset($_GET['logout_session'])) {
    $sessionId = $_GET['logout_session'];
    
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET is_active = 0 
        WHERE session_id = ? 
        AND user_id = ?
    ");
    $stmt->execute([$sessionId, $_SESSION['user_id']]);
    
    logActivity('session_terminated', "Sitzung $sessionId beendet");
    
    $message = 'Sitzung wurde beendet';
    $messageType = 'success';
}

// Alle anderen Sessions beenden
if (isset($_POST['logout_all_others'])) {
    validateCSRF();
    
    logoutAllOtherSessions($_SESSION['user_id'], $pdo, true);
    
    $message = 'Alle anderen Sitzungen wurden beendet';
    $messageType = 'success';
}

// Aktive Sessions laden
$sessions = getActiveSessions($_SESSION['user_id'], $pdo);
$currentSessionId = session_id();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <title>Sitzungsverwaltung - BGG System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .session-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .session-card.current {
            border-color: #4caf50;
            background: #f1f8f4;
        }
        
        .session-card.current::before {
            content: "Aktuelle Sitzung";
            position: absolute;
            top: -12px;
            left: 20px;
            background: #4caf50;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .session-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .session-detail {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .session-detail i {
            color: #666;
            width: 20px;
        }
        
        .session-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-shield-alt"></i> Sitzungsverwaltung</h1>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zurück zum Profil
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h2>Aktive Sitzungen</h2>
                        <p style="color: #666; margin: 5px 0 0 0;">
                            Sie haben <?= count($sessions) ?> aktive Sitzung(en)
                        </p>
                    </div>
                    
                    <?php if (count($sessions) > 1): ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" name="logout_all_others" class="btn btn-danger"
                                    onclick="return confirm('Möchten Sie alle anderen Sitzungen beenden? Sie bleiben nur auf diesem Gerät angemeldet.')">
                                <i class="fas fa-sign-out-alt"></i> Alle anderen abmelden
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <div class="info-box" style="margin-bottom: 20px;">
                    <h3><i class="fas fa-info-circle"></i> Über Sitzungen</h3>
                    <p>Eine Sitzung wird erstellt, wenn Sie sich auf einem Gerät anmelden. 
                       Hier sehen Sie alle Geräte, auf denen Sie aktuell angemeldet sind.</p>
                    <ul style="margin-left: 20px;">
                        <li>Sitzungen laufen nach 24 Stunden Inaktivität ab</li>
                        <li>Sie können verdächtige Sitzungen einzeln beenden</li>
                        <li>Die aktuelle Sitzung ist grün markiert</li>
                    </ul>
                </div>
                
                <?php foreach ($sessions as $session): ?>
                    <div class="session-card <?= $session['session_id'] == $currentSessionId ? 'current' : '' ?>">
                        <div class="session-info">
                            <div class="session-detail">
                                <i class="fas fa-laptop"></i>
                                <div>
                                    <strong><?= e($session['device_info']) ?></strong>
                                    <br>
                                    <small style="color: #666;"><?= e(substr($session['user_agent'], 0, 50)) ?>...</small>
                                </div>
                            </div>
                            
                            <div class="session-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>IP-Adresse</strong>
                                    <br>
                                    <small style="color: #666;"><?= e($session['ip_address']) ?></small>
                                </div>
                            </div>
                            
                            <div class="session-detail">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Letzte Aktivität</strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?php 
                                        $lastActivity = strtotime($session['last_activity']);
                                        $diff = time() - $lastActivity;
                                        if ($diff < 60) {
                                            echo 'Gerade eben';
                                        } elseif ($diff < 3600) {
                                            echo 'Vor ' . floor($diff / 60) . ' Minuten';
                                        } elseif ($diff < 86400) {
                                            echo 'Vor ' . floor($diff / 3600) . ' Stunden';
                                        } else {
                                            echo date('d.m.Y H:i', $lastActivity) . ' Uhr';
                                        }
                                        ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="session-detail">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <strong>Angemeldet seit</strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?= date('d.m.Y H:i', strtotime($session['created_at'])) ?> Uhr
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($session['session_id'] != $currentSessionId): ?>
                            <div class="session-actions">
                                <a href="?logout_session=<?= e($session['session_id']) ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Diese Sitzung beenden?')">
                                    <i class="fas fa-sign-out-alt"></i> Sitzung beenden
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="session-actions">
                                <span style="color: #4caf50; font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Dies ist Ihre aktuelle Sitzung
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($sessions) == 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Keine aktiven Sitzungen gefunden.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-history"></i> Sicherheitshinweise</h2>
                
                <div class="info-box">
                    <h3 style="color: #f44336;">
                        <i class="fas fa-exclamation-triangle"></i> Verdächtige Aktivität?
                    </h3>
                    <p>Falls Sie Sitzungen sehen, die Sie nicht erkennen:</p>
                    <ul style="margin-left: 20px;">
                        <li>Beenden Sie sofort alle unbekannten Sitzungen</li>
                        <li>Ändern Sie Ihr Passwort</li>
                        <li>Aktivieren Sie 2FA in Ihrem <a href="profile.php">Profil</a></li>
                        <li>Kontaktieren Sie den Administrator</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>