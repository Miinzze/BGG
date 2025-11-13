<?php
require_once 'config.php';
require_once 'functions.php';

// Authentifizierung erforderlich
requireLogin();
requirePermission('markers_edit');

// CSRF-Schutz
validateCSRF();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfragemethode']);
    exit;
}

$marker_id = isset($_POST['marker_id']) ? intval($_POST['marker_id']) : 0;

if (!$marker_id) {
    echo json_encode(['success' => false, 'message' => 'Marker ID fehlt']);
    exit;
}

// Prüfe ob Marker existiert
$stmt = $pdo->prepare("SELECT id FROM markers WHERE id = ?");
$stmt->execute([$marker_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Marker nicht gefunden']);
    exit;
}

// Prüfe ob mindestens 8 Bilder hochgeladen wurden
if (!isset($_FILES['images']) || count($_FILES['images']['name']) < 8) {
    echo json_encode(['success' => false, 'message' => 'Mindestens 8 Bilder benötigt']);
    exit;
}

// Erstelle Upload-Verzeichnis
$upload_dir = __DIR__ . '/uploads/3d_reconstruction/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$session_id = uniqid('3d_' . $marker_id . '_', true);
$session_dir = $upload_dir . $session_id . '/';
mkdir($session_dir, 0755, true);

// Speichere alle Bilder
$saved_images = [];
$total_images = count($_FILES['images']['name']);

for ($i = 0; $i < $total_images; $i++) {
    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['images']['tmp_name'][$i];
        $filename = 'img_' . str_pad($i, 3, '0', STR_PAD_LEFT) . '.jpg';
        $destination = $session_dir . $filename;
        
        if (move_uploaded_file($tmp_name, $destination)) {
            $saved_images[] = $filename;
        }
    }
}

if (count($saved_images) < 8) {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern der Bilder']);
    exit;
}

// HINWEIS: Echte 3D-Rekonstruktion würde hier Tools wie:
// - Meshroom (AliceVision)
// - COLMAP
// - RealityCapture
// - oder andere Photogrammetrie-Software verwenden
//
// Für diese Demo erstellen wir nur einen Platzhalter-Eintrag

try {
    // Erstelle einen Datenbankeintrag als "in Bearbeitung"
    $stmt = $pdo->prepare("
        INSERT INTO marker_3d_models 
        (marker_id, model_name, description, file_name, file_path, file_size, file_format, uploaded_by, is_public) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $model_name = 'Mobile Capture - ' . date('d.m.Y H:i');
    $description = 'Erstellt aus ' . count($saved_images) . ' Bildern';
    
    // In echter Implementierung würde hier das fertige 3D-Modell gespeichert
    $placeholder_file = 'pending_reconstruction_' . $session_id . '.glb';
    $file_path = 'uploads/3d_reconstruction/' . $session_id . '/' . $placeholder_file;
    
    $stmt->execute([
        $marker_id,
        $model_name,
        $description,
        $placeholder_file,
        $file_path,
        0, // Dateigröße noch unbekannt
        'glb',
        $_SESSION['user_id']
    ]);
    
    $model_id = $pdo->lastInsertId();
    
    // Speichere Metadaten für spätere Verarbeitung
    $metadata = [
        'model_id' => $model_id,
        'marker_id' => $marker_id,
        'session_id' => $session_id,
        'images' => $saved_images,
        'image_count' => count($saved_images),
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => $_SESSION['user_id'],
        'status' => 'pending'
    ];
    
    file_put_contents($session_dir . 'metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
    
    // Activity Log
    logActivity($pdo, $_SESSION['user_id'], 'model_3d_capture', 
                "3D-Capture mit $total_images Bildern für Marker #$marker_id gestartet", $marker_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bilder erfolgreich hochgeladen. Die 3D-Rekonstruktion wird gestartet.',
        'model_id' => $model_id,
        'session_id' => $session_id,
        'image_count' => count($saved_images),
        'note' => 'In einer Produktionsumgebung würde hier eine echte 3D-Rekonstruktion durchgeführt werden.'
    ]);
    
} catch (PDOException $e) {
    // Lösche hochgeladene Dateien bei Fehler
    array_map('unlink', glob($session_dir . '*'));
    rmdir($session_dir);
    
    echo json_encode(['success' => false, 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
}