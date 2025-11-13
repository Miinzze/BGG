<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('users_manage');

$message = '';
$messageType = '';

// User ID aus URL
$userId = $_GET['id'] ?? null;
if (!$userId || !validateInteger($userId, 1)) {
    header('Location: users.php');
    exit;
}

// Benutzer laden
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit;
}

// ========== BACKUP-CODES GENERIEREN ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_backup_codes'])) {
    validateCSRF();
    
    // Alte Codes löschen
    $stmt = $pdo->prepare("DELETE FROM user_backup_codes WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 10 neue Codes generieren
    $backupCodes = [];
    for ($i = 0; $i < 10; $i++) {
        $code = strtoupper(bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4)));
        $backupCodes[] = $code;
        
        $stmt = $pdo->prepare("INSERT INTO user_backup_codes (user_id, code) VALUES (?, ?)");
        $stmt->execute([$userId, $code]);
    }
    
    logActivity('backup_codes_generated', "Backup-Codes für Benutzer '{$user['username']}' generiert");
    $message = 'Backup-Codes erfolgreich generiert!';
    $messageType = 'success';
}

// ========== SMS/WHATSAPP 2FA EINRICHTEN ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_phone_2fa'])) {
    validateCSRF();
    
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $method = $_POST['method'] ?? 'sms';
    
    if (preg_match('/^[\d\s\+\-\/\(\)]+$/', $phoneNumber)) {
        // Prüfen ob bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM user_2fa_phone WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->fetch()) {
            // Update
            $stmt = $pdo->prepare("UPDATE user_2fa_phone SET phone_number = ?, method = ? WHERE user_id = ?");
            $stmt->execute([$phoneNumber, $method, $userId]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO user_2fa_phone (user_id, phone_number, method) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $phoneNumber, $method]);
        }
        
        logActivity('phone_2fa_setup', "SMS/WhatsApp 2FA für '{$user['username']}' eingerichtet");
        $message = 'SMS/WhatsApp 2FA erfolgreich eingerichtet!';
        $messageType = 'success';
    } else {
        $message = 'Ungültige Telefonnummer';
        $messageType = 'danger';
    }
}

// ========== SMS/WHATSAPP 2FA ENTFERNEN ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_phone_2fa'])) {
    validateCSRF();
    
    $stmt = $pdo->prepare("DELETE FROM user_2fa_phone WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    logActivity('phone_2fa_removed', "SMS/WhatsApp 2FA für '{$user['username']}' entfernt");
    $message = 'SMS/WhatsApp 2FA entfernt';
    $messageType = 'success';
}

// ========== TRUSTED DEVICE ENTFERNEN ==========
if (isset($_GET['remove_device'])) {
    $deviceId = $_GET['remove_device'];
    $stmt = $pdo->prepare("DELETE FROM user_trusted_devices WHERE id = ? AND user_id = ?");
    $stmt->execute([$deviceId, $userId]);
    
    logActivity('trusted_device_removed', "Trusted Device für '{$user['username']}' entfernt");
    $message = 'Vertrauenswürdiges Gerät entfernt';
    $messageType = 'success';
}

