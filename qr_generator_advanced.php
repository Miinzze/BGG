<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();
requirePermission('generate_qr_codes');

$marker_id = $_GET['marker_id'] ?? null;

if (!$marker_id) {
    die('Keine Marker-ID angegeben');
}

// Marker laden
$stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$marker_id]);
$marker = $stmt->fetch();

if (!$marker) {
    die('Gerät nicht gefunden');
}

$success = '';
$error = '';

// QR-Code generieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_qr') {
    validateCSRF();
    
    try {
        require_once 'vendor/autoload.php'; // Composer autoload für phpqrcode
        
        $qrSize = $_POST['qr_size'] ?? 300;
        $fgColor = $_POST['fg_color'] ?? '#000000';
        $bgColor = $_POST['bg_color'] ?? '#FFFFFF';
        $logoPosition = $_POST['logo_position'] ?? 'center';
        $logoSizePercent = $_POST['logo_size_percent'] ?? 20;
        $frameStyle = $_POST['frame_style'] ?? 'square';
        $companyName = $_POST['company_name'] ?? '';
        $customText = $_POST['custom_text'] ?? '';
        $gradientStart = $_POST['gradient_start'] ?? null;
        $gradientEnd = $_POST['gradient_end'] ?? null;
        
        // Logo Upload
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/qr_logos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $logoFilename = 'logo_' . uniqid() . '.' . $extension;
            $logoPath = $uploadDir . $logoFilename;
            
            move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
        }
        
        // QR-Code Daten
        $scanUrl = $websiteUrl . '/scan.php?qr=' . urlencode($marker['qr_code']);
        
        // QR-Code mit PHP QR Code Library erstellen
        $qrDir = 'uploads/qr_codes/';
        if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);
        
        $qrFilename = 'qr_' . $marker['qr_code'] . '_' . time() . '.png';
        $qrPath = $qrDir . $qrFilename;
        
        // Basis QR-Code generieren
        QRcode::png($scanUrl, $qrPath, QR_ECLEVEL_H, 10, 2);
        
        // QR-Code mit GD bearbeiten (Logo, Farben, etc.)
        $qrImage = imagecreatefrompng($qrPath);
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);
        
        // Canvas mit gewünschter Größe erstellen (mit Platz für Text)
        $textSpace = 0;
        if ($companyName || $customText) {
            $textSpace = 80;
        }
        
        $canvas = imagecreatetruecolor($qrSize, $qrSize + $textSpace);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        
        // Background (Gradient oder Solid)
        if ($gradientStart && $gradientEnd) {
            // Gradient erstellen
            list($r1, $g1, $b1) = sscanf($gradientStart, "#%02x%02x%02x");
            list($r2, $g2, $b2) = sscanf($gradientEnd, "#%02x%02x%02x");
            
            for ($i = 0; $i < $qrSize; $i++) {
                $ratio = $i / $qrSize;
                $r = floor($r1 + ($r2 - $r1) * $ratio);
                $g = floor($g1 + ($g2 - $g1) * $ratio);
                $b = floor($b1 + ($b2 - $b1) * $ratio);
                $color = imagecolorallocate($canvas, $r, $g, $b);
                imageline($canvas, 0, $i, $qrSize, $i, $color);
            }
        } else {
            // Solid Background
            list($r, $g, $b) = sscanf($bgColor, "#%02x%02x%02x");
            $bgColorGd = imagecolorallocate($canvas, $r, $g, $b);
            imagefill($canvas, 0, 0, $bgColorGd);
        }
        
        // QR-Code auf Canvas kopieren
        $margin = 20;
        $qrTargetSize = $qrSize - ($margin * 2);
        imagecopyresampled($canvas, $qrImage, $margin, $margin, 0, 0, 
                          $qrTargetSize, $qrTargetSize, $qrWidth, $qrHeight);
        
        // Logo hinzufügen
        if ($logoPath && file_exists($logoPath)) {
            $logoImage = null;
            $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            
            if ($extension === 'png') {
                $logoImage = imagecreatefrompng($logoPath);
            } elseif ($extension === 'jpg' || $extension === 'jpeg') {
                $logoImage = imagecreatefromjpeg($logoPath);
            } elseif ($extension === 'gif') {
                $logoImage = imagecreatefromgif($logoPath);
            }
            
            if ($logoImage) {
                $logoWidth = imagesx($logoImage);
                $logoHeight = imagesy($logoImage);
                
                // Logo Größe berechnen
                $logoTargetSize = ($qrSize * $logoSizePercent) / 100;
                $logoTargetWidth = $logoTargetSize;
                $logoTargetHeight = ($logoHeight / $logoWidth) * $logoTargetSize;
                
                // Position berechnen
                $logoX = 0;
                $logoY = 0;
                
                switch ($logoPosition) {
                    case 'center':
                        $logoX = ($qrSize - $logoTargetWidth) / 2;
                        $logoY = ($qrSize - $logoTargetHeight) / 2;
                        break;
                    case 'top':
                        $logoX = ($qrSize - $logoTargetWidth) / 2;
                        $logoY = $margin + 10;
                        break;
                    case 'bottom':
                        $logoX = ($qrSize - $logoTargetWidth) / 2;
                        $logoY = $qrSize - $margin - $logoTargetHeight - 10;
                        break;
                    case 'top_left':
                        $logoX = $margin + 10;
                        $logoY = $margin + 10;
                        break;
                    case 'top_right':
                        $logoX = $qrSize - $margin - $logoTargetWidth - 10;
                        $logoY = $margin + 10;
                        break;
                    case 'bottom_left':
                        $logoX = $margin + 10;
                        $logoY = $qrSize - $margin - $logoTargetHeight - 10;
                        break;
                    case 'bottom_right':
                        $logoX = $qrSize - $margin - $logoTargetWidth - 10;
                        $logoY = $qrSize - $margin - $logoTargetHeight - 10;
                        break;
                }
                
                // Weißer Hintergrund für Logo
                $logoMargin = 5;
                $whiteRect = imagecolorallocate($canvas, 255, 255, 255);
                imagefilledrectangle($canvas, 
                    $logoX - $logoMargin, 
                    $logoY - $logoMargin, 
                    $logoX + $logoTargetWidth + $logoMargin, 
                    $logoY + $logoTargetHeight + $logoMargin, 
                    $whiteRect);
                
                // Logo kopieren
                imagecopyresampled($canvas, $logoImage, 
                    $logoX, $logoY, 0, 0, 
                    $logoTargetWidth, $logoTargetHeight, 
                    $logoWidth, $logoHeight);
                
                imagedestroy($logoImage);
            }
        }
        
        // Text hinzufügen
        if ($companyName || $customText) {
            $textColor = imagecolorallocate($canvas, 0, 0, 0);
            $font = 5; // Built-in font
            
            $y = $qrSize + 20;
            
            if ($companyName) {
                $textWidth = imagefontwidth($font) * strlen($companyName);
                $x = ($qrSize - $textWidth) / 2;
                imagestring($canvas, $font, $x, $y, $companyName, $textColor);
                $y += 20;
            }
            
            if ($customText) {
                $textWidth = imagefontwidth($font) * strlen($customText);
                $x = ($qrSize - $textWidth) / 2;
                imagestring($canvas, $font, $x, $y, $customText, $textColor);
            }
        }
        
        // Finales Bild speichern
        imagepng($canvas, $qrPath);
        imagedestroy($canvas);
        imagedestroy($qrImage);
        
        // In Datenbank speichern/aktualisieren
        $stmt = $pdo->prepare("
            INSERT INTO qr_codes 
            (qr_code, file_path, logo_path, logo_position, logo_size_percent, frame_style, 
             gradient_start, gradient_end, company_name, custom_text, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            file_path = VALUES(file_path),
            logo_path = VALUES(logo_path),
            logo_position = VALUES(logo_position),
            logo_size_percent = VALUES(logo_size_percent),
            frame_style = VALUES(frame_style),
            gradient_start = VALUES(gradient_start),
            gradient_end = VALUES(gradient_end),
            company_name = VALUES(company_name),
            custom_text = VALUES(custom_text)
        ");
        
        $stmt->execute([
            $marker['qr_code'],
            $qrPath,
            $logoPath,
            $logoPosition,
            $logoSizePercent,
            $frameStyle,
            $gradientStart,
            $gradientEnd,
            $companyName ?: null,
            $customText ?: null,
            $_SESSION['user_id']
        ]);
        
        logActivity('qr_generated', "QR-Code mit Branding für '{$marker['name']}' generiert");
        $success = "QR-Code erfolgreich generiert!";
        
        // Zur Download-Seite weiterleiten
        header("Location: ?marker_id=$marker_id&success=1&qr_path=" . urlencode($qrPath));
        exit;
        
    } catch (Exception $e) {
        $error = "Fehler: " . $e->getMessage();
    }
}

