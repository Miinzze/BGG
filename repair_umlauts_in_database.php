<?php
/**
 * Script zum Reparieren von Umlauten direkt in der Datenbank
 *
 * WICHTIG: Erstelle vorher ein Backup der Datenbank!
 *
 * Aufruf:
 * - Browser: https://deine-domain.de/repair_umlauts_in_database.php
 * - CLI: php repair_umlauts_in_database.php
 */

require_once 'config.php';

// Nur für eingeloggte Admins oder über CLI
if (php_sapi_name() !== 'cli') {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        die('Nur Administratoren können dieses Script ausführen.');
    }
}

echo "=== Umlaut-Reparatur in der Datenbank ===\n\n";

// Sicherstellen, dass UTF-8 verwendet wird
$pdo->exec("SET NAMES utf8mb4");
$pdo->exec("SET CHARACTER SET utf8mb4");

echo "1. Charset auf utf8mb4 gesetzt\n\n";

// Mapping von falschen zu korrekten Umlauten
$replacements = [
    'Ã¤' => 'ä',
    'Ã¶' => 'ö',
    'Ã¼' => 'ü',
    'Ã„' => 'Ä',
    'Ã–' => 'Ö',
    'Ãœ' => 'Ü',
    'ÃŸ' => 'ß',
];

// Tabellen und ihre Text-Spalten
$tables = [
    'maintenance_checklists' => ['name', 'description', 'category'],
    'maintenance_checklist_items' => ['item_text'],
    'activity_log' => ['action', 'details'],
    'markers' => ['name', 'description', 'category', 'location'],
    'maintenance_history' => ['description', 'notes'],
];

$totalFixed = 0;

foreach ($tables as $table => $columns) {
    echo "2. Repariere Tabelle: $table\n";

    foreach ($columns as $column) {
        // Prüfe, ob Spalte existiert
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($stmt->rowCount() === 0) {
                echo "   - Spalte '$column' existiert nicht, überspringe\n";
                continue;
            }
        } catch (Exception $e) {
            echo "   - Fehler bei Spalte '$column': " . $e->getMessage() . "\n";
            continue;
        }

        // Baue UPDATE Statement
        $setClauses = [];
        $currentCol = $column;

        foreach ($replacements as $wrong => $correct) {
            $setClauses[] = "REPLACE($currentCol, " . $pdo->quote($wrong) . ", " . $pdo->quote($correct) . ")";
            $currentCol = $setClauses[count($setClauses) - 1];
        }

        $finalSet = end($setClauses);

        // Zähle betroffene Zeilen
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table` WHERE `$column` LIKE '%Ã%'");
            $affected = $stmt->fetch()['count'];

            if ($affected > 0) {
                // Führe Update aus
                $sql = "UPDATE `$table` SET `$column` = $finalSet WHERE `$column` LIKE '%Ã%'";
                $pdo->exec($sql);

                echo "   ✓ Spalte '$column': $affected Zeilen repariert\n";
                $totalFixed += $affected;
            } else {
                echo "   - Spalte '$column': Keine Fehler gefunden\n";
            }
        } catch (Exception $e) {
            echo "   ✗ Fehler bei '$column': " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

echo "=== Zusammenfassung ===\n";
echo "Gesamt reparierte Zeilen: $totalFixed\n\n";

// Zeige Beispiele
echo "=== Test: Beispieldaten ===\n";
try {
    $stmt = $pdo->query("SELECT id, name, description FROM maintenance_checklists LIMIT 3");
    $checklists = $stmt->fetchAll();

    foreach ($checklists as $checklist) {
        echo "\nID {$checklist['id']}:\n";
        echo "  Name: {$checklist['name']}\n";
        if ($checklist['description']) {
            echo "  Beschreibung: " . substr($checklist['description'], 0, 80) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "Fehler beim Abrufen der Testdaten: " . $e->getMessage() . "\n";
}

echo "\n=== Reparatur abgeschlossen ===\n";

// Sicherheitshalber: Lösche dieses Script nach der Ausführung
echo "\nWICHTIG: Bitte lösche dieses Script nach erfolgreicher Ausführung!\n";
echo "Oder benenne es um, damit es nicht versehentlich erneut ausgeführt wird.\n";
?>
