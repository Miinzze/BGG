<?php
/**
 * UTF-8 Encoding Fixer für bereits gespeicherte Daten
 *
 * Problem: Daten wurden mit falschem Encoding gespeichert
 * "Ã¤" statt "ä", "Ã¼" statt "ü", etc.
 */

require_once 'config.php';
require_once 'functions.php';
requireLogin();
requireAdmin();

$fixed = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_encoding'])) {
    validateCSRF();

    try {
        // 1. Tabellen zu UTF-8 konvertieren
        $tables = ['maintenance_checklists', 'maintenance_checklist_items', 'markers', 'maintenance_history', 'categories'];

        foreach ($tables as $table) {
            try {
                $pdo->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $fixed[] = "Tabelle '$table' zu UTF-8 konvertiert";
            } catch (PDOException $e) {
                $errors[] = "Fehler bei Tabelle '$table': " . $e->getMessage();
            }
        }

        // 2. Double-Encoding in maintenance_checklists fixen
        $stmt = $pdo->query("SELECT id, name, description FROM maintenance_checklists WHERE name LIKE '%Ã%' OR description LIKE '%Ã%'");
        $broken = $stmt->fetchAll();

        foreach ($broken as $row) {
            try {
                // Fix name
                $fixedName = mb_convert_encoding($row['name'], 'UTF-8', 'ISO-8859-1');
                // Fix description
                $fixedDesc = mb_convert_encoding($row['description'], 'UTF-8', 'ISO-8859-1');

                $updateStmt = $pdo->prepare("UPDATE maintenance_checklists SET name = ?, description = ? WHERE id = ?");
                $updateStmt->execute([$fixedName, $fixedDesc, $row['id']]);

                $fixed[] = "Checkliste ID {$row['id']} repariert: '{$row['name']}' → '$fixedName'";
            } catch (Exception $e) {
                $errors[] = "Fehler bei ID {$row['id']}: " . $e->getMessage();
            }
        }

        // 3. Double-Encoding in maintenance_checklist_items fixen
        $stmt = $pdo->query("SELECT id, item_text FROM maintenance_checklist_items WHERE item_text LIKE '%Ã%'");
        $brokenItems = $stmt->fetchAll();

        foreach ($brokenItems as $row) {
            try {
                $fixedText = mb_convert_encoding($row['item_text'], 'UTF-8', 'ISO-8859-1');

                $updateStmt = $pdo->prepare("UPDATE maintenance_checklist_items SET item_text = ? WHERE id = ?");
                $updateStmt->execute([$fixedText, $row['id']]);

                $fixed[] = "Checklist-Item ID {$row['id']} repariert";
            } catch (Exception $e) {
                $errors[] = "Fehler bei Item ID {$row['id']}: " . $e->getMessage();
            }
        }

        // Cache löschen
        $cache->clear();

    } catch (Exception $e) {
        $errors[] = "Genereller Fehler: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTF-8 Encoding Fixer</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <style>
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #5568d3;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 UTF-8 Encoding Fixer</h1>

        <?php if (!empty($fixed)): ?>
            <div class="success">
                <h3>✅ Erfolgreich repariert (<?= count($fixed) ?>):</h3>
                <pre><?php foreach ($fixed as $msg) echo htmlspecialchars($msg) . "\n"; ?></pre>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <h3>❌ Fehler (<?= count($errors) ?>):</h3>
                <pre><?php foreach ($errors as $msg) echo htmlspecialchars($msg) . "\n"; ?></pre>
            </div>
        <?php endif; ?>

        <div class="warning">
            <h3>⚠️ Warnung</h3>
            <p>Dieses Tool behebt UTF-8 Encoding-Probleme in der Datenbank.</p>
            <p><strong>Es werden folgende Änderungen vorgenommen:</strong></p>
            <ul>
                <li>Alle Tabellen werden zu UTF-8 (utf8mb4) konvertiert</li>
                <li>Falsch gespeicherte Umlaute werden korrigiert (Ã¤ → ä, etc.)</li>
                <li>Betrifft: Checklisten, Checklist-Items, Marker, etc.</li>
            </ul>
            <p><strong>⚠️ Bitte erstellen Sie vorher ein Backup!</strong></p>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <button type="submit" name="fix_encoding" class="btn" onclick="return confirm('Sind Sie sicher? Haben Sie ein Backup erstellt?')">
                🔧 Encoding jetzt reparieren
            </button>
        </form>

        <p style="margin-top: 30px;">
            <a href="settings.php">← Zurück zu den Einstellungen</a>
        </p>
    </div>
</body>
</html>
