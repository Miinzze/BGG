<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

trackUsage('profile_view');

$message = '';
$messageType = '';

// Benutzer-Daten laden
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Profilbild Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) {
    validateCSRF();
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $fileType = $_FILES['profile_image']['type'];
    $fileSize = $_FILES['profile_image']['size'];
    
    if (!in_array($fileType, $allowed_types)) {
        $message = 'Ungültiges Bildformat. Erlaubt: JPG, PNG, GIF, WebP';
        $messageType = 'danger';
    } elseif ($fileSize > $max_size) {
        $message = 'Bild ist zu groß. Maximal 2MB erlaubt.';
        $messageType = 'danger';
    } else {
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $filepath)) {
            // Altes Bild löschen
            if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                unlink($user['profile_image']);
            }
            
            // In DB speichern
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$filepath, $_SESSION['user_id']]);
            
            logActivity('profile_image_updated', 'Profilbild geändert');
            
            $message = 'Profilbild erfolgreich hochgeladen!';
            $messageType = 'success';
            
            // Neu laden
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } else {
            $message = 'Fehler beim Hochladen';
            $messageType = 'danger';
        }
    }
}

// Profilbild löschen
if (isset($_GET['delete_image']) && !empty($user['profile_image'])) {
    if (file_exists($user['profile_image'])) {
        unlink($user['profile_image']);
    }
    
    $stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    logActivity('profile_image_deleted', 'Profilbild gelöscht');
    
    header('Location: profile.php');
    exit;
}

// Profil aktualisieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    validateCSRF();
    
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (!validateEmail($email)) {
        $message = 'Gültige E-Mail-Adresse erforderlich';
        $messageType = 'danger';
    } elseif (!empty($phone) && !preg_match('/^[\d\s\+\-\/\(\)]+$/', $phone)) {
        $message = 'Ungültiges Telefonnummer-Format';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $email, $phone, $_SESSION['user_id']]);
            
            logActivity('profile_updated', 'Profil aktualisiert');
            
            $message = 'Profil erfolgreich aktualisiert!';
            $messageType = 'success';
            
            // Neu laden
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
        } catch (Exception $e) {
            $message = 'Fehler: ' . e($e->getMessage());
            $messageType = 'danger';
        }
    }
}

// Passwort ändern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    validateCSRF();
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (!password_verify($currentPassword, $user['password'])) {
        $message = 'Aktuelles Passwort ist falsch';
        $messageType = 'danger';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Neue Passwörter stimmen nicht überein';
        $messageType = 'danger';
    } else {
        $pwCheck = validatePasswordStrength($newPassword);
        if (!$pwCheck['valid']) {
            $message = $pwCheck['message'];
            $messageType = 'danger';
        } else {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                logActivity('password_changed', 'Passwort geändert');
                
                $message = 'Passwort erfolgreich geändert!';
                $messageType = 'success';
                
            } catch (Exception $e) {
                $message = 'Fehler: ' . e($e->getMessage());
                $messageType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <title>Mein Profil - RFID Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-user-circle"></i> Mein Profil</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <div class="admin-grid">
                <!-- Profil-Informationen -->
                <div class="admin-section">
                    <h2>Profil-Informationen</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label>Benutzername</label>
                            <input type="text" value="<?= e($user['username']) ?>" disabled>
                            <small>Benutzername kann nicht geändert werden</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Vorname</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?= e($user['first_name']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Nachname</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?= e($user['last_name']) ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-Mail *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= e($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Telefonnummer</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?= e($user['phone']) ?>"
                                   placeholder="+49 123 456789">
                        </div>
                        
                        <!-- Profilbild Section VOR admin-grid -->
                        <div class="info-card" style="margin-bottom: 30px;">
                            <h2><i class="fas fa-camera"></i> Profilbild</h2>
                            
                            <div style="text-align: center; margin: 20px 0;">
                                <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                                    <img src="<?= e($user['profile_image']) ?>?v=<?= time() ?>" 
                                        alt="Profilbild" 
                                        style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #e63312; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                                <?php else: ?>
                                    <div style="width: 150px; height: 150px; border-radius: 50%; background: #f8f9fa; display: inline-flex; align-items: center; justify-content: center; border: 3px solid #dee2e6;">
                                        <i class="fas fa-user" style="font-size: 60px; color: #6c757d;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data" style="max-width: 500px; margin: 0 auto;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="form-group">
                                    <label for="profile_image">
                                        <i class="fas fa-upload"></i> Neues Profilbild hochladen
                                    </label>
                                    <input type="file" 
                                        id="profile_image" 
                                        name="profile_image" 
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        onchange="previewImage(this)">
                                    <small>Max. 2MB | JPG, PNG, GIF, WebP</small>
                                </div>
                                
                                <div id="imagePreview" style="text-align: center; margin: 15px 0;"></div>
                                
                                <div style="display: flex; gap: 10px; justify-content: center;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Hochladen
                                    </button>
                                    
                                    <?php if (!empty($user['profile_image'])): ?>
                                    <a href="profile.php?delete_image=1" 
                                    class="btn btn-danger" 
                                    onclick="return confirm('Profilbild wirklich löschen?')">
                                        <i class="fas fa-trash"></i> Bild löschen
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <script>
                            function previewImage(input) {
                                const preview = document.getElementById('imagePreview');
                                
                                if (input.files && input.files[0]) {
                                    const reader = new FileReader();
                                    
                                    reader.onload = function(e) {
                                        preview.innerHTML = '<img src="' + e.target.result + '" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #007bff;">';
                                    };
                                    
                                    reader.readAsDataURL(input.files[0]);
                                } else {
                                    preview.innerHTML = '';
                                }
                            }
                        </script>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Profil speichern
                        </button>
                    </form>
                </div>
                
                <!-- Passwort ändern -->
                <div class="admin-section">
                    <h2>Passwort ändern</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label for="current_password">Aktuelles Passwort *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Neues Passwort *</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <small>Mindestens 8 Zeichen, 1 Großbuchstabe, 1 Kleinbuchstabe, 1 Zahl</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Neues Passwort wiederholen *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-success">
                            <i class="fas fa-key"></i> Passwort ändern
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Sicherheit -->
            <div class="info-card">
                <h2><i class="fas fa-shield-alt"></i> Sicherheit</h2>
                
                <div style="margin: 20px 0;">
                    <p><strong>Zwei-Faktor-Authentifizierung (2FA):</strong></p>
                    <?php
                    $stmt = $pdo->prepare("SELECT is_enabled FROM user_2fa WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $has2FA = $stmt->fetchColumn();
                    ?>
                    
                    <?php if ($has2FA): ?>
                        <span class="badge badge-success">Aktiviert</span>
                        <a href="setup_2fa.php" class="btn btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-cog"></i> 2FA Verwalten
                        </a>
                    <?php else: ?>
                        <span class="badge badge-secondary">Nicht aktiviert</span>
                        <a href="setup_2fa.php" class="btn btn-primary" style="margin-left: 10px;">
                            <i class="fas fa-shield-alt"></i> 2FA Einrichten
                        </a>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    <p><strong>Letzter Login:</strong> 
                        <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) . ' Uhr' : 'Noch nie' ?>
                    </p>
                    <p><strong>Konto erstellt:</strong> 
                        <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?> Uhr
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>