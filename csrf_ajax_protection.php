<?php
/**
 * ===============================================
 * CSRF-SCHUTZ FÜR AJAX-REQUESTS
 * ===============================================
 * 
 * Erweiterte CSRF-Token-Validierung speziell für AJAX/Fetch-Requests
 * 
 * FEATURES:
 * - Session-basierte CSRF-Tokens
 * - Automatische Token-Rotation
 * - AJAX-Header-Validierung
 * - Rate-Limiting pro IP
 * - Token-Ablauf (30 Minuten)
 * 
 * INTEGRATION:
 * In config.php NACH session_start():
 * require_once __DIR__ . '/csrf_ajax_protection.php';
 * 
 * CLIENT-SEITE (JavaScript):
 * const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
 * fetch('/api/endpoint', {
 *     method: 'POST',
 *     headers: {
 *         'X-CSRF-Token': csrfToken
 *     },
 *     body: formData
 * });
 */

/**
 * ===============================================
 * KONFIGURATION
 * ===============================================
 */

// CSRF-Token Lebensdauer (Sekunden)
define('CSRF_TOKEN_LIFETIME', 1800); // 30 Minuten

// Rate Limiting (Anfragen pro Minute)
define('CSRF_RATE_LIMIT', 60);

/**
 * ===============================================
 * CSRF-TOKEN GENERIERUNG
 * ===============================================
 */

/**
 * Generiere neues CSRF-Token
 */
function generateCSRFToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

/**
 * Hole aktuelles CSRF-Token (erstelle neues wenn nicht vorhanden)
 */
function getCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return generateCSRFToken();
    }
    
    // Prüfe ob Token abgelaufen ist
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        return generateCSRFToken();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * ===============================================
 * CSRF-TOKEN VALIDIERUNG
 * ===============================================
 */

/**
 * Validiere CSRF-Token aus verschiedenen Quellen
 * 
 * @param bool $ajax Ob dies ein AJAX-Request ist
 * @return bool True wenn valid, False wenn invalid
 */
function validateCSRFToken($ajax = false) {
    // Hole Token aus verschiedenen Quellen
    $token = null;
    
    if ($ajax) {
        // Für AJAX: Prüfe Header zuerst
        $headers = getallheaders();
        if (isset($headers['X-CSRF-Token'])) {
            $token = $headers['X-CSRF-Token'];
        } elseif (isset($headers['X-Csrf-Token'])) {
            $token = $headers['X-Csrf-Token'];
        }
    }
    
    // Fallback: POST-Parameter
    if (!$token && isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }
    
    // Fallback: GET-Parameter (nur für bestimmte Fälle)
    if (!$token && isset($_GET['csrf_token'])) {
        $token = $_GET['csrf_token'];
    }
    
    // Prüfe ob Token vorhanden ist
    if (!$token) {
        logCSRFViolation('Token fehlt');
        return false;
    }
    
    // Prüfe ob Session-Token existiert
    if (!isset($_SESSION['csrf_token'])) {
        logCSRFViolation('Kein Session-Token');
        return false;
    }
    
    // Prüfe ob Token abgelaufen ist
    if (isset($_SESSION['csrf_token_time'])) {
        if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
            logCSRFViolation('Token abgelaufen');
            return false;
        }
    }
    
    // Hash-Vergleich (timing-attack sicher)
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        logCSRFViolation('Token ungültig');
        return false;
    }
    
    // Prüfe Rate Limiting
    if (!checkRateLimit()) {
        logCSRFViolation('Rate Limit überschritten');
        return false;
    }
    
    return true;
}

/**
 * Middleware für AJAX-Requests
 * Rufe dies am Anfang von AJAX-Endpoints auf
 */
function requireAjaxCSRF() {
    // Prüfe ob es ein AJAX-Request ist
    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
    ) || (
        isset($_SERVER['CONTENT_TYPE']) && 
        strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
    );
    
    // Nur POST/PUT/DELETE/PATCH prüfen
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        return true;
    }
    
    // Validiere Token
    if (!validateCSRFToken($isAjax)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'CSRF-Token ungültig',
            'message' => 'Sicherheitsvalidierung fehlgeschlagen. Bitte laden Sie die Seite neu.'
        ]);
        exit;
    }
    
    return true;
}

/**
 * ===============================================
 * RATE LIMITING
 * ===============================================
 */

/**
 * Prüfe Rate Limit für aktuelle IP
 */
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'csrf_rate_' . md5($ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 1,
            'start' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    // Reset wenn Minute vorbei ist
    if (time() - $data['start'] > 60) {
        $_SESSION[$key] = [
            'count' => 1,
            'start' => time()
        ];
        return true;
    }
    
    // Increment counter
    $data['count']++;
    $_SESSION[$key] = $data;
    
    // Prüfe Limit
    if ($data['count'] > CSRF_RATE_LIMIT) {
        return false;
    }
    
    return true;
}

/**
 * ===============================================
 * LOGGING
 * ===============================================
 */

/**
 * Logge CSRF-Verletzungen
 */
function logCSRFViolation($reason) {
    $logEntry = sprintf(
        "[%s] CSRF Violation - IP: %s, Reason: %s, URL: %s, Referer: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'],
        $reason,
        $_SERVER['REQUEST_URI'] ?? 'unknown',
        $_SERVER['HTTP_REFERER'] ?? 'none'
    );
    
    $logFile = __DIR__ . '/logs/csrf_violations.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * ===============================================
 * HELPER FUNCTIONS
 * ===============================================
 */

/**
 * Erstelle Meta-Tag für CSRF-Token (für <head>)
 */
function csrfMetaTag() {
    $token = getCSRFToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}

/**
 * Erstelle Hidden Input für CSRF-Token (für Formulare)
 */
function csrfField() {
    $token = getCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Erstelle JavaScript-Variable für CSRF-Token
 */
function csrfScript() {
    $token = getCSRFToken();
    return '<script>window.CSRF_TOKEN = "' . htmlspecialchars($token) . '";</script>';
}

/**
 * Hole alle Headers (case-insensitive)
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * ===============================================
 * AUTOMATISCHE INITIALISIERUNG
 * ===============================================
 */

// Generiere Token wenn Session aktiv ist
if (session_status() === PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['csrf_token'])) {
        generateCSRFToken();
    }
}