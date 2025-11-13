<?php
require_once 'config.php';

// Prüfe ob Benutzer eingeloggt ist
$isLoggedIn = isset($_SESSION['user_id']);

$error = null;
$marker = null;
$qr_code = null;
$nfc_chip = null;
$scan_method = null;

try {
    // Prüfe verschiedene URL-Parameter für QR-Codes
    if (isset($_GET['qr'])) {
        $qr_code = $_GET['qr'];
        $scan_method = 'qr';
    } elseif (isset($_GET['code'])) {
        $qr_code = $_GET['code'];
        $scan_method = 'qr';
    } elseif (isset($_GET['qr_code'])) {
        $qr_code = $_GET['qr_code'];
        $scan_method = 'qr';
    }
    
    // Prüfe verschiedene URL-Parameter für NFC
    if (isset($_GET['nfc'])) {
        $nfc_chip = $_GET['nfc'];
        $scan_method = 'nfc';
    } elseif (isset($_GET['nfc_chip'])) {
        $nfc_chip = $_GET['nfc_chip'];
        $scan_method = 'nfc';
    } elseif (isset($_GET['chip'])) {
        $nfc_chip = $_GET['chip'];
        $scan_method = 'nfc';
    }
    
    // Wenn weder QR noch NFC, prüfe ob Token übergeben wurde
    if (!$qr_code && !$nfc_chip && isset($_GET['token'])) {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u.username as created_by_name,
                   ms.name as maintenance_set_name
            FROM markers m
            LEFT JOIN users u ON m.created_by = u.id
            LEFT JOIN maintenance_sets ms ON m.maintenance_set_id = ms.id
            WHERE m.public_token = ? 
            AND m.deleted_at IS NULL
        ");
        $stmt->execute([$_GET['token']]);
        $marker = $stmt->fetch(PDO::FETCH_ASSOC);
        $scan_method = 'token';
    }
    
    if ($qr_code) {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u.username as created_by_name,
                   ms.name as maintenance_set_name
            FROM markers m
            LEFT JOIN users u ON m.created_by = u.id
            LEFT JOIN maintenance_sets ms ON m.maintenance_set_id = ms.id
            WHERE m.qr_code = ? 
            AND m.deleted_at IS NULL
        ");
        $stmt->execute([$qr_code]);
        $marker = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } elseif ($nfc_chip) {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u.username as created_by_name,
                   ms.name as maintenance_set_name
            FROM markers m
            LEFT JOIN users u ON m.created_by = u.id
            LEFT JOIN maintenance_sets ms ON m.maintenance_set_id = ms.id
            WHERE m.nfc_chip_id = ? 
            AND m.deleted_at IS NULL
        ");
        $stmt->execute([$nfc_chip]);
        $marker = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$marker && $scan_method != 'token') {
        throw new Exception('Kein QR-Code oder NFC-Chip-ID angegeben');
    }
    
    if (!$marker) {
        throw new Exception('Marker nicht gefunden oder wurde gelöscht');
    }
    
    // === AUTOMATISCHE AKTIVIERUNG beim ersten Scan ===
    if ($marker['is_activated'] == 0 && $scan_method != 'token') {
        $scanMethodLabel = ($scan_method == 'qr') ? 'QR' : 'NFC';
        
        // UPDATE: is_activated auf 1 setzen
        $updateStmt = $pdo->prepare("
            UPDATE markers 
            SET is_activated = 1,
                gps_captured_by = ?,
                gps_captured_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$scanMethodLabel, $marker['id']]);
        
        // Activity Log
        $action = 'qr_activated';
        $details = "{$scanMethodLabel}-Code '{$marker['qr_code']}' aktiviert beim ersten Scan";
        
        $logStmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, username, action, details, marker_id, ip_address, user_agent, created_at)
            VALUES (NULL, NULL, ?, ?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([
            $action,
            $details,
            $marker['id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
        
        $marker['is_activated'] = 1;
        $marker['gps_captured_by'] = $scanMethodLabel;
    }
    
    // === PRÜFE OB MARKER ZU AKTIVER MESSE GEHÖRT ===
    if ($scan_method === 'qr' || $scan_method === 'nfc') {
        $messeCheck = $pdo->prepare("
            SELECT mc.id as messe_id
            FROM messe_markers mm
            JOIN messe_config mc ON mm.messe_id = mc.id
            WHERE mm.marker_id = ? 
            AND mc.is_active = 1
            LIMIT 1
        ");
        $messeCheck->execute([$marker['id']]);
        $activeMesse = $messeCheck->fetch(PDO::FETCH_ASSOC);
        
        // Wenn Marker zu aktiver Messe gehört, zur Messe-Ansicht weiterleiten
        if ($activeMesse) {
            if ($scan_method === 'qr') {
                $redirectParam = $qr_code;
                $paramName = 'qr';
            } else {
                $redirectParam = $nfc_chip;
                $paramName = 'nfc';
            }
            header('Location: messe_view.php?' . $paramName . '=' . urlencode($redirectParam));
            exit;
        }
    }
    
    // Scan-Historie speichern
    if ($scan_method === 'qr') {
        $stmt = $pdo->prepare("
            INSERT INTO qr_scan_history (marker_id, qr_code, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $marker['id'],
            $marker['qr_code'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } elseif ($scan_method === 'nfc') {
        $stmt = $pdo->prepare("
            INSERT INTO nfc_scan_history (marker_id, nfc_chip_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $marker['id'],
            $nfc_chip,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    // Wartungshistorie abrufen
    $historyStmt = $pdo->prepare("
        SELECT mh.*, u.username as performed_by_name
        FROM maintenance_history mh
        LEFT JOIN users u ON mh.performed_by = u.id
        WHERE mh.marker_id = ?
        ORDER BY mh.maintenance_date DESC, mh.created_at DESC
        LIMIT 10
    ");
    $historyStmt->execute([$marker['id']]);
    $maintenanceHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Dokumente abrufen
    $docsStmt = $pdo->prepare("
        SELECT * FROM uploaded_files
        WHERE marker_id = ?
        ORDER BY uploaded_at DESC
    ");
    $docsStmt->execute([$marker['id']]);
    $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // DGUV/UVV/TÜV Inspektionen laden
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM inspection_schedules 
            WHERE marker_id = ? 
            ORDER BY 
                CASE 
                    WHEN next_inspection IS NULL THEN 1
                    WHEN next_inspection < CURDATE() THEN 0
                    ELSE 2
                END,
                next_inspection ASC
        ");
        $stmt->execute([$marker['id']]);
        $inspections = $stmt->fetchAll();
    } catch (Exception $e) {
        $inspections = [];
    }
    
    // Seriennummern bei Multi-Device
    $serialNumbers = [];
    if ($marker['is_multi_device']) {
        $stmt = $pdo->prepare("SELECT serial_number FROM marker_serial_numbers WHERE marker_id = ? ORDER BY id");
        $stmt->execute([$marker['id']]);
        $serialNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

$rentalStatus = getRentalStatusLabel($marker['rental_status'] ?? null);
$maintenanceStatus = getMaintenanceStatus($marker['next_maintenance'] ?? null);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $marker ? htmlspecialchars($marker['name']) : 'Marker nicht gefunden' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            line-height: 1.6;
            padding: 20px;
        }
        .main-container { max-width: 1200px; margin: 0 auto; }
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .page-header h1 { color: #2c3e50; font-size: 32px; margin-bottom: 10px; }
        .scan-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-card h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e1e8ed;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-item { display: flex; flex-direction: column; gap: 5px; }
        .info-item .label {
            font-size: 12px;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .info-item .value { font-size: 16px; color: #2c3e50; font-weight: 500; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-primary { background: #cce5ff; color: #004085; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        .fuel-display { display: flex; align-items: center; gap: 20px; }
        .fuel-bar {
            flex: 1;
            height: 40px;
            background: #ecf0f1;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }
        .fuel-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: width 0.5s ease;
        }
        .fuel-bar-fill.low { background: linear-gradient(90deg, #f39c12, #f1c40f); }
        .fuel-bar-fill.empty { background: linear-gradient(90deg, #c0392b, #e74c3c); }
        .gps-capture-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .gps-capture-banner i { font-size: 32px; margin-bottom: 10px; display: block; }
        .gps-success-banner {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }
        .gps-success-banner i { font-size: 32px; margin-bottom: 10px; display: block; }
        .error-card {
            background: white;
            border-radius: 12px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .error-icon { font-size: 64px; color: #e74c3c; margin-bottom: 20px; }
        .history-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .doc-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }
        .doc-item:hover { background: #e9ecef; transform: translateX(5px); }
        .doc-icon {
            font-size: 32px;
            color: #667eea;
            margin-right: 15px;
            width: 50px;
            text-align: center;
        }
        #viewMap {
            height: 400px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            margin-top: 15px;
        }
        .gps-coordinates {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            display: inline-block;
        }
        .inspection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .inspection-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .inspection-card.overdue { border-left-color: #dc3545; background: #fff5f5; }
        .inspection-card.due-soon { border-left-color: #ffc107; background: #fffbf0; }
        .inspection-card.ok { border-left-color: #28a745; }
        .inspection-type {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .inspection-date { font-size: 14px; color: #6c757d; margin: 5px 0; }
        .inspection-status {
            margin-top: 15px;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .serial-list { background: #f8f9fa; border-radius: 8px; padding: 15px; }
        .serial-list code {
            display: block;
            margin: 5px 0;
            padding: 8px;
            background: white;
            border: 1px solid #dee2e6;
        }
        
        /* Login/Edit Buttons */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-edit {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }
        
        /* Login Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: #95a5a6;
            cursor: pointer;
            background: none;
            border: none;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        .modal-close:hover { background: #f5f7fa; color: #2c3e50; }
        .modal-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .modal-header h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .modal-header p { color: #7f8c8d; font-size: 14px; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        .alert-success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }
        .remember-me input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        .remember-me label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                top: 10px;
                right: 10px;
            }
            .btn-action {
                padding: 10px 16px;
                font-size: 13px;
            }
            .modal-content {
                padding: 30px 20px;
                width: 95%;
            }
        }
    </style>
</head>
<body>

<!-- Action Buttons -->
<?php if ($marker): ?>
<div class="action-buttons">
    <?php if ($isLoggedIn): ?>
        <a href="edit_marker.php?id=<?= $marker['id'] ?>" class="btn-action btn-edit">
            <i class="fas fa-edit"></i> Marker bearbeiten
        </a>
        <a href="index.php" class="btn-action" style="background: #3498db; color: white;">
            <i class="fas fa-home"></i> Dashboard
        </a>
    <?php else: ?>
        <button onclick="openLoginModal()" class="btn-action btn-login">
            <i class="fas fa-sign-in-alt"></i> Anmelden
        </button>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeLoginModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <h2><i class="fas fa-lock"></i> Admin Login</h2>
            <p>Melden Sie sich an, um diesen Marker zu bearbeiten</p>
        </div>
        
        <div id="loginError" class="alert alert-danger" style="display: none;"></div>
        
        <form id="loginForm" method="POST" action="login.php">
            <input type="hidden" name="redirect" value="edit_marker.php?id=<?= $marker['id'] ?? '' ?>">
            <input type="hidden" name="login" value="1">
            
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Benutzername oder E-Mail
                </label>
                <input type="text" id="username" name="username" required 
                       placeholder="Ihr Benutzername">
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-key"></i> Passwort
                </label>
                <input type="password" id="password" name="password" required 
                       placeholder="Ihr Passwort">
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me" value="1">
                <label for="remember_me">Angemeldet bleiben</label>
            </div>
            
            <button type="submit" class="btn-submit" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Jetzt anmelden
            </button>
        </form>
    </div>
</div>

<script>
function openLoginModal() {
    document.getElementById('loginModal').classList.add('active');
    document.getElementById('username').focus();
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('active');
}

// Modal schließen bei Klick außerhalb
document.getElementById('loginModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLoginModal();
    }
});

// ESC-Taste zum Schließen
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLoginModal();
    }
});

// Form Submission mit Feedback
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Anmeldung läuft...';
});
</script>

<?php if ($error): ?>
    <div class="main-container">
        <div class="error-card">
            <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Fehler</h2>
            <p style="color: #6c757d; margin-top: 10px;"><?= htmlspecialchars($error) ?></p>
        </div>
    </div>
<?php else: ?>
    <div class="main-container">
        <div class="page-header">
            <h1>
                <i class="fas fa-<?= $scan_method == 'nfc' ? 'wifi' : 'qrcode' ?>"></i> 
                <?= htmlspecialchars($marker['name']) ?>
            </h1>
            <div class="scan-badge">
                <i class="fas fa-check-circle"></i>
                Gescannt via <?= strtoupper($scan_method) ?>
                <?php if ($scan_method == 'qr'): ?>
                    • QR-Code: <?= htmlspecialchars($marker['qr_code']) ?>
                <?php elseif ($scan_method == 'nfc'): ?>
                    • NFC-Chip: <?= htmlspecialchars($marker['nfc_chip_id']) ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="gpsCaptureBanner" class="gps-capture-banner" style="display: none;">
            <i class="fas fa-satellite-dish"></i>
            <strong>GPS-Position wird erfasst...</strong>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">
                Bitte erlaube den Zugriff auf deinen Standort für höchste Genauigkeit
            </p>
        </div>
        
        <div id="gpsSuccessBanner" class="gps-success-banner" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <strong>Marker erfolgreich aktiviert!</strong>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">
                GPS-Position wurde mit hoher Genauigkeit gespeichert
            </p>
        </div>
        
        <div class="info-card">
            <h2><i class="fas fa-info-circle"></i> Geräte-Informationen</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">
                        <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                            <i class="fas fa-wifi"></i> Marker-Typ
                        <?php else: ?>
                            <i class="fas fa-qrcode"></i> Marker-Typ
                        <?php endif; ?>
                    </span>
                    <span class="value">
                        <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                            <span class="badge badge-info"><i class="fas fa-wifi"></i> NFC-Chip</span>
                        <?php else: ?>
                            <span class="badge badge-primary"><i class="fas fa-qrcode"></i> QR-Code</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="label">
                        <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                            NFC-Chip-ID
                        <?php else: ?>
                            QR-Code
                        <?php endif; ?>
                    </span>
                    <span class="value">
                        <?php if ($marker['marker_type'] === 'nfc_chip'): ?>
                            <code><?= htmlspecialchars($marker['nfc_chip_id']) ?></code>
                            <br><small style="color: #95a5a6;">Backup QR: <?= htmlspecialchars($marker['qr_code']) ?></small>
                        <?php else: ?>
                            <code><?= htmlspecialchars($marker['qr_code']) ?></code>
                        <?php endif; ?>
                    </span>
                </div>
                
                <?php if ($marker['category']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-tag"></i> Kategorie</span>
                    <span class="value"><?= htmlspecialchars($marker['category']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['serial_number']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-barcode"></i> Seriennummer</span>
                    <span class="value"><code><?= htmlspecialchars($marker['serial_number']) ?></code></span>
                </div>
                <?php endif; ?>
                
                <?php if (!$marker['is_storage'] && !$marker['is_multi_device'] && $marker['rental_status']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-info-circle"></i> Status</span>
                    <span class="value">
                        <span class="badge badge-<?= $rentalStatus['class'] ?>"><?= $rentalStatus['label'] ?></span>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['operating_hours'] > 0): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-clock"></i> Betriebsstunden</span>
                    <span class="value"><strong><?= number_format($marker['operating_hours'], 2) ?> h</strong></span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['is_customer_device'] && $marker['customer_name']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-user"></i> Kunde</span>
                    <span class="value"><?= htmlspecialchars($marker['customer_name']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['order_number']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-receipt"></i> Auftragsnr.</span>
                    <span class="value"><code><?= htmlspecialchars($marker['order_number']) ?></code></span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['is_activated']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-check-circle"></i> Aktivierungsstatus</span>
                    <span class="value">
                        <span class="badge badge-success"><i class="fas fa-check"></i> Aktiviert</span>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($marker['is_multi_device'] && !empty($serialNumbers)): ?>
            <div style="margin-top: 25px;">
                <h3 style="font-size: 18px; margin-bottom: 15px;"><i class="fas fa-list"></i> Seriennummern</h3>
                <div class="serial-list">
                    <?php foreach ($serialNumbers as $serial): ?>
                        <code><?= htmlspecialchars($serial) ?></code>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!$marker['is_storage'] && !$marker['is_multi_device'] && !$marker['is_customer_device']): ?>
            <div style="margin-top: 25px;">
                <h3 style="font-size: 18px; margin-bottom: 15px;"><i class="fas fa-gas-pump"></i> Kraftstofffüllstand</h3>
                <div class="fuel-display">
                    <div class="fuel-bar">
                        <?php 
                        $fuelPercent = intval($marker['fuel_level'] ?? 0);
                        if ($marker['fuel_unit'] === 'liter' && $marker['fuel_capacity']) {
                            $fuelPercent = min(100, round(($marker['fuel_level'] / $marker['fuel_capacity']) * 100));
                        }
                        $fuelClass = $fuelPercent > 50 ? '' : ($fuelPercent > 20 ? 'low' : 'empty');
                        ?>
                        <div class="fuel-bar-fill <?= $fuelClass ?>" style="width: <?= $fuelPercent ?>%">
                            <?php if ($fuelPercent > 15): ?><?= $fuelPercent ?>%<?php endif; ?>
                        </div>
                    </div>
                    <div style="min-width: 100px; text-align: right;">
                        <?php if ($marker['fuel_unit'] === 'liter'): ?>
                            <strong style="font-size: 18px;"><?= number_format($marker['fuel_level'], 1) ?> L</strong>
                            <?php if ($marker['fuel_capacity']): ?>
                                <br><small>von <?= number_format($marker['fuel_capacity'], 1) ?> L</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <strong style="font-size: 18px;"><?= $fuelPercent ?>%</strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($marker['latitude'] && $marker['longitude']): ?>
        <div class="info-card">
            <h2><i class="fas fa-map-marker-alt"></i> GPS-Position</h2>
            <div class="gps-coordinates" style="margin: 10px 0;">
                <i class="fas fa-map-pin"></i> 
                <?= number_format($marker['latitude'], 6) ?>, <?= number_format($marker['longitude'], 6) ?>
            </div>
            <?php if ($marker['gps_captured_at']): ?>
                <div style="margin-top: 10px; color: #6c757d;">
                    <i class="fas fa-clock"></i> 
                    Erfasst am <?= date('d.m.Y H:i', strtotime($marker['gps_captured_at'])) ?> Uhr
                    <?php if ($marker['gps_captured_by']): ?>
                        via <?= htmlspecialchars($marker['gps_captured_by']) ?>
                    <?php endif; ?>
                    <?php if ($marker['gps_accuracy']): ?>
                        • Genauigkeit: ±<?= number_format($marker['gps_accuracy'], 1) ?>m
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div id="viewMap"></div>
        </div>
        <?php endif; ?>
        
        <?php if (!$marker['is_storage'] && !$marker['is_multi_device'] && !$marker['is_customer_device']): ?>
        <?php if ($marker['last_maintenance'] || $marker['next_maintenance']): ?>
        <div class="info-card">
            <h2><i class="fas fa-wrench"></i> Wartungsinformationen</h2>
            <div class="info-grid">
                <?php if ($marker['last_maintenance']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-check"></i> Letzte Wartung</span>
                    <span class="value"><?= date('d.m.Y', strtotime($marker['last_maintenance'])) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['next_maintenance']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-calendar-check"></i> Nächste Wartung</span>
                    <span class="value">
                        <?= date('d.m.Y', strtotime($marker['next_maintenance'])) ?>
                        <?php
                        $days_until = (strtotime($marker['next_maintenance']) - time()) / (60 * 60 * 24);
                        if ($days_until < 0) {
                            echo ' <span class="badge badge-danger">Überfällig</span>';
                        } elseif ($days_until < 7) {
                            echo ' <span class="badge badge-warning">Bald fällig</span>';
                        }
                        ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['maintenance_set_name']): ?>
                <div class="info-item">
                    <span class="label"><i class="fas fa-list-check"></i> Wartungssatz</span>
                    <span class="value"><?= htmlspecialchars($marker['maintenance_set_name']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!$marker['is_storage'] && !empty($inspections)): ?>
        <div class="info-card">
            <h2><i class="fas fa-clipboard-check"></i> Prüfungen (DGUV / UVV / TÜV)</h2>
            <div class="inspection-grid">
                <?php foreach ($inspections as $inspection): 
                    $daysUntil = $inspection['next_inspection'] ? (strtotime($inspection['next_inspection']) - time()) / (60 * 60 * 24) : 999;
                    $statusClass = 'ok';
                    $statusText = 'Aktuell';
                    $statusBadge = 'success';
                    if ($daysUntil < 0) {
                        $statusClass = 'overdue';
                        $statusText = 'ÜBERFÄLLIG!';
                        $statusBadge = 'danger';
                    } elseif ($daysUntil <= 30) {
                        $statusClass = 'due-soon';
                        $statusText = 'Bald fällig';
                        $statusBadge = 'warning';
                    }
                ?>
                <div class="inspection-card <?= $statusClass ?>">
                    <div class="inspection-type">
                        <i class="fas fa-certificate"></i>
                        <?= htmlspecialchars($inspection['inspection_type']) ?>
                    </div>
                    <?php if ($inspection['last_inspection']): ?>
                    <div class="inspection-date">
                        <i class="fas fa-check"></i> Letzte Prüfung: 
                        <strong><?= date('d.m.Y', strtotime($inspection['last_inspection'])) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($inspection['next_inspection']): ?>
                    <div class="inspection-date">
                        <i class="fas fa-calendar"></i> Nächste Prüfung: 
                        <strong><?= date('d.m.Y', strtotime($inspection['next_inspection'])) ?></strong>
                        <?php if ($daysUntil < 999): ?>
                            (<?= $daysUntil < 0 ? 'vor ' . abs(round($daysUntil)) : 'in ' . round($daysUntil) ?> Tagen)
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($inspection['inspection_authority']): ?>
                    <div class="inspection-date">
                        <i class="fas fa-building"></i> Prüfstelle: 
                        <?= htmlspecialchars($inspection['inspection_authority']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="inspection-status badge-<?= $statusBadge ?>"><?= $statusText ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Wartung -->
        <?php if (!$marker['is_storage'] && !$marker['is_multi_device']): ?>
        <div class="info-card">
            <h2><i class="fas fa-wrench"></i> Wartung</h2>
            <div class="info-grid">
                <?php if ($marker['maintenance_interval_months']): ?>
                <div class="info-item">
                    <span class="label">Wartungsintervall</span>
                    <span class="value"><?= $marker['maintenance_interval_months'] ?> Monate</span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['last_maintenance']): ?>
                <div class="info-item">
                    <span class="label">Letzte Wartung</span>
                    <span class="value"><?= date('d.m.Y', strtotime($marker['last_maintenance'])) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['next_maintenance']): ?>
                <div class="info-item">
                    <span class="label">Nächste Wartung</span>
                    <span class="value">
                        <?= date('d.m.Y', strtotime($marker['next_maintenance'])) ?>
                        <span class="badge badge-<?= $maintenanceStatus['class'] ?>">
                            <?= $maintenanceStatus['label'] ?>
                        </span>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($marker['maintenance_set_name']): ?>
                <div class="info-item">
                    <span class="label">Wartungsset</span>
                    <span class="value"><?= htmlspecialchars($marker['maintenance_set_name']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($maintenanceHistory)): ?>
        <div class="info-card">
            <h2><i class="fas fa-history"></i> Wartungshistorie</h2>
            <?php foreach ($maintenanceHistory as $history): ?>
            <div class="history-item">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <strong style="font-size: 16px;"><?= htmlspecialchars($history['description'] ?? 'Wartung') ?></strong>
                        <br>
                        <small style="color: #6c757d;">
                            <i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($history['maintenance_date'])) ?>
                            <?php if ($history['performed_by_name']): ?>
                                • <?= htmlspecialchars($history['performed_by_name']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <?php if ($history['notes']): ?>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                    <small><?= nl2br(htmlspecialchars($history['notes'])) ?></small>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($documents)): ?>
        <div class="info-card">
            <h2><i class="fas fa-file-alt"></i> Dokumente</h2>
            <?php foreach ($documents as $doc): ?>
            <a href="download_document.php?id=<?= $doc['id'] ?>" class="doc-item">
                <div class="doc-icon">
                    <?php
                    $icon = 'file-alt';
                    if (strpos($doc['mime_type'], 'pdf') !== false) $icon = 'file-pdf';
                    elseif (strpos($doc['mime_type'], 'image') !== false) $icon = 'file-image';
                    elseif (strpos($doc['mime_type'], 'word') !== false) $icon = 'file-word';
                    elseif (strpos($doc['mime_type'], 'excel') !== false || strpos($doc['mime_type'], 'spreadsheet') !== false) $icon = 'file-excel';
                    ?>
                    <i class="fas fa-<?= $icon ?>"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 15px;"><?= htmlspecialchars($doc['document_name']) ?></div>
                    <small style="color: #6c757d;">
                        <?= number_format($doc['file_size'] / 1024, 2) ?> KB • 
                        <?= date('d.m.Y', strtotime($doc['uploaded_at'])) ?>
                    </small>
                </div>
                <i class="fas fa-download" style="color: #667eea; font-size: 20px;"></i>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; color: #95a5a6; margin-top: 30px; padding: 20px;">
            <small>
                <i class="fas fa-info-circle"></i> 
                Gescannt am <?= date('d.m.Y \u\m H:i') ?> Uhr
                <?php if ($marker['created_at']): ?>
                    • Erstellt am <?= date('d.m.Y', strtotime($marker['created_at'])) ?>
                <?php endif; ?>
                <?php if ($marker['created_by_name']): ?>
                    von <?= htmlspecialchars($marker['created_by_name']) ?>
                <?php endif; ?>
            </small>
        </div>
    </div>
<?php endif; ?>

<?php
// Ich erstelle nur den Script-Teil der am Ende hinzugefügt werden muss
// Dieser Teil ersetzt die Zeilen ab <script> bis </html>
?>

<script>
// === KARTEN-INITIALISIERUNG ===
<?php if ($marker && $marker['latitude'] && $marker['longitude']): ?>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🗺️ Initialisiere Karte...');
    if (typeof L !== 'undefined') {
        var map = L.map('viewMap').setView([<?= $marker['latitude'] ?>, <?= $marker['longitude'] ?>], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        L.marker([<?= $marker['latitude'] ?>, <?= $marker['longitude'] ?>])
            .addTo(map)
            .bindPopup('<strong><?= addslashes($marker['name']) ?></strong>')
            .openPopup();
        console.log('✅ Karte initialisiert');
    } else {
        console.error('❌ Leaflet nicht geladen');
    }
});
<?php endif; ?>

// === GPS-ERFASSUNG - BEI JEDEM SCAN AUTOMATISCH ===
<?php if ($marker && $scan_method != 'token'): ?>
// GPS-Erfassung läuft nur einmal beim Laden der Seite
let gpsAlreadyStarted = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 GPS-Erfassung wird gestartet...');
    const banner = document.getElementById('gpsCaptureBanner');
    const successBanner = document.getElementById('gpsSuccessBanner');
    
    // Nur einmal starten
    if (!gpsAlreadyStarted && banner) {
        gpsAlreadyStarted = true;
        banner.style.display = 'block';
        startGPSCapture();
    }
});

function startGPSCapture() {
    console.log('📍 startGPSCapture() gestartet');
    
    const banner = document.getElementById('gpsCaptureBanner');
    const successBanner = document.getElementById('gpsSuccessBanner');
    
    if (!banner) {
        console.error('❌ Banner Element nicht gefunden');
        return;
    }
    
    if (!('geolocation' in navigator)) {
        console.error('❌ Geolocation nicht verfügbar');
        banner.innerHTML = '<div style="text-align: center;"><i class="fas fa-exclamation-triangle"></i> <strong>GPS nicht verfügbar</strong><p style="margin: 10px 0 0 0;">Dein Browser unterstützt keine Standortbestimmung</p></div>';
        banner.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
        return;
    }
    
    console.log('✅ Geolocation verfügbar');
    
    // Erkenne Plattform
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isAndroid = /Android/.test(navigator.userAgent);
    console.log('📱 Plattform - iOS:', isIOS, 'Android:', isAndroid);
    
    // GPS-Einstellungen
    let bestAccuracy = Infinity;
    let samples = [];
    const maxSamples = 20;
    const minSamples = 5;
    const maxTime = 45000;
    const targetAccuracy = 15;
    const maxAccuracyFilter = 100;
    const startTime = Date.now();
    let firstPositionReceived = false;
    let isCompleted = false;
    
    // Initialer Banner
    banner.innerHTML = `
        <div style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 15px;">
                <i class="fas fa-satellite-dish fa-spin"></i>
            </div>
            <strong style="font-size: 20px;">GPS-Position wird erfasst...</strong>
            <p style="margin-top: 15px; font-size: 14px; opacity: 0.9;">
                <i class="fas fa-info-circle"></i> Bitte erlaube den Standortzugriff
            </p>
        </div>
    `;
    
    // Timeout wenn kein Signal
    const permissionTimeout = setTimeout(() => {
        if (!firstPositionReceived && !isCompleted) {
            console.warn('⚠️ Kein GPS-Signal nach 15 Sekunden');
            banner.innerHTML = `
                <div style="text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 15px; color: #f39c12;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <strong style="font-size: 20px;">Kein GPS-Signal</strong>
                    <p style="margin-top: 15px; font-size: 14px;">Bitte überprüfe:</p>
                    <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 8px; text-align: left; font-size: 13px;">
                        ${isIOS ? '📱 <strong>iPhone:</strong> Einstellungen → Datenschutz → Ortungsdienste → Safari' : ''}
                        ${isAndroid ? '🤖 <strong>Android:</strong> Einstellungen → Standort → Browser → Berechtigung' : ''}
                        ${!isIOS && !isAndroid ? '💻 <strong>Desktop:</strong> Schloss-Symbol in Adressleiste → Standort erlauben' : ''}
                        <br><br>✓ Gehe ins Freie für besseren Empfang
                    </div>
                    <button onclick="location.reload()" style="margin-top: 20px; padding: 12px 24px; background: white; color: #667eea; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-redo"></i> Neu laden
                    </button>
                </div>
            `;
        }
    }, 15000);
    
    // Banner nach erstem GPS-Signal aktualisieren
    function updateBanner() {
        clearTimeout(permissionTimeout);
        banner.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">
                    <i class="fas fa-satellite-dish fa-spin"></i>
                </div>
                <strong style="font-size: 20px;">GPS-Position wird erfasst...</strong>
                <div style="margin-top: 20px; background: rgba(255,255,255,0.2); border-radius: 10px; padding: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <div style="font-size: 12px; opacity: 0.9;">Messungen</div>
                            <div style="font-size: 24px; font-weight: bold;"><span id="sampleCount">0</span>/${maxSamples}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; opacity: 0.9;">Beste Genauigkeit</div>
                            <div style="font-size: 24px; font-weight: bold;" id="bestAccuracy">--</div>
                        </div>
                    </div>
                    <div style="margin-top: 15px; background: rgba(255,255,255,0.3); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div id="progressBar" style="background: #fff; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // GPS-Sample erfassen
    function captureGPSSample() {
        if (isCompleted) return;
        
        console.log('📍 GPS-Sample #' + (samples.length + 1) + '...');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                if (isCompleted) return;
                
                console.log('✅ GPS:', position.coords.latitude, position.coords.longitude, '±' + position.coords.accuracy + 'm');
                
                // Erster Empfang
                if (!firstPositionReceived) {
                    firstPositionReceived = true;
                    updateBanner();
                }
                
                const accuracy = position.coords.accuracy;
                const elapsed = Date.now() - startTime;
                
                // Sample speichern wenn Qualität OK
                if (accuracy <= maxAccuracyFilter) {
                    samples.push({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: accuracy,
                        altitude: position.coords.altitude,
                        timestamp: Date.now()
                    });
                    
                    if (accuracy < bestAccuracy) {
                        bestAccuracy = accuracy;
                    }
                }
                
                // UI aktualisieren
                if (document.getElementById('sampleCount')) {
                    document.getElementById('sampleCount').textContent = samples.length;
                    document.getElementById('bestAccuracy').textContent = bestAccuracy.toFixed(1) + ' m';
                    const progress = Math.min(100, (samples.length / maxSamples) * 100);
                    document.getElementById('progressBar').style.width = progress + '%';
                }
                
                // Fertig?
                const hasEnoughSamples = samples.length >= maxSamples;
                const hasGoodAccuracy = samples.length >= minSamples && accuracy < targetAccuracy;
                const timeExpired = elapsed >= maxTime;
                const hasMinimumData = samples.length >= minSamples && elapsed >= 15000;
                
                if (hasEnoughSamples || hasGoodAccuracy || timeExpired || hasMinimumData) {
                    isCompleted = true;
                    clearTimeout(permissionTimeout);
                    console.log('🏁 GPS-Erfassung abgeschlossen mit ' + samples.length + ' Messungen');
                    
                    if (samples.length < minSamples) {
                        banner.innerHTML = '<div style="text-align: center;"><i class="fas fa-exclamation-triangle"></i> <strong>Zu wenige Messungen</strong><p style="margin: 10px 0 0 0;">Bitte gehe ins Freie</p></div>';
                        banner.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
                        return;
                    }
                    
                    // Gewichteter Durchschnitt
                    let totalWeightedLat = 0;
                    let totalWeightedLng = 0;
                    let totalWeight = 0;
                    
                    samples.forEach(sample => {
                        const weight = 1 / (sample.accuracy * sample.accuracy);
                        totalWeightedLat += sample.lat * weight;
                        totalWeightedLng += sample.lng * weight;
                        totalWeight += weight;
                    });
                    
                    const finalLat = totalWeightedLat / totalWeight;
                    const finalLng = totalWeightedLng / totalWeight;
                    
                    // Standardabweichung
                    let sumSquaredDiff = 0;
                    samples.forEach(sample => {
                        const latDiff = sample.lat - finalLat;
                        const lngDiff = sample.lng - finalLng;
                        const distance = Math.sqrt(latDiff * latDiff + lngDiff * lngDiff) * 111000;
                        sumSquaredDiff += distance * distance;
                    });
                    const stdDev = Math.sqrt(sumSquaredDiff / samples.length);
                    
                    const improvementFactor = 1 / Math.sqrt(samples.length);
                    const finalAccuracy = Math.max(bestAccuracy * improvementFactor, stdDev, 5);
                    
                    console.log('💾 Speichere:', finalLat, finalLng, '±' + finalAccuracy.toFixed(1) + 'm');
                    
                    // Speichern
                    fetch('api/save_gps.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            marker_id: <?= $marker['id'] ?>,
                            latitude: finalLat,
                            longitude: finalLng,
                            accuracy: finalAccuracy,
                            altitude: samples[0].altitude,
                            samples: samples.length,
                            bestAccuracy: bestAccuracy,
                            method: 'weighted_average',
                            scan_method: '<?= strtoupper($scan_method) ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('✅ GPS gespeichert:', data);
                        if (data.success) {
                            banner.style.display = 'none';
                            if (successBanner) {
                                let qualityBadge = '';
                                if (finalAccuracy <= 10) qualityBadge = '⭐ Sehr gut';
                                else if (finalAccuracy <= 20) qualityBadge = '✓ Gut';
                                else if (finalAccuracy <= 50) qualityBadge = '✓ Akzeptabel';
                                else qualityBadge = '⚠ Ausreichend';
                                
                                successBanner.innerHTML = '<i class="fas fa-check-circle"></i> <strong>GPS gespeichert!</strong><p style="margin: 10px 0 0 0;">' + qualityBadge + ' - ±' + finalAccuracy.toFixed(1) + 'm</p><p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.9;">Beim nächsten Scan wird die Position erneut aktualisiert</p>';
                                successBanner.style.display = 'block';
                                // KEIN Reload - Seite bleibt offen, Erfassung ist abgeschlossen
                            }
                        }
                    })
                    .catch(error => {
                        console.error('❌ Fehler:', error);
                        banner.innerHTML = '<div style="text-align: center;"><i class="fas fa-exclamation-triangle"></i> <strong>Fehler beim Speichern</strong><p style="margin: 10px 0 0 0;">Bitte scanne erneut</p></div>';
                        banner.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
                    });
                    
                    console.log('✅ GPS-Erfassung abgeschlossen - bereit für nächsten Scan');
                    return;
                }
                
                // Nächstes Sample
                if (samples.length < maxSamples && !isCompleted) {
                    setTimeout(captureGPSSample, 1500);
                }
            },
            function(error) {
                console.error('❌ GPS-Fehler:', error);
                clearTimeout(permissionTimeout);
                isCompleted = true;
                
                let errorMsg = 'GPS-Zugriff verweigert';
                let helpText = '';
                
                if (error.code === error.PERMISSION_DENIED) {
                    errorMsg = 'GPS-Zugriff verweigert';
                    helpText = `
                        <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 8px; text-align: left; font-size: 13px;">
                            ${isIOS ? '📱 <strong>iPhone:</strong> Einstellungen → Datenschutz → Ortungsdienste → Safari<br>Safari: "aA" → Website-Einstellungen → Standort' : ''}
                            ${isAndroid ? '🤖 <strong>Android:</strong> Einstellungen → Standort → Browser-Berechtigungen' : ''}
                            ${!isIOS && !isAndroid ? '💻 Klicke auf das Schloss-Symbol in der Adressleiste' : ''}
                        </div>
                        <button onclick="location.reload()" style="margin-top: 15px; padding: 12px 24px; background: white; color: #e74c3c; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-redo"></i> Neu laden
                        </button>
                    `;
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    errorMsg = 'Standort nicht verfügbar';
                    helpText = '<p style="margin-top: 10px; font-size: 14px;">Gehe ins Freie mit freier Sicht zum Himmel</p>';
                } else if (error.code === error.TIMEOUT) {
                    errorMsg = 'Zeitüberschreitung';
                    helpText = '<p style="margin-top: 10px; font-size: 14px;">GPS-Signal zu schwach. Gehe ins Freie.</p>';
                }
                
                banner.innerHTML = `
                    <div style="text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <strong style="font-size: 20px;">${errorMsg}</strong>
                        ${helpText}
                    </div>
                `;
                banner.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }
    
    // Starte GPS-Erfassung
    captureGPSSample();
}
<?php endif; ?>
</script>

</body>
</html>