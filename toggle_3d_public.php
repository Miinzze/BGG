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
$is_public = isset($input['is_public']) && $input['is_public'] ? 1 : 0;

if (!$model_id) {
    echo json_encode(['success' => false, 'message' => 'Model ID fehlt']);
    exit;
}

try {
    // Hole Modell-Details
    $stmt = $pdo->prepare("SELECT marker_id, model_name FROM marker_3d_models WHERE id = ?");
    $stmt->execute([$model_id]);
    $model = $stmt->fetch();
    
    if (!$model) {
        echo json_encode(['success' => false, 'message' => 'Modell nicht gefunden']);
        exit;
    }
    
    // Update is_public
    $stmt = $pdo->prepare("UPDATE marker_3d_models SET is_public = ? WHERE id = ?");
    $stmt->execute([$is_public, $model_id]);
    
    // Activity Log
    $action = $is_public ? 'öffentlich' : 'privat';
    logActivity($pdo, $_SESSION['user_id'], 'model_3d_visibility_changed', 
                "3D-Modell '{$model['model_name']}' auf $action gesetzt", $model['marker_id']);
    
    echo json_encode([
        'success' => true,
        'message' => '3D-Modell Sichtbarkeit wurde aktualisiert',
        'is_public' => $is_public
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}