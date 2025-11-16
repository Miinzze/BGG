-- SQL-Script zum Reparieren von Umlauten direkt in der Datenbank
-- WICHTIG: Erstelle vorher ein Backup!

-- Charset sicherstellen
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Repariere Umlaute in maintenance_checklists
UPDATE maintenance_checklists SET
    name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        name,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß'),
    description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        description,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß')
WHERE name LIKE '%Ã%' OR description LIKE '%Ã%';

-- Repariere maintenance_checklist_items
UPDATE maintenance_checklist_items SET
    item_text = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        item_text,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß')
WHERE item_text LIKE '%Ã%';

-- Repariere activity_log
UPDATE activity_log SET
    action = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        action,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß'),
    details = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        details,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß')
WHERE action LIKE '%Ã%' OR details LIKE '%Ã%';

-- Repariere markers
UPDATE markers SET
    name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        name,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß'),
    description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        description,
        'Ã¤', 'ä'),
        'Ã¶', 'ö'),
        'Ã¼', 'ü'),
        'Ã„', 'Ä'),
        'Ã–', 'Ö'),
        'Ãœ', 'Ü'),
        'ÃŸ', 'ß')
WHERE name LIKE '%Ã%' OR description LIKE '%Ã%';

-- Zeige Ergebnis
SELECT 'Reparatur abgeschlossen!' AS Status;
SELECT COUNT(*) AS 'Betroffene Checklisten' FROM maintenance_checklists WHERE name LIKE '%ü%' OR name LIKE '%ä%' OR name LIKE '%ö%';
