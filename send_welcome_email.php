<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('users_manage');

$userId = $_GET['user_id'] ?? 0;

if (!validateInteger($userId, 1)) {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die('Benutzer nicht gefunden');
}

// Email-Template laden
$emailTemplate = '
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .checklist { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .checklist-item { padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Willkommen im RFID Marker System!</h1>
        </div>
        <div class="content">
            <p>Hallo ' . htmlspecialchars($user['first_name'] ?: $user['username']) . ',</p>
            
            <p>herzlich willkommen! Ihr Konto wurde erfolgreich erstellt und ist jetzt einsatzbereit.</p>
            
            <h2>📋 Ihre Login-Daten:</h2>
            <ul>
                <li><strong>Benutzername:</strong> ' . htmlspecialchars($user['username']) . '</li>
                <li><strong>E-Mail:</strong> ' . htmlspecialchars($user['email']) . '</li>
                <li><strong>Rolle:</strong> ' . htmlspecialchars($user['role']) . '</li>
            </ul>
            
            <p style="text-align: center;">
                <a href="' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/login.php" class="button">
                    Jetzt anmelden
                </a>
            </p>
            
            <div class="checklist">
                <h3>✅ Erste Schritte:</h3>
                <div class="checklist-item">
                    <strong>1. Beim ersten Login:</strong> Sie werden aufgefordert, Ihr Passwort zu ändern
                </div>
                <div class="checklist-item">
                    <strong>2. Profil vervollständigen:</strong> Fügen Sie ein Profilbild und weitere Informationen hinzu
                </div>
                <div class="checklist-item">
                    <strong>3. 2FA einrichten:</strong> Erhöhen Sie die Sicherheit mit Zwei-Faktor-Authentifizierung
                </div>
                <div class="checklist-item">
                    <strong>4. Tour starten:</strong> Nutzen Sie die interaktive Tour, um die Funktionen kennenzulernen
                </div>
            </div>
            
            <h2>🎯 Was Sie tun können:</h2>
            <p>Als <strong>' . htmlspecialchars($user['role']) . '</strong> haben Sie Zugriff auf:</p>
            <ul>
                <li>Dashboard mit Übersicht aller wichtigen Informationen</li>
                <li>Marker-Verwaltung für Objekte und Standorte</li>
                <li>Prüfungs- und Wartungsfunktionen</li>
                <li>Reports und Statistiken</li>
            </ul>
            
            <h2>💡 Hilfe benötigt?</h2>
            <p>
                • Nutzen Sie die <strong>interaktive Tour</strong> im System<br>
                • Schauen Sie in die <strong>Hilfe-Dokumentation</strong><br>
                • Kontaktieren Sie bei Fragen den Administrator
            </p>
            
            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                Viel Erfolg und herzlich willkommen im Team!<br>
                <em>Ihr RFID Marker System</em>
            </p>
        </div>
        <div class="footer">
            <p>Diese E-Mail wurde automatisch generiert. Bitte nicht direkt antworten.</p>
            <p>&copy; ' . date('Y') . ' RFID Marker System</p>
        </div>
    </div>
</body>
</html>';

// Email senden
$subject = 'Willkommen im RFID Marker System - Ihr Konto ist bereit!';
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: RFID System <noreply@' . $_SERVER['HTTP_HOST'] . '>',
    'Reply-To: noreply@' . $_SERVER['HTTP_HOST'],
    'X-Mailer: PHP/' . phpversion()
];

if (mail($user['email'], $subject, $emailTemplate, implode("\r\n", $headers))) {
    // Onboarding-Status aktualisieren
    $stmt = $pdo->prepare("
        INSERT INTO user_onboarding (user_id, welcome_email_sent) 
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE welcome_email_sent = 1
    ");
    $stmt->execute([$userId]);
    
    logActivity('welcome_email_sent', "Willkommens-Email an '{$user['username']}' ({$user['email']}) gesendet");
    
    header('Location: edit_user.php?id=' . $userId . '&message=email_sent');
} else {
    header('Location: edit_user.php?id=' . $userId . '&message=email_failed');
}
exit;
?>
