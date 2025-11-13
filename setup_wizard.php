<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// Prüfen ob Setup-Wizard erforderlich
$stmt = $pdo->prepare("SELECT * FROM user_onboarding WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$onboarding = $stmt->fetch();

if ($onboarding && $onboarding['setup_wizard_completed']) {
    header('Location: index.php');
    exit;
}

// Setup-Wizard abschließen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_wizard'])) {
    validateCSRF();
    
    $stmt = $pdo->prepare("
        INSERT INTO user_onboarding (user_id, setup_wizard_completed, setup_completed_at)
        VALUES (?, 1, NOW())
        ON DUPLICATE KEY UPDATE setup_wizard_completed = 1, setup_completed_at = NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Checkliste initialisieren
    $checklistItems = ['profile_completed', 'password_changed', '2fa_enabled', 'first_marker_created', 'tour_completed'];
    
    foreach ($checklistItems as $item) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_checklist (user_id, checklist_item) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $item]);
    }
    
    logActivity('setup_wizard_completed', "Setup-Wizard abgeschlossen");
    
    header('Location: index.php?show_tour=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willkommen - Setup-Wizard</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .wizard-container { background: white; border-radius: 10px; max-width: 800px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .wizard-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; }
        .wizard-content { padding: 40px; }
        .wizard-step { display: none; animation: fadeIn 0.5s; }
        .wizard-step.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; padding: 0 20px; }
        .step-indicator-item { flex: 1; text-align: center; position: relative; padding: 10px; }
        .step-indicator-item:not(:last-child)::after { content: ''; position: absolute; top: 20px; right: -50%; width: 100%; height: 2px; background: #dee2e6; z-index: -1; }
        .step-indicator-item.completed::after { background: #28a745; }
        .step-number { width: 40px; height: 40px; border-radius: 50%; background: #dee2e6; color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 5px; }
        .step-indicator-item.active .step-number { background: #667eea; }
        .step-indicator-item.completed .step-number { background: #28a745; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .feature-card { background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center; border: 2px solid transparent; transition: all 0.3s; }
        .feature-card:hover { border-color: #667eea; transform: translateY(-5px); }
        .feature-icon { font-size: 40px; color: #667eea; margin-bottom: 10px; }
        .wizard-buttons { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1><i class="fas fa-rocket"></i> Willkommen, <?= e($_SESSION['username']) ?>!</h1>
            <p>Lassen Sie uns gemeinsam Ihr Konto einrichten</p>
        </div>
        
        <div class="wizard-content">
            <div class="step-indicator">
                <div class="step-indicator-item active" data-step="1"><div class="step-number">1</div><small>Willkommen</small></div>
                <div class="step-indicator-item" data-step="2"><div class="step-number">2</div><small>Funktionen</small></div>
                <div class="step-indicator-item" data-step="3"><div class="step-number">3</div><small>Sicherheit</small></div>
                <div class="step-indicator-item" data-step="4"><div class="step-number">4</div><small>Los geht's!</small></div>
            </div>
            
            <div class="wizard-step active" data-step="1">
                <h2><i class="fas fa-hand-wave"></i> Herzlich willkommen!</h2>
                <p>Schön, dass Sie hier sind! Wir helfen Ihnen dabei, Ihr Konto in wenigen Schritten einzurichten.</p>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Was erwartet Sie:</strong><br>• Überblick über die wichtigsten Funktionen<br>• Sicherheitseinstellungen<br>• Interaktive Tour durchs System<br>• Ihre persönliche Checkliste</div>
                <p><strong>Ihre Rolle:</strong> <span class="badge badge-primary"><?= e($_SESSION['role']) ?></span></p>
            </div>
            
            <div class="wizard-step" data-step="2">
                <h2><i class="fas fa-star"></i> Das können Sie mit dem System machen</h2>
                <div class="feature-grid">
                    <div class="feature-card"><div class="feature-icon"><i class="fas fa-map-marker-alt"></i></div><h4>Marker verwalten</h4><p>Objekte und Standorte digital erfassen</p></div>
                    <div class="feature-card"><div class="feature-icon"><i class="fas fa-tasks"></i></div><h4>Prüfungen</h4><p>DGUV, UVV und TÜV-Prüfungen planen</p></div>
                    <div class="feature-card"><div class="feature-icon"><i class="fas fa-tools"></i></div><h4>Wartung</h4><p>Wartungsintervalle tracken</p></div>
                    <div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-line"></i></div><h4>Reports</h4><p>Detaillierte Auswertungen</p></div>
                    <div class="feature-card"><div class="feature-icon"><i class="fas fa-qrcode"></i></div><h4>QR-Codes</h4><p>QR-Codes generieren und scannen</p></div>
                    <div class="feature-card"><div class="feature-icon"><i class="fas fa-map"></i></div><h4>Geofencing</h4><p>Standorte auf Karten verwalten</p></div>
                </div>
            </div>
            
            <div class="wizard-step" data-step="3">
                <h2><i class="fas fa-shield-alt"></i> Sicherheit ist wichtig!</h2>
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Wichtige Sicherheitshinweise:</strong></div>
                <div style="margin: 20px 0;">
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 5px; margin-bottom: 15px;">
                        <h4><i class="fas fa-lock"></i> Starkes Passwort</h4>
                        <p>Verwenden Sie ein sicheres Passwort mit mindestens 8 Zeichen.</p>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 5px; margin-bottom: 15px;">
                        <h4><i class="fas fa-mobile-alt"></i> Zwei-Faktor-Authentifizierung (2FA)</h4>
                        <p>Erhöhen Sie die Sicherheit mit 2FA.</p>
                    </div>
                </div>
            </div>
            
            <div class="wizard-step" data-step="4">
                <h2><i class="fas fa-flag-checkered"></i> Alles bereit!</h2>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>Sie sind bereit, das System zu nutzen!</strong></div>
                <h3>📋 Ihre Checkliste für die ersten Schritte:</h3>
                <div style="margin: 20px 0;">
                    <div style="padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; background: #f8f9fa;">☐ Profil vervollständigen</div>
                    <div style="padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; background: #f8f9fa;">☐ Passwort geändert</div>
                    <div style="padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; background: #f8f9fa;">☐ 2FA eingerichtet</div>
                    <div style="padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; background: #f8f9fa;">☐ Ersten Marker erstellt</div>
                    <div style="padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; background: #f8f9fa;">☐ Interaktive Tour abgeschlossen</div>
                </div>
                <form method="post">
                    <?= csrf_field() ?>
                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" name="complete_wizard" class="btn btn-success btn-lg">
                            <i class="fas fa-rocket"></i> Setup abschließen und Tour starten
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="wizard-buttons">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;"><i class="fas fa-arrow-left"></i> Zurück</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Weiter <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
    
    <script>
    let currentStep = 1;
    const totalSteps = 4;
    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-indicator-item').forEach(el => el.classList.remove('active'));
        document.querySelector(`.wizard-step[data-step="${step}"]`).classList.add('active');
        document.querySelector(`.step-indicator-item[data-step="${step}"]`).classList.add('active');
        for (let i = 1; i < step; i++) {
            document.querySelector(`.step-indicator-item[data-step="${i}"]`).classList.add('completed');
        }
        document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
        document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'inline-block';
        currentStep = step;
    }
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (currentStep < totalSteps) showStep(currentStep + 1);
    });
    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentStep > 1) showStep(currentStep - 1);
    });
    </script>
</body>
</html>
