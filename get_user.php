<?php
require_once 'config.php';
require_once 'functions.php';
// ✅ GEÄNDERT: Von requireAdmin() zu spezifischer Permission
requirePermission('users_view');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Keine ID angegeben']);
    exit;
}

$userId = $_GET['id'];

$stmt = $pdo->prepare("SELECT id, username, email, role, receive_maintenance_emails FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode($user);
} else {
    echo json_encode(['error' => 'Benutzer nicht gefunden']);
}
?>