<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

if (!isset($_SESSION['impersonation_active']) || !$_SESSION['impersonation_active']) {
    header('Location: index.php');
    exit;
}

// Log beenden
if (isset($_SESSION['impersonation_log_id'])) {
    $stmt = $pdo->prepare("UPDATE user_impersonation_log SET ended_at = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['impersonation_log_id']]);
}

logActivity('user_impersonation_ended', "Admin '{$_SESSION['impersonation_original_username']}' beendet Impersonation");

// Zurück zur Original-Session
$_SESSION['user_id'] = $_SESSION['impersonation_original_user_id'];
$_SESSION['username'] = $_SESSION['impersonation_original_username'];
$_SESSION['role'] = $_SESSION['impersonation_original_role'];

// Impersonation-Daten löschen
unset($_SESSION['impersonation_original_user_id']);
unset($_SESSION['impersonation_original_username']);
unset($_SESSION['impersonation_original_role']);
unset($_SESSION['impersonation_active']);
unset($_SESSION['impersonation_log_id']);

header('Location: users.php');
exit;
?>
