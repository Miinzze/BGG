<?php
/**
 * QR-Code Branding Verwaltung
 * Logos für Branded QR-Codes hochladen und verwalten
 */

require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('qr_branding_manage');

$message = '';
$messageType = '';

// Logo hochladen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_logo'])) {
    validateCSRF();
    
    $name = trim($_POST['name'] ?? '');
    $setDefault = isset($_POST['set_default']);
    
    if (empty($name)) {
        $message = 'Bitte geben Sie einen Namen ein';
        $messageType = 'danger';
    } elseif (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Bitte wählen Sie eine Logo-Datei aus';
        $messageType = 'danger';
    } else {
        $file = $_FILES['logo'];
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2 MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $message = 'Nur PNG und JPEG Dateien sind erlaubt';
            $messageType = 'danger';
        } elseif ($file['size'] > $maxSize) {
            $message = 'Datei ist zu groß (max. 2 MB)';
            $messageType = 'danger';
        } else {
            try {
                // Upload-Verzeichnis sicherstellen
                $uploadDir = __DIR__ . '/uploads/qr-logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Dateiname generieren
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                $dbPath = 'uploads/qr-logos/' . $filename;
                
                // Bild optimieren (auf 200x200 skalieren für QR-Code)
                $image = null;
                if ($file['type'] === 'image/png') {
                    $image = imagecreatefrompng($file['tmp_name']);
                } else {
                    $image = imagecreatefromjpeg($file['tmp_name']);
                }
                
                if ($image) {
                    // Skalieren
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $newSize = 200;
                    
                    $newImage = imagecreatetruecolor($newSize, $newSize);
                    
                    // Transparenz für PNG beibehalten
                    if ($file['type'] === 'image/png') {
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                    }
                    
                    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newSize, $newSize, $width, $height);
                    
                    // Speichern
                    if ($file['type'] === 'image/png') {
                        imagepng($newImage, $filepath);
                    } else {
                        imagejpeg($newImage, $filepath, 90);
                    }
                    
                    imagedestroy($image);
                    imagedestroy($newImage);
                    
                    $pdo->beginTransaction();
                    
                    // Als Standard festlegen?
                    if ($setDefault) {
                        $pdo->exec("UPDATE qr_branding SET is_default = 0");
                    }
                    
                    // In Datenbank eintragen
                    $stmt = $pdo->prepare("
                        INSERT INTO qr_branding (name, logo_path, is_default, created_by)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $dbPath, $setDefault ? 1 : 0, $_SESSION['user_id']]);
                    
                    $pdo->commit();
                    
                    logActivity('qr_branding_added', "Logo '$name' hochgeladen");
                    
                    $message = '✓ Logo erfolgreich hochgeladen!';
                    $messageType = 'success';
                } else {
                    $message = 'Fehler beim Verarbeiten des Bildes';
                    $messageType = 'danger';
                }
                
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $message = 'Fehler: ' . e($e->getMessage());
                $messageType = 'danger';
            }
        }
    }
}

// Logo löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logo'])) {
    validateCSRF();
    
    $logoId = intval($_POST['logo_id']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM qr_branding WHERE id = ?");
        $stmt->execute([$logoId]);
        $logo = $stmt->fetch();
        
        if ($logo) {
            // Datei löschen
            $filepath = __DIR__ . '/' . $logo['logo_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Aus Datenbank löschen
            $stmt = $pdo->prepare("DELETE FROM qr_branding WHERE id = ?");
            $stmt->execute([$logoId]);
            
            logActivity('qr_branding_deleted', "Logo '{$logo['name']}' gelöscht");
            
            $message = '✓ Logo erfolgreich gelöscht';
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        $message = 'Fehler: ' . e($e->getMessage());
        $messageType = 'danger';
    }
}

// Standard-Logo setzen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default'])) {
    validateCSRF();
    
    $logoId = intval($_POST['logo_id']);
    
    try {
        $pdo->beginTransaction();
        
        $pdo->exec("UPDATE qr_branding SET is_default = 0");
        
        $stmt = $pdo->prepare("UPDATE qr_branding SET is_default = 1 WHERE id = ?");
        $stmt->execute([$logoId]);
        
        $pdo->commit();
        
        $message = '✓ Standard-Logo aktualisiert';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Fehler: ' . e($e->getMessage());
        $messageType = 'danger';
    }
}

