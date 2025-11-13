<?php
/**
 * ASSET MINIFICATION SYSTEM - FIXED VERSION
 * 
 * Automatische Minification von CSS und JS Dateien
 * JETZT MIT UNTERSTÜTZUNG FÜR UNTERVERZEICHNISSE!
 * 
 * FEATURES:
 * - Automatische Pfad-Berechnung für Unterverzeichnisse
 * - Automatische Minification bei Änderungen
 * - Caching der minierten Dateien
 * - Fallback auf Originale bei Fehlern
 * - Keine Dependencies (Pure PHP)
 */

// Cache-Verzeichnis für minifizierte Dateien
define('MINIFY_CACHE_DIR', __DIR__ . '/cache/minified');
define('MINIFY_ENABLED', true); // Auf false für Entwicklung

// Stelle sicher dass Cache-Verzeichnis existiert
if (!file_exists(MINIFY_CACHE_DIR)) {
    @mkdir(MINIFY_CACHE_DIR, 0755, true);
}

/**
 * Berechnet den relativen Pfad zum Root-Verzeichnis
 */
function getRelativeRoot() {
    // Ermittle die Anzahl der Verzeichnisebenen
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptPath);
    
    // Zähle die Slashes (außer dem ersten)
    $depth = substr_count($scriptDir, '/');
    
    // Wenn wir im Root sind (z.B. /index.php), depth ist 1
    // Wenn wir in /bug-admin/file.php sind, depth ist 2
    if ($depth <= 1) {
        return '';
    }
    
    // Für jede Ebene über Root, gehe ein Verzeichnis zurück
    return str_repeat('../', $depth - 1);
}

/**
 * Minifiziere CSS-Datei - VERBESSERTE VERSION
 */
function minify_css($filepath) {
    $baseUrl = getRelativeRoot();
    
    if (!MINIFY_ENABLED) {
        // Gib den korrekten relativen Pfad zurück
        return $baseUrl . $filepath;
    }
    
    $fullpath = __DIR__ . '/' . $filepath;
    
    if (!file_exists($fullpath)) {
        return $baseUrl . $filepath; // Fallback
    }
    
    // Cache-Dateiname basierend auf Original + Timestamp
    $filename = basename($filepath, '.css');
    $mtime = filemtime($fullpath);
    $cachedFile = MINIFY_CACHE_DIR . '/' . $filename . '.' . $mtime . '.min.css';
    $cachedPath = $baseUrl . 'cache/minified/' . $filename . '.' . $mtime . '.min.css';
    
    // Wenn Cache existiert, verwende ihn
    if (file_exists($cachedFile)) {
        return $cachedPath;
    }
    
    // Alte Versionen löschen
    foreach (glob(MINIFY_CACHE_DIR . '/' . $filename . '.*.min.css') as $oldFile) {
        @unlink($oldFile);
    }
    
    // CSS lesen und minifizieren
    $css = file_get_contents($fullpath);
    $minified = minify_css_content($css);
    
    // Speichern
    file_put_contents($cachedFile, $minified);
    
    return $cachedPath;
}

/**
 * Minifiziere JS-Datei - VERBESSERTE VERSION
 */
function minify_js($filepath) {
    $baseUrl = getRelativeRoot();
    
    if (!MINIFY_ENABLED) {
        return $baseUrl . $filepath;
    }
    
    $fullpath = __DIR__ . '/' . $filepath;
    
    if (!file_exists($fullpath)) {
        return $baseUrl . $filepath; // Fallback
    }
    
    // Cache-Dateiname basierend auf Original + Timestamp
    $filename = basename($filepath, '.js');
    $mtime = filemtime($fullpath);
    $cachedFile = MINIFY_CACHE_DIR . '/' . $filename . '.' . $mtime . '.min.js';
    $cachedPath = $baseUrl . 'cache/minified/' . $filename . '.' . $mtime . '.min.js';
    
    // Wenn Cache existiert, verwende ihn
    if (file_exists($cachedFile)) {
        return $cachedPath;
    }
    
    // Alte Versionen löschen
    foreach (glob(MINIFY_CACHE_DIR . '/' . $filename . '.*.min.js') as $oldFile) {
        @unlink($oldFile);
    }
    
    // JS lesen und minifizieren
    $js = file_get_contents($fullpath);
    $minified = minify_js_content($js);
    
    // Speichern
    file_put_contents($cachedFile, $minified);
    
    return $cachedPath;
}

/**
 * CSS Minification
 */
function minify_css_content($css) {
    // Kommentare entfernen
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    
    // Whitespace entfernen
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    
    // Spaces um Symbole entfernen
    $css = preg_replace('/\s*([{}:;,>+])\s*/', '$1', $css);
    
    // Letzte Semicolons in Blocks entfernen
    $css = preg_replace('/;}/','}',$css);
    
    // Trim
    return trim($css);
}

