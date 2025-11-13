<?php
require_once 'config.php';

// Alte Login-Versuche löschen
$pdo->query("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 7 DAY)");

// Alte Logs archivieren
$pdo->query("DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)");

// Abgelaufene Remember-Tokens löschen
$pdo->query("DELETE FROM remember_tokens WHERE expires_at < NOW()");

echo "Cleanup completed: " . date('Y-m-d H:i:s') . "\n";