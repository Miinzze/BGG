<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'extended_functions.php';
requireLogin();
requirePermission('signature_manage');

$message = '';
$messageType = '';

// Signatur speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_signature'])) {
    validateCSRF();
    
    $signatureData = $_POST['signature_data'] ?? '';
    
    if (empty($signatureData)) {
        $message = 'Bitte erstellen Sie zunächst eine Signatur';
        $messageType = 'warning';
    } else {
        if (saveUserSignature($_SESSION['user_id'], $signatureData)) {
            $message = 'Signatur erfolgreich gespeichert!';
            $messageType = 'success';
        } else {
            $message = 'Fehler beim Speichern der Signatur';
            $messageType = 'danger';
        }
    }
}

// Signatur löschen
if (isset($_GET['delete']) && $_GET['delete'] === 'signature') {
    validateCSRF();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM user_signatures WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $message = 'Signatur gelöscht';
        $messageType = 'success';
        logExtendedActivity('signature_deleted', 'Benutzer-Signatur gelöscht');
    } catch (Exception $e) {
        $message = 'Fehler beim Löschen';
        $messageType = 'danger';
    }
}

// Aktuelle Signatur laden
$currentSignature = getUserSignature($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digitale Signatur verwalten</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .signature-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .signature-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .signature-canvas-container {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
        }
        
        #signatureCanvas {
            border: 1px dashed #adb5bd;
            border-radius: 4px;
            cursor: crosshair;
            touch-action: none;
        }
        
        .canvas-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .signature-preview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        
        .signature-preview img {
            max-width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: white;
            padding: 10px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .info-box i {
            color: #007bff;
            margin-right: 10px;
        }
        
        .usage-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .usage-info i {
            color: #856404;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="signature-container">
        <h1><i class="fas fa-signature"></i> Digitale Signatur verwalten</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <strong>Hinweis:</strong> Ihre digitale Signatur wird verwendet, um Wartungsberichte und Prüfprotokolle zu signieren. 
            Sie können Ihre Signatur jederzeit ändern oder löschen.
        </div>
        
        <?php if ($currentSignature): ?>
        <div class="signature-card">
            <h2><i class="fas fa-check-circle" style="color: #28a745;"></i> Aktuelle Signatur</h2>
            <div class="signature-preview">
                <img src="<?= e($currentSignature) ?>" alt="Aktuelle Signatur">
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button onclick="showEditSignature()" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Signatur ändern
                </button>
                <a href="?delete=signature&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('Signatur wirklich löschen?')">
                    <i class="fas fa-trash"></i> Signatur löschen
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="signature-card">
            <h2><i class="fas fa-pen"></i> Neue Signatur erstellen</h2>
            <p>Sie haben noch keine Signatur erstellt. Erstellen Sie jetzt Ihre persönliche digitale Signatur.</p>
        </div>
        <?php endif; ?>
        
        <div class="signature-card" id="signatureEditor" style="<?= $currentSignature ? 'display: none;' : '' ?>">
            <h2><i class="fas fa-pen-fancy"></i> Signatur zeichnen</h2>
            
            <div class="usage-info">
                <i class="fas fa-lightbulb"></i>
                <strong>So funktioniert's:</strong> Zeichnen Sie Ihre Signatur mit der Maus oder dem Touchscreen in das Feld unten. 
                Sie können die Signatur jederzeit löschen und neu zeichnen, bis Sie zufrieden sind.
            </div>
            
            <form method="POST" id="signatureForm">
                <?= csrf_field() ?>
                <input type="hidden" name="signature_data" id="signatureDataField">
                
                <div class="signature-canvas-container">
                    <p style="color: #6c757d; margin-bottom: 10px;">
                        <i class="fas fa-mouse"></i> Zeichnen Sie Ihre Signatur hier
                    </p>
                    <canvas id="signatureCanvas" width="600" height="200"></canvas>
                    <div class="canvas-controls">
                        <button type="button" onclick="clearSignature()" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Löschen
                        </button>
                        <button type="button" onclick="undoSignature()" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Rückgängig
                        </button>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="save_signature" class="btn btn-success" id="saveBtn">
                        <i class="fas fa-save"></i> Signatur speichern
                    </button>
                    <?php if ($currentSignature): ?>
                    <button type="button" onclick="hideEditSignature()" class="btn btn-secondary">
                        Abbrechen
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="signature-card">
            <h2><i class="fas fa-question-circle"></i> Häufige Fragen</h2>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #007bff; font-size: 16px; margin-bottom: 10px;">
                    <i class="fas fa-angle-right"></i> Wofür wird meine Signatur verwendet?
                </h3>
                <p style="color: #6c757d; padding-left: 25px;">
                    Ihre digitale Signatur wird verwendet, um Wartungsberichte, Prüfprotokolle und andere Dokumente zu signieren. 
                    Sie bestätigt damit, dass Sie die Wartung oder Prüfung durchgeführt haben.
                </p>
            </div>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #007bff; font-size: 16px; margin-bottom: 10px;">
                    <i class="fas fa-angle-right"></i> Ist meine Signatur sicher?
                </h3>
                <p style="color: #6c757d; padding-left: 25px;">
                    Ja, Ihre Signatur wird verschlüsselt in der Datenbank gespeichert und ist nur für Sie und Administratoren sichtbar.
                    Sie kann jederzeit von Ihnen geändert oder gelöscht werden.
                </p>
            </div>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #007bff; font-size: 16px; margin-bottom: 10px;">
                    <i class="fas fa-angle-right"></i> Kann ich meine Signatur später ändern?
                </h3>
                <p style="color: #6c757d; padding-left: 25px;">
                    Ja, Sie können Ihre Signatur jederzeit ändern oder löschen. Bereits signierte Dokumente behalten 
                    jedoch die alte Signatur, damit die Authentizität gewahrt bleibt.
                </p>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    let strokes = []; // Array für Undo-Funktion
    let currentStroke = [];
    
    // Canvas-Setup
    ctx.strokeStyle = '#000000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    // Maus-Events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Touch-Events für Mobile
    canvas.addEventListener('touchstart', handleTouchStart);
    canvas.addEventListener('touchmove', handleTouchMove);
    canvas.addEventListener('touchend', stopDrawing);
    
    function startDrawing(e) {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        lastX = e.clientX - rect.left;
        lastY = e.clientY - rect.top;
        currentStroke = [{x: lastX, y: lastY}];
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.stroke();
        
        currentStroke.push({x: x, y: y});
        lastX = x;
        lastY = y;
    }
    
    function stopDrawing() {
        if (isDrawing && currentStroke.length > 0) {
            strokes.push([...currentStroke]);
            currentStroke = [];
        }
        isDrawing = false;
    }
    
    function handleTouchStart(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }
    
    function handleTouchMove(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }
    
    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        strokes = [];
        currentStroke = [];
    }
    
    function undoSignature() {
        if (strokes.length === 0) return;
        
        strokes.pop();
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Alle Striche neu zeichnen
        strokes.forEach(stroke => {
            if (stroke.length < 2) return;
            
            ctx.beginPath();
            ctx.moveTo(stroke[0].x, stroke[0].y);
            
            for (let i = 1; i < stroke.length; i++) {
                ctx.lineTo(stroke[i].x, stroke[i].y);
            }
            ctx.stroke();
        });
    }
    
    // Form-Submit
    document.getElementById('signatureForm').addEventListener('submit', function(e) {
        if (strokes.length === 0) {
            e.preventDefault();
            alert('Bitte zeichnen Sie zunächst eine Signatur!');
            return false;
        }
        
        // Canvas zu Base64 konvertieren
        const signatureData = canvas.toDataURL('image/png');
        document.getElementById('signatureDataField').value = signatureData;
    });
    
    function showEditSignature() {
        document.getElementById('signatureEditor').style.display = 'block';
        clearSignature();
    }
    
    function hideEditSignature() {
        document.getElementById('signatureEditor').style.display = 'none';
    }
    </script>
</body>
</html>