<?php
/**
 * SECURITY FUNCTIONS
 * Erweiterte Sicherheitsfunktionen für Login und Passwort-Management
 */

// ================================================
// BRUTE-FORCE PROTECTION
// ================================================

/**
 * Prüft ob Account gesperrt ist
 */
function isAccountLocked($userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT account_locked, locked_until, failed_login_count 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) return false;
    
    // Wenn locked_until abgelaufen ist, entsperren
    if ($user['account_locked'] && $user['locked_until']) {
        if (strtotime($user['locked_until']) < time()) {
            unlockAccount($userId, $pdo);
            return false;
        }
        return true;
    }
    
    return $user['account_locked'];
}

/**
 * Sperrt Account nach zu vielen Fehlversuchen
 */
function lockAccount($userId, $pdo, $minutes = 30) {
    $lockedUntil = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET account_locked = 1,
            locked_until = ?,
            failed_login_count = 0
        WHERE id = ?
    ");
    $stmt->execute([$lockedUntil, $userId]);
    
    // E-Mail-Benachrichtigung senden
    sendSecurityNotification($userId, 'account_locked', $pdo, [
        'locked_until' => $lockedUntil,
        'reason' => 'Zu viele Fehlversuche'
    ]);
}

/**
 * Entsperrt Account
 */
function unlockAccount($userId, $pdo) {
    $stmt = $pdo->prepare("
        UPDATE users 
        SET account_locked = 0,
            locked_until = NULL,
            failed_login_count = 0
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
}

/**
 * Login-Versuch aufzeichnen
 */
function logLoginAttempt($usernameOrEmail, $pdo, $success = false, $userId = null) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (username_or_email, ip_address, user_agent, success, user_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$usernameOrEmail, $ipAddress, $userAgent, $success, $userId]);
    
    // Wenn Login fehlgeschlagen, Fehlversuch-Counter erhöhen
    if (!$success && $userId) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET failed_login_count = failed_login_count + 1,
                last_failed_login = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        
        // Prüfen ob Account gesperrt werden muss
        $stmt = $pdo->prepare("SELECT failed_login_count FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $failedCount = $stmt->fetchColumn();
        
        if ($failedCount >= 5) {
            lockAccount($userId, $pdo, 30); // 30 Minuten Sperre
        } elseif ($failedCount >= 3) {
            // Bei 3+ Fehlversuchen: Warnung per E-Mail
            sendSecurityNotification($userId, 'suspicious_login', $pdo, [
                'failed_attempts' => $failedCount,
                'ip_address' => $ipAddress
            ]);
        }
    }
    
    // Bei erfolgreichem Login: Counter zurücksetzen
    if ($success && $userId) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET failed_login_count = 0,
                last_failed_login = NULL
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
}

/**
 * Prüft ob verdächtige Login-Aktivität vorliegt
 */
function checkSuspiciousActivity($userId, $pdo) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Prüfe: Mehr als 3 Fehlversuche in letzten 10 Minuten
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM login_attempts 
        WHERE user_id = ? 
        AND success = 0 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $stmt->execute([$userId]);
    $recentFails = $stmt->fetchColumn();
    
    if ($recentFails >= 3) {
        return true;
    }
    
    // Prüfe: Login von neuer IP-Adresse
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM login_attempts 
        WHERE user_id = ? 
        AND success = 1 
        AND ip_address = ?
    ");
    $stmt->execute([$userId, $ipAddress]);
    $knownIP = $stmt->fetchColumn();
    
    if ($knownIP == 0) {
        // Neue IP - Benachrichtigung senden
        sendSecurityNotification($userId, 'new_location', $pdo, [
            'ip_address' => $ipAddress
        ]);
    }
    
    return false;
}

// ================================================
// PASSWORT-HISTORIE
// ================================================

/**
 * Speichert Passwort in Historie
 */
function savePasswordHistory($userId, $passwordHash, $pdo) {
    $stmt = $pdo->prepare("
        INSERT INTO password_history (user_id, password_hash)
        VALUES (?, ?)
    ");
    $stmt->execute([$userId, $passwordHash]);
    
    // Nur letzten 5 behalten
    $stmt = $pdo->prepare("
        DELETE FROM password_history 
        WHERE user_id = ? 
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM password_history 
                WHERE user_id = ? 
                ORDER BY changed_at DESC 
                LIMIT 5
            ) AS recent
        )
    ");
    $stmt->execute([$userId, $userId]);
}

