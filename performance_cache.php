<?php
/**
 * PERFORMANCE CACHE SYSTEM
 * 
 * Dieses System cached statische Daten um Datenbank-Queries zu reduzieren
 * und die Performance deutlich zu verbessern.
 * 
 * Integration in config.php:
 * require_once __DIR__ . '/performance_cache.php';
 * 
 * WICHTIG: Nach allen anderen Includes laden!
 */

// Verhindere doppeltes Laden
if (defined('PERFORMANCE_CACHE_LOADED')) {
    return;
}
define('PERFORMANCE_CACHE_LOADED', true);

/**
 * ===================================================================
 * CACHE KONFIGURATION
 * ===================================================================
 */

// Cache-Dauer in Sekunden (Standard: 5 Minuten)
define('CACHE_DURATION', 300);

// Cache aktivieren/deaktivieren
define('CACHE_ENABLED', true);

// Cache-Verzeichnis
define('CACHE_DIR', __DIR__ . '/cache');

// Stelle sicher dass Cache-Verzeichnis existiert
if (!file_exists(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0755, true);
}

// Prüfe ob PDO verfügbar ist
if (!isset($pdo)) {
    return;
}

/**
 * ===================================================================
 * CACHE FUNKTIONEN
 * ===================================================================
 */

/**
 * Generiere Cache-Key
 */
function getCacheKey($identifier) {
    return md5($identifier);
}

/**
 * Schreibe in Cache
 */
function setCache($key, $data, $duration = CACHE_DURATION) {
    if (!CACHE_ENABLED) {
        return false;
    }
    
    $cacheFile = CACHE_DIR . '/' . getCacheKey($key) . '.cache';
    $cacheData = [
        'expires' => time() + $duration,
        'data' => $data
    ];
    
    return file_put_contents($cacheFile, serialize($cacheData), LOCK_EX) !== false;
}

/**
 * Lese aus Cache
 */
function getCache($key) {
    if (!CACHE_ENABLED) {
        return null;
    }
    
    $cacheFile = CACHE_DIR . '/' . getCacheKey($key) . '.cache';
    
    if (!file_exists($cacheFile)) {
        return null;
    }
    
    $cacheData = unserialize(file_get_contents($cacheFile));
    
    // Prüfe ob Cache abgelaufen ist
    if ($cacheData['expires'] < time()) {
        @unlink($cacheFile);
        return null;
    }
    
    return $cacheData['data'];
}

/**
 * Lösche Cache-Eintrag
 */
function deleteCache($key) {
    $cacheFile = CACHE_DIR . '/' . getCacheKey($key) . '.cache';
    if (file_exists($cacheFile)) {
        return @unlink($cacheFile);
    }
    return true;
}

/**
 * Lösche alle Cache-Einträge
 */
function clearAllCache() {
    $files = glob(CACHE_DIR . '/*.cache');
    foreach ($files as $file) {
        @unlink($file);
    }
    return true;
}

/**
 * Lösche abgelaufene Cache-Einträge
 */
function cleanupExpiredCache() {
    $files = glob(CACHE_DIR . '/*.cache');
    $cleaned = 0;
    
    foreach ($files as $file) {
        $cacheData = @unserialize(file_get_contents($file));
        if ($cacheData && isset($cacheData['expires']) && $cacheData['expires'] < time()) {
            @unlink($file);
            $cleaned++;
        }
    }
    
    return $cleaned;
}

/**
 * ===================================================================
 * CACHED DATEN FUNKTIONEN
 * ===================================================================
 */

/**
 * Lade Kategorien (mit Cache)
 */