// Alle Logos abrufen
$logos = $pdo->query("
    SELECT qb.*, u.username as created_by_name
    FROM qr_branding qb
    LEFT JOIN users u ON qb.created_by = u.id
    ORDER BY qb.is_default DESC, qb.created_at DESC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Code Branding - Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .logo-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
        }
        
        .logo-card.default {
            border: 3px solid #28a745;
        }
        
        .logo-card .default-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin: 15px auto;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .logo-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            border-color: #007bff;
            background: #e7f3ff;
        }
        
        .upload-area input[type="file"] {
            display: none;
        }
        
        .upload-label {
            cursor: pointer;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-palette"></i> QR-Code Branding</h1>
                <p>Verwalten Sie Logos für Branded QR-Codes</p>
                <a href="qr_code_generator.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zurück
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <!-- Logo hochladen -->
            <div class="form-section">
                <h2><i class="fas fa-upload"></i> Neues Logo hochladen</h2>
                
                <form method="POST" enctype="multipart/form-data" class="marker-form">
                    <?= csrf_field() ?>
                    
                    <div class="upload-area" onclick="document.getElementById('logo-file').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #007bff; margin-bottom: 15px;"></i>
                        <div>
                            <label for="logo-file" class="upload-label">Klicken Sie hier um ein Logo auszuwählen</label>
                            <input type="file" id="logo-file" name="logo" accept="image/png,image/jpeg,image/jpg" required>
                        </div>
                        <p style="color: #666; margin-top: 10px; font-size: 14px;">
                            PNG oder JPEG, max. 2 MB<br>
                            Empfohlen: Quadratisches Format, mindestens 200x200 Pixel
                        </p>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="name">Logo-Name</label>
                        <input type="text" id="name" name="name" placeholder="z.B. Firmenlogo" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="set_default">
                            Als Standard-Logo festlegen
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="upload_logo" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Logo hochladen
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Vorhandene Logos -->
            <div class="form-section">
                <h2><i class="fas fa-images"></i> Vorhandene Logos (<?= count($logos) ?>)</h2>
                
                <?php if (empty($logos)): ?>
                    <p style="text-align: center; color: #666; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Noch keine Logos hochgeladen
                    </p>
                <?php else: ?>
                    <div class="logo-grid">
                        <?php foreach ($logos as $logo): ?>
                            <div class="logo-card <?= $logo['is_default'] ? 'default' : '' ?>">
                                <?php if ($logo['is_default']): ?>
                                    <div class="default-badge">
                                        <i class="fas fa-star"></i> Standard
                                    </div>
                                <?php endif; ?>
                                
                                <h3><?= e($logo['name']) ?></h3>
                                
                                <img src="<?= e($logo['logo_path']) ?>" 
                                     alt="<?= e($logo['name']) ?>" 
                                     class="logo-preview">
                                
                                <div style="font-size: 12px; color: #666; margin-top: 10px;">
                                    Hochgeladen am <?= formatDateTime($logo['created_at']) ?><br>
                                    von <?= e($logo['created_by_name'] ?? 'Unbekannt') ?>
                                </div>
                                
                                <div class="logo-actions">
                                    <?php if (!$logo['is_default']): ?>
                                        <form method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="logo_id" value="<?= $logo['id'] ?>">
                                            <button type="submit" name="set_default" class="btn btn-sm btn-success" 
                                                    title="Als Standard festlegen">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="print_qr.php?code=DEMO&logo=<?= $logo['id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       target="_blank" 
                                       title="Vorschau">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Logo wirklich löschen?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="logo_id" value="<?= $logo['id'] ?>">
                                        <button type="submit" name="delete_logo" class="btn btn-sm btn-danger" 
                                                title="Löschen">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Hinweise -->
            <div class="form-section">
                <h2><i class="fas fa-info-circle"></i> Hinweise</h2>
                <ul>
                    <li>Das Logo wird in der Mitte des QR-Codes platziert</li>
                    <li>Verwenden Sie möglichst einfache Logos ohne viele Details</li>
                    <li>Das Standard-Logo wird automatisch bei neuen QR-Codes verwendet</li>
                    <li>Logos sollten einen transparenten oder weißen Hintergrund haben</li>
                    <li>Die Lesbarkeit des QR-Codes bleibt durch Fehlerkorrektur erhalten</li>
                </ul>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Datei-Auswahl Feedback
        document.getElementById('logo-file')?.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const uploadArea = document.querySelector('.upload-area');
                uploadArea.querySelector('.upload-label').textContent = 
                    'Ausgewählt: ' + file.name;
                uploadArea.style.borderColor = '#28a745';
                uploadArea.style.background = '#d4edda';
            }
        });
    </script>
</body>
</html>
