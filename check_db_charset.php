<?php
require_once 'config.php';

echo "<h2>Datenbank Charset Überprüfung</h2>\n\n";

// 1. Datenbank-Charset prüfen
echo "<h3>1. Datenbank-Charset:</h3>\n";
$stmt = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
                      FROM information_schema.SCHEMATA
                      WHERE SCHEMA_NAME = '" . DB_NAME . "'");
$dbCharset = $stmt->fetch();
echo "<pre>";
print_r($dbCharset);
echo "</pre>\n\n";

// 2. Tabellen-Charset prüfen
echo "<h3>2. Tabellen-Charset:</h3>\n";
$stmt = $pdo->query("SELECT TABLE_NAME, TABLE_COLLATION
                      FROM information_schema.TABLES
                      WHERE TABLE_SCHEMA = '" . DB_NAME . "'");
$tables = $stmt->fetchAll();
echo "<pre>";
foreach ($tables as $table) {
    echo $table['TABLE_NAME'] . ": " . $table['TABLE_COLLATION'] . "\n";
}
echo "</pre>\n\n";

// 3. Spalten-Charset prüfen (nur relevante Tabellen)
echo "<h3>3. Spalten-Charset (maintenance_checklists):</h3>\n";
$stmt = $pdo->query("SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
                      FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                      AND TABLE_NAME = 'maintenance_checklists'
                      AND DATA_TYPE IN ('varchar', 'text', 'char')");
$columns = $stmt->fetchAll();
echo "<pre>";
print_r($columns);
echo "</pre>\n\n";

// 4. Test-Daten abrufen
echo "<h3>4. Test-Daten aus maintenance_checklists:</h3>\n";
$stmt = $pdo->query("SELECT id, name, description FROM maintenance_checklists LIMIT 3");
$checklists = $stmt->fetchAll();
echo "<pre>";
foreach ($checklists as $checklist) {
    echo "ID: " . $checklist['id'] . "\n";
    echo "Name: " . $checklist['name'] . "\n";
    echo "Description: " . substr($checklist['description'], 0, 100) . "...\n";
    echo "---\n";
}
echo "</pre>\n\n";

// 5. Verbindungs-Charset prüfen
echo "<h3>5. Aktuelle Verbindungs-Einstellungen:</h3>\n";
$stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
$charsetVars = $stmt->fetchAll();
echo "<pre>";
print_r($charsetVars);
echo "</pre>\n\n";

$stmt = $pdo->query("SHOW VARIABLES LIKE 'collation%'");
$collationVars = $stmt->fetchAll();
echo "<pre>";
print_r($collationVars);
echo "</pre>\n";
?>
