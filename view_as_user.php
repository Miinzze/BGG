<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('system_admin');

$userId = $_GET['user_id'] ?? 0;

if (!validateInteger($userId, 1)) {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    die('Benutzer nicht gefunden');
}

if ($userId == $_SESSION['user_id']) {
    header('Location: edit_user.php?id=' . $userId);
    exit;
}

// Aktuelle Session sichern
$_SESSION['impersonation_original_user_id'] = $_SESSION['user_id'];
$_SESSION['impersonation_original_username'] = $_SESSION['username'];
$_SESSION['impersonation_original_role'] = $_SESSION['role'];
$_SESSION['impersonation_active'] = true;

// Als Ziel-Benutzer anmelden
$_SESSION['user_id'] = $targetUser['id'];
$_SESSION['username'] = $targetUser['username'];
$_SESSION['role'] = $targetUser['role'];
$_SESSION['role_id'] = $targetUser['role_id'];

// Log erstellen
$stmt = $pdo->prepare("
    INSERT INTO user_impersonation_log (admin_user_id, impersonated_user_id, ip_address, user_agent) 
    VALUES (?, ?, ?, ?)
");
$stmt->execute([
    $_SESSION['impersonation_original_user_id'],
    $userId,
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['HTTP_USER_AGENT']
]);

$impersonationLogId = $pdo->lastInsertId();
$_SESSION['impersonation_log_id'] = $impersonationLogId;

logActivity('user_impersonation_started', "Admin '{$_SESSION['impersonation_original_username']}' zeigt System als '{$targetUser['username']}' an");

header('Location: index.php');
exit;
?>