/**
 * JS Minification (Basic)
 */
function minify_js_content($js) {
    // Kommentare entfernen (// und /* */)
    $js = preg_replace('~//[^\n]*~', '', $js);
    $js = preg_replace('~/\*.*?\*/~s', '', $js);
    
    // Mehrfache Leerzeichen reduzieren
    $js = preg_replace('/\s+/', ' ', $js);
    
    // Leerzeichen um Operatoren entfernen
    $js = preg_replace('/\s*([=+\-*\/<>!&|,;{}()\[\]])\s*/', '$1', $js);
    
    // Zeilenumbrüche entfernen
    $js = str_replace(["\r\n", "\r", "\n"], '', $js);
    
    return trim($js);
}

/**
 * Bundle mehrere CSS-Dateien zusammen
 */
function bundle_css($files) {
    $baseUrl = getRelativeRoot();
    
    if (!MINIFY_ENABLED) {
        return $files; // Return Array für separate Includes
    }
    
    // Cache-Key basierend auf allen Dateien
    $cacheKey = md5(implode('|', $files));
    $timestamps = array_map(function($f) {
        $path = __DIR__ . '/' . $f;
        return file_exists($path) ? filemtime($path) : 0;
    }, $files);
    $maxTime = max($timestamps);
    
    $cachedFile = MINIFY_CACHE_DIR . '/bundle.' . $cacheKey . '.' . $maxTime . '.css';
    $cachedPath = $baseUrl . 'cache/minified/bundle.' . $cacheKey . '.' . $maxTime . '.css';
    
    if (file_exists($cachedFile)) {
        return $cachedPath;
    }
    
    // Alte Bundles löschen
    foreach (glob(MINIFY_CACHE_DIR . '/bundle.' . $cacheKey . '.*.css') as $oldFile) {
        @unlink($oldFile);
    }
    
    // Alle CSS-Dateien zusammenfassen und minifizieren
    $combined = '';
    foreach ($files as $file) {
        $fullpath = __DIR__ . '/' . $file;
        if (file_exists($fullpath)) {
            $combined .= file_get_contents($fullpath) . "\n";
        }
    }
    
    $minified = minify_css_content($combined);
    file_put_contents($cachedFile, $minified);
    
    return $cachedPath;
}

/**
 * Bundle mehrere JS-Dateien zusammen
 */
function bundle_js($files) {
    $baseUrl = getRelativeRoot();
    
    if (!MINIFY_ENABLED) {
        return $files; // Return Array für separate Includes
    }
    
    // Cache-Key basierend auf allen Dateien
    $cacheKey = md5(implode('|', $files));
    $timestamps = array_map(function($f) {
        $path = __DIR__ . '/' . $f;
        return file_exists($path) ? filemtime($path) : 0;
    }, $files);
    $maxTime = max($timestamps);
    
    $cachedFile = MINIFY_CACHE_DIR . '/bundle.' . $cacheKey . '.' . $maxTime . '.js';
    $cachedPath = $baseUrl . 'cache/minified/bundle.' . $cacheKey . '.' . $maxTime . '.js';
    
    if (file_exists($cachedFile)) {
        return $cachedPath;
    }
    
    // Alte Bundles löschen
    foreach (glob(MINIFY_CACHE_DIR . '/bundle.' . $cacheKey . '.*.js') as $oldFile) {
        @unlink($oldFile);
    }
    
    // Alle JS-Dateien zusammenfassen und minifizieren
    $combined = '';
    foreach ($files as $file) {
        $fullpath = __DIR__ . '/' . $file;
        if (file_exists($fullpath)) {
            $combined .= file_get_contents($fullpath) . ";\n";
        }
    }
    
    $minified = minify_js_content($combined);
    file_put_contents($cachedFile, $minified);
    
    return $cachedPath;
}

/**
 * Cache leeren
 */
function clear_minify_cache() {
    $files = glob(MINIFY_CACHE_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    return count($files);
}

/**
 * Cache-Statistiken
 */
function get_minify_stats() {
    $files = glob(MINIFY_CACHE_DIR . '/*');
    $totalSize = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            $totalSize += filesize($file);
        }
    }
    
    return [
        'cached_files' => count($files),
        'total_size' => $totalSize,
        'total_size_formatted' => formatBytes($totalSize)
    ];
}

/**
 * Byte-Formatierung
 */
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Automatische Bereinigung alter Cache-Dateien (1% Chance)
if (mt_rand(1, 100) === 1) {
    $files = glob(MINIFY_CACHE_DIR . '/*');
    $now = time();
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) > 86400 * 7) { // 7 Tage alt
            @unlink($file);
        }
    }
}