$qrPath = $_GET['qr_path'] ?? null;

include 'header.php';
?>

<style>
.qr-generator-container {
    max-width: 1000px;
    margin: 0 auto;
}

.preview-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    text-align: center;
}

.qr-preview {
    max-width: 400px;
    margin: 20px auto;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.qr-preview img {
    max-width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.color-picker-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.color-picker-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.color-picker-item input[type="color"] {
    width: 100%;
    height: 50px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.logo-upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.logo-upload-area:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.logo-upload-area.has-file {
    border-color: #4caf50;
    background: #f1f8f4;
}
</style>

<div class="container">
    <div class="qr-generator-container">
        <div class="page-header">
            <h1><i class="fas fa-qrcode"></i> QR-Code mit Branding</h1>
            <a href="view_marker.php?id=<?= $marker_id ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Zurück
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Geräte-Info -->
        <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Gerät</h3>
            <p style="margin: 10px 0 0 0;">
                <strong><?= htmlspecialchars($marker['name']) ?></strong>
                <br>QR-Code: <?= htmlspecialchars($marker['qr_code']) ?>
                <?php if ($marker['serial_number']): ?>
                    | Seriennummer: <?= htmlspecialchars($marker['serial_number']) ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if ($qrPath && file_exists($qrPath)): ?>
            <!-- Vorschau und Download -->
            <div class="preview-section">
                <h2><i class="fas fa-check-circle"></i> QR-Code erfolgreich erstellt!</h2>
                
                <div class="qr-preview">
                    <img src="<?= htmlspecialchars($qrPath) ?>?t=<?= time() ?>" alt="QR-Code">
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                    <a href="<?= htmlspecialchars($qrPath) ?>" download class="btn btn-success btn-lg">
                        <i class="fas fa-download"></i> QR-Code herunterladen
                    </a>
                    <a href="?marker_id=<?= $marker_id ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-redo"></i> Neuen QR-Code erstellen
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary btn-lg">
                        <i class="fas fa-print"></i> Drucken
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Generator-Formular -->
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="generate_qr">

                <!-- Logo Upload -->
                <div class="form-section">
                    <h3><i class="fas fa-image"></i> Logo</h3>
                    
                    <div class="logo-upload-area" id="logoUploadArea">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #667eea;"></i>
                        <p style="margin: 15px 0 5px 0; font-size: 18px;">Logo hochladen</p>
                        <p style="color: #666; margin: 0;">PNG, JPG oder GIF (max. 5MB)</p>
                        <input type="file" name="logo" id="logoInput" accept="image/*" style="display: none;">
                    </div>
                    
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Logo Position</label>
                                <select name="logo_position" class="form-control">
                                    <option value="center">Zentrum</option>
                                    <option value="top">Oben</option>
                                    <option value="bottom">Unten</option>
                                    <option value="top_left">Oben Links</option>
                                    <option value="top_right">Oben Rechts</option>
                                    <option value="bottom_left">Unten Links</option>
                                    <option value="bottom_right">Unten Rechts</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Logo Größe (%)</label>
                                <input type="range" name="logo_size_percent" min="10" max="40" value="20" 
                                       class="form-control" id="logoSizeRange">
                                <small class="text-muted">Aktuell: <span id="logoSizeValue">20</span>%</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Design -->
                <div class="form-section">
                    <h3><i class="fas fa-palette"></i> Design</h3>
                    
                    <div class="form-group">
                        <label>Stil</label>
                        <select name="frame_style" class="form-control">
                            <option value="square">Quadratisch</option>
                            <option value="round">Abgerundet</option>
                            <option value="dots">Punkte</option>
                            <option value="none">Ohne Rahmen</option>
                        </select>
                    </div>
                    
                    <div class="color-picker-group">
                        <div class="color-picker-item">
                            <label>Vordergrundfarbe</label>
                            <input type="color" name="fg_color" value="#000000">
                        </div>
                        <div class="color-picker-item">
                            <label>Hintergrundfarbe</label>
                            <input type="color" name="bg_color" value="#FFFFFF">
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <label>
                            <input type="checkbox" id="gradientCheckbox"> Gradient verwenden
                        </label>
                    </div>
                    
                    <div id="gradientOptions" style="display: none; margin-top: 15px;">
                        <div class="color-picker-group">
                            <div class="color-picker-item">
                                <label>Gradient Start</label>
                                <input type="color" name="gradient_start" value="#667eea">
                            </div>
                            <div class="color-picker-item">
                                <label>Gradient Ende</label>
                                <input type="color" name="gradient_end" value="#764ba2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text & Branding -->
                <div class="form-section">
                    <h3><i class="fas fa-font"></i> Text & Branding</h3>
                    
                    <div class="form-group">
                        <label>Firmenname</label>
                        <input type="text" name="company_name" class="form-control" 
                               placeholder="z.B. Ihre Firma GmbH">
                    </div>
                    
                    <div class="form-group">
                        <label>Zusätzlicher Text</label>
                        <input type="text" name="custom_text" class="form-control" 
                               placeholder="z.B. Scannen für Details">
                    </div>
                </div>

                <!-- Größe -->
                <div class="form-section">
                    <h3><i class="fas fa-expand"></i> Größe</h3>
                    
                    <div class="form-group">
                        <label>QR-Code Größe (Pixel)</label>
                        <select name="qr_size" class="form-control">
                            <option value="200">200x200 (Klein)</option>
                            <option value="300" selected>300x300 (Standard)</option>
                            <option value="500">500x500 (Groß)</option>
                            <option value="800">800x800 (Extra Groß)</option>
                            <option value="1000">1000x1000 (Druck-Qualität)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-magic"></i> QR-Code generieren
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Logo Upload Area
const logoUploadArea = document.getElementById('logoUploadArea');
const logoInput = document.getElementById('logoInput');

logoUploadArea.addEventListener('click', () => {
    logoInput.click();
});

logoInput.addEventListener('change', () => {
    if (logoInput.files.length > 0) {
        logoUploadArea.classList.add('has-file');
        logoUploadArea.querySelector('p').textContent = 'Logo ausgewählt: ' + logoInput.files[0].name;
    }
});

// Logo Size Range
const logoSizeRange = document.getElementById('logoSizeRange');
const logoSizeValue = document.getElementById('logoSizeValue');

logoSizeRange.addEventListener('input', () => {
    logoSizeValue.textContent = logoSizeRange.value;
});

// Gradient Toggle
const gradientCheckbox = document.getElementById('gradientCheckbox');
const gradientOptions = document.getElementById('gradientOptions');

gradientCheckbox.addEventListener('change', () => {
    gradientOptions.style.display = gradientCheckbox.checked ? 'block' : 'none';
});
</script>

<?php include 'footer.php'; ?>