/**
 * Prüft ob Passwort bereits verwendet wurde
 */
function isPasswordReused($userId, $newPassword, $pdo) {
    $stmt = $pdo->prepare("
        SELECT password_hash 
        FROM password_history 
        WHERE user_id = ? 
        ORDER BY changed_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $history = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($history as $oldHash) {
        if (password_verify($newPassword, $oldHash)) {
            return true;
        }
    }
    
    return false;
}

// ================================================
// SESSION MANAGEMENT
// ================================================

/**
 * Erstellt neue Session in Datenbank
 */
function createUserSession($userId, $pdo) {
    $sessionId = session_id();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $deviceInfo = getUserDeviceInfo();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Alte inaktive Sessions des Users bereinigen
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET is_active = 0 
        WHERE user_id = ? 
        AND last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$userId]);
    
    // Neue Session erstellen
    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, device_info, last_activity, expires_at)
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
        ON DUPLICATE KEY UPDATE 
            last_activity = NOW(),
            expires_at = ?
    ");
    $stmt->execute([$userId, $sessionId, $ipAddress, $userAgent, $deviceInfo, $expiresAt, $expiresAt]);
}

/**
 * Aktualisiert Session-Aktivität
 */
function updateSessionActivity($userId, $pdo) {
    $sessionId = session_id();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $pdo->prepare("
        UPDATE user_sessions 
        SET last_activity = NOW(),
            expires_at = ?
        WHERE user_id = ? 
        AND session_id = ?
    ");
    $stmt->execute([$expiresAt, $userId, $sessionId]);
}

/**
 * Beendet alle Sessions eines Users (außer der aktuellen)
 */
function logoutAllOtherSessions($userId, $pdo, $keepCurrent = true) {
    $currentSessionId = session_id();
    
    if ($keepCurrent) {
        $stmt = $pdo->prepare("
            UPDATE user_sessions 
            SET is_active = 0 
            WHERE user_id = ? 
            AND session_id != ?
        ");
        $stmt->execute([$userId, $currentSessionId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE user_sessions 
            SET is_active = 0 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    logActivity('all_sessions_logout', 'Alle Sitzungen beendet');
}

/**
 * Holt aktive Sessions eines Users
 */
function getActiveSessions($userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM user_sessions 
        WHERE user_id = ? 
        AND is_active = 1 
        AND expires_at > NOW()
        ORDER BY last_activity DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Ermittelt Geräte-Info aus User-Agent
 */
function getUserDeviceInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (preg_match('/mobile/i', $userAgent)) {
        if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            return 'iOS Mobile';
        } elseif (preg_match('/Android/i', $userAgent)) {
            return 'Android Mobile';
        }
        return 'Mobile Device';
    }
    
    if (preg_match('/Windows/i', $userAgent)) {
        return 'Windows PC';
    } elseif (preg_match('/Mac/i', $userAgent)) {
        return 'Mac';
    } elseif (preg_match('/Linux/i', $userAgent)) {
        return 'Linux';
    }
    
    return 'Unknown Device';
}

// ================================================
// E-MAIL BENACHRICHTIGUNGEN
// ================================================

/**
 * Sendet Sicherheits-Benachrichtigung per E-Mail
 */
function sendSecurityNotification($userId, $type, $pdo, $details = []) {
    // User-Daten laden
    $stmt = $pdo->prepare("SELECT username, email, first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || empty($user['email'])) {
        return false;
    }
    
    $name = trim($user['first_name'] . ' ' . $user['last_name']) ?: $user['username'];
    $email = $user['email'];
    
    // E-Mail-Inhalt je nach Typ
    switch ($type) {
        case 'account_locked':
            $subject = '⚠️ Ihr Account wurde gesperrt';
            $message = "
                <p>Hallo $name,</p>
                <p>Ihr Account wurde aufgrund zu vieler Fehlversuche vorübergehend gesperrt.</p>
                <p><strong>Gesperrt bis:</strong> {$details['locked_until']}</p>
                <p><strong>Grund:</strong> {$details['reason']}</p>
                <p>Falls Sie das waren, können Sie nach Ablauf der Sperrfrist erneut versuchen sich anzumelden.</p>
                <p>Falls Sie das nicht waren, kontaktieren Sie bitte umgehend den Administrator.</p>
            ";
            break;
            
        case 'suspicious_login':
            $subject = '⚠️ Verdächtige Login-Aktivität';
            $ipAddress = $details['ip_address'] ?? 'unbekannt';
            $failedAttempts = $details['failed_attempts'] ?? 0;
            $message = "
                <p>Hallo $name,</p>
                <p>Es gab mehrere fehlgeschlagene Login-Versuche auf Ihrem Account.</p>
                <p><strong>Anzahl Fehlversuche:</strong> $failedAttempts</p>
                <p><strong>IP-Adresse:</strong> $ipAddress</p>
                <p><strong>Zeitpunkt:</strong> " . date('d.m.Y H:i:s') . " Uhr</p>
                <p>Falls Sie das waren, ignorieren Sie diese E-Mail.</p>
                <p>Falls nicht, ändern Sie bitte umgehend Ihr Passwort!</p>
            ";
            break;
            
        case 'new_location':
            $ipAddress = $details['ip_address'] ?? 'unbekannt';
            $subject = '🌍 Login von neuer IP-Adresse';
            $message = "
                <p>Hallo $name,</p>
                <p>Es gab einen erfolgreichen Login von einer neuen IP-Adresse.</p>
                <p><strong>IP-Adresse:</strong> $ipAddress</p>
                <p><strong>Zeitpunkt:</strong> " . date('d.m.Y H:i:s') . " Uhr</p>
                <p>Falls Sie das waren, können Sie diese E-Mail ignorieren.</p>
                <p>Falls nicht, ändern Sie bitte sofort Ihr Passwort und kontaktieren Sie den Administrator!</p>
            ";
            break;
            
        case 'password_changed':
            $subject = '✅ Passwort wurde geändert';
            $message = "
                <p>Hallo $name,</p>
                <p>Ihr Passwort wurde erfolgreich geändert.</p>
                <p><strong>Zeitpunkt:</strong> " . date('d.m.Y H:i:s') . " Uhr</p>
                <p>Falls Sie das nicht waren, kontaktieren Sie sofort den Administrator!</p>
            ";
            break;
            
        default:
            return false;
    }
    
    // E-Mail versenden
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: BGG System <noreply@bgg-system.de>\r\n";
    
    $htmlMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;'>
                <div style='background: #007bff; color: white; padding: 15px; text-align: center;'>
                    <h2 style='margin: 0;'>BGG Geräteverwaltung</h2>
                </div>
                <div style='background: white; padding: 20px; margin-top: 20px;'>
                    $message
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                    <p style='font-size: 12px; color: #666;'>
                        Diese E-Mail wurde automatisch generiert. Bitte nicht darauf antworten.
                    </p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    $sent = mail($email, $subject, $htmlMessage, $headers);
    
    // Log in Datenbank
    $stmt = $pdo->prepare("
        INSERT INTO security_notifications (user_id, notification_type, details)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $type, json_encode($details)]);
    
    return $sent;
}

// ================================================
// PASSWORT-STÄRKE PRÜFUNG (erweitert)
// ================================================

/**
 * Erweiterte Passwort-Stärke Prüfung
 */
function validatePasswordStrengthExtended($password) {
    $errors = [];
    $score = 0;
    
    // Mindestlänge
    if (strlen($password) < 8) {
        $errors[] = 'Mindestens 8 Zeichen';
    } else {
        $score += 1;
    }
    
    // Großbuchstaben
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Mindestens ein Großbuchstabe';
    } else {
        $score += 1;
    }
    
    // Kleinbuchstaben
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Mindestens ein Kleinbuchstabe';
    } else {
        $score += 1;
    }
    
    // Zahlen
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Mindestens eine Zahl';
    } else {
        $score += 1;
    }
    
    // Sonderzeichen (optional, gibt Bonus-Punkte)
    if (preg_match('/[^A-Za-z0-9]/', $password)) {
        $score += 2;
    }
    
    // Länge-Bonus
    if (strlen($password) >= 12) {
        $score += 1;
    }
    if (strlen($password) >= 16) {
        $score += 1;
    }
    
    // Stärke bewerten
    if ($score <= 3) {
        $strength = 'weak';
        $strengthText = 'Schwach';
    } elseif ($score <= 5) {
        $strength = 'medium';
        $strengthText = 'Mittel';
    } else {
        $strength = 'strong';
        $strengthText = 'Stark';
    }
    
    return [
        'valid' => count($errors) == 0,
        'message' => count($errors) > 0 ? implode(', ', $errors) : 'Passwort ist stark',
        'errors' => $errors,
        'score' => $score,
        'strength' => $strength,
        'strength_text' => $strengthText
    ];
}