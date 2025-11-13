<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// Prüfen ob View-As aktiv ist
if (!isset($_SESSION['view_as_active']) || !isset($_SESSION['view_as_backup'])) {
    header('Location: dashboard.php');
    exit;
}

$originalUserId = $_SESSION['view_as_backup']['user_id'];
$viewedUserId = $_SESSION['user_id'];

// View-As Log beenden
if (isset($_SESSION['view_as_log_id'])) {
    $stmt = $pdo->prepare("UPDATE user_view_as_logs SET end_time = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['view_as_log_id']]);
}

// Session wiederherstellen
$_SESSION['user_id'] = $_SESSION['view_as_backup']['user_id'];
$_SESSION['username'] = $_SESSION['view_as_backup']['username'];
$_SESSION['role'] = $_SESSION['view_as_backup']['role'];
$_SESSION['role_id'] = $_SESSION['view_as_backup']['role_id'];
$_SESSION['permissions'] = $_SESSION['view_as_backup']['permissions'];

// View-As Daten löschen
unset($_SESSION['view_as_active']);
unset($_SESSION['view_as_backup']);
unset($_SESSION['view_as_log_id']);

logActivity('view_as_user_ended', "Anzeige als Benutzer beendet (User-ID: {$viewedUserId})", $originalUserId);

header('Location: users.php');
exit;
