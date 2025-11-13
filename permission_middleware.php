<?php
/**
 * Permission Middleware
 * Automatische Permission-Prüfung basierend auf der aufgerufenen Datei
 * 
 * Diese Datei wird in config.php eingebunden und prüft automatisch
 * ob der aktuelle Benutzer die benötigten Rechte für die aufgerufene Seite hat
 */

// Nur ausführen wenn Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    return;
}

// Aktuellen Script-Namen ermitteln
$currentScript = basename($_SERVER['PHP_SELF']);

// Mapping: Script-Name => Benötigte Permission
$permissionMap = [
    // === BENUTZER-VERWALTUNG ===
    'users.php' => 'users_view',
    'add_user.php' => 'users_create',
    'edit_user.php' => 'users_edit',
    'delete_user.php' => 'users_delete',
    'user_profile.php' => 'users_view_profile',
    'export_users.php' => 'users_export',
    
    // === MARKER-VERWALTUNG ===
    'markers.php' => 'markers_view',
    'add_marker.php' => 'markers_create',
    'edit_marker.php' => 'markers_edit',
    'delete_marker.php' => 'markers_delete',
    'marker_details.php' => 'markers_view',
    'activate_marker.php' => 'markers_activate',
    'deactivate_marker.php' => 'markers_deactivate',
    'duplicate_marker.php' => 'markers_duplicate',
    'marker_history.php' => 'markers_history_view',
    'view_marker.php' => 'markers_view',
    'marker_documents.php' => 'markers_documents_view',
    'bulk_edit_markers.php' => 'markers_bulk_edit',
    'markers_bulk_edit.php' => 'markers_bulk_edit',
    'export_markers.php' => 'markers_export',
    
    // === MARKER GPS ===
    'update_position.php' => 'markers_gps_update',
    'view_route.php' => 'markers_gps_view_route',
    
    // === MARKER STATUS ===
    'mark_ready.php' => 'markers_status_ready',
    'mark_in_repair.php' => 'markers_status_repair',
    'mark_in_storage.php' => 'markers_status_storage',
    
    // === QR-CODES ===
    'qr_codes.php' => 'qr_list_view',
    'qr_code_list.php' => 'qr_list_view',
    'generate_qr.php' => 'qr_generate',
    'print_qr.php' => 'qr_print',
    'print_qr_batch.php' => 'qr_batch_print',
    'qr_code_print_batch.php' => 'qr_batch_print',
    'download_qr.php' => 'qr_download',
    'regenerate_qr.php' => 'qr_regenerate',
    'qr_code_generator.php' => 'qr_generate',
    'qr_generator_advanced.php' => 'qr_generate_advanced',
    'qr_branding.php' => 'qr_branding_manage',
    
    // === NFC-CHIPS ===
    'nfc_chip_list.php' => 'nfc_pool_view',
    'nfc_chip_pool.php' => 'nfc_pool_manage',
    'add_nfc_chips.php' => 'nfc_pool_add',
    'assign_nfc.php' => 'nfc_assign',
    'unassign_nfc.php' => 'nfc_unassign',
    'export_nfc.php' => 'nfc_export',
    'nfc_chip_generator.php' => 'nfc_generate',
    
    // === GEO-FENCE ===
    'geofence_groups.php' => 'geofence_groups_manage',
    'geofence_manager.php' => 'geofence_create',
    'create_geofence_mobile.php' => 'geofence_create_mobile',
    'edit_geofence.php' => 'geofence_edit',
    'delete_geofence.php' => 'geofence_delete',
    
    // === ROLLEN & PERMISSIONS ===
    'roles.php' => 'roles_manage',
    'add_role.php' => 'roles_create',
    'create_role.php' => 'roles_create',
    'edit_role.php' => 'roles_edit',
    'delete_role.php' => 'roles_delete',
    'role_permissions.php' => 'permissions_assign',
    
    // === CUSTOM FIELDS ===
    'custom_fields.php' => 'custom_fields_manage',
    'add_custom_field.php' => 'custom_fields_create',
    'edit_custom_field.php' => 'custom_fields_edit',
    'delete_custom_field.php' => 'custom_fields_delete',
    
    // === LOCATIONS ===
    'locations.php' => 'locations_manage',
    'add_location.php' => 'locations_create',
    'edit_location.php' => 'locations_edit',
    'delete_location.php' => 'locations_delete',
    
    // === WARTUNG ===
    'maintenance.php' => 'maintenance_view',
    'maintenance_overview.php' => 'maintenance_overview',
    'maintenance_timeline.php' => 'maintenance_view',
    'add_maintenance.php' => 'maintenance_create',
    'edit_maintenance.php' => 'maintenance_edit',
    'complete_maintenance.php' => 'maintenance_complete',
    'maintenance_report.php' => 'maintenance_reports',
    'maintenance_signature.php' => 'maintenance_signature',
    'user_signature.php' => 'maintenance_signature',
    'maintenance_checklists.php' => 'maintenance_checklists_manage',
    'maintenance_perform.php' => 'maintenance_perform',
    'maintenance_pdf_generator.php' => 'maintenance_reports',
    'maintenance_sets.php' => 'maintenance_sets_manage',
    
    // === DATEIVERWALTUNG ===
    'file_manager.php' => 'file_manager_view',
    
    // === TEMPLATES ===
    'marker_templates.php' => 'templates_manage',
    'create_template.php' => 'templates_create',
    'edit_template.php' => 'templates_edit',
    'delete_template.php' => 'templates_delete',
    'use_template.php' => 'templates_use',
    
    // === BERICHTE & EXPORT ===
    'reports.php' => 'reports_view',
    'export_report.php' => 'reports_export',
    'statistics.php' => 'statistics_view',
    
    // === LOGS ===
    'activity_log.php' => 'activity_log_view',
    'login_history.php' => 'login_history_view',
    'export_logs.php' => 'logs_export',
    
    // === TRASH ===
    'trash.php' => 'trash_view',
    'restore_item.php' => 'trash_restore',
    'empty_trash.php' => 'trash_empty',
    
    // === EINSTELLUNGEN ===
    // 'settings.php' => 'settings_view', // AUSKOMMENTIERT - ist Menü-Seite
    'system_settings.php' => 'settings_system',
    'security_settings.php' => 'settings_security',
    'notification_settings.php' => 'settings_notifications',
    'backup_settings.php' => 'settings_backup',
    'escalation_settings.php' => 'settings_escalation',
    
    // === API ===
    'api_keys.php' => 'api_manage',
    'generate_api_key.php' => 'api_generate',
    'revoke_api_key.php' => 'api_revoke',
    
    // === TAG-SYSTEM ===
    'tags.php' => 'tags_view',
    'create_tag.php' => 'tags_create',
    'edit_tag.php' => 'tags_edit',
    'delete_tag.php' => 'tags_delete',
    'assign_tags.php' => 'tags_assign',
    'manage_tags.php' => 'tags_manage',
    
    // === 3D-MODELLE ===
    '3d_models.php' => 'models_3d_view',
    'upload_3d_model.php' => 'models_3d_upload',
    'download_3d_model.php' => 'models_3d_download',
    'delete_3d_model.php' => 'models_3d_delete',
    'manage_3d_models.php' => 'models_3d_manage',
    
    // === AR-NAVIGATION ===
    'ar_navigation.php' => 'ar_navigation_use',
    'ar_markers.php' => 'ar_markers_view',
    'ar_settings.php' => 'ar_markers_manage',
    
    // === KALENDER-INTEGRATION ===
    'calendar.php' => 'calendar_view',
    'calendar_events.php' => 'calendar_create_events',
    'edit_calendar_event.php' => 'calendar_edit_events',
    'delete_calendar_event.php' => 'calendar_delete_events',
    'outlook_integration.php' => 'calendar_sync_outlook',
    'google_calendar_integration.php' => 'calendar_sync_google',
    'calendar_settings.php' => 'calendar_settings',
    'auto_maintenance_calendar.php' => 'calendar_auto_maintenance',
    'user_calendar_settings.php' => 'calendar_user_settings',
    
    // === KAMERA (ERWEITERT) ===
    'advanced_camera.php' => 'camera_use_advanced',
    'edit_photo.php' => 'photos_edit',
    'annotate_photo.php' => 'photos_annotations',
    'bulk_upload_photos.php' => 'photos_bulk_upload',
    
    // === KARTEN (ERWEITERT) ===
    'map_cluster.php' => 'maps_cluster_view',
    'map_heatmap.php' => 'maps_heatmap',
    'export_map.php' => 'maps_export',
    
    // === MESSE-MODUS ===
    'messe_admin.php' => 'manage_system_settings',
    'messe_view.php' => null, // Öffentlich zugänglich
    'messe_stats.php' => 'manage_system_settings',
    'messe_info.php' => null,
    'messe_custom_fields.php' => 'manage_system_settings',
    'messe_print_qr.php' => 'qr_print',
    
    // === SCANNER ===
    'scan.php' => 'markers_view',
    'qr_scanner.php' => 'markers_view',
    
    // === KATEGORIEN ===
    'categories.php' => 'manage_system_settings',
    
    // === INSPECTIONS ===
    'add_inspection.php' => 'maintenance_create',
    'edit_inspection.php' => 'maintenance_edit',
    'delete_inspection.php' => 'maintenance_delete',
    'complete_inspection.php' => 'maintenance_complete',
    
    // === ERWEITERTE SUCHE ===
    'advanced_search.php' => 'markers_view',
    'inactive_markers.php' => 'markers_view',
    
    // === BUG REPORTING ===
    'submit_bug.php' => null,
    'my_bug_tickets.php' => null,
    
    // === DATENSCHUTZ ===
    'datenschutz.php' => null,
    'impressum.php' => null,
    'public_view.php' => null,
    'measure_distance.php' => 'maps_measure_distance',
];

