<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$setId = $_GET['set_id'] ?? null;

if (!$setId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM maintenance_set_fields 
    WHERE maintenance_set_id = ? 
    ORDER BY sort_order ASC
");
$stmt->execute([$setId]);
$fields = $stmt->fetchAll();

echo json_encode($fields);