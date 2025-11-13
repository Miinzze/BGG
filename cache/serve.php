<?php
/**
 * Cache File Server mit korrekten MIME-Types
 * Liefert minifizierte CSS/JS Dateien mit korrekten Headers aus
 */

// File-Parameter aus URL holen
$file = $_GET['file'] ?? '';

// Sicherheit: Nur Dateien im minified-Verzeichnis erlauben
$file = basename($file); // Verhindert Directory Traversal
$filePath = __DIR__ . '/minified/' . $file;

// Prüfen ob Datei existiert
if (!file_exists($filePath) || !is_file($filePath)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

// MIME-Type basierend auf Dateiendung
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mimeTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
];

$mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

// Cache-Headers setzen (1 Jahr)
$expires = 60 * 60 * 24 * 365; // 1 Jahr in Sekunden
header('Content-Type: ' . $mimeType);
header('Cache-Control: public, max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
header('Pragma: public');

// Content-Length für besseres Caching
$fileSize = filesize($filePath);
header('Content-Length: ' . $fileSize);

// Datei ausgeben
readfile($filePath);
exit;
