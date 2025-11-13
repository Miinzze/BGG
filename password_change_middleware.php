<?php
/**
 * password_change_middleware.php
 * Middleware die prüft ob User sein Passwort ändern muss
 * 
 * Verwendung: Include diese Datei nach requireLogin() in jeder geschützten Seite
 * 
 * Beispiel:
 * require_once 'config.php';
 * require_once 'functions.php';
 * requireLogin();
 * require_once 'password_change_middleware.php'; // <-- Hier einfügen
 */

// Nur wenn User eingeloggt ist
if (isset($_SESSION['user_id'])) {
    
    // Aktuelle Seite ermitteln
    $currentPage = basename($_SERVER['PHP_SELF']);
    
    // Diese Seiten dürfen ohne Passwort-Änderung aufgerufen werden
    $allowedPages = [
        'force_password_change.php',
        'logout.php',
        'profile.php' // Optional: User darf sein Profil sehen
    ];
    
    // Wenn wir nicht auf einer erlaubten Seite sind, prüfen
    if (!in_array($currentPage, $allowedPages)) {
        
        // Check ob Session-Flag gesetzt ist
        if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password'] === true) {
            header('Location: force_password_change.php');
            exit;
        }
        
        // Zusätzlich: DB-Check (falls Session-Flag fehlt)
        try {
            $stmt = $pdo->prepare("SELECT must_change_password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user && $user['must_change_password'] == 1) {
                $_SESSION['must_change_password'] = true;
                header('Location: force_password_change.php');
                exit;
            }
        } catch (PDOException $e) {
            // Bei DB-Fehler: weitermachen (Fehler nicht blockieren)
            error_log("Password change middleware error: " . $e->getMessage());
        }
    }
}
?>