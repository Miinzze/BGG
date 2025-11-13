<?php
/**
 * force_password_change.php
 * Zwingt User dazu, ihr Passwort zu ändern
 */

require_once 'config.php';
require_once 'functions.php';

// User muss eingeloggt sein
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Hole User-Daten
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Wenn User kein Passwort ändern muss, zur Startseite
if ($user['must_change_password'] == 0 && !isset($_SESSION['must_change_password'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// CSRF Token generieren
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Passwort-Änderung verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Ungültiges CSRF-Token. Bitte versuchen Sie es erneut.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validierung
        if (empty($currentPassword)) {
            $error = 'Bitte geben Sie Ihr aktuelles Passwort ein.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Das aktuelle Passwort ist falsch.';
        } elseif (empty($newPassword)) {
            $error = 'Bitte geben Sie ein neues Passwort ein.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Die Passwörter stimmen nicht überein.';
        } elseif ($currentPassword === $newPassword) {
            $error = 'Das neue Passwort muss sich vom alten unterscheiden.';
        } elseif (isPasswordReused($_SESSION['user_id'], $newPassword, $pdo)) {
            // NEU: Prüfung auf Passwort-Wiederverwendung
            $error = 'Dieses Passwort wurde bereits verwendet. Bitte wählen Sie ein anderes Passwort.';
        } else {
            // Passwort-Stärke prüfen
            $pwCheck = validatePasswordStrength($newPassword);
            if (!$pwCheck['valid']) {
                $error = $pwCheck['message'];
            } else {
                // Passwort ändern
                try {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // NEU: Altes Passwort in Historie speichern
                    savePasswordHistory($_SESSION['user_id'], $user['password'], $pdo);
                    
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET password = ?, 
                            must_change_password = 0
                        WHERE id = ?
                    ");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    
                    // Session-Flag entfernen
                    unset($_SESSION['must_change_password']);
                    
                    // Log-Eintrag
                    logActivity('password_changed', 'Pflicht-Passwortänderung durchgeführt');
                    
                    // NEU: E-Mail-Benachrichtigung senden
                    sendSecurityNotification($_SESSION['user_id'], 'password_changed', $pdo, []);
                    
                    $success = 'Passwort erfolgreich geändert! Sie werden weitergeleitet...';
                    
                    // Nach 2 Sekunden zur Startseite
                    header('Refresh: 2; url=index.php');
                    
                } catch (PDOException $e) {
                    $error = 'Fehler beim Ändern des Passworts: ' . $e->getMessage();
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort ändern - Erforderlich</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="js/password-strength.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        }
        .password-change-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        .password-change-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .password-change-header i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .password-change-header h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .password-change-header p {
            color: #666;
            font-size: 0.95rem;
        }
        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-box.info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #1976d2;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-change {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .password-requirements ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin: 5px 0;
            color: #666;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .position-relative {
            position: relative;
        }
        /* Passwort-Stärke Anzeige Integration */
        #password-strength-meter {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="password-change-container">
        <div class="password-change-header">
            <i class="bi bi-shield-lock"></i>
            <h1>Passwort ändern erforderlich</h1>
            <p>Aus Sicherheitsgründen müssen Sie Ihr Passwort ändern, bevor Sie fortfahren können.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="alert-box info">
            <i class="bi bi-info-circle"></i> 
            <strong>Angemeldet als:</strong> <?php echo htmlspecialchars($user['username']); ?>
        </div>

        <form method="POST" id="passwordChangeForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="mb-3">
                <label for="current_password" class="form-label">Aktuelles Passwort *</label>
                <div class="position-relative">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="current_password" 
                        name="current_password" 
                        required
                        autocomplete="current-password"
                    >
                    <i class="bi bi-eye password-toggle" onclick="togglePassword('current_password')"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Neues Passwort *</label>
                <div class="position-relative">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="new_password" 
                        name="new_password" 
                        required
                        autocomplete="new-password"
                        minlength="8"
                    >
                    <i class="bi bi-eye password-toggle" onclick="togglePassword('new_password')"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Neues Passwort bestätigen *</label>
                <div class="position-relative">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        autocomplete="new-password"
                        minlength="8"
                    >
                    <i class="bi bi-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                </div>
            </div>

            <div class="password-requirements">
                <strong>Passwort-Anforderungen:</strong>
                <ul>
                    <li>Mindestens 8 Zeichen lang</li>
                    <li>Mindestens ein Großbuchstabe</li>
                    <li>Mindestens ein Kleinbuchstabe</li>
                    <li>Mindestens eine Zahl</li>
                    <li>Muss sich vom alten Passwort unterscheiden</li>
                    <li>Darf keines der letzten 5 Passwörter sein</li>
                </ul>
            </div>

            <button type="submit" class="btn btn-primary btn-change w-100 mt-4">
                <i class="bi bi-shield-check"></i> Passwort jetzt ändern
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-lock-fill"></i> Sichere Verbindung
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Passwort-Übereinstimmung prüfen
        document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Die Passwörter stimmen nicht überein!');
                return false;
            }
        });
    </script>
</body>
</html>