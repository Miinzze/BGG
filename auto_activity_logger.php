<?php
/**
 * AUTOMATISCHES ACTIVITY LOGGING
 * 
 * Diese Datei erweitert das System um automatisches Logging ALLER Benutzeraktionen.
 * Einbinden in config.php NACH dem Session-Start und PDO-Setup:
 * require_once __DIR__ . '/auto_activity_logger.php';
 * 
 * WICHTIG: Diese Datei ersetzt NICHT das bestehende System, sondern erweitert es!
 */

// Nur ausführen wenn Session und PDO vorhanden
if (!isset($_SESSION) || !isset($pdo)) {
    return;
}

// Prüfe ob bereits initialisiert
if (defined('AUTO_ACTIVITY_LOGGER_LOADED')) {
    return;
}
define('AUTO_ACTIVITY_LOGGER_LOADED', true);

/**
 * Erweiterte logActivity Funktion die automatisch mehr Details erfasst
 */
function logActivity($action, $details = '', $markerId = null) {
    global $pdo;
    
    if (!isset($pdo)) {
        return false;
    }
    
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'guest';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Erweitere Details um JSON wenn nötig
        if (is_array($details)) {
            $details = json_encode($details, JSON_UNESCAPED_UNICODE);
        }
        
        // Validiere marker_id: Nur einfügen wenn sie in der markers Tabelle existiert oder NULL ist
        if ($markerId !== null) {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM markers WHERE id = ?");
            $checkStmt->execute([$markerId]);
            if ($checkStmt->fetchColumn() == 0) {
                // marker_id existiert nicht, setze auf NULL
                error_log("Activity Log Warning: marker_id $markerId existiert nicht in markers Tabelle, setze auf NULL");
                $markerId = null;
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_log 
            (user_id, username, action, details, marker_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId, 
            $username, 
            $action, 
            $details, 
            $markerId, 
            $ipAddress, 
            $userAgent
        ]);
    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Automatisches Logging von POST-Requests
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $script = basename($_SERVER['SCRIPT_NAME'], '.php');
    
    // Ignoriere bestimmte Skripte um Duplikate zu vermeiden
    $ignoreScripts = ['login', 'logout'];
    
    if (!in_array($script, $ignoreScripts)) {
        // Bestimme die Aktion basierend auf dem Skript und POST-Daten
        $action = $script;
        $details = [];
        
        // Spezielle Behandlung für bekannte Aktionen
        if (isset($_POST['action'])) {
            $action .= '_' . $_POST['action'];
        }
        
        // Marker-ID erfassen wenn vorhanden
        $markerId = null;
        if (isset($_POST['marker_id'])) {
            $markerId = $_POST['marker_id'];
        } elseif (isset($_GET['id']) && in_array($script, ['edit_marker', 'delete_marker', 'view_marker'])) {
            $markerId = $_GET['id'];
        }
        
        // Sammle relevante POST-Daten (ohne sensible Daten)
        $excludeFields = ['password', 'password_confirm', 'csrf_token', 'api_key', 'secret'];
        foreach ($_POST as $key => $value) {
            if (!in_array($key, $excludeFields) && !is_array($value)) {
                if (strlen($value) < 100) { // Nur kurze Werte speichern
                    $details[$key] = $value;
                }
            }
        }
        
        logActivity($action, $details, $markerId);
    }
}

/**
 * Hook für spezifische Aktionen in verschiedenen Dateien
 * Diese Funktionen werden automatisch aufgerufen wenn die entsprechenden Aktionen passieren
 */

// Login/Logout Tracking
if (isset($_SESSION['user_id']) && !isset($_SESSION['activity_logged'])) {
    $_SESSION['activity_logged'] = true;
}

/**
 * Wrapper-Funktionen für häufige Aktionen
 */

// Marker erstellen
function logMarkerCreated($markerId, $markerName, $qrCode) {
    logActivity('marker_created', "Marker '$markerName' erstellt mit QR-Code: $qrCode", $markerId);
}

// Marker bearbeiten
function logMarkerUpdated($markerId, $markerName, $changes = []) {
    $details = "Marker '$markerName' bearbeitet";
    if (!empty($changes)) {
        $details = [
            'marker_name' => $markerName,
            'changes' => $changes
        ];
    }
    logActivity('marker_updated', $details, $markerId);
}

// Marker löschen
function logMarkerDeleted($markerId, $markerName) {
    logActivity('marker_deleted', "Marker '$markerName' gelöscht", $markerId);
}

// Marker aktivieren/deaktivieren
function logMarkerActivationChanged($markerId, $markerName, $isActivated) {
    $status = $isActivated ? 'aktiviert' : 'deaktiviert';
    logActivity('marker_activation_changed', "Marker '$markerName' $status", $markerId);
}

// QR-Code scannen
function logQRCodeScanned($qrCode, $markerId = null) {
    logActivity('qr_code_scanned', "QR-Code '$qrCode' gescannt", $markerId);
}

// Wartung hinzufügen
function logMaintenanceAdded($markerId, $maintenanceType) {
    logActivity('maintenance_added', "Wartung hinzugefügt: $maintenanceType", $markerId);
}

// Wartung gelöscht
function logMaintenanceDeleted($markerId, $maintenanceId) {
    logActivity('maintenance_deleted', "Wartung #$maintenanceId gelöscht", $markerId);
}

// Wartung abgeschlossen
function logMaintenanceCompleted($markerId, $maintenanceId) {
    logActivity('maintenance_completed', "Wartung #$maintenanceId abgeschlossen", $markerId);
}

// Inspektion erstellt
function logInspectionCreated($markerId, $inspectionId) {
    logActivity('inspection_created', "Inspektion #$inspectionId erstellt", $markerId);
}

// Inspektion abgeschlossen
function logInspectionCompleted($markerId, $inspectionId) {
    logActivity('inspection_completed', "Inspektion #$inspectionId abgeschlossen", $markerId);
}

// Benutzer erstellt
function logUserCreated($userId, $username) {
    logActivity('user_created', "Benutzer '$username' erstellt");
}

// Benutzer bearbeitet
function logUserUpdated($userId, $username) {
    logActivity('user_updated', "Benutzer '$username' bearbeitet");
}

// Benutzer gelöscht
function logUserDeleted($userId, $username) {
    logActivity('user_deleted', "Benutzer '$username' gelöscht");
}

// Rolle erstellt
function logRoleCreated($roleId, $roleName) {
    logActivity('role_created', "Rolle '$roleName' erstellt");
}

// Rolle bearbeitet
function logRoleUpdated($roleId, $roleName) {
    logActivity('role_updated', "Rolle '$roleName' bearbeitet");
}

// Rolle gelöscht
function logRoleDeleted($roleId, $roleName) {
    logActivity('role_deleted', "Rolle '$roleName' gelöscht");
}

// Permission geändert
function logPermissionChanged($userId, $permissionKey, $granted) {
    $action = $granted ? 'erteilt' : 'entzogen';
    logActivity('permission_changed', "Berechtigung '$permissionKey' $action für Benutzer #$userId");
}

// Einstellung geändert
function logSettingChanged($settingKey, $oldValue, $newValue) {
    logActivity('setting_changed', [
        'setting' => $settingKey,
        'old_value' => $oldValue,
        'new_value' => $newValue
    ]);
}

// Datei hochgeladen
function logFileUploaded($fileName, $fileSize, $markerId = null) {
    logActivity('file_uploaded', "Datei '$fileName' hochgeladen (" . formatBytes($fileSize) . ")", $markerId);
}

// Datei gelöscht
function logFileDeleted($fileName, $markerId = null) {
    logActivity('file_deleted', "Datei '$fileName' gelöscht", $markerId);
}

// Export
function logDataExported($exportType, $recordCount) {
    logActivity('data_exported', "Export: $exportType ($recordCount Datensätze)");
}

// Import
function logDataImported($importType, $recordCount, $errors = 0) {
    $details = "Import: $importType ($recordCount Datensätze";
    if ($errors > 0) {
        $details .= ", $errors Fehler";
    }
    $details .= ")";
    logActivity('data_imported', $details);
}

// Geo-Fence erstellt
function logGeofenceCreated($geofenceId, $geofenceName) {
    logActivity('geofence_created', "Geo-Fence '$geofenceName' erstellt");
}

// Geo-Fence bearbeitet
function logGeofenceUpdated($geofenceId, $geofenceName) {
    logActivity('geofence_updated', "Geo-Fence '$geofenceName' bearbeitet");
}

// Geo-Fence gelöscht
function logGeofenceDeleted($geofenceId, $geofenceName) {
    logActivity('geofence_deleted', "Geo-Fence '$geofenceName' gelöscht");
}

// Kategorie erstellt
function logCategoryCreated($categoryName) {
    logActivity('category_created', "Kategorie '$categoryName' erstellt");
}

// Kategorie bearbeitet
function logCategoryUpdated($categoryName) {
    logActivity('category_updated', "Kategorie '$categoryName' bearbeitet");
}

// Kategorie gelöscht
function logCategoryDeleted($categoryName) {
    logActivity('category_deleted', "Kategorie '$categoryName' gelöscht");
}

// Custom Field erstellt
function logCustomFieldCreated($fieldId, $fieldName) {
    logActivity('custom_field_created', "Custom Field '$fieldName' erstellt");
}

// Custom Field gelöscht
function logCustomFieldDeleted($fieldId, $fieldName) {
    logActivity('custom_field_deleted', "Custom Field '$fieldName' gelöscht");
}

// Template erstellt
function logTemplateCreated($templateId, $templateName) {
    logActivity('template_created', "Template '$templateName' erstellt");
}

// Template gelöscht
function logTemplateDeleted($templateId, $templateName) {
    logActivity('template_deleted', "Template '$templateName' gelöscht");
}

// Bug-Report erstellt
function logBugReported($bugId, $bugTitle) {
    logActivity('bug_reported', "Bug-Report erstellt: $bugTitle");
}

// Suchanfrage
function logSearchPerformed($searchTerm, $resultCount) {
    logActivity('search_performed', "Suche: '$searchTerm' ($resultCount Ergebnisse)");
}

// Report generiert
function logReportGenerated($reportType) {
    logActivity('report_generated', "Report generiert: $reportType");
}

// Bulk-Operation
function logBulkOperation($operation, $affectedCount) {
    logActivity('bulk_operation', "Bulk-Operation: $operation ($affectedCount Elemente)");
}

// Passwort geändert
function logPasswordChanged($userId, $username) {
    logActivity('password_changed', "Passwort geändert für Benutzer '$username'");
}

// 2FA aktiviert/deaktiviert
function log2FAChanged($userId, $username, $enabled) {
    $status = $enabled ? 'aktiviert' : 'deaktiviert';
    logActivity('2fa_changed', "2FA $status für Benutzer '$username'");
}

/**
 * Helper-Funktion für Dateigröße
 */
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

/**
 * Automatisches Fehler-Logging
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Nur kritische Fehler loggen
    if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR) {
        logActivity('system_error', [
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]);
    }
    
    // Standard Error Handler
    return false;
});

/**
 * Session-Timeout Tracking
 */
if (isset($_SESSION['last_activity'])) {
    $inactiveTime = time() - $_SESSION['last_activity'];
    
    // Logge wenn Benutzer lange inaktiv war (> 30 Minuten)
    if ($inactiveTime > 1800 && !isset($_SESSION['inactivity_logged'])) {
        logActivity('user_inactive', "Benutzer war " . round($inactiveTime / 60) . " Minuten inaktiv");
        $_SESSION['inactivity_logged'] = true;
    }
    
    // Reset Flag wenn Aktivität erkannt
    if ($inactiveTime < 60) {
        unset($_SESSION['inactivity_logged']);
    }
}

/**
 * Automatische Integration in bestehende Funktionen
 * 
 * Die folgenden Aktionen werden automatisch geloggt wenn die entsprechenden
 * Funktionen aufgerufen werden. Dies funktioniert durch Hooks in den
 * bestehenden PHP-Dateien.
 */

// Monitoring: Speichere Start-Zeit für Performance-Tracking
if (!isset($_SESSION['request_start_time'])) {
    $_SESSION['request_start_time'] = microtime(true);
}

/**
 * Cleanup: Alte Logs automatisch bereinigen (optional)
 */
function cleanupOldActivityLogs($daysToKeep = 90) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            DELETE FROM activity_log 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$daysToKeep]);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Activity Log Cleanup Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Statistiken für Dashboard
 */
function getActivityStats($days = 30) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_activities,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT DATE(created_at)) as active_days,
                COUNT(DISTINCT marker_id) as unique_markers
            FROM activity_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Activity Stats Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Hook-System: Ermöglicht es anderen Dateien, automatisch zu loggen
 * 
 * Verwendung in anderen Dateien:
 * 
 * // In create_marker.php nach erfolgreichem INSERT:
 * if (function_exists('logMarkerCreated')) {
 *     logMarkerCreated($markerId, $markerName, $qrCode);
 * }
 * 
 * // In edit_marker.php nach erfolgreichem UPDATE:
 * if (function_exists('logMarkerUpdated')) {
 *     logMarkerUpdated($markerId, $markerName, $changes);
 * }
 */

/**
 * Debug-Modus (nur für Entwicklung)
 */
if (defined('ACTIVITY_LOG_DEBUG') && ACTIVITY_LOG_DEBUG === true) {
    error_log("Activity Logger geladen für: " . basename($_SERVER['SCRIPT_NAME']));
}