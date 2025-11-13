<?php
require_once 'config.php';
require_once 'functions.php';
requireAdmin();

trackUsage('edit_user');

$userId = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die('Benutzer nicht gefunden');
}

$message = '';
$messageType = '';

// Benutzer aktualisieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Ungültiges Sicherheitstoken';
        $messageType = 'danger';
    } else {
        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roleId = $_POST['role_id'] ?? null;
        $receiveMaintenanceEmails = isset($_POST['receive_maintenance_emails']) ? 1 : 0;
        $require2fa = isset($_POST['require_2fa']) ? 1 : 0;
        $forcePasswordChange = isset($_POST['force_password_change']) ? 1 : 0;
        $newPassword = $_POST['new_password'] ?? '';
        
        if (!validateEmail($email)) {
            $message = 'Gültige E-Mail-Adresse erforderlich';
            $messageType = 'danger';
        } elseif (!empty($phone) && !preg_match('/^[\d\s\+\-\/\(\)]+$/', $phone)) {
            $message = 'Ungültiges Telefonnummer-Format';
            $messageType = 'danger';
        } elseif (!validateInteger($roleId, 1)) {
            $message = 'Gültige Rolle erforderlich';
            $messageType = 'danger';
        } else {
            try {
                if (!empty($newPassword)) {
                    $pwCheck = validatePasswordStrength($newPassword);
                    if (!$pwCheck['valid']) {
                        throw new Exception($pwCheck['message']);
                    }
                    
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            email = ?, 
                            first_name = ?, 
                            last_name = ?, 
                            phone = ?, 
                            role_id = ?, 
                            receive_maintenance_emails = ?, 
                            require_2fa = ?, 
                            must_change_password = ?,
                            password = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $email, 
                        $firstName, 
                        $lastName, 
                        $phone, 
                        intval($roleId), 
                        $receiveMaintenanceEmails, 
                        $require2fa,
                        $forcePasswordChange, 
                        $hashedPassword, 
                        $userId
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            email = ?, 
                            first_name = ?, 
                            last_name = ?, 
                            phone = ?, 
                            role_id = ?, 
                            receive_maintenance_emails = ?, 
                            require_2fa = ?,
                            must_change_password = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $email, 
                        $firstName, 
                        $lastName, 
                        $phone, 
                        intval($roleId), 
                        $receiveMaintenanceEmails, 
                        $require2fa,
                        $forcePasswordChange, 
                        $userId
                    ]);
                }
                
                // Rolle-String aktualisieren
                $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
                $stmt->execute([$roleId]);
                $roleName = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$roleName, $userId]);
                
                logActivity('user_updated', "Benutzer '{$user['username']}' aktualisiert");
                
                $message = 'Benutzer erfolgreich aktualisiert!';
                $messageType = 'success';
                
                // Benutzer neu laden
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
            } catch (Exception $e) {
                $message = 'Fehler: ' . e($e->getMessage());
                $messageType = 'danger';
            }
        }
    }
}

