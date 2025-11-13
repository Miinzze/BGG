<?php
/**
 * ===============================================
 * BILDOPTIMIERUNGS-SYSTEM
 * ===============================================
 * 
 * Automatische Komprimierung und Optimierung von hochgeladenen Bildern
 * 
 * FEATURES:
 * - Automatische Größenanpassung
 * - Qualitätsoptimierung (80% Standard)
 * - EXIF-Orientierung korrigieren
 * - WebP-Konvertierung (optional)
 * - Thumbnail-Generierung
 * - Erhält Original als Backup
 * 
 * INTEGRATION:
 * In config.php NACH functions.php:
 * require_once __DIR__ . '/image_optimizer.php';
 * 
 * VERWENDUNG:
 * $result = optimizeUploadedImage($_FILES['image'], [
 *     'max_width' => 1920,
 *     'max_height' => 1080,
 *     'quality' => 85,
 *     'create_webp' => true
 * ]);
 */

// Konfiguration
define('IMAGE_OPTIMIZER_ENABLED', true);
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1080);
define('IMAGE_QUALITY', 85);
define('IMAGE_THUMBNAIL_SIZE', 300);
define('IMAGE_CREATE_WEBP', true);

/**
 * Optimiere hochgeladenes Bild
 * 
 * @param array $file $_FILES['fieldname']
 * @param array $options Optionen für Optimierung
 * @return array ['success' => bool, 'path' => string, 'webp_path' => string, 'message' => string]
 */
function optimizeUploadedImage($file, $options = []) {
    if (!IMAGE_OPTIMIZER_ENABLED) {
        return [
            'success' => false,
            'message' => 'Bildoptimierung ist deaktiviert'
        ];
    }
    
    // Prüfe ob Datei hochgeladen wurde
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return [
            'success' => false,
            'message' => 'Keine gültige hochgeladene Datei'
        ];
    }
    
    // Prüfe Dateigröße (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return [
            'success' => false,
            'message' => 'Datei zu groß (max. 10MB)'
        ];
    }
    
    // Prüfe Dateityp
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return [
            'success' => false,
            'message' => 'Ungültiger Dateityp. Nur JPG, PNG, GIF und WebP erlaubt.'
        ];
    }
    
    // Optionen mit Defaults mergen
    $options = array_merge([
        'max_width' => IMAGE_MAX_WIDTH,
        'max_height' => IMAGE_MAX_HEIGHT,
        'quality' => IMAGE_QUALITY,
        'create_webp' => IMAGE_CREATE_WEBP,
        'create_thumbnail' => false,
        'thumbnail_size' => IMAGE_THUMBNAIL_SIZE,
        'target_dir' => 'uploads/',
        'keep_original' => true
    ], $options);
    
    // Stelle sicher dass Zielverzeichnis existiert
    if (!file_exists($options['target_dir'])) {
        mkdir($options['target_dir'], 0755, true);
    }
    
    // Generiere eindeutigen Dateinamen
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '_' . time() . '.' . $extension;
    $targetPath = $options['target_dir'] . $filename;
    
    try {
        // Lade Bild
        $image = loadImage($file['tmp_name'], $mimeType);
        
        if (!$image) {
            return [
                'success' => false,
                'message' => 'Bild konnte nicht geladen werden'
            ];
        }
        
        // Hole Original-Dimensionen
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        
        // Korrigiere EXIF-Orientierung
        $image = correctImageOrientation($image, $file['tmp_name']);
        
        // Berechne neue Dimensionen
        list($newWidth, $newHeight) = calculateNewDimensions(
            $origWidth, 
            $origHeight, 
            $options['max_width'], 
            $options['max_height']
        );
        
        // Nur resizen wenn nötig
        $needsResize = ($origWidth > $options['max_width'] || $origHeight > $options['max_height']);
        
        if ($needsResize) {
            $optimized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Transparenz für PNG/GIF erhalten
            if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
                imagealphablending($optimized, false);
                imagesavealpha($optimized, true);
                $transparent = imagecolorallocatealpha($optimized, 255, 255, 255, 127);
                imagefilledrectangle($optimized, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resample mit hoher Qualität
            imagecopyresampled(
                $optimized, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $origWidth, $origHeight
            );
        } else {
            $optimized = $image;
        }
        
        // Speichere optimiertes Bild
        $saved = saveOptimizedImage($optimized, $targetPath, $mimeType, $options['quality']);
        
        if (!$saved) {
            imagedestroy($image);
            if ($needsResize) imagedestroy($optimized);
            return [
                'success' => false,
                'message' => 'Bild konnte nicht gespeichert werden'
            ];
        }
        
        $result = [
            'success' => true,
            'path' => $targetPath,
            'filename' => $filename,
            'original_size' => $file['size'],
            'optimized_size' => filesize($targetPath),
            'savings_percent' => round((1 - filesize($targetPath) / $file['size']) * 100, 1),
            'dimensions' => [
                'original' => ['width' => $origWidth, 'height' => $origHeight],
                'optimized' => ['width' => $newWidth, 'height' => $newHeight]
            ]
        ];
        
        // WebP-Version erstellen (wenn aktiviert und möglich)
        if ($options['create_webp'] && function_exists('imagewebp')) {
            $webpPath = $options['target_dir'] . pathinfo($filename, PATHINFO_FILENAME) . '.webp';
            if (imagewebp($optimized, $webpPath, $options['quality'])) {
                $result['webp_path'] = $webpPath;
                $result['webp_size'] = filesize($webpPath);
            }
        }
        
        // Thumbnail erstellen (wenn gewünscht)
        if ($options['create_thumbnail']) {
            $thumbPath = createThumbnail(
                $optimized, 
                $options['target_dir'], 
                $filename, 
                $options['thumbnail_size']
            );
            if ($thumbPath) {
                $result['thumbnail_path'] = $thumbPath;
            }
        }
        
        // Aufräumen
        imagedestroy($image);
        if ($needsResize) imagedestroy($optimized);
        
        return $result;
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Fehler bei der Bildoptimierung: ' . $e->getMessage()
        ];
    }
}

