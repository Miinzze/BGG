<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['tour_completed'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$userId = $_SESSION['user_id'];
$page = $input['page'] ?? '';

try {
    // Tour-Status aktualisieren
    $stmt = $pdo->prepare("
        INSERT INTO user_tour_status (user_id, welcome_tour_seen) 
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE welcome_tour_seen = 1, updated_at = NOW()
    ");
    $stmt->execute([$userId]);
    
    // Onboarding-Checklist aktualisieren
    $stmt = $pdo->prepare("
        UPDATE user_onboarding_checklist 
        SET tour_completed = 1 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    
    logActivity('tour_completed', "Tour abgeschlossen: {$page}");
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
