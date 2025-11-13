<?php
/**
 * ERWEITERTE ACTIVITY LOGGING FUNKTIONEN
 * 
 * Diese Datei erweitert das Auto Activity Logger System um zusätzliche Logging-Funktionen
 * für Aktionen die bisher nicht protokolliert wurden.
 * 
 * Integration:
 * require_once __DIR__ . '/extended_activity_logger.php';
 * 
 * Füge dieses Script in config.php NACH dem auto_activity_logger.php ein.
 */

// Verhindere doppeltes Laden
if (defined('EXTENDED_ACTIVITY_LOGGER_LOADED')) {
    return;
}
define('EXTENDED_ACTIVITY_LOGGER_LOADED', true);

// Prüfe ob PDO verfügbar ist - wenn nicht, noch nicht laden
if (!isset($pdo)) {
    // Wird später automatisch nachgeladen wenn PDO verfügbar ist
    return;
}

// Prüfe ob Session verfügbar ist
if (!isset($_SESSION)) {
    return;
}

/**
 * Fallback logActivity Funktion falls auto_activity_logger.php nicht geladen wurde
 * oder noch nicht verfügbar ist
 */
if (!function_exists('logActivity')) {
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
            
            // Validiere marker_id
            if ($markerId !== null) {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM markers WHERE id = ?");
                $checkStmt->execute([$markerId]);
                if ($checkStmt->fetchColumn() == 0) {
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
}

/**
 * ===================================================================
 * SEITENAUFRUFE (PAGEVIEWS) TRACKING
 * ===================================================================
 */

// Tracking für jede Seite die aufgerufen wird
if (isset($_SESSION['user_id'])) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $referer = $_SERVER['HTTP_REFERER'] ?? 'direct';
    
    // Ignoriere AJAX-Requests und API-Calls für Pageview-Tracking
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if (!$isAjax) {
        // Speichere Pageview in Session um Duplikate zu vermeiden
        $pageviewKey = 'pageview_' . $currentPage . '_' . time();
        
        if (!isset($_SESSION['last_pageview']) || 
            $_SESSION['last_pageview'] !== $pageviewKey) {
            
            $_SESSION['last_pageview'] = $pageviewKey;
            
            // Log the pageview
            logActivity('pageview', [
                'page' => $currentPage,
                'referer' => parse_url($referer, PHP_URL_PATH) ?? 'direct',
                'query_string' => $_SERVER['QUERY_STRING'] ?? ''
            ]);
        }
    }
}

/**
 * ===================================================================
 * KLICK-EVENTS AUF WICHTIGE BUTTONS
 * ===================================================================
 */

function logButtonClick($buttonName, $context = '') {
    logActivity('button_click', [
        'button' => $buttonName,
        'context' => $context,
        'page' => basename($_SERVER['PHP_SELF'])
    ]);
}

/**
 * ===================================================================
 * FILTER & SORTIERUNG ÄNDERUNGEN
 * ===================================================================
 */

function logFilterChange($filterType, $filterValue, $page = null) {
    if ($page === null) {
        $page = basename($_SERVER['PHP_SELF']);
    }
    
    logActivity('filter_changed', [
        'filter_type' => $filterType,
        'filter_value' => $filterValue,
        'page' => $page
    ]);
}

function logSortingChange($sortField, $sortOrder, $page = null) {
    if ($page === null) {
        $page = basename($_SERVER['PHP_SELF']);
    }
    
    logActivity('sorting_changed', [
        'sort_field' => $sortField,
        'sort_order' => $sortOrder,
        'page' => $page
    ]);
}

/**
 * ===================================================================
 * PAGINIERUNG
 * ===================================================================
 */

function logPaginationChange($page, $itemsPerPage, $context = '') {
    logActivity('pagination_change', [
        'page' => $page,
        'items_per_page' => $itemsPerPage,
        'context' => $context
    ]);
}

/**
 * ===================================================================
 * SUCHEN (MIT UND OHNE ERGEBNIS)
 * ===================================================================
 */

function logSearchWithResults($searchTerm, $resultCount, $searchType = 'general') {
    logActivity('search_success', [
        'search_term' => $searchTerm,
        'result_count' => $resultCount,
        'search_type' => $searchType
    ]);
}

function logSearchWithoutResults($searchTerm, $searchType = 'general') {
    logActivity('search_no_results', [
        'search_term' => $searchTerm,
        'search_type' => $searchType
    ]);
}

function logSearchError($searchTerm, $errorMessage) {
    logActivity('search_error', [
        'search_term' => $searchTerm,
        'error' => $errorMessage
    ]);
}

/**
 * ===================================================================
 * ABGEBROCHENE AKTIONEN
 * ===================================================================
 */

function logActionCancelled($actionType, $reason = '') {
    logActivity('action_cancelled', [
        'action_type' => $actionType,
        'reason' => $reason,
        'page' => basename($_SERVER['PHP_SELF'])
    ]);
}

function logFormAbandoned($formName, $fieldsCompleted = 0, $totalFields = 0) {
    logActivity('form_abandoned', [
        'form_name' => $formName,
        'fields_completed' => $fieldsCompleted,
        'total_fields' => $totalFields,
        'completion_rate' => $totalFields > 0 ? round(($fieldsCompleted / $totalFields) * 100, 2) : 0
    ]);
}

/**
 * ===================================================================
 * ZEIT AUF SEITE (TIME ON PAGE)
 * ===================================================================
 */

// Speichere Start-Zeit wenn Seite geladen wird
if (!isset($_SESSION['page_start_time'])) {
    $_SESSION['page_start_time'] = microtime(true);
    $_SESSION['current_page'] = basename($_SERVER['PHP_SELF']);
}

// Funktion zum Loggen der Verweildauer
function logTimeOnPage() {
    if (isset($_SESSION['page_start_time']) && isset($_SESSION['current_page'])) {
        $timeSpent = microtime(true) - $_SESSION['page_start_time'];
        
        // Logge nur wenn mindestens 5 Sekunden auf der Seite verbracht wurden
        if ($timeSpent >= 5) {
            logActivity('time_on_page', [
                'page' => $_SESSION['current_page'],
                'time_spent_seconds' => round($timeSpent, 2),
                'time_spent_formatted' => formatTimeSpent($timeSpent)
            ]);
        }
        
        // Reset für nächste Seite
        $_SESSION['page_start_time'] = microtime(true);
        $_SESSION['current_page'] = basename($_SERVER['PHP_SELF']);
    }
}

// Automatisch Zeit loggen wenn Seite gewechselt wird
register_shutdown_function('logTimeOnPage');

/**
 * ===================================================================
 * AR-NAVIGATION
 * ===================================================================
 */

function logARNavigationStart($markerId = null, $targetLocation = '') {
    logActivity('ar_navigation_start', [
        'target_location' => $targetLocation,
        'device_type' => getDeviceType()
    ], $markerId);
}

function logARNavigationStop($markerId = null, $duration = 0, $reached = false) {
    logActivity('ar_navigation_stop', [
        'duration_seconds' => $duration,
        'target_reached' => $reached,
        'device_type' => getDeviceType()
    ], $markerId);
}

function logARMarkerScanned($markerId, $scanMethod = 'camera') {
    logActivity('ar_marker_scanned', [
        'scan_method' => $scanMethod,
        'device_type' => getDeviceType()
    ], $markerId);
}

/**
 * ===================================================================
 * KAMERA-NUTZUNG
 * ===================================================================
 */

function logCameraOpened($purpose = 'general', $markerId = null) {
    logActivity('camera_opened', [
        'purpose' => $purpose,
        'device_type' => getDeviceType()
    ], $markerId);
}

function logCameraClosed($purpose = 'general', $photosTaken = 0, $markerId = null) {
    logActivity('camera_closed', [
        'purpose' => $purpose,
        'photos_taken' => $photosTaken,
        'device_type' => getDeviceType()
    ], $markerId);
}

function logPhotoCapture($photoName, $fileSize, $markerId = null) {
    logActivity('photo_captured', [
        'photo_name' => $photoName,
        'file_size' => formatBytes($fileSize),
        'device_type' => getDeviceType()
    ], $markerId);
}

function logPhotoEditStart($photoId, $markerId = null) {
    logActivity('photo_edit_start', [
        'photo_id' => $photoId
    ], $markerId);
}

function logPhotoEditComplete($photoId, $editType = 'general', $markerId = null) {
    logActivity('photo_edit_complete', [
        'photo_id' => $photoId,
        'edit_type' => $editType
    ], $markerId);
}

/**
 * ===================================================================
 * GEO-FENCE EIN/AUSTRITT
 * ===================================================================
 */

function logGeofenceEnter($geofenceId, $geofenceName, $markerId = null, $location = []) {
    logActivity('geofence_enter', [
        'geofence_id' => $geofenceId,
        'geofence_name' => $geofenceName,
        'latitude' => $location['lat'] ?? null,
        'longitude' => $location['lng'] ?? null
    ], $markerId);
}

function logGeofenceExit($geofenceId, $geofenceName, $timeInside = 0, $markerId = null) {
    logActivity('geofence_exit', [
        'geofence_id' => $geofenceId,
        'geofence_name' => $geofenceName,
        'time_inside_seconds' => $timeInside,
        'time_inside_formatted' => formatTimeSpent($timeInside)
    ], $markerId);
}

function logGeofenceViolation($geofenceId, $geofenceName, $violationType, $markerId = null) {
    logActivity('geofence_violation', [
        'geofence_id' => $geofenceId,
        'geofence_name' => $geofenceName,
        'violation_type' => $violationType
    ], $markerId);
}

/**
 * ===================================================================
 * PDF-DOWNLOADS
 * ===================================================================
 */

function logPDFDownload($pdfType, $pdfName, $markerId = null) {
    logActivity('pdf_download', [
        'pdf_type' => $pdfType,
        'pdf_name' => $pdfName,
        'page' => basename($_SERVER['PHP_SELF'])
    ], $markerId);
}

function logPDFGenerated($pdfType, $pdfName, $generationTime = 0, $markerId = null) {
    logActivity('pdf_generated', [
        'pdf_type' => $pdfType,
        'pdf_name' => $pdfName,
        'generation_time_seconds' => $generationTime
    ], $markerId);
}

function logPDFViewed($pdfType, $pdfName, $markerId = null) {
    logActivity('pdf_viewed', [
        'pdf_type' => $pdfType,
        'pdf_name' => $pdfName
    ], $markerId);
}

/**
 * ===================================================================
 * BILDUPLOAD
 * ===================================================================
 */

function logImageUploadStart($imageCount = 1, $markerId = null) {
    logActivity('image_upload_start', [
        'image_count' => $imageCount,
        'page' => basename($_SERVER['PHP_SELF'])
    ], $markerId);
}

function logImageUploadComplete($imageName, $fileSize, $imageType, $markerId = null) {
    logActivity('image_upload_complete', [
        'image_name' => $imageName,
        'file_size' => formatBytes($fileSize),
        'image_type' => $imageType
    ], $markerId);
}

function logImageUploadFailed($imageName, $errorMessage, $markerId = null) {
    logActivity('image_upload_failed', [
        'image_name' => $imageName,
        'error' => $errorMessage
    ], $markerId);
}

function logBulkImageUpload($imageCount, $successCount, $failCount, $markerId = null) {
    logActivity('bulk_image_upload', [
        'total_images' => $imageCount,
        'success_count' => $successCount,
        'fail_count' => $failCount,
        'success_rate' => $imageCount > 0 ? round(($successCount / $imageCount) * 100, 2) : 0
    ], $markerId);
}

/**
 * ===================================================================
 * ZUSÄTZLICHE MARKER-AKTIONEN
 * ===================================================================
 */

function logMarkerViewed($markerId, $markerName, $viewDuration = 0) {
    logActivity('marker_viewed', [
        'marker_name' => $markerName,
        'view_duration_seconds' => $viewDuration
    ], $markerId);
}

function logMarkerExport($markerIds, $exportFormat = 'csv') {
    logActivity('markers_exported', [
        'marker_count' => count($markerIds),
        'export_format' => $exportFormat
    ]);
}

function logMarkerImport($importedCount, $failedCount = 0, $importFormat = 'csv') {
    logActivity('markers_imported', [
        'imported_count' => $importedCount,
        'failed_count' => $failedCount,
        'import_format' => $importFormat,
        'success_rate' => ($importedCount + $failedCount) > 0 
            ? round(($importedCount / ($importedCount + $failedCount)) * 100, 2) 
            : 0
    ]);
}

/**
 * ===================================================================
 * QR-CODE & NFC AKTIONEN
 * ===================================================================
 */

function logQRCodeGenerated($qrCode, $qrType = 'standard', $markerId = null) {
    logActivity('qr_code_generated', [
        'qr_code' => $qrCode,
        'qr_type' => $qrType
    ], $markerId);
}

function logQRCodePrinted($qrCode, $printMethod = 'browser', $markerId = null) {
    logActivity('qr_code_printed', [
        'qr_code' => $qrCode,
        'print_method' => $printMethod
    ], $markerId);
}

function logNFCChipGenerated($chipId, $markerId = null) {
    logActivity('nfc_chip_generated', [
        'chip_id' => $chipId
    ], $markerId);
}

function logNFCChipScanned($chipId, $markerId = null) {
    logActivity('nfc_chip_scanned', [
        'chip_id' => $chipId,
        'device_type' => getDeviceType()
    ], $markerId);
}

/**
 * ===================================================================
 * WARTUNGS-AKTIONEN
 * ===================================================================
 */

function logMaintenanceChecklistCreated($checklistName, $itemCount) {
    logActivity('maintenance_checklist_created', [
        'checklist_name' => $checklistName,
        'item_count' => $itemCount
    ]);
}

function logMaintenanceChecklistUpdated($checklistName, $itemCount) {
    logActivity('maintenance_checklist_updated', [
        'checklist_name' => $checklistName,
        'item_count' => $itemCount
    ]);
}

function logMaintenancePerformed($markerId, $maintenanceType, $checklistItems = 0) {
    logActivity('maintenance_performed', [
        'maintenance_type' => $maintenanceType,
        'checklist_items' => $checklistItems
    ], $markerId);
}

function logMaintenancePDFGenerated($markerId, $maintenanceId, $pdfName) {
    logActivity('maintenance_pdf_generated', [
        'maintenance_id' => $maintenanceId,
        'pdf_name' => $pdfName
    ], $markerId);
}

/**
 * ===================================================================
 * DOKUMENT-VERWALTUNG
 * ===================================================================
 */

function logDocumentUploaded($documentName, $fileSize, $documentType, $markerId = null) {
    logActivity('document_uploaded', [
        'document_name' => $documentName,
        'file_size' => formatBytes($fileSize),
        'document_type' => $documentType
    ], $markerId);
}

function logDocumentViewed($documentName, $documentType, $markerId = null) {
    logActivity('document_viewed', [
        'document_name' => $documentName,
        'document_type' => $documentType
    ], $markerId);
}

function logDocumentDownloaded($documentName, $documentType, $markerId = null) {
    logActivity('document_downloaded', [
        'document_name' => $documentName,
        'document_type' => $documentType
    ], $markerId);
}

function logDocumentDeleted($documentName, $documentType, $markerId = null) {
    logActivity('document_deleted', [
        'document_name' => $documentName,
        'document_type' => $documentType
    ], $markerId);
}

/**
 * ===================================================================
 * KALENDER & EINSTELLUNGEN
 * ===================================================================
 */

function logCalendarSettingsChanged($settingName, $oldValue, $newValue) {
    logActivity('calendar_settings_changed', [
        'setting_name' => $settingName,
        'old_value' => $oldValue,
        'new_value' => $newValue
    ]);
}

function logUserCalendarSettingsChanged($settingName, $oldValue, $newValue) {
    logActivity('user_calendar_settings_changed', [
        'setting_name' => $settingName,
        'old_value' => $oldValue,
        'new_value' => $newValue
    ]);
}

function logEscalationSettingsChanged($settingName, $oldValue, $newValue) {
    logActivity('escalation_settings_changed', [
        'setting_name' => $settingName,
        'old_value' => $oldValue,
        'new_value' => $newValue
    ]);
}

function logEscalationTriggered($escalationType, $escalationLevel, $targetUserId, $markerId = null) {
    logActivity('escalation_triggered', [
        'escalation_type' => $escalationType,
        'escalation_level' => $escalationLevel,
        'target_user_id' => $targetUserId
    ], $markerId);
}

/**
 * ===================================================================
 * FEHLER & EXCEPTIONS
 * ===================================================================
 */

function logValidationError($formName, $fieldName, $errorMessage) {
    logActivity('validation_error', [
        'form_name' => $formName,
        'field_name' => $fieldName,
        'error_message' => $errorMessage
    ]);
}

function logDatabaseError($query, $errorMessage) {
    logActivity('database_error', [
        'query' => substr($query, 0, 200), // Nur erste 200 Zeichen
        'error' => $errorMessage
    ]);
}

function logAPIError($endpoint, $statusCode, $errorMessage) {
    logActivity('api_error', [
        'endpoint' => $endpoint,
        'status_code' => $statusCode,
        'error' => $errorMessage
    ]);
}

/**
 * ===================================================================
 * HILFSFUNKTIONEN
 * ===================================================================
 */

function getDeviceType() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (preg_match('/mobile|android|iphone|ipad|tablet/i', $userAgent)) {
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    return 'desktop';
}

function formatTimeSpent($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = round($seconds % 60);
    
    if ($hours > 0) {
        return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
    } elseif ($minutes > 0) {
        return sprintf('%dm %ds', $minutes, $secs);
    } else {
        return sprintf('%ds', $secs);
    }
}

/**
 * ===================================================================
 * AUTOMATISCHE JAVASCRIPT EVENT LISTENER INTEGRATION
 * ===================================================================
 * 
 * Die folgenden JavaScript Event Listener können in der footer.php
 * oder in einem separaten JS-File eingebunden werden:
 */

function getActivityLoggerJavaScript() {
    return <<<'JAVASCRIPT'
<script>
// Activity Logger - Client-Side Events
(function() {
    'use strict';
    
    // Hilfsfunktion zum Senden von Logs
    function sendActivityLog(action, details) {
        fetch('log_activity_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: action,
                details: details
            })
        }).catch(err => console.error('Activity Log Error:', err));
    }
    
    // Track wichtige Button Clicks
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-log-click]');
        if (btn) {
            sendActivityLog('button_click', {
                button: btn.dataset.logClick,
                page: window.location.pathname
            });
        }
    });
    
    // Track Filter Änderungen
    document.querySelectorAll('select[data-filter], input[data-filter]').forEach(el => {
        el.addEventListener('change', function() {
            sendActivityLog('filter_changed', {
                filter_type: this.dataset.filter,
                filter_value: this.value,
                page: window.location.pathname
            });
        });
    });
    
    // Track Sortierung
    document.querySelectorAll('[data-sort]').forEach(el => {
        el.addEventListener('click', function() {
            sendActivityLog('sorting_changed', {
                sort_field: this.dataset.sort,
                sort_order: this.dataset.sortOrder || 'asc',
                page: window.location.pathname
            });
        });
    });
    
    // Track Suchen ohne Ergebnis
    const searchForms = document.querySelectorAll('form[role="search"]');
    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const searchInput = form.querySelector('input[type="search"], input[name="search"]');
            if (searchInput && searchInput.value) {
                // Prüfe nach Submit ob Ergebnisse vorhanden sind
                setTimeout(() => {
                    const noResults = document.querySelector('.no-results, .empty-state');
                    if (noResults) {
                        sendActivityLog('search_no_results', {
                            search_term: searchInput.value,
                            page: window.location.pathname
                        });
                    }
                }, 1000);
            }
        });
    });
    
    // Track Form Abandonment
    const forms = document.querySelectorAll('form[data-track-abandon]');
    forms.forEach(form => {
        let fieldsCompleted = 0;
        const totalFields = form.querySelectorAll('input, select, textarea').length;
        
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('change', () => {
                if (field.value) fieldsCompleted++;
            });
        });
        
        // Track wenn Benutzer Seite verlässt ohne zu submiten
        window.addEventListener('beforeunload', function() {
            if (fieldsCompleted > 0 && fieldsCompleted < totalFields) {
                sendActivityLog('form_abandoned', {
                    form_name: form.dataset.trackAbandon,
                    fields_completed: fieldsCompleted,
                    total_fields: totalFields
                });
            }
        });
    });
    
    // Track AR Navigation (wenn AR Module vorhanden)
    if (window.ARNavigator) {
        window.ARNavigator.on('start', (data) => {
            sendActivityLog('ar_navigation_start', data);
        });
        
        window.ARNavigator.on('stop', (data) => {
            sendActivityLog('ar_navigation_stop', data);
        });
    }
    
    // Track Kamera-Öffnen (wenn Camera API genutzt wird)
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        const originalGetUserMedia = navigator.mediaDevices.getUserMedia;
        navigator.mediaDevices.getUserMedia = function(constraints) {
            if (constraints.video) {
                sendActivityLog('camera_opened', {
                    purpose: document.body.dataset.cameraPurpose || 'general'
                });
            }
            return originalGetUserMedia.apply(this, arguments);
        };
    }
    
    // Track Zeit auf Seite bei Page Unload
    let pageStartTime = Date.now();
    window.addEventListener('beforeunload', function() {
        const timeSpent = Math.round((Date.now() - pageStartTime) / 1000);
        if (timeSpent >= 5) {
            sendActivityLog('time_on_page', {
                page: window.location.pathname,
                time_spent_seconds: timeSpent
            });
        }
    });
    
})();
</script>
JAVASCRIPT;
}

