<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('users_manage');

$message = '';
$messageType = '';

// Alle Rollen abrufen
$stmt = $pdo->query("SELECT * FROM roles ORDER BY display_name");
$roles = $stmt->fetchAll();

// ========== BULK-OPERATIONEN ==========
// Bulk 2FA verpflichtend machen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_require_2fa'])) {
    validateCSRF();
    $userIds = $_POST['selected_users'] ?? [];
    
    if (!empty($userIds)) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $stmt = $pdo->prepare("UPDATE users SET require_2fa = 1 WHERE id IN ($placeholders)");
        $stmt->execute($userIds);
        
        logActivity('bulk_2fa_enabled', count($userIds) . " Benutzer mit 2FA-Pflicht versehen");
        $message = count($userIds) . " Benutzer: 2FA ist nun verpflichtend";
        $messageType = 'success';
    } else {
        $message = 'Keine Benutzer ausgewählt';
        $messageType = 'warning';
    }
}

// Bulk 2FA deaktivieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_disable_2fa'])) {
    validateCSRF();
    $userIds = $_POST['selected_users'] ?? [];
    
    if (!empty($userIds)) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $stmt = $pdo->prepare("UPDATE users SET require_2fa = 0 WHERE id IN ($placeholders)");
        $stmt->execute($userIds);
        
        logActivity('bulk_2fa_disabled', count($userIds) . " Benutzer ohne 2FA-Pflicht");
        $message = count($userIds) . " Benutzer: 2FA-Pflicht entfernt";
        $messageType = 'success';
    }
}

// Bulk Löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    validateCSRF();
    $userIds = $_POST['selected_users'] ?? [];
    
    // Aktuellen Benutzer aus der Liste entfernen
    $userIds = array_filter($userIds, function($id) {
        return $id != $_SESSION['user_id'];
    });
    
    if (!empty($userIds)) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders)");
        $stmt->execute($userIds);
        
        logActivity('bulk_delete', count($userIds) . " Benutzer gelöscht");
        $message = count($userIds) . " Benutzer gelöscht";
        $messageType = 'success';
    }
}

// CSV/Excel Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_users']) && isset($_FILES['import_file'])) {
    validateCSRF();
    
    $file = $_FILES['import_file'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $totalRows = 0;
    $successCount = 0;
    $failCount = 0;
    $errors = [];
    
    if ($ext === 'csv') {
        if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ',');
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $totalRows++;
                
                // CSV Format: username, email, first_name, last_name, phone, password, role_id
                if (count($data) >= 7) {
                    $username = trim($data[0]);
                    $email = trim($data[1]);
                    $firstName = trim($data[2]);
                    $lastName = trim($data[3]);
                    $phone = trim($data[4]);
                    $password = $data[5];
                    $roleId = intval($data[6]);
                    
                    // Validierung
                    if (validateUsername($username) && validateEmail($email) && validateInteger($roleId, 1)) {
                        try {
                            // Prüfen ob bereits existiert
                            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                            $stmt->execute([$username, $email]);
                            
                            if (!$stmt->fetch()) {
                                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                                
                                $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
                                $stmt->execute([$roleId]);
                                $roleName = $stmt->fetchColumn();
                                
                                $stmt = $pdo->prepare("
                                    INSERT INTO users (username, email, first_name, last_name, phone, password, role, role_id, must_change_password) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                                ");
                                $stmt->execute([$username, $email, $firstName, $lastName, $phone, $hashedPassword, $roleName, $roleId]);
                                $successCount++;
                            } else {
                                $failCount++;
                                $errors[] = "Zeile $totalRows: Benutzer '$username' existiert bereits";
                            }
                        } catch (PDOException $e) {
                            $failCount++;
                            $errors[] = "Zeile $totalRows: " . $e->getMessage();
                        }
                    } else {
                        $failCount++;
                        $errors[] = "Zeile $totalRows: Ungültige Daten";
                    }
                } else {
                    $failCount++;
                    $errors[] = "Zeile $totalRows: Unvollständige Daten";
                }
            }
            fclose($handle);
        }
    }
    
    // Import-Historie speichern
    $stmt = $pdo->prepare("INSERT INTO user_bulk_imports (imported_by, filename, total_rows, successful_rows, failed_rows, error_log) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $filename, $totalRows, $successCount, $failCount, json_encode($errors)]);
    
    logActivity('bulk_import', "CSV-Import: $successCount erfolgreich, $failCount fehlgeschlagen");
    $message = "Import abgeschlossen: $successCount erfolgreich, $failCount fehlgeschlagen";
    $messageType = $failCount > 0 ? 'warning' : 'success';
}

