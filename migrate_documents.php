<?php
/**
 * Migrations-Script: Verschiebt bestehende Dokumente in Ordner mit Marker-Namen
 * 
 * WICHTIG: Dieses Script nur EINMAL ausführen!
 * Backup der Datenbank und uploads-Ordner erstellen vor der Ausführung!
 */

require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('admin'); // Nur für Admins

set_time_limit(300); // 5 Minuten Timeout

$migrated = 0;
$errors = [];
$skipped = 0;

echo "<!DOCTYPE html><html><head><title>Dokument-Migration</title></head><body>";
echo "<h1>Dokument-Migration gestartet...</h1>";
echo "<pre>";

try {
    // Alle Dokumente laden
    $stmt = $pdo->query("
        SELECT md.*, m.name as marker_name
        FROM marker_documents md
        LEFT JOIN markers m ON md.marker_id = m.id
        WHERE md.marker_id IS NOT NULL
    ");
    $documents = $stmt->fetchAll();
    
    echo "Gefunden: " . count($documents) . " Dokumente\n\n";
    
    foreach ($documents as $doc) {
        $oldPath = $doc['document_path'];
        
        // Prüfe ob Datei existiert
        if (!file_exists($oldPath)) {
            $errors[] = "Datei nicht gefunden: $oldPath";
            $skipped++;
            continue;
        }
        
        // Prüfe ob marker_name bereits gesetzt ist
        if (!empty($doc['marker_name'])) {
            echo "✓ Marker-Name bereits gesetzt für Dokument #{$doc['id']}\n";
        } else {
            // Setze marker_name aus markers Tabelle
            if (!empty($doc['marker_name'])) {
                $pdo->prepare("UPDATE marker_documents SET marker_name = ? WHERE id = ?")
                    ->execute([$doc['marker_name'], $doc['id']]);
                echo "✓ Marker-Name gesetzt für Dokument #{$doc['id']}: {$doc['marker_name']}\n";
            }
        }
        
        // Erstelle sicheren Ordnernamen
        $markerName = $doc['marker_name'] ?? 'Unbekannt';
        $safeFolderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $markerName);
        $safeFolderName = trim($safeFolderName, '_');
        
        // Neuer Pfad
        $newDir = 'uploads/documents/' . $safeFolderName . '/';
        $filename = basename($oldPath);
        $newPath = $newDir . $filename;
        
        // Prüfe ob bereits am richtigen Ort
        if ($oldPath === $newPath) {
            echo "- Überspringe Dokument #{$doc['id']} (bereits am richtigen Ort)\n";
            $skipped++;
            continue;
        }
        
        // Erstelle Zielordner
        if (!is_dir($newDir)) {
            mkdir($newDir, 0755, true);
            echo "✓ Ordner erstellt: $newDir\n";
        }
        
        // Verschiebe Datei
        if (rename($oldPath, $newPath)) {
            // Aktualisiere Datenbank
            $pdo->prepare("UPDATE marker_documents SET document_path = ? WHERE id = ?")
                ->execute([$newPath, $doc['id']]);
            
            echo "✓ Migriert: {$doc['document_name']} → $newPath\n";
            $migrated++;
        } else {
            $errors[] = "Fehler beim Verschieben: $oldPath → $newPath";
        }
    }
    
    echo "\n===========================================\n";
    echo "Migration abgeschlossen!\n";
    echo "Migriert: $migrated Dokumente\n";
    echo "Übersprungen: $skipped Dokumente\n";
    echo "Fehler: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\nFehler-Details:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n\nFEHLER: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='file_manager.php'>Zur Dateiverwaltung</a></p>";
echo "</body></html>";
