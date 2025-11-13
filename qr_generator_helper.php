<?php
/**
 * QR-Code Generator mit Logo-Unterstützung
 * Generiert QR-Codes mit eingebettetem Logo
 */

/**
 * Generiert einen QR-Code mit optionalem Logo
 * 
 * @param string $data Die zu kodierenden Daten
 * @param string|null $logoPath Pfad zum Logo (optional)
 * @param int $size Größe des QR-Codes (Standard: 400)
 * @return string Base64-kodiertes Bild
 */
function generateBrandedQRCode($data, $logoPath = null, $size = 400) {
    // QR-Code API URL
    $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&margin=10&ecc=H&data=' . urlencode($data);
    
    // QR-Code von API abrufen
    $qrImage = @file_get_contents($qrApiUrl);
    
    if ($qrImage === false) {
        throw new Exception('QR-Code konnte nicht generiert werden');
    }
    
    // Wenn kein Logo, direkt zurückgeben
    if (!$logoPath || !file_exists($logoPath)) {
        return 'data:image/png;base64,' . base64_encode($qrImage);
    }
    
    // QR-Code als GD-Image laden
    $qr = imagecreatefromstring($qrImage);
    if ($qr === false) {
        return 'data:image/png;base64,' . base64_encode($qrImage);
    }
    
    // Logo laden
    $logoExt = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    if ($logoExt === 'png') {
        $logo = imagecreatefrompng($logoPath);
    } elseif (in_array($logoExt, ['jpg', 'jpeg'])) {
        $logo = imagecreatefromjpeg($logoPath);
    } else {
        imagedestroy($qr);
        return 'data:image/png;base64,' . base64_encode($qrImage);
    }
    
    if ($logo === false) {
        imagedestroy($qr);
        return 'data:image/png;base64,' . base64_encode($qrImage);
    }
    
    // Logo-Größe berechnen (ca. 20% des QR-Codes)
    $qrWidth = imagesx($qr);
    $qrHeight = imagesy($qr);
    $logoSize = intval($qrWidth * 0.20);
    
    // Logo skalieren
    $logoWidth = imagesx($logo);
    $logoHeight = imagesy($logo);
    $logoResized = imagecreatetruecolor($logoSize, $logoSize);
    
    // Transparenz beibehalten
    imagealphablending($logoResized, false);
    imagesavealpha($logoResized, true);
    $transparent = imagecolorallocatealpha($logoResized, 0, 0, 0, 127);
    imagefill($logoResized, 0, 0, $transparent);
    imagealphablending($logoResized, true);
    
    // Logo skalieren
    imagecopyresampled(
        $logoResized, $logo,
        0, 0, 0, 0,
        $logoSize, $logoSize,
        $logoWidth, $logoHeight
    );
    
    // Weißen Hintergrund für Logo erstellen (für bessere Lesbarkeit)
    $padding = intval($logoSize * 0.1);
    $bgSize = $logoSize + ($padding * 2);
    $background = imagecreatetruecolor($bgSize, $bgSize);
    $white = imagecolorallocate($background, 255, 255, 255);
    imagefill($background, 0, 0, $white);
    
    // Abgerundete Ecken für Hintergrund
    imagefilledrectangle($background, 0, 0, $bgSize, $bgSize, $white);
    
    // Logo auf Hintergrund kopieren
    imagecopy(
        $background, $logoResized,
        $padding, $padding,
        0, 0,
        $logoSize, $logoSize
    );
    
    // Logo mittig auf QR-Code platzieren
    $logoX = intval(($qrWidth - $bgSize) / 2);
    $logoY = intval(($qrHeight - $bgSize) / 2);
    
    imagecopy(
        $qr, $background,
        $logoX, $logoY,
        0, 0,
        $bgSize, $bgSize
    );
    
    // Als PNG ausgeben und Base64 kodieren
    ob_start();
    imagepng($qr);
    $imageData = ob_get_clean();
    
    // Aufräumen
    imagedestroy($qr);
    imagedestroy($logo);
    imagedestroy($logoResized);
    imagedestroy($background);
    
    return 'data:image/png;base64,' . base64_encode($imageData);
}

/**
 * Gibt das Standard-Logo zurück, falls vorhanden
 * 
 * @param PDO $pdo Datenbankverbindung
 * @return string|null Pfad zum Standard-Logo oder null
 */
function getDefaultLogo($pdo) {
    $stmt = $pdo->query("SELECT logo_path FROM qr_branding WHERE is_default = 1 LIMIT 1");
    $logo = $stmt->fetch();
    
    if ($logo) {
        $logoPath = __DIR__ . '/' . $logo['logo_path'];
        if (file_exists($logoPath)) {
            return $logoPath;
        }
    }
    
    return null;
}

/**
 * Gibt ein spezifisches Logo zurück
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $logoId Logo-ID
 * @return string|null Pfad zum Logo oder null
 */
function getLogoById($pdo, $logoId) {
    $stmt = $pdo->prepare("SELECT logo_path FROM qr_branding WHERE id = ?");
    $stmt->execute([$logoId]);
    $logo = $stmt->fetch();
    
    if ($logo) {
        $logoPath = __DIR__ . '/' . $logo['logo_path'];
        if (file_exists($logoPath)) {
            return $logoPath;
        }
    }
    
    return null;
}