// CSV/Excel Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Header
    fputcsv($output, ['ID', 'Benutzername', 'E-Mail', 'Vorname', 'Nachname', 'Telefon', 'Rolle', 'Rolle ID', '2FA Pflicht', '2FA Aktiv', 'Erstellt', 'Letzter Login']);
    
    // Daten
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['username'],
            $row['email'],
            $row['first_name'],
            $row['last_name'],
            $row['phone'],
            $row['role'],
            $row['role_id'],
            $row['require_2fa'] ? 'Ja' : 'Nein',
            $row['has_2fa_enabled'] ? 'Ja' : 'Nein',
            $row['created_at'],
            $row['last_login']
        ]);
    }
    
    fclose($output);
    logActivity('user_export', "Benutzer als CSV exportiert");
    exit;
}

// Neuen Benutzer erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    validateCSRF();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId = $_POST['role_id'] ?? null;
    $receiveMaintenanceEmails = isset($_POST['receive_maintenance_emails']) ? 1 : 0;
    $require2fa = isset($_POST['require_2fa']) ? 1 : 0;
    $forcePasswordChange = isset($_POST['force_password_change']) ? 1 : 0;
    
    // Validierung
    if (!validateUsername($username)) {
        $message = 'Benutzername muss 3-50 Zeichen lang sein und darf nur Buchstaben, Zahlen und Unterstriche enthalten';
        $messageType = 'danger';
    } elseif (!validateEmail($email)) {
        $message = 'Gültige E-Mail-Adresse erforderlich';
        $messageType = 'danger';
    } elseif (!empty($phone) && !preg_match('/^[\d\s\+\-\/\(\)]+$/', $phone)) {
        $message = 'Ungültiges Telefonnummer-Format';
        $messageType = 'danger';
    } elseif (!validateInteger($roleId, 1)) {
        $message = 'Gültige Rolle erforderlich';
        $messageType = 'danger';
    } else {
        $pwCheck = validatePasswordStrength($password);
        if (!$pwCheck['valid']) {
            $message = $pwCheck['message'];
            $messageType = 'danger';
        } else {
            // Prüfen ob Benutzername oder E-Mail bereits existiert
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $message = 'Benutzername oder E-Mail bereits vergeben';
                $messageType = 'danger';
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Rolle-String aus rolle_id ermitteln
                    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
                    $stmt->execute([$roleId]);
                    $roleName = $stmt->fetchColumn();
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, email, first_name, last_name, phone, password, role, role_id, receive_maintenance_emails, require_2fa, must_change_password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $username, 
                        $email, 
                        $firstName, 
                        $lastName, 
                        $phone, 
                        $hashedPassword, 
                        $roleName, 
                        intval($roleId), 
                        $receiveMaintenanceEmails, 
                        $require2fa,
                        $forcePasswordChange
                    ]);
                    
                    logActivity('user_created', "Benutzer '{$username}' erstellt");
                    
                    $message = 'Benutzer erfolgreich erstellt!';
                    $messageType = 'success';
                    
                    // Benutzer neu laden
                    $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
                    
                } catch (PDOException $e) {
                    $message = 'Fehler beim Erstellen: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Benutzer löschen
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    if ($userId != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'Benutzer gelöscht';
        $messageType = 'success';
    } else {
        $message = 'Sie können sich nicht selbst löschen';
        $messageType = 'danger';
    }
}

