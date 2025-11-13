<?php
require_once 'config.php';
require_once 'functions.php';

// Authentifizierung erforderlich
requireLogin();
requirePermission('markers_edit');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfragemethode']);
    exit;
}

// CSRF-Schutz
validateCSRF();

$marker_id = isset($_POST['marker_id']) ? intval($_POST['marker_id']) : 0;
$model_name = isset($_POST['model_name']) ? trim($_POST['model_name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$is_public = isset($_POST['is_public']) && $_POST['is_public'] == '1' ? 1 : 0;

if (!$marker_id) {
    echo json_encode(['success' => false, 'message' => 'Marker ID fehlt']);
    exit;
}

// Prüfe ob Benutzer Berechtigung für diesen Marker hat
$stmt = $pdo->prepare("SELECT id FROM markers WHERE id = ?");
$stmt->execute([$marker_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Marker nicht gefunden']);
    exit;
}

// Prüfe ob Datei hochgeladen wurde
if (!isset($_FILES['model_file']) || $_FILES['model_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Keine Datei hochgeladen oder Upload-Fehler']);
    exit;
}

$file = $_FILES['model_file'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Erlaubte 3D-Formate
$allowed_formats = ['glb', 'gltf', 'obj', 'fbx', 'usdz'];

if (!in_array($file_extension, $allowed_formats)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Dateiformat. Erlaubt: ' . implode(', ', $allowed_formats)]);
    exit;
}

// Erstelle Upload-Verzeichnis falls nicht vorhanden
$upload_dir = __DIR__ . '/uploads/3d_models/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generiere eindeutigen Dateinamen
$unique_name = 'model_' . $marker_id . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $unique_name;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern der Datei']);
    exit;
}

// Speichere in Datenbank
try {
    $stmt = $pdo->prepare("
        INSERT INTO marker_3d_models 
        (marker_id, model_name, description, file_name, file_path, file_size, file_format, uploaded_by, is_public) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $marker_id,
        $model_name ?: 'Mobiles 3D-Modell',
        $description,
        $unique_name,
        'uploads/3d_models/' . $unique_name,
        $file['size'],
        $file_extension,
        $_SESSION['user_id'],
        $is_public
    ]);
    
    // Activity Log
    logActivity($pdo, $_SESSION['user_id'], 'model_3d_uploaded', "3D-Modell für Marker #$marker_id hochgeladen", $marker_id);
    
    echo json_encode([
        'success' => true, 
        'message' => '3D-Modell erfolgreich hochgeladen',
        'model_id' => $pdo->lastInsertId(),
        'file_path' => 'uploads/3d_models/' . $unique_name
    ]);
} catch (PDOException $e) {
    // Lösche Datei bei Datenbankfehler
    unlink($upload_path);
    echo json_encode(['success' => false, 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
}