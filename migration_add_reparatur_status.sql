-- ======================================================================
-- Migration: Status "reparatur" zu rental_status hinzufügen
-- Datum: 2025-11-13
-- Beschreibung: Erweitert die rental_status Spalte um den neuen Status
--               "reparatur" (In Reparatur)
-- ======================================================================

-- Prüfung: Ist rental_status eine ENUM-Spalte?
-- Wenn ja, dann erweitern wir das ENUM
-- Wenn nein (VARCHAR), dann ist keine Änderung nötig

-- ======================================================================
-- VARIANTE 1: Falls rental_status ein ENUM ist (häufigster Fall)
-- ======================================================================

-- Backup-Tipp: Erstelle vorher ein Backup mit:
-- mysqldump -u root -p bgg markers > markers_backup_$(date +%Y%m%d_%H%M%S).sql

-- ENUM erweitern (funktioniert nur wenn rental_status bereits ENUM ist)
-- EMPFOHLEN: Diese Version enthält auch "auf_messe" für Messe-Geräte
ALTER TABLE markers
MODIFY COLUMN rental_status ENUM('verfuegbar', 'vermietet', 'wartung', 'reparatur', 'auf_messe')
DEFAULT 'verfuegbar';

-- Alternative: Nur "reparatur" ohne "auf_messe" (falls du Messe-Status nicht brauchst)
-- ALTER TABLE markers
-- MODIFY COLUMN rental_status ENUM('verfuegbar', 'vermietet', 'wartung', 'reparatur')
-- DEFAULT 'verfuegbar';

-- ======================================================================
-- VARIANTE 2: Falls rental_status ein VARCHAR ist
-- ======================================================================
-- In diesem Fall ist keine Änderung nötig, da VARCHAR beliebige Werte erlaubt
-- Die neuen Status "reparatur" und "auf_messe" funktionieren automatisch

-- ======================================================================
-- PRÜFUNG: Aktuelle Struktur anzeigen
-- ======================================================================
-- Mit diesem Befehl kannst du die aktuelle Struktur prüfen:

-- SHOW COLUMNS FROM markers LIKE 'rental_status';

-- ======================================================================
-- TEST: Bestehende Werte prüfen
-- ======================================================================
-- Zeige alle aktuellen Status-Werte:

-- SELECT DISTINCT rental_status, COUNT(*) as anzahl
-- FROM markers
-- GROUP BY rental_status;

-- ======================================================================
-- ROLLBACK (falls etwas schief geht)
-- ======================================================================
-- Falls du den alten Zustand wiederherstellen möchtest:

-- ALTER TABLE markers
-- MODIFY COLUMN rental_status ENUM('verfuegbar', 'vermietet', 'wartung')
-- DEFAULT 'verfuegbar';

-- ACHTUNG: Alle Marker mit Status "reparatur" werden auf NULL gesetzt!