// 2FA zurücksetzen
if (isset($_GET['reset_2fa'])) {
    $stmt = $pdo->prepare("DELETE FROM user_2fa WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $stmt = $pdo->prepare("UPDATE users SET has_2fa_enabled = 0 WHERE id = ?");
    $stmt->execute([$userId]);
    
    logActivity('2fa_reset', "2FA für Benutzer '{$user['username']}' zurückgesetzt");
    
    header("Location: edit_user.php?id=$userId&reset_success=1");
    exit;
}

// Rollen laden
$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <title>Benutzer bearbeiten</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="js/password-strength.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-user-edit"></i> Benutzer bearbeiten</h1>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zurück
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['reset_success'])): ?>
                <div class="alert alert-success">2FA erfolgreich zurückgesetzt!</div>
            <?php endif; ?>
            
            <div class="admin-grid">
                <div class="admin-section">
                    <h2>Benutzerdaten</h2>
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
                                    value="<?= e($user['first_name']) ?>"
                                    placeholder="Max">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Nachname</label>
                                <input type="text" id="last_name" name="last_name" 
                                    value="<?= e($user['last_name']) ?>"
                                    placeholder="Mustermann">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-Mail *</label>
                            <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Telefonnummer</label>
                            <input type="tel" id="phone" name="phone" 
                                value="<?= e($user['phone']) ?>"
                                placeholder="+49 123 456789">
                            <small>Optional - für Benachrichtigungen</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="role_id">Rolle *</label>
                            <select id="role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= e($role['display_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <h3><i class="fas fa-envelope"></i> E-Mail-Benachrichtigungen</h3>
                            <p style="color: #666; margin-bottom: 15px;">
                                <i class="fas fa-info-circle"></i> 
                                Legen Sie fest, für welche Ereignisse dieser Benutzer E-Mails erhalten soll.
                            </p>
                            
                            <label class="checkbox-group">
                                <input type="checkbox" id="receive_maintenance_emails" name="receive_maintenance_emails" 
                                    <?= $user['receive_maintenance_emails'] ? 'checked' : '' ?>>
                                <span>
                                    <strong>Wartungs- und Prüfungs-Erinnerungen</strong>
                                    <br>
                                    <small style="color: #666;">
                                        E-Mails bei fälliger Wartung UND bei fälligen Prüfungen (DGUV, UVV, TÜV)
                                    </small>
                                </span>
                            </label>
                            
                            <div style="margin-top: 15px; background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; border-radius: 4px;">
                                <p style="margin: 0; font-size: 0.9em;">
                                    <i class="fas fa-lightbulb"></i> <strong>Hinweis:</strong>
                                    E-Mails werden täglich automatisch versendet, wenn Wartungen oder Prüfungen 
                                    innerhalb der konfigurierten Erinnerungsfrist fällig sind.
                                    <br><br>
                                    <i class="fas fa-cog"></i> 
                                    Erinnerungsfristen können in den 
                                    <a href="settings.php" style="color: #007bff;">Systemeinstellungen</a> angepasst werden.
                                </p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="require_2fa" 
                                    <?= $user['require_2fa'] ? 'checked' : '' ?>>
                                <span>
                                    <strong>Zwei-Faktor-Authentifizierung (2FA) verpflichtend</strong><br>
                                    <small>Benutzer muss 2FA aktivieren und bei jedem Login verwenden</small>
                                </span>
                            </label>
                            <div style="margin-top: 10px;">
                                <a href="user_2fa_settings.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-shield-alt"></i> Erweiterte 2FA-Einstellungen (Backup-Codes, SMS/WhatsApp, Trusted Devices)
                                </a>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="force_password_change" id="forcePasswordChange"
                                    <?= (isset($user['must_change_password']) && $user['must_change_password'] == 1) ? 'checked' : '' ?>>
                                <span>
                                    <strong><i class="fas fa-shield-lock"></i> Benutzer muss beim nächsten Login das Passwort ändern</strong><br>
                                    <small>Nützlich wenn Sie ein Passwort zurückgesetzt haben</small>
                                </span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Neues Passwort</label>
                            <input type="password" id="new_password" name="new_password">
                            <small>Leer lassen, um Passwort nicht zu ändern. Mindestens 8 Zeichen, 1 Großbuchstabe, 1 Kleinbuchstabe, 1 Zahl</small>
                        </div>
                        
                        <button type="submit" name="update_user" class="btn btn-success">
                            <i class="fas fa-save"></i> Änderungen speichern
                        </button>
                    </form>
                </div>
                
                <div class="admin-section">
                    <h2>2FA Status</h2>
                    
                    <div style="margin-bottom: 20px;">
                        <p><strong>2FA Pflicht:</strong> 
                            <?php if ($user['require_2fa']): ?>
                                <span class="badge badge-warning">Ja</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nein</span>
                            <?php endif; ?>
                        </p>
                        
                        <p><strong>2FA Aktiviert:</strong> 
                            <?php if ($user['has_2fa_enabled']): ?>
                                <span class="badge badge-success">Ja</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nein</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if ($user['has_2fa_enabled']): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Wenn Sie 2FA zurücksetzen, muss der Benutzer beim nächsten Login 2FA neu einrichten.
                        </div>
                        
                        <a href="?id=<?= $userId ?>&reset_2fa=1" class="btn btn-danger"
                           onclick="return confirm('2FA wirklich zurücksetzen? Der Benutzer muss es neu einrichten!')">
                            <i class="fas fa-undo"></i> 2FA zurücksetzen
                        </a>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Benutzer hat 2FA noch nicht eingerichtet.
                            <?php if ($user['require_2fa']): ?>
                                <br>Da 2FA verpflichtend ist, wird der Benutzer beim nächsten Login zur Einrichtung aufgefordert.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="margin-top: 30px;">Letzte Aktivität</h3>
                    <p><strong>Letzter Login:</strong> 
                        <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) . ' Uhr' : 'Noch nie' ?>
                    </p>
                    <p><strong>Erstellt am:</strong> 
                        <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?> Uhr
                    </p>
                </div>
                
                <!-- NEU: Was kann dieser Benutzer? -->
                <div class="admin-section">
                    <h2><i class="fas fa-shield-alt"></i> Was kann dieser Benutzer?</h2>
                    
                    <?php
                    // Rolle und Permissions laden
                    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
                    $stmt->execute([$user['role_id']]);
                    $userRole = $stmt->fetch();
                    
                    // Permissions dekodieren
                    $permissions = [];
                    if ($userRole && !empty($userRole['permissions'])) {
                        $permissions = json_decode($userRole['permissions'], true) ?: [];
                    }
                    
                    // Permissions kategorisieren
                    $permissionCategories = [
                        'Benutzerverwaltung' => ['users_manage', 'users_view', 'roles_manage'],
                        'Marker & Objekte' => ['markers_create', 'markers_edit', 'markers_delete', 'markers_view'],
                        'Prüfungen & Wartung' => ['inspections_create', 'inspections_edit', 'inspections_delete', 'inspections_view', 'maintenance_manage'],
                        'System & Einstellungen' => ['settings_manage', 'backup_restore', 'logs_view', 'system_admin'],
                        'Reports & Export' => ['reports_view', 'export_data', 'import_data'],
                        'Geofencing' => ['geofences_manage', 'geofences_view'],
                        'Kategorien' => ['categories_manage']
                    ];
                    
                    $permissionLabels = [
                        'users_manage' => 'Benutzer verwalten',
                        'users_view' => 'Benutzer anzeigen',
                        'roles_manage' => 'Rollen verwalten',
                        'markers_create' => 'Marker erstellen',
                        'markers_edit' => 'Marker bearbeiten',
                        'markers_delete' => 'Marker löschen',
                        'markers_view' => 'Marker anzeigen',
                        'inspections_create' => 'Prüfungen erstellen',
                        'inspections_edit' => 'Prüfungen bearbeiten',
                        'inspections_delete' => 'Prüfungen löschen',
                        'inspections_view' => 'Prüfungen anzeigen',
                        'maintenance_manage' => 'Wartung verwalten',
                        'settings_manage' => 'Einstellungen verwalten',
                        'backup_restore' => 'Backup & Wiederherstellung',
                        'logs_view' => 'Logs anzeigen',
                        'system_admin' => 'System-Administrator',
                        'reports_view' => 'Reports anzeigen',
                        'export_data' => 'Daten exportieren',
                        'import_data' => 'Daten importieren',
                        'geofences_manage' => 'Geofences verwalten',
                        'geofences_view' => 'Geofences anzeigen',
                        'categories_manage' => 'Kategorien verwalten'
                    ];
                    ?>
                    
                    <div style="margin-bottom: 20px;">
                        <p><strong>Rolle:</strong> <span class="badge badge-primary"><?= e($userRole['display_name']) ?></span></p>
                        <p><small><?= e($userRole['description'] ?? '') ?></small></p>
                    </div>
                    
                    <h3>Berechtigungen im Detail</h3>
                    
                    <?php foreach ($permissionCategories as $category => $perms): ?>
                        <div style="margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <h4 style="margin-top: 0; color: #495057;"><?= $category ?></h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                                <?php foreach ($perms as $perm): ?>
                                    <?php if (isset($permissionLabels[$perm])): ?>
                                        <div style="display: flex; align-items: center;">
                                            <?php if (isset($permissions[$perm]) && $permissions[$perm]): ?>
                                                <i class="fas fa-check-circle" style="color: #28a745; margin-right: 8px;"></i>
                                                <span><?= $permissionLabels[$perm] ?></span>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle" style="color: #dc3545; margin-right: 8px;"></i>
                                                <span style="color: #6c757d;"><?= $permissionLabels[$perm] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- NEU: Als Benutzer anzeigen -->
                <div class="admin-section">
                    <h2><i class="fas fa-user-secret"></i> Admin-Funktionen</h2>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Als Benutzer anzeigen:</strong> Sehen Sie das System aus der Perspektive dieses Benutzers. 
                        Hilfreich zum Testen von Berechtigungen und Support.
                    </div>
                    
                    <a href="view_as_user.php?user_id=<?= $userId ?>" class="btn btn-warning" 
                       onclick="return confirm('Als Benutzer <?= e($user['username']) ?> anzeigen?\n\nSie werden temporär mit den Rechten dieses Benutzers angemeldet.')">
                        <i class="fas fa-eye"></i> Als "<?= e($user['username']) ?>" anzeigen
                    </a>
                    
                    <a href="send_welcome_email.php?user_id=<?= $userId ?>" class="btn btn-success"
                       onclick="return confirm('Willkommens-Email an <?= e($user['email']) ?> senden?')">
                        <i class="fas fa-envelope"></i> Willkommens-Email senden
                    </a>
                    
                    <p style="margin-top: 15px; font-size: 0.9em; color: #6c757d;">
                        <i class="fas fa-shield-alt"></i> Alle Aktionen werden im Activity-Log protokolliert.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>