// Prüfen ob aktuelles Script eine Permission benötigt
if (isset($permissionMap[$currentScript])) {
    $requiredPermission = $permissionMap[$currentScript];
    
    // Permission prüfen (nutzt die Funktion aus functions.php)
    if (function_exists('hasPermission') && !hasPermission($requiredPermission)) {
        // Fehlende Berechtigung loggen
        if (function_exists('logActivity')) {
            logActivity(
                $pdo,
                $_SESSION['user_id'],
                'permission_denied',
                "Zugriff verweigert auf {$currentScript} (benötigt: {$requiredPermission})"
            );
        }
        
        // Fehlerseite anzeigen
        $_SESSION['error_message'] = "Sie haben keine Berechtigung für diese Aktion. (Benötigt: {$requiredPermission})";
        header('Location: index.php');
        exit;
    }
}

// === SPEZIELLE PRÜFUNGEN FÜR AKTIONEN INNERHALB VON SEITEN ===

// Prüfung für POST/GET Aktionen (z.B. ?action=delete)
if (isset($_GET['action']) || isset($_POST['action'])) {
    $action = $_GET['action'] ?? $_POST['action'];
    
    // Action-basierte Permission-Prüfungen
    $actionPermissions = [
        // Marker Aktionen
        'delete_marker' => 'markers_delete',
        'activate_marker' => 'markers_activate',
        'deactivate_marker' => 'markers_deactivate',
        'duplicate_marker' => 'markers_duplicate',
        'export_markers' => 'markers_export',
        
        // User Aktionen
        'delete_user' => 'users_delete',
        'reset_password' => 'users_edit',
        'change_role' => 'users_edit',
        
        // Settings Aktionen
        'save_system_settings' => 'settings_system',
        'save_security_settings' => 'settings_security',
        'clear_cache' => 'settings_system',
        
        // Backup Aktionen
        'create_backup' => 'settings_backup',
        'restore_backup' => 'settings_backup',
        
        // Trash Aktionen
        'restore' => 'trash_restore',
        'permanent_delete' => 'trash_empty',
        
        // Tag Aktionen
        'delete_tag' => 'tags_delete',
        'assign_tag' => 'tags_assign',
        
        // 3D Model Aktionen
        'delete_3d_model' => 'models_3d_delete',
        'download_3d_model' => 'models_3d_download',
        
        // Kalender Aktionen
        'delete_event' => 'calendar_delete_events',
        'sync_outlook' => 'calendar_sync_outlook',
        'sync_google' => 'calendar_sync_google',
    ];
    
    if (isset($actionPermissions[$action])) {
        $requiredPermission = $actionPermissions[$action];
        
        if (!hasPermission($requiredPermission)) {
            if (function_exists('logActivity')) {
                logActivity(
                    $pdo,
                    $_SESSION['user_id'],
                    'permission_denied',
                    "Aktion verweigert: {$action} (benötigt: {$requiredPermission})"
                );
            }
            
            $_SESSION['error_message'] = "Sie haben keine Berechtigung für diese Aktion.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }
    }
}

// === FORMULAR-BASIERTE PRÜFUNGEN ===

// Prüfung für Submit-Buttons mit spezifischen Namen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitPermissions = [
        // User Management
        'create_user' => 'users_create',
        'update_user' => 'users_edit',
        'delete_user' => 'users_delete',
        
        // Marker Management
        'create_marker' => 'markers_create',
        'update_marker' => 'markers_edit',
        'bulk_update' => 'markers_bulk_edit',
        
        // Settings
        'save_settings' => 'settings_system',
        'update_security' => 'settings_security',
        
        // Roles
        'create_role' => 'roles_create',
        'update_role' => 'roles_edit',
        'update_permissions' => 'permissions_assign',
        
        // Tags
        'create_tag' => 'tags_create',
        'update_tag' => 'tags_edit',
        'delete_tag' => 'tags_delete',
        
        // 3D Models
        'upload_3d_model' => 'models_3d_upload',
        'delete_3d_model' => 'models_3d_delete',
        
        // Calendar
        'create_event' => 'calendar_create_events',
        'update_event' => 'calendar_edit_events',
        'delete_event' => 'calendar_delete_events',
    ];
    
    foreach ($submitPermissions as $submitName => $permission) {
        if (isset($_POST[$submitName])) {
            if (!hasPermission($permission)) {
                if (function_exists('logActivity')) {
                    logActivity(
                        $pdo,
                        $_SESSION['user_id'],
                        'permission_denied',
                        "Formular verweigert: {$submitName} (benötigt: {$permission})"
                    );
                }
                
                $_SESSION['error_message'] = "Sie haben keine Berechtigung für diese Aktion.";
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
                exit;
            }
            break; // Nur eine Prüfung pro Request
        }
    }
}

