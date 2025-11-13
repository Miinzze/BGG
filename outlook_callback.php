<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Prüfen ob Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// System-Einstellungen laden
$settings = getSystemSettings();

// Prüfen ob Outlook-Integration aktiviert ist
if (($settings['enable_outlook_sync'] ?? '0') != '1') {
    $_SESSION['error'] = 'Outlook-Integration ist nicht aktiviert';
    header('Location: user_calendar_settings.php');
    exit;
}

// Benutzer-Daten laden
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Prüfen ob Wartungserinnerungen aktiviert sind
$hasNotificationsEnabled = ($user['receive_maintenance_emails'] == 1 || $user['maintenance_notification'] == 1);

if (!$hasNotificationsEnabled) {
    $_SESSION['error'] = 'Bitte aktivieren Sie zuerst die Wartungserinnerungen';
    header('Location: user_calendar_settings.php');
    exit;
}

$clientId = $settings['outlook_client_id'] ?? '';
$clientSecret = $settings['outlook_client_secret'] ?? '';
$redirectUri = $settings['outlook_redirect_uri'] ?? '';

if (empty($clientId) || empty($clientSecret)) {
    $_SESSION['error'] = 'Outlook ist nicht korrekt konfiguriert. Bitte kontaktieren Sie Ihren Administrator.';
    header('Location: user_calendar_settings.php');
    exit;
}

// OAuth-URLs
$authUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
$tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
$scope = 'openid offline_access Calendars.ReadWrite';

// Verbindung herstellen
if (isset($_GET['action']) && $_GET['action'] === 'connect') {
    // State für CSRF-Schutz generieren
    $state = bin2hex(random_bytes(16));
    $_SESSION['outlook_oauth_state'] = $state;
    
    // Authorization URL erstellen
    $params = [
        'client_id' => $clientId,
        'response_type' => 'code',
        'redirect_uri' => $redirectUri,
        'scope' => $scope,
        'state' => $state,
        'response_mode' => 'query'
    ];
    
    $authorizationUrl = $authUrl . '?' . http_build_query($params);
    
    // Weiterleitung zu Microsoft
    header('Location: ' . $authorizationUrl);
    exit;
}

// Callback von Microsoft
if (isset($_GET['code'])) {
    // State validieren
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['outlook_oauth_state']) {
        $_SESSION['error'] = 'Sicherheitsfehler: Ungültiger State-Parameter';
        header('Location: user_calendar_settings.php');
        exit;
    }
    
    $code = $_GET['code'];
    
    // Access Token anfordern
    $tokenParams = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $code,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
        'scope' => $scope
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $_SESSION['error'] = 'Fehler beim Abrufen des Access Tokens';
        logActivity($pdo, $_SESSION['user_id'], 'outlook_connection_failed', 'Fehler beim OAuth-Prozess');
        header('Location: user_calendar_settings.php');
        exit;
    }
    
    $tokenData = json_decode($response, true);
    
    if (!isset($tokenData['access_token'])) {
        $_SESSION['error'] = 'Ungültige Antwort von Microsoft';
        logActivity($pdo, $_SESSION['user_id'], 'outlook_connection_failed', 'Ungültige Token-Antwort');
        header('Location: user_calendar_settings.php');
        exit;
    }
    
    $accessToken = $tokenData['access_token'];
    $refreshToken = $tokenData['refresh_token'] ?? '';
    $expiresIn = $tokenData['expires_in'] ?? 3600;
    
    // Token verschlüsseln (basic encryption, sollte idealerweise stärker sein)
    $encryptedAccessToken = base64_encode($accessToken);
    $encryptedRefreshToken = base64_encode($refreshToken);
    
    try {
        // In Datenbank speichern
        $stmt = $pdo->prepare("
            UPDATE user_calendar_settings 
            SET outlook_enabled = 1,
                calendar_token = ?,
                calendar_url = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        // Refresh Token als calendar_token und Access Token als calendar_url speichern
        // In einer Produktionsumgebung sollten diese in separaten, verschlüsselten Feldern gespeichert werden
        $stmt->execute([$encryptedRefreshToken, $encryptedAccessToken, $_SESSION['user_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'outlook_connected', 'Outlook-Kalender erfolgreich verbunden');
        
        $_SESSION['success'] = 'Outlook-Kalender erfolgreich verbunden!';
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Datenbankfehler: ' . $e->getMessage();
        logActivity($pdo, $_SESSION['user_id'], 'outlook_connection_failed', 'Datenbankfehler');
    }
    
    // State löschen
    unset($_SESSION['outlook_oauth_state']);
    
    header('Location: user_calendar_settings.php');
    exit;
}

// Fehler von Microsoft
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $errorDescription = $_GET['error_description'] ?? 'Unbekannter Fehler';
    
    $_SESSION['error'] = 'Fehler bei der Outlook-Autorisierung: ' . htmlspecialchars($errorDescription);
    logActivity($pdo, $_SESSION['user_id'], 'outlook_connection_failed', 'OAuth-Fehler: ' . $error);
    
    header('Location: user_calendar_settings.php');
    exit;
}

// Fallback
$_SESSION['error'] = 'Ungültige Anfrage';
header('Location: user_calendar_settings.php');
exit;