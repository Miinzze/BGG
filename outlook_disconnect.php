<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Prüfen ob Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Outlook-Verbindung trennen
    $stmt = $pdo->prepare("
        UPDATE user_calendar_settings 
        SET outlook_enabled = 0,
            calendar_token = NULL,
            calendar_url = NULL,
            updated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    logActivity($pdo, $_SESSION['user_id'], 'outlook_disconnected', 'Outlook-Kalender getrennt');
    
    $_SESSION['success'] = 'Outlook-Verbindung erfolgreich getrennt';
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Fehler beim Trennen: ' . $e->getMessage();
}

header('Location: user_calendar_settings.php');
exit;