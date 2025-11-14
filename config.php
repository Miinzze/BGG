<?php
// .env laden
require_once __DIR__ . '/load_env.php';

// Sichere Session-Konfiguration (MUSS vor session_start() sein!)
// Nur setzen wenn Session noch nicht gestartet wurde
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Auf 1 setzen wenn HTTPS!
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    
    // Session starten (NACH ini_set)
    session_start();
}

require_once __DIR__ . '/csrf_ajax_protection.php';

// Functions.php laden (WICHTIG: VOR permission_middleware.php!)
require_once 'asset_minifier.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/image_optimizer.php';
require_once __DIR__ . '/security_functions.php'; // NEU: Sicherheitsfunktionen

// Permission Middleware laden (NACH functions.php)
require_once __DIR__ . '/permission_middleware.php';
require_once __DIR__ . '/auto_activity_logger.php';
require_once __DIR__ . '/extended_activity_logger.php';
require_once __DIR__ . '/performance_cache.php';

// CSRF-Token generieren
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF-Token validieren
function validateCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            die('CSRF-Token fehlt');
        }
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('Ungültiger CSRF-Token');
        }
    }
}

function csrf_field() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

// Security Headers - GANZ AM ANFANG
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://unpkg.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://unpkg.com https://cdnjs.cloudflare.com 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self' https://unpkg.com https://cdn.jsdelivr.net https://api.qrserver.com;");header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Session-Timeout (30 Minuten Inaktivität)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'd044f149');
define('DB_USER', $_ENV['DB_USER'] ?? 'd044f149');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

// Datenbank-Verbindung
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    // Explizit UTF-8 Encoding setzen für korrekte Umlaut-Behandlung
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

require_once __DIR__ . '/simple_file_cache.php';
$cache = new SimpleFileCache();

// Benutzer-Rollen
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
define('ROLE_VIEWER', 'viewer');

// Upload-Verzeichnis
define('UPLOAD_DIR', 'uploads/');
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Weclapp-Konfiguration
// Format: https://IhrTenantName.weclapp.com (ohne /webapp am Ende)
// Beispiel: https://musterfirma.weclapp.com
define('WECLAPP_TENANT_URL', $_ENV['WECLAPP_TENANT_URL'] ?? 'https://IhrTenantName.weclapp.com');
?>