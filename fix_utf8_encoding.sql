-- UTF-8 Encoding Fix für bestehende Daten
-- Problem: Daten wurden falsch gespeichert (Double-Encoding)

-- Schritt 1: Prüfe aktuelle Collation
SELECT
    TABLE_NAME,
    TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('maintenance_checklists', 'maintenance_checklist_items', 'markers');

-- Schritt 2: Konvertiere Tabellen zu UTF-8 (falls nicht bereits)
ALTER TABLE maintenance_checklists CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE maintenance_checklist_items CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE markers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE maintenance_history CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Schritt 3: Für bereits falsch gespeicherte Daten (Double-Encoding-Fix)
-- ACHTUNG: Nur ausführen wenn Umlaute als "Ã¤" statt "ä" angezeigt werden!
--
-- UPDATE maintenance_checklists
-- SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4)
-- WHERE name LIKE '%Ã%';
--
-- UPDATE maintenance_checklists
-- SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4)
-- WHERE description LIKE '%Ã%';
