<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['completed']) && $data['completed']) {
    $stmt = $pdo->prepare("
        INSERT INTO user_onboarding (user_id, tour_completed, tour_completed_at)
        VALUES (?, 1, NOW())
        ON DUPLICATE KEY UPDATE tour_completed = 1, tour_completed_at = NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("
        UPDATE user_checklist SET completed = 1, completed_at = NOW()
        WHERE user_id = ? AND checklist_item = 'tour_completed'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    logActivity('tour_completed', "Interaktive Tour abgeschlossen");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