// Alle Benutzer abrufen mit Rollennamen
$stmt = $pdo->query("
    SELECT u.*, r.display_name as role_display_name, r.role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung - RFID Marker System</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        .user-profile-placeholder {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            vertical-align: middle;
            color: white;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid #dee2e6;
        }
        
        .bulk-actions {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .bulk-actions.active {
            display: block;
        }
        
        .bulk-actions-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .import-export-section {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
    <script src="js/password-strength.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1>Benutzerverwaltung</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= e($message) ?>
            </div>
            <?php endif; ?>
            
            <div class="admin-section-container">
                <!-- Import/Export Section -->
                <div class="import-export-section">
                    <h3><i class="fas fa-file-import"></i> Import / Export</h3>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                        <form method="post" enctype="multipart/form-data" style="display: inline-flex; gap: 10px; align-items: center;">
                            <?= csrf_field() ?>
                            <input type="file" name="import_file" accept=".csv" required>
                            <button type="submit" name="import_users" class="btn btn-primary">
                                <i class="fas fa-upload"></i> CSV Importieren
                            </button>
                        </form>
                        
                        <a href="?export=csv" class="btn btn-success">
                            <i class="fas fa-download"></i> Als CSV Exportieren
                        </a>
                        
                        <small style="color: #666;">
                            CSV Format: username, email, first_name, last_name, phone, password, role_id
                        </small>
                    </div>
                </div>
                
                <div class="admin-section">
                    <h2>Neuen Benutzer erstellen</h2>
                    <form method="post" class="admin-form">
                        <?= csrf_field() ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Benutzername *</label>
                                <input type="text" id="username" name="username" required 
                                       pattern="^[a-zA-Z0-9_]{3,50}$"
                                       title="3-50 Zeichen, nur Buchstaben, Zahlen und Unterstriche">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">E-Mail *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Vorname</label>
                                <input type="text" id="first_name" name="first_name" placeholder="Max">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Nachname</label>
                                <input type="text" id="last_name" name="last_name" placeholder="Mustermann">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Telefonnummer</label>
                            <input type="tel" id="phone" name="phone" placeholder="+49 123 456789">
                            <small>Optional - für Benachrichtigungen</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Passwort *</label>
                                <input type="password" id="password" name="password" required>
                                <small>Mindestens 8 Zeichen, 1 Großbuchstabe, 1 Kleinbuchstabe, 1 Zahl</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="role_id">Rolle *</label>
                                <select id="role_id" name="role_id" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>"><?= e($role['display_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="receive_maintenance_emails" checked>
                                <span>Wartungs-E-Mails empfangen</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="require_2fa" id="require_2fa">
                                <span>
                                    <strong>Zwei-Faktor-Authentifizierung (2FA) verpflichtend</strong><br>
                                    <small>Benutzer muss 2FA aktivieren und bei jedem Login verwenden</small>
                                </span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="force_password_change" id="forcePasswordChange" checked>
                                <span>
                                    <strong><i class="fas fa-shield-lock"></i> Benutzer muss beim ersten Login das Passwort ändern</strong><br>
                                    <small>Empfohlen für neue Benutzer aus Sicherheitsgründen</small>
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" name="create_user" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Benutzer erstellen
                        </button>
                    </form>
                </div>
                
                <div class="admin-section">
                    <h2>Vorhandene Benutzer</h2>
                    
                    <!-- Bulk-Aktionen -->
                    <div class="bulk-actions" id="bulkActions">
                        <strong><span id="selectedCount">0</span> Benutzer ausgewählt</strong>
                        <div class="bulk-actions-buttons">
                            <form method="post" style="display: inline;" onsubmit="return confirm('2FA-Pflicht für ausgewählte Benutzer aktivieren?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="selected_users[]" id="bulkUsers2FA">
                                <button type="submit" name="bulk_require_2fa" class="btn btn-warning">
                                    <i class="fas fa-shield-alt"></i> 2FA verpflichtend machen
                                </button>
                            </form>
                            
                            <form method="post" style="display: inline;" onsubmit="return confirm('2FA-Pflicht für ausgewählte Benutzer entfernen?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="selected_users[]" id="bulkUsersDisable2FA">
                                <button type="submit" name="bulk_disable_2fa" class="btn btn-info">
                                    <i class="fas fa-shield-alt"></i> 2FA-Pflicht entfernen
                                </button>
                            </form>
                            
                            <form method="post" style="display: inline;" onsubmit="return confirm('Ausgewählte Benutzer wirklich löschen?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="selected_users[]" id="bulkUsersDelete">
                                <button type="submit" name="bulk_delete" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Ausgewählte löschen
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Benutzername</th>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Telefon</th>
                                <th>Rolle</th>
                                <th>2FA</th>
                                <th>Letzter Login</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <input type="checkbox" class="user-checkbox" value="<?= $user['id'] ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?= e($user['id']) ?></td>
                                <td>
                                    <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                                        <img src="<?= e($user['profile_image']) ?>?v=<?= time() ?>" 
                                             alt="<?= e($user['username']) ?>" 
                                             class="user-profile-img">
                                    <?php else: ?>
                                        <span class="user-profile-placeholder">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                    <strong><?= e($user['username']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($user['first_name'] || $user['last_name']): ?>
                                        <?= e(trim($user['first_name'] . ' ' . $user['last_name'])) ?>
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-style: italic;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($user['email']) ?></td>
                                <td>
                                    <?php if ($user['phone']): ?>
                                        <i class="fas fa-phone" style="color: #28a745;"></i>
                                        <?= e($user['phone']) ?>
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-style: italic;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'user' ? 'primary' : 'secondary') ?>">
                                        <?= e($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['require_2fa']): ?>
                                        <span class="badge badge-warning" title="2FA Pflicht">
                                            <i class="fas fa-exclamation-circle"></i> Pflicht
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($user['has_2fa_enabled']): ?>
                                        <span class="badge badge-success" title="2FA Aktiv">
                                            <i class="fas fa-check-circle"></i> Aktiv
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" title="2FA Inaktiv">
                                            <i class="fas fa-times-circle"></i> Inaktiv
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : '<span style="color: #6c757d;">Noch nie</span>' ?>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary" title="Bearbeiten">
                                        <i class="fas fa-edit"></i>Bearbeiten
                                    </a>
                                    
                                    <a href="user_2fa_settings.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info" title="2FA-Einstellungen">
                                        <i class="fas fa-shield-alt"></i>2FA
                                    </a>
                                    
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Benutzer <?= e($user['username']) ?> wirklich löschen?')"
                                        title="Löschen">
                                            <i class="fas fa-trash"></i>Löschen
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="info-box">
                <h3>Über Rollen</h3>
                <p>Jeder Benutzer benötigt eine Rolle. Rollen definieren, welche Aktionen ein Benutzer durchführen kann.</p>
                <p><a href="roles.php" class="btn btn-sm btn-primary">Zur Rollenverwaltung</a></p>
            </div>
        </div>
    </div>
    
    <script>
    // Checkbox-Funktionalität für Bulk-Operationen
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    function updateBulkActions() {
        const selectedUsers = Array.from(userCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        selectedCountSpan.textContent = selectedUsers.length;
        
        if (selectedUsers.length > 0) {
            bulkActions.classList.add('active');
            
            // Update hidden inputs mit ausgewählten IDs
            document.getElementById('bulkUsers2FA').value = selectedUsers.join(',');
            document.getElementById('bulkUsersDisable2FA').value = selectedUsers.join(',');
            document.getElementById('bulkUsersDelete').value = selectedUsers.join(',');
        } else {
            bulkActions.classList.remove('active');
        }
    }
    
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(cb => {
            cb.checked = this.checked;
        });
        updateBulkActions();
    });
    
    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>