// Backup-Codes laden
$stmt = $pdo->prepare("SELECT * FROM user_backup_codes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$backupCodes = $stmt->fetchAll();

// Phone 2FA laden
$stmt = $pdo->prepare("SELECT * FROM user_2fa_phone WHERE user_id = ?");
$stmt->execute([$userId]);
$phone2fa = $stmt->fetch();

// Trusted Devices laden
$stmt = $pdo->prepare("SELECT * FROM user_trusted_devices WHERE user_id = ? ORDER BY last_used DESC");
$stmt->execute([$userId]);
$trustedDevices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA-Einstellungen - <?= e($user['username']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .backup-codes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        
        .backup-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            border: 2px solid #dee2e6;
        }
        
        .backup-code.used {
            background: #e9ecef;
            text-decoration: line-through;
            opacity: 0.5;
        }
        
        .device-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .device-card h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .print-button {
            margin: 10px 0;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .backup-codes-print, .backup-codes-print * {
                visibility: visible;
            }
            .backup-codes-print {
                position: absolute;
                left: 0;
                top: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1>Erweiterte 2FA-Einstellungen</h1>
                <p>Benutzer: <strong><?= e($user['username']) ?></strong></p>
                <a href="edit_user.php?id=<?= $userId ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zurück zum Benutzer
                </a>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= e($message) ?>
            </div>
            <?php endif; ?>
            
            <div class="admin-section-container">
                <!-- Backup-Codes -->
                <div class="admin-section">
                    <h2><i class="fas fa-key"></i> Backup-Codes zum Ausdrucken</h2>
                    <p>Backup-Codes können verwendet werden, wenn der normale 2FA-Zugang nicht möglich ist. Jeder Code kann nur einmal verwendet werden.</p>
                    
                    <?php if (!empty($backupCodes)): ?>
                        <button onclick="window.print()" class="btn btn-primary print-button no-print">
                            <i class="fas fa-print"></i> Backup-Codes ausdrucken
                        </button>
                        
                        <div class="backup-codes-print">
                            <h3>Backup-Codes für <?= e($user['username']) ?></h3>
                            <p>Generiert am: <?= date('d.m.Y H:i:s') ?></p>
                            
                            <div class="backup-codes-grid">
                                <?php foreach ($backupCodes as $code): ?>
                                <div class="backup-code <?= $code['used'] ? 'used' : '' ?>">
                                    <?= e($code['code']) ?>
                                    <?php if ($code['used']): ?>
                                        <br><small>(Verwendet am <?= date('d.m.Y H:i', strtotime($code['used_at'])) ?>)</small>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <form method="post" onsubmit="return confirm('Neue Backup-Codes generieren? Alte Codes werden ungültig!')" class="no-print">
                            <?= csrf_field() ?>
                            <button type="submit" name="generate_backup_codes" class="btn btn-warning">
                                <i class="fas fa-sync"></i> Neue Backup-Codes generieren
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <button type="submit" name="generate_backup_codes" class="btn btn-success">
                                <i class="fas fa-key"></i> Backup-Codes generieren
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- SMS/WhatsApp 2FA -->
                <div class="admin-section">
                    <h2><i class="fas fa-mobile-alt"></i> SMS/WhatsApp als 2FA-Alternative</h2>
                    
                    <?php if ($phone2fa): ?>
                        <div class="alert alert-success">
                            <strong>SMS/WhatsApp 2FA ist aktiv</strong><br>
                            Telefonnummer: <?= e($phone2fa['phone_number']) ?><br>
                            Methode: <?= e(ucfirst($phone2fa['method'])) ?><br>
                            Status: <?= $phone2fa['verified'] ? '<span class="badge badge-success">Verifiziert</span>' : '<span class="badge badge-warning">Nicht verifiziert</span>' ?>
                        </div>
                        
                        <form method="post" onsubmit="return confirm('SMS/WhatsApp 2FA wirklich entfernen?')">
                            <?= csrf_field() ?>
                            <button type="submit" name="remove_phone_2fa" class="btn btn-danger">
                                <i class="fas fa-trash"></i> SMS/WhatsApp 2FA entfernen
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" class="admin-form">
                            <?= csrf_field() ?>
                            
                            <div class="form-group">
                                <label for="phone_number">Telefonnummer *</label>
                                <input type="tel" id="phone_number" name="phone_number" 
                                       value="<?= e($user['phone']) ?>" 
                                       placeholder="+49 123 456789" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="method">Methode *</label>
                                <select id="method" name="method" required>
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="setup_phone_2fa" class="btn btn-success">
                                <i class="fas fa-mobile-alt"></i> SMS/WhatsApp 2FA einrichten
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Trusted Devices -->
                <div class="admin-section">
                    <h2><i class="fas fa-laptop"></i> Vertrauenswürdige Geräte</h2>
                    <p>Auf vertrauenswürdigen Geräten wird 30 Tage lang keine 2FA abgefragt. Der Benutzer kann Geräte beim Login als "vertrauenswürdig" markieren.</p>
                    
                    <?php if (!empty($trustedDevices)): ?>
                        <?php foreach ($trustedDevices as $device): ?>
                        <div class="device-card">
                            <h4>
                                <i class="fas fa-laptop"></i> 
                                <?= e($device['device_name'] ?: 'Unbenanntes Gerät') ?>
                            </h4>
                            <p>
                                <strong>IP:</strong> <?= e($device['ip_address']) ?><br>
                                <strong>Vertrauenswürdig bis:</strong> <?= date('d.m.Y H:i', strtotime($device['trusted_until'])) ?><br>
                                <strong>Zuletzt verwendet:</strong> <?= date('d.m.Y H:i', strtotime($device['last_used'])) ?><br>
                                <strong>User Agent:</strong> <small><?= e(substr($device['user_agent'], 0, 100)) ?>...</small>
                            </p>
                            <a href="?id=<?= $userId ?>&remove_device=<?= $device['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Gerät wirklich entfernen?')">
                                <i class="fas fa-trash"></i> Entfernen
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #6c757d; font-style: italic;">Keine vertrauenswürdigen Geräte vorhanden</p>
                    <?php endif; ?>
                </div>
                
                <!-- Statistik -->
                <div class="admin-section">
                    <h2><i class="fas fa-chart-bar"></i> 2FA-Statistik</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div class="info-box">
                            <h4>Backup-Codes</h4>
                            <p style="font-size: 24px; margin: 10px 0;">
                                <?= count(array_filter($backupCodes, function($c) { return !$c['used']; })) ?> / <?= count($backupCodes) ?>
                            </p>
                            <small>Verfügbar / Gesamt</small>
                        </div>
                        
                        <div class="info-box">
                            <h4>SMS/WhatsApp</h4>
                            <p style="font-size: 24px; margin: 10px 0;">
                                <?= $phone2fa ? '<i class="fas fa-check-circle" style="color: green;"></i>' : '<i class="fas fa-times-circle" style="color: red;"></i>' ?>
                            </p>
                            <small><?= $phone2fa ? 'Eingerichtet' : 'Nicht eingerichtet' ?></small>
                        </div>
                        
                        <div class="info-box">
                            <h4>Trusted Devices</h4>
                            <p style="font-size: 24px; margin: 10px 0;">
                                <?= count($trustedDevices) ?>
                            </p>
                            <small>Vertrauenswürdige Geräte</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