// === AJAX REQUEST PRÜFUNGEN ===

// Prüfung für AJAX-Endpoints
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $ajaxPermissions = [
        'get_marker_data.php' => 'markers_view',
        'update_marker_position.php' => 'markers_gps_update',
        'get_statistics.php' => 'statistics_view',
        'search_users.php' => 'users_view',
        'validate_qr.php' => 'qr_list_view',
        'get_tags.php' => 'tags_view',
        'get_3d_models.php' => 'models_3d_view',
        'get_calendar_events.php' => 'calendar_view',
    ];
    
    if (isset($ajaxPermissions[$currentScript])) {
        if (!hasPermission($ajaxPermissions[$currentScript])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Keine Berechtigung für diese Aktion'
            ]);
            exit;
        }
    }
}

/**
 * Hilfsfunktion: Prüft mehrere Permissions (UND-Verknüpfung)
 * Alle angegebenen Permissions müssen vorhanden sein
 */
function requireMultiplePermissions(array $permissions) {
    foreach ($permissions as $permission) {
        if (!hasPermission($permission)) {
            if (function_exists('logActivity')) {
                global $pdo;
                logActivity(
                    $pdo,
                    $_SESSION['user_id'],
                    'permission_denied',
                    "Mehrfache Berechtigung verweigert: " . implode(', ', $permissions)
                );
            }
            
            $_SESSION['error_message'] = "Sie haben nicht alle erforderlichen Berechtigungen für diese Aktion.";
            header('Location: index.php');
            exit;
        }
    }
}

/**
 * Hilfsfunktion: Prüft ob EINE der angegebenen Permissions vorhanden ist (ODER-Verknüpfung)
 * Mindestens eine Permission muss vorhanden sein
 */
function requireAnyPermission(array $permissions) {
    foreach ($permissions as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    
    if (function_exists('logActivity')) {
        global $pdo;
        logActivity(
            $pdo,
            $_SESSION['user_id'],
            'permission_denied',
            "Keine der erforderlichen Berechtigungen vorhanden: " . implode(', ', $permissions)
        );
    }
    
    $_SESSION['error_message'] = "Sie benötigen mindestens eine der folgenden Berechtigungen: " . implode(', ', $permissions);
    header('Location: index.php');
    exit;
}

/**
 * Hilfsfunktion: Prüft ob Benutzer Admin ist
 * HINWEIS: Diese Funktion existiert bereits in functions.php
 * Daher wird sie hier nicht neu deklariert
 */
// function requireAdmin() - bereits in functions.php vorhanden