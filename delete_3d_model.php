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
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['csrf_token']) || !validateCSRFToken($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF-Token ungültig']);
    exit;
}

$model_id = isset($input['model_id']) ? intval($input['model_id']) : 0;

if (!$model_id) {
    echo json_encode(['success' => false, 'message' => 'Model ID fehlt']);
    exit;
}

try {
    // Hole Modell-Details
    $stmt = $pdo->prepare("SELECT * FROM marker_3d_models WHERE id = ?");
    $stmt->execute([$model_id]);
    $model = $stmt->fetch();
    
    if (!$model) {
        echo json_encode(['success' => false, 'message' => 'Modell nicht gefunden']);
        exit;
    }
    
    // Lösche Datei vom Server
    $file_path = __DIR__ . '/' . $model['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Lösche aus Datenbank
    $stmt = $pdo->prepare("DELETE FROM marker_3d_models WHERE id = ?");
    $stmt->execute([$model_id]);
    
    // Activity Log
    logActivity($pdo, $_SESSION['user_id'], 'model_3d_deleted', 
                "3D-Modell '{$model['model_name']}' gelöscht", $model['marker_id']);
    
    echo json_encode([
        'success' => true,
        'message' => '3D-Modell wurde erfolgreich gelöscht'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}