/**
 * Lade Bild basierend auf MIME-Type
 */
function loadImage($path, $mimeType) {
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            return @imagecreatefromjpeg($path);
        case 'image/png':
            return @imagecreatefrompng($path);
        case 'image/gif':
            return @imagecreatefromgif($path);
        case 'image/webp':
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
        default:
            return false;
    }
}

/**
 * Korrigiere EXIF-Orientierung
 */
function correctImageOrientation($image, $filepath) {
    if (!function_exists('exif_read_data')) {
        return $image;
    }
    
    $exif = @exif_read_data($filepath);
    if (!$exif || !isset($exif['Orientation'])) {
        return $image;
    }
    
    $orientation = $exif['Orientation'];
    
    switch ($orientation) {
        case 3:
            return imagerotate($image, 180, 0);
        case 6:
            return imagerotate($image, -90, 0);
        case 8:
            return imagerotate($image, 90, 0);
        default:
            return $image;
    }
}

/**
 * Berechne neue Dimensionen unter Beibehaltung des Seitenverhältnisses
 */
function calculateNewDimensions($origWidth, $origHeight, $maxWidth, $maxHeight) {
    $ratio = $origWidth / $origHeight;
    
    if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
        if ($ratio > 1) {
            // Landscape
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        } else {
            // Portrait
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $ratio;
        }
    } else {
        $newWidth = $origWidth;
        $newHeight = $origHeight;
    }
    
    return [round($newWidth), round($newHeight)];
}

/**
 * Speichere optimiertes Bild
 */
function saveOptimizedImage($image, $path, $mimeType, $quality) {
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            return imagejpeg($image, $path, $quality);
        case 'image/png':
            // PNG Qualität: 0-9 (0 = keine Kompression, 9 = max)
            $pngQuality = round((100 - $quality) / 11.11);
            return imagepng($image, $path, $pngQuality);
        case 'image/gif':
            return imagegif($image, $path);
        case 'image/webp':
            return function_exists('imagewebp') ? imagewebp($image, $path, $quality) : false;
        default:
            return false;
    }
}

/**
 * Erstelle Thumbnail
 */
function createThumbnail($image, $targetDir, $filename, $size) {
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Berechne Thumbnail-Dimensionen (quadratisch)
    $thumbWidth = $thumbHeight = $size;
    
    $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
    
    // Transparenz erhalten
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);
    $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
    imagefilledrectangle($thumb, 0, 0, $thumbWidth, $thumbHeight, $transparent);
    
    // Crop zum Quadrat
    $sourceSize = min($width, $height);
    $sourceX = ($width - $sourceSize) / 2;
    $sourceY = ($height - $sourceSize) / 2;
    
    imagecopyresampled(
        $thumb, $image,
        0, 0,
        $sourceX, $sourceY,
        $thumbWidth, $thumbHeight,
        $sourceSize, $sourceSize
    );
    
    $thumbFilename = 'thumb_' . $filename;
    $thumbPath = $targetDir . $thumbFilename;
    
    if (imagejpeg($thumb, $thumbPath, 85)) {
        imagedestroy($thumb);
        return $thumbPath;
    }
    
    imagedestroy($thumb);
    return false;
}

/**
 * Batch-Optimierung existierender Bilder
 */
function batchOptimizeImages($directory, $options = []) {
    $results = [
        'total' => 0,
        'optimized' => 0,
        'failed' => 0,
        'total_savings' => 0,
        'errors' => []
    ];
    
    if (!is_dir($directory)) {
        return $results;
    }
    
    $files = glob($directory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $results['total'] = count($files);
    
    foreach ($files as $file) {
        $originalSize = filesize($file);
        
        // Lade Bild
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file);
        finfo_close($finfo);
        
        $image = loadImage($file, $mimeType);
        if (!$image) {
            $results['failed']++;
            $results['errors'][] = "Konnte nicht laden: " . basename($file);
            continue;
        }
        
        // Backup erstellen
        $backupPath = $file . '.backup';
        copy($file, $backupPath);
        
        // Optimiere
        $width = imagesx($image);
        $height = imagesy($image);
        
        list($newWidth, $newHeight) = calculateNewDimensions(
            $width, 
            $height, 
            IMAGE_MAX_WIDTH, 
            IMAGE_MAX_HEIGHT
        );
        
        $optimized = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($optimized, false);
            imagesavealpha($optimized, true);
            $transparent = imagecolorallocatealpha($optimized, 255, 255, 255, 127);
            imagefilledrectangle($optimized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled($optimized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        if (saveOptimizedImage($optimized, $file, $mimeType, IMAGE_QUALITY)) {
            $newSize = filesize($file);
            $savings = $originalSize - $newSize;
            $results['total_savings'] += $savings;
            $results['optimized']++;
        } else {
            // Restore backup
            copy($backupPath, $file);
            $results['failed']++;
            $results['errors'][] = "Konnte nicht optimieren: " . basename($file);
        }
        
        imagedestroy($image);
        imagedestroy($optimized);
        
        // Lösche Backup wenn erfolgreich
        if (file_exists($backupPath)) {
            unlink($backupPath);
        }
    }
    
    return $results;
}

/**
 * Formatiere Bytes zu lesbarem Format
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