/**
 * ===================================================================
 * AJAX ENDPOINT FÜR CLIENT-SIDE LOGGING
 * ===================================================================
 * 
 * Erstelle eine neue Datei: log_activity_ajax.php mit folgendem Inhalt:
 */

function createAjaxLogEndpoint() {
    $content = <<<'PHP'
<?php
/**
 * AJAX Endpoint für Client-Side Activity Logging
 */

require_once __DIR__ . '/config.php';

// Nur AJAX-Requests erlauben
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    die('Forbidden');
}

// Nur eingeloggte Benutzer
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

// JSON Daten einlesen
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['action'])) {
    http_response_code(400);
    die('Bad Request');
}

// Log die Activity
$action = $data['action'];
$details = $data['details'] ?? [];
$markerId = $data['marker_id'] ?? null;

if (function_exists('logActivity')) {
    logActivity($action, $details, $markerId);
    
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Activity logger not available']);
}
PHP;

    file_put_contents(__DIR__ . '/log_activity_ajax.php', $content);
}

// Erstelle AJAX Endpoint automatisch wenn nicht vorhanden
if (!file_exists(__DIR__ . '/log_activity_ajax.php')) {
    createAjaxLogEndpoint();
}

/**
 * ===================================================================
 * INTEGRATION HINWEISE
 * ===================================================================
 * 
 * Um die JavaScript Event Listener zu aktivieren, füge folgendes
 * in deine footer.php ein:
 * 
 * <?php
 * if (function_exists('getActivityLoggerJavaScript')) {
 *     echo getActivityLoggerJavaScript();
 * }
 * ?>
 * 
 * HTML-Elemente mit Logging-Attributen markieren:
 * 
 * <button data-log-click="create_marker_button">Marker erstellen</button>
 * <select data-filter="category">...</select>
 * <a href="#" data-sort="name" data-sort-order="asc">Sortieren</a>
 * <form data-track-abandon="create_marker_form">...</form>
 */

// Debug-Ausgabe wenn aktiviert
if (defined('ACTIVITY_LOG_DEBUG') && ACTIVITY_LOG_DEBUG === true) {
    error_log("Extended Activity Logger erfolgreich geladen!");
}