function getCachedCategories() {
    global $pdo;
    
    $cacheKey = 'categories_list';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->query("
        SELECT DISTINCT category 
        FROM markers 
        WHERE category IS NOT NULL 
        AND deleted_at IS NULL
        ORDER BY category
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Cache für 10 Minuten
    setCache($cacheKey, $categories, 600);
    
    return $categories;
}

/**
 * Lade User-Rollen (mit Cache)
 */
function getCachedRoles() {
    global $pdo;
    
    $cacheKey = 'user_roles';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->query("
        SELECT id, name, description 
        FROM roles 
        ORDER BY name
    ");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache für 15 Minuten
    setCache($cacheKey, $roles, 900);
    
    return $roles;
}

/**
 * Lade Permissions für eine Rolle (mit Cache)
 */
function getCachedRolePermissions($roleId) {
    global $pdo;
    
    $cacheKey = 'role_permissions_' . $roleId;
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->prepare("
        SELECT permission_key 
        FROM role_permissions 
        WHERE role_id = ?
    ");
    $stmt->execute([$roleId]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Cache für 15 Minuten
    setCache($cacheKey, $permissions, 900);
    
    return $permissions;
}

/**
 * Lade System-Einstellungen (mit Cache)
 */
function getCachedSettings() {
    global $pdo;
    
    $cacheKey = 'system_settings';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Cache für 30 Minuten
    setCache($cacheKey, $settings, 1800);
    
    return $settings;
}

/**
 * Lade eine einzelne Einstellung (mit Cache)
 */
function getCachedSetting($key, $default = null) {
    $settings = getCachedSettings();
    return $settings[$key] ?? $default;
}

/**
 * Lade Custom Fields (mit Cache)
 */
function getCachedCustomFields() {
    global $pdo;
    
    $cacheKey = 'custom_fields';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->query("
        SELECT * FROM custom_fields 
        WHERE is_active = 1 
        ORDER BY display_order, field_name
    ");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache für 10 Minuten
    setCache($cacheKey, $fields, 600);
    
    return $fields;
}

/**
 * Lade Marker-Liste für Dropdowns (mit Cache)
 */
function getCachedMarkersList() {
    global $pdo;
    
    $cacheKey = 'markers_dropdown_list';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->query("
        SELECT id, name, qr_code, category 
        FROM markers 
        WHERE deleted_at IS NULL 
        ORDER BY name
    ");
    $markers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache für 5 Minuten
    setCache($cacheKey, $markers, 300);
    
    return $markers;
}

/**
 * Lade Wartungstypen (mit Cache)
 */
function getCachedMaintenanceTypes() {
    $cacheKey = 'maintenance_types';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Statische Daten
    $types = [
        'routine' => ['name' => 'Routinewartung', 'icon' => 'fa-calendar-check', 'color' => '#28a745'],
        'repair' => ['name' => 'Reparatur', 'icon' => 'fa-tools', 'color' => '#dc3545'],
        'inspection' => ['name' => 'Inspektion', 'icon' => 'fa-clipboard-check', 'color' => '#007bff'],
        'emergency' => ['name' => 'Notfallwartung', 'icon' => 'fa-exclamation-triangle', 'color' => '#ffc107'],
        'cleaning' => ['name' => 'Reinigung', 'icon' => 'fa-broom', 'color' => '#17a2b8'],
        'other' => ['name' => 'Sonstiges', 'icon' => 'fa-wrench', 'color' => '#6c757d']
    ];
    
    // Cache für 1 Stunde
    setCache($cacheKey, $types, 3600);
    
    return $types;
}

/**
 * Lade User-Liste für Dropdowns (mit Cache)
 */
function getCachedUsersList() {
    global $pdo;
    
    $cacheKey = 'users_dropdown_list';
    $cached = getCache($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // Lade aus Datenbank
    $stmt = $pdo->query("
        SELECT id, username, email, role 
        FROM users 
        WHERE is_active = 1 
        ORDER BY username
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache für 10 Minuten
    setCache($cacheKey, $users, 600);
    
    return $users;
}

/**
 * ===================================================================
 * CACHE INVALIDIERUNG
 * ===================================================================
 */

/**
 * Invalidiere Kategorien-Cache (nach CRUD-Operationen)
 */
function invalidateCategoriesCache() {
    deleteCache('categories_list');
}

/**
 * Invalidiere Rollen-Cache
 */
function invalidateRolesCache() {
    deleteCache('user_roles');
}

/**
 * Invalidiere Permissions-Cache für eine Rolle
 */
function invalidateRolePermissionsCache($roleId) {
    deleteCache('role_permissions_' . $roleId);
}

/**
 * Invalidiere alle Role-Permissions-Caches
 */
function invalidateAllRolePermissionsCache() {
    $files = glob(CACHE_DIR . '/*.cache');
    foreach ($files as $file) {
        if (strpos($file, 'role_permissions_') !== false) {
            @unlink($file);
        }
    }
}

/**
 * Invalidiere System-Einstellungen Cache
 */
function invalidateSettingsCache() {
    deleteCache('system_settings');
}

/**
 * Invalidiere Custom Fields Cache
 */
function invalidateCustomFieldsCache() {
    deleteCache('custom_fields');
}

/**
 * Invalidiere Marker-Liste Cache
 */
function invalidateMarkersListCache() {
    deleteCache('markers_dropdown_list');
}

/**
 * Invalidiere User-Liste Cache
 */
function invalidateUsersListCache() {
    deleteCache('users_dropdown_list');
}

/**
 * ===================================================================
 * AUTOMATISCHE CACHE INVALIDIERUNG
 * ===================================================================
 */

/**
 * Hook für Marker-Änderungen
 */
function onMarkerChanged() {
    invalidateCategoriesCache();
    invalidateMarkersListCache();
}

/**
 * Hook für Role-Änderungen
 */
function onRoleChanged() {
    invalidateRolesCache();
    invalidateAllRolePermissionsCache();
}

/**
 * Hook für User-Änderungen
 */
function onUserChanged() {
    invalidateUsersListCache();
}

/**
 * Hook für Settings-Änderungen
 */
function onSettingsChanged() {
    invalidateSettingsCache();
}

/**
 * Hook für Custom Fields-Änderungen
 */
function onCustomFieldChanged() {
    invalidateCustomFieldsCache();
}

/**
 * ===================================================================
 * CACHE STATISTIKEN
 * ===================================================================
 */

/**
 * Hole Cache-Statistiken
 */
function getCacheStats() {
    $files = glob(CACHE_DIR . '/*.cache');
    $totalSize = 0;
    $validCaches = 0;
    $expiredCaches = 0;
    
    foreach ($files as $file) {
        $totalSize += filesize($file);
        $cacheData = @unserialize(file_get_contents($file));
        
        if ($cacheData && isset($cacheData['expires'])) {
            if ($cacheData['expires'] >= time()) {
                $validCaches++;
            } else {
                $expiredCaches++;
            }
        }
    }
    
    return [
        'total_files' => count($files),
        'valid_caches' => $validCaches,
        'expired_caches' => $expiredCaches,
        'total_size' => $totalSize,
        'total_size_formatted' => formatBytes($totalSize)
    ];
}

/**
 * ===================================================================
 * DEBUG & MONITORING
 * ===================================================================
 */

// Debug-Modus (nur für Entwicklung)
if (defined('CACHE_DEBUG') && CACHE_DEBUG === true) {
    error_log("Performance Cache System geladen");
    
    // Zeige Cache-Hit/Miss Statistiken
    register_shutdown_function(function() {
        $stats = getCacheStats();
        error_log("Cache Stats: " . json_encode($stats));
    });
}

/**
 * ===================================================================
 * AUTOMATISCHE CACHE BEREINIGUNG
 * ===================================================================
 */

// Bereinige abgelaufene Caches (10% Chance bei jedem Request)
if (CACHE_ENABLED && mt_rand(1, 100) <= 10) {
    cleanupExpiredCache();
}