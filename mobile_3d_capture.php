<?php
// PHP-Code zuerst, um Header-Probleme zu vermeiden
require_once 'config.php';
require_once 'functions.php';

// Authentifizierung erforderlich
requireLogin();
requirePermission('markers_edit');

$marker_id = isset($_GET['marker_id']) ? intval($_GET['marker_id']) : 0;

if (!$marker_id) {
    die('Marker ID fehlt');
}

// Prüfe ob Marker existiert
$stmt = $pdo->prepare("SELECT name FROM markers WHERE id = ?");
$stmt->execute([$marker_id]);
$marker = $stmt->fetch();

if (!$marker) {
    die('Marker nicht gefunden');
}

// Mobile Device Check
$isMobile = preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);

if (!$isMobile) {
    echo '<div style="padding: 40px; text-align: center;">';
    echo '<h1><i class="fas fa-mobile-alt"></i> Nur auf Mobilgeräten</h1>';
    echo '<p>Diese Funktion ist nur auf Smartphones verfügbar.</p>';
    echo '<a href="edit_marker.php?id=' . $marker_id . '" class="btn btn-primary">Zurück zur Bearbeitung</a>';
    echo '</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D-Erfassung</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/3d-features.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Three.js und Loader - WICHTIG: Diese müssen VOR dem 3d-viewer.js geladen werden! -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
</head>
<body>
    <div class="capture-3d-container">
        <div class="camera-preview">
            <video id="cameraVideo" autoplay playsinline></video>
            <div class="capture-overlay">
                <div class="capture-guide"></div>
                <div class="badge-360"><i class="fas fa-sync-alt"></i> 360° Aufnahme</div>
                <div class="capture-progress">
                    <span><i class="fas fa-camera"></i> Bilder: <strong id="imageCount">0</strong> / 20</span>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="capture-buttons">
            <button onclick="captureImage()" class="btn-capture"><i class="fas fa-camera"></i></button>
            <button onclick="finishCapture()" class="btn-secondary">Fertig</button>
        </div>
        <div class="captured-images" id="capturedImages"></div>
    </div>

    <script src="js/3d-viewer.js"></script>
    <script>
        let capture3D = new Mobile3DCapture();
        let cameraStream = null;
        const markerId = <?= $marker_id ?>;

        async function startCamera() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                document.getElementById('cameraVideo').srcObject = cameraStream;
            } catch (error) {
                alert('Kamera nicht verfügbar: ' + error.message);
            }
        }

        async function captureImage() {
            const video = document.getElementById('cameraVideo');
            const blob = await capture3D.captureImage(video);
            const result = await capture3D.addImage(blob);
            
            if (result.success) {
                document.getElementById('imageCount').textContent = result.count;
                document.getElementById('progressFill').style.width = result.progress + '%';
            }
        }

        async function finishCapture() {
            if (capture3D.images.length < 8) {
                alert('Mindestens 8 Bilder benötigt!');
                return;
            }
            
            const formData = new FormData();
            formData.append('marker_id', markerId);
            capture3D.images.forEach((img, i) => {
                formData.append('images[]', img.blob, `img_${i}.jpg`);
            });
            
            // CSRF-Token hinzufügen
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            const response = await fetch('process_3d_reconstruction.php', {
                method: 'POST',
                headers: csrfToken ? { 'X-CSRF-Token': csrfToken } : {},
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Erfolgreich! Die Bilder wurden hochgeladen.');
                window.location.href = 'edit_marker.php?id=' + markerId;
            } else {
                alert('Fehler: ' + result.message);
            }
        }

        startCamera();
    </script>
</